<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Controllers\Buscas;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigStatusModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

class EntOcoModOcorrencia extends Entity
{
    protected $attributes = [
        'moc_id'       => null,
        'moc_nome'     => null,
        'moc_ativo'    => 'A',
        'moc_excluido' => null,
        'tpo_id'       => null,
    ];

    protected $casts = [
        'moc_id' => 'integer',
        'tpo_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(object|array|null $data = null)
    {
        if (is_array($data)) {
            $data = (object) $data;
        }
        parent::__construct((array) ($data ?? []));
        $this->campos = $this->defCampos($data ?? new \stdClass());
    }

    public function defCampos($dados = false, $show = false)
    {
        if (is_array($dados)) {
            $dados = (object) $dados;
        }
        if ($dados === false || $dados === null) {
            $dados = new \stdClass();
        }
    
        $ret = [];
        $mid            = new MyCampo('oco_mod_ocorrencia', 'moc_id');
        $mid->valor     = $dados->moc_id ?? '';
        $ret['moc_id']   = $mid->crOculto();

        $nome            =  new MyCampo('oco_mod_ocorrencia', 'moc_nome');
        $nome->valor     = $dados->moc_nome ?? '';
        $nome->obrigatorio = true;
        $nome->leitura   = $show;
        $nome->largura   = 80;
        $ret['moc_nome'] = $nome->crInput();


        $tipoocor = new OcorreTipoOcorrenciaModel();
        $lst_tipoocor = $tipoocor->where('tpo_ativo', 'A')->findAll();
        $opc_tipoocor = array_column($lst_tipoocor, 'tpo_nome', 'tpo_id');

        $tpoid                 = new MyCampo('oco_mod_ocorrencia', 'tpo_id', false);
        $tpoid->valor          = $dados->tpo_id ?? '';
        $tpoid->selecionado    = $tpoid->valor;
        $tpoid->opcoes         = $opc_tipoocor;
        $tpoid->obrigatorio    = true;
        $tpoid->leitura        = isset($dados->moc_id);
        $tpoid->largura        = 40;
        $tpoid->dispForm       = 'col-4';
        $tpoid->funcChan       = 'carregaTelaAcaoTipo(this); carregaAcaoTipo(this);';
        $ret['tpo_id'] = $tpoid->crSelect();

        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';
        $teste          = new MyCampo('oco_mod_ocorrencia','moc_ativo');
        $teste->valor   = $dados->moc_ativo ?? 'A';
        $teste->leitura = $show;
        $teste->opcoes  = $simnao;
        $ret['moc_ativo'] = $teste->cr2opcoes();

        return $ret;
    }
    

    public function defCamposTelasAplicaveis($dados = false, $pos = 0,  $total = 1)
    {
        if (is_object($dados)) {
        $dados = (array) $dados;
        }

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

    public function defCamposAcao($dados = false, $pos = 0, $total = 1, $modo = 'edit')
    {
        if (is_array($dados)) {
        $dados = (object) $dados;
    }

    if ($dados === false) {
        $dados = new \stdClass();  
    }

    $ret = [];

        $tipoacao = new OcorreTipoAcaoModel;
        $lst_tipoacao = $tipoacao->getTipoAcao();
        $opc_tipoacao = array_column($lst_tipoacao, 'tpa_nome', 'tpa_id');

        $tpa_id               =  new MyCampo('oco_moc_acao', 'tpa_id');
        // $tpa_id->obrigatorio  =  true;
        $tpa_id->leitura      = true;
        $tpa_id->ordem        =  $pos;
        $tpa_id->valor        =  $dados->tpa_id ?? '';
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

        $del = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        
        // if ($total == 1) {
        //     $del->classep .= " d-none";
        // }

        $del->funcChan  = "exclui_campo('acoes',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_deltp'] = $del->crBotao();

        $tmoModel = new EstoquTipoMovimentacaoModel();
        $lst_tmo  = $tmoModel->getTipoMovimentacao($dados->tmo_id ?? false);
        $opc_tmo  = array_column($lst_tmo, 'tmo_nome', 'tmo_id');
        
        $tmo_id               = new MyCampo('oco_moc_acao', 'tmo_id');
        $tmo_id->valor        = $dados->tmo_id ?? '';
        $tmo_id->selecionado  = $tmo_id->valor;
        $tmo_id->opcoes       = $opc_tmo;
        $tmo_id->leitura      = true; 
        $tmo_id->dispForm     = '2col';
        $tmo_id->largura      = 30;
        $tmo_id->ordem        = $pos;
        
        $ret['tmo_id'] = $tmo_id->crSelect();

        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_moc_acao', 'mod_id', false);
        $mod_id->nome = $mod_id->id = 'mod_id_tpa';
        $mod_id->valor          = $dados->mod_id ?? '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->label          = 'Módulo';
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->ordem          = $pos;
        // $mod_id->obrigatorio    = true;
        $mod_id->leitura        = true;
        $mod_id->largura        = 30;
        $mod_id->dispForm       = '2col';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_moc_acao', 'tel_id');
        $tela->nome = $tela->id = 'tel_id_tpa';
        $tela->valor        = $dados->tel_id ?? '';
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
        $statu->valor        = $dados->stt_id ?? '';
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