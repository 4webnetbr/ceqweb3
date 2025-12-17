<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigModuloModel;

class OcorreTipoAcaoModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_tipo_acao';
    protected $view             = 'oco_tipo_acao';
    protected $primaryKey       = 'tpa_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
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

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getTipoAcao($tpa_id = false)
    {
        $this->builder()->select('*');
        if ($tpa_id) {
            $this->builder()->where('tpa_id', $tpa_id);
        }
        $this->builder()->where('tpa_excluido', null);
        $this->orderBy('tpa_ativo, tpa_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getTipoAcaoSearch($termo)
    {
        $array = ['tpa_nome' => $termo . '%'];
        $this->builder()->select(['tpa_id', 'tpa_nome']);
        $this->builder()->where('tpa_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }
    

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $mid            = new MyCampo('oco_tipo_acao', 'tpa_id');
        $mid->valor     = (isset($dados['tpa_id'])) ? $dados['tpa_id'] : '';
        $ret['tpa_id']   = $mid->crOculto();

        $nome           =  new MyCampo('oco_tipo_acao', 'tpa_nome');
        $nome->valor    = (isset($dados['tpa_nome'])) ? $dados['tpa_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura  = $show;
        $ret['tpa_nome'] = $nome->crInput();

        $opcex['1'] = 'Justificar';
        $opcex['2'] = 'Listar Telas';
        $opcex['3'] = 'Listar Movimentações';
        $opcex['4'] = 'Listar Status';
        $tipo           =  new MyCampo('oco_tipo_acao', 'tpa_tipo');
        $tipo->valor    = (isset($dados['tpa_tipo'])) ? $dados['tpa_tipo'] : '';
        $tipo->selecionado    = $tipo->valor;
        $tipo->opcoes   = $opcex;
        $tipo->dispForm     = 'col-2';
        $tipo->classep     = 'mb-2';
        $ret['tpa_tipo'] = $tipo->crRadio();

        return $ret;
    }

    
}
