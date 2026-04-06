<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Estoque\EntInspecao;
use App\Entities\Estoque\EntRequisicao;

class EstoquRequisicaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_requisicao';
    protected $view             = 'vw_est_requisicao_lista_relac';
    protected $viewlista        = 'vw_est_requisicao_lista_relac';
    protected $viewoutra        = 'vw_est_requisicao_produto_relac';
    protected $viewatend        = 'vw_est_requisicao_produto_atendimento_relac';
    protected $viewinspe        = 'vw_est_requisicao_produto_inspecao_relac';
    protected $primaryKey       = 'req_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = EntInspecao::class;
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

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;


    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
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
        // $perfil = session()->get('usu_perfil_id');
        // $builder->like('prf_id', $perfil);
        $builder->orderBy('req_data');
        return $builder->get()->getResult();
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
        // debug($builder->getCompiledSelect(), true);
        $ret = $builder->get()->getResult();
        return $ret;
    }

    public function getRequisicaoProdutos($req_id = false, $tipo = '')
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewatend);
        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        if ($tipo == 'conferencia') {
            // $builder->where('rep_quantia = rpa_atendida + rpa_cancelada', null, false);
            $builder->where('rpa_conferida', 0);
        }
        // debug($builder->getCompiledSelect(), true);
        $ret = $builder->get()->getResult();
        return $ret;
    }

    public function getRequisicaoRep($rep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->viewoutra);
        $builder->select('*');
        if ($rep_id) {
            $builder->where('rep_id', $rep_id);
        }

        return $builder->get()->getResult();
    }

    public function getProdutoRequisicao($produto)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where('pro_id', $produto);
        return $builder->get()->getResult();
    }

    public function getRequisicaoConferencia($req_id = false, $status = false)
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
        // só lista as requisições cujo Tipo de Movimentação Exige Conferencia
        $builder->where('tmo_conferencia', 'S');
        // $perfil = session()->get('usu_perfil_id');
        // $builder->like('prf_id', $perfil);
        $builder->orderBy('req_data');
        return $builder->get()->getResult();
    }
}
