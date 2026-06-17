<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquRequisicaoProdutoAtendimentoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_requisicao_produto_atendimento';
    protected $view             = '';
    protected $primaryKey       = 'rpa_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'rpa_id',
        'rep_id',
        'pro_id',
        'rpa_cancelada',
        'rpa_atendida',
        'rpa_data',
        'rpa_conferida',
        'rpa_data_conferencia',
        'rpa_aprovada',
        'rpa_data_inspecao'
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
        $registro = $data['id'][0] ?? $data['id'];
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

    public function getProdutoRequisicaoAtendimento($rep_id, $produto)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_requisicao_produto_atendimento');
        $builder->select('*');
        $builder->where('rep_id', $rep_id);
        $builder->where('pro_id', $produto);
        return $builder->get()->getResultArray();
    }

    public function getRequisicaoProdutoAtendimento($req_id)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_requisicao_produto_atendimento');
        $builder->join('est_requisicao_produto', 'est_requisicao_produto.rep_id = est_requisicao_produto_atendimento.rep_id');
        $builder->select('*');
        $builder->where('est_requisicao_produto.req_id', $req_id);
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

    public function getSaldoRequisicao($requisicao)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_saldo_requisicao_produto');
        $builder->select('*');
        $builder->where('req_id', $requisicao);
        return $builder->get()->getResultArray();
    }

    public function getRequisicaoPendencias($requisicao)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_requisicao_produto_pendencias');
        $builder->select('*');
        $builder->where('req_id', $requisicao);
        return $builder->get()->getResultArray();
    }
}
