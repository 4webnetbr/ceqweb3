<?php
// Carrega o autoloader do Composer
require __DIR__ . '/../../vendor/autoload.php';

// Carrega os caminhos do CI4
require_once __DIR__ . '/../../app/Config/Paths.php';
$paths = new Config\Paths();

// Bootstrap do CodeIgniter
require_once realpath($paths->systemDirectory . 'bootstrap.php');

// Inicializa o framework (sem executar uma requisição web)
$context = CodeIgniter\CodeIgniter::createContext();
$app = new CodeIgniter\CodeIgniter($context);
$analise        = new MicrobAnaliseModel();
$produto        = new ProdutProdutoModel();
$lote           = new ProdutLoteModel();
$busca          = new BuscasSapiens();
$tipomovimento  = new EstoquTipoMovimentacaoModel();

$app->initialize();

// Agora você pode usar qualquer recurso do CI4

use App\Controllers\BuscasSapiens;

while (true) {
    $saldoestObjs = $busca->buscaEstoqueDeposito('QUA', '');
    $saldoest = is_array($saldoestObjs) ? $saldoestObjs : iterator_to_array($saldoestObjs);
    if (empty($saldoest)) {
        echo json_encode(['data' => []]);
        return;
    }
    $saldoestFiltrado = array_filter($saldoest, function ($item) {
        // Remove o item se:
        // (codigoLote == 'N/A' && estoqueDeposito == 0) ou (codigoLote != 'N/A' && quantidadeEstoque == 0)
        return !(($item->codigoLote == 'N/A' && $item->estoqueDeposito == 0) ||
            ($item->codigoLote != 'N/A' && $item->quantidadeEstoque == 0));
    });
    // reindexar o array (opcional):
    $saldoestFiltrado = array_values($saldoestFiltrado);
    // Converte todos os objetos para array de uma vez
    $saldoestArr = array_map(function ($obj) {
        return (array) $obj;
    }, $saldoestFiltrado);

    $codigoProdutoArray = array_column($saldoestArr, 'codigoProduto');
    $codigoLoteArray    = array_column($saldoestArr, 'codigoLote');
    // debug($codigoLoteArray);

    $prodsArr   = $produto->getProdutoCodLista($codigoProdutoArray, 'S');
    $lotesArr   = $lote->getLoteIn($codigoLoteArray);
    // debug($lotesArr);
    $analises   = $analise->getAnaliseCod();
    // debug($analises);

    // Reindexa os arrays para acesso rápido
    $prods  = array_column($prodsArr, null, 'pro_codpro');
    $lotes  = array_column($lotesArr, null, 'lot_lote');
    // debug($lotes);
    // exit;
    $analisesAssoc = [];
    foreach ($analises as $analise) {
        // Se o objeto vier como stdClass, converte para array
        if (is_object($analise)) {
            $analise = (array)$analise;
        }
        $chave = $analise['pro_codpro'] . '-' . $analise['lot_lote'];
        $analisesAssoc[$chave] = $analise;
    }

    $totalProdutos = count($saldoestArr);

    // Obtém o tipo de movimentação apenas uma vez
    $movimData = $tipomovimento->getTipoMovimentacao(5);
    $movim     = isset($movimData[0]) ? $movimData[0] : null;

    // Arrays para processamento em lote (caso seus métodos suportem)
    $analisesToSave = [];
    $lotesToUpdate  = [];
    $geramovimentacao = false;

    foreach ($saldoestArr as $saldo) {
        $prodproc   = $saldo['codigoProduto'];
        $loteproc   = $saldo['codigoLote'];
        $quantidade = str_replace(['.', ','], '', $saldo['quantidadeEstoque']);

        // envia_msg_ws($this->data['controler'], "Processando Produto $prodproc Lote $loteproc", 'MsgServer', session()->get('usu_id'), 1);

        // Verifica se o produto existe e se requer análise (cla_micro == 'S')
        if (!isset($prods[$prodproc]) || $prods[$prodproc]['cla_micro'] !== 'S') {
            continue;
        }

        $prod = $prods[$prodproc];
        $lote = $lotes[$loteproc] ?? [
            'lot_lote'     => $saldo['codigoLote'],
            'lot_entrada'  => $saldo['entrada'],
            'lot_validade' => $saldo['validade'],
            'stt_id'       => null,
        ];
        $lote['lot_entrada'] = $saldo['entrada'];

        $analiseKey = $prodproc . '-' . $loteproc;
        // debug($analisesAssoc);
        // debug($analiseKey);
        $analis = $analisesAssoc[$analiseKey] ?? null;
        // debug($analis);

        // Se lote bloqueado e sem análise ou com status reprovada (16)
        if ($lote['stt_id'] == 8) {
            if (is_null($analis) || $analis['stt_id'] == 16) {
                $analisesToSave[] = [
                    'pro_id'   => $prod['pro_id'],
                    'lot_id'   => $lote['lot_id'],
                    'ana_qtde' => $quantidade,
                    'ana_data' => date('Y-m-d'),
                    'stt_id'   => 10, // ANÁLISE BLOQUEADA
                ];
            } else {
                $geramovimentacao = true;
            }
            // debug($analisesToSave);
        }
        // Se lote liberado
        elseif ($lote['stt_id'] == 9) {
            // Se não tem analise ou analise Não Realizada(13) ou Reprovada(16)
            if (is_null($analis) || in_array($analis['stt_id'], [13, 16])) {
                // cria nova analise
                $analisesToSave[] = [
                    'pro_id'   => $prod['pro_id'],
                    'lot_id'   => $lote['lot_id'],
                    'ana_qtde' => $quantidade,
                    'ana_data' => date('Y-m-d'),
                    'stt_id'   => 10, // ANÁLISE BLOQUEADA
                ];
                // muda o status do lote para Bloqueado
                $lotesToUpdate[] = [
                    'lot_id' => $lote['lot_id'],
                    'stt_id' => 8, // LOTE BLOQUEADO
                ];
            } else {
                $geramovimentacao = true;
            }
        }
        if ($geramovimentacao) {
            if ($analis && $analis['stt_id'] == 15) {
                // se existe análise com status APROVADA (15)
                // envia_msg_ws(
                //     $this->data['controler'],
                //     "Lote {$saldo['codigoLote']} Análise Aprovada, Movimenta Estoque",
                //     'MsgServer',
                //     session()->get('usu_id'),
                //     1
                // );
                // GERA MOVIMENTAÇÃO DA QUARENTENA PARA O DEP GERAL (5)
                if ($movim) {
                    (new SoapSapiens())->transfProdutosSapiens(
                        $prod['pro_codpro'],
                        $movim['tmo_transacao_erp'],
                        $movim['dep_codorigem'],
                        date('d/m/Y'),
                        $quantidade,
                        $lote['lot_lote'],
                        $movim['dep_coddestino']
                    );
                }
                // muda o status do lote para Liberado
                $lotesToUpdate[] = [
                    'lot_id' => $lote['lot_id'],
                    'stt_id' => 9, // LOTE LIBERADO
                ];
            }
        }
    }

    // Salva as análises em lote, se possível
    if (!empty($analisesToSave)) {
        if (method_exists($analise, 'saveBatch')) {
            $analise->saveBatch($analisesToSave);
        } else {
            foreach ($analisesToSave as $data) {
                $analise->save($data);
            }
        }
        // envia_msg_ws($this->data['controler'], "Atualizando Análises", 'MsgServer', session()->get('usu_id'), 1);
        // $this->analise->atualizaEvento();
    }

    // Atualiza os lotes em lote, se suportado
    if (!empty($lotesToUpdate)) {
        if (method_exists($lote, 'updateBatch')) {
            // debug($lotesToUpdate, true);
            $lote->updateBatch($lotesToUpdate,'lot_id');
        } else {
            foreach ($lotesToUpdate as $data) {
                $lote->save($data);
            }
        }
    }

    // Espera 60 segundos antes de repetir
    sleep(300);
}