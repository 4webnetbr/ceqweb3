<?php namespace App\Controllers\Micro;

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

class Requisicao extends BaseController {
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
        $this->analise      = new MicrobAnaliseModel();
        $this->produto      = new ProdutProdutoModel();
        $this->lote         = new ProdutLoteModel();
        $this->tipomovimento         = new EstoquTipoMovimentacaoModel();
        $this->common       = new CommonModel();
        
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
        $order          = new MyCampo();
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-code-pull-request" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->i_cone  .= '<div class="align-items-start txt-bt-manut ">Gerar Requisição</div>';
        $order->place    = 'Gerar Requisição';
        $order->funcChan = 'redireciona(\'Analise/requisicao/\')';
        $order->classep  = 'btn-outline-success bt-manut btn-sm mb-2 float-end ';
        $this->bt_order = $order->crBotao();
        $this->data['botao'] = $this->bt_order;

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
        $msg = 'Buscando Produtos no Depõsito Quarentena';
        envia_msg_ws($this->data['controler'],$msg,'MsgServer',session()->get('usu_id'),1);
        if (!$analise = cache('analise')) {
            $campos = montaColunasCampos($this->data, 'ana_id');
            // BUSCA OS LOTES DE PRODUTOS NO DEPÓSITO QUARENTENA
            $busca = new BuscasSapiens();
            $saldoest = (array) $busca->buscaEstoqueDeposito('QUA','');
            // debug($saldoest, false);
            // debug($codigoProdutoArray, true);
            $dados_analise = [];
            $ct =0;
            if(count($saldoest) > 0){
                // MONTA O ARRAY COM OS CÕDIGOS DOS PRODUTOS PARA O SQL
                $codigoProdutoArray = array_map(function($item) {
                    return $item->codigoProduto;
                }, $saldoest);
                $prods = $this->produto->getProdutoCodLista($codigoProdutoArray, 'S');
                // debug($prods,true);
                $msg = count($saldoest).' Produtos no Depõsito Quarentena';
                envia_msg_ws($this->data['controler'],$msg,'MsgServer',session()->get('usu_id'),1);
                for($s=0;$s<count($saldoest);$s++){
                    $saldo = (array) $saldoest[$s];
                    // debug($saldo);x
                    $prodproc = $saldo['codigoProduto'];
                    // Buscar o CODIGO DO PRODUTO NO ARRAY DE PRODUTOS item no array
                    $resultado = array_filter($prods, function($item) use ($prodproc) {
                        return $item['pro_codpro'] === $prodproc;
                    });
                    // Converter o resultado para o primeiro item encontrado
                    $prod = reset($resultado);

                    // $prod = $this->produto->getProdutoCod($saldo['codigoProduto'], '1');
                    // $prod = $prods[$resultado];
                    // debug($prod, true);
                    $msg = 'Processando Produto '.$saldo['codigoProduto'].' Lote '.$saldo['codigoLote'].' no Depõsito Quarentena';
                    envia_msg_ws($this->data['controler'],$msg,'MsgServer',session()->get('usu_id'),1);
                    // Verifica se o produto necessita de anãlise de Micro
                    if(isset($prod[0]['cla_micro']) && $prod[0]['cla_micro'] == 'S'){
                            // debug($prod, true);
                        $lote = $this->lote->getLoteSearch($saldo['codigoLote']);
                        if(count($lote) == 0){
                            $lote[0]['lot_lote'] = $saldo['codigoLote'];
                            $lote[0]['lot_entrada'] = '';
                            $lote[0]['lot_validade'] = $saldo['validade'];
                        }
                        if($lote[0]['stt_id'] == 8){
                            // SE O LOTE ESTIVER BLOQUEADO (ID=8)
                            // debug($lote);
                            // VERIFICA SE O PRODUTO E O LOTE JÃ TEM ANÃLISE
                            $analis = $this->analise->getAnaliseCod($prod[0]['pro_codpro'], $lote[0]['lot_id']);
                            // debug($analis);
                            if(count($analis) == 0 || $analis[0]['stt_id'] == 16){
                                // SE NÃO TEM, OU TEM COM STATUS REPROVADA (ID 16), INCLUI A ANÁLISE
                                // debug('Incluir analise');
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
                        } else if($lote[0]['stt_id'] == 9){
                            // SE O LOTE ESTIVER LIBERADO (ID=9)
                            // debug($lote);
                            // VERIFICA SE O PRODUTO E O LOTE JÃ TEM ANÃLISE
                            $analis = $this->analise->getAnaliseCod($prod[0]['pro_codpro'], $lote[0]['lot_id']);
                            // debug($analis);
                            if(count($analis) == 0 || $analis[0]['stt_id'] == 13 || $analis[0]['stt_id'] == 16){
                                    // SE NÃO TEM, OU TEM COM STATUS NAO REALIZADA (ID 13), 
                                // OU TEM COM STATUS REPROVADA (ID 16) INCLUI A ANÁLISE
                                // debug('Incluir analise');                                
                                $sql_ana = [
                                    'pro_id' => $prod[0]['pro_id'],
                                    'lot_id' => $lote[0]['lot_id'],
                                    'ana_qtde' => $saldo['quantidadeEstoque'],
                                    'ana_data' => date('Y-m-d'),
                                    'stt_id'   => 10 // ANÁLISE BLOQUEADA
                                ];
                                // debug($sql_ana);
                                $this->analise->save($sql_ana);

                                // ATUALIZA O LOTE PARA BLOQUEADO
                                $sql_lot = [
                                    'lot_id' => $lote[0]['lot_id'],
                                    'stt_id'   => 8 // LOTE BLOQUEADA
                                ];
                                $this->lote->save($sql_lot);
                            } else if($analis[0]['stt_id'] == 15){
                                $msg = 'Lote '.$saldo['codigoLote'].' Análise Aprovada, Movimenta Estoque';
                                envia_msg_ws($this->data['controler'],$msg,'MsgServer',session()->get('usu_id'),1);
                                // CASO A ANALISE ESTEJA APROVADA, GERA MOVIMENTAÇÃO DE ESTOQUE
                                $codpro = $prod[0]['pro_codpro'];
                                // debug($codpro);
                                $datmov = date('d/m/Y');
                                $codlot = $lote[0]['lot_lote'];
                                $qtdmov = $saldo['quantidadeEstoque'];
                                // debug($qtdmov);
                                $qtdmov = str_replace(['.', ','], '', $qtdmov);
                                // debug($qtdmov);
                                // BUSCA TIPO MOVIMENTO
                                $movim  = $this->tipomovimento->getTipoMovimentacao(5);
                                // debug($movim);
                                $codtns = $movim[0]['tmo_transacao_erp'];
                                $depori = $movim[0]['dep_codorigem'];
                                $depdes = $movim[0]['dep_coddestino'];

                                $soaptrf = new SoapSapiens();
                                $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes);
                            }
                        }
                    }
                }
            }
            // BUSCA TODAS AS ANÁLISES
            $dados_analise = $this->analise->getAnalise();
            for ($da=0; $da < count($dados_analise) ; $da++) { 
                // $dados_analise[$da]['d'] = '';
                $ent = $dados_analise[$da];
                $log = buscaLog('pro_mic_analise', $ent['ana_id']);
                // debug($log);
                $dados_analise[$da]['usu_nome'] = isset($log['usua_alterou'])?$log['usua_alterou']:'';
            }
            // debug($dados_analise, true);
            $this->data['exclusao'] = false;
            $analise = [
                'data' => montaListaColunas($this->data, 'ana_id', $dados_analise, $campos[1]),
            ];
            cache()->save('analise', $analise, 600);
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
    public function show($id){
        $this->edit($id, true);
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
        $dados_analise = $this->analise->getListaAnalise($id)[0];
        // debug($dados_analise['stt_id'], true);

        $secao[0] = 'Dados Gerais'; 
        $this->data['botao']      = '';
        if($dados_analise['stt_id'] == 10){ // SE O STATUS É BLOQUEADA
            $fields = $this->analise->defCampos($dados_analise, false);
            $campos[0][0] = $fields['ana_id'];
            $campos[0][count($campos[0])] = $fields['stt_id'];
            $campos[0][count($campos[0])] = $fields['lot_entrada'];
            $campos[0][count($campos[0])] = $fields['pro_id'];
            $campos[0][count($campos[0])] = $fields['fab_apeFab'];  
            $campos[0][count($campos[0])] = $fields['lot_lote'];
            $campos[0][count($campos[0])] = $fields['lot_validade'];
            $campos[0][count($campos[0])] = $fields['ana_qtde'];
            $campos[0][count($campos[0])] = $fields['ana_qtde_micro'];
        } else if($dados_analise['stt_id'] == 11){ // SE O STATUS É PENDENTE
            $fields = $this->analise->defCampos($dados_analise, true);
            $campos[0][0]                 = $fields['ana_id'];
            $campos[0][count($campos[0])] = $fields['stt_id'];
            $campos[0][count($campos[0])] = $fields['lot_entrada'];
            $campos[0][count($campos[0])] = $fields['pro_id'];
            $campos[0][count($campos[0])] = $fields['fab_apeFab'];  
            $campos[0][count($campos[0])] = $fields['lot_lote'];
            $campos[0][count($campos[0])] = $fields['lot_validade'];
            $campos[0][count($campos[0])] = $fields['ana_qtde'];
            $campos[0][count($campos[0])] = $fields['ana_qtde_micro'];
            $secao[1] = 'Dados da Análise'; 
            $fields2 = $this->analise->defCamposAnalise($dados_analise, true);
            $campos[1][0]                 = $fields2['cla_metodanalise'];
            $campos[1][count($campos[1])] = $fields2['ana_liberarsemmicro'];
            $campos[1][count($campos[1])] = $fields2['ana_descmetodo'];
            $campos[1][count($campos[1])] = $fields2['ana_lotemb'];
            $campos[1][count($campos[1])] = $fields2['ana_datalotemb'];
        } else {
            $fields = $this->analise->defCampos($dados_analise, true);
            $campos[0][0]                 = $fields['ana_id'];
            $campos[0][count($campos[0])] = $fields['stt_id'];
            $campos[0][count($campos[0])] = $fields['lot_entrada'];
            $campos[0][count($campos[0])] = $fields['pro_id'];
            $campos[0][count($campos[0])] = $fields['fab_apeFab'];  
            $campos[0][count($campos[0])] = $fields['lot_lote'];
            $campos[0][count($campos[0])] = $fields['lot_validade'];
            $campos[0][count($campos[0])] = $fields['ana_qtde'];
            $campos[0][count($campos[0])] = $fields['ana_qtde_micro'];
            $secao[1] = 'Dados da Análise'; 
            $fields2 = $this->analise->defCamposAnalise($dados_analise, true);
            $campos[1][0]                 = $fields2['cla_metodanalise'];
            $campos[1][count($campos[1])] = $fields2['ana_liberarsemmicro'];
            $campos[1][count($campos[1])] = $fields2['ana_descmetodo'];
            $campos[1][count($campos[1])] = $fields2['ana_lotemb'];
            $campos[1][count($campos[1])] = $fields2['ana_datalotemb'];
            $campos[1][count($campos[1])] = $fields2['ana_laudo'];
            $campos[1][count($campos[1])] = $fields2['ana_arqlaudo'];
            $secao[2] = 'Ações'; 
            $fields3 = $this->analise->defCamposAcoes($dados_analise, true);
            $campos[2][0]                 = $fields3['ana_liberar'];
            $campos[2][count($campos[2])] = $fields3['ana_reprovar'];
            $campos[2][count($campos[2])] = $fields3['tmo_id'];
            $this->data['botao']          = $fields['bt_finalizar'];
        }
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';


        $this->data['script'] = "<script>
                                    mostraOcultaCampo('cla_metodanalise','N','ana_descmetodo');
                                    mostraOcultaCampo('cla_metodanalise','S','ana_lotemb');
                                    </script>";
                                    
        $this->data['desc_edicao'] = $dados_analise['pro_despro'];
                                    
                                    // // BUSCAR DADOS DO LOG
                                    // mostraOcultaCampo('cla_liberarsemmicro','N','bt_finalizar');
        $this->data['log'] = buscaLog('pro_analise', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
    * Gerar Requisicao
    * gerar
    *
    * @param mixed $id 
    * @return void
    */
    public function gerar(){
        $fields[0] = 'Id';
        $fields[1] = 'Produto';
        $fields[2] = 'Fabricante';
        $fields[3] = 'Lote';
        $fields[4] = 'Lote MB';
        $fields[5] = 'Data';
        $fields[6] = 'Status';
        $fields[7] = 'Usuário';
        $fields[8] = 'Ação';
        $this->data['colunas'] = $fields;
        $this->data['desc_metodo'] = 'Requisição de ';
        $this->data['url_lista'] = base_url($this->data['controler'] . '/listarequisicao');
        echo view('vw_lista', $this->data);
    }
    
    /**
    * Lista das Requisições
    * listarequisicao
    *
    * @param mixed $id 
    * @return void
    */
    public function listarequisicao(){
        $fields[0] = 'ana_id';
        $fields[1] = 'pro_despro';
        $fields[2] = 'fab_apeFab';
        $fields[3] = 'lot_lote';
        $fields[4] = 'ana_lotemb';
        $fields[5] = 'ana_data';
        $fields[6] = 'stt_nome';
        $fields[7] = 'usu_nome';
        $fields[8] = 'acao';

        $analis = $this->analise->getListaAnalise(false, 14);
        // debug($analis, true);
        for ($da=0; $da < count($analis) ; $da++) { 
            $ent = $analis[$da];
            $log = buscaLog('pro_mic_analise', $ent['ana_id']);
            // debug($log);
            $analis[$da]['usu_nome'] = isset($log['usua_alterou'])?$log['usua_alterou']:'';
            $analis[$da]['stt_cor'] = 'bg-warning';
            $analis[$da]['stt_nome'] = 'Pendente';
        }
        $analise = [
            'data' => montaListaEditColunas($fields, 'ana_id', $analis, 'pro_despro'),
        ];
        echo json_encode($analise);
    }
    
    /**
    * Gravação
    * store
    *
    * @return void
    */
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        // debug($postado, true);
        $ret['erro'] = false;
        $erros = [];
        $movs  = [];
        if($postado['stt_id'] == 10){ // ESTAVA BLOQUEADO
            $movs[count($movs)]['id'] = 4;
            $movs[count($movs)]['qt'] = intval($postado['ana_qtde_micro']);
            $movs[count($movs)]['msg'] = ' enviado para Análise';
            $sql_ana = [
                'ana_id'    => intval($postado['ana_id']),
                'ana_qtde_micro'    => intval($postado['ana_qtde_micro']),
                'stt_id'    => 11, // status PENDENTE
            ];
        } else if($postado['stt_id'] == 11){ // ESTAVA PENDENDTE
            if($postado['ana_liberarsemmicro'] == 'N'){
                $status = 14; // status REALIZADA
                $sql_ana = [
                    'ana_id'    => intval($postado['ana_id']),
                    'ana_lotemb' => $postado['ana_lotemb'],
                    'ana_datalotemb' => $postado['ana_datalotemb'],
                    'stt_id'    => $status,
                ];
            } else {
                $status = 13; // status NÃO REALIZADO
                $sql_ana = [
                    'ana_id'    => intval($postado['ana_id']),
                    'stt_id'    => $status,
                ];
                $movs[count($movs)]['id'] = 5;
                $movs[count($movs)]['qt'] = intval($postado['ana_qtde']);
                $movs[count($movs)]['msg'] = ' liberado sem Micro';
                $movs[count($movs)]['id'] = 6;
                $movs[count($movs)]['qt'] = intval($postado['ana_qtde_micro']);
                $movs[count($movs)]['msg'] = ' liberado sem Micro';
            }
        } else if($postado['stt_id'] == 14){ // ESTAVA REALIZADA
            if($postado['ana_reprovar'] == 'S'){
                $status = 16; // status REPROVADA
                $sql_ana = [
                    'ana_id'    => intval($postado['ana_id']),
                    'ana_lotemb' => $postado['ana_lotemb'],
                    'tmo_id'    => intval($postado['tmo_id']),
                    'stt_id'    => $status,
                ];
                $movs[count($movs)]['id'] = intval($postado['tmo_id']);
                $movs[count($movs)]['qt'] = intval($postado['ana_qtde']);
                $movs[count($movs)]['msg'] = ' Análise reprovada';
            } 
            if($postado['ana_liberar'] == 'S'){
                $status = 12; // status EM ANDAMENTO
                $sql_ana = [
                    'ana_id'    => intval($postado['ana_id']),
                    'ana_lotemb' => $postado['ana_lotemb'],
                    'tmo_id'    => intval($postado['tmo_id']),
                    'stt_id'    => $status,
                ];
                $movs[count($movs)]['id'] = intval($postado['tmo_id']);
                $movs[count($movs)]['qt'] = intval($postado['ana_qtde']);
                $movs[count($movs)]['msg'] = ' Análise liberada';
            } 
        }
        if ($this->analise->save($sql_ana)) {
            if(count($movs) > 0){
                $this->geraMovimento($movs, $postado);
            }
            if($postado['stt_id'] == 11){ // ESTAVA PENDENTE
                if($postado['ana_liberarsemmicro'] == 'S'){
                    // ATUALIZA O LOTE PARA LIBERADO
                    $sql_lot = [
                        'lot_id' => $postado['lot_lote'],
                        'stt_id'   => 9 // LOTE LIBERADO
                    ];
                    $this->lote->save($sql_lot);
                }
            } else if($postado['stt_id'] == 14){ // ESTAVA REALIZADO
                if($postado['ana_liberar'] == 'S'){
                    $sql_lot = [
                        'lot_id' => $postado['lot_lote'],
                        'stt_id'   => 9 // LOTE LIBERADO
                    ];
                    $this->lote->save($sql_lot);
                }
                if($postado['ana_reprovar'] == 'S'){
                    $sql_lot = [
                        'lot_id' => $postado['lot_lote'],
                        'stt_id'   => 8 // LOTE BLOQUEADO
                    ];
                    $this->lote->save($sql_lot);
                }
            } else if($postado['stt_id'] == 12){ // ESTAVA EM ANDAMENTO
                if($postado['ana_reprovar'] == 'N'){
                    $files = $this->request->getFiles();
                    // debug($files,true);
                    if($files['ana_arqlaudo']->getSize()>0){
                        $arquivo = $files['ana_arqlaudo']->getPathName();
                        $tamanho = $files['ana_arqlaudo']->getSize();
                        $exte    = $files['ana_arqlaudo']->getExtension();
                        $tipo    = mime_content_type($arquivo);
                        $nome    = $files['ana_arqlaudo']->getName();
                        
                        $arqstring = file_get_contents($arquivo);
                        $base64orig = base64_encode($arqstring);   
                        $base64 = 'data: '.mime_content_type($arquivo).';base64,'.$base64orig;
                        // debug($base64,true);
                        $arqs['arq_nome'] = $nome;
                        $arqs['arq_exte'] = $exte;
                        $arqs['arq_tipo'] = $tipo;
                        $arqs['arq_size'] = $tamanho;
                        $registro = intval($postado['ana_id']);
                        
                        $arqdb       = new ArquivoMonModel();
                        $arq = $arqdb->insertArquivo('Analisa','ArqLaudo',$registro, $arqs, $base64);
                        if(!$arq){
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar o Arquivo '.$nome.', Verifique!';
                            echo json_encode($ret);
                            exit;
                        }
                    }
                }
                if($postado['ana_reprovar'] == 'S'){
                    $sql_lot = [
                        'lot_id' => $postado['lot_lote'],
                        'stt_id'   => 8 // LOTE BLOQUEADO
                    ];
                    $this->lote->save($sql_lot);
                }
            }
        } else {
            $ret['erro'] = true;
            $erros = $this->common->errors();
        }
        if ($ret['erro']) {
            if(is_numeric($erros[0])){
                $ret['msg'] = $erros[0];
            } else {
                $ret['msg']  = 'Não foi possível gravar os Dados do Analise, Verifique!<br><br>';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        } else {
            cache()->clean();
            $ret['msg']  = 'Dados da Analise gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
        cache()->clean();
    }
        
    public function geraMovimento($movimentos, $postado){
        for ($m = 0; $m < count($movimentos); $m++){
            $mov = $movimentos[$m];
            $produto = $this->produto->getProduto($postado['pro_id'])[0];
            $codpro = $produto['pro_codpro'];

            $msg =  'Produto '.$codpro.' Lote '.$postado['lot_lote'].$mov['msg'];
            envia_msg_ws($this->data['controler'],$msg,'MsgServer',session()->get('usu_id'),1);
            
            $datmov = date('d/m/Y');
            $codlot = $postado['lot_lote'];
            $qtdmov = $mov['qt'];
            $qtdmov = str_replace(['.', ','], '', $qtdmov);
            // BUSCA TIPO MOVIMENTO
            $movim  = $this->tipomovimento->getTipoMovimentacao($mov['id']);
            $codtns = $movim[0]['tmo_transacao_erp'];
            $depori = $movim[0]['dep_codorigem'];
            $depdes = $movim[0]['dep_coddestino'];

            // $soaptrf = new SoapSapiens();
            // $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes);        
        }
    }
}

