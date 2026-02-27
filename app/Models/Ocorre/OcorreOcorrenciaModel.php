<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Ocorrencia\EntOcoOcorrencia;

class OcorreOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $viewcomp   = 'vw_oco_ocorrencia_completa_relac';
    protected $primaryKey = 'oco_id';

    protected $returnType = EntOcoOcorrencia::class;

    protected $allowedFields = [
        'oco_id',
        'tel_id',
        'tpo_id',
        'sut_id',
        'req_id',
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


    public function getOcorrencia($id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('oco_id', $id);

        return $builder->get()->getRow();
    }

    public function getListaCompleta()
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->orderBy('stt_ordem');
        $builder->orderBy('oco_id', 'ASC'); 
        return $builder->get()->getResult();
    }

    public function getListaPendente($stt_ids = [])
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view);
        $builder->select('*');

        if (!empty($stt_ids)) {
            $builder->whereIn('stt_id', $stt_ids);
        }

        $builder->orderBy('stt_ordem,oco_id desc');

        return $builder->get()->getResult();
    }

    public function getAcoesFinalizar($oco_id = false)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('oco_ocorrencia o');
        $builder->select('*');
        $builder->join(
            'oco_subt_ocorrencia_acao sa',
            'sa.sut_id = o.sut_id'
        );
        $builder->join(
            'oco_tipo_acao ta',
            'ta.tpa_id = sa.tpa_id',
            'left'
        );

        if ($oco_id) {
            $builder->where('o.oco_id', $oco_id);
        }

        return $builder->get()->getResult();
    }

    public function getOcorrenciaPdf($oco_id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table('vw_oco_ocorrencia_relac');
        $builder->select('*');
        $builder->where('oco_id', $oco_id);
        $builder->orderBy('stt_ordem');

        return $builder->get()->getResult();
    }
}
