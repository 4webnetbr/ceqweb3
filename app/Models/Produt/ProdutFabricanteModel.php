<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ProdutFabricanteModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_fabricante';
    protected $view             = 'pro_sap_fabricante';
    protected $primaryKey       = 'fab_codFab';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [ 'fab_codFab',
                                    'fab_nomFab',
                                    'fab_apeFab',
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

    public function getFabricante($codFab = false)
    {
        $db = db_connect('dbProduto');
        $this->builder = $db->table('pro_sap_fabricante');
        $this->builder()->select('*');
        if ($codFab) {
            $this->builder()->where('fab_codFab', $codFab);
        }
        return $this->builder()->get()->getResultArray();
    }

    public function getFabricanteSearch($termo)
    {
        $array = ['fab_nomFab' => $termo . '%'];
        $db = db_connect('dbProduto');
        $this->builder = $db->table('pro_sap_fabricante');
        $this->builder()->select('*');
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $cori           =  new MyCampo('pro_sap_fabricante', 'fab_codFab', true);
        $cori->valor    = (isset($dados['fab_codFab'])) ? $dados['fab_codFab'] : '';
        $cori->obrigatorio = true;
        $cori->leitura  = $show;
        $ret['fab_codFab'] = $cori->crInput();

        $dori           =  new MyCampo('pro_sap_fabricante', 'fab_nomFab');
        $dori->valor    = (isset($dados['fab_nomFab'])) ? $dados['fab_nomFab'] : '';
        $dori->obrigatorio = true;
        $dori->leitura  = $show;
        $ret['fab_nomFab'] = $dori->crInput();

        $cdes           =  new MyCampo('pro_sap_fabricante', 'fab_apeFab');
        $cdes->valor    = (isset($dados['fab_apeFab'])) ? $dados['fab_apeFab'] : '';
        $cdes->obrigatorio = true;
        $cdes->leitura  = $show;
        $ret['fab_apeFab'] = $cdes->crInput();
        
        return $ret;
    }    

}
