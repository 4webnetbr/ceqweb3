<?php namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigClasseListaModel extends Model
{
    protected $DBGroup          = 'dbConfig';

    protected $table            = 'cfg_classe_lista';
    protected $primaryKey       = 'lis_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['lis_id',
                                    'cls_id',
                                    'lis_campo',
                                    'lis_rotulo',
                                    'lis_atualizado'
                                ];


    protected $skipValidation   = true;


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
    protected function depoisInsert(array $data) {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
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
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
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
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    /**
     * getListagem
     *
     * Retorna os dados da ClasseLista, pelo termo (nome) informado
     * Utilizado nas Seleções de ClasseLista
     *
     * @param mixed $termo
     * @return array
     */
    public function getListagem($classelista)
    {
        $clas = ['cls_id' => $classelista];
        $db = db_connect('dbConfig');
        $builder = $db->table('cfg_classe_lista');
        $builder->select('*');
        $builder->where($clas);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * exclui_campo_lista
     *
     * Exclui campos da Listagem que não foram atualizados
     *
     * @param string $classelista
     * @return string
     */
    public function excluiCampoLista($classelista)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('cfg_classe_lista');
        $builder->where("cls_id", $classelista);
        // $builder->groupStart();
        // $builder->where("lis_atualizado != '$data_atu'");
        // $builder->orWhere("lis_atualizado IS NULL");
        // $builder->groupEnd();
        $ret = $builder->delete();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
