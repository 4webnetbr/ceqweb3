<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;

class EntOcoTratativa extends Entity
{
    protected $attributes = [
        'oco_id'        => null,
        'tpo_id'        => null,
        'tpa_id'        => null,
        'lot_lote'      => null,
        'oco_descricao' => null,
        'pro_despro'    => null,
        'oco_qtd'       => null,
        'oco_data'      => null,
        'stt_id'        => null,
        'tmo_id'        => null,
        'oco_justi'     => null,
        'usu_nome'      => null,
        'tel_id'        => null,
    ];

    protected $casts = [
        'oco_id' => 'integer',
        'tpo_id' => 'integer',
        'tpa_id' => 'integer',
        'stt_id' => 'integer',
        'tmo_id' => 'integer',
        'tel_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(object|array|null $data = null)
    {
        if (is_array($data)) {
            $data = (object) $data;
        }
        parent::__construct((array) ($data ?? []));
        $this->campos = $this->defCampos($data ?? new \stdClass());
    }

    public function defCampos(object $dados)

    // DADOS GERAIS
    {
        $dados = (array) $dados;
        $ret = [];

        // ID DA OCORRÊNCIA
        $ocoid = new MyCampo('oco_ocorrencia', 'oco_id');
        $ocoid->valor = $dados['oco_id'] ?? null;
        $ret['oco_id'] = $ocoid->crOculto();


        // TIPO DE AÇÃO
        $config['Label'] = 'Ação';
        $config['Pai'] = 'tpo_id';
        $config['Urlbusca'] = base_url('Buscas/buscaAcoesPorTipo');

        $ret['tpa_id'] = criaSelectRelativo(
            'oco_tipo_acao',
            'tpa_id',
            'tpa_nome',
            $dados['tpa_id'] ?? null,
            2,
            'oco_ocorrencia',
            [],
            $config
        );

        // SUBTIPO
        $config['Label'] = 'Subtipo';

        $ret['sut_id'] = criaSelectRelativo(
            'oco_subt_ocorrencia',
            'sut_id',
            'sut_nome',
            $dados['sut_id'] ?? null,
            1,
            'oco_ocorrencia',
            [],
            $config
        );

        // USUÁRIO
        $usu           = new MyCampo('oco_ocorrencia', 'usu_nome');
        $usu->valor    = (isset($dados['usu_nome'])) ? $dados['usu_nome'] : '';
        $usu->objeto   = '';
        $usu->label    = 'Usuário';
        $usu->dispForm = '2col';
        $usu->size     = 40;
        $usu->leitura  = true;
        $ret['usu_nome'] = $usu->crInput();

        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->nome        = 'oco_descricao';
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->leitura     = true;
        $desc->label       = 'Descrição';
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';
        $ret['oco_descricao'] = $desc->crTexto();

        // LOTE
        $lotid              = new MyCampo('oco_ocorrencia', 'lot_id');
        $lotid->valor       = (isset($dados['lot_id'])) ? $dados['lot_id'] : '';
        $ret['lot_id'] = $lotid->crOculto();

        $lote              = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor       = model(ProdutLoteModel::class)->getBuscaLote($dados['lot_id'] ?? null);
        $lote->leitura     = true;
        $lote->label       = 'Lote';
        $lote->size        = 54;
        $lote->funcBlur    = "buscaLoteProduto(this,'" . base_url('/buscas/buscaProdutoporLote') . "')";
        $ret['lot_lote']   = $lote->crInput();

        $descpro = '';
        if (isset($dados['pro_id']) && !empty($dados['pro_id'])) {
            $modProduto = new ProdutProdutoModel();
            $prod = $modProduto->getProduto($dados['pro_id']);
            $descpro = !empty($listaProd) ? $prod[0]->pro_despro : '';
        }
        // PRODUTO
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = $descpro;
        $produto->dispForm = '2col';
        $produto->label    = ' ';
        $produto->size     = 54;
        $produto->leitura  = true;
        $ret['pro_despro'] = $produto->crInput();

        // QUANTIDADE
        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
        $qtd->valor        = (isset($dados['oco_qtd'])) ? $dados['oco_qtd'] : '';
        $qtd->label        = 'Quantidade';
        $qtd->dispForm     = '2col';
        $qtd->largura      = 5;
        $qtd->leitura      = true;
        $ret['oco_qtd'] = $qtd->crInput();


        // DATA
        $data              = new MyCampo('oco_ocorrencia', 'oco_data');
        $data->valor       = $dados->oco_data ?? date('Y-m-d\TH:i');
        $data->label       = 'Data da Ocorrência';
        $data->dispForm    = '2col';
        $data->leitura     = true;
        $data->largura     = 30;

        $ret['oco_data'] = $data->crInput();

        return $ret;
    }

}
