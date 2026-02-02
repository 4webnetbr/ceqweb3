<?php

namespace App\Models\Produt;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutOrigemModel;
use App\Models\Produt\ProdutProdutoModel;
use CodeIgniter\Model;

class ProdutIngredienteModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_ingrediente';
    protected $view             = 'vw_pro_ingrediente_lista_relac';
    protected $primaryKey       = 'ing_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'ing_id',
        'ing_nome',
        'cla_id',
        'ing_ativo',
    ];

    protected $deletedField  = 'ing_excluido';

    protected $validationRules = [
        'ing_nome' => 'required|min_length[5]|max_length[50]|isUniqueValue[dbProduto.pro_ingrediente.ing_nome, ing_id]',
    ];

    protected $validationMessages = [
        'ing_nome' => [
            'required' => 'O campo nome é Obrigatório.',
            'min_length' => 'Digite pelo menos 5 Caracteres.',
            'max_length' => 'Número de Caracteres excedido.',
            'isUniqueValue' =>  '8'
        ],

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

    public function getIngredienteLista($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_lista_relac');
        $builder->select('*');
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        $builder->orderBy('ing_ativo, ing_nome');
        return $builder->get()->getResultArray();
    }

    public function getIngrediente($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_relac');
        $builder->select('*');
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        $builder->where('ing_ativo', 'A');
        $builder->orderBy('ing_ativo, ing_nome');
        return $builder->get()->getResultArray();
    }

    public function getIngredienteSearch($termo)
    {
        $array = ['ing_nome' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_relac');
        $builder->select('*');
        $builder->where('ing_ativo', 'A');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function getIngredienteProdutos($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ing_produto');
        $builder->select('*');
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getProdutoIngrediente($produto)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ing_produto');
        $builder->select('*');
        $builder->where('pro_id', $produto);
        return $builder->get()->getResultArray();
    }

    public function getIngredienteClasse($classe = false, $ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ingrediente');
        $builder->select('*');
        if ($classe) {
            $builder->where('cla_id', $classe);
        }
        return $builder->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $id           =  new MyCampo('pro_ingrediente', 'ing_id', false);
        $id->valor    = (isset($dados['ing_id'])) ? $dados['ing_id'] : '';
        $id->leitura  = $show;
        $ret['ing_id']    = $id->crOculto();

        $nome                 = new MyCampo('pro_ingrediente', 'ing_nome', false);
        $nome->valor          = (isset($dados['ing_nome'])) ? $dados['ing_nome'] : '';
        $nome->leitura        = $show;
        $nome->obrigatorio    = true;
        $ret['ing_nome']          = $nome->crInput();

        $classes = new ProdutClasseModel();
        $lst_classes = $classes->getClasse();
        $opc_classes = array_column($lst_classes, 'cla_nome', 'cla_id');

        $cla_id = new MyCampo('oco_tpo_pro_classe', 'cla_id', false);
        $cla_id->valor          = (isset($dados['cla_id'])) ? $dados['cla_id'] : '';
        $cla_id->obrigatorio    = true;
        $cla_id->selecionado    = $cla_id->valor;
        $cla_id->opcoes         = $opc_classes;
        $cla_id->largura        = 50;
        $ret['cla_id']          = $cla_id->crSelect();

        return $ret;
    }


    public function defCamposProduto($dados = false, $selec = false, $show = false)
    {
        // debug($dados, true);
        $produtos       = new ProdutProdutoModel();
        if (isset($dados['cla_id'])) {
            $lst_produts    = $produtos->getProdutoClasse($dados['cla_id'], $dados['ing_id']);
            $tipo = $dados['ing_id'];
        } else {
            $lst_produts    = $produtos->getProdutoSemIngrediente();
            $tipo = 0;
        }
        $opc_prods      = array_column($lst_produts, 'pro_despro', 'pro_id');

        if (isset($selec['pro_id'])) {
            $prodsele = array_values($selec['pro_id']);
        } else {
            $prodsele = [];
        }
        $prod                   = new MyCampo('pro_ing_produto', 'pro_id', false);
        $prod->valor            = (isset($selec['pro_id'])) ? implode(",", $selec['pro_id']) : '';
        $prod->selecionado      = $prodsele;
        $prod->opcoes           = $opc_prods;
        $prod->valid            = ($tipo > 0) ? true : false;
        $prod->largura          = 50;
        $prod->pai              = "cla_id";
        $prod->urlbusca         = base_url('buscas/buscaProdutoClasseSemIngrediente/' . $tipo);
        $ret['pro_id']          = $prod->crDependeMultiplo();

        return $ret;
    }
}
