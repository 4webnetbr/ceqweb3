<?php

namespace App\Models\Ocorre;

use App\Controllers\Estoque\TipoMovimentacao;
use App\Controllers\Ocorrencia\OcoTipoAcao;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Controllers\Buscas;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigStatusModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutClasseModel;
use CodeIgniter\Model;

class OcorreModOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_mod_ocorrencia';
    protected $view             = 'vw_oco_mod_ocorrencia_relac';
    protected $primaryKey       = 'moc_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
                'moc_id',
                'moc_nome',
                'moc_ativo',
                'moc_excluido',
                'tpo_id',
    ];

    protected $validationRules = [
        'moc_nome' => 'required|max_length[50]|min_length[5]',
    ];

    protected $validationMessages = [
        'moc_nome' => [
            'required' => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_lenght'  => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_lenght' => 'O Campo Devente Conter no Minimo 5 Caracteres',
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

    public function getModOcorrencia($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_ocorrencia_relac');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_ativo, moc_nome');
        return $builder->get()->getResultArray();
    }

    public function getModOcorrenciaPorTipo($tpo_id = null)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_ocorrencia_relac');
    
        $builder->select('*');
    
        if ($tpo_id !== null) {
            $builder->where('tpo_id', $tpo_id);
        }
    
        $builder->orderBy('moc_ativo, moc_nome');
        
        return $builder->get()->getResultArray();
    }


    public function getTOTelasAplicaveis($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_mod_campo_relac');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_id');
        return $builder->get()->getResultArray();
    }
    

    public function getTOAcao($moc_id = false)
    {
        $db = db_connect('dbOcorrencia');
        $builder = $db->table('oco_moc_acao');

        $builder->select('*');
        if ($moc_id) {
            $builder->where('moc_id', $moc_id);
        }
        $builder->orderBy('moc_id');
        return $builder->get()->getResultArray();
    }

    public function getTipoOcorrenciaSearch($termo)
    {
        $array = ['moc_nome' => $termo . '%'];

        $db = db_connect('dbOcorrencia');
        $builder = $db->table('vw_oco_moc_ocorrencia_relac');

        $builder->select('*');
        $builder->like($array);
        $builder->orderBy('moc_ativo, moc_nome');
        return $builder->get()->getResultArray();
    }

    public function getAcoesByTipoOcorrencia($tpo_id)
    {
        return $this->db->table('oco_tpo_acao o')
            ->select('o.tpa_id, a.tpa_nome')
            ->join('oco_tipo_acao a', 'a.tpa_id = o.tpa_id')
            ->where('o.tpo_id', $tpo_id)
            ->get()
            ->getResultArray();
    }

    public function getStatusByTpoTpa($tpo_id, $tpa_id)
    {
        $row = $this->db->table('oco_tpo_acao')
            ->select('stt_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRowArray();
        return $row['stt_id'];
    }
    
    public function getStatus()
    {
        return $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id, stt_nome')
            ->orderBy('stt_nome', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function buscarPorTipo($tpo_id)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_oco_mod_ocorrencia_relac');
        $builder->select('moc_id, moc_nome');
    
        if ($tpo_id) {
            $builder->where("tpo_id", $tpo_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getTelaByTpoTpa($tpo_id, $tpa_id)
    {
        $row = $this->db->table('oco_tpo_acao')
            ->select('tel_id')
            ->where('tpo_id', $tpo_id)
            ->where('tpa_id', $tpa_id)
            ->get()
            ->getRowArray();
    
        return $row['tel_id'];
    }

    public function getTelas()
    {
        return $this->db->table('config_ceqweb_db.cfg_tela')
            ->select('tel_id, tel_nome')
            ->orderBy('tel_nome', 'ASC')
            ->get()
            ->getResultArray();
    }


    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $mid            = new MyCampo('oco_mod_ocorrencia', 'moc_id');
        $mid->valor     = (isset($dados['moc_id'])) ? $dados['moc_id'] : '';
        $ret['moc_id']   = $mid->crOculto();

        $nome            =  new MyCampo('oco_mod_ocorrencia', 'moc_nome');
        $nome->valor     = (isset($dados['moc_nome'])) ? $dados['moc_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura   = $show;
        $nome->largura   = 80;
        $ret['moc_nome'] = $nome->crInput();


        $tipoocor = new OcorreTipoOcorrenciaModel();
        $lst_tipoocor = $tipoocor->getTipoOcorrencia();
        $opc_tipoocor = array_column($lst_tipoocor, 'tpo_nome', 'tpo_id');

        $tpoid                 = new MyCampo('oco_mod_ocorrencia', 'tpo_id', false);
        $tpoid->valor          = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $tpoid->selecionado    = $tpoid->valor;
        $tpoid->opcoes         = $opc_tipoocor;
        $tpoid->obrigatorio    = true;
        $tpoid->largura        = 40;
        $tpoid->dispForm       = 'col-4';
        $tpoid->funcChan       = 'carregaTelaAcaoTipo(this); carregaAcaoTipo(this);';
        $ret['tpo_id'] = $tpoid->crSelect();

        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';
        $teste          = new MyCampo('oco_mod_ocorrencia','moc_ativo');
        $teste->valor   = (isset($dados['moc_ativo'])) ? $dados['moc_ativo'] : 'A';
        $teste->leitura = $show;
        $teste->opcoes  = $simnao;
        $ret['moc_ativo'] = $teste->cr2opcoes();

        return $ret;
    }
    

    public function defCamposTelasAplicaveis($dados = false, $pos = 0,  $total = 1)
    {
        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_moc_tela', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->ordem          = $pos;
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->leitura        = true;
        $mod_id->largura        = 40;
        $mod_id->dispForm       = 'col-4';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_moc_tela', 'tel_id');
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->ordem          = $pos;
        $tela->leitura      = true;
        $tela->largura      = 40;
        $tela->dispForm     = 'col-4';
        $tela->pai          = "mod_id[$pos]";
        $ret['tel_id']      = $tela->crDepende();

        //  debug($dados, true);
        if(!isset($dados['tof_campo']) || $dados['tof_campo'] == ''){
            $tipoCampo  = new ConfigTelaModel();
            $lst_campo = $tipoCampo->getTelaId();
            $opc_campo = array_column($lst_campo, 'tel_nome', 'tel_id');

            $mof_campo               = new MyCampo('oco_moc_campos', 'mof_campo');
            $mof_campo->valor        = '';
            $mof_campo->selecionado  = [];
            $mof_campo->opcoes       = $opc_campo;
            $mof_campo->urlbusca     = base_url('buscas/busca_campo_tela');
            // $mof_campo->obrigatorio  = true;
            $mof_campo->leitura      = true;
            $mof_campo->largura      = 40;
            $mof_campo->dispForm     = 'col-4';
            $mof_campo->ordem        = $pos;
            $mof_campo->pai          = "tel_id[$pos]";
            $ret['mof_campo']           = $mof_campo->crDependeMultiplo();
        } else {
            // debug("Entrei aqui");
            $buscas = new Buscas();
            // debug(var_dump($buscas));
            // $buscas->busca_campo_tela($dados['tel_id']);
            // $jsonOutput = ob_get_clean(); // Captura o que foi "echoado"

            // $campos = json_decode($buscas->busca_campo_tela($dados['tel_id']), true); // Transforma em array associativo

            $campos = $buscas->busca_campo_tela($dados['tel_id']);
            // debug(var_dump($campos), true);
            // $lst_campo = $tipoCampo->getTelaId();
            $opc_campo = array_column($campos, 'text', 'id');
            // debug($dados['tof_campo'], true);
            $mof_campo               = new MyCampo('oco_moc_campos', 'mof_campo');
            $mof_campo->valor        = (isset($dados['tof_campo'])) ? $dados['tof_campo'] : '';
            $mof_campo->selecionado  = explode(',',$dados['tof_campo']);
            $mof_campo->opcoes       = $opc_campo;
            // $mof_campo->urlbusca     = base_url('buscas/busca_campo_tela');
            $mof_campo->leitura      = true;
            $mof_campo->largura      = 40;
            $mof_campo->dispForm     = 'col-4';
            $mof_campo->ordem        = $pos;
            // $mof_campo->pai          = "tel_id[$pos]";
            // debug($mof_campo, true);
            $ret['mof_campo']           = $mof_campo->crMultiple();
        }

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addta[$pos]";
        $add->id        = "bt_addta[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete esconder";
        $add->funcChan  = "addCampo('" . base_url("OcoTipoOcorrencia/addCampoTa/") . "','telas_aplicaveis',this)";
        $ret['bt_addta']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        if ($total == 1) {
          $del->classep .= " d-none";  
        }
        $del->funcChan  = "exclui_campo('telas_aplicaveis',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_delta']   = $del->crBotao();

        return $ret;
    }

    public function defCamposAcao($dados = false, $pos = 0, $show = false)
    {
        $ret = [];

        $tipoacao = new OcorreTipoAcaoModel;
        $lst_tipoacao = $tipoacao->getTipoAcao();
        $opc_tipoacao = array_column($lst_tipoacao, 'tpa_nome', 'tpa_id');

        $tpa_id               =  new MyCampo('oco_moc_acao', 'tpa_id');
        // $tpa_id->obrigatorio  =  true;
        $tpa_id->leitura      = true;
        $tpa_id->ordem        =  $pos;
        $tpa_id->valor        =  (isset($dados['tpa_id'])) ? $dados['tpa_id'] : '';
        $tpa_id->selecionado  =  [$tpa_id->valor];
        $tpa_id->dispForm     =  '2col';
        $tpa_id->largura      =  50;
        $tpa_id->opcoes       =  $opc_tipoacao;
        $tpa_id->funcChan     =  'verificaTipoAcao(this)';
        // debug($tpa_id, false);
        $ret['tpa_id']        =  $tpa_id->crSelect();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addta[$pos]";
        $add->id        = "bt_addta[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete esconder";
        $add->funcChan  = "addCampo('" . base_url("OcoModOcorrencia/addCampoTp/") . "','acoes',this)";
        $ret['bt_addtp']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('acoes',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_deltp']   = $del->crBotao();

        $tpoacao = new EstoquTipoMovimentacaoModel;
        $lst_acao = $tpoacao->getTipoMovimentacao();
        $opc_acao = array_column($lst_acao, 'tmo_nome', 'tmo_id');

        $tmo_id               =  new MyCampo('oco_moc_acao', 'tmo_id');
        $tmo_id->nome = $tmo_id->id = 'tmo_id_tpa';
        // $tmo_id->obrigatorio  =  true;
        $tmo_id->leitura      = true;
        $tmo_id->valor        =  (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $tmo_id->dispForm     =  '2col';
        $tmo_id->largura      =  30;
        $tmo_id->opcoes       =  $opc_acao;
        $tmo_id->ordem        =  $pos;
        $tmo_id->selecionado  =  $tmo_id->valor;
        $ret['tmo_id']        =  $tmo_id->crSelect();

        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_moc_acao', 'mod_id', false);
        $mod_id->nome = $mod_id->id = 'mod_id_tpa';
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->label          = 'Módulo';
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->ordem          = $pos;
        // $mod_id->obrigatorio    = true;
        $mod_id->leitura      = true;
        $mod_id->largura        = 30;
        $mod_id->dispForm       = '2col';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_moc_acao', 'tel_id');
        $tela->nome = $tela->id = 'tel_id_tpa';
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->ordem        = $pos;
        // $tela->obrigatorio  = true;
        $tela->leitura      = true;
        $tela->largura      = 30;
        $tela->dispForm     = '2col';
        $tela->pai          = "mod_id_tpa[$pos]";
        $ret['tel_id']     = $tela->crDepende();

        $stat  = new ConfigStatusModel();
        $lst_stat = $stat->getStatus();
        // debug($lst_stat);
        $opc_stat = array_column($lst_stat, 'stt_tela_status', 'stt_id');

        $statu               = new MyCampo('oco_moc_acao', 'stt_id');
        $statu->nome = $statu->id = 'stt_id_tpa';
        $statu->valor        = (isset($dados['stt_id'])) ? $dados['stt_id'] : '';
        $statu->selecionado  = $statu->valor;
        $statu->ordem        = $pos;
        $statu->opcoes       = $opc_stat;
        // $statu->obrigatorio  = true;
        $statu->leitura      = true;
        $statu->largura      = 50;
        $statu->dispForm     = '2col';
        $ret['stt_id']     = $statu->crSelect();

        return $ret;
    }

    
    public function defCamposParaMostrar($dados = false, $show = false)
    {
        $ret = [];

        $pegatela = new ConfigTelaModel();
        $lst_pegatela = $pegatela->getTelaId();
        $opc_tela = array_column($lst_pegatela, 'tel_nome', 'tel_id');

        $tel_id                 = new MyCampo('oco_moc_campos', 'tel_id', false);
        $tel_id->valor          = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tel_id->selecionado    = $tel_id->valor;
        $tel_id->opcoes         = $opc_tela;
        $tel_id->obrigatorio    = true;
        $tel_id->largura        = 50;
        $tel_id->dispForm       = '2col';
        $ret['tel_id'] = $tel_id->crSelect();

        // $tipoCampo  = new ConfigTelaModel();
        // $lst_campo = $tipoCampo->getTelaId();
        // $opc_campo = array_column($lst_campo, 'toc_nome', 'toc_id');
        $opc_campo = [];

        $mof_campo               = new MyCampo('oco_moc_campos', 'mof_campo');
        $mof_campo->valor        = (isset($dados['mof_campo'])) ? $dados['mof_campo'] : '';
        $mof_campo->selecionado  = $mof_campo->valor;
        $mof_campo->opcoes       = $opc_campo;
        $mof_campo->urlbusca     = base_url('buscas/busca_tela_modulo');
        $mof_campo->obrigatorio  = true;
        $mof_campo->largura      = 50;
        $mof_campo->dispForm     = '2col';
        $mof_campo->pai          = 'tel_id';
        $ret['mof_campo']           = $mof_campo->crDepende();

        $toc_rotulo                 =  new MyCampo('oco_moc_campos', 'toc_rotulo');
        $toc_rotulo->valor          = (isset($dados['toc_rotulo'])) ? $dados['toc_rotulo'] : '';
        $toc_rotulo->obrigatorio    =  true;
        $toc_rotulo->leitura        = $show;
        $toc_rotulo->largura        = 50;
        $toc_rotulo->dispForm       = '2col';
        $ret['toc_rotulo']          = $toc_rotulo->crInput();
        return $ret;
    }

    public function defPermissoes($dados = false, $show = false)
    {
        $ret = [];

        $pegPerfil               = new ConfigPerfilModel;
        $lst_permissao           = $pegPerfil->getPerfil();
        $opc_prf                 = array_column($lst_permissao, 'prf_nome', 'prf_id');

        $top_id                = new MyCampo('oco_moc_permissao', 'prf_id', false);
        $top_id->valor         = (isset($dados['prf_id'])) ? $dados['prf_id'][0] : '';
        $top_id->selecionado   = (isset($dados['prf_id'])) ?[$dados['prf_id']] : [];
        $top_id->opcoes        = $opc_prf;
        $top_id->leitura       = $show;
        $top_id->largura       = 50;
        $top_id->obrigatorio   = true;
        $ret['prf_id']         = $top_id->crMultiple();
        return $ret;
    }
}
