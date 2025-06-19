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
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        if ($status) {
            $builder->whereIn('stt_id', $status);
        }
        $builder->orderBy('req_data');
        return $builder->get()->getResultArray();
    }

    public function getRequisicao($req_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        $builder->orderBy('req_data');
        return $builder->get()->getResultArray();
    }

    public function getRequisicaoProdutos($req_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_requisicao_produto');
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getProdutoRequisicao($produto)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_requisicao_produto');
        $builder->select('*');
        $builder->where('pro_id', $produto);
        return $builder->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
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
        $data->valor          = (isset($dados['req_data'])) ? $dados['req_data'] : $hoje->format('Y-m-d');
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
        $entr->funcBlur       = 'validaDataMinima(this)';
        $ret['req_dataentrega']   = $entr->crInput();

        $tipomovs = new EstoquTipoMovimentacaoModel();
        $lst_tipomov = $tipomovs->getTipoMovimentacao();
        $opc_tipomov = array_column($lst_tipomov, 'tmo_nome', 'tmo_id');

        $tmov                 = new MyCampo('est_requisicao', 'tmo_id', false);
        $tmov->valor          = (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $tmov->obrigatorio    = true;
        $tmov->selecionado    = [$tmov->valor];
        $tmov->opcoes         = $opc_tipomov;
        $tmov->largura        = 50;
        $tmov->funcChan       = "buscaTipoMovimentacao(this,'req_deporigem','req_depdestino')";
        $tmov->dispForm       = 'col-6';
        $ret['tmo_id'] = $tmov->crSelect();

        $mudi                 = new MyCampo('est_requisicao', 'req_repetedias', false);
        $mudi->valor          = (isset($dados['req_repetedias'])) ? $dados['req_repetedias'] : 1;
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
        $coda->dispForm       = 'col-3';
        $coda->funcChan       = "mostraOcultaCampo(this,'N','req_medconsumodias,req_meddias');mudaCheck2opcoes(this,'req_medconsumodias');";
        $ret['req_consdiaanterior']          = $coda->cr2opcoes();


        $mcdi                 = new MyCampo('est_requisicao', 'req_medconsumodias', false);
        $mcdi->valor          = (isset($dados['req_medconsumodias'])) ? $dados['req_medconsumodias'] : 'N';
        $mcdi->leitura        = $show;
        $mcdi->opcoes         = $simnao;
        $mcdi->selecionado    = $mcdi->valor;
        $mcdi->classep        = 'mb2';
        $mcdi->dispForm       = 'col-3';
        $mcdi->funcChan       = "mostraOcultaCampo(this,'S','req_meddias')";
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

        $btca            = new MyCampo();
        $btca->nome      = "bt_carregar";
        $btca->id        = "bt_carregar";
        $btca->i_cone    = "<i class='fas fa-refresh fs-3'></i> <scan class='mx-3'>Carregar Produtos</scan>";
        $btca->label     = $btca->place     = "Carregar Produtos";
        $btca->classep   = "btn-outline-success btn-sm align-items-center d-flex m-3";
        $btca->funcChan  = "carregarProdutos('" . base_url("Requisicao/produtos/") . "','produtos',this)";
        $ret['bt_carregar']   = $btca->crBotao();

        return $ret;
    }


    public function defCamposProduto($dados = false, $show = false)
    {
        $produtos       = new ProdutProdutoModel();
        $lst_produts    = $produtos->getProdutoSemRequisicao();
        $opc_prods      = array_column($lst_produts, 'pro_despro', 'pro_id');

        $prod                   = new MyCampo('pro_req_produto', 'pro_id', false);
        $prod->valor            = (isset($dados['pro_id'])) ? implode(",", $dados['pro_id']) : '';
        $prod->selecionado      = (isset($dados['pro_id'])) ? $dados['pro_id'] : [];
        $prod->opcoes           = $opc_prods;
        $prod->valid            = isset($dados['pro_id']) ? true : false;
        $prod->largura          = 50;
        $prod->pai              = "cla_id";
        $prod->urlbusca         = base_url('buscas/buscaProdutoClasse');
        $ret['pro_id']          = $prod->crDependeMultiplo();

        return $ret;
    }
}
