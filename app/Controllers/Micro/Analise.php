<?php

namespace App\Controllers\Micro;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;
use App\Libraries\SoapSapiens;
use App\Models\ArquivoMonModel;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Microb\MicrobAnaliseModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutProdutoModel;
use Config\Database;

class Analise extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $analise;
    public $produto;
    public $lote;
    public $tipomovimento;
    public $common;

    /**
     * Construtor da Analise
     * construct
     */
    public function __construct()
    {
        $this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];

        /** @var \App\Models\Microb\MicrobAnaliseModel */
        $this->analise      = model(MicrobAnaliseModel::class);
        
        $this->lote         = model(ProdutLoteModel::class);
        $this->produto      = model(ProdutProdutoModel::class);
        $this->tipomovimento         = model(EstoquTipoMovimentacaoModel::class);
        $this->common       = model(CommonModel::class);

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $gera          = new MyCampo();
        $gera->nome    = 'bt_order';
        $gera->id      = 'bt_order';
        $gera->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-code-branch" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $gera->i_cone  .= '<div class="align-items-start txt-bt-manut ">Gerar Requisição</div>';
        $gera->place    = 'Gerar Requisição';
        $gera->funcChan = 'redireciona(\'AnaRequisicao/add/\')';
        $gera->classep  = 'btn-outline-success bt-manut btn-sm mb-2 float-end ';
        $this->data['botao'] = $gera->crBotao();

        $this->data['colunas'] = montaColunasLista($this->data, 'ana_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $analise = [];
        // if (!$analise = cache('analise')) {

        $campos = montaColunasCampos($this->data, 'ana_id');

        // BUSCA TODAS AS ANÁLISES
        $dados_analise = $this->analise->getAnalise();
        $ana_ids_assoc = array_column($dados_analise, 'ana_id');
        $log = buscaLogTabela('pro_mic_analise', $ana_ids_assoc);
        // debug($log);
        // Armazenar a base URL fora do loop para evitar chamadas repetidas
        $base_url = base_url('/CriaPdf2025/PrintAnaRequisicao/');

        foreach ($dados_analise as &$ana) {
            // Verificar se o log já está disponível para esse ana_id
            $ana['usu_nome'] = $log[$ana['ana_id']]['usua_alterou'] ?? '';

            if ($ana['req_id']) {
                // Concatenar o URL de forma mais eficiente
                $url_ati = $base_url . $ana['req_id'];
                // Gerar a ação do botão
                $ana['acao_person'] = [
                    "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0 float-end' 
            data-mdb-toggle='tooltip' data-mdb-placement='top' 
            title='Imprimir Requisição' onclick='openPDFModal(\"$url_ati\",\"Imprimir Requisição\")'>
            <i class='fas fa-print'></i></button>"
                ];
            }
        }

        // $this->data['exclusao'] = false;
        $this->data['consulta'] = false;
        $analise = ['data' => montaListaColunas($this->data, 'ana_id', $dados_analise, $campos[1])];

        cache()->save('analise', $analise, 300);
        // }
        echo json_encode($analise);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        $ret = [];
        try {
            $this->analise->delete($id);
            $ret['erro'] = false;
            cache()->clean();
            session()->setFlashdata('msg', 'Analise Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir essa Análise Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    // public function lista()
    // {
    //     $msg = 'Buscando Produtos no Depósito Quarentena';
    //     envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

    //     $campos = montaColunasCampos($this->data, 'ana_id');

    //     // BUSCA OS LOTES DE PRODUTOS NO DEPÓSITO QUARENTENA
    //     $busca = new BuscasSapiens();
    //     $saldoest = (array) $busca->buscaEstoqueDeposito('QUA', '');

    //     if (empty($saldoest)) {
    //         echo json_encode(['data' => []]);
    //         return;
    //     }

    //     $codigoProdutoArray = array_column($saldoest, 'codigoProduto');
    //     $codigoLoteArray = array_column($saldoest, 'codigoLote');

    //     $prods = $this->produto->getProdutoCodLista($codigoProdutoArray, 'S');
    //     $lotes = $this->lote->getLoteProdutoIn($codigoLoteArray);
    //     $analises = $this->analise->getAnaliseCod();

    //     // Reestrutura os arrays para facilitar busca rápida por chave
    //     $prods = array_column($prods, null, 'pro_codpro');
    //     $lotes = array_column($lotes, null, 'lot_lote');
    //     $analisesAssoc = [];
    //     foreach ($analises as $analise) {
    //         $chave = $analise['pro_codpro'] . '-' . $analise['lot_id'];
    //         $analisesAssoc[$chave] = $analise;
    //     }

    //     $msg = count($saldoest) . ' Produtos no Depósito Quarentena';
    //     envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

    //     foreach ($saldoest as $saldoobj) {
    //         $saldo = (array)$saldoobj;
    //         $prodproc = $saldo['codigoProduto'];
    //         $loteproc = $saldo['codigoLote'];
    //         $quantidade = str_replace(['.', ','], '', $saldo['quantidadeEstoque']);

    //         if (!isset($prods[$prodproc]) || $prods[$prodproc]['cla_micro'] !== 'S') {
    //             continue;
    //         }

    //         $prod = $prods[$prodproc];
    //         $lote = $lotes[$loteproc] ?? [
    //             'lot_lote' => $saldo['codigoLote'],
    //             'lot_entrada' => '',
    //             'lot_validade' => $saldo['validade'],
    //             'stt_id' => null,
    //         ];

    //         $analiseKey = $prodproc . '-' . $loteproc;
    //         $analis = $analisesAssoc[$analiseKey] ?? null;

    //         if ($lote['stt_id'] == 8 && (is_null($analis) || $analis['stt_id'] == 16)) {
    //             $this->analise->save([
    //                 'pro_id' => $prod['pro_id'],
    //                 'lot_id' => $lote['lot_id'],
    //                 'ana_qtde' => $quantidade,
    //                 'ana_data' => date('Y-m-d'),
    //                 'stt_id' => 10 // ANÁLISE BLOQUEADA
    //             ]);
    //         } elseif ($lote['stt_id'] == 9) {
    //             if (is_null($analis) || in_array($analis['stt_id'], [13, 16])) {
    //                 $this->analise->save([
    //                     'pro_id' => $prod['pro_id'],
    //                     'lot_id' => $lote['lot_id'],
    //                     'ana_qtde' => $quantidade,
    //                     'ana_data' => date('Y-m-d'),
    //                     'stt_id' => 10 // ANÁLISE BLOQUEADA
    //                 ]);

    //                 $this->lote->save([
    //                     'lot_id' => $lote['lot_id'],
    //                     'stt_id' => 8 // LOTE BLOQUEADO
    //                 ]);
    //             } elseif ($analis['stt_id'] == 15) {
    //                 envia_msg_ws($this->data['controler'], "Lote {$saldo['codigoLote']} Análise Aprovada, Movimenta Estoque", 'MsgServer', session()->get('usu_id'), 1);

    //                 $movim = $this->tipomovimento->getTipoMovimentacao(5)[0] ?? null;
    //                 if ($movim) {
    //                     (new SoapSapiens())->transfProdutosSapiens(
    //                         $prod['pro_codpro'],
    //                         $movim['tmo_transacao_erp'],
    //                         $movim['dep_codorigem'],
    //                         date('d/m/Y'),
    //                         $quantidade,
    //                         $lote['lot_lote'],
    //                         $movim['dep_coddestino']
    //                     );
    //                 }
    //             }
    //         }
    //     }

    //     // BUSCA TODAS AS ANÁLISES
    //     $dados_analise = $this->analise->getAnalise();
    //     foreach ($dados_analise as &$ana) {
    //         $log = buscaLog('pro_mic_analise', $ana['ana_id']);
    //         $ana['usu_nome'] = $log['usua_alterou'] ?? '';

    //         if ($ana['req_id']) {
    //             $url_ati = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $ana['req_id']);
    //             $ana['acao_person'] = [
    //                 "<button class='btn btn-outline-danger btn-sm border-0 mx-0 fs-0 float-end' 
    //                 data-mdb-toggle='tooltip' data-mdb-placement='top' 
    //                 title='Imprimir Requisição' onclick='openPDFModal(\"$url_ati\",\"Imprimir Requisição\")'>
    //                 <i class='fas fa-print'></i></button>"
    //             ];
    //         }
    //     }

    //     $this->data['exclusao'] = false;
    //     $analise = ['data' => montaListaColunas($this->data, 'ana_id', $dados_analise, $campos[1])];

    //     cache()->save('analise', $analise, 1200);
    //     echo json_encode($analise);
    // }

    public function listaAnt()
    {
        $msg = 'Buscando Produtos no Depósito Quarentena';
        envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);
        $campos = montaColunasCampos($this->data, 'ana_id');
        // BUSCA OS LOTES DE PRODUTOS NO DEPÓSITO QUARENTENA
        $busca = new BuscasSapiens();
        $saldoest = (array) $busca->buscaEstoqueDeposito('QUA', '');

        $dados_analise = [];
        $ct = 0;
        if (count($saldoest) > 0) {
            // MONTA O ARRAY COM OS CÕDIGOS DOS PRODUTOS PARA O SQL
            $codigoProdutoArray = array_map(function ($item) {
                return $item->codigoProduto;
            }, $saldoest);
            $prods = $this->produto->getProdutoCodLista($codigoProdutoArray, 'S');

            $codigoLoteArray = array_map(function ($item) {
                return $item->codigoLote;
            }, $saldoest);
            $lotes = $this->lote->getLoteProdutoIn($codigoLoteArray);

            $analises = $this->analise->getAnaliseCod();

            $msg = count($saldoest) . ' Produtos no Depósito Quarentena';
            envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);
            for ($s = 0; $s < count($saldoest); $s++) {
                $saldo = (array) $saldoest[$s];
                // debug($saldo);x
                $prodproc = $saldo['codigoProduto'];
                // Buscar o CODIGO DO PRODUTO NO ARRAY DE PRODUTOS item no array
                $resultado = array_filter($prods, function ($item) use ($prodproc) {
                    return $item['pro_codpro'] === $prodproc;
                });
                // Converter o resultado para o primeiro item encontrado
                $prod = reset($resultado);

                $msg = 'Processando Produto ' . $saldo['codigoProduto'] . ' Lote ' . $saldo['codigoLote'] . ' no Depõsito Quarentena';
                envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);
                // Verifica se o produto necessita de anãlise de Micro
                if (isset($prod[0]['cla_micro']) && $prod[0]['cla_micro'] == 'S') {
                    // $lote = $this->lote->getLoteSearch($saldo['codigoLote']);
                    $loteproc = $saldo['codigoLote'];
                    // Buscar o CODIGO DO PRODUTO NO ARRAY DE PRODUTOS item no array
                    $resultado = array_filter($lotes, function ($item) use ($loteproc) {
                        return $item['lot_lote'] === $loteproc;
                    });
                    $lote = reset($resultado);
                    if (count($lote) == 0) {
                        $lote[0]['lot_lote']     = $saldo['codigoLote'];
                        $lote[0]['lot_entrada']  = $saldo['entrada'];
                        $lote[0]['lot_validade'] = $saldo['validade'];
                    }
                    $resultado = array_filter($analises, function ($item) use ($prodproc, $loteproc) {
                        if ($item['pro_codpro'] === $prodproc && $item['lot_id'] === $loteproc) {
                            return $item;
                        }
                    });
                    $analis = reset($resultado);
                    if ($lote[0]['stt_id'] == 8) {
                        // SE O LOTE ESTIVER BLOQUEADO (ID=8)
                        // VERIFICA SE O PRODUTO E O LOTE JÃ TEM ANÃLISE
                        if (count($analis) == 0 || $analis[0]['stt_id'] == 16) {
                            // SE NÃO TEM, OU TEM COM STATUS REPROVADA (ID 16), INCLUI A ANÁLISE
                            $sql_ana = [
                                'pro_id' => $prod[0]['pro_id'],
                                'lot_id' => $lote[0]['lot_id'],
                                'ana_qtde' => $saldo['quantidadeEstoque'],
                                'ana_data' => date('Y-m-d'),
                                'stt_id'   => 10 // ANÁLISE BLOQUEADA
                            ];
                            // debug($sql_ana);
                            $this->analise->save($sql_ana);
                        }
                    } else if ($lote[0]['stt_id'] == 9) {
                        // SE O LOTE ESTIVER LIBERADO (ID=9)
                        // VERIFICA SE O PRODUTO E O LOTE JÃ TEM ANÃLISE
                        if (count($analis) == 0 || $analis[0]['stt_id'] == 13 || $analis[0]['stt_id'] == 16) {
                            // SE NÃO TEM, OU TEM COM STATUS NAO REALIZADA (ID 13), 
                            // OU TEM COM STATUS REPROVADA (ID 16) INCLUI A ANÁLISE
                            $sql_ana = [
                                'pro_id' => $prod[0]['pro_id'],
                                'lot_id' => $lote[0]['lot_id'],
                                'ana_qtde' => $saldo['quantidadeEstoque'],
                                'ana_data' => date('Y-m-d'),
                                'stt_id'   => 10 // ANÁLISE BLOQUEADA
                            ];
                            $this->analise->save($sql_ana);

                            // ATUALIZA O LOTE PARA BLOQUEADO
                            $sql_lot = [
                                'lot_id' => $lote[0]['lot_id'],
                                'stt_id'   => 8 // LOTE BLOQUEADA
                            ];
                            $this->lote->save($sql_lot);
                        } else if ($analis[0]['stt_id'] == 15) {
                            $msg = 'Lote ' . $saldo['codigoLote'] . ' Análise Aprovada, Movimenta Estoque';
                            envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);
                            // CASO A ANALISE ESTEJA APROVADA, GERA MOVIMENTAÇÃO DE ESTOQUE
                            $codpro = $prod[0]['pro_codpro'];
                            $datmov = date('d/m/Y');
                            $codlot = $lote[0]['lot_lote'];
                            $qtdmov = $saldo['quantidadeEstoque'];
                            $qtdmov = str_replace(['.', ','], '', $qtdmov);
                            // BUSCA TIPO MOVIMENTO
                            $movim  = $this->tipomovimento->getTipoMovimentacao(5);
                            $codtns = $movim[0]['tmo_transacao_erp'];
                            $depori = $movim[0]['dep_codorigem'];
                            $depdes = $movim[0]['dep_coddestino'];

                            // debug($movim);
                            $soaptrf = new SoapSapiens();
                            $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes);
                        }
                    }
                }
            }
        }
        // BUSCA TODAS AS ANÁLISES
        $dados_analise = $this->analise->getAnalise();
        for ($da = 0; $da < count($dados_analise); $da++) {
            // $dados_analise[$da]['d'] = '';
            $ana = $dados_analise[$da];
            $log = buscaLog('pro_mic_analise', $ana['ana_id']);
            $dados_analise[$da]['usu_nome'] = isset($log['usua_alterou']) ? $log['usua_alterou'] : '';
            $this->data['botao'] = [];
            if ($ana['req_id'] != null) {
                // debug('Req ' . $ana['req_id']);
                $url_ati = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $ana['req_id']);
                $imprimir =
                    "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0 float-end' data-mdb-toggle='tooltip' 
                    data-mdb-placement='top' title='Imprimir Requisição' onclick='openPDFModal(\"" .
                    $url_ati .
                    "\",\"Imprimir Requisição\")'><i class='fas fa-print'></i></button>";

                $dados_analise[$da]['acao_person'] = [$imprimir];
            }
            // debug($this->data['botao']);
            // }
            // debug($dados_analise, true);
            $this->data['exclusao'] = false;
            $analise = [
                'data' => montaListaColunas($this->data, 'ana_id', $dados_analise, $campos[1]),
            ];
            cache()->save('analise', $analise, 1200);
        }

        echo json_encode($analise);
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        return $this->edit($id, true);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        $dados = $this->analise->getListaAnalise($id)[0] ?? null;

        if (!$dados) {
            throw new \RuntimeException('Análise não encontrada.');
        }

        $status = (int) $dados['stt_id'];
        $secao = ['Dados Gerais'];
        $campos = [[]];
        $this->data['botao'] = '';

        // Campos sempre presentes na primeira seção
        $fields = $this->analise->defCampos($dados, $show);
        $campos[0][] = $fields['ana_id'];
        $campos[0][] = $fields['stt_id'];
        $campos[0][] = $fields['lot_entrada'];
        $campos[0][] = $fields['pro_id'];
        $campos[0][] = $fields['fab_apeFab'];
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['lot_id'];
        $campos[0][] = $fields['lot_validade'];
        $campos[0][] = $fields['ana_qtde'];
        $campos[0][] = $fields['ana_qtde_micro'];

        // Se status for diferente de BLOQUEADA, montar seção de Análise
        if ($status !== 10) {
            $secao[1] = 'Dados da Análise';
            $fields2 = $this->analise->defCamposAnalise($dados, $show);
            $campos[1][] = $fields2['cla_metodanalise'];
            $campos[1][] = $fields2['ana_liberarsemmicro'];
            $campos[1][] = $fields2['ana_descmetodo'];
            $campos[1][] = $fields2['ana_lotemb'];
            $campos[1][] = $fields2['ana_datalotemb'];

            if ($status === 12 || $status === 15 || $status === 16) { // EM ANDAMENTO ou APROVADA ou REPROVADA
                $campos[1][] = $fields2['ana_laudo'];
                $campos[1][] = $fields2['ana_arqlaudo'];
                if ($status === 12 || $status === 16){ // EM ANDAMENTO
                    $this->data['botao'] = $fields['bt_finalizar'];
                }
            }

            // Ações adicionais
            $secao[2] = 'Ações';
            $fields3 = $this->analise->defCamposAcoes($dados, $show);
            $campos[2][] = $fields3['ana_liberar'];
            $campos[2][] = $fields3['ana_reprovar'];

            $campos[2][] = $fields3['tmo_id'];
            $campos[2][] = $fields3['tmo_id_rep'];
        }

        // Script JavaScript dinâmico
        $script = <<<SCRIPT
            <script>
                mostraOcultaCampo('cla_metodanalise', 'N', 'ana_descmetodo');
                mostraOcultaCampo('cla_metodanalise', 'S', 'ana_lotemb');
            </script>
        SCRIPT;

        if($dados['ana_liberar'] == 'S'){
            $script .= <<<SCRIPT
                <script>
                    mostraOcultaCampo('ana_liberar','S','tmo_id');
                    mostraOcultaCampo('ana_liberar','N','tmo_id_rep');
                    mudaObrigatorio('ana_liberar','S','tmo_id')
                </script>
            SCRIPT;
        }
        if($dados['ana_reprovar'] == 'S'){
            $script .= <<<SCRIPT
                <script>
                    mostraOcultaCampo('ana_reprovar','S','tmo_id_rep');
                    mostraOcultaCampo('ana_reprovar','N','tmo_id');
                    mudaObrigatorio('ana_liberar','S','tmo_id');
                </script>
            SCRIPT;
        }

        // Prepara dados da view
        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = 'store';
        $this->data['script']      = $script;
        $this->data['desc_edicao'] = $dados['pro_despro'];
        $this->data['log']         = buscaLog('pro_analise', $id);

        return view('vw_edicao', $this->data);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function finalizar()
    {
        $ret = ['erro' => false];
        $postado = $this->request->getPost();
        $erros = [];

        $ana_id = intval($postado['ana_id'] ?? 0);
        $ana_laudo = trim($postado['ana_laudo'] ?? '');

        // Verifica se o laudo foi preenchido
        if (empty($ana_laudo)) {
            echo json_encode([
                'erro' => true,
                'msg'  => 'É obrigatório informar o Laudo'
            ]);
            return;
        }

        $sql_ana = [];
        // Prepara os dados para salvar se aprovado
        if (($postado['ana_liberar'] ?? '') === 'S') {
            $sql_ana = [
                'ana_id'    => $ana_id,
                'ana_laudo' => $ana_laudo,
                'ana_liberar' => $postado['ana_liberar'],
                'ana_reprovar' => $postado['ana_reprovar'],
                'stt_id'    => 15, // Aprovado
            ];

            if (!empty($postado['tmo_id'])) {
                $sql_ana['tmo_id'] = intval($postado['tmo_id']);
            }
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $files = $this->request->getFiles();
            $arquivoUpload = $files['ana_arqlaudo'] ?? null;

            if (!$arquivoUpload || $arquivoUpload->getSize() <= 0) {
                throw new \Exception('É necessário anexar o Arquivo do Laudo.');
            }

            // Preparar arquivo
            $nomeArquivo = $arquivoUpload->getName();
            $caminhoTemporario = $arquivoUpload->getPathName();

            $arqBase64 = base64_encode(file_get_contents($caminhoTemporario));
            $mime = mime_content_type($caminhoTemporario);

            $arqs = [
                'arq_nome' => $nomeArquivo,
                'arq_exte' => $arquivoUpload->getExtension(),
                'arq_tipo' => $mime,
                'arq_size' => $arquivoUpload->getSize(),
            ];

            // Salva o arquivo
            $arqdb = new ArquivoMonModel();
            $base64Completo = "data:{$mime};base64,{$arqBase64}";

            $arquivoSalvo = $arqdb->insertArquivo('Analisa', 'ArqLaudo', $ana_id, $arqs, $base64Completo);

            if (!$arquivoSalvo) {
                throw new \Exception("Não foi possível gravar o Arquivo {$nomeArquivo}, Verifique!");
            }

            // Salva dados da análise
            if(count($sql_ana)){
                if (!$this->analise->save($sql_ana)) {
                    $erros = $this->common->errors();
                    throw new \Exception("Erro ao salvar dados da análise.");
                }
            }

            $db->transCommit();

            cache()->clean();
            $ret['msg'] = 'Análise finalizada com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } catch (\Throwable $e) {
            $db->transRollback();
            $ret['erro'] = true;

            if (empty($erros)) {
                $ret['msg'] = $e->getMessage();
            } else {
                $ret['msg'] = $e->getMessage() . '<br><br>';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }

        echo json_encode($ret);
    }

    public function store()
    {
        $ret = ['erro' => false];
        $post = $this->request->getPost();
        $movs = [];
        $sqlAna = [];
        $sqlLot = null;

        try {
            // Define a ação conforme o status inicial recebido
            switch ($post['stt_id']) {
                case 10: // ESTAVA BLOQUEADO
                    $movs[] = [
                        'id'  => 4,
                        'qt'  => intval($post['ana_qtde_micro']),
                        'msg' => 'enviado para Análise'
                    ];
                    $sqlAna = [
                        'ana_id'           => intval($post['ana_id']),
                        'ana_qtde_micro'   => intval($post['ana_qtde_micro']),
                        'stt_id'           => 11, // Status PENDENTE
                    ];
                    break;
                case 11: // ESTAVA PENDENTE
                    if ($post['ana_liberarsemmicro'] == 'N') {
                        $status = 14; // REALIZADA
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_lotemb'   => $post['ana_lotemb'],
                            'ana_liberarsemmicro'   => $post['ana_liberarsemmicro'],
                            'ana_datalotemb' => $post['ana_datalotemb'],
                            'ana_descmetodo' => $post['ana_descmetodo'],
                            'stt_id'       => $status,
                        ];
                    } else {
                        $status = 13; // NÃO REALIZADA
                        $sqlAna = [
                            'ana_id' => intval($post['ana_id']),
                            'ana_liberarsemmicro'   => $post['ana_liberarsemmicro'],
                            'stt_id' => $status,
                        ];
                        // gera movimentação do tipo MOV5 (5) da quantidade total do lote
                        $movs[] = [
                            'id'  => 5,
                            'qt'  => intval($post['ana_qtde']) - intval($post['ana_qtde_micro']),
                            'msg' => 'liberado sem Micro'
                        ];
                        // gera movimentação MOV6 da quantidade micro
                        $movs[] = [
                            'id'  => 6,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'liberado sem Micro'
                        ];
                        // Atualização do lote para liberado
                        $sqlLot = [
                            'lot_id' => $post['lot_id'],
                            'stt_id' => 9, // LOTE LIBERADO
                        ];
                    }
                    break;
                case 14: // ESTAVA REALIZADA
                    if ($post['ana_reprovar'] == 'S') {
                        $status = 16; // REPROVADA
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_lotemb'   => $post['ana_lotemb'],
                            'ana_liberar'  => $post['ana_liberar'],
                            'ana_reprovar'  => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'       => intval($post['tmo_id_rep']),
                            'stt_id'       => $status,
                        ];
                        // BUSCAR DEPÓSITO DE ORIGEM 
                        $mov = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
                        $deporig = $mov[0]['dep_codorigem'];
                        // BUSCAR PRODUTO 
                        $produto = $this->produto->getProduto($post['pro_id'], false)[0];
                        $codpro = $produto['pro_codpro'];

                        // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
                        $qtia = 0;
                        $busca = new BuscasSapiens();
                        $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
                        if(count($saldoest)> 0){
                            for($s=0;$s<count($saldoest);$s++){
                                if($saldoest[$s]->codigoLote == $post['lot_lote'] ){
                                    $qtia = $saldoest[$s]->quantidadeEstoque;
                                    break;
                                }
                            }
                        }
                        $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
                            'id'  => intval($post['tmo_id_rep']),
                            'qt'  => $qtia,
                            'msg' => 'Análise reprovada'
                        ];
                       $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
                            'id'  => 7,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise reprovada'
                        ];
                        // Atualização do lote para bloqueado
                        $sqlLot = [
                            'lot_id' => $post['lot_id'],
                            'stt_id' => 8, // LOTE BLOQUEADO
                        ];
                    } else if ($post['ana_liberar'] == 'S') {
                        $status = 12; // EM ANDAMENTO
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_lotemb'   => $post['ana_lotemb'],
                            'ana_liberar'  => $post['ana_liberar'],
                            'ana_reprovar'  => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'       => intval($post['tmo_id_rep']),
                            'stt_id'       => $status,
                        ];
                        // gera movimentação do tipo MOV5 (5) da quantidade total do lote
                        $movs[] = [
                            'id'  => 5,
                            'qt'  => intval($post['ana_qtde']) -intval($post['ana_qtde_micro']),
                            'msg' => 'Análise liberada'
                        ];
                        // gera movimentação cadastrada da quantidade micro
                        $movs[] = [
                            'id'  => intval($post['tmo_id']),
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise liberada'
                        ];
                        // Atualização do lote para liberado
                        $sqlLot = [
                            'lot_id' => $post['lot_id'],
                            'stt_id' => 9, // LOTE LIBERADO
                        ];
                    } else {
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_descmetodo' => $post['ana_descmetodo'],
                        ];
                    }
                    break;

                case 12: // ESTAVA EM ANDAMENTO
                    // $status = 12; // Continua EM ANDAMENTO
                    $sqlAna = [
                        'ana_id' => intval($post['ana_id']),
                        'tmo_id'       => intval($post['tmo_id']),
                        'tmo_id_rep'       => intval($post['tmo_id_rep']),
                        'ana_lotemb'   => $post['ana_lotemb'],
                        // 'stt_id' => $status,
                    ];
                    if ($post['ana_reprovar'] == 'S') {
                        $status = 16; // Reprovado
                        $sqlAna = [
                            'ana_id' => intval($post['ana_id']),
                            'ana_liberar'  => $post['ana_liberar'],
                            'ana_reprovar'  => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'       => intval($post['tmo_id_rep']),
                            'stt_id' => $status,
                        ];
                        // BUSCAR DEPÓSITO DE ORIGEM 
                        $mov = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
                        $deporig = $mov[0]['dep_codorigem'];
                        // BUSCAR PRODUTO 
                        $produto = $this->produto->getProduto($post['pro_id'], false)[0];
                        $codpro = $produto['pro_codpro'];

                        // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
                        $qtia = 0;
                        $busca = new BuscasSapiens();
                        $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
                        if(count($saldoest)> 0){
                            for($s=0;$s<count($saldoest);$s++){
                                if($saldoest[$s]->codigoLote == $post['lot_lote'] ){
                                    $qtia = $saldoest[$s]->quantidadeEstoque;
                                    break;
                                }
                            }
                        }
                        $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
                            'id'  => intval($post['tmo_id_rep']),
                            'qt'  => $qtia,
                            'msg' => 'Análise reprovada'
                        ];
                        $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
                            'id'  => 7,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise reprovada'
                        ];
                        $sqlLot = [
                            'lot_id' => $post['lot_id'],
                            'stt_id' => 8, // LOTE BLOQUEADO
                        ];
                    }
                    break;

                default:
                    throw new \Exception("Status inválido recebido.");
            }

            // Inicia a transação
            $this->analise->transBegin();

            // Gera movimentos se existirem
            // if (!empty($movs)) {
            //     cache()->clean();
            //     $movim = $this->geraMovimento($movs, $post);
            //     // debug($movim, true);
            //     if($movim->tipoRetorno > 1){
            //         $ret['erro'] = true;
            //         $ret['msg'] = $movim->mensagemRetorno;
            //         // Rollback em ambas transações
            //         $this->analise->transRollback();
            //     }
            // }
            
            if(!$ret['erro']){
                // Salva dados da análise
                if (!$this->analise->save($sqlAna)) {
                    throw new \Exception(implode(' ', $this->analise->errors()));
                }


                // Trata upload e processamento de arquivo quando estiver EM ANDAMENTO e não for reprovação
                if ($post['stt_id'] == 12 && $post['ana_reprovar'] != 'S') {
                    $files = $this->request->getFiles();
                    if (isset($files['ana_arqlaudo']) && $files['ana_arqlaudo']->getSize() > 0) {
                        $uploadRet = $this->processaArquivoLaudo($files['ana_arqlaudo'], $post['ana_id']);
                        if ($uploadRet !== true) {
                            throw new \Exception($uploadRet);
                        }
                    }
                }

                // Atualiza o lote, se necessário
                if ($sqlLot) {
                    // Inicia transação para lote
                    $this->lote->transBegin();
                    if (!$this->lote->save($sqlLot)) {
                        throw new \Exception(implode(' ', $this->lote->errors()));
                    }
                    $this->lote->transCommit();
                }

                // Commit final
                $this->analise->transCommit();
                cache()->clean();
                $ret['msg'] = 'Dados da Analise gravados com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);

                if($post['stt_id'] == 10){ // ESTAVA BLOQUEADO
                    $dados = $this->analise->getListaAnalise($post['ana_id'])[0] ?? null;

                    $numetiquetas = (int) $dados['ana_qtde_micro'];
                    $dados = array_fill(0, $numetiquetas, $dados);
                    $chave = uniqid('etq_');
                    cache()->save($chave, $dados, 120); // 1 minuto

                    $link = base_url('/CriaEtiquetaZPL/emiteEtiqueta/');
                    session()->setFlashdata('modal', $link);
                    session()->setFlashdata('chave', $chave);
                    session()->setFlashdata('modal-title', 'Imprimir Etiqueta');
                }

                $ret['url'] = site_url($this->data['controler']);
            }
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg'] = $e->getMessage();
            // Rollback em ambas transações
            $this->analise->transRollback();
            if ($this->lote) {
                $this->lote->transRollback();
            }
        }

        echo json_encode($ret);
    }

    /**
     * Processa o upload do arquivo de laudo.
     * Retorna true em caso de sucesso ou mensagem de erro.
     */
    private function processaArquivoLaudo($file, $anaId)
    {
        if ($file->getSize() > 0) {
            $arquivo = $file->getPathName();
            $tamanho = $file->getSize();
            $exte    = $file->getExtension();
            $tipo    = mime_content_type($arquivo);
            $nome    = $file->getName();

            $conteudo = file_get_contents($arquivo);
            $base64   = 'data:' . $tipo . ';base64,' . base64_encode($conteudo);

            $arqs = [
                'arq_nome' => $nome,
                'arq_exte' => $exte,
                'arq_tipo' => $tipo,
                'arq_size' => $tamanho,
            ];

            $arqdb = new ArquivoMonModel();
            $resultado = $arqdb->insertArquivo('Analisa', 'ArqLaudo', intval($anaId), $arqs, $base64);
            if (!$resultado) {
                return 'Não foi possível gravar o Arquivo ' . $nome . ', verifique!';
            }
        }
        return true;
    }

    public function geraMovimento($movimentos, $postado)
    {
        for ($m = 0; $m < count($movimentos); $m++) {
            $mov = $movimentos[$m];
            $produto = $this->produto->getProduto($postado['pro_id'], false)[0];
            $codpro = $produto['pro_codpro'];

            $msg =  'Produto ' . $codpro . ' Lote ' . $postado['lot_lote'] . $mov['msg'];
            envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

            $datmov = date('d/m/Y');
            $codlot = $postado['lot_lote'];
            $qtdmov = $mov['qt'];
            $qtdmov = str_replace(['.', ','], '', $qtdmov);
            // BUSCA TIPO MOVIMENTO
            $movim  = $this->tipomovimento->getTipoMovimentacao($mov['id']);
            $codtns = $movim[0]['tmo_transacao_erp'];
            $depori = $movim[0]['dep_codorigem'];
            $depdes = $movim[0]['dep_coddestino'];
            $valida = data_br($postado['lot_validade']);

            log_message('info', 'Movimento '.json_encode($movim));


            // DESCOMENTAR AQUI QDO FOR PRA  MOVIMENTAR EFETIVAMENTE
            // $soaptrf = new SoapSapiens();
            // $movimenta = $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
            // // debug($movimenta, true);
            // return $movimenta;
        }
    }

}
