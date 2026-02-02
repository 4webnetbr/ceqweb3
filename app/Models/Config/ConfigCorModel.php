<?php

namespace App\Models\Config;

use CodeIgniter\Model;
// use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use App\Entities\Config\EntCfgCor;

class ConfigCorModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_cor';
    protected $view             = 'cfg_cor';
    protected $primaryKey       = 'cor_id';
    protected $useAutoIncremodt = true;

    // protected $returnType       = 'array';
    protected $returnType       = EntCfgCor::class;
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'cor_id',
        'cor_nome',
        'cor_valorrgb',
        'cor_ativo'
    ];

    protected $deletedField  = 'cor_excluido';

    protected $validationRules = [
        'cor_nome' => 'required|min_length[5]',
    ];

    protected $validationMessages = [
        'cor_nome' => [
            'required' => 'O campo Nome da Cor é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
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
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getListaCores($cor_id = false)
    {
        // Seleciona todos os campos
        $this->builder()->select('*');

        // Filtra por ID da cor, se informado
        if ($cor_id) {
            $this->builder()->where('cor_id', $cor_id);
        }
        $this->builder()->orderBy('cor_ativo, cor_nome');
        return $this->builder()->get()->getResult();
    }

    public function getCores($cor_id = false)
    {
        // Busca apenas cores ativas
        $ret = $this
            // ->where('cor_ativo', 'A')
            ->when($cor_id !== null, fn($q) => $q->where('cor_id', $cor_id))
            ->orderBy('cor_ativo, cor_nome')
            ->first();

        return $ret;
    }

    public function getCoresSearch($termo)
    {
        // Monta filtro LIKE para busca
        $array = ['cor_nome' => $termo . '%'];
        $this->builder()->select(['cor_id', 'cor_nome', 'cor_valorrgb']);
        $this->builder()->where('cor_ativo', 'A');
        $this->builder()->like($array);

        // Retorna os resultados encontrados
        return $this->builder()->get()->getResult();
    }
}
