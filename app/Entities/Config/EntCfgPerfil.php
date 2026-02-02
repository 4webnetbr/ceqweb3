<?php

namespace App\Entities\Config;

use App\Traits\HasTela;
use App\Libraries\Campos;
use App\Traits\HasModulo;
use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgPerfil extends Entity
{
    use HasModulo, HasTela;

    protected $attributes = [
        'prf_id'          => null,
        'prf_nome'        => null,
        'prf_dashboard'   => null,
        'prf_descricao'   => null,
    ];

    public array $campos = [];
    public array $camposPermissoes = [];

    protected $dates = ['prf_excluido'];


    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }



    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        $id             = new MyCampo('cfg_perfil', 'prf_id');
        $id->valor      = (isset($dados['prf_id'])) ? $dados['prf_id'] : '';
        $ret['prf_id']   = $id->crOculto();

        $nome           =  new MyCampo('cfg_perfil', 'prf_nome');
        $nome->obrigatorio = true;
        $nome->valor    = (isset($dados['prf_nome'])) ? $dados['prf_nome'] : '';
        $ret['prf_nome'] = $nome->crInput();

        $config['Leitura'] = $show;
        $config['Largura'] = 50;
        $config['Label']   = 'Tela Inicial (DashBoard)';

        $ret['prf_dashboard'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['prf_dashboard'] ?? '',
            1,
            'cfg_status',
            [],
            $config,
            'prf_dashboard'
        );


        $desc =  new MyCampo('cfg_perfil', 'prf_descricao');
        $desc->obrigatorio = false;
        $desc->linhas   = 3;
        $desc->colunas  = 80;
        $desc->valor    = (isset($dados['prf_descricao'])) ? $dados['prf_descricao'] : '';
        $ret['prf_descricao'] = $desc->crTexto();

        return $ret;
    }

    public function defCamposPermissoes(int $pos, $mod, ?object $items = null)
    {
        $ret = [];

        $modu                = new MyCampo();
        $modu->label         = '';
        $modu->valor         = (isset($items->mod_nome)) ? $items->mod_nome : '';
        $ret['pit_modu']     = $modu->crTextShow();

        $tela                = new MyCampo();
        $tela->label         = '';
        $tela->valor         = (isset($items->tel_nome)) ? $items->tel_nome : '';
        $ret['pit_tela']      = $tela->crTextShow();

        $all                 = new MyCampo();
        // $all->objeto         = 'checkbox';
        $all->nome           = "pit_all[$mod][$pos]";
        $all->id             = "pit_all[$mod][$pos]";
        $all->label          = '';
        $all->obrigatorio    = false;
        $all->valor          = '';
        $all->selecionado    = 'X';
        $all->size           = 20;
        $all->tamanho        = 1;
        $all->classep         = "pit_all[$mod]";
        $ret['pit_all']       = $all->crCheckbox();

        $consulta                = new MyCampo();
        // $consulta->objeto        = 'checkbox';
        $consulta->nome          = "pit_consulta[$mod][$pos]";
        $consulta->id            = "pit_consulta[$mod][$pos]";
        $consulta->label         = '';
        $consulta->obrigatorio   = false;
        $consulta->valor         = 'C';
        $consulta->selecionado   = '';
        if (isset($items->pit_permissao)) {
            if (strpos($items->pit_permissao, 'C') !== false) {
                $consulta->selecionado = 'C';
            }
        }
        $consulta->size          = 20;
        $consulta->tamanho       = 1;
        $consulta->classep        = "pit_consulta[$mod] pit_all[$mod]";
        $ret['pit_consulta']      = $consulta->crCheckbox();

        $adicao                  = new MyCampo();
        // $adicao->objeto          = 'checkbox';
        $adicao->nome            = "pit_adicao[$mod][$pos]";
        $adicao->id              = "pit_adicao[$mod][$pos]";
        $adicao->label           = '';
        $adicao->obrigatorio     = false;
        $adicao->valor           = 'A';
        $adicao->selecionado     = '';
        if (isset($items->pit_permissao)) {
            if (strpos($items->pit_permissao, 'A') !== false) {
                $adicao->selecionado = 'A';
            }
        }
        $adicao->size            = 20;
        $adicao->tamanho         = 1;
        $adicao->classep          = "pit_adicao[$mod] pit_all[$mod]";
        $ret['pit_adicao']        = $adicao->crCheckbox();

        $edicao                  = new MyCampo();
        // $edicao->objeto          = 'checkbox';
        $edicao->nome            = "pit_edicao[$mod][$pos]";
        $edicao->id              = "pit_edicao[$mod][$pos]";
        $edicao->label           = '';
        $edicao->obrigatorio     = false;
        $edicao->valor           = 'E';
        $edicao->selecionado     = '';
        if (isset($items->pit_permissao)) {
            if (strpos($items->pit_permissao, 'E') !== false) {
                $edicao->selecionado = 'E';
            }
        }
        $edicao->size            = 20;
        $edicao->tamanho         = 1;
        $edicao->classep         = "pit_edicao[$mod] pit_all[$mod]";
        $ret['pit_edicao']        = $edicao->crCheckbox();

        $exclusao                = new MyCampo();
        // $exclusao->objeto        = 'checkbox';
        $exclusao->nome          = "pit_exclusao[$mod][$pos]";
        $exclusao->id            = "pit_exclusao[$mod][$pos]";
        $exclusao->label         = '';
        $exclusao->obrigatorio   = false;
        $exclusao->valor         = 'X';
        $exclusao->selecionado   = '';
        if (isset($items->pit_permissao)) {
            if (strpos($items->pit_permissao, 'X') !== false) {
                $exclusao->selecionado = 'X';
            }
        }
        $exclusao->size          = 20;
        $exclusao->tamanho       = 1;
        $exclusao->classep        = "pit_exclusao[$mod] pit_all[$mod]";
        $ret['pit_exclusao']      = $exclusao->crCheckbox();

        $notifica                = new MyCampo();
        // $notifica->objeto        = 'checkbox';
        $notifica->nome          = "pit_notifica[$mod][$pos]";
        $notifica->id            = "pit_notifica[$mod][$pos]";
        $notifica->label         = '';
        $notifica->obrigatorio   = false;
        $notifica->valor         = 'N';
        $notifica->selecionado   = '';
        if (isset($items->pit_permissao)) {
            if (strpos($items->pit_permissao, 'N') !== false) {
                $notifica->selecionado = 'N';
            }
        }
        $notifica->size          = 20;
        $notifica->tamanho       = 1;
        $notifica->classep        = "pit_notifica[$mod] pit_all[$mod]";
        $ret['pit_notifica']      = $notifica->crCheckbox();

        $this->camposPermissoes = $ret;

        return $ret;
    }


    // public function defCamposItens($dados, $show = false, $tabela = '', $view = '')
    // {
    //     $dicionario = new ConfigDicDadosModel();
    //     $modulo     = new ConfigModuloModel();

    //     $ret = [];
    //     $id         = new MyCampo('cfg_tela', 'tel_id');
    //     $id->valor  = array_key_exists('tel_id', $dados) ? $dados['tel_id'] : '';
    //     $ret['tel_id'] = $id->crOculto();

    //     $opc_mod = [];
    //     $opc_mods = $modulo->getModulo();
    //     $opc_mod  = array_column($opc_mods, 'mod_nome', 'mod_id');

    //     $modu               =  new MyCampo('cfg_tela', 'mod_id');
    //     $modu->label        = 'Módulo';
    //     $modu->largura      = 50;
    //     $modu->obrigatorio  = true;
    //     $modu->urlbusca     = base_url('buscas/busca_modulo');
    //     $modu->cadModal     = base_url('CfgModulo/add/modal=true');
    //     $modu->valor        = array_key_exists('mod_id', $dados) ? $dados['mod_id'] : '';
    //     $modu->selecionado  = $modu->valor;
    //     $modu->opcoes       = $opc_mod;
    //     $modu->leitura      = $show;
    //     $modu->dispForm     = '2col';
    //     $ret['tel_modulo']   = $modu->crSelBusca();

    //     $nome               = new MyCampo('cfg_tela', 'tel_nome');
    //     $nome->obrigatorio  = true;
    //     $nome->valor        = array_key_exists('tel_nome', $dados) ? $dados['tel_nome'] : '';
    //     $nome->dispForm     = '2col';
    //     $nome->leitura      = $show;
    //     $ret['tel_nome']    = $nome->crInput();

    //     // debug($dados,true);
    //     $iden               = new MyCampo('cfg_tela', 'tel_ident');
    //     $iden->obrigatorio  = true;
    //     $iden->valor        = array_key_exists('tel_ident', $dados) ? $dados['tel_ident'] : '';
    //     $iden->dispForm     = '2col';
    //     $iden->leitura      = $show;
    //     $iden->largura      = 15;
    //     $iden->minLength    = 2;
    //     $ret['tel_ident']    = $iden->crInput();

    //     $icon                = new MyCampo('cfg_tela', 'tel_icone');
    //     $icon->tipo         = 'icone';
    //     $icon->valor        = (isset($dados['tel_icone'])) ? $dados['tel_icone'] : '';
    //     $icon->dispForm     = '2col';
    //     $icon->leitura      = $show;
    //     $ret['tel_icon']     = $icon->crInput();

    //     $pasta    = APPPATH . '/Controllers';
    //     $arquivos = buscaArquivos($pasta, false, ['BaseController.php', 'Buscas.php', 'Logger.php']);
    //     sort($arquivos);

    //     $arqs               = array_combine(array_values($arquivos), array_values($arquivos));
    //     $cont               = new MyCampo('cfg_tela', 'tel_controler');
    //     $cont->obrigatorio  = true;
    //     $cont->valor        = array_key_exists('tel_controler', $dados) ? $dados['tel_controler'] : '';
    //     $cont->dispForm     = '2col';
    //     $cont->opcoes       = $arqs;
    //     $cont->selecionado  = $cont->valor;
    //     $cont->leitura      = $show;
    //     $ret['tel_cont']   = $cont->crSelect();

    //     $pasta = APPPATH . '/Models';
    //     $models = buscaArquivos($pasta, false, []);
    //     sort($models);
    //     $mods = array_combine(array_values($models), array_values($models));
    //     $mode               = new MyCampo('cfg_tela', 'tel_model');
    //     $mode->obrigatorio  = false;
    //     $mode->valor        = array_key_exists('tel_model', $dados) ? $dados['tel_model'] : '';
    //     $mode->dispForm     = '2col';
    //     $mode->opcoes       = $mods;
    //     $mode->selecionado  = $mode->valor;
    //     $mode->leitura      = $show;
    //     $ret['tel_mode']   =  $mode->crSelect();

    //     $txtb               = new MyCampo('cfg_tela', 'tel_texto_botao');
    //     $txtb->obrigatorio  = false;
    //     $txtb->dispForm     = '2col';
    //     $txtb->valor        = array_key_exists('tel_texto_botao', $dados) ? $dados['tel_texto_botao'] : '';
    //     $txtb->leitura      = $show;
    //     $ret['tel_txtb']   = $txtb->crInput();

    //     $desc           = new MyCampo('cfg_tela', 'tel_descricao');
    //     $desc->colunas  = 80;
    //     $desc->linhas   = 3;
    //     $desc->maximo   = 255;
    //     $desc->valor    = array_key_exists('tel_descricao', $dados)
    //         ? $dados['tel_descricao']
    //         : '';
    //     $desc->leitura      = $show;
    //     $ret['tel_desc'] = $desc->crTexto();

    //     $regg           = new MyCampo('cfg_tela', 'tel_regras_gerais');
    //     $regg->valor    = array_key_exists('tel_regras_gerais', $dados)
    //         ? $dados['tel_regras_gerais']
    //         : '';
    //     $regg->leitura      = $show;
    //     $ret['tel_regg'] = $regg->crEditor();

    //     $regc           = new MyCampo('cfg_tela', 'tel_regras_cadastro');
    //     $regc->valor    = array_key_exists('tel_regras_cadastro', $dados)
    //         ? $dados['tel_regras_cadastro']
    //         : '';
    //     $regc->leitura      = $show;
    //     $ret['tel_regc'] = $regc->crEditor();

    //     $tabe               =  new MyCampo();
    //     $tabe->largura      = 40;
    //     $tabe->dispForm     = '2col';
    //     $tabe->label        = 'Tabela Principal';
    //     $tabe->valor        = isset($tabela) ? $tabela : '';
    //     $tabe->leitura      = $show;
    //     $ret['tel_tabela']   = $tabe->crShow();

    //     $cvie               =  new MyCampo();
    //     $cvie->largura      = 40;
    //     $cvie->dispForm     = '2col';
    //     $cvie->label        = 'Visão Principal';
    //     $cvie->valor        = isset($view) ? $view : '';
    //     $cvie->leitura      = $show;
    //     $ret['tel_view']   = $cvie->crShow();

    //     // if ($tabela != '') {
    //     $camp           = new MyCampo();
    //     $camp->label    = 'Campos da Tabela';
    //     $camp->valor    = '';
    //     if ($tabela != '') {
    //         $campos_tab = $dicionario->getCampos($tabela);
    //         $camp->valor    = campos_tabela($campos_tab);
    //     }
    //     $ret['tel_camp'] = $camp->crShow();

    //     $camp           = new MyCampo();
    //     $camp->label    = 'Campos da Visão Principal';
    //     if ($view != '') {
    //         $campos_view = $dicionario->getCampos($view);
    //         // debug($campos_view, false);
    //         $camp->valor   = campos_tabela($campos_view);
    //     }
    //     $ret['tel_camp_view'] = $camp->crShow();

    //     $trel           = new MyCampo();
    //     $trel->label    = 'Tabelas Relacionadas';
    //     if ($tabela != '') {
    //         $relac = $dicionario->getRelacionamentos($tabela);
    //         $trel->valor    = relacion_tabela($relac);
    //     }
    //     $ret['tel_trel'] = $trel->crShow();

    //     $meto           = new MyCampo();
    //     $meto->label    = 'Métodos';
    //     $meto->valor    = '';
    //     if (isset($dados['tel_controler'])) {
    //         $path = 'App\\Controllers\\Estoque\\';
    //         if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
    //             $path = 'App\\Controllers\\Config\\';
    //         } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
    //             $path = 'App\\Controllers\\Produto\\';
    //         } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
    //             $path = 'App\\Controllers\\Ocorrencia\\';
    //         }
    //         if (class_exists($path . $dados['tel_controler'])) {
    //             $class      = new ReflectionClass($path . $dados['tel_controler']);
    //             $methods    = $class->getMethods(ReflectionMethod::IS_PUBLIC);
    //             $meto->valor = metodosTela($methods, $class->name);
    //         } else {
    //             $meto->valor = 'Tela ainda não foi codificada!';
    //         }
    //     }
    //     $ret['tel_meto'] = $meto->crShow();

    //     if (!empty($dados) && array_key_exists('tel_controler', $dados)) {
    //         if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
    //             $fonte = verCodigo('Controllers/Config/' . $dados['tel_controler']);
    //         } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
    //             $fonte = verCodigo('Controllers/Produto/' . $dados['tel_controler']);
    //         } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
    //             $fonte = verCodigo('Controllers/Ocorrencia/' . $dados['tel_controler']);
    //         } else {
    //             $fonte = verCodigo('Controllers/Estoque/' . $dados['tel_controler']);
    //         }

    //         $codi           = new MyCampo();
    //         $codi->label    = 'Código Fonte';
    //         $codi->valor    = '<pre>' . $fonte . '</pre>';
    //         $ret['tel_codi'] = $codi->crShow();
    //     }

    //     return $ret;
    // }
}
