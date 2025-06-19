<?php

namespace App\Libraries;

class SoapClient
{

    function clientesSapiens() {
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_cad_clientes?wsdl');
        #Operação a ser executada
        $function = 'obterCliente';
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'usuario',
                        'password'        => 'senha',
                        'encryption'      => 0,
                        'parameters'      => array(
                        'codigoEmpresa'   => 9997,
                        'codigoFilial'    => 1,
                        'codigoCliente'   => 6
                        ));
        #Sobrescrevendo endpoint do serviço
        $arguments = array('obterCliente' => array( $parameters));
                                
        $options = array('location' => 'http://services.senior.com.br');
        
        #Chamada do serviço
        $result = $client->__soapCall($function, $parameters);
        
        echo 'Response: ';
        print_r($result->nomeCliente);

    }
}

?>