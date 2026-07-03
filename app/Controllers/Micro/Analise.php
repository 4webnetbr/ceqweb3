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
    public $data      = [];
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
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        /** @var \App\Models\Microb\MicrobAnaliseModel */
        $this->analise = model(MicrobAnaliseModel::class);

        $this->lote          = model(ProdutLoteModel::class);
        $this->produto       = model(ProdutProdutoModel::class);
        $this->tipomovimento = model(EstoquTipoMovimentacaoModel::class);
        $this->common        = model(CommonModel::class);

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
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
        $gera->i_cone        .= '<div class="align-items-start txt-bt-manut ">Gerar Requisição</div>';
        $gera->place          = 'Gerar Requisição';
        $gera->funcChan       = 'redireciona(\'AnaRequisicao/add/\')';
        $gera->classep        = 'btn-outline-success bt-manut btn-sm mb-2 float-end ';
        $this->data['botao']  = $gera->crBotao();

        $this->data['colunas']   = montaColunasLista($this->data, 'ana_id');
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
        $log           = buscaLogTabela('pro_mic_analise', $ana_ids_assoc);
        // debug($log);
        // Armazenar a base URL fora do loop para evitar chamadas repetidas
        $base_url = base_url('/CriamPdf2026/PrintAnaRequisicao/');
        // $imgService = new UserImageService();

        foreach ($dados_analise as &$ana) {
            $ana['usu_nome'] = buscaUsuarioLog($log[$ana['ana_id']]);

            if ($ana['stt_id'] == 11) { // se estiver pendente
                // Concatenar o URL de forma mais eficiente
                $dados = $this->analise->getListaAnalise($ana['ana_id'])[0] ?? null;

                $numetiquetas = (int) $dados['ana_qtde_micro'];
                $dados        = array_fill(0, $numetiquetas, $dados);
                // $chave        = uniqid('etq_');
                // cache()->save($chave, $dados, 300); // 1 minuto 

                $redis     = \Config\Services::redis();
                $sessionId = session_id();

                // 🔑 chave única por sessão + produto + quantidade
                $chave = "etq:{$sessionId}:" . md5($ana['ana_id']);

                // 🔍 tenta recuperar do Redis
                $cached = $redis->get($chave);

                if (! $cached) {
                    // 🔄 busca dados apenas se não existir
                    // 💾 salva no Redis com TTL de 15 minutos (900 segundos)
                    $redis->setex($chave, 900, json_encode($dados));

                    // 🧠 opcional: rastrear chaves da sessão
                    $redis->sAdd("etq_session:{$sessionId}", $chave);
                }

                $link = base_url('/CriaEtiquetaZPL/emiteEtiqueta/');
                // Gerar a ação do botão
                $ana['acao_person'] = [
                    "<button type='button' class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0 float-end'
                    data-mdb-toggle='tooltip' data-mdb-placement='top'
                    title='Imprimir Etiqueta'
                    onclick='gerarEtiquetaZPL(\"" . $link . "\",false,\"" . $chave . "\");'>
                    <i class='fas fa-tag'></i></button>",

                ];
            }
        }

        // $this->data['exclusao'] = false;
        $this->data['consulta'] = false;
        $analise                = ['data' => montaListaColunas($this->data, 'ana_id', $dados_analise, $campos[1])];

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
        $dados = (array) $this->analise->getListaAnalise($id)[0] ?? null;

        if (! $dados) {
            throw new \RuntimeException('Análise não encontrada.');
        }

        $status              = (int) $dados['stt_id'];
        $secao               = ['Dados Gerais'];
        $campos              = [[]];
        $this->data['botao'] = '';

        // Campos sempre presentes na primeira seção
        $fields      = $this->analise->defCampos($dados, $show);
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
            $secao[1]    = 'Dados da Análise';
            $fields2     = $this->analise->defCamposAnalise($dados, $show);
            $campos[1][] = $fields2['cla_metodanalise'];
            $campos[1][] = $fields2['ana_liberarsemmicro'];
            $campos[1][] = $fields2['ana_descmetodo'];
            $campos[1][] = $fields2['ana_lotemb'];
            $campos[1][] = $fields2['ana_datalotemb'];

            if ($status === 12 || $status === 15 || $status === 16) { // EM ANDAMENTO ou APROVADA ou REPROVADA
                $campos[1][] = $fields2['ana_laudo'];
                $campos[1][] = $fields2['ana_arqlaudo'];
                if ($status === 12 || $status === 16) { // EM ANDAMENTO
                    $this->data['botao'] = $fields['bt_finalizar'];
                }
            }

            // Ações adicionais
            $secao[2]    = 'Ações';
            $fields3     = $this->analise->defCamposAcoes($dados, $show);
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

        if ($dados['ana_liberar'] == 'S') {
            $script .= <<<SCRIPT
                <script>
                    mostraOcultaCampo('ana_liberar','S','tmo_id');
                    mostraOcultaCampo('ana_liberar','N','tmo_id_rep');
                    mudaObrigatorio('ana_liberar','S','tmo_id')
                </script>
            SCRIPT;
        }
        if ($dados['ana_reprovar'] == 'S') {
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
        $ret     = ['erro' => false];
        $postado = $this->request->getPost();
        $erros   = [];

        $ana_id    = intval($postado['ana_id'] ?? 0);
        $ana_laudo = trim($postado['ana_laudo'] ?? '');

        // Verifica se o laudo foi preenchido
        if (empty($ana_laudo)) {
            echo json_encode([
                'erro' => true,
                'msg'  => 'É obrigatório informar o Laudo',
            ]);
            return;
        }

        $sql_ana = [];
        // Prepara os dados para salvar se aprovado
        if (($postado['ana_liberar'] ?? '') === 'S') {
            $sql_ana = [
                'ana_id'       => $ana_id,
                'ana_laudo'    => $ana_laudo,
                'ana_liberar'  => $postado['ana_liberar'],
                'ana_reprovar' => $postado['ana_reprovar'],
                'stt_id'       => 15, // Aprovado
            ];

            if (! empty($postado['tmo_id'])) {
                $sql_ana['tmo_id'] = intval($postado['tmo_id']);
            }
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $files         = $this->request->getFiles();
            $arquivoUpload = $files['ana_arqlaudo'] ?? null;

            if (! $arquivoUpload || $arquivoUpload->getSize() <= 0) {
                throw new \Exception('É necessário anexar o Arquivo do Laudo.');
            }

            // Preparar arquivo
            $nomeArquivo       = $arquivoUpload->getName();
            $caminhoTemporario = $arquivoUpload->getPathName();

            $arqBase64 = base64_encode(file_get_contents($caminhoTemporario));
            $mime      = mime_content_type($caminhoTemporario);

            $arqs = [
                'arq_nome' => $nomeArquivo,
                'arq_exte' => $arquivoUpload->getExtension(),
                'arq_tipo' => $mime,
                'arq_size' => $arquivoUpload->getSize(),
            ];

            // Salva o arquivo
            $arqdb          = new ArquivoMonModel();
            $base64Completo = "data:{$mime};base64,{$arqBase64}";

            $arquivoSalvo = $arqdb->insertArquivo('Analisa', 'ArqLaudo', $ana_id, $arqs, $base64Completo);

            if (! $arquivoSalvo) {
                throw new \Exception("Não foi possível gravar o Arquivo {$nomeArquivo}, Verifique!");
            }

            // Salva dados da análise
            if (count($sql_ana)) {
                if (! $this->analise->save($sql_ana)) {
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

    // public function store()
    // {
    //     $ret  = ['erro' => false];
    //     $post = $this->request->getPost();
    //     // debug($post, true);
    //     $movs   = [];
    //     $sqlAna = [];
    //     $sqlLot = null;

    //     try {
    //         // Define a ação conforme o status inicial recebido
    //         switch ($post['stt_id']) {
    //             case 10: // ESTAVA BLOQUEADO
    //                 $movs[] = [
    //                     'id'  => 4,
    //                     'qt'  => intval($post['ana_qtde_micro']),
    //                     'msg' => 'enviado para Análise',
    //                 ];
    //                 $sqlAna = [
    //                     'ana_id'         => intval($post['ana_id']),
    //                     'ana_qtde_micro' => intval($post['ana_qtde_micro']),
    //                     'stt_id'         => 11, // Status PENDENTE
    //                 ];
    //                 break;
    //             case 11: // ESTAVA PENDENTE
    //                 if ($post['ana_liberarsemmicro'] == 'N') {
    //                     $status = 14; // REALIZADA
    //                     $sqlAna = [
    //                         'ana_id'              => intval($post['ana_id']),
    //                         'ana_lotemb'          => $post['ana_lotemb'],
    //                         'ana_liberarsemmicro' => $post['ana_liberarsemmicro'],
    //                         'ana_datalotemb'      => $post['ana_datalotemb'],
    //                         'ana_descmetodo'      => $post['ana_descmetodo'],
    //                         'stt_id'              => $status,
    //                     ];
    //                 } else {
    //                     $status = 13; // NÃO REALIZADA
    //                     $sqlAna = [
    //                         'ana_id'              => intval($post['ana_id']),
    //                         'ana_liberarsemmicro' => $post['ana_liberarsemmicro'],
    //                         'stt_id'              => $status,
    //                     ];
    //                     // gera movimentação do tipo MOV5 (5) da quantidade total do lote
    //                     $movs[] = [
    //                         'id'  => 5,
    //                         'qt'  => intval($post['ana_qtde']) - intval($post['ana_qtde_micro']),
    //                         'msg' => 'liberado sem Micro',
    //                     ];
    //                     // gera movimentação MOV6 da quantidade micro
    //                     $movs[] = [
    //                         'id'  => 6,
    //                         'qt'  => intval($post['ana_qtde_micro']),
    //                         'msg' => 'liberado sem Micro',
    //                     ];
    //                     // Atualização do lote para liberado
    //                     $sqlLot = [
    //                         'lot_id'       => $post['lot_id'],
    //                         'lot_validade' => $post['lot_validade'],
    //                         'stt_id'       => 9, // LOTE LIBERADO
    //                     ];
    //                 }
    //                 break;
    //             case 14: // ESTAVA REALIZADA
    //                 if ($post['ana_reprovar'] == 'S') {
    //                     $status = 16; // REPROVADA
    //                     $sqlAna = [
    //                         'ana_id'       => intval($post['ana_id']),
    //                         'ana_lotemb'   => $post['ana_lotemb'],
    //                         'ana_liberar'  => $post['ana_liberar'],
    //                         'ana_reprovar' => $post['ana_reprovar'],
    //                         'tmo_id'       => intval($post['tmo_id']),
    //                         'tmo_id_rep'   => intval($post['tmo_id_rep']),
    //                         'stt_id'       => $status,
    //                     ];
    //                     // BUSCAR DEPÓSITO DE ORIGEM
    //                     $mov     = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
    //                     $deporig = $mov[0]['dep_codorigem'];
    //                     // BUSCAR PRODUTO
    //                     $produto = $this->produto->getProduto($post['pro_id'], false)[0];
    //                     $codpro  = $produto['pro_codpro'];

    //                     // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
    //                     $qtia     = 0;
    //                     $busca    = new BuscasSapiens();
    //                     $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
    //                     if (count($saldoest) > 0) {
    //                         for ($s = 0; $s < count($saldoest); $s++) {
    //                             if ($saldoest[$s]->codigoLote == $post['lot_lote']) {
    //                                 $qtia = $saldoest[$s]->quantidadeEstoque;
    //                                 break;
    //                             }
    //                         }
    //                     }
    //                     $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
    //                         'id'  => intval($post['tmo_id_rep']),
    //                         'qt'  => $qtia,
    //                         'msg' => 'Análise reprovada',
    //                     ];
    //                     $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
    //                         'id'  => 7,
    //                         'qt'  => intval($post['ana_qtde_micro']),
    //                         'msg' => 'Análise reprovada',
    //                     ];
    //                     // Atualização do lote para bloqueado
    //                     $sqlLot = [
    //                         'lot_id'       => $post['lot_id'],
    //                         'lot_validade' => $post['lot_validade'],
    //                         'stt_id'       => 8, // LOTE BLOQUEADO
    //                     ];
    //                 } else if ($post['ana_liberar'] == 'S') {
    //                     $status = 12; // EM ANDAMENTO
    //                     $sqlAna = [
    //                         'ana_id'       => intval($post['ana_id']),
    //                         'ana_lotemb'   => $post['ana_lotemb'],
    //                         'ana_liberar'  => $post['ana_liberar'],
    //                         'ana_reprovar' => $post['ana_reprovar'],
    //                         'tmo_id'       => intval($post['tmo_id']),
    //                         'tmo_id_rep'   => intval($post['tmo_id_rep']),
    //                         'stt_id'       => $status,
    //                     ];
    //                     // gera movimentação do tipo MOV5 (5) da quantidade total do lote
    //                     $movs[] = [
    //                         'id'  => 5,
    //                         'qt'  => intval($post['ana_qtde']) - intval($post['ana_qtde_micro']),
    //                         'msg' => 'Análise liberada',
    //                     ];
    //                     // gera movimentação cadastrada da quantidade micro
    //                     $movs[] = [
    //                         'id'  => intval($post['tmo_id']),
    //                         'qt'  => intval($post['ana_qtde_micro']),
    //                         'msg' => 'Análise liberada',
    //                     ];
    //                     // debug($movs, true);
    //                     // Atualização do lote para liberado
    //                     $sqlLot = [
    //                         'lot_id'       => $post['lot_id'],
    //                         'lot_validade' => $post['lot_validade'],
    //                         'stt_id'       => 9, // LOTE LIBERADO
    //                     ];
    //                 } else {
    //                     $sqlAna = [
    //                         'ana_id'         => intval($post['ana_id']),
    //                         'ana_descmetodo' => $post['ana_descmetodo'],
    //                         'ana_lotemb'     => $post['ana_lotemb'],
    //                     ];
    //                 }
    //                 break;

    //             case 12: // ESTAVA EM ANDAMENTO
    //                 // $status = 12; // Continua EM ANDAMENTO
    //                 $sqlAna = [
    //                     'ana_id'     => intval($post['ana_id']),
    //                     'tmo_id'     => intval($post['tmo_id']),
    //                     'tmo_id_rep' => intval($post['tmo_id_rep']),
    //                     'ana_lotemb' => $post['ana_lotemb'],
    //                     // 'stt_id' => $status,
    //                 ];
    //                 if ($post['ana_reprovar'] == 'S') {
    //                     $status = 16; // Reprovado
    //                     $sqlAna = [
    //                         'ana_id'       => intval($post['ana_id']),
    //                         'ana_liberar'  => $post['ana_liberar'],
    //                         'ana_reprovar' => $post['ana_reprovar'],
    //                         'tmo_id'       => intval($post['tmo_id']),
    //                         'tmo_id_rep'   => intval($post['tmo_id_rep']),
    //                         'stt_id'       => $status,
    //                     ];
    //                     // BUSCAR DEPÓSITO DE ORIGEM
    //                     $mov     = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
    //                     $deporig = $mov[0]['dep_codorigem'];
    //                     // BUSCAR PRODUTO
    //                     $produto = $this->produto->getProduto($post['pro_id'], false)[0];
    //                     $codpro  = $produto['pro_codpro'];

    //                     // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
    //                     $qtia     = 0;
    //                     $busca    = new BuscasSapiens();
    //                     $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
    //                     if (count($saldoest) > 0) {
    //                         for ($s = 0; $s < count($saldoest); $s++) {
    //                             if ($saldoest[$s]->codigoLote == $post['lot_lote']) {
    //                                 $qtia = $saldoest[$s]->quantidadeEstoque;
    //                                 break;
    //                             }
    //                         }
    //                     }
    //                     $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
    //                         'id'  => intval($post['tmo_id_rep']),
    //                         'qt'  => $qtia,
    //                         'msg' => 'Análise reprovada',
    //                     ];
    //                     $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
    //                         'id'  => 7,
    //                         'qt'  => intval($post['ana_qtde_micro']),
    //                         'msg' => 'Análise reprovada',
    //                     ];
    //                     $sqlLot = [
    //                         'lot_id'       => $post['lot_id'],
    //                         'lot_validade' => $post['lot_validade'],
    //                         'stt_id'       => 8, // LOTE BLOQUEADO
    //                     ];
    //                 }
    //                 break;

    //             default:
    //                 throw new \Exception("Status inválido recebido.");
    //         }

    //         // Inicia a transação

    //         // Gera movimentos se existirem
    //         if (! empty($movs)) {
    //             cache()->clean();
    //             $movim = $this->geraMovimento($movs, $post);
    //             // debug($movim, true);
    //             if ($movim['status'] == 'Erro') {
    //                 $ret['erro'] = true;
    //                 $ret['msg']  = $movim['mensagem'];
    //             }
    //         }

    //         if (! $ret['erro']) {
    //             $this->analise->transBegin();
    //             // Salva dados da análise
    //             if (! $this->analise->save($sqlAna)) {
    //                 $this->analise->transRollback();
    //                 $ret['erro'] = true;
    //                 $ret['msg']  = $this->analise->errors();
    //                 throw new \Exception(implode(' ', $this->analise->errors()));
    //             } else {
    //                 // Trata upload e processamento de arquivo quando estiver EM ANDAMENTO e não for reprovação
    //                 if ($post['stt_id'] == 12 && $post['ana_reprovar'] != 'S') {
    //                     $files = $this->request->getFiles();
    //                     if (isset($files['ana_arqlaudo']) && $files['ana_arqlaudo']->getSize() > 0) {
    //                         $uploadRet = $this->processaArquivoLaudo($files['ana_arqlaudo'], $post['ana_id']);
    //                         if ($uploadRet !== true) {
    //                             $ret['erro'] = true;
    //                             $ret['msg']  = $this->analise->errors();
    //                             $this->analise->transRollback();
    //                             throw new \Exception($uploadRet);
    //                         }
    //                     }
    //                 }
    //                 if (! $ret['erro']) {
    //                     // Atualiza o lote, se necessário
    //                     if ($sqlLot) {
    //                         // Inicia transação para lote
    //                         $this->lote->transBegin();
    //                         if (! $this->lote->save($sqlLot)) {
    //                             $this->analise->transRollback();
    //                             $this->lote->transRollback();
    //                             $ret['erro'] = true;
    //                             $ret['msg']  = $this->lote->errors();
    //                             throw new \Exception(implode(' ', $this->lote->errors()));
    //                         } else {
    //                             $this->lote->transCommit();
    //                         }
    //                     }

    //                     if (! $ret['erro']) {
    //                         // Commit final
    //                         $this->analise->transCommit();
    //                         cache()->clean();
    //                         $ret['msg'] = 'Dados da Analise gravados com Sucesso!!!';
    //                         session()->setFlashdata('msg', $ret['msg']);

    //                         if ($post['stt_id'] == 10) { // ESTAVA BLOQUEADO
    //                             $dados = $this->analise->getListaAnalise($post['ana_id'])[0] ?? null;

    //                             $numetiquetas = (int) $dados['ana_qtde_micro'];
    //                             $dados        = array_fill(0, $numetiquetas, $dados);
    //                             // $chave        = uniqid('etq_');
    //                             // cache()->save($chave, $dados, 300); // 1 minuto 

    //                             $redis     = \Config\Services::redis();
    //                             $sessionId = session_id();

    //                             // 🔑 chave única por sessão + produto + quantidade
    //                             $chave = "etq:{$sessionId}:" . md5($post['ana_id']);

    //                             // 🔍 tenta recuperar do Redis
    //                             $cached = $redis->get($chave);

    //                             if (! $cached) {
    //                                 // 🔄 busca dados apenas se não existir
    //                                 // 💾 salva no Redis com TTL de 15 minutos (900 segundos)
    //                                 $redis->setex($chave, 900, json_encode($dados));

    //                                 // 🧠 opcional: rastrear chaves da sessão
    //                                 $redis->sAdd("etq_session:{$sessionId}", $chave);
    //                             }

    //                             $link   = base_url('/CriaEtiquetaZPL/emiteEtiqueta/');
    //                             $script = "gerarEtiquetaZPL(\"" . $link . "\",false,\"" . $chave . "\");";
    //                             session()->setFlashdata('modal', $link);
    //                             session()->setFlashdata('chave', $chave);
    //                             session()->setFlashdata('script', $script);
    //                             session()->setFlashdata('modal-title', 'Imprimir Etiqueta');
    //                         }

    //                         $ret['url'] = site_url($this->data['controler']);
    //                     }
    //                 }
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $ret['erro'] = true;
    //         $ret['msg']  = $e->getMessage();
    //     }

    //     echo json_encode($ret);
    // }

    public function store()
    {
        $ret  = ['erro' => false];
        $post = $this->request->getPost();
        // debug($post, true);
        $movs   = [];
        $sqlAna = [];
        $sqlLot = null;

        try {
            // Define a ação conforme o status inicial recebido
            switch ($post['stt_id']) {
                case 10: // ESTAVA BLOQUEADO
                    $movs[] = [
                        'id'  => 4,
                        'qt'  => intval($post['ana_qtde_micro']),
                        'msg' => 'enviado para Análise',
                    ];
                    $sqlAna = [
                        'ana_id'         => intval($post['ana_id']),
                        'ana_qtde_micro' => intval($post['ana_qtde_micro']),
                        'stt_id'         => 11, // Status PENDENTE
                    ];
                    break;
                case 11: // ESTAVA PENDENTE
                    if ($post['ana_liberarsemmicro'] == 'N') {
                        $status = 14; // REALIZADA
                        $sqlAna = [
                            'ana_id'              => intval($post['ana_id']),
                            'ana_lotemb'          => $post['ana_lotemb'],
                            'ana_liberarsemmicro' => $post['ana_liberarsemmicro'],
                            'ana_datalotemb'      => $post['ana_datalotemb'],
                            'ana_descmetodo'      => $post['ana_descmetodo'],
                            'stt_id'              => $status,
                        ];
                    } else {
                        $status = 13; // NÃO REALIZADA
                        $sqlAna = [
                            'ana_id'              => intval($post['ana_id']),
                            'ana_liberarsemmicro' => $post['ana_liberarsemmicro'],
                            'stt_id'              => $status,
                        ];
                        // gera movimentação do tipo MOV5 (5) da quantidade total do lote
                        $movs[] = [
                            'id'  => 5,
                            'qt'  => intval($post['ana_qtde']) - intval($post['ana_qtde_micro']),
                            'msg' => 'liberado sem Micro',
                        ];
                        // gera movimentação MOV6 da quantidade micro
                        $movs[] = [
                            'id'  => 6,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'liberado sem Micro',
                        ];
                        // Atualização do lote para liberado
                        $sqlLot = [
                            'lot_id'       => $post['lot_id'],
                            'lot_validade' => $post['lot_validade'],
                            'stt_id'       => 9, // LOTE LIBERADO
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
                            'ana_reprovar' => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'   => intval($post['tmo_id_rep']),
                            'stt_id'       => $status,
                        ];
                        // BUSCAR DEPÓSITO DE ORIGEM
                        $mov     = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
                        $deporig = $mov[0]['dep_codorigem'];
                        // BUSCAR PRODUTO
                        $produto = $this->produto->getProduto($post['pro_id'], false)[0];
                        $codpro  = $produto['pro_codpro'];

                        // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
                        $qtia     = 0;
                        $busca    = new BuscasSapiens();
                        $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
                        if (count($saldoest) > 0) {
                            for ($s = 0; $s < count($saldoest); $s++) {
                                if ($saldoest[$s]->codigoLote == $post['lot_lote']) {
                                    $qtia = $saldoest[$s]->quantidadeEstoque;
                                    break;
                                }
                            }
                        }
                        $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
                            'id'  => intval($post['tmo_id_rep']),
                            'qt'  => $qtia,
                            'msg' => 'Análise reprovada',
                        ];
                        $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
                            'id'  => 7,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise reprovada',
                        ];
                        // Atualização do lote para bloqueado
                        $sqlLot = [
                            'lot_id'       => $post['lot_id'],
                            'lot_validade' => $post['lot_validade'],
                            'stt_id'       => 8, // LOTE BLOQUEADO
                        ];
                    } else if ($post['ana_liberar'] == 'S') {
                        $status = 12; // EM ANDAMENTO
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_lotemb'   => $post['ana_lotemb'],
                            'ana_liberar'  => $post['ana_liberar'],
                            'ana_reprovar' => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'   => intval($post['tmo_id_rep']),
                            'stt_id'       => $status,
                        ];
                        // gera movimentação do tipo MOV5 (5) da quantidade total do lote
                        $movs[] = [
                            'id'  => 5,
                            'qt'  => intval($post['ana_qtde']) - intval($post['ana_qtde_micro']),
                            'msg' => 'Análise liberada',
                        ];
                        // gera movimentação cadastrada da quantidade micro
                        $movs[] = [
                            'id'  => intval($post['tmo_id']),
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise liberada',
                        ];
                        // debug($movs, true);
                        // Atualização do lote para liberado
                        $sqlLot = [
                            'lot_id'       => $post['lot_id'],
                            'lot_validade' => $post['lot_validade'],
                            'stt_id'       => 9, // LOTE LIBERADO
                        ];
                    } else {
                        $sqlAna = [
                            'ana_id'         => intval($post['ana_id']),
                            'ana_descmetodo' => $post['ana_descmetodo'],
                            'ana_lotemb'     => $post['ana_lotemb'],
                        ];
                    }
                    break;

                case 12: // ESTAVA EM ANDAMENTO
                    // $status = 12; // Continua EM ANDAMENTO
                    $sqlAna = [
                        'ana_id'     => intval($post['ana_id']),
                        'tmo_id'     => intval($post['tmo_id']),
                        'tmo_id_rep' => intval($post['tmo_id_rep']),
                        'ana_lotemb' => $post['ana_lotemb'],
                        // 'stt_id' => $status,
                    ];
                    if ($post['ana_reprovar'] == 'S') {
                        $status = 16; // Reprovado
                        $sqlAna = [
                            'ana_id'       => intval($post['ana_id']),
                            'ana_liberar'  => $post['ana_liberar'],
                            'ana_reprovar' => $post['ana_reprovar'],
                            'tmo_id'       => intval($post['tmo_id']),
                            'tmo_id_rep'   => intval($post['tmo_id_rep']),
                            'stt_id'       => $status,
                        ];
                        // BUSCAR DEPÓSITO DE ORIGEM
                        $mov     = $this->tipomovimento->getTipoMovimentacao($post['tmo_id_rep']);
                        $deporig = $mov[0]['dep_codorigem'];
                        // BUSCAR PRODUTO
                        $produto = $this->produto->getProduto($post['pro_id'], false)[0];
                        $codpro  = $produto['pro_codpro'];

                        // BUSCAR SALDO DO DEPÓSITO DE ORIGEM
                        $qtia     = 0;
                        $busca    = new BuscasSapiens();
                        $saldoest = $busca->buscaEstoqueDeposito($deporig, $codpro);
                        if (count($saldoest) > 0) {
                            for ($s = 0; $s < count($saldoest); $s++) {
                                if ($saldoest[$s]->codigoLote == $post['lot_lote']) {
                                    $qtia = $saldoest[$s]->quantidadeEstoque;
                                    break;
                                }
                            }
                        }
                        $movs[] = [ // A QUANTIDADE É O SALDO DO DEPÓSITO DE ORIGEM INFORMADO NA MOVIMENTAÇÃO
                            'id'  => intval($post['tmo_id_rep']),
                            'qt'  => $qtia,
                            'msg' => 'Análise reprovada',
                        ];
                        $movs[] = [ // GERA MOVIMENTACAO MOV7 (7) DA QUANTIDADE MICRO
                            'id'  => 7,
                            'qt'  => intval($post['ana_qtde_micro']),
                            'msg' => 'Análise reprovada',
                        ];
                        $sqlLot = [
                            'lot_id'       => $post['lot_id'],
                            'lot_validade' => $post['lot_validade'],
                            'stt_id'       => 8, // LOTE BLOQUEADO
                        ];
                    }
                    break;

                default:
                    throw new \Exception("Status inválido recebido.");
            }

            // Inicia a transação

            // Gera movimentos se existirem
            if (! empty($movs)) {
                cache()->clean();

                // Enriquece os movimentos com dados do produto/lote
                $movsEnriquecidos = array_map(function ($mov) use ($post) {
                    return array_merge($mov, [
                        'pro_id'        => $post['pro_id'] ?? null,
                        'lot_lote'      => $post['lot_lote'] ?? '',
                        'lot_validade'  => $post['lot_validade'] ?? '',
                        'rep_id'        => null,
                        'reserva'       => '',
                    ]);
                }, $movs);

                // Chama a função unificada
                $movim = geraMovimentoRequisicoes($movsEnriquecidos, $this->data['controler']);

                if ($movim['status'] == 'Erro') {
                    $ret['erro'] = true;
                    $ret['msg']  = $movim['mensagem'];
                }
            }

            if (! $ret['erro']) {
                $this->analise->transBegin();
                // Salva dados da análise
                if (! $this->analise->save($sqlAna)) {
                    $this->analise->transRollback();
                    $ret['erro'] = true;
                    $ret['msg']  = $this->analise->errors();
                    throw new \Exception(implode(' ', $this->analise->errors()));
                } else {
                    // Trata upload e processamento de arquivo quando estiver EM ANDAMENTO e não for reprovação
                    if ($post['stt_id'] == 12 && $post['ana_reprovar'] != 'S') {
                        $files = $this->request->getFiles();
                        if (isset($files['ana_arqlaudo']) && $files['ana_arqlaudo']->getSize() > 0) {
                            $uploadRet = $this->processaArquivoLaudo($files['ana_arqlaudo'], $post['ana_id']);
                            if ($uploadRet !== true) {
                                $ret['erro'] = true;
                                $ret['msg']  = $this->analise->errors();
                                $this->analise->transRollback();
                                throw new \Exception($uploadRet);
                            }
                        }
                    }
                    if (! $ret['erro']) {
                        // Atualiza o lote, se necessário
                        if ($sqlLot) {
                            // Inicia transação para lote
                            $this->lote->transBegin();
                            if (! $this->lote->save($sqlLot)) {
                                $this->analise->transRollback();
                                $this->lote->transRollback();
                                $ret['erro'] = true;
                                $ret['msg']  = $this->lote->errors();
                                throw new \Exception(implode(' ', $this->lote->errors()));
                            } else {
                                $this->lote->transCommit();
                            }
                        }

                        if (! $ret['erro']) {
                            // Commit final
                            $this->analise->transCommit();
                            cache()->clean();
                            $ret['msg'] = 'Dados da Analise gravados com Sucesso!!!';
                            session()->setFlashdata('msg', $ret['msg']);

                            if ($post['stt_id'] == 10) { // ESTAVA BLOQUEADO
                                $dados = $this->analise->getListaAnalise($post['ana_id'])[0] ?? null;

                                $numetiquetas = (int) $dados['ana_qtde_micro'];
                                $dados        = array_fill(0, $numetiquetas, $dados);
                                // $chave        = uniqid('etq_');
                                // cache()->save($chave, $dados, 300); // 1 minuto 

                                $redis     = \Config\Services::redis();
                                $sessionId = session_id();

                                // 🔑 chave única por sessão + produto + quantidade
                                $chave = "etq:{$sessionId}:" . md5($post['ana_id']);

                                // 🔍 tenta recuperar do Redis
                                $cached = $redis->get($chave);

                                if (! $cached) {
                                    // 🔄 busca dados apenas se não existir
                                    // 💾 salva no Redis com TTL de 15 minutos (900 segundos)
                                    $redis->setex($chave, 900, json_encode($dados));

                                    // 🧠 opcional: rastrear chaves da sessão
                                    $redis->sAdd("etq_session:{$sessionId}", $chave);
                                }

                                $link   = base_url('/CriaEtiquetaZPL/emiteEtiqueta/');
                                $script = "gerarEtiquetaZPL(\"" . $link . "\",false,\"" . $chave . "\");";
                                session()->setFlashdata('modal', $link);
                                session()->setFlashdata('chave', $chave);
                                session()->setFlashdata('script', $script);
                                session()->setFlashdata('modal-title', 'Imprimir Etiqueta');
                            }

                            $ret['url'] = site_url($this->data['controler']);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = $e->getMessage();
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

            $arqdb     = new ArquivoMonModel();
            $resultado = $arqdb->insertArquivo('Analisa', 'ArqLaudo', intval($anaId), $arqs, $base64);
            if (! $resultado) {
                return 'Não foi possível gravar o Arquivo ' . $nome . ', verifique!';
            }
        }
        return true;
    }

    public function geraMovimento($movimentos, $postado)
    {
        $soaptrf = new SoapSapiens();
        for ($m = 0; $m < count($movimentos); $m++) {
            $mov     = $movimentos[$m];
            $produto = (array) $this->produto->getProduto($postado['pro_id'], false)[0];
            $codpro  = $produto['pro_codpro'];

            $msg = 'Produto ' . $codpro . ' Lote ' . $postado['lot_lote'] . $mov['msg'];
            envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

            $datmov = date('d/m/Y');
            $codlot = $postado['lot_lote'];
            $qtdmov = $mov['qt'];
            $qtdmov = str_replace(['.', ','], '', $qtdmov);
            // BUSCA TIPO MOVIMENTO
            $movim = (array) $this->tipomovimento->getTipoMovimentacao($mov['id'])[0];
            // debug($movim, tre);
            $codtns = $movim['tmm_transacao'];
            $depori = $movim['dep_codorigem'];
            $depdes = $movim['dep_coddestino'];
            $valida = data_br($postado['lot_validade']);

            log_message('info', 'Movimento ' . json_encode($movim));
            log_message('info', 'Depósito Origem ' . $depori);
            log_message('info', 'Depósito Destino ' . $depdes);

            if ($depdes == null || $depdes == '') {
                log_message('info', 'Sem depósito de Destino, vou movimentar');
                $movimenta = $soaptrf->movimProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
            } else {
                log_message('info', 'Com depósito de Destino, vou transferir');
                $movimenta = $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
            }
            if ($movimenta['status'] == 'Erro') {
                // se o movimento deu erro, verifica se teve movimentos anteriores e desfaz
                if ($m > 0) {
                    for ($rv = ($m - 1); $rv >= 0; $rv--) {
                        $rev     = $movimentos[$rv];
                        $produto = $this->produto->getProduto($postado['pro_id'], false)[0];
                        $codpro  = $produto['pro_codpro'];

                        $msg = 'Produto ' . $codpro . ' Lote ' . $postado['lot_lote'] . $rev['msg'];
                        envia_msg_ws($this->data['controler'], $msg, 'MsgServer', session()->get('usu_id'), 1);

                        $datmov = date('d/m/Y');
                        $codlot = $postado['lot_lote'];
                        $qtdmov = $rev['qt'];
                        $qtdmov = str_replace(['.', ','], '', $qtdmov);
                        // BUSCA TIPO MOVIMENTO
                        $movim  = $this->tipomovimento->getTipoMovimentacao($rev['id']);
                        $codtns = $movim['tmm_transacao'];
                        // deposito de destino é a origem, para reverter
                        $depdes = $movim['dep_codorigem'];
                        // depósito de origem é o destino, para reverter
                        $depori = $movim['dep_coddestino'];
                        $valida = data_br($postado['lot_validade']);

                        log_message('info', 'Movimento Reverso ' . json_encode($movim));
                        $reverte = $soaptrf->transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida);
                    }
                }
                break;
            }
        }
        return $movimenta;
    }
}
