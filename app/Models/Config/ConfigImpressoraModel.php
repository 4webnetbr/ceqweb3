<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Config\EntCfgImpressora;

class ConfigImpressoraModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_impressora';
    protected $view             = 'cfg_impressora';
    protected $primaryKey       = 'imp_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntCfgImpressora::class;
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'imp_nome',
        'imp_ip',
        'imp_porta',
    ];

    protected $deletedField  = 'imp_excluido';

    protected $validationRules = [
        'imp_nome'    => 'required|min_length[5]',
        'imp_ip'      => 'required',
        'imp_porta'   => 'required',
    ];

    protected $validationMessages = [
        'imp_nome' => [
            'required'      => 'O campo Nome é Obrigatório',
            'min_length'    => 'O campo Nome exige pelo menos 5 Caracteres.',
            'isUniqueValue' => '8',
        ],
        'imp_ip' => [
            'required' => 'O campo IP é Obrigatório',
        ],
        'imp_porta' => [
            'required' => 'O campo Porta é Obrigatório',
        ],
    ];


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


    public function getImpressora($imp_id = false)
    {
        $this->builder()->select('*');
        if ($imp_id) {
            $this->builder()->where('imp_id', $imp_id);
        }
        $this->builder()->where('imp_excluido', null);
        $this->builder()->orderBy('imp_nome');
        return $this->builder()->get()->getResult();
    }


    public function getImpressoraId($imp_id = false)
    {
        $this->builder()->select('*');
        if ($imp_id) {
            $this->builder()->where('imp_id', $imp_id);
        }
        $this->builder()->where('imp_excluido', null);
        $this->builder()->orderBy('imp_id');
        return $this->builder()->get()->getResult();
    }


    public function getImpressoraSearch($termo)
    {
        $array = ['imp_nome' => $termo . '%'];
        $this->builder()->select(['imp_id', 'imp_nome']);
        $this->builder()->where('imp_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResult();
    }
}
