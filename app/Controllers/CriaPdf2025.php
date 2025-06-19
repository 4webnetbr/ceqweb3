<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyPdf2025;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use DateTime;

class CriaPdf2025 extends BaseController
{
    public $data;
    public $requisicao;
    public $pdf;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data             = session()->getFlashdata('dados_classe');
        $this->requisicao       = new MicrobAnaRequisicaoModel();
    }

    public function PrintAnaRequisicao($req_id)
    {
        $requis = $this->requisicao->getListaRequisicao($req_id);
        // debug($requis);
        if ($requis) {
            $req = $requis[0];
            $this->pdf = new MyPdf2025(false, false);

            $this->pdf->SetTitle(formata_texto('Requerimento Nº: ' . $req['req_id']));
            //     // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numanoversao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('P', 'A4', 0);
            //     // $this->pdf->SetFillColor(220,230,241);
            $this->pdf->SetFont('Arial', '', 14);
            $this->pdf->Rect(10, 10, 190, 12);
            $this->pdf->Image('assets/images/logo-back.png', 11, 10, 15);
            $this->pdf->EtiqTexto('', $req['cla_cabecalho'], 'Arial', 11, 12, 0, 0, 1, 'R');
            $this->pdf->SetFont('Arial', '', 12);
            $this->pdf->ln(4);
            $this->pdf->Rect(10, 10, 190, 37);
            $this->pdf->SetX(15);
            if ($req['req_lotemb'] != '') {
                $this->pdf->EtiqTexto('Lote: ', $req['req_lotemb'], 'Arial', 10, 6, 12, 0, 1, 'L');
            } else {
                $this->pdf->EtiqTexto('Método: ', $req['ana_descmetodo'], 'Arial', 10, 6, 16, 0, 1, 'L');
            }
            $this->pdf->SetX(15);
            $this->pdf->EtiqTexto('Data: ', substr(data_br($req['req_data']), 0, 10), 'Arial', 10, 6, 12, 0, 1, 'L');
            $this->pdf->SetX(15);
            $this->pdf->EtiqTexto('Responsável: ', $req['usu_login'], 'Arial', 10, 6, 27, 0, 0, 'L');
            $this->pdf->SetX(90);
            $this->pdf->EtiqTexto('Horário: ', substr(data_br($req['req_data']), 11, 5), 'Arial', 10, 6, 16, 0, 1, 'L');

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
                $this->pdf->EtiqTexto('', $reqi['pro_despro'], 'Arial', 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetX(90);
                $this->pdf->EtiqTexto('', $reqi['fab_apeFab'], 'Arial', 8, 5, 20, 0, 0, 'L');
                $this->pdf->SetX(150);
                $this->pdf->EtiqTexto('', $reqi['lot_lote'], 'Arial', 8, 5, 15, 0, 0, 'L');
                $this->pdf->SetX(175);
                $this->pdf->EtiqTexto('', data_br($reqi['lot_validade']), 'Arial', 8, 5, 20, 0, 1, 'L');
            }
            $posy = 47 + 20 + (5 * count($requis));
            $this->pdf->Rect(10, $posy, 190, 8);
            $this->pdf->SetY($posy);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->Cell(190, 8, utf8_decode($req['cla_rodape']), 0, 0, 'L');

            $this->pdf->AliasNbPages();

            $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            $output = base64_encode($output);
            echo json_encode(['pdf' => $output]); // Retorne um JSON
        }
    }
}
