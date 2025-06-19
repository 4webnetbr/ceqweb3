<?php namespace App\Controllers\Ws;

use App\Libraries\SoapServerLibrary;
use CodeIgniter\Controller;

class SoapController extends Controller
{
    public function index()
    {
        // Crie e configure o servidor SOAP
        $soapServer = new SoapServerLibrary();
        $soapServer->start();
    }
}