<?php

namespace App\Models\Estoqu;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Estoque\EntTipoMovimentacao;

class EstoquTipoMovimentacaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_tipo_movimentacao';
    protected $view             = 'vw_est_tipo_movimentacao_relac_lista';
    protected $primaryKey       = 'tmo_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = EntTipoMovimentacao::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'tmo_id',
        'tmo_nome',
        'tmo_acumulador',
        'tmo_conferencia',
        'tmo_semestoque',
        'tmo_transacao_erp',
        'tmo_atendeautomatico',
        'tmo_transacao_erp_entrada',
        'tmo_transacao_erp_saida',
        'tmo_entrefiliais',
        'tmo_ativo',
        'tmo_excluido',
        'tmo_estoquepadrao',
        'tmo_gestaoestoque',
        'tmo_exigeinspecao',
        'tmo_requisicao',

    ];

    protected $deletedField  = 'tmo_excluido';

    protected $validationRules = [
        'tmo_nome' => 'required|min_length[5]|max_length[50]',
        // 'tmo_acumulador' => 'required'
    ];

    protected $validationMessages = [
        'tmo_nome' => [
            'required' => 'O campo nome é Obrigatório.',
            'min_length' => 'Digite pelo menos 5 Caracteres.',
            'max_length' => 'Número de Caracteres excedido.',
            'isUniqueValue' =>  '8'
        ],

        // 'tmo_acumulador' => [
        //     'required' => 'O campo acumulador é Obrigatório'
        // ]

    ];

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;


    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }

    public function getMovimentacoes(): array
    {
        return $this->db->table('est_tipo_movimentacao')
            ->select('tmo_id, tmo_nome')
            ->where('tmo_excluido', null)
            ->orderBy('tmo_nome')
            ->get()
            ->getResult();
    }

    public function getTipoMovimentacaoDepositos($dorig, $ddest)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_relac_lista');
        $builder->select('*');
        $builder->where('dep_codorigem', $dorig);
        $builder->where('dep_coddestino', $ddest);
        $builder->orderBy('tmo_ativo, tmo_nome');
        return $builder->get()->getResult();
    }

    public function getTipoMovimentacaoSearch($termo)
    {
        $array = ['tmo_nome' => $termo . '%'];
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_relac_lista');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResult();
    }

    public function getTipoMovimentacaoMovimentos($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_tipo_movimentacao_movimento');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        return $builder->get()->getResult();
    }

    public function getTipoMovimentacaoPermissao($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_tipo_movimentacao_permissao');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        return $builder->get()->getResult();
    }

    public function getTipoMovimentacaoTemPermissao($tmo_id = false, $prf_id = false)
    {
        if (!$tmo_id || !$prf_id) {
            return false;
        }
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_tipo_movimentacao_permissao');
        $builder->select('*');
        $builder->where('tmo_id', $tmo_id);
        $builder->where('prf_id', $prf_id);
        return $builder->get()->getResult();
    }

    public function getTipoMovimentacao($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_relac_lista');
        $builder->select('*');

        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        $builder->orderBy('tmo_ativo, tmo_nome');

        return $builder->get()->getResult();
    }

    /**
     * Esse método retorna apenas os Tipos de Movimentação que estão disponíveis para Requisição
     * 
     *
     */
    public function getTipoMovimentacaoParaRequisicao($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_para_requisicao');
        $builder->select('*');

        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        $builder->where('tmo_requisicao', 'S');
        $builder->orderBy('tmo_ativo, tmo_nome');

        return $builder->get()->getResult();
    }

    /** Usado para retornar 1 ou mais de 1 movimentos */
    public function getTipoMovimentacaoMovim($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_movimento_relac');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        return $builder->get()->getResult();
    }
}
