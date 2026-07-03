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
        $req_ids_assoc = array_map(
            fn($r) => $r->req_id,
            $requisicao
        );
        $log = buscaLogTabelaFirst('est_requisicao', $req_ids_assoc);
        $req->usu_nome = buscaUsuarioLog($log[$req->req_id]);

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

    // ─────────────────────────────────────────────────────────────────────────
    //  Relatório Genérico (Gerador de Relatórios)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Gera PDF do relatório genérico a partir da configuração salva no banco.
     */
    public function PrintRelatorioGenerico(int $rel_id, string $saida = 'base64')
    {
        $db = db_connect('default');

        $relatorio = $db->table('cfg_relatorios')->where('rel_id', $rel_id)->get()->getFirstRow();
        if (!$relatorio) {
            return $this->response->setStatusCode(404)->setJSON(['erro' => 'Relatório não encontrado']);
        }

        $colunasBD = $db->table('cfg_rel_colunas')->where('rel_id', $rel_id)->orderBy('rco_ordem')->get()->getResult();
        $filtrosBD = $db->table('cfg_rel_filtros')->where('rel_id', $rel_id)->orderBy('rfi_ordem')->get()->getResult();

        $colunas = [];
        foreach ($colunasBD as $col) {
            $colunas[] = [
                'tabela'      => $col->rco_tabela,
                'campo'       => $col->rco_campo,
                'tamanho'     => (int) $col->rco_tamanho,
                'tipo_dado'   => $col->rco_tipo_dado ?? '',
                'label'       => $col->rco_label,
                'alinhamento' => $col->rco_alinhamento,
                'totalizar'   => (int) ($col->rco_totalizar ?? 0),
            ];
        }

        $filtros = [];
        foreach ($filtrosBD as $f) {
            $filtros[] = [
                'campo'       => $f->rfi_campo,
                'tabela'      => $f->rfi_tabela,
                'tipo_filtro' => $f->rfi_tipo_filtro,
                'label'       => $f->rfi_label,
            ];
        }

        $config = [
            'titulo'              => $relatorio->rel_titulo,
            'nome'                => $relatorio->rel_nome ?? '',
            'formato'             => $relatorio->rel_formato,
            'tamanho_fonte'       => (int) $relatorio->rel_tamanho_fonte,
            'tabela_base'         => $relatorio->rel_tabela_base,
            'totalizar_registros' => (int) ($relatorio->rel_totalizar_registros ?? 0),
            'colunas'             => $colunas,
            'filtros'             => $filtros,
        ];

        $orientacao = $relatorio->rel_formato === 'L' ? 'L' : 'P';
        // ANTES (BKP 30/06/2026): $this->pdf = new MymPdf2026(true, true, 'A4', $orientacao);
        // temHeader=false: com $paraPdf=true, htmlRelatorioGenerico() já injeta o cabeçalho
        // (logo/título/subtítulo) via <htmlpageheader> nativo do mPDF — temHeader=true aqui
        // duplicaria com o cabeçalho automático da própria MymPdf2026.
        $this->pdf  = new MymPdf2026(false, true, 'A4', $orientacao);
        $this->pdf->titulo($config['titulo']);

        // ANTES (BKP 30/06/2026): $html = $this->htmlRelatorioGenerico($config, false);
        $html = $this->htmlRelatorioGenerico($config, false, [], true);
        $this->pdf->html($html);

        return $this->saidaPdf($this->pdf, 'relatorio_' . $rel_id . '.pdf', $saida);
    }

    /**
     * Resolve schema, monta e executa o SQL do relatório (com WHERE dos
     * filtros reais quando aplicável) e enriquece $dados com
     * usuario_inc/usuario_alt via MongoDB. Extraído de htmlRelatorioGenerico()
     * pra ser reaproveitado pelos exportadores Excel/Word (ver
     * exportarExcelRelatorio()/exportarWordRelatorio() abaixo), que precisam
     * dos mesmos dados filtrados sem montar HTML.
     *
     * @param array $config  titulo, formato, tamanho_fonte, tabela_base, colunas[], filtros[], totalizar_registros
     * @param bool  $preview true = LIMIT 10 sem WHERE, false = filtros reais
     * @param array $valoresFiltro valores selecionados pelo usuário p/ montar WHERE real
     * @return array{dados: array, colunas: array}
     */
    protected function _buscarDadosRelatorio(array $config, bool $preview = false, array $valoresFiltro = []): array
    {
        $tabelaBase = $config['tabela_base'] ?? '';
        $colunas    = $config['colunas'] ?? [];

        if (empty($tabelaBase) || empty($colunas)) {
            return ['dados' => [], 'colunas' => $colunas];
        }

        $dicDados = new \App\Models\Config\ConfigDicDadosModel();

        // Resolver schema para cada tabela
        $resolverSchema = function (string $tabela) use ($dicDados): string {
            static $cache = [];
            if (!isset($cache[$tabela])) {
                $info = $dicDados->getDbGroupAndSchema($tabela);
                $cache[$tabela] = !empty($info['schema']) ? $info['schema'] . '.' . $tabela : $tabela;
            }
            return $cache[$tabela];
        };

        // ── Buscar dados ────────────────────────────────────────────────
        $dados = [];
        try {
            $tabelaBaseSchema = $resolverSchema($tabelaBase);

            $selCols = [];
            $tabelasJoin = [];
            foreach ($colunas as $col) {
                $tabSchema = $resolverSchema($col['tabela']);
                $selCols[] = "{$tabSchema}.{$col['campo']} AS '{$col['label']}'";
                if ($col['tabela'] !== $tabelaBase && !in_array($col['tabela'], $tabelasJoin)) {
                    $tabelasJoin[] = $col['tabela'];
                }
            }

            $dbGrSche = $dicDados->getDbGroupAndSchema($tabelaBase);
            $dbConn   = db_connect($dbGrSche['dbGroup']);

            $sql = 'SELECT ' . implode(', ', $selCols) . ' FROM ' . $tabelaBaseSchema;

            if (!empty($tabelasJoin)) {
                // Mapa de relacionamentos por FK constraint
                // Chave: tabela referenciada → condição ON
                $relMap = [];

                $buildRelMap = function (string $tab) use ($dicDados, $resolverSchema, &$relMap) {
                    $rels = $dicDados->getRelacionamentos($tab);
                    foreach ($rels['relacionamentos'] as $r) {
                        if (empty($r['REFERENCED_TABLE_NAME'])) continue;

                        // FK direta: $tab.coluna → referenciada.coluna
                        if ($r['TABLE_NAME'] === $tab) {
                            $chave = $tab . '→' . $r['REFERENCED_TABLE_NAME'];
                            if (!isset($relMap[$chave])) {
                                $relMap[$chave] = [
                                    'de'    => $tab,
                                    'para'  => $r['REFERENCED_TABLE_NAME'],
                                    'on'    => $resolverSchema($tab) . '.' . $r['COLUMN_NAME']
                                        . ' = ' . $resolverSchema($r['REFERENCED_TABLE_NAME']) . '.' . $r['REFERENCED_COLUMN_NAME'],
                                ];
                            }
                        }
                    }
                };

                // Carrega relacionamentos da base e de todas as tabelas a ligar
                $buildRelMap($tabelaBase);
                foreach ($tabelasJoin as $tj) {
                    $buildRelMap($tj);
                }

                // Resolve JOINs transitivos
                $joinedTables = [$tabelaBase];
                $pending      = $tabelasJoin;
                $maxIter      = 10;

                while (!empty($pending) && $maxIter-- > 0) {
                    $nextPending = [];

                    foreach ($pending as $tj) {
                        $tabJoinSchema = $resolverSchema($tj);
                        $on = '';

                        foreach ($joinedTables as $jt) {
                            // Caso 1: $jt tem FK para $tj (jt referencia tj)
                            $chave1 = $jt . '→' . $tj;
                            if (isset($relMap[$chave1])) {
                                $on = $relMap[$chave1]['on'];
                                break;
                            }

                            // Caso 2: $tj tem FK para $jt (tj referencia jt — filha)
                            $chave2 = $tj . '→' . $jt;
                            if (isset($relMap[$chave2])) {
                                $on = $relMap[$chave2]['on'];
                                break;
                            }
                        }

                        if ($on) {
                            $sql .= " LEFT JOIN {$tabJoinSchema} ON {$on}";
                            $joinedTables[] = $tj;
                            $buildRelMap($tj);
                        } else {
                            // Descobre tabelas intermediárias que $tj referencia
                            foreach ($relMap as $chave => $rel) {
                                if ($rel['de'] === $tj && !in_array($rel['para'], $joinedTables) && !in_array($rel['para'], $nextPending) && !in_array($rel['para'], $pending)) {
                                    $nextPending[] = $rel['para'];
                                    $buildRelMap($rel['para']);
                                }
                                if ($rel['para'] === $tj && !in_array($rel['de'], $joinedTables) && !in_array($rel['de'], $nextPending) && !in_array($rel['de'], $pending)) {
                                    $nextPending[] = $rel['de'];
                                    $buildRelMap($rel['de']);
                                }
                            }
                            $nextPending[] = $tj;
                        }
                    }

                    $pending = $nextPending;
                }
            }

            // Aplica filtros reais (WHERE), sempre qualificados pela tabela base
            // (rfi_tabela == rel_tabela_base sempre — ver Utils\Relatorio::gerar())
            $bindings = [];
            if (!$preview && !empty($valoresFiltro)) {
                $whereParts = [];
                foreach ($valoresFiltro as $vf) {
                    $campoQualificado = $tabelaBaseSchema . '.' . $vf['campo'];

                    if (($vf['tipo_filtro'] ?? '') === 'DATE') {
                        if (!empty($vf['de']) && !empty($vf['ate'])) {
                            $whereParts[] = "{$campoQualificado} BETWEEN ? AND ?";
                            $bindings[]   = $vf['de'];
                            $bindings[]   = $vf['ate'];
                        }
                    } else {
                        $valores = $vf['valores'] ?? [];
                        if (!empty($valores)) {
                            $placeholders = implode(',', array_fill(0, count($valores), '?'));
                            $whereParts[] = "{$campoQualificado} IN ({$placeholders})";
                            foreach ($valores as $val) {
                                $bindings[] = $val;
                            }
                        }
                    }
                }
                if (!empty($whereParts)) {
                    $sql .= ' WHERE ' . implode(' AND ', $whereParts);
                }
            }

            // Preview: últimos 10 registros (ORDER BY PK DESC + LIMIT, depois inverte)
            if ($preview) {
                $pkPrev = '';
                $camposPrev = $dicDados->getCampos($tabelaBase);
                foreach ($camposPrev as $cp) {
                    if ($cp['COLUMN_KEY'] === 'PRI') {
                        $pkPrev = $cp['COLUMN_NAME'];
                        break;
                    }
                }
                if (!$pkPrev && !empty($camposPrev)) {
                    $pkPrev = $camposPrev[0]['COLUMN_NAME'];
                }
                if ($pkPrev) {
                    $sql .= ' ORDER BY ' . $tabelaBaseSchema . '.' . $pkPrev . ' DESC';
                }
                $sql .= ' LIMIT 10';
            }

            $result = $dbConn->query($sql, $bindings);
            $dados  = $result ? $result->getResultArray() : [];

            // Preview veio DESC, inverte para exibir na ordem correta
            if ($preview && !empty($dados)) {
                $dados = array_reverse($dados);
            }
        } catch (\Throwable $e) {
            $sql   = $sql ?? '';
            $dados = [];
        }

        // ── Preenche usuario_inc/usu_nome (primeiro log) e usuario_alt (último log) via MongoDB
        $temUsuInc = false;
        $temUsuAlt = false;
        $labelsUsuInc = [];
        $labelUsuAlt  = '';
        foreach ($colunas as $col) {
            // usu_nome segue a mesma regra de usuario_inc (primeiro log = quem incluiu)
            if (in_array($col['campo'], ['usuario_inc', 'usu_nome'])) {
                $temUsuInc = true;
                $labelsUsuInc[] = $col['label'];
            }
            if ($col['campo'] === 'usuario_alt') {
                $temUsuAlt = true;
                $labelUsuAlt = $col['label'];
            }
        }

        if (($temUsuInc || $temUsuAlt) && !empty($dados) && !empty($tabelaBase)) {
            // Descobre a tabela real para log — se for view, extrai do VIEW_DEFINITION
            $tabelaLog = $tabelaBase;
            $pkBase    = '';
            $camposBase = $dicDados->getCampos($tabelaBase);
            foreach ($camposBase as $cb) {
                if ($cb['COLUMN_KEY'] === 'PRI') {
                    $pkBase = $cb['COLUMN_NAME'];
                    break;
                }
            }

            // Se não tem PK, é view — busca a tabela que tem o primeiro campo da view como PK
            if (!$pkBase && !empty($camposBase)) {
                $primeiroCampo = $camposBase[0]['COLUMN_NAME'];
                $pkBase = $primeiroCampo;

                // Busca a BASE TABLE onde esse campo é PK — essa é a tabela original da view
                $dbGrView = $dicDados->getDbGroupAndSchema($tabelaBase);
                $dbView   = db_connect($dbGrView['dbGroup']);
                $resPk    = $dbView->table('information_schema.COLUMNS')
                    ->select('TABLE_NAME')
                    ->where('COLUMN_NAME', $primeiroCampo)
                    ->where('COLUMN_KEY', 'PRI')
                    ->where('TABLE_SCHEMA', $dbGrView['schema'])
                    ->get();
                $rowPk = $resPk ? $resPk->getFirstRow() : null;
                if ($rowPk) {
                    $tabelaLog = $rowPk->TABLE_NAME;
                }
            }

            if ($pkBase) {
                $ids = array_column($dados, $pkBase) ?: array_map(fn($r) => $r[array_key_first($r)] ?? '', $dados);
                // MongoDB armazena log_id_registro como string sem zeros à esquerda
                $ids = array_map(fn($v) => (string) (int) $v, array_filter($ids));
                if (!empty($ids)) {
                    if ($temUsuInc && function_exists('buscaLogTabelaFirst')) {
                        // Usa a tabela real (não a view) para buscar log
                        $logsFirst = buscaLogTabelaFirst($tabelaLog, $ids);
                        foreach ($dados as &$row) {
                            // Primeira chave do row (label) → valor → inteiro (sem zeros à esquerda)
                            $id = (string) (int) reset($row);
                            $nome = isset($logsFirst[$id]) ? buscaUsuarioLog($logsFirst[$id]) : '';
                            foreach ($labelsUsuInc as $lbl) {
                                $row[$lbl] = $nome;
                            }
                        }
                        unset($row);
                    }

                    if ($temUsuAlt && function_exists('buscaLogTabela')) {
                        // Usa a tabela real (não a view) para buscar log
                        $logsLast = buscaLogTabela($tabelaLog, $ids);
                        foreach ($dados as &$row) {
                            $id = (string) (int) reset($row);
                            $row[$labelUsuAlt] = isset($logsLast[$id]) ? buscaUsuarioLog($logsLast[$id]) : '';
                        }
                        unset($row);
                    }
                }
            }
        }

        return ['dados' => $dados, 'colunas' => $colunas];
    }

    /**
     * Monta as partes do subtítulo (um "Label: valor" por filtro efetivamente
     * selecionado) — mesma regra usada no HTML/PDF (ver htmlRelatorioGenerico()),
     * reaproveitada aqui pelos exportadores Excel/Word.
     */
    private function _subtituloFiltros(array $filtros, array $valoresFiltro): array
    {
        $subParts = [];
        foreach ($filtros as $f) {
            $vfMatch = null;
            foreach ($valoresFiltro as $vf) {
                if ($vf['campo'] === $f['campo']) {
                    $vfMatch = $vf;
                    break;
                }
            }
            if (!$vfMatch) {
                continue;
            }
            if ($f['tipo_filtro'] === 'DATE') {
                // 'de'/'ate' vêm com hora (00:00:00/23:59:59, ver Utils\Relatorio::
                // _prepararRelatorioFiltrado()) pro BETWEEN cobrir o dia inteiro em
                // colunas DATETIME; no subtítulo mostramos só a data (sem a hora).
                $txt = data_br(substr($vfMatch['de'], 0, 10)) . ' a ' . data_br(substr($vfMatch['ate'], 0, 10));
            } else {
                $txt = implode(', ', $vfMatch['valores']);
            }
            $subParts[] = $f['label'] . ': ' . $txt;
        }
        return $subParts;
    }

    /**
     * Exporta o relatório em Excel (.xlsx) com os mesmos dados/filtros usados
     * no HTML (ver _buscarDadosRelatorio()). Requer phpoffice/phpspreadsheet
     * (composer require phpoffice/phpspreadsheet).
     *
     * @param array $config  mesma estrutura usada em htmlRelatorioGenerico()
     * @param array $valoresFiltro valores selecionados pelo usuário
     * @return string conteúdo binário do .xlsx pronto pra ser devolvido na resposta
     */
    public function exportarExcelRelatorio(array $config, array $valoresFiltro = []): string
    {
        $resultado = $this->_buscarDadosRelatorio($config, false, $valoresFiltro);
        $dados     = $resultado['dados'];
        $colunas   = $resultado['colunas'];

        // Mesma separação usada no HTML/PDF: colunas "linha inteira" (ex.: Observação)
        // não ficam lado a lado — viram uma linha própria mesclada abaixo do registro.
        $colsNormais  = [];
        $colsLinhaInt = [];
        foreach ($colunas as $col) {
            if (($col['comportamento'] ?? 'cortar') === 'linha') {
                $colsLinhaInt[] = $col;
            } else {
                $colsNormais[] = $col;
            }
        }
        $numColunas = max(1, count($colsNormais));

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr((string) ($config['titulo'] ?? 'Relatorio'), 0, 31));

        $ultimaColuna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numColunas);

        // Logo (coluna A, linha 1)
        $logoPath = $this->logoBack();
        if (is_file($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(45);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Título (linha 1, ao lado da logo)
        $sheet->mergeCells("B1:{$ultimaColuna}1");
        $sheet->setCellValue('B1', $config['titulo'] ?? '');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtítulo (linha 2) — só os filtros efetivamente selecionados
        $subParts = $this->_subtituloFiltros($config['filtros'] ?? [], $valoresFiltro);
        if (!empty($subParts)) {
            $sheet->mergeCells("B2:{$ultimaColuna}2");
            $sheet->setCellValue('B2', implode(' | ', $subParts));
            $sheet->getStyle('B2')->getFont()->setItalic(true)->setSize(10);
            $sheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Cabeçalho das colunas (linha 4, com uma linha em branco de respiro)
        $linhaCabecalho = 4;
        $colIdx = 1;
        foreach ($colsNormais as $col) {
            $sheet->setCellValueByColumnAndRow($colIdx, $linhaCabecalho, $col['label']);
            $colIdx++;
        }
        $sheet->getStyle([1, $linhaCabecalho, $numColunas, $linhaCabecalho])->getFont()->setBold(true);
        $sheet->getStyle([1, $linhaCabecalho, $numColunas, $linhaCabecalho])->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DCDCDC');

        // Congela o cabeçalho na tela e repete nas páginas ao imprimir
        $sheet->freezePane('A' . ($linhaCabecalho + 1));
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $linhaCabecalho);

        $totais       = array_fill(0, count($colsNormais), 0.0);
        $numRegistros = 0;
        $linha        = $linhaCabecalho + 1;
        foreach ($dados as $row) {
            $colIdx = 1;
            foreach ($colsNormais as $idx => $col) {
                $valor = $row[$col['label']] ?? '';
                $sheet->setCellValueByColumnAndRow($colIdx, $linha, $valor);
                if (!empty($col['totalizar']) && is_numeric($valor)) {
                    $totais[$idx] += (float) $valor;
                }
                $colIdx++;
            }
            $linha++;
            $numRegistros++;

            // Colunas "linha inteira" — uma linha mesclada abaixo do registro,
            // igual ao comportamento no HTML/PDF (só se tiver valor).
            foreach ($colsLinhaInt as $col) {
                $valor = $row[$col['label']] ?? '';
                if ($valor === '' || $valor === null) {
                    continue;
                }
                $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
                $sheet->setCellValue("A{$linha}", $col['label'] . ': ' . $valor);
                $sheet->getStyle("A{$linha}")->getFont()->setItalic(true)->setSize(9);
                $linha++;
            }
        }

        // Totalizadores por coluna (rco_totalizar) — mesma regra do HTML/PDF
        $temTotalizador = false;
        foreach ($colsNormais as $col) {
            if (!empty($col['totalizar'])) {
                $temTotalizador = true;
                break;
            }
        }
        if ($temTotalizador) {
            $colIdx = 1;
            foreach ($colsNormais as $idx => $col) {
                if (!empty($col['totalizar'])) {
                    $sheet->setCellValueByColumnAndRow($colIdx, $linha, $totais[$idx]);
                    $sheet->getStyleByColumnAndRow($colIdx, $linha)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                $colIdx++;
            }
            $sheet->getStyle([1, $linha, $numColunas, $linha])->getFont()->setBold(true);
            $linha++;
        }

        // Total de registros (rel_totalizar_registros)
        if (!empty($config['totalizar_registros'])) {
            $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
            $sheet->setCellValue("A{$linha}", 'Total de registros: ' . $numRegistros);
            $sheet->getStyle("A{$linha}")->getFont()->setBold(true);
            $sheet->getStyle("A{$linha}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }

        foreach (range(1, $numColunas) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Exporta o relatório em Word (.docx) com os mesmos dados/filtros usados
     * no HTML. Requer phpoffice/phpword (composer require phpoffice/phpword).
     *
     * @param array $config  mesma estrutura usada em htmlRelatorioGenerico()
     * @param array $valoresFiltro valores selecionados pelo usuário
     * @return string conteúdo binário do .docx pronto pra ser devolvido na resposta
     */
    public function exportarWordRelatorio(array $config, array $valoresFiltro = []): string
    {
        $resultado = $this->_buscarDadosRelatorio($config, false, $valoresFiltro);
        $dados      = $resultado['dados'];
        $colunas    = $resultado['colunas'];
        $paisagem   = ($config['formato'] ?? 'P') === 'L';

        // Mesma separação usada no HTML/PDF: colunas "linha inteira" (ex.: Observação)
        // não ficam lado a lado — viram uma linha própria mesclada abaixo do registro.
        $colsNormais  = [];
        $colsLinhaInt = [];
        foreach ($colunas as $col) {
            if (($col['comportamento'] ?? 'cortar') === 'linha') {
                $colsLinhaInt[] = $col;
            } else {
                $colsNormais[] = $col;
            }
        }
        $numColunas = max(1, count($colsNormais));

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection([
            'orientation' => $paisagem ? 'landscape' : 'portrait',
        ]);

        // Largura das colunas distribuída pelo espaço realmente disponível na página
        // (A4, margem padrão de 1440 twips/1" de cada lado) — antes cada célula tinha
        // 2000 twips fixos, e com várias colunas a soma passava da largura da página,
        // cortando as últimas colunas pra fora da folha.
        $larguraDisponivel = $paisagem ? 13958 : 9026;
        $larguraColuna     = intdiv($larguraDisponivel, $numColunas);

        // Cabeçalho de página (logo + título + subtítulo) — usa o header nativo do
        // Word, que repete IGUAL em toda página (antes ficava só no corpo do texto,
        // por isso só aparecia na 1ª página). Logo e título ficam numa tabela sem
        // borda, lado a lado (mesma altura) — ANTES (BKP 01/07/2026) cada um era
        // adicionado direto no header, o que empilha um embaixo do outro no Word.
        $header = $section->addHeader();
        $tabelaCabecalho = $header->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $tabelaCabecalho->addRow();

        $larguraLogo = 1500;
        $celulaLogo  = $tabelaCabecalho->addCell($larguraLogo, ['valign' => 'center']);
        $logoPath    = $this->logoBack();
        if (is_file($logoPath)) {
            $celulaLogo->addImage($logoPath, ['height' => 40]);
        }

        $celulaTitulo = $tabelaCabecalho->addCell($larguraDisponivel - $larguraLogo, ['valign' => 'center']);
        $celulaTitulo->addText(
            $this->e($config['titulo'] ?? ''),
            ['bold' => true, 'size' => 14],
            ['alignment' => 'center']
        );
        $subParts = $this->_subtituloFiltros($config['filtros'] ?? [], $valoresFiltro);
        if (!empty($subParts)) {
            $celulaTitulo->addText(
                $this->e(implode(' | ', $subParts)),
                ['italic' => true, 'size' => 9, 'color' => '555555'],
                ['alignment' => 'center']
            );
        }

        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '999999',
            'width'       => 100 * 50,
            'unit'        => 'pct',
        ]);

        $table->addRow(null, ['tblHeader' => true]);
        foreach ($colsNormais as $col) {
            $table->addCell($larguraColuna)->addText($this->e($col['label']), ['bold' => true]);
        }

        $totais       = array_fill(0, count($colsNormais), 0.0);
        $numRegistros = 0;
        foreach ($dados as $row) {
            $table->addRow();
            foreach ($colsNormais as $idx => $col) {
                $valor = $row[$col['label']] ?? '';
                $table->addCell($larguraColuna)->addText($this->e((string) $valor));
                if (!empty($col['totalizar']) && is_numeric($valor)) {
                    $totais[$idx] += (float) $valor;
                }
            }
            $numRegistros++;

            // Colunas "linha inteira" — uma linha só, célula mesclada (gridSpan)
            // abaixo do registro, igual ao comportamento no HTML/PDF.
            foreach ($colsLinhaInt as $col) {
                $valor = $row[$col['label']] ?? '';
                if ($valor === '' || $valor === null) {
                    continue;
                }
                $table->addRow();
                $celula = $table->addCell($larguraDisponivel, ['gridSpan' => $numColunas]);
                $celula->addText($this->e($col['label'] . ': ' . (string) $valor), ['italic' => true, 'size' => 9]);
            }
        }

        // Totalizadores por coluna (rco_totalizar) — mesma regra do HTML/PDF
        $temTotalizador = false;
        foreach ($colsNormais as $col) {
            if (!empty($col['totalizar'])) {
                $temTotalizador = true;
                break;
            }
        }
        if ($temTotalizador) {
            $table->addRow();
            foreach ($colsNormais as $idx => $col) {
                $valorTxt = !empty($col['totalizar']) ? number_format($totais[$idx], 2, ',', '.') : '';
                $table->addCell($larguraColuna)->addText($this->e($valorTxt), ['bold' => true]);
            }
        }

        // Total de registros (rel_totalizar_registros)
        if (!empty($config['totalizar_registros'])) {
            $table->addRow();
            $celula = $table->addCell($larguraDisponivel, ['gridSpan' => $numColunas]);
            $celula->addText(
                $this->e('Total de registros: ' . $numRegistros),
                ['bold' => true],
                ['alignment' => 'right']
            );
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Gera HTML do relatório genérico.
     * Usado para preview (HTML direto) e para PDF real (alimenta mPDF).
     *
     * @param array $config  titulo, formato, tamanho_fonte, tabela_base, colunas[], filtros[], totalizar_registros
     * @param bool  $preview true = LIMIT 10 sem WHERE, false = filtros reais
     * @param array $valoresFiltro valores selecionados pelo usuário p/ montar WHERE real (ver Utils\Relatorio::gerar())
     * @param bool  $paraPdf true = logo via caminho de arquivo (mPDF), false = logo via URL (exibição em navegador)
     */
    // ANTES (BKP 30/06/2026): public function htmlRelatorioGenerico(array $config, bool $preview = false): string
    // ANTES (BKP 30/06/2026): public function htmlRelatorioGenerico(array $config, bool $preview = false, array $valoresFiltro = []): string
    public function htmlRelatorioGenerico(array $config, bool $preview = false, array $valoresFiltro = [], bool $paraPdf = false): string
    {
        $titulo    = $config['titulo'] ?? '';
        $formato   = $config['formato'] ?? 'P';
        $fonte     = $config['tamanho_fonte'] ?? 10;
        $tabelaBase = $config['tabela_base'] ?? '';
        $colunas   = $config['colunas'] ?? [];
        $filtros   = $config['filtros'] ?? [];
        $totReg    = $config['totalizar_registros'] ?? 0;

        if (empty($tabelaBase) || empty($colunas)) {
            return '<div style="padding:10px; color:#999; font-style:italic;">Selecione a tabela base e adicione colunas para visualizar o preview.</div>';
        }

        $largura = $formato === 'L' ? '277mm' : '190mm';
        $alinMap = ['E' => 'left', 'C' => 'center', 'D' => 'right'];

        // ANTES (BKP 30/06/2026): resolução de schema + SQL + WHERE + enriquecimento Mongo
        // ficavam todos aqui dentro; extraídos pra _buscarDadosRelatorio() (ver acima) pra
        // serem reaproveitados por exportarExcelRelatorio()/exportarWordRelatorio().
        $resultado = $this->_buscarDadosRelatorio($config, $preview, $valoresFiltro);
        $dados     = $resultado['dados'];

        // ── Separar colunas normais e linha inteira ─────────────────────
        $colsNormais    = [];
        $colsLinhaInt   = [];
        foreach ($colunas as $idx => $col) {
            $col['_idx'] = $idx;
            if (($col['comportamento'] ?? 'cortar') === 'linha') {
                $colsLinhaInt[] = $col;
            } else {
                $colsNormais[] = $col;
            }
        }
        $numNormais = count($colsNormais);

        // ── Montar HTML ─────────────────────────────────────────────────
        $html = '<div style="font-family:Arial,sans-serif; font-size:' . $fonte . 'pt; max-width:' . $largura . '; margin:0 auto; padding:5px;">';

        // Logo + Título
        // ANTES (BKP 30/06/2026): $logoUrl = $preview ? base_url('assets/images/logo-back.png') : $this->logoBack();
        // $preview e o "gerar" real (Utils\Relatorio::gerar()) exibem HTML direto no navegador
        // (precisam de URL); só a geração de PDF via mPDF (PrintRelatorioGenerico) precisa do
        // caminho de arquivo. Por isso a logo agora depende de $paraPdf, não de $preview.
        $logoUrl = $paraPdf ? $this->logoBack() : base_url('assets/images/logo-back.png');
        $headerHtml = '<table style="width:100%; border-bottom:1px solid #999; margin-bottom:5px;">'
            . '<tr>'
            . '<td style="width:20mm;"><img src="' . $this->e($logoUrl) . '" style="height:15mm;"></td>'
            . '<td style="text-align:center; font-size:16pt; font-weight:bold; vertical-align:middle;">' . $this->e($titulo) . '</td>'
            . '</tr></table>';

        // Subtítulo (filtros) — só mostra os filtros efetivamente selecionados; os
        // demais (sem valor em $valoresFiltro) ficam de fora, em vez de "Campo: Todos".
        if (!empty($filtros)) {
            $subParts = [];
            foreach ($filtros as $f) {
                $vfMatch = null;
                foreach ($valoresFiltro as $vf) {
                    if ($vf['campo'] === $f['campo']) {
                        $vfMatch = $vf;
                        break;
                    }
                }
                // ANTES (BKP 30/06/2026): filtro sem valor selecionado entrava no subtítulo como "Todos"
                if (!$vfMatch) {
                    continue;
                }
                if ($f['tipo_filtro'] === 'DATE') {
                    // 'de'/'ate' vêm com hora (00:00:00/23:59:59, ver Utils\Relatorio::
                    // _prepararRelatorioFiltrado()) pro BETWEEN cobrir o dia inteiro em
                    // colunas DATETIME; no subtítulo mostramos só a data (sem a hora).
                    $txt = data_br(substr($vfMatch['de'], 0, 10)) . ' a ' . data_br(substr($vfMatch['ate'], 0, 10));
                } else {
                    // ANTES (BKP 30/06/2026): mostrava só a contagem ("N selecionado(s)"), não os valores
                    $txt = implode(', ', array_map([$this, 'e'], $vfMatch['valores']));
                }
                $subParts[] = $this->e($f['label']) . ': ' . $txt;
            }
            if (!empty($subParts)) {
                $headerHtml .= '<div style="text-align:center; font-size:14pt; color:#555; margin-bottom:5px;">'
                    . implode(' &nbsp;|&nbsp; ', $subParts) . '</div>';
            }
        }

        // No PDF, o cabeçalho vira um <htmlpageheader> nativo do mPDF — repete
        // IGUAL em toda página. Utils\Relatorio::exportarPdf() constrói a MymPdf2026
        // com $temHeader=false quando $paraPdf=true, pra não duplicar com o cabeçalho
        // automático da lib (era isso que causava logo duplicada na pág.1 e cabeçalho
        // genérico/diferente a partir da pág.2).
        if ($paraPdf) {
            $html .= '<style>@page { header: relatorio-cabecalho; margin-top: 32mm; }</style>'
                . '<htmlpageheader name="relatorio-cabecalho">' . $headerHtml . '</htmlpageheader>';
        } else {
            $html .= $headerHtml;
        }

        // Tabela
        $html .= '<table style="width:100%; border-collapse:collapse; font-size:' . $fonte . 'pt;">';

        // Cabeçalho (só colunas normais)
        $html .= '<thead><tr>';
        foreach ($colsNormais as $col) {
            $alin = $alinMap[$col['alinhamento']] ?? 'left';
            // Largura em ch (caracteres) — respeitando o valor definido pelo usuário
            $larg = (int) ($col['largura'] ?? 0);
            $w    = $larg > 0 ? "width:{$larg}ch; max-width:{$larg}ch;" : '';
            $html .= '<th style="border:1px solid #000; background:#dcdcdc; padding:2px 4px; text-align:' . $alin . '; font-weight:bold; ' . $w . '">'
                . $this->e($col['label']) . '</th>';
        }
        $html .= '</tr></thead>';

        // Dados
        $html .= '<tbody>';
        $totais  = array_fill(0, count($colunas), 0);
        $numRows = 0;

        if (empty($dados)) {
            $html .= '<tr><td colspan="' . $numNormais . '" style="border:1px solid #ccc; padding:8px; text-align:center; color:#999;">'
                . ($preview ? 'Nenhum registro encontrado na tabela base.' : 'Sem dados.') . '</td></tr>';
        } else {
            foreach ($dados as $row) {
                // Linha principal (colunas normais)
                $html .= '<tr>';
                foreach ($colsNormais as $col) {
                    $valor = $row[$col['label']] ?? '';
                    $tipo  = strtolower($col['tipo_dado'] ?? '');

                    if ($valor !== '' && in_array($tipo, ['date', 'datetime', 'timestamp'])) {
                        $valor = function_exists('data_br') ? data_br($valor) : $valor;
                    }

                    $alin    = $alinMap[$col['alinhamento']] ?? 'left';
                    $larg    = (int) ($col['largura'] ?? 0);
                    $w       = $larg > 0 ? "width:{$larg}ch; max-width:{$larg}ch;" : '';
                    $comport = $col['comportamento'] ?? 'cortar';

                    // Quebrar: limita a 3 linhas (3 x largura caracteres), restante corta
                    if ($comport === 'quebrar' && $larg > 0) {
                        $maxChars = $larg * 3;
                        if (mb_strlen((string) $valor) > $maxChars) {
                            $valor = mb_substr((string) $valor, 0, $maxChars) . '...';
                        }
                    }

                    $estilo = 'border:1px solid #ccc; padding:1px 4px; text-align:' . $alin . '; ' . $w;
                    if ($comport === 'cortar') {
                        $estilo .= ' overflow:hidden; white-space:nowrap; text-overflow:ellipsis;';
                    } elseif ($comport === 'quebrar') {
                        $estilo .= ' word-wrap:break-word; overflow:hidden;';
                    }

                    $html .= '<td style="' . $estilo . '">' . $this->e((string) $valor) . '</td>';

                    if ($col['totalizar'] && is_numeric($valor)) {
                        $totais[$col['_idx']] += (float) $valor;
                    }
                }
                $html .= '</tr>';

                // Linhas inteiras (abaixo da linha principal)
                foreach ($colsLinhaInt as $col) {
                    $valor = $row[$col['label']] ?? '';
                    $tipo  = strtolower($col['tipo_dado'] ?? '');

                    if ($valor !== '' && in_array($tipo, ['date', 'datetime', 'timestamp'])) {
                        $valor = function_exists('data_br') ? data_br($valor) : $valor;
                    }

                    if ($valor !== '') {
                        $html .= '<tr><td colspan="' . $numNormais . '" style="border:1px solid #ccc; padding:1px 4px; word-wrap:break-word;">'
                            . '<strong>' . $this->e($col['label']) . ':</strong> '
                            . $this->e((string) $valor) . '</td></tr>';
                    }

                    if ($col['totalizar'] && is_numeric($valor)) {
                        $totais[$col['_idx']] += (float) $valor;
                    }
                }

                $numRows++;
            }
        }
        $html .= '</tbody>';

        // Rodapé (totalizadores)
        $temTotalizador = false;
        foreach ($colunas as $col) {
            if ($col['totalizar']) {
                $temTotalizador = true;
                break;
            }
        }

        if ($temTotalizador || $totReg) {
            $html .= '<tfoot>';

            if ($temTotalizador) {
                $html .= '<tr style="font-weight:bold; background:#f0f0f0;">';
                foreach ($colunas as $idx => $col) {
                    $alin = $alinMap[$col['alinhamento']] ?? 'left';
                    $val  = $col['totalizar'] ? number_format($totais[$idx], 2, ',', '.') : '';
                    $html .= '<td style="border:1px solid #000; padding:2px 4px; text-align:' . $alin . ';">' . $val . '</td>';
                }
                $html .= '</tr>';
            }

            if ($totReg) {
                $html .= '<tr><td colspan="' . count($colunas) . '" style="border:1px solid #000; padding:2px 4px; text-align:right; font-weight:bold;">'
                    . 'Total de registros: ' . $numRows . '</td></tr>';
            }

            $html .= '</tfoot>';
        }

        $html .= '</table>';
        $html .= '</div>';

        return $html;
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
