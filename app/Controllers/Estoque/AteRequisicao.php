<?php

namespace App\Controllers\Estoque;

use App\Libraries\MyCampo;
use App\Controllers\BuscasSapiens;
use App\Controllers\BaseController;
use App\Models\Produt\ProdutLoteModel;
use App\Entities\Estoque\EntRequisicao;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Entities\Estoque\EntAteRequisicao;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
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
        $this->data          = session()->getFlashdata('dados_tela');
        $this->permissao     = $this->data['permissao'];
        $this->requisicao    = new EstoquRequisicaoModel();
        $this->reqproduto    = new EstoquRequisicaoProdutoModel();
        $this->reqprodutoate = new EstoquRequisicaoProdutoAtendimentoModel();
        $this->classes       = new ProdutClasseModel();
        $this->produtos      = new ProdutProdutoModel();
        $this->busca         = new BuscasSapiens();
        $this->deposito      = new EstoquDepositoModel();
        $this->lote          = new ProdutLoteModel();

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
        $dados_requis = filtrarPorPerfil($dados_requis);

        $req_ids_assoc = array_map(
            fn($r) => $r->req_id,
            $dados_requis
        );

        $log = buscaLogTabela('est_requisicao', $req_ids_assoc);

        $base_url = base_url($this->data['controler']);
        foreach ($dados_requis as $req) {
            // Verificar se o log já está disponível para esse req_id
            if ($req->req_id) {
                $req->usu_nome = $log[$req->req_id]['usua_alterou'] ?? '';

                // Concatenar o URL de forma mais eficiente
                $url_eti = base_url('/EtqProduto/Etiqueta/' . $req->req_id);
                $url_ate = $base_url . '/atende/' . $req->req_id;
                $url_imp = base_url('/CriaPdf2025/PrintRequisicaoEstoq/' . $req->req_id);

                $bt_ate = new MyCampo();
                $bt_ate->id = $bt_ate->nome = 'bt_atende';
                $bt_ate->classep  = 'btn btn-outline-success btn-sm border-0 mx-0 fs-0';
                $bt_ate->i_cone   = "<i class='fas fa-bell-concierge'></i>";
                $bt_ate->label    = '';
                $bt_ate->place    = 'Atendimento';
                $bt_ate->funcChan = "redireciona('{$url_ate}')";
                $btate = $bt_ate->crBotao();

                $bt_etq = new MyCampo();
                $bt_etq->id = $bt_etq->nome = 'bt_etiqueta';
                $bt_etq->classep  = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0';
                $bt_etq->i_cone   = "<i class='fas fa-tag'></i>";
                $bt_etq->label    = '';
                $bt_etq->place    = 'Etiquetas de Produtos';
                $bt_etq->funcChan = "redireciona('{$url_eti}')";
                $btetq = $bt_etq->crBotao();

                // Botão imprimir
                $btprn = '';
                if (trim($req->stt_impressao) === 'S') {
                    $bt_prn = new MyCampo();
                    $bt_prn->id = $bt_prn->nome = 'bt_print';
                    $bt_prn->classep  = 'btn btn-outline-dark btn-sm border-0 mx-0 fs-0';
                    $bt_prn->i_cone   = "<i class='fa-solid fa-print'></i>";
                    $bt_prn->label    = '';
                    $bt_prn->place    = 'Imprimir Requisição';
                    $bt_prn->funcChan = "openPDFModal('{$url_imp}','Imprimir Requisição')";
                    $btprn = $bt_prn->crBotao();
                }
                // Ações personalizadas
                $req->acao_person = [
                    $btate,
                    $btetq,
                    $btprn
                ];
            }
        }

        // debug($dados_requis, true);
        $this->data['edicao'] = false;

        // MOSTRA CONSULTA EM TODOS OS STATUS
        $this->data['allconsulta'] = true;

        $requis = [
            'data' => montaListaColunasEnt($this->data, 'req_id', $dados_requis, $campos[1]),
        ];

        cache()->save('requis', $requis, 60000);
        // }

        echo json_encode($requis);
    }

    public function show($id)
    {
        return redirect()->to('/Requisicao/show/' . $id);
    }

    public function edit($id, $show = false)
    {
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

        $log = buscaLog('est_requisicao', $id);
        $requisicao->usu_nome = $log['usua_alterou'] ?? '';

        $contRequis = new Requisicao();
        $secao[0] = 'Dados Gerais';
        $campos[0] = $contRequis->showCabecalhoSimples($requisicao);
        // Montar campos como no add()
        $entReq = new EntAteRequisicao();
        $fields = $entReq->defCampos($requisicao, $show);
        // // debug($fields, true);
        // $secao[0] = 'Dados Gerais';
        // $campos[0][0] = $fields['req_id'];
        // $campos[0][count($campos[0])] = $fields['req_data'];
        // $campos[0][count($campos[0])] = $fields['req_dataentrega'];
        // $campos[0][count($campos[0])] = $fields['tmo_id'];
        $campos[0][] = "<div class='col-6'>.</div>";
        $campos[0][] = $fields['lot_codbar'];

        $produtos = $this->requisicao->getRequisicaoProdutos($id);

        // array_column mudando para o OBJ
        $pro_ids = array_map(
            fn(object $p): int => $p->pro_id,
            $produtos
        );
        // debug($pro_ids);

        $dados_est_produto = $this->produtos->getProdutoEstoque($pro_ids, $requisicao->req_deporigem);
        // debug($produtos, false);
        // debug($dados_est_produto, true);

        // Transformar $produtos em um array indexado por pro_id
        $produtosIndexado = [];
        foreach ($produtos as $param) {
            $produtosIndexado[$param->pro_id][$param->lot_lote] = $param;
        }
        // debug($produtosIndexado, true);
        // Indexa os dados de estoque por pro_id para facilitar busca
        $estoqueIndexado = [];
        foreach ($dados_est_produto as $itemEstoque) {
            $estoqueIndexado[$itemEstoque->pro_id] = $itemEstoque;
        }


        $semsaldo = false;

        if ($semsaldo) {
            $ret['erro'] = true;
            $ret['msg']  = 37;
            session()->setFlashdata('msg', 37);
            return redirect()->to(site_url($this->data['controler']));
        } else {
            // Resultado final com todos os produtos
            $resultado = [];

            foreach ($produtosIndexado as $pro_id => $lotes) {
                foreach ($lotes as $produto) {
                    if (isset($estoqueIndexado[$pro_id])) {
                        // Mescla produto com dados de estoque (OBJ para array temporário)
                        $resultado[] = array_merge((array)$produto, (array)$estoqueIndexado[$pro_id]);
                    } else {
                        // Apenas dados do produto 
                        $resultado[] = (array)$produto;
                    }
                }
            }
            // debug($resultado, true);

            for ($p = 0; $p < count($resultado); $p++) {
                $prod = (object)$resultado[$p];
                $estoqueOrigem  = $this->busca->buscaEstoqueDeposito($requisicao->req_deporigem, $prod->pro_codpro);
                // debug($estoqueOrigem);
                $estoqueEncontrado = 0;

                foreach ($estoqueOrigem as $item) {
                    if (isset($item->codigoLote) && $item->codigoLote === $prod->lot_lote) {
                        $estoqueEncontrado = (int) str_replace('.', '', (string) $item->quantidadeEstoque);
                        break; // encontrou o lote, não precisa continuar
                    } else if (!isset($item->codigoLote)) {
                        $estoqueEncontrado = 1000000000;
                    }
                }

                $prod->estoque_origem = $estoqueEncontrado;

                if (!isset($prod->pre_cbfabricante)) {
                    // debug($prod);
                    $resultado[$p]['pre_cbfabricante'] = 'N';
                    $resultado[$p]['pre_undfabricante'] = 'N';
                    $resultado[$p]['pre_cblote'] = 'N';
                    $resultado[$p]['pre_undlote'] = 'N';

                    $prod->pre_cbfabricante = 'N';
                    $prod->pre_undfabricante = 'N';
                    $prod->pre_cblote = 'N';
                    $prod->pre_undlote = 'N';
                }

                $fields = $entReq->defCamposProdutoAte($prod);

                $url = base_url("OcoOcorrencia/addOutraTela/" . $this->data['tel_id'] . "/" . $id . "/" . $prod->pro_id);


                $bt_oco = new MyCampo();
                $bt_oco->id = $bt_oco->nome = 'bt_ocorre';
                $bt_oco->classep  = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0';
                $bt_oco->i_cone   = "<i class='fa-solid fa-exclamation-triangle'></i>";
                $bt_oco->label    = '';
                // $bt_oco->attrdata = ['data-id' => ];
                $bt_oco->place    = 'Gerar Ocorrência';
                $bt_oco->funcChan = "gerarOcorrencia({$this->data['tel_id']}, {$prod->rep_id})";
                $btoco = $bt_oco->crBotao();

                $resultado[$p]['bt_ocorre'] = $btoco;

                $resultado[$p]['rpa_cancelada']     = $fields['rpa_cancelada'];
                $resultado[$p]['rpa_atendida']      = $fields['rpa_atendida'];
                $resultado[$p]['rpa_cancelada_val'] = $prod->rpa_cancelada;
                $resultado[$p]['rpa_atendida_val']  = $prod->rpa_atendida;
                $resultado[$p]['saldo'] =
                    intval($resultado[$p]['rep_quantia']) -
                    (intval($prod->rpa_cancelada) + intval($prod->rpa_atendida));
                $resultado[$p]['estoque_origem']  = $prod->estoque_origem;
            }
            // debug('fim', true);
            // $secao[1] = 'Produtos';
            $campos[0][count($campos[0])] =
                view('partials/pw_produtos_requisicao', ['produtos' => $resultado]); // mesma estrutura do add

            $scripti = "<SCRIPT>jQuery('#lot_codbar').focus();</SCRIPT>";

            $this->data['desc_edicao']       = 'Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT).' '.fmtEtiquetaCor($requisicao->stt_cor, $requisicao->stt_nome, 1);
            $this->data['desc_metodo'] = ' ';
            $this->data['secoes']      = $secao;
            $this->data['campos']      = $campos;
            $this->data['destino']     = 'store';
            $this->data['scripts']     = 'my_requisicao';

            $this->data['script'] = $scripti;
            echo view('vw_edicao', $this->data);
        }
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
        $temprodutopendente = false;

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
                    if ($campo === "req_id") {
                        $dadosTemp[$campo] = $val;
                    }
                    if ($campo === "tmo_id") {
                        $dadosTemp[$campo] = $val;
                    }
                }

                // Faz a verificação
                $qtia       = (int)($dadosTemp['repqtia'] ?? 0);
                $cancelada  = (int)($dadosTemp['rpa_cancelada'] ?? 0);
                $atendida   = (int)($dadosTemp['rpa_atendida'] ?? 0);
                $somaStatus = $cancelada + $atendida;

                if ($qtia === $somaStatus) {
                    $dadosAgrupados[$id] = $dadosTemp;
                } else {
                    $temprodutopendente = true;
                }
            }
        }

        if (count($dadosAgrupados) === 0) {
            $msg = 7;
            session()->setFlashdata('msg', $msg);
            $ret['url']  = site_url($this->data['controler']);
            $ret['erro'] = false;
        } else {

            $ret['erro'] = false;
            $this->reqprodutoate->transStart();

            $movs = [];
            $qtiaatendida = 0;

            foreach ($dadosAgrupados as $campo => $val) {

                $sql_save = [
                    'rep_id'        => $val['repid'],
                    'pro_id'        => $val['proid'],
                    'rpa_cancelada' => $val['rpa_cancelada'],
                    'rpa_atendida'  => $val['rpa_atendida'],
                    'rpa_data'      => date('Y-m-d H:i:s'),
                ];

                $ate = $this->reqprodutoate
                    ->getProdutoRequisicaoAtendimento($val['repid'], $val['proid']);

                if ($ate) {
                    $salva = $this->reqprodutoate->update($ate[0]['rpa_id'], $sql_save);
                } else {
                    $salva = $this->reqprodutoate->insert($sql_save);
                }

                if (!$salva) {
                    $ret['erro'] = true;
                    $ret['msg']  = 'Erro ao gravar o Atendimento.';
                    $this->reqprodutoate->transRollback();
                    echo json_encode($ret);
                    return;
                } else {

                    $idmov = $val['tmo_id'];
                    $qtia  = $val['rpa_atendida'];
                    $qtiaatendida += intval($qtia);
                    $proid = $val['proid'];

                    $requisicao = $this->requisicao->getRequisicaoRep($val['repid'])[0];

                    $movs[] = [
                        'id'           => $idmov,
                        'qt'           => $qtia,
                        'msg'          => 'Atendimento de Requisição',
                        'pro_id'       => $proid,
                        'lot_lote'     => $requisicao->lot_lote,
                        'lot_validade' => $requisicao->lot_validade,
                    ];
                }
            }

            if (!$ret['erro']) {

                if ($qtiaatendida == 0 && !$temprodutopendente) {
                    $status = 7;
                } else {
                    $saldos = $this->reqprodutoate->getSaldoRequisicao($postado['req_id']);
                    $sald   = 0;
                    $status = 18;

                    for ($s = 0; $s < count($saldos); $s++) {
                        $sald += $saldos[$s]['saldo'];
                    }

                    if ($sald > 0) {
                        $status = 21;
                    }
                }

                $dadosReq = ['stt_id' => $status];

                $this->requisicao->transStart();
                $salvareq = $this->requisicao->update($postado['req_id'], $dadosReq);

                if ($salvareq) {
                    $this->reqprodutoate->transCommit();
                    $this->requisicao->transCommit();

                    $ret['msg'] = 'Atendimento gravada com sucesso!';
                    $ret['url'] = site_url($this->data['controler']);
                    session()->setFlashdata('msg', $ret['msg']);
                } else {
                    $ret['erro'] = true;
                    $ret['msg']  = 'Erro ao gravar Atendimento de Requisição.';
                    $this->reqprodutoate->transRollback();
                }
            }
        }

        echo json_encode($ret);
    }
}
