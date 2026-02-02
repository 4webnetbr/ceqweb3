<?php

namespace App\Models\Produt;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Produto\EntProdutClasse;

class ProdutClasseModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_classe';
    protected $view             = 'vw_pro_classe_lista_relac';
    protected $primaryKey       = 'cla_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = EntProdutClasse::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'cla_id',
        'cla_nome',
        'cla_requisicao',
        'cla_insvis',
        'cla_insvisconf',
        'cla_formula',
        'cla_micro',
        'cla_metodanalise',
        'cla_ativo',
        'cla_ordem',
        'cla_estdataatual',
        'cla_dash_consumo',
        'cla_gestaoestoque',
        'cla_cabecalho',
        'cla_rodape',
        'cla_deposito'
    ];

    protected $deletedField  = 'cla_excluido';

    protected $validationRules = [
        'cla_nome' => "required|min_length[5]|max_length[50]",
    ];


    protected $validationMessages = [
        'cla_nome' => [
            'required' => 'O campo nome é Obrigatório.',
            'min_length' => 'Digite pelo menos 5 Caracteres.',
            'max_length' => 'Número de Caracteres excedido.',
            'isUniqueValue' => '8'
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


    public function getClasse($cla_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');

        // Filtra por classe específica, se informado
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->orderBy('cla_ativo, cla_ordem, cla_nome');
        
        return $builder->get()->getResult(); 
    }
    public function getClassePorId($cla_id)
{
    $db = db_connect('dbProduto');
    return $db->table('vw_pro_classe_lista_relac')   // ou a tabela/view certa da classe
        ->where('cla_id', $cla_id)
        ->get()
        ->getRow(); // ✅ retorna 1 stdClass ou null
}
    
        public function getClasseOrdem($cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');

        // Filtra por classe específica, se informado
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->orderBy('cla_ordem, cla_nome');

        return $builder->get()->getResult(); 
    }
    
        public function getUltimaOrdemClasse()
    {
        $db = db_connect('dbProduto');
        // Tabela base de classes
        $builder = $db->table('pro_classe');
        $builder->select('MAX(cla_ordem) as ultima');
    
        return $builder->get()->getResult(); 
    }
    
        public function getClasseSearch($termo)
    {
        $db = db_connect('dbProduto');
        // Utiliza a VIEW de classes
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');
        $builder->like(['cla_nome' => $termo . '%']);
    
        return $builder->get()->getResult(); 
    }
    
        public function getClasseClassificacao($cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_classe_classificacao');
        $builder->select('*');
        // Filtra por classe específica, se informado
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->orderBy('pcl_ordem');

        return $builder->get()->getResult(); 
    }
    
        public function getClassificacaoClasse($origem, $familia)
    {
        $db = db_connect('dbProduto');
        // Utiliza a VIEW de classes relacionadas
        $builder = $db->table('vw_pro_classe_relac');
        $builder->select('*');
        $builder->where('ori_codOri', $origem);
        $builder->where('fam_codFam', $familia);
    
        return $builder->get()->getResult(); 
    }
}
