<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgRelatorios;
use App\Models\LogMonModel;

class ConfigRelatoriosModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_relatorios';
    protected $view             = 'cfg_relatorios';
    protected $primaryKey       = 'rel_id';

    protected $returnType       = EntCfgRelatorios::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'rel_id',
        'mod_id',
        'tel_id',
        'rel_titulo',
        'rel_tabela_base',
        'rel_formato',
        'rel_tamanho_fonte',
        'rel_chars_por_linha',
        'rel_sql_gerado',
        'rel_ativo',
        'rel_criado_por',
        'rel_criado_em',
        'rel_atualizado_em',
    ];

    protected $validationRules = [
        'rel_titulo'       => 'required|min_length[3]',
        'rel_tabela_base'  => 'required',
        'rel_formato'      => 'required|in_list[P,L]',
        'rel_tamanho_fonte' => 'required|integer|greater_than_equal_to[8]|less_than_equal_to[14]',
    ];

    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function logUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function logDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Leitura
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna um ou todos os relatórios.
     *
     * @param  int|false $rel_id  ID específico ou false para todos
     * @param  int|null  $ativo   1 = ativos, 0 = inativos, null = todos
     */
    public function getRelatorios($rel_id = false, $ativo = null)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');

        if ($ativo !== null) {
            $builder->where('rel_ativo', $ativo);
        }

        if ($rel_id) {
            $builder->where('rel_id', $rel_id);
            return $builder->get()->getFirstRow(EntCfgRelatorios::class);
        }

        return $builder->get()->getResult(EntCfgRelatorios::class);
    }

    /**
     * Retorna relatórios filtrados por módulo.
     */
    public function getRelatoriosPorModulo(int $mod_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('mod_id', $mod_id);
        $builder->where('rel_ativo', 1);
        $builder->orderBy('rel_titulo');

        return $builder->get()->getResult(EntCfgRelatorios::class);
    }

    /**
     * Retorna relatórios filtrados por tela.
     */
    public function getRelatoriosPorTela(int $tel_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('tel_id', $tel_id);
        $builder->where('rel_ativo', 1);
        $builder->orderBy('rel_titulo');

        return $builder->get()->getResult(EntCfgRelatorios::class);
    }

    /**
     * Busca por título (autocomplete / search).
     */
    public function getRelatoriosSearch(string $termo)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->like('rel_titulo', $termo . '%');

        return $builder->get()->getResult(EntCfgRelatorios::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gravação com cálculo automático
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula quantos caracteres cabem em uma linha do relatório
     * com base no formato (P/L) e no tamanho da fonte (Arial).
     *
     * Fórmula: largura útil em mm / (tamanho_pt × 0,353 × 0,45)
     */
    public function calcCharsPorLinha(string $formato, int $tamanhoFonte): int
    {
        $larguraUtilMm  = ($formato === 'L') ? 277 : 190;
        $larguraCharMm  = $tamanhoFonte * 0.353 * 0.45;

        return (int) floor($larguraUtilMm / $larguraCharMm);
    }

    /**
     * Persiste o cabeçalho do relatório, calculando chars_por_linha
     * automaticamente antes de gravar.
     *
     * @param  array $dados  Campos do formulário
     * @return int           ID gravado (insert) ou ID existente (update)
     */
    public function salvarRelatorio(array $dados): int
    {
        $dados['rel_chars_por_linha'] = $this->calcCharsPorLinha(
            $dados['rel_formato']      ?? 'P',
            (int)($dados['rel_tamanho_fonte'] ?? 10)
        );

        if (!empty($dados['rel_id'])) {
            $this->update($dados['rel_id'], $dados);
            return (int) $dados['rel_id'];
        }

        $dados['rel_criado_por'] = session()->get('usu_id');
        return (int) $this->insert($dados);
    }

    /**
     * Grava o SQL gerado no campo rel_sql_gerado.
     */
    public function gravarSqlGerado(int $rel_id, string $sql): void
    {
        $this->update($rel_id, ['rel_sql_gerado' => $sql]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Geração do SQL de consulta
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Monta o SQL de consulta completo com base nas configurações gravadas
     * nas tabelas filhas (filtros, colunas, joins) e o armazena em rel_sql_gerado.
     */
    public function gerarSQL(int $rel_id): string
    {
        $db = db_connect('default');

        $relatorio = $db->table('cfg_relatorios')
            ->where('rel_id', $rel_id)
            ->get()->getFirstRow();

        $colunas = $db->table('cfg_rel_colunas')
            ->where('rel_id', $rel_id)
            ->orderBy('rco_ordem')
            ->get()->getResult();

        $joins = $db->table('cfg_rel_joins')
            ->where('rel_id', $rel_id)
            ->orderBy('rjo_ordem')
            ->get()->getResult();

        $filtros = $db->table('cfg_rel_filtros')
            ->where('rel_id', $rel_id)
            ->orderBy('rfi_ordem')
            ->get()->getResult();

        // SELECT
        $sel = [];
        foreach ($colunas as $col) {
            $sel[] = "{$col->rco_tabela}.{$col->rco_campo} AS '{$col->rco_label}'";
        }
        $sql  = 'SELECT ' . implode(',' . PHP_EOL . '       ', $sel);

        // FROM
        $sql .= PHP_EOL . 'FROM ' . $relatorio->rel_tabela_base;

        // JOINs
        foreach ($joins as $j) {
            $alias = $j->rjo_alias_join ? " AS {$j->rjo_alias_join}" : '';
            $sql  .= PHP_EOL . "{$j->rjo_tipo_join} JOIN {$j->rjo_tabela_join}{$alias}"
                . " ON {$j->rjo_condicao_on}";
        }

        // WHERE
        $sql .= PHP_EOL . 'WHERE 1=1';
        foreach ($filtros as $f) {
            if ($f->rfi_tipo_filtro === 'FK') {
                $sql .= PHP_EOL . "  AND {$f->rfi_tabela}.{$f->rfi_campo} = :{$f->rfi_campo}";
            } else {
                $sql .= PHP_EOL . "  AND {$f->rfi_tabela}.{$f->rfi_campo} >= :{$f->rfi_campo}_de";
                $sql .= PHP_EOL . "  AND {$f->rfi_tabela}.{$f->rfi_campo} <= :{$f->rfi_campo}_ate";
            }
        }

        $sql .= PHP_EOL . "ORDER BY {$relatorio->rel_tabela_base}.{$this->primaryKey}";

        $this->gravarSqlGerado($rel_id, $sql);

        return $sql;
    }
}
