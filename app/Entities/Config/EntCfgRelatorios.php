<?php

namespace App\Entities\Config;

use App\Traits\HasTela;
use App\Traits\HasModulo;
use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgRelatorios extends Entity
{
    use HasModulo, HasTela;

    protected $attributes = [
        'rel_id'              => null,
        'mod_id'              => null,
        'tel_id'              => null,
        'rel_titulo'          => null,
        'rel_tabela_base'     => null,
        'rel_formato'         => 'P',
        'rel_tamanho_fonte'   => 10,
        'rel_chars_por_linha' => 0,
        'rel_sql_gerado'      => null,
        'rel_ativo'           => 1,
        'rel_criado_por'      => null,
        'rel_criado_em'       => null,
        'rel_atualizado_em'   => null,
    ];

    protected $dates = ['rel_criado_em', 'rel_atualizado_em'];

    public array $campos     = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Campos do cabeçalho (Aba Dados Gerais)
    // ─────────────────────────────────────────────────────────────────────────

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret   = [];

        // Oculto
        $ret['rel_id'] = (new MyCampo('cfg_relatorios', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->crOculto();

        // Módulo
        $config = ['Leitura' => $show, 'Largura' => 50];

        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'cfg_relatorios',
            [],
            $config
        );

        // Tela (dependente do Módulo)
        $configTela             = $config;
        $configTela['Obrigatorio']      = false;
        $configTela['Pai']      = 'mod_id';
        $configTela['Urlbusca'] = base_url('buscas/busca_tela_modulo');

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            2,
            'cfg_relatorios',
            [],
            $configTela
        );

        // Título
        $ret['rel_titulo'] = (new MyCampo('cfg_relatorios', 'rel_titulo'))
            ->setValor($dados['rel_titulo'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-12')
            ->setLeitura($show)
            ->crInput();

        // Tabela base — ao mudar, dispara JS que popula selects das abas filhas
        $ret['rel_tabela_base'] = (new MyCampo('cfg_relatorios', 'rel_tabela_base'))
            ->setValor($dados['rel_tabela_base'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-6')
            ->setLeitura($show)
            ->crInput();

        // Formato
        $ret['rel_formato'] = (new MyCampo('cfg_relatorios', 'rel_formato'))
            ->setValor($dados['rel_formato'] ?? 'P')
            ->setSelecionado($dados['rel_formato'] ?? 'P')
            ->setOpcoes(['P' => 'Retrato', 'L' => 'Paisagem'])
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->cr2opcoes();

        // Tamanho da fonte
        $opFonte = [];
        for ($i = 8; $i <= 14; $i++) {
            $opFonte[$i] = $i . ' pt';
        }

        $ret['rel_tamanho_fonte'] = (new MyCampo('cfg_relatorios', 'rel_tamanho_fonte'))
            ->setValor($dados['rel_tamanho_fonte'] ?? 10)
            ->setSelecionado($dados['rel_tamanho_fonte'] ?? 10)
            ->setOpcoes($opFonte)
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->crSelect();

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Campos de UMA linha da aba Filtros
    //  Padrão idêntico ao defCamposCfg() da EntCfgEtiqueta
    // ─────────────────────────────────────────────────────────────────────────

    public function defCamposFiltro($dados = false, bool $show = false, int $pos = 0): array
    {
        $ret = [];

        // ── Ocultos ──────────────────────────────────────────────────────────

        $ret['rfi_id'] = (new MyCampo('cfg_rel_filtros', 'rfi_id'))
            ->setValor($dados['rfi_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rel_id'] = (new MyCampo('cfg_rel_filtros', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        // Tipo do filtro — oculto, preenchido via JS ao selecionar o campo
        $ret['rfi_tipo_filtro'] = (new MyCampo('cfg_rel_filtros', 'rfi_tipo_filtro'))
            ->setValor($dados['rfi_tipo_filtro'] ?? 'FK')
            ->setOrdem($pos)
            ->crOculto();

        // Tabela — oculto, preenchido via JS ao selecionar o campo
        $ret['rfi_tabela'] = (new MyCampo('cfg_rel_filtros', 'rfi_tabela'))
            ->setValor($dados['rfi_tabela'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        // Ordem — oculto
        $ret['rfi_ordem'] = (new MyCampo('cfg_rel_filtros', 'rfi_ordem'))
            ->setValor($dados['rfi_ordem'] ?? $pos)
            ->setOrdem($pos)
            ->crOculto();

        // ── Visíveis ──────────────────────────────────────────────────────────

        // Select de campo — populado via JS/AJAX a partir da tabela base
        // O value carrega "campo|tabela|tipo" para o JS decodificar
        $opcaoCampo = [];
        if (!empty($dados['rfi_campo'])) {
            $selecionado = $dados['rfi_campo'] . '|' . ($dados['rfi_tabela'] ?? '') . '|' . ($dados['rfi_tipo_filtro'] ?? 'FK');
            $opcaoCampo[$selecionado] = ucwords(str_replace('_', ' ', $dados['rfi_campo']));
        }

        $ret['rfi_campo'] = (new MyCampo('cfg_rel_filtros', 'rfi_campo'))
            ->setValor($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setSelecionado($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setOpcoes($opcaoCampo)
            ->setObrigatorio()
            ->setOrdem($pos)
            ->setDispForm('col-4')
            ->setLeitura($show)
            ->crSelect();

        // Label
        $ret['rfi_label'] = (new MyCampo('cfg_rel_filtros', 'rfi_label'))
            ->setValor($dados['rfi_label'] ?? '')
            ->setObrigatorio()
            ->setOrdem($pos)
            ->setDispForm('col-5')
            ->setLeitura($show)
            ->crInput();

        // Obrigatório
        $ret['rfi_obrigatorio'] = (new MyCampo('cfg_rel_filtros', 'rfi_obrigatorio'))
            ->setValor($dados['rfi_obrigatorio'] ?? 0)
            ->setSelecionado($dados['rfi_obrigatorio'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setOrdem($pos)
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->cr2opcoes();

        // ── Botões + e lixeira ────────────────────────────────────────────────
        $atrib = ['data-index' => $pos];

        $add           = new MyCampo();
        $add->attrdata = $atrib;
        $add->tipo     = 'button';
        $add->dispForm = '1col';
        $add->nome     = "bt_add_fil[{$pos}]";
        $add->id       = "bt_add_fil[{$pos}]";
        $add->i_cone   = "<i class='fas fa-plus'></i>";
        $add->place    = 'Adicionar Filtro';
        $add->classep  = 'btn-outline-success btn-sm bt-repete';
        $add->funcChan = "addCampo('" . base_url('CfgRelatorio/addFiltro/') . "','filtros',this)";
        $ret['bt_add'] = $add->crBotao();

        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->tipo     = 'button';
        $del->dispForm = '1col';
        $del->nome     = "bt_del_fil[{$pos}]";
        $del->id       = "bt_del_fil[{$pos}]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = 'btn-outline-danger btn-sm bt-exclui';
        $del->funcChan = "exclui_campo('filtros',this)";
        $del->place    = 'Excluir Filtro';
        $ret['bt_del'] = $del->crBotao();

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Campos de UMA linha da aba Colunas
    // ─────────────────────────────────────────────────────────────────────────

    public function defCamposColunas($dados = false, bool $show = false, int $pos = 0): array
    {
        $ret = [];

        // ── Ocultos ──────────────────────────────────────────────────────────

        $ret['rco_id'] = (new MyCampo('cfg_rel_colunas', 'rco_id'))
            ->setValor($dados['rco_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rel_id'] = (new MyCampo('cfg_rel_colunas', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        // Tabela — oculto, preenchido via JS ao selecionar o campo
        $ret['rco_tabela'] = (new MyCampo('cfg_rel_colunas', 'rco_tabela'))
            ->setValor($dados['rco_tabela'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        // Tamanho original — oculto, preenchido via JS ao selecionar o campo
        $ret['rco_tamanho'] = (new MyCampo('cfg_rel_colunas', 'rco_tamanho'))
            ->setValor($dados['rco_tamanho'] ?? 0)
            ->setOrdem($pos)
            ->crOculto();

        // Tamanho efetivo — oculto, recalculado via JS ao marcar quebra
        $ret['rco_tamanho_efetivo'] = (new MyCampo('cfg_rel_colunas', 'rco_tamanho_efetivo'))
            ->setValor($dados['rco_tamanho_efetivo'] ?? 0)
            ->setOrdem($pos)
            ->crOculto();

        // Ordem — oculto
        $ret['rco_ordem'] = (new MyCampo('cfg_rel_colunas', 'rco_ordem'))
            ->setValor($dados['rco_ordem'] ?? $pos)
            ->setOrdem($pos)
            ->crOculto();

        // ── Visíveis ──────────────────────────────────────────────────────────

        // Select de campo — populado via JS/AJAX
        // O value carrega "tabela|campo|tamanho" para o JS decodificar
        $opcaoCampo = [];
        if (!empty($dados['rco_campo'])) {
            $selecionado = ($dados['rco_tabela'] ?? '') . '|' . $dados['rco_campo'] . '|' . ($dados['rco_tamanho'] ?? 0);
            $opcaoCampo[$selecionado] = '[' . ($dados['rco_tabela'] ?? '') . '] ' . ucwords(str_replace('_', ' ', $dados['rco_campo']));
        }

        $ret['rco_campo'] = (new MyCampo('cfg_rel_colunas', 'rco_campo'))
            ->setValor($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setSelecionado($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setOpcoes($opcaoCampo)
            ->setObrigatorio()
            ->setOrdem($pos)
            ->setDispForm('col-4')
            ->setLeitura($show)
            ->crSelect();

        // Label (cabeçalho da coluna no relatório)
        $ret['rco_label'] = (new MyCampo('cfg_rel_colunas', 'rco_label'))
            ->setValor($dados['rco_label'] ?? '')
            ->setObrigatorio()
            ->setOrdem($pos)
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->crInput();

        // Alias (opcional)
        $ret['rco_alias'] = (new MyCampo('cfg_rel_colunas', 'rco_alias'))
            ->setValor($dados['rco_alias'] ?? '')
            ->setOrdem($pos)
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->crInput();

        // Alinhamento
        $ret['rco_alinhamento'] = (new MyCampo('cfg_rel_colunas', 'rco_alinhamento'))
            ->setValor($dados['rco_alinhamento'] ?? 'E')
            ->setSelecionado($dados['rco_alinhamento'] ?? 'E')
            ->setOpcoes(['E' => 'Esquerda', 'C' => 'Centro', 'D' => 'Direita'])
            ->setOrdem($pos)
            ->setDispForm('col-1')
            ->setLeitura($show)
            ->crSelect();

        // Quebra de linha — desabilitado via JS quando tamanho <= 60
        $ret['rco_quebra_linha'] = (new MyCampo('cfg_rel_colunas', 'rco_quebra_linha'))
            ->setValor($dados['rco_quebra_linha'] ?? 0)
            ->setSelecionado($dados['rco_quebra_linha'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setOrdem($pos)
            ->setDispForm('col-1')
            ->setLeitura($show)
            ->cr2opcoes();

        // ── Botões + e lixeira ────────────────────────────────────────────────
        $atrib = ['data-index' => $pos];

        $add           = new MyCampo();
        $add->attrdata = $atrib;
        $add->tipo     = 'button';
        $add->dispForm = '1col';
        $add->nome     = "bt_add_col[{$pos}]";
        $add->id       = "bt_add_col[{$pos}]";
        $add->i_cone   = "<i class='fas fa-plus'></i>";
        $add->place    = 'Adicionar Coluna';
        $add->classep  = 'btn-outline-success btn-sm bt-repete';
        $add->funcChan = "addCampo('" . base_url('CfgRelatorio/addColuna/') . "','colunas',this)";
        $ret['bt_add'] = $add->crBotao();

        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->tipo     = 'button';
        $del->dispForm = '1col';
        $del->nome     = "bt_del_col[{$pos}]";
        $del->id       = "bt_del_col[{$pos}]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = 'btn-outline-danger btn-sm bt-exclui';
        $del->funcChan = "exclui_campo('colunas',this)";
        $del->place    = 'Excluir Coluna';
        $ret['bt_del'] = $del->crBotao();

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function getFormatoLabel(): string
    {
        return $this->rel_formato === 'L' ? 'Paisagem' : 'Retrato';
    }

    public function temCharsCalculados(): bool
    {
        return (int) $this->rel_chars_por_linha > 0;
    }
}
