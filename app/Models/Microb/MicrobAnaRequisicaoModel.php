<?php

namespace App\Models\Microb;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Microb\EntMicrobAnaRequisicao;


class MicrobAnaRequisicaoModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_mic_requisicao';
    protected $view             = 'vw_pro_mic_requisicao_relac';
    protected $primaryKey       = 'req_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntMicrobAnaRequisicao::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'req_id',
        'req_data',
        'req_lotemb',
        'usu_id',
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


    public function getListaRequisicao($req_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_requisicao_relac');
    
        $builder->select('*');
    
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
    
        $builder->orderBy('pro_despro');
    
        return $builder->get()->getResult();
    }
}
