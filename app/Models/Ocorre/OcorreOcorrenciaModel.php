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
    
        $perfilId = session()->get('usu_perfil_id');
    
        $builder = $db->table($this->view . ' v');
        $builder->select('v.*');
    
        $builder->join(
            'oco_subt_ocorrencia_permissao perm',
            "perm.sut_id = v.sut_id AND perm.prf_id = {$perfilId}",
            'inner'
        );
    
        $builder->groupBy('v.oco_id');
    
        $builder->orderBy('stt_ordem');
        $builder->orderBy('oco_id', 'ASC');
    
        return $builder->get()->getResult();
    }

    public function getListaPendente($stt_ids = [])
    {
        $db = db_connect('dbOcorrencia');
    
        $perfilId = session()->get('usu_perfil_id');
        $builder = $db->table($this->view . ' v');
        $builder->distinct()->select('v.*');
        $builder->join(
            'oco_subt_ocorrencia_permissao perm',
            "perm.sut_id = v.sut_id AND perm.prf_id = {$perfilId}",
            'inner'
        );
        if (!empty($stt_ids)) {
            $builder->whereIn('v.stt_id', $stt_ids);
        }
    
        $builder->orderBy('v.stt_ordem');
        $builder->orderBy('v.oco_id', 'DESC');
    
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
