<?php namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigTelaListaModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'cfg_tela_lista';
    protected $primaryKey       = 'lis_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['lis_id',
                                    'tel_id',
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
     * Retorna os dados da TelaLista, pelo termo (nome) informado
     * Utilizado nas Seleções de TelaLista
     *
     * @param mixed $termo
     * @return array
     */
    public function getListagem($Telalista)
    {
        $clas = ['tel_id' => $Telalista];
        $db = db_connect('default');
        $builder = $db->table('cfg_tela_lista');
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
     * @param string $Telalista
     * @return string
     */
    public function excluiCampoLista($Telalista)
    {
        $db = db_connect('default');
        $builder = $db->table('cfg_tela_lista');
        $builder->where("tel_id", $Telalista);
        // $builder->groupStart();
        // $builder->where("lis_atualizado != '$data_atu'");
        // $builder->orWhere("lis_atualizado IS NULL");
        // $builder->groupEnd();
        $ret = $builder->delete();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
