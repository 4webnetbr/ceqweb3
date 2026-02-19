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
        'sut_id',
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



    public function getOcorrencia($id)
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
}
