<?php

namespace App\Models\Ocorre;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;

class OcorreModOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_subt_ocorrencia';
    protected $view             = 'vw_oco_subt_ocorrencia_relac';
    protected $primaryKey       = 'sut_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntOcoModOcorrencia::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
                'sut_id',
                'sut_nome',
                'sut_ativo',
                'sut_excluido',
                'tpo_id',
    ];

    protected $validationRules = [
        'sut_nome' => 'required|max_length[50]|min_length[5]',
    ];

    protected $validationMessages = [
        'sut_nome'   => [
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


    public function getModOcorrencia($sut_id = false)
    {
        // Conecta ao banco de Ocorrência
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_subt_ocorrencia_relac');
        $builder->select('*');

        // Filtra por modelo específico, se informado
        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }
        $builder->orderBy('sut_ativo, sut_nome');

         // Ordena por status e nome do modelo
        return $builder->get()->getResult();
    }

    public function getAcoesByTipo(int $tpo_id)
    {
        return $this->db
            ->table('oco_tipo_ocorrencia_acao o')
            ->where('tpo_id', $tpo_id)
            ->orderBy('o.toa_id', 'ASC')
            ->get()
            ->getResult();
    }

    public function getModOcorrenciaPorTipo($tpo_id = null)
    {
        // Conecta ao banco de Ocorrência
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_subt_ocorrencia_relac');
        $builder->select('*');
    
        // Filtra pelo tipo de ocorrência, se informado
        if ($tpo_id !== null) {
            $builder->where('tpo_id', $tpo_id);
        }
        $builder->orderBy('sut_ativo, sut_nome');

        // Retorna os resultados
        return $builder->get()->getResult();
    }

    public function getTOTelasAplicaveis($sut_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_subt_campo_relac');

        $builder->select('*');
        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }
        $builder->orderBy('sut_id');

        return $builder->get()->getResult();
    }

    public function getTOAcao($sut_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('oco_subt_ocorrencia_acao');
        $builder->select('*');

        // Filtra pelo modelo de ocorrência
        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }
        $builder->orderBy('sut_id');

        return $builder->get()->getResult();
    }
    

    public function getMocIdByTpa(int $tpa_id): ?int
    {
        $db = db_connect('dbOcorrencia');
    
        // Busca o modelo vinculado à ação
        return $db->table('oco_moc_acao')
            ->select('sut_nome')
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow('sut_nome');
    }

    public function getTipoOcorrenciaSearch($termo)
    {
        $array = ['sut_nome' => $termo . '%'];
    
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_moc_ocorrencia_relac');
    
        // Aplica filtros e ordenação
        $builder->select('*');
        $builder->like($array);
        $builder->orderBy('sut_ativo, sut_nome');
    
        return $builder->get()->getResult();
    }

    public function getUsoGestao(int $sut_id): bool
    {
        return $this->db
            ->table('oco_ocorrencia')
            ->where('sut_id', $sut_id)
            ->countAllResults() > 0;
    }

    public function getSubtipoPorTipo(int $tpo_id)
    {
        return $this->where('tpo_id', $tpo_id)
                    ->orderBy('sut_nome')
                    ->findAll();
    }

    public function getAcoesByTipoOcorrencia($tpo_id)
    {
        // Busca ações vinculadas ao tipo de ocorrência
        return $this->db->table('oco_tipo_ocorrencia_acao o')
            ->select('o.tpa_id, a.tpa_nome')
            ->join('oco_tipo_ocorrencia_acao a', 'a.tpa_id = o.tpa_id')
            ->where('o.tpo_id', $tpo_id)
            ->get()
            ->getResult();
    }

    public function getStatusByTpoTpa($tpo_id, $tpa_id)
    {
        // Busca o status associado
        $row = $this->db->table('oco_tipo_ocorrencia_acao')
            ->select('stt_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRowArray();
        return $row['stt_id'];
    }
    
    public function getStatus()
    {
        // Busca status cadastrados no sistema
        return $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id, stt_nome')
            ->orderBy('stt_nome', 'ASC')
            ->get()
            ->getResult();
    }

    public function buscarPorTipo(int $tpo_id): array
    {
        // Busca modelos ativos vinculados ao tipo
        return $this->db
            ->table('ocorrencia_db.vw_oco_subt_ocorrencia_relac')
            ->select('sut_nome, sut_nome')
            ->where('tpo_id', $tpo_id)
            ->where('sut_ativo', 'A')
            ->orderBy('sut_nome')
            ->get()
            ->getResult();
    }

    public function getTelaByTpoTpa(int $tpo_id, int $tpa_id): ?int
    {
        // Busca a tela associada
        $row = $this->db->table('oco_tipo_ocorrencia_acao')
            ->select('tel_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow();
    
        return $row?->tel_id;
    }

    public function getTelas()
    {
        // Busca telas do sistema
        return $this->db->table('config_ceqweb_db.cfg_tela')
            ->select('tel_id, tel_nome')
            ->orderBy('tel_nome', 'ASC')
            ->get()
            ->getResult();
    }

    public function getMovimentacaoByTpoTpa(int $tpo_id, int $tpa_id): ?int
    {
        // Busca a movimentação associada
        $row = $this->db->table('oco_tipo_ocorrencia_acao')
            ->select('tmo_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRow();
    
        // Retorna o ID da movimentação    
        return $row?->tmo_id;
    }
}
