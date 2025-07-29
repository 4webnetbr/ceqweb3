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
    public $alturaFinal;
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


    // public function emiteEtiqueta($etq_id, $dados = false)
    // {
    //     $etiq = $this->etiqueta->getEtiqueta($etq_id);
    //     $camp = $this->etiquetaCampo->getEtiquetaCampo($etq_id);
    //     // debug($camp, true);
    //     if ($etiq) {
    //         $etq = $etiq[0];
    //         $this->largura      = $etq['let_largura']; // Largura da etiqueta (mm)
    //         $this->altura       = $etq['let_altura'];  // Altura da etiqueta (mm)
    //         $this->esquerda     = $etq['let_marg_esquerda'];  // Margem esquerda da página
    //         $this->direita      = $etq['let_marg_direita'];  // Margem esquerda da página
    //         $this->topo         = $etq['let_marg_superior'];      // Margem superior da página
    //         $this->rodape       = $etq['let_marg_inferior'];      // Margem superior da página
    //         $this->horizontal   = $etq['let_distancia_h']; // Espaço entre etiquetas (mm)
    //         $this->vertical     = $etq['let_distancia_v'];   // Espaço entre linhas (mm)
    //         $this->colunas      = $etq['let_colunas'];          // Número de etiquetas por linha
    //         $this->linhas       = $etq['let_linhas'];           // Número de etiquetas por coluna

    //         $tamanho[0] = ($this->largura * $this->colunas) + ($this->horizontal * ($this->colunas - 1)) + $this->esquerda + $this->direita;
    //         $tamanho[1] = $this->topo + ($this->altura * $this->linhas) + ($this->vertical * ($this->linhas)) + $this->rodape + $this->altura;
    //         // debug($this->largura, false);
    //         $this->pdf = new MyPdf2025(false, false, $tamanho);
    //         // debug($this->pdf->size[1], false);


    //         $modelo = false;
    //         if (!$dados) {
    //             $modelo = true;
    //             $fields = [];
    //             for ($c = 0; $c < count($camp); $c++) {
    //                 $fields[$c] = $camp[$c]['etc_campo'];
    //             }
    //             $telid = $etq['tel_id'];
    //             $telas = $this->tela->getTelaId($telid)[0];
    //             if (isset($telas['tel_model']) && $telas['tel_model'] != null) {
    //                 $model = $telas['tel_model'];
    //                 $compl_model = substr($model, 0, 6);
    //                 $pasta = "App\\Models\\" . $compl_model . "\\";
    //                 $model_atual = model($pasta . $model);
    //                 $banco   = $model_atual->DBGroup;
    //                 $view   = $model_atual->view;
    //                 $dados = $this->common->getListaTabela($banco, $view, $fields);
    //             }
    //         }
    //         $colunaAtual    = 0;
    //         $linhaAtual     = 0;

    //         $this->pdf->Add_Page('P', $tamanho, 0);
    //         $this->pdf->SetMargins($this->esquerda, $this->topo, $this->direita);
    //         for ($rg = 0; $rg < count($dados); $rg++) {
    //             $registro = $dados[$rg];
    //             // debug($registro);
    //             // Cálculo da posição X e Y
    //             $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //             $y = $this->topo + ($linhaAtual * ($this->altura + $this->vertical));
    //             // $this->pdf->SetXY($x, $y); // Pequeno ajuste para centralizar
    //             // Desenha a borda da etiqueta
    //             if ($modelo) {
    //                 $this->pdf->Rect($x, $y, $this->largura, $this->altura);
    //             }
    //             $ocupoularg = 0;
    //             for ($cp = 0; $cp < count($camp); $cp++) {
    //                 $propCamp = $camp[$cp];
    //                 // debug($propCamp);
    //                 $this->pdf->SetY($y); // Pequeno ajuste para centralizar
    //                 if ($propCamp['etc_campo'] == 0) { // TEXTO LIVRE
    //                     // debug($y);
    //                     $ocupado = $this->largura * ($ocupoularg / 100);
    //                     $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal)) + $ocupado;
    //                     $this->pdf->SetX($x); // Pequeno ajuste para centralizar
    //                     // debug($propCamp);
    //                     // Insere texto na etiqueta
    //                     $estilo = '';
    //                     if ($propCamp['etc_negrito'] == "S") {
    //                         $estilo = "B";
    //                     }
    //                     if ($propCamp['etc_italico'] == "S") {
    //                         $estilo .= "I";
    //                     }
    //                     if ($propCamp['etc_sublinhado'] == "S") {
    //                         $estilo .= "S";
    //                     }
    //                     $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
    //                     $conteudo = trim($propCamp['etc_rotulo']);
    //                     $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                     $altucont = ($propCamp['etc_tamanho'] / 3);
    //                     $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
    //                     $ocupoularg += $propCamp['etc_colunas'];
    //                     if ($ocupoularg >= 90) {
    //                         $this->pdf->Cell(10, $altucont, '', 0, 1, 'E');
    //                         $ocupoularg = 0;
    //                         $y = $this->pdf->getY();
    //                     } else {
    //                         // $x = $x + $tamconte + 4;
    //                         $this->pdf->SetX($x); // Pequeno ajuste para centralizar
    //                     }
    //                 } else if ($propCamp['etc_campo'] == 1) { // Linha Horizontal
    //                     $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                     $this->pdf->Line($x, $y, $x + $this->largura, $y);
    //                     $y = $this->pdf->getY();
    //                 } else if ($propCamp['etc_codbar'] === 'S') {
    //                     $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                     // $y = $this->pdf->getY() + 2;
    //                     $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $propCamp['etc_caracteres']));
    //                     $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                     $altconte = $propCamp['etc_tamanho'];
    //                     $left = $x + (((100 - $propCamp['etc_colunas']) / 2) * ($this->largura / 100));
    //                     $this->pdf->Code128($left, $y, $conteudo, $tamconte, $altconte);
    //                     $y = $this->pdf->getY() + $altconte;
    //                 } else {
    //                     $ocupado = $this->largura * ($ocupoularg / 100);
    //                     $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal)) + $ocupado;
    //                     $this->pdf->SetX($x); // Pequeno ajuste para centralizar
    //                     // debug($propCamp);
    //                     // Insere texto na etiqueta
    //                     $estilo = "";
    //                     $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
    //                     // $this->pdf->Cell(10, 3, 'Y ' . $y, 0, 'L');
    //                     if ($propCamp['etc_rotulo'] != 'Sem Rótulo') {
    //                         $conteudo = trim($propCamp['etc_rotulo']);
    //                         $tamconte = ($this->largura * ($propCamp['etc_colunas'] / 100) * 30 / 100);
    //                         $altucont = ($propCamp['etc_tamanho'] / 3);
    //                         $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
    //                     }
    //                     if ($propCamp['etc_negrito'] == "S") {
    //                         $estilo = "B";
    //                     }
    //                     if ($propCamp['etc_italico'] == "S") {
    //                         $estilo .= "I";
    //                     }
    //                     if ($propCamp['etc_sublinhado'] == "S") {
    //                         $estilo .= "S";
    //                     }
    //                     $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);
    //                     $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $propCamp['etc_caracteres']));
    //                     $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                     if ($propCamp['etc_rotulo'] != 'Sem Rótulo') {
    //                         $tamconte = $tamconte * (70 / 100);
    //                     }
    //                     $altucont = ($propCamp['etc_tamanho'] / 3);
    //                     if ($propCamp['etc_linhas'] > 1) {
    //                         $this->pdf->MultiCell($tamconte, $altucont, utf8_decode($conteudo), 0, $propCamp['etc_alinhamento']);
    //                     } else {
    //                         $this->pdf->Cell($tamconte, $altucont, utf8_decode($conteudo), 0, 0, $propCamp['etc_alinhamento']);
    //                     }
    //                     $ocupoularg += $propCamp['etc_colunas'];
    //                     if ($ocupoularg >= 90) {
    //                         $this->pdf->Cell(10, $altucont, '', 0, 1, 'E');
    //                         $ocupoularg = 0;
    //                         $y = $this->pdf->getY();
    //                     } else {
    //                         // $x = $x + $tamconte + 4;
    //                         $this->pdf->SetX($x); // Pequeno ajuste para centralizar
    //                     }
    //                 }
    //             }
    //             // Atualiza a posição
    //             $colunaAtual++;

    //             // Verifica se deve pular para a próxima linha
    //             if ($colunaAtual == $this->colunas) {
    //                 $colunaAtual = 0;
    //                 $linhaAtual++;
    //             }
    //             // Se ultrapassar o limite da página, cria uma nova
    //             if ($linhaAtual >= $this->linhas) {
    //                 $this->pdf->Add_Page('P', $tamanho, 0);
    //                 $colunaAtual = 0;
    //                 $linhaAtual = 0;
    //             }
    //         }

    //         $this->pdf->AliasNbPages();

    //         // $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
    //         // $output = base64_encode($output);
    //         // echo json_encode(['pdf' => $output]); // Retorne um JSON
    //         // Define o cabeçalho para exibir no navegador
    //         $this->response->setHeader('Content-Type', 'application/pdf');

    //         // Exibe o PDF no navegador
    //         $this->pdf->Output('documento.pdf', 'I');
    //     }
    // }


    // public function previewEtiquetaViaAjax()
    // {
    //     log_message('info', 'JSON recebido no preview: ' . json_encode($this->request->getJSON(true)));

    //     $json = $this->request->getJSON(true);
    //     if (!$json) {
    //         $json = json_decode($this->request->getBody(), true);
    //     }
    //     log_message('info', 'Tipo de conteúdo: ' . $this->request->getHeaderLine('Content-Type'));
    //     log_message('info', 'Corpo cru: ' . $this->request->getBody());
    //     log_message('info', 'JSON interpretado: ' . json_encode($json));

    //     if (!$json || !isset($json['let_id']) || !isset($json['tel_id']) || !isset($json['campos'])) {
    //         return $this->response->setStatusCode(400)->setJSON(['error' => 'Dados inválidos aqui.']);
    //     }

    //     $let_id = $json['let_id'];
    //     $tel_id = $json['tel_id'];
    //     $camp = $json['campos'];

    //     // 1. Buscar layout da etiqueta
    //     $etiq = $this->etiqueta->getEtiquetaLayout($let_id);
    //     if (!$etiq) {
    //         return $this->response->setStatusCode(404)->setJSON(['error' => 'Layout da etiqueta não encontrado.']);
    //     }

    //     $etq = $etiq[0];

    //     $this->largura    = $etq['let_largura'];
    //     $this->altura     = $etq['let_altura'];
    //     $this->esquerda   = $etq['let_marg_esquerda'];
    //     $this->direita    = $etq['let_marg_direita'];
    //     $this->topo       = $etq['let_marg_superior'];
    //     $this->rodape     = $etq['let_marg_inferior'];
    //     $this->horizontal = $etq['let_distancia_h'];
    //     $this->vertical   = $etq['let_distancia_v'];
    //     $this->colunas    = $etq['let_colunas'];
    //     $this->linhas     = $etq['let_linhas'];

    //     $tamanho[0] = ($this->largura * $this->colunas) + ($this->horizontal * ($this->colunas - 1)) + $this->esquerda + $this->direita;
    //     $tamanho[1] = $this->topo + ($this->altura * $this->linhas) + ($this->vertical * $this->linhas) + $this->rodape + $this->altura;

    //     // 2. Buscar dados do modelo da tela
    //     $telas = $this->tela->getTelaId($tel_id)[0];
    //     $dados = [];

    //     if (isset($telas['tel_model']) && $telas['tel_model'] != null) {
    //         $model = $telas['tel_model'];
    //         $compl_model = substr($model, 0, 6);
    //         $pasta = "App\\Models\\" . $compl_model . "\\";
    //         $model_atual = model($pasta . $model);

    //         $banco = $model_atual->DBGroup;
    //         $view  = $model_atual->view;

    //         $fields = [];
    //         foreach ($camp as $c) {
    //             if ($c['etc_campo'] != '0' && $c['etc_campo'] != '1') {
    //                 $fields[] = $c['etc_campo'];
    //             }
    //         }

    //         $dados = $this->common->getListaTabela($banco, $view, $fields);
    //     }

    //     // 3. Geração do PDF
    //     $this->pdf = new MyPdf2025(false, false, $tamanho);
    //     $this->pdf->Add_Page('P', $tamanho, 0);
    //     // $this->pdf->SetMargins($this->esquerda, $this->topo, $this->direita);
    //     $this->pdf->SetMargins(0, 0, 0);

    //     $colunaAtual = 0;
    //     $linhaAtual = 0;
    //     foreach ($dados as $registro) {
    //         $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //         $y = $this->topo + ($linhaAtual * ($this->altura + $this->vertical));
    //         $this->pdf->Rect($x, $y, $this->largura, $this->altura);

    //         $ocupoularg = 0;

    //         foreach ($camp as $propCamp) {
    //             $this->pdf->SetY($y);
    //             $caracteres = intval($propCamp['etc_caracteres'])??30;

    //             if ($propCamp['etc_campo'] == '1') { // Linha Horizontal
    //                 $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                 $larg = $this->largura * ($propCamp['etc_colunas'] /100);
    //                 $this->pdf->Line($x, $y, $x + $larg, $y);
    //                 $alturalinha = (1 / 8) * 3; // 8 é o tamanho padrão
    //                 $propCamp['etc_colunas'] = 100;
    //                 // $y = $this->pdf->getY();
    //             } elseif ($propCamp['etc_codbar'] === 'S') {
    //                 $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                 if($caracteres == 0){
    //                     $caracteres = 10;
    //                 }
    //                 $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $caracteres));
    //                 $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                 $altconte = intval($propCamp['etc_tamanho'])+2;
    //                 $left = $x + (((100 - $propCamp['etc_colunas']) / 2) * ($this->largura / 100));
    //                 if(strlen($conteudo) == 0 ){
    //                     $conteudo = str_repeat('0', $caracteres);
    //                 }
    //                 $this->pdf->Code128($left, $y, $conteudo, $tamconte, $altconte);
    //                 $y = $this->pdf->getY() + $altconte;
    //                 $alturalinha = $altconte; // 8 é o tamanho padrão
    //                 $propCamp['etc_colunas'] = 100;
    //             } else {
    //                 $alturalinha = ($propCamp['etc_tamanho'] / 8) * 3; // 8 é o tamanho padrão
    //                 $ocupado = $this->largura * ($ocupoularg / 100);
    //                 $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                 $total = $ocupado + $espacodisponivel;
    //                 if ($total > $this->largura) {
    //                     // Ajusta $espacodisponivel para caber no restante da largura
    //                     $espacodisponivel = $this->largura - $ocupado;

    //                     // se espaço disponível < 5%, vai para linha abaixo
    //                     if ($espacodisponivel < 5) {
    //                         $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
    //                         $ocupado = 0;
    //                         $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                         $y = $this->pdf->getY();
    //                     }
    //                 }
    //                 $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal)) + $ocupado - 1;
    //                 $this->pdf->SetX($x);
    //                 $estilo = '';
    //                 $rotulo = '';
    //                 if ($propCamp['etc_rotulo'] != 'Sem Rótulo' && $propCamp['etc_rotulo'] != '.') { // TEM RÓTULO
    //                     $rotulo = trim($propCamp['etc_rotulo']);
    //                 }

    //                 if ($propCamp['etc_negrito'] === "S") $estilo .= "B";
    //                 if ($propCamp['etc_italico'] === "S") $estilo .= "I";
    //                 if ($propCamp['etc_sublinhado'] === "S") $estilo .= "U";

    //                 $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);

    //                 if ($propCamp['etc_campo'] == '0') { // Texto Livre
    //                     $rotulo = '';
    //                     $conteudo = trim($propCamp['etc_rotulo']);
    //                     $propCamp['etc_linhas'] = 1;
    //                 } else if ($propCamp['etc_campo'] == '2') { // Data Atual
    //                     $data = data_br(date('Y-m-d'));
    //                     $conteudo = $data;
    //                     $propCamp['etc_linhas'] = 1;
    //                 } else if ($propCamp['etc_campo'] == '3') { // Data e Hora Atual
    //                     $datahora = data_br(date('Y-m-d H:i:s'));
    //                     $conteudo = $datahora;
    //                     $propCamp['etc_linhas'] = 1;
    //                 } else {
    //                     $conteudo = trim(substr($registro[$propCamp['etc_campo']], 0, $caracteres));
    //                 }
    //                 $conteudo = $rotulo .' '. $conteudo;
    //                 // $tamconte = ($this->largura * ($propCamp['etc_colunas'] / 100));
    //                 $linhas = intval($propCamp['etc_linhas']); 
    //                 if ($linhas > 1) {
    //                     $conteudo = trim($registro[$propCamp['etc_campo']]);
    //                     $this->pdf->MultiCellLimited($espacodisponivel, $alturalinha, utf8_decode($conteudo), 0,$propCamp['etc_alinhamento'],false,$linhas, $caracteres);
    //                 } else {
    //                     $maxWidth = $espacodisponivel;
    //                     $conteudoOriginal = utf8_decode($conteudo);
    //                     $conteudoFinal = $conteudoOriginal;

    //                     while ($this->pdf->GetStringWidth($conteudoFinal) > $maxWidth) {
    //                         $conteudoFinal = substr($conteudoFinal, 0, -1); // Remove último caractere
    //                         if (strlen($conteudoFinal) <= 3) break; // Evita cortar demais
    //                     }

    //                     if ($conteudoFinal !== $conteudoOriginal) {
    //                         $conteudoFinal = rtrim($conteudoFinal);
    //                     }
    //                     if ($propCamp['etc_alinhamento'] === 'R') {
    //                         $x = (($this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal))) + $this->largura) - $espacodisponivel + 1;
    //                         $this->pdf->SetX($x);
    //                         $propCamp['etc_colunas'] = 100;
    //                     }

    //                     $this->pdf->Cell($espacodisponivel, $alturalinha, $conteudoFinal, 0, 0, $propCamp['etc_alinhamento']);
    //                     // $this->pdf->Cell($espacodisponivel, $alturalinha, "'".$propCamp['etc_alinhamento']."'", 0, 0, "'".$propCamp['etc_alinhamento']."'");
    //                 }
    //             }
    //             $x = $this->pdf->GetX();
    //             $ocupoularg += $propCamp['etc_colunas'];
    //             if ($ocupoularg >= 90) {
    //                 $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
    //                 $ocupoularg = 0;
    //                 $y = $this->pdf->getY();
    //             }
    //         }

    //         $colunaAtual++;
    //         if ($colunaAtual == $this->colunas) {
    //             $colunaAtual = 0;
    //             $linhaAtual++;
    //         }

    //         if ($linhaAtual >= $this->linhas) {
    //             $this->pdf->Add_Page('P', $tamanho, 0);
    //             $colunaAtual = 0;
    //             $linhaAtual = 0;
    //         }
    //     }

    //     $this->pdf->AliasNbPages();


    //     $output = $this->pdf->Output('etiqueta.pdf', 'S');

    //     return $this->response
    //         ->setHeader('Content-Type', 'application/pdf')
    //         ->setBody($output);
    // }

    // private function renderEtiquetas(array $dados, array $camp, array $tamanho, bool $exibirBorda = true)
    // {
    //     $this->pdf = new MyPdf2025(false, false, $tamanho);
    //     $this->pdf->Add_Page('P', $tamanho, 0);
    //     $this->pdf->SetMargins(0, 0, 0);

    //     $colunaAtual = 0;
    //     $linhaAtual = 0;

    //     foreach ($dados as $registro) {
    //         $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //         $y = $this->topo + ($linhaAtual * ($this->altura + $this->vertical));

    //         if ($exibirBorda) {
    //             $this->pdf->Rect($x, $y, $this->largura, $this->altura);
    //         }

    //         $ocupoularg = 0;

    //         foreach ($camp as $propCamp) {
    //             $this->pdf->SetY($y);
    //             $caracteres = intval($propCamp['etc_caracteres']) ?? 30;

    //             if ($propCamp['etc_campo'] == '1') {
    //                 $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                 $larg = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                 $this->pdf->Line($x, $y, $x + $larg, $y);
    //                 $alturalinha = (1 / 8) * 3;
    //                 $propCamp['etc_colunas'] = 100;

    //             } elseif ($propCamp['etc_codbar'] === 'S') {
    //                 $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
    //                 if ($caracteres == 0) $caracteres = 10;

    //                 $conteudo = trim(substr($registro[$propCamp['etc_campo']] ?? '', 0, $caracteres));
    //                 if (strlen($conteudo) == 0) $conteudo = str_repeat('0', $caracteres);

    //                 $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                 $altconte = intval($propCamp['etc_tamanho']) + 2;
    //                 $left = $x + (((100 - $propCamp['etc_colunas']) / 2) * ($this->largura / 100));

    //                 $this->pdf->Code128($left, $y, $conteudo, $tamconte, $altconte);
    //                 $y = $this->pdf->getY() + $altconte;

    //                 $alturalinha = $altconte;
    //                 $propCamp['etc_colunas'] = 100;

    //             } else {
    //                 $alturalinha = ($propCamp['etc_tamanho'] / 8) * 3;
    //                 $ocupado = $this->largura * ($ocupoularg / 100);
    //                 $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);

    //                 $total = $ocupado + $espacodisponivel;
    //                 if ($total > $this->largura) {
    //                     $espacodisponivel = $this->largura - $ocupado;
    //                     if ($espacodisponivel < 5) {
    //                         $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
    //                         $ocupado = 0;
    //                         $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);
    //                         $y = $this->pdf->getY();
    //                     }
    //                 }

    //                 $baseX = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));

    //                 if ($propCamp['etc_alinhamento'] === 'R') {
    //                     $x = $baseX + $this->largura - $espacodisponivel + 1;
    //                 } elseif ($propCamp['etc_alinhamento'] === 'C') {
    //                     $x = $baseX + $ocupado;
    //                 } else { // Alinhamento à esquerda (ou padrão)
    //                     $x = $baseX + $ocupado - 1;
    //                 }

    //                 $this->pdf->SetX($x);

    //                 $estilo = '';
    //                 if ($propCamp['etc_negrito'] === 'S') $estilo .= 'B';
    //                 if ($propCamp['etc_italico'] === 'S') $estilo .= 'I';
    //                 if ($propCamp['etc_sublinhado'] === 'S') $estilo .= 'U';

    //                 $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);

    //                 $rotulo = ($propCamp['etc_rotulo'] != 'Sem Rótulo' && $propCamp['etc_rotulo'] != '.') ? trim($propCamp['etc_rotulo']) : '';
    //                 $conteudo = '';

    //                 if ($propCamp['etc_campo'] == '0') {
    //                     $rotulo = '';
    //                     $conteudo = trim($propCamp['etc_rotulo']);
    //                     $propCamp['etc_linhas'] = 1;

    //                 } elseif ($propCamp['etc_campo'] == '2') {
    //                     $conteudo = data_br(date('Y-m-d'));
    //                     $propCamp['etc_linhas'] = 1;

    //                 } elseif ($propCamp['etc_campo'] == '3') {
    //                     $conteudo = data_br(date('Y-m-d H:i:s'));
    //                     $propCamp['etc_linhas'] = 1;

    //                 } else {
    //                     $conteudo = trim(substr($registro[$propCamp['etc_campo']] ?? '', 0, $caracteres));
    //                 }

    //                 $conteudo = trim($rotulo . ' ' . $conteudo);

    //                 $linhas = intval($propCamp['etc_linhas']); 
    //                 if ($linhas > 1) {
    //                     $conteudo = trim($registro[$propCamp['etc_campo']]);
    //                     $this->pdf->MultiCellLimited($espacodisponivel, $alturalinha, utf8_decode($conteudo), 0,$propCamp['etc_alinhamento'],false,$linhas, $caracteres);
    //                 } else {
    //                     $maxWidth = $espacodisponivel;
    //                     $conteudoOriginal = utf8_decode($conteudo);
    //                     $conteudoFinal = $conteudoOriginal;

    //                     while ($this->pdf->GetStringWidth($conteudoFinal) > $maxWidth) {
    //                         $conteudoFinal = substr($conteudoFinal, 0, -1); // Remove último caractere
    //                         if (strlen($conteudoFinal) <= 3) break; // Evita cortar demais
    //                     }

    //                     if ($conteudoFinal !== $conteudoOriginal) {
    //                         $conteudoFinal = rtrim($conteudoFinal);
    //                     }
    //                     if ($propCamp['etc_alinhamento'] === 'R') {
    //                         $x = (($this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal))) + $this->largura) - $espacodisponivel + 1;
    //                         $this->pdf->SetX($x);
    //                         $propCamp['etc_colunas'] = 100;
    //                     }

    //                     $this->pdf->Cell($espacodisponivel, $alturalinha, $conteudoFinal, 0, 0, $propCamp['etc_alinhamento']);
    //                     // $this->pdf->Cell($espacodisponivel, $alturalinha, "'".$propCamp['etc_alinhamento']."'", 0, 0, "'".$propCamp['etc_alinhamento']."'");
    //                 }
    //             }

    //             $ocupoularg += $propCamp['etc_colunas'];
    //             if ($ocupoularg >= 90) {
    //                 $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
    //                 $ocupoularg = 0;
    //                 $y = $this->pdf->getY();
    //             }
    //         }

    //         $colunaAtual++;
    //         if ($colunaAtual == $this->colunas) {
    //             $colunaAtual = 0;
    //             $linhaAtual++;
    //         }

    //         if ($linhaAtual >= $this->linhas) {
    //             $this->pdf->Add_Page('P', $tamanho, 0);
    //             $colunaAtual = 0;
    //             $linhaAtual = 0;
    //         }
    //     }

    //     $this->pdf->AliasNbPages();
    // }

private function renderEtiquetas(array $dados, array $camp, array $tamanho, bool $exibirBorda = true)
{
    // Altura inicial exagerada para renderização contínua
    $alturaTemporaria = 5000;

    // 1. Criar PDF temporário para medir altura usada
    $largurafinal   = ($this->colunas * $this->largura) + (($this->colunas - 1) * $this->horizontal) + ($this->esquerda + $this->direita);
    $this->pdf = new MyPdf2025(false, false, [$largurafinal, $alturaTemporaria]);
    $this->pdf->Add_Page('P', [$largurafinal, $alturaTemporaria], 0);
    $this->pdf->SetMargins(0, 0, 0);
    $this->pdf->SetAutoPageBreak(false, 0);

    $tamanho[0] = $largurafinal;
    $tamanho[1] = $alturaTemporaria;
    // Medir altura final usada
    // $this->pdf->StartTransaction();
    $this->renderConteudoInterno($dados, $camp, $exibirBorda);

    // $this->pdf->RollbackTransaction();

    // 2. Criar novo PDF com altura final real
    // $largurafinal   = ($this->colunas * $this->largura) + (($this->colunas - 1) * $this->horizontal) + ($this->esquerda + $this->direita);
    // $largurafinal   = 10;
    // debug($largurafinal, true);
    $this->pdf = new MyPdf2025(false, false, [$largurafinal, $this->alturaFinal]);
    $orienta = 'P';
    if($largurafinal > $this->alturaFinal){
        $orienta = 'L';
    }
    $this->pdf->Add_Page($orienta, [$largurafinal, $this->alturaFinal], 0);
    $this->pdf->SetMargins(0, 0, 0);
    $this->pdf->SetAutoPageBreak(false, 0);

    // debug($largurafinal, true);

    // 3. Renderizar de novo com altura exata
    $this->renderConteudoInterno($dados, $camp, $exibirBorda);
    $this->pdf->AliasNbPages();
}

private function renderConteudoInterno(array $dados, array $camp, bool $exibirBorda = true)
{
    $colunaAtual = 0;
    $y = $this->topo;
    $ultimoY = 0;
    $totalEtiquetas = count($dados);
    $etiquetaIndex = 0;

    foreach ($dados as $registro) {
        $x = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));

        if ($exibirBorda) {
            $this->pdf->Rect($x, $y, $this->largura, $this->altura);
        }

        $ocupoularg = 0;
        $campoY = $y;

        foreach ($camp as $propCamp) {
            $this->pdf->SetY($campoY);
            $caracteres = intval($propCamp['etc_caracteres']) ?? 30;

            if ($propCamp['etc_campo'] == '1') {
                $larg = $this->largura * ($propCamp['etc_colunas'] / 100);
                $this->pdf->Line($x, $campoY, $x + $larg, $campoY);
                $alturalinha = (1 / 8) * 3;
                $propCamp['etc_colunas'] = 100;

            } elseif ($propCamp['etc_codbar'] === 'S') {
                if ($caracteres == 0) $caracteres = 10;
                $conteudo = trim(substr($registro[$propCamp['etc_campo']] ?? '', 0, $caracteres));
                if (strlen($conteudo) == 0) $conteudo = str_repeat('0', $caracteres);

                $tamconte = $this->largura * ($propCamp['etc_colunas'] / 100);
                $altconte = intval($propCamp['etc_tamanho']) + 2;
                $left = $x + (((100 - $propCamp['etc_colunas']) / 2) * ($this->largura / 100));

                $this->pdf->Code128($left, $campoY, $conteudo, $tamconte, $altconte);
                $campoY += $altconte;

                $alturalinha = $altconte;
                $propCamp['etc_colunas'] = 100;

            } else {
                $alturalinha = ($propCamp['etc_tamanho'] / 8) * 3;
                $ocupado = $this->largura * ($ocupoularg / 100);
                $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);

                if (($ocupado + $espacodisponivel) > $this->largura) {
                    $espacodisponivel = $this->largura - $ocupado;
                    if ($espacodisponivel < 5) {
                        $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
                        $ocupado = 0;
                        $espacodisponivel = $this->largura * ($propCamp['etc_colunas'] / 100);
                        $campoY = $this->pdf->getY();
                    }
                }

                $baseX = $this->esquerda + ($colunaAtual * ($this->largura + $this->horizontal));
                $x = match ($propCamp['etc_alinhamento']) {
                    'R' => $baseX + $this->largura - $espacodisponivel + 1,
                    'C' => $baseX + $ocupado,
                    default => $baseX + $ocupado - 1,
                };

                $this->pdf->SetX($x);

                $estilo = '';
                if ($propCamp['etc_negrito'] === 'S') $estilo .= 'B';
                if ($propCamp['etc_italico'] === 'S') $estilo .= 'I';
                if ($propCamp['etc_sublinhado'] === 'S') $estilo .= 'U';

                $this->pdf->SetFont($propCamp['etc_fonte'], $estilo, $propCamp['etc_tamanho']);

                $rotulo = ($propCamp['etc_rotulo'] != 'Sem Rótulo' && $propCamp['etc_rotulo'] != '.') ? trim($propCamp['etc_rotulo']) : '';
                $conteudo = '';

                if ($propCamp['etc_campo'] == '0') {
                    $rotulo = '';
                    $conteudo = trim($propCamp['etc_rotulo']);
                    $propCamp['etc_linhas'] = 1;
                } elseif ($propCamp['etc_campo'] == '2') {
                    $conteudo = data_br(date('Y-m-d'));
                    $propCamp['etc_linhas'] = 1;
                } elseif ($propCamp['etc_campo'] == '3') {
                    $conteudo = data_br(date('Y-m-d H:i:s'));
                    $propCamp['etc_linhas'] = 1;
                } else {
                    $conteudo = trim(substr($registro[$propCamp['etc_campo']] ?? '', 0, $caracteres));
                }

                $conteudo = trim($rotulo . ' ' . $conteudo);
                $linhas = intval($propCamp['etc_linhas']);

                if ($linhas > 1) {
                    $conteudo = trim($registro[$propCamp['etc_campo']]);
                    $this->pdf->MultiCellLimited($espacodisponivel, $alturalinha, utf8_decode($conteudo), 0, $propCamp['etc_alinhamento'], false, $linhas, $caracteres);
                    $campoY = $this->pdf->getY();
                } else {
                    $conteudoOriginal = utf8_decode($conteudo);
                    $conteudoFinal = $conteudoOriginal;

                    while ($this->pdf->GetStringWidth($conteudoFinal) > $espacodisponivel) {
                        $conteudoFinal = substr($conteudoFinal, 0, -1);
                        if (strlen($conteudoFinal) <= 3) break;
                    }

                    $this->pdf->Cell($espacodisponivel, $alturalinha, $conteudoFinal, 0, 0, $propCamp['etc_alinhamento']);
                    $campoY += $alturalinha;
                }
            }

            $ocupoularg += $propCamp['etc_colunas'];
            if ($ocupoularg >= 90) {
                $this->pdf->Cell(10, $alturalinha, '', 0, 1, 'E');
                $ocupoularg = 0;
                $campoY = $this->pdf->getY();
            }
        }

        $colunaAtual++;
        $etiquetaIndex++;

        // Salta para a próxima linha apenas se completar todas as colunas
        if ($colunaAtual >= $this->colunas || $etiquetaIndex === $totalEtiquetas) {
            $colunaAtual = 0;
            $y += $this->altura + $this->vertical;
        }

        $ultimoY = max($ultimoY, $y);
    }
    $this->alturaFinal = $ultimoY;
    // return $ultimoY;
}

    public function emiteEtiqueta($etq_id, $chave = false)
    {
        $etiq = $this->etiqueta->getEtiqueta($etq_id);
        $camp = $this->etiquetaCampo->getEtiquetaCampo($etq_id);

        if (!$etiq) return;

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

        $tamanho[0] = ($this->largura * $this->colunas) + ($this->horizontal * ($this->colunas - 1)) + $this->esquerda + $this->direita;
        $tamanho[1] = $this->topo + ($this->altura * $this->linhas) + ($this->vertical * $this->linhas) + $this->rodape + $this->altura;


        $modelo = ($chave === false);

        if ($modelo) {
            $fields = array_column($camp, 'etc_campo');
            $telid = $etq['tel_id'];
            $telas = $this->tela->getTelaId($telid)[0];

            if (!empty($telas['tel_model'])) {
                $model = $telas['tel_model'];
                $model_atual = model("App\\Models\\" . substr($model, 0, 6) . "\\" . $model);
                $dados = $this->common->getListaTabela($model_atual->DBGroup, $model_atual->view, $fields, false, 20);
            }
        } else {
            $dados = cache()->get($chave);
        }

        $this->renderEtiquetas($dados, $camp, $tamanho, $modelo);

        if($chave){
            $output = $this->pdf->Output('S'); // 'S' retorna o PDF como string
            $output = base64_encode($output);
            echo json_encode(['pdf' => $output]); // Retorne um JSON
        } else {
            $this->response->setHeader('Content-Type', 'application/pdf');
            $this->pdf->Output('etiqueta.pdf', 'I');
        }
    }

    public function previewEtiquetaViaAjax()
    {
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
        // debug($tamanho, true);

        $this->renderEtiquetas($dados, $camp, $tamanho, true);

        $output = $this->pdf->Output('etiqueta.pdf', 'S');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($output);
    }

}
