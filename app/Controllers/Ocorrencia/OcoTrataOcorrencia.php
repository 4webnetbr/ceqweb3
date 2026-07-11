<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Controllers\Ocorrencia\OcoOcorrencia;

use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $permissao;
    public $ocorrencia;
    public $subtocorrencia;
    public $tipoacao;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        // Inicialização dos models auxiliares
        $this->ocorrencia     = new OcorreOcorrenciaModel();
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
     * RN03.15 — retorna o HTML de uma nova linha de "ação extra" (avulsa),
     * para ser adicionada dinamicamente na aba Ações de finalizar().
     *
     * @param mixed $oco_id
     * @param mixed $ind
     * @return void
     */
    public function addCampoAcaoExtra($oco_id, $ind)
    {
        $entity = new EntOcoTratativa();
        $fields = $entity->defCamposAcaoExtra((int) $ind);

        $html  = "<tr><td><div class='row'>";
        $html .= $fields['tpa_id'];
        $html .= $fields['bt_del'];
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

        // BLOCO DAS AÇÕES
        $entity = new EntOcoTratativa($dados, true);
        $acoes  = $this->ocorrencia->getAcoesFinalizar($id);
        $acoesResultado = [];

        foreach ($acoes as $acao) {
            $acao->somente_leitura = false;
            $camposAcao            = $entity->defCamposAcao($acao);
            $acoesResultado[]      = $camposAcao;
        }

        // RN03.15 — permite adicionar ação extra apenas na tratativa (edição)
        $campos[0][] = view(
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
        // RN03.15 — mescla ações de origem (tpa_id[]) com ações extras
        // adicionadas manualmente pelo usuário (tpa_id_extra[]) para a
        // execução (Justificar/Gerar Movimentação/Alterar Status).
        $acaoSelecionada = array_filter(array_merge(
            $postado['tpa_id'] ?? [],
            $postado['tpa_id_extra'] ?? []
        ));
        // debug($postado, true);
        // debug($acaoSelecionada, true);

        $acoes = $this->tipoacao->getTipoAcao($acaoSelecionada);
        // debug($acoes, true);
        $retTrat = [];
        $retAcao = [];
        foreach ($acoes as $key => $valor) {
            // debug($key);
            // debug($valor);
            switch ($valor->tpa_tipo) {
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
                // (tpa_tipo=4) entre as ações executadas, usa o stt_id
                // configurado para ela no subtipo; senão, Finalizada (30).
                $sttIdFinal = $automatica ? 29 : $this->resolveStatusFinal($postado, $acoes);

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
     * (tpa_tipo=4) vinculada ao subtipo e usa o stt_id configurado para ela
     * em oco_subt_ocorrencia_acao; se não houver, usa 30 (Finalizada).
     */
    private function resolveStatusFinal(array $postado, $acoes): int
    {
        foreach ($acoes as $acao) {
            if ((int) $acao->tpa_tipo === 4) {
                $acaoSubt = $this->subtocorrencia->getAcaoPorId($acao->tpa_id, $postado['sut_id']);
                if ($acaoSubt && !empty($acaoSubt->stt_id)) {
                    return (int) $acaoSubt->stt_id;
                }
            }
        }

        return 30;
    }

    private function gerarMovimentacao($postado, $acao)
    {
        $retMov         = [];
        $retMov['erro'] = false;
        $acaosubt = $this->subtocorrencia->getTOAcao($postado['sut_id'], $acao->tpa_id)[0] ?? false;
        if ($acaosubt) {
            $movsOco[] = [
                'id'           => $acaosubt->tmo_id,
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
}
