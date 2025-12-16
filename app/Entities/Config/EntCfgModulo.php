<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgModulo extends Entity
{
    protected $attributes = [
        'mod_id'      => null,
        'mod_nome'    => null,
        'mod_icone'   => null,
        'mod_ativo'   => 'A',
        'mod_excluido'=> null,
    ];

    protected $dates = ['mod_excluido'];
    protected $casts = [];

    /** Campos de formulário */
    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /**
     * Definição dos campos de tela
     */
    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        $mid = new MyCampo('cfg_modulo', 'mod_id');
        $mid->valor = $dados['mod_id'] ?? '';
        $ret['mod_id'] = $mid->crOculto();

        $nome = new MyCampo('cfg_modulo', 'mod_nome');
        $nome->valor = $dados['mod_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura = $show;
        $ret['mod_nome'] = $nome->crInput();

        $icon = new MyCampo('cfg_modulo', 'mod_icone');
        $icon->tipo = 'icone';
        $icon->valor = $dados['mod_icone'] ?? '';
        $icon->leitura = $show;
        $ret['mod_icon'] = $icon->crInput();

        $op = ['A' => 'Ativo', 'I' => 'Inativo'];

        $ativ = new MyCampo('cfg_modulo', 'mod_ativo');
        $ativ->valor = $dados['mod_ativo'] ?? 'A';
        $ativ->selecionado = $ativ->valor;
        $ativ->opcoes = $op;
        $ativ->leitura = $show;
        $ret['mod_ativo'] = $ativ->cr2opcoes();

        return $ret;
    }
}
