<?php

namespace App\Models\Estoqu;

use App\Entities\Estoque\EntDeposito;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquDepositoModel extends Model
{
    protected $DBGroup     = 'dbEstoque';
    protected $table       = 'est_sap_deposito';
    protected $view        = 'est_sap_deposito';
    protected $viewlista   = 'est_sap_deposito';
    protected $viewoutra   = 'est_sap_deposito';
    protected $primaryKey  = 'dep_codDep';

    protected $returnType       = EntDeposito::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'dep_codDep',
        'dep_desDep',
        'dep_aceNeg',
        'dep_codDescricao',
        'dep_padrao',
        'dep_reserva',
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
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        (new LogMonModel())->insertLog($this->table, 'Alteração', $id, $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }

    public function getDeposito($dep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');

        if ($dep_id) {
            $builder->where('dep_codDep', $dep_id);
        }
        return $builder->get()->getResult();
    }



    public function getDepositoPadrao()
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');

        $builder->where('dep_padrao', 'S'); // Filtra apenas o depósito marcado como padrão
        $builder->limit(1);                 // Garante que apenas um registro seja retornado
        return $builder->get()->getResult();
    }

    public function getDepositoSearch($termo)
    {
        $array = ['dep_desDep' => $termo . '%'];    // Define filtro de busca pela descrição do depósito

        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->like($array);             // Aplica busca com LIKE para autocomplete/pesquisa

        return $builder->get()->getResult();
    }

    public function getDepositoCodDep($termo)
    {
        $array = ['dep_codDep' => $termo . '%']; // Define filtro de busca pelo código do depósito

        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');
        $builder->like($array);          // Aplica busca com LIKE pelo código do depósito

        return $builder->get()->getResult();
    }

    public function getDestino($dep_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table($this->table);
        $builder->select('*');
        if ($dep_id) {
            $builder->where('dep_codDep !=', $dep_id); // exclui ele da lista de destinos possíveis
        }
        return $builder->get()->getResult();
    }
}
