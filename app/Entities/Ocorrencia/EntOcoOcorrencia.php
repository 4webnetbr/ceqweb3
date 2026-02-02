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
        'tel_id'        => null,
        'moc_nome'      => null,
        'tpa_id'        => null,
        'oco_descricao' => null,
        'pro_id'        => null,
        'lot_id'        => null,
        'oco_qtd'       => null,
        'oco_data'      => null,
        'stt_id'        => null,
        'tmo_id'        => null,
        'oco_justi'     => null,
    ];

    protected $casts = [
        'oco_id'   => 'integer',
        'tel_id'   => 'integer',
        'pro_id'   => 'integer',
        'lot_id'   => 'integer',
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
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        $ocoid              = new MyCampo('oco_ocorrencia', 'oco_id');
        $ocoid->valor       = (isset($dados['oco_id'])) ? $dados['oco_id'] : '';
        $ret['oco_id'] = $ocoid->crOculto();
        // TIPO DE OCORRÊNCIA
        $config = [];
        $config['Label'] = 'Tipo de Ocorrência';
        $ret['tpo_id'] = criaSelectRelativo(
            'oco_tipo_ocorrencia',
            'tpo_id',
            'tpo_nome',
            $dados['tpo_id'] ?? null,
            1,
            'oco_ocorrencia',
            [],
            $config
        );


        // SUBTIPO
        $config['Label']   = 'Subtipo';
        $config['Pai'] = 'tpo_id';
        $config['Urlbusca'] = base_url('Buscas/buscaAcoesPorTipo');

        $ret['tpa_id'] = criaSelectRelativo(
            'oco_subt_ocorrencia',
            'sut_id',
            'sut_nome',
            $dados['sut_id'] ?? null,
            2,
            'oco_ocorrencia',
            [],
            $config
        );


        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->label       = 'Descrição';
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';

        $ret['oco_descricao'] = $desc->crTexto();

        $proid              = new MyCampo('oco_ocorrencia', 'pro_id');
        $proid->valor       = (isset($dados['pro_id'])) ? $dados['pro_id'] : '';
        $ret['pro_id'] = $proid->crOculto();

        // LOTE
        $lotid              = new MyCampo('oco_ocorrencia', 'lot_id');
        $lotid->valor       = (isset($dados['lot_id'])) ? $dados['lot_id'] : '';
        $ret['lot_id'] = $lotid->crOculto();

        $lote              = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor       = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->obrigatorio = true;
        $lote->leitura     = $show;
        $lote->label       = 'Lote';
        $lote->dispForm    = 'col-6';
        $lote->size        = 54;
        $lote->funcBlur    = "buscaLoteProduto(this,'" . base_url('/buscas/buscaProdutoporLote') . "')";
        $ret['lot_lote'] = $lote->crInput();


        // PRODUTO 
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $produto->dispForm = '2col';
        $produto->label    = ' ';
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
