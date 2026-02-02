<?php

namespace App\Models\Config;

use App\Entities\Config\EntCfgLayout;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigLayoutEtiqModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_layout_etiqueta';
    protected $view             = 'cfg_layout_etiqueta';
    protected $primaryKey       = 'let_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntCfgLayout::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'let_id',
        'let_nome',
        'let_altura',
        'let_largura',
        'let_colunas',
        'let_linhas',
        'let_marg_esquerda',
        'let_marg_direita',
        'let_marg_superior',
        'let_marg_inferior',
        'let_distancia_h',
        'let_distancia_v',
        'let_ativo',


    ];


    protected $validationRules = [
        'let_nome'          => 'required|min_length[5]|max_length[50]',
        'let_altura'        => 'required|integer|max_length[4]|min_length[1]',
        'let_largura'       => 'required|integer|max_length[4]|min_length[1]',
        'let_colunas'       => 'required|integer|max_length[2]|min_length[1]',
        'let_marg_esquerda' => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_direita'  => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_superior' => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_inferior' => 'required|integer|max_length[4]|min_length[1]',
        'let_distancia_h'   => 'required|integer|max_length[4]|min_length[1]',
        'let_distancia_v'   => 'required|integer|max_length[4]|min_length[1]'
    ];

    // Nome do layout
    protected $validationMessages = [
        'let_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
            'max_length' => 'O campo deve ter no máximo 50 Caracteres. ',
        ],
        // Altura da etiqueta
        'let_altura' => [
            'required' => 'Altura é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Largura da etiqueta
        'let_largura' => [
            'required' => 'Largura é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Margem esquerda
        'let_marg_esquerda' => [
            'required' => 'Margem Esquerda é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Margem direita
        'let_marg_direita' => [
            'required' => 'Margem Direita é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Margem superior
        'let_marg_superior' => [
            'required' => 'Margem Superior é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Margem inferior
        'let_marg_inferior' => [
            'required' => 'Margem inferior é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Distância horizontal
        'let_distancia_h' => [
            'required' => 'Distância horizontal é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Distância vertical
        'let_distancia_v' => [
            'required' => 'Distância vertical é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        // Quantidade de colunas
        'let_colunas' => [
            'required' => 'Colunas Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 2 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ]
    ];


    // Callbacks
    protected $allowCallbacks = true;

    // Callbacks de log
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


    public function getListaLayouts($let_id = false)
    {
        $builder = $this->builder();
        $builder->select('*');

        // Filtra por layout específico, se informado
        if ($let_id) {
            $builder->where('let_id', $let_id);
        }

        $builder->orderBy('let_ativo, let_nome');

        return $builder->get()->getResult(); 
    }

        public function getLayEtiqueta($let_id = false)
    {
        $builder = $this->builder();
        $builder->select('*');

        // Filtra por ID, se informado
        if ($let_id) {
            $builder->where('let_id', $let_id);
        }

        $builder->where('let_ativo', 'A');
        $builder->orderBy('let_ativo, let_nome');

        return $builder->get()->getResult(); 
    }

        public function getLayEtiquetaSearch($termo)
    {
        $builder = $this->builder();
        $builder->select('*');
        $builder->where('let_ativo', 'A'); // Apenas layouts ativos
        $builder->like('let_nome', $termo . '%'); // Busca por nome

        return $builder->get()->getResult(); 
    }
}
