<?php

namespace App\Controllers;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigEtiquetaModel;
use App\Models\Config\ConfigEtiquetaCampoModel;

class CriaEtiquetaZPL extends BaseController
{
    public $data;
    public $etiqueta;
    public $etiquetaCampo;
    public $common;
    public $tela;
    public $pdf;

    private $largura    = 70; // Largura da etiqueta (mm)
    private $altura     = 35;  // Altura da etiqueta (mm)
    private $esquerda   = 10;  // Margem esquerda da página
    private $direita    = 10;  // Margem esquerda da página
    private $topo       = 10;      // Margem superior da página
    private $rodape     = 10;      // Margem superior da página
    private $horizontal = 5; // Espaço entre etiquetas (mm)
    private $vertical   = 5;   // Espaço entre linhas (mm)
    private $colunas    = 3;          // Número de etiquetas por linha
    private $linhas     = 8;           // Número de etiquetas por coluna

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data             = session()->getFlashdata('dados_classe');
        $this->etiqueta         = new ConfigEtiquetaModel();
        $this->etiquetaCampo    = new ConfigEtiquetaCampoModel();
        $this->common           = new CommonModel();
        $this->tela             = new ConfigTelaModel();
    }

    private function renderEtiquetasZPL(array $dados, array $camp): string
    {
        $dpi = 300;
        $dotsPorMm = $dpi / 25.4;

        $larguraDot    = $this->largura * $dotsPorMm;
        $alturaDot     = $this->altura * $dotsPorMm;
        $esquerdaDot   = $this->esquerda * $dotsPorMm;
        $topoDot       = $this->topo * $dotsPorMm;
        $horizontalDot = $this->horizontal * $dotsPorMm;
        $verticalDot   = $this->vertical * $dotsPorMm;

        $zpl = "^XA\n";

        $colunaAtual = 0;
        $linhaAtual = 0;

        foreach ($dados as $registro) {
            $posX = $esquerdaDot + ($colunaAtual * ($larguraDot + $horizontalDot));
            $posY = $topoDot + ($linhaAtual * ($alturaDot + $verticalDot));

            $zpl .= $this->renderConteudoZPLInterno($camp, $registro, $posX, $posY, $larguraDot);

            $colunaAtual++;
            if ($colunaAtual >= $this->colunas) {
                $colunaAtual = 0;
                $linhaAtual++;
            }
        }

        $zpl .= "^XZ";
        return $zpl;
    }

    private function renderConteudoZPLInterno(array $camp, array $registro, float $xBase, float $yBase, float $larguraDot): string
    {
        $zpl = "";
        $y = $yBase;
        $ocupouLargura = 0;

        foreach ($camp as $propCamp) {
            $caracteres = intval($propCamp['etc_caracteres'] ?? 30);
            $colunas = floatval($propCamp['etc_colunas'] ?? 100);
            $espacoLargura = ($larguraDot * $colunas) / 100;
            $ocupado = ($larguraDot * $ocupouLargura) / 100;

            $x = $xBase + $ocupado;

            if ($propCamp['etc_campo'] == '1') {
                $zpl .= "^FO{$x},{$y}^GB{$espacoLargura},1,1^FS\n";
                $y += 4;
                $ocupouLargura = 0;
                continue;
            }

            if ($propCamp['etc_codbar'] === 'S') {
                $conteudo = substr(trim($registro[$propCamp['etc_campo']] ?? ''), 0, $caracteres);
                if ($conteudo === "") {
                    $conteudo = str_repeat('0', $caracteres);
                }

                $alturaCodigo = intval($propCamp['etc_tamanho']) * 3.5;
                $zpl .= "^FO{$x},{$y}^BY2^BCN,{$alturaCodigo},N,N,N^FD{$conteudo}^FS\n";
                $y += $alturaCodigo + 10;
                $ocupouLargura = 0;
                continue;
            }

            $estilo = "";
            if ($propCamp['etc_negrito'] === 'S') $estilo .= 'B';
            if ($propCamp['etc_italico'] === 'S') $estilo .= 'I';
            if ($propCamp['etc_sublinhado'] === 'S') $estilo .= 'U';

            $fonte = strtoupper(substr($propCamp['etc_fonte'], 0, 1)) ?: 'A';
            $tamanho = intval($propCamp['etc_tamanho']) ?: 10;
            $alturaFonte = $tamanho * 3;
            $larguraFonte = $tamanho * 2;

            if ($propCamp['etc_campo'] == '0') {
                $conteudo = trim($propCamp['etc_rotulo']);
            } elseif ($propCamp['etc_campo'] == '2') {
                $conteudo = date('d/m/Y');
            } elseif ($propCamp['etc_campo'] == '3') {
                $conteudo = date('d/m/Y H:i');
            } else {
                $rotulo = ($propCamp['etc_rotulo'] != 'Sem Rótulo' && $propCamp['etc_rotulo'] != '.') ? trim($propCamp['etc_rotulo']) . ': ' : '';
                $conteudo = $rotulo . substr(trim($registro[$propCamp['etc_campo']] ?? ''), 0, $caracteres);
            }

            $alinhamento = strtoupper($propCamp['etc_alinhamento'] ?? 'L');
            if ($alinhamento === 'C') {
                $x += ($espacoLargura - strlen($conteudo) * $larguraFonte) / 2;
            } elseif ($alinhamento === 'R') {
                $x += ($espacoLargura - strlen($conteudo) * $larguraFonte);
            }

            $zpl .= "^FO{$x},{$y}^A{$fonte},{$alturaFonte},{$larguraFonte}^FD{$conteudo}^FS\n";
            $y += $alturaFonte + 2;

            $ocupouLargura += $colunas;
            if ($ocupouLargura >= 90) {
                $y += 10;
                $ocupouLargura = 0;
            }
        }

        return $zpl;
    }

    /**
     * Visualiza ZPL renderizado via API Labelary
     */
    public function previewZPL()
    {
        // $dados = [[ "nome" => "Produto 01", "codigo" => "1234567890" ]]; // Exemplo
        // $campos = [
        //     [ "etc_campo" => "nome", "etc_tamanho" => 8, "etc_colunas" => 100, "etc_caracteres" => 20, "etc_codbar" => "N", "etc_rotulo" => "Produto", "etc_negrito" => "S", "etc_alinhamento" => "L", "etc_fonte" => "A" ],
        //     [ "etc_campo" => "codigo", "etc_tamanho" => 10, "etc_colunas" => 100, "etc_caracteres" => 12, "etc_codbar" => "S", "etc_rotulo" => "", "etc_negrito" => "N", "etc_alinhamento" => "L", "etc_fonte" => "A" ]
        // ];

        $json = $this->request->getJSON(true) ?? json_decode($this->request->getBody(), true);

        if (!$json || !isset($json['let_id'], $json['tel_id'], $json['campos'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Dados inválidos.']);
        }

        $let_id = $json['let_id'];
        $tel_id = $json['tel_id'];
        $camp = $json['campos'];

        $etiq = $this->etiqueta->getEtiquetaLayout($let_id);
        if (!$etiq) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Layout não encontrado.']);
        }

        $etq = $etiq[0];
        $this->largura    = $etq['let_largura'];
        $this->altura     = $etq['let_altura'];
        $this->esquerda   = $etq['let_marg_esquerda'];
        $this->direita    = $etq['let_marg_direita'];
        $this->topo       = $etq['let_marg_superior'];
        $this->rodape     = $etq['let_marg_inferior'];
        $this->horizontal = $etq['let_distancia_h'];
        $this->vertical   = $etq['let_distancia_v'];
        $this->colunas    = $etq['let_colunas'];
        $this->linhas     = $etq['let_linhas'];

        $dados = [];
        $telas = $this->tela->getTelaId($tel_id)[0];

        if (!empty($telas['tel_model'])) {
            $model = $telas['tel_model'];
            $model_atual = model("App\\Models\\" . substr($model, 0, 6) . "\\" . $model);
            $fields = array_filter(array_column($camp, 'etc_campo'), fn($f) => $f !== '0' && $f !== '1');
            $dados = $this->common->getListaTabela($model_atual->DBGroup, $model_atual->view, $fields, false, 10);
        }

        $tamanho[0]   = ($this->colunas * $this->largura) + (($this->colunas - 1) * $this->horizontal) + ($this->esquerda + $this->direita);

        // $tamanho[0] = ($this->largura * $this->colunas) + ($this->horizontal * ($this->colunas - 1)) + $this->esquerda + $this->direita;
        $tamanho[1] = $this->topo + ($this->altura * $this->linhas) + ($this->vertical * $this->linhas) + $this->rodape + $this->altura;

        // debug($dados);
        $zpl = $this->renderEtiquetasZPL($dados, $camp);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.labelary.com/v1/printers/12dpmm/labels/4x6/0/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $zpl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/pdf']);

        $response = curl_exec($ch);
        curl_close($ch);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($response);
    }

    /**
     * Download do arquivo .prn para envio direto à impressora
     */
    public function downloadPRN()
    {
        $dados = [[ "nome" => "Produto 01", "codigo" => "1234567890" ]]; // Exemplo
        $campos = [
            [ "etc_campo" => "nome", "etc_tamanho" => 8, "etc_colunas" => 100, "etc_caracteres" => 20, "etc_codbar" => "N", "etc_rotulo" => "Produto", "etc_negrito" => "S", "etc_alinhamento" => "L", "etc_fonte" => "A" ],
            [ "etc_campo" => "codigo", "etc_tamanho" => 10, "etc_colunas" => 100, "etc_caracteres" => 12, "etc_codbar" => "S", "etc_rotulo" => "", "etc_negrito" => "N", "etc_alinhamento" => "L", "etc_fonte" => "A" ]
        ];

        $zpl = $this->geraZPL($dados, $campos);
        return $this->response
            ->setHeader('Content-Type', 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="etiqueta.prn"')
            ->setBody($zpl);
    }
}
