<?php

namespace App\Models\Produt;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Produto\EntProdutos;

class ProdutLoteModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_sap_lote';
    protected $view             = 'vw_pro_sap_lote_relac';
    protected $primaryKey       = 'lot_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntProdutos::class;
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

    public function getLote($lot_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');

        // Filtra por ID do lote
        if ($lot_id) {
            $builder->where('lot_id', $lot_id);
        }
        return $builder->get()->getResult();
    }

    public function getLoteId($lot_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_lote');
        $builder->select('*');

        // Filtra por ID do lote
        if ($lot_id) {
            $builder->where('lot_lote', $lot_id);
        }
        return $builder->get()->getResult();
    }

    public function getLoteValidade($validade = false)
    {
        $db = db_connect('dbProduto');

        $builder = $db->table('pro_sap_lote');
        $builder->select('*');

        if (!empty($validade)) {
            $builder->where('lot_validade', $validade);
        }

        return $builder->get()->getResult();
    }


    public function getLoteCodbar($codbar = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');

        // Filtra pelo código de barras, se informado
        if ($codbar) {
            $builder->where('lot_codbar', $codbar);
        }
        return $builder->get()->getResult();
    }

    public function getUltimoLote()
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('pro_sap_lote');
        $builder->selectMax('lot_codbar');
        return $builder->get()->getResult();
    }

    public function getLoteSearch($termo)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');

        $builder->select('*');

        $builder->groupStart()
            ->like('lot_lote', $termo)
            ->orLike('lot_codbar', $termo)
            ->groupEnd();

        $builder->where('stt_id', 9);

        return $builder->get()->getResult();
    }

    public function getLoteClasse($termo, $classe)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');

        $builder->select('*');

        $builder->groupStart()
            ->like('lot_lote', $termo)
            ->orLike('lot_codbar', $termo)
            ->groupEnd();

        $builder->whereIn('cla_id', $classe);
        $builder->where('stt_id', 9);
        // $ret = $builder->get()->getResult();
        // debug($db->getLastQuery());
        return $ret;
    }

    public function getLoteCodproLote($codpro, $lote)
    {
        // Monta filtro LIKE para lote
        $array = ['lot_lote' => $lote];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');

        // Filtra pelo código do produto, se informado
        if ($codpro) {
            $builder->where('lot_codpro', $codpro);
        }

        $builder->groupStart();
        $builder->like('lot_lote', $lote);
        $builder->orLike('lot_codbar', $lote);
        $builder->groupEnd();

        $ret = $builder->get()->getResult();
        // debug($builder->getCompiledSelect());

        return $ret;
    }

    public function getLoteProduto($termo)
    {
        // Monta filtro por código do produto
        $array = ['lot_codpro' => $termo];
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->where($array);

        // Retorna os resultados
        return $builder->get()->getResult();
    }
    public function getLoteIn($termo)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');

        // Utiliza a VIEW de lotes
        $builder = $db->table('vw_pro_sap_lote_relac');
        $builder->select('*');
        $builder->whereIn('lot_lote', $termo);

        return $builder->get()->getResult();
    }

    public function getBuscaLote($lot_id)
    {
        if (empty($lot_id)) {
            return '';
        }

        $db = db_connect('dbProduto');

        $builder = $db->table('pro_sap_lote');
        $builder->select('lot_lote');
        $builder->where('lot_id', $lot_id);

        $row = $builder->get()->getRow();

        return $row->lot_lote ?? '';
    }
}
