<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\Config\CfgTela;
use App\Libraries\MyPdf2025;
use App\Models\CommonModel;
use App\Models\Config\ConfigEtiquetaCampoModel;
use App\Models\Config\ConfigEtiquetaModel;
use App\Models\Config\ConfigTelaModel;
use DateTime;

class CriaEtiqueta extends BaseController
{
    public $data;
    public $etiqueta;
    public $etiquetaCampo;
    public $common;
    public $tela;
    public $pdf;
    // Configuração das etiquetas
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


    public function emiteEtiqueta($etq_id, $dados = false)
    {
        $etiq = $this->etiqueta->getEtiqueta($etq_id);
        $camp = $this->etiquetaCampo->getEtiquetaCampo($etq_id);
        // debug($camp, true);
        if ($etiq) {
            $etq = $etiq[0];
            $this->largura      = $etq['let_largura']; // Largura da etiqueta (mm)
            $this->altura       = $etq['let_altura'];  // Altura da etiqueta (mm)
            $this->esquerda     = $etq['let_marg_esquerda'];  // Margem esquerda da página
            $this->direita      = $etq['let_marg_direita'];  // Margem esquerda da página
            $this->topo         = $etq['let_marg_superior'];      // Margem superior da página
            $this->rodape       = $etq['let_marg_inferior'];      // Margem superior da página
            $this->horizontal   = $etq['let_distancia_h']; // Espaço entre etiquetas (mm)
            $this->vertical     = $etq['let_distancia_v'];   // Espaço entre linhas (mm)
            $this->colunas      = $etq['let_colunas'];          // Número de etiquetas por linha
            $this->linhas       = $etq['let_linhas'];           // Número de etiquetas por coluna

            $tamanho[0] = ($this->largura * $this->colunas) + ($this->horizontal * ($this->colunas - 1)) + $this->esquerda + $this->direita;
            $tamanho[1] = $this->topo + ($this->altura * $this->linhas) + ($this->vertical * ($this->linhas)) + $this->rodape + $this->altura;
            // debug($this->largura, false);
            $this->pdf = new MyPdf2025(false, false, $tamanho);
            // debug($this->pdf->size[1], false);


            $modelo = false;
            if (!$dados) {
                $modelo = true;
                $fields = [];
                for ($c = 0; $c < count($camp); $c++) {
                    $fields[$c] = $camp[$c]['etc_campo'];
                }
                $telid = $etq['tel_id'];
                $telas = $this->tela->getTelaId($telid)[0];
                if (isset($telas['tel_model']) && $telas['tel_model'] != null) {
                    $model = $telas['tel_model'];
                    $compl_model = substr($model, 0, 6);
                    $pasta = "App\\Models\\" . $compl_model . "\\";
                    $model_atual = model($pasta . $model);
                    $banco   = $model_atual->DBGroup;
                    $view   = $model_atual->view;
                    $dados = $this->common->getListaTabela($banco, $view, $fields);
                }
            }
            $colunaAtual    = 0;
            $linhaAtual     = 0;

            $this->pdf->Add_Page('P', $tamanho, 0);
            $this->pdf->SetMargins($this->esquerda, $this->topo, $this->direita);
            for ($rg = 0; $rg < count($dados); $rg++) {
                $registro = $dados[$rg];
                // debug($registro);
                // Cálculo da posição X e Y
                $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
                $y = $this->topo + ($linhaAtual * ($this->altura + $this->vertical));
                // $this->pdf->SetXY($x, $y); // Pequeno ajuste para centralizar
                // Desenha a borda da etiqueta
                if ($modelo) {
                    $this->pdf->Rect($x, $y, $this->largura, $this->altura);
                }
                $ocupoularg = 0;
                for ($cp = 0; $cp < count($camp); $cp++) {
                    $propCamp = $camp[$cp];
                    // debug($propCamp);
                    $this->pdf->SetY($y); // Pequeno ajuste para centralizar
                    if ($propCamp['etc_campo'] == 0) { // TEXTO LIVRE
                        // debug($y);
                        $ocupado = $this->largura * ($ocupoularg / 100);
                        $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal)) + $ocupado;
                        $this->pdf->SetX($x); // Pequeno ajuste para centralizar
                        // debug($propCamp);
                        // Insere texto na etiqueta
                        if ($propCamp['etc_negrito'] == "S") {
                            $estilo = "B";
                        }
                        if ($propCamp['etc_italico'] == "S") {
                            $estilo .= "I";
                        }
                        if ($propCamp['etc_sublinhado'] == "S") {
                            $estilo .= "S";
                        }
                        $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
                        $conteudo = trim($propCamp['etc_rotulo']);
                        $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
                        $altucont = ($propCamp['etc_tamanho'] / 3);
                        $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
                        $ocupoularg += $propCamp['etc_colunas'];
                        if ($ocupoularg >= 90) {
                            $this->pdf->Cell(10, $altucont, '', 0, 1, 'E');
                            $ocupoularg = 0;
                            $y = $this->pdf->getY();
                        } else {
                            // $x = $x + $tamconte + 4;
                            $this->pdf->SetX($x); // Pequeno ajuste para centralizar
                        }
                    } else if ($propCamp['etc_campo'] == 1) { // Linha Horizontal
                        $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
                        $this->pdf->Line($x, $y, $x + $this->largura, $y);
                        $y = $this->pdf->getY();
                    } else if ($propCamp['etc_codbar'] === 'S') {
                        $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
                        // $y = $this->pdf->getY() + 2;
                        $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $propCamp['etc_caracteres']));
                        $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
                        $altconte = $propCamp['etc_tamanho'];
                        $left = $x + (((100 - $propCamp['etc_colunas']) / 2) * ($this->largura / 100));
                        $this->pdf->Code128($left, $y, $conteudo, $tamconte, $altconte);
                        $y = $this->pdf->getY() + $altconte;
                    } else {
                        $ocupado = $this->largura * ($ocupoularg / 100);
                        $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal)) + $ocupado;
                        $this->pdf->SetX($x); // Pequeno ajuste para centralizar
                        // debug($propCamp);
                        // Insere texto na etiqueta
                        $estilo = "";
                        $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
                        // $this->pdf->Cell(10, 3, 'Y ' . $y, 0, 'L');
                        if ($propCamp['etc_rotulo'] != 'Sem Rótulo') {
                            $conteudo = trim($propCamp['etc_rotulo']);
                            $tamconte = ($this->largura * ($propCamp['etc_colunas'] / 100) * 30 / 100);
                            $altucont = ($propCamp['etc_tamanho'] / 3);
                            $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
                        }
                        if ($propCamp['etc_negrito'] == "S") {
                            $estilo = "B";
                        }
                        if ($propCamp['etc_italico'] == "S") {
                            $estilo .= "I";
                        }
                        if ($propCamp['etc_sublinhado'] == "S") {
                            $estilo .= "S";
                        }
                        $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
                        $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $propCamp['etc_caracteres']));
                        $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
                        if ($propCamp['etc_rotulo'] != 'Sem Rótulo') {
                            $tamconte = $tamconte * (70 / 100);
                        }
                        $altucont = ($propCamp['etc_tamanho'] / 3);
                        if ($propCamp['etc_linhas'] > 1) {
                            $this->pdf->MultiCell($tamconte, $altucont, utf8_decode($conteudo), 0, $propCamp['etc_alinhamento']);
                        } else {
                            $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
                        }
                        $ocupoularg += $propCamp['etc_colunas'];
                        if ($ocupoularg >= 90) {
                            $this->pdf->Cell(10, $altucont, '', 0, 1, 'E');
                            $ocupoularg = 0;
                            $y = $this->pdf->getY();
                        } else {
                            // $x = $x + $tamconte + 4;
                            $this->pdf->SetX($x); // Pequeno ajuste para centralizar
                        }
                    }
                }
                // Atualiza a posição
                $colunaAtual++;

                // Verifica se deve pular para a próxima linha
                if ($colunaAtual == $this->colunas) {
                    $colunaAtual = 0;
                    $linhaAtual++;
                }
                // Se ultrapassar o limite da página, cria uma nova
                if ($linhaAtual >= $this->linhas) {
                    $this->pdf->Add_Page('P', $tamanho, 0);
                    $colunaAtual = 0;
                    $linhaAtual = 0;
                }
            }

            $this->pdf->AliasNbPages();

            // $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            // $output = base64_encode($output);
            // echo json_encode(['pdf' => $output]); // Retorne um JSON
            // Define o cabeçalho para exibir no navegador
            $this->response->setHeader('Content-Type', 'application/pdf');

            // Exibe o PDF no navegador
            $this->pdf->Output('documento.pdf', 'I');
        }
    }
}
