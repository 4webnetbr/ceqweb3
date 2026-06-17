<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MymPdf2026;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use CodeIgniter\HTTP\ResponseInterface;

class CriamPdf2026 extends BaseController
{
    public $data;
    public $anarequisicao;
    public $requisicao;
    public MymPdf2026 $pdf;
    public $ocorrencia;
    public $busca;

    public function __construct()
    {
        $this->data          = session()->getFlashdata('dados_classe');
        $this->anarequisicao = new MicrobAnaRequisicaoModel();
        $this->requisicao    = new EstoquRequisicaoModel();
        $this->ocorrencia    = new OcorreOcorrenciaModel();
        $this->busca         = new BuscasSapiens();
    }

    /**
     * Relatório de análise de requisição.
     *
     * $saida:
     * - inline   = abre no navegador
     * - download = força download
     * - save     = salva em disco e retorna JSON com caminho
     * - base64   = retorna JSON {pdf: "..."} para AJAX
     */
    public function PrintAnaRequisicao($req_id, string $saida = 'base64')
    {
        $requis = $this->anarequisicao->getListaRequisicao($req_id);

        if (! $requis) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['erro' => 'Requisição não encontrado']);
        }

        $req       = (object) $requis[0];
        $this->pdf = new MymPdf2026(false, false, 'A4', 'P');
        $this->pdf->titulo('Requisição Nº: ' . $req->req_id);

        $html = $this->htmlAnaRequisicao($req, $requis);
        $this->pdf->html($html);

        return $this->saidaPdf($this->pdf, 'reqanalise_' . $req_id . '.pdf', $saida);
    }

    /**
     * Relatório de requisição de estoque.
     */
    public function PrintRequisicaoEstoq($req_id, string $saida = 'base64')
    {
        $requisicao = $this->requisicao->getRequisicao($req_id);

        if (! $requisicao) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['erro' => 'Requisição não encontrada']);
        }

        $req = $requisicao[0];

        $estoqueOrigem = indexarEstoque(
            $this->busca->buscaEstoqueDeposito($req->req_deporigem)
        );

        $produtosreq = $this->requisicao->getRequisicaoProdutos($req->req_id);

        $this->pdf = new MymPdf2026(false, false, 'A4', 'L');
        $this->pdf->titulo('Requisição Nº: ' . str_pad($req->req_id, 6, '0', STR_PAD_LEFT));

        $html = $this->htmlRequisicaoEstoque($req, $produtosreq, $estoqueOrigem);
        $this->pdf->html($html);

        return $this->saidaPdf($this->pdf, 'requisicao_' . $req_id . '.pdf', $saida);
    }

    /**
     * Relatório de ocorrência.
     */
    public function PrintOcorrencia($oco_id, string $saida = 'base64')
    {
        $ocorrencias = $this->ocorrencia->getOcorrenciaPdf($oco_id);

        if (! $ocorrencias) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['erro' => 'Ocorrência não encontrada']);
        }

        $oco       = $ocorrencias[0];
        $this->pdf = new MymPdf2026(false, false, 'A4', 'P');
        $this->pdf->titulo('Ocorrência Nº: ' . $oco->oco_id);

        $html = $this->htmlOcorrencia($oco);
        $this->pdf->html($html);

        return $this->saidaPdf($this->pdf, 'ocorrencia_' . $oco_id . '.pdf', $saida);
    }

    protected function htmlAnaRequisicao(object $req, array $requis): string
    {
        $logo = $this->logoBack();
        $rows = '';

        foreach ($requis as $item) {
            $reqi = (object) $item;
            $rows .= '
                <tr>
                    <td style="width:75mm;">' . $this->e($reqi->pro_despro ?? '') . '</td>
                    <td style="width:60mm;">' . $this->e($reqi->fab_apeFab ?? '') . '</td>
                    <td style="width:25mm;">' . $this->e($reqi->lot_lote ?? '') . '</td>
                    <td style="width:25mm;">' . $this->e(data_br($reqi->lot_validade ?? '')) . '</td>
                </tr>';
        }

        $loteOuMetodo = '';
        if (($req->req_lotemb ?? '') !== '') {
            $loteOuMetodo = '<span class="label">Lote:</span> ' . $this->e($req->req_lotemb);
        } else {
            $loteOuMetodo = '<span class="label">Método:</span> ' . $this->e($req->ana_descmetodo ?? '');
        }

        return $this->pdf->cssBase() . '
        <div class="pdf-page">
            <table class="header-box">
                <tr>
                    <td style="width:20mm; padding-left:1mm; vertical-align:middle;"><img src="' . $this->e($logo) . '" class="logo"></td>
                    <td style="text-align:right; padding-right:2mm; vertical-align:middle; font-size:11pt;">
                        <strong>' . $this->e($req->cla_cabecalho ?? '') . '</strong><br>
                        N° ' . $this->e((string) $req->req_id) . '
                    </td>
                </tr>
            </table>

            <div class="box" style="min-height:25mm;">
                <table>
                    <tr>
                        <td style="width:50%;">' . $loteOuMetodo . '</td>
                        <td style="width:50%;"></td>
                    </tr>
                    <tr>
                        <td><span class="label">Data:</span> ' . $this->e(substr(data_br($req->req_data ?? ''), 0, 10)) . '</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><span class="label">Responsável:</span> ' . $this->e($req->usu_login ?? '') . '</td>
                        <td><span class="label">Horário:</span> ' . $this->e(substr(data_br($req->req_data ?? ''), 11, 5)) . '</td>
                    </tr>
                </table>
            </div>

            <table class="table-border small" autosize="1">
                <thead>
                    <tr>
                        <th style="width:75mm; text-align:left;">Produto</th>
                        <th style="width:60mm; text-align:left;">Fabricante</th>
                        <th style="width:25mm; text-align:left;">Lote</th>
                        <th style="width:25mm; text-align:left;">Validade</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>

            <div class="box small" style="min-height:10mm;">
                ' . nl2br($this->e($req->cla_rodape ?? '')) . '
            </div>
        </div>';
    }

    protected function htmlRequisicaoEstoque(object $req, array $produtosreq, array $estoqueOrigem): string
    {
        $rows = '';

        foreach ($produtosreq as $reqi) {
            $estoqProd = $estoqueOrigem[$reqi->pro_codpro][$reqi->lot_lote][0] ?? null;
            $estoqueOrigemQtd = $estoqProd
                ? (int) str_replace('.', '', (string) $estoqProd->quantidadeEstoque)
                : 0;

            $conferencia     = '';
            $dataConferencia = data_br($reqi->rpa_data_conferencia ?? '');
            if (($reqi->rpa_conferida ?? 0) == -1) {
                $conferencia     = 'NA';
                $dataConferencia = 'NA';
            } elseif (($reqi->rpa_conferida ?? 0) != 0) {
                $conferencia = (string) $reqi->rpa_conferida;
            }

            $aprovada     = '';
            $dataInspecao = data_br($reqi->rpa_data_inspecao ?? '');
            if (($reqi->rpa_aprovada ?? 0) == -1) {
                $aprovada     = 'NA';
                $dataInspecao = 'NA';
            } elseif (($reqi->rpa_aprovada ?? 0) != 0) {
                $aprovada = (string) $reqi->rpa_aprovada;
            }

            $rows .= '
            <tr>
                <td class="text-center nowrap">' . $this->e($reqi->pro_codpro ?? '') . '</td>
                <td>' . $this->e($reqi->pro_despro ?? '') . '</td>
                <td>' . $this->e($reqi->fab_apeFab ?? '') . '</td>
                <td class="text-center">' . $this->e(($reqi->lot_lote ?? '') . ' ' . data_br($reqi->lot_validade ?? '')) . '</td>
                <td class="text-right">' . $this->e((string) $estoqueOrigemQtd) . '</td>
                <td class="text-right">' . $this->e((string) ($reqi->qtd_caixa ?? '')) . '</td>
                <td class="text-right">' . $this->e((string) ($reqi->rep_multiplicador ?? '')) . '</td>
                <td class="text-right">' . $this->e((string) ($reqi->rep_seguranca ?? '')) . '%</td>
                <td class="text-right">' . $this->e((string) ($reqi->rep_quantia ?? '')) . '</td>
                <td class="text-right">' . $this->e((string) ($reqi->rpa_cancelada ?? '')) . '</td>
                <td class="text-right">' . $this->e((string) ($reqi->rpa_atendida ?? '')) . '</td>
                <td class="text-center">' . $this->e(data_br($reqi->rpa_data ?? '')) . '</td>
                <td class="text-right">' . $this->e($conferencia) . '</td>
                <td class="text-center">' . $this->e($dataConferencia) . '</td>
                <td class="text-right">' . $this->e($aprovada) . '</td>
                <td class="text-center">' . $this->e($dataInspecao) . '</td>
            </tr>';
        }

        $repetedias = '';
        if (($req->req_repetedias ?? 0) > 0) {
            $repetedias = 'Requisição repetida para ' . $req->req_repetedias . ' dia(s)';
        }

        return $this->pdf->cssBase() . '
        <style>
            body { font-size: 7.2pt; }
            .header-title { font-size: 14pt; font-weight:bold; }
            .info td { padding: 0.8mm 0; font-size: 9pt; }
            .req-table th { font-size: 6.4pt; height: 18mm; padding: 0.6mm; }
            .req-table td { font-size: 6.2pt; padding: 0.7mm; }

            @page {
                header: requisicao-header;
                margin-top: 18mm; /* ajuste conforme a altura do seu header-box */
            }
        </style>

        <htmlpageheader name="requisicao-header">
            <table class="header-box">
                <tr>
                    <td style="width:20mm; padding-left:1mm; vertical-align:middle;"><img src="' . $this->e($this->logoBack()) . '" class="logo"></td>
                    <td class="text-right header-title" style="vertical-align:middle; padding-right:2mm;">
                        Requisição N° ' . $this->e(str_pad($req->req_id, 6, '0', STR_PAD_LEFT)) . '
                    </td>
                </tr>
            </table>
        </htmlpageheader>

        <div class="box-info" style="padding-top: 3mm;">
        <table class="info">
            <tr>
                <td><span class="label">Data da Requisição:</span> ' . $this->e(data_br($req->req_data ?? '')) . '</td>
                <td class="text-right"><span class="label">Data para Entrega:</span> ' . $this->e(substr(data_br($req->req_dataentrega ?? ''), 0, 10)) . '</td>
            </tr>
            <tr>
                <td><span class="label">Tipo de Movimentação:</span> ' . $this->e($req->tmo_nome ?? '') . '</td>
                <td class="text-right">' . $this->e($repetedias) . '</td>
            </tr>
            <tr>
                <td><span class="label">Depósito de Origem:</span> ' . $this->e($req->desdeporigem ?? '') . '</td>
                <td class="text-right"><span class="label">Consumo dia Anterior:</span> ' . (($req->req_consdiaanterior ?? '') === 'S' ? 'Sim' : 'Não') . '</td>
            </tr>
            <tr>
                <td><span class="label">Depósito de Destino:</span> ' . $this->e($req->desdepdestino ?? '') . '</td>
                <td class="text-right"><span class="label">Percentual de Segurança (%):</span> ' . $this->e((string) ($req->req_percseguranca ?? '')) . '%</td>
            </tr>
            <tr>
                <td><span class="label">Status:</span> ' . $this->e($req->stt_nome ?? '') . '</td>
                <td class="text-right"><span class="label">Usuário:</span> ' . $this->e($req->usu_nome ?? '') . '</td>
            </tr>
            <tr>
                <td colspan="2"><span class="label">Observações:</span><br>' . nl2br($this->e($req->req_observacao ?? '')) . '</td>
            </tr>
        </table>
        </div>

        <table class="table-border req-table" autosize="1">
            <thead>
                <tr>
                    <th style="width:15mm; text-align:center; vertical-align:middle">Cód ERP</th>
                    <th style="width:50mm; text-align:center; vertical-align:middle">Descrição</th>
                    <th style="width:40mm; text-align:center; vertical-align:middle">Fabricante</th>
                    <th style="width:20mm; text-align:center; vertical-align:middle">Lote<br>Validade</th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Saldo Origem</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Qtde Caixa</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Multiplica</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">% Seg</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Requisição</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Cancelada</div></th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;"><div class="col-12 float-start text-start">Atendida</div></th>
                    <th style="width:20mm; text-align:center; vertical-align:middle">Data<br>Atendimento</th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;">Conferida</th>
                    <th style="width:20mm; text-align:center; vertical-align:middle">Data<br>Conferência</th>
                    <th text-rotate="180" style="width:8mm; height:20mm; text-align:center; vertical-align:bottom;">Aprovada</th>
                    <th style="width:20mm; text-align:center; vertical-align:middle">Data<br>Inspeção</th>
                </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
        </table>';
    }

    protected function htmlOcorrencia(object $oco): string
    {
        return $this->pdf->cssBase() . '
        <div class="pdf-page">
            <table class="header-box">
                <tr>
                    <td style="width:20mm; padding-left:1mm; vertical-align:middle;"><img src="' . $this->e($this->logoBack()) . '" class="logo"></td>
                    <td style="text-align:right; padding-right:2mm; vertical-align:middle; font-size:11pt;">
                        <strong>OCORRÊNCIA</strong><br>
                        N° ' . $this->e((string) ($oco->oco_id ?? '')) . '
                    </td>
                </tr>
            </table>

            <div class="box" style="min-height:21mm;">
                <table>
                    <tr>
                        <td style="width:50%;"><span class="label">Tipo:</span> ' . $this->e($oco->tpo_nome ?? '') . '</td>
                        <td style="width:50%;" rowspan="4"><span class="label">Data:</span> ' . $this->e(substr(data_br($oco->oco_data ?? ''), 0, 16)) . '</td>
                    </tr>
                    <tr><td><span class="label">Subtipo:</span> ' . $this->e($oco->sut_nome ?? '') . '</td></tr>
                    <tr><td><span class="label">Produto:</span> ' . $this->e($oco->pro_despro ?? '') . '</td></tr>
                    <tr><td><span class="label">Lote:</span> ' . $this->e($oco->lot_lote ?? '') . '</td></tr>
                </table>
            </div>

            <div class="box" style="min-height:36mm;">
                <strong>Descrição:</strong><br>
                ' . nl2br($this->e($oco->oco_descricao ?? '')) . '
            </div>
        </div>';
    }

    protected function saidaPdf(MymPdf2026 $pdf, string $filename, string $saida = 'base64', ?string $savePath = null)
    {
        $saida = strtolower($saida);

        if ($saida === 'inline') {
            $content = $pdf->Output($filename, 'I');
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($content);
        }

        if ($saida === 'download') {
            $content = $pdf->Output($filename, 'S');
            return $this->response->download($filename, $content, true);
        }

        if ($saida === 'save') {
            $path = $savePath ?: WRITEPATH . 'uploads/pdf/' . $filename;
            $pdf->save($path);
            return $this->response->setJSON([
                'arquivo' => $filename,
                'path'    => $path,
            ]);
        }

        // Padrão mantido para AJAX, mas agora com Content-Type correto.
        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setBody($pdf->outputBase64Json());
    }

    protected function logoBack(): string
    {
        $path = 'assets/images/logo-back.png';
        if (defined('FCPATH') && is_file(FCPATH . $path)) {
            return FCPATH . $path;
        }

        return $path;
    }

    protected function e(?string $value): string
    {
        $value = (string) $value;
        $enc = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($enc !== 'UTF-8') {
            $value = mb_convert_encoding($value, 'UTF-8', $enc ?: 'ISO-8859-1');
        }

        return htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
