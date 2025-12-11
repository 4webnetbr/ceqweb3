<?php

use Workerman\Worker;
use Workerman\Lib\Timer;

require_once __DIR__ . '/../../vendor/autoload.php';

class CommWsCeqweb
{
    public $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onConnect($connection)
    {
        $this->clients->attach($connection);
        log_message('info', 'cliente conectado');
    }

    public function onMessage($connection, $msg)
    {
        $mensagem = json_decode($msg);
        $xmsg = $mensagem->msg ?? '';
        $tipo = $mensagem->tipo ?? 'Cliente';

        log_message('info', 'Msg recebida: ' . $xmsg);

        // Se a mensagem vier da função envia_msg_ws (tipo Servidor), é um broadcast geral
        if ($tipo === 'Servidor') {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
            log_message('info', 'Broadcast enviado para todos os clientes conectados');
            return;
        }

        // Se não for tipo "Servidor", segue lógica normal
        foreach ($this->clients as $client) {
            if ($xmsg === "Ativo") {
                if ($connection === $client) {
                    $client->send($msg);
                }
            } elseif ($xmsg === "ok") {
                // não faz nada
            } else {
                log_message('info', 'Replicando mensagem para cliente');
                $client->send($msg);
            }
        }
    }

    public function onClose($connection)
    {
        $this->clients->detach($connection);
        log_message('info', "Cliente desconectado: {$connection->id}");
    }
}

$socket = new CommWsCeqweb();

// Caminhos dos certificados (WSS)
$context = [
    'ssl' => [
        'local_cert'  => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.crt',
        'local_pk'    => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.key',
        'verify_peer' => false
    ]
];

// Cria servidor WSS na porta 8443
$ws = new Worker("websocket://0.0.0.0:8443", $context);
$ws->transport = 'ssl';

// Eventos
$ws->onConnect = fn($conn) => $socket->onConnect($conn);
$ws->onMessage = fn($conn, $msg) => $socket->onMessage($conn, $msg);
$ws->onClose   = fn($conn) => $socket->onClose($conn);

// Timer que envia “Ativo” a cada 15 segundos
$ws->onWorkerStart = function () use ($socket) {
    Timer::add(15, function () use ($socket) {
        $msg = [
            'msg'       => 'Ativo',
            'controler' => 'Servidor',
            'tipo'      => 'Servidor Ativo',
            'usuario'   => '',
            'id'        => '',
        ];

        foreach ($socket->clients as $client) {
            echo "Connection {$client->id} check\n";
            $client->send(json_encode($msg));
        }
    });

    log_message('info', 'servidor configurado');
    log_message('info', 'servidor rodando na porta 8443 (WSS)');
};

// Inicia o servidor
Worker::runAll();
