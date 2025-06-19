<?php
use App\Libraries\CommCeqweb;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$socket = new CommCeqweb();
$server = IoServer::factory( 
	new HttpServer(
		new WsServer(
			$socket
		)
	),
	8443
	// HTTPS: 2053 2083 2087 2096 8443
);
$server->loop->addPeriodicTimer(15, function () use ($socket) {
	$msg['msg'] = 'Ativo';
	$msg['controler'] = 'Servidor';
	$msg['tipo'] = 'Servidor Ativo';
	$msg['usuario'] = '';
	$msg['id'] = '';
	foreach($socket->clients as $client) {
        echo "Connection ".$client->resourceId." check\n";
        $client->send(json_encode($msg));		
    }
});
log_message('info','servidor configurado');
log_message('info','servidor rodando na porta 8443');
$server->run();

