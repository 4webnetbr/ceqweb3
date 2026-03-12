<?php

namespace App\Controllers\Preproces;

use App\Controllers\BaseController;
use App\Controllers\Estoque\Requisicao;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Preproces\EntInspecaoProd;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;

class InspecaoProd extends BaseController
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
        $this->data          = session()->getFlashdata('dados_tela');
        $this->permissao     = $this->data['permissao'];
        $this->requisicao    = new EstoquRequisicaoModel();
        $this->requisicaoate = new EstoquRequisicaoProdutoAtendimentoModel();
        $this->classes       = new ProdutClasseModel();
        $this->produtos      = new ProdutProdutoModel();

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
        if ($dados_requis) {
            $req_ids_assoc = array_map(fn($r) => $r->req_id, $dados_requis);

            $log = buscaLogTabela('est_requisicao', $req_ids_assoc);
            $base_url = base_url($this->data['controler']);
            foreach ($dados_requis as $req) {
                if ($req->req_id) {
                    $req->usu_nome = $log[$req->req_id]['usua_alterou'] ?? '';

                    $url_ins = $base_url . '/edit/' . $req->req_id;
                    // Gerar a ação do botão
                    $bt_ins = new MyCampo();
                    $bt_ins->id = $bt_ins->nome = 'bt_inspecao';
                    $bt_ins->classep  = 'btn btn-outline-success btn-sm border-0 mx-0 fs-0';
                    $bt_ins->i_cone   = "<i class='fab fa-searchengin'></i>";
                    $bt_ins->label    = '';
                    $bt_ins->place    = 'Inpeção de Produtos';
                    $bt_ins->funcChan = "redireciona('{$url_ins}')";
                    $btins = $bt_ins->crBotao();

                    $req->acao_person = [
                        $btins,
                    ];
                }
            }
        }
        // debug($dados_requis, true);
        $this->data['edicao'] = false;
        $requis = [
            'data' => montaListaColunasEnt($this->data, 'req_id', $dados_requis, $campos[1]),
        ];
        // cache()->save('requis', $requis, 60000);
        // }

        echo json_encode($requis);
    }

    public function show($id)
    {
        return redirect()->to('/Requisicao/show/' . $id);
    }

    public function edit($id, $show = true)
    {
        $requisicao = $this->requisicao->getRequisicao($id)[0];

        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }
        $log = buscaLog('est_requisicao', $id);
        $requisicao->usu_nome = $log['usua_alterou'] ?? '';
        // debug($requisicao, true);
        $contRequis = new Requisicao();
        $secao[0] = 'Dados Gerais';
        $campos[0] = $contRequis->showCabecalhoSimples($requisicao);

        $entRequisicao    = new EntInspecaoProd((array) $requisicao, $show, 'conf');

        $produtos = $this->requisicao->getRequisicaoProdutos($id);

        $filtrado = array_filter($produtos, function ($item) {
            return ($item->rpa_atendida + $item->rpa_cancelada) == $item->rep_quantia;
        });
        $produtos = $filtrado;
        $pro_ids = array_unique(array_map(fn($p) => $p->pro_id, $produtos));

        $resultado = $produtos;

        // Loop de tratamento visual e funcional de cada produto
        for ($p = 0; $p < count($resultado); $p++) {
            $prod = $resultado[$p];

            // Inicializa flags padrão de conferência
            $prod->pre_cbmisturador  ??= 'N';
            $prod->pre_undmisturador ??= 'N';
            $prod->pre_cbfabricante  ??= 'N';
            $prod->pre_undfabricante ??= 'N';
            $prod->pre_cblote        ??= 'N';
            $prod->pre_undlote       ??= 'N';

            $prod->bt_insvis = '';
            $prod->bt_ok = '';
            if ($prod->cla_insvis == 'S' && $prod->cla_insvisconf == 'S') {
                $bt_insvis = new MyCampo();
                $bt_insvis->id = $bt_insvis->nome = "bt_insvis[$p]";
                $bt_insvis->classep  = 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0 float-start';
                $bt_insvis->i_cone   = "<i class='fab fa-searchengin'></i>";
                $bt_insvis->label    = '';
                $bt_insvis->place    = 'Inspeção';
                $bt_insvis->funcChan = "gerarInspecao({$this->data['tel_id']}, {$prod->rep_id})";
                $prod->bt_insvis = $bt_insvis->crBotao();

                $okop[0] = 'Não';
                $okop[1] = 'Sim';
                $bt_ok = new MyCampo();
                $bt_ok->id = $bt_ok->nome = "bt_ok[$p]";
                $bt_ok->dispForm  = 'float-end';
                $bt_ok->classep  = 'semmb';
                $bt_ok->label    = '';
                $bt_ok->valor   = 1;
                $bt_ok->selecionado   = '';
                $bt_ok->place    = 'Inspeção';
                $bt_ok->funcChan = "mostraOcultaCampo('bt_ok[$p]', '', 'bt_insvis[$p]');";
                $prod->bt_ok = $bt_ok->crCheckbox();
            }
            // $fieldsAten = $entRequisicao->defCamposProdutoAte($prod);
            // $fieldsConf = $entRequisicao->defCamposProdutoConf($prod);

            $prod->rpa_data         = data_br($prod->rpa_data);
            $prod->rpa_data_conferencia = data_br($prod->rpa_data_conferencia);
            $prod->saldo         = (int)$prod->rpa_atendida - (int)$prod->rpa_conferida;
        }
        // debug($resultado, true);
        $campos[0][] = view('partials/pw_produtos_inspecao', ['produtos' => array_map(fn($p) => (array) $p, $resultado)]);

        // Define dados principais da tela
        // $this->data['title']       = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['desc_metodo'] = '';
        $this->data['desc_edicao'] = 'Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT).' '.fmtEtiquetaCor($requisicao->stt_cor, $requisicao->stt_nome, 1);
        $this->data['destino']     = 'store';
        $this->data['scripts']     = 'my_requisicao';
        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;

        // Define foco inicial no campo de código de barras
        $this->data['script'] = "<SCRIPT>jQuery('#lot_codbar').focus();</SCRIPT>";
        echo view('vw_edicao', $this->data);
    }

    /**
     * Atenndimento
     * atende
     *
     * @param mixed $id 
     * @return void
     */
    public function inspeciona()
    {
        $request = service('request');

        // Lê e decodifica o JSON recebido
        $json = $request->getJSON(true); // true = como array associativo
        // Agora você pode acessar normalmente:
        $proId    = $json['pro_id'] ?? null;
        $lotlote  = $json['lot_lote'] ?? null;
        $reqId    = $json['req_id'] ?? null;
        $telId    = $json['tel_id'] ?? null;

        $config['Leitura']  = true;
        $config['Label']    = "Tela";
        $config['DispForm'] = "col-6";

        $tel_id = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $telId,
            1,
            'oco_ocorrencia',
            [],
            $config
        );
        $modProd = new ProdutProdutoModel();
        $dadosProd = $modProd->getProduto($proId)[0];
        // debug($dadosProd, true);

        $config['Label'] = "Subtipo de Ocorrência";
        $config['Leitura'] = false;
        $filtros = [
            'tel_id' => [$json['tel_id']],
            'cla_id' => [$dadosProd->cla_id],
        ];
        $mod_oc = criaSelectRelativo(
            'vw_oco_subt_ocorrencia_relac',
            'sut_id',
            'sut_nome',
            null,
            1,
            'oco_ocorrencia',
            $filtros,
            $config
        );

        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->dispForm    = 'col-6';
        $descreva = $desc->crInput();

        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->dispForm     = 'col-6';
        $qtd->minimo       = 1;
        $qtd->largura      = 10;
        $qtd->size         = 3;
        $qtd->maximo       = 999;
        $qtd->obrigatorio  = true;
        $quantia = $qtd->crInput();

        // Instancia a entity
        $dados['lot_lote'] = $lotlote;
        $dados['req_id']   = $reqId;
        $oco = new EntOcoOcorrencia($dados, true);

        // Dados Gerais
        $this->data['secoes']  = ['Dados Gerais'];

        // define os campos
        $this->data['campos']  = [[
            $oco->campos['oco_data'],
            $tel_id,
            $oco->campos['lot_id'],
            $oco->campos['lot_lote'],
            $oco->campos['pro_id'],
            $oco->campos['pro_despro'],
            // $toc_oc,
            $mod_oc,
            $quantia,
            // $descreva,
        ]];
        $this->data['destino'] = 'store';
        $this->data['desc_metodo'] = 'Inspeção de Produtos';
        $this->data['script'] = "<script>
                                    var elemento = document.getElementById('lot_lote');
                                    buscaLoteProduto(elemento,'" . base_url('/buscas/buscaProdutoporLote') . "')
                                </script>";
        $this->data['script'] .= "  <script>
                                       jQuery(document).ready(function() {
                                           var \$sut = jQuery('#sut_id');
                                           if (\$sut.hasClass('selectpicker')) {
                                               \$sut.selectpicker('destroy'); 
                                               \$sut.removeAttr('title');     
                                               \$sut.selectpicker();         
                                           }
                                       });
                                    </script>";
        $this->data['hidden'][] = [
            'name'  => 'req_id',
            'value' => $reqId
        ];

        // Renderiza a view
        echo view('vw_edicao_modal', $this->data);
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
                    if ($campo === "req_id") {
                        $dadosTemp[$campo] = $val;
                    }
                    if ($campo === "tmo_id") {
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
        if (count($dadosAgrupados) === 0) {
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
                if (!empty($movs)) {
                    cache()->clean();
                    // debug($movs);
                    $movim = geraMovimentoRequisicoes($movs, $this->data, 'C');
                    if ($movim['status'] == 'Erro') {
                        $ret['erro'] = true;
                        $ret['msg'] = $movim['mensagem'];
                        // debug($ret);
                    }
                }
                if (!$ret['erro']) {
                    $status = 5;
                    $dadosReq = [
                        'stt_id' => $status
                    ];
                    $this->requisicao->transStart();
                    $salvareq = $this->requisicao->update($postado['req_id'], $dadosReq);
                    if ($salvareq) {
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
