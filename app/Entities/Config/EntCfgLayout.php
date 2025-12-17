<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgLayout extends Entity
{
    public array $campos = [];

    protected $attributes = [
        'let_id'            => null,
        'let_nome'          => null,
        'let_altura'        => null,
        'let_largura'       => null,
        'let_colunas'       => null,
        'let_linhas'        => null,
        'let_marg_esquerda' => null,
        'let_marg_direita'  => null,
        'let_marg_superior' => null,
        'let_marg_inferior' => null,
        'let_distancia_h'   => null,
        'let_distancia_v'   => null,
        'let_ativo'         => 'A',
    ];

    public function __construct(?array $data = null, bool $show = false)
    {
      parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $let_id            = new MyCampo('cfg_layout_etiqueta', 'let_id');
        $let_id->valor     = (isset($dados['let_id'])) ? $dados['let_id'] : '';
        $ret['let_id']   = $let_id->crOculto();

        $nome           =  new MyCampo('cfg_layout_etiqueta', 'let_nome');
        $nome->valor    = (isset($dados['let_nome'])) ? $dados['let_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura  = $show;
        $ret['let_nome'] = $nome->crInput();

        $altura           =  new MyCampo('cfg_layout_etiqueta', 'let_altura');
        $altura->valor    = (isset($dados['let_altura'])) ? $dados['let_altura'] : '';
        $altura->obrigatorio = true;
        $altura->leitura  = $show;
        $altura->maxLength  = 3;
        $altura->largura  = 20;
        $ret['let_altura'] = $altura->crInput();

        $largura           =  new MyCampo('cfg_layout_etiqueta', 'let_largura');
        $largura->valor    = (isset($dados['let_largura'])) ? $dados['let_largura'] : '';
        $largura->obrigatorio = true;
        $largura->leitura  = $show;
        $largura->maxLength  = 3;
        $largura->largura  = 20;
        $ret['let_largura'] = $largura->crInput();

        $colunas          =  new MyCampo('cfg_layout_etiqueta', 'let_colunas');
        $colunas->valor    = (isset($dados['let_colunas'])) ? $dados['let_colunas'] : '';
        $colunas->obrigatorio = true;
        $colunas->leitura  = $show;
        $colunas->maxLength  = 2;
        $colunas->largura  = 20;
        $ret['let_colunas'] = $colunas->crInput();

        $let_linhas          =  new MyCampo('cfg_layout_etiqueta', 'let_linhas');
        $let_linhas->valor    = (isset($dados['let_linhas'])) ? $dados['let_linhas'] : '';
        $let_linhas->obrigatorio = true;
        $let_linhas->leitura  = $show;
        $let_linhas->maxLength  = 2;
        $let_linhas->largura  = 20;
        $ret['let_linhas'] = $let_linhas->crInput();

        $margemDireita          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_direita');
        $margemDireita->valor    = (isset($dados['let_marg_direita'])) ? $dados['let_marg_direita'] : '';
        $margemDireita->obrigatorio = true;
        $margemDireita->leitura  = $show;
        $margemDireita->maxLength  = 2;
        $margemDireita->largura  = 20;
        $ret['let_marg_direita'] = $margemDireita->crInput();

        $margemEsquerda          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_esquerda');
        $margemEsquerda->valor    = (isset($dados['let_marg_esquerda'])) ? $dados['let_marg_esquerda'] : '';
        $margemEsquerda->obrigatorio = true;
        $margemEsquerda->leitura  = $show;
        $margemEsquerda->maxLength  = 2;
        $margemEsquerda->largura  = 20;
        $ret['let_marg_esquerda'] = $margemEsquerda->crInput();

        $margemSuperior          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_superior');
        $margemSuperior->valor    = (isset($dados['let_marg_superior'])) ? $dados['let_marg_superior'] : '';
        $margemSuperior->obrigatorio = true;
        $margemSuperior->leitura  = $show;
        $margemSuperior->maxLength  = 2;
        $margemSuperior->largura  = 20;
        $ret['let_marg_superior'] = $margemSuperior->crInput();

        $margemInferior          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_inferior');
        $margemInferior->valor    = (isset($dados['let_marg_inferior'])) ? $dados['let_marg_inferior'] : '';
        $margemInferior->obrigatorio = true;
        $margemInferior->leitura  = $show;
        $margemInferior->maxLength =  2;
        $margemInferior->largura =  20;
        $ret['let_marg_inferior'] = $margemInferior->crInput();

        $let_distancia          =  new MyCampo('cfg_layout_etiqueta', 'let_distancia_h');
        $let_distancia->valor    = (isset($dados['let_distancia_h'])) ? $dados['let_distancia_h'] : '';
        $let_distancia->obrigatorio = true;
        $let_distancia->leitura  = $show;
        $let_distancia->maxLength  = 2;
        $let_distancia->largura  = 20;
        $ret['let_distancia_h'] = $let_distancia->crInput();

        $let_distancia          =  new MyCampo('cfg_layout_etiqueta', 'let_distancia_v');
        $let_distancia->valor    = (isset($dados['let_distancia_v'])) ? $dados['let_distancia_v'] : '';
        $let_distancia->obrigatorio = true;
        $let_distancia->leitura  = $show;
        $let_distancia->maxLength  = 2;
        $let_distancia->largura  = 20;
        $ret['let_distancia_v'] = $let_distancia->crInput();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_layout_etiqueta', 'let_ativo');
        $ativ->valor    = (isset($dados['let_ativo'])) ? $dados['let_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $ativ->leitura  = $show;
        $ret['let_ativo'] = $ativ->cr2opcoes();
        return $ret;
    }
}
