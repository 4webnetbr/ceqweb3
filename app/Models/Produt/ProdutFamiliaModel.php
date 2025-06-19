<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ProdutFamiliaModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_familia';
    protected $view             = 'vw_pro_sap_familia_relac';
    protected $primaryKey       = 'fam_codFam';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'fam_codFam',
                                    'fam_desFam',
                                    'ori_codOri',
                                    'fam_codDescricao',
    
    
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

    public function getFamilia($fam_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        if ($fam_id) {
            $builder->where('fam_codFam', $fam_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getFamiliaSearch($termo)
    {
        $array = ['fam_desFam' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function getFamiliaOrigem($termo)
    {
        $array = ['ori_codOri' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_familia_relac');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $cfam           =  new MyCampo('pro_sap_familia', 'fam_codFam', true);
        $cfam->valor    = (isset($dados['fam_codFam'])) ? $dados['fam_codFam'] : '';
        $cfam->obrigatorio = true;
        $cfam->leitura  = $show;
        $ret['fam_codFam'] = $cfam->crInput();

        $dfam           =  new MyCampo('pro_sap_familia', 'fam_desFam');
        $dfam->valor    = (isset($dados['fam_desFam'])) ? $dados['fam_desFam'] : '';
        $dfam->obrigatorio = true;
        $dfam->leitura  = $show;
        $ret['fam_desFam'] = $dfam->crInput();

        $cori           =  new MyCampo('pro_sap_origem', 'ori_codDescricao', true);
        $cori->valor    = (isset($dados['ori_codDescricao'])) ? $dados['ori_codDescricao'] : '';
        $cori->label    = 'Origem';
        $cori->obrigatorio = true;
        $cori->leitura  = $show;
        $ret['ori_codOri'] = $cori->crInput();

        $cdes           =  new MyCampo('pro_sap_familia', 'fam_codDescricao');
        $cdes->valor    = (isset($dados['fam_codDescricao'])) ? $dados['fam_codDescricao'] : '';
        $cdes->obrigatorio = true;
        $cdes->leitura  = $show;
        $ret['fam_codDescricao'] = $cdes->crInput();
        
        return $ret;
    }    

}
