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

class Requisicao extends BaseController
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
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [6, 4]);
        // debug($dados_requis, true);
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

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
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
        $pro_ids = array_unique(array_column($produtosreq, 'pro_id'));
        $dados_est_produto = $this->produtos->getProdutoEstoque($pro_ids, $requisicao['req_depdestino']);
        // debug($dados_est_produto, true);
        // Transformar $produtos em um array indexado por pro_id
        $produtosIndexado = [];
        foreach ($produtosreq as $param) {
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
            $produtosreq = $resultado;
        } 
        // debug($produtosreq);
        $colunas = ['Cód ERP','Descrição','Fabricante','LF','Lote','LP','Validade','Caixas','Qtde.Requerida','Qtde.Cancelada','Qtde.Pendente','Saldo'];
        $produtos = [];
        $produtos[0] = $id;
        if(count($produtosreq) > 0){
            for ($p=0; $p < count($produtosreq) ; $p++) { 
                $prod = $produtosreq[$p];
                // debug($prod);
                $produto = [];
                $produto[0] = $prod['rep_id'];
                $produto[count($produto)] = $prod['pro_codpro'];
                $produto[count($produto)] = $prod['pro_despro'];
                $produto[count($produto)] = $prod['fab_apeFab'];
                if(isset($prod['pre_cbfabricante'])){
                    $produto[count($produto)] = $prod['pre_cbfabricante'].$prod['pre_undfabricante'];
                } else {
                    $produto[count($produto)] = '';
                }
                $produto[count($produto)] = $prod['lot_lote'];
                if(isset($prod['pre_cblote'])){
                    $produto[count($produto)] = $prod['pre_cblote'].$prod['pre_undlote'];
                } else {
                    $produto[count($produto)] = '';
                }
                $produto[count($produto)] = data_br($prod['lot_validade']);
                $produto[count($produto)] = $prod['qtd_caixa'];
                $produto[count($produto)] = $prod['rep_quantia'];
                $produto[count($produto)] = $prod['rpa_cancelada'];
                $produto[count($produto)] = $prod['rpa_atendida'];
                $produto[count($produto)] = $prod['rep_quantia'];
                $produtos[count($produtos)] =$produto;
            }
        }
        // debug($produtos, true);
        $data = [
            'show' => true,
            'colunas' => $colunas,
            'produtos' => $produtos
        ];

        $campos[0][count($campos[0])] = view('partials/pw_show_produtos_req',$data); // mesma estrutura do add()

        $this->data['desc_edicao']     = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = ''; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';

        echo view('vw_edicao', $this->data);
    }


    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id)
    {
        $requisicao = $this->requisicao->find($id);

        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }

        // Montar campos como no add()
        $fields = $this->requisicao->defCampos($requisicao);
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
        $campos[1][0] = ''; // mesma estrutura do add()

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

        $this->data['title']     = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = 'store'; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';
        $this->data['script']    = "<script>jQuery('#bt_carregar').trigger('click');mostraOcultaCampo('req_consdiaanterior', 'N', 'req_medconsumodias,req_meddias');mudaCheck2opcoes('req_consdiaanterior', 'req_medconsumodias');</script>";

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
     * Carga de Produtos da Requisição
     * produtos
     *
     * @return void
     */
    public function produtos()
    {
        $req = request()->getVar();
        // === Parâmetros recebidos ===
        $reqid          = trim($req['reqid']);
        $proid          = trim($req['proid']);
        $deporigem      = trim($req['deporigem']);
        $depdestino     = trim($req['depdestino']);
        $tipomovim      = trim($req['tipomovim']);
        $multiplica     = trim($req['multiplica']);
        $diaanterior    = trim($req['diaanterior']);
        $mediaconsumo   = trim($req['mediaconsumo']);
        $pct_seguranca  = trim($req['seguranca']);
        $meddias        = trim($req['meddias']);

        $prodreq = [];
        if ($reqid != '') {
            $prodreq = $this->requisicaoproduto->getRequisicaoProdutos($reqid);
            // debug($prodreq, true);
            $pro_ids = array_unique(array_column($prodreq, 'pro_id'));
            $dados_est_produto = $this->produtos->getProdutoEstoque($pro_ids, $req['depdestino']);

            // Indexar dados de estoque por pro_id para acesso rápido
            $estoqueIndexado = [];
            foreach ($dados_est_produto as $estoque) {
                $estoqueIndexado[$estoque['pro_id']] = $estoque;
            }

            // Resultado final com todos os produtos da requisição
            $resultado = [];

            foreach ($prodreq as $produto) {
                $pro_id = $produto['pro_id'];

                if (isset($estoqueIndexado[$pro_id])) {
                    // Mescla os dados do produto com os dados de estoque
                    $resultado[] = array_merge($produto, $estoqueIndexado[$pro_id]);
                } else {
                    // Produto sem controle de estoque, usa apenas os dados da requisição
                    $resultado[] = $produto;
                }
            }

            $prodreq = $resultado;

            // debug($prodreq, false);

        }

        // === Dados iniciais de retorno ===
        $ret = [
            'deporigem'     => $deporigem,
            'depdestino'    => $depdestino,
            'deppadrao'     => '',
            'diaanterior'   => $diaanterior,
            'mediaconsumo'  => $mediaconsumo,
            'meddias'       => $meddias,
            'classe'        => []
        ];

        envia_msg_ws($this->data['controler'], "Buscando Transação", 'MsgServer', session()->get('usu_id'), 1);
        $tipomov = (new EstoquTipoMovimentacaoModel())->getTipoMovimentacao($tipomovim)[0];

        envia_msg_ws($this->data['controler'], "Carregando produtos", 'MsgServer', session()->get('usu_id'), 1);
        if ($proid == '') {
            $proid = false;
        }
        $listaProdutos = $this->produtos->getProdutoRequisicao($depdestino, $proid);
        // debug($listaProdutos, true);
        // === Estoque origem e destino ===
        envia_msg_ws($this->data['controler'], "Buscando estoque de origem", 'MsgServer', session()->get('usu_id'), 1);
        $estoqueOrigem = $this->indexarEstoque($this->busca->buscaEstoqueDeposito($deporigem) ?? []);

        envia_msg_ws($this->data['controler'], "Buscando estoque de destino", 'MsgServer', session()->get('usu_id'), 1);
        $estoqueDestino = $this->indexarEstoque($this->busca->buscaEstoqueDeposito($depdestino) ?? []);
        // debug($estoqueDestino);

        // === Estoque padrão (opcional) ===
        $estoquePadrao = [];
        if (!empty($tipomov['tmo_estoquepadrao']) && strtoupper($tipomov['tmo_estoquepadrao']) === 'S') {
            envia_msg_ws($this->data['controler'], "Obtendo depósito padrão", 'MsgServer', session()->get('usu_id'), 1);
            $deppadrao = $this->deposito->getDepositoPadrao()[0]['dep_codDep'] ?? '';

            if ($deppadrao && $deppadrao !== $deporigem && $deppadrao !== $depdestino) {
                $ret['deppadrao'] = $deppadrao;
                envia_msg_ws($this->data['controler'], "Buscando estoque padrão", 'MsgServer', session()->get('usu_id'), 1);
                $estoquePadrao = $this->indexarEstoque($this->busca->buscaEstoqueDeposito($deppadrao) ?? []);
            } else {
                envia_msg_ws($this->data['controler'], "Depósito padrão inválido (igual à origem ou destino)", 'MsgServer', session()->get('usu_id'), 1);
            }
        }

        // === Período para consumo ===
        $dias   = ($mediaconsumo === 'S' && $meddias > 0) ? $meddias : 1;
        $ontem  = date('Y-m-d', strtotime('-1 day'));
        $inicio = date('Y-m-d', strtotime("-{$dias} days"));

        envia_msg_ws($this->data['controler'], "Consumo de {$inicio} até {$ontem}", 'MsgServer', session()->get('usu_id'), 1);

        // === Buscar consumo de insumos, equipos, bolsas ===
        $consumo = [
            'insumos' => [],
            'equipos' => [],
            'bolsas'  => []
        ];

        $tiposConsumo = [
            'insumos' => 'api_tot_insumos.php',
            'equipos' => 'api_tot_equipo.php',
            'bolsas'  => 'api_tot_bolsa.php'
        ];

        foreach ($tiposConsumo as $tipo => $endpoint) {
            envia_msg_ws($this->data['controler'], "Buscando " . ucfirst($tipo), 'MsgServer', session()->get('usu_id'), 1);

            $res = api_request(
                "https://secure.ceqnep.com.br/producao/home/ajax/{$endpoint}",
                ['inicio' => $inicio, 'final' => $ontem],
                'get'
            );

            if ($res) {
                $consumo[$tipo] = $this->indexarConsumo($res);
            }
        }

        // === Agrupar produtos por classe com produtos válidos ===
        $classesTemp = [];

        foreach ($listaProdutos as $prod) {
            // debug($prod);
            $codPro  = $prod['pro_codpro'];
            $lotePro = $prod['lot_lote'];
            $loteid  = $prod['lot_id']??'';
            $claId   = $prod['cla_id'];
            $claNome = $prod['cla_nome'];

            // debug($codPro . ' - ' . $loteid);
            $produtoreq = [];
            // if($loteid){
                $produtoreq = $this->buscarProdutoReq($prodreq, $codPro, $loteid);
                // debug($produtoreq);
            // }
            $ori = $estoqueOrigem[$codPro][$lotePro] ?? [];
            $des = $estoqueDestino[$codPro][$lotePro] ?? [];
            $pad = ($ret['deppadrao'] !== '') ? ($estoquePadrao[$codPro][$lotePro] ?? []) : [];


            // Parâmetros para cálculo
            $prod['pro_consumo']      = 0;
            $prod['pro_multiplica']   = $produtoreq['rep_multiplicador']??1;
            $prod['pct_seguranca']    = $pct_seguranca;
            $prod['pro_seguranca']    = 0;
            $prod['pro_meddias']      = $meddias;
            $prod['pro_sugestao']     = 0;
            $prod['pro_diaanterior']  = $diaanterior;
            $prod['pro_mediaconsumo'] = $mediaconsumo;

            $produto = $this->montarProduto($prod, $ori, $des, $pad, $consumo, $produtoreq);

            if (!empty($produto)) {
                if (!isset($classesTemp[$claId])) {
                    envia_msg_ws($this->data['controler'], "Processando classe: {$claNome}", 'MsgServer', session()->get('usu_id'), 1);
                    $classesTemp[$claId] = [
                        'id'   => $claId,
                        'nome' => $claNome,
                        'prod' => []
                    ];
                }

                $classesTemp[$claId]['prod'][] = $produto;
            }
        }

        // === Montar array final com classes que têm produtos ===
        $ret['classe'] = array_values($classesTemp);

        if (empty($ret['classe'])) {
            envia_msg_ws($this->data['controler'], "Nenhum produto encontrado", 'MsgServer', session()->get('usu_id'), 1);
        } else {
            envia_msg_ws($this->data['controler'], "Finalizando resposta com produtos", 'MsgServer', session()->get('usu_id'), 1);
        }

        echo json_encode($ret);
    }

    function buscarProdutoReq(array $dados, string $codpro, string $lot_id): array
    {
        // debug('Prod '.$codpro.' Lote '.$lot_id);
        foreach ($dados as $item) {
            if ($item['pro_codpro'] == $codpro) {
                if ($lot_id === '' || $item['lot_id'] == $lot_id) {
                    return $item;
                }
            }
        }

        return [];
    }

    private function indexarEstoque(array $estoque): array
    {
        $indexado = [];

        foreach ($estoque as $item) {
            // Remover apenas o ponto do número (mantém vírgula, se houver)
            if (isset($item->quantidadeEstoque)) {
                $item->quantidadeEstoque = str_replace('.', '', (string) $item->quantidadeEstoque);
            }

            $indexado[$item->codigoProduto][$item->codigoLote][] = $item;
        }

        return $indexado;
    }

    private function indexarConsumo(array $consumo): array
    {
        $indexado = [];
        foreach ($consumo as $item) {
            $codigo = $item['codigo_erp'];
            $reindexado[$codigo] = $item;
        }
        return $reindexado;
    }

    private function montarProduto(array $prod, array $ori, array $des, array $padr, array $consumo, array $produtoreq): array
    {
        $produto = new ProdutoMontado($prod);

        // === Preencher lote de origem ===
        if ($oriItem = $ori[0] ?? null) {
            $produto->loteori->lote_origem     = $oriItem->codigoLote ?? '';
            $produto->loteori->validade_origem = $oriItem->validade ?? '';
            $produto->loteori->pro_estorigem   = $oriItem->quantidadeEstoque ?? 0;
        }

        // === Preencher lote de destino ===
        if ($desItem = $des[0] ?? null) {
            $produto->lotedes->lote_destino     = $desItem->codigoLote ?? '';
            $produto->lotedes->validade_destino = $desItem->validade ?? '';
            $produto->lotedes->pro_estdestino   = $desItem->quantidadeEstoque ?? 0;
        }

        // === Preencher lote padrão ===
        foreach ($padr as $item) {
            if ($item->quantidadeEstoque > 0) {
                $produto->lotepad->lote_padrao     = $item->codigoLote ?? '';
                $produto->lotepad->validade_padrao = $item->validade ?? '';
                $produto->lotepad->pro_estpadrao   = $item->quantidadeEstoque ?? 0;
                break;
            }
        }

        // === Validação de estoque origem/padrão ===
        $proEstDestin = $produto->lotedes->pro_estdestino;
        $proEstOrigem = $produto->loteori->pro_estorigem;
        $proEstPadrao = $produto->lotepad->pro_estpadrao;

        $proEstDispon = $proEstPadrao + $proEstOrigem;

        if (($proEstDispon + $proEstDestin) <= 0) {
            return [];
        }

        // === Verifica se deve calcular consumo ===
        if ($prod['pro_diaanterior'] === 'S' || $prod['pro_mediaconsumo'] === 'S') {
            $dash = $prod['cla_dash_consumo'];
            $codPro = $prod['pro_codpro'];

            // === Buscar consumo ===
            if ($prod['pre_gestaoestoque'] === 'S') {
                switch ($dash) {
                    case 'Insumos':
                        $produto->pro_consumo_medio  = $consumo['insumos'][$codPro]['media'] ?? 0;
                        $produto->pro_consumo_diaant = $consumo['insumos'][$codPro]['consumido'] ?? 0;
                        break;
                    case 'Equipos':
                        $produto->pro_consumo_medio  = $consumo['equipos'][$codPro]['media'] ?? 0;
                        $produto->pro_consumo_diaant = $consumo['equipos'][$codPro]['consumido'] ?? 0;
                        break;
                    case 'Bolsas':
                        $produto->pro_consumo_medio  = $consumo['bolsas'][$codPro]['media'] ?? 0;
                        $produto->pro_consumo_diaant = $consumo['bolsas'][$codPro]['consumido'] ?? 0;
                        break;
                }
            } else {
                $produto->pro_consumo_medio  = 0;
                $produto->pro_consumo_diaant = 0;
            }

            // === Base de consumo ===
            $produto->pro_consumo = ($prod['pro_meddias'] > 0)
                ? $produto->pro_consumo_medio
                : $produto->pro_consumo_diaant;

            // === Ajuste no estoque de destino (condicional) ===
            if ($prod['pre_gestaoestoque'] === 'S' && $prod['pre_estdataatual'] === 'N') {
                $produto->lotedes->pro_estdestino -= $produto->pro_consumo;
            }

            // === Segurança e sugestão bruta (sempre calculadas) ===
            $produto->pro_seguranca = ceil($produto->pro_consumo * ($prod['pct_seguranca'] / 100));
            $sugestaoBruta = ($produto->pro_consumo + $produto->pro_seguranca) - $produto->lotedes->pro_estdestino;

            // === Aplicar faixa de mínimo e máximo (somente se gestão de estoque) ===
            if ($prod['pre_gestaoestoque'] === 'S') {
                $produto->pro_minimo = ($prod['pre_mindiaanterior'] === 'S')
                    ? $produto->pro_consumo_diaant
                    : $prod['pre_minimo'];
                $produto->pro_consumo = ($prod['pre_mindiaanterior'] === 'S')
                    ? $produto->pro_consumo
                    : $prod['pre_sugerida'];

                $produto->pro_seguranca = ceil(floatval($produto->pro_consumo * ($prod['pct_seguranca'] / 100)));

                // debug('Consumo '.$produto->pro_consumo);
                // debug(('Segurança '.$produto->pro_seguranca));
                // debug('Est Destino '.$produto->lotedes->pro_estdestino);
                $sugestaoBruta = $produto->pro_consumo + $produto->pro_seguranca - $produto->lotedes->pro_estdestino;
                // debug('Sugest Ini '.$sugestaoBruta, true);
                if ($prod['pre_maxdiaanterior'] === 'S') {
                    $percentual = floatval($prod['pre_porcmaximo']) / 100;
                    $produto->pro_maximo = ceil($produto->pro_consumo * (1 + $percentual));
                } else {
                    $produto->pro_maximo = $prod['pre_maximo'];
                }
                // === Definir sugestão respeitando faixa
                if ($sugestaoBruta <= 0) {
                    $produto->pro_sugestao = 0;
                } elseif ($produto->pro_minimo == 0 && $produto->pro_maximo == 0) {
                    $produto->pro_sugestao = $sugestaoBruta;
                } elseif ($produto->pro_minimo > $produto->pro_maximo) {
                    $produto->pro_sugestao = min($sugestaoBruta, $produto->pro_maximo);
                } else {
                    $produto->pro_sugestao = max($produto->pro_minimo, $sugestaoBruta);
                    $produto->pro_sugestao = min($produto->pro_sugestao, $produto->pro_maximo);
                    $produto->pro_sugestao = max(0, ($produto->pro_sugestao - $produto->lotedes->pro_estdestino));
                }
            } else {
                // === Sem gestão de estoque: usar sugestão bruta diretamente
                $sugestaoBruta = ($produto->pro_consumo + $produto->pro_seguranca) - $produto->lotedes->pro_estdestino;
                $produto->pro_minimo = 0;
                $produto->pro_maximo = 0;
                $produto->pro_sugestao = max(0, $sugestaoBruta);
            }

            $produto->pro_sugestao = min($proEstDispon, $produto->pro_sugestao);
            $produto->pro_requisicao = $produtoreq['rep_quantia']??0;
        }

        return $produto->toArray();
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
        // debug($requisicoes, true);
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
                if(trim($item['lote']) != 'Sem Lote'){
                    $lote = $this->lote->getLoteCodproLote($item['cod_erp'], $item['lote']);
                } else {
                    $lote[0]['lot_id'] = null;
                }


                if (!$produto || !$lote) {
                    $ret['erro'] = true;
                    $ret['msg'] = "Produto ou lote não encontrado para o código: {$item['cod_erp']} ou lote: {$item['lote']}";
                    $db->transRollback();
                    echo json_encode($ret);
                    return;
                }

                // debug($item);
                $rep = [
                    'req_id' => $req_id,
                    'pro_id' => $produto[0]['pro_id'],
                    'lot_id' => $lote[0]['lot_id'],
                    'rep_multiplicador' => $item['multiplica'],
                    'rep_quantia' => $item['requisicao']
                ];
                // debug($rep);
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
                            'rep_multiplicador' => $item['multiplica'],
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
