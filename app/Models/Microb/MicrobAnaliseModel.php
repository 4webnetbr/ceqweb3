<?php

namespace App\Models\Microb;
use App\Models\LogMonModel;
use App\Entities\Microb\EntMicrobAnalise;

use CodeIgniter\Model;

class MicrobAnaliseModel extends Model
{
    protected $DBGroup          = 'dbProduto';
    protected $table            = 'pro_mic_analise';
    protected $view             = 'vw_pro_mic_analise_relac';
    protected $primaryKey       = 'ana_id';
    // protected $useAutoIncremodt = false;

    protected $returnType       = EntMicrobAnalise::class;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'ana_id',
        'pro_id',
        'lot_id',
        'ana_qtde',
        'ana_qtde_micro',
        'ana_data',
        'ana_laudo',
        'ana_obs',
        'ana_data_result',
        'ana_usu_id_result',
        'stt_id',
        'ana_liberarsemmicro',
        'ana_lotemb',
        'ana_datalotemb',
        'ana_descmetodo',
        'req_id',
        'ana_liberar',
        'ana_reprovar',
        'tmo_id',
        'tmo_id_rep'

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

    public function getListaAnalise($ana_id = false, $stt_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac_v2');
        $builder->select('*');
        // Filtra por análise específica, se informado
        if ($ana_id) {
            $builder->where('ana_id', $ana_id);
        }
        // Filtra por status, se informado
        if ($stt_id) {
            $builder->where('stt_id', $stt_id);
        }
        $builder->orderBy('stt_ordem, pro_despro');
    
        return $builder->get()->getResult(); 
    }

    public function getListaAnaliseSemReq($ana_id = false, $stt_id = false)
{
    // Conecta ao banco de produtos
    $db = db_connect('dbProduto');
    $builder = $db->table('vw_pro_mic_analise_relac_v2');

    // Seleciona todos os campos
    $builder->select('*');

    if ($ana_id) {
        $builder->where('ana_id', $ana_id);
    }
    if ($stt_id) {
        $builder->where('stt_id', $stt_id);
    }
    $builder->where('req_id IS NULL', null, false);
    $builder->orderBy('stt_ordem, pro_despro');

    return $builder->get()->getResult(); 
}

    public function getListaAnaliseComReq($req_id)
    {
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac_v2');
    
        $builder->select('*');
        $builder->where('req_id', $req_id);
        $builder->orderBy('stt_ordem, pro_despro');
    
        return $builder->get()->getResult(); 
    }

    public function atualizaEvento()
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $db->query('ALTER EVENT atualiza_produto_mic_analise_mv ON SCHEDULE AT NOW()');
        return;
    }


    public function getAnalise($pro_id = false, $fields = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac_v2');
    
        // Seleciona campos específicos ou todos
        $builder->select($fields ?: '*');
    
        // Filtra pelo produto, se informado
        if ($pro_id) {
            $builder->where('pro_id', $pro_id);
        }
        $builder->orderBy('stt_ordem, lot_entrada DESC');
    
        // Retorna os resultados
        return $builder->get()->getResult(); 
    }

    public function getAnaliseLotemb($lote = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac');
    
        $builder->select('*');
    
        // Filtra pelo lote informado
        if ($lote != '') {
            $builder->where('TRIM(ana_lotemb)', trim($lote));
        } else {
            $builder->groupStart();
            $builder->where('TRIM(ana_lotemb)', trim($lote));
            $builder->orWhere('ana_lotemb', null);
            $builder->groupEnd();
        }
    
        $builder->where('req_id', null);
        $builder->where('stt_id', 14);
    
        // Retorna os resultados
        return $builder->get()->getResult();
    }

    public function getAnaliseCod($pro_cod = false, $lot_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac');
        $builder->select('*');
    
        // Filtra pelo código do produto
        if ($pro_cod) {
            $builder->where('TRIM(pro_codpro)', trim($pro_cod));
        }
        // Filtra pelo lote
        if ($lot_id) {
            $builder->where('lot_id', $lot_id);
        }
    
        return $builder->get()->getResult(); 
    }

    public function getAnaliseCodIn($pro_cod = false, $lot_id = false)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac');
        $builder->select('*');
    
        // Filtra por lista de códigos de produto
        if ($pro_cod) {
            $builder->whereIn('TRIM(pro_codpro)', $pro_cod);
        }
        // Filtra por lista de lotes
        if ($lot_id) {
            $builder->whereIn('lot_id', $lot_id);
        }
    
        // Retorna os resultados
        return $builder->get()->getResult(); 
    }

    public function getAnaliseClasse($classe)
    {
        // Conecta ao banco de produtos
        $db = db_connect('dbProduto');
        $builder = $db->table('vw_pro_mic_analise_relac');
        $builder->select('*');
    
        // Filtra pela classe informada
        if ($classe) {
            $builder->where('cla_id', $classe);
        }
        $builder->where('pro_ativo', 'A');
    
        // Considera análises sem ingrediente ou compatíveis com a classe
        $builder->groupStart();
        $builder->where('ing_id IS NULL');
        $builder->orWhere('cla_ing_id = cla_id');
        $builder->groupEnd();
    
        return $builder->get()->getResult(); 
    }
    
}
