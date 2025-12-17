<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgStatus;
use App\Models\LogMonModel;

class ConfigStatusModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_status';
    protected $view             = 'vw_cfg_status_relac';
    protected $primaryKey       = 'stt_id';

    protected $returnType       = EntCfgStatus::class;
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'stt_excluido';

    protected $allowedFields = [
        'stt_nome','stt_cor','mod_id','tel_id',
        'stt_exclusao','stt_edicao','stt_disponivel',
        'stt_ativo','stt_ordem'
    ];

    protected $validationRules = [
        'stt_nome' => 'required|min_length[5]|nome_status_existe[]',
    ];

    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

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

    /* ===== Métodos de domínio ===== */

    public function getStatus($stt_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        
        if ($stt_id) {
            $builder->where("stt_id", $stt_id);
            return $builder->get()->getFirstRow(); 
        }

        return $builder->get()->getResult(); 
    }

    public function getStatusTela(int $tel_id)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view); // usando a VIEW como fonte
        $builder->select('*');
        $builder->where('tel_id', $tel_id);
        $builder->orderBy('stt_ordem');

        // Retorna array de objetos da Entity
        return $builder->get()->getResult(\App\Entities\Config\EntCfgStatus::class);
    }

    public function getStatusNomeTela($tel_id, $nome, $stt_id)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where("tel_id", $tel_id);
        $builder->where("stt_nome", $nome);
        $builder->where("stt_id !=", $stt_id);
        $ret = $builder->get()->getResult(\App\Entities\Config\EntCfgStatus::class);
        // debug($this->db->getLastQuery());

        return $ret;
    }

    public function getStatusOrdem($stt_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_status_ordem');
        $builder->select('*');
        if ($stt_id) {
            $builder->where("stt_id", $stt_id);
        }
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function getStatusProximaOrdem($tel_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_status_ordem');
        $builder->selectMax('stt_ordem');
        if ($tel_id) {
            $builder->where("tel_id", $tel_id);
        }
        $ret = $builder->get()->getResult(\App\Entities\Config\EntCfgStatus::class);

        return $ret;
    }
    
    public function getStatusSearch($termo)
    {
        $array = ['stt_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->like($array);
        $ret = $builder->get()->getResult(\App\Entities\Config\EntCfgStatus::class);

        return $ret;
    }

    public function getProximaOrdem(int $tel_id): int
    {
        return (int) ($this->selectMax('stt_ordem')
                    ->where('tel_id', $tel_id)
                    ->first()
                    ->stt_ordem ?? 0) + 1;
    }
}
