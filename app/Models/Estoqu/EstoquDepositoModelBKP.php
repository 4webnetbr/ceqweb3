<?php

namespace App\Models\Estoqu;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquDepositoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_sap_deposito';
    protected $view             = 'est_sap_deposito';
    protected $viewlista        = 'est_sap_deposito';
    protected $viewoutra        = 'est_sap_deposito';
    protected $primaryKey       = 'dep_codDep';
    // protected $useAutoIncremodt = false;


    protected $returnType       = 'array';
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

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        // debug($data, true);
        if(isset($data['id'][0])){
            $logdb = new LogMonModel();
            $registro = $data['id'][0];
            $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
            return $data;
        }
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getDeposito($dep_id = false)
    {
        $this->builder()->select('*');
        if ($dep_id) {
            $this->builder()->where('dep_codDep', $dep_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getDepositoPadrao()
    {
        $this->builder()->select('*');
        $this->builder()->where('dep_padrao', 'S');
        $this->builder()->limit(1);
        return $this->builder()->get()->getResultArray();
    }

    public function getDepositoSearch($termo)
    {
        $array = ['dep_desDep' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function getDepositoCodDep($termo)
    {
        $array = ['dep_codDep' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function getDestino($dep_id = false)
    {
        $this->builder()->select('*');
        if ($dep_id) {
            $this->builder()->where('dep_codDep !=', $dep_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $cdep           =  new MyCampo('est_sap_deposito', 'dep_codDep', true);
        $cdep->valor    = (isset($dados['dep_codDep'])) ? $dados['dep_codDep'] : '';
        $cdep->leitura  = $show;
        $ret['dep_codDep'] = $cdep->crInput();

        $ddep           =  new MyCampo('est_sap_deposito', 'dep_desDep');
        $ddep->valor    = (isset($dados['dep_desDep'])) ? $dados['dep_desDep'] : '';
        $ddep->leitura  = $show;
        $ret['dep_desDep'] = $ddep->crInput();

        $acng           =  new MyCampo('est_sap_deposito', 'dep_aceNeg');
        $acng->valor = $acng->selecionado    = (isset($dados['dep_aceNeg'])) ? $dados['dep_aceNeg'] : 'N';
        $acng->leitura  = $show;
        $acng->opcoes   = $simnao;
        $ret['dep_aceNeg'] = $acng->cr2opcoes();

        $padr           =  new MyCampo('est_sap_deposito', 'dep_padrao');
        $padr->valor = $padr->selecionado    = (isset($dados['dep_padrao'])) ? $dados['dep_padrao'] : 'N';
        $padr->leitura  = false;
        $padr->opcoes   = $simnao;
        $ret['dep_padrao'] = $padr->cr2opcoes();

        $cdes           =  new MyCampo('est_sap_deposito', 'dep_codDescricao');
        $cdes->valor    = (isset($dados['dep_codDescricao'])) ? $dados['dep_codDescricao'] : '';
        $cdes->leitura  = $show;
        $ret['dep_codDescricao'] = $cdes->crInput();

        $depositos = $this->getDeposito();
        $reservas = array_column($depositos, 'dep_codDescricao','dep_codDep');

        $rese           =  new MyCampo('est_sap_deposito', 'dep_reserva');
        $rese->valor = $rese->selecionado    = (isset($dados['dep_reserva'])) ? $dados['dep_reserva'] : '';
        $rese->leitura  = false;
        $rese->opcoes   = $reservas;
        $rese->largura  = 50;
        $ret['dep_reserva'] = $rese->crSelect();

        return $ret;
    }
}
