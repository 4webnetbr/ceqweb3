<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Config\EntCfgEmpresa;

class ConfigEmpresaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_empresa';
    protected $view             = 'cfg_empresa';
    protected $primaryKey       = 'emp_codfil';
    protected $useAutoIncremodt = false;

    protected $returnType       = EntCfgEmpresa::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'emp_codfil',                               
        'emp_codemp',                                
        'emp_nomfil',                               
        'emp_sigfil',                                  
        'emp_numcgc',            
        'emp_insest'
    
        ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert    = ['depoisInsert'];
    protected $afterUpdate    = ['depoisUpdate'];
    protected $afterDelete    = ['depoisDelete'];

    // protected $logdb;

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }
    

    public function getEmpresa($emp_codfil = false)
    {
        $db      = db_connect($this->DBGroup);
        $builder = $db->table($this->view);
        $builder->select('*');

        if ($emp_codfil) {
            $builder->where('emp_codfil', $emp_codfil);
            return $builder->get()->getFirstRow(EntCfgEmpresa::class); 
        }

        return $builder->get()->getResult(EntCfgEmpresa::class); 
    }


    public function getEmpresasSearch(string $termo)
    {
        $db      = db_connect($this->DBGroup);
        $builder = $db->table($this->view);

        $builder->select(['emp_codfil', 'emp_nomfil', 'emp_sigfil']);
        $builder->like('emp_nomfil', $termo, 'after');
        $builder->orderBy('emp_nomfil');

        return $builder->get()->getResult(EntCfgEmpresa::class);
    }  

}
