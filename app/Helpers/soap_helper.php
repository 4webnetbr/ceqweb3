<?php

use App\Models\CommonModel;
use App\Libraries\SoapSapiens;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
    
    
    function geraMovimentoSOAP($movimentos, $data, $reserva = false)
    {
        $produto                = model(ProdutProdutoModel::class);
        $tipomovimento          = model(EstoquTipoMovimentacaoModel::class);
        $common                 = model(CommonModel::class);

        $soaptrf = new SoapSapiens();
        for ($m = 0; $m < count($movimentos); $m++) {
            $mov = $movimentos[$m];
            $produto = $produto->getProduto($mov['pro_id'], false)[0];
            $codpro = $produto['pro_codpro'];

            $msg =  'Produto ' . $codpro . ' Lote ' . $mov['lot_lote'] . $mov['msg'];
            envia_msg_ws($data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

            $datmov = date('d/m/Y');
            $codlot = $mov['lot_lote'];
            $qtdmov = $mov['qt'];
            $qtdmov = str_replace(['.', ','], '', $qtdmov);
            // BUSCA TIPO MOVIMENTO
            $movim  = $tipomovimento->getTipoMovimentacao($mov['id']);
            $codtns = $movim[0]['tmo_transacao_erp'];
            $depori = $movim[0]['dep_codorigem']; 
            $depdes = $movim[0]['dep_coddestino'];
            if ($reserva) {
                $reserva = strtoupper($reserva);
                if ($reserva === 'A') {
                    $depdes = $movim[0]['dep_reserva'];
                } elseif ($reserva === 'C') {
                    $depori = $movim[0]['dep_reserva'];
                }
            }
            $valida = data_br($mov['lot_validade']);

            log_message('info', 'Movimento '.json_encode($movim));
            log_message('info', 'Depósito Origem '.$depori);
            log_message('info', 'Depósito Destino '.$depdes);

            debug( 'Movimento '.json_encode($movim));
            debug( 'Depósito Origem '.$depori);
            debug( 'Depósito Destino '.$depdes);

            if($depdes == null || $depdes == ''){
                log_message('info', 'Sem depósito de Destino, vou movimentar');
                // $movimenta = $soaptrf->movimProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
            } else {
                log_message('info', 'Com depósito de Destino, vou transferir');
                // $movimenta = $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
            }
            $movimenta['status'] = 'OK';
            if($movimenta['status'] == 'Erro'){
                // se o movimento deu erro, verifica se teve movimentos anteriores e desfaz
                if($m > 0){
                    for ($rv=($m-1); $rv >= 0; $rv--) { 
                        $rev = $movimentos[$rv];
                        $produto = $produto->getProduto($rev['pro_id'], false)[0];
                        $codpro = $produto['pro_codpro'];

                        $msg =  'Produto ' . $codpro . ' Lote ' . $rev['lot_lote'] . $rev['msg'];
                        envia_msg_ws($data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

                        $datmov = date('d/m/Y');
                        $codlot = $rev['lot_lote'];
                        $qtdmov = $rev['qt'];
                        $qtdmov = str_replace(['.', ','], '', $qtdmov);
                        // BUSCA TIPO MOVIMENTO
                        $movim  = $tipomovimento->getTipoMovimentacao($rev['id']);
                        $codtns = $movim[0]['tmo_transacao_erp'];
                        // deposito de destino é a origem, para reverter
                        $depdes = $movim[0]['dep_codorigem'];
                        // depósito de origem é o destino, para reverter
                        $depori = $movim[0]['dep_coddestino'];
                        $valida = data_br($rev['lot_validade']);

                        log_message('info', 'Movimento Reverso '.json_encode($movim));
                        $reverte = $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
                    }
                }
                break;
            }
        }
        return $movimenta;
    }
