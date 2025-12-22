<?php

namespace App\Entities\Microb;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\ArquivoMonModel;


class EntMicrobAnalise extends Entity
{
    protected $attributes = [
        'ana_id' => null,
        'pro_id' => null,
        'lot_id' => null,
        'ana_qtde' => null,
        'ana_qtde_micro' => null,
        'ana_data' => null,
        'ana_laudo' => null,
        'ana_obs' => null,
        'ana_data_result' => null,
        'ana_usu_id_result' => null,
        'stt_id' => null,
        'ana_liberarsemmicro' => 'N',
        'ana_lotemb' => null,
        'ana_datalotemb' => null,
        'ana_descmetodo' => null,
        'req_id' => null,
        'ana_liberar' => 'N',
        'ana_reprovar' => 'N',
        'tmo_id' => null,
        'tmo_id_rep' => null,
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /* =========================================================
     * CAMPOS PRINCIPAIS
     * =======================================================*/
      public function defCampos($dados = false, $show = false)
    {
        $opcoes         = new CommonModel();
        $ret = [];


        $id           =  new MyCampo('pro_mic_analise', 'ana_id', false);
        $id->valor    = $dados['ana_id'];
        $ret['ana_id']    = $id->crOculto();

        $sttid           =  new MyCampo('pro_mic_analise', 'stt_id', false);
        $sttid->valor    = $dados['stt_id'];
        $ret['stt_id']    = $sttid->crOculto();

        $entr           =  new MyCampo('pro_sap_lote', 'lot_entrada');
        $entr->valor    = $dados['lot_entrada']??'';
        $entr->leitura  = true;
        $entr->largura    = 20;
        $ret['lot_entrada'] = $entr->crInput();

        $opc_produ      = $opcoes->getListaOpcoes('dbProduto', 'pro_sap_produto', ['pro_despro', 'pro_id'], 'pro_id = ' . $dados['pro_id']);
        // $produtos       =  new ProdutProdutoModel();
        // $lst_produ      = $produtos->getProduto($dados['pro_id']);
        // $opc_produ       = array_column($lst_produ,'pro_despro','pro_id');

        $proid             = new MyCampo('pro_mic_analise', 'pro_id', true);
        $proid->valor = $proid->selecionado  = $dados['pro_id'] ?? '';
        $proid->leitura     = true;
        $proid->largura    = 60;
        $proid->opcoes      = $opc_produ;
        $ret['pro_id'] = $proid->crSelect();


        $opc_fabr      = $opcoes->getListaOpcoes('dbProduto', 'pro_sap_fabricante', ['fab_apeFab', 'fab_codFab'], 'fab_codFab = ' . $dados['fab_codFab']);
        // $fabricantes        =  new ProdutFabricanteModel();
        // $lst_fabrics    = $fabricantes->getFabricante($dados['fab_codFab']);
        // $opc_fabr       = array_column($lst_fabrics,'fab_apeFab','fab_codFab');
        // debug($opc_fabr, true);
        $fabr           =  new MyCampo('pro_sap_fabricante', 'fab_apeFab');
        $fabr->valor = $fabr->selecionado = $dados['fab_codFab'] ?? '';
        $fabr->leitura     = true;
        $fabr->largura     = 60;
        $fabr->label       = 'Fabricante';
        $fabr->opcoes      = $opc_fabr;
        $ret['fab_apeFab'] = $fabr->crSelect();

        $lote           =  new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor    = $dados['lot_lote'] ?? '';
        $lote->leitura  = true;
        $lote->largura    = 20;
        $ret['lot_lote'] = $lote->crInput();

        $loti           =  new MyCampo('pro_sap_lote', 'lot_id');
        $loti->valor    = $dados['lot_id'] ?? '';
        $ret['lot_id'] = $loti->crOculto();

        $vali           =  new MyCampo('pro_sap_lote', 'lot_validade');
        $vali->valor    = $dados['lot_validade'] ?? '';
        $vali->leitura  = true;
        $vali->largura    = 20;
        $ret['lot_validade'] = $vali->crInput();


        $aqtd               =  new MyCampo('pro_mic_analise', 'ana_qtde');
        $aqtd->valor        = $dados['ana_qtde'] ?? 0;
        $aqtd->maximo       = 99999;
        $aqtd->leitura      = $show;
        $aqtd->largura      = 20;
        $aqtd->tamanho      = 40;
        $ret['ana_qtde']    = $aqtd->crInput();

        $aqtm               =  new MyCampo('pro_mic_analise', 'ana_qtde_micro');
        $aqtm->valor        = ($dados['ana_qtde_micro'] != '') ? $dados['ana_qtde_micro'] : 0;
        $aqtm->largura      = 20;
        $aqtm->tamanho      = 40;
        $aqtm->minimo       = 1;
        $aqtm->maximo       = 99;
        $aqtm->leitura      = $show;
        $aqtm->obrigatorio  = true;
        $ret['ana_qtde_micro']  = $aqtm->crInput();

        $final          = new MyCampo();
        $final->nome    = 'bt_finalizar';
        $final->id      = 'bt_finalizar';
        $final->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-check" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $final->i_cone  .= '<div class="align-items-start txt-bt-manut">Finalizar</div>';
        $final->place    = 'Finalizar a Análise';
        $final->funcChan = 'submeter(\'/Analise/finalizar/\')';
        $final->classep  = 'btn-secondary bt-manut btn-sm mb-2 float-end';
        $ret['bt_finalizar'] = $final->crBotao();

        return $ret;
    }

    public function defCamposAnalise($dados = false, $show = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $met           =  new MyCampo('pro_classe', 'cla_metodanalise', false);
        $met->valor = $met->selecionado    = $dados['cla_metodanalise'];
        $met->leitura  = true;
        $met->opcoes   = $simnao;
        $met->funcChan       = "mostraOcultaCampo(this,'S','ana_lotemb');mostraOcultaCampo(this,'N','ana_descmetodo')";
        $met->dispForm = '2col';
        $ret['cla_metodanalise']    = $met->cr2opcoes();

        $lsm           =  new MyCampo('pro_mic_analise', 'ana_liberarsemmicro', false);
        $lsm->valor    = $lsm->selecionado    = isset($dados['ana_liberarsemmicro']) ? $dados['ana_liberarsemmicro'] : 'N';
        $lsm->leitura  = $show;
        $lsm->opcoes   = $simnao;
        $lsm->dispForm = '2col';
        $lsm->funcChan = "mostraOcultaCampo(this,'N','ana_lotemb,ana_laudo,ana_arqlaudo,ana_liberar,ana_reprovar,ana_descmetodo')";
        $ret['ana_liberarsemmicro']    = $lsm->cr2opcoes();

        $dlt           =  new MyCampo('pro_mic_analise', 'ana_datalotemb', false);
        $dlt->valor    = isset($dados['ana_datalotemb']) ? $dados['ana_datalotemb'] : date('Y-m-d');
        $ret['ana_datalotemb']    = $dlt->crOculto();

        $info = isset($dados['ana_datalotemb']) ? date('dmY', strtotime($dados['ana_datalotemb'])) : date('dmY');

        $lmb           =  new MyCampo('pro_mic_analise', 'ana_lotemb', false);
        $lmb->valor = $lmb->selecionado    = isset($dados['ana_lotemb']) ? $dados['ana_lotemb'] : '';
        $lmb->tipo     = 'sonumero';
        $lmb->leitura  = $show;
        $lmb->maxLength = 9;
        $lmb->largura   = 100;
        $lmb->size      = 9;
        $lmb->infotexto   = 'formato XX-DDMMAA';
        $lmb->obrigatorio = true;
        $ret['ana_lotemb']    = $lmb->crInput();

        $met           =  new MyCampo('pro_mic_analise', 'ana_descmetodo', false);
        $met->valor = $met->selecionado    = isset($dados['ana_descmetodo']) ? $dados['ana_descmetodo'] : '';
        $met->leitura  = $show;
        $met->obrigatorio = true;
        $ret['ana_descmetodo']    = $met->crInput();

        $lau            =  new MyCampo('pro_mic_analise', 'ana_laudo', false);
        $lau->valor     = isset($dados['ana_laudo']) ? $dados['ana_laudo'] : '';
        $lau->leitura  = $show;
        // $lau->obrigatorio = true;
        $ret['ana_laudo']    = $lau->crInput();

        $arq            =  new MyCampo();
        $arq->id = $arq->nome     = 'ana_arqlaudo';
        $arq->label     = 'Laudo PDF';
        $arq->size      = 300;
        $arq->tamanho      = 300;
        $arq->valor      = '';
        if (isset($dados['ana_id'])) {
            $arqdb       = new ArquivoMonModel();
            $dados_files = $arqdb->getArquivos('Analisa', 'ArqLaudo', $dados['ana_id']);
            // debug($dados_files, true);
            if (count($dados_files) > 0) {
                $arqlaudo = $dados_files[0];
                // debug($arqlaudo, true);
                $arquivo = buscaTipoArquivo($arqlaudo);
                $nome_arq = (isset($arqlaudo->arq_nome)) ? $arqlaudo->arq_nome : '';
                $id         = (string)$arqlaudo->_id;
                $link              = base_url("/Showfile/" . $id);
                $redir             = "redirec_blank('$link', event)";
                $arq->funcChan     = $redir;
                $arq->valor        = $nome_arq;
                $arq->selecionado  = $arquivo;
                $arq->classep      = 'btn-outline-success';
                // $arq->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">'
                // <i class="fa-solid fa-file" style="font-size: 2rem;" aria-hidden="true"></i></div>';
                if($dados['stt_id'] == 15){
                    $arq->i_cone = "<div id='view_img_" . $id . "' class='show img-thumbnail border border-1' >";
                    $arq->i_cone .= "<img id='img_" . $id . "' src='" . $arquivo . "'  class='img-thumbnail sempadding' alt='' style='width:" . $arq->size . "px;' />";
                    $arq->i_cone .= "</div>";
                    $arq->i_cone  .= '<div class="align-items-start txt-bt-manut">Ver Arquivo do Laudo<br>'.$nome_arq.'</div>';
                    $arq->place = '';
                    $ret['ana_arqlaudo']    = $arq->crLabel().$arq->crBotao();
                } else if($dados['stt_id'] != 15){
                    $arq->leitura  = $show;
                    $arq->tipoArq  = '.pdf';
                    $arq->valor        = $nome_arq;
                    $arq->selecionado  = $arquivo;
                    $arq->funcChan    = "validarArquivoPorAccept(this);readURL(this, 'img_$arq->id', $arq->size, $arq->tamanho)";
                    $arq->funcBlur    = $redir;
                    $ret['ana_arqlaudo'] = $arq->crArquivo();
                }
                // debug($arq);
            } else {
                $arq->leitura  = $show;
                $arq->tipoArq  = '.pdf';
                $arq->selecionado  = "/assets/uploads/tipo_arquivo/vazio.png";
                $arq->funcChan    = "validarArquivoPorAccept(this);readURL(this, 'img_$arq->id', $arq->size, $arq->tamanho)";
                $ret['ana_arqlaudo']    = $arq->crArquivo();
            }
        }

        return $ret;
    }

    public function defCamposAcoes($dados = false, $show = false)
    {
        $opcoes         = new CommonModel();
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $liberar = 'N';
        if (isset($dados['ana_liberar'])) {
            $liberar = $dados['ana_liberar'];
        } else if ($dados['stt_id'] == 12) { // EM ANDAMENTO
            $liberar = 'S';
        }
        $lib           =  new MyCampo('pro_mic_analise', 'ana_liberar', false);
        // $lib->id = $lib->nome = 'ana_liberar';
        // $lib->label    = 'Liberar';
        $lib->valor    = $lib->selecionado    = $liberar;
        $lib->leitura  = $show;
        $lib->opcoes   = $simnao;
        $lib->funcChan = "reprovar(this,'ana_reprovar');reprovar(this,'ana_liberarsemmicro');mostraOcultaCampo('ana_liberar','S','tmo_id');mostraOcultaCampo('ana_liberar','N','tmo_id_rep');mudaObrigatorio(this,'S','tmo_id')";
        $lib->dispForm = '2col';
        $ret['ana_liberar']    = $lib->cr2opcoes();

        $reprovar = 'N';
        if (isset($dados['ana_reprovar'])) {
            $reprovar = $dados['ana_reprovar'];
        } else if ($dados['stt_id'] == 16) { // EM ANDAMENTO
            $reprovar = 'S';
        }
        $rep           =  new MyCampo('pro_mic_analise', 'ana_reprovar', false);
        // $rep->id = $rep->nome = 'ana_reprovar';
        // $rep->label    = 'Reprovar';
        $rep->valor    = $rep->selecionado    = $reprovar;
        $rep->leitura  = $show;
        $rep->opcoes   = $simnao;
        $rep->dispForm = '2col';
        $rep->funcChan = "reprovar(this,'ana_liberar');reprovar(this,'ana_liberarsemmicro');mostraOcultaCampo('ana_reprovar','S','tmo_id_rep');mostraOcultaCampo('ana_reprovar','N','tmo_id');mudaObrigatorio(this,'S','tmo_id_rep')";
        $ret['ana_reprovar']    = $rep->cr2opcoes();
        // debug($dados['tmo_id']);

        $opc_mov           = $opcoes->getListaOpcoes('dbEstoque', 'est_tipo_movimentacao', ['tmo_nome', 'tmo_id']);
        $movi               =  new MyCampo('pro_mic_analise', 'tmo_id');
        $movi->valor        = $movi->selecionado = ($liberar=='S')?isset($dados['tmo_id']) ? $dados['tmo_id'] : '':'';
        $movi->leitura      = $show;
        $movi->largura      = 60;
        $movi->opcoes       = $opc_mov;
        $movi->obrigatorio  = false;
        $ret['tmo_id']      = $movi->crSelect();

        $opc_mov_rep           = $opcoes->getListaOpcoes('dbEstoque', 'est_tipo_movimentacao', ['tmo_nome', 'tmo_id']);
        $movi               =  new MyCampo('pro_mic_analise', 'tmo_id_rep');
        $movi->valor        = $movi->selecionado = $reprovar=='S'?isset($dados['tmo_id_rep'])?$dados['tmo_id_rep']:'' : '';
        $movi->leitura      = $show;
        $movi->largura      = 60;
        $movi->opcoes       = $opc_mov_rep;
        $movi->obrigatorio  = false;
        $ret['tmo_id_rep']      = $movi->crSelect();

        return $ret;
    }
}
