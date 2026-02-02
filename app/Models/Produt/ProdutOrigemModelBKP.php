<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ProdutOrigemModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_origem';
    protected $view             = 'pro_sap_origem';
    protected $primaryKey       = 'ori_codOri';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'ori_codOri',
                                    'ori_desOri',
                                    'ori_codDescricao',
    
    
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
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
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

    public function getOrigem($ori_id = false)
    {
        $this->builder()->select('*');
        if ($ori_id) {
            $this->builder()->where('ori_codOri', $ori_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getOrigemSearch($termo)
    {
        $array = ['ori_desOri' => $termo . '%'];
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $cori           =  new MyCampo('pro_sap_origem', 'ori_codOri', true);
        $cori->valor    = (isset($dados['ori_codOri'])) ? $dados['ori_codOri'] : '';
        $cori->obrigatorio = true;
        $cori->leitura  = $show;
        $ret['ori_codOri'] = $cori->crInput();

        $dori           =  new MyCampo('pro_sap_origem', 'ori_desOri');
        $dori->valor    = (isset($dados['ori_desOri'])) ? $dados['ori_desOri'] : '';
        $dori->obrigatorio = true;
        $dori->leitura  = $show;
        $ret['ori_desOri'] = $dori->crInput();

        $cdes           =  new MyCampo('pro_sap_origem', 'ori_codDescricao');
        $cdes->valor    = (isset($dados['ori_codDescricao'])) ? $dados['ori_codDescricao'] : '';
        $cdes->obrigatorio = true;
        $cdes->leitura  = $show;
        $ret['ori_codDescricao'] = $cdes->crInput();
        
        return $ret;
    }    

}
