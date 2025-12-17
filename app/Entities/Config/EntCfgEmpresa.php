<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgEmpresa extends Entity
{
    protected $attributes = [
        'emp_codfil' => null,
        'emp_codemp' => null,
        'emp_nomfil' => null,
        'emp_sigfil' => null,
        'emp_numcgc' => null,
        'emp_insest' => null,
    ];

    protected $dates = [];
    protected $casts = [];

    public array $campos = [];


    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }


    public function defCampos($dados = false, $show = false)
    {
        // NOME FILIAL
        $nome              =  new MyCampo('cfg_empresa', 'emp_nomfil');
        $nome->valor       = (isset($dados['emp_nomfil'])) ? $dados['emp_nomfil'] : '';
        $nome->size        = 100;
        $nome->tamanho     = 100;
        $nome->largura     = 100;
        $nome->leitura     = $show;
        $ret['emp_nomfil'] = $nome->crInput();

        // SIGLA FILAL
        $apel              =  new MyCampo('cfg_empresa', 'emp_sigfil');
        $apel->valor       = (isset($dados['emp_sigfil'])) ? $dados['emp_sigfil'] : '';
        $apel->leitura     = $show;
        $ret['emp_sigfil'] = $apel->crInput();

        // CÓDIGO DA EMPRESA
        $code              =  new MyCampo('cfg_empresa', 'emp_codemp');
        $code->valor       = (isset($dados['emp_codemp'])) ? $dados['emp_codemp'] : '';
        $code->leitura     = $show;
        $ret['emp_codemp'] = $code->crInput();

        // CÓDIGO DA FILIAL
        $codf              =  new MyCampo('cfg_empresa', 'emp_codfil', true);
        $codf->valor       = (isset($dados['emp_codfil'])) ? $dados['emp_codfil'] : '';
        $codf->leitura     = $show;
        $ret['emp_codfil'] = $codf->crInput();

        // CNPJ DA EMPRESA
        $cnpj              =  new MyCampo('cfg_empresa', 'emp_numcgc');
        $cnpj->valor       = (isset($dados['emp_numcgc'])) ? $dados['emp_numcgc'] : '';
        $cnpj->leitura     = $show;
        $ret['emp_numcgc'] = $cnpj->crInput();

        // INSCRIÇÃO ESTADUAL
        $inest             =  new MyCampo('cfg_empresa', 'emp_insest');
        $inest->valor      = (isset($dados['emp_insest'])) ? $dados['emp_insest'] : '';
        $inest->leitura    = $show;
        $ret['emp_insest'] = $inest->crInput();

        return $ret;
    }  
}
