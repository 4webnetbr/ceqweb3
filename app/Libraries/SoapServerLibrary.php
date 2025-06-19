<?php namespace App\Libraries;

use CodeIgniter\HTTP\Response;
use \SoapServer;

class SoapServerLibrary
{
    private $soapServer;

    public function __construct()
    {
        // Carregue a configuração do SOAP
        $config = config('soap');

        // Crie o servidor SOAP
        $this->soapServer = new SoapServer($config['wsdl'], $config['options']);
    }

    public function start()
    {
        // Adicione métodos e classes ao servidor SOAP
        $this->soapServer->addFunction('deposito'); // Exemplo de função SOAP
        $this->soapServer->setClass('App\Controllers\Ws\WsCeqweb'); // Exemplo de classe SOAP

        // Inicie o servidor SOAP
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContentType('text/xml');
        $response->setBody($this->soapServer->handle());
        return $response;
    }
}