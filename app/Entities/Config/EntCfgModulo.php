<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigModuloModel;

class EntCfgModulo extends Entity
{
    protected $attributes = [
        'mod_id'       => null,
        'mod_nome'     => null,
        'mod_icone'    => null,
        'mod_dbgroup'  => null,
        'mod_ativo'    => 'A',
        'mod_excluido' => null,
    ];

    protected $dates = ['mod_excluido'];

    /** Campos de formulário */
    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /**
     * Criação de campos para formulário do módulo
     */
    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        // ID do Módulo (campo oculto)
        $mid = new MyCampo('cfg_modulo', 'mod_id');
        $mid->valor = $dados['mod_id'] ?? '';
        $ret['mod_id'] = $mid->crOculto();

        // Nome do Módulo
        $nome = new MyCampo('cfg_modulo', 'mod_nome');
        $nome->valor = $dados['mod_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura = $show;
        $ret['mod_nome'] = $nome->crInput();

        // Ícone do Módulo
        $icon = new MyCampo('cfg_modulo', 'mod_icone');
        $icon->tipo = 'icone';
        $icon->valor = $dados['mod_icone'] ?? '';
        $icon->leitura = $show;
        $ret['mod_icone'] = $icon->crInput();

        // Banco de Dados do Módulo
        $dbOpcoes = [
            ''              => '-- Selecione --',
            'default'       => 'Configuração (config_ceqweb_db)',
            'dbEstoque'     => 'Estoque (estoque_db)',
            'dbProduto'     => 'Produto (produto_db)',
            'dbOcorrencia'  => 'Ocorrência (ocorrencia_db)',
        ];
        $dbg = new MyCampo('cfg_modulo', 'mod_dbgroup');
        $dbg->label = 'Banco de Dados';
        $dbg->valor = $dados['mod_dbgroup'] ?? '';
        $dbg->selecionado = $dbg->valor;
        $dbg->opcoes = $dbOpcoes;
        $dbg->leitura = $show;
        $ret['mod_dbgroup'] = $dbg->crSelect();

        // Opções de status
        $op = ['A' => 'Ativo', 'I' => 'Inativo'];

        $ativ = new MyCampo('cfg_modulo', 'mod_ativo');
        $ativ->valor = $dados['mod_ativo'] ?? 'A';
        $ativ->selecionado = $ativ->valor;
        $ativ->opcoes = $op;
        $ativ->leitura = $show;
        $ret['mod_ativo'] = $ativ->cr2opcoes();

        return $ret;
    }

    /**
     * Campo reutilizável para mod_id (select)
     */
    // public static function campoSelectModulo(mixed $valor = null, bool $leitura = false, string $entidade = ''): string
    // {
    //     $modModel = new ConfigModuloModel();
    //     $mods = array_column($modModel->getModulo(), 'mod_nome', 'mod_id');

    //     $mod = (new MyCampo($entidade, 'mod_id'))
    //         ->setValor($valor ?? '')
    //         ->setSelecionado($valor ?? '')
    //         ->setOpcoes($mods)
    //         ->setLargura(40)
    //         ->setObrigatorio()
    //         ->setDispForm('col-4')
    //         ->setLeitura($leitura);

    //     return $mod->crSelect();
    // }
}
