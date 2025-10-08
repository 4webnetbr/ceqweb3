<?php

namespace App\Controllers\Estoque;

use Config\Database;
use App\DTOs\LoteOrigem;
use App\DTOs\LotePadrao;
use App\DTOs\LoteDestino;
use App\Libraries\MyCampo;
use App\DTOs\ProdutoMontado;
use App\Controllers\BuscasSapiens;
use App\Controllers\BaseController;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;

class AteRequisicao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $requisicao;
    public $reqproduto;
    public $reqprodutoate;
    public $classes;
    public $produtos;
    public $lote;
    public $busca;
    public $deposito;
    public $bt_envia;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->requisicao   = new EstoquRequisicaoModel();
        $this->reqproduto   = new EstoquRequisicaoProdutoModel();
        $this->reqprodutoate   = new EstoquRequisicaoProdutoAtendimentoModel();
        $this->classes      = new ProdutClasseModel();
        $this->produtos     = new ProdutProdutoModel();
        $this->busca        = new BuscasSapiens();
        $this->deposito     = new EstoquDepositoModel();
        $this->lote         = new ProdutLoteModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'req_id');
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
        // if (!$requis = cache('requis')) {
        $campos = montaColunasCampos($this->data, 'req_id');
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [4, 21]);

        $base_url = base_url($this->data['controler']);
        foreach ($dados_requis as &$req) {
            // Verificar se o log já está disponível para esse ana_id
            if ($req['req_id']) {
                // Concatenar o URL de forma mais eficiente
                $url_eti = $base_url .'/EtqProduto/' . $req['req_id'];
                $url_ate = $base_url .'/atende/' . $req['req_id'];
                $url_imp = base_url('/CriaPdf2025/PrintRequisicaoEstoq/' . $req['req_id']);
                // Gerar a ação do botão
                $req['acao_person'] = [
                    "<button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0' 
                    data-mdb-toggle='tooltip' data-mdb-placement='top' 
                    title='Atendimento' onclick='redireciona(\"$url_ate\")'>
                    <i class='fas fa-bell-concierge'></i></button>",
                    "<button class='btn btn-outline-warning btn-sm border-0 mx-0 fs-0' 
                    data-mdb-toggle='tooltip' data-mdb-placement='top' 
                    title='Etiquetas de Produtos' onclick='redireciona(\"$url_eti\")'>
                    <i class='fas fa-tag'></i></button>",
                    "<button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0 float-end' 
                    data-mdb-toggle='tooltip' data-mdb-placement='top' 
                    title='Imprimir Requisição' onclick='openPDFModal(\"$url_imp\",\"Imprimir Requisição\")'>
                    <i class='fa-solid fa-print'></i></button>"
                ];
            }
        }
        // debug($dados_requis, true);
        $this->data['edicao'] = false;
        $requis = [
            'data' => montaListaColunas($this->data, 'req_id', $dados_requis, $campos[1]),
        ];
        cache()->save('requis', $requis, 60000);
        // }

        echo json_encode($requis);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $fields = $this->requisicao->defCampos();
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
        $campos[0][count($campos[0])] = $fields['req_data'];
        $campos[0][count($campos[0])] = $fields['req_dataentrega'];
        $campos[0][count($campos[0])] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = $fields['req_repetedias'];
        $campos[0][count($campos[0])] = $fields['req_deporigem'];
        $campos[0][count($campos[0])] = $fields['req_depdestino'];
        $campos[0][count($campos[0])] = $fields['req_consdiaanterior'];
        $campos[0][count($campos[0])] = $fields['req_medconsumodias'];
        $campos[0][count($campos[0])] = $fields['req_meddias'];
        $campos[0][count($campos[0])] = $fields['req_percseguranca'];
        $campos[0][count($campos[0])] = $fields['pro_id'];
        $campos[0][count($campos[0])] = $fields['req_observacao'];
        $campos[0][count($campos[0])] = $fields['bt_carregar'];

        $secao[1] = 'Produtos';
        $campos[1][0] = '';

        $envr          = new MyCampo();
        $envr->nome    = 'bt_envia';
        $envr->id      = 'bt_envia';
        $envr->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-regular fa-paper-plane" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $envr->i_cone  .= '<div class="align-items-start txt-bt-manut">Enviar Requisição</div>';
        $envr->place    = 'Enviar Requisição';
        $envr->funcChan = 'enviarRequisicoes(1)';
        $envr->classep  = 'btn-success bt-manut btn-sm mb-2 float-end';
        $this->bt_envia = $envr->crBotao();

        $this->data['botao'] = $this->bt_envia;
        $this->data['title']     = 'Requisição';
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        $this->data['scripts']  = 'my_requisicao';

        $this->data['script']   = "<script>mostraOcultaCampo('req_consdiaanterior', 'N', 'req_medconsumodias,req_meddias');mudaCheck2opcoes('req_consdiaanterior', 'req_medconsumodias');atualizarEstadoBotaoSalvar();</script>";

        echo view('vw_edicao', $this->data);
    }

    public function show($id){
        return redirect()->to('/Requisicao/show/'.$id);
    }

    public function edit($id, $show = false){
        $this->atende($id, $show = false);
    }
    /**
     * Atenndimento
     * atende
     *
     * @param mixed $id 
     * @return void
     */
    public function atende($id, $show = true)
    {
        $requisicao = $this->requisicao->getRequisicao($id)[0];
        
        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }
        
        // Montar campos como no add()
        $fields = $this->requisicao->defCampos($requisicao, $show);
        // debug($fields, true);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
        $campos[0][count($campos[0])] = $fields['req_data'];
        $campos[0][count($campos[0])] = $fields['req_dataentrega'];
        $campos[0][count($campos[0])] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = "<div class='col-6'>.</div>";
        $campos[0][count($campos[0])] = $fields['lot_codbar'];
        
        $produtos = $this->requisicao->getRequisicaoProdutos($id);
        $pro_ids = array_unique(array_column($produtos, 'pro_id'));
        $dados_est_produto = $this->produtos->getProdutoEstoque($pro_ids, $requisicao['req_deporigem']);
        // debug($produtos, false);
        // debug($dados_est_produto, false);
        // Transformar $produtos em um array indexado por pro_id
        $produtosIndexado = [];
        foreach ($produtos as $param) {
            $produtosIndexado[$param['pro_id']] = $param;
        }

        // Indexa os dados de estoque por pro_id para facilitar busca
        $estoqueIndexado = [];
        foreach ($dados_est_produto as $itemEstoque) {
            $estoqueIndexado[$itemEstoque['pro_id']] = $itemEstoque;
        }

        // Resultado final com todos os produtos
        $resultado = [];

        foreach ($produtosIndexado as $pro_id => $produto) {
            if (isset($estoqueIndexado[$pro_id])) {
                // Mescla produto com dados de estoque
                $resultado[] = array_merge($produto, $estoqueIndexado[$pro_id]);
            } else {
                // Apenas dados do produto
                $resultado[] = $produto;
            }
        }
        // debug($resultado, true);
        
        for ($p=0; $p < count($resultado); $p++) { 
            $prod = $resultado[$p];
            if(!isset($prod['pre_cbfabricante'])){
                $resultado[$p]['pre_cbfabricante'] = 'N';
                $resultado[$p]['pre_undfabricante'] = 'N';
                $resultado[$p]['pre_cblote'] = 'N';
                $resultado[$p]['pre_undlote'] = 'N';
                $prod['pre_cbfabricante'] = 'N';
                $prod['pre_undfabricante'] = 'N';
                $prod['pre_cblote'] = 'N';
                $prod['pre_undlote'] = 'N';
            }
            $fields = $this->requisicao->defCamposProdutoAte($prod);
            $resultado[$p]['rpa_cancelada'] = $fields['rpa_cancelada'];
            $resultado[$p]['rpa_atendida'] = $fields['rpa_atendida'];
            $resultado[$p]['rpa_cancelada_val'] = $prod['rpa_cancelada'];
            $resultado[$p]['rpa_atendida_val'] = $prod['rpa_atendida'];
            $resultado[$p]['saldo'] = intval($resultado[$p]['rep_quantia']) - (intval($prod['rpa_cancelada']) + intval($prod['rpa_atendida']));
        }
        // $secao[1] = 'Produtos';
        $campos[0][count($campos[0])] = view('partials/pw_produtos_requisicao',['produtos' => $resultado]); // mesma estrutura do add()

        // $envr          = new MyCampo();
        // $envr->nome    = 'bt_envia';
        // $envr->id      = 'bt_envia';
        // $envr->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
        //                     <i class="fa-solid fa-check" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        // $envr->i_cone  .= '<div class="align-items-start txt-bt-manut">Finalizar Atendimento</div>';
        // $envr->place    = 'Finalizar Atendimento';
        // // $envr->funcChan = 'enviarRequisicoes(1)';
        // $envr->classep  = 'btn-success bt-manut btn-sm mb-2 float-end';
        // $this->bt_envia = $envr->crBotao();

        // $this->data['botao'] = $this->bt_envia;

        $this->data['title']     = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['desc_metodo']     = ' Atendimento de ';
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = 'store'; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        // TODO implementar

    }
    /**
     * Impressão de Etiquetas de Produtos
     * EtqProduto
     *
     * @param mixed $id 
     * @return void
     */
    public function EtqProduto($id)
    {
        $requisicao = $this->requisicao->getRequisicao($id)[0];

        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }

        // Montar campos como no add()
        $fields = $this->requisicao->defCampos($requisicao, true);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
        $campos[0][count($campos[0])] = $fields['req_data'];
        $campos[0][count($campos[0])] = $fields['req_dataentrega'];
        $campos[0][count($campos[0])] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = "<div class='col-6'>.</div>";
        // $campos[0][count($campos[0])] = $fields['lot_codbar'];

        $produtosreq = $this->requisicao->getRequisicaoProdutos($id);
        // debug($produtosreq, true);
        $colunas = ['Cód ERP','Descrição','Fabricante','Lote','Qtde.Requerida','Qtde.Imprimir','Coloração Etiqueta','Imprimir'];
        $produtos = [];
        $produtos[0] = $id;
        if(count($produtosreq) > 0){
            for ($p=0; $p < count($produtosreq) ; $p++) { 
                $prod = $produtosreq[$p];

                $rep_id = $prod['rep_id'];
                $qtia = $prod['rep_quantia'];
                $url_ati = base_url($this->data['controler'].'/GeraEtiqueta/'.$rep_id.'/'.$qtia);
                $imprimir =
                    "<button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0' data-mdb-toggle='tooltip' 
                    data-mdb-placement='top' title='Imprimir Etiqueta' onclick='geraEiquetaProd(\"".$url_ati."\")'><i class='fas fa-print'></i></button>";
                $item = [];
                $item[0] = $rep_id;
                $item[count($item)] = $prod['pro_codpro'];
                $item[count($item)] = $prod['pro_despro'];
                $item[count($item)] = $prod['fab_apeFab'];
                $item[count($item)] = $prod['lot_lote'];
                $item[count($item)] = $prod['rep_quantia'];
                $item[count($item)] = $prod['rep_quantia'];
                $item[count($item)] = $prod['etiq_cor'];
                $item[count($item)] = $imprimir;
                $produtos[count($produtos)] = $item;
            }
        }
        // debug($produtos, true);
        $data = [
            'show' => true,
            'colunas' => $colunas,
            'produtos' => $produtos
        ];

        $campos[0][count($campos[0])] = view('partials/pw_show_produtos_req',$data); // mesma estrutura do add()
        
        // $this->data['mostrar']   = ''; // ou 'update' se você for criar
        $this->data['icone']   = "<i class='fas fa-tag'></i>"; // ou 'update' se você for criar
        $this->data['desc_metodo']   = ''; // ou 'update' se você for criar
        $this->data['title']   = 'Impressão de Etiquetas de Produtos'; // ou 'update' se você for criar
        $this->data['desc_edicao']  = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = ''; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';

        echo view('vw_edicao', $this->data);
    }

    public function GeraEtiqueta($id, $qtia){
        $produtos = $this->requisicao->getRequisicaoRep($id);
        // debug($produtos);
        $produtosreq = array_fill(0, $qtia, $produtos[0]);
        // debug($produtosreq);
        $chave = uniqid('etq_');
        cache()->save($chave, $produtosreq, 300); // 1 minuto

        $link = base_url('/CriaEtiquetaZPL/emiteEtiqueta');

        $ret['link'] = $link;
        $ret['chave'] = $chave;

        return json_encode($ret);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $postado = $this->request->getPost();
        // debug($postado);

        $dadosAgrupados = [];

        foreach ($postado as $key => $value) {
            if (preg_match('/^repid_(\d+)$/', $key, $matches)) {
                $id = $matches[1]; // Ex: 88, 89

                // Inicializa temporário para os dados desse ID
                $dadosTemp = ['repid' => $value];

                // Pega os campos relacionados ao mesmo ID
                foreach ($postado as $campo => $val) {
                    if (str_ends_with($campo, "_$id") && $campo !== "repid_$id") {
                        $nomeCampo = substr($campo, 0, -strlen("_$id"));
                        $dadosTemp[$nomeCampo] = $val;
                    }
                    if($campo === "req_id"){
                        $dadosTemp[$campo] = $val;
                    }
                    if($campo === "tmo_id"){
                        $dadosTemp[$campo] = $val;
                    }
                }

                // Faz a verificação: repqtia == (rpa_cancelada + rpa_atendida)
                $qtia        = (int)($dadosTemp['repqtia'] ?? 0);
                $cancelada   = (int)($dadosTemp['rpa_cancelada'] ?? 0);
                $atendida    = (int)($dadosTemp['rpa_atendida'] ?? 0);
                $somaStatus  = $cancelada + $atendida;

                if ($qtia === $somaStatus) {
                    // Somente adiciona se a condição for satisfeita
                    $dadosAgrupados[$id] = $dadosTemp;
                }
            }
        }
        // debug(count($dadosAgrupados));
        if(count($dadosAgrupados) === 0){
            $msg            = 7;
            session()->setFlashdata('msg', $msg);
            $ret['url'] = site_url($this->data['controler']);
            $ret['erro'] = false;
        } else {
            debug($dadosAgrupados, true);
            $ret['erro'] = false;
            $db = \Config\Database::connect();

            $db->transStart(); // Início da transação


            foreach ($dadosAgrupados as $campo => $val) {
                $sql_save = [
                    'rep_id' => $val['repid'],
                    'pro_id' => $val['proid'],
                    'rpa_cancelada' => $val['rpa_cancelada'],
                    'rpa_atendida' => $val['rpa_atendida'],
                    'rpa_data' => date('Y-m-d H:i:s'),
                ];
                if (!$this->reqprodutoate->insert($sql_save)) {
                    $ret['erro'] = true;
                    $ret['msg'] = 'Erro ao gravar o Atendimento.';
                    $db->transRollback();
                    echo json_encode($ret);
                    return;
                } else {
                    // pega o movimento
                    $idmov  = $val['tmo_id'];
                    $qtia   = $val['rpa_atendida'];
                    // cria os movimentos
                    $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
                        'id'  => $idmov,
                        'qt'  => $qtia,
                        'msg' => 'Atendimento de Requisição'
                    ];
                }
            }
            $db->transComplete();

            if ($db->transStatus() === false) {
                $ret['erro'] = true;
                $ret['msg'] = 'Erro na transação. Nenhum dado foi gravado.';
            } else {


                $saldos = $this->reqprodutoate->getSaldoRequisicao($postado['req_id']);
                $sald = 0;
                $status = 18;
                for ($s=0; $s < count($saldos) ; $s++) { 
                    $sald += $saldos[$s]['saldo'];
                }
                if($sald > 0){
                    $status = 21;
                }
                $dadosReq = [
                    'stt_id' => $status
                ];
                $this->requisicao->update($postado['req_id'], $dadosReq);
                $ret['msg'] = 'Atendimento gravada com sucesso!';
                $ret['url'] = site_url($this->data['controler']);
                session()->setFlashdata('msg', $ret['msg']);
            }
        }

        echo json_encode($ret);
        // cache()->clean();
    }
}
