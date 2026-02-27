<?php

namespace App\Models\Config;

use App\Entities\Config\EntCfgTela;
use App\Models\LogMonModel;
use CodeIgniter\Model;


class ConfigTelaModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'cfg_tela';
    protected $view             = 'vw_cfg_tela_relac';
    protected $primaryKey       = 'tel_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntCfgTela::class;
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'tel_id',
        'mod_id',
        'tel_nome',
        'tel_icone',
        'tel_controler',
        'tel_metodo',
        'tel_texto_botao',
        'tel_model',
        'tel_descricao',
        'tel_regras_gerais',
        'tel_regras_cadastro',
        'tel_lista',
        'tel_ident'
    ];


    protected $skipValidation   = true;

    // protected $createdField  = 'tel_criado';
    // protected $updatedField  = 'tel_alterado';
    protected $deletedField  = 'tel_excluido';

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }

    /**
     * getTelaId
     *
     * Retorna os dados da Tela, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getTelaId($tel_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($tel_id) {
            $builder->where("tel_id", $tel_id);
        }
        $ret = $builder->get()->getResult();
        return $ret;
    }

    /**
     * getTelaSearch
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaSearch($termo)
    {
        $s_nome = ['tel_nome' => $termo];
        $s_contro = ['tel_controler' => $termo];
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->where($s_contro);

        $ret = $builder->get()->getResult();
        return $ret;
    }

    /**
     * getTelaModulo
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaModulo($modu)
    {
        $s_modu = ['mod_id' => $modu];
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->where($s_modu);

        $ret = $builder->get()->getResult();
        return $ret;
    }

    public function getTelas()
    {
        $db = db_connect('dbOcorrencia');
    
        $builder = $db->table('config_ceqweb_db.cfg_tela t');
        $builder->select('t.tel_id, t.tel_nome');
    
        $builder->orderBy('t.tel_nome', 'ASC');
    
        return $builder->get()->getResult();
    }

    /**
     * getTelaPerfil
     *
     * Retorna os dados da Tela, pelo perfil (id) informado
     * Utilizado nas Seleções de Tela
     *  
     * @param mixed $perfil
     * @return array
     */
    public function getTelaPerfil($perfil = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($perfil) {
            $builder->where('prf_id', $perfil);
            $builder->orWhere('prf_id', null);
        }
        $builder->orderBy('mod_nome');
        $ret = $builder->get()->getResult();
        return $ret;
    }
}
