<?php
namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigStatusModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_status';
    protected $view             = 'vw_cfg_status_relac';
    protected $primaryKey       = 'stt_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = ['stt_id',
                                    'stt_nome',
                                    'stt_cor',
                                    'mod_id',
                                    'tel_id',
                                    'stt_exclusao',
                                    'stt_edicao',
                                    'stt_disponivel',
                                    'stt_ativo',
                                    'stt_ordem',                                    
                                ];

    protected $deletedField  = 'stt_excluido';

    protected $validationRules = [
        'stt_nome' => 'required|min_length[5]|nome_status_existe[]',
    ];

    protected $validationMessages = [
        'stt_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
            'nome_status_existe' => 8
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

    public function getStatus($stt_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        if ($stt_id) {
            $builder->where("stt_id", $stt_id);
        }
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function getStatusNomeTela($tel_id,$nome, $stt_id)
    {
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where("tel_id", $tel_id);
        $builder->where("stt_nome", $nome);
        $builder->where("stt_id !=", $stt_id);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());

        return $ret;
    }

    public function getStatusOrdem($stt_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_status_ordem');
        $builder->select('*');
        if ($stt_id) {
            $builder->where("stt_id", $stt_id);
        }
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function getStatusProximaOrdem($tel_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_status_ordem');
        $builder->selectMax('stt_ordem');
        if ($tel_id) {
            $builder->where("tel_id", $tel_id);
        }
        $ret = $builder->get()->getResultArray();

        return $ret;
    }
    
    public function getStatusSearch($termo)
    {
        $array = ['stt_nome' => $termo . '%'];
        $db = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->like($array);
        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    public function defCampos($dados = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_status', 'stt_id');
        $mid->valor     = (isset($dados['stt_id'])) ? $dados['stt_id'] : '';
        $ret['stt_id']   = $mid->crOculto();

        $nome           =  new MyCampo('cfg_status', 'stt_nome');
        $nome->valor    = (isset($dados['stt_nome'])) ? $dados['stt_nome'] : '';
        $nome->obrigatorio = true;
        $nome->dispForm     = '2col';
        $ret['stt_nome'] = $nome->crInput();

        $modulo_mod =  new ConfigModuloModel();
        $moduloss = $modulo_mod->getModulo();
        $modulos = array_column($moduloss, 'mod_nome', 'mod_id');
        $modu               = new MyCampo('cfg_status', 'mod_id');
        $modu->valor        = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $modu->selecionado  = $modu->valor;
        $modu->opcoes       = $modulos;
        $modu->obrigatorio  = true;
        $modu->largura      = 30;
        $modu->dispForm     = '2col';
        $ret['mod_id'] = $modu->crSelect();
    
        $telas_mod = new ConfigTelaModel();
        $telass = $telas_mod->getTelaId((isset($dados['tel_id'])) ? $dados['tel_id'] : false);
        $telas = array_column($telass, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('cfg_status', 'tel_id');
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $telas;
        $tela->obrigatorio  = true;
        $tela->largura      = 30;
        $tela->dispForm     = '2col';
        $tela->pai          = 'mod_id';
        $ret['tel_id']     = $tela->crDepende();
        
        $cor           =  new MyCampo('cfg_status','stt_cor');
        $cor->valor    = (isset($dados['stt_cor'])) ? $dados['stt_cor'] : '';
        $cor->selecionado  = $this->valor;
        $cor->obrigatorio  = true;
        $cor->largura      =  30;
        $cor->dispForm     = '2col';
        $ret['stt_cor'] = $cor->crCorbst();

        $opcex['S'] = 'Sim';
        $opcex['N'] = 'Não';
        $exclu           =  new MyCampo('cfg_status', 'stt_exclusao');
        $exclu->valor    = (isset($dados['stt_exclusao'])) ? $dados['stt_exclusao'] : 'S';
        $exclu->selecionado    = $exclu->valor;
        $exclu->opcoes   = $opcex;
        $exclu->dispForm     = '2col';
        $ret['stt_exclusao'] = $exclu->cr2opcoes();


        $edit           =  new MyCampo('cfg_status', 'stt_edicao');
        $edit->valor    = (isset($dados['stt_edicao'])) ? $dados['stt_edicao'] : 'S';
        $edit->selecionado    = $edit->valor;
        $edit->opcoes   = $opcex;
        $edit->dispForm     = '2col';
        $ret['stt_edicao'] = $edit->cr2opcoes();

        $disp           =  new MyCampo('cfg_status', 'stt_disponivel');
        $disp->valor    = (isset($dados['stt_disponivel'])) ? $dados['stt_disponivel'] : 'S';
        $disp->selecionado    = $disp->valor;
        $disp->opcoes   = $opcex;
        $disp->dispForm     = '2col';
        $ret['stt_disponivel'] = $disp->cr2opcoes();

        

        return $ret;
    }
    
}
