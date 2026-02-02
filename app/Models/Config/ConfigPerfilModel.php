<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Config\EntCfgPerfil;

class ConfigPerfilModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table      = 'cfg_perfil';
    protected $view       = 'vw_cfg_perfil_item_relac';
    protected $primaryKey = 'prf_id';
    protected $useAutoIncrement = true;

    protected $returnType     = EntCfgPerfil::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'prf_id',
        'prf_nome',
        'prf_dashboard',
        'prf_descricao',
    ];

    protected $skipValidation   = true;
    protected $deletedField  = "prf_excluido";
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert   = ["logInsert"];
    protected $afterUpdate   = ["logUpdate"];
    protected $afterDelete   = ["logDelete"];

    protected $logdb;

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

    public function getPerfil($id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where('prf_excluido', null);
        if ($id) {
            $builder->where('prf_id', $id);
            return $builder->get()->getFirstRow();
        }

        // Caso contrário, retorna todos os registros
        $builder->orderBy('prf_id');
        return $builder->get()->getResult();
    }

    public function getPerfilIdSel()
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('prf_id >', 1);
        // $builder->where('prf_excluido', null);
        $builder->orderBy('prf_id');
        return $builder->get()->getResult();
    }
}
