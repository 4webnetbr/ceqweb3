<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\SoapSapiens;
use SoapClient;

class Cliente extends BaseController
{ 
    public $data = [];
    public $permissao = '';
    public $Tela;
    public $TelaLista;
    public $dicionario;
    public $logs;
    public $usuario;
    public $modulo;
    public $common;
    public $model_atual;

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
        $cliente = new SoapSapiens();

        $cliente->saldoEstoqueSapiens();
    }
}
