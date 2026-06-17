<?php

namespace App\Entities\Config;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgRelColunas extends Entity
{
    protected $attributes = [
        'rco_id'              => null,
        'rel_id'              => null,
        'rco_tabela'          => null,
        'rco_campo'           => null,
        'rco_alias'           => null,
        'rco_label'           => null,
        'rco_tamanho'         => 0,
        'rco_tamanho_efetivo' => 0,
        'rco_quebra_linha'    => 0,
        'rco_alinhamento'     => 'E',
        'rco_ordem'           => 0,
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
        $ret['rco_id'] = (new MyCampo('cfg_rel_colunas', 'rco_id'))
            ->setValor($dados['rco_id'] ?? '')
            ->crOculto();

        $ret['rel_id'] = (new MyCampo('cfg_rel_colunas', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->crOculto();

        $ret['rco_tabela'] = (new MyCampo('cfg_rel_colunas', 'rco_tabela'))
            ->setValor($dados['rco_tabela'] ?? '')
            ->crOculto();

        $ret['rco_campo'] = (new MyCampo('cfg_rel_colunas', 'rco_campo'))
            ->setValor($dados['rco_campo'] ?? '')
            ->crOculto();

        $ret['rco_tamanho'] = (new MyCampo('cfg_rel_colunas', 'rco_tamanho'))
            ->setValor($dados['rco_tamanho'] ?? 0)
            ->crOculto();

        $ret['rco_tamanho_efetivo'] = (new MyCampo('cfg_rel_colunas', 'rco_tamanho_efetivo'))
            ->setValor($dados['rco_tamanho_efetivo'] ?? 0)
            ->crOculto();

        $ret['rco_ordem'] = (new MyCampo('cfg_rel_colunas', 'rco_ordem'))
            ->setValor($dados['rco_ordem'] ?? 0)
            ->crOculto();

        // ── Label da coluna ───────────────────────────────────────────────────
        $ret['rco_label'] = (new MyCampo('cfg_rel_colunas', 'rco_label'))
            ->setValor($dados['rco_label'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-5')
            ->setLeitura($show)
            ->crInput();

        // ── Alias (opcional) ──────────────────────────────────────────────────
        $ret['rco_alias'] = (new MyCampo('cfg_rel_colunas', 'rco_alias'))
            ->setValor($dados['rco_alias'] ?? '')
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->crInput();

        // ── Alinhamento ───────────────────────────────────────────────────────
        $ret['rco_alinhamento'] = (new MyCampo('cfg_rel_colunas', 'rco_alinhamento'))
            ->setValor($dados['rco_alinhamento'] ?? 'E')
            ->setSelecionado($dados['rco_alinhamento'] ?? 'E')
            ->setOpcoes(['E' => 'Esquerda', 'C' => 'Centro', 'D' => 'Direita'])
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->crSelect();

        // ── Quebra de linha (habilitado apenas quando tamanho > 60) ──────────
        // Renderizado como checkbox; a lógica de habilitar/desabilitar
        // fica no JavaScript da tela, verificando rco_tamanho.
        $ret['rco_quebra_linha'] = (new MyCampo('cfg_rel_colunas', 'rco_quebra_linha'))
            ->setValor($dados['rco_quebra_linha'] ?? 0)
            ->setSelecionado($dados['rco_quebra_linha'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->cr2opcoes();

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers de exibição e cálculo
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna o label legível do alinhamento.
     */
    public function getAlinhamentoLabel(): string
    {
        return match ($this->rco_alinhamento) {
            'C'     => 'Centro',
            'D'     => 'Direita',
            default => 'Esquerda',
        };
    }

    /**
     * Indica se o campo pode ter quebra de linha (tamanho original > 60).
     */
    public function permiteQuebraLinha(): bool
    {
        return (int) $this->rco_tamanho > 60;
    }

    /**
     * Retorna o tamanho efetivo calculado com base na quebra de linha.
     * Útil para recalcular no front antes de gravar.
     */
    public function calcTamanhoEfetivo(): int
    {
        if ($this->rco_quebra_linha && $this->permiteQuebraLinha()) {
            return (int) ceil((int) $this->rco_tamanho / 2);
        }

        return (int) $this->rco_tamanho;
    }

    /**
     * Retorna o fragmento SQL de SELECT para esta coluna.
     * Ex.: "tabela.campo AS 'Label'"
     */
    public function toSelectFragment(): string
    {
        $campo = $this->rco_alias ?: $this->rco_campo;

        return "{$this->rco_tabela}.{$campo} AS '{$this->rco_label}'";
    }
}
