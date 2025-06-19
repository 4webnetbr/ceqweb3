<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\CommonModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;
use Config\Database;


class Produto extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $produtos;
    public $classe;
    public $common;
    public $ingrediente;
    public $lote;

    /**
     * Construtor da Produto
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->produtos    = new ProdutProdutoModel();
        $this->ingrediente    = new ProdutIngredienteModel();
        $this->classe    = new ProdutClasseModel();
        $this->lote    = new ProdutLoteModel();
        $this->common    = new CommonModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'pro_id');
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
        // $integ = new WsCeqweb();
        // $integ->integraProduto();

        //if (!$produto = cache('produto')) {
        $campos = montaColunasCampos($this->data, 'pro_id');
        $dados_produto = $this->produtos->getListaProduto();
        $this->data['exclusao'] = false;
        $produto = [
            'data' => montaListaColunas($this->data, 'pro_id', $dados_produto, $campos[1]),
        ];
        cache()->save('produto', $produto, 60000);
        //}

        echo json_encode($produto);
    }

    /**
     * Aprovação
     * aprova
     *
     * @param mixed $id 
     * @return void
     */
    public function aprova($dados_produtos)
    {
        $fields = $this->produtos->defCampos($dados_produtos, true);

        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['pro_codpro'];
        $campos[0][count($campos[0])] = $fields['ori_codOri'];
        $campos[0][count($campos[0])] = $fields['pro_id'];
        $campos[0][count($campos[0])] = $fields['fam_codFam'];
        $campos[0][count($campos[0])] = $fields['pro_ctrlot'];
        $campos[0][count($campos[0])] = $fields['pro_despro'];
        $campos[0][count($campos[0])] = $fields['cla_id'];
        $campos[0][count($campos[0])] = $fields['fab_codFab'];
        $campos[0][count($campos[0])] = $fields['ing_id'];
        $campos[0][count($campos[0])] = $fields['pro_codbar_fabricante'];
        $campos[0][count($campos[0])] = $fields['pro_informacoes'];

        $this->data['desc_metodo']     = 'Aprovação de Produto';
        $this->data['title']     = '';
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'storeaprova';

        echo view('vw_aprovacao', $this->data);
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
        $this->edit($id, true);
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
        $dados_produtos = $this->produtos->getListaProduto($id)[0] ?? null;

        if (!$dados_produtos) {
            throw new \RuntimeException('Produto não encontrado.');
        }

        // Se produto está em status inicial, envia para aprovação
        if (in_array($dados_produtos['stt_id'], [1, 2])) {
            return $this->aprova($dados_produtos);
        }

        // Se chegou aqui, renderiza a tela de edição
        $fields = $this->produtos->defCampos($dados_produtos, true);
        $secao = ['Dados Gerais'];
        $campos = [[]];

        $campos[0][] = $fields['pro_codpro'];
        $campos[0][] = $fields['ori_codOri'];
        $campos[0][] = $fields['pro_id'];
        $campos[0][] = $fields['fam_codFam'];
        $campos[0][] = $fields['pro_ctrlot'];
        $campos[0][] = $fields['pro_despro'];
        $campos[0][] = $fields['cla_id'];
        $campos[0][] = $fields['fab_codFab'];
        $campos[0][] = $fields['ing_id'];
        $campos[0][] = $fields['pro_codbar_fabricante'];
        $campos[0][] = $fields['pro_informacoes'];

        // Dados Estoque
        $dados_ceq_produto = $this->produtos->getProdutoCeqweb($id)[0] ?? ['prc_cplpro' => $dados_produtos['pro_cplpro']];
        $dados_ceq_produto['produto'] = $dados_produtos;
        $fieldceq = $this->produtos->defCamposCeqweb($dados_ceq_produto, $show);

        $secao[1] = 'Dados Estoque';
        $campos[1] = [
            $fieldceq['prc_id'],
            $fieldceq['pro_cplpro'],
            $fields['pro_qtdemb'],
            'vazio2',
            $fieldceq['prc_qtdemb_ceq'],
            $fieldceq['prc_conf_req'],
            $fieldceq['prc_etiq_misturador'],
            $fieldceq['prc_etiq_produto'],
            $fieldceq['prc_pedido_caixa'],
            $fieldceq['prc_codbar'],
            $fieldceq['prc_cor_etiqueta_prod'],
            $fieldceq['prc_deposito'],
            $fieldceq['prc_cor_etiqueta_mist'],
        ];

        $displ = [];

        // Gestão de Estoque
        if ($dados_produtos['cla_gestaoestoque'] === 'S') {
            $secao[2] = 'Gestão de Estoque';
            $displ[2] = 'tabela';
            $campos[2] = [];

            $dados_est_produto = $this->produtos->getProdutoEstoque($id);

            if (count($dados_est_produto) === 0) {
                $dados_est_produto[] = $dados_produtos;
            }

            foreach ($dados_est_produto as $index => $estoque) {
                $fieldest = $this->produtos->defCamposEstoque($estoque, $index, $show);
                $linha = [];

                $linha[] = $fieldest['pre_id'];
                $linha[] = $fieldest['dep_codDep'];
                $linha[] = $fieldest['pre_gestaoestoque'];

                // Estoque Mínimo
                $linha[] = "<div id='div_est_minimo[$index]' class='border border-2 col-6 d-inline-block mt-4 float-start pb-1' style='clear: left'>";
                $linha[] = "<span class='col-3 bg-white border border-1 px-1 d-block position-relative' style='top: -12px;margin-bottom: -10px;left: 10px;'>Estoque Mínimo</span>";
                $linha[] = "<div class='border-0'>";
                $linha[] = $fieldest['pre_mindiaanterior'];
                $linha[] = $fieldest['pre_minimo'];
                $linha[] = "</div>";
                $linha[] = "</div>";

                // Estoque Máximo
                $linha[] = "<div id='div_est_maximo[$index]' class='border border-2 col-6 d-inline-block mt-4 float-start pb-1'>";
                $linha[] = "<span class='col-3 bg-white border border-1 px-1 d-block position-relative' style='top: -12px;margin-bottom: -10px;left: 10px;'>Estoque Máximo</span>";
                $linha[] = "<div class='border-0'>";
                $linha[] = $fieldest['pre_maxdiaanterior'];
                $linha[] = $fieldest['pre_porcmaximo'];
                $linha[] = $fieldest['pre_maximo'];
                $linha[] = "</div>";
                $linha[] = "</div>";

                // Outros campos
                $linha[] = "<div class='border border-0 col-12 d-inline-block mt-4 float-start pb-1'>";
                $linha[] = $fieldest['pre_sugerida'];
                $linha[] = $fieldest['pre_cbfabricante'];
                $linha[] = $fieldest['pre_cblote'];
                $linha[] = $fieldest['pre_cbmisturador'];
                $linha[] = $fieldest['pre_estdataatual'];
                $linha[] = $fieldest['pre_undfabricante'];
                $linha[] = $fieldest['pre_undlote'];
                $linha[] = $fieldest['pre_undmisturador'];
                $linha[] = "</div>";

                // Botões
                $linha[] = $show ? '' : $fieldest['bt_add'];
                $linha[] = $show ? '' : $fieldest['bt_del'];

                $campos[2][$index] = $linha;
            }
        }

        // Script JS
        $script = "<script>
            acerta_botoes_rep('gestao_de_estoque');
            mostraOcultaCampo('prc_etiq_misturador','S','prc_cor_etiqueta_mist,prc_codbar');
            mostraOcultaCampo('prc_etiq_produto','S','prc_cor_etiqueta_prod');

            mostraOcultaCampoTodos('pre_mindiaanterior','N','pre_minimo');

            mostraOcultaCampoTodos('pre_maxdiaanterior','S','pre_porcmaximo');
            mostraOcultaCampoTodos('pre_maxdiaanterior','N','pre_maximo');

            mostraOcultaCampoTodos('pre_cbfabricante','S','pre_undfabricante');
            mostraOcultaCampoTodos('pre_cblote','S','pre_undlote');
            mostraOcultaCampoTodos('pre_cbmisturador','S','pre_undmisturador');
            mostraOcultaCampoTodos('pre_gestaoestoque','S','pre_sugerida,pre_estdataatual');

            mostraOcultaDivTodos('pre_gestaoestoque','S','div_est_minimo,div_est_maximo');
        </script>";

        $this->data['secoes']       = $secao;
        $this->data['campos']       = $campos;
        $this->data['displ']        = $displ;
        $this->data['destino']      = 'store';
        $this->data['script']       = $script;
        $this->data['desc_edicao']  = $dados_produtos['pro_despro'];
        $this->data['log']          = buscaLog('pro_produto', $id);

        return view('vw_edicao', $this->data);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'pro_ativo' => 'A'
            ];
            $tipotxt = [27, 'Ativado'];
        } else {
            $dad_atin = [
                'pro_ativo' => 'I'
            ];
            $tipotxt = [14, 'Inativado'];
        }
        $podeinativar = $this->produtos->getProdutoEstoque($id);
        $ret = [];
        if (count($podeinativar) > 0) {
            try {
                $this->produtos->update($id, $dad_atin);
                $ret['erro'] = false;
                session()->setFlashdata('msg', "Produto " . $tipotxt[1] . " com Sucesso");
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $ret['erro'] = true;
                $ret['msg']  = $tipotxt[0];
            }
        } else {
            $ret['erro'] = true;
            // $ret['msg']  = "Não foi possível " . $tipotxt[0] . " o Produto, Verifique!<br><br>";
            $ret['msg']  = $tipotxt[0];
        }
        echo json_encode($ret);
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
        try {
            $this->produtos->delete($id);
            $ret['erro'] = false;
            cache()->clean();
            session()->setFlashdata('msg', 'Produto de Produto Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir essa Produto de Produto Verifique!<br><br>';
        }
        echo json_encode($ret);
    }

    /**
     * Summary of addCampo
     * @param mixed $ind
     * @return never
     */
    public function addCampo($ind)
    {
        $fieldest = $this->produtos->defCamposEstoque(false, $ind);
        $campo = [];
        $campo[count($campo)] = $fieldest['pre_id'];
        $campo[count($campo)] = $fieldest['dep_codDep'];
        $campo[count($campo)] = $fieldest['pre_gestaoestoque'];
        $campo[count($campo)] = "<div id='div_est_minimo[$ind]' class='border border-2 col-6 d-inline-block mt-4 float-start pb-1' style='clear: left'>";
        $campo[count($campo)] = "<span class='col-3 bg-white border border-1 px-1 d-block position-relative' style='top: -12px;margin-bottom: -10px;left: 10px;'>Estoque Mínimo</span>";
        $campo[count($campo)] = "<div class='border-0'>";
        $campo[count($campo)] = $fieldest['pre_mindiaanterior'];
        $campo[count($campo)] = $fieldest['pre_minimo'];
        $campo[count($campo)] = "</div>";
        $campo[count($campo)] = "</div>";
        $campo[count($campo)] = "<div id='div_est_maximo[$ind]' class='border border-2 col-6 d-inline-block mt-4 float-start pb-1'>";
        $campo[count($campo)] = "<span class='col-3 bg-white border border-1 px-1 d-block position-relative' style='top: -12px;margin-bottom: -10px;left: 10px;'>Estoque Máximo</span>";
        $campo[count($campo)] = "<div class='border-0'>";
        $campo[count($campo)] = $fieldest['pre_maxdiaanterior'];
        $campo[count($campo)] = $fieldest['pre_porcmaximo'];
        $campo[count($campo)] = $fieldest['pre_maximo'];
        $campo[count($campo)] = "</div>";
        $campo[count($campo)] = "</div>";
        $campo[count($campo)] = "<div class='border border-0 col-12 d-inline-block mt-4 float-start pb-1'>";
        $campo[count($campo)] = $fieldest['pre_sugerida'];
        $campo[count($campo)] = $fieldest['pre_cbfabricante'];
        $campo[count($campo)] = $fieldest['pre_cblote'];
        $campo[count($campo)] = $fieldest['pre_cbmisturador'];
        $campo[count($campo)] = $fieldest['pre_estdataatual'];
        $campo[count($campo)] = $fieldest['pre_undfabricante'];
        $campo[count($campo)] = $fieldest['pre_undlote'];
        $campo[count($campo)] = $fieldest['pre_undmisturador'];
        $campo[count($campo)] = "</div>";

        $campo[count($campo)] = $fieldest['bt_add'];
        $campo[count($campo)] = $fieldest['bt_del'];

        echo json_encode($campo);
        exit;
    }

    /**
     * Gravação de Aprovação ou Rejeição
     * storeaprova
     *
     * @return void
     */
    // public function storeaprova()
    // {
    //     $ret = [];
    //     $ret['erro'] = false;
    //     $postado = $this->request->getPost();
    //     $ativ = 'I';
    //     if ($postado['aprova'] == 3) { // APROVADO
    //         $ativ = 'A';
    //         if (!isset($postado['cla_id']) || $postado['cla_id'] == null) {
    //             $ret['erro'] = true;
    //             $ret['msg']  = 24; // ID da mensagem no cadastro
    //         }
    //     }
    //     if (!$ret['erro']) {
    //         $sql_aprova = [
    //             'pro_id'    => intval($postado['pro_id']),
    //             'stt_id'    => $postado['aprova'],
    //             'cla_id'    => $postado['cla_id'],
    //             'pro_codbar_fabricante'    => $postado['pro_codbar_fabricante'],
    //             'pro_informacoes'    => $postado['pro_informacoes'],
    //             // 'pro_ativo' => $ativ,
    //         ];
    //         if ($this->produtos->save($sql_aprova)) {
    //             if ($postado['ing_id']) {
    //                 $data_atu = date('Y-m-d H:i');
    //                 $sql_pro = [
    //                     'ing_id' => $postado['ing_id'],
    //                     'cla_id' => $postado['cla_id'],
    //                     'pro_id' => intval($postado['pro_id']),
    //                     'inp_atualizado' => $data_atu,
    //                 ];
    //                 $tem = $this->ingrediente->getProdutoIngrediente(intval($postado['pro_id']));
    //                 if (count($tem) > 0) {
    //                     $ing_id = $this->common->updateReg('dbProduto', 'pro_ing_produto', 'pro_id =' . intval($postado['pro_id']), $sql_pro);
    //                 } else {
    //                     $ing_id = $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_pro);
    //                 }
    //             }
    //             if ($postado['aprova'] == 3) { // APROVADO
    //                 $ret['msg']  = 'Produto Aprovado!!!';
    //                 session()->setFlashdata('msg', $ret['msg']);
    //                 $ret['url']  = site_url($this->data['controler'] . '/edit/' . intval($postado['pro_id']));
    //             } else {
    //                 cache()->clean();
    //                 $ret['msg']  = 'Produto Reprovado!!!';
    //                 session()->setFlashdata('msg', $ret['msg']);
    //                 $ret['url']  = site_url($this->data['controler']);
    //             }
    //         } else {
    //             $ret['msg']  = 'Não foi possível alterar o Produto!!!';
    //             session()->setFlashdata('msg', $ret['msg']);
    //             $ret['url']  = site_url($this->data['controler']);
    //         }
    //     }
    //     echo json_encode($ret);
    // }
    public function storeaprova()
    {
        $ret = ['erro' => false];
        $postado = $this->request->getPost();

        $pro_id = intval($postado['pro_id'] ?? 0);
        $pro_codpro = $postado['pro_codpro'];
        $cla_id = $postado['cla_id'] ?? null;
        $ing_id = $postado['ing_id'] ?? null;
        $aprova = intval($postado['aprova'] ?? 0);

        // Verificação obrigatória para aprovação
        if ($aprova === 3 && empty($cla_id)) {
            echo json_encode([
                'erro' => true,
                'msg'  => 24 // ID da mensagem no cadastro
            ]);
            return;
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $sql_aprova = [
                'pro_id'                => $pro_id,
                'stt_id'                => $aprova,
                'cla_id'                => $cla_id,
                'pro_codbar_fabricante' => $postado['pro_codbar_fabricante'] ?? null,
                'pro_informacoes'       => $postado['pro_informacoes'] ?? null,
            ];

            if (!$this->produtos->save($sql_aprova)) {
                throw new \Exception('Erro ao atualizar o status do produto.');
            }
            if ($aprova === 3) {
                $dadosclasse = $this->classe->getClasse($cla_id)[0];
                if ($dadosclasse['cla_micro'] == 'N') {
                    $sttlote = 9;
                    $sql_lote = [
                        'stt_id' => $sttlote
                    ];
                    $this->common->updateReg('dbProduto', 'pro_sap_lote', "lot_codpro = '" . $pro_codpro . "' ", $sql_lote);
                }
            }
            // Atualiza ou insere relacionamento com ingrediente, se houver
            if (!empty($ing_id)) {
                $data_atu = date('Y-m-d H:i:s');
                $sql_ing = [
                    'ing_id'         => $ing_id,
                    'cla_id'         => $cla_id,
                    'pro_id'         => $pro_id,
                    'inp_atualizado' => $data_atu,
                ];

                $temIngrediente = $this->ingrediente->getProdutoIngrediente($pro_id);

                if ($temIngrediente) {
                    $this->common->updateReg('dbProduto', 'pro_ing_produto', 'pro_id = ' . $pro_id, $sql_ing);
                } else {
                    $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_ing);
                }
            }

            $db->transCommit();

            // Mensagens e redirecionamento
            if ($aprova === 3) {
                $ret['msg'] = 'Produto Aprovado!!!';
                $ret['url'] = site_url($this->data['controler'] . '/edit/' . $pro_id);
            } else {
                $ret['msg'] = 'Produto Reprovado!!!';
                $ret['url'] = site_url($this->data['controler']);
            }

            session()->setFlashdata('msg', $ret['msg']);
            cache()->clean();
        } catch (\Throwable $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao processar aprovação do produto: ' . $e->getMessage();
        }

        echo json_encode($ret);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    // public function store()
    // {
    //     $ret = [];
    //     $postado = $this->request->getPost();
    //     // debug($postado, true);
    //     $ret['erro'] = false;
    //     $erros = [];
    //     $depjatem = '';
    //     if (isset($postado['dep_codDep'])) {
    //         foreach ($postado['dep_codDep'] as $key => $value) {
    //             if (strpos($depjatem, $postado['dep_codDep'][$key]) > -1) {
    //                 $ret['erro'] = true;
    //                 $erros[0] = 22;
    //                 break;
    //             } else {
    //                 $depjatem .= $postado['dep_codDep'][$key] . ',';
    //             }
    //         }
    //     }
    //     if (!$ret['erro']) {
    //         $sql_pro = [
    //             'pro_id'    => intval($postado['pro_id']),
    //             'cla_id'    => $postado['cla_id'],
    //             'pro_codbar_fabricante'    => $postado['pro_codbar_fabricante'],
    //             'pro_informacoes'    => $postado['pro_informacoes'],
    //             'pro_ativo' => 'A',
    //         ];
    //         if ($this->produtos->save($sql_pro)) {
    //             $depositos = '';
    //             foreach ($postado['prc_deposito'] as $key => $value) {
    //                 $depositos .= $value . ', ';
    //             }
    //             $postado['prc_deposito'] = $depositos;

    //             $sql_prc = [
    //                 'pro_id'  => $postado['pro_id'],
    //                 'prc_cplpro'  => $postado['prc_cplpro'],
    //                 'prc_qtdemb_ceq'  => $postado['prc_qtdemb_ceq'],
    //                 'prc_conf_req'  => $postado['prc_conf_req'],
    //                 'prc_pedido_caixa'  => $postado['prc_pedido_caixa'],
    //                 'prc_etiq_misturador'  => $postado['prc_etiq_misturador'],
    //                 'prc_codbar'  => $postado['prc_codbar'],
    //                 'prc_cor_etiqueta_mist'  => isset($postado['prc_cor_etiqueta_mist']) ? $postado['prc_cor_etiqueta_mist'] : null,
    //                 'prc_etiq_produto'  => $postado['prc_etiq_produto'],
    //                 'prc_cor_etiqueta_prod'  => isset($postado['prc_cor_etiqueta_prod']) ? $postado['prc_cor_etiqueta_prod'] : null,
    //                 'prc_deposito'  => $postado['prc_deposito'],
    //             ];
    //             if ($postado['prc_id'] != '') {
    //                 $salva = $this->common->updateReg('dbProduto', 'pro_ceq_produto', 'prc_id = ' . $postado['prc_id'], $sql_prc);
    //             } else {
    //                 $salva = $this->common->insertReg('dbProduto', 'pro_ceq_produto', $sql_prc);
    //             }
    //             // var_dump($salva);
    //             if ($salva) {
    //                 $pro_id = intval($postado['pro_id']);
    //                 $data_atu = date('Y-m-d H:i:s');
    //                 if ($postado['ing_id']) {
    //                     $sql_pro = [
    //                         'ing_id' => $postado['ing_id'],
    //                         'cla_id' => $postado['cla_id'],
    //                         'pro_id' => $pro_id,
    //                         'inp_atualizado' => $data_atu,
    //                     ];
    //                     $tem = $this->ingrediente->getProdutoIngrediente($pro_id);
    //                     if (count($tem) > 0) {
    //                         $ing_id = $this->common->updateReg('dbProduto', 'pro_ing_produto', 'pro_id =' . $pro_id, $sql_pro);
    //                     } else {
    //                         $ing_id = $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_pro);
    //                     }
    //                 }
    //                 $this->common->deleteReg("dbProduto", "pro_ing_produto", "pro_id = " . $pro_id . " AND inp_atualizado != '" . $data_atu . "'");
    //                 if (isset($postado['dep_codDep'])) {
    //                     // GRAVAÇãO DOS Depósitos
    //                     $this->common->deleteReg("dbProduto", "pro_est_produto", "pro_id = " . $pro_id . "");

    //                     foreach ($postado['dep_codDep'] as $key => $value) {
    //                         $sql_dep = [
    //                             'pro_id' => $pro_id,
    //                             'dep_codDep' => $postado['dep_codDep'][$key],
    //                             'pre_mindiaanterior' => $postado['pre_mindiaanterior'][$key],
    //                             'pre_minimo' => $postado['pre_minimo'][$key],
    //                             'pre_maxdiaanterior' => $postado['pre_maxdiaanterior'][$key],
    //                             'pre_porcmaximo' => $postado['pre_porcmaximo'][$key],
    //                             'pre_maximo' => $postado['pre_maximo'][$key],
    //                             'pre_sugerida' => $postado['pre_sugerida'][$key],
    //                             'pre_cbfabricante' => $postado['pre_cbfabricante'][$key],
    //                             'pre_cblote' => $postado['pre_cblote'][$key],
    //                             'pre_undfabricante' => $postado['pre_undfabricante'][$key],
    //                             'pre_undlote' => $postado['pre_undlote'][$key],
    //                             'pre_estdataatual' => $postado['pre_estdataatual'][$key],
    //                             'pre_gestaoestoque' => $postado['pre_gestaoestoque'][$key],
    //                         ];
    //                         $dep_id = $this->common->insertReg('dbProduto', 'pro_est_produto', $sql_dep);
    //                         if (!$dep_id) {
    //                             $ret['erro'] = true;
    //                             $erros = $this->common->errors();
    //                             $ret['msg'] = 'Não foi possível gravar os Depósitos do Produto, Verifique!';
    //                         }
    //                     }
    //                 }
    //             } else {
    //                 $ret['erro'] = true;
    //                 $erros = $this->common->errors();
    //             }
    //         } else {
    //             $ret['erro'] = true;
    //             $erros = $this->common->errors();
    //         }
    //     }
    //     if ($ret['erro']) {
    //         if (is_numeric($erros[0])) {
    //             $ret['msg'] = $erros[0];
    //         } else {
    //             $ret['msg']  = 'Não foi possível gravar os Dados do Produto, Verifique!<br><br>';
    //             foreach ($erros as $erro) {
    //                 $ret['msg'] .= $erro . '<br>';
    //             }
    //         }
    //     } else {
    //         cache()->clean();
    //         $ret['msg']  = 'Dados do Produto gravado com Sucesso!!!';
    //         session()->setFlashdata('msg', $ret['msg']);
    //         $ret['url']  = site_url($this->data['controler']);
    //     }
    //     echo json_encode($ret);
    //     cache()->clean();
    // }

    public function store()
    {
        $ret = ['erro' => false];
        $postado = $this->request->getPost();
        $erros = [];

        // Verificação de duplicidade de depósitos
        if (isset($postado['dep_codDep'])) {
            $depositos = array_filter($postado['dep_codDep']);
            if (count($depositos) !== count(array_unique($depositos))) {
                $ret['erro'] = true;
                $ret['msg'] = 'Depósitos duplicados não são permitidos.';
                echo json_encode($ret);
                return;
            }
        }

        // Início da transação
        $db = Database::connect();
        $db->transBegin();

        try {
            // Grava produto
            $sql_pro = [
                'pro_id'                    => (int) ($postado['pro_id'] ?? 0),
                'cla_id'                    => $postado['cla_id'] ?? null,
                'pro_codbar_fabricante'     => $postado['pro_codbar_fabricante'] ?? null,
                'pro_informacoes'           => $postado['pro_informacoes'] ?? null,
                'pro_ativo'                 => 'A',
            ];

            if (!$this->produtos->save($sql_pro)) {
                throw new \Exception('Erro ao salvar produto.');
            }

            // Grava prc (propriedades do produto)
            $postado['prc_deposito'] = isset($postado['prc_deposito']) ? implode(', ', $postado['prc_deposito']) : null;

            $sql_prc = [
                'pro_id'                    => $postado['pro_id'],
                'prc_cplpro'                => $postado['prc_cplpro'] ?? null,
                'prc_qtdemb_ceq'            => $postado['prc_qtdemb_ceq'] ?? null,
                'prc_conf_req'              => $postado['prc_conf_req'] ?? null,
                'prc_pedido_caixa'          => $postado['prc_pedido_caixa'] ?? null,
                'prc_etiq_misturador'       => $postado['prc_etiq_misturador'] ?? null,
                'prc_codbar'                => $postado['prc_codbar'] ?? null,
                'prc_cor_etiqueta_mist'     => $postado['prc_cor_etiqueta_mist'] ?? null,
                'prc_etiq_produto'          => $postado['prc_etiq_produto'] ?? null,
                'prc_cor_etiqueta_prod'     => $postado['prc_cor_etiqueta_prod'] ?? null,
                'prc_deposito'              => $postado['prc_deposito'],
            ];

            $salva = !empty($postado['prc_id'])
                ? $this->common->updateReg('dbProduto', 'pro_ceq_produto', 'prc_id = ' . $postado['prc_id'], $sql_prc)
                : $this->common->insertReg('dbProduto', 'pro_ceq_produto', $sql_prc);

            if (!$salva) {
                throw new \Exception('Erro ao salvar dados adicionais do produto.');
            }

            $pro_id = (int) $postado['pro_id'];
            $data_atu = date('Y-m-d H:i:s');

            // Ingrediente
            if (!empty($postado['ing_id'])) {
                $sql_ingrediente = [
                    'ing_id'         => $postado['ing_id'],
                    'cla_id'         => $postado['cla_id'],
                    'pro_id'         => $pro_id,
                    'inp_atualizado' => $data_atu,
                ];

                $temIngrediente = $this->ingrediente->getProdutoIngrediente($pro_id);

                if ($temIngrediente) {
                    $this->common->updateReg('dbProduto', 'pro_ing_produto', 'pro_id = ' . $pro_id, $sql_ingrediente);
                } else {
                    $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_ingrediente);
                }

                $this->common->deleteReg("dbProduto", "pro_ing_produto", "pro_id = $pro_id AND inp_atualizado != '$data_atu'");
            }

            // Grava depósitos
            if (isset($postado['dep_codDep'])) {
                $this->common->deleteReg("dbProduto", "pro_est_produto", "pro_id = " . $pro_id);

                foreach ($postado['dep_codDep'] as $key => $dep) {
                    $sql_dep = [
                        'pro_id'              => $pro_id,
                        'dep_codDep'          => $dep,
                        'pre_mindiaanterior'  => $postado['pre_mindiaanterior'][$key] ?? null,
                        'pre_minimo'          => $postado['pre_minimo'][$key] ?? null,
                        'pre_maxdiaanterior'  => $postado['pre_maxdiaanterior'][$key] ?? null,
                        'pre_porcmaximo'      => $postado['pre_porcmaximo'][$key] ?? null,
                        'pre_maximo'          => $postado['pre_maximo'][$key] ?? null,
                        'pre_sugerida'        => $postado['pre_sugerida'][$key] ?? null,
                        'pre_cbfabricante'    => $postado['pre_cbfabricante'][$key] ?? null,
                        'pre_undfabricante'   => $postado['pre_undfabricante'][$key] ?? null,
                        'pre_cblote'          => $postado['pre_cblote'][$key] ?? null,
                        'pre_undlote'         => $postado['pre_undlote'][$key] ?? null,
                        'pre_cbmisturador'    => $postado['pre_cbmisturador'][$key] ?? null,
                        'pre_undmisturador'   => $postado['pre_undmisturador'][$key] ?? null,
                        'pre_estdataatual'    => $postado['pre_estdataatual'][$key] ?? null,
                        'pre_gestaoestoque'   => $postado['pre_gestaoestoque'][$key] ?? null,
                    ];

                    $dep_id = $this->common->insertReg('dbProduto', 'pro_est_produto', $sql_dep);

                    if (!$dep_id) {
                        throw new \Exception('Erro ao salvar depósitos do produto.');
                    }
                }
            }

            // Finaliza com sucesso
            $db->transCommit();
            cache()->clean();
            session()->setFlashdata('msg', 'Dados do Produto gravado com Sucesso!!!');

            $ret['msg'] = 'Dados do Produto gravado com Sucesso!!!';
            $ret['url'] = site_url($this->data['controler']);
        } catch (\Throwable $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao salvar produto: ' . $e->getMessage();
        }

        echo json_encode($ret);
    }
}
