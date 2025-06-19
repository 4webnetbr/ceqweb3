<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigModuloModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_modulo';
    protected $view             = 'cfg_modulo';
    protected $primaryKey       = 'mod_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'mod_id',
        'mod_nome',
        'mod_icone',
        'mod_ativo'
    ];

    protected $deletedField  = 'mod_excluido';

    protected $validationRules = [
        'mod_nome' => 'required|isUniqueValue[default.cfg_modulo.mod_nome, mod_id]',
    ];

    protected $validationMessages = [
        'mod_nome' => [
            'required' => 'O campo Nome do Módulo é Obrigatório',
            'isUniqueValue' => '8',
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

    public function getModulo($mod_id = false)
    {
        $this->builder()->select('*');
        if ($mod_id) {
            $this->builder()->where('mod_id', $mod_id);
        }
        $this->builder()->where('mod_excluido', null);
        $this->builder()->orderBy('mod_order');
        return $this->builder()->get()->getResultArray();
    }

    public function getModulosSearch($termo)
    {
        $array = ['mod_nome' => $termo . '%'];
        $this->builder()->select(['mod_id', 'mod_nome', 'mod_icone']);
        $this->builder()->where('mod_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_modulo', 'mod_id');
        $mid->valor     = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $ret['mod_id']   = $mid->crOculto();

        $nome           =  new MyCampo('cfg_modulo', 'mod_nome');
        $nome->valor    = (isset($dados['mod_nome'])) ? $dados['mod_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura  = $show;
        $ret['mod_nome'] = $nome->crInput();

        $icon           =  new MyCampo('cfg_modulo', 'mod_icone');
        $icon->tipo     = 'icone';
        $icon->valor    = (isset($dados['mod_icone'])) ? $dados['mod_icone'] : '';
        $icon->leitura  = $show;
        $ret['mod_icon'] = $icon->crInput();

        $cor           =  new MyCampo('cfg_mensagem', 'msg_cor');
        $cor->valor    = '';
        $cor->selecionado    = $this->valor;
        $cor->leitura  = $show;
        $ret['mod_cor'] = $cor->crCorbst();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_modulo', 'mod_ativo');
        $ativ->valor    = (isset($dados['mod_ativo'])) ? $dados['mod_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $ativ->leitura  = $show;
        $ret['mod_ativo'] = $ativ->cr2opcoes();

        return $ret;
    }
}
