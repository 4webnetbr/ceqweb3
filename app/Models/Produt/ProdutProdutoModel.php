<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigCorModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutFabricanteModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutOrigemModel;
use CodeIgniter\Model;

class ProdutProdutoModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_produto';
    protected $view             = 'vw_pro_sap_produto_relac';
    protected $primaryKey       = 'pro_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'pro_id',
        'pro_codemp',
        'pro_codpro',
        'pro_despro',
        'fab_codFab',
        'ori_codOri',
        'fam_codFam',
        'cla_id',
        'pro_cplpro',
        'pro_ctrlot',
        'pro_qtdemb',
        'pro_codbar_fabricante',
        'pro_informacoes',
        'pro_ativo',
        'stt_id',
    ];

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getListaProduto($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        $builder->orderBy('stt_ordem, pro_ativo, pro_despro, cla_ordem, pro_codpro');
        return $builder->get()->getResultArray();
    }

    public function getProduto($pro_id = false, $ativo = true)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        if ($ativo) {
            $builder->where('pro_ativo', 'A');
        }
        $builder->where('stt_id >', 2);
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function getProdutoCod($pro_cod = false, $dispon = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_cod) {
            $builder->where('TRIM(pro_codpro)', trim($pro_cod));
        }
        if ($dispon) {
            $builder->where('stt_disponivel', $dispon);
        }
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function getProdutoCodLista($pro_cod = false, $dispon = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_cod) {
            $builder->whereIn('pro_codpro', $pro_cod);
        }
        if ($dispon) {
            $builder->where('stt_disponivel', $dispon);
        }
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function getProdutoClasse($classe = false, $ing = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($classe) {
            $builder->where('cla_id', $classe);
        }
        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);
        if ($ing) {
            $builder->groupStart();
            $builder->where('ing_id', null);
            $builder->orWhere('ing_id', $ing);
            $builder->groupEnd();
        } else {
            $builder->groupStart();
            $builder->where('ing_id', null);
            $builder->orWhere('cla_ing_id = cla_id');
            $builder->groupEnd();
        }
        $builder->orderBy('cla_ordem', 'pro_nome');
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    // public function getProdutoClasseIngrediente($classe = false, $ing_id = false)
    // {
    //     $db = db_connect('dbProduto');
    //     $builder = $db->table('vw_pro_sap_produto_relac');

    //     $builder->select('*');
    //     if ($classe) {
    //         $builder->where('cla_id', $classe);
    //     }
    //     if ($ing_id) {
    //         $builder->groupStart();
    //         $builder->where('ing_id', $ing_id);
    //         $builder->where('ing_id', null);
    //         $builder->groupEnd();
    //     }
    //     $builder->where('pro_ativo', 'A');
    //     $builder->where('stt_id >', 2);
    //     $builder->groupStart();
    //     $builder->where('ing_id IS NULL');
    //     $builder->orWhere('cla_ing_id = cla_id');
    //     $builder->groupEnd();
    //     $builder->orderBy('cla_ordem', 'pro_nome');
    //     // $sql = $builder->getCompiledSelect();
    //     // debug($sql, true);
    //     return $builder->get()->getResultArray();
    // }

    public function getProdutoSemIngrediente($pro_id = false, $cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);
        $builder->where('ing_id IS NULL');
        // $sql = $builder->getCompiledSelect();
        // debug($sql);
        return $builder->get()->getResultArray();
    }

    public function getProdutoComIngrediente($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);
        // $sql = $builder->getCompiledSelect();
        // debug($sql);
        return $builder->get()->getResultArray();
    }

    public function getProdutoSearch($termo)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');
        $array = ['pro_desPro' => $termo . '%'];
        $builder->select('*');
        $builder->where('pro_ativo', 'A');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function getProdutoCeqweb($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ceq_produto');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function getProdutoEstoque($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_est_produto');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function getProdutoOrigemFamiliaClasse($origem = false, $familia = false, $classe = false)
    {
        if (!$origem || !$familia || !$classe) {
            return false;
        }
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_produto');

        $builder->select('*');
        $builder->where('ori_codOri', $origem);
        $builder->where('fam_codFam', $familia);
        $builder->where('cla_id', $classe);
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }

    public function excluiFabricante($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');
        $builder->where('pro_codPro', $pro_id);
        $builder->delete();
        return true;
    }

    public function excluiFabricanteArray($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');
        $builder->whereIn('pro_codPro', $pro_id);
        $builder->delete();
        return true;
    }

    public function getFabricanteProduto($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');
        $builder->select('*');
        $builder->where('pro_codPro', $pro_id);
        return $builder->get()->getResultArray();
    }

    public function getFabricanteProdutosArray($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');
        $builder->select('*');
        $builder->whereIn('pro_codPro', $pro_id);
        return $builder->get()->getResultArray();
    }

    public function getProdutoRequisicao($deposito =  false, $produto = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_classe_produto_lote_semlote_info');

        $builder->select('*');
        if ($deposito) {
            $builder->where('pre_codDep', $deposito);
        }
        if ($produto) {
            $builder->where('pro_id', $produto);
        }

        $builder->orderBy('cla_ordem, pro_despro, pro_codpro, lot_validade');
        // $sql = $builder->getCompiledSelect();
        // debug($sql);
        return $builder->get()->getResultArray();
    }


    public function defCampos($dados = false, $show = false)
    {
        $opcoes         = new CommonModel();

        $id             = new MyCampo('pro_sap_produto', 'pro_id', true);
        $id->tipo       = 'text';
        $id->valor      = $dados['cod_ceqweb'];
        $id->obrigatorio = true;
        $id->leitura     = $show;
        $id->largura    = 15;
        $id->dispForm   = '2col';
        $ret['pro_id'] = $id->crInput();

        $cpro           =  new MyCampo('pro_sap_produto', 'pro_codpro', true);
        $cpro->valor    = (isset($dados['pro_codpro'])) ? $dados['pro_codpro'] : '';
        $cpro->obrigatorio = true;
        $cpro->leitura  = $show;
        $cpro->largura    = 15;
        $cpro->dispForm   = '2col';
        $ret['pro_codpro'] = $cpro->crInput();

        $dpro           =  new MyCampo('pro_sap_produto', 'pro_despro');
        $dpro->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $dpro->obrigatorio = true;
        $dpro->leitura  = $show;
        $dpro->largura    = 50;
        $dpro->dispForm   = '2col';
        $ret['pro_despro'] = $dpro->crInput();

        $opc_orig      = $opcoes->getListaOpcoes('dbProduto', 'pro_sap_origem', ['ori_codDescricao', 'ori_codOri'], "ori_codOri = '" . $dados['ori_codOri'] . "'");

        // $origens        =  new ProdutOrigemModel();
        // $lst_origens    = $origens->getOrigem($dados['ori_codOri']);
        // $opc_orig       = array_column($lst_origens,'ori_codDescricao','ori_codOri');
        $orig           =  new MyCampo('pro_sap_produto', 'ori_codOri');
        $orig->valor    = $orig->selecionado = (isset($dados['ori_codOri'])) ? $dados['ori_codOri'] : '';
        $orig->obrigatorio = true;
        $orig->leitura  = $show;
        $orig->opcoes   = $opc_orig;
        $orig->largura    = 50;
        $orig->dispForm   = '2col';
        $ret['ori_codOri'] = $orig->crSelect();

        $opc_fami      = $opcoes->getListaOpcoes('dbProduto', 'pro_sap_familia', ['fam_codDescricao', 'fam_codFam'], "fam_codFam = '" . $dados['fam_codFam'] . "'");

        // $familias       =  new ProdutFamiliaModel();
        // $lst_familias   = $familias->getFamilia($dados['fam_codFam']);
        // $opc_fami       = array_column($lst_familias,'fam_codDescricao','fam_codFam');
        $fami           =  new MyCampo('pro_sap_produto', 'fam_codFam');
        $fami->valor    = $fami->selecionado = (isset($dados['fam_codFam'])) ? $dados['fam_codFam'] : '';
        $fami->obrigatorio = true;
        $fami->leitura  = $show;
        $fami->opcoes   = $opc_fami;
        $fami->largura    = 50;
        $fami->dispForm   = '2col';
        $ret['fam_codFam'] = $fami->crSelect();

        $classes       =  new ProdutClasseModel();
        $lst_classes   = $classes->getClassificacaoClasse($dados['ori_codOri'], $dados['fam_codFam']);
        $cla_id        = isset($dados['cla_id']) ? [$dados['cla_id']] : [];
        if (count($lst_classes) == 1) {
            $cla_id = [$lst_classes[0]['cla_id']];
        }
        $opc_clas       = array_column($lst_classes, 'cla_nome', 'cla_id');

        $clas           =  new MyCampo('pro_sap_produto', 'cla_id');
        $clas->valor    = (isset($dados['cla_id'])) ? $dados['cla_id'] : '';
        $clas->selecionado = $cla_id;
        $clas->obrigatorio = false;
        if (count($opc_clas) > 1 || count($opc_clas) == 0) {
            $clas->leitura  = false;
        } else {
            $clas->leitura  = true;
        }
        $clas->opcoes     = $opc_clas;
        $clas->largura    = 50;
        $clas->dispForm   = '2col';
        $ret['cla_id'] = $clas->crSelect();

        if (strlen($dados['fab_codFab']) > 0) {
            $opc_fabr      = $opcoes->getListaOpcoes('dbProduto', 'pro_sap_fabricante', ['fab_apeFab', 'fab_codFab'], 'fab_codFab = ' . $dados['fab_codFab']);
            // $fabricantes        =  new ProdutFabricanteModel();
            // $lst_fabrics    = $fabricantes->getFabricante($dados['fab_codFab']);
            // $opc_fabr       = array_column($lst_fabrics,'fab_apeFab','fab_codFab');
        } else {
            $opc_fabr      = [];
        }
        $fabr           =  new MyCampo('pro_sap_fabricante', 'fab_codFab');
        $fabr->valor    = (isset($dados['fab_codFab'])) ? $dados['fab_codFab'] : '';
        $fabr->selecionado = (isset($dados['fab_codFab'])) ? [$dados['fab_codFab']] : [];
        $fabr->obrigatorio = true;
        $fabr->leitura     = $show;
        $fabr->opcoes      = $opc_fabr;
        $fabr->largura     = 50;
        $fabr->dispForm    = '2col';
        $fabr->label        = 'Fabricante';
        $ret['fab_codFab'] = $fabr->crSelect();

        $pcpl           =  new MyCampo('pro_sap_produto', 'pro_cplpro');
        $pcpl->valor    = (isset($dados['pro_cplpro'])) ? $dados['pro_cplpro'] : '';
        $pcpl->obrigatorio = true;
        $pcpl->leitura  = $show;
        $pcpl->largura    = 50;
        $pcpl->dispForm   = '2col';
        $ret['pro_cplpro'] = $pcpl->crInput();

        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $ctrl           =  new MyCampo('pro_sap_produto', 'pro_ctrlot');
        $ctrl->valor    = (isset($dados['pro_ctrlot'])) ? $dados['pro_ctrlot'] : 'S';
        $ctrl->selecionado = $ctrl->valor;
        $ctrl->obrigatorio = true;
        $ctrl->leitura  = $show;
        $ctrl->largura    = 10;
        $ctrl->dispForm   = 'col-6';
        $ctrl->opcoes     = $simnao;
        $ret['pro_ctrlot'] = $ctrl->cr2opcoes();

        $qtem               =  new MyCampo('pro_sap_produto', 'pro_qtdemb');
        $qtem->tipo         = 'sonumero';
        $qtem->valor        = (isset($dados['pro_qtdemb'])) ? $dados['pro_qtdemb'] : 0;
        $qtem->obrigatorio  = true;
        $qtem->leitura      = $show;
        $qtem->largura      = 50;
        $qtem->minimo       = 0;
        $qtem->maximo       = 9999;
        $qtem->dispForm     = '2col';
        $ret['pro_qtdemb']  = $qtem->crInput();

        // debug($cla_id);
        if (count($cla_id) > 0) {
            $opc_ing            = $opcoes->getListaOpcoes('dbProduto', 'pro_ingrediente', ['ing_nome', 'ing_id'], 'cla_id = ' . $cla_id[0] . '');
        } else {
            $opc_ing            = [];
        }

        $ingp              =  new MyCampo('pro_ing_produto', 'ing_id');
        $ingp->valor       = (isset($dados['ing_id'])) ? $dados['ing_id'] : '';
        $ingp->selecionado = [$ingp->valor];
        $ingp->obrigatorio = false;
        $ingp->leitura     = false;
        $ingp->opcoes      = $opc_ing;
        $ingp->largura     = 50;
        $ingp->dispForm    = '2col';
        $ingp->pai         = 'cla_id';
        $ingp->urlbusca    = base_url('buscas/buscaIngredienteClasse');
        $ret['ing_id'] = $ingp->crDepende();

        $codb               =  new MyCampo('pro_sap_produto', 'pro_codbar_fabricante');
        $codb->tipo         = 'sonumero';
        $codb->valor        = (isset($dados['pro_codbar_fabricante'])) ? $dados['pro_codbar_fabricante'] : '';
        $codb->leitura      = false;
        $codb->largura      = 50;
        $codb->dispForm     = '2col';
        $ret['pro_codbar_fabricante']  = $codb->crInput();

        $info               =  new MyCampo('pro_sap_produto', 'pro_informacoes');
        $info->tipo         = 'sonumero';
        $info->valor        = (isset($dados['pro_informacoes'])) ? $dados['pro_informacoes'] : '';
        $info->leitura      = false;
        $info->dispForm     = '2col';
        $ret['pro_informacoes']  = $info->crInput();

        return $ret;
    }

    public function defCamposCeqweb($dados = false, $show = false)
    {
        $opcoes         = new CommonModel();
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $opc_cor            = $opcoes->getListaOpcoes('default', 'cfg_cor', ['cor_nome_rgb', 'cor_id']);
        // $cores          = new ConfigCorModel();
        // $lst_cores      = $cores->getCores();
        // $opc_cor        = array_column($lst_cores,'cor_nome_rgb','cor_id');

        $id             = new MyCampo('pro_ceq_produto', 'prc_id', true);
        $id->valor      = (isset($dados['prc_id'])) ? $dados['prc_id'] : '';
        $ret['prc_id'] = $id->crOculto();

        $pcpl               =  new MyCampo('pro_ceq_produto', 'prc_cplpro');
        $pcpl->valor        = (isset($dados['prc_cplpro'])) ? $dados['prc_cplpro'] : '';
        $pcpl->obrigatorio  = false;
        $pcpl->leitura      = $show;
        $pcpl->largura      = 50;
        $pcpl->dispForm     = '2col';
        $ret['pro_cplpro']  = $pcpl->crInput();

        $qtem               =  new MyCampo('pro_ceq_produto', 'prc_qtdemb_ceq');
        $qtem->valor        = (isset($dados['prc_qtdemb_ceq'])) ? $dados['prc_qtdemb_ceq'] : 0;
        $qtem->obrigatorio  = false;
        $qtem->leitura      = $show;
        $qtem->largura      = 50;
        $qtem->minimo       = 0;
        $qtem->maximo       = 9999;
        $qtem->size         = 4;
        $qtem->maxLength    = 4;
        $qtem->dispForm     = '2col';
        $ret['prc_qtdemb_ceq']  = $qtem->crInput();

        $creq               =  new MyCampo('pro_ceq_produto', 'prc_conf_req');
        $creq->valor        = (isset($dados['prc_conf_req'])) ? $dados['prc_conf_req'] : 'N';
        $creq->obrigatorio  = true;
        $creq->leitura      = $show;
        $creq->dispForm     = '3col';
        $creq->opcoes         = $simnao;
        $creq->selecionado    = $creq->valor;
        $ret['prc_conf_req']  = $creq->cr2opcoes();

        $pcai               =  new MyCampo('pro_ceq_produto', 'prc_pedido_caixa');
        $pcai->valor        = (isset($dados['prc_pedido_caixa'])) ? $dados['prc_pedido_caixa'] : 'N';
        $pcai->obrigatorio  = true;
        $pcai->leitura      = $show;
        $pcai->dispForm     = '3col';
        $pcai->opcoes         = $simnao;
        $pcai->selecionado    = $pcai->valor;
        $ret['prc_pedido_caixa']  = $pcai->cr2opcoes();

        $emis               =  new MyCampo('pro_ceq_produto', 'prc_etiq_misturador');
        $emis->valor        = (isset($dados['prc_etiq_misturador'])) ? $dados['prc_etiq_misturador'] : 'N';
        $emis->obrigatorio  = true;
        $emis->leitura      = $show;
        $emis->dispForm     = '3col';
        $emis->opcoes         = $simnao;
        $emis->selecionado    = $emis->valor;
        $emis->funcChan         = "mostraOcultaCampo(this,'S','prc_cor_etiqueta_mist,prc_codbar')";
        $ret['prc_etiq_misturador']  = $emis->cr2opcoes();

        $cemi               =  new MyCampo('pro_ceq_produto', 'prc_cor_etiqueta_mist');
        $cemi->valor        = (isset($dados['prc_cor_etiqueta_mist'])) ? $dados['prc_cor_etiqueta_mist'] : '';
        $cemi->selecionado  = $cemi->valor;
        $cemi->obrigatorio  = true;
        $cemi->leitura      = $show;
        $cemi->largura      = 50;
        $cemi->dispForm     = '3col';
        $cemi->opcoes       = $opc_cor;
        $ret['prc_cor_etiqueta_mist']  = $cemi->crSelectCor();

        $codb               =  new MyCampo('pro_ceq_produto', 'prc_codbar');
        $codb->tipo         = 'sonumero';
        $codb->valor        = (isset($dados['prc_codbar'])) ? $dados['prc_codbar'] : '';
        $codb->leitura      = $show;
        $codb->obrigatorio  = true;
        $codb->maxLength    = 13;
        $codb->largura      = 50;
        $codb->dispForm     = '3col';
        $ret['prc_codbar']  = $codb->crInput();

        $epro               =  new MyCampo('pro_ceq_produto', 'prc_etiq_produto');
        $epro->valor        = (isset($dados['prc_etiq_produto'])) ? $dados['prc_etiq_produto'] : 'N';
        $epro->obrigatorio  = true;
        $epro->leitura      = $show;
        $epro->dispForm     = '3col';
        $epro->opcoes         = $simnao;
        $epro->selecionado    = $epro->valor;
        $epro->funcChan         = "mostraOcultaCampo(this,'S','prc_cor_etiqueta_prod')";
        $ret['prc_etiq_produto']  = $epro->cr2opcoes();

        $cemp               =  new MyCampo('pro_ceq_produto', 'prc_cor_etiqueta_prod');
        $cemp->valor        = (isset($dados['prc_cor_etiqueta_prod']) && $dados['prc_cor_etiqueta_prod'] != '') ? $dados['prc_cor_etiqueta_prod'] : '1';
        $cemp->selecionado  = [$cemp->valor];
        $cemp->obrigatorio  = true;
        $cemp->default      = '1';
        $cemp->leitura      = $show;
        $cemp->largura      = 50;
        $cemp->dispForm     = '3col';
        $cemp->opcoes       = $opc_cor;
        $ret['prc_cor_etiqueta_prod']  = $cemp->crSelectCor();

        $depositos            = new EstoquDepositoModel();
        $lst_depositos        = $depositos->getDeposito();
        $opc_dep              = array_column($lst_depositos, 'dep_desDep', 'dep_codDep');

        $depositos = '';
        if (isset($dados['prc_deposito']) && $dados['prc_deposito'] != '') {
            $depositos = $dados['prc_deposito'];
        } else {
            if ($dados['produto']['cla_deposito'] != null) {
                $depositos = $dados['produto']['cla_deposito'];
            }
        }
        $depo                 = new MyCampo('pro_ceq_produto', 'prc_deposito', false);
        $depo->nome           = $depo->id = "prc_deposito";
        $depo->valor          = $depositos;
        $depo->selecionado    = array_filter(array_map('trim', explode(',', $depositos)));
        $depo->opcoes         = $opc_dep;
        $depo->largura        = 50;
        $depo->dispForm       = 'col-4';
        $ret['prc_deposito']  = $depo->crMultiple();

        return $ret;
    }

    public function defCamposEstoque($dados = false, $pos = 0, $show = false)
    {
        $opcoes         = new CommonModel();
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $opc_dep            = $opcoes->getListaOpcoes('dbEstoque', 'est_sap_deposito', ['dep_codDescricao', 'dep_codDep']);

        // $depositos      = new EstoquDepositoModel();
        // $lst_depos      = $depositos->getDeposito();
        // $opc_dep        = array_column($lst_depos,'dep_codDescricao','dep_codDep');

        $id             = new MyCampo('pro_est_produto', 'pre_id', true);
        $id->nome          = $id->id = "pre_id[$pos]";
        $id->valor      = (isset($dados['pre_id'])) ? $dados['pre_id'] : '';
        $ret['pre_id'] = $id->crOculto();

        $depo               =  new MyCampo('pro_est_produto', 'dep_codDep');
        $depo->nome          = $depo->id = "dep_codDep[$pos]";
        $depo->valor        = (isset($dados['dep_codDep'])) ? $dados['dep_codDep'] : '';
        $depo->selecionado  = [$depo->valor];
        $depo->obrigatorio  = true;
        $depo->leitura      = $show;
        $depo->largura      = 40;
        $depo->dispForm     = '3col';
        $depo->opcoes       = $opc_dep;
        $ret['dep_codDep']  = $depo->crSelect();

        $valor = isset($dados['pre_gestaoestoque'])
            ? $dados['pre_gestaoestoque']
            : (isset($dados['cla_gestaoestoque']) && $dados['cla_gestaoestoque'] != ''
                ? substr($dados['cla_gestaoestoque'], 0, 1)
                : 'S');

        $ges                 = new MyCampo('pro_est_produto', 'pre_gestaoestoque', false);
        $ges->nome          = $ges->id = "pre_gestaoestoque[$pos]";
        $ges->valor          = $valor;
        $ges->leitura        = $show;
        $ges->opcoes         = $simnao;
        $ges->selecionado    = $ges->valor;
        $ges->funcChan         = "mostraOcultaDiv(this,'S','div_est_minimo\\[$pos\\],div_est_maximo\\[$pos\\]');mostraOcultaCampo(this,'S','pre_sugerida\\[$pos\\],pre_estdataatual\\[$pos\\],pre_minimo\\[$pos\\],pre_maximo\\[$pos\\]');";
        $ges->dispForm       = '4col';
        $ret['pre_gestaoestoque']          = $ges->cr2opcoes();


        $mda               =  new MyCampo('pro_est_produto', 'pre_mindiaanterior');
        $mda->nome          = $mda->id = "pre_mindiaanterior[$pos]";
        $mda->valor        = (isset($dados['pre_mindiaanterior'])) ? $dados['pre_mindiaanterior'] : 'N';
        $mda->obrigatorio  = true;
        $mda->leitura      = $show;
        $mda->dispForm     = 'col-6';
        $mda->opcoes         = $simnao;
        $mda->selecionado    = $mda->valor;
        $mda->funcChan         = "mostraOcultaCampo(this,'N','pre_minimo\\[$pos\\]')";
        $ret['pre_mindiaanterior']  = $mda->cr2opcoes();

        $mini               =  new MyCampo('pro_est_produto', 'pre_minimo');
        $mini->nome          = $mini->id = "pre_minimo[$pos]";
        $mini->valor        = (isset($dados['pre_minimo'])) ? $dados['pre_minimo'] : 0;
        $mini->obrigatorio  = true;
        $mini->leitura      = $show;
        $mini->minimo       = 1;
        $mini->maximo       = 9999;
        $mini->largura      = 40;
        $mini->maxLength    = 4;
        $mini->dispForm     = 'col-6';
        $ret['pre_minimo']  = $mini->crInput();

        $mda               =  new MyCampo('pro_est_produto', 'pre_maxdiaanterior');
        $mda->nome          = $mda->id = "pre_maxdiaanterior[$pos]";
        $mda->valor        = (isset($dados['pre_maxdiaanterior'])) ? $dados['pre_maxdiaanterior'] : 'N';
        $mda->obrigatorio  = true;
        $mda->leitura      = $show;
        $mda->dispForm     = 'col-4';
        $mda->opcoes         = $simnao;
        $mda->selecionado    = $mda->valor;
        $mda->funcChan         = "mostraOcultaCampo(this,'S','pre_porcmaximo\\[$pos\\]');mostraOcultaCampo(this,'N','pre_maximo\\[$pos\\]')";
        $ret['pre_maxdiaanterior']  = $mda->cr2opcoes();

        $pmax               =  new MyCampo('pro_est_produto', 'pre_porcmaximo');
        $pmax->nome          = $pmax->id = "pre_porcmaximo[$pos]";
        $pmax->valor        = (isset($dados['pre_porcmaximo'])) ? $dados['pre_porcmaximo'] : 0;
        $pmax->obrigatorio  = false;
        $pmax->leitura      = $show;
        $pmax->minimo       = 0;
        $pmax->maximo       = 999;
        $pmax->maxLength    = 3;
        $pmax->largura      = 40;
        $pmax->dispForm     = 'col-4';
        $ret['pre_porcmaximo']  = $pmax->crInput();

        $maxi               =  new MyCampo('pro_est_produto', 'pre_maximo');
        $maxi->nome          = $maxi->id = "pre_maximo[$pos]";
        $maxi->valor        = (isset($dados['pre_maximo'])) ? $dados['pre_maximo'] : 0;
        $maxi->obrigatorio  = false;
        $maxi->leitura      = $show;
        $maxi->minimo       = 1;
        $maxi->maximo       = 9999;
        $maxi->maxLength    = 4;
        $maxi->largura      = 40;
        $maxi->dispForm     = 'col-4';
        $ret['pre_maximo']  = $maxi->crInput();

        $suge               =  new MyCampo('pro_est_produto', 'pre_sugerida');
        $suge->nome          = $suge->id = "pre_sugerida[$pos]";
        $suge->valor        = (isset($dados['pre_sugerida'])) ? $dados['pre_sugerida'] : 0;
        $suge->obrigatorio  = false;
        $suge->leitura      = $show;
        $suge->minimo       = 0;
        $suge->maximo       = 9999;
        $suge->largura      = 40;
        $suge->maxLength    = 4;
        $suge->dispForm     = 'col-3';
        $ret['pre_sugerida']  = $suge->crInput();

        $cbf               =  new MyCampo('pro_est_produto', 'pre_cbfabricante');
        $cbf->nome          = $cbf->id = "pre_cbfabricante[$pos]";
        $cbf->valor        = (isset($dados['pre_cbfabricante'])) ? $dados['pre_cbfabricante'] : 'S';
        $cbf->obrigatorio    = true;
        $cbf->leitura        = $show;
        $cbf->dispForm       = 'col-3';
        $cbf->opcoes         = $simnao;
        $cbf->selecionado    = $cbf->valor;
        $cbf->funcChan         = "mostraOcultaCampo(this,'S','pre_undfabricante\\[$pos\\]')";
        $ret['pre_cbfabricante']  = $cbf->cr2opcoes();

        $unf               =  new MyCampo('pro_est_produto', 'pre_undfabricante');
        $unf->nome          = $unf->id = "pre_undfabricante[$pos]";
        $unf->valor        = (isset($dados['pre_undfabricante'])) ? $dados['pre_undfabricante'] : 'N';
        $unf->obrigatorio  = true;
        $unf->leitura      = $show;
        $unf->dispForm     = 'col-3';
        $unf->opcoes         = $simnao;
        $unf->selecionado    = $unf->valor;
        $ret['pre_undfabricante']  = $unf->cr2opcoes();

        $cbl               =  new MyCampo('pro_est_produto', 'pre_cblote');
        $cbl->nome          = $cbl->id = "pre_cblote[$pos]";
        $cbl->valor        = (isset($dados['pre_cblote'])) ? $dados['pre_cblote'] : 'S';
        $cbl->obrigatorio  = true;
        $cbl->leitura      = $show;
        $cbl->dispForm     = 'col-3';
        $cbl->opcoes         = $simnao;
        $cbl->selecionado    = $cbl->valor;
        $cbl->funcChan         = "mostraOcultaCampo(this,'S','pre_undlote\\[$pos\\]')";
        $ret['pre_cblote']  = $cbl->cr2opcoes();

        $unl               =  new MyCampo('pro_est_produto', 'pre_undlote');
        $unl->nome          = $unl->id = "pre_undlote[$pos]";
        $unl->valor        = (isset($dados['pre_undlote'])) ? $dados['pre_undlote'] : 'N';
        $unl->obrigatorio  = true;
        $unl->leitura      = $show;
        $unl->dispForm     = 'col-3';
        $unl->opcoes         = $simnao;
        $unl->selecionado    = $unl->valor;
        $ret['pre_undlote']  = $unl->cr2opcoes();

        $cbm               =  new MyCampo('pro_est_produto', 'pre_cbmisturador');
        $cbm->nome          = $cbm->id = "pre_cbmisturador[$pos]";
        $cbm->valor        = (isset($dados['pre_cbmisturador'])) ? $dados['pre_cbmisturador'] : 'S';
        $cbm->obrigatorio  = true;
        $cbm->leitura      = $show;
        $cbm->dispForm     = 'col-3';
        $cbm->opcoes         = $simnao;
        $cbm->selecionado    = $cbm->valor;
        $cbm->funcChan         = "mostraOcultaCampo(this,'S','pre_undmisturador\\[$pos\\]')";
        $ret['pre_cbmisturador']  = $cbm->cr2opcoes();

        $unm               =  new MyCampo('pro_est_produto', 'pre_undmisturador');
        $unm->nome          = $unm->id = "pre_undmisturador[$pos]";
        $unm->valor        = (isset($dados['pre_undmisturador'])) ? $dados['pre_undmisturador'] : 'N';
        $unm->obrigatorio  = true;
        $unm->leitura      = $show;
        $unm->dispForm     = 'col-3';
        $unm->opcoes         = $simnao;
        $unm->selecionado    = $unm->valor;
        $ret['pre_undmisturador']  = $unm->cr2opcoes();

        $valor = isset($dados['pre_estdataatual'])
            ? $dados['pre_estdataatual']
            : (isset($dados['cla_desestdataatual']) && $dados['cla_desestdataatual'] != ''
                ? substr($dados['cla_desestdataatual'], 0, 1)
                : 'N');

        $eda                 = new MyCampo('pro_est_produto', 'pre_estdataatual', false);
        $eda->nome          = $eda->id = "pre_estdataatual[$pos]";
        $eda->valor          = $valor;
        $eda->leitura        = $show;
        $eda->opcoes         = $simnao;
        $eda->selecionado    = $eda->valor;
        // $eda->classep        = 'mb2';
        $eda->dispForm       = 'col-3';
        $ret['pre_estdataatual']          = $eda->cr2opcoes();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("Produto/addCampo/") . "','gestao_de_estoque',this)";
        $ret['bt_add']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('gestao_de_estoque',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_del']   = $del->crBotao();

        return $ret;
    }
}
