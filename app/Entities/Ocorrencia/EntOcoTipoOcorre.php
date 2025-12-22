<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigStatusModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

class EntOcoTipoOcorre extends Entity
{
    protected $attributes = [
        'tpo_id'    => null,
        'tpo_nome'  => null,
        'tpo_ativo' => 'A',
        'cla_id'    => null,
    ];

    protected $casts = [
        'tpo_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    public function defCampos($dados = false, $show = false)
    {
        $dados = (array) $dados; 
        $ret = [];
        $mid            = new MyCampo('oco_tipo_ocorrencia', 'tpo_id');
        $mid->valor     = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $ret['tpo_id']   = $mid->crOculto();

        $nome            =  new MyCampo('oco_tipo_ocorrencia', 'tpo_nome');
        $nome->valor     = (isset($dados['tpo_nome'])) ? $dados['tpo_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura   = $show;
        $nome->largura   = 80;
        $ret['tpo_nome'] = $nome->crInput();

        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';
        $teste          = new MyCampo('oco_tipo_ocorrencia','tpo_ativo');
        $teste->valor   = (isset($dados['tpo_ativo'])) ? $dados['tpo_ativo'] : 'A';
        $teste->leitura = $show;
        $teste->opcoes  = $simnao;
        $ret['tpo_ativo'] = $teste->cr2opcoes();

        $classes = new ProdutClasseModel();
        $lst_classes = $classes->getClasse();
        $opc_classes = array_column($lst_classes, 'cla_nome', 'cla_id');

        $cla_id                 = new MyCampo('oco_tpo_classe', 'cla_id', false);
        $cla_id->valor          = (isset($dados['cla_id'])) ? $dados['cla_id']: '';
        $cla_id->selecionado    = (isset($dados['cla_id'])) ? explode(',',$dados['cla_id']) : [];
        $cla_id->opcoes         = $opc_classes;
        $cla_id->largura        = 50;
        $ret['cla_id']          = $cla_id->crMultiple();

        
        return $ret;
    }

    public function defCamposTelasAplicaveis($dados = false, $pos = 0, $show = false)
    {
        $dados = (array) $dados; 
        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_tpo_tela', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->ordem          = $pos;
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->obrigatorio    = true;
        $mod_id->largura        = 40;
        $mod_id->dispForm       = 'col-4';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_tpo_tela', 'tel_id');
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->ordem          = $pos;
        $tela->obrigatorio  = true;
        $tela->largura      = 40;
        $tela->dispForm     = 'col-4';
        $tela->pai          = "mod_id[$pos]";
        $ret['tel_id']      = $tela->crDepende();

        $tipoCampo  = new ConfigTelaModel();
        $lst_campo = $tipoCampo->getTelaId();
        $opc_campo = array_column($lst_campo, 'tel_nome', 'tel_id');

        $tof_campo               = new MyCampo('oco_tpo_campos', 'tof_campo');
        $tof_campo->valor        = (isset($dados['tof_campo'])) ? $dados['tof_campo'] : '';
        $tof_campo->selecionado  = explode(',',$tof_campo->valor);
        $tof_campo->opcoes       = $opc_campo;
        $tof_campo->urlbusca     = base_url('buscas/busca_campo_tela');
        $tof_campo->obrigatorio  = true;
        $tof_campo->largura      = 40;
        $tof_campo->dispForm     = 'col-4';
        $tof_campo->ordem        = $pos;
        $tof_campo->pai          = "tel_id[$pos]";
        $ret['tof_campo']           = $tof_campo->crDependeMultiplo();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addta[$pos]";
        $add->id        = "bt_addta[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("OcoTipoOcorrencia/addCampoTa/") . "','telas_aplicaveis',this)";
        $ret['bt_addta']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('telas_aplicaveis',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_delta']   = $del->crBotao();

        return $ret;
    }

    public function defCamposAcao($dados = false, $pos = 0, $show = false)
    {
        $dados = (array) $dados; 
        $ret = [];

        $tipoacao     = new OcorreTipoAcaoModel;
        $lst_tipoacao = $tipoacao->getTipoAcao();
        $opc_tipoacao = array_column($lst_tipoacao, 'tpa_nome', 'tpa_id');

        $tpa_id               =  new MyCampo('oco_tpo_acao', 'tpa_id');
        $tpa_id->obrigatorio  =  true;
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
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("OcoTipoOcorrencia/addCampoTp/") . "','acoes',this)";
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

        $tmoModel = new EstoquTipoMovimentacaoModel();
        $lst_tmo  = $tmoModel->getTipoMovimentacao();
        $opc_tmo  = array_column($lst_tmo, 'tmo_nome', 'tmo_id');
        
        $tmo_id               =  new MyCampo('oco_tpo_acao', 'tmo_id');
        $tmo_id->nome = $tmo_id->id = 'tmo_id_tpa';
        $tmo_id->obrigatorio  =  false;
        $tmo_id->valor        =  (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $tmo_id->dispForm     =  '2col';
        $tmo_id->largura      =  30;
        $tmo_id->opcoes       = $opc_tmo;
        $tmo_id->ordem        =  $pos;
        $tmo_id->selecionado  =  $tmo_id->valor;
        $ret['tmo_id']        =  $tmo_id->crSelect();

        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_tpo_acao', 'mod_id', false);
        $mod_id->nome = $mod_id->id = 'mod_id_tpa';
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->label          = 'Módulo';
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->ordem          = $pos;
        // $mod_id->obrigatorio    = true;
        $mod_id->largura        = 30;
        $mod_id->dispForm       = '2col';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_tpo_acao', 'tel_id');
        $tela->nome = $tela->id = 'tel_id_tpa';
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->ordem        = $pos;
        // $tela->obrigatorio  = true;
        $tela->largura      = 30;
        $tela->dispForm     = '2col';
        $tela->pai          = "mod_id_tpa[$pos]";
        $ret['tel_id']     = $tela->crDepende();

        $stat  = new ConfigStatusModel();
        $lst_stat = $stat->getStatus();
        // debug($lst_stat);
        $opc_stat = array_column($lst_stat, 'stt_tela_status', 'stt_id');

        $statu               = new MyCampo('oco_tpo_acao', 'stt_id');
        $statu->nome = $statu->id = 'stt_id_tpa';
        $statu->valor        = (isset($dados['stt_id'])) ? $dados['stt_id'] : '';
        $statu->selecionado  = $statu->valor;
        $statu->ordem        = $pos;
        $statu->opcoes       = $opc_stat;
        // $statu->obrigatorio  = true;
        $statu->largura      = 50;
        $statu->dispForm     = '2col';
        $ret['stt_id']     = $statu->crSelect();

        return $ret;
    }

    public function defCamposParaMostrar($dados = false, $show = false)
    {
        $dados = (array) $dados; 
        $ret = [];

        $pegatela = new ConfigTelaModel();
        $lst_pegatela = $pegatela->getTelaId();
        $opc_tela = array_column($lst_pegatela, 'tel_nome', 'tel_id');

        $tel_id                 = new MyCampo('oco_tpo_campos', 'tel_id', false);
        $tel_id->valor          = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tel_id->selecionado    = $tel_id->valor;
        $tel_id->opcoes         = $opc_tela;
        $tel_id->obrigatorio    = true;
        $tel_id->largura        = 50;
        $tel_id->dispForm       = '2col';
        $ret['tel_id'] = $tel_id->crSelect();

        $opc_campo = [];

        $tof_campo               = new MyCampo('oco_tpo_campos', 'tof_campo');
        $tof_campo->valor        = (isset($dados['tof_campo'])) ? $dados['tof_campo'] : '';
        $tof_campo->selecionado  = $tof_campo->valor;
        $tof_campo->opcoes       = $opc_campo;
        $tof_campo->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tof_campo->obrigatorio  = true;
        $tof_campo->largura      = 50;
        $tof_campo->dispForm     = '2col';
        $tof_campo->pai          = 'tel_id';
        $ret['tof_campo']           = $tof_campo->crDepende();

        $toc_rotulo                 =  new MyCampo('oco_tpo_campos', 'toc_rotulo');
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
        $dados = (array) $dados; 
        $ret = [];

        $pegPerfil               = new ConfigPerfilModel;
        $lst_permissao           = $pegPerfil->getPerfil();
        $opc_prf                 = array_column($lst_permissao, 'prf_nome', 'prf_id');

        $top_id                = new MyCampo('oco_tpo_permissao', 'prf_id', false);
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