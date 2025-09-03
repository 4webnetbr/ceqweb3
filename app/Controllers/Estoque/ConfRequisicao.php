<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\DTOs\LoteDestino;
use App\DTOs\LoteOrigem;
use App\DTOs\LotePadrao;
use App\DTOs\ProdutoMontado;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;
use Config\Database;

class ConfRequisicao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $requisicao;
    public $requisicaoproduto;
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
        $this->requisicaoproduto   = new EstoquRequisicaoProdutoModel();
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
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [18]);

        $base_url = base_url($this->data['controler']);
        foreach ($dados_requis as &$req) {
            // Verificar se o log já está disponível para esse ana_id
            if ($req['req_id']) {
                // Concatenar o URL de forma mais eficiente
                $url_con = $base_url .'/confere/' . $req['req_id'];
                // Gerar a ação do botão
                $req['acao_person'] = [
                    "<button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0' 
            data-mdb-toggle='tooltip' data-mdb-placement='top' 
            title='Conferência' onclick='redireciona(\"$url_con\")'>
            <i class='fas fa-check'></i></button>",
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
    public function atende($id, $show = false)
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
        // debug($dados_est_produto, true);
        // Transformar $produtos em um array indexado por pro_id
        $produtosIndexado = [];
        foreach ($produtos as $param) {
            $produtosIndexado[$param['pro_id']] = $param;
        }

        // Array para o resultado final
        $resultado = [];

        if(count($dados_est_produto) > 0){
            foreach ($dados_est_produto as $itemEstoque) {
                $pro_id = $itemEstoque['pro_id'];

                if (isset($produtosIndexado[$pro_id])) {
                    // Mescla os dados de estoque + parâmetros (com todas as chaves)
                    $resultado[] = array_merge($itemEstoque, $produtosIndexado[$pro_id]);
                } else {
                    // Se não existir parâmetro correspondente, adiciona só o estoque
                    $resultado[] = $itemEstoque;
                }
            }
        } else {
            $resultado = $produtos;
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
            $resultado[$p]['rep_cancelada'] = $fields['rep_cancelada'];
            $resultado[$p]['rep_atendida'] = $fields['rep_atendida'];
        }
        // $secao[1] = 'Produtos';
        $campos[0][count($campos[0])] = view('partials/pw_produtos_requisicao',['produtos' => $resultado]); // mesma estrutura do add()

        $envr          = new MyCampo();
        $envr->nome    = 'bt_envia';
        $envr->id      = 'bt_envia';
        $envr->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-check" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $envr->i_cone  .= '<div class="align-items-start txt-bt-manut">Finalizar Atendimento</div>';
        $envr->place    = 'Finalizar Atendimento';
        // $envr->funcChan = 'enviarRequisicoes(1)';
        $envr->classep  = 'btn-success bt-manut btn-sm mb-2 float-end';
        $this->bt_envia = $envr->crBotao();

        $this->data['botao'] = $this->bt_envia;

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
        $ret['erro'] = false;
        $db = \Config\Database::connect();

        $requisicoes = json_decode($postado['json_requisicoes'], true);

        $db->transStart(); // Início da transação

        $status = 6;
        if (isset($postado['req_status'])) {
            if ($postado['req_status'] == 1) {
                $status = 4;
            }
        }

        // Prepara dados para inserção/atualização na tabela est_requisicao
        $dadosReq = [
            'req_data' => $postado['req_data'],
            'req_dataentrega' => $postado['req_dataentrega'],
            'tmo_id' => $postado['tmo_id'],
            'req_deporigem' => $postado['req_deporigem'],
            'req_depdestino' => $postado['req_depdestino'],
            'req_consdiaanterior' => $postado['req_consdiaanterior'],
            'req_medconsumodias' => $postado['req_medconsumodias'],
            'req_meddias' => $postado['req_meddias'] ?? null,
            'req_repetedias' => 1,
            'req_percseguranca' => $postado['req_percseguranca'],
            'req_observacao' => $postado['req_observacao'],
            'stt_id' => $status
        ];

        if ($postado['req_id'] != "") {
            $salvaReq = $this->requisicao->update($postado['req_id'], $dadosReq);
            $req_id = $postado['req_id'];
            $this->requisicaoproduto->excluir($req_id);
        } else {
            $salvaReq = $this->requisicao->insert($dadosReq);
            $req_id = $this->requisicao->getInsertID();
        }

        if ($salvaReq) {
            // Inserir os produtos da requisição principal
            foreach ($requisicoes as $item) {
                $produto = $this->produtos->getProdutoCod($item['cod_erp']);
                $lote = $this->lote->getLoteCodproLote($item['cod_erp'], $item['lote']);

                if (!$produto || !$lote) {
                    $ret['erro'] = true;
                    $ret['msg'] = "Produto ou lote não encontrado para o código: {$item['cod_erp']} ou lote: {$item['lote']}";
                    $db->transRollback();
                    echo json_encode($ret);
                    return;
                }

                $rep = [
                    'req_id' => $req_id,
                    'pro_id' => $produto[0]['pro_id'],
                    'lot_id' => $lote[0]['lot_id'],
                    'rep_quantia' => $item['requisicao']
                ];

                if (!$this->requisicaoproduto->insert($rep)) {
                    $ret['erro'] = true;
                    $erros = $this->requisicaoproduto->errors();
                    $ret['msg'] = 'Erro ao inserir item na requisição de produto.';

                    if (!empty($erros)) {
                        foreach ($erros as $campo => $mensagem) {
                            $ret['msg'] .= " Campo: {$campo} - Erro: {$mensagem}";
                        }
                    }

                    $db->transRollback();
                    echo json_encode($ret);
                    return;
                }
            }

            // Repetições mesmo em update
            if (intval($postado['req_repetedias']) > 1) {
                $totalRepeticoes = intval($postado['req_repetedias']);

                for ($i = 1; $i < $totalRepeticoes; $i++) {
                    $novaReq = $dadosReq;

                    // Incrementa a data de entrega
                    $dataEntrega = new \DateTime($dadosReq['req_dataentrega']);
                    $dataEntrega->modify("+{$i} days");
                    $novaReq['req_dataentrega'] = $dataEntrega->format('Y-m-d');

                    // Atualiza a data de criação
                    $novaReq['req_data'] = date('Y-m-d');

                    $salvaNova = $this->requisicao->insert($novaReq);

                    if (!$salvaNova) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Erro ao inserir requisição repetida.';
                        $db->transRollback();
                        echo json_encode($ret);
                        return;
                    }

                    $novo_req_id = $this->requisicao->getInsertID();

                    foreach ($requisicoes as $item) {
                        $produto = $this->produtos->getProdutoCod($item['cod_erp']);
                        $lote = $this->lote->getLoteCodproLote($item['cod_erp'], $item['lote']);

                        if (!$produto || !$lote) {
                            $ret['erro'] = true;
                            $ret['msg'] = "Produto ou lote não encontrado para o código: {$item['cod_erp']} ou lote: {$item['lote']} (repetição)";
                            $db->transRollback();
                            echo json_encode($ret);
                            return;
                        }

                        $rep = [
                            'req_id' => $novo_req_id,
                            'pro_id' => $produto[0]['pro_id'],
                            'lot_id' => $lote[0]['lot_id'],
                            'rep_quantia' => $item['requisicao']
                        ];

                        if (!$this->requisicaoproduto->insert($rep)) {
                            $ret['erro'] = true;
                            $ret['msg'] = "Erro ao inserir item em requisição repetida.";
                            $db->transRollback();
                            echo json_encode($ret);
                            return;
                        }
                    }
                }
            }
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Erro ao gravar a requisição.';
            $db->transRollback();
            echo json_encode($ret);
            return;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            $ret['erro'] = true;
            $ret['msg'] = 'Erro na transação. Nenhum dado foi gravado.';
        } else {
            $ret['msg'] = 'Requisição gravada com sucesso!';
            $ret['url'] = site_url($this->data['controler']);
            session()->setFlashdata('msg', $ret['msg']);
        }

        echo json_encode($ret);
        cache()->clean();
    }
}
