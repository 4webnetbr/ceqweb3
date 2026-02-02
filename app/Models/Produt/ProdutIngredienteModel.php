<?php

namespace App\Models\Produt;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Produto\EntProdutIngrediente;

class ProdutIngredienteModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_ingrediente';
    protected $view             = 'vw_pro_ingrediente_lista_relac';
    protected $primaryKey       = 'ing_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = EntProdutIngrediente::class;
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
            'required'      => 'O campo nome é Obrigatório.',
            'min_length'    => 'Digite pelo menos 5 Caracteres.',
            'max_length'    => 'Número de Caracteres excedido.',
            'isUniqueValue' =>  '8'
        ],

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

    public function getIngredienteLista($ing_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_lista_relac');
        $builder->select('*');

        // Filtra por ingrediente específico, se informado
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        $builder->orderBy('ing_ativo, ing_nome');

        return $builder->get()->getResult(); 
    }

    public function getIngrediente($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_relac');
        $builder->select('*');

        // Filtra por ingrediente específico, se informado
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }
        $builder->where('ing_ativo', 'A');
        $builder->orderBy('ing_ativo, ing_nome');
        
        return $builder->get()->getResult(); 
    }

    public function getIngredienteSearch($termo)
    {
        // Monta filtro LIKE para busca
        $array = ['ing_nome' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ingrediente_relac');
        $builder->select('*');
        $builder->where('ing_ativo', 'A');
        $builder->like($array);

        return $builder->get()->getResult(); 
    }

    public function getIngredienteProdutos($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ing_produto');
        $builder->select('*');

        // Filtra pelo ingrediente, se informado
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }

        return $builder->get()->getResult(); 
    }

    public function getProdutoIngrediente($produto)
    {
        $db = db_connect('dbProduto');
        // Tabela de vínculo ingrediente x produto
        $builder = $db->table('pro_ing_produto');
        $builder->select('*');
        $builder->where('pro_id', $produto);
        return $builder->get()->getResult(); 
    }

    public function getIngredienteClasse($classe = false, $ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ingrediente');
        $builder->select('*');

        // Filtra pela classe do produto, se informada
        if ($classe) {
            $builder->where('cla_id', $classe);
        }
        return $builder->get()->getResult(); 
    }
}
