<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Controllers\Estoque\Requisicao;
use App\Controllers\Notifica;
use App\Entities\Estoque\EntAteRequisicao;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;
use Config\Services;

class AteRequisicao extends BaseController
{
    public $data      = [];
    public $permissao = '';
    public $requisicao;
    public $reqproduto;
    public $tipomovimentacao;
    public $reqprodutoate;
    public $classes;
    public $produtos;
    public $lote;
    public $ocorrencia;
    public $common;
    public $busca;
    public $deposito;
    public $bt_envia;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data             = session()->getFlashdata('dados_tela');
        $this->permissao        = $this->data['permissao'];
        $this->requisicao       = new EstoquRequisicaoModel();
        $this->reqproduto       = new EstoquRequisicaoProdutoModel();
        $this->reqprodutoate    = new EstoquRequisicaoProdutoAtendimentoModel();
        $this->classes          = new ProdutClasseModel();
        $this->produtos         = new ProdutProdutoModel();
        $this->busca            = new BuscasSapiens();
        $this->deposito         = new EstoquDepositoModel();
        $this->lote             = new ProdutLoteModel();
        $this->tipomovimentacao = new EstoquTipoMovimentacaoModel();
        $this->ocorrencia       = new OcorreOcorrenciaModel();
        $this->common           = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'req_id');
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
        $campos       = montaColunasCampos($this->data, 'req_id');
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [4, 21]);
        // Filtra por perfil
        $dados_requis = filtrarPorPerfil($dados_requis);
        // Filtra por perfil do tipo de movimentação
        $dados_requis = filtrarPorPerfil($dados_requis, null, 'prf_id_tmo_a');

        $req_ids_assoc = array_map(
            fn($r) => $r->req_id,
            $dados_requis
        );

        $log = buscaLogTabela('est_requisicao', $req_ids_assoc);

        $base_url = base_url($this->data['controler']);
        foreach ($dados_requis as $req) {
            // Verificar se o log já está disponível para esse req_id
            if ($req->req_id) {
                $req->usu_nome = buscaUsuarioLog($log[$req->req_id]);

                // Concatenar o URL de forma mais eficiente
                $url_eti = base_url('/EtqProduto/Etiqueta/' . $req->req_id);
                $url_ate = $base_url . '/atende/' . $req->req_id;
                $url_imp = base_url('/CriaPdf2025/PrintRequisicaoEstoq/' . $req->req_id);

                $bt_ate           = new MyCampo();
                $bt_ate->id       = $bt_ate->nome       = 'bt_atende';
                $bt_ate->classep  = 'btn btn-outline-success btn-sm border-0 mx-0 fs-0';
                $bt_ate->i_cone   = "<i class='fas fa-bell-concierge'></i>";
                $bt_ate->label    = '';
                $bt_ate->place    = 'Atendimento';
                $bt_ate->funcChan = "redireciona('{$url_ate}')";
                $btate            = $bt_ate->crBotao();

                $bt_etq           = new MyCampo();
                $bt_etq->id       = $bt_etq->nome       = 'bt_etiqueta';
                $bt_etq->classep  = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0';
                $bt_etq->i_cone   = "<i class='fas fa-tag'></i>";
                $bt_etq->label    = '';
                $bt_etq->place    = 'Etiquetas de Produtos';
                $bt_etq->funcChan = "redireciona('{$url_eti}')";
                $btetq            = $bt_etq->crBotao();

                // Botão imprimir
                $btprn = '';
                if (trim($req->stt_impressao) === 'S') {
                    $bt_prn           = new MyCampo();
                    $bt_prn->id       = $bt_prn->nome       = 'bt_print';
                    $bt_prn->classep  = 'btn btn-outline-dark btn-sm border-0 mx-0 fs-0';
                    $bt_prn->i_cone   = "<i class='fa-solid fa-print'></i>";
                    $bt_prn->label    = '';
                    $bt_prn->place    = 'Imprimir Requisição';
                    $bt_prn->funcChan = "openPDFModal('{$url_imp}','Imprimir Requisição')";
                    $btprn            = $bt_prn->crBotao();
                }
                // Ações personalizadas
                $req->acao_person = [
                    $btate,
                    $btetq,
                    $btprn,
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
     * Exclusão
     * delete
     *
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        $ret = [];

        $requis = $this->requisicao->getRequisicao($id);

        if (! $requis) {
            return redirectWithError($this->data['controler'], 41);

            // return view('errors/vw_semregistro', [
            //     'mensagem' => 'Requisição não encontrada'
            // ]);
        }
        $status = $requis[0]->stt_id;

        // if ($status == 6) {
        try {
            // Soft delete
            $this->requisicao->delete($id);
            $ret['erro'] = false;
            $ret['msg']  = 'Requisição Excluída com Sucesso';
            session()->setFlashdata('msg', 'Requisição Excluída com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
        // } else {
        //     $ret['erro'] = true;
        //     $ret['msg']  = 3;
        // }

        echo json_encode($ret);
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
        $requisicao = $this->requisicao->getRequisicao($id);

        if (! $requisicao) {
            return redirectWithError($this->data['controler'], 41);
            // session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            // return redirect()->to(site_url($this->data['controler']));
        }
        $requisicao = $requisicao[0];

        $log                  = buscaLog('est_requisicao', $id);
        $requisicao->usu_nome = $log['usua_alterou'] ?? '';

        $contRequis = new Requisicao();
        $secao[0]   = 'Dados Gerais';
        $campos[0]  = $contRequis->showCabecalhoSimples($requisicao);
        $entReq     = new EntAteRequisicao();

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
                        $resultado[] = array_merge((array) $produto, (array) $estoqueIndexado[$pro_id]);
                    } else {
                        // Apenas dados do produto
                        $resultado[] = (array) $produto;
                    }
                }
            }
            // debug($resultado, true);

            for ($p = 0; $p < count($resultado); $p++) {
                $prod = (object) $resultado[$p];
                envia_msg_ws($this->data['controler'], "Buscando estoque de origem", 'MsgServer', session()->get('usu_id'), 1);
                $estoqueOrigem = $this->busca->buscaEstoqueDeposito(
                    $requisicao->req_deporigem,
                    $prod->pro_codpro
                );

                // 🔒 Normaliza para array
                if ($estoqueOrigem instanceof \stdClass) {
                    $estoqueOrigem = [$estoqueOrigem];
                } elseif (! is_array($estoqueOrigem)) {
                    $estoqueOrigem = [];
                }

                $estoqueEncontrado = 0;

                foreach ($estoqueOrigem as $item) {
                    if (
                        isset($item->codigoLote) &&
                        ($item->codigoLote === $prod->lot_lote || trim($item->codigoLote) === 'Sem Lote')
                    ) {
                        $estoqueEncontrado = (int) str_replace('.', '', (string) $item->quantidadeEstoque);
                        break;
                    }

                    if (! isset($item->codigoLote)) {
                        $estoqueEncontrado = 1000000000;
                        break;
                    }
                }

                $prod->estoque_origem = $estoqueEncontrado;

                envia_msg_ws($this->data['controler'], "Definindo os Campos", 'MsgServer', session()->get('usu_id'), 1);
                if (! isset($prod->pre_cbfabricante)) {
                    // debug($prod);
                    $resultado[$p]['pre_cbfabricante']  = 'N';
                    $resultado[$p]['pre_undfabricante'] = 'N';
                    $resultado[$p]['pre_cblote']        = 'N';
                    $resultado[$p]['pre_undlote']       = 'N';

                    $prod->pre_cbfabricante  = 'N';
                    $prod->pre_undfabricante = 'N';
                    $prod->pre_cblote        = 'N';
                    $prod->pre_undlote       = 'N';
                }

                $fields = $entReq->defCamposProdutoAte($prod);

                $url = base_url("OcoOcorrencia/addOutraTela/" . $this->data['tel_id'] . "/" . $id . "/" . $prod->pro_id);

                $bt_oco          = new MyCampo();
                $bt_oco->id      = $bt_oco->nome      = 'bt_ocorre';
                $bt_oco->classep = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0';
                $bt_oco->i_cone  = "<i class='fa-solid fa-exclamation-triangle'></i>";
                $bt_oco->label   = '';
                // $bt_oco->attrdata = ['data-id' => ];
                $bt_oco->place    = 'Gerar Ocorrência';
                $bt_oco->funcChan = "gerarOcorrencia({$this->data['tel_id']}, {$prod->rep_id})";
                $btoco            = $bt_oco->crBotao();

                $resultado[$p]['bt_ocorre'] = $btoco;

                $resultado[$p]['rpa_cancelada']     = $fields['rpa_cancelada'];
                $resultado[$p]['rpa_atendida']      = $fields['rpa_atendida'];
                $resultado[$p]['rpa_cancelada_val'] = $prod->rpa_cancelada;
                $resultado[$p]['rpa_atendida_val']  = $prod->rpa_atendida;
                $resultado[$p]['saldo']             =
                    intval($resultado[$p]['rep_quantia']) -
                    (intval($prod->rpa_cancelada) + intval($prod->rpa_atendida));
                $resultado[$p]['estoque_origem'] = $prod->estoque_origem;
            }
            // debug('fim', true);
            // $secao[1] = 'Produtos';
            // envia_msg_ws($this->data['controler'], "Preparando a tela", 'MsgServer', session()->get('usu_id'), 1);

            $fields      = $entReq->defCampos($requisicao, $show);
            $dispositivo = Services::device();
            if ($dispositivo->isTablet()) {
                // if (1 == 1) {
                $secao[1]    = 'Produtos';
                $campos[1][] = $fields['lot_codbar'];
                $campos[1][] =
                    view('partials/pw_produtos_requisicao', ['produtos' => $resultado, 'maxHeig' => '60vh']); // mesma estrutura do add
            } else {
                $campos[0][] = $fields['lot_codbar'];
                $campos[0][] =
                    view('partials/pw_produtos_requisicao', ['produtos' => $resultado, 'maxHeig' => '49vh']); // mesma estrutura do add
            }

            $scripti = "<SCRIPT>jQuery('#lot_codbar').focus();</SCRIPT>";

            $this->data['desc_edicao'] = 'Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' ' . fmtEtiquetaCor($requisicao->stt_cor, $requisicao->stt_nome, 1);
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

        $dadosAgrupados     = [];
        $temprodutopendente = false;
        $totalatend         = 0;

        envia_msg_ws($this->data['controler'], "Preparando dados recebidos", 'MsgServer', session()->get('usu_id'), 1);

        foreach ($postado as $key => $value) {
            if (preg_match('/^repid_(\d+)$/', $key, $matches)) {
                $id = $matches[1]; // Ex: 88, 89

                // Inicializa temporário para os dados desse ID
                $dadosTemp = ['repid' => $value];

                // Pega os campos relacionados ao mesmo ID
                foreach ($postado as $campo => $val) {
                    if (str_ends_with($campo, "_$id") && $campo !== "repid_$id") {
                        $nomeCampo             = substr($campo, 0, -strlen("_$id"));
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
                $qtia       = (int) ($dadosTemp['repqtia'] ?? 0);
                $cancelada  = (int) ($dadosTemp['rpa_cancelada'] ?? 0);
                $atendida   = (int) ($dadosTemp['rpa_atendida'] ?? 0);
                $somaStatus = $cancelada + $atendida;
                $totalatend += $atendida;

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
            $db = \Config\Database::connect();
            $db->transBegin();

            $ret['erro'] = false;

            $movs         = [];
            $qtiaatendida = 0;

            envia_msg_ws($this->data['controler'], "Gravando Atendimento", 'MsgServer', session()->get('usu_id'), 1);
            foreach ($dadosAgrupados as $campo => $val) {
                $ate = $this->reqprodutoate
                    ->getProdutoRequisicaoAtendimento($val['repid'], $val['proid']);

                if (! $ate) {
                    $sql_save = [
                        'rep_id'        => $val['repid'],
                        'pro_id'        => $val['proid'],
                        'rpa_cancelada' => $val['rpa_cancelada'],
                        'rpa_atendida'  => $val['rpa_atendida'],
                        'rpa_data'      => date('Y-m-d H:i:s'),
                    ];
                    if ($val['rpa_cancelada'] > 0 && $val['rpa_atendida'] == 0) {
                        /** Todos os ítens foram cancelados */
                        $sql_save['rpa_conferida']        = -1;
                        $sql_save['rpa_data_conferencia'] = date('Y-m-d H:i:s');
                        $sql_save['rpa_aprovada']         = -1;
                        $sql_save['rpa_data_inspecao']    = date('Y-m-d H:i:s');
                    }
                    $qtiaatendida += (int) $val['rpa_atendida'];
                    //     $salva = $this->reqprodutoate->update($ate[0]['rpa_id'], $sql_save);
                    // } else {
                    $salva  = $this->reqprodutoate->insert($sql_save);
                    // }

                    if (! $salva) {
                        $ret['erro'] = true;
                        $ret['msg']  = 'Erro ao gravar o Atendimento.';
                        $db->transRollback();
                        echo json_encode($ret);
                        return;
                    } else {
                        if ($val['rpa_atendida'] > 0) {
                            $idmov = $postado['tmo_id'];
                            $qtia  = $val['rpa_atendida'];
                            $proid = $val['proid'];
                            $repid = $val['repid'];

                            $movs[] = [
                                'id'      => $idmov,
                                'qt'      => $qtia,
                                'msg'     => 'Atendimento de Requisição',
                                'pro_id'  => $proid,
                                'rep_id'  => $repid,
                                'reserva' => "D",
                            ];
                        }
                    }
                }
            }

            if (! $ret['erro']) {
                envia_msg_ws($this->data['controler'], "Acertando Status", 'MsgServer', session()->get('usu_id'), 1);
                $nreq      = str_pad($postado['req_id'], 6, '0', STR_PAD_LEFT);
                $destNotif = 'Estoque\ConfRequisicao';
                $msgsocket = "A Requisição Nº " . $nreq . " foi Atendida!";
                if ($qtiaatendida == 0 && ! $temprodutopendente && $totalatend == 0) {
                    $status    = 7; // CANCELADA
                    $destNotif = 'Estoque\Requisicao';
                    $msgsocket = "A Requisição Nº " . $nreq . " foi Cancelada!";
                } else {
                    $saldos = $this->reqprodutoate->getSaldoRequisicao($postado['req_id']);
                    $sald   = 0;
                    $status = 18;
                    for ($s = 0; $s < count($saldos); $s++) {
                        $sald += $saldos[$s]['saldo'];
                    }
                    if ($sald > 0) {
                        $status = 21;
                    } else {
                        $insvis  = retornaInsVis($postado['req_id']);
                        $tipomov = $this->tipomovimentacao->getTipoMovimentacao($postado['tmo_id'])[0];
                        if ($tipomov->tmo_conferencia == 'N') {
                            $status    = 25; // Conferida
                            $destNotif = 'Preproces/InspecaoProd';
                            $msgsocket = "A Requisição Nº " . $nreq . " foi Conferida!";
                            if ($insvis->temN || $postado['tmo_id'] != 8) {
                                $status    = 5; // Concluída
                                $destNotif = 'Estoque\Requisicao';
                                $msgsocket = "A Requisição Nº " . $nreq . " foi Concluída!";
                            }
                        }
                    }
                }

                $dadosReq  = ['stt_id' => $status];

                // $this->requisicao->transStart();
                $salvareq = $this->requisicao->update($postado['req_id'], $dadosReq);

                if ($salvareq) {
                    /**Gera Notificação para os Usuários que Atendem Requisição*/
                    $requisicao = $postado['req_id'];
                    $notif      = new Notifica();
                    $usuario    = session()->get('usu_id');
                    $notif->gravaNotifica($destNotif, $usuario, $requisicao, $msgsocket, 'C');

                    $produtosreq = $this->requisicao->getRequisicaoProdutos($postado['req_id']);
                    if ($status == 25) {
                        foreach ($produtosreq as $val) {
                            $sql_save = [
                                'rep_id'               => $val->rep_id,
                                'pro_id'               => $val->pro_id,
                                'rpa_cancelada'        => 0,
                                'rpa_atendida'         => $val->rep_quantia,
                                'rpa_conferida'        => $val->rep_quantia,
                                'rpa_data'             => date('Y-m-d H:i:s'),
                                'rpa_data_conferencia' => date('Y-m-d H:i:s'),
                            ];
                            $ate = $this->reqprodutoate
                                ->getProdutoRequisicaoAtendimento($val->repid, $val->proid);

                            if ($ate) {
                                $salva = $this->reqprodutoate->update($ate[0]['rpa_id'], $sql_save);
                            } else {
                                $salva = $this->reqprodutoate->insert($sql_save);
                            }
                        }
                    } else if ($status == 5) {
                        foreach ($produtosreq as $val) {
                            $sql_save = [
                                'rep_id'               => $val->rep_id,
                                'pro_id'               => $val->pro_id,
                                'rpa_cancelada'        => 0,
                                'rpa_atendida'         => $val->rep_quantia,
                                'rpa_conferida'        => $val->rep_quantia,
                                'rpa_data'             => date('Y-m-d H:i:s'),
                                'rpa_data_conferencia' => date('Y-m-d H:i:s'),
                                'rpa_aprovada'         => -1,
                                'rpa_data_inspecao'    => date('Y-m-d H:i:s'),
                            ];
                            $ate = $this->reqprodutoate
                                ->getProdutoRequisicaoAtendimento($val->rep_id, $val->pro_id);

                            if ($ate) {
                                $salva = $this->reqprodutoate->update($ate[0]['rpa_id'], $sql_save);
                            } else {
                                $salva = $this->reqprodutoate->insert($sql_save);
                            }
                            $idmov = $postado['tmo_id'];
                            $qtia  = $val->rpa_atendida;
                            $proid = $val->pro_id;
                            $repid = $val->rep_id;

                            $movs[] = [
                                'id'      => $idmov,
                                'qt'      => $qtia,
                                'msg'     => 'Conclusão Automática de Requisição',
                                'pro_id'  => $proid,
                                'rep_id'  => $repid,
                                'reserva' => "O",
                            ];
                        }
                    }
                    if (! empty($movs)) {
                        // debug($movs);
                        envia_msg_ws($this->data['controler'], "Gerando Movimentações de Estoque", 'MsgServer', session()->get('usu_id'), 1);
                        $movim = geraMovimentoRequisicoes($movs, $this->data['controler']);
                        if ($movim['status'] == 'Erro') {
                            $db->transRollback();
                            $ret['erro'] = true;
                            $ret['msg']  = $movim['mensagem'];
                        }
                    }
                    if (! $ret['erro']) {
                        envia_msg_ws($this->data['controler'], "Processando Ocorrências", 'MsgServer', session()->get('usu_id'), 1);
                        foreach ($produtosreq as $val) {
                            $filtro = [
                                'req_id'   => $postado['req_id'],
                                'pro_id'   => $val->pro_id,
                                'lot_lote' => $val->lot_lote,
                                'oco_id'   => null,
                            ];
                            $ocorrencias = $this->common->getRegFiltro('dbEstoque', 'vw_est_requisicao_produto_ocorrencia_relac', ['*'], $filtro);
                            // debug('Ocorrencias');
                            // debug($ocorrencias);
                            for ($o = 0; $o < count($ocorrencias); $o++) {
                                $result = [];
                                foreach ($ocorrencias[0] as $key => $value) {
                                    $result[$key] = $value;
                                }
                                $result['stt_id'] = 28;
                                // debug($result);
                                $entity = new EntOcoOcorrencia($result);
                                // debug($entity);
                                $salvaoco = $this->ocorrencia->save($entity);
                                // debug($salvaoco);
                                if ($salvaoco) {
                                    // pega os dados já persistidos
                                    $idoco                = $this->ocorrencia->getInsertID();
                                    $data                 = $entity->toArray();
                                    $lote                 = $this->lote->getLoteId($val->lot_lote)[0];
                                    $data['oco_id']       = $idoco;
                                    $data['lot_lote']     = $lote->lot_lote;
                                    $data['lot_validade'] = $lote->lot_validade;
                                    // debug($data);
                                    $ocoService = service('ocorrenciaService');
                                    $ocoService->processAfterSave($data);
                                    // debug($ocoService);
                                    $sql = [
                                        'oco_id' => $idoco,
                                    ];
                                    $this->common->updateReg('dbEstoque', 'est_requisicao_produto_ocorrencia', 'rpo_id = ' . $ocorrencias[0]['rpo_id'], $sql);
                                } else {
                                    $errors      = $this->ocorrencia->errors();
                                    $ret['erro'] = true;
                                    $ret['msg']  = $errors;
                                    $db->transRollback();
                                    break;
                                }
                            }
                        }
                        if (! $ret['erro']) {
                            $db->transCommit();
                            $ret['msg'] = 'Atendimento gravada com sucesso!';
                            $ret['url'] = site_url($this->data['controler']);
                            session()->setFlashdata('msg', $ret['msg']);
                        }
                    }
                } else {
                    $ret['erro'] = true;
                    $ret['msg']  = 'Erro ao gravar Atendimento de Requisição.';
                    $db->transRollback();
                }
            }
        }

        echo json_encode($ret);
    }
}
