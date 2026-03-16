<?php

namespace App\Models\Ocorre;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Ocorrencia\EntOcoTipoOcorre;

class OcorreTipoOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_tipo_ocorrencia';
    protected $view             = 'vw_oco_tpo_ocorrencia_relac';
    protected $primaryKey       = 'tpo_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntOcoTipoOcorre::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'tpo_id',
        'tpo_nome',
        'tpo_ativo'
    ];

    protected $validationRules = [
        'tpo_nome' => 'required|max_length[50]|min_length[5]',
    ];

    protected $validationMessages = [
        'tpo_nome' => [
            'required'    => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_lenght'  => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_lenght'  => 'O Campo Devente Conter no Minimo 5 Caracteres',
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


    public function getTipoOcorrencia($tpo_id = false, $tel_id = false, bool $somenteAtivos = false)
    {
        // $perfilId = session()->get('usu_perfil_id'); 
        $db       = db_connect('dbOcorrencia');
        $builder  = $db->table('vw_oco_tpo_ocorrencia_relac');
        $builder->select('vw_oco_tpo_ocorrencia_relac.*');

        // filtros existentes
        if ($somenteAtivos) {
            $builder->groupStart()
                    ->where('tpo_ativo', 'A');
            if ($tpo_id) {
                $builder->orWhere('vw_oco_tpo_ocorrencia_relac.tpo_id', $tpo_id);
            }
            $builder->groupEnd();
        } elseif ($tpo_id) {
            $builder->where('vw_oco_tpo_ocorrencia_relac.tpo_id', $tpo_id);
        }

        if ($tel_id) {
            $builder->where('tel_id', $tel_id);
        }

        $builder->orderBy("CASE WHEN tpo_ativo = 'A' THEN 0 ELSE 1 END");
        $builder->orderBy('tpo_nome');

        return $builder->get()->getResult();
    }

    public function getTOTelasAplicaveis($tpo_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_tela_campo_relac');
        $builder->select('*');

        // Filtra pelo tipo de ocorrência
        if ($tpo_id) {
            $builder->where('tpo_id', $tpo_id);
        }
        $builder->orderBy('tpo_id');

        return $builder->get()->getResult();
    }


    public function getTOAcao($tpo_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('oco_tipo_ocorrencia_acao');
        $builder->select('*');

        // Filtra pelo tipo de ocorrência
        if ($tpo_id) {
            $builder->where('tpo_id', $tpo_id);
        }
        $builder->orderBy('tpo_id');

        return $builder->get()->getResult();
    }

    public function getTipoMovimentacao($tmo_id = false, $prf_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('oco_tipo_ocorrencia');
        $builder->select('*');

        // Filtra pelo tipo de movimentação
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        // Filtra pelos perfis vinculados 
        if ($prf_id) {
            $builder->where("FIND_IN_SET($prf_id, prf_id) >", 0);
        }
        $builder->orderBy('tmo_ativo, tmo_nome');

        return $builder->get()->getResult();
    }

    public function getMovimentacao(int $tpo_id, int $tpa_id): ?int
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_tipo_ocorrencia_acao o');
        $builder->select('o.tmo_id');

        $builder->where('o.tpo_id', $tpo_id);
        $builder->where('o.tpa_id', $tpa_id);

        $row = $builder->get()->getRow();

        return $row ? (int) $row->tmo_id : null;
    }

    public function getClassePorTipo($tpo_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('vw_tpo_ocorrencia_classe_relac');
        $builder->select('*');

        $builder->where('tpo_id', $tpo_id);

        return $builder->get()->getResult();
    }
}
