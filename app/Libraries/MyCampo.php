<?php

namespace App\Libraries;

use App\Models\Config\ConfigDicDadosModel;
use DateTime;

// use CodeIgniter\Libraries;
// use PhpParser\Node\Stmt\Case_;

/**
 * MyCampo
 * Criação de Campo Customizado
 */

class MyCampo
{
    public string $objeto;     // informa o tipo de campo a ser criado
    public string $tipo;       // informa o tipo Customizado do campo
    public string $nome;       // Nome do Campo
    public string $id;         // Id do Campo
    public string $label;      // Rótulo do Campo
    public string $place;      // placeholder do Campo
    public string $hint;       // Hint (tooltip) a ser mostrado no campo
    public string $valor;      // Valor inicial do Campo
    public string $funcChan;   // Função que será executada na alteracao do campo
    public string $funcBlur;   // Função que será executada na saída do campo
    public string $classep;    // Classe personalizada a ser aplicado no campo
    public string $tipoArq;    // Tipos de Arquivo suportados pelo campo imagem
    public string $urlbusca;    // URL de busca do campo selbusca e do campo dependente
    public string $dispForm;   // Disposição dos campos no Formulário (linha, 2col, 3col)
    public string $pasta;      // Pasta do arquivo de imagem
    public string $imgName;    // Nome do arquivo de Imagem pré-carregada
    public string $pai;        // nome do campo pai, para um campo dependente
    public string $i_cone;     // icone do label
    public string $infotop;    // Texto mostrado antes do label
    public string $inforig;    // Texto mostrado ao lado do campo
    public string $infotexto;    // Texto mostrado ao lado do campo sem formatação
    public string $cadModal;   // Permite Cadastro Rápido em Janela Modal
    public int $size;       // Quantia de Caracteres do Campo
    public int $tamanho;    // Largura do input
    public int $largura;    // Largura do campo
    public int $alturashow; // Altura do campo para campos Show
    public int $maxLength;  // Quantia Total de Caracteres do Campo
    public int $minLength;  // Quantia Mínima de Caracteres do Campo
    public int $minimo;     // Valor mínimo para o campo do tipo number
    public int $maximo;     // Valor máximo para o campo do tipo number
    public int $step;       // Valor de incremento do campo do tipo number
    public int $colunas;    // Colunas do Textarea
    public int $linhas;     // Linhas do Textarea
    public int $ordem;      // indice do data-index
    public int $valid;      // data-valid informa se as alterações do campo devem ser validadas
    public array $opcoes;     // Array de elementos da lista
    public array $attrdata;   // Attibuto de Botão
    public bool $obrigatorio; // verdadeiro se o campo for obrigatorio
    public bool $leitura; // verdadeiro se o campo for somente leitura
    public string $datamin; // data mínima para um campo data
    public mixed $selecionado; // Item selecionado da lista
    public mixed $default; // Valor default do Select;
    public string $tabela; // Tabela do campo
    public string $campo; // Campo da Tabela
    protected $field;

    public function __construct($tabela = '', $campo = '', $showchave = false)
    {
        helper('form');
        $this->tipo         = 'text';
        $this->obrigatorio  = false;
        $this->leitura      = false;
        $this->dispForm     = 'linha';
        $this->classep      = '';
        $this->funcChan     = '';
        $this->funcBlur     = '';
        $this->ordem        = -1;
        $this->infotop      = '';
        $this->inforig      = '';
        $this->infotexto    = '';
        $this->cadModal     = '';
        $this->maximo       = 100;
        $this->tabela       = $tabela;
        $this->campo        = $campo;
        // se for passado a tabela e o campo na criação, busca as propriedades no banco
        if ($tabela != '' && $campo != '') {
            $this->doBanco($tabela, $campo, $showchave);
        }
    }

    public function acertaId()
    {
        // QUANDO TEM ORDEM, ALTERA O ID ANTES DE CRIAR AS OPÇÕES
        if ($this->ordem != -1) {
            if (strpos($this->nome, "[") === false) {
                $this->nome = $this->nome . "[" . $this->ordem . "]";
            }
            if (strpos($this->id, "[") === false) {
                $this->id = $this->id . "[" . $this->ordem . "]";
            }
            $this->field['data-index'] = $this->ordem;
        }
    }
    /**
     * doBanco
     * Busca informações do Campo no Banco de Dados
     * Esse método deve ser chamado logo após o comando new MyCampo
     * antes da definição das demais propriedades
     * @return void
     */
    public function doBanco($tabela = '', $campo = '', $showchave = false)
    {
        if (
            $tabela == '' || // tabela é vazia
            $campo == '' // campo é vazio
        ) {
            return;
        }
        $tip_camp['char']      = 'Caracter curto';
        $tip_camp['varchar']   = 'Caracter longo';
        $tip_camp['mediumtext'] = 'Texto';
        $tip_camp['text']      = 'Texto';
        $tip_camp['int']       = 'Inteiro';
        $tip_camp['decimal']   = 'Decimal';
        $tip_camp['float']     = 'Moeda';
        $tip_camp['date']      = 'Data';
        $tip_camp['timestamp'] = 'Data e Hora';
        $tip_camp['datetime']  = 'Data e Hora';

        $dicionario = new ConfigDicDadosModel();
        // debug($tabela.' '.$campo);
        $dados_campo = $dicionario->getDetalhesCampo($tabela, $campo);
        // debug($dados_campo);
        if (count($dados_campo)) {
            $dad_camp = $dados_campo[0];

            $this->id = $dad_camp['COLUMN_NAME'];
            $this->nome = $dad_camp['COLUMN_NAME'];

            if ($dad_camp['COLUMN_KEY'] == 'PRI' && !$showchave) {
                $this->objeto = 'oculto';
                return;
            }

            $this->label = $dad_camp['COLUMN_COMMENT'];
            $this->place = 'Informe ' . $dad_camp['COLUMN_COMMENT'];
            if (stripos(strtolower($dad_camp['COLUMN_NAME']), '_id')) {
                $this->place = 'Selecione ' . $dad_camp['COLUMN_COMMENT'];
            }
            $this->hint = $dad_camp['COLUMN_COMMENT'];

            switch ($tip_camp[$dad_camp['DATA_TYPE']]) {
                case 'Data':
                    $this->objeto = 'input';
                    $this->tipo = 'date';
                    $this->size = 10;
                    $this->largura = 25;
                    break;
                case 'Data e Hora':
                    $this->objeto = 'input';
                    $this->tipo = 'datetime-local';
                    $this->size = 18;
                    $this->largura = 35;
                    break;
                case 'Caracter curto':
                    $this->objeto = 'input';
                    $this->tipo = 'text';
                    if (stripos(strtolower($dad_camp['COLUMN_NAME']), 'cep')) {
                        $this->tipo = 'cep';
                    } elseif (stripos(strtolower($dad_camp['COLUMN_NAME']), 'fone')) {
                        $this->tipo = 'fone';
                    } elseif (stripos(strtolower($dad_camp['COLUMN_NAME']), 'celular')) {
                        $this->tipo = 'celular';
                    }
                    if (intval($dad_camp['COLUMN_SIZE']) > 50) {
                        $this->size = 50;
                        $this->maximo = intval($dad_camp['COLUMN_SIZE']);
                        $this->largura = 55;
                    } else {
                        $this->size = intval($dad_camp['COLUMN_SIZE']);
                        $this->maximo = intval($dad_camp['COLUMN_SIZE']);
                        $this->largura = $this->size + 8;
                    }
                    break;
                case 'Caracter longo':
                    if (intval($dad_camp['COLUMN_SIZE']) <= 100) {
                        $this->objeto = 'input';
                        $this->tipo = 'text';
                        if (intval($dad_camp['COLUMN_SIZE']) > 50) {
                            $this->size = 50;
                            $this->maximo = intval($dad_camp['COLUMN_SIZE']);
                            $this->largura = 58;
                        } else {
                            $this->size = intval($dad_camp['COLUMN_SIZE']);
                            $this->maximo = intval($dad_camp['COLUMN_SIZE']);
                            $this->largura = $this->size + 8;
                        }
                    } else {
                        $this->objeto = 'texto';
                        $this->size = intval($dad_camp['COLUMN_SIZE']);
                        $this->maximo = intval($dad_camp['COLUMN_SIZE']);
                        $this->linhas = 3;
                        $this->colunas = 80;
                    }
                    break;
                case 'Texto':
                    $this->objeto = 'texto';
                    $this->size = intval($dad_camp['COLUMN_SIZE']);
                    $this->minLength = 5;
                    $this->linhas = 3;
                    $this->colunas = 80;
                    $this->classep = 'editor';
                    break;
                case 'Inteiro':
                    $this->objeto = 'input';
                    $this->tipo = 'number';
                    $this->minimo = 1;
                    $this->step   = 1;
                    $this->maximo = 100;
                    $this->size   = 10;
                    $this->largura = 25;
                    break;
                case 'Decimal':
                    $this->objeto = 'input';
                    $this->tipo = 'quantia';
                    $this->size = $dad_camp['NUMERIC_SCALE'];
                    $this->largura = 25;
                    break;
                case 'Moeda':
                    $this->objeto = 'input';
                    $this->tipo = 'moeda';
                    $this->size = 12;
                    $this->largura = 25;
                    break;
            }
        }
        return;
    }

    /**
     * crLabel
     * Formata a Label do Campo
     * @param string $ident
     * @return string
     */
    public function crLabel(): string
    {
        $ident = '';
        if (isset($this->id)) {
            $ident = $this->id;
        }

        $label = array(
            'class'         => 'form-label p-0 m-0',
        );
        $label_text = $this->label;
        if (($this->tipo == 'file' || $this->tipo == 'imagem') && $this->tipo != 'hidden') {
            $label['class'] = "btn btn-primary";
            $label['style'] = "white-space: normal;width:$this->largura;padding:0.8em;";
            $label_text .= "<i class='fa fa-file-archive-o'></i> Selecionar Arquivo";
        }
        if ($this->dispForm == 'linha') {
            $ret = "<div class='col-2 col-lg-2 d-block'>";
            // } elseif ($this->dispForm != 'linha' || $this->tipo == 'editor') {
        } elseif ($this->dispForm != 'linha') {
            $ret = "<div class='col-12 col-lg-12 d-block'>";
        }
        if (isset($this->objeto) && $this->objeto == 'cr2opcoes' && $this->dispForm == '2col') {
            $ret = "<div class='col-3 d-block text-nowrap'>";
        }
        if (isset($this->objeto) && $this->objeto == 'cr2opcoes' && $this->dispForm == '3col') {
            $ret = "<div class='col-6 d-block text-nowrap text-start'>";
        }
        $ret .= form_label($label_text, $ident, $label);
        $ret .= "</div>";
        return $ret;
    }

    /**
     * fmtDisplay
     * Formata a disposição do Campo na tela
     * @return string
     */
    public function fmtDisplay($campo, $groupant = '', $grouppos = ''): string
    {
        $divaberta = false;
        $respf = '';
        $colunas = '';
        $mb = 'mb-0 mt-2';
        if ($this->dispForm == 'linha' || $this->tipo == 'editor') {
            $colunas = "col-12 col-lg-12";
        } elseif ($this->dispForm == '2col') {
            $colunas = "col-6 col-lg-6";
            if (isset($this->objeto) && $this->objeto == 'cr2opcoes') {
                $mb = 'my-3';
            }
        } elseif ($this->dispForm == '3col') {
            $colunas = "col-4 col-lg-4";
        } elseif ($this->dispForm == '4col') {
            $colunas = "col-3 col-lg-3";
        }
        if (substr($this->dispForm, 0, 3) == 'col') { // se for especificado em colunas variáveis
            $colunas = $this->dispForm;
            $mb = 'mb-1';
        }
        if ($this->classep == 'semmb') {
            $mb = 'm-0';
        }
        $respf .= "<div id='ig_$this->id' class='row $colunas align-items-center float-start d-inline-flex $mb'>";
        $divaberta = true;
        if ($this->infotop != '') {
            $respf .= "<div class='text-info'><i class='fa-solid fa-bullhorn'></i> $this->infotop</div>";
        }

        if ($this->label != '') {
            $respf .= $this->crLabel();
        }
        $hasvalid = '';
        if ($this->tipo == 'cpf') {
            $hasvalid = ' has-validation';
        }
        $disabled = '';
        if ($this->leitura) {
            $disabled = 'disabled';
        }

        if (isset($this->largura) && $this->tipo != 'check' && $this->tipo != 'editor') {
            $auto = 'auto';
            $largura = $this->largura . 'ch';
            if ($this->tipo == 'select') {
                $auto = $largura;
            }
            $respf .= "<div class='input-group mt-0 $hasvalid $disabled' style='width: $auto !important; max-width: $largura !important;'>";
        } elseif ($this->tipo != 'check' && $this->tipo != 'editor') {
            $respf .= "<div class='input-group mt-0 $hasvalid $disabled' 
                style='width: auto !important;'>";
        } elseif ($this->tipo == 'editor') {
            $respf .= "<div class='input-group mt-0 $disabled'>";
        } elseif ($this->tipo == 'check') {
            $respf .= "<div class='input-group mt-0 $disabled' style='width: auto !important; '>";
        }
        $respf .= $groupant;

        $respf .= $campo;

        if($grouppos == 'arquivo' ){
            $respf .= "<div id='nome_arquivo_img_" . $this->nome . "' class='text-break border p-1 mb-2' style='width: auto;'></div>";
            $respf .= "<div id='view_img_" . $this->nome . "' class='show img-thumbnail ' >";
            $respf .= "<img id='img_" . $this->nome . "' src='" . $this->selecionado . "' for='" . $this->id . "' class='img-thumbnail sempadding' alt='' style='width:" . $this->size . "px;' />";
            $respf .= "</div>";
            $respf .= "</label>";
        } else {
            $respf .= $grouppos;
        }

        if (!$this->leitura) {
            $respf .= "<div id='" . $this->id . "-fival' class='invalid-feedback'>";
            $respf .= $this->label . ' é obrigatório';
            $respf .= "</div>";
            // }
            if ($this->tipo == 'cpf') {
                $respf .= "<div id='" . $this->id . "-fival' class='invalid-feedback'>";
                $respf .= "CPF Inválido, verifique!";
                $respf .= "</div>";
            }
        }
        if ($this->tipo == 'password' || $this->tipo == 'senha') {
            $respf .= "<div id='pass-info' class='invalid-feedback
                                border border-1 bg-white position-content p-2 text-start'
                                style='z-index:200;top:2rem'></div>";
        }
        $respf .= "</div>";
        if ($this->inforig != '') {
            $respf .= "<div class='text-warning fst-italic w-auto ms-3'>
                        <i class='fa-solid fa-triangle-exclamation'></i> $this->inforig </div>";
        }
        if ($this->infotexto != '') {
            $respf .= $this->infotexto;
        }
        // if ($this->tipo == 'text' || $this->objeto == 'texto' || isset($this->maxLength)) {
        // $respf .= "<div id='dc-$this->id' class='div-caract badge bg-info-subtle'></div>";
        // }

        if ($this->cadModal != '' && !$this->leitura) {
            $field_btn = array(
                'name'          => 'bt_ad_' . $this->nome,
                'id'            => 'bt_ad_' . $this->id,
                'style'         => 'width:2.5rem',
                'type'          => 'button',
                // 'hint'          => "Novo Cadastro",
                'class'         => "btn btn-outline-secondary m-0",
                'content'       => "<i class='fa-solid fa-wand-sparkles fa-flip-horizontal'></i> ",
                'onclick'       => "openModal('" . $this->cadModal . "')"
            );
            $respf .= form_button($field_btn);
        }
        $respf .= "</div>";

        return $respf;
    }

    /**
     * propriedades
     * Acerta as propriedades (leitura, obrigatório e hint)
     * @param mixed $this->field
     * @return void
     */
    public function propriedades()
    {
        $selec = '';
        if (isset($this->selecionado)) {
            if (gettype($this->selecionado) == 'array') {
                $selec = implode(',', $this->selecionado);
            } else {
                $selec = $this->selecionado;
            }
        }
        // debug($this->selecionado);
        $this->field['data-enabled']  = $this->leitura;
        $this->field['data-alter']    = false;
        $this->field['data-valid']    = isset($this->valid) ? $this->valid : false;
        $this->field['data-live-search'] = 'true';
        $this->field['data-label']    = isset($this->label) ? $this->label : "";
        $this->field['data-valor']    = isset($this->valor) ? $this->valor : "";
        $this->field['data-selec']    = $selec;
        $this->field['data-obrig']    = isset($this->obrigatorio) && $this->obrigatorio ? "required" : "";
        $this->field['placeholder']   = isset($this->place) ? $this->place : "";
        $this->field['label']         = isset($this->label) ? $this->label : "";
        $this->field['hint']          = isset($this->hint) ? $this->hint : "";
        $this->field['autocomplete']  = 'off';
        $this->field['onchange']      = isset($this->funcChan) ? $this->funcChan : "";
        $this->field['onblur']        = isset($this->funcBlur) ? $this->funcBlur : "";
        if (isset($this->default)) {
            $this->field['data-default'] = $this->default;
        }

        if ($this->tipo == 'senha') {
            $this->field['type'] = "password";
        }
        if (
            $this->obrigatorio &&
            $this->tipo != 'login' &&
            $this->tipo != 'password' &&
            $this->tipo != 'senha' &&
            !$this->leitura
        ) {
            $this->field['required'] = true;
            if ($this->tipo == 'text' || $this->objeto == 'texto') {
                $this->field['minLength'] = isset($this->minLength) ? isset($this->minLength) : 5;
            }
        }
        if ($this->leitura) {
            $this->field['readonly'] = "readonly";
            $this->field['disabled'] = "disabled";
            $this->field['onfocus'] = "this.blur()";
            $this->field['tabindex'] = -1;
        }

        if (isset($this->hint) && $this->hint != '') {
            $this->field['data-mdb-toggle'] = "tooltip";
            $this->field['data-mdb-placement'] = "top";
            $this->field['title'] = $this->hint;
        }

        // QUANDO TEM ORDEM DEFINIDA ALTERA O ID E O NOME COLOCANDO A ORDEM
        // OS CAMPOS DO TIPO CHECK ESSA AÇÃO É FEITA ANTES DE CRIAR AS OPÇÕES
        // if ($this->ordem != -1 && $this->tipo != 'check') {
        //     if (strpos("[", $this->field['name']) === false) {
        //         $this->field['name'] = $this->field['name'] . "[" . $this->ordem . "]";
        //     }
        //     if (strpos("[", $this->field['id']) === false) {
        //         $this->field['id'] = $this->field['id'] . "[" . $this->ordem . "]";
        //     }
        //     $this->field['data-index'] = $this->ordem;
        // }
        return;
    }

    /**
     * crShow
     * Mostra a informação com formato de campo
     * @return string
     */
    public function crShow(): string
    {
        $resp = '';
        $resp = "<div class='row col-12 col-lg-12 align-items-center float-start d-inline-flex mb-0 mt-2'>";
        if ($this->label != '') {
            $resp .= $this->crLabel();
        }
        $altu  = '30 rem';
        if (isset($this->alturashow)) {
            $altu  = $this->alturashow . 'rem';
        }
        $larg = '100%';
        if (isset($this->largura)) {
            $larg = $this->largura . 'ch';
        }

        $resp .= "<div class='border rounded bg-gradient-secondary input-group mb-lg-1 mb-2 overflow-auto form-control'
                        style='width: $larg !important; height: $altu !important'>";
        $resp .= isset($this->valor) ? $this->valor : '';
        $resp .= "</div>";
        $resp .= "</div>";

        return $resp;
    }

    /**
     * crBotao
     * Cria um botão de ação
     * @return string
     */
    public function crBotao(): string
    {
        $this->acertaId();
        // NÃO TEM FORMATO NO FORMULÁRIO
        $txtlabel = isset($this->label) ? $this->label : "";
        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'type'          => $this->tipo,
            'class'         => "btn $this->classep ",
            'content'       => $this->i_cone,
            'onclick'       => $this->funcChan,
            'title'         => $this->place,
        );
        $this->propriedades();

        if (isset($this->attrdata)) {
            foreach ($this->attrdata as $key => $value) {
                $this->field[$key] = $value;
            }
        }
        $resp = form_button($this->field);
        return $resp;
    }

    /**
     * crOculto
     * Cria um campo hidden (oculto)
     * @return string
     */
    public function crOculto(): string
    {
        $this->acertaId();
        // NÃO TEM FORMATO NO FORMULÁRIO
        $this->field = array(
            'type'  => 'hidden',
            'name'  => $this->nome,
            'id'    => $this->nome,
            'value' => $this->valor,
        );

        $resp = form_input($this->field);
        return $resp;
    }

    /**
     * crCheckbox
     * cria CheckBox em formato normal
     * @return string
     */
    public function crCheckbox(): string
    {
        $this->acertaId();
        $resp = '';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'value'         => $this->valor,
            'data-selec'    => $this->selecionado,
            'class'         => "form-check-input ml-2 float-start $this->classep",
        );
        if ($this->valor == $this->selecionado) {
            $this->field['checked'] = true;
        }

        $this->propriedades();
        $campo = form_checkbox($this->field);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crCheckbutton
     * Cria Checkbox em formato de Botão
     * @return string
     */
    public function crCheckbutton(): string
    {
        $this->acertaId();
        $this->tipo = 'check';
        $resp = '';

        $resp .= "<div class='form-check form-switch form-check-inline 
                                form-control px-1 sort overflow-auto 
                                overflow-x-hidden' 
                                style='width: auto; max-height: 70vh; '>";
        $cont = 0;
        foreach ($this->opcoes as $valor => $label) {
            $id = $this->id . '[' . $cont . ']';
            if (!isset($this->selecionado)) {
                $this->selecionado = $valor;
            }

            $this->field = array(
                'name'          => $this->nome,
                'id'            => $id,
                'value'         => $valor,
                'class'         => "btn-check ui-state-default position-fixed"
            );
            $checked = false;
            if ($valor == $this->selecionado) {
                $checked = true;
            }
            $this->propriedades();

            $lab = "<label class='btn $this->classep fs-4' for='$id'> $label </label>";
            $resp .= "<div class='d-inline-flex me-2 col-12'>";
            $resp .= form_checkbox($this->field, '', $checked) . $lab;
            $resp .= '</div>';
            $cont++;
        }
        $resp .= "</div>";

        $resp .= $this->fmtDisplay($resp);
        return $resp;
    }

    /**
     * cr2opcoes
     * Cria um input Radio de 2 opções que mostra a que está selecionada
     * @return string
     */
    public function cr2opcoes(): string
    {
        $this->acertaId();
        $this->tipo = 'check';
        $this->objeto = 'cr2opcoes';
        $resp = '';
        $campo = '';

        $cont = 0;
        foreach ($this->opcoes as $valor => $label) {
            $id = $this->id . '[' . $cont . ']';
            if ($cont == 0) {
                $cor = 'btn btn-outline-primary';
                $corradio = 'duasOpcoes primeira';
            } else {
                $cor = 'btn btn-outline-secondary';
                $corradio = 'duasOpcoes segunda';
            }
            if (!isset($this->selecionado)) {
                $this->selecionado = $valor;
            }
            $disabled = '';
            if ($this->leitura) {
                $disabled = 'disabled';
            }


            $this->field = array(
                'name'          => $this->nome,
                'id'            => $id,
                // 'value'         => $valor,
                'data-selec'    => $this->selecionado,
                'data-salva'    => true,
                'data-index'    => $cont,
                'disabled'      => true,
                'class'         => "form-check-input $corradio ml-2 $this->classep "
            );
            $disp = 'd-none';
            $checked = false;
            if ($valor == $this->selecionado) {
                // $this->field['checked'] = true;
                $checked = true;
                $disp = 'd-block';
            }
            $this->propriedades();
            $lab = "<label class='form-check-label px-1 m-auto mx-0 duasOpcoes' for='$id'> $label </label>";
            $campo .= "<div class='form-check form-switch form-check-inline form-control ps-1 pe-3 duasOpcoes $disp $cor $disabled' style='width: auto;height:33px;padding-top:2px'>";
            $campo .= "<div class='d-inline-flex' style='width: auto;'>";
            $campo .= form_radio($this->field, $valor, $checked) . $lab;
            $campo .= '</div>';
            $campo .= "</div>";
            $cont++;
        }

        $resp .= $this->fmtDisplay($campo);
        return $resp;
    }

    /**
     * crRadio
     * Cria um input Radio normal
     * @return string
     */
    public function crRadio(): string
    {
        $this->acertaId();
        $this->tipo = 'check';
        $resp = '';
        $campo = '';

        $campo .= "<div class='form-check form-switch form-check-inline 
                                form-control px-1' style='width: auto'>";
        $cont = 0;
        foreach ($this->opcoes as $valor => $label) {
            $id = $this->id . '[' . $cont . ']';
            if (!isset($this->selecionado)) {
                $this->selecionado = $valor;
            }
            $this->field = array(
                'name'          => $this->nome,
                'id'            => $id,
                'value'         => $valor,
                'data-selec'    => $this->selecionado,
                'data-salva'    => true,
                'class'         => "form-check-input ml-2 $this->classep"
            );
            if ($valor == $this->selecionado) {
                $this->field['checked'] = true;
            }
            $this->propriedades();

            $lab = "<label class='form-check-label px-1 m-auto mx-0' for='$id'> $label </label>";
            $campo .= "<div class='d-inline-flex' style='width: auto'>";
            $campo .= form_radio($this->field) . $lab;
            $campo .= '</div>';
            $cont++;
        }
        $campo .= "</div>";
        $resp .= $this->fmtDisplay($campo);
        return $resp;
    }

    /**
     * crRadiobutton
     * cria um input Radio em formato de botão
     * @return string
     */
    public function crRadiobutton(): string
    {
        $this->acertaId();
        $this->tipo = 'check';
        $resp = '';
        $campo = '';
        // $resp .= "<div class='form-check form-switch form-check-inline 
        //                         form-control px-1 w-auto'>";
        $campo .= "<div class='form-check form-switch form-check-inline 
                                form-control px-1 sort overflow-auto 
                                overflow-x-hidden' 
                                style='width: auto; max-height: 70vh; '>";
        $cont = 0;
        foreach ($this->opcoes as $valor => $label) {
            $id = $this->id . '[' . $cont . ']';
            if (!isset($this->selecionado)) {
                $this->selecionado = $valor;
            }

            $this->field = array(
                'name'          => $this->nome,
                'id'            => $id,
                'value'         => $valor,
                'autocomplete'  => 'off',
                'class'         => "btn-check ui-state-default position-fixed"
                // 'class'         => "btn-check position-fixed"
            );
            $this->propriedades();
            $checked = false;
            if ($valor == $this->selecionado) {
                $this->field['checked'] = true;
                $checked = true;
            }
            // if ($this->leitura === true) {
            //     $this->field['readonly'] = "readonly";
            //     $this->field['disabled'] = "disabled";
            //     $this->field['onfocus'] = "this.blur()";
            //     $this->field['tabindex'] = -1;
            // }

            $lab = "<label class='btn $this->classep fs-4' for='$id'> $label </label>";
            $campo .= "<div class='d-inline-flex me-2 col-12'>";
            $campo .= form_radio($this->field, '', $checked) . $lab;
            $campo .= '</div>';
            $cont++;
        }
        $campo .= "</div>";
        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crInput
     * Cria um campo input normal
     * @return string
     */
    public function crInput(): string
    {
        $this->acertaId();
        $resp = '';
        if (isset($this->objeto) && $this->objeto == 'texto') {
            $resp .= $this->crTexto();
        } else if (isset($this->objeto) && $this->objeto == 'oculto') {
            $resp .= $this->crOculto();
        } else {
            $groupant = '';
            $grouppos = '';
            $this->field = array(
                'type'          => $this->tipo,
                'name'          => $this->nome,
                'id'            => $this->id,
                'value'         => $this->valor,
                'size'          => $this->size,
                'maxlength'     => isset($this->maxLength) ? $this->maxLength : $this->size,
                'class'         => "form-control $this->classep",
                'data-inicial'  => $this->valor,
                'data-nome'     => $this->campo,
            );
            $leng =  !isset($this->maxLength) ? $this->size : $this->maxLength;
            $pe = 'pe-auto';
            if ($this->leitura) {
                $pe = 'pe-none';
            }
            switch ($this->tipo) {
                case 'color':
                    $this->field['type']  = 'color';
                    $this->field['class'] = "form-control $this->classep";
                    $this->field['aria-describedby'] = 'ig_' . $this->nome;
                    break;
                case 'icone':
                    $this->field['type']  = 'text';
                    $this->field['class'] = "form-control $this->classep icone";
                    $this->field['aria-describedby'] = 'ig_' . $this->nome;
                    $groupant .= "<span class='input-group-text input-group-addon'>
                                <i class='" . $this->valor . "'></i></span>";
                    break;
                case 'sonumero':
                    $this->field['type']      = 'text';
                    $this->field['onkeyup']   = 'mascara(this, \'mnum\')';
                    $this->field['onchange']  = 'mascara(this, \'mnum\')';
                    $this->field['pattern']   = '^[0-9-]{1,' . $leng . '}$';
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby'] = 'ig_' . $this->nome;
                    break;
                case 'quantia':
                    $this->field['type']      = 'text';
                    $this->field['onkeyup']   = 'mascara(this, \'mquantia\')';
                    $this->field['onblur']    = $this->funcBlur;
                    $this->field['value']     = floatToQuantia($this->valor, $this->size);
                    $this->field['pattern']   = "/^([\d]*\,?[\d]{0," . $this->size . "})$/";
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby'] = 'ig_' . $this->nome;
                    break;
                case 'inteiro':
                    // $this->field['type']      = 'text';
                    $this->field['dir']       = 'rtl';
                    $this->field['min']       = $this->minimo;
                    $this->field['max']       = $this->maximo;
                    $this->field['step']      = $this->step;
                    $this->field['onkeyup']   = 'mascara(this, \'mnum\')';
                    $this->field['onchange']  = 'mascara(this, \'mnum\')';
                    $this->field['pattern']   = '^[1-9][0-9]{0,' . $leng . '}$';
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby']  = 'ig_' . $this->nome;
                    $this->field['class']     = $this->field['class'] . ' form-number';
                    $groupant = "<div class='input-group-text input-group-addon down-num $pe' data-refer='$this->id' id='dw_$this->nome'><i class='fas fa-minus'></i></div>";
                    $grouppos = "<div class='input-group-text input-group-append up-num $pe' data-refer='$this->id' id='up_$this->nome'><i class='fas fa-plus'></i></div>";
                    break;
                case 'number':
                    // $this->field['type']      = 'text';
                    $this->field['dir']       = 'rtl';
                    $this->field['min']       = $this->minimo;
                    $this->field['max']       = $this->maximo;
                    $this->field['step']      = $this->step;
                    $this->field['onfocus']   = 'entrar_moeda(this)';
                    $this->field['onkeyup']   = 'mascara(this, \'mnum\')';
                    $this->field['pattern']   = '^[1-9][0-9]{0,' . $leng . '}$';
                    $this->field['style']     = 'text-align: right';
                    $this->field['class']     = $this->field['class'] . ' form-number';
                    $groupant = "<div class='input-group-text input-group-addon down-num $pe' data-refer='$this->id' id='dw_$this->nome'><i class='fas fa-minus'></i></div>";
                    $grouppos = "<div class='input-group-text input-group-append up-num $pe' data-refer='$this->id' id='up_$this->nome'><i class='fas fa-plus'></i></div>";
                    break;
                case 'moeda':
                    $this->field['type']      = 'text';
                    $this->field['onkeyup']   = 'mascara(this, \'mvalor\')';
                    $this->field['pattern']   = "/^([\$]?)([0-9]*\,?[0-9]{0,2})$/";
                    $this->field['onchange']  = $this->funcChan;
                    $this->field['data-origin'] = floatToMoeda($this->valor);
                    $this->field['value']     = floatToMoeda($this->valor);
                    $this->field['data-person'] = '0';
                    $this->field['onblur']    = 'sair_moeda(this);' . $this->funcBlur;
                    $this->field['onfocus']   = 'entrar_moeda(this)';
                    $this->field['class']     = $this->field['class'] . ' moeda has-validation';
                    $this->field['style']     = 'text-align: right';
                    break;
                case 'date':
                case 'datetime-local':
                    if (isset($this->datamin)) {
                        $this->field['min']  = $this->datamin;
                    }
                    break;
                case 'senha':
                    $groupant .= "<span class='input-group-text input-group-addon' 
                                id='ad_$this->nome'><i class='bi bi-key'></i></span>";
                    break;
                case 'password':
                    $fieldpassoculto = array(
                        'type'      => 'password',
                        'name'      => 'enganagoogle',
                        'value'     => '',
                        'style'     => "opacity: 0;position: absolute;"
                    );
                    $resp .= form_input($fieldpassoculto);
                    $this->field['class']     = "form-control $this->classep password";
                    $this->field['onchange']  = $this->funcChan;
                    $this->field['onblur']    = 'validaSenha(this);oculta_passinfo();' . $this->funcBlur;
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $groupant .= "<span class='input-group-text input-group-addon' 
                                id='ad_$this->nome'><i class='bi bi-key'></i></span>";
                    break;
                case 'email':
                    $this->field['type']      = 'email';
                    $this->field['pattern']   = '/^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/';
                    $this->field['style']     = 'text-align: left';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Informe um E-mail válido!';
                    $this->field['title']     = 'Informe um E-mail válido!';
                    $grouppos .= "<span class='input-group-text input-group-append' id='ad_$this->nome'>
                            <i class='far fa-envelope-open' ></i></span>";
                    break;
                case 'site':
                case 'url':
                    $this->field['type']      = 'url';
                    $this->field['pattern']   = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
                    $this->field['style']     = 'text-align: left';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Informe uma url válida!';
                    $this->field['title']     = 'Informe uma url válida!';
                    $grouppos .= "<span class='input-group-text input-group-append' id='ad_$this->nome'>
                                <i class='far fa-link'></i></span>";
                    break;
                case 'telefone':
                case 'fone':
                    $this->field['type']      = 'tel';
                    $this->field['pattern']   = '/^\(\d{2}\) \d{4}\-\d{4}$/';
                    $this->field['onkeyup']   = 'mascara(this, \'mtel\')';
                    $this->field['style']     = 'text-align: left';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Informe um Telefone válido! (99) 9999-9999';
                    $this->field['title'] = 'Informe um Telefone válido! (99) 9999-9999';
                    $grouppos .= "<span class='input-group-text input-group-append' id='ad_$this->nome'></span>
                                <i class='fas fa-phone' ></i></span>";
                    break;
                case 'celular':
                case 'celul':
                case 'whatsapp':
                case 'whats':
                    $this->field['type']      = 'tel';
                    $this->field['pattern']   = '/^\(\d{2}\) \d{4,5}\-\d{4}$/';
                    $this->field['onkeyup']   = 'mascara(this, \'mcel2\')';
                    $this->field['style']     = 'text-align: left';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Informe um Celular válido! (99) 99999-9999';
                    $this->field['title']     = 'Informe um Celular válido! (99) 99999-9999';
                    $grouppos .= "<span class='input-group-text input-group-append' id='ad_$this->nome'>
                                <i class='fa fa-mobile-alt'></i></span>";
                    break;
                case 'cnpj':
                    $this->field['type']      = 'text';
                    $this->field['pattern']   = '/^\\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/';
                    $this->field['onkeyup']   = 'mascara(this, \'mcnpj\')';
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Digite o CNPJ no formato 99.999.999/9999-99';
                    $this->field['title']     = 'Digite o CNPJ no formato 99.999.999/9999-99';
                    break;
                case 'cpf':
                    $this->field['type']      = 'text';
                    $this->field['pattern']   = '/^\\d{3}\.\d{3}\.\d{3}\-\d{2}/';
                    $this->field['onkeyup']   = 'mascara(this, \'mcpf\')';
                    $this->field['onblur']    = $this->field['onblur'] . ';ValidaCPF(this)';
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Digite o CPF no formato 999.999.999-99';
                    $this->field['title']     = 'Digite o CPF no formato 999.999.999-99';
                    break;
                case 'cep':
                    $this->field['type']      = 'text';
                    $this->field['pattern']   = '/^\\d{5}\-\d{3}$/';
                    $this->field['onkeyup']   = 'mascara(this, \'mcep\')';
                    $this->field['style']     = 'text-align: right';
                    $this->field['aria-describedby'] = 'ad_' . $this->nome;
                    $this->field['data-original-title'] = 'Digite o CEP no formato 99999-999';
                    $this->field['title']     = 'Digite o CEP no formato 99999-999';
                    break;
                case 'placaveiculo':
                    $this->field['type']      = 'text';
                    $this->field['pattern']   = '/^\\[A-Z]{3}\-\d[A-Z0-9]\d{2}$/';
                    $this->field['onkeyup']   = 'mascara(this, \'mplaca\')';
                    $this->field['class']     = "form-control $this->classep text-uppercase";
                    $this->field['style']     = 'text-align: left';
                    $this->field['data-original-title'] = 'Informe uma Placa Válida! AAA=0000 ou AAA-0A00';
                    $this->field['title']     = 'Informe uma Placa Válida! AAA=0000 ou AAA-0A00';
                    $this->field['aria-describedby'] = 'ig_' . $this->nome;
                    break;
                case 'file':
                    $this->field['type']      = 'file';
                    $this->field['data_folder'] = $this->pasta;
                    $this->field['data_img_name'] = $this->imgName;
                    $this->field['class']     = '';
                    if ($this->valor != '') {
                        $ico_arq = substr($this->valor, strrpos($this->valor, '.') + 1) . ".png";
                    } else {
                        $ico_arq = '';
                    }

                    $grouppos .= "<div id='view_img_" . $this->nome . "' class='show clearfix' 
                                style='width:200px; height:200px;' >";
                    $grouppos .= "<img id='img_" . $this->nome . "' src='" .
                        base_url('uploads/tipo_down/') . $ico_arq . "' for='" . $this->id .
                        "' class='img-thumbnail col-lg-12 col-xs-12'
                                style='width:200px; height:200px;' alt='' /></div>";
                    break;
                case 'textselect': //mostra o texto do select informado
                    $this->field['type']      = 'text';
                    $busca = "buscaTextselect(this,\"" . $this->nome . "\")";
                    $resp .= "<script>";
                    $resp .= "chang_ant = jQuery('#" . $this->place . "').attr('onchange');";
                    $resp .= "jQuery('#" . $this->place . "').attr('onchange','+chang_ant+'" . $busca . "');";
                    $resp .= "jQuery('#" . $this->place . "').trigger('change');";
                    $resp .= "</script>";
                    break;
                case 'textselectoculto': //guarda o texto do select informado
                    $this->field['type']      = 'hidden';
                    $busca = "buscaTextselect(this,\"" . $this->nome . "\")";
                    $resp .= "<script>";
                    $resp .= "chang_ant = jQuery('#" . $this->place . "').attr('onchange');";
                    $resp .= "jQuery('#" . $this->place . "').attr('onchange','+chang_ant+'" . $busca . "');";
                    $resp .= "jQuery('#" . $this->place . "').trigger('blur');";
                    $resp .= "</script>";
                    break;
                case 'calculo': //campo com resultado de cálculo
                    $this->field['placeholder'] = '';
                    $busca = "calcula(\"" . $this->id . "\",\"" . $this->place . "\", \"" . $this->pai . "\")";
                    $resp .= "<script>";
                    $resp .= "jQuery('#" . $this->valor . "').attr('onchange','" . $busca . "');";
                    $resp .= "jQuery('#" . $this->valor . "').trigger('change');";
                    $resp .= "</script>";
                    break;
            }
            $grouppos .= "<div id='dc-$this->id' class='div-caract badge bg-info-subtle'></div>";

            $this->propriedades();
            $campo = form_input($this->field);

            $resp .= $this->fmtDisplay($campo, $groupant, $grouppos);
        }
        return $resp;
    }

    /**
     * crDaterange
     * Campo de Período (range  de data)
     * @return string
     */
    public function crDaterange(): string
    {
        $this->acertaId();
        $resp = '';
        $this->field = array(
            'type'          => 'text',
            'name'          => $this->nome,
            'id'            => $this->id,
            'value'         => $this->valor,
            'size'          => $this->size,
            'maxlength'     => isset($this->maxLength) ? $this->maxLength : $this->size,
            'class'         => "daterange form-control $this->classep"
        );
        $this->propriedades();
        $campo = form_input($this->field);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crEditor
     * Campo Texto formatado com Editor de texto
     * @return string
     */
    public function crEditor(): string
    {
        $this->acertaId();
        $this->tipo = 'editor';
        $this->colunas = 100;
        $resp = '';
        $this->field = array(
            'type'          => 'textarea',
            'name'          => $this->nome,
            'id'            => $this->id,
            'value'         => $this->valor,
            'class'         => "$this->classep form-control",
        );
        $this->propriedades();
        $campo = form_textarea($this->field);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crTexto
     * Campo textarea normal
     * @return string
     */
    public function crTexto(): string
    {
        $this->acertaId();
        $resp = '';
        $grouppos = '';
        $this->field = array(
            'type'          => 'textarea',
            'name'          => $this->nome,
            'id'            => $this->id,
            'value'         => $this->valor,
            'cols'          => $this->colunas,
            'rows'          => $this->linhas,
            'maxlength'     => $this->maximo,
            'class'         => 'form-control',
        );
        $grouppos = "<div id='dc-$this->id' class='div-caract badge bg-info-subtle'></div>";
        $this->propriedades();
        $campo = form_textarea($this->field);

        $resp .= $this->fmtDisplay($campo, '', $grouppos);

        return $resp;
    }

    /**
     * crDual
     * Campo para seleção de ítens com duas listas
     * @return string
     */
    public function crDual(): string
    {
        $this->acertaId();
        $resp = '';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'data-enabled'  => $this->leitura,
            'data-alter'    => false,
            'data-label'    => $this->label,
            'data-valor'    => $this->selecionado,
            'data-size'     => $this->size,
            'placeholder'   => $this->place,
            'hint'          => $this->hint,
            'multiple'      => "multiple",
            'onchange'      => $this->funcChan,
            'class'         => ' form-control form-dual'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array('-1 disabled'  => 'Escolha ' . $this->place) + $this->opcoes;
        }
        $this->propriedades();
        $campo = form_dropdown($this->field, $this->opcoes, $this->selecionado);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crMultiple
     * Campo Select de seleção multipla
     * @return string
     */
    public function crMultiple(): string
    {
        $this->acertaId();
        $this->tipo = 'select';

        $resp = '';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $this->nome = $this->nome . '[]';
        $this->id = $this->id . '[]';
        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'multiple'      => 'multiple',
            'data-live-search' => "true",
            'class'         => 'selectpicker form-control form-select show-tick'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();

        // debug($this->opcoes);
        // debug($this->selecionado, true);
        $extras = 'data-actions-box="true"; data-size="5"; data-selected-text-format="count > 3"';

        $campo = form_multiselect($this->field, $this->opcoes, $this->selecionado, $extras);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crSelectIcone
     * Campo de Seleção de Lista com ícone ao lado da opção
     * @return string
     */
    public function crSelectIcone(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'class'         => ' form-control form-select selectpicker'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();
        $this->field['placeholder'] = str_replace('Informe', 'Selecione', $this->field['placeholder']);

        $campo = form_dropdown($this->field, $this->opcoes['texto'], $this->selecionado);

        if (isset($this->opcoes['icone']) && count($this->opcoes['icone']) > 0) {
            $campo .= "<script>";
            foreach ($this->opcoes['icone'] as $key => $value) {
                $opt = "#$this->id option[value=\"$key\"]";
                $campo .= "jQuery('$opt').attr('data-content','" . $this->opcoes['icone'][$key] . "');";
            }
            $campo .= "</script>";
        }

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crSelect
     * Campo de Seleção de Lista
     * @return string
     */
    public function crSelect(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'class'         => ' form-control form-select selectpicker'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();
        $this->field['placeholder'] = str_replace('Informe', 'Selecione', $this->field['placeholder']);

        $campo = form_dropdown($this->field, $this->opcoes, $this->selecionado);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crCorbst
     * Campo de Seleção de Cores do Bootstrap
     * @return string
     */
    public function crCorbst(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            'type'          => 'color',
            'list'          => 'bstColors',
            'name'          => $this->nome,
            'id'            => $this->id,
            'class'         => ' form-control form-select form-color selectpicker'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();
        $this->field['placeholder'] = str_replace('Informe', 'Selecione', $this->field['placeholder']);

        $cores["bg-primary"] = "";
        $cores['bg-secondary'] = "";
        $cores['bg-success'] = "";
        $cores['bg-danger'] = "";
        $cores['bg-warning'] = "";
        $cores['bg-info'] = "";
        $cores['bg-dark'] = "";
        $cores['bg-white'] = "";
        $campo = form_dropdown($this->field, $cores, $this->selecionado);

        $campo .= "<script>";
        $cor = fmtEtiquetaCorBst('bg-primary');
        $campo .= "jQuery(\"#$this->id option[value='bg-primary']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-secondary');
        $campo .= "jQuery(\"#$this->id option[value='bg-secondary']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-success');
        $campo .= "jQuery(\"#$this->id option[value='bg-success']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-danger');
        $campo .= "jQuery(\"#$this->id option[value='bg-danger']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-warning');
        $campo .= "jQuery(\"#$this->id option[value='bg-warning']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-info');
        $campo .= "jQuery(\"#$this->id option[value='bg-info']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-dark');
        $campo .= "jQuery(\"#$this->id option[value='bg-dark']\").attr('data-content',\"$cor\");";
        $cor = fmtEtiquetaCorBst('bg-white');
        $campo .= "jQuery(\"#$this->id option[value='bg-white']\").attr('data-content',\"$cor\");";
        $campo .= "</script>";

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crSelectCor
     * Campo de Seleção de Cores do Sistema
     * @return string
     */
    public function crSelectCor(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            // 'type'          => 'color',
            // 'list'          => 'sisColors',
            'name'          => $this->nome,
            'id'            => $this->id,
            'class'         => ' form-control form-select form-color '
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();
        $this->field['placeholder'] = str_replace('Informe', 'Selecione', $this->field['placeholder']);

        $campo = form_dropdown($this->field, $this->opcoes, $this->selecionado);
        // debug($campo);
        $campo .= "<script>";
        foreach ($this->opcoes as $key => $valor) {
            $pos1 = strpos($valor, "-");
            $rgb = substr($valor, 0, $pos1 - 1);
            $nome = substr($valor, $pos1 + 1);
            $cor = fmtEtiquetaCor($rgb, $nome);
            $campo .= "jQuery(\"#$this->id option[value='" . $key . "']\").attr('data-content',\"$cor\");";
        }
        $campo .= "</script>";

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crSelbusca
     * Select com busca de Opções por ajax
     * @return string
     */
    public function crSelbusca(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        $resp = '';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }
        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'data-busca'    => $this->urlbusca,
            'class'         => "$this->classep form-control form-select selbusca selectpicker"
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }

        $this->propriedades();
        $this->field['placeholder'] = str_replace('Informe', 'Selecione', $this->field['placeholder']);

        $campo = form_dropdown($this->field, $this->opcoes, $this->selecionado);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }


    /**
     * crDepende
     * Select de Opções dependentes de outro Select
     * @return string
     */
    public function crDepende(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'data-busca'    => $this->urlbusca,
            'data-pai'      => $this->pai,
            'onfocus'       => "testa_dep('" . $this->pai . "')",
            'class'         => ' form-control form-select dependente selectpicker'
        );

        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();

        if ($this->place != '') {
            $this->opcoes = array(''  => 'Escolha ' . $this->place) + $this->opcoes;
        }

        $campo = form_dropdown($this->field, $this->opcoes, $this->selecionado);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crDependeMultiplo
     * Select de Opções dependentes de outro Select, com multipla seleção
     * @return string
     */
    public function crDependeMultiplo(): string
    {
        $this->acertaId();
        $this->tipo = 'select';
        if (!isset($this->selecionado)) {
            $this->selecionado = $this->valor;
        }

        $resp = '';

        $this->field = array(
            'name'          => $this->nome . '[]',
            'id'            => $this->id . '[]',
            'data-busca'    => $this->urlbusca,
            'data-pai'      => $this->pai,
            'onfocus'       => "testa_dep('" . $this->pai . "')",
            'multiple'      => 'multiple',
            'data-live-search' => "true",
            'class'         => 'selectpicker form-control form-select dependente show-tick'
        );

        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        $this->propriedades();

        if ($this->place != '') {
            $this->opcoes = array(''  => 'Escolha ' . $this->place) + $this->opcoes;
        }

        $extras = 'size=3; data-actions-box="true"; data-size="5"; data-selected-text-format="count > 3"; style = "height:auto; overflow-y:auto; max-height:100px;"; ';

        // debug($this->selecionado);
        $campo = form_multiselect($this->field, $this->opcoes, $this->selecionado, $extras);

        $resp .= $this->fmtDisplay($campo);

        return $resp;
    }

    /**
     * crImagem
     * Campo para Upload de Imagem
     * @return string
     */
    public function crImagem(): string
    {
        $this->acertaId();
        $resp = '';
        $groupant = '';
        $grouppos = '';

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'data-folder'   => $this->pasta,
            'data-file-type'    => '.jpg',
            'accept'        => '.jpg',
            'style'         => "display:none",
            'class'         => ""
        );
        if ($this->leitura !== true) {
            $groupant .= "<label id='lbl_$this->id' class='btn btn-primary' 
                        style='white-space: normal;width:" . $this->size . "px; padding:0.8em;' 
                                for='" . $this->id . "' data-mdb-toggle='tooltip' data-mdb-placement='bottom' title='' 
                                data-bs-original-title='A imagem será redimensionada para " . $this->size . " X " .
                $this->largura . " proporcionalmente' aria-label='A imagem será redimensionada para 
                                $this->size X $this->largura proporcionalmente' >
                                <i class=\"fas fa-image\"></i> Clique para selecionar imagem de $this->label";
        }

        $groupant .= "<div id='view_img_" . $this->nome . "' class='show img-thumbnail ' >";
        $groupant .= "<img id='img_" . $this->nome . "' src='" . $this->valor . "'
                    for='" . $this->id . "' class='img-thumbnail sempadding' alt='' 
                    style='width:" . $this->size . "px;' />";
        $groupant .= "</div>";

        if ($this->leitura !== true) {
            $grouppos .= "</label>";
        }
        $this->propriedades();

        $campo = form_upload($this->field, $this->valor);

        $resp .= $this->fmtDisplay($campo, $groupant, $grouppos);
        return $resp;
    }

    /**
     * crImagem
     * Campo para Upload de Imagem
     * @return string
     */
    public function crArquivo(): string
    {
        $this->acertaId();
        $resp = '';
        $groupant = '';
        $grouppos = '';

        $this->field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'data-file-type'    => '.pdf,.docx',
            'accept'        => '.pdf,.docx',
            'style'         => "display:none",
            'class'         => ""
        );
        if ($this->leitura !== true) {
            $groupant .= "<label id='lbl_$this->id' class='btn btn-primary' 
                        style='white-space: normal;width:" . $this->size . "px; padding:0.8em;'>
                                <i class=\"fas fa-file\"></i> Clique para selecionar Arquivo de $this->label";
            $grouppos = 'arquivo';
        }

        // if ($this->leitura !== true) {
        //     $grouppos .= "</label>";
        // }
        $this->propriedades();

        $campo = form_upload($this->field, $this->valor);
        // debug($campo, true);
        // $campo['tipo'] = 'Arquivo';

        $resp .= $this->fmtDisplay($campo, $groupant, $grouppos);
        return $resp;
    }
}
