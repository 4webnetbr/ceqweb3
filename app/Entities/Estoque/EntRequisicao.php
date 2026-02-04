<?php

namespace App\Entities\Estoque;

use DateTime;
use CodeIgniter\Entity\Entity;
use App\Entities\Produto\EntProdutos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Estoqu\EstoquDepositoModel;

class EntRequisicao extends Entity
{
    protected $attributes = [
        'req_id'                => null,
        'req_data'              => null,
        'req_dataentrega'       => null,
        'tmo_id'                => null,
        'req_deporigem'         => null,
        'req_depdestino'        => null,
        'req_consdiaanterior'   => 'S',
        'req_medconsumodias'    => 'N',
        'req_meddias'           => 2,
        'req_repetedias'        => 0,
        'req_percseguranca'     => 0,
        'req_observacao'        => null,
        'stt_id'                => null,
        'usu_nome'              => null,
        'stt_nome'              => null,
        'stt_cor'               => null,
    ];

    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false, $tipo = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($this, $show, $tipo);
    }

    /**
     * defCampos
     * Lógica mantida 100% igual ao Model
     */
    public function defCampos(?object $dados = null, bool $show = false, $tipo = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // PRODUTO
        $config['Leitura'] = $show;
        $config['Obrigatorio'] = false;
        $config['Label'] = 'Produto';
        $config['DispForm'] = 'col-12';
        $config['Largura'] = 60;

        $ret['pro_id'] = criaSelectRelativo(
            'pro_sap_produto',
            'pro_id',
            'pro_despro',
            $dados->pro_id ?? '',
            1,
            'est_requisicao_produto',
            [],
            $config
        );

        // $ret['pro_id'] = EntProdutos::campoSelectProduto($dados->pro_id ?? '', $show, 'pro_produto');
        // $ret['tmo_id'] = EntTipoMovimentacao::campoSelectTipoMovi($dados->tmo_id ?? '', $show, 'oco_tpo_acao');

        // ID da requisição
        $id           =  new MyCampo('est_requisicao', 'req_id', false);
        $id->valor    = (isset($dados->req_id)) ? $dados->req_id : '';
        $id->leitura  = $show;
        $ret['req_id']    = $id->crOculto();

        // Número visual da requisição 
        $num                 =  new MyCampo('est_requisicao', 'req_id', true);
        $num->valor          = (isset($dados->req_id)) ? str_pad($dados->req_id, 6, '0', STR_PAD_LEFT) : '';
        $num->label          = 'Requisição Nº';
        $num->leitura        = true;
        $num->dispForm       = 'col-6';
        $num->classep        = 'mb3';
        $ret['req_numero']   = $num->crInput();

        // Data de criação da requisição
        $hoje = new DateTime();
        $data                 = new MyCampo('est_requisicao', 'req_data', false);
        $data->valor          = (isset($dados->req_data)) ? $dados->req_data : $hoje->format('Y-m-d H:i:s');
        $data->leitura        = true;
        $data->dispForm       = 'col-6';
        $data->classep        = 'mb3';
        $ret['req_data']          = $data->crInput();

        // $prev = new DateTime('+1 days');
        $data = new DateTime();
        $entr                 = new MyCampo('est_requisicao', 'req_dataentrega', false);
        $entr->valor          = (isset($dados->req_dataentrega)) ? $dados->req_dataentrega : '';
        $entr->leitura        = $show;
        $entr->datamin         = $data->format('Y-m-d');
        $entr->obrigatorio    = true;
        $entr->dispForm       = 'col-6';
        $entr->classep        = 'mb3';
        if (!$show) {
            $entr->funcBlur       = 'validaDataMinima(this)';
        }
        $ret['req_dataentrega']   = $entr->crInput();

        if (!$show) {
            $config['FunChan']       = "buscaTipoMovimentacao(this,'req_deporigem','req_depdestino','req_deppadrao')";
        }
        $config['Label'] = 'Tipo de Movimentação';
        $config['DispForm'] = 'col-6';
        $config['Largura'] = 50;
        $config['Leitura'] = $show;

        // MOVIMENTAÇÃO
        $perfilId = session()->get('usu_perfil_id');

        $ret['tmo_id'] = criaSelectRelativo(
            'vw_est_tipo_movimentacao_relac_lista',
            'tmo_id',
            'tmo_nome',
            $dados->tmo_id ?? '',
            1,
            'est_requisicao',
            [],
            $config
        );

        // Quantidade de dias de repetição automática da requisição
        $mudi                 = new MyCampo('est_requisicao', 'req_repetedias', false);
        $mudi->valor          = (isset($dados->req_repetedias)) ? $dados->req_repetedias : 0;
        $mudi->leitura        = $show;
        $mudi->minimo         = 0;
        $mudi->step           = 1;
        $mudi->maximo         = 10;
        $mudi->size           = 2;
        $mudi->classep        = 'mb2';
        $mudi->dispForm       = 'col-6';
        $ret['req_repetedias']          = $mudi->crInput();


        if (isset($dados->req_deporigem)) {
            $config['Leitura']       = true;
        }
        $config['Label'] = 'Depósito de Origem';
        $config['Leitura'] = true;
        $config['Largura'] = 60;

        // DEPÓSITO ORIGEM
        // debug($dados->req_deporigem, true);
        $ret['req_deporigem'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_codDescricao',
            $dados->req_deporigem ?? '',
            1,
            'est_requisicao',
            [],
            $config,
            'req_deporigem'
        );

        // Depósito padrão
        $depr                 = new MyCampo('est_requisicao', 'req_deporigem', false);
        $depr->id = $depr->nome = 'req_deppadrao';
        $depr->valor          = (isset($dados->req_deppadrao)) ? $dados->req_deppadrao : '';
        $ret['req_deppadrao'] = $depr->crOculto();

        // Depósito de destino
        if (isset($dados->req_depdestino)) {
            $config['Leitura']       = true;
        }
        $config['Label'] = 'Depósito de Destino';

        $ret['req_depdestino'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_codDescricao',
            $dados->req_depdestino ?? '',
            1,
            'est_requisicao',
            [],
            $config,
            'req_depdestino'
        );


        // Considerar consumo do dia anterior
        $coda                 = new MyCampo('est_requisicao', 'req_consdiaanterior', false);
        $coda->valor          = (isset($dados->req_consdiaanterior)) ? $dados->req_consdiaanterior : 'S';
        $coda->leitura        = $show;
        $coda->opcoes         = $simnao;
        $coda->selecionado    = $coda->valor;
        $coda->classep        = 'semmb';
        $coda->dispForm       = ($show) ? 'col-6' : 'col-3';
        if (!$show) {
            // $coda->funcChan       = "mostraOcultaCampo(this,'N','req_medconsumodias,req_meddias');mudaCheck2opcoes(this,'req_medconsumodias');";
            $coda->funcChan       = "mostraOcultaCampo(this,'N','req_medconsumodias,req_meddias');";
        }
        $ret['req_consdiaanterior']          = $coda->cr2opcoes();

        // Média de consumo por dias
        $mcdi                 = new MyCampo('est_requisicao', 'req_medconsumodias', false);
        $mcdi->valor          = (isset($dados->req_medconsumodias)) ? $dados->req_medconsumodias : 'S';
        $mcdi->leitura        = $show;
        $mcdi->opcoes         = $simnao;
        $mcdi->selecionado    = $mcdi->valor;
        $mcdi->classep        = 'mb2';
        $mcdi->dispForm       = ($show) ? 'col-6' : 'col-3';
        if (!$show) {
            // $mcdi->funcChan       = "mostraOcultaCampo(this,'S','req_meddias');mudaCheck2opcoes(thiqs,'req_consdiaanterior');";
            $mcdi->funcChan       = "mostraOcultaCampo(this,'S','req_meddias');";
        }
        $ret['req_medconsumodias']          = $mcdi->cr2opcoes();

        // Quantidade de dias para cálculo de média
        $medi                 = new MyCampo('est_requisicao', 'req_meddias', false);
        $medi->valor          = (isset($dados->req_meddias)) ? $dados->req_meddias : 2;
        $medi->leitura        = $show;
        $medi->minimo         = 2;
        $medi->step           = 1;
        $medi->maximo         = 30;
        $medi->size           = 2;
        $medi->classep        = 'mb2';
        $medi->dispForm       = 'col-3';
        $ret['req_meddias']          = $medi->crInput();

        // Percentual de segurança aplicado ao cálculo
        $pseg                 = new MyCampo('est_requisicao', 'req_percseguranca', false);
        $pseg->valor          = (isset($dados->req_percseguranca)) ? $dados->req_percseguranca : 0;
        $pseg->leitura        = $show;
        $pseg->obrigatorio    = true;
        $pseg->minimo         = 0;
        $pseg->step           = 1;
        $pseg->maximo         = 100;
        $pseg->size           = 3;
        $pseg->classep        = 'mb2';
        $pseg->dispForm       = 'col-3';
        $ret['req_percseguranca']      = $pseg->crInput();

        $obsv                 = new MyCampo('est_requisicao', 'req_observacao', false);
        $obsv->valor          = (isset($dados->req_observacao)) ? $dados->req_observacao : '';
        $obsv->leitura        = $show;
        $obsv->classep        = 'mb2';
        $obsv->dispForm       = 'col-12';
        $ret['req_observacao']      = $obsv->crInput();

        // Botão de carregamento de produtos
        $btca            = new MyCampo();
        $btca->nome      = "bt_carregar";
        $btca->id        = "bt_carregar";
        $btca->i_cone    = "<i class='fas fa-cart-flatbed fs-3'></i> <scan class='mx-3'>Carregar Produtos</scan>";
        $btca->label     = $btca->place     = "Carregar Produtos";
        $btca->classep   = "btn-outline-success btn-sm align-items-center d-flex m-3";
        $btca->funcChan  = "carregarProdutos('" . base_url("Requisicao/produtos/") . "','produtos',this)";
        $ret['bt_carregar']   = $btca->crBotao();

        $codb                 = new MyCampo('pro_sap_lote', 'lot_codbar', false);
        $codb->valor          = '';
        $codb->leitura        = false;
        $codb->classep        = 'mb2';
        $codb->dispForm       = 'col-6';
        $codb->tamanho        = '30';
        $codb->largura        = '30';
        $codb->size           = '30';
        $codb->funcBlur       = 'validaCodBar(this)';
        if ($tipo && $tipo == 'conf') {
            $codb->funcBlur       = 'validaCodBarConf(this)';
        }
        $ret['lot_codbar']      = $codb->crInput();

        // Usuário responsável pela requisição
        $usua                 = new MyCampo();
        $usua->valor          = (isset($dados->usu_nome)) ? $dados->usu_nome : '';
        $usua->id = $usua->nome        = 'usu_nome';
        $usua->label          = 'Usuário';
        $usua->size           = 50;
        $usua->largura        = 50;
        $usua->dispForm       = 'col-6';
        $usua->leitura      = true;
        $ret['usu_nome'] = $usua->crInput();

        // Status atual da requisição
        $stat                 = new MyCampo();
        $stat->valor          = (isset($dados->stt_nome)) ? fmtEtiquetaCor($dados->stt_cor, $dados->stt_nome) : '';
        $stat->id = $stat->nome        = 'stt_nome';
        $stat->label          = 'Status';
        $stat->size           = 50;
        $stat->largura        = 50;
        $stat->dispForm       = 'col-4';
        $stat->leitura      = true;
        $ret['stt_nome']    = $stat->crShow();

        return $ret;
    }


    public function defCamposProdutoAte(object $dados, bool $show = false)
    {
        $ret = [];
        // debug($dados);
        $rpaid           =  new MyCampo('est_requisicao_produto_atendimento', 'rpa_id', false);
        $rpaid->valor    = (isset($dados->rpa_id)) ? $dados->rpa_id : '';
        $rpaid->leitura  = $show;
        $ret['rpa_id']    = $rpaid->crOculto();

        // Campo para quantidade cancelada 
        $canc                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_cancelada', false);
        $canc->id = $canc->nome = "rpa_cancelada_" . $dados->rep_id;
        $canc->valor          = $dados->rpa_cancelada ?? 0;
        $canc->label          = '';
        $canc->leitura        = false;
        if (intval($dados->rep_quantia) == (intval($dados->rpa_atendida) + intval($dados->rpa_cancelada))) {
            $canc->leitura        = true;
        }
        $canc->classep        = 'semmb';
        $canc->minimo           = 0;
        $canc->maximo           = $dados->rep_quantia;
        $canc->dispForm       = 'col-12';
        $canc->funcChan       = 'acertaSaldoReq(this)';
        $canc->size           = 4;
        $canc->largura        = 30;
        $ret['rpa_cancelada']      = $canc->crInput();

        // Campo para quantidade atendida
        $aten                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_atendida', false);
        $aten->id = $aten->nome = "rpa_atendida_" . $dados->rep_id;
        $aten->valor          = $dados->rpa_atendida ? $dados->rpa_atendida : 0;
        $aten->label          = '';
        $aten->size           = 4;
        $aten->leitura        = true;
        if ($dados->pre_cbfabricante == 'N' && $dados->pre_undfabricante == 'N') {
            $aten->leitura        = false;
        }
        if (intval($dados->rep_quantia) == (intval($dados->rpa_atendida) + intval($dados->rpa_cancelada))) {
            $aten->leitura        = true;
        }
        $aten->classep        = 'semmb';
        $aten->dispForm       = 'col-12';
        $aten->minimo           = 0;
        $aten->maximo           = $dados->rep_quantia;
        $aten->funcChan       = 'acertaSaldoReq(this)';
        $aten->largura        = 30;
        $ret['rpa_atendida']      = $aten->crInput();
        return $ret;
    }

    public function defCamposProdutoConf(object $dados, bool $show = false)
    {
        $ret = [];
        $rpaid           =  new MyCampo('est_requisicao_produto_atendimento', 'rpa_id', false);
        $rpaid->valor    = (isset($dados->rpa_id)) ? $dados->rpa_id : '';
        $rpaid->leitura  = $show;
        $ret['rpa_id']    = $rpaid->crOculto();

        // Quantidade atendida 
        // Campo para quantidade atendida
        $aten                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_atendida', false);
        $aten->id = $aten->nome = "rpa_atendida_" . $dados->rep_id;
        $aten->valor          = $dados->rpa_atendida ? $dados->rpa_atendida : 0;
        $aten->label          = '';
        $aten->size           = 4;
        $aten->leitura        = true;
        $aten->classep        = 'semmb';
        $aten->dispForm       = 'col-12';
        // $aten->funcChan       = 'acertaSaldoReq(this)';
        $aten->largura        = 10;
        $ret['rpa_atendida']      = $aten->crInput();

        // Quantidade conferida
        $conf                 = new MyCampo('est_requisicao_produto_atendimento', 'rpa_conferida', false);
        $conf->id = $conf->nome = "rpa_conferida_" . $dados->rep_id;
        $conf->valor          = $dados->rpa_conferida ?? 0;
        $conf->label          = '';
        $conf->leitura        = true;
        $conf->classep        = 'semmb';
        $conf->dispForm       = 'col-12';
        $conf->minimo         = 0;
        $conf->maximo         = intval($dados->rpa_atendida);
        $conf->funcChan       = 'acertaSaldoConf(this)';
        $conf->largura        = 20;
        if ($dados->pre_cbfabricante == 'N' && $dados->pre_undfabricante == 'N') {
            $conf->leitura        = false;
        }
        if (intval($dados->rep_quantia) == intval($dados->rpa_conferida)) {
            $conf->leitura        = true;
        }
        $ret['rpa_conferida']      = $conf->crInput();
        return $ret;
    }
}
