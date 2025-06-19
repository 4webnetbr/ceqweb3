<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigMenuModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = "cfg_menu";
    protected $view             = "vw_cfg_menu_relac";
    protected $primaryKey       = "men_id";
    protected $useAutoIncrement = true;

    protected $returnType       = "array";
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ["men_id",
                                    "men_hierarquia",
                                    "men_menupai_id",
                                    "men_submenu_id",
                                    "mod_id",
                                    "tel_id",
                                    "men_etiqueta",
                                    "men_icone",
                                    "men_order",
                                    "men_metodo",
                                ];

    protected $skipValidation   = true;

    protected $deletedField  = "men_excluido";
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

    public function getMenu($men_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac');
        $builder->select('*');
        if ($men_id) {
            $builder->where("men_id", $men_id);
        }
        $ret = $builder->get()->getResultArray();
        // debug('getMenu');
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    public function getProximo($hierarquia = false, $submenu = false, $menupai = false)
    {
        if(!$hierarquia && !$submenu && !$menupai){
            $ret[0]['ordem'] = 0;
        } else {
            $db = db_connect('default');
            $builder = $db->table('vw_cfg_menu_ordem');
            $builder->select('*');
            if($hierarquia)
                $builder->where("men_hierarquia", $hierarquia);
            if($submenu)
                $builder->where("men_submenu_id", $submenu);
            if($menupai)
                $builder->where("men_menupai_id", $menupai);
            $ret = $builder->get()->getResultArray();
            // debug('getProximo');
            // debug($this->db->getLastQuery(), false);
        }
        // debug($ret);
        return $ret;
    }

    public function getMenuPai($men_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac menu');
        $builder->select('*');
        if ($men_id) {
            $builder->where("menu.men_id", $men_id);
        }
        $builder->where("men_hierarquia", '2');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getSubMenu($men_pai)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac menu');
        $builder->select('*');
        if ($men_pai) {
            $builder->where("menu.men_menupai_id", $men_pai);
        }
        $builder->where("men_hierarquia", '3');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    public function getRaizMenuPerfil($perfil_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac');
        $builder->select('*');
        $builder->wherein("men_hierarquia", [1,2]);
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getMenuCompleto
     * @param mixed $perfil_id
     * @param mixed $tipo_usu
     * @return array
     */
    public function getMenuCompleto($perfil_id = false, $tipo_usu = 1)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_perfil_item_relac');
        $builder->select('*');
        if ($tipo_usu && $tipo_usu < 3) {
            $builder->where("men_tipo", $tipo_usu);
            $builder->groupStart();
        }
        if ($perfil_id) {
            $builder->where("prf_id", null);
            $builder->orWhere("prf_id", $perfil_id);
        }
        if ($tipo_usu && $tipo_usu < 3) {
            $builder->groupEnd();
        }
        $builder->orderBy('men_order');
        // $sql = $builder->getCompiledSelect();
        // debug($sql,true);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }


    public function getPrimeiroNivel($menu_id, $perfil_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac menu');
        $builder->select('*');
        if ($perfil_id) {
            $builder->join('cfg_perfil_item pit', '(pit.tel_id = menu.tel_id OR menu.tel_id IS NULL) 
                            AND pit.prf_id = ' . $perfil_id, 'inner');
        }
        $builder->where("men_menupai_id", $menu_id);
        $builder->groupStart();
        $builder->where("men_hierarquia", 3);
        $builder->orWhere("men_hierarquia", 4);
        $builder->where("men_submenu_id <", 1);
        $builder->groupEnd();
        $builder->groupBy('men_id');
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        // d($this->db->getLastQuery());
        return $ret;
    }

    public function getSegundoNivel($menu_id, $submenu, $perfil_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_menu_relac menu');
        if ($perfil_id) {
            $builder->join('cfg_perfil_item pit', '(pit.tel_id = menu.tel_id OR menu.tel_id IS NULL) 
                            AND pit.prf_id = ' . $perfil_id, 'inner');
        }
        $builder->select('*');
        $builder->where("men_menupai_id", $menu_id);
        $builder->where("men_submenu_id", $submenu);
        $builder->wherein("men_hierarquia", [4]);
        $builder->groupBy('men_id');
        $ret = $builder->get()->getResultArray();
        // d($this->db->getLastQuery(), false);
        return $ret;
    }
}
