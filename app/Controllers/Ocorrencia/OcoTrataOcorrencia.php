<?php

namespace App\Controllers\Ocorrencia;

use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Ocorre\OcorreTrataOcorrenciaModel;
use App\Models\Produt\ProdutProdutoModel;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $permissao;
    public $ocorrencia;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        // debug($this->data, true);
        // Inicialização dos models auxiliares
        $this->ocorrencia = new OcorreOcorrenciaModel();

        // debug($this->data['erromsg'], true);
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
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
        $campos   = montaColunasCampos($this->data, 'oco_id');
        $dados    = $this->ocorrencia->getListaPendente([28]);

        // Caso não existam registros
        if (!$dados) {
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
            $usuLog = $logGeracao[$nov->oco_id]['usua_alterou'] ?? '';
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
                $url_finalizar = $base_url . '/finalizar/' . $nov->oco_id;
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
            'data' => montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1])
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
        if (!$dados) {
            throw new \Exception('Ocorrência não encontrada');
        }

        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? null;
        // Instancia a entity
        // debug($dados, true);
        $entoco    = new EntOcoOcorrencia((array) $dados, true);
        $fields = $entoco->campos;
        $contOcorr = new OcoOcorrencia();
        $secao[0] = 'Dados Gerais';
        $campos = $contOcorr->showCabecalho($dados);

        $etiqueta = fmtEtiquetaCor($dados->stt_cor, $dados->stt_nome, 1);

        // BLOCO TELAS APLICAVEIS
        $entity   = new EntOcoModOcorrencia((array) $dados);
        $sutModel = new OcorreModOcorrenciaModel();
        $telas = $sutModel->getTOTelasAplicaveis($dados->sut_id);
        // debug($telas, true);

        if (!empty($telas)) {

            $telasResultado = [];
            $total = count($telas);

            for ($c = 0; $c < $total; $c++) {
                // debug($telas[$c]);
                $fields = $entity->defCamposTelasAplicaveis(
                    $telas[$c],
                    $c,
                    $total,
                    true
                );
                $campos[1][$c][] = $fields['mod_id'];
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['tof_campo'];
            }
            $telasResultado = $campos[1];
            $campos[0][] = view(
                'partials/pw_telas_aplicaveis_ocorrencia',
                [
                    'telas'  => $telasResultado,
                    'oco_id' => $id
                ]
            );
        }

        // BLOCO DAS AÇÕES 
        $entity = new EntOcoTratativa($dados, true);
        $acoes = $this->ocorrencia->getAcoesFinalizar($id);
        if (!empty($acoes)) {

            $acoesResultado = [];

            foreach ($acoes as $acao) {
                $acao->somente_leitura = false;
                $camposAcao = $entity->defCamposAcao($acao);
                $acoesResultado[] = $camposAcao;
            }

            $campos[0][] = view(
                'partials/pw_acoes_ocorrencia',
                [
                    'acoes'  => $acoesResultado,
                    'oco_id' => $id
                ]
            );
        }

        // CONFIG VIEW
        $this->data['desc_edicao'] = ' Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' - ' . $etiqueta;

        $this->data['secoes']       = $secao;
        $this->data['campos']       = $campos;
        $this->data['destino']      = "store";
        $this->data['desc_metodo']  = '';
        $this->data['script']       = '<script>jQuery("#form1").attr("data-alter", true);</script>';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Gravação
     * store
     *
     * @param mixed $id 
     * @return void
     */
    public function store()
    {
        // debug('ENTROU NO STORE');
        $postado = $this->request->getPost();
        $acaoSelecionada = $postado['tpa_id'] ?? [];
        // debug($postado, true);
        // debug($this->request->getPost(), true);

        if (!is_array($acaoSelecionada)) {
            $acaoSelecionada = [$acaoSelecionada];
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {

            $ocoId = $this->request->getPost('oco_id');
            $oco = (new OcorreTrataOcorrenciaModel())->getTrataOcorrencia($ocoId);

            if (!$oco) {
                throw new \Exception('Ocorrência não encontrada');
            }

            // confirmação obrigatória
            $movs    = [];
            $novoStt = null;

            foreach ($acaoSelecionada as $tpaId) {

                $tpaId = (int)$tpaId;
                $acao = (new OcorreModOcorrenciaModel())->getAcaoPorId($tpaId, $oco->sut_id);

                if (!$acao) {
                    continue;
                }

                // // MOVIMENTAÇÃO
                // if ((int)$acao->tpa_id === 3) {

                //     // CONFIRMAÇÃO DE MOVIMENTAÇÃO, MSG 6
                //     if (empty($postado['tmo_id']) || (int)($postado['oco_qtd'] ?? 0) <= 0) {
                //         return $this->response->setJSON([
                //             'erro' => true,
                //             'msg'  => 6
                //         ]);
                //     }
                // }

                // ALTERAR STATUS
                // debug($postado['stt_id'] ?? 'NÃO VEIO', true);
                // debug($acao->tpa_id, true);
                if ((int)$acao->tpa_id === 4) {
                    $novoStt = (int)$acao->stt_id;

                    // PRODUTO
                    $produtoModel = new ProdutProdutoModel();
                    if (!$produtoModel->update($oco->pro_id, [
                        'stt_id' => $novoStt
                    ])) {
                        throw new \Exception('Erro ao atualizar status do produto');
                    }

                    // OCORRÊNCIA = SEMPRE FINALIZADA
                    $this->ocorrencia->update($oco->oco_id, [
                        'stt_id'       => 30,
                        'usu_fina'     => session()->get('usu_id'),
                        'oco_data_fim' => date('Y-m-d H:i:s')
                    ]);

                    $novoStt = null;
                }
            }

            // Gera movimentos se existirem
            if (!empty($movs)) {
                cache()->clean();
                $movim = geraMovimentoSOAP($movs, $postado, $this->data);

                if ($movim['status'] == 'Erro') {
                    $ret['erro'] = true;
                    $ret['msg']  = $movim['mensagem'];
                }
            }

            // FINALIZA 
            $postado['stt_id']       = $novoStt ?? 30;
            $postado['usu_fina']     = session()->get('usu_id');
            $postado['oco_data_fim'] = date('Y-m-d H:i:s');

            unset(
                $postado['usu_nome'],
                $postado['sut_id'],
                $postado['tpa_id'],
                $postado['tmo_id'],
                $postado['tel_id']
            );

            $entity = new EntOcoOcorrencia($postado);

            if (!$this->ocorrencia->save($entity)) {
                throw new \Exception(implode('<br>', $this->ocorrencia->errors()));
            }

            $db->transCommit();
            session()->setFlashdata('msg', 'Ocorrência finalizada com sucesso!');

            // debug(session()->getFlashdata('msg'));
            return $this->response->setJSON([
                'erro' => false,
                'url'  => site_url($this->data['controler'])
            ]);
        } catch (\Exception $e) {
            $db->transRollback();

            return $this->response->setJSON([
                'erro' => true,
                'msg'  => $e->getMessage()
            ]);
        }
    }
}
