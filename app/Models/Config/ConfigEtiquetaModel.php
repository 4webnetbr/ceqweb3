<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigLayoutEtiqModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigEtiquetaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_etiqueta';
    protected $view             = 'vw_cfg_etiqueta_relac';
    protected $primaryKey       = 'etq_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
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
        'etq_nome'          => 'required|min_length[5]|max_length[50]|isUniqueValue[default.cfg_etiqueta.etq_nome, etq_id]',
        'let_id'            => 'required',
        'mod_id'            => 'required',
        'tel_id'            => 'required',
    ];

    protected $validationMessages = [
        'etq_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'isUniqueValue' => '8',
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

    public function getEtiqueta($etq_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($etq_id) {
            $builder->where('etq_id', $etq_id);
        }
        $builder->where('etq_ativo', 'A');
        $builder->orderBy('etq_ativo, etq_nome');
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function getEtiquetaLayout($lay_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($lay_id) {
            $builder->where('let_id', $lay_id);
        }
        $builder->where('etq_ativo', 'A');
        $builder->orderBy('etq_ativo, etq_nome');
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function getEtiquetaSearch($termo)
    {
        $array = ['etq_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select(['*']);
        $builder->where('etq_ativo', 'A');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false, $view = '')
    {
        $opcoes         = new CommonModel();
        $simnao['S']    = 'Sim';
        $simnao['N']    = 'Não';

        $ret = [];
        $etq_id                 = new MyCampo('cfg_etiqueta', 'etq_id');
        $etq_id->valor          = (isset($dados['etq_id'])) ? $dados['etq_id'] : '';
        $ret['etq_id']          = $etq_id->crOculto();

        $nome                   =  new MyCampo('cfg_etiqueta', 'etq_nome');
        $nome->valor            = (isset($dados['etq_nome'])) ? $dados['etq_nome'] : '';
        $nome->obrigatorio      = true;
        $nome->leitura          = $show;
        $nome->dispForm         = "linha";
        $ret['etq_nome']        = $nome->crInput();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ                   = new MyCampo('cfg_etiqueta', 'etq_ativo');
        $ativ->valor            = (isset($dados['etq_ativo'])) ? $dados['etq_ativo'] : 'A';
        $ativ->selecionado      = $ativ->valor;
        $ativ->opcoes           = $opcat;
        $ativ->leitura          = $show;
        $ativ->dispForm         = "col-12";
        $ret['etq_ativo']       = $ativ->cr2opcoes();

        $chave = false;
        if (isset($dados['let_id'])) {
            $chave = 'let_id = ' . $dados['let_id'];
        }
        $opc_let      = $opcoes->getListaOpcoes('default', 'cfg_layout_etiqueta', ['let_nome', 'let_id'], $chave);

        $let_id                 = new MyCampo('cfg_etiqueta', 'let_id', false);
        $let_id->valor          = (isset($dados['let_id'])) ? $dados['let_id'] : '';
        $let_id->selecionado    = $let_id->valor;
        $let_id->opcoes         = $opc_let;
        $let_id->leitura        = $show;
        $let_id->dispForm       = "linha";
        $ret['let_id'] = $let_id->crSelect();


        $chave = false;
        if (isset($dados['mod_id'])) {
            $chave = 'mod_id = ' . $dados['mod_id'];
        }
        $opc_mod      = $opcoes->getListaOpcoes('default', 'cfg_modulo', ['mod_nome', 'mod_id'], $chave);

        $mod_id                 = new MyCampo('cfg_etiqueta', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->opcoes         = $opc_mod;
        $mod_id->leitura        = $show;
        $mod_id->dispForm       = "col-5";
        $ret['mod_id']          = $mod_id->crSelect();

        $chave = false;
        if (isset($dados['tel_id'])) {
            $chave = 'tel_id = ' . $dados['tel_id'];
        }
        $opc_tel      = $opcoes->getListaOpcoes('default', 'cfg_tela', ['tel_nome', 'tel_id'], $chave);

        $tel_id                 = new MyCampo('cfg_etiqueta', 'tel_id');
        $tel_id->valor          = (isset($dados['tel_id'])) ? $dados['tel_id'] : "";
        $tel_id->selecionado    = $tel_id->valor;
        $tel_id->urlbusca       = base_url('buscas/busca_tela_modulo');
        $tel_id->pai            = 'mod_id';
        $tel_id->opcoes         = $opc_tel;
        $tel_id->dispForm       = "col-5";
        $ret['tel_id']          = $tel_id->crDepende();
        return $ret;
    }
}
