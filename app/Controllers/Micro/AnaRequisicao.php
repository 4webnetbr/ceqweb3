<?php

namespace App\Controllers\Micro;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;
use App\Libraries\SoapSapiens;
use App\Models\ArquivoMonModel;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use App\Models\Microb\MicrobAnaliseModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;
use Config\Database;
use DateTime;

class AnaRequisicao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $analise;
    public $anarequisicao;
    public $produto;
    public $lote;
    public $tipomovimento;
    public $common;

    /**
     * Construtor da Analise
     * construct
     */
    public function __construct()
    {
        $this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->analise      = new MicrobAnaliseModel();
        $this->anarequisicao = new MicrobAnaRequisicaoModel();
        $this->common       = new CommonModel();

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
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'ana_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // if (!$anarequis = cache('anarequis')) {
        $campos = montaColunasCampos($this->data, 'req_id');
        $dados_tela = $this->anarequisicao->getListaRequisicao();
        foreach ($dados_tela as &$req) {
            $url_ati = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $req['req_id']);
            $req['acao_person'] = [
                "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0 float-end' 
                data-mdb-toggle='tooltip' data-mdb-placement='top' 
                title='Imprimir Requisição' onclick='openPDFModal(\"$url_ati\",\"Imprimir Requisição\")'>
                <i class='fa-solid fa-print'></i></button>"
            ];
        }
        $this->data['edicao'] = false;
        $anarequis = [
            'data' => montaListaColunas($this->data, 'req_id', $dados_tela, $campos[1]),
        ];
        cache()->save('anarequis', $anarequis, 60000);
        // }
        echo json_encode($anarequis);
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $requis = $this->anarequisicao->getListaRequisicao($id);
        // debug($requis);
        if ($requis) {
            $req = $requis[0];
            $fields = $this->anarequisicao->defCampos($requis[0], true);
            $secao[0] = 'Dados Gerais';
            $campos[0][0] = $fields['req_id'];
            if ($req['req_lotemb'] != '') {
                $campos[0][1] = $fields['req_lotemb'];
                $prox = 2;
            } else {
                $campos[0][1] = $fields['req_lotemb'];
                $campos[0][2] = $fields['ana_descmetodo'];
                $prox = 3;
            }

            $texto = "<div class='col-12 float-start d-block mt-5'>";
            $texto .= "<div class='col-4 float-start fw-bold'>Produto</div>";
            $texto .= "<div class='col-4 float-start fw-bold'>Fabricante</div>";
            $texto .= "<div class='col-2 float-start fw-bold'>Lote</div>";
            $texto .= "<div class='col-2 float-start fw-bold'>Validade</div>";
            for ($p = 0; $p < count($requis); $p++) {
                $prod = $requis[$p];
                $texto .= "<div class='col-4 float-start'>" . $prod['pro_despro'] . "</div>";
                $texto .= "<div class='col-4 float-start'>" . $prod['fab_apeFab'] . "</div>";
                $texto .= "<div class='col-2 float-start'>" . $prod['lot_lote'] . "</div>";
                $texto .= "<div class='col-2 float-start'>" . data_br($prod['lot_validade']) . "</div>";
            }
            $texto .= "</div>";
            $campos[0][$prox] = $texto;

            $this->data['secoes']     = $secao;
            $this->data['campos']     = $campos;
            $this->data['destino']    = 'store';

            echo view('vw_edicao', $this->data);
        } else {
            $this->index();
        }
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        $dados_analise = $this->analise->getListaAnaliseSemReq($id);
        if (count($dados_analise) === 0) {
            $this->index();
            return;
            // return redirect()->to('/AnaRequisicao/');
        }
        $dados_analise = $dados_analise[0];
        $lotemb = $dados_analise['ana_lotemb'];
        // debug($lotemb, true);
        $dados_requisi = $this->analise->getAnaliseLotemb($lotemb);
        // debug($dados_requisi, true);

        $fields = $this->anarequisicao->defCampos($dados_analise, false);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
        if ($dados_analise['ana_lotemb'] != '') {
            $campos[0][1] = $fields['req_lotemb'];
            $prox = 2;
        } else {
            $campos[0][1] = $fields['req_lotemb'];
            $campos[0][2] = $fields['ana_descmetodo'];
            $prox = 3;
        }

        $texto = "<div class='col-12 float-start d-block mt-5'>";
        $texto .= "<div class='col-4 float-start fw-bold'>Produto</div>";
        $texto .= "<div class='col-4 float-start fw-bold'>Fabricante</div>";
        $texto .= "<div class='col-2 float-start fw-bold'>Lote</div>";
        $texto .= "<div class='col-2 float-start fw-bold'>Validade</div>";
        for ($p = 0; $p < count($dados_requisi); $p++) {
            $prod = $dados_requisi[$p];
            $texto .= "<div class='col-4 float-start'>" . $prod['pro_despro'] . "</div>";
            $texto .= "<div class='col-4 float-start'>" . $prod['fab_apeFab'] . "</div>";
            $texto .= "<div class='col-2 float-start'>" . $prod['lot_lote'] . "</div>";
            $texto .= "<div class='col-2 float-start'>" . data_br($prod['lot_validade']) . "</div>";
        }
        $texto .= "</div>";
        $campos[0][$prox] = $texto;

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        $this->data['script'] = "<script>jQuery('#form1').attr('data-alter', true);</script>";

        echo view('vw_edicao', $this->data);
    }

    /**
     * Gerar Requisicao
     * gerar
     *
     * @param mixed $id 
     * @return void
     */
    public function add()
    {
        $fields[0] = 'Id';
        $fields[1] = 'Produto';
        $fields[2] = 'Fabricante';
        $fields[3] = 'Lote';
        $fields[4] = 'Lote MB';
        $fields[5] = 'Data';
        $fields[6] = 'Status';
        $fields[7] = 'Usuário';
        $fields[8] = 'Ação';
        $this->data['colunas'] = $fields;
        $this->data['desc_metodo'] = '';
        $this->data['mostrar']  = true; // não mostrar botão salvar e cancelar
        $this->data['url_lista'] = base_url($this->data['controler'] . '/listarequisicao');
        echo view('vw_lista', $this->data);
    }

    /**
     * Lista das Requisições
     * listarequisicao
     *
     * @param mixed $id 
     * @return void
     */
    public function listarequisicao()
    {
        $fields[0] = 'pro_despro';
        $fields[1] = 'fab_apeFab';
        $fields[2] = 'lot_lote';
        $fields[3] = 'ana_lotemb';
        $fields[4] = 'ana_data';
        $fields[5] = 'stt_nome';
        $fields[6] = 'usu_nome';

        $analis = $this->analise->getListaAnaliseSemReq(false, 14);
        // debug($analis, true);
        for ($da = 0; $da < count($analis); $da++) {
            $ent = $analis[$da];
            $log = buscaLog('pro_mic_analise', $ent['ana_id']);
            // debug($log);
            $analis[$da]['usu_nome'] = isset($log['usua_alterou']) ? $log['usua_alterou'] : '';
            $analis[$da]['stt_cor'] = 'bg-warning';
            $analis[$da]['stt_nome'] = 'Pendente';
        }
        $analise = [
            'data' => montaListaEditColunas($fields, $this->data, 'ana_id', $analis, 'pro_despro'),
        ];
        echo json_encode($analise);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        // debug($postado, true);
        $ret['erro'] = false;
        $erros = [];
        $db = Database::connect();
        $db->transBegin();

        try {
            $data_atual = new DateTime();
            $datatu_fmt = $data_atual->format('Y-m-d H:i:s');
            $dados_req = [
                'req_data'      => $datatu_fmt,
                'req_lotemb'    => $postado['req_lotemb'],
                'usu_id'        => session()->get('usu_id'),
            ];
            if (!$this->anarequisicao->save($dados_req)) {
                $erros = $this->anarequisicao->errors();
                throw new \Exception('Não foi possível gravar a Requisição. ' . implode(' ', $erros));
            }
            $req_id = $this->anarequisicao->getInsertID();
            $dados_analise = $this->analise->getAnaliseLotemb($postado['req_lotemb']);
            for ($da = 0; $da < count($dados_analise); $da++) {
                $dados_ana = [
                    'ana_id'    => $dados_analise[$da]['ana_id'],
                    'req_id'    => $req_id,
                ];
                if (!$this->analise->save($dados_ana)) {
                    $erros = $this->analise->errors();
                    throw new \Exception('Não foi possível atualizar as Análises. ' . implode(' ', $erros));
                }
            }
            // Confirma a transação
            $db->transCommit();
            $ret['msg']  = 'Dados da Requisição gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $link = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $req_id);
            session()->setFlashdata('modal', $link);
            session()->setFlashdata('modal-title', 'Imprimir Requisição');
            $ret['url']  = site_url($this->data['controler']);
        } catch (\Exception $e) {
            // Em caso de erro, reverte a transação
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg'] = $e->getMessage();
        }
        echo json_encode($ret);
    }
}
