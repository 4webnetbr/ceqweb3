<?php

namespace App\Models\Produt;

use App\Entities\Produto\EntProdutProduto;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ProdutOrigemModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_origem';
    protected $view             = 'pro_sap_origem';
    protected $primaryKey       = 'ori_codOri';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntProdutProduto::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'ori_codOri',
                                    'ori_desOri',
                                    'ori_codDescricao',
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

    public function getOrigem($ori_id = false)
    {
        $this->builder()->select('*');
    
        if ($ori_id) {
            $this->builder()->where('ori_codOri', $ori_id);
        }
    
        return $this->builder()->get()->getResult();
    }
    
    public function getOrigemSearch($termo)
    {
        $array = ['ori_desOri' => $termo . '%'];
    
        $this->builder()->select('*');
        $this->builder()->like($array);
    
        return $this->builder()->get()->getResult();
    }
}
