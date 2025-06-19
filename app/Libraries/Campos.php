<?php
namespace App\Libraries;

use App\Models\Config\ConfigDicDadosModel;
use CodeIgniter\Libraries;

/**
 * Campos_cust
 * Criação de Campo Customizado
 * @param string    $tabela     - informa a Tabela de Origem do Campo
 * @param string    $campo     -  informa o Campo de Origem
 * @param string    $objeto     - informa o tipo de campo a ser criado
 * @param boolean   $obrigatorio- verdadeiro se o campo for obrigatorio
 * @param boolean   $leitura    - verdadeiro se o campo for somente leitura
 * @param string    $tipo       - informa o tipo Customizado do campo
 * @param string    $nome       - Nome do Campo
 * @param string    $id         - Id do Campo
 * @param string    $label      - Rótulo do Campo
 * @param string    $place      - placeholder do Campo
 * @param string    $valor      - Valor inicial do Campo
 * @param int       $size       - Quantia de Caracteres do Campo
 * @param int       $max_size   - Quantia de Caracteres máximo do Campo
 * @param int       $tamanho    - Largura do campo
 * @param int       $colunas    - Colunas do Textarea
 * @param int       $linhas    - -Linhas do Textarea
 * @param string    $funcao_chan- Função que será executada na alteracao do campo
 * @param string    $funcao_blur- Função que será executada na saída do campo
 * @param string    $refer      - Campo de Referência
 * @param string    $classs     - Classe personalizada a ser aplicado no campo
 * @param string    $tipo_arquivo - Tipos de Arquivo suportados pelo campo imagem
 * @param string    $hint       - Hint (tooltip) a ser mostrado no campo
 * @param int       $minimo     - Valor mínimo para o campo do tipo number
 * @param int       $maximo     - Valor máximo para o campo do tipo number
 * @param int       $step       - Valor de incremento do campo do tipo number
 * @param array     $opcoes     - Array de elementos da lista
 * @param int       $selecionado - Item selecionado da lista
 * @param array     $selecmulti - Itens selecionados quando o campo é multiselect
 * @param string    $busca      - URL de busca do campo selbusca e do campo dependente
 * @param boolean   $repete     - verdadeiro, se o campo pode ser repetido
 * @param string    $tipo_form  - Formato de disposição dos campos no Formulário (inline, vertical)
 * @param string    $pasta      - Pasta do arquivo de imagem
 * @param string    $img_name   - Nome do arquivo de Imagem pré-carregada
 * @param string    $pai        - nome do campo pai, para um campo dependente
 * @param string    $i_cone     - icone do label
 * @param int       $ordem
 * @param array     $attrdata
 */
class Campos
{
    public $tabela;         // input, select, textarea, botao, file
    public $campo;         // input, select, textarea, botao, file
    public $objeto;         // input, select, textarea, botao, file
    public $obrigatorio     = false;
    public $leitura         = false;
    public $tipo;
    public $nome;
    public $id;
    public $label;
    public $place           = '';
    public $valor           = '';
    public $size            = 30;
    public $max_size        = 30;
    public $tamanho         = 50;
    public $linhas          = 3;
    public $colunas         = 100;
    public $funcao_chan     = '';
    public $funcao_blur     = '';
    public $refer           = '';
    public $classs          = '';
    public $tipo_arquivo    = '';
    public $hint            = '';
    public $minimo          = 0;
    public $maximo          = 50;
    public $step            = 1;
    public $opcoes          = [];
    public $selecionado     = "";
    public $selecmulti      = [];
    public $busca           = '';
    public $repete          = false;
    public $tipo_form       = 'vertical';
    public $pasta           = '';
    public $img_name        = '';
    public $i_cone          = '';
    public $novo_cadastro   = '';
    public $attrdata        = [];
    // DEPENDENTE
    public $pai             = '';
    public $infotop         = '';
    public $infobot         = '';
    public $ordem           = null;

    public $dicionario;
    public $tip_camp;

    public function __construct()
    {
        helper('form');
        $this->tip_camp['char']      = 'Caracter curto';
        $this->tip_camp['varchar']   = 'Caracter longo';
        $this->tip_camp['mediumtext'] = 'Texto';
        $this->tip_camp['text']      = 'Texto';
        $this->tip_camp['int']       = 'Inteiro';
        $this->tip_camp['decimal']   = 'Decimal';
        $this->tip_camp['float']     = 'Moeda';
        $this->tip_camp['date']      = 'Data';
        $this->tip_camp['timestamp'] = 'Data e Hora';
        $this->tip_camp['datetime']  = 'Data e Hora';
    }

    /**
     * Create campo
     * @return string
     */
    public function create(): string
    {
        // echo $this->id." Largura ".$this->tamanho."<br>";

        if (isset($this->tabela) && $this->tabela != '' && isset($this->campo) && $this->campo != '') {
            $this->dicionario = new ConfigDicDadosModel();
            $dados_campo = $this->dicionario->getDetalhesCampo($this->tabela, $this->campo);
            if (count($dados_campo)) {
                $dad_camp = $dados_campo[0];
                if ($this->id == '') {
                    $this->id = $dad_camp['COLUMN_NAME'];
                    $this->nome = $dad_camp['COLUMN_NAME'];
                }
                $this->label = $dad_camp['COLUMN_COMMENT'];
                if ($this->place == '') {
                    $this->place = 'Informe ' . $dad_camp['COLUMN_COMMENT'];
                }
                if ($this->hint == '') {
                    $this->hint = $dad_camp['COLUMN_COMMENT'];
                }
                if ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Data') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'date';
                    $this->size = 10;
                    $this->tamanho = 15;
                } elseif ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Data e Hora') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'datetime-local';
                    $this->size = 18;
                    $this->tamanho = 23;
                } elseif (
                    $this->tip_camp[$dad_camp['DATA_TYPE']] == 'Caracter curto'
                        || ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Caracter longo'
                        && $dad_camp['COLUMN_SIZE'] <= 100)
                ) {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'text';
                    if (intval($dad_camp['COLUMN_SIZE']) > 50) {
                        $this->size    = 40;
                        $this->max_size    = $dad_camp['COLUMN_SIZE'];
                    } else {
                        $this->size    = intval($dad_camp['COLUMN_SIZE']);
                    }
                    if (!isset($this->tamanho) || $this->tamanho == 50) {
                        $this->tamanho = $this->size + 5;
                    }
                } elseif (
                    $this->tip_camp[$dad_camp['DATA_TYPE']] == 'Caracter longo'
                    && $dad_camp['COLUMN_SIZE'] > 100
                ) {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'texto';
                    }
                    $this->linhas = 3;
                    $this->colunas = 80;
                    $this->maximo  = $dad_camp['COLUMN_SIZE'];
                } elseif ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Texto') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'texto';
                    }
                    $this->linhas = 3;
                    $this->colunas = 80;
                    $this->maximo = $dad_camp['COLUMN_SIZE'];
                    $this->classs = 'editor';
                } elseif ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Inteiro') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'number';
                    if (!isset($this->tamanho)) {
                        $this->size = 10;
                        $this->tamanho = 15;
                    }
                } elseif ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Decimal') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'quantia';
                    $this->size = $dad_camp['NUMERIC_SCALE'];

                    if (!isset($this->tamanho)) {
                        $this->tamanho = 15;
                    }
                } elseif ($this->tip_camp[$dad_camp['DATA_TYPE']] == 'Moeda') {
                    if (!isset($this->objeto)) {
                        $this->objeto = 'input';
                    }
                    $this->tipo = 'moeda';

                    if (!isset($this->tamanho)) {
                        $this->size = 10;
                        $this->tamanho = 15;
                    }
                }
                if (stripos($dad_camp['COLUMN_NAME'], 'cep')) {
                    $this->tipo = 'cep';
                }
            }
        }

        if ($this->objeto == 'botao') {
            $ret = $this->crBotao();
        } elseif ($this->objeto == 'oculto') {
            $ret = $this->cr_oculto();
        } elseif ($this->objeto == 'imagem') {
            $ret = "<div id='ig_$this->id' class='d-inline-block col-2'>";
            $ret .= $this->cr_imagem();
            $ret .= "</div>";
        } else {
            if ($this->tipo_form == 'inline') {
                if ($this->repete) {
                    $ret = "<div id='ig_$this->id' class='row d-inline-flex g-1 align-items-center'>";
                } else {
                    $ret = "<div id='ig_$this->id' class='row d-inline-flex g-1 align-items-center col-6 mb-2'>";
                }
            } else {
                $ret = "<div id='ig_$this->id' class='row d-flex g-1 align-items-center col-12'>";
            }
            if ($this->infotop != '') {
                $ret .= "<div class='text-info'><i class='fa-solid fa-bullhorn'></i> $this->infotop</div>";
            }
            if ($this->objeto == 'show') {
                $ret .= $this->cr_show();
            }
            if ($this->objeto == 'input') {
                $ret .= $this->cr_input();
            }

            if ($this->objeto == 'daterange') {
                $ret .= $this->cr_daterange();
            }

            if ($this->objeto == 'texto') {
                $ret .= $this->cr_texto();
            }

            if ($this->objeto == 'textoarea') {
                $ret .= $this->cr_textoarea();
            }

            if ($this->objeto == 'checkbox') {
                $ret .= $this->cr_checkbox();
            }
            if ($this->objeto == 'checkbutton') {
                $ret .= $this->cr_checkbutton();
            }

            if ($this->objeto == 'radio') {
                $ret .= $this->cr_radio();
            }
            if ($this->objeto == 'radiobutton') {
                $ret .= $this->cr_radiobutton();
            }

            if ($this->objeto == 'dual') {
                $ret .= $this->cr_dual();
            }

            if ($this->objeto == 'multiple') {
                $ret .= $this->cr_multiple();
            }

            if ($this->objeto == 'select') {
                $ret .= $this->cr_select();
            }
            if ($this->objeto == 'selbusca') {
                $ret .= $this->cr_selbusca();
            }
            if ($this->objeto == 'depende') {
                $ret .= $this->cr_depende();
            }
            // if ($this->objeto == 'imagem') {
                // $ret .= $this->cr_imagem();
            // }

            if ($this->objeto == 'text_show') {
                $ret .= $this->cr_text_show();
            }

            if ($this->tipo == 'AUTOCOMPLETE') {
                $this->hint = " data-toggle='tooltip' data-original-title='"
                . lang('ms_3caracteres')
                . "'";
            }
            if ($this->infobot != '') {
                $ret .= "<div class='text-warning fst-italic w-auto ms-3'><i class='fa-solid fa-triangle-exclamation'></i> $this->infobot</div>";
            }
            if (($this->objeto == 'input' && $this->tipo == 'text') || $this->objeto == 'textoarea' || $this->objeto == 'texto'){
                $ret .= "<div id='dc-$this->id' class='div-caract badge bg-info-subtle'></div>";
            }
            $ret .= "</div>";
        }
        return $ret;
    }

    public function crLabel($ident = '')
    {
        if ($ident == '') {
            $ident = $this->id;
        }
        $label = array(
            'class'         => 'form-label p-0 m-0',
        );
        $label_text = $this->label;
        if (($this->tipo == 'file' || $this->tipo == 'imagem') && $this->oculto === false) {
            $label['class'] = "btn btn-primary";
            $label['style'] = "white-space: normal;width:$this->sizepx;padding:0.8em;";
            $label_text .= "<i class='fa fa-file-archive-o'></i> Selecionar Arquivo";
        }

        if ($this->tipo_form == 'inline') {
            $ret = "<div class='col-12 d-block'>";
        } else {
            $ret = "<div class='col-12 col-lg-2 d-block'>";
        }
        // $ret = "<div class='col-12 col-lg-2 d-block'>";
    
        if ($this->repete) {
            // $ret = "<div class='p-0 me-3 mb-0 me-lg-0 d-block'>";
        }
        $ret .= form_label($label_text, $ident, $label);
        $ret .= "</div>";
        return $ret;
    }

    public function cr_show(): string
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $altura  = $this->tamanho.'rem';
        $largura = $this->size.'ch';

        $resp .= "<div class='border rounded bg-gradient-secondary input-group mb-lg-1 mb-2 overflow-auto' style='width: auto !important; height: $altura !important'>";
        $resp .= $this->valor;
        $resp .= "</div>";

        return $resp;
    }

    public function crBotao(): string
    {
        $field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'type'          => $this->tipo,
            'class'			=> "btn $this->classs ",
            'content'       => $this->i_cone.$this->label,
            'onclick'  		=> $this->funcao_chan,
            'title'         => $this->place,
            // 'data-index'    => $this->attrdata,
        );
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        if (isset($this->attrdata)) {
            foreach($this->attrdata as $key => $value) {
                $field[$key] = $value;
            }
        }
        $resp = form_button($field);
        return $resp;
    }

    public function cr_oculto(): string
    {
        $field = array(
            'type'  		=> 'hidden',
            'name'  		=> $this->nome,
            'id'    		=> $this->nome,
            'value' 		=> $this->valor,
        );

        $resp = form_input($field);
        return $resp;
    }

    public function cr_checkbox()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch form-control px-1' style='width: auto'>";
        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-selec' 	=> $this->selecionado, 
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'label' 		=> $this->label,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'class' 		=> "form-check-input ml-2 float-start $this->classs",
                // 'style'         => 'max-width: '.$largura
        );
        if ($this->valor == $this->selecionado) {
            $field['checked'] = true;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        // $resp .= "<label class='form-check-label' for='".$this->id."'>".$this->label.'</label>'.form_checkbox($field);
        $resp .= form_checkbox($field);
        $resp .= "</div>";
        return $resp;
    }

    public function cr_checkbutton()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch form-check-inline form-control px-1 sort overflow-auto overflow-x-hidden' style='width: auto; max-height: 70vh; '>";
        $cont = 0;
        foreach ( $this->opcoes as $valor => $label ) {
            $id = $this->id.'['.$cont.']';
            // debug($label, false);
            $field = array(
                    'name'  		=> $this->nome,
                    'id'    		=> $id,
                    'value' 		=> $valor,
                    'autocomplete'  => 'off',
                    'class' 		=> "btn-check ui-state-default position-fixed"
            );
            $checked = false;
            if (in_array($valor,$this->selecionado)) {
                $checked = true;
            }
            if ($this->leitura === true) {
                $field['readonly'] = "readonly";
                // $field['disabled'] = "disabled";
                $field['onfocus'] = "this.blur()";
                $field['tabindex'] = -1;
            }
            $lab = "<label class='btn $this->classs fs-4' for='$id'> $label </label>";
            $resp .= "<div class='d-inline-flex me-2 col-12'>";
            $resp .= form_checkbox($field, '', $checked).$lab;
            $resp .= '</div>';
            $cont++;
        }
        $resp .= "</div>";
        return $resp;
    }

    public function cr_radio()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch form-check-inline  form-control px-1' style='width: auto'>";
        $cont = 0;
        foreach ( $this->opcoes as $valor => $label ) {
            $id = $this->id.'['.$cont.']';
            $field = array(
                    'name'  		=> $this->nome,
                    'id'    		=> $id,
                    'value' 		=> $valor,
                    'data-enabled' 	=> $this->leitura,
                    'data-selec' 	=> $this->selecionado, 
                    'data-alter' 	=> false,
                    'data-salva' 	=> true,
                    // 'data-label' 	=> $valor,
                    // 'label' 		=> $valor,
                    'hint'  		=> $this->hint,
                    'onchange' 		=> $this->funcao_chan,
                    'class' 		=> "form-check-input ml-2 $this->classs"
            );
            if ($valor == $this->selecionado) {
                $field['checked'] = true;
            }
            if ($this->obrigatorio === true) {
                $field['required'] = true;
                // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
            } else {
                $field['required'] = false;
            }
            if ($this->leitura === true) {
                $field['readonly'] = "readonly";
                $field['disabled'] = "disabled";
                $field['onfocus'] = "this.blur()";
                $field['tabindex'] = -1;
            }
            $lab = "<label class='form-check-label px-1 m-auto mx-0' for='$id'> $label </label>";
            $resp .= "<div class='d-inline-flex' style='width: auto'>";
            $resp .= form_radio($field).$lab;
            $resp .= '</div>';
            $cont++;
        }
        $resp .= "</div>";
        return $resp;
    }

    public function cr_radiobutton()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch form-check-inline  form-control px-1 w-auto'>";
        $cont = 0;
        foreach ( $this->opcoes as $valor => $label ) {
            $id = $this->id.'['.$cont.']';
            $field = array(
                    'name'  		=> $this->nome,
                    'id'    		=> $id,
                    'value' 		=> $valor,
                    'autocomplete'  => 'off',
                    'class' 		=> "btn-check position-fixed"
            );
            if ($valor == $this->selecionado) {
                $field['checked'] = true;
            }
            if ($this->leitura === true) {
                $field['readonly'] = "readonly";
                // $field['disabled'] = "disabled";
                $field['onfocus'] = "this.blur()";
                $field['tabindex'] = -1;
            }
            $lab = "<label class='btn $this->classs fs-6' for='$id'> $label </label>";
            $resp .= "<div class='d-inline-flex me-2'>";
            $resp .= form_radio($field).$lab;
            $resp .= '</div>';
            $cont++;
        }
        $resp .= "</div>";
        return $resp;
    }
    
    public function cr_input()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $maximo  = $this->maximo.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $hasvalid = '';
        if ($this->tipo == 'cpf') {
            $hasvalid = ' has-validation';
        }
        $resp .= "<div class='input-group mb-2 p-0 $hasvalid' style='width: auto !important; min-width: $largura !important; max-width: $maximo !important;'>";
        // $resp .= "<div class='input-group mb-lg-1 mb-2 $hasvalid' style='width: auto !important; max-width: $largura !important;'>";
        $field = array(
            'type'  		=> $this->tipo,
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'value' 		=> $this->valor,
            'autocomplete'  => 'off',
            'size'			=> $this->size,
            'maxlength' 	=> isset($this->max_size)?$this->max_size:$this->size,
            'hint'  		=> $this->hint,
            'onblur' 		=> $this->funcao_blur,
            'class' 		=> "form-control $this->classs",
            'data-inicial' 	=> $this->valor,
            'data-enabled' 	=> $this->leitura,
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'data-index'    => $this->ordem,
            'data-nome'    => $this->campo,
            'placeholder' 	=> $this->place,
        );
        if ($this->tipo == 'senha') {
            $field['type'] = "password";
        }
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true && $this->tipo != 'login' && $this->tipo != 'password' && $this->tipo != 'senha') {
            $field['required'] = true;
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "top";
            $field['title'] = $this->hint;
        }

        switch ($this->tipo) {
            case 'icone':
                $field['type'] = 'text';
                $field['class'] = "form-control $this->classs icone";
                $field['aria-describedby'] = 'ig_'.$this->nome;
                $resp .= "<span class='input-group-text input-group-addon'><i class='".$this->valor."'></i></span>";
                break;
            case 'sonumero':
                $field['type'] = 'number';
                $field['onkeyup'] = 'mascara(this, \'mnum\')';
                $field['onchange'] = 'mascara(this, \'mnum\')';
                $field['pattern'] = '/[\d,.?!'.$this->size.'}$/';
                $field['style'] 			= 'text-align: right';
                $field['aria-describedby'] = 'ig_'.$this->nome;
                break;
            case 'quantia':
                $field['type'] = 'text';
                $field['onkeyup'] = 'mascara(this, \'mquantia\')';
                $field['onblur'] = $this->funcao_blur;
                $field['value'] = floatToQuantia($this->valor, $this->size);
                $field['pattern'] = "/^([\d]*\,?[\d]{0,".$this->size."})$/";
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ig_'.$this->nome;
                break;
            case 'inteiro':
                $field['type']              = 'number';
                $field['onkeyup']           = 'mascara(this, \'mnum\')';
                $field['onchange']          = 'mascara(this, \'mnum\')';
                $field['pattern']           = '/\\d{1,'.$this->size.'}/';
                $field['style'] 			= 'text-align: right';
                $field['aria-describedby']  = 'ig_'.$this->nome;
                break;
            case 'number':
                $field['type'] = 'number';
                $field['dir'] = 'rtl';
                $field['min'] = $this->minimo;
                $field['max'] = $this->maximo;
                $field['step'] = $this->step;
                $field['onfocus'] = 'entrar_moeda(this)';
                $field['style'] = 'text-align: right';
                break;
            case 'moeda':
                $field['type'] = 'text';
                $field['onkeyup'] = 'mascara(this, \'mvalor\')';
                $field['pattern'] = "/^([\$]?)([0-9]*\,?[0-9]{0,2})$/";
                $field['onchange'] = $this->funcao_chan;
                $field['data-origin'] = floatToMoeda($this->valor);
                $field['value'] = floatToMoeda($this->valor);
                $field['data-person'] = '0';
                $field['onblur']    = 'sair_moeda(this);'.$this->funcao_blur;
                $field['onfocus'] = 'entrar_moeda(this)';
                $field['class'] = $field['class'].' moeda has-validation';
                $field['style'] = 'text-align: right';
                break;
            case 'date':
            case 'datetime-local':
                break;
            case 'senha':
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='bi bi-key'></i></span>";
                break;
            case 'password':
                $fieldpassoculto =array(
                    'type'  		=> 'password',
                    'name'  		=> 'enganagoogle',
                    'value' 		=> '',
                    'style'         => "opacity: 0;position: absolute;"
                );
                $resp .= form_input($fieldpassoculto);
                $field['class'] = "form-control $this->classs password";
                $field['onchange']           = $this->funcao_chan;
                $field['onblur']             = 'validaSenha(this);oculta_passinfo();'.$this->funcao_blur;
                // $field['pattern'] = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[$*&@#])(?:([0-9a-zA-Z$*&@#])(?!\1)) {6,8}$/';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='bi bi-key'></i></span>";
                break;
            case 'email':
                $field['type'] = 'email';
                $field['pattern'] = '/^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um E-mail válido!';
                $field['title'] = 'Informe um E-mail válido!';
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='far fa-envelope-open' ></i></span>";
                break;
            case 'site':
            case 'url':
                $field['type'] = 'url';
                $field['pattern'] = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe uma url válida!';
                $field['title'] = 'Informe uma url válida!';
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='far fa-link'></i></span>";
                break;
            case 'telefone':
            case 'fone':
                $field['type'] = 'tel';
                $field['pattern'] = '/^\(\d{2}\) \d{4}\-\d{4}$/';
                $field['onkeyup'] = 'mascara(this, \'mtel\')';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um Telefone válido! (99) 9999-9999';
                $field['title'] = 'Informe um Telefone válido! (99) 9999-9999';
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'></span><i class='fas fa-phone' ></i></span>";
                break;
            case 'celular':
            case 'celul':
            case 'whatsapp':
            case 'whats':
                $field['type'] = 'tel';
                $field['pattern'] = '/^\(\d{2}\) \d{4,5}\-\d{4}$/';
                $field['onkeyup'] = 'mascara(this, \'mcel2\')';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um Celular válido! (99) 99999-9999';
                $field['title'] = 'Informe um Celular válido! (99) 99999-9999';
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='fa fa-mobile-alt'></i></span>";
                break;
            case 'cnpj':
                $field['type'] = 'text';
                $field['pattern'] = '/^\\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/';
                $field['onkeyup'] = 'mascara(this, \'mcnpj\')';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CNPJ no formato 99.999.999/9999-99';
                $field['title'] = 'Digite o CNPJ no formato 99.999.999/9999-99';
                break;
            case 'cpf':
                $field['type'] = 'text';
                $field['pattern'] = '/^\\d{3}\.\d{3}\.\d{3}\-\d{2}/';
                $field['onkeyup'] = 'mascara(this, \'mcpf\')';
                $field['onblur'] = $field['onblur'].';ValidaCPF(this)';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CPF no formato 999.999.999-99';
                $field['title'] = 'Digite o CPF no formato 999.999.999-99';
                break;
            case 'cep':
                $field['type'] = 'text';
                $field['pattern'] = '/^\\d{5}\-\d{3}$/';
                $field['onkeyup'] = 'mascara(this, \'mcep\')';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CEP no formato 99999-999';
                $field['title'] = 'Digite o CEP no formato 99999-999';
                break;
            case 'placaveiculo':
                $field['type'] = 'text';
                $field['pattern'] = '/^\\[A-Z]{3}\-\d[A-Z0-9]\d{2}$/';
                $field['onkeyup'] = 'mascara(this, \'mplaca\')';
                $field['class'] = "form-control $this->classs text-uppercase";
                $field['style'] = 'text-align: left';
                $field['data-original-title'] = 'Informe uma Placa Válida! AAA=0000 ou AAA-0A00';
                $field['title'] = 'Informe uma Placa Válida! AAA=0000 ou AAA-0A00';
                $field['aria-describedby'] = 'ig_'.$this->nome;
                break;
            case 'file':
                $field['type'] = 'file';
                $field['data_folder'] = $this->pasta;
                $field['data_img_name'] = $this->img_name;
                $field['class'] = '';
                if ($this->valor != '') {
                    $ico_arq = substr($this->valor, strrpos($this->valor, '.') + 1).".png";
                } else {
                    $ico_arq = '';
                }
                
                $resp .= "<div id='view_img_".$this->nome."' class='show clearfix' style='width:200px; height:200px;' >";
                $resp .= "<img id='img_".$this->nome."' src='".base_url('uploads/tipo_down/').$ico_arq."' for='".$this->id."' class='img-thumbnail col-lg-12 col-xs-12' style='width:200px; height:200px;' alt='' /></div>";
                break;
            case 'imagem':
                $field['type'] = 'file';
                $field['data-folder'] = $this->pasta;
                $field['data-img-name'] = $this->img_name;
                $field['data-file-type'] = $this->tipo_arquivo;
                $field['accept'] = 'image/*';
                $field['capture'] = 'capture';
                $field['class'] = '';
                $resp .= "<div>A imagem será redimensionada para ".$this->size."X".$this->tamanho." proporcionalmente</div>";
                $resp .= "<div id='view_img_".$this->nome."' class='show img-thumbnail sempadding' style='width:".$this->size."px; height:".$this->tamanho."px;' >";
                $resp .= "<img id='img_".$this->nome."' src='".$this->valor."' for='".$this->id."' class='img-thumbnail sempadding' alt=''  />";
                $resp .= "</div><div class='clear'></div>";
                break;
            case 'textselect': //pega o texto do select informado
                $field['type'] = 'text';
                $busca = "busca_textselect(this,\"".$this->nome."\")";
                $resp .= "<script>";
                $resp .= "jQuery('#".$this->place."').attr('onchange','".$busca."');";
                $resp .= "jQuery('#".$this->place."').trigger('change');";
                $resp .= "</script>";
                break; 
            case 'textinput': //pega o texto do select informado
                $field['type'] = 'hidden';
                $busca = "busca_textselect(this,\"".$this->nome."\")";
                $resp .= "<script>";
                $resp .= "chang_ant = jQuery('#".$this->place."').attr('onchange');";
                $resp .= "jQuery('#".$this->place."').attr('onchange','+chang_ant+'".$busca."');";
                $resp .= "jQuery('#".$this->place."').trigger('blur');";
                $resp .= "</script>";
                break;
            case 'calculo': //pega o texto do select informado
                $field['placeholder'] = '';
                $busca = "calcula(\"".$this->id."\",\"".$this->place."\",\"".$this->refer."\")";
                $resp .= "<script>";
                $resp .= "jQuery('#".$this->valor."').attr('onchange','".$busca."');";
                $resp .= "jQuery('#".$this->valor."').trigger('change');";
                $resp .= "</script>";
                break;
        }
        $resp .= form_input($field);
        if ($this->tipo == 'password' || $this->tipo == 'senha') {
            $resp .= "<span name='show_password' class='input-group-text input-group-append bi bi-eye-slash-fill show_password' id='ada_$this->nome' data-field='$this->nome'></span>";
        }
        if ($this->obrigatorio) {
            $resp .= "<div class='invalid-feedback'>";
            $resp .= $this->label.' é obrigatório';
            $resp .= "</div>";            
        }
        if ($this->tipo == 'cpf') {
            $resp .= "<div class='invalid-feedback'>";
            $resp .= "CPF Inválido, verifique!";
            $resp .= "</div>";
        }
        if ($this->tipo == 'password') {
            $resp .= "<div id='pass-info' class='invalid-feedback  border border-1 bg-white position-content p-2 text-start' style='z-index:200;top:2rem'></div>";
        }

        $resp .= "</div>";
        
        // if ($this->tipo == 'icone') {
        //     $resp .= "</div>";
        // }
        return $resp;
    }

    public function cr_daterange()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: auto !important'>";
        $field = array(
            'type'  		=> 'text',
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'value' 		=> $this->valor,
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'size'			=> $this->size,
            'maxlength' 	=> isset($this->max_size)?$this->max_size:$this->size,
            'class' 		=> "daterange form-control $this->classs"
        );
        if ($this->obrigatorio === true) {
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        $resp .= form_input($field);
        $resp .= "</div>";
        
        return $resp;
    }

    public function cr_texto()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->colunas.'ch !important';
        if (session()->ismobile) {
            $largura = '';
        }
        if ($this->classs == 'editor') {
            $resp .= "<div class='input-group mb-lg-1 mb-2'>";
        } else {
            $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: auto'>";
        }
        $field = array(
                'type'  		=> 'textarea',
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'placeholder' 	=> $this->place,
                'cols'			=> $this->colunas,
                'rows' 			=> $this->linhas,
                'maxlength'     => $this->maximo,
                'hint'  		=> $this->hint,
                'onblur' 		=> $this->funcao_blur,
                'style'         => 'white-space: normal;',
                'class' 		=> "$this->classs form-control",
        );
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }

        $resp .= form_textarea($field);
        $resp .= "</div>";
        
        return $resp;
    }
    public function cr_textoarea()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: auto !important'>";
        $field = array(
                'type'  		=> 'textarea',
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'placeholder' 	=> $this->place,
                'cols'			=> $this->colunas,
                'rows' 			=> $this->linhas,
                'maxlength'     => $this->maximo,
                'hint'  		=> $this->hint,
                'onblur' 		=> $this->funcao_blur,
                'style'         => 'white-space: normal;width: auto;',
                'class' 		=> 'form-control',
        );
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }

        $resp .= form_textarea($field);
        if ($this->obrigatorio) {
            $resp .= "<div class='invalid-feedback'>";
            $resp .= $this->label.' é obrigatório';
            $resp .= "</div>";            
        }
        $resp .= "</div>";
        
        return $resp;
    }

    public function cr_dual()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: auto !important'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'data-size'	    => $this->size,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'multiple'  	=> "multiple",
                'onchange' 		=> $this->funcao_chan,
                'class' 		=> ' form-control form-dual'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array('-1 disabled'  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
      
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado);
        if ($this->obrigatorio) {
            $resp .= "<div class='invalid-feedback'>";
            $resp .= $this->label.' é obrigatório';
            $resp .= "</div>";            
        }

        $resp .= "</div>";

        return $resp;
    }

    public function cr_multiple()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $larguramax = ($this->tamanho+5).'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $larguramax !important; max-width: $largura !important; max-width: $larguramax !important;'>";

        $field = array(
                'name'  		=> $this->nome.'[]',
                'id'    		=> $this->id.'[]',
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'multiple'      => '',
                'data-live-search' => "true",
                'class' 		=> 'selectpicker form-control form-select show-tick'
        );
        if ($this->ordem != null) {
            $field['data-index'] = $this->ordem; 
        }
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        // if ($this->place != '') {
        //     $this->opcoes = array('""'  => 'Escolha '.$this->place)+$this->opcoes;
        // }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
      
        $resp .= form_multiselect($field, $this->opcoes, $this->selecmulti);
        if ($this->obrigatorio) {
            $resp .= "<div class='invalid-feedback'>";
            $resp .= $this->label.' é obrigatório';
            $resp .= "</div>";            
        }

        $resp .= "</div>";

        return $resp;
    }

    public function cr_select()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $larguramax = ($this->tamanho+5).'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $larguramax !important; max-width: $largura !important; max-width: $larguramax !important;'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'data-live-search' => 'true',
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'onblur'        => $this->funcao_blur,
                'data-live-search' => "true",
                'class' 		=> ' form-control form-select selectpicker'
        );
        if ($this->ordem != null) {
            $field['data-index'] = $this->ordem; 
        }
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        $obriga = '';
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            $obriga .= "<div id='".$this->id."-fival' class='invalid-feedback'>";
            $obriga .= $this->label.' é obrigatório';
            $obriga .= "</div>";            
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado);
        $resp .= $obriga;

        $resp .= "</div>";
        if ($this->novo_cadastro != '' && $this->leitura === false) {
            $field_btn = array(
                'name'          => 'bt_ad_'.$this->nome,
                'id'            => 'bt_ad_'.$this->id,
                'style'         => 'width:2.5rem',
                'type'          => 'button',
                'hint'          => "Novo Cadastro",
                'class'			=> "btn btn-outline-secondary ",
                'content'       => "<i class='fa-solid fa-wand-sparkles fa-flip-horizontal'></i> ",
                'onclick'  		=> "openModal('".$this->novo_cadastro."')"
            );
            $resp .= form_button($field_btn);
        }

        return $resp;
    }

    public function cr_selbusca()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $larguramax = ($this->tamanho+5).'ch';
        // echo "Largura ".$this->tamanho;
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $larguramax !important; max-width: $largura !important; max-width: $larguramax !important;'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'data-live-search' => 'true',
                'data-busca'    => $this->busca,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'onblur'        => $this->funcao_blur,
                'class' 		=> ' form-control form-select selbusca selectpicker'
        );
        if ($this->ordem != null) {
            $field['data-index'] = $this->ordem; 
        }
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        $obriga = '';
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            $obriga .= "<div id='".$this->id."-fival' class='invalid-feedback'>";
            $obriga .= $this->label.' é obrigatório';
            $obriga .= "</div>";            
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        // debug($this->selecionado);
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado);
        $resp .= $obriga;

        $resp .= "</div>";
        if ($this->novo_cadastro != '' && $this->leitura === false) {
            $field_btn = array(
                'name'          => 'bt_ad_'.$this->nome,
                'id'            => 'bt_ad_'.$this->id,
                'style'         => 'width:2.5rem',
                'type'          => 'button',
                'hint'          => "Novo Cadastro",
                'class'			=> "btn btn-outline-secondary ",
                'content'       => "<i class='fa-solid fa-wand-sparkles fa-flip-horizontal'></i> ",
                'onclick'  		=> "openModal('".$this->novo_cadastro."')"
            );
            $resp .= form_button($field_btn);
        }

        return $resp;
    }

    // public function cr_selbusca()
    // {
    //     $resp = '';
    //     if ($this->label != '') {
    //         $resp .= $this->crlabel('bus_'.$this->id);
    //     }
    //     $largura = $this->tamanho.'ch';
    //     if (session()->ismobile) {
    //         $largura = '';
    //     }
    //     // debug($this->valor, false);
    //     $resp .= "<div class='mb-lg-1 mb-2' style='width: $largura !important'>";
    //     // CRIA O CAMPO OCULTO QUE ARMAZENA O REGISTRO SELECIONADO
    //     $field = array(
    //             'type'  		=> 'hidden',
    //             'name'  		=> $this->nome,
    //             'id'    		=> $this->nome,
    //             'value' 		=> $this->valor,
    //             'onblur'        => $this->funcao_blur,
    //             'onchange' 		=> $this->funcao_chan,
    //     );
    //     $resp .= form_input($field);

    //     // CRIA O DROPDOWN DE BUSCA
    //     $resp .= "<div id='db_$this->id' class='dropdown '>\n";
    //     $resp .= "<div class='input-group' >\n";
    //     $field = array(
    //                     'type'  		=> 'text',
    //                     'name'  		=> 'bus_'.$this->nome,
    //                     'id'    		=> 'bus_'.$this->id,
    //                     'size'			=> $this->size,
    //                     'maxlength' 	=> $this->tamanho,
    //                     'value'         => $this->selecionado,
    //                     'autocomplete'  => 'off',
    //                     'placeholder' 	=> 'Digite 3 letras para buscar...',
    //                     'onKeyUp' 	    => "buscar('$this->busca', this,'$this->nome');",
    //                     'class' 		=> "form-control dropdown-toggle",
    //                     'data-bs-toggle'=> "dropdown",
    //                     'aria-expanded' => "false",
    //                 );
    //     if ($this->leitura === true) {
    //         $field['class'] = "form-control";
    //         $field['data-bs-toggle'] = "";
    //         $field['readonly'] = "readonly";
    //         $field['disabled'] = "disabled";
    //         $field['onfocus'] = "this.blur()";
    //         $field['tabindex'] = -1;
    //     }
    //     $obriga = '';
    //     if ($this->obrigatorio === true) {
    //         $field['required'] = true;
    //         $obriga .= "<div id='".$this->id."-fival' class='invalid-feedback'>";
    //         $obriga .= $this->label.' é obrigatório';
    //         $obriga .= "</div>";            
    //         // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
    //     }
    //     $resp .= form_input($field);
    //     if ($this->leitura === false) {
    //         $resp .= "<span class='input-group-text'><i class='fa fa-search'></i></span>\n";
    //         $resp .= "<ul id='dd_$this->nome' class='dropdown-menu w-100 bg-gray-padrao opacity-100 border border-dark border-1' aria-labelledby='bus_".$this->nome."' data-popper-placement='bottom-start' data-bs-auto-close='true' style='margin: 0px;transform: translate(0rem, 10rem); max-height: 10rem;overflow-y: auto;'>\n";
    //         $resp .= "    <li><h6 class='dropdown-header disabled'>Digite 3 letras para buscar...</h6></li>\n";
    //         $resp .= "</ul>\n";
    //     }
    //     $resp .= $obriga;
    //     $resp .= "</div>\n";
    //     $resp .= "</div>\n";

    //     $resp .= "</div>\n";
    //     if ($this->novo_cadastro != '' && $this->leitura === false) {
    //         $field_btn = array(
    //             'name'          => 'bt_ad_'.$this->nome,
    //             'id'            => 'bt_ad_'.$this->id,
    //             'style'         => 'width:2.5rem',
    //             'type'          => 'button',
    //             'hint'          => "Novo Cadastro",
    //             'class'			=> "btn btn-outline-secondary ",
    //             'content'       => "<i class='fa-solid fa-wand-sparkles fa-flip-horizontal'></i> ",
    //             'onclick'  		=> "openModal('".$this->novo_cadastro."')"
    //         );
    //         $resp .= form_button($field_btn);
    //     }
    //     return $resp;
    // }

    public function cr_depende()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';
        $larguramax = ($this->tamanho+5).'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $larguramax !important; max-width: $largura !important; max-width: $larguramax !important;'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-live-search' => 'true',
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->valor,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'onblur'        => $this->funcao_blur,
                'data-busca'    => $this->busca,
                'data-pai'      => $this->pai,
                'onfocus' 		=> "testa_dep('".$this->pai."')",
                'class' 		=> ' form-control form-select dependente selectpicker'
        );

        if ($this->ordem != null) {
            $field['data-index'] = $this->ordem; 
        }
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array(''  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        $obriga = '';
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            $obriga .= "<div id='".$this->id."-fival' class='invalid-feedback'>";
            $obriga .= $this->label.' é obrigatório';
            $obriga .= "</div>";            
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado).$obriga;
        $resp .= "</div>";
        if ($this->novo_cadastro != '' && $this->leitura === false) {
            $field_btn = array(
                'name'          => 'bt_ad_'.$this->nome,
                'id'            => 'bt_ad_'.$this->id,
                'style'         => 'width:2.5rem',
                'type'          => 'button',
                'hint'          => "Novo Cadastro",
                'class'			=> "btn btn-outline-secondary ",
                'content'       => "<i class='fa-solid fa-wand-sparkles fa-flip-horizontal'></i> ",
                'onclick'  		=> "openModal('".$this->novo_cadastro."')"
            );
            $resp .= form_button($field_btn);
        }

        // $busca = "busca_dependente(this,\"".$this->nome."\",\"".$this->busca."\",\"".$this->valor."\")";
                    
        // $resp .= "\n<script>\n";
        // $resp .= "_chan_ant = jQuery(\"#".$this->pai."\").attr(\"onchange\"); ";
        // $resp .= "\n</script>\n";
        // $resp .= "\n<script>\n";
        // $resp .= "jQuery('#".$this->pai."').attr('onchange',_chan_ant+'; ".$busca."');";
        // $resp .= "jQuery('#".$this->pai."').trigger('change');\n";
        // $resp .= "</script>\n";


        return $resp;
    }

    public function cr_imagem()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->crlabel();
        }
        $largura = $this->tamanho.'ch';        
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2'>";

        $field = array(
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'data-folder' 	=> $this->pasta,
            'data-file-type' 	=> '.jpg',
            'accept' 	    => '.jpg',
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'data-valor'	=> $this->valor,
            'placeholder' 	=> $this->place,
            'onchange'      => $this->funcao_chan,
            'style'         => "display:none",
            'class'         => ""
        );
        if ($this->leitura !== true) {
            $resp .= "<label id='lbl_$this->id' class='btn btn-primary' style='white-space: normal;width:".$this->size."px; padding:0.8em;' for='".$this->id."' data-mdb-toggle='tooltip' data-mdb-placement='bottom' title='' data-bs-original-title='A imagem será redimensionada para ".$this->size." X ".$this->tamanho." proporcionalmente' aria-label='A imagem será redimensionada para $this->size X $this->tamanho proporcionalmente' ><i class=\"fas fa-image\"></i> Clique para selecionar imagem de $this->label";
        }

        $resp .= "<div id='view_img_".$this->nome."' class='show img-thumbnail ' >";
        $resp .= "<img id='img_".$this->nome."' src='".$this->valor."' for='".$this->id."' class='img-thumbnail sempadding' alt='' style='width:".$this->size."px;' />";
        $resp .= "</div>";

										  
																																										 
		 
        $resp .= form_upload($field, $this->valor);
        if ($this->leitura !== true) {
            $resp .= "</label>";
        }

        $resp .= "</div>";
        return $resp;
    }

    /**
     * cr_text_show
     *  só imprime o valor
     * @return string
     */
    public function cr_text_show(): string 
    {
        $resp = $this->valor;
        return $resp;
    }
}
?>