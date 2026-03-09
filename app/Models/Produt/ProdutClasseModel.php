<?php

namespace App\Models\Produt;

use CodeIgniter\Model;
use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use App\Entities\Produto\EntProClasse;
use App\Models\Produt\ProdutOrigemModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquDepositoModel;

class ProdutClasseModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_classe';
    protected $view             = 'vw_pro_classe_lista_relac';
    protected $primaryKey       = 'cla_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = EntProClasse::class;
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

    public function getClasse($cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
            return $builder->get()->getFirstRow();
        }
        $builder->orderBy('cla_ativo, cla_ordem, cla_nome');

        return $builder->get()->getResult();
    }

    public function getClasseOrdem($cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->orderBy('cla_ordem, cla_nome');
        return $builder->get()->getResultArray();
    }

    public function getUltimaOrdemClasse()
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_classe');
        $builder->select('MAX(cla_ordem) as ultima');
        $builder->orderBy('cla_ordem');
        return $builder->get()->getResultArray();
    }

    public function getClasseSearch($termo)
    {
        $array = ['cla_nome' => $termo . '%'];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_lista_relac');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function getClasseClassificacao($cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_classe_classificacao');
        $builder->select('*');
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }
        $builder->orderBy('pcl_ordem');
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        return $builder->get()->getResultArray();
    }


    public function getClassificacaoClasse($origem, $familia)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_classe_relac');
        $builder->select('*');
        $builder->where('ori_codOri', $origem);
        $builder->where('fam_codFam', $familia);
        return $builder->get()->getResultArray();
    }
}
