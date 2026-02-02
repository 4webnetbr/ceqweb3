<?php

namespace App\Controllers\Estoque;

use App\Models\MovimMonModel;
use App\Controllers\BaseController;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquTransacaoModel;

class Movimento extends BaseController
{
    public $data = [];
    public $permissao = '';

    public $common;
    public $produto;
    public $deposito;
    public $transacao;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->produto = new ProdutProdutoModel();
        $this->deposito = new EstoquDepositoModel();
        $this->transacao = new EstoquTransacaoModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, '_id,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        // if (!$depositos = cache('depositos')) {
            $movdb = new MovimMonModel();
            $movimentos = $movdb->getMovimentos();
            $deposito   = $this->deposito->getDeposito();
            $deposits   = array_column($deposito, 'dep_codDescricao', 'dep_codDep');
            $transac    = $this->transacao->getTransacao();
            $transacs   = array_column($transac, 'tns_codDescricao','tns_codtns');
            $produt     = $this->produto->getProduto();
            $produtos   = array_column($produt, 'pro_despro','pro_codpro');

            $dadosCombinados = [];

            foreach ($movimentos as $mov) {
                // debug($mov);
                $depori  = $mov->mov_depori;
                $depdes  = $mov->mov_depdes;
                $produto = $mov->mov_produto;
                $transac = $mov->mov_codtns;

                // Consulta no MariaDB pelo ID
                $deporigem  = $deposits[$depori] ?? '';
                $depdestino = $deposits[$depdes] ?? '';
                $transacao  = $transacs[$transac] ?? '';
                $desproduto = $produtos[$produto] ?? '';

                $id  = $mov->_id;
                $oid = (string) $id;

                $url_edit = base_url($this->data['controler'].'/edit/'.$oid);
                $botaoedt = "<button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0 float-end' 
                    data-mdb-toggle='tooltip' data-mdb-placement='top' 
                    title='Imprimir Requisição' onclick='openPDFModal(\"$url_edit\",\"Imprimir Requisição\")'>
                    <i class='fas fa-print'></i></button>";
                $botaoedt = '';

                $o = new \stdClass();
                $o->_id            = $oid;
                $o->mov_produto    = $mov->mov_produto;
                $o->mov_codtns     = $mov->mov_codtns;
                $o->mov_depori     = $mov->mov_depori;
                $o->mov_datmov     = data_br($mov->mov_data);
                $o->mov_qtdmov     = $mov->mov_qtdmov;
                $o->mov_codlot     = $mov->mov_codlot;
                $o->mov_depdes     = $mov->mov_depdes;
                $o->mov_valida     = $mov->mov_valida;
                $o->mov_status     = $mov->mov_status;
                $o->mov_msgretorno = $mov->mov_msgretorno;
                $o->deporigem      = $deporigem;
                $o->depdestino     = $depdestino;
                $o->transacao      = $transacao;
                $o->desproduto     = $mov->mov_produto.' - '.$desproduto;
                $o->acao_person    = [$botaoedt];

                $dadosCombinados[] = $o;

                $this->data['exclusao'] = false; // não tem exclusão
                $this->data['edicao'] = false;   // não tem edição

            }

            $campos = montaColunasCampos($this->data, '_id');
            $movimentos = [
                'data' => montaListaColunasEnt($this->data, '_id', $dadosCombinados, $campos[1]),
            ];
        // }
        echo json_encode($movimentos);
    }

    // public function edit($id){
    //     echo "Entrei na Edição";
    // }
}
