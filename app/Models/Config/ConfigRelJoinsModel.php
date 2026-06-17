<?php

namespace App\Models\Config;

use CodeIgniter\Model;
use App\Entities\Config\EntCfgRelJoins;
use App\Models\LogMonModel;

class ConfigRelJoinsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_rel_joins';
    protected $primaryKey       = 'rjo_id';

    protected $returnType       = EntCfgRelJoins::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'rjo_id',
        'rel_id',
        'rjo_tipo_join',
        'rjo_tabela_join',
        'rjo_alias_join',
        'rjo_condicao_on',
        'rjo_ordem',
    ];

    protected $validationRules = [
        'rel_id'          => 'required|integer',
        'rjo_tipo_join'   => 'required|in_list[INNER,LEFT,RIGHT]',
        'rjo_tabela_join' => 'required',
        'rjo_condicao_on' => 'required',
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
     * Retorna todos os JOINs de um relatório, na ordem de execução.
     */
    public function getJoins(int $rel_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->where('rel_id', $rel_id);
        $builder->orderBy('rjo_ordem');

        return $builder->get()->getResult(EntCfgRelJoins::class);
    }

    /**
     * Retorna um JOIN específico pelo ID.
     */
    public function getJoin(int $rjo_id)
    {
        $db      = db_connect('default');
        $builder = $db->table($this->table);
        $builder->where('rjo_id', $rjo_id);

        return $builder->get()->getFirstRow(EntCfgRelJoins::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gravação
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Substitui todos os JOINs de um relatório de uma vez.
     * Remove os existentes e regrava a lista completa.
     *
     * @param  int   $rel_id
     * @param  array $joins  Array de arrays com os campos de cada JOIN
     */
    public function sincronizarJoins(int $rel_id, array $joins): void
    {
        $db = db_connect('default');
        $db->table($this->table)->where('rel_id', $rel_id)->delete();

        foreach ($joins as $ordem => $j) {
            $j['rel_id']    = $rel_id;
            $j['rjo_ordem'] = $ordem + 1;
            $this->insert($j);
        }
    }

    /**
     * Retorna a próxima ordem disponível para um relatório.
     */
    public function getProximaOrdem(int $rel_id): int
    {
        return (int) ($this->selectMax('rjo_ordem')
            ->where('rel_id', $rel_id)
            ->first()
            ->rjo_ordem ?? 0) + 1;
    }
}
