<?php namespace App\Controllers\Ws;

use App\Libraries\SoapServerLibrary;
use CodeIgniter\Controller;

class Soap extends Controller
{
    public function index()
    {
        // Crie e configure o servidor SOAP
        $soapServer = new SoapServerLibrary();
        $soapServer->start();
    }
}