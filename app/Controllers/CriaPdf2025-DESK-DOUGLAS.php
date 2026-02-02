<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyPdf2025;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use DateTime;

class CriaPdf2025 extends BaseController
{
    public $data;
    public $anarequisicao;
    public $requisicao;
    public $pdf;
    public $ocorrencia;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data             = session()->getFlashdata('dados_classe');
        $this->anarequisicao    = new MicrobAnaRequisicaoModel();
        $this->requisicao       = new EstoquRequisicaoModel();
        $this->ocorrencia       = new OcorreOcorrenciaModel();
    }

    public function PrintAnaRequisicao($req_id)
    {
        $requis = $this->anarequisicao->getListaRequisicao($req_id);
        // debug($requis);
        if ($requis) {
            $req = $requis[0];
            $this->pdf = new MyPdf2025(false, false);

            $this->pdf->SetTitle(formata_texto('Requerimento Nº: ' . $req->req_id));
            //     // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numanoversao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('P', 'A4', 0);
            //     // $this->pdf->SetFillColor(220,230,241);
            $this->pdf->SetFont('Arial', '', 14);
            $this->pdf->Rect(10, 10, 190, 12);
            $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
            $this->pdf->EtiqTexto('', $req->cla_cabecalho, 'Arial', 11, 6, 0, 0, 1, 'R');
            $this->pdf->EtiqTexto('', 'N° '.$req->req_id, 'Arial', 10, 5, 0, 0, 1, 'R');
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
                $reqi = $requis[$i];
                $this->pdf->SetX(15);
                $this->pdf->EtiqTexto('', $reqi->pro_despro, 'Arial', 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetX(90);
                $this->pdf->EtiqTexto('', $reqi->fab_apeFab, 'Arial', 8, 5, 20, 0, 0, 'L');
                $this->pdf->SetX(150);
                $this->pdf->EtiqTexto('', $reqi->lot_lote, 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetX(175);
                $this->pdf->EtiqTexto('', data_br($reqi->lot_validade), 'Arial', 8, 5, 20, 0, 1, 'L');
            }
            $posy = 47 + 20 + (5 * count($requis));
            $this->pdf->Rect(10, $posy, 190, 14);
            $this->pdf->SetY($posy+1);
            $this->pdf->SetFont('Arial', '', 9);
            // $this->pdf->Cell(190, 8, utf8_decode($req['cla_rodape']), 0, 0, 'L');
            $this->pdf->MultiCell(190, 4, utf8_decode($req->cla_rodape), 0,'L');

            $this->pdf->AliasNbPages();

            $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            $output = base64_encode($output);
            echo json_encode(['pdf' => $output]); // Retorne um JSON
        }
    }


    public function PrintRequisicaoEstoq($req_id)
    {
        // debug($req_id);
        $requisicao = $this->requisicao->getRequisicao($req_id);
    //    debug($requisicao, true);
        if ($requisicao) {
            $req = $requisicao[0];
            $produtosreq = $this->requisicao->getRequisicaoProdutos($req->req_id);
            // debug($produtosreq, true);
            $this->pdf = new MyPdf2025(false, false);

            $this->pdf->SetTitle(formata_texto('Requisição Nº: ' . $req->req_id));
            //     // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numanoversao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('L', 'A4', 0);
            //     // $this->pdf->SetFillColor(220,230,241);
            $this->pdf->Rect(10, 10, 280, 12);
            $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
            $this->pdf->EtiqTexto('', 'Requisição N° '.$req->req_id, 'Arial', 14, 12, 0, 0, 1, 'R');
            $this->pdf->SetFont('Arial', '', 12);
            $this->pdf->ln(6);

            $this->pdf->EtiqTexto('Data da Requisição: ', substr(data_br($req->req_data), 0, 10), 'Arial', 10, 6, 0, 0, 0, 'L');
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('Data para Entrega: ', substr(data_br($req->req_dataentrega), 0, 10), 'Arial', 10, 6, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Tipo de Movimentação: ', $req->tmo_nome, 'Arial', 10, 6, 0, 0, 0, 'L');
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('',"Repetir pedido para {$req->req_repetedias} dia(s)" , 'Arial', 10, 6, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Consumo dia Anterior: ', ($req->req_consdiaanterior=='S')?'Sim':'Não', 'Arial', 10, 6, 0, 0, 0, 'L');
            $this->pdf->SetX(220);
            $this->pdf->EtiqTexto('Percentual de Segurança (%): ', $req->req_percseguranca.'%', 'Arial', 10, 6, 0, 0, 1, 'R');

            $this->pdf->EtiqTexto('Observações: ', '', 'Arial', 10, 6, 0, 0, 1, 'L');
            $texto = $req->req_observacao;
            $linha = $this->pdf->GetY();
            $this->imprimeDescricao($texto, $linha);
 

            // Cabeçalho da tabela
            $this->pdf->SetFont('Arial','B',9);
            $this->pdf->SetFillColor(200,200,200);

            $this->pdf->Cell(15, 7, utf8_decode('Cód ERP'), 1, 0, 'C', true);
            $this->pdf->Cell(50, 7, utf8_decode('Descrição'), 1, 0, 'C', true);
            $this->pdf->Cell(30, 7, utf8_decode('Fabricante'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Lote'), 1, 0, 'C', true);
            $this->pdf->Cell(25, 7, utf8_decode('Validade'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Qtde Caixa'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Multiplica'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('% Seg'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Requisição'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Cancelada'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Atendida'), 1, 0, 'C', true);
            $this->pdf->Cell(20, 7, utf8_decode('Saldo'), 1, 1, 'C', true);
            
            $this->pdf->SetFont('Arial','',8);
            for ($i = 0; $i < count($produtosreq); $i++) {
                $reqi = $produtosreq[$i];
                $x = $this->pdf->GetX();
                $y = $this->pdf->GetY();    
                
                $descricao = utf8_decode($reqi->pro_despro);
                $fabricante = utf8_decode($reqi->fab_apeFab);

                // Define largura das células
                $larguraDescricao = 50;
                $larguraFabricante = 30;

                // Conta número de linhas que cada campo irá ocupar
                $linhasDescricao = strlen($descricao)> 28?2:1;
                $linhasFabricante = strlen($fabricante)> 15?2:1;

                // Se algum dos dois ocupa mais de 1 linha, altura total é 8
                $alturaTotal = ($linhasDescricao > 1 || $linhasFabricante > 1) ? 8 : 6;
                $alturaCelula = ($alturaTotal == 8) ? 4 : 6;

                $this->pdf->Cell(15, $alturaTotal, $reqi->pro_codpro, 1, 0,'C');
                $this->pdf->SetXY($x + 15, $y);
                if($linhasDescricao > 1){
                    $this->pdf->MultiCell($larguraDescricao, $alturaCelula, $descricao, 1);
                } else {
                    $this->pdf->MultiCell($larguraDescricao, $alturaTotal, $descricao, 1);
                }

                $this->pdf->SetXY($x + 15 + $larguraDescricao, $y);

                if($linhasFabricante > 1){
                    $this->pdf->MultiCell($larguraFabricante, $alturaCelula, $reqi->fab_apeFab, 1);
                } else {
                    $this->pdf->MultiCell($larguraFabricante, $alturaTotal, $reqi->fab_apeFab, 1);
                }
                $altura = $this->pdf->GetY() - $y; // altura real da célula quebrada

                // Move para a próxima célula da mesma linha
                $this->pdf->SetXY($x + 15 + $larguraDescricao + $larguraFabricante, $y);                

                $this->pdf->Cell(20, $alturaTotal, $reqi->lot_lote, 1, 0, 'C');
                $this->pdf->Cell(25, $alturaTotal, data_br($reqi->lot_validade), 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->qtd_caixa, 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rep_multiplicador, 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rep_seguranca . '%', 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rep_quantia, 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rpa_cancelada, 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rpa_atendida, 1, 0, 'C');
                $this->pdf->Cell(20, $alturaTotal, $reqi->rep_quantia - ($reqi->rpa_cancelada + $reqi->rpa_atendida), 1, 1, 'C');
            }

            $this->pdf->AliasNbPages();

            $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            $output = base64_encode($output);
            echo json_encode(['pdf' => $output]); // Retorne um JSON
        }
    }

    function imprimeDescricao($texto, $linha){
        $enc = mb_detect_encoding($texto, "UTF-8, ISO-8859-1, Windows-1252", true);
        // Se necessário, converte o texto para UTF-8 (ajuste o parâmetro 'origem' conforme seu caso)
        if ($enc !== 'UTF-8') {
            $texto = mb_convert_encoding($texto, 'UTF-8', $enc);
        }
        $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
        $texto = utf8_decode($texto);
        $this->pdf->SetY($linha);
        $this->pdf->SetX(10);
        $this->pdf->MultiCellSafe(95, 5, $texto, 0, 'J');
    }

    public function PrintOcorrencia($oco_id)
    {
        // Busca dados da ocorrência (ajuste o model se o nome for outro)
        $ocorrencias = $this->ocorrencia->getListaOcorrenciaPdf($oco_id);
    
        if (!$ocorrencias) {
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
        $this->pdf->EtiqTexto(
            'Data: ',
            substr(data_br($oco->oco_data), 0, 16),
            'Arial',
            10,
            6,
            16,
            0,
            1,
            'L'
        );
    
        // Descrição
        $posy = 47;
        $this->pdf->Rect(10, $posy, 190, 40);
        $this->pdf->SetY($posy + 2);
        
        // Label em negrito
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->MultiCell(
            190,
            5,
            utf8_decode("Descrição:"),
            0,
            'L'
        );
        
        // Texto normal
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->MultiCell(
            190,
            5,
            utf8_decode($oco->oco_descricao ?? ''),
            0,
            'L'
        );
    
        $this->pdf->AliasNbPages();
    
        // Retorno em base64 (IGUAL ao outro método)
        $output = $this->pdf->Output('S');
        $output = base64_encode($output);
    
        echo json_encode(['pdf' => $output]);
    }
}
