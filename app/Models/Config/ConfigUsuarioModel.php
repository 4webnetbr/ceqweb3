<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigUsuarioModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'cfg_usuario';
    protected $view             = 'vw_cfg_usuario_relac';
    protected $primaryKey       = 'usu_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['usu_id', 
                                    'usu_nome',
                                    'usu_login',
                                    'usu_senha',
                                    'usu_email',
                                    'prf_id',
                                    'usu_status',
                                    'usu_dashboard'
                                    ];

    protected $deletedField  = 'usu_excluido';

    protected $skipValidation     = true;

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
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
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
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
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
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    /**
     * usuLogonConfig
     * Validação do Login de Usuário de CONFIG
     *
     * @param mixed $data - WHERE MONTADO NA FUNÇÃO LOGON da Classe Login
     * @return array
     */
    public function usuLogonConfig($data)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_usuario_relac');
        $builder->select('*');
        $builder->where($data);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());

        return $ret;
    }

    /**
     * getUsuarioId
     *
     * Retorna os dados do Usuário de Config, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getUsuarioId($id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_usuario_relac');
        $builder->select('*');
        if ($id) {
            $builder->where('usu_id', $id);
        }
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getUsuarioSearch
     *
     * Retorna os dados do Usuário de Config, pelo termo (nome) informado
     * Utilizado nas Seleções de Usuário
     *
     * @param mixed $termo
     * @return array
     */
    public function getUsuarioSearch($termo)
    {
        $array = ['usu_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_usuario_relac');
        $builder->select('*');
        $builder->like($array);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
