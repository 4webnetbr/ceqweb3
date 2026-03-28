<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigUsuarioModel;

class CfgLoguser extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $periodo;
    public $usuario;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->usuario   = new ConfigUsuarioModel();

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
        $fields = $this->defCampos();
        $secao[0] = 'Buscar';
        $campos[0][] = $fields->log_periodo;
        $campos[0][] = $fields->log_usuario;
        $campos[0][] = $fields->log_btbu;
    
        $colunas = ['Data', 'Tela', 'Método', 'Descrição', 'IP', 'Acesso'];
    
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
        // $vars = $_REQUEST;
        // $dep = trim($vars['codDep']);
        // $pro = trim($vars['codPro']);
        // if ($pro == '') {
        //     $pro = false;
        // }

        // $estoques = [];
        // // $produt = new SoapSapiens();
        // if (trim($vars['codDep']) != '') {
        //     $busca = new BuscasSapiens();
        //     $saldoest = $busca->buscaEstoqueDeposito($dep, $pro);
        //     if (is_object($saldoest)) {
        //         // Converte objeto em array
        //         $dep = $saldoest;
        //         // debug($dep);
        //         if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
        //             ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
        //         ) {
        //             if ($this->produto->getProdutoCod($dep->codigoProduto)) {
        //                 $lote = [
        //                     'codDep'      => $dep->codigoDeposito,
        //                     'Coderp'      => $dep->codigoProduto,
        //                     'DescProduto' => $dep->descricaoProduto,
        //                     'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
        //                     'lote'        => $dep->codigoLote,
        //                     'validade'    => $dep->validade,
        //                     'validadeord' => data_db($dep->validade),
        //                     'entrada'     => $dep->entrada,
        //                     'entradaord'  => data_db($dep->entrada),
        //                     'und'         => $dep->unidmedida,
        //                 ];
        //                 if ($dep->codigoLote == 'N/A') {
        //                     $lote['saldo'] = $dep->estoqueDeposito;
        //                     $lote['validade']    = '';
        //                     $lote['validadeord'] = '';
        //                     $lote['entrada']     = '';
        //                     $lote['entradaord']  = '';
        //                 } else {
        //                     $lote['saldo'] = $dep->quantidadeEstoque;
        //                 }
        //                 array_push($estoques, $lote);
        //             }
        //         }
        //     } else {
        //         // debug($saldoest, true);

        //         $total = 0;
        //         for ($d = 0; $d < sizeof($saldoest); $d++) {
        //             $dep = $saldoest[$d];
        //             // debug($dep);
        //             if ($this->produto->getProdutoCod($dep->codigoProduto)) {
        //                 if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
        //                     ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
        //                 ) {
        //                     // debug($dep, true);
        //                     $lote = [
        //                         'codDep'      => $dep->codigoDeposito,
        //                         'Coderp'      => $dep->codigoProduto,
        //                         'DescProduto' => $dep->descricaoProduto,
        //                         'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
        //                         'lote'        => $dep->codigoLote,
        //                         'validade'    => $dep->validade,
        //                         'validadeord' => data_db($dep->validade),
        //                         'entrada'     => $dep->entrada,
        //                         'entradaord'  => data_db($dep->entrada),
        //                         'und'         => $dep->unidmedida,
        //                     ];
        //                     if ($dep->codigoLote == 'N/A') {
        //                         $lote['saldo'] = $dep->estoqueDeposito;
        //                         $lote['validade']    = '';
        //                         $lote['validadeord'] = '';
        //                         $lote['entrada']     = '';
        //                         $lote['entradaord']  = '';
        //                     } else {
        //                         $lote['saldo'] = $dep->quantidadeEstoque;
        //                     }
        //                     array_push($estoques, $lote);
        //                 }
        //             }
        //         }
        //     }
        // }
        // echo json_encode($estoques);
    }

    public function defCampos()
    {
        $ret = new \stdClass();
        // USUÁRIOS
        $config = [];
        $config['Label']       = 'Usuário';
        $config['DispForm']    = 'col-5';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        
        $ret->log_usuario = criaSelectRelativo(
            'cfg_usuario',
            'usu_id',
            'usu_nome',
            null,
            1,
            'CfgLoguser',
            [],
            $config
        );

        // PERÍODO
        $peri               = new MyCampo();
        $peri->objeto       = 'input';
        $peri->id           = 'periodo';
        $peri->nome         = 'periodo';
        $peri->label        = 'Período';
        $peri->obrigatorio  = true;
        $peri->size         = 30;
        $peri->valor        = '';
        $peri->dispForm     = 'col-3';

        $ret->log_periodo = $peri->crDaterange();


        // BOTÃO BUSCAR
        $btbu               = new MyCampo();
        $btbu->id           = 'btBuscar';
        $btbu->nome         = 'btBuscar';
        $btbu->tipo         = 'button';
        $btbu->label        = 'Buscar';
        $btbu->dispForm     = '2col';
        $btbu->funcChan     = 'buscaLogUser()';
        $btbu->i_cone       = '<i class="fa-solid fa-magnifying-glass"></i> Buscar Log';
        $btbu->place        = 'Buscar Log';
        $btbu->classep      = 'btn-primary mt-2';

        $ret->log_btbu = $btbu->crBotao();

        return $ret;
    }

}
