<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;
use ReflectionClass;
use ReflectionMethod;
use App\Traits\HasModulo;

class EntCfgTela extends Entity
{
    use HasModulo;

    public array $campos = [];

    /** Cache do relacionamento */
    protected ?EntCfgModulo $modulo = null;

    public function __construct(array $data = [], bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /**
     * Definição dos campos da tela
     */
    public function defCampos(bool $show = false, $tabela = '', $view = ''): array
    {
        $dados = $this->toArray();
        $ret = [];
        $dicionario = new ConfigDicDadosModel();

        // ID
        $id = new MyCampo('cfg_tela', 'tel_id');
        $id->valor = $dados['tel_id'] ?? '';
        $ret['tel_id'] = $id->crOculto();

        $config['Leitura'] = $show;
        $config['DispForm'] = 'col-6';

        // debug($dados);
        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'cfg_status',
            [],
            $config
        );

        // Nome
        $nome = new MyCampo('cfg_tela', 'tel_nome');
        $nome->obrigatorio = true;
        $nome->valor = $dados['tel_nome'] ?? '';
        $nome->dispForm = 'col-6';
        $nome->leitura = $show;
        $ret['tel_nome'] = $nome->crInput();

        // Identificador
        $iden = new MyCampo('cfg_tela', 'tel_ident');
        $iden->obrigatorio = true;
        $iden->valor = $dados['tel_ident'] ?? '';
        $iden->dispForm = 'col-6';
        $iden->leitura = $show;
        $iden->largura = 15;
        $iden->minLength = 2;
        $ret['tel_ident'] = $iden->crInput();

        // Ícone
        $icon = new MyCampo('cfg_tela', 'tel_icone');
        $icon->tipo = 'icone';
        $icon->valor = $dados['tel_icone'] ?? '';
        $icon->dispForm = '2col';
        $icon->leitura = $show;
        $ret['tel_icon'] = $icon->crInput();

        // Controller
        $pasta = APPPATH . '/Controllers';
        $arquivos = buscaArquivos($pasta, false, [
            'BaseController.php',
            'Buscas.php',
        ]);
        sort($arquivos);

        $arqs = array_combine($arquivos, $arquivos);
        $cont = new MyCampo('cfg_tela', 'tel_controler');
        $cont->obrigatorio = true;
        $cont->valor = $dados['tel_controler'] ?? '';
        $cont->dispForm = '2col';
        $cont->opcoes = $arqs;
        $cont->selecionado = $cont->valor;
        $cont->leitura = $show;
        $ret['tel_cont'] = $cont->crSelect();

        // Model
        $pasta = APPPATH . '/Models';
        $models = buscaArquivos($pasta, false, []);
        sort($models);

        $mods = array_combine($models, $models);
        $mode = new MyCampo('cfg_tela', 'tel_model');
        $mode->valor = $dados['tel_model'] ?? '';
        $mode->dispForm = '2col';
        $mode->opcoes = $mods;
        $mode->selecionado = $mode->valor;
        $mode->leitura = $show;
        $ret['tel_mode'] = $mode->crSelect();

        // Texto botão
        $txtb = new MyCampo('cfg_tela', 'tel_texto_botao');
        $txtb->valor = $dados['tel_texto_botao'] ?? '';
        $txtb->dispForm = '2col';
        $txtb->leitura = $show;
        $ret['tel_txtb'] = $txtb->crInput();

        // Descrição
        $desc = new MyCampo('cfg_tela', 'tel_descricao');
        $desc->linhas = 3;
        $desc->maximo = 255;
        $desc->valor = $dados['tel_descricao'] ?? '';
        $desc->leitura = $show;
        $ret['tel_desc'] = $desc->crTexto();

        $ret['tel_codi'] = '';
        if ($dados) {
            if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
                $fonte = verCodigo('Controllers/Config/' . $dados['tel_controler']);
            } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
                $fonte = verCodigo('Controllers/Produto/' . $dados['tel_controler']);
            } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
                $fonte = verCodigo('Controllers/Ocorrencia/' . $dados['tel_controler']);
            } else {
                $fonte = verCodigo('Controllers/Estoque/' . $dados['tel_controler']);
            }

            $codi           = new MyCampo();
            $codi->label    = 'Código Fonte';
            $codi->valor    = '<pre>' . $fonte . '</pre>';
            $ret['tel_codi'] = $codi->crShow();
        }

        // Regras
        foreach (['tel_regras_gerais', 'tel_regras_cadastro'] as $campo) {
            $reg = new MyCampo('cfg_tela', $campo);
            $reg->valor = $dados[$campo] ?? '';
            $reg->leitura = $show;
            $ret[$campo] = $reg->crEditor();
        }

        $ret['tel_regg'] = $ret['tel_regras_gerais'];
        $ret['tel_regc'] = $ret['tel_regras_cadastro'];

        // Informações técnicas
        $ret['tel_tabela'] = (new MyCampo())->setLabel('Tabela Principal')
            ->setDispForm('col-6')
            ->setLargura(40)
            ->setValor($tabela)->crShow();

        $ret['tel_view'] = (new MyCampo())->setLabel('Visão Principal')
            ->setDispForm('col-6')
            ->setLargura(40)
            ->setValor($view)->crShow();

        $ret['tel_camp'] = '';
        if ($tabela) {
            $ret['tel_camp'] = (new MyCampo())
                ->setDispForm('col-12')
                ->setLabel('Campos da Tabela')
                ->setValor(campos_tabela($dicionario->getCampos($tabela)))
                ->crShow();
        }

        $ret['tel_camp_view'] = '';
        if ($view) {
            $ret['tel_camp_view'] = (new MyCampo())
                ->setDispForm('col-12')
                ->setLabel('Campos da Visão Principal')
                ->setValor(campos_tabela($dicionario->getCampos($view)))
                ->crShow();
        }

        $trel = (new MyCampo())->setDispForm('col-12');
        $trel->label = 'Tabelas Relacionadas';

        if (!empty($tabela)) {
            // debug($tabela);
            $relac = $dicionario->getRelacionamentos($tabela);
            // debug($relac);
            $trel->valor = relacion_tabela($relac['relacionamentos']);
        } else {
            $trel->valor = '';
        }

        $ret['tel_trel'] = $trel->crShow();

        // Métodos do controller
        $ret['tel_meto'] = '';
        if (!empty($dados['tel_controler'])) {
            $path = 'App\\Controllers\\Config\\';
            $ctrl = $path . $dados['tel_controler'];

            $meto = new MyCampo();
            $meto->setLabel('Métodos');

            if (class_exists($ctrl)) {
                $class = new ReflectionClass($ctrl);
                $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
                $meto->valor = metodosTela($methods, $class->name);
            } else {
                $meto->valor = 'Tela ainda não foi codificada!';
            }

            $ret['tel_meto'] = $meto->crShow();
        }

        return $ret;
    }

    public function defCamposLista($dados, $show = false, $pos = 0, $view = false)
    {
        $dados = (array) $dados;
        $ret = [];
        $dicionario = new ConfigDicDadosModel();

        $campos_tab = $dicionario->getCampos($view);
        // debug($campos_tab);
        $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');

        $id             = new MyCampo('cfg_tela_lista', 'lis_id');
        $id->nome       = $id->nome . "[$pos]";
        $id->id         = $id->id . "[$pos]";
        $id->valor      = isset($dados['lis_id']) ? $dados['lis_id'] : '';
        $ret['lis_id']   = $id->crOculto();

        $camp           = new MyCampo('cfg_tela_lista', 'lis_campo');
        $camp->nome     = $camp->nome . "[$pos]";
        $camp->id       = $camp->id . "[$pos]";
        $camp->opcoes         = $campos_lis;
        $camp->valor          = (isset($dados['lis_campo'])) ? $dados['lis_campo'] : '';
        $camp->selecionado    = $camp->valor;
        $camp->dispForm        = 'col-6';
        $camp->classep        = 'semmb';
        $camp->leitura        = $show;
        $ret['lis_campo']      = $camp->crSelect();

        $rotu           = new MyCampo('cfg_tela_lista', 'lis_rotulo');
        $rotu->nome     = $rotu->nome . "[$pos]";
        $rotu->id       = $rotu->id . "[$pos]";
        $rotu->valor          = (isset($dados['lis_rotulo'])) ? $dados['lis_rotulo'] : '';
        $rotu->dispForm        = 'col-6';
        $rotu->classep        = 'semmb';
        $rotu->leitura      = $show;
        $ret['lis_rotulo']       = $rotu->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete listagem";
        $add->funcChan  = "addCampo('" . base_url("CfgTela/addCampoLista/" . $dados['tel_id']) . "','listagem',this)";
        $ret['bt_add']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui listagem";
        $del->funcChan  = "exclui_campo('listagem',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_del']   = $del->crBotao();

        return $ret;
    }
}
