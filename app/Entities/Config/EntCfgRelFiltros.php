<?php

namespace App\Entities\Config;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgRelFiltros extends Entity
{
    protected $attributes = [
        'rfi_id'          => null,
        'rel_id'          => null,
        'rfi_tabela'      => null,
        'rfi_campo'       => null,
        'rfi_tipo_filtro' => 'FK',
        'rfi_label'       => null,
        'rfi_obrigatorio' => 0,
        'rfi_ordem'       => 0,
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret   = [];

        // ── Ocultos ──────────────────────────────────────────────────────────
        $ret['rfi_id'] = (new MyCampo('cfg_rel_filtros', 'rfi_id'))
            ->setValor($dados['rfi_id'] ?? '')
            ->crOculto();

        $ret['rel_id'] = (new MyCampo('cfg_rel_filtros', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->crOculto();

        $ret['rfi_tabela'] = (new MyCampo('cfg_rel_filtros', 'rfi_tabela'))
            ->setValor($dados['rfi_tabela'] ?? '')
            ->crOculto();

        $ret['rfi_campo'] = (new MyCampo('cfg_rel_filtros', 'rfi_campo'))
            ->setValor($dados['rfi_campo'] ?? '')
            ->crOculto();

        $ret['rfi_ordem'] = (new MyCampo('cfg_rel_filtros', 'rfi_ordem'))
            ->setValor($dados['rfi_ordem'] ?? 0)
            ->crOculto();

        // ── Tipo do filtro ────────────────────────────────────────────────────
        $ret['rfi_tipo_filtro'] = (new MyCampo('cfg_rel_filtros', 'rfi_tipo_filtro'))
            ->setValor($dados['rfi_tipo_filtro'] ?? 'FK')
            ->setSelecionado($dados['rfi_tipo_filtro'] ?? 'FK')
            ->setOpcoes(['FK' => 'Chave Estrangeira', 'DATE' => 'Data'])
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->cr2opcoes();

        // ── Label ─────────────────────────────────────────────────────────────
        $ret['rfi_label'] = (new MyCampo('cfg_rel_filtros', 'rfi_label'))
            ->setValor($dados['rfi_label'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-6')
            ->setLeitura($show)
            ->crInput();

        // ── Obrigatório ───────────────────────────────────────────────────────
        $ret['rfi_obrigatorio'] = (new MyCampo('cfg_rel_filtros', 'rfi_obrigatorio'))
            ->setValor($dados['rfi_obrigatorio'] ?? 0)
            ->setSelecionado($dados['rfi_obrigatorio'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->cr2opcoes();

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers de exibição
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna o label legível do tipo de filtro.
     */
    public function getTipoLabel(): string
    {
        return $this->rfi_tipo_filtro === 'DATE' ? 'Data' : 'Chave Estrangeira';
    }

    /**
     * Informa se o filtro é do tipo data.
     */
    public function isData(): bool
    {
        return $this->rfi_tipo_filtro === 'DATE';
    }

    /**
     * Retorna os nomes dos placeholders gerados para este filtro no SQL.
     * FK  → [':campo']
     * DATE → [':campo_de', ':campo_ate']
     */
    public function getPlaceholders(): array
    {
        if ($this->isData()) {
            return [
                ":{$this->rfi_campo}_de",
                ":{$this->rfi_campo}_ate",
            ];
        }

        return [":{$this->rfi_campo}"];
    }
}
