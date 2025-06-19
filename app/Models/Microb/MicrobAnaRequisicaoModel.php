<?php

namespace App\Models\Microb;

use App\Libraries\MyCampo;
use App\Models\ArquivoMonModel;
use App\Models\CommonModel;
use App\Models\Config\ConfigCorModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutFabricanteModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutOrigemModel;
use App\Models\Produt\ProdutProdutoModel;
use CodeIgniter\Model;

class MicrobAnaRequisicaoModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_mic_requisicao';
    protected $view             = 'vw_pro_mic_requisicao_relac';
    protected $primaryKey       = 'req_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'req_id',
        'req_data',
        'req_lotemb',
        'usu_id',
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

    public function getListaRequisicao($req_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_requisicao_relac');

        $builder->select('*');
        if ($req_id) {
            $builder->where('req_id', $req_id);
        }
        $builder->orderBy('pro_despro');
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery());
        return $ret;
    }

    public function defCampos($dados = false, $show = false)
    {
        $opcoes         = new CommonModel();
        $ret = [];

        $id           =  new MyCampo('pro_mic_requisicao', 'req_id', false);
        $id->valor    = isset($dados['req_id']) ? $dados['req_id'] : '';
        $ret['req_id']    = $id->crOculto();

        if (isset($dados['req_lotemb'])) {
            $lote = $dados['req_lotemb'];
        } else if (isset($dados['ana_lotemb'])) {
            $lote = $dados['ana_lotemb'];
        } else {
            $lote = '';
        }
        // debug($dados['ana_descmetodo'], true);

        $lmb            =  new MyCampo('pro_mic_requisicao', 'req_lotemb', false);
        $lmb->valor     = $lote;
        $lmb->tipo      = 'sonumero';
        $lmb->maxLength = 9;
        $lmb->largura   = 100;
        $lmb->size      = 9;
        $lmb->leitura   = true;
        if ($lote != "") {
            $ret['req_lotemb']    = $lmb->crInput();
        } else {
            $ret['req_lotemb']    = $lmb->crOculto();
        }

        $met            =  new MyCampo('pro_mic_analise', 'ana_descmetodo', false);
        $met->valor     = isset($dados['ana_descmetodo']) ? $dados['ana_descmetodo'] : '';
        $met->maxLength = 40;
        $met->largura   = 100;
        $met->size      = 40;
        $met->leitura   = true;
        $ret['ana_descmetodo']    = $met->crInput();

        return $ret;
    }
}
