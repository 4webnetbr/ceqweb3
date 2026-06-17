<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Ocorrencia\EntOcoTipoAcao;

class OcorreTipoAcaoModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_tipo_acao';
    protected $view             = 'oco_tipo_acao';
    protected $primaryKey       = 'tpa_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntOcoTipoAcao::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'tpa_id',
        'tpa_nome',
        'tpa_ativo',
        'tpa_tipo',
    ];

    protected $deletedField  = 'tpa_excluido';

    protected $validationRules = [
        'tpa_nome' => 'required|max_length[50]|min_length[5]',
    ];

    protected $validationMessages = [
        'tpa_nome' => [
            'required' => 'O campo Nome do Tipo da Ação é Obrigatório',
            'max_lenght'  => 'O Nome deve Conter no Máximo 50 Caracteres',
            'min_lenght' => 'O Nome deve Conter no Minimo 5 Caracteres',
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


    public function getTipoAcao(int|array|null $tpa_id = null)
    {
        $builder = $this->builder();
        $builder->select('*');

        // Filtra por tipo de ação específico, se informado
        if (!empty($tpa_id)) {
            if (is_array($tpa_id)) {
                $builder->whereIn('tpa_id', $tpa_id);
            } else {
                $builder->where('tpa_id', $tpa_id);
            }
        }
        $builder->where('tpa_excluido', null);
        $builder->orderBy('tpa_ativo, tpa_nome');

        return $builder->get()->getResult();
    }

    public function getTipoAcaoSearch($termo)
    {
        // Inicializa o builder da tabela do model
        $builder = $this->builder();
        $builder->select(['tpa_id', 'tpa_nome']);
        $builder->where('tpa_excluido', null);
        $builder->like('tpa_nome', $termo . '%');

        return $builder->get()->getResult();
    }
}
