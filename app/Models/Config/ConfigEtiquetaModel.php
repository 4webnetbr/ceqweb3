<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Config\EntCfgEtiqueta;

class ConfigEtiquetaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_etiqueta';
    protected $view             = 'vw_cfg_etiqueta_relac';
    protected $primaryKey       = 'etq_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntCfgEtiqueta::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'etq_id',
        'etq_nome',
        'let_id',
        'mod_id',
        'tel_id',
        'etq_ativo',
    ];


    protected $validationRules = [
        'etq_nome'          => 'required|min_length[5]|max_length[50]',
        'let_id'            => 'required',
        'mod_id'            => 'required',
        'tel_id'            => 'required',
    ];

    protected $validationMessages = [
        'etq_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
            'max_length' => 'O campo deve ter no máximo 50 Caracteres. '
        ],

        'let_id' => [
            'required' => 'O Campo Layout é Obrigatório '
        ],

        'mod_id' => [
            'required' => 'O Campo Módulo é Obrigatório '
        ],

        'tel_id' => [
            'required' => 'O Campo Tela é Obrigatório '
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




    public function getEtiqueta($etq_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');

        // Filtra por ID da etiqueta, se informado
        if ($etq_id) {
            $builder->where('etq_id', $etq_id);
        }
        $builder->orderBy('etq_ativo, etq_nome');

        return $builder->get()->getResult();
    }

    public function getEtiquetaTela($tel_id = false)
    {
        // Se não houver tela informada, retorna array vazio
        if (!$tel_id) {
            return [];
        }

        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('tel_id', $tel_id);
        $builder->where('etq_ativo', 'A');
        $builder->orderBy('etq_ativo, etq_nome');

        // Retorna o resultado da consulta
        return $builder->get()->getResult();
    }


    public function getEtiquetaLayout($lay_id = false)
    {
        // Inicializa o builder utilizando a VIEW do model
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($lay_id) {
            $builder->where('let_id', $lay_id);
        }

        $builder->where('etq_ativo', 'A');        // Apenas etiquetas ativas
        $builder->orderBy('etq_ativo, etq_nome'); // Ordena por status e nome

        return $builder->get()->getResult();
    }


    public function getEtiquetaSearch($termo)
    {
        // Inicializa o builder utilizando a VIEW do model
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('etq_ativo', 'A');           // Apenas etiquetas ativas
        $builder->like('etq_nome', $termo, 'after'); // Aplica busca pelo nome da etiqueta

        return $builder->get()->getResult();
    }
}
