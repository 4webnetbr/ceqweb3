<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class EntOcoOcorrencia extends Entity
{
    protected $attributes = [
        'oco_id'        => null,
        'tpo_id'        => null,
        'moc_id'        => null,
        'moc_nome'      => null,
        'tpa_id'        => null,
        'oco_descricao' => null,
        'lot_lote'      => null,
        'pro_despro'    => null,
        'oco_qtd'       => null,
        'oco_data'      => null,
        'stt_id'        => null,
        'tmo_id'        => null,
        'oco_justi'     => null,
    ];

    protected $casts = [
        'oco_id'   => 'integer',
        'tpo_id'   => 'integer',
        'moc_id'   => 'integer',
        'tpa_id'   => 'integer',
        'oco_qtd'  => 'integer',
        'stt_id'   => 'integer',
        'tmo_id'   => 'integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    public function defCampos($dados = false)

    // DADOS GERAIS
    {
        $ret = [];

        // TIPO DE OCORRÊNCIA
        $modelTipo = new OcorreTipoOcorrenciaModel();
        $tipos = $modelTipo->where('tpo_ativo', 'A')->findAll();
        
        foreach ($tipos as $t) {
            $opcoes[$t->tpo_id] = $t->tpo_nome;
        }
        
        $tipo              = new MyCampo('oco_ocorrencia', 'tpo_id');
        $tipo->opcoes      = $opcoes;
        $tipo->valor       = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $tipo->label       = 'Tipo de Ocorrência';
        $tipo->obrigatorio = true;
        $tipo->dispForm    = '2col';
        $tipo->largura     = 58;
        
        $ret['tpo_id'] = $tipo->crSelect();


       // AÇÃO
       $acao = new MyCampo('oco_ocorrencia', 'tpa_id');
       $acao->valor       = $dados['tpa_id'] ?? '';
       $acao->opcoes      = [];
       $acao->label       = 'Ação';
       $acao->dispForm    = '2col';
       $acao->largura     = 58;
       
       $acao->pai      = 'tpo_id';
       $acao->urlbusca = base_url('Buscas/buscaAcoesPorTipo');
       
       $ret['tpa_id'] = $acao->crDepende();
       

        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');  
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';

        $ret['oco_descricao'] = $desc->crTexto();


        // LOTE
        $lote              = new MyCampo('pro_sap_lote', 'lot_lote'); 
        $lote->valor       = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->obrigatorio = true;
        $lote->size        = 54;
        $lote->funcBlur    = "buscaLoteProduto(this,'".base_url('/buscas/buscaProdutoporLote')."')";
        $ret['lot_lote'] = $lote->crInput();


        // PRODUTO 
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $produto->dispForm = '2col';
        $produto->size     = 54;
        $produto->leitura  = true;
        $ret['pro_despro'] = $produto->crInput();

        // QUANTIDADE
        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd'); 
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->label        = 'Quantidade';
        $qtd->dispForm     = '2col';
        $qtd->minimo       = 1;
        $qtd->step         = 1;
        $qtd->largura      = 10;
        $qtd->obrigatorio  = true;

        $ret['oco_qtd'] = $qtd->crInput();
        
        // DATA 
        $data              = new MyCampo('oco_ocorrencia', 'oco_data');
        $data->valor       = $dados['oco_data'] ?? date('Y-m-d\TH:i'); 
        $data->label       = 'Data da Ocorrência';
        $data->dispForm    = '2col';
        $data->leitura     = true;
        $data->largura     = 30;

        $ret['oco_data'] = $data->crInput();

        return $ret;
    }
}
