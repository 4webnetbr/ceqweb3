<?php 

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigPerfilModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table      = 'cfg_perfil';
    protected $view       = 'vw_cfg_perfil_item_relac';
    protected $primaryKey = 'prf_id';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['prf_id',
                                'prf_nome',
                                'prf_dashboard',
                                'prf_descricao',
                                ];

    protected $skipValidation   = true;
    protected $deletedField  = "prf_excluido";
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert   = ["depoisInsert"];
    protected $afterUpdate   = ["depoisUpdate"];
    protected $afterDelete   = ["depoisDelete"];

    protected $logdb;

/**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data["id"];
        $logdb->insertLog($this->table, "Incluído", $registro, $data["data"]);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data["id"][0];
        $logdb->insertLog($this->table, "Alterado", $registro, $data["data"]);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data["id"][0];
        $logdb->insertLog($this->table, "Excluído", $registro, $data["data"]);
        return $data;
    }

    public function getPerfil($id = false)
    {
        $this->builder()->select();
        if ($id) {
            $this->builder()->where('prf_id', $id);
        }
        $this->builder()->where('prf_excluido', null);
        $this->builder()->orderBy('prf_id');
        return $this->find();
    }

    public function getPerfilIdSel()
    {
        $this->builder()->select();
        $this->builder()->where('prf_id >', 1);
        $this->builder()->where('prf_excluido', null);
        $this->builder()->orderBy('prf_id');
        return $this->find();
    }
}
