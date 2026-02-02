<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Config\EntCfgTela;

class ConfigTelaListaModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'cfg_tela_lista';
    protected $primaryKey       = 'lis_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntCfgTela::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'lis_id',
        'tel_id',
        'lis_campo',
        'lis_rotulo',
        'lis_atualizado'
    ];


    protected $skipValidation   = true;


    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

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
        $ret = $builder->get()->getResult();
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
