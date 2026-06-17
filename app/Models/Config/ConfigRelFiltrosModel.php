<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgRelFiltros;
use App\Models\LogMonModel;

class ConfigRelFiltrosModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_rel_filtros';
    protected $primaryKey       = 'rfi_id';

    protected $returnType       = EntCfgRelFiltros::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'rfi_id',
        'rel_id',
        'rfi_tabela',
        'rfi_campo',
        'rfi_tipo_filtro',
        'rfi_label',
        'rfi_obrigatorio',
        'rfi_ordem',
    ];

    protected $validationRules = [
        'rel_id'          => 'required|integer',
        'rfi_tabela'      => 'required',
        'rfi_campo'       => 'required',
        'rfi_tipo_filtro' => 'required|in_list[FK,DATE]',
        'rfi_label'       => 'required',
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
     * Retorna todos os filtros de um relatório, ordenados.
     */
    public function getFiltros(int $rel_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where('rel_id', $rel_id);
        $builder->orderBy('rfi_ordem');

        return $builder->get()->getResult(EntCfgRelFiltros::class);
    }

    /**
     * Retorna um filtro específico pelo ID.
     */
    public function getFiltro(int $rfi_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->where('rfi_id', $rfi_id);

        return $builder->get()->getFirstRow(EntCfgRelFiltros::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gravação
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Substitui todos os filtros de um relatório de uma vez.
     * Remove os existentes e regrava a lista completa.
     *
     * @param  int   $rel_id
     * @param  array $filtros  Array de arrays com os campos de cada filtro
     */
    public function sincronizarFiltros(int $rel_id, array $filtros): void
    {
        $db = db_connect('default');
        $db->table($this->table)->where('rel_id', $rel_id)->delete();

        foreach ($filtros as $ordem => $f) {
            $f['rel_id']    = $rel_id;
            $f['rfi_ordem'] = $ordem + 1;
            $this->insert($f);
        }
    }

    /**
     * Retorna a próxima ordem disponível para um relatório.
     */
    public function getProximaOrdem(int $rel_id): int
    {
        return (int) ($this->selectMax('rfi_ordem')
            ->where('rel_id', $rel_id)
            ->first()
            ->rfi_ordem ?? 0) + 1;
    }
}
