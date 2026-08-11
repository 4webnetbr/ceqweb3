<?php

namespace App\Commands;

use App\Models\Ocorre\OcorreOcorrenciaAcaoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Backfill de oco_ocorrencia_acao para ocorrências criadas ANTES da feature
 * de tratativa parcial existir (nenhuma linha ainda em oco_ocorrencia_acao).
 *
 * Regra (decisão do usuário, 2026-08-10):
 *  - stt_id=28 (Pendente): semeia o catálogo do subtipo como pendente
 *    (oac_executada='N') — mesmo efeito de OcoTrataOcorrencia::seedAcoes(),
 *    só que em lote, sem esperar alguém abrir a tela de tratativa.
 *  - Qualquer outro status (ocorrência já tratada sob o modelo antigo,
 *    quando só existiam colunas escalares por ocorrência): aproximação —
 *    marca TODAS as ações do catálogo como executadas (oac_executada='S'),
 *    já que não há como reconstruir com certeza qual ação individual
 *    rodou quando o subtipo tinha mais de uma ação configurada (essa
 *    granularidade nunca foi gravada no modelo antigo). Para a linha cujo
 *    tpa_id bate com o oco_ocorrencia.tpa_id (resumo antigo), copia
 *    oco_justi/tmo_id do resumo para preservar o que dá pra preservar.
 *  - Catálogo vazio: não insere nada (mesmo comportamento de seedAcoes()).
 *
 * Sem parâmetro, roda em modo DRY-RUN (só relata o que faria). Passe
 * `-force` para gravar de verdade.
 */
class OcorrenciaBackfillAcoes extends BaseCommand
{
    protected $group       = 'Ocorrencia';
    protected $name        = 'ocorrencia:backfill-acoes';
    protected $description = 'Semeia oco_ocorrencia_acao para ocorrências antigas (criadas antes desta feature existir).';
    protected $usage       = 'ocorrencia:backfill-acoes [-force]';
    protected $options     = [
        '-force' => 'Grava de verdade. Sem essa opção, roda em modo DRY-RUN (só relata).',
    ];

    private OcorreOcorrenciaModel $ocorrencia;
    private OcorreOcorrenciaAcaoModel $ocorrenciaAcao;
    private OcorreSubtOcorrenciaModel $subtocorrencia;
    private OcorreTipoAcaoModel $tipoacao;

    public function run(array $params)
    {
        $force = (bool) (CLI::getOption('force') ?? false);

        $this->ocorrencia     = new OcorreOcorrenciaModel();
        $this->ocorrenciaAcao = new OcorreOcorrenciaAcaoModel();
        $this->subtocorrencia = new OcorreSubtOcorrenciaModel();
        $this->tipoacao       = new OcorreTipoAcaoModel();

        $db = \Config\Database::connect('dbOcorrencia');

        // Ocorrências sem nenhuma linha em oco_ocorrencia_acao ainda.
        $orfas = $db->table('oco_ocorrencia o')
            ->select('o.oco_id, o.sut_id, o.stt_id, o.tpa_id, o.tmo_id, o.oco_justi, o.oco_data_fim, o.usu_fina')
            ->where('NOT EXISTS (SELECT 1 FROM oco_ocorrencia_acao a WHERE a.oco_id = o.oco_id)', null, false)
            ->get()
            ->getResultArray();

        if (empty($orfas)) {
            CLI::write('Nada a fazer — todas as ocorrências já têm linhas em oco_ocorrencia_acao.', 'green');
            return;
        }

        CLI::write(count($orfas) . ' ocorrência(s) sem linhas em oco_ocorrencia_acao encontrada(s).', 'yellow');
        CLI::write($force ? 'Modo: GRAVANDO de verdade (-force).' : 'Modo: DRY-RUN (nada será gravado — rode com -force para aplicar).', $force ? 'red' : 'yellow');
        CLI::newLine();

        $catalogoCache = [];
        $tiposCache    = [];
        $totalSeed     = 0;
        $totalBackfill = 0;
        $totalSemCatalogo = 0;

        foreach ($orfas as $oco) {
            $oco_id = (int) $oco['oco_id'];
            $sut_id = (int) $oco['sut_id'];

            if (!array_key_exists($sut_id, $catalogoCache)) {
                $catalogoCache[$sut_id] = $this->subtocorrencia->getTOAcao($sut_id);
            }
            $catalogo = $catalogoCache[$sut_id];

            if (empty($catalogo)) {
                $totalSemCatalogo++;
                CLI::write("  oco_id={$oco_id} (sut_id={$sut_id}): catálogo vazio, nada a semear.", 'dark_gray');
                continue;
            }

            if (!array_key_exists($sut_id, $tiposCache)) {
                $tipos = [];
                foreach ($this->tipoacao->getTipoAcao(array_column($catalogo, 'tpa_id')) as $tp) {
                    $tipos[$tp->tpa_id] = $tp->tpa_tipo;
                }
                $tiposCache[$sut_id] = $tipos;
            }
            $tipos = $tiposCache[$sut_id];

            $pendente = (int) $oco['stt_id'] === 28;
            $agora    = date('Y-m-d H:i:s');
            $linhas   = [];

            foreach ($catalogo as $acao) {
                $linha = [
                    'oco_id'        => $oco_id,
                    'tpa_id'        => $acao->tpa_id,
                    'tpa_tipo'      => $tipos[$acao->tpa_id] ?? 0,
                    'oac_auto'      => $acao->sta_fina ?? 'N',
                    'tmo_id'        => $acao->tmo_id ?? null,
                    'stt_id'        => $acao->stt_id ?? null,
                    'tel_id'        => $acao->tel_id ?? null,
                    'oco_justi'     => null,
                    'oac_executada' => 'N',
                    'oac_erro'      => 0,
                    'oac_msg'       => null,
                    'usu_executou'  => null,
                    'oac_automatica' => 0,
                    'oac_criado'    => $agora,
                    'oac_executado_em' => null,
                ];

                if (!$pendente) {
                    // Ocorrência já tratada sob o modelo antigo — aproximação:
                    // marca como executada. Sem como saber a ordem/detalhe
                    // real por ação (nunca foi gravado no modelo antigo).
                    $linha['oac_executada']    = 'S';
                    $linha['oac_automatica']   = (int) $oco['stt_id'] === 29 ? 1 : 0;
                    $linha['oac_executado_em'] = $oco['oco_data_fim'] ?: $agora;
                    $linha['usu_executou']     = $oco['usu_fina'] ?: null;

                    // Linha cujo tpa_id bate com o resumo antigo (oco_ocorrencia.tpa_id)
                    // recebe o que dá pra preservar do resumo (justificativa/tmo_id).
                    if (!empty($oco['tpa_id']) && (int) $oco['tpa_id'] === (int) $acao->tpa_id) {
                        $linha['oco_justi'] = $oco['oco_justi'] ?: null;
                        $linha['tmo_id']    = $oco['tmo_id'] ?: $linha['tmo_id'];
                    }
                }

                $linhas[] = $linha;
            }

            $rotulo = $pendente ? 'seed pendente' : 'backfill (marcado executado, aproximação)';
            CLI::write("  oco_id={$oco_id} (sut_id={$sut_id}, stt_id={$oco['stt_id']}): " . count($linhas) . " linha(s) — {$rotulo}");

            if ($pendente) {
                $totalSeed += count($linhas);
            } else {
                $totalBackfill += count($linhas);
            }

            if ($force) {
                $db->transBegin();
                try {
                    $this->ocorrenciaAcao->insertBatch($linhas);
                    $db->transCommit();
                } catch (\Throwable $e) {
                    $db->transRollback();
                    CLI::error("  Falha ao gravar oco_id={$oco_id}: " . $e->getMessage());
                }
            }
        }

        CLI::newLine();
        CLI::write('Resumo:', 'green');
        CLI::write("  Ocorrências pendentes semeadas: {$totalSeed} linha(s) em " . ($totalSeed > 0 ? 'N' : '0') . ' ocorrência(s).');
        CLI::write("  Ocorrências já tratadas (backfill aproximado): {$totalBackfill} linha(s).");
        CLI::write("  Sem catálogo (nada a semear): {$totalSemCatalogo} ocorrência(s).");

        if (!$force) {
            CLI::newLine();
            CLI::write('Nada foi gravado (DRY-RUN). Rode novamente com -force para aplicar.', 'yellow');
        }
    }
}
