<?php 

// namespace App\Entities\Config;

// use CodeIgniter\Entity\Entity;
// use App\Libraries\MyCampo;
// use App\Models\Config\ConfigDicDadosModel;
// use App\Models\Config\ConfigModuloModel;
// use ReflectionClass;
// use ReflectionMethod;

// class EntCfgTela extends Entity
// {
//     public array $campos = [];

//     public function __construct(array $data = [], $show = false)
//     {
//         parent::__construct($data);
//         $this->campos = $this->defCampos($show);
//     }

//     public function defCampos($dados, $show = false, $tabela = '', $view = '')
//     {
//         $dicionario = new ConfigDicDadosModel();
//         // $usuario   = new ConfigUsuarioModel();
//         $modulo    = new ConfigModuloModel();
//         // $common    = new CommonModel();

//         $ret = [];
//         $id         = new MyCampo('cfg_tela', 'tel_id');
//         $id->valor  = array_key_exists('tel_id', $dados) ? $dados['tel_id'] : '';
//         $ret['tel_id'] = $id->crOculto();

//         $opc_mod = [];
//         // if (isset($dados['mod_id'])){
//         $opc_mods = $modulo->getModulo();
//         $opc_mod  = array_column($opc_mods, 'mod_nome', 'mod_id');
//         // }

//         $modu               =  new MyCampo('cfg_tela', 'mod_id');
//         $modu->label        = 'Módulo';
//         $modu->largura      = 50;
//         $modu->obrigatorio  = true;
//         $modu->urlbusca     = base_url('buscas/busca_modulo');
//         $modu->cadModal     = base_url('CfgModulo/add/modal=true');
//         $modu->valor        = array_key_exists('mod_id', $dados) ? $dados['mod_id'] : '';
//         $modu->selecionado  = $modu->valor;
//         $modu->opcoes       = $opc_mod;
//         $modu->leitura      = $show;
//         $modu->dispForm     = '2col';
//         $ret['tel_modulo']   = $modu->crSelBusca();

//         $nome               = new MyCampo('cfg_tela', 'tel_nome');
//         $nome->obrigatorio  = true;
//         $nome->valor        = array_key_exists('tel_nome', $dados) ? $dados['tel_nome'] : '';
//         $nome->dispForm     = '2col';
//         $nome->leitura      = $show;
//         $ret['tel_nome']    = $nome->crInput();

//         // debug($dados,true);
//         $iden               = new MyCampo('cfg_tela', 'tel_ident');
//         $iden->obrigatorio  = true;
//         $iden->valor        = array_key_exists('tel_ident', $dados) ? $dados['tel_ident'] : '';
//         $iden->dispForm     = '2col';
//         $iden->leitura      = $show;
//         $iden->largura      = 15;
//         $iden->minLength    = 2;
//         $ret['tel_ident']    = $iden->crInput();

//         $icon                = new MyCampo('cfg_tela', 'tel_icone');
//         $icon->tipo         = 'icone';
//         $icon->valor        = (isset($dados['tel_icone'])) ? $dados['tel_icone'] : '';
//         $icon->dispForm     = '2col';
//         $icon->leitura      = $show;
//         $ret['tel_icon']     = $icon->crInput();

//         $pasta    = APPPATH . '/Controllers';
//         $arquivos = buscaArquivos($pasta, false, ['BaseController.php', 'Buscas.php', 'Logger.php']);
//         sort($arquivos);

//         $arqs               = array_combine(array_values($arquivos), array_values($arquivos));
//         $cont               = new MyCampo('cfg_tela', 'tel_controler');
//         $cont->obrigatorio  = true;
//         $cont->valor        = array_key_exists('tel_controler', $dados) ? $dados['tel_controler'] : '';
//         $cont->dispForm     = '2col';
//         $cont->opcoes       = $arqs;
//         $cont->selecionado  = $cont->valor;
//         $cont->leitura      = $show;
//         $ret['tel_cont']   = $cont->crSelect();

//         $pasta = APPPATH . '/Models';
//         $models = buscaArquivos($pasta, false, []);
//         sort($models);
//         $mods = array_combine(array_values($models), array_values($models));
//         $mode               = new MyCampo('cfg_tela', 'tel_model');
//         $mode->obrigatorio  = false;
//         $mode->valor        = array_key_exists('tel_model', $dados) ? $dados['tel_model'] : '';
//         $mode->dispForm     = '2col';
//         $mode->opcoes       = $mods;
//         $mode->selecionado  = $mode->valor;
//         $mode->leitura      = $show;
//         $ret['tel_mode']   =  $mode->crSelect();

//         $txtb               = new MyCampo('cfg_tela', 'tel_texto_botao');
//         $txtb->obrigatorio  = false;
//         $txtb->dispForm     = '2col';
//         $txtb->valor        = array_key_exists('tel_texto_botao', $dados) ? $dados['tel_texto_botao'] : '';
//         $txtb->leitura      = $show;
//         $ret['tel_txtb']   = $txtb->crInput();

//         $desc           = new MyCampo('cfg_tela', 'tel_descricao');
//         $desc->colunas  = 80;
//         $desc->linhas   = 3;
//         $desc->maximo   = 255;
//         $desc->valor    = array_key_exists('tel_descricao', $dados)
//             ? $dados['tel_descricao']
//             : '';
//         $desc->leitura      = $show;
//         $ret['tel_desc'] = $desc->crTexto();

//         $regg           = new MyCampo('cfg_tela', 'tel_regras_gerais');
//         $regg->valor    = array_key_exists('tel_regras_gerais', $dados)
//             ? $dados['tel_regras_gerais']
//             : '';
//         $regg->leitura      = $show;
//         $ret['tel_regg'] = $regg->crEditor();

//         $regc           = new MyCampo('cfg_tela', 'tel_regras_cadastro');
//         $regc->valor    = array_key_exists('tel_regras_cadastro', $dados)
//             ? $dados['tel_regras_cadastro']
//             : '';
//         $regc->leitura      = $show;
//         $ret['tel_regc'] = $regc->crEditor();

//         $tabe               =  new MyCampo();
//         $tabe->largura      = 40;
//         $tabe->dispForm     = '2col';
//         $tabe->label        = 'Tabela Principal';
//         $tabe->valor        = isset($tabela) ? $tabela : '';
//         $tabe->leitura      = $show;
//         $ret['tel_tabela']   = $tabe->crShow();

//         $cvie               =  new MyCampo();
//         $cvie->largura      = 40;
//         $cvie->dispForm     = '2col';
//         $cvie->label        = 'Visão Principal';
//         $cvie->valor        = isset($view) ? $view : '';
//         $cvie->leitura      = $show;
//         $ret['tel_view']   = $cvie->crShow();

//         // if ($tabela != '') {
//         $camp           = new MyCampo();
//         $camp->label    = 'Campos da Tabela';
//         $camp->valor    = '';
//         if ($tabela != '') {
//             $campos_tab = $dicionario->getCampos($tabela);
//             $camp->valor    = campos_tabela($campos_tab);
//         }
//         $ret['tel_camp'] = $camp->crShow();

//         $camp           = new MyCampo();
//         $camp->label    = 'Campos da Visão Principal';
//         if ($view != '') {
//             $campos_view = $dicionario->getCampos($view);
//             // debug($campos_view, false);
//             $camp->valor   = campos_tabela($campos_view);
//         }
//         $ret['tel_camp_view'] = $camp->crShow();

//         $trel           = new MyCampo();
//         $trel->label    = 'Tabelas Relacionadas';
//         if ($tabela != '') {
//             $relac = $dicionario->getRelacionamentos($tabela);
//             $trel->valor    = relacion_tabela($relac);
//         }
//         $ret['tel_trel'] = $trel->crShow();

//         $meto           = new MyCampo();
//         $meto->label    = 'Métodos';
//         $meto->valor    = '';
//         if (isset($dados['tel_controler'])) {
//             $path = 'App\\Controllers\\Estoque\\';
//             if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
//                 $path = 'App\\Controllers\\Config\\';
//             } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
//                 $path = 'App\\Controllers\\Produto\\';
//             } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
//                 $path = 'App\\Controllers\\Ocorrencia\\';
//             }
//             if (class_exists($path . $dados['tel_controler'])) {
//                 $class      = new ReflectionClass($path . $dados['tel_controler']);
//                 $methods    = $class->getMethods(ReflectionMethod::IS_PUBLIC);
//                 $meto->valor = metodosTela($methods, $class->name);
//             } else {
//                 $meto->valor = 'Tela ainda não foi codificada!';
//             }
//         }
//         $ret['tel_meto'] = $meto->crShow();

//         if ($dados) {
//             if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
//                 $fonte = verCodigo('Controllers/Config/' . $dados['tel_controler']);
//             } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
//                 $fonte = verCodigo('Controllers/Produto/' . $dados['tel_controler']);
//             } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
//                 $fonte = verCodigo('Controllers/Ocorrencia/' . $dados['tel_controler']);
//             } else {
//                 $fonte = verCodigo('Controllers/Estoque/' . $dados['tel_controler']);
//             }

//             $codi           = new MyCampo();
//             $codi->label    = 'Código Fonte';
//             $codi->valor    = '<pre>' . $fonte . '</pre>';
//             $ret['tel_codi'] = $codi->crShow();
//         }

//         return $ret;
//     }
// }
