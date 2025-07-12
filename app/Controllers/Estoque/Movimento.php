<?php

namespace App\Controllers\Estoque;

use App\Models\MovimMonModel;
use App\Controllers\Ws\WsCeqweb;
use App\Controllers\BaseController;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquTransacaoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

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
            // debug($movimentos, true);
            $deposito   = $this->deposito->getDeposito();
            // debug($deposito, true);
            $deposits = array_column($deposito, 'dep_codDescricao', 'dep_codDep');
            $transac    = $this->transacao->getTransacao();
            $transacs = array_column($transac, 'tns_codDescricao','tns_codtns');
            // debug($transacs, true);
            $produt    = $this->produto->getProduto();
            $produtos = array_column($produt, 'pro_despro','pro_codpro');

            $dadosCombinados = [];

            foreach ($movimentos as $mov) {
                $depori = $mov->mov_depori;
                $depdes = $mov->mov_depdes;
                $produto = $mov->mov_produto;
                $transac = $mov->mov_codtns;

                // Consulta no MariaDB pelo ID
                $deporigem = $deposits[$depori];
                $depdestino = $deposits[$depdes];
                $transacao = $transacs[$transac];
                $desproduto = $produtos[$produto];

                $dadosCombinados[] = [
                    '_id'       => $mov->_id,
                    'mov_produto'       => $mov->mov_produto,
                    'mov_codtns'       => $mov->mov_codtns,
                    'mov_depori'       => $mov->mov_depori,
                    'mov_datmov'       => $mov->mov_datmov,
                    'mov_qtdmov'       => $mov->mov_qtdmov,
                    'mov_codlot'       => $mov->mov_codlot,
                    'mov_depdes'       => $mov->mov_depdes,
                    'mov_valida'       => $mov->mov_valida,
                    'mov_status'       => $mov->mov_status,
                    'mov_msgretorno'       => $mov->mov_msgretorno,
                    'deporigem' => $deporigem,
                    'depdestino' => $depdestino,
                    'transacao' => $transacao,
                    'desproduto' => $desproduto,
                ];
            }

            $campos = montaColunasCampos($this->data, '_id');
            $movimentos = [
                'data' => montaListaColunas($this->data, '_id', $dadosCombinados, $campos[1]),
            ];
        // }
        echo json_encode($movimentos);
    }

    public function show($id){
        $integ = new WsCeqweb();
        $integ->integraDeposito();

		$dados_depositos = $this->depositos->getDeposito($id);
        $fields = $this->depositos->defCampos($dados_depositos[0], true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['dep_codDep']; 
        $campos[0][1] = $fields['dep_desDep'];
        $campos[0][2] = $fields['dep_aceNeg'];
        $campos[0][3] = $fields['dep_codDescricao'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_sap_deposito', $id);

        echo view('vw_edicao', $this->data);
    }

}
