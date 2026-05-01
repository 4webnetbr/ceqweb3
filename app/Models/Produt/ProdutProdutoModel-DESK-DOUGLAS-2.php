<?php

namespace App\Models\Produt;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Produto\EntProdutProduto;

class ProdutProdutoModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_produto';
    protected $view             = 'vw_pro_sap_produto_relac';
    protected $primaryKey       = 'pro_id';

    protected $returnType       = EntProdutProduto::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'pro_id',
        'pro_codemp',
        'pro_codpro',
        'pro_despro',
        'fab_codFab',
        'ori_codOri',
        'fam_codFam',
        'cla_id',
        'pro_cplpro',
        'pro_ctrlot',
        'pro_qtdemb',
        'pro_codbar_fabricante',
        'pro_informacoes',
        'pro_ativo',
        'stt_id',
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


    public function getListaProduto($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        $builder->orderBy('stt_ordem, pro_ativo, pro_despro, cla_ordem, pro_codpro');

        return $builder->get()->getResult();
    }

    public function getProduto($pro_id = false, $ativo = true)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        if ($ativo) {
            $builder->where('pro_ativo', 'A');
        }
        $builder->where('stt_id >', 2);

        return $builder->get()->getResult();
    }

    public function getProdutoCod($pro_cod = false, $dispon = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_cod) {
            $builder->where('TRIM(pro_codpro)', trim($pro_cod));
        }
        if ($dispon) {
            $builder->where('stt_disponivel', $dispon);
        }

        return $builder->get()->getResult();
    }

    public function getProdutoCodLista($pro_cod = false, $dispon = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_cod) {
            $builder->whereIn('pro_codpro', $pro_cod);
        }
        if ($dispon) {
            $builder->where('stt_disponivel', $dispon);
        }

        return $builder->get()->getResult();
    }

    public function getProdutoClasse($classe = false, $ing = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($classe) {
            $builder->where('cla_id', $classe);
        }
        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);

        if ($ing) {
            $builder->groupStart()
                ->where('ing_id', null)
                ->orWhere('ing_id', $ing)
                ->groupEnd();
        } else {
            $builder->groupStart()
                ->where('ing_id', null)
                ->orWhere('cla_ing_id = cla_id')
                ->groupEnd();
        }
        $builder->orderBy('cla_ordem', 'pro_nome');

        return $builder->get()->getResult();
    }

    public function getProdutoSemIngrediente($pro_id = false, $cla_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        if ($cla_id) {
            $builder->where('cla_id', $cla_id);
        }

        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);
        $builder->where('ing_id IS NULL');

        return $builder->get()->getResult();
    }

    public function getProdutoComIngrediente($ing_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        if ($ing_id) {
            $builder->where('ing_id', $ing_id);
        }

        $builder->where('pro_ativo', 'A');
        $builder->where('stt_id >', 2);

        return $builder->get()->getResult();
    }

    public function getProdutoSearch($termo)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_produto_relac');

        $builder->select('*');
        $builder->where('pro_ativo', 'A');
        $builder->like(['pro_desPro' => $termo . '%']);

        return $builder->get()->getResult();
    }

    public function getProdutoCeqweb($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_ceq_produto');

        $builder->select('*');
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }

        return $builder->get()->getResult();
    }

    public function getProdutoEstoque($pro_id = false, $deposito = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_est_ceq_relac');

        $builder->select('*');

        if ($pro_id) {
            is_array($pro_id)
                ? $builder->whereIn('pro_id', $pro_id)
                : $builder->where('pro_id', $pro_id);
        }

        if ($deposito) {
            $builder->where("FIND_IN_SET('$deposito', dep_codDep) >", 0);
            // $builder->like('dep_codDep', $deposito);
        }
        // debug($builder->getCompiledSelect());
        // $cs = $builder->getCompiledSelect();
        $ret = $builder->get()->getResult();

        return $ret;
    }

    public function getProdutoEstoqueCeqweb($pro_id = false, $deposito = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_ceq_est_relac');

        $builder->select('*');

        if ($pro_id) {
            is_array($pro_id)
                ? $builder->whereIn('pro_id', $pro_id)
                : $builder->where('pro_id', $pro_id);
        }

        if ($deposito) {
            $builder->where("FIND_IN_SET('$deposito', prc_deposito) >", 0);
            // $builder->like('prc_deposito', $deposito);
        }
        $builder->groupBy('pro_codpro');
		// debug($builder->getCompiledSelect()); 
		// $cs = $builder->getCompiledSelect();

        return $builder->get()->getResult();
    }

    public function getProdutoOrigemFamiliaClasse($origem = false, $familia = false, $classe = false)
    {
        if (!$origem || !$familia || !$classe) {
            return false;
        }

        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_produto');
        $familiasArray = array_map(
            static fn(string $item): string => trim($item),
            explode(',', $familia)
        );

        $builder->select('*')
            ->where('ori_codOri', $origem)
            ->whereIn('fam_codFam', $familiasArray)
            ->where('cla_id', $classe);

        // debug($builder->getCompiledSelect());
        return $builder->get()->getResult();
    }

    public function getFabricanteProduto($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');

        $builder->select('*')
            ->where('pro_codPro', $pro_id);

        return $builder->get()->getResult();
    }

    public function getFabricanteProdutosArray($pro_id = false)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_prod_fabric');

        $builder->select('*')
            ->whereIn('pro_codPro', $pro_id);

        return $builder->get()->getResult();
    }

    public function getProdutoRequisicao($deposito = false, $produto = false)
    {
        $db = db_connect('dbProduto');
        // $builder = $db->table('vw_classe_produto_lote_semlote_info3');
        $builder = $db->table('vw_produtos_para_requisicao');

        $builder->select('*');

        if ($deposito) {
            $builder->like('prc_deposito', $deposito);
        }
        if ($produto) {
            $builder->whereIn('pro_id', $produto);
        }
        $builder->orderBy('cla_ordem, pro_despro, pro_codpro, lot_validade');
        $builder->groupBy('pro_codpro,lot_lote');
        // debug($builder->getCompiledSelect());
        // $cs = $builder->getCompiledSelect();
        $ret = $builder->get()->getResult();

        return $ret;
    }
}
