<?php

namespace App\Entities\Config;

use App\Traits\HasTela;
use App\Traits\HasModulo;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use CodeIgniter\Entity\Entity;

class EntCfgRelatorios extends Entity
{
    use HasModulo, HasTela;

    protected $attributes = [
        'rel_id'                   => null,
        'rel_nome'                 => null,
        'mod_id'                   => null,
        'tel_id'                   => null,
        'rel_titulo'               => null,
        'rel_tabela_base'          => null,
        'rel_formato'              => 'P',
        'rel_tamanho_fonte'        => 10,
        'rel_chars_por_linha'      => 0,
        'rel_totalizar_registros'  => 0,
        'rel_sql_gerado'           => null,
        'rel_ativo'                => 1,
        'rel_criado_por'           => null,
        'rel_criado_em'            => null,
        'rel_atualizado_em'        => null,
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

        $ret['rel_id'] = (new MyCampo('cfg_relatorios', 'rel_id'))
            ->setValor($dados['rel_id'] ?? '')
            ->crOculto();

        $ret['rel_nome'] = (new MyCampo('cfg_relatorios', 'rel_nome'))
            ->setValor($dados['rel_nome'] ?? '')
            ->setObrigatorio()
            ->setMinLength(5)
            ->setMaxLength(50)
            ->setDispForm('col-12')
            ->setLeitura($show)
            ->crInput();

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

        $configTela             = $config;
        $configTela['Obrigatorio'] = false;
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

        $ret['rel_titulo'] = (new MyCampo('cfg_relatorios', 'rel_titulo'))
            ->setValor($dados['rel_titulo'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-8')
            ->setLeitura($show)
            ->crInput();

        $opTabelas = [];
        if (!empty($dados['mod_id'])) {
            $mod = (new ConfigModuloModel())->find((int) $dados['mod_id']);
            if ($mod && !empty($mod->mod_dbgroup)) {
                foreach ((new ConfigDicDadosModel())->getTabelasPorDbGroup($mod->mod_dbgroup) as $t) {
                    $opTabelas[$t['table_name']] = $t['table_comment'];
                }
            }
        }

        $ret['rel_tabela_base'] = (new MyCampo('cfg_relatorios', 'rel_tabela_base'))
            ->setValor($dados['rel_tabela_base'] ?? '')
            ->setSelecionado([$dados['rel_tabela_base'] ?? ''])
            ->setOpcoes($opTabelas)
            ->setObrigatorio()
            ->setDispForm('col-5')
            ->setLeitura($show)
            ->setPai('mod_id')
            ->setUrlbusca(base_url('buscas/busca_tabelas_modulo'))
            ->crDepende();

        $ret['rel_formato'] = (new MyCampo('cfg_relatorios', 'rel_formato'))
            ->setValor($dados['rel_formato'] ?? 'P')
            ->setSelecionado($dados['rel_formato'] ?? 'P')
            ->setOpcoes(['P' => 'Retrato', 'L' => 'Paisagem'])
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->cr2opcoes();

        $opFonte = [];
        // Fontes de 6 a 16pt — alterado para permitir fontes menores e maiores
        for ($i = 6; $i <= 16; $i++) {
            $opFonte[$i] = $i . ' pt';
        }

        $ret['rel_tamanho_fonte'] = (new MyCampo('cfg_relatorios', 'rel_tamanho_fonte'))
            ->setValor($dados['rel_tamanho_fonte'] ?? 10)
            ->setSelecionado($dados['rel_tamanho_fonte'] ?? 10)
            ->setOpcoes($opFonte)
            ->setDispForm('col-2')
            ->setLargura(20)
            ->setLeitura($show)
            ->crSelect();

        $ret['rel_totalizar_registros'] = (new MyCampo('cfg_relatorios', 'rel_totalizar_registros'))
            ->setValor($dados['rel_totalizar_registros'] ?? 0)
            ->setSelecionado($dados['rel_totalizar_registros'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->cr2opcoes();

        // Campo visual (não grava) — mostra chars por linha conforme orientação e fonte
        $charsCalc = 0;
        $fmt = $dados['rel_formato'] ?? 'P';
        $fnt = (int) ($dados['rel_tamanho_fonte'] ?? 10);
        if ($fnt > 0) {
            $largMm   = ($fmt === 'L') ? 277 : 190;
            $charsCalc = (int) floor($largMm / ($fnt * 0.353 * 0.45));
        }
        $campoChars = new MyCampo();
        $campoChars->nome     = 'rel_chars_display';
        $campoChars->id       = 'rel_chars_display';
        $campoChars->label    = 'Caracteres por linha';
        $campoChars->tipo     = 'text';
        $campoChars->objeto   = 'input';
        $campoChars->valor    = $charsCalc;
        $campoChars->largura  = 12;
        $campoChars->dispForm = 'col-2';
        $campoChars->leitura  = true;
        $ret['rel_chars_display'] = $campoChars->crInput();

        $ret['prf_id'] = criaSelectRelativo(
            'cfg_perfil',
            'prf_id',
            'prf_nome',
            $dados['prf_id'] ?? '',
            3,
            'cfg_rel_permissao',
            [],
            $config
        );

        return $ret;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Campos das abas Filtros e Colunas — delegados para as entities corretas
    // ─────────────────────────────────────────────────────────────────────────

    public function defCamposFiltro($dados = false, bool $show = false, int $pos = 0, array $opcoesCampo = []): array
    {
        return EntCfgRelFiltros::defCampos($dados, $show, $pos, $opcoesCampo);
    }

    public function defCamposColunas($dados = false, bool $show = false, int $pos = 0): array
    {
        return EntCfgRelColunas::defCampos($dados, $show, $pos);
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
