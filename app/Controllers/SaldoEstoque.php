<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;

class SaldoEstoque extends BaseController
{
    public $data = [];
    public $permissao = '';

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

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
        $this->defCampos([]);

        $secao[0] = 'Buscar';
        $campos[0][0] = $this->sal_depo;
        $campos[0][1] = $this->sal_btbu;

        $colunas = ['Depósito','CodErp','Produto', 'Lote','Validade','Saldo','Und'];

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
        $estoques = [];
        // $produt = new SoapSapiens();
        if (trim($vars['codDep']) != '') {
            $busca = new BuscasSapiens();
            $saldoest = $busca->buscaEstoqueDeposito(trim($vars['codDep']));
            // debug($depositos, true);

            $total = 0;
            for ($d = 0; $d < sizeof($saldoest); $d++) {
                $dep = $saldoest[$d];
                // debug($dep, true);
                if ($dep->quantidadeEstoque != '0') {
                    $lote = ['codDep'   => $dep->codigoDeposito,
                            'Coderp'    => $dep->codigoProduto,
                            'DescProduto' => $dep->descricaoProduto,
                            'Produto'   => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
                            'lote'      => $dep->codigoLote,
                            'validade'  => $dep->validade,
                            'validadeord' => data_db($dep->validade),
                            'saldo'     => $dep->quantidadeEstoque,
                            'und'       => $dep->unidmedida,
                        ];
                    array_push($estoques, $lote);
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
    public function defCampos($dados, $pos = 0)
    {
        $busca = new BuscasSapiens();
        $r_deps = $busca->buscaDepositos();
        // debug($r_deps);
        $depos = array_column($r_deps, 'codDescricao', 'codDep');
        // debug($depos, true);
        $depo               =  new MyCampo();
        $depo->id           = 'codDep';
        $depo->nome         = 'codDep';
        $depo->label        = 'Depósito';
        $depo->obrigatorio  = true;
        $depo->size         = 50;
        $depo->largura      = 50;
        $depo->valor        = '';
        $depo->dispForm     = '2col';
        $depo->opcoes       = $depos;
        $depo->selecionado  = 'GER';
        $this->sal_depo     = $depo->crSelect();

        $code               =  new MyCampo();
        $code->id           = 'codPro';
        $code->nome         = 'codPro';
        $code->label        = 'Código ERP';
        $code->obrigatorio  = true;
        $code->size         = 15;
        $code->valor        = '';
        $code->dispForm     = '2col';
        $this->sal_code     = $code->crInput();

        $lote               =  new MyCampo();
        $lote->id           = 'codLot';
        $lote->nome         = 'codLot';
        $lote->label        = 'Lote';
        $lote->size         = 15;
        $lote->valor        = '';
        $lote->dispForm     = '3col';
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
