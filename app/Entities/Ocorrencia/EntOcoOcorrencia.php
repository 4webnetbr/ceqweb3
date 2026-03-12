<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntOcoOcorrencia extends Entity
{
    protected $attributes = [
        'oco_id'        => null,
        'tpo_id'        => null,
        'sut_id'        => null,
        'tel_id'        => null,
        'req_id'        => null,
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
        'sut_id'   => 'integer',
        'tpa_id'   => 'integer',
        'oco_qtd'  => 'integer',
        'stt_id'   => 'integer',
        'tmo_id'   => 'integer',
        'req_id'   => 'integer',
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

        $reqid              = new MyCampo('oco_ocorrencia', 'req_id');
        $reqid->valor       = (isset($dados['req_id'])) ? $dados['req_id'] : '';
        $ret['req_id']      = $reqid->crOculto();

        // TIPO DE OCORRÊNCIA
        $config = [];
        $config['Label'] = 'Tipo de Ocorrência';
        $config['Leitura'] = $show;
        // $config['FunChan'] = 'carregaSubtipoOcorrencia(this)';


        $ret['tpo_id'] = criaSelectRelativo(
            'vw_oco_tpo_ocorrencia_relac',
            'tpo_id',
            'tpo_nome',
            $dados['tpo_id'] ?? null,
            1,
            'oco_ocorrencia',
            [],
            $config
        );


        // SUBTIPO
        $config['Label']   = 'Subtipo de Ocorrência';
        $config['Pai'] = 'tpo_id';
        $config['Urlbusca'] = base_url('Buscas/buscaAcoesPorTipo');

        $ret['sut_id'] = criaSelectRelativo(
            '',
            '',
            '',
            $dados['sut_id'] ?? null,
            2,
            'oco_ocorrencia',
            ['sut_id' => $dados['sut_id'] ?? ''],
            $config,
            'sut_id'
        );


        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->leitura     = $show;
        $desc->dispForm    = 'col-6';
        $ret['oco_descricao'] = $desc->crInput();


        $proid              = new MyCampo('oco_ocorrencia', 'pro_id');
        $proid->valor       = (isset($dados['pro_id'])) ? $dados['pro_id'] : '';
        $ret['pro_id'] = $proid->crOculto();

        // CÓDIGO ERP (oculto)
        $lotVal = new MyCampo('oco_ocorrencia', 'lot_codpro');
        $lotVal->valor = (isset($dados['lot_codpro'])) ? $dados['lot_codpro'] : '';
        $ret['cod_erp'] = $lotVal->crOculto();

        // CÓDIGO ERP (mostrar na tela) 
        $valid = new MyCampo('pro_sap_lote', 'lot_codpro');
        $valid->valor    = (isset($dados['lot_codpro'])) ? $dados['lot_codpro'] : '';
        $valid->leitura  = true;
        $valid->size     = 30;
        $valid->dispForm = 'col-6';

        $ret['cod_erp_show'] = $valid->crInput();

        // LOTE
        $lotid              = new MyCampo('oco_ocorrencia', 'lot_id');
        $lotid->valor       = (isset($dados['lot_id'])) ? $dados['lot_id'] : '';
        $ret['lot_id'] = $lotid->crOculto();

        $lote              = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor       = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->obrigatorio = true;
        $lote->leitura     = $show;
        $lote->dispForm    = 'col-6';
        $lote->funcBlur    = "buscaLoteProduto(this,'" . base_url('/buscas/buscaProdutoporLote') . "')";
        $ret['lot_lote'] = $lote->crInput();

        // VALIDADE (oculto)
        $lotVal = new MyCampo('oco_ocorrencia', 'lot_validade');
        $lotVal->valor = (isset($dados['lot_validade'])) ? $dados['lot_validade'] : '';
        $ret['lot_validade'] = $lotVal->crOculto();

        // VALIDADE (mostrar na tela) 
        $valid = new MyCampo('pro_sap_lote', 'lot_validade');
        $valid->valor    = (isset($dados['lot_validade'])) ? $dados['lot_validade'] : '';
        $valid->leitura  = true;
        $valid->size     = 20;
        $valid->dispForm = 'col-6';

        $ret['lot_validade_show'] = $valid->crInput();

        // fabricante (oculto)
        $lotVal = new MyCampo('oco_ocorrencia', 'fab_nomFab');
        $lotVal->valor = (isset($dados['fab_nomFab'])) ? $dados['fab_nomFab'] : '';
        $ret['fab_nomFab'] = $lotVal->crOculto();

        // fabricante (mostrar na tela)
        $valid = new MyCampo('pro_sap_fabricante', 'fab_nomFab');
        $valid->valor    = (isset($dados['fab_nomFab'])) ? $dados['fab_nomFab'] : '';
        $valid->leitura  = true;
        $valid->size     = 40;
        $valid->dispForm = 'col-6';

        $ret['lot_fabricante'] = $valid->crInput();


        // PRODUTO 
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $produto->dispForm = 'col-6';
        $produto->leitura  = true;
        $ret['pro_despro'] = $produto->crInput();

        // QUANTIDADE
        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->dispForm     = 'col-6';
        $qtd->minimo       = 1;
        $qtd->largura      = 10;
        $qtd->size         = 3;
        $qtd->maximo       = 999;
        $qtd->obrigatorio  = true;
        $qtd->leitura      = $show;
        $ret['oco_qtd'] = $qtd->crInput();

        // DATA 
        $data              = new MyCampo('oco_ocorrencia', 'oco_data');
        $data->valor       = $dados['oco_data'] ?? date('Y-m-d\TH:i');
        $data->dispForm    = '2col';
        $data->leitura     = true;
        $data->largura     = 30;

        $ret['oco_data'] = $data->crInput();


        // $stat                 = new MyCampo();
        // $stat->valor          = (isset($dados['stt_nome'])) ? fmtEtiquetaCor($dados['stt_cor'], $dados['stt_nome']) : '';
        // $stat->id = $stat->nome        = 'stt_nome';
        // $stat->label           = 'Status';
        // $stat->size           = 50;
        // $stat->largura        = 50;
        // $stat->dispForm       = 'col-4';
        // $stat->leitura      = true;
        // $ret['stt_nome']    = $stat->crShow();

        // USUÁRIO 
        $usu           = new MyCampo();
        $usu->valor    = (isset($dados['usu_nome'])) ? $dados['usu_nome'] : '';
        $usu->label    = 'Usuário';
        $usu->dispForm = 'col-6';
        $usu->size     = 40;
        $usu->leitura  = true;
        $ret['usu_nome'] = $usu->crInput();

        return $ret;
    }
}
