<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgModulo;
use App\Models\LogMonModel;

class ConfigModuloModel extends Model
{
    protected $table            = 'cfg_modulo';
    protected $primaryKey       = 'mod_id';

    protected $returnType       = EntCfgModulo::class;
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'mod_excluido';

    protected $allowedFields = [
        'mod_nome',
        'mod_icone',
        'mod_dbgroup',
        'mod_ativo'
    ];

    protected $validationRules = [
        'mod_nome' => 'required',
    ];

    protected $validationMessages = [
        'mod_nome' => [
            'required'      => 'O campo Nome do Módulo é Obrigatório',
        ],
    ];

    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function logUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function logDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }


    // Métodos de domínio
    public function getModulo($mod_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select('*');
        if ($mod_id) {
            $builder->where('mod_id', $mod_id);
        }
        $builder->where('mod_excluido', null);
        $builder->orderBy('mod_order');
        return $builder->get()->getResult();
    }

    public function getModulosSearch($termo)
    {
        $array = ['mod_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select(['mod_id', 'mod_nome', 'mod_icone']);
        $builder->where('mod_excluido', null);
        $builder->like($array);

        return $builder->get()->getResultArray();
    }
}
