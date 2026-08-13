<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Controllers\Ocorrencia\OcoOcorrencia;

use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreOcorrenciaAcaoModel;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Models\Fornec\FornecNotifDesvioModel;
use App\Models\Produt\ProdutProdutoModel;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $permissao;
    public $ocorrencia;
    public $ocorrenciaAcao;
    public $subtocorrencia;
    public $tipoacao;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        // Inicialização dos models auxiliares
        $this->ocorrencia     = new OcorreOcorrenciaModel();
        $this->ocorrenciaAcao = new OcorreOcorrenciaAcaoModel();
        $this->subtocorrencia = new OcorreSubtOcorrenciaModel();
        $this->tipoacao       = new OcorreTipoAcaoModel();

        // debug($this->data['erromsg'], true);
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * errof7
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de abertura
     * index
     *
     * @param mixed $id
     * @return void
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        // Renderiza view de listagem
        echo view('vw_lista', $this->data);
    }

    /**
     * Tela de listagem
     * lista
     *
     * @param mixed $id
     * @return void
     */
    public function lista()
    {
        // Monta definição dos campos da listagem
        $campos = montaColunasCampos($this->data, 'oco_id');
        $dados  = $this->ocorrencia->getListaPendente([28, 37]);

        // Caso não existam registros
        if (! $dados) {
            return $this->response->setJSON(['data' => []]);
        }
        foreach ($dados as $item) {
            unset($item->oco_ativo);
        }
        // debug($dados, true); 

        $oco_ids    = array_map(fn($o) => $o->oco_id, $dados);
        $logGeracao = buscaLogTabela('oco_ocorrencia', $oco_ids);

        $this->data['exclusao']    = false; // não tem exclusão
        $this->data['edicao']      = false; // não tem edição
        $this->data['allconsulta'] = true;

        $base_url = base_url($this->data['controler']);

        // Processa cada ocorrência
        foreach ($dados as $nov) {

            // Usuário que realizou a última alteração
            $usuLog        = $logGeracao[$nov->oco_id]['usua_alterou'] ?? '';
            $nov->usu_nome = $usuLog;

            // // Define usuário de finalização se estiver finalizada
            // if ((int) $nov->stt_id === 30) {
            //     $nov->usu_fina = $usuLog;
            // } else {
            //     $nov->usu_fina = '';
            // }

            $nov->acao_person = [];

            // Botão de finalizar se estiver pendente
            // if (trim($nov->stt_nome ?? '') === 'Pendente') {
            $url_finalizar      = $base_url . '/finalizar/' . $nov->oco_id;
            $nov->acao_person[] = "
                    <button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0'
                        data-mdb-toggle='tooltip'
                        data-mdb-placement='top'
                        title='Finalizar Tratativa'
                        onclick='redireciona(\"$url_finalizar\")'>
                        <i class='fas fa-check'></i>
                    </button>
                ";
            // }
        }
        // Retorna JSON formatado para DataTable
        return $this->response->setJSON([
            'data' => montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1]),
        ]);
    }

    /**
     * visualização
     * show
     *
     * @param mixed $id
     * @return void
     */
    public function show($id)
    {
        return redirect()->to('/OcoOcorrencia/show/' . $id);
    }

    /**
     * RN03.15 — retorna o HTML de uma nova linha de "ação extra" (avulsa),
     * para ser adicionada dinamicamente na aba Ações de finalizar().
     * Bloqueante 2 (revisão 01): a linha traz também os campos condicionais
     * (oco_justi/tmo_id/mod_id+tel_id/stt_id) escondidos por padrão — o
     * mesmo padrão já usado em T9, alternado via `verificaTipoAcao()`
     * (my_fields.js).
     *
     * @param mixed $oco_id
     * @param mixed $ind
     * @return void
     */
    public function addCampoAcao($oco_id, $ind)
    {
        $dados = $this->ocorrencia->getOcorrencia($oco_id);

        $entity = new EntOcoTratativa($dados);
        $fields = $entity->defCamposAcao(null, (int) $ind);
        // $html = debug($fields);

        $html = "<tr><td><div class='row col-12'>";
        $html .= "<div class='col-11'>";
        $html .= "<div class='col-4 float-start'>";
        $html .= $fields['tpa_id'];
        $html .= "</div>";
        $html .= "<div class='col-6 float-start'>";
        $html .= "<div id='divjust[$ind]' class='d-none row col-12'>" . $fields['oco_justi'] . "</div>";
        $html .= "<div id='divmovi[$ind]' class='d-none row col-12'>" . $fields['tmo_id'] . "</div>";
        $html .= "<div id='divtela[$ind]' class='d-none row col-12'>" . $fields['mod_id'] . $fields['tel_id'] . "</div>";
        $html .= "<div id='divstat[$ind]' class='d-none row col-12'>" . $fields['stt_id'] . "</div>";
        $html .= "</div>";
        $html .= $fields['executar'];
        $html .= "</div>";
        $html .= "<div class='col-1'>";
        $html .= $fields['bt_del'];
        $html .= "</div>";
        $html .= '</div></td></tr>';

        return $this->response->setJSON(['html' => $html]);
    }

    /**
     * finalização da tratativa
     * finalizar
     *
     * @param mixed $id
     * @return void
     */
    public function finalizar($id)
    {
        $dados = $this->ocorrencia->getOcorrencia($id);
        // debug($dados, true);

        // Valida se a ocorrência existe
        if (! $dados) {
            throw new \Exception('Ocorrência não encontrada');
        }

        $log             = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? null;
        // Instancia a entity
        // debug($dados, true);
        $entoco    = new EntOcoOcorrencia((array) $dados, true);
        $fields    = $entoco->campos;
        $contOcorr = new OcoOcorrencia();
        $secao[0]  = 'Dados Gerais';
        $campos    = $contOcorr->showCabecalho($dados);

        $etiqueta = fmtEtiquetaCor($dados->stt_cor, $dados->stt_nome, 1);

        // BLOCO TELAS APLICAVEIS
        $entity   = new EntOcoSubtOcorrencia((array) $dados);
        $sutModel = new OcorreSubtOcorrenciaModel();

        // BLOCO DAS AÇÕES — fonte passa a ser oco_ocorrencia_acao (semeada
        // na criação/processAfterSave), não mais o catálogo
        // oco_subt_ocorrencia_acao. Reflete TODAS as linhas da ocorrência
        // (pendentes e já executadas, de qualquer rodada — automática ou
        // manual). getAcoesComNome() já traz tpa_nome via join com
        // oco_tipo_acao (sem isso a coluna "Ação" renderiza em branco).
        // PASSO 0 — Seed idempotente: cobre a primeira abertura manual de
        // uma ocorrência sem linhas ainda (ex.: criada antes desta feature
        // existir), igual ao que store() já faz na gravação.
        $this->seedAcoes((int) $dados->oco_id, (int) $dados->sut_id);

        $entity = new EntOcoTratativa($dados, true);
        $acoes  = $this->ocorrenciaAcao->getAcoesComNome($id);
        $acoesResultado = [];

        foreach ($acoes as $pos => $acao) {
            $acao                  = (object) $acao;
            $acao->somente_leitura = ($acao->oac_executada === 'S');
            $camposAcao            = $entity->defCamposAcao($acao, $pos);
            $acoesResultado[]      = $camposAcao;
        }

        $secao[1]  = 'Ações';
        // RN03.15 — permite adicionar ação extra apenas na tratativa (edição)
        $campos[1][] = view(
            'partials/pw_acoes_ocorrencia',
            [
                'acoes'            => $acoesResultado,
                'oco_id'           => $id,
                'permiteAcaoExtra' => true,
            ]
        );
        // CONFIG VIEW
        $this->data['desc_edicao'] = ' Ocorrência. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' - ' . $etiqueta;

        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = "store";
        $this->data['desc_metodo'] = '';
        $this->data['script']      = '<script>jQuery("#form1").attr("data-alter", true);</script>';
        // RN03.18.2 — carrega o script com confirmaAcaoTratativa() (MSG 6)
        $this->data['scripts']     = 'my_ocorrencia';
        // debug($this->data, true);

        echo view('vw_edicao', $this->data);
    }

    /**
     * Gravação
     * store
     *
     * @param mixed $id
     * @return void
     */
    public function store(?array $data = null)
    {
        $automatica = false;
        // Se veio via chamada direta
        if ($data !== null) {
            $automatica = true;
            $postado    = $data;
        } else {
            // Se veio via requisição HTTP
            $postado = $this->request->getPost();
        }

        // PASSO 0 — Seed idempotente: se a ocorrência ainda não tem nenhuma
        // linha em oco_ocorrencia_acao, copia o catálogo de ações do
        // subtipo (oco_subt_ocorrencia_acao) como pendentes. Cobre tanto a
        // primeira chamada automática (criação) quanto uma eventual
        // primeira abertura manual de uma ocorrência antiga sem linhas.
        $this->seedAcoes((int) $postado['oco_id'], (int) $postado['sut_id']);

        // PASSO 1 — Seleciona as ações a processar nesta rodada.
        if ($automatica) {
            $acoesExecutar = $this->montaAcoesAutomaticas((int) $postado['oco_id'], $postado);
        } else {
            $acoesExecutar = $this->montaAcoesManuais($postado);
        }

        // debug($postado, true);
        // debug($acoesExecutar, true);

        // PASSO 2 — Execução (mesmo switch por tpa_tipo já existente) e
        // persistência POR AÇÃO (B3), imediatamente após cada ação ser
        // processada, FORA de qualquer transação agregada: cada ação grava
        // seu próprio resultado (sucesso ou falha) na hora, e não fica
        // pendente de rollback por causa de outra ação do mesmo lote. Isso
        // garante idempotência em retry — uma ação com sucesso real (ex.:
        // gerarMovimentacao() já gerou movimentação de estoque) fica marcada
        // oac_executada='S' e não é reprocessada; só as que falharam (que
        // continuam 'N') voltam a ser tentadas.
        $erroExecucao = null;
        foreach ($acoesExecutar as $valor) {
            $valor->erro = false;
            $valor->msg  = null;

            switch ((int) $valor->tpa_tipo) {
                case 1:
                    // RN03.19 — "Justificar" apenas resolve o texto (ver
                    // resolveJustificativa()); a gravação em
                    // oco_ocorrencia.oco_justi ocorre junto com o update
                    // final da ocorrência, igual ao stt_id em case 4.
                    break;
                case 2:
                    // lógica para Abrir Tela
                    break;
                case 3:
                    // lógica para Gerar Movimentação
                    $retAcao     = $this->gerarMovimentacao($postado, $valor);
                    $valor->erro = $retAcao['erro'] ?? false;
                    $valor->msg  = $retAcao['msg'] ?? null;
                    break;
                case 4:
                    // RN03.18 — "Alterar Status" altera o status do PRODUTO
                    // (pro_sap_produto.stt_id, pelo pro_id da ocorrência), não
                    // o status da ocorrência. O stt_id alvo é resolvido em
                    // resolveStatusProduto() e gravado após o loop.
                    break;
                case 5:
                    // RN02.3 de T42 — "Notificação do Fornecedor": cria
                    // automaticamente um registro Pendente em
                    // oco_notif_desvio (Fornecedores > Desvio de Qualidade).
                    // Ver docs/desenvolvimento/fornecedores-t42-t43-dev.md,
                    // decisão 3.2.
                    $retAcao     = $this->gerarNotificacaoDesvio($postado);
                    $valor->erro = $retAcao['erro'] ?? false;
                    $valor->msg  = $retAcao['msg'] ?? null;
                    break;
                default:
                    // opcional: tratar valores inesperados
                    break;
            }

            // Persistência por ação (B3) — UPDATE se a linha já existia
            // (seed ou rodada anterior), INSERT se for ad-hoc (adicionada
            // via botão "+", sem oac_id). Sucesso: marca oac_executada='S'
            // (não reprocessa em retry futuro). Falha: mantém
            // oac_executada='N' (continua elegível para nova tentativa).
            $dadosLinha = [
                'oco_id'    => $postado['oco_id'],
                'tpa_id'    => $valor->tpa_id,
                'tpa_tipo'  => $valor->tpa_tipo,
                'tmo_id'    => $valor->tmo_id,
                'stt_id'    => $valor->stt_id,
                'tel_id'    => $valor->tel_id,
                'oco_justi' => $valor->oco_justi,
                'oac_erro'  => (int) $valor->erro,
                'oac_msg'   => $valor->msg,
            ];

            if ($valor->erro) {
                $dadosLinha['oac_executada'] = 'N';
            } else {
                $dadosLinha['oac_executada']    = 'S';
                $dadosLinha['oac_executado_em'] = date('Y-m-d H:i:s');
                $dadosLinha['usu_executou']     = $automatica ? null : session()->get('usu_id');
                $dadosLinha['oac_automatica']   = $automatica ? 1 : 0;
            }

            if (!empty($valor->oac_id)) {
                $this->ocorrenciaAcao->update($valor->oac_id, $dadosLinha);
            } else {
                // Ação ad-hoc (botão "+") — nunca é automática, não
                // faz parte do catálogo do subtipo.
                $dadosLinha['oac_auto']   = 'N';
                $dadosLinha['oac_criado'] = date('Y-m-d H:i:s');
                $this->ocorrenciaAcao->insert($dadosLinha);
            }

            if ($valor->erro && $erroExecucao === null) {
                $erroExecucao = $valor->msg;
            }
        }

        $retTrat = [];
        if ($erroExecucao === null) {
            // B4 — grupo dbOcorrencia (onde estão oco_ocorrencia/
            // pro_sap_produto), não mais o grupo default. Cobre só o
            // resumo/status final (Passos 4/5) — a persistência por ação do
            // Passo 2 já foi commitada individualmente acima.
            $db = \Config\Database::connect('dbOcorrencia');
            $db->transBegin();
            try {
                // PASSO 4 — Resumo (compatibilidade): primeiro valor não
                // vazio, entre as ações desta rodada, grava em
                // oco_ocorrencia.oco_justi / pro_sap_produto.stt_id.
                $justificativa = $this->resolveJustificativa($acoesExecutar);
                $sttIdProduto  = $this->resolveStatusProduto($acoesExecutar);

                // PASSO 5 — Status final da ocorrência, conforme execução
                // real (não mais previsão fixa 29/30).
                $sttIdFinal = $this->resolveStatusOcorrencia((int) $postado['oco_id'], $automatica);

                $sql_save = [
                    'stt_id'       => $sttIdFinal,
                    'usu_fina'     => session()->get('usu_id'),
                    'oco_data_fim' => date('Y-m-d H:i:s'),
                ];
                if ($justificativa !== null) {
                    $sql_save['oco_justi'] = $justificativa;
                }

                $this->ocorrencia->update($postado['oco_id'], $sql_save);

                if ($sttIdProduto !== null) {
                    (new ProdutProdutoModel())->update($postado['pro_id'], ['stt_id' => $sttIdProduto]);
                }

                $db->transCommit();
                $retTrat['erro'] = false;
                $retTrat['msg']  = 'Ocorrência tratada com sucesso!';
                session()->setFlashdata('msg', $retTrat['msg']);
                $retTrat['url'] = site_url($this->data['controler']);
            } catch (\Exception $e) {
                $db->transRollback();
                $retTrat['erro'] = true;
                $retTrat['msg']  = $e->getMessage();
            }
        } else {
            $retTrat['erro'] = true;
            $retTrat['msg']  = $erroExecucao;
        }

        // Chamado via HTTP (tela de tratativa) precisa de uma Response de
        // verdade — um array puro retornado aqui é descartado pelo
        // CodeIgniter (CodeIgniter::gatherOutput() só usa o retorno quando é
        // string ou ResponseInterface), resultando em corpo de resposta
        // vazio. Chamada automática (OcorrenciaService::processAfterSave())
        // ignora o retorno, então não há problema em sempre devolver JSON.
        return $this->response->setJSON($retTrat);
    }

    /**
     * PASSO 0 do motor de execução — semeia oco_ocorrencia_acao com o
     * catálogo de ações do subtipo (oco_subt_ocorrencia_acao), como
     * pendentes, se a ocorrência ainda não tiver nenhuma linha. Idempotente
     * (checa existência antes de inserir). Se o catálogo vier vazio, não
     * insere nada (sem erro) — mesmo comportamento hoje coberto por
     * getStatusInicial() para subtipo sem ações.
     *
     * B4 — checagem + insertBatch() rodam dentro de uma transação própria
     * (grupo dbOcorrencia), fechando a janela de corrida entre duas
     * chamadas concorrentes de store() para o mesmo oco_id (que antes podia
     * duplicar o catálogo semeado).
     */
    private function seedAcoes(int $oco_id, int $sut_id): void
    {
        $db = \Config\Database::connect('dbOcorrencia');
        $db->transBegin();

        try {
            $jaExiste = $this->ocorrenciaAcao->where('oco_id', $oco_id)->countAllResults();
            if ($jaExiste > 0) {
                $db->transCommit();
                return;
            }

            $catalogo = $this->subtocorrencia->getTOAcao($sut_id);
            if (empty($catalogo)) {
                $db->transCommit();
                return;
            }

            $tipos = [];
            foreach ($this->tipoacao->getTipoAcao(array_column($catalogo, 'tpa_id')) as $tp) {
                $tipos[$tp->tpa_id] = $tp->tpa_tipo;
            }

            $agora  = date('Y-m-d H:i:s');
            $linhas = [];
            foreach ($catalogo as $acao) {
                $linhas[] = [
                    'oco_id'        => $oco_id,
                    'tpa_id'        => $acao->tpa_id,
                    'tpa_tipo'      => $tipos[$acao->tpa_id] ?? 0,
                    'oac_auto'      => $acao->sta_fina ?? 'N',
                    'tmo_id'        => $acao->tmo_id ?? null,
                    'stt_id'        => $acao->stt_id ?? null,
                    'tel_id'        => $acao->tel_id ?? null,
                    'oco_justi'     => null,
                    'oac_executada' => 'N',
                    'oac_criado'    => $agora,
                ];
            }

            $this->ocorrenciaAcao->insertBatch($linhas);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * PASSO 1 (execução automática) — todas as linhas de
     * oco_ocorrencia_acao com oac_auto='S' e ainda não executadas.
     */
    private function montaAcoesAutomaticas(int $oco_id, array $postado): array
    {
        $linhas = $this->ocorrenciaAcao
            ->where('oco_id', $oco_id)
            ->where('oac_auto', 'S')
            ->where('oac_executada', 'N')
            ->findAll();

        $acoesExecutar = [];
        foreach ($linhas as $linha) {
            $acoesExecutar[] = (object) [
                'oac_id'    => $linha['oac_id'],
                'tpa_id'    => $linha['tpa_id'],
                'tpa_tipo'  => $linha['tpa_tipo'],
                'tmo_id'    => $linha['tmo_id'],
                'stt_id'    => $linha['stt_id'],
                'tel_id'    => $linha['tel_id'],
                'oco_justi' => $linha['oco_justi'] ?? ($postado['oco_justi'] ?? null),
            ];
        }

        return $acoesExecutar;
    }

    /**
     * PASSO 1 (tratativa manual) — monta as ações desta rodada a partir do
     * POST: uma linha só entra na rodada se `executar[$pos]` veio marcado.
     * Para linhas com `oac_id` (pendentes vindas do seed/rodada anterior),
     * os dados AUTORITATIVOS de tpa_id/tpa_tipo vêm do próprio banco (não
     * confia no POST para isso — só usa o POST para os campos realmente
     * editáveis: oco_justi/tmo_id/stt_id/tel_id). Para linhas sem oac_id
     * (ad-hoc, adicionadas via botão "+"), tpa_id vem do select livre do
     * POST e o tpa_tipo é resolvido via catálogo (oco_tipo_acao).
     */
    private function montaAcoesManuais(array $postado): array
    {
        $executar = $postado['executar']  ?? [];
        $oacIds   = $postado['oac_id']    ?? [];
        $tpaIds   = $postado['tpa_id']    ?? [];
        $justis   = $postado['oco_justi'] ?? [];
        $tmoIds   = $postado['tmo_id']    ?? [];
        $sttIds   = $postado['stt_id']    ?? [];
        $telIds   = $postado['tel_id']    ?? [];

        $acoesExecutar = [];
        $tpaIdsUsados  = [];

        foreach ($executar as $pos => $marcado) {
            // Campo agora é cr2opcoes (Sim/Não) — sempre vem preenchido no
            // POST (diferente do checkbox antigo, que só chegava quando
            // marcado); só entra na rodada quem valer 'S'.
            if ($marcado !== 'S') {
                continue;
            }

            $oacId = $oacIds[$pos] ?? null;

            if (!empty($oacId)) {
                $linha = $this->ocorrenciaAcao->find($oacId);
                if (!$linha || $linha['oac_executada'] === 'S') {
                    continue; // linha inexistente ou já executada — ignora
                }
                $tpaId   = $linha['tpa_id'];
                $tpaTipo = $linha['tpa_tipo'];
            } else {
                $tpaId = $tpaIds[$pos] ?? null;
                if (empty($tpaId)) {
                    continue;
                }
                $tipoInfo = $this->tipoacao->getTipoAcao((int) $tpaId);
                $tpaTipo  = $tipoInfo[0]->tpa_tipo ?? null;
                if ($tpaTipo === null) {
                    continue;
                }
            }

            // Defesa contra duplicidade de tpa_id na mesma rodada — com o
            // seed, duplicar tpa_id não deveria mais ser estruturalmente
            // possível, mas mantém a checagem por ser barata.
            if (isset($tpaIdsUsados[$tpaId])) {
                continue;
            }
            $tpaIdsUsados[$tpaId] = true;

            $acoesExecutar[] = (object) [
                'oac_id'    => $oacId ?: null,
                'tpa_id'    => $tpaId,
                'tpa_tipo'  => $tpaTipo,
                'tmo_id'    => $tmoIds[$pos] ?? null,
                'stt_id'    => $sttIds[$pos] ?? null,
                'tel_id'    => $telIds[$pos] ?? null,
                'oco_justi' => $justis[$pos] ?? null,
            ];
        }

        return $acoesExecutar;
    }

    /**
     * PASSO 5 — resolve o stt_id final da ocorrência conforme a execução
     * real das ações (não mais uma previsão fixa 29/30):
     *  - sem ações (ou nenhuma pendente sobrando) -> Finalizada (29 auto /
     *    30 manual);
     *  - nenhuma ação executada ainda -> Pendente (28);
     *  - mistura (ao menos 1 executada, ao menos 1 pendente) ->
     *    Parcialmente Tratada.
     */
    private function resolveStatusOcorrencia(int $oco_id, bool $automatica): int
    {
        $total      = $this->ocorrenciaAcao->where('oco_id', $oco_id)->countAllResults();
        $executadas = $this->ocorrenciaAcao->where('oco_id', $oco_id)->where('oac_executada', 'S')->countAllResults();
        $pendentes  = $total - $executadas;

        if ($total === 0 || $pendentes === 0) {
            return $automatica ? 29 : 30;
        }
        if ($executadas === 0) {
            return 28;
        }

        return $this->getStatusParcialId();
    }

    /**
     * Resolve dinamicamente o stt_id do status "Parcialmente Tratada"
     * (inserido pela migration 2026-08-10-000001_OcoOcorrenciaAcao), sem
     * hardcode de id numérico. Cacheado em memória por request.
     */
    private function getStatusParcialId(): int
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $status = \Config\Database::connect('default')
            ->table('cfg_status')
            ->where('stt_nome', 'Parcialmente Tratada')
            ->get()->getRow();

        // Fallback defensivo (nunca deveria faltar após a migration rodar)
        // — cai para Pendente (28) em vez de quebrar a tratativa.
        $cache = $status ? (int) $status->stt_id : 28;

        return $cache;
    }

    /**
     * RN03.18 — Resolve o stt_id do PRODUTO (pro_sap_produto.stt_id) ao
     * concluir a tratativa: busca, entre as ações desta rodada, alguma do
     * tipo "Alterar Status" (tpa_tipo=4), e usa o stt_id já resolvido na
     * própria ação (vindo do catálogo, no seed, ou editado pelo usuário na
     * tratativa — ver montaAcoesManuais()/montaAcoesAutomaticas()). Retorna
     * null se nenhuma ação "Alterar Status" foi processada nesta rodada
     * (produto não é alterado).
     */
    private function resolveStatusProduto(array $acoesExecutar): ?int
    {
        foreach ($acoesExecutar as $acao) {
            if ((int) $acao->tpa_tipo !== 4) {
                continue;
            }

            if (!empty($acao->stt_id)) {
                return (int) $acao->stt_id;
            }
        }

        return null;
    }

    /**
     * RN03.19 — Resolve o texto de justificativa a gravar em
     * oco_ocorrencia.oco_justi: busca, entre as ações desta rodada, alguma
     * do tipo "Justificar" (tpa_tipo=1). Retorna null se nenhuma ação
     * "Justificar" foi processada nesta rodada ou o texto veio vazio.
     */
    private function resolveJustificativa(array $acoesExecutar): ?string
    {
        foreach ($acoesExecutar as $acao) {
            if ((int) $acao->tpa_tipo !== 1) {
                continue;
            }

            if (!empty($acao->oco_justi)) {
                return $acao->oco_justi;
            }
        }

        return null;
    }

    /**
     * Gera a movimentação de estoque para uma ação do tipo "Gerar
     * Movimentação" (tpa_tipo=3). O tmo_id já vem resolvido na própria
     * ação — do catálogo do subtipo (seed/execução automática) ou editado
     * pelo usuário na tratativa manual (montaAcoesManuais()).
     */
    private function gerarMovimentacao($postado, $acao)
    {
        $retMov         = [];
        $retMov['erro'] = false;

        // tmo_id já vem resolvido na própria ação (catálogo, no seed, ou
        // editado pelo usuário na tratativa — ver
        // montaAcoesManuais()/montaAcoesAutomaticas()).
        $tmoId = $acao->tmo_id ?? null;

        if ($tmoId) {
            $movsOco[] = [
                'id'           => $tmoId,
                'qt'           => $postado['oco_qtd'],
                'msg'          => $postado['oco_descricao'],
                'pro_id'       => $postado['pro_id'],
                'rep_id'       => null,
                'reserva'      => null,
                'lot_lote'     => $postado['lot_lote'],
                'lot_validade' => $postado['lot_validade'],
            ];
            $movim = geraMovimentoRequisicoes($movsOco, $this->data['controler']);
            if ($movim['status'] == 'Erro') {
                $retMov['erro'] = true;
                $retMov['msg']  = $movim['mensagem'];
            }
        }
        return $retMov;
    }

    /**
     * RN02.3 (T42) — ação "Notificação do Fornecedor" (tpa_tipo = 5): cria
     * automaticamente um registro Pendente em oco_notif_desvio para o
     * oco_id da ocorrência sendo tratada/finalizada.
     *
     * Idempotente: não duplica se já existir um oco_notif_desvio para o
     * mesmo oco_id (ex.: reprocessamento/reenvio do mesmo submit).
     */
    private function gerarNotificacaoDesvio($postado)
    {
        $ret = ['erro' => false];

        $modelNotif = new FornecNotifDesvioModel();

        $jaExiste = \Config\Database::connect('dbOcorrencia')
            ->table('oco_notif_desvio')
            ->where('oco_id', $postado['oco_id'])
            ->countAllResults();

        if ($jaExiste > 0) {
            return $ret; // já notificado — não duplica
        }

        $sttPendente = $modelNotif->getStatusPendenteId();
        if (!$sttPendente) {
            $ret['erro'] = true;
            $ret['msg']  = 'Status "Pendente" de Desvio de Qualidade não configurado (cfg_status)';
            return $ret;
        }

        // Insert via Model (não CommonModel::insertReg()) — preserva os
        // hooks de auditoria (afterInsert => depoisInsert, log gravado com
        // a PK real ndv_id) e os timestamps (ndv_criado). skipValidation()
        // porque ndv_local/ndv_descreva legitimamente ainda não existem
        // neste momento — só serão preenchidos depois pelo usuário em T42
        // (RN03.7/RN03.14).
        $modelNotif->skipValidation(true)->insert([
            'oco_id'    => $postado['oco_id'],
            'stt_id'    => $sttPendente,
            'usu_criou' => session()->get('usu_id'),
        ]);

        return $ret;
    }
}
