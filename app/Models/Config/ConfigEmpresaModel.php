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
    protected $useAutoIncrement = false;

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
    

    public function getEmpresa($emp_codfil = false)
    {
        // Conecta ao banco definido no DBGroup do model
        $db      = db_connect($this->DBGroup);
        $builder = $db->table($this->view);
        $builder->select('*');

        // Se informado o código da filial, retorna apenas uma empresa
        if ($emp_codfil) {
            $builder->where('emp_codfil', $emp_codfil);
            return $builder->get()->getFirstRow(EntCfgEmpresa::class); 
        }
        // Caso contrário, retorna todas as empresas
        return $builder->get()->getResult(EntCfgEmpresa::class); 
    }


    public function getEmpresasSearch(string $termo)
    {
        // Conecta ao banco definido no DBGroup do model
        $db      = db_connect($this->DBGroup);
        $builder = $db->table($this->view);

        // Seleciona apenas os campos necessários para a busca
        $builder->select(['emp_codfil', 'emp_nomfil', 'emp_sigfil']);
        $builder->like('emp_nomfil', $termo, 'after');
        $builder->orderBy('emp_nomfil');

        // Retorna os resultados encontrados como Entity
        return $builder->get()->getResult(EntCfgEmpresa::class);
    }  

}
