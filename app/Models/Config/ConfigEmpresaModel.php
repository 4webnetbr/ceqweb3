<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigEmpresaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_empresa';
    protected $view             = 'cfg_empresa';
    protected $primaryKey       = 'emp_codfil';
    protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['emp_codfil',
                                    'emp_codemp',
                                    'emp_nomfil',
                                    'emp_sigfil',
                                    'emp_numcgc',
                                    'emp_insest'
    
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

    public function getEmpresa($emp_id = false)
    {
        $this->builder()->select('*');
        if ($emp_id) {
            $this->builder()->where('emp_id', $emp_id);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getEmpresasSearch($termo)
    {
        $array = ['emp_nome' => $termo . '%'];
        $this->builder()->select(['emp_id','emp_nomfil','emp_sigfil']);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $nome           =  new MyCampo('cfg_empresa', 'emp_nomfil');
        $nome->valor    = (isset($dados['emp_nomfil'])) ? $dados['emp_nomfil'] : '';
        $nome->size     = 100;
        $nome->tamanho  = 100;
        $nome->largura  =  100;
        $nome->leitura  = $show;
        $ret['emp_nomfil'] = $nome->crInput();

        $apel           =  new MyCampo('cfg_empresa', 'emp_sigfil');
        $apel->valor    = (isset($dados['emp_sigfil'])) ? $dados['emp_sigfil'] : '';
        $apel->leitura  = $show;
        $ret['emp_sigfil'] = $apel->crInput();

        $code           =  new MyCampo('cfg_empresa', 'emp_codemp');
        $code->valor    = (isset($dados['emp_codemp'])) ? $dados['emp_codemp'] : '';
        $code->leitura  = $show;
        $ret['emp_codemp'] = $code->crInput();

        $codf           =  new MyCampo('cfg_empresa', 'emp_codfil', true);
        $codf->valor    = (isset($dados['emp_codfil'])) ? $dados['emp_codfil'] : '';
        $codf->leitura  = $show;
        $ret['emp_codfil'] = $codf->crInput();

        $cnpj           =  new MyCampo('cfg_empresa', 'emp_numcgc');
        $cnpj->valor    = (isset($dados['emp_numcgc'])) ? $dados['emp_numcgc'] : '';
        $cnpj->leitura  = $show;
        $ret['emp_numcgc'] = $cnpj->crInput();

        $inest           =  new MyCampo('cfg_empresa', 'emp_insest');
        $inest->valor    = (isset($dados['emp_insest'])) ? $dados['emp_insest'] : '';
        $inest->leitura  = $show;
        $ret['emp_insest'] = $inest->crInput();

        return $ret;
    }    

}
