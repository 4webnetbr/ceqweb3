<?php

namespace App\Models\Ocorre;

use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class OcorreSubtOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_subt_ocorrencia';
    protected $view             = 'vw_oco_subt_ocorrencia_relac';
    protected $primaryKey       = 'sut_id';
    protected $useAutoIncrement = true;

    protected $returnType     = EntOcoSubtOcorrencia::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
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
        'sut_nome' => [
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

        $builder = $db->table('vw_oco_subt_ocorrencia_relac');
        $builder->select('*');

        // $perfilId = session()->get('usu_perfil_id');

        // $builder->where("FIND_IN_SET($perfilId, prf_id) >", 0, false);

        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }

        $builder->groupBy('sut_id');

        $builder->orderBy('sut_ativo, sut_nome');

        $ret = $builder->get()->getResult();
        // debug($db->getLastQuery(), true);
        return $ret;
    }

    public function getSubtOcorrenciaPorTipo($tpo_id = null, $prfid = false, $classe = false, $tela = false)
    {
        // Conecta ao banco de Ocorrência
        $db      = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_subt_ocorrencia_relac');
        $builder->select('*');

        // Filtra pelo tipo de ocorrência, se informado
        if ($tpo_id !== null) {
            $builder->where('tpo_id', $tpo_id);
        }
        if ($prfid) {
            $builder->where("FIND_IN_SET($prfid, prf_id) >", 0, false);
        }
        if ($classe) {
            $builder->where("FIND_IN_SET($classe, cla_id) >", 0, false);
        }
        if ($tela) {
            $builder->where("FIND_IN_SET($tela, tel_id) >", 0, false);
        }

        $builder->where('sut_ativo', 'A');
        $builder->orderBy('sut_ativo, sut_nome');

        // Retorna os resultados
        $ret = $builder->get()->getResult();
        // debug($db->getLastQuery(), true);

        return $ret;
    }

    public function getTOTelasAplicaveis($sut_id = false)
    {
        $db      = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_subt_campo_relac');

        $builder->select('*');
        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }
        $builder->orderBy('sut_id');

        return $builder->get()->getResult();
    }

    public function getTOAcao($sut_id = false, $tpa_id = false)
    {
        $db      = db_connect('dbOcorrencia');
        $builder = $db->table('oco_subt_ocorrencia_acao');
        $builder->select('*');

        // Filtra pelo modelo de ocorrência
        if ($sut_id) {
            $builder->where('sut_id', $sut_id);
        }
        if ($tpa_id) {
            $builder->where('tpa_id', $tpa_id);
        }
        $builder->orderBy('sut_id');

        return $builder->get()->getResult();
    }

    /**
     * RN06.2 — Lista as ocorrências Pendentes (stt_id=28) vinculadas ao
     * subtipo, usada para bloquear a inativação em OcoSubtOcorrencia::ativinativ()
     * exibindo ao usuário quais ocorrências impedem a inativação.
     * Dados mínimos por item (revisão 01): número da ocorrência, subtipo, data.
     * Substitui getUsoGestao() (removido — sem chamadores após esta RN,
     * confirmado via grep na revisão 01: o único outro getUsoGestao() do
     * projeto pertence a OcorreModOcorrenciaModel, classe diferente).
     */
    public function getPendenciasGestao(int $sut_id): array
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_ocorrencia o');
        $builder->select('o.oco_id, s.sut_nome, o.oco_data');
        $builder->join('oco_subt_ocorrencia s', 's.sut_id = o.sut_id');

        $builder->where('o.sut_id', $sut_id);
        $builder->where('o.stt_id', 28);
        $builder->orderBy('o.oco_data', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * RN03.1.6 / RN03.2.5 — Resolve o stt_id inicial de uma ocorrência a
     * partir das ações cadastradas para o subtipo (oco_subt_ocorrencia_acao.sta_fina):
     *  - sem nenhuma ação cadastrada        -> 29 (Finalização Automática)
     *  - todas as ações com sta_fina = 'S'  -> 29 (Finalização Automática)
     *  - ao menos uma ação com sta_fina='N' -> 28 (Pendente)
     */
    public function getStatusInicial(int $sut_id): int
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_acao');
        $builder->select('sta_fina');
        $builder->where('sut_id', $sut_id);

        $acoes = $builder->get()->getResult();

        if (empty($acoes)) {
            return 29;
        }

        foreach ($acoes as $acao) {
            if (($acao->sta_fina ?? 'N') !== 'S') {
                return 28;
            }
        }

        return 29;
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

    public function getClassePorSubtipo($sut_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_subt_ocorrencia_classe');
        $builder->select('cla_id');
        $builder->where('sut_id', $sut_id);

        return $builder->get()->getResult();
    }
}
