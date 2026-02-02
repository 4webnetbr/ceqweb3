<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Entities\Estoque\EntSaldoEstoque;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Produt\ProdutProdutoModel;

class SaldoEstoque extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $deposito;
    public $produto;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->deposito  = new EstoquDepositoModel();
        $this->produto   = new ProdutProdutoModel();

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
        $entity = new EntSaldoEstoque();
        $fields = $entity->campos;
    
        $secao[0] = 'Buscar';
        $campos[0][0] = $fields->sal_depo;
        $campos[0][1] = $fields->sal_code;
        $campos[0][2] = $fields->sal_btbu;
    
        $colunas = ['Depósito', 'CodErp', 'Produto', 'Lote', 'Validade', 'Saldo', 'Und', 'Entrada'];
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['colunas'] = $colunas;
        $this->data['destino'] = 'lista';
    
        echo view('vw_filtro', $this->data);
    }

    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        $vars = $_REQUEST;
        $dep = trim($vars['codDep']);
        $pro = trim($vars['codPro']);
        if ($pro == '') {
            $pro = false;
        }

        $estoques = [];
        // $produt = new SoapSapiens();
        if (trim($vars['codDep']) != '') {
            $busca = new BuscasSapiens();
            $saldoest = $busca->buscaEstoqueDeposito($dep, $pro);
            if (is_object($saldoest)) {
                // Converte objeto em array
                $dep = $saldoest;
                // debug($dep);
                if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
                    ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
                ) {
                    if ($this->produto->getProdutoCod($dep->codigoProduto)) {
                        $lote = [
                            'codDep'      => $dep->codigoDeposito,
                            'Coderp'      => $dep->codigoProduto,
                            'DescProduto' => $dep->descricaoProduto,
                            'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
                            'lote'        => $dep->codigoLote,
                            'validade'    => $dep->validade,
                            'validadeord' => data_db($dep->validade),
                            'entrada'     => $dep->entrada,
                            'entradaord'  => data_db($dep->entrada),
                            'und'         => $dep->unidmedida,
                        ];
                        if ($dep->codigoLote == 'N/A') {
                            $lote['saldo'] = $dep->estoqueDeposito;
                            $lote['validade']    = '';
                            $lote['validadeord'] = '';
                            $lote['entrada']     = '';
                            $lote['entradaord']  = '';
                        } else {
                            $lote['saldo'] = $dep->quantidadeEstoque;
                        }
                        array_push($estoques, $lote);
                    }
                }
            } else {
                // debug($saldoest, true);

                $total = 0;
                for ($d = 0; $d < sizeof($saldoest); $d++) {
                    $dep = $saldoest[$d];
                    // debug($dep);
                    if ($this->produto->getProdutoCod($dep->codigoProduto)) {
                        if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
                            ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
                        ) {
                            // debug($dep, true);
                            $lote = [
                                'codDep'      => $dep->codigoDeposito,
                                'Coderp'      => $dep->codigoProduto,
                                'DescProduto' => $dep->descricaoProduto,
                                'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
                                'lote'        => $dep->codigoLote,
                                'validade'    => $dep->validade,
                                'validadeord' => data_db($dep->validade),
                                'entrada'     => $dep->entrada,
                                'entradaord'  => data_db($dep->entrada),
                                'und'         => $dep->unidmedida,
                            ];
                            if ($dep->codigoLote == 'N/A') {
                                $lote['saldo'] = $dep->estoqueDeposito;
                                $lote['validade']    = '';
                                $lote['validadeord'] = '';
                                $lote['entrada']     = '';
                                $lote['entradaord']  = '';
                            } else {
                                $lote['saldo'] = $dep->quantidadeEstoque;
                            }
                            array_push($estoques, $lote);
                        }
                    }
                }
            }
        }
        echo json_encode($estoques);
    }
}
