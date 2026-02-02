<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Estoque\EntMovimento;

class EstoquTransacaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_sap_transacao';
    protected $view             = 'est_sap_transacao';
    protected $primaryKey       = 'tns_codtns';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntMovimento::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'tns_codtns',
                                    'tns_germvp',
                                    'tns_dettns',
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


    public function getTransacao($dep_id = false)
    {
        $this->builder()->select('*');
        if ($dep_id) {
            $this->builder()->where('tns_codtns', $dep_id);
        }
        return $this->builder()->get()->getResult();
    }

    public function getTransacaoSearch($termo)
    {
        $array = ['tns_codtns' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResult();
    }

}
