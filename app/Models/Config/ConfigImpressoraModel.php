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
    protected $useAutoIncrement = true;

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


    public function getImpressora($imp_id = null)
    {
        // Conecta ao banco padrão
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->where('imp_excluido', null);

        // Se informado um ID, retorna apenas a impressora correspondente
        if ($imp_id) {
            $builder->where('imp_id', $imp_id);
            return $builder->get()->getFirstRow(); 
        }

        $builder->orderBy('imp_nome');
        return $builder->get()->getResult(); 
    }
    

    public function getImpressoraSearch($termo)
    {
        // Monta filtro LIKE para busca pelo nome
        $alike = ['imp_nome' => $termo . '%'];

        $db = db_connect('default');
        $builder = $db->table($this->view);
        // Seleciona apenas os campos necessários e aplica filtros
        return $builder->select(['imp_id', 'imp_nome'])
            ->where('imp_excluido', null)
            ->like($alike)
            ->get()
            ->getResult();
    }
}
