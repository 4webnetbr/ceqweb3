<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigLayoutEtiqModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_layout_etiqueta';
    protected $view             = 'cfg_layout_etiqueta';
    protected $primaryKey       = 'let_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'let_id',
        'let_nome',
        'let_altura',
        'let_largura',
        'let_colunas',
        'let_linhas',
        'let_marg_esquerda',
        'let_marg_direita',
        'let_marg_superior',
        'let_marg_inferior',
        'let_distancia_h',
        'let_distancia_v',
        'let_ativo',


    ];


    protected $validationRules = [
        'let_nome'          => 'required|min_length[5]|max_length[50]|isUniqueValue[default.cfg_layout_etiqueta.let_nome, let_id]',
        'let_altura'        => 'required|integer|max_length[4]|min_length[1]',
        'let_largura'       => 'required|integer|max_length[4]|min_length[1]',
        'let_colunas'       => 'required|integer|max_length[2]|min_length[1]',
        'let_marg_esquerda' => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_direita'  => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_superior' => 'required|integer|max_length[4]|min_length[1]',
        'let_marg_inferior' => 'required|integer|max_length[4]|min_length[1]',
        'let_distancia_h'   => 'required|integer|max_length[4]|min_length[1]',
        'let_distancia_v'   => 'required|integer|max_length[4]|min_length[1]'
    ];

    protected $validationMessages = [
        'let_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
            'max_length' => 'O campo deve ter no máximo 50 Caracteres. ',
            'isUniqueValue' =>  '8'
        ],
        'let_altura' => [
            'required' => 'Altura é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],
        'let_largura' => [
            'required' => 'Largura é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_marg_esquerda' => [
            'required' => 'Margem Esquerda é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_marg_direita' => [
            'required' => 'Margem Direita é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_marg_superior' => [
            'required' => 'Margem Superior é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_marg_inferior' => [
            'required' => 'Margem inferior é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_distancia_h' => [
            'required' => 'Distância horizontal é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_distancia_v' => [
            'required' => 'Distância vertical é Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 4 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ],

        'let_colunas' => [
            'required' => 'Colunas Obrigatório',
            'decimal' => 'Um valor do tipo Inteiro é requerido.',
            'max_length' => 'No máximo 2 Caracteres',
            'min_length' => 'No Mínimo 1 Caracter'
        ]
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

    public function getListaLayouts($let_id = false)
    {
        $this->builder()->select('*');
        if ($let_id) {
            $this->builder()->where('let_id', $let_id);
        }
        $this->builder()->orderBy('let_ativo, let_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getLayEtiqueta($let_id = false)
    {
        $this->builder()->select('*');
        if ($let_id) {
            $this->builder()->where('let_id', $let_id);
        }
        $this->builder()->where('let_ativo', 'A');
        $this->builder()->orderBy('let_ativo, let_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getLayEtiquetaSearch($termo)
    {
        $array = ['let_nome' => $termo . '%'];
        $this->builder()->select(['*']);
        $this->builder()->where('let_ativo', 'A');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
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
        $altura->largura  = 50;
        $ret['let_altura'] = $altura->crInput();

        $largura           =  new MyCampo('cfg_layout_etiqueta', 'let_largura');
        $largura->valor    = (isset($dados['let_largura'])) ? $dados['let_largura'] : '';
        $largura->obrigatorio = true;
        $largura->leitura  = $show;
        $largura->maxLength  = 3;
        $largura->largura  = 50;
        $ret['let_largura'] = $largura->crInput();

        $colunas          =  new MyCampo('cfg_layout_etiqueta', 'let_colunas');
        $colunas->valor    = (isset($dados['let_colunas'])) ? $dados['let_colunas'] : '';
        $colunas->obrigatorio = true;
        $colunas->leitura  = $show;
        $colunas->maxLength  = 2;
        $colunas->largura  = 50;
        $ret['let_colunas'] = $colunas->crInput();

        $let_linhas          =  new MyCampo('cfg_layout_etiqueta', 'let_linhas');
        $let_linhas->valor    = (isset($dados['let_linhas'])) ? $dados['let_linhas'] : '';
        $let_linhas->obrigatorio = true;
        $let_linhas->leitura  = $show;
        $let_linhas->maxLength  = 2;
        $let_linhas->largura  = 50;
        $ret['let_linhas'] = $let_linhas->crInput();

        $margemDireita          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_direita');
        $margemDireita->valor    = (isset($dados['let_marg_direita'])) ? $dados['let_marg_direita'] : '';
        $margemDireita->obrigatorio = true;
        $margemDireita->leitura  = $show;
        $margemDireita->maxLength  = 2;
        $margemDireita->largura  = 50;
        $ret['let_marg_direita'] = $margemDireita->crInput();

        $margemEsquerda          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_esquerda');
        $margemEsquerda->valor    = (isset($dados['let_marg_esquerda'])) ? $dados['let_marg_esquerda'] : '';
        $margemEsquerda->obrigatorio = true;
        $margemEsquerda->leitura  = $show;
        $margemEsquerda->maxLength  = 2;
        $margemEsquerda->largura  = 50;
        $ret['let_marg_esquerda'] = $margemEsquerda->crInput();

        $margemSuperior          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_superior');
        $margemSuperior->valor    = (isset($dados['let_marg_superior'])) ? $dados['let_marg_superior'] : '';
        $margemSuperior->obrigatorio = true;
        $margemSuperior->leitura  = $show;
        $margemSuperior->maxLength  = 2;
        $margemSuperior->largura  = 50;
        $ret['let_marg_superior'] = $margemSuperior->crInput();

        $margemInferior          =  new MyCampo('cfg_layout_etiqueta', 'let_marg_inferior');
        $margemInferior->valor    = (isset($dados['let_marg_inferior'])) ? $dados['let_marg_inferior'] : '';
        $margemInferior->obrigatorio = true;
        $margemInferior->leitura  = $show;
        $margemInferior->maxLength =  2;
        $margemInferior->largura =  50;
        $ret['let_marg_inferior'] = $margemInferior->crInput();

        $let_distancia          =  new MyCampo('cfg_layout_etiqueta', 'let_distancia_h');
        $let_distancia->valor    = (isset($dados['let_distancia_h'])) ? $dados['let_distancia_h'] : '';
        $let_distancia->obrigatorio = true;
        $let_distancia->leitura  = $show;
        $let_distancia->maxLength  = 2;
        $let_distancia->largura  = 50;
        $ret['let_distancia_h'] = $let_distancia->crInput();

        $let_distancia          =  new MyCampo('cfg_layout_etiqueta', 'let_distancia_v');
        $let_distancia->valor    = (isset($dados['let_distancia_v'])) ? $dados['let_distancia_v'] : '';
        $let_distancia->obrigatorio = true;
        $let_distancia->leitura  = $show;
        $let_distancia->maxLength  = 2;
        $let_distancia->largura  = 50;
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
