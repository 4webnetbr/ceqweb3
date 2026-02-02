<?php

namespace App\Models\Ocorre;

use App\Entities\Ocorrencia\EntOcoTratativa;
use CodeIgniter\Model;
use App\Models\LogMonModel;

class OcorreTrataOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $primaryKey = 'oco_id';

    protected $returnType = EntOcoTratativa::class;

    protected $allowedFields = [
        'oco_id',
        'tpo_id',
        'tpa_id',
        'lot_lote',
        'oco_descricao',
        'pro_despro',
        'oco_qtd',
        'oco_data',
        'stt_id',
        'tmo_id',
        'oco_justi',
    ];

    protected $validationRules = [
        'oco_descricao'  => 'required|max_length[50]|min_length[3]',
    ];

    protected $validationMessages = [
        'oco_descricao'  => [
            'required'   => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_length' => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_length' => 'O Campo Devente Conter no Minimo 3 Caracteres',
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


    public function getStatusIdByNome(string $nome, ?int $telId = null)
    {
        // Inicializa a consulta na tabela de status
        $builder = $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id')
            ->where('stt_nome', $nome);
    
        // Filtra pela tela, se informada    
        if ($telId !== null) {
            $builder->where('tel_id', $telId);
        }
        return $builder
            ->orderBy('stt_id', 'DESC')
            ->get()
            ->getResult();
    }


    public function getById($id)
    {
        // Busca ocorrência na tabela principal
        return $this->db->table('oco_ocorrencia')
            ->where('oco_id', $id)
            ->get()
            ->getResult();
    }
    
    public function getListaCompleta()
    {
        // Retorna todos os registros da VIEW
        return $this->db->table($this->view)
            ->get()
            ->getResult();
    }  

    public function getView($id)
    {
        // Busca ocorrência específica na VIEW
        return $this->db->table($this->view)
            ->where('oco_id', $id)
            ->get()
            ->getResult();
    }
    
    public function getAcoesForTratativa($tpo_id)
    {
        // Busca ações vinculadas ao tipo de ocorrência com joins auxiliares
        return $this->db->table('oco_tpo_acao a')
            ->select('
                a.*,
                ta.tpa_nome,
                tm.tmo_nome,
                te.tel_nome,
                s.stt_nome
            ')
            ->join('oco_tipo_acao ta', 'ta.tpa_id = a.tpa_id', 'left')
            ->join('estoqu_tipo_movimentacao tm', 'tm.tmo_id = a.tmo_id', 'left')
            ->join('config_tela te', 'te.tel_id = a.tel_id', 'left')
            ->join('cfg_status s', 's.stt_id = a.stt_id', 'left')
            ->where('a.tpo_id', $tpo_id)
            ->get()
            ->getResult();
    }
}
