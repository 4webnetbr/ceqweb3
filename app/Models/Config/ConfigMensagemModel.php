<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigMensagemModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_mensagem';
    protected $view             = 'cfg_mensagem';
    protected $primaryKey       = 'msg_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'msg_id',
        'msg_titulo',
        'msg_tipo',
        'msg_cor',
        'msg_mensagem',
        'msg_desc_tipo',
        'msg_ativo',
    ];

    protected $deletedField  = 'msg_excluido';

    protected $validationRules = [
        'msg_titulo' => 'required|min_length[5]|isUniqueValue[default.cfg_mensagem.msg_titulo, msg_id]',
        'msg_tipo'  => 'required',
        'msg_cor'   => 'required',
        'msg_mensagem' => 'required|min_length[5]'
    ];

    protected $validationMessages = [
        'msg_titulo' => [
            'required' => 'O campo Título é Obrigatório',
            'min_length' => 'O campo Título exige pelo menos 5 Caracteres.',
            'isUniqueValue' => '8',
        ],
        'msg_tipo' => [
            'required' => 'O campo Tipo é Obrigatório',
        ],
        'msg_cor' => [
            'required' => 'O campo Cor é Obrigatório',
        ],
        'msg_mensagem' => [
            'required' => 'O campo Mensagem é Obrigatório',
            'min_length' => 'O campo Mensagem exige pelo menos 5 Caracteres.',
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

    public function getMensagem($msg_id = false)
    {
        $this->builder()->select('*');
        if ($msg_id) {
            $this->builder()->where('msg_id', $msg_id);
        }
        $this->builder()->where('msg_excluido', null);
        $this->builder()->orderBy('msg_ativo, msg_titulo');
        return $this->builder()->get()->getResultArray();
    }

    public function getMensagemId($msg_id = false)
    {
        $this->builder()->select('*');
        if ($msg_id) {
            $this->builder()->where('msg_id', $msg_id);
        }
        $this->builder()->where('msg_excluido', null);
        $this->builder()->orderBy('msg_id');
        return $this->builder()->get()->getResultArray();
    }

    public function getMensagemSearch($termo)
    {
        $array = ['msg_titulo' => $termo . '%'];
        $this->builder()->select(['msg_id', 'msg_titulo']);
        $this->builder()->where('msg_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_mensagem', 'msg_id');
        $mid->valor     = (isset($dados['msg_id'])) ? $dados['msg_id'] : '';
        $ret['msg_id']   = $mid->crOculto();

        $titu           =  new MyCampo('cfg_mensagem', 'msg_titulo');
        $titu->valor    = (isset($dados['msg_titulo'])) ? $dados['msg_titulo'] : '';
        $titu->obrigatorio = true;
        $ret['msg_titulo'] = $titu->crInput();

        $opctipo['icone']['P'] = '<i class="fa-solid fa-circle-question fa-lg"></i> Pergunta';
        $opctipo['texto']['P'] = 'Pergunta';
        $opctipo['icone']['A'] = '<i class="fa-solid fa-circle-exclamation fa-lg"></i> Atenção';
        $opctipo['texto']['A'] = 'Alerta';
        $opctipo['icone']['E'] = '<i class="fa-solid fa-circle-xmark fa-lg"></i> Erro';
        $opctipo['texto']['E'] = 'Erro';
        $opctipo['icone']['I'] = '<i class="fa-solid fa-circle-info fa-lg"></i> Informação';
        $opctipo['texto']['I'] = 'Informação';

        $tipo           =  new MyCampo('cfg_mensagem', 'msg_tipo');
        $tipo->tipo     = 'tipo';
        $tipo->valor    = (isset($dados['msg_tipo'])) ? $dados['msg_tipo'] : '';
        $tipo->largura  =  30;
        $tipo->selecionado = $tipo->valor;
        $tipo->opcoes   = $opctipo;
        $tipo->obrigatorio = true;
        $ret['msg_tipo'] = $tipo->crSelectIcone();

        $cor           =  new MyCampo('cfg_mensagem', 'msg_cor');
        $cor->valor    = (isset($dados['msg_cor'])) ? $dados['msg_cor'] : '';
        $cor->selecionado    = $cor->valor;
        $cor->largura  =  30;
        $cor->obrigatorio = true;
        $ret['msg_cor'] = $cor->crCorbst();

        $mens           =  new MyCampo('cfg_mensagem', 'msg_mensagem');
        $mens->valor    = (isset($dados['msg_mensagem'])) ? $dados['msg_mensagem'] : '';
        $mens->obrigatorio = true;
        $ret['msg_mensagem'] = $mens->crTexto();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_mensagem', 'msg_ativo');
        $ativ->valor    = (isset($dados['msg_ativo'])) ? $dados['msg_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $ret['msg_ativo'] = $ativ->cr2opcoes();

        return $ret;
    }
}
