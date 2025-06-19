<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\MicrobAnaliseModel;
use App\Models\ProdutProdutoModel;
use App\Models\ProdutLoteModel;
use App\Models\Estoque\EstoqTipoMovimentacaoModel;
use App\Controllers\BuscasSapiens;
use App\Libraries\SoapSapiens;

class WorkAnalise extends BaseCommand
{
    protected $group       = 'Workers';
    protected $name        = 'worker:analise';
    protected $description = 'Executa o worker de análises microbiológicas.';
    protected $usage       = 'php spark worker:analise';

    public function run(array $params)
    {
        $logger         = service('logger');
        $analise        = new MicrobAnaliseModel();
        $produto        = new ProdutProdutoModel();
        $lote           = new ProdutLoteModel();
        $busca          = new BuscasSapiens();
        $tipomovimento  = new EstoqTipoMovimentacaoModel();

        $logger->info('Worker worker:analise iniciado.');
        CLI::write('Worker iniciado com sucesso.', 'green');

        while (true) {
            try {
                $saldoestObjs = $busca->buscaEstoqueDeposito('QUA', '');
                $saldoest = is_array($saldoestObjs) ? $saldoestObjs : iterator_to_array($saldoestObjs);

                if (empty($saldoest)) {
                    $logger->info('Sem saldo. Aguardando 5 minutos.');
                    CLI::write('Sem saldo encontrado. Aguardando 5 minutos...', 'yellow');
                    sleep(300);
                    continue;
                }

                $saldoestFiltrado = array_filter($saldoest, function ($item) {
                    return !(($item->codigoLote === 'N/A' && $item->estoqueDeposito == 0) ||
                             ($item->codigoLote !== 'N/A' && $item->quantidadeEstoque == 0));
                });

                $saldoestArr = array_map(fn($obj) => (array)$obj, array_values($saldoestFiltrado));
                $codigoProdutoArray = array_column($saldoestArr, 'codigoProduto');
                $codigoLoteArray    = array_column($saldoestArr, 'codigoLote');

                $prodsArr = $produto->getProdutoCodLista($codigoProdutoArray, 'S');
                $lotesArr = $lote->getLoteIn($codigoLoteArray);
                $analises = $analise->getAnaliseCod();
                $movimData = $tipomovimento->getTipoMovimentacao(5);
                $movim = $movimData[0] ?? null;

                $prods = array_column($prodsArr, null, 'pro_codpro');
                $lotes = array_column($lotesArr, null, 'lot_lote');

                $analisesAssoc = [];
                foreach ($analises as $a) {
                    $a = (array) $a;
                    $analisesAssoc[$a['pro_codpro'] . '-' . $a['lot_lote']] = $a;
                }

                $analisesToSave = [];
                $lotesToUpdate  = [];

                foreach ($saldoestArr as $saldo) {
                    $prodproc   = $saldo['codigoProduto'];
                    $loteproc   = $saldo['codigoLote'];
                    $quantidade = str_replace(['.', ','], '', $saldo['quantidadeEstoque']);

                    if (!isset($prods[$prodproc]) || $prods[$prodproc]['cla_micro'] !== 'S') {
                        continue;
                    }

                    $prod = $prods[$prodproc];
                    $loteInfo = $lotes[$loteproc] ?? [
                        'lot_lote'     => $loteproc,
                        'lot_entrada'  => $saldo['entrada'],
                        'lot_validade' => $saldo['validade'],
                        'stt_id'       => null,
                    ];
                    $loteInfo['lot_entrada'] = $saldo['entrada'];

                    $analiseKey = $prodproc . '-' . $loteproc;
                    $analis = $analisesAssoc[$analiseKey] ?? null;
                    $geramovimentacao = false;

                    if ($loteInfo['stt_id'] == 8) {
                        if (is_null($analis) || $analis['stt_id'] == 16) {
                            $analisesToSave[] = [
                                'pro_id'   => $prod['pro_id'],
                                'lot_id'   => $loteInfo['lot_id'],
                                'ana_qtde' => $quantidade,
                                'ana_data' => date('Y-m-d'),
                                'stt_id'   => 10,
                            ];
                        } else {
                            $geramovimentacao = true;
                        }
                    } elseif ($loteInfo['stt_id'] == 9) {
                        if (is_null($analis) || in_array($analis['stt_id'], [13, 16])) {
                            $analisesToSave[] = [
                                'pro_id'   => $prod['pro_id'],
                                'lot_id'   => $loteInfo['lot_id'],
                                'ana_qtde' => $quantidade,
                                'ana_data' => date('Y-m-d'),
                                'stt_id'   => 10,
                            ];
                            $lotesToUpdate[] = [
                                'lot_id' => $loteInfo['lot_id'],
                                'stt_id' => 8,
                            ];
                        } else {
                            $geramovimentacao = true;
                        }
                    }

                    if ($geramovimentacao && $analis && $analis['stt_id'] == 15) {
                        if ($movim) {
                            (new SoapSapiens())->transfProdutosSapiens(
                                $prod['pro_codpro'],
                                $movim['tmo_transacao_erp'],
                                $movim['dep_codorigem'],
                                date('d/m/Y'),
                                $quantidade,
                                $loteInfo['lot_lote'],
                                $movim['dep_coddestino']
                            );
                        }
                        $lotesToUpdate[] = [
                            'lot_id' => $loteInfo['lot_id'],
                            'stt_id' => 9,
                        ];
                    }
                }

                if (!empty($analisesToSave)) {
                    if (method_exists($analise, 'saveBatch')) {
                        $analise->saveBatch($analisesToSave);
                    } else {
                        foreach ($analisesToSave as $data) {
                            $analise->save($data);
                        }
                    }
                    $logger->info(count($analisesToSave) . ' análises salvas.');
                }

                if (!empty($lotesToUpdate)) {
                    if (method_exists($lote, 'updateBatch')) {
                        $lote->updateBatch($lotesToUpdate, 'lot_id');
                    } else {
                        foreach ($lotesToUpdate as $data) {
                            $lote->save($data);
                        }
                    }
                    $logger->info(count($lotesToUpdate) . ' lotes atualizados.');
                }

            } catch (\Throwable $e) {
                $logger->error('Erro no worker: ' . $e->getMessage());
                CLI::error('Erro no worker: ' . $e->getMessage());
            }

            CLI::write('Aguardando 5 minutos para o próximo ciclo...', 'cyan');
            sleep(300); // 5 minutos
        }
    }
}
