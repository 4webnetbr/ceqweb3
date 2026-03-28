<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Entities\Estoque\EntConfRequisicao;
use App\Entities\Estoque\EntRequisicao;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;

class ConfRequisicao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $requisicao;
    public $requisicaoproduto;
    public $requisicaoate;
    public $classes;
    public $produtos;
    public $ocorrencia;
    public $common;
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
        $this->ocorrencia    = new OcorreOcorrenciaModel();
        $this->common        = new CommonModel();

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

        $dados_requis = $this->requisicao->getRequisicaoConferencia(false, [18, 21, 24]);
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
                $url_con = $base_url . '/confere/' . $req->req_id;
                $url_imp = base_url('/CriaPdf2025/PrintRequisicaoEstoq/' . $req->req_id);

                $bt_con = new MyCampo();
                $bt_con->id = $bt_con->nome = 'bt_confere';
                $bt_con->classep  = 'btn btn-outline-success btn-sm border-0 mx-0 fs-0';
                $bt_con->i_cone   = "<i class='fas fa-check'></i>";
                $bt_con->label    = '';
                $bt_con->place    = 'Conferência';
                $bt_con->funcChan = "redireciona('{$url_con}')";
                $btcon = $bt_con->crBotao();

                // Gerar a ação do botão
                $btprn = '';
                $req->acao_person = [
                    $btcon,
                    $btprn
                ];
            }
        }

        $this->data['edicao'] = false;
        $this->data['allconsulta'] = true;

        // debug($dados_requis, true);
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
        $this->confere($id, $show = true);
    }
    /**
     * Atenndimento
     * atende
     *
     * @param mixed $id 
     * @return void
     */
    public function confere($id, $show = true)
    {
        $requisicao = $this->requisicao->getRequisicao($id);

        if (!$requisicao) {
            return redirectWithError($this->data['controler'], 41);
            // session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            // return redirect()->to(site_url($this->data['controler']));
        }
        $requisicao = $requisicao[0];

        $log = buscaLog('est_requisicao', $id);
        $requisicao->usu_nome = $log['usua_alterou'] ?? '';

        $contRequis = new Requisicao();
        $secao[0] = 'Dados Gerais';
        $campos[0] = $contRequis->showCabecalhoSimples($requisicao);

        // Montar campos como no add()
        $ent = new EntConfRequisicao((array) $requisicao, $show, 'conf');
        $fields = $ent->campos;
        // debug($fields, true);

        // $secao[0] = 'Dados Gerais';
        // $campos[0][] = $fields['req_id'];
        // $campos[0][] = $fields['req_data'];
        // $campos[0][] = $fields['req_dataentrega'];
        // $campos[0][] = $fields['tmo_id'];
        // $campos[0][] = "<div class='col-6'>.</div>";
        $campos[0][] = $fields['lot_codbar'];

        $produtos = $this->requisicao->getRequisicaoProdutos($id, 'conferencia');
        // debug($produtos, true);
        // array_column mudando para o OBJ
        $pro_ids = array_unique(array_map(
            fn($p) => $p->pro_id,
            $produtos
        ));



        $dados_est_produto = $this->produtos
            ->getProdutoEstoque($pro_ids, $requisicao->req_depdestino);

        // debug($dados_est_produto, true);
        // debug($dados_est_produto);

        // Transformar $produtos em um array indexado por pro_id
        $produtosIndexado = [];
        foreach ($produtos as $param) {
            $produtosIndexado[$param->pro_id] = $param;
        }

        // debug(count($produtosIndexado));
        // Array para o resultado final
        $resultado = [];

        if (count($dados_est_produto) > 0) {
            foreach ($dados_est_produto as $itemEstoque) {
                $pro_id = $itemEstoque->pro_id;

                if (isset($produtosIndexado[$pro_id])) {
                    // Mescla os dados de estoque + parâmetros
                    $resultado[] = (object) array_merge(
                        (array) $itemEstoque,
                        (array) $produtosIndexado[$pro_id]
                    );
                } else {
                    $resultado[] = $itemEstoque;
                }
            }
        } else {
            $resultado = $produtos;
        }

        // debug($resultado, true);

        // debug(count($resultado));
        for ($p = 0; $p < count($resultado); $p++) {
            $prod = $resultado[$p];
            // debug($prod, true);qs
            $val_cancelada  = $prod->rpa_cancelada  ?? 0;
            $val_atendida   = $prod->rpa_atendida   ?? 0;
            $val_conferida  = $prod->rpa_conferida  ?? 0;

            if (!isset($prod->pre_cbmisturador)) {
                $resultado[$p]->pre_cbmisturador = 'N';
                $resultado[$p]->pre_undmisturador = 'N';
                $prod->pre_cbmisturador = 'N';
                $prod->pre_undmisturador = 'N';
            }

            if (!isset($prod->pre_cbfabricante)) {
                $resultado[$p]->pre_cbfabricante = 'N';
                $resultado[$p]->pre_undfabricante = 'N';
                $resultado[$p]->pre_cblote = 'N';
                $resultado[$p]->pre_undlote = 'N';
                $prod->pre_cbfabricante = 'N';
                $prod->pre_undfabricante = 'N';
                $prod->pre_cblote = 'N';
                $prod->pre_undlote = 'N';
            }
            // debug($prod, true);
            $bt_oco = new MyCampo();
            $bt_oco->id = $bt_oco->nome = 'bt_ocorre';
            $bt_oco->classep  = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0';
            $bt_oco->i_cone   = "<i class='fa-solid fa-exclamation-triangle'></i>";
            $bt_oco->label    = '';
            $bt_oco->place    = 'Gerar Ocorrência';
            $bt_oco->funcChan = "gerarOcorrencia({$this->data['tel_id']}, {$prod->rep_id})";
            $btoco = $bt_oco->crBotao();

            $resultado[$p]->bt_ocorre = $btoco;
            $resultado[$p]->prc_codbar      = $prod->prc_codbar ?? null;
            $resultado[$p]->cla_insvis      = $prod->cla_insvis ?? 'N';
            $resultado[$p]->cla_insvisconf  = $prod->cla_insvisconf ?? 'N';
            $resultado[$p]->bt_insvis       = "";

            if ($prod->cla_insvis == 'S' && $prod->cla_insvisconf == 'S') {
                $bt_insvis = new MyCampo();
                $bt_insvis->id = $bt_insvis->nome = 'bt_insvis';
                $bt_insvis->classep  = 'btn btn-outline-black btn-sm border-0 mx-0 fs-0';
                $bt_insvis->i_cone   = "<i class='fa-solid fa-magnifying-glass-arrow-right'></i>";
                $bt_insvis->label    = '';
                $bt_insvis->place    = 'Inspeção Visual';
                // FIXADO TEL_ID 48 TELA DE INSPEÇÃO
                $bt_insvis->funcChan = "gerarInspecao(48, {$prod->rep_id})";
                $btinsvis = $bt_insvis->crBotao();
                $resultado[$p]->bt_insvis = $btinsvis;
            }

            $fields = $ent->defCamposProdutoConf($prod);

            $resultado[$p]->rpa_id = $prod->rpa_id;
            $resultado[$p]->rpa_cancelada = $prod->rpa_cancelada;
            $resultado[$p]->rpa_atendida = $fields['rpa_atendida'];
            $resultado[$p]->rpa_cancelada_val = $val_cancelada;
            $resultado[$p]->rpa_atendida_val = $val_atendida;
            $resultado[$p]->rpa_conferida = $fields['rpa_conferida'];
            $resultado[$p]->saldo = intval($val_atendida) - intval($prod->rpa_conferida);
        }
        foreach ($resultado as &$prod) {

            // CONTEXTO
            $prod->req_id = $requisicao->req_id;
            $prod->rpa_id        = $prod->rpa_id;

            // IDENTIFICAÇÃO DO PRODUTO
            $prod->pro_id       = $prod->pro_id       ?? 0;
            $prod->pro_codpro   = $prod->pro_codpro   ?? '';
            $prod->pro_despro   = $prod->pro_despro   ?? '';
            $prod->pro_qtdemb   = $prod->pro_qtdemb   ?? 1;

            // FABRICANTE
            $prod->pro_codbar_fabricante = $prod->pro_codbar_fabricante ?? '';
            $prod->fab_apeFab            = $prod->fab_apeFab            ?? '';

            // LOTE
            $prod->lot_codbar   = $prod->lot_codbar   ?? '';
            $prod->lot_lote     = $prod->lot_lote     ?? '';
            $prod->lot_validade = $prod->lot_validade ?? null;

            // PARAMETRIZAÇÕES (LF / LP / LM)
            $prod->pre_cbfabricante  = $prod->pre_cbfabricante  ?? 'N';
            $prod->pre_undfabricante = $prod->pre_undfabricante ?? 'N';
            $prod->pre_cblote        = $prod->pre_cblote        ?? 'N';
            $prod->pre_undlote       = $prod->pre_undlote       ?? 'N';
            $prod->pre_cbmisturador  = $prod->pre_cbmisturador  ?? 'N';
            $prod->pre_undmisturador = $prod->pre_undmisturador ?? 'N';

            // QUANTIDADES
            $prod->rep_id        = $prod->rep_id        ?? 0;
            $prod->rep_quantia   = $prod->rep_quantia   ?? 0;
            if ($prod->rpa_cancelada_val > 0) {
                $qtcaixa = ceil($prod->rpa_atendida_val / $prod->pro_qtdemb);
                $prod->qtd_caixa     = $qtcaixa;
            } else {
                $prod->qtd_caixa     = $prod->qtd_caixa     ?? 0;
            }

            // ATENDIMENTO / CONFERÊNCIA
            $prod->rpa_atendida       = $prod->rpa_atendida       ?? 0;
            $prod->rpa_cancelada      = $prod->rpa_cancelada      ?? 0;
            $prod->rpa_conferida      = $prod->rpa_conferida      ?? 0;
            $prod->rpa_atendida_val   = $prod->rpa_atendida_val   ?? '';
            $prod->rpa_cancelada_val  = $prod->rpa_cancelada_val  ?? '';

            // SALDO
            $prod->saldo = $prod->saldo ?? (
                intval($prod->rpa_atendida) - intval($prod->rpa_conferida)
            );

            // BOTÕES (SEGURANÇA)
            $prod->bt_ocorre = $prod->bt_ocorre ?? '';
            $prod->bt_insvis = $prod->bt_insvis ?? '';
            // debug($prod, true);
        }
        unset($prod);

        $campos[0][] =
            view('partials/pw_produtos_conferencia', ['produtos' => $resultado]);
        $campos[0][] = '</>';
        $this->data['desc_edicao']       = 'Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' ' . fmtEtiquetaCor($requisicao->stt_cor, $requisicao->stt_nome, 1);
        $this->data['desc_metodo'] = ' ';
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['scripts'] = 'my_requisicao';

        $this->data['script'] = "<SCRIPT>jQuery('#lot_codbar').focus();</SCRIPT>";
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
        $requisicao = $this->requisicao->getRequisicao($postado['req_id'])[0];
        $mudastatus = true;
        if ($requisicao->stt_id == 21) { // ATENDIDA PARCIAL NÃO MUDA STATUS
            $mudastatus = false;
            $status = 21;
        }


        $dadosAgrupados = [];
        $parcial = false;

        foreach ($postado as $key => $value) {
            if (preg_match('/^repid_(\d+)$/', $key, $matches)) {
                $id = $matches[1]; // Ex: 88, 89
                // debug($id);
                // OBJETO
                $dadosTemp = new \stdClass();
                $dadosTemp->repid = $value;

                // Pega os campos relacionados ao mesmo ID
                foreach ($postado as $campo => $val) {
                    if (str_ends_with($campo, "_$id") && $campo !== "repid_$id") {
                        $nomeCampo = substr($campo, 0, -strlen("_$id"));
                        // debug($nomeCampo);
                        $dadosTemp->$nomeCampo = $val;
                    }
                    if ($campo === "req_id") {
                        $dadosTemp->req_id = $val;
                    }
                    if ($campo === "tmo_id") {
                        $dadosTemp->tmo_id = $val;
                    }
                    if ($campo === "rpa_id") {
                        $dadosTemp->rpa_id = $val;
                    }
                }

                // Verificação
                $qtia       = (int)($dadosTemp->repqtia ?? 0);
                $cancelada  = (int)($dadosTemp->rpa_cancelada ?? 0);
                $atendida   = (int)($dadosTemp->rpa_atendida ?? 0);
                $conferida  = (int)($dadosTemp->rpa_conferida ?? 0);

                if ($conferida > 0) {
                    $dadosAgrupados[$id] = $dadosTemp;
                } else {
                    $parcial = true;
                }
            }
        }

        // debug($dadosAgrupados, true);

        if (count($dadosAgrupados) === 0) {
            $msg = 7;
            session()->setFlashdata('msg', $msg);
            $ret['url']  = site_url($this->data['controler']);
            $ret['erro'] = false;
        } else {

            $ret['erro'] = false;
            $this->requisicaoate->transStart();

            $movs = [];

            foreach ($dadosAgrupados as $campo => $val) {
                $sql_save = [
                    'rpa_conferida' => $val->rpa_conferida,
                    'rpa_data_conferencia' => date('Y-m-d H:i:s'),
                ];
                // debug($sql_save);
                // debug($val, true);
                $db      = \Config\Database::connect();
                $atualizar = $this->requisicaoate->update($val->rpaid, $sql_save);
                $lastSql = $this->requisicaoate->getLastQuery();
                // debug($lastSql, true);

                if (!$atualizar) {
                    $ret['erro'] = true;
                    $ret['msg']  = 'Erro ao gravar a Conferência.';
                    $this->requisicaoate->transRollback();
                    echo json_encode($ret);
                    return;
                } else {
                    $filtro = [
                        'req_id' => $val->req_id,
                        'pro_id' => $val->proid,
                        'lot_lote' => $val->lotlote
                    ];
                    $ocorrencias = $this->common->getRegFiltro('dbEstoque', 'est_requisicao_produto_ocorrencia', ['*'], $filtro);
                    // debug($ocorrencias, true);
                    for ($o = 0; $o < count($ocorrencias); $o++) {
                        $result = [];

                        foreach ($ocorrencias[0] as $key => $value) {
                            $newKey = str_starts_with($key, 'rpo_')
                                ? str_replace('rpo_', 'oco_', $key)
                                : $key;

                            $result[$newKey] = $value;
                        }
                        $result['stt_id'] = 28;
                        // debug($result);
                        $entity = new EntOcoOcorrencia($result);
                        // debug($entity, true);
                        $this->ocorrencia->save($entity);
                    }
                    // $ret['erro'] = true;
                    $idmov = $val->tmo_id;
                    $conf  = $val->rpa_conferida;
                    $proid = $val->proid;

                    $requisicao = $this->requisicao->getRequisicaoRep($val->repid)[0];

                    $movs[] = [
                        'id'           => $idmov,
                        'qt'           => $conf,
                        'msg'          => 'Conferência de Requisição',
                        'pro_id'       => $proid,
                        'lot_lote'     => $requisicao->lot_lote,
                        'lot_validade' => $requisicao->lot_validade,
                    ];
                }
            }

            if (!$ret['erro']) {
                if ($mudastatus) {
                    $status = 25;
                    if ($parcial) {
                        $status = 24; //conferida parcial
                    } else {
                        $insvis = retornaInsVis($postado['req_id']);
                        if ($insvis == 'N') {
                            $status = 5; // Concluída
                        }
                    }
                    $dadosReq = ['stt_id' => $status];

                    // VERIFICAR SE OS PRODUTOS DA REQUISIÇÃO EXIGEM INSPEÇÃO VISUAL E SE AINDA NÃO FOI REALIZADO
                    // CASO TODOS OS PRODUTOS, JA TENHAM SIDO FEITOS INSPEÇÃO VISUAL OU NÃO EXIJAM INSPEÇÃO VISUAL
                    // O STATUS DA REQUISIÇÃO PASSA A SER CONCLUÍDA
                    $this->requisicao->transStart();
                    $salvareq = $this->requisicao->update($postado['req_id'], $dadosReq);
                } else {
                    $salvareq = true;
                }

                if ($salvareq) {
                    $produtosreq = $this->requisicao->getRequisicaoProdutos($postado['req_id']);
                    if ($status == 5) {
                        foreach ($produtosreq as $val) {
                            $sql_save = [
                                'rpa_aprovada'  => $val->rep_quantia,
                                'rpa_data_inspecao'      => date('Y-m-d H:i:s'),
                            ];
                            $atualizar = $this->requisicaoate->update($val->rpaid, $sql_save);
                            // TODO CRIAR MOVIMENTAÇÃO DE ESTOQUE
                        }
                    }
                    $this->requisicaoate->transCommit();
                    $this->requisicao->transCommit();
                    $ret['msg'] = 'Conferência gravada com sucesso!';
                    $ret['url'] = site_url($this->data['controler']);
                    session()->setFlashdata('msg', $ret['msg']);
                } else {
                    $ret['erro'] = true;
                    $ret['msg']  = 'Erro ao gravar a Conferência.';
                    $this->requisicao->transRollback();
                    $this->requisicaoate->transRollback();
                }
            }
        }
        echo json_encode($ret);
    }
}
