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

class Inspecao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $requisicao;
    public $requisicaoproduto;
    public $requisicaoate;
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
        $this->requisicaoate   = new EstoquRequisicaoProdutoAtendimentoModel();
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
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [25]);
        $dados_requis = filtrarRequisicoesPorPerfil($dados_requis);
        // debug($dados_requis, true);
        if($dados_requis){
            $req_ids_assoc = array_column($dados_requis, 'req_id');
            $log = buscaLogTabela('est_requisicao', $req_ids_assoc);

            $base_url = base_url($this->data['controler']);
            foreach ($dados_requis as &$req) {
                // Verificar se o log já está disponível para esse ana_id
                if ($req['req_id']) {
                    $req['usu_nome'] = $log[$req['req_id']]['usua_alterou'] ?? '';
                    // Concatenar o URL de forma mais eficiente
                    $url_con = $base_url .'/inspeciona/' . $req['req_id'];
                    // Gerar a ação do botão
                    $req['acao_person'] = [
                        "<button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0' 
                data-mdb-toggle='tooltip' data-mdb-placement='top' 
                title='Conferência' onclick='redireciona(\"$url_con\")'>
                <i class='fas fa-check'></i></button>",
                    ];
                }
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

    public function show($id){
        return redirect()->to('/Requisicao/show/'.$id);
    }

    public function edit($id, $show = false){
        $this->inspeciona($id, $show = true);
    }
    /**
     * Atenndimento
     * atende
     *
     * @param mixed $id 
     * @return void
     */
    public function inspeciona($id, $show = true)
    {
        $requisicao = $this->requisicao->getRequisicao($id)[0];
        
        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }
        
        // Montar campos como no add()
        $fields = $this->requisicao->defCampos($requisicao, $show, 'conf');
        // debug($fields, true);
        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['req_id']; 
        $campos[0][] = $fields['req_numero']; 
        $campos[0][] = $fields['req_data'];
        $campos[0][] = $fields['req_dataentrega'];
        $campos[0][] = $fields['tmo_id'];
        $campos[0][] = "<div class='col-6'>.</div>";
        // $campos[0][count($campos[0])] = $fields['lot_codbar'];
        
        $produtos = $this->requisicao->getRequisicaoProdutos($id);
        // debug($produtos, true);
        $filtrado = array_filter($produtos, function ($item) {
            return ($item['rpa_atendida'] + $item['rpa_cancelada']) == $item['rep_quantia'];
        });
        $produtos = $filtrado;
        $pro_ids = array_unique(array_column($produtos, 'pro_id'));
        $dados_est_produto = $this->produtos->getProdutoEstoqueCeqweb($pro_ids, $requisicao['req_depdestino']);
        // debug($dados_est_produto);
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
            if(!isset($prod['pre_cbmisturador'])){
                $resultado[$p]['pre_cbmisturador'] = 'N';
                $resultado[$p]['pre_undmisturador'] = 'N';
                $prod['pre_cbmisturador'] = 'N';
                $prod['pre_undmisturador'] = 'N';
            }
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
            $resultado[$p]['bt_ocorre'] = "<button class='btn btn-outline-warning btn-sm border-0 mx-0 fs-0' 
            data-mdb-toggle='tooltip' data-mdb-placement='top' 
            title='Gerar Ocorrência'>
            <i class='fas fa-exclamation-triangle'></i></button>";
            
            $resultado[$p]['prc_codbar'] = $prod['prc_codbar'];
            $resultado[$p]['cla_insvis'] = $prod['cla_insvis'];
            $resultado[$p]['cla_insvisconf'] = $prod['cla_insvisconf'];
            $resultado[$p]['bt_insvis'] = "";
            if($prod['cla_insvis'] == 'S' && $prod['cla_insvisconf'] == 'S'){
                $resultado[$p]['bt_insvis'] = "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0' 
                data-mdb-toggle='tooltip' data-mdb-placement='top' 
                title='Inspeção Visual'>
                <i class='fa-solid fa-magnifying-glass-arrow-right'></i></button>";
            }
            $fields = $this->requisicao->defCamposProdutoConf($prod);
            $resultado[$p]['rpa_cancelada'] = $prod['rpa_cancelada'];
            $resultado[$p]['rpa_atendida'] = $fields['rpa_atendida'];
            $resultado[$p]['rpa_cancelada_val'] = $prod['rpa_cancelada'];
            $resultado[$p]['rpa_atendida_val'] = $prod['rpa_atendida'];
            $resultado[$p]['rpa_conferida'] = $fields['rpa_conferida'];
            $resultado[$p]['saldo'] = intval($prod['rpa_atendida']) - intval($prod['rpa_conferida']);
        }
        // $secao[1] = 'Produtos';
        $campos[0][] = view('partials/pw_produtos_inspecao',['produtos' => $resultado]); // mesma estrutura do add()


        $this->data['title']     = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['desc_metodo']     = ' Inspeção de ';
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = 'store'; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';

        $this->data['script']   = "<SCRIPT>jQuery('#lot_codbar').focus();</SCRIPT>";
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
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $postado = $this->request->getPost();
        // debug($postado, true);

        $dadosAgrupados = [];

        foreach ($postado as $key => $value) {
            if (preg_match('/^repid_(\d+)$/', $key, $matches)) {
                $id = $matches[1]; // Ex: 88, 89
                // debug($id);
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
                    // debug($dadosTemp);
                }

                // Faz a verificação: repqtia == (rpa_cancelada + rpa_atendida)
                $qtia        = (int)($dadosTemp['repqtia'] ?? 0);
                $cancelada   = (int)($dadosTemp['rpa_cancelada'] ?? 0);
                $atendida    = (int)($dadosTemp['rpa_atendida'] ?? 0);
                $conferida    = (int)($dadosTemp['rpa_conferida'] ?? 0);

                if ($atendida === $conferida) {
                    // Somente adiciona se a condição for satisfeita
                    $dadosAgrupados[$id] = $dadosTemp;
                }
            }
        }
        // debug($dadosAgrupados, true);
        if(count($dadosAgrupados) === 0){
            $msg            = 7;
            session()->setFlashdata('msg', $msg);
            $ret['url'] = site_url($this->data['controler']);
            $ret['erro'] = false;
        } else {
            // debug($dadosAgrupados, true);
            $ret['erro'] = false;

            $this->requisicaoate->transStart(); // Início da transação

            $movs = [];

            foreach ($dadosAgrupados as $campo => $val) {
                $sql_save = [
                    'rpa_conferida' => $val['rpa_conferida'],
                    'rpa_data_conferencia' => date('Y-m-d H:i:s'),
                ];
                if (!$this->requisicaoate->update($val['repid'], $sql_save)) {
                    $ret['erro'] = true;
                    $ret['msg'] = 'Erro ao gravar a Conferência.';
                    $this->requisicaoate->transRollback();
                    echo json_encode($ret);
                    return;
                } else {
                    // pega o movimento
                    $idmov  = $val['tmo_id'];
                    $qtia   = $val['rpa_atendida'];
                    $conf   = $val['rpa_conferida'];
                    $proid  = $val['proid'];
                    $requisicao = $this->requisicao->getRequisicaoRep($val['repid'])[0];
                    // cria os movimentos
                    $movs[] = [ // A QUANTIDADE É A QUANTIDADE CONFERIDA
                        'id'  => $idmov,
                        'qt'  => $conf,
                        'msg' => 'Conferência de Requisição',
                        'pro_id' => $proid,
                        'lot_lote' => $requisicao['lot_lote'],
                        'lot_validade' => $requisicao['lot_validade'],
                    ];
                }
            }
            if (!$ret['erro']) {
                // if (!empty($movs)) {
                //     cache()->clean();
                //     // debug($movs);
                //     $movim = geraMovimentoRequisicoes($movs, $this->data, 'C');
                //     if($movim['status'] == 'Erro'){
                //         $ret['erro'] = true;
                //         $ret['msg'] = $movim['mensagem'];
                //         // debug($ret);
                //     }
                // }
                if(!$ret['erro']){
                    $status = 5;
                    $dadosReq = [
                        'stt_id' => $status
                    ];
                    $this->requisicao->transStart();
                    $salvareq = $this->requisicao->update($postado['req_id'], $dadosReq);
                    if($salvareq){
                        $this->requisicaoate->transCommit();
                        $this->requisicao->transCommit();

                        $ret['msg'] = 'Conferência gravada com sucesso!';
                        $ret['url'] = site_url($this->data['controler']);
                        session()->setFlashdata('msg', $ret['msg']);
                    } else {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Erro ao gravar a Conferência.';
                        $this->requisicaoate->transRollback();
                    }
                }
            }
            echo json_encode($ret);
            // cache()->clean();
        }
    }
}
