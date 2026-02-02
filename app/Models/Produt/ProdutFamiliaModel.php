<?php

namespace App\Models\Produt;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Produto\EntFamilia;

class ProdutFamiliaModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_familia';
    protected $view             = 'vw_pro_sap_familia_relac';
    protected $primaryKey       = 'fam_codFam';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntFamilia::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'fam_codFam',
                                    'fam_desFam',
                                    'ori_codOri',
                                    'fam_codDescricao',
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

    public function getFamilia($fam_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        if ($fam_id) {
            $builder->where('fam_codFam', $fam_id);
        }
        return $builder->get()->getResult(); 
    }

    public function getFamiliaSearch($termo)
    {
        $array = ['fam_desFam' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResult(); 
    }

    public function getFamiliaOrigem($termo)
    {
        $array = ['ori_codOri' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResult(); 
    }
}
