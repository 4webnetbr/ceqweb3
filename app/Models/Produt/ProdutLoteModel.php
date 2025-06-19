<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ProdutLoteModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_lote';
    protected $view             = 'vw_pro_sap_lote_relac';
    protected $primaryKey       = 'lot_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'lot_id',
        'lot_codbar',
        'lot_codpro',
        'lot_lote',
        'lot_entrada',
        'lot_validade',
        'stt_id',
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

    public function getLote($lot_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        if ($lot_id) {
            $builder->where('lot_id', $lot_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getLoteCodbar($codbar = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        if ($codbar) {
            $builder->where('lot_codbar', $codbar);
        }
        return $builder->get()->getResultArray();
    }

    public function getUltimoLote()
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_lote');
        $builder->selectMax('lot_codbar');
        return $builder->get()->getResultArray();
    }

    public function getLoteSearch($termo)
    {
        $array = ['lot_lote' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->like($array);

        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());
        return $ret;
    }

    public function getLoteCodproLote($codpro, $lote)
    {
        $array = ['lot_lote' => $lote . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->where('lot_codpro', $codpro);
        $builder->like($array);

        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());
        return $ret;
    }

    public function getLoteProduto($termo)
    {
        $array = ['lot_codpro' => $termo];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->where($array);

        return $builder->get()->getResultArray();
    }
    public function getLoteIn($termo)
    {
        $array = ['lot_codpro' => $termo];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->whereIn('lot_lote', $termo);

        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());
        return $ret;
    }

    public function defCampos($dados = false, $show = false)
    {
        // debug($dados, true);
        $id           =  new MyCampo('pro_sap_lote', 'lot_id', false);
        $id->valor    = (isset($dados['lot_id'])) ? $dados['lot_id'] : '';
        $id->leitura  = $show;
        $ret['lot_id']    = $id->crOculto();

        $codb           =  new MyCampo('pro_sap_lote', 'lot_codbar', true);
        $codb->valor    = (isset($dados['lot_codbar'])) ? $dados['lot_codbar'] : '';
        $codb->obrigatorio = true;
        $codb->leitura  = $show;
        $ret['lot_codbar'] = $codb->crInput();

        $dcod           =  new MyCampo('pro_sap_lote', 'lot_codpro');
        $dcod->valor    = (isset($dados['lot_codpro'])) ? $dados['lot_codpro'] : '';
        $dcod->obrigatorio  = true;
        $dcod->leitura      = $show;
        $ret['lot_codpro']  = $dcod->crInput();

        $prod           =  new MyCampo('pro_sap_produto', 'pro_despro');
        $prod->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $prod->obrigatorio  = true;
        $prod->leitura      = $show;
        $ret['pro_despro']  = $prod->crInput();

        $lote           =  new MyCampo('pro_sap_lote', 'lot_lote', true);
        $lote->valor    = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->obrigatorio = true;
        $lote->leitura  = $show;
        $ret['lot_lote'] = $lote->crInput();

        $vali           =  new MyCampo('pro_sap_lote', 'lot_validade');
        $vali->valor    = (isset($dados['lot_validade'])) ? $dados['lot_validade'] : '';
        $vali->obrigatorio = true;
        $vali->leitura  = $show;
        $ret['lot_validade'] = $vali->crInput();

        $stat           =  new MyCampo();
        // $stat->id = $stat->nome = 'lot_status';
        $stat->label    = 'Status';
        $stat->valor    = fmtEtiquetaCorBst($dados['stt_cor'], $dados['stt_nome']);
        // $stat->obrigatorio = true;
        // $stat->leitura  = $show;
        $stat->largura     = 30;
        $ret['lot_status'] = $stat->crShow();

        return $ret;
    }
}
