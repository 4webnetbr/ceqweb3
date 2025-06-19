<?php namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigClasseModel extends Model
{
    protected $DBGroup          = 'dbConfig';

    protected $table            = 'cfg_classe';
    protected $primaryKey       = 'cls_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['cls_id',
                                    'mod_id',
                                    'cls_nome',
                                    'cls_icone',
                                    'cls_controler',
                                    'cls_metodo',
                                    'cls_texto_botao',
                                    'cls_tabela',
                                    'cls_descricao',
                                    'cls_regras_gerais',
                                    'cls_regras_cadastro',
                                    'cls_lista'
                                ];


    protected $skipValidation   = true;

    // protected $createdField  = 'cls_criado';
    // protected $updatedField  = 'cls_alterado';
    protected $deletedField  = 'cls_excluido';

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
     * getClasseId
     *
     * Retorna os dados da Classe, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getClasseId($cls_id = false)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_classe_relac');
        $builder->select('*');
        if ($cls_id) {
            $builder->where("cls_id", $cls_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getClasseSearch
     *
     * Retorna os dados da Classe, pelo termo (nome) informado
     * Utilizado nas Seleções de Classe
     *
     * @param mixed $termo
     * @return array
     */
    public function getClasseSearch($termo)
    {
        $s_nome = ['cls_nome' => $termo];
        $s_contro = ['cls_controler' => $termo];
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_classe_relac');
        $builder->select('*');
        $builder->like($s_contro);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getClasseModulo
     *
     * Retorna os dados da Classe, pelo termo (nome) informado
     * Utilizado nas Seleções de Classe
     *
     * @param mixed $termo
     * @return array
     */
    public function getClasseModulo($modu)
    {
        $s_modu = ['mod_id' => $modu];
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_classe_relac');
        $builder->select('*');
        $builder->where($s_modu);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getClasseSearch
     *
     * Retorna os dados da Classe, pelo perfil (id) informado
     * Utilizado nas Seleções de Classe
     *  
     * @param mixed $perfil
     * @return array
     */
    public function getClassePerfil($perfil = false)
    {
        $db = db_connect('dbConfig');
        $builder = $db->table('vw_cfg_classe_relac');
        $builder->select('*');
        if ($perfil) {
            $builder->join('cfg_perfil_item pit', 'pit.cls_id = vw_cfg_classe_relac.cls_id 
                            AND pit.prf_id = ' . $perfil, 'left');
            // $builder->where('pit.pit_perfil_id',$perfil);
        }
        $builder->orderBy('mod_nome');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
