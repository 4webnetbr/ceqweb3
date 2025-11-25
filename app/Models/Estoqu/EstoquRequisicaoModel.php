<?php

namespace App\Models\Estoqu;

use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutProdutoModel;
use CodeIgniter\Model;
use DateTime;

class EstoquRequisicaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_requisicao';
    protected $view             = 'vw_est_requisicao_lista_relac';
    protected $viewlista        = 'vw_est_requisicao_lista_relac';
    protected $viewoutra        = 'vw_est_requisicao_produto_relac';
    protected $primaryKey       = 'req_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'req_id',
        'req_data',
        'req_dataentrega',
        'tmo_id',
        'req_deporigem',
        'req_depdestino',
        'req_consdiaanterior',
        'req_medconsumodias',
        'req_meddias',
        'req_repetedias',
        'req_percseguranca',
        'req_observacao',
        'stt_id',

    ];

    // protected $deletedField  = 'req_excluido';

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

    public function getRequisicaoLista($req_id = false, $status = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewlista);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        if ($status) {
            $builder->whereIn('stt_id', $status);
        }
        $builder->orderBy('stt_ordem, req_data, req_id');
        $ret = $builder->get()->getResultArray();
        // debug($db->getLastQuery(), true);
        return $ret;
    }

    public function getRequisicao($req_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewlista);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        $builder->orderBy('req_data');
        return $builder->get()->getResultArray();
    }

    public function getRequisicaoProdutos($req_id = false, $pro_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewoutra);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        $ret = $builder->get()->getResultArray();
        // debug($db->getLastQuery(), true);

        return $ret;
    }

    public function getProdutoRequisicao($produto)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewoutra);
        $builder->select('*');
        $builder->where('pro_id', $produto);
        return $builder->get()->getResultArray();
    }

    public function getRequisicaoRep($rep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewoutra);
        $builder->select('*');
        if ($rep_id) {
            $builder->where('rep_id', $rep_id);
        }
        
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }


    public function defCampos($dados = false, $show = false, $tipo = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $id           =  new MyCampo('est_requisicao', 'req_id', false);
        $id->valor    = (isset($dados['req_id'])) ? $dados['req_id'] : '';
        $id->leitura  = $show;
        $ret['req_id']    = $id->crOculto();

        $hoje = new DateTime();
        $data                 = new MyCampo('est_requisicao', 'req_data', false);
        $data->valor          = (isset($dados['req_data'])) ? $dados['req_data'] : $hoje->format('Y-m-d H:i:s');
        $data->leitura        = true;
        $data->dispForm       = 'col-6';
        $data->classep        = 'mb3';
        $ret['req_data']          = $data->crInput();

        // $prev = new DateTime('+1 days');
        $data = new DateTime();
        $entr                 = new MyCampo('est_requisicao', 'req_dataentrega', false);
        $entr->valor          = (isset($dados['req_dataentrega'])) ? $dados['req_dataentrega'] : '';
        $entr->leitura        = $show;
        $entr->datamin         = $data->format('Y-m-d');
        $entr->obrigatorio    = true;
        $entr->dispForm       = 'col-6';
        $entr->classep        = 'mb3';
        if(!$show){
            $entr->funcBlur       = 'validaDataMinima(this)';
        }
        $ret['req_dataentrega']   = $entr->crInput();

        $tipomovs = new EstoquTipoMovimentacaoModel();
        $lst_tipomov = $tipomovs->getTipoMovimentacao(false, session()->get('usu_perfil_id'));
        $opc_tipomov = array_column($lst_tipomov, 'tmo_nome', 'tmo_id');

        $tmov                 = new MyCampo('est_requisicao', 'tmo_id', false);
        $tmov->valor          = (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $tmov->obrigatorio    = true;
        $tmov->selecionado    = [$tmov->valor];
        $tmov->opcoes         = $opc_tipomov;
        $tmov->largura        = 50;
        $tmov->leitura        = $show;
        if(!$show){
            $tmov->funcChan       = "buscaTipoMovimentacao(this,'req_deporigem','req_depdestino','req_deppadrao')";
        }
        $tmov->dispForm       = 'col-6';
        $ret['tmo_id'] = $tmov->crSelect();

        $mudi                 = new MyCampo('est_requisicao', 'req_repetedias', false);
        $mudi->valor          = (isset($dados['req_repetedias'])) ? $dados['req_repetedias'] : 0;
        $mudi->leitura        = $show;
        $mudi->minimo         = 0;
        $mudi->step           = 1;
        $mudi->maximo         = 10;
        $mudi->classep        = 'mb2';
        $mudi->dispForm       = 'col-6';
        $ret['req_repetedias']          = $mudi->crInput();

        $depositos = new EstoquDepositoModel();
        $lst_depos = $depositos->getDeposito();
        $opc_depos = array_column($lst_depos, 'dep_codDescricao', 'dep_codDep');

        $deor                 = new MyCampo('est_requisicao', 'req_deporigem', false);
        $deor->valor          = (isset($dados['req_deporigem'])) ? $dados['req_deporigem'] : '';
        $deor->obrigatorio    = true;
        $deor->selecionado    = [$deor->valor];
        $deor->opcoes         = $opc_depos;
        $deor->largura        = 50;
        $deor->dispForm       = 'col-6';
        if (isset($dados['req_deporigem'])) {
            $deor->leitura      = true;
        }
        $ret['req_deporigem'] = $deor->crSelect();

        $depr                 = new MyCampo('est_requisicao', 'req_deporigem', false);
        $depr->id = $depr->nome = 'req_deppadrao';
        $depr->valor          = (isset($dados['req_deppadrao'])) ? $dados['req_deppadrao'] : '';
        $ret['req_deppadrao'] = $depr->crOculto();

        $dede                 = new MyCampo('est_requisicao', 'req_depdestino', false);
        $dede->valor          = (isset($dados['req_depdestino'])) ? $dados['req_depdestino'] : '';
        $dede->obrigatorio    = true;
        $dede->selecionado    = [$dede->valor];
        $dede->opcoes         = $opc_depos;
        $dede->largura        = 50;
        $dede->dispForm       = 'col-6';
        if (isset($dados['req_depdestino'])) {
            $dede->leitura      = true;
        }
        $ret['req_depdestino'] = $dede->crSelect();

        $coda                 = new MyCampo('est_requisicao', 'req_consdiaanterior', false);
        $coda->valor          = (isset($dados['req_consdiaanterior'])) ? $dados['req_consdiaanterior'] : 'S';
        $coda->leitura        = $show;
        $coda->opcoes         = $simnao;
        $coda->selecionado    = $coda->valor;
        $coda->classep        = 'semmb';
        $coda->dispForm       = ($show)?'col-6':'col-3';
        if(!$show){
            $coda->funcChan       = "mostraOcultaCampo(this,'N','req_medconsumodias,req_meddias');mudaCheck2opcoes(this,'req_medconsumodias');";
        }
        $ret['req_consdiaanterior']          = $coda->cr2opcoes();


        $mcdi                 = new MyCampo('est_requisicao', 'req_medconsumodias', false);
        $mcdi->valor          = (isset($dados['req_medconsumodias'])) ? $dados['req_medconsumodias'] : 'N';
        $mcdi->leitura        = $show;
        $mcdi->opcoes         = $simnao;
        $mcdi->selecionado    = $mcdi->valor;
        $mcdi->classep        = 'mb2';
        $mcdi->dispForm       = ($show)?'col-6':'col-3';
        if(!$show){
            $mcdi->funcChan       = "mostraOcultaCampo(this,'S','req_meddias');mudaCheck2opcoes(this,'req_consdiaanterior');";
        }
        $ret['req_medconsumodias']          = $mcdi->cr2opcoes();

        $medi                 = new MyCampo('est_requisicao', 'req_meddias', false);
        $medi->valor          = (isset($dados['req_meddias'])) ? $dados['req_meddias'] : 2;
        $medi->leitura        = $show;
        $medi->minimo         = 2;
        $medi->step           = 1;
        $medi->maximo         = 100;
        $medi->classep        = 'mb2';
        $medi->dispForm       = 'col-3';
        $ret['req_meddias']          = $medi->crInput();

        $pseg                 = new MyCampo('est_requisicao', 'req_percseguranca', false);
        $pseg->valor          = (isset($dados['req_percseguranca'])) ? $dados['req_percseguranca'] : 0;
        $pseg->leitura        = $show;
        $pseg->obrigatorio    = true;
        $pseg->minimo         = 0;
        $pseg->step           = 1;
        $pseg->maximo         = 100;
        $pseg->classep        = 'mb2';
        $pseg->dispForm       = 'col-3';
        $ret['req_percseguranca']      = $pseg->crInput();

        $obsv                 = new MyCampo('est_requisicao', 'req_observacao', false);
        $obsv->valor          = (isset($dados['req_observacao'])) ? $dados['req_observacao'] : '';
        $obsv->leitura        = $show;
        $obsv->classep        = 'mb2';
        $obsv->dispForm       = 'col-12';
        $ret['req_observacao']      = $obsv->crInput();

        $prod                 = new MyCampo();
        $prod->nome = $prod->id = 'pro_id';
        $prod->label          = 'Produto';
        $prod->valor = $prod->selecionado = '';
        $prod->leitura        = $show;
        $prod->urlbusca       = base_url('buscas/buscaProduto');
        $prod->classep        = 'mb2';
        $prod->dispForm       = 'col-12';
        $prod->largura        = 50;
        $prod->opcoes         = [];
        $ret['pro_id']        = $prod->crSelbusca();

        $codb                 = new MyCampo('pro_sap_lote', 'lot_codbar', false);
        $codb->valor          = '';
        $codb->leitura        = false;
        $codb->classep        = 'mb2';
        $codb->dispForm       = 'col-6';
        $codb->tamanho        = '30';
        $codb->largura        = '30';
        $codb->size           = '30';
        $codb->funcBlur       = 'validaCodBar(this)';
        if($tipo && $tipo == 'conf'){
            $codb->funcBlur       = 'validaCodBarConf(this)';
        }
        $ret['lot_codbar']      = $codb->crInput();

        $btca            = new MyCampo();
        $btca->nome      = "bt_carregar";
        $btca->id        = "bt_carregar";
        $btca->i_cone    = "<i class='fas fa-refresh fs-3'></i> <scan class='mx-3'>Carregar Produtos</scan>";
        $btca->label     = $btca->place     = "Carregar Produtos";
        $btca->classep   = "btn-outline-success btn-sm align-items-center d-flex m-3";
        $btca->funcChan  = "carregarProdutos('" . base_url("Requisicao/produtos/") . "','produtos',this)";
        $ret['bt_carregar']   = $btca->crBotao();

        $usua                 = new MyCampo();
        $usua->valor          = (isset($dados['usu_nome'])) ? $dados['usu_nome'] : '';
        $usua->id = $usua->nome        = 'usu_nome';
        $usua->label          = 'Usuário';
        $usua->size           = 50;
        $usua->largura        = 50;
        $usua->dispForm       = 'col-6';
        $usua->leitura      = true;
        $ret['usu_nome'] = $usua->crInput();

        $stat                 = new MyCampo();
        $stat->valor          = (isset($dados['stt_nome'])) ? fmtEtiquetaCorBst($dados['stt_cor'], $dados['stt_nome']) : '';
        $stat->id = $stat->nome        = 'stt_nome';
        $stat->label          = 'Status';
        $stat->size           = 50;
        $stat->largura        = 50;
        $stat->dispForm       = 'col-4';
        $stat->leitura      = true;
        $ret['stt_nome'] = $stat->crShow();

        return $ret;
    }


    public function defCamposProdutoAte($dados = false, $show = false)
    {
        $ret = [];
        // debug($dados);
        $canc                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_cancelada', false);
        $canc->id = $canc->nome = "rpa_cancelada_".$dados['rep_id'];
        $canc->valor          = $dados['rpa_cancelada']??0;
        $canc->label          = '';
        $canc->leitura        = false;
        if(intval($dados['rep_quantia']) == (intval($dados['rpa_atendida']) + intval($dados['rpa_cancelada']))){
            $canc->leitura        = true;
        }
        $canc->classep        = 'semmb';
        $canc->minimo           = 0;
        $canc->maximo           = $dados['rep_quantia'];
        $canc->dispForm       = 'col-12';
        $canc->funcChan       = 'acertaSaldoReq(this)';
        $canc->size           = 4;
        $canc->largura        = 30;
        $ret['rpa_cancelada']      = $canc->crInput();


        $aten                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_atendida', false);
        $aten->id = $aten->nome = "rpa_atendida_".$dados['rep_id'];
        $aten->valor          = $dados['rpa_atendida']?$dados['rpa_atendida']:0;
        $aten->label          = '';
        $aten->size           = 4;
        $aten->leitura        = true;
        if($dados['pre_cbfabricante'] == 'N' && $dados['pre_undfabricante'] == 'N'){
            $aten->leitura        = false;
        }
        if(intval($dados['rep_quantia']) == (intval($dados['rpa_atendida']) + intval($dados['rpa_cancelada']))){
            $aten->leitura        = true;
        }
        $aten->classep        = 'semmb';
        $aten->dispForm       = 'col-12';
        $aten->minimo           = 0;
        $aten->maximo           = $dados['rep_quantia'];
        $aten->funcChan       = 'acertaSaldoReq(this)';
        $aten->largura        = 30;
        $ret['rpa_atendida']      = $aten->crInput(); 
        return $ret;
    }

    public function defCamposProdutoConf($dados = false, $show = false)
    {
        $ret = [];

        $aten                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_atendida', false);
        $aten->id = $aten->nome = "rpa_atendida_".$dados['rep_id'];
        $aten->valor          = $dados['rpa_atendida']??0;
        $aten->label          = '';
        $aten->minimo           = 0;
        $aten->maximo           = $dados['rep_quantia'];
        $ret['rpa_atendida']      = $aten->crOculto(); 

        $conf                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_conferida', false);
        $conf->id = $conf->nome = "rpa_conferida_".$dados['rep_id'];
        $conf->valor          = $dados['rpa_conferida']??0;
        $conf->label          = '';
        $conf->leitura        = true;
        $conf->classep        = 'semmb';
        $conf->dispForm       = 'col-12';
        $conf->minimo         = 0;
        $conf->maximo         = intval($dados['rpa_atendida']);
        $conf->funcChan       = 'acertaSaldoConf(this)';
        $conf->largura        = 20;
        if($dados['pre_cbfabricante'] == 'N' && $dados['pre_undfabricante'] == 'N'){
            $conf->leitura        = false;
        }
        if(intval($dados['rep_quantia']) == intval($dados['rpa_conferida'])){
            $conf->leitura        = true;
        }
        $ret['rpa_conferida']      = $conf->crInput(); 
        return $ret;
    }

}
