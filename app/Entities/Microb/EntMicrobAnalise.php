<?php

namespace App\Entities\Microb;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\ArquivoMonModel;
use App\Entities\Produto\EntProdutos;


class EntMicrobAnalise extends Entity
{
    protected $attributes = [
        'ana_id'              => null,
        'pro_id'              => null,
        'lot_id'              => null,
        'ana_qtde'            => null,
        'ana_qtde_micro'      => null,
        'ana_data'            => null,
        'ana_laudo'           => null,
        'ana_obs'             => null,
        'ana_data_result'     => null,
        'ana_usu_id_result'   => null,
        'stt_id'              => null,
        'ana_liberarsemmicro' => 'N',
        'ana_lotemb'          => null,
        'ana_datalotemb'      => null,
        'ana_descmetodo'      => null,
        'req_id'              => null,
        'ana_liberar'         => 'N',
        'ana_reprovar'        => 'N',
        'tmo_id'              => null,
        'tmo_id_rep'          => null,
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($this, $show);
    }

    public function defCampos(EntMicrobAnalise $dados, bool $show = false)
    {
        $opcoes = new CommonModel();
        $ret = [];

        // ID da Análise (oculto)
        $id = new MyCampo('pro_mic_analise', 'ana_id', false);
        $id->valor = $dados->ana_id;
        $ret['ana_id'] = $id->crOculto();

        // Status da Análise (oculto)
        $sttid = new MyCampo('pro_mic_analise', 'stt_id', false);
        $sttid->valor = $dados->stt_id;
        $ret['stt_id'] = $sttid->crOculto();

        // Data de Entrada do Lote
        $entr = new MyCampo('pro_sap_lote', 'lot_entrada');
        $entr->valor   = $dados->lot_entrada ?? '';
        $entr->leitura = true;
        $entr->largura = 20;
        $ret['lot_entrada'] = $entr->crInput();

        $config['Label'] = 'Produto';

        // Produto (somente leitura)
        $config = [];
        $config['Leitura'] = true;
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


        $config = [];
        $config['Label']   = 'Fabricante';
        $config['Leitura'] = true;
        $config['DispForm'] = 'col-8';

        $ret['fab_codFab'] = criaSelectRelativo(
            'pro_sap_fabricante',
            'fab_codFab',
            'fab_apeFab',
            $dados->fab_codFab ?? '',
            1,
            'pro_sap_fabricante',
            [],
            $config
        );

        // Lote
        $lote = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor   = $dados->lot_lote ?? '';
        $lote->leitura = true;
        $lote->largura = 20;
        $ret['lot_lote'] = $lote->crInput();

        // ID do Lote (oculto)
        $loti = new MyCampo('pro_sap_lote', 'lot_id');
        $loti->valor = $dados->lot_id ?? '';
        $ret['lot_id'] = $loti->crOculto();

        // Validade do Lote
        $vali = new MyCampo('pro_sap_lote', 'lot_validade');
        $vali->valor   = $dados->lot_validade ?? '';
        $vali->leitura = false;
        $vali->largura = 20;
        $ret['lot_validade'] = $vali->crInput();

        // Quantidade da Análise
        $aqtd = new MyCampo('pro_mic_analise', 'ana_qtde');
        $aqtd->valor   = $dados->ana_qtde ?? 0;
        $aqtd->maximo  = 99999;
        $aqtd->leitura = $show;
        $aqtd->largura = 30;
        $aqtd->tamanho = 40;
        $ret['ana_qtde'] = $aqtd->crInput();

        // Quantidade Microbiológica
        $aqtm = new MyCampo('pro_mic_analise', 'ana_qtde_micro');
        $aqtm->valor = ($dados->ana_qtde_micro !== null && $dados->ana_qtde_micro !== '')
            ? $dados->ana_qtde_micro
            : 0;
        $aqtm->largura     = 30;
        $aqtm->tamanho     = 40;
        $aqtm->minimo      = 1;
        $aqtm->maximo      = 99;
        $aqtm->leitura     = $show;
        $aqtm->obrigatorio = true;
        $ret['ana_qtde_micro'] = $aqtm->crInput();

        // Botão Finalizar Análise
        $final = new MyCampo();
        $final->nome    = 'bt_finalizar';
        $final->id      = 'bt_finalizar';
        $final->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold">
                            <i class="fa-solid fa-check" style="font-size: 2rem;" aria-hidden="true"></i>
                           </div>';
        $final->i_cone .= '<div class="align-items-start txt-bt-manut">Finalizar</div>';
        $final->place   = 'Finalizar a Análise';
        $final->funcChan = 'submeter(\'/Analise/finalizar/\')';
        $final->classep  = 'btn-secondary bt-manut btn-sm mb-2 float-end';
        $ret['bt_finalizar'] = $final->crBotao();

        // Retorna todos os campos
        return $ret;
    }


    public function defCamposAnalise(self $dados, bool $show = false)
    {
        $ret = [];
        // Opções padrão Sim / Não
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // Metodologia de Análise (Classe)
        $met = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $met->valor = $met->selecionado = $dados->cla_metodanalise;
        $met->leitura  = true;
        $met->opcoes   = $simnao;
        $met->funcChan = "mostraOcultaCampo(this,'S','ana_lotemb');mostraOcultaCampo(this,'N','ana_descmetodo')";
        $met->dispForm = '2col';
        $ret['cla_metodanalise'] = $met->cr2opcoes();

        // Liberação sem Microbiologia
        $lsm = new MyCampo('pro_mic_analise', 'ana_liberarsemmicro', false);
        $lsm->valor = $lsm->selecionado = $dados->ana_liberarsemmicro ?? 'N';
        $lsm->leitura  = $show;
        $lsm->opcoes   = $simnao;
        $lsm->dispForm = '2col';
        $lsm->funcChan = "mostraOcultaCampo(this,'N','ana_lotemb,ana_laudo,ana_arqlaudo,ana_liberar,ana_reprovar,ana_descmetodo')";
        $ret['ana_liberarsemmicro'] = $lsm->cr2opcoes();

        // Data do Lote de Embarque
        $dlt = new MyCampo('pro_mic_analise', 'ana_datalotemb', false);
        $dlt->valor = $dados->ana_datalotemb ?? date('Y-m-d');
        $ret['ana_datalotemb'] = $dlt->crOculto();

        // Lote de Embarque
        $lmb = new MyCampo('pro_mic_analise', 'ana_lotemb', false);
        $lmb->valor = $lmb->selecionado = $dados->ana_lotemb ?? '';
        $lmb->tipo        = 'sonumero';
        $lmb->leitura     = $show;
        $lmb->maxLength   = 9;
        $lmb->largura     = 100;
        $lmb->size        = 9;
        $lmb->infotexto   = 'formato XX-DDMMAA';
        $lmb->obrigatorio = true;
        $ret['ana_lotemb'] = $lmb->crInput();

        // Descrição do Método
        $met = new MyCampo('pro_mic_analise', 'ana_descmetodo', false);
        $met->valor = $met->selecionado = $dados->ana_descmetodo ?? '';
        $met->leitura      = $show;
        $met->obrigatorio  = true;
        $ret['ana_descmetodo'] = $met->crInput();

        // Laudo (texto)
        $lau = new MyCampo('pro_mic_analise', 'ana_laudo', false);
        $lau->valor   = $dados->ana_laudo ?? '';
        $lau->leitura = $show;
        $ret['ana_laudo'] = $lau->crInput();

        // Arquivo do Laudo (PDF)
        $arq = new MyCampo();
        $arq->id = $arq->nome = 'ana_arqlaudo';
        $arq->place = 'Arquivo do Laudo';
        $arq->label = 'Laudo PDF';
        $arq->size  = 300;
        $arq->tamanho = 300;
        $arq->valor = '';

        // Só processa arquivo se existir análise salva
        if ($dados->ana_id) {

            // Busca arquivos vinculados à análise
            $arqdb = new ArquivoMonModel();
            $dados_files = $arqdb->getArquivos('Analisa', 'ArqLaudo', $dados->ana_id);

            if (count($dados_files) > 0) {

                // Dados do arquivo existente
                $arqlaudo = $dados_files[0];
                $arquivo  = buscaTipoArquivo($arqlaudo);
                $nome_arq = $arqlaudo->arq_nome ?? '';
                $id       = (string) $arqlaudo->_id;

                $link  = base_url("/Showfile/" . $id);
                $redir = "redirec_blank('$link', event)";

                $arq->funcChan    = $redir;
                $arq->valor       = $nome_arq;
                $arq->selecionado = $arquivo;
                $arq->classep     = 'btn-outline-success';

                // Caso esteja finalizado (status 15), apenas visualização
                if ($dados->stt_id == 15) {

                    $arq->i_cone  = "<div id='view_img_$id' class='show img-thumbnail border border-1'>";
                    $arq->i_cone .= "<img id='img_$id' src='$arquivo' class='img-thumbnail sempadding' style='width:{$arq->size}px;' />";
                    $arq->i_cone .= "</div>";
                    $arq->i_cone .= "<div class='align-items-start txt-bt-manut'>Ver Arquivo do Laudo<br>$nome_arq</div>";

                    $ret['ana_arqlaudo'] = $arq->crLabel() . $arq->crBotao();
                } else {
                    // Upload permitido enquanto não finalizado
                    $arq->leitura   = $show;
                    $arq->tipoArq   = '.pdf';
                    $arq->funcChan  = "validarArquivoPorAccept(this);readURL(this, 'img_$arq->id', $arq->size, $arq->tamanho)";
                    $arq->funcBlur  = $redir;

                    $ret['ana_arqlaudo'] = $arq->crArquivo();
                }
            } else {

                // Nenhum arquivo existente
                $arq->leitura     = $show;
                $arq->tipoArq     = '.pdf';
                $arq->selecionado = "/assets/uploads/tipo_arquivo/vazio.png";
                $arq->funcChan    = "validarArquivoPorAccept(this);readURL(this, 'img_$arq->id', $arq->size, $arq->tamanho)";

                $ret['ana_arqlaudo'] = $arq->crArquivo();
            }
        }

        return $ret;
    }

    // Retorna os campos da análise
    public function defCamposAcoes(self $dados, bool $show = false)
    {
        $opcoes = new CommonModel();
        $ret = [];

        // Opções Sim / Não
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // Controle de Liberação
        $liberar = 'N';
        if (isset($dados->ana_liberar)) {
            $liberar = $dados->ana_liberar;
        } else if ($dados->stt_id == 12) { // EM ANDAMENTO
            $liberar = 'S';
        }

        $lib = new MyCampo('pro_mic_analise', 'ana_liberar', false);
        $lib->valor = $lib->selecionado = $liberar;
        $lib->leitura  = $show;
        $lib->opcoes   = $simnao;
        $lib->funcChan = "reprovar(this,'ana_reprovar');reprovar(this,'ana_liberarsemmicro');mostraOcultaCampo('ana_liberar','S','tmo_id');mudaObrigatorio(this,'S','tmo_id')";
        $lib->dispForm = '2col';
        $ret['ana_liberar'] = $lib->cr2opcoes();

        // Controle de Reprovação
        $reprovar = 'N';
        if (isset($dados->ana_reprovar)) {
            $reprovar = $dados->ana_reprovar;
        } else if ($dados->stt_id == 16) { // EM ANDAMENTO
            $reprovar = 'S';
        }

        $rep = new MyCampo('pro_mic_analise', 'ana_reprovar', false);
        $rep->valor = $rep->selecionado = $reprovar;
        $rep->leitura  = $show;
        $rep->opcoes   = $simnao;
        $rep->dispForm = '2col';
        $rep->funcChan = "reprovar(this,'ana_liberar');reprovar(this,'ana_liberarsemmicro');mostraOcultaCampo('ana_reprovar','S','tmo_id_rep');mudaObrigatorio(this,'S','tmo_id_rep')";
        $ret['ana_reprovar'] = $rep->cr2opcoes();


        // Movimentação (Liberação)
        $ret['tmo_id'] = '';
        // if ($liberar === 'S') {

        $config = [];
        $config['Label']    = 'Movimentação (Liberação)';
        $config['DispForm'] = 'col-6';
        $config['Leitura']  = $show;

        $ret['tmo_id'] = criaSelectRelativo(
            'est_tipo_movimentacao',
            'tmo_id',
            'tmo_nome',
            $dados->tmo_id ?? '',
            1,
            'pro_mic_analise',
            [],
            $config
        );
        // }


        // Movimentação (Reprovação)
        $ret['tmo_id_rep'] = '';
        // if ($reprovar === 'S') {

        $config = [];
        $config['Label']    = 'Movimentação (Reprovação)';
        $config['DispForm'] = 'col-6';
        $config['Leitura']  = $show;

        $ret['tmo_id_rep'] = criaSelectRelativo(
            'est_tipo_movimentacao',
            'tmo_id',
            'tmo_nome',
            $dados->tmo_id_rep ?? '',
            1,
            'pro_mic_analise',
            [],
            $config,
            'tmo_id_rep'
        );
        // }q
        return $ret;
    }
}
