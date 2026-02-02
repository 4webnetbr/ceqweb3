<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigLayoutEtiqModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigEtiquetaCampoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_etiqueta_campo';
    protected $view             = 'vw_cfg_etiqueta_campo_relac';
    protected $primaryKey       = 'etc_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'etc_id',
        'etq_id',
        'etc_campo',
        'etc_rotulo',
        'etc_codbar',
        'etc_fonte',
        'etc_tamanho',
        'etc_negrito',
        'etc_italico',
        'etc_sublinhado',
        'etc_alinhamento',
        'etc_caracteres',
        'etc_linhas',
        'etc_colunas',
        'etc_atualizado',
    ];

    protected $validationRules = [
        'etc_campo'         => 'required',
        'etc_rotulo'        => 'required|min_length[5]|max_length[50]'
    ];

    protected $validationMessages = [
        'etc_campo' => [
            'required' => 'O Campo da Tabela é Obrigatório '
        ],

        'etc_rotulo' => [
            'required' => 'O Campo Rótulo é Obrigatório ',
            'min_length' => 'O Campo Rótulo exige pelo menos  5 caracteres ',
            'max_length' => 'O Campo Rótulo deve ter no máximo 50 Caracteres. '
        ],
    ];


    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getEtiquetaCampo($etq_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($etq_id) {
            $builder->where('etq_id', $etq_id);
        }
        $builder->orderBy('etc_id');
        $ret = $builder->get()->getResultArray();
        $sql = $this->db->getLastQuery();
        // debug($sql);

        return $ret;
    }

    public function getEtiquetaCampoSearch($termo)
    {
        $array = ['etq_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select(['*']);
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function excluiCampos($etq_id)
    {
        $db = db_connect('default');
        $builder = $db->table($this->table);
        $builder->where('etq_id', $etq_id);
        $ret = $builder->delete();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }
}
