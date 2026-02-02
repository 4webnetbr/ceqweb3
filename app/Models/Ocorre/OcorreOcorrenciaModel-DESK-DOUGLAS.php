<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoOcorrencia;

class OcorreOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $primaryKey = 'oco_id';

    protected $returnType = EntOcoOcorrencia::class;

    protected $allowedFields = [
        'oco_id',
        'tel_id',
        'tpo_id',
        'moc_id',
        'tpa_id',
        'oco_descricao',
        'pro_id',
        'lot_id',
        'oco_qtd',
        'oco_data',
        'stt_id',
        'tmo_id',
        'oco_justi',
    ];

    protected $validationRules = [
        'oco_descricao' => 'required|max_length[50]|min_length[3]',
    ];


    protected $validationMessages = [
        'oco_descricao'   => [
            'required'    => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_length'  => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_length'  => 'O Campo Devente Conter no Minimo 3 Caracteres',
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


    public function getStatusIdByNome(string $nome, ?int $telId = null): ?int
    {
        $builder = $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id')
            ->where('stt_nome', $nome);

        if ($telId !== null) {
            $builder->where('tel_id', $telId);
        }

        $result = $builder
            ->orderBy('stt_id', 'DESC')
            ->get()
            ->getResult();

        return $result[0]->stt_id ?? null;
    }


    public function getListaCompleta()
    {
        $builder = $this->db->table($this->view);

        return $builder
            ->get()
            ->getResult();
    }

    public function getByIdFinalizar(int $oco_id)
{
    return $this->db->table('oco_ocorrencia o')
        ->select('
            o.*,
            a.tpa_id   AS acao_tpa_id,
            a.tmo_id   AS acao_tmo_id,
            a.tel_id   AS acao_tel_id,
            a.stt_id   AS acao_stt_id
        ')
        ->join(
            'oco_tipo_ocorrencia_acao a',
            'a.tpo_id = o.tpo_id',
            'left'
        )
        ->where('o.oco_id', $oco_id)
        ->get()
        ->getRow();
}


    public function getAcoesByModelo($moc_id)
    {
        $builder = $this->db->table('oco_moc_acao')
            ->where('moc_id', $moc_id);

        return $builder
            ->get()
            ->getResult();
    }

    public function getListaOcorrenciaPdf($oco_id)
    {
        return $this->db
            ->table('vw_oco_ocorrencia_relac')
            ->where('oco_id', $oco_id)
            ->get()
            ->getResult();
    }

    public function getSelectPorTipo(int $tpo_id): array
    {
        $modelMod    = new OcorreModOcorrenciaModel();
        $ocorrencias = $modelMod->buscarPorTipo($tpo_id);

        $retorno = [];

        foreach ($ocorrencias as $ocorrencia) {
            $retorno[$ocorrencia->moc_id] = $ocorrencia->moc_nome;
        }

        return $retorno;
    }
}
