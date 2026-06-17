<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyPdf2025;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;

class CriaPdf2025 extends BaseController
{
    public $data;
    public $anarequisicao;
    public $requisicao;
    public $pdf;
    public $ocorrencia;
    public $busca;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data          = session()->getFlashdata('dados_classe');
        $this->anarequisicao = new MicrobAnaRequisicaoModel();
        $this->requisicao    = new EstoquRequisicaoModel();
        $this->ocorrencia    = new OcorreOcorrenciaModel();
        $this->busca = new BuscasSapiens();
    }

    public function PrintAnaRequisicao($req_id)
    {
        $requis = $this->anarequisicao->getListaRequisicao($req_id);
        // debug($requis);
        if ($requis) {
            $req       = (object) $requis[0];
            $this->pdf = new MyPdf2025(false, false);

            $this->pdf->SetTitle(formata_texto('Requerimento Nº: ' . $req->req_id));
            //     // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numanoversao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('P', 'A4', 0);
            //     // $this->pdf->SetFillColor(220,230,241);
            $this->pdf->SetFont('Arial', '', 14);
            $this->pdf->Rect(10, 10, 190, 12);
            $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
            $this->pdf->EtiqTexto('', $req->cla_cabecalho, 'Arial', 11, 6, 0, 0, 1, 'R');
            $this->pdf->EtiqTexto('', 'N° ' . $req->req_id, 'Arial', 10, 5, 0, 0, 1, 'R');
            $this->pdf->SetFont('Arial', '', 12);
            $this->pdf->ln(4);
            $this->pdf->Rect(10, 10, 190, 37);
            $this->pdf->SetX(15);

            if ($req->req_lotemb != '') {
                $this->pdf->EtiqTexto('Lote: ', $req->req_lotemb, 'Arial', 10, 6, 12, 0, 1, 'L');
            } else {
                $this->pdf->EtiqTexto('Método: ', $req->ana_descmetodo, 'Arial', 10, 6, 16, 0, 1, 'L');
            }
            $this->pdf->SetX(15);
            $this->pdf->EtiqTexto('Data: ', substr(data_br($req->req_data), 0, 10), 'Arial', 10, 6, 12, 0, 1, 'L');
            $this->pdf->SetX(15);
            $this->pdf->EtiqTexto('Responsável: ', $req->usu_login, 'Arial', 10, 6, 27, 0, 0, 'L');

            $this->pdf->SetX(90);
            $this->pdf->EtiqTexto('Horário: ', substr(data_br($req->req_data), 11, 5), 'Arial', 10, 6, 16, 0, 1, 'L');

            $this->pdf->EtiqTexto('', '', 'Arial', 11, 7, 16, 0, 1, 'L');

            $this->pdf->Rect(10, 47, 190, 20 + (5 * count($requis)));
            $this->pdf->SetX(15);
            $this->pdf->EtiqTexto('Produto', '', 'Arial', 10, 6, 15, 0, 0, 'L');
            $this->pdf->SetX(90);
            $this->pdf->EtiqTexto('Fabricante', '', 'Arial', 10, 6, 20, 0, 0, 'L');
            $this->pdf->SetX(150);
            $this->pdf->EtiqTexto('Lote', '', 'Arial', 10, 6, 15, 0, 0, 'L');
            $this->pdf->SetX(175);
            $this->pdf->EtiqTexto('Validade', '', 'Arial', 10, 6, 20, 0, 1, 'L');
            for ($i = 0; $i < count($requis); $i++) {
                $reqi = (object) $requis[$i];
                $this->pdf->SetX(15);
                $this->pdf->SetFont('Arial', '', 8);
                $y = $this->pdf->GetY();
                $this->pdf->MultiCell(75, 4, mb_convert_encoding($reqi->pro_despro, 'ISO-8859-1', 'UTF-8'), 0, 'L');
                $yd = $this->pdf->GetY();
                // $this->pdf->EtiqTexto('', $reqi->pro_despro, 'Arial', 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetY($yd - 4);
                $this->pdf->SetX(90);
                $this->pdf->EtiqTexto('', $reqi->fab_apeFab, 'Arial', 8, 5, 20, 0, 0, 'L');
                $this->pdf->SetX(150);
                $this->pdf->EtiqTexto('', $reqi->lot_lote, 'Arial', 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetX(175);
                $this->pdf->EtiqTexto('', data_br($reqi->lot_validade), 'Arial', 8, 5, 20, 0, 1, 'L');
                $this->pdf->SetY($yd);
            }
            $posy = 47 + 20 + (5 * count($requis));
            $this->pdf->Rect(10, $posy, 190, 14);
            $this->pdf->SetY($posy + 1);
            $this->pdf->SetFont('Arial', '', 9);
            // $this->pdf->Cell(190, 8, mb_convert_encoding($req['cla_rodape'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->pdf->MultiCell(190, 4, mb_convert_encoding($req->cla_rodape, 'ISO-8859-1', 'UTF-8'), 0, 'L');

            $this->pdf->AliasNbPages();

            // $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            // $output = base64_encode($output);
            // echo json_encode(['pdf' => $output]); // Retorne um JSON

            // Captura o PDF como string em vez de enviar direto
            $pdfContent = $this->pdf->Output('S'); // 'S' = retorna como string
            $pdfContent = base64_encode($pdfContent);
            $pdfContent = json_encode(['pdf' => $pdfContent]); // Retorne um JSON

            return $this->response->setBody($pdfContent);
        }
    }

    public function PrintRequisicaoEstoq($req_id)
    {
        // debug($req_id);
        $requisicao = $this->requisicao->getRequisicao($req_id);
        // debug($requisicao, true);

        //    debug($requisicao, true);
        if ($requisicao) {
            $req = $requisicao[0];
            $estoqueOrigem = indexarEstoque(
                $this->busca->buscaEstoqueDeposito($req->req_deporigem)
            );
            // debug($req, true);
            $produtosreq = $this->requisicao->getRequisicaoProdutos($req->req_id);
            $this->pdf   = new MyPdf2025(false, false);

            $this->pdf->SetTitle(formata_texto('Requisição Nº: ' . str_pad($req->req_id, 6, '0', STR_PAD_LEFT)));
            //     // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numano versao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('L', 'A4', 0);
            $this->pdf->SetAutoPageBreak(true, 10); // margem inferior
            //     // $this->pdf->SetFillColor(220,230,241);
            $this->headerLogoRequis($req);

            $this->pdf->EtiqTexto('Data da Requisição: ', data_br($req->req_data), 'Arial', 10, 5, 0, 0, 0, 'L');
            $this->pdf->SetX(230);
            $this->pdf->EtiqTexto('Data para Entrega: ', substr(data_br($req->req_dataentrega), 0, 10), 'Arial', 10, 5, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Tipo de Movimentação: ', $req->tmo_nome, 'Arial', 10, 5, 0, 0, 0, 'L');
            $repetedias = '';
            if ($req->req_repetedias > 0) {
                $repetedias = "Requisição repetida para {$req->req_repetedias} dia(s)";
            }
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('', $repetedias, 'Arial', 10, 5, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Depósito de Origem: ', $req->desdeporigem, 'Arial', 10, 5, 0, 0, 0, 'L');
            $this->pdf->SetX(235);
            $this->pdf->EtiqTexto('Consumo dia Anterior: ', ($req->req_consdiaanterior == 'S') ? 'Sim' : 'Não', 'Arial', 10, 5, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Depósito de Destino: ', $req->desdepdestino, 'Arial', 10, 5, 0, 0, 0, 'L');
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('Percentual de Segurança (%): ', $req->req_percseguranca . '%', 'Arial', 10, 5, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Status: ', $req->stt_nome, 'Arial', 10, 5, 0, 0, 0, 'L');
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('Usuário: ', $req->usu_nome, 'Arial', 10, 5, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Observações: ', '', 'Arial', 10, 5, 0, 0, 1, 'L');
            $texto = $req->req_observacao;
            $linha = $this->pdf->GetY();
            $this->imprimeDescricao($texto, $linha);

            $this->headerColsRequis();

            $this->pdf->SetFont('Arial', '', 7);
            for ($i = 0; $i < count($produtosreq); $i++) {
                $reqi = $produtosreq[$i];
                $x    = $this->pdf->GetX();
                $y    = $this->pdf->GetY();

                $descricao  = mb_convert_encoding($reqi->pro_despro, 'ISO-8859-1', 'UTF-8');
                $fabricante = mb_convert_encoding($reqi->fab_apeFab, 'ISO-8859-1', 'UTF-8');

                // Define largura das células
                $larguraDescricao  = 50;
                $larguraFabricante = 40;

                // Conta número de linhas que cada campo irá ocupar
                $linhasDescricao  = strlen($descricao) > 33 ? 2 : 1;
                $linhasFabricante = strlen($fabricante) > 25 ? 2 : 1;
                $margemInferior   = 10;
                $alturaLinha      = 6;

                if ($this->pdf->GetY() + $alturaLinha > ($this->pdf->GetPageHeight() - $margemInferior)) {
                    $this->pdf->AddPage('L', 'A4', 0);
                    $this->headerLogoRequis($req);
                    $this->headerColsRequis();
                    $this->pdf->SetFont('Arial', '', 7);
                    $y = $this->pdf->GetY();
                }

                $this->pdf->Cell(15, 6, $reqi->pro_codpro, 1, 0, 'C');
                $this->pdf->SetXY($x + 15, $y);
                if ($linhasDescricao > 1) {
                    $this->pdf->MultiCell(50, 3, $descricao, 1, 'L', false);
                } else {
                    $this->pdf->Cell(50, 6, $descricao, 1, 0, 'L');
                }
                $this->pdf->SetXY($x + 65, $y);
                if ($linhasFabricante > 1) {
                    $this->pdf->MultiCell(40, 3, $reqi->fab_apeFab, 1, 'L', false);
                } else {
                    $this->pdf->Cell(40, 6, $reqi->fab_apeFab, 1, 0, 'L');
                }
                $estoqProd = $estoqueOrigem[$reqi->pro_codpro][$reqi->lot_lote][0] ?? null;
                $estoque_origem = $estoqProd
                    ? (int) str_replace('.', '', (string) $estoqProd->quantidadeEstoque)
                    : 0;

                // Move para a próxima célula da mesma linha
                $this->pdf->SetXY($x + 105, $y);

                $colun = $this->pdf->getX();
                $linha = $this->pdf->getY();
                $this->pdf->MultiCell(20, 3, $reqi->lot_lote . ' ' . data_br($reqi->lot_validade), 1, 'C', false);
                $this->pdf->setY($linha);
                $this->pdf->setX($colun + 20);
                // $this->pdf->Cell(20, 6, data_br($reqi->lot_validade), 1, 0, 'C');
                $this->pdf->Cell(8, 6, $estoque_origem, 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->qtd_caixa, 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->rep_multiplicador, 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->rep_seguranca . '%', 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->rep_quantia, 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->rpa_cancelada, 1, 0, 'R');
                $this->pdf->Cell(8, 6, $reqi->rpa_atendida, 1, 0, 'R');
                $x = $this->pdf->GetX();
                if ($reqi->rpa_data != null) {
                    $this->pdf->MultiCell(20, 3, data_br($reqi->rpa_data), 1, 'C', false);
                } else {
                    $this->pdf->Cell(20, 6, data_br($reqi->rpa_data), 1, 0, 'C');
                }
                $this->pdf->SetXY($x + 20, $y);
                $conferencia     = '';
                $dataConferencia = data_br($reqi->rpa_data_conferencia);

                if ($reqi->rpa_conferida == -1) {
                    $conferencia     = 'NA';
                    $dataConferencia = 'NA';
                } elseif ($reqi->rpa_conferida != 0) {
                    $conferencia = $reqi->rpa_conferida;
                }
                $this->pdf->Cell(8, 6, $conferencia, 1, 0, 'R');
                $x = $this->pdf->GetX();
                if (strlen($dataConferencia) > 10) {
                    $this->pdf->MultiCell(20, 3, $dataConferencia, 1, 'C', false);
                } else {
                    $this->pdf->Cell(20, 6, $dataConferencia, 1, 0, 'C');
                }
                // $this->pdf->MultiCell(20, 3, $dataConferencia, 1, 'C');
                $this->pdf->SetXY($x + 20, $y);
                $aprovada     = '';
                $dataInspecao = data_br($reqi->rpa_data_inspecao);

                if ($reqi->rpa_aprovada == -1) {
                    $aprovada     = 'NA';
                    $dataInspecao = 'NA';
                } elseif ($reqi->rpa_aprovada != 0) {
                    $aprovada = $reqi->rpa_aprovada;
                }

                $this->pdf->Cell(8, 6, $aprovada, 1, 0, 'R');
                $x = $this->pdf->GetX();
                if (strlen($dataInspecao) > 10) {
                    $this->pdf->MultiCell(20, 3, $dataInspecao, 1, 'C', false);
                } else {
                    $this->pdf->Cell(20, 6, $dataInspecao, 1, 0, 'C');
                }
                // $this->pdf->MultiCell(20, 3, $dataInspecao, 1, 'C');
                $this->pdf->SetY($y + 6);
                $this->pdf->SetX(10);
            }

            $this->pdf->AliasNbPages();

            // $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            // $output = base64_encode($output);

            // Captura o PDF como string em vez de enviar direto
            $pdfContent = $this->pdf->Output('S'); // 'S' = retorna como string
            $pdfContent = base64_encode($pdfContent);
            $pdfContent = json_encode(['pdf' => $pdfContent]); // Retorne um JSON

            return $this->response->setBody($pdfContent);
        }
    }

    public function headerLogoRequis($req)
    {
        $this->pdf->Rect(10, 10, 280, 12);
        $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
        $this->pdf->EtiqTexto('', 'Requisição N° ' . str_pad($req->req_id, 6, '0', STR_PAD_LEFT), 'Arial', 14, 12, 0, 0, 1, 'R');
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->ln(4);
    }

    public function headerColsRequis()
    {
        $this->pdf->SetFont('Arial', 'B', 8);
        $this->pdf->SetFillColor(220, 220, 220);
        // colunas normais
        $this->pdf->Cell(15, 20, mb_convert_encoding('Cód ERP', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $this->pdf->Cell(50, 20, mb_convert_encoding('Descrição', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $this->pdf->Cell(40, 20, mb_convert_encoding('Fabricante', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $colun = $this->pdf->getX();
        $linha = $this->pdf->getY();
        $this->pdf->MultiCell(20, 4, "\n\nLote\nValidade\n\n", 1, 'C', true);
        $this->pdf->setY($linha);
        $this->pdf->setX($colun + 20);
        // colunas na vertical
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Saldo Origem', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Qtde Caixa', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Multiplica', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('% Seg', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Requisição', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Cancelada', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Atendida', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(20, 20, mb_convert_encoding('Data Atend.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Conferida', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(20, 20, mb_convert_encoding('Data Confer.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(8, 20, mb_convert_encoding('Aprovada', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->RotatedCell(20, 20, mb_convert_encoding('Data Inspec.', 'ISO-8859-1', 'UTF-8'), 1, 1, 'L', true);
    }

    public function imprimeDescricao($texto, $linha)
    {
        $enc = mb_detect_encoding($texto, "UTF-8, ISO-8859-1, Windows-1252", true);
        // Se necessário, converte o texto para UTF-8 (ajuste o parâmetro 'origem' conforme seu caso)
        if ($enc !== 'UTF-8') {
            $texto = mb_convert_encoding($texto, 'UTF-8', $enc);
        }
        $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
        $texto = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        $this->pdf->SetY($linha);
        $this->pdf->SetX(10);
        $this->pdf->MultiCellSafe(95, 5, $texto, 0, 'J');
    }

    public function PrintOcorrencia($oco_id)
    {
        // Busca dados da ocorrência (ajuste o model se o nome for outro)
        $ocorrencias = $this->ocorrencia->getOcorrenciaPdf($oco_id);

        if (! $ocorrencias) {
            echo json_encode(['erro' => 'Ocorrência não encontrada']);
            return;
        }

        $oco = $ocorrencias[0];

        $this->pdf = new MyPdf2025(false, false);

        $this->pdf->SetTitle(formata_texto('Ocorrência Nº: ' . $oco->oco_id));
        $this->pdf->Add_Page('P', 'A4', 0);
        $this->pdf->SetFont('Arial', '', 14);

        // Cabeçalho
        $this->pdf->Rect(10, 10, 190, 12);
        $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
        $this->pdf->EtiqTexto('', 'OCORRÊNCIA', 'Arial', 11, 6, 0, 0, 1, 'R');
        $this->pdf->EtiqTexto('', 'N° ' . $oco->oco_id, 'Arial', 10, 5, 0, 0, 1, 'R');

        // Bloco de informações
        $this->pdf->Rect(10, 22, 190, 25);
        $this->pdf->SetY(24);
        $this->pdf->SetX(15);

        // Informações principais
        $this->pdf->EtiqTexto('Tipo: ', $oco->tpo_nome ?? '', 'Arial', 10, 6, 15, 0, 1, 'L');
        $this->pdf->SetX(15);
        $this->pdf->EtiqTexto('Subtipo: ', $oco->sut_nome ?? '', 'Arial', 10, 6, 15, 0, 1, 'L');
        $this->pdf->SetX(15);
        $this->pdf->EtiqTexto('Produto: ', $oco->pro_despro ?? '', 'Arial', 10, 6, 15, 0, 1, 'L');
        $this->pdf->SetX(15);
        $this->pdf->EtiqTexto('Lote: ', $oco->lot_lote ?? '', 'Arial', 10, 6, 15, 0, 1, 'L');
        $this->pdf->SetX(90);
        $this->pdf->EtiqTexto('Data: ', substr(data_br($oco->oco_data), 0, 16), 'Arial', 10, 6, 16, 0, 1, 'L');

        // Descrição
        $posy = 47;
        $this->pdf->Rect(10, $posy, 190, 40);
        $this->pdf->SetY($posy + 2);

        // Label em negrito
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->MultiCell(190, 5, mb_convert_encoding("Descrição:", 'ISO-8859-1', 'UTF-8'), 0, 'L');

        // Texto normal
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(190, 5, mb_convert_encoding($oco->oco_descricao ?? '', 'ISO-8859-1', 'UTF-8'), 0, 'L');

        $this->pdf->AliasNbPages();

        // Retorno em base64 (IGUAL ao outro método)
        // $output = $this->pdf->Output('S');
        // $output = base64_encode($output);

        // echo json_encode(['pdf' => $output]);
        // Captura o PDF como string em vez de enviar direto
        $pdfContent = $this->pdf->Output('S'); // 'S' = retorna como string
        $pdfContent = base64_encode($pdfContent);
        $pdfContent = json_encode(['pdf' => $pdfContent]); // Retorne um JSON

        return $this->response->setBody($pdfContent);
    }
}
