<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgCor extends Entity
{
    protected $attributes = [
        'cor_id'        => null,
        'cor_nome'      => null,
        'cor_valorrgb'  => null,
        'cor_ativo'     => 'A',
        'cor_excluido'  => null
    ];

    protected $datamap  = [];
    protected $dates    = ['cor_excluido'];
    protected $casts    = [];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data); // importante: primeiro
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray(); // Agora funciona corretamente
        $ret = [];

        $mid = new MyCampo('cfg_cor', 'cor_id');
        $mid->valor = $dados['cor_id'] ?? '';
        $ret['cor_id'] = $mid->crOculto();

        $nome = new MyCampo('cfg_cor', 'cor_nome');
        $nome->valor = $dados['cor_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura = $show;
        $ret['cor_nome'] = $nome->crInput();

        $vrgb = new MyCampo('cfg_cor', 'cor_valorrgb');
        $vrgb->tipo = 'color';
        $vrgb->obrigatorio = true;
        $vrgb->valor = $dados['cor_valorrgb'] ?? '';
        $vrgb->leitura = $show;
        $ret['cor_valorrgb'] = $vrgb->crInput();

        $opcat = ['A' => 'Ativo', 'I' => 'Inativo'];

        $ativ = new MyCampo('cfg_cor', 'cor_ativo');
        $ativ->valor = $dados['cor_ativo'] ?? 'A';
        $ativ->selecionado = $ativ->valor;
        $ativ->opcoes = $opcat;
        $ativ->leitura = $show;
        $ret['cor_ativo'] = $ativ->cr2opcoes();

        return $ret;
    }
}
