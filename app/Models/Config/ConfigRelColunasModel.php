<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgRelColunas;
use App\Models\LogMonModel;

class ConfigRelColunasModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_rel_colunas';
    protected $primaryKey       = 'rco_id';

    protected $returnType       = EntCfgRelColunas::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'rco_id',
        'rel_id',
        'rco_tabela',
        'rco_campo',
        'rco_alias',
        'rco_label',
        'rco_tamanho',
        'rco_tamanho_efetivo',
        'rco_quebra_linha',
        'rco_alinhamento',
        'rco_ordem',
    ];

    protected $validationRules = [
        'rel_id'       => 'required|integer',
        'rco_tabela'   => 'required',
        'rco_campo'    => 'required',
        'rco_label'    => 'required',
        'rco_tamanho'  => 'required|integer',
        'rco_alinhamento' => 'required|in_list[E,C,D]',
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
     * Retorna todas as colunas de um relatório, ordenadas.
     */
    public function getColunas(int $rel_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where('rel_id', $rel_id);
        $builder->orderBy('rco_ordem');

        return $builder->get()->getResult(EntCfgRelColunas::class);
    }

    /**
     * Retorna uma coluna específica pelo ID.
     */
    public function getColuna(int $rco_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->where('rco_id', $rco_id);

        return $builder->get()->getFirstRow(EntCfgRelColunas::class);
    }

    /**
     * Calcula a soma de rco_tamanho_efetivo de todas as colunas
     * já gravadas de um relatório — usado para verificar estouro.
     */
    public function getTotalLarguraUsada(int $rel_id): int
    {
        $db = db_connect('default');
        $row = $db->table($this->table)
            ->selectSum('rco_tamanho_efetivo', 'total')
            ->where('rel_id', $rel_id)
            ->get()->getFirstRow();

        return (int) ($row->total ?? 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gravação
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula rco_tamanho_efetivo com base na quebra de linha e retorna
     * o array de dados pronto para persistência.
     */
    public function prepararColuna(array $dados): array
    {
        $tamanho = (int) ($dados['rco_tamanho'] ?? 0);
        $quebra  = !empty($dados['rco_quebra_linha']) && $tamanho > 60;

        $dados['rco_quebra_linha']    = $quebra ? 1 : 0;
        $dados['rco_tamanho_efetivo'] = $quebra ? (int) ceil($tamanho / 2) : $tamanho;

        return $dados;
    }

    /**
     * Substitui todas as colunas de um relatório de uma vez.
     * Remove as existentes e regrava a lista completa.
     *
     * @param  int   $rel_id
     * @param  array $colunas  Array de arrays com os campos de cada coluna
     */
    public function sincronizarColunas(int $rel_id, array $colunas): void
    {
        $db = db_connect('default');
        $db->table($this->table)->where('rel_id', $rel_id)->delete();

        foreach ($colunas as $ordem => $col) {
            $col['rel_id']    = $rel_id;
            $col['rco_ordem'] = $ordem + 1;
            $this->insert($this->prepararColuna($col));
        }
    }

    /**
     * Retorna a próxima ordem disponível para um relatório.
     */
    public function getProximaOrdem(int $rel_id): int
    {
        return (int) ($this->selectMax('rco_ordem')
            ->where('rel_id', $rel_id)
            ->first()
            ->rco_ordem ?? 0) + 1;
    }
}
