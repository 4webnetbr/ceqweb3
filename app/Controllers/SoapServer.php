<?php

namespace App\Controllers;

class SoapServer extends BaseController
{
    public function index()
    {
        // Criar um novo servidor SOAP
        $server = new \SoapServer(null, array('uri' => 'http://dev.ceqnep.com.br/soapserver/'));

        // Definir os métodos do servidor
        $server->addFunction('Ws/WsCeqweb.Deposito', array('tipo' => 'string','idDeposito' => 'int'), array('return' => 'string'));
        // $server->addFunction('outraFuncao', array('param2' => 'int'), array('return' => 'bool'));

        // Processar solicitações SOAP
        $server->handle();
    }
}
