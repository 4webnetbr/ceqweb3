<?php
// soap_proxy.php
// Microserviço para consumir o SOAP da Senior e retornar JSON

header('Content-Type: application/json');

try {
    // Dados de autenticação e parâmetros
    $usuario  = 'Smart2';
    $senha    = 'omyjano1';
    $input = json_decode(file_get_contents('php://input'), true);
    $numPeds = $input['pedidos'] ?? ['12345'];

    // $numPeds = [
    //     'numPed' => 44416299,
    //     'numPed' => 44416300,
    //     'numPed' => 44416301,
    //     'numPed' => 44416303
    // ];

    $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_ven_pedidos?wsdl');
    #Operação a ser executada
    $function = 'ExportarPedidos';
    #Montando o payload de requisição
    $parameters = array(
        'user'       => $usuario,
        'password'   => $senha,
        'encryption' => 0,
        'parameters' => [
            'exportacaoPadrao' => array( 
                'codEmp'   => array(
                    'codEmp'   => 1
                )
            ),
            'exportacaoPadrao' => array(
                'codFil'   => array(
                    'codFil'   => 1
                )
            ),
            'exportacaoPadrao' => [
                'numPed' => $numPeds
            ]
        ]
    );

    #Sobrescrevendo endpoint do serviço
    $arguments = array('ExportarPedidos' => array($parameters));
    // echo "<pre>";
    // print_r($parameters);
    // echo "</pre>";

    $options = array('location' => 'http://services.senior.com.br');

    #Chamada do serviço
    $result = $client->__soapCall($function, $parameters);

    // Converte para JSON
    echo json_encode([
        'status' => 'ok',
        'dados' => $result
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
