<?php

namespace App\Models\Ocorre;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;

class OcorreModOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_mod_ocorrencia';
    protected $view             = 'vw_oco_mod_ocorrencia_relac';
    protected $primaryKey       = 'moc_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntOcoModOcorrencia::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
                'moc_id',
                'moc_nome',
                'moc_ativo',
                'moc_excluido',
                'tpo_id',
    ];

    protected $validationRules = [
        'moc_nome' => 'required|max_length[50]|min_length[5]',
    ];

    protected $validationMessages = [
        'moc_nome'   => [
            'required'   => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_lenght' => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_lenght' => 'O Campo Devente Conter no Minimo 5 Caracteres',
        ],
    ];


    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert = ['depoisInsert'];
    protected $afterUpdate = ['depoisUpdate'];
    protected $afterDelete = ['depoisDelete'];

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


    public function getModOcorrencia($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_ocorrencia_relac');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_ativo, moc_nome');

        return $builder->get()->getResult();
    }

    public function getModOcorrenciaPorTipo($tpo_id = null)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_ocorrencia_relac');
    
        $builder->select('*');
    
        if ($tpo_id !== null) {
            $builder->where('tpo_id', $tpo_id);
        }
        $builder->orderBy('moc_ativo, moc_nome');

        return $builder->get()->getResult();
    }

    public function getTOTelasAplicaveis($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_campo_relac');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_id');

        return $builder->get()->getResult();
    }

    public function getTOAcao($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('oco_moc_acao');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_id');

        return $builder->get()->getResult();
    }

    public function getMocIdByTpa(int $tpa_id): ?int
    {
        $db = db_connect('dbOcorrencia');
    
        return $db->table('oco_moc_acao')
            ->select('moc_id')
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow('moc_id');
    }

    public function getTipoOcorrenciaSearch($termo)
    {
        $array = ['moc_nome' => $termo . '%'];
    
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_moc_ocorrencia_relac');
    
        $builder->select('*');
        $builder->like($array);
        $builder->orderBy('moc_ativo, moc_nome');
    
        return $builder->get()->getResult();
    }

    public function getAcoesByTipoOcorrencia($tpo_id)
    {
        return $this->db->table('oco_tpo_acao o')
            ->select('o.tpa_id, a.tpa_nome')
            ->join('oco_tipo_acao a', 'a.tpa_id = o.tpa_id')
            ->where('o.tpo_id', $tpo_id)
            ->get()
            ->getResult();
    }

    public function getStatusByTpoTpa($tpo_id, $tpa_id)
    {
        $row = $this->db->table('oco_tpo_acao')
            ->select('stt_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRowArray();
        return $row['stt_id'];
    }
    
    public function getStatus()
    {
        return $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id, stt_nome')
            ->orderBy('stt_nome', 'ASC')
            ->get()
            ->getResult();
    }

    public function buscarPorTipo(int $tpo_id): array
    {
        return $this->db
            ->table('ocorrencia_db.vw_oco_mod_ocorrencia_relac')
            ->select('moc_id, moc_nome')
            ->where('tpo_id', $tpo_id)
            ->where('moc_ativo', 'A')
            ->orderBy('moc_nome')
            ->get()
            ->getResult();
    }

    public function getTelaByTpoTpa(int $tpo_id, int $tpa_id): ?int
    {
        $row = $this->db->table('oco_tpo_acao')
            ->select('tel_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow();
    
        return $row?->tel_id;
    }

    public function getTelas()
    {
        return $this->db->table('config_ceqweb_db.cfg_tela')
            ->select('tel_id, tel_nome')
            ->orderBy('tel_nome', 'ASC')
            ->get()
            ->getResult();
    }

    public function getMovimentacaoByTpoTpa(int $tpo_id, int $tpa_id): ?int
    {
        $row = $this->db->table('oco_tpo_acao')
            ->select('tmo_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow();
    
        return $row?->tmo_id;
    }
}
