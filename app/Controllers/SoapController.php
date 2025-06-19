<?php

namespace App\Controllers;

use App\Controllers\Estoque\Deposito;
use App\Controllers\Notifica;
use App\Models\Estoqu\EstoquDepositoModel;
use CodeIgniter\Controller;
use \SoapServer;

class SoapController extends Controller
{
    public function index()
    {
        // Obter solicitação SOAP
        $request = file_get_contents('php://input');

        // Processar solicitação SOAP
        $response = $this->processSoapRequest($request);

        // Enviar resposta SOAP
        header('Content-Type: text/xml');
        echo $response;
    }

    private function processSoapRequest($request)
    {
        $server = new SoapServer('webservice/soap.wsdl');

        // Definir métodos e classes do servidor
        $server->addFunction("Ws/WsCeqweb/Deposito/$1/$2");

        // Processar solicitação SOAP
        $response = $server->handle($request);

        return $response;
    }

    public function depositoMethod($tipo, $idDeposito)
    {
        // se o tipo for 'I' // Inclusão
        if($tipo == 'I' || $tipo == 'A'){
            // Chama o método Integra da Classe Depósito para atualizar a tabela de depósitos local
                $cdeposito = new Deposito();
                $cdeposito->integra();
            // Cria uma notificação avisando que foi incluído um novo depósito
                //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Depósito 
                //com o ID do Depósito incluído
                if($tipo == 'I'){
                    $msgsocket  = "Foi Incluído um novo Depósito";
                } else {
                    $dep = new EstoquDepositoModel();
                    $depos = $dep->getDeposito($idDeposito);
                    if($depos){
                        $deposito = $depos[0]['dep_desDep'];
                        $msgsocket  = "O Depósito ".$deposito." foi alterado!";
                    } else {
                        $msgsocket  = "Foi Incluído um novo Depósito";
                    }
                }
                $notif = new Notifica();
                $notif->gravaNotifica('Deposito',0,$idDeposito, $msgsocket, $tipo);
            } else if($tipo == 'E'){
                $dep = new EstoquDepositoModel();
                $depos = $dep->getDeposito($idDeposito);
                if($depos){
                    $deposito = $depos[0]['dep_desDep'];
                    $msgsocket  = "O Depósito ".$deposito." foi excluído no Sapiens!";
                    $notif = new Notifica();
                    // Cria uma notificação avisando que foi incluído um novo depósito
                        //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Depósito 
                        //com o ID do Depósito incluído
                    $notif->gravaNotifica('Deposito',0,$idDeposito, $msgsocket, $tipo);
                }
            }
        }

    }
