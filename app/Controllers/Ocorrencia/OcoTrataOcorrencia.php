<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Controllers\Ocorrencia\OcoOcorrencia;

use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Models\Fornec\FornecNotifDesvioModel;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $permissao;
    public $ocorrencia;
    public $tipoacao;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        // Inicialização dos models auxiliares
        $this->ocorrencia     = new OcorreOcorrenciaModel();
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
        $dados  = $this->ocorrencia->getListaPendente([28]);

        // Caso não existam registros
        if (! $dados) {
            return $this->response->setJSON(['data' => []]);
        }

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

            // Define usuário de finalização se estiver finalizada
            if ((int) $nov->stt_id === 30) {
                $nov->usu_fina = $usuLog;
            } else {
                $nov->usu_fina = '';
            }

            $nov->acao_person = [];

            // Botão de finalizar se estiver pendente
            if (trim($nov->stt_nome ?? '') === 'Pendente') {
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
            }
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
     * Ação extra (avulsa), adicionada manualmente pelo usuário na aba Ações
     * da tratativa (T12), além das ações de origem do subtipo. Usa o mesmo
     * EntOcoSubtOcorrencia::defCamposAcao() da aba Ações do OcoSubtOcorrencia
     * — mesma "coisa", mesmos campos — só que aqui totalmente editável
     * (tpa_id livre, sem valor pré-configurado) e sempre excluível, já que
     * ainda não foi executada.
     *
     * Contrato compatível com o helper genérico `addCampo()` (my_fields.js):
     * retorna um array plano de HTML, sendo os 2 últimos elementos
     * destacados para a coluna de ações (adicionar + excluir).
     *
     * @param mixed $oco_id
     * @param mixed $ind
     * @return void
     */
    public function addCampoAcaoExtra($oco_id, $ind)
    {
        $entity = new EntOcoSubtOcorrencia();
        $acao   = (object) ['tpa_id' => null, 'sta_fina' => 'N'];
        $ind    = (int) $ind;

        // A linha recém-adicionada é sempre a "última" no momento em que é
        // criada, então recebe o botão de adicionar (ver montaLinhaAcao()).
        return $this->response->setJSON(
            $this->montaLinhaAcao($entity, $acao, $ind, $ind + 1, false, false, false, $oco_id)
        );
    }

    /**
     * Monta uma linha da aba Ações (finalizar) a partir de
     * EntOcoSubtOcorrencia::defCamposAcao() — mesmos campos/estrutura da aba
     * Ações do OcoSubtOcorrencia. "Finalização Automática" não é exibida
     * aqui: se a ação já veio marcada como automática do subtipo, ela já
     * foi/será executada e simplesmente não pode ser excluída
     * ($bloqueiaExclusaoAutomatica); se foi incluída agora na tratativa, será
     * executada ao salvar — não há o que marcar.
     *
     * O botão de adicionar (bt_addtp, já criado pela própria entity) é
     * gerado em toda linha, igual à aba Ações do OcoTipoOcorrencia — quem
     * deixa visível só o da última linha é acerta_botoes_rep('acoes'),
     * chamada na abertura da tela (ver finalizar()).
     *
     * Retorna um array de 6 posições: [tpa_id, div-movimentação, div-tela,
     * div-status, adicionar, excluir] — as 2 últimas compõem a coluna de
     * ações no layout "tabela" (ver vw_edicao.php / addCampo() em
     * my_fields.js).
     */
    private function montaLinhaAcao(
        EntOcoSubtOcorrencia $entity,
        object $acao,
        int $pos,
        int $total,
        bool $somenteLeitura,
        bool $tpaIdLeitura,
        bool $bloqueiaExclusaoAutomatica,
        $oco_id
    ): array {
        $urlAddCampo = base_url("OcoTrataOcorrencia/addCampoAcaoExtra/{$oco_id}/");
        $fields      = $entity->defCamposAcao($acao, $pos, $total, 'tratativa', $somenteLeitura, $tpaIdLeitura, $urlAddCampo, $bloqueiaExclusaoAutomatica);

        $clsMovi = $clsTela = $clsStat = 'd-none';
        switch ((int) ($acao->tpa_id ?? 0)) {
            case 3: // Gerar Movimentação
                $clsMovi = '';
                break;
            case 2: // Abrir Tela
                $clsTela = '';
                break;
            case 4: // Alterar Status
                $clsStat = '';
                break;
        }

        return [
            $fields['tpa_id'],
            "<div id='divmovi[$pos]' class='{$clsMovi} row col-5 float-start'>{$fields['tmo_id']}</div>",
            "<div id='divtela[$pos]' class='{$clsTela} row col-5 float-start'>{$fields['mod_id']}{$fields['tel_id']}</div>",
            "<div id='divstat[$pos]' class='{$clsStat} row col-5 float-start'>{$fields['stt_id']}</div>",
            $fields['bt_addtp'],
            $fields['bt_deltp'],
        ];
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
        $telas    = $sutModel->getTOTelasAplicaveis($dados->sut_id);
        // debug($telas, true);

        if (! empty($telas)) {

            $telasResultado = [];
            $total          = count($telas);

            for ($c = 0; $c < $total; $c++) {
                // debug($telas[$c]);
                $fields = $entity->defCamposTelasAplicaveis(
                    $telas[$c],
                    $c,
                    true
                );
                $campos[1][$c][] = $fields['mod_id'];
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['tof_campo'];
            }
            $telasResultado = $campos[1];
            $campos[0][]    = view(
                'partials/pw_telas_aplicaveis_ocorrencia',
                [
                    'telas'  => $telasResultado,
                    'oco_id' => $id,
                ]
            );
        }

        // BLOCO DAS AÇÕES — mesma aba/campos do OcoSubtOcorrencia
        // (EntOcoSubtOcorrencia::defCamposAcao), pois é a mesma coisa: uma
        // ação com seus dados de execução. Ações já configuradas no subtipo
        // ficam somente leitura (tpa_id/tmo_id/mod_id/tel_id/stt_id) — só
        // passam a ser editáveis quando adicionadas manualmente aqui na
        // tratativa (addCampoAcaoExtra()). Ações de finalização automática
        // (sta_fina='S') já configuradas não podem ser excluídas.
        $entity = new EntOcoSubtOcorrencia();
        $acoes  = $this->ocorrencia->getAcoesFinalizar($id);
        $total  = count($acoes);
        $acoesResultado = [];

        foreach ($acoes as $c => $acao) {
            $acoesResultado[] = $this->montaLinhaAcao($entity, (object) $acao, $c, $total, true, true, true, $id);
        }

        $campos[0][] = view(
            'partials/pw_acoes_ocorrencia',
            [
                'acoes'  => $acoesResultado,
                'oco_id' => $id,
            ]
        );

        // CONFIG VIEW
        $this->data['desc_edicao'] = ' Ocorrência. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' - ' . $etiqueta;

        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = "store";
        $this->data['desc_metodo'] = '';
        // Deixa visível só o botão de adicionar da última linha da aba Ações
        // (mesmo padrão de OcoTipoOcorrencia::add()/edit()).
        $this->data['script']      = '<script>
            jQuery("#form1").attr("data-alter", true);
            acerta_botoes_rep("acoes");
        </script>';

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

        // Normaliza tpa_id para array — quando store() é chamado
        // diretamente (via OcorrenciaService::processAfterSave -> $automatica),
        // tpa_id vem como escalar (int|null), não array.
        if (isset($postado['tpa_id']) && !is_array($postado['tpa_id'])) {
            $postado['tpa_id'] = [$postado['tpa_id']];
        }

        // Monta o catálogo (tpa_tipo) de todos os tpa_id envolvidos.
        $todosTpaIds = array_filter($postado['tpa_id'] ?? []);

        $catalogo = [];
        foreach ($this->tipoacao->getTipoAcao($todosTpaIds) as $tp) {
            $catalogo[$tp->tpa_id] = $tp;
        }

        // Monta a lista de ações a executar. Cada linha (já configurada no
        // subtipo ou adicionada manualmente na tela — mesmos campos, ver
        // EntOcoSubtOcorrencia::defCamposAcao()) carrega seus próprios
        // tmo_id/stt_id/tel_id, então não há mais distinção entre ação de
        // origem e ação extra.
        //
        // Bug encontrado pelo bytest: nada impedia o usuário de escolher, em
        // uma ação adicionada manualmente, o MESMO tpa_id que já é ação de
        // origem do subtipo — ou o mesmo tpa_id em duas linhas diferentes —
        // gerando duas entradas para o mesmo tpa_id e, para tpa_tipo=3
        // (Gerar Movimentação), duas chamadas a gerarMovimentacao() no mesmo
        // submit (movimentação de estoque duplicada). Correção: deduplica
        // por tpa_id, mantendo a primeira ocorrência.
        $acoesExecutar = [];
        $tpaIdsUsados  = [];

        if ($automatica) {
            // Chamada direta (via OcorrenciaService::processAfterSave, quando
            // sut_fina='S') — $postado não traz os campos posicionais do
            // formulário de tratativa (tmo_id_tpa[]/stt_id_tpa[]/tel_id_tpa[]),
            // então busca a configuração completa da ação direto no subtipo
            // (oco_subt_ocorrencia_acao), como já era feito antes.
            $subModel = new OcorreSubtOcorrenciaModel();
            foreach (($postado['tpa_id'] ?? []) as $tpa_id) {
                if (!$tpa_id || !isset($catalogo[$tpa_id]) || isset($tpaIdsUsados[$tpa_id])) {
                    continue;
                }
                $acaoSubt              = $subModel->getAcaoPorId($tpa_id, $postado['sut_id']);
                $tpaIdsUsados[$tpa_id] = true;
                $acoesExecutar[] = (object) [
                    'tpa_id'   => $tpa_id,
                    'tpa_tipo' => $catalogo[$tpa_id]->tpa_tipo,
                    'tmo_id'   => $acaoSubt->tmo_id ?? null,
                    'stt_id'   => $acaoSubt->stt_id ?? null,
                    'tel_id'   => $acaoSubt->tel_id ?? null,
                ];
            }
        } else {
            foreach (($postado['tpa_id'] ?? []) as $i => $tpa_id) {
                if (!$tpa_id || !isset($catalogo[$tpa_id]) || isset($tpaIdsUsados[$tpa_id])) {
                    continue;
                }
                $tpaIdsUsados[$tpa_id] = true;
                $acoesExecutar[] = (object) [
                    'tpa_id'   => $tpa_id,
                    'tpa_tipo' => $catalogo[$tpa_id]->tpa_tipo,
                    'tmo_id'   => $postado['tmo_id_tpa'][$i] ?? null,
                    'stt_id'   => $postado['stt_id_tpa'][$i] ?? null,
                    'tel_id'   => $postado['tel_id_tpa'][$i] ?? null,
                ];
            }
        }

        // debug($postado, true);
        // debug($acoesExecutar, true);

        $retTrat = [];
        $retAcao = ['erro' => false];
        foreach ($acoesExecutar as $valor) {
            switch ((int) $valor->tpa_tipo) {
                case 1:
                    // lógica para Justificar
                    break;
                case 2:
                    // lógica para Abrir Tela
                    break;
                case 3:
                    // lógica para Gerar Movimentação
                    $retAcao = $this->gerarMovimentacao($postado, $valor);
                    break;
                case 4:
                    // RN03.18 — Alterar Status apenas resolve o stt_id alvo
                    // (ver resolveStatusFinal()); NÃO gera movimentação.
                    $retAcao = ['erro' => false];
                    break;
                case 5:
                    // RN02.3 de T42 — "Notificação do Fornecedor": cria
                    // automaticamente um registro Pendente em
                    // oco_notif_desvio (Fornecedores > Desvio de Qualidade).
                    // Ver docs/desenvolvimento/fornecedores-t42-t43-dev.md,
                    // decisão 3.2.
                    $retAcao = $this->gerarNotificacaoDesvio($postado);
                    break;
                default:
                    // opcional: tratar valores inesperados
                    break;
            }
        }
        if (! $retAcao['erro']) {
            $db = \Config\Database::connect();
            $db->transBegin();
            try {
                // RN03.18.1 — status final: se houver ação "Alterar Status"
                // (tpa_tipo=4) entre as ações executadas, usa o stt_id da
                // própria linha; senão, Finalizada (30).
                $sttIdFinal = $automatica ? 29 : $this->resolveStatusFinal($acoesExecutar);

                $sql_save = [
                    'stt_id'       => $sttIdFinal,
                    'usu_fina'     => session()->get('usu_id'),
                    'oco_data_fim' => date('Y-m-d H:i:s'),
                ];
                // OCORRÊNCIA = SEMPRE FINALIZADA
                $this->ocorrencia->update($postado['oco_id'], $sql_save);

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
            $retTrat = $retAcao;
        }
        return $retTrat;
    }

    /**
     * RN03.18.1 — Resolve o stt_id final da ocorrência ao concluir a tratativa:
     * busca, entre as ações executadas, alguma do tipo "Alterar Status"
     * (tpa_tipo=4) e usa o stt_id da própria linha (POST); se nenhuma
     * resolver, usa 30 (Finalizada).
     */
    private function resolveStatusFinal(array $acoesExecutar): int
    {
        foreach ($acoesExecutar as $acao) {
            if ((int) $acao->tpa_tipo !== 4) {
                continue;
            }

            if (!empty($acao->stt_id)) {
                return (int) $acao->stt_id;
            }
        }

        return 30;
    }

    /**
     * Gera a movimentação de estoque para uma ação do tipo "Gerar
     * Movimentação" (tpa_tipo=3). O tmo_id vem da própria linha (POST).
     */
    private function gerarMovimentacao($postado, $acao)
    {
        $retMov         = [];
        $retMov['erro'] = false;
        $tmoId          = $acao->tmo_id ?? null;

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
