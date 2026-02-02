<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;
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
        $this->defCampos();

        $secao[0] = 'Buscar';
        $campos[0][0] = $this->sal_depo;
        $campos[0][1] = $this->sal_code;
        $campos[0][2] = $this->sal_btbu;

        $colunas = ['Depósito', 'CodErp', 'Produto', 'Lote', 'Validade', 'Saldo', 'Und', 'Entrada'];

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
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
                            'codDep'   => $dep->codigoDeposito,
                            'Coderp'    => $dep->codigoProduto,
                            'DescProduto' => $dep->descricaoProduto,
                            'Produto'   => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
                            'lote'      => $dep->codigoLote,
                            'validade'  => $dep->validade,
                            'validadeord' => data_db($dep->validade),
                            'entrada'   => $dep->entrada,
                            'entradaord' => data_db($dep->entrada),
                            'und'       => $dep->unidmedida,
                        ];
                        if ($dep->codigoLote == 'N/A') {
                            $lote['saldo'] = $dep->estoqueDeposito;
                            $lote['validade']  = '';
                            $lote['validadeord'] = '';
                            $lote['entrada']  = '';
                            $lote['entradaord'] = '';
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
                                'codDep'   => $dep->codigoDeposito,
                                'Coderp'    => $dep->codigoProduto,
                                'DescProduto' => $dep->descricaoProduto,
                                'Produto'   => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
                                'lote'      => $dep->codigoLote,
                                'validade'  => $dep->validade,
                                'validadeord' => data_db($dep->validade),
                                'entrada'   => $dep->entrada,
                                'entradaord' => data_db($dep->entrada),
                                'und'       => $dep->unidmedida,
                            ];
                            if ($dep->codigoLote == 'N/A') {
                                $lote['saldo'] = $dep->estoqueDeposito;
                                $lote['validade']  = '';
                                $lote['validadeord'] = '';
                                $lote['entrada']  = '';
                                $lote['entradaord'] = '';
                            } else {
                                $lote['saldo'] = $dep->quantidadeEstoque;
                            }
                            array_push($estoques, $lote);
                        }
                    }
                }
            }
        }
        // array_push($estoques, $lote);
        echo json_encode($estoques);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param array $dados
     * @return void
     */
    public function defCampos()
    {
        // $busca = new BuscasSapiens();
        // $r_deps = $busca->buscaDepositos();

        $r_deps = $this->deposito->getDeposito();

        // debug($r_deps);
        $depos = array_column($r_deps, 'dep_codDescricao', 'dep_codDep');
        // debug($depos, true);
        $depo               =  new MyCampo();
        $depo->objeto       = 'select';
        $depo->id           = 'codDep';
        $depo->nome         = 'codDep';
        $depo->label        = 'Depósito';
        $depo->obrigatorio  = true;
        $depo->size         = 50;
        $depo->largura      = 50;
        $depo->valor        = '';
        $depo->dispForm     = 'col-5';
        $depo->opcoes       = $depos;
        $depo->selecionado  = 'GER';
        $this->sal_depo     = $depo->crSelect();

        $code               =  new MyCampo();
        $code->objeto       = 'input';
        $code->id           = 'codPro';
        $code->nome         = 'codPro';
        $code->label        = 'Código ERP';
        $code->obrigatorio  = false;
        $code->size         = 15;
        $code->valor        = '';
        $code->dispForm     = 'col-2';
        $this->sal_code     = $code->crInput();

        $lote               =  new MyCampo();
        $lote->objeto       = 'input';
        $lote->id           = 'codLot';
        $lote->nome         = 'codLot';
        $lote->label        = 'Lote';
        $lote->size         = 15;
        $lote->valor        = '';
        $lote->dispForm     = 'col-2';
        $this->sal_lote     = $lote->crInput();

        $btbu               = new MyCampo();
        $btbu->id           = 'btBuscar';
        $btbu->nome         = 'btBuscar';
        $btbu->tipo         = 'button';
        $btbu->label        = 'Buscar';
        $btbu->dispForm     = '2col';
        $btbu->funcChan     = 'buscaSaldo()';
        $btbu->i_cone       = '<i class="fa-solid fa-magnifying-glass"></i> Buscar Estoque';
        $btbu->place        = 'Buscar Saldo';
        $btbu->classep      = 'btn-primary mt-2';
        $this->sal_btbu     = $btbu->crBotao();
    }
}
