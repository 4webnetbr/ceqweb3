<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigCorModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_cor';
    protected $view             = 'cfg_cor';
    protected $primaryKey       = 'cor_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'cor_id',
        'cor_nome',
        'cor_valorrgb',
        'cor_ativo'
    ];

    protected $deletedField  = 'cor_excluido';

    protected $validationRules = [
        'cor_nome' => 'required|min_length[5]|isUniqueValue[default.cfg_cor.cor_nome, cor_id]',
    ];

    protected $validationMessages = [
        'cor_nome' => [
            'required' => 'O campo Nome da Cor é Obrigatório',
            'isUniqueValue' => '8',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
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

    public function getListaCores($cor_id = false)
    {
        $this->builder()->select('*');
        if ($cor_id) {
            $this->builder()->where('cor_id', $cor_id);
        }
        $this->builder()->orderBy('cor_ativo, cor_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getCores($cor_id = false)
    {
        $this->builder()->select('*');
        if ($cor_id) {
            $this->builder()->where('cor_id', $cor_id);
        }
        $this->builder()->where('cor_ativo', 'A');
        $this->builder()->orderBy('cor_ativo, cor_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getCoresSearch($termo)
    {
        $array = ['cor_nome' => $termo . '%'];
        $this->builder()->select(['cor_id', 'cor_nome', 'cor_valorrgb']);
        $this->builder()->where('cor_ativo', 'A');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_cor', 'cor_id');
        $mid->valor     = (isset($dados['cor_id'])) ? $dados['cor_id'] : '';
        $ret['cor_id']   = $mid->crOculto();

        $nome           =  new MyCampo('cfg_cor', 'cor_nome');
        $nome->valor    = (isset($dados['cor_nome'])) ? $dados['cor_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura  = $show;
        $ret['cor_nome'] = $nome->crInput();

        $vrgb           =  new MyCampo('cfg_cor', 'cor_valorrgb');
        $vrgb->tipo     = 'color';
        $vrgb->obrigatorio = true;
        $vrgb->valor    = (isset($dados['cor_valorrgb'])) ? $dados['cor_valorrgb'] : '';
        $vrgb->leitura  = $show;
        $ret['cor_valorrgb'] = $vrgb->crInput();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_cor', 'cor_ativo');
        $ativ->valor    = (isset($dados['cor_ativo'])) ? $dados['cor_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $ativ->leitura  = $show;
        $ret['cor_ativo'] = $ativ->cr2opcoes();
        // debug($ret);

        return $ret;
    }
}
