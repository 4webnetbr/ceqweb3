<?php

use App\Controllers\BuscasSapiens;
use App\Libraries\SoapSapiens;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Produt\ProdutProdutoModel;


// function geraMovimentoRequisicoes($movimentos, $controller)
// {
//     $mdproduto                = new ProdutProdutoModel();
//     $tipomovimento          = new EstoquTipoMovimentacaoModel();
//     $common                 = new CommonModel();
//     $requisicao             = new EstoquRequisicaoModel();

//     $soaptrf = new SoapSapiens();
//     for ($m = 0; $m < count($movimentos); $m++) {
//         $mov = $movimentos[$m];
//         $lote = $mov['lot_lote'] ?? '';
//         $loteval = $mov['lot_validade'] ?? '';
//         if ($mov['rep_id'] != null) {
//             $reqlote = $requisicao->getRequisicaoRep($mov['rep_id'])[0];
//             $lote = $reqlote->lot_lote;
//             $loteval = $reqlote->lot_validade;
//         }
//         $produto = (array) $mdproduto->getProduto($mov['pro_id'], false)[0];
//         $codpro = $produto['pro_codpro'];
//         $msg =  'Produto ' . $codpro . ' Lote ' . $lote . $mov['msg'];
//         envia_msg_ws($controller, $msg, 'MsgServer', session()->get('usu_id'), 1);

//         $datmov = date('d/m/Y');
//         $codlot = $lote;
//         $qtdmov = $mov['qt'];
//         $qtdmov = str_replace(['.', ','], '', $qtdmov);
//         // BUSCA TIPO MOVIMENTO
//         $movim  = (array) $tipomovimento->getTipoMovimentacao($mov['id']);
//         $codtns = $movim[0]->tmo_transacao_erp;
//         $depori = $movim[0]->dep_codorigem;
//         $depdes = $movim[0]->dep_coddestino;
//         if (isset($mov['tipomov']) && $mov['tipomov'] == 'R') {
//             $depori = $movim[0]->dep_coddestino;
//             $depdes = $movim[0]->dep_codorigem;
//         }
//         // if ($mov['reserva'] != '') {
//         if ($movim[0]->dep_reserva != '') {
//             $reserva = strtoupper($mov['reserva']);
//             if ($reserva === 'D') {
//                 $depdes = $movim[0]->dep_reserva;
//             } elseif ($reserva === 'O') {
//                 $depori = $movim[0]->dep_reserva;
//             }
//         }
//         $valida = data_br($loteval);

//         // debug($movim);

//         log_message('info', 'Movimento ' . json_encode($movim));
//         log_message('info', 'Depósito Origem ' . $depori);
//         log_message('info', 'Depósito Destino ' . $depdes);

//         // debug( 'Movimento '.json_encoade($movim));
//         // debug( 'Depósito Origem '.$depori);
//         // debug( 'Depósito Destino '.$depdes);

//         if ($depdes == null || $depdes == '') {
//             log_message('info', 'Sem depósito de Destino, Somente Saída');
//             $movimenta = $soaptrf->movimProdutosSapiens(
//                 $codpro,
//                 $codtns,
//                 $depori,
//                 $datmov,
//                 $qtdmov,
//                 $codlot,
//                 $depdes,
//                 $valida
//             );
//         } else {
//             log_message('info', 'Com depósito de Destino, Transferência');
//             $movimenta = $soaptrf->transfProdutosSapiens(
//                 $codpro,
//                 $codtns,
//                 $depori,
//                 $datmov,
//                 $qtdmov,
//                 $codlot,
//                 $depdes,
//                 $valida
//             );
//         }
//         if ($movimenta['status'] == 'Erro') {
//             // se o movimento deu erro, verifica se teve movimentos anteriores e desfaz
//             if ($m > 0) {
//                 for ($rv = ($m - 1); $rv >= 0; $rv--) {
//                     $rev = $movimentos[$rv];
//                     $produto = (array) $mdproduto->getProduto($rev['pro_id'], false)[0];
//                     $codpro = $produto['pro_codpro'];
//                     $lote = $rev['lot_lote'] ?? '';
//                     $loteval = $rev['lot_validade'] ?? '';
//                     if ($rev['rep_id'] != null) {
//                         $reqlote = $requisicao->getRequisicaoRep($rev['rep_id'])[0];
//                         $lote = $reqlote->lot_lote;
//                         $loteval = $reqlote->lot_validade;
//                     }

//                     $msg =  'Produto ' . $codpro . ' Lote ' . $lote . $rev['msg'];
//                     envia_msg_ws($controller, $msg, 'MsgServer', session()->get('usu_id'), 1);

//                     $datmov = date('d/m/Y');
//                     $codlot = $lote;
//                     $qtdmov = $rev['qt'];
//                     $qtdmov = str_replace(['.', ','], '', $qtdmov);
//                     // BUSCA TIPO MOVIMENTO
//                     $movim  = $tipomovimento->getTipoMovimentacao($rev['id']);
//                     $codtns = $movim[0]->tmo_transacao_erp;
//                     // deposito de destino é a origem, para reverter
//                     $depdes = $movim[0]->dep_codorigem;
//                     // depósito de origem é o destino, para reverter
//                     $depori = $movim[0]->dep_coddestino;
//                     $valida = data_br($loteval);

//                     log_message('info', 'Movimento Reverso ' . json_encode($movim));
//                     $reverte = $soaptrf->transfProdutosSapiens(
//                         $codpro,
//                         $codtns,
//                         $depori,
//                         $datmov,
//                         $qtdmov,
//                         $codlot,
//                         $depdes,
//                         $valida
//                     );
//                 }
//             }
//             break;
//         }
//     }
//     return $movimenta;
// }
function geraMovimentoRequisicoes($movimentos, $controller)
{
    $mdproduto     = new ProdutProdutoModel();
    $tipomovimento = new EstoquTipoMovimentacaoModel();
    $common        = new CommonModel();
    $requisicao    = new EstoquRequisicaoModel();
    $soaptrf       = new SoapSapiens();

    if (empty($movimentos)) {
        return ['status' => 'OK', 'mensagem' => 'Nenhum movimento a processar'];
    }

    $movimentosRealizados = [];

    for ($m = 0; $m < count($movimentos); $m++) {
        $mov     = $movimentos[$m];
        $lote    = $mov['lot_lote'] ?? '';
        $loteval = $mov['lot_validade'] ?? '';

        if ($mov['rep_id'] != null) {
            $reqlote = $requisicao->getRequisicaoRep($mov['rep_id'])[0];
            $lote    = $reqlote->lot_lote;
            $loteval = $reqlote->lot_validade;
        }
        $msg = $mov['msg'] ?? '';

        $produto = (array) $mdproduto->getProduto($mov['pro_id'], false)[0];
        $codpro  = $produto['pro_codpro'];
        $msg = (trim((string) $msg) !== '' ? $msg . '<br />' : '')
            . 'Produto ' . $codpro
            . ' Lote ' . (trim((string) $lote) !== '' ? $lote : 'Sem Lote');

        envia_msg_ws($controller, $msg, 'MsgServer', session()->get('usu_id'), 1);

        $datmov = date('d/m/Y');
        $codlot = $lote;
        $qtdmov = $mov['qt'];
        $qtdmov = str_replace(['.', ','], '', $qtdmov);

        // BUSCA TIPO MOVIMENTO
        $movim  = (array) $tipomovimento->getTipoMovimentacao($mov['id']);
        debug($movim, true);
        $codtns = $movim[0]->tmo_transacao_erp;
        $depori = $movim[0]->dep_codorigem;
        $depdes = $movim[0]->dep_coddestino;

        if (isset($mov['tipomov']) && $mov['tipomov'] == 'R') {
            $depori = $movim[0]->dep_coddestino;
            $depdes = $movim[0]->dep_codorigem;
        }

        if ($movim[0]->dep_reserva != '') {
            $reserva = strtoupper($mov['reserva']);
            if ($reserva === 'D') {
                $depdes = $movim[0]->dep_reserva;
            } elseif ($reserva === 'O') {
                $depori = $movim[0]->dep_reserva;
            }
        }

        $valida = data_br($loteval);

        log_message('info', 'Movimento ' . json_encode($movim));
        log_message('info', 'Depósito Origem ' . $depori);
        log_message('info', 'Depósito Destino ' . $depdes);

        if ($depdes == null || $depdes == '') {
            log_message('info', 'Sem depósito de Destino, Somente Saída');
            $movimenta = $soaptrf->movimProdutosSapiens(
                $codpro,
                $codtns,
                $depori,
                $datmov,
                $qtdmov,
                $codlot,
                $depdes,
                $valida,
                $msg
            );
        } else {
            log_message('info', 'Com depósito de Destino, Transferência');
            $movimenta = $soaptrf->transfProdutosSapiens(
                $codpro,
                $codtns,
                $depori,
                $datmov,
                $qtdmov,
                $codlot,
                $depdes,
                $valida,
                $msg
            );
        }

        if ($movimenta['status'] == 'Erro') {
            log_message('error', 'Erro no movimento ' . $m . ': ' . ($movimenta['mensagem'] ?? ''));

            // Reverte os movimentos já realizados
            if (!empty($movimentosRealizados)) {
                log_message('info', 'Iniciando reversão de ' . count($movimentosRealizados) . ' movimento(s)');

                foreach (array_reverse($movimentosRealizados) as $rv) {
                    $prodRev  = (array) $mdproduto->getProduto($rv['pro_id'], false)[0];
                    $codproRv = $prodRev['pro_codpro'];
                    $loteRv   = $rv['lot_lote'] ?? '';
                    $lotevRv  = $rv['lot_validade'] ?? '';

                    if ($rv['rep_id'] != null) {
                        $reqloteRv = $requisicao->getRequisicaoRep($rv['rep_id'])[0];
                        $loteRv    = $reqloteRv->lot_lote;
                        $lotevRv   = $reqloteRv->lot_validade;
                    }

                    $msgRv  = 'REVERSÃO - Produto ' . $codproRv . ' Lote ' . $loteRv . ' - ' . $rv['msg'];
                    envia_msg_ws($controller, $msgRv, 'MsgServer', session()->get('usu_id'), 1);

                    $qtdmovRv = str_replace(['.', ','], '', $rv['qt']);
                    $movimRv  = (array) $tipomovimento->getTipoMovimentacao($rv['id']);
                    $codtnsRv = $movimRv[0]->tmo_transacao_erp;
                    $validaRv = data_br($lotevRv);

                    // Inverte origem/destino para reverter
                    $deporiRv = $rv['depdes_realizado']; // destino que foi usado
                    $depdesRv = $rv['depori_realizado']; // origem que foi usada

                    log_message('info', 'Reversão: ' . $codproRv . ' Ori:' . $deporiRv . ' Des:' . $depdesRv);

                    $reverte = $soaptrf->transfProdutosSapiens(
                        $codproRv,
                        $codtnsRv,
                        $deporiRv,
                        date('d/m/Y'),
                        $qtdmovRv,
                        $loteRv,
                        $depdesRv,
                        $validaRv,
                        $msgRv
                    );

                    if ($reverte['status'] == 'Erro') {
                        log_message('error', 'Falha ao reverter movimento do produto ' . $codproRv . ': ' . ($reverte['mensagem'] ?? ''));
                    }
                }
            }

            return [
                'status'   => 'Erro',
                'mensagem' => $movimenta['mensagem'] ?? 'Erro ao gerar movimento de estoque',
            ];
        }

        // Guarda os depósitos reais usados para permitir reversão correta
        $movimentosRealizados[] = array_merge($mov, [
            'depori_realizado' => $depori,
            'depdes_realizado' => $depdes,
            'lot_lote'         => $lote,
            'lot_validade'     => $loteval,
        ]);
    }

    return ['status' => 'OK', 'mensagem' => 'Movimentos realizados com sucesso'];
}


function indexarEstoque(array|object|null $estoque): array
{
    if ($estoque instanceof \stdClass) {
        $estoque = [$estoque];
    } elseif (! is_array($estoque)) {
        $estoque = [];
    }

    $indexado = [];

    foreach ($estoque as $item) {
        if (! is_object($item)) {
            continue;
        }

        if (isset($item->quantidadeEstoque)) {
            $item->quantidadeEstoque = str_replace(
                ['.', ','],
                ['', '.'],
                (string) $item->quantidadeEstoque
            );
        }

        $produto = $item->codigoProduto ?? null;
        $lote    = $item->codigoLote    ?? 'SEM_LOTE';

        if ($produto === null) {
            continue;
        }

        $indexado[$produto][$lote][] = $item;
    }

    return $indexado;
}

function indexarRequisicaoProdutos(array|null $requisicaoProdutos): array
{
    if (! is_array($requisicaoProdutos)) {
        $requisicaoProdutos = [];
    }

    $indexado = [];

    foreach ($requisicaoProdutos as $item) {
        if (! is_array($item)) {
            continue;
        }

        $repId = $item['rep_id'] ?? null;
        $proId = $item['pro_id'] ?? null;

        if ($repId === null || $proId === null) {
            continue;
        }

        $indexado[$repId][$proId] = $item;
    }

    return $indexado;
}
function indexarConsumo(array|object|null $consumo): array
{
    // 🔒 Normalização (igual fizemos antes)
    if ($consumo instanceof \stdClass) {
        $consumo = [$consumo];
    } elseif (!is_array($consumo)) {
        $consumo = [];
    }

    $indexado = [];

    foreach ($consumo as $item) {
        // Proteção: garante formato válido
        if (is_object($item)) {
            $item = (array) $item;
        }

        if (!is_array($item)) {
            continue;
        }

        $codigo = $item['codigo_erp'] ?? null;

        if ($codigo === null) {
            continue;
        }

        $indexado[$codigo] = $item;
    }

    return $indexado;
}
function indexarArray(array|object|null $items, array $campos, bool $multiplos = false): array
{
    if ($items instanceof \stdClass) {
        $items = [$items];
    } elseif (! is_array($items)) {
        $items = [];
    }

    $indexado = [];

    foreach ($items as $item) {
        if (! is_object($item)) {
            continue;
        }

        $ref = &$indexado;

        foreach ($campos as $campo) {
            $valor = trim((string) ($item->$campo ?? 'SEM_' . strtoupper($campo)));

            if (! isset($ref[$valor])) {
                $ref[$valor] = [];
            }

            $ref = &$ref[$valor];
        }

        if ($multiplos) {
            $ref[] = $item;
        } else {
            $ref = $item;
        }

        unset($ref);
    }

    return $indexado;
}

function tratarOriginal(int|string $reqId): bool
{
    $requisicao    = new EstoquRequisicaoModel();
    $requisicaoate = new EstoquRequisicaoProdutoAtendimentoModel();

    $reqDerivadaLista = $requisicao->getRequisicao($reqId);

    if (empty($reqDerivadaLista)) {
        return false;
    }

    $reqderivada = $reqDerivadaLista[0];
    log_message('info', 'Derivada ' . json_encode($reqderivada));

    if (
        ! isset($reqderivada->req_original)
        || trim((string) $reqderivada->req_original) === ''
    ) {
        return false;
    }

    $produtosDerivada = $requisicao->getRequisicaoProdutos($reqderivada->req_id);

    $reqOriginalLista = $requisicao->getRequisicao($reqderivada->req_original);

    if (empty($reqOriginalLista)) {
        return false;
    }

    $reqoriginal  = $reqOriginalLista[0];
    $produtosOrig = $requisicao->getRequisicaoProdutos($reqoriginal->req_id);

    log_message('info', 'Tem Original ' . $reqoriginal->req_id);

    $produtosOrigIndexados = indexarArray($produtosOrig, ['pro_id', 'lot_id']);

    $totalAtendidos = 0;
    $db = \Config\Database::connect();
    $db->transBegin();

    foreach ($produtosDerivada as $prodDerivada) {
        $proId = trim((string) $prodDerivada->pro_id);
        $lotId = trim((string) ($prodDerivada->lot_id ?? 'SEM_LOT_ID'));

        $prod = $produtosOrigIndexados[$proId][$lotId] ?? null;

        if ($prod === null) {
            continue;
        }

        if (is_null($prodDerivada->rpa_data_conferencia) || is_null($prodDerivada->rpa_data_inspecao)) {
            continue;
        }

        $qtdDerivada = $prodDerivada->rpa_aprovada > -1
            ? $prodDerivada->rpa_aprovada
            : ($prodDerivada->rpa_conferida > -1
                ? $prodDerivada->rpa_conferida
                : $prodDerivada->rpa_atendida);

        log_message('info', 'Produto Derivada ' . $prodDerivada->pro_codpro);
        log_message('info', 'Lote Derivada ' . $prodDerivada->lot_lote);
        log_message('info', 'Quantidade Derivada ' . $qtdDerivada);
        log_message('info', 'Solicitada Original ' . $prod->rep_quantia);
        log_message('info', 'Produto Original ' . $prod->pro_codpro);
        log_message('info', 'Lote Original ' . $prod->lot_lote);

        if ((int) $qtdDerivada !== (int) $prod->rep_quantia || (int) $qtdDerivada == 0) {
            continue;
        }

        $ate = $requisicaoate->getProdutoRequisicaoAtendimento($prod->rep_id, $prod->pro_id);
        if ($ate) {
            continue;
        }

        envia_msg_ws('AteRequisicao', 'Gerando Movimentação de Estoque', 'MsgServer', session()->get('usu_id'), 1);
        $movim = geraMovimentoRequisicoes([[
            'id'      => $reqoriginal->tmo_id,
            'qt'      => (int) $prod->rep_quantia,
            'msg'     => 'Atendimento da Requisição Original Nº ' . str_pad($reqoriginal->req_id, 6, '0', STR_PAD_LEFT),
            'pro_id'  => $prod->pro_id,
            'rep_id'  => $prod->rep_id,
            'reserva' => 'D',
        ]], 'AteRequisicao');

        if ($movim['status'] === 'Erro') {
            continue;
        }

        $salva = $requisicaoate->insert([
            'rep_id'        => $prod->rep_id,
            'pro_id'        => $prod->pro_id,
            'rpa_cancelada' => 0,
            'rpa_atendida'  => (int) $prod->rep_quantia,
            'rpa_data'      => date('Y-m-d H:i:s'),
        ]);

        if (! $salva) {
            $db->transRollback();
            return false;
        }

        $totalAtendidos++;
    }
    $db->transCommit();

    // ─── Acerta status da requisição original ────────────────────────────
    if ($totalAtendidos > 0) {
        $pendencias = $requisicaoate->getRequisicaoPendencias($reqoriginal->req_id);
        log_message('info', 'Pendências ' . json_encode($pendencias) ?? '');

        if (isset($pendencias[0]) && $pendencias[0]['pendente_atendimento'] == 0) {
            $status = 18; // totalmente atendida
        } else {
            $status = 21; // parcialmente atendida
        }

        $db->transBegin();
        $salvareq = $requisicao->update($reqoriginal->req_id, ['stt_id' => $status]);

        if (! $salvareq) {
            $db->transRollback();
            return false;
        }
        $db->transCommit();
    }

    return $db->transStatus();
}

function resolveCodigoProduto(object $produto): string|int
{
    return $produto->codigoProduto
        ?? $produto->codPro
        ?? $produto->pro_codigo
        ?? $produto->pro_id;
}

function resolveCodigoLote(object $produto): string
{
    return $produto->codigoLote
        ?? $produto->lotePro
        ?? $produto->lot_codigo
        ?? $produto->lot_id
        ?? 'SEM_LOTE';
}
