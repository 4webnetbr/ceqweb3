<?php

namespace App\Models\Ocorre;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;

class OcorreSubtOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_subt_ocorrencia';
    protected $view             = 'vw_oco_subt_ocorrencia_relac';
    protected $primaryKey       = 'sut_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntOcoSubtOcorrencia::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'sut_id',
        'sut_nome',
        'sut_ativo',
        'sut_excluido',
        'tpo_id',
        'cla_id',
        'sut_fina',
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


    public function getSubtOcorrencia($sut_id = false)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('vw_oco_subt_ocorrencia_relac v');
        $builder->select('*');

        $perfilId = session()->get('usu_perfil_id');

        $builder->where("FIND_IN_SET($perfilId, v.prf_id)");

        if ($sut_id) {
            $builder->where('v.sut_id', $sut_id);
        }

        $builder->groupBy('v.sut_id');

        $builder->orderBy('v.sut_ativo, v.sut_nome');

        return $builder->get()->getResult();
    }

    public function getSubtOcorrenciaPorTipo($tpo_id = null)
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

    public function getUsoGestao(int $sut_id): bool
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_ocorrencia o');
        $builder->select('o.oco_id');

        $builder->where('o.sut_id', $sut_id);
        $builder->where('o.stt_id', 28);

        return $builder->countAllResults() > 0;
    }

    public function getSubtipoPorTipos(int $tpo_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia s');
        $builder->select('s.*');

        $builder->where('s.tpo_id', $tpo_id);
        $builder->where('sut_ativo', 'A');
        $builder->orderBy('s.sut_nome', 'ASC');

        return $builder->get()->getResult();
    }


    public function getSubTipo($tpo_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia');
        $builder->select('sut_id');

        $builder->where('tpo_id', $tpo_id);

        $row = $builder->get()->getResult();

        return $row;
    }


    public function getTelaByTpoTpa(int $tpo_id, int $tpa_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_tipo_ocorrencia_acao o');
        $builder->select('o.tel_id');

        $builder->where('o.tpo_id', $tpo_id);
        $builder->where('o.tpa_id', $tpa_id);

        $row = $builder->get()->getRow();

        return $row ? (int) $row->tel_id : null;
    }


    public function getAcaoConfigurada(int $sut_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_acao');
        $builder->select('*');
        $builder->where('sut_id', $sut_id);

        return $builder->get()->getRow();
    }

    public function getAcaoPorId($tpa_id, $sut_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_acao');
        $builder->select('*');
        $builder->where('sut_id', $sut_id);
        $builder->where('tpa_id', $tpa_id);

        return $builder->get()->getRow();
    }

    public function getSubtipoAtivo(int $tpo_id): bool
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia s');
        $builder->select('s.sut_id');

        $builder->where('s.tpo_id', $tpo_id);
        $builder->where('s.sut_ativo', 'A');

        return $builder->countAllResults() > 0;
    }

    public function getPermissoesSubtipo($sut_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_permissao');
        $builder->select('prf_id');

        $builder->where('sut_id', $sut_id);

        return $builder->get()->getResult();
    }

    public function getClassePorSubtipo(int $sut_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_classe');
        $builder->select('cla_id');
        $builder->where('sut_id', $sut_id);

        return $builder->get()->getResult();
    }
}
