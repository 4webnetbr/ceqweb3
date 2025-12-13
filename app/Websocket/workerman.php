<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Config\Logger;
use Workerman\Worker;
use Workerman\Timer;

// -----------------------------------------------------------------------------
// 1. Carrega estrutura mínima do CodeIgniter
// -----------------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/codeigniter4/framework/system/Common.php';
require_once __DIR__ . '/../../vendor/codeigniter4/framework/system/CodeIgniter.php';

define('ENVIRONMENT', 'development');
define('CI_DEBUG', ENVIRONMENT !== 'production');
define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

try {
    $logger = new Logger();
} catch (\Throwable $e) {
    error_log("Erro ao iniciar logger: " . $e->getMessage());
}

// -----------------------------------------------------------------------------
// 2. Classe do WebSocket
// -----------------------------------------------------------------------------
class CommWsCeqweb
{
    public $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onConnect($connection)
    {
        try {
            $this->clients->attach($connection);
            log_message('info', 'WS: cliente conectado');
        } catch (\Throwable $e) {
            error_log("Erro onConnect: " . $e->getMessage());
        }
    }

    public function onMessage($connection, $msg)
    {
        try {
            $mensagem = json_decode($msg);
            $xmsg = $mensagem->msg ?? '';
            $tipo = $mensagem->tipo ?? 'Cliente';

            log_message('info', 'WS: Msg recebida -> ' . $xmsg);

            // Mensagem interna do servidor PHP (via envia_msg_ws)
            if ($tipo === 'Servidor') {
                foreach ($this->clients as $client) {
                    $client->send($msg);
                }
                log_message('info', 'WS: Broadcast enviado para todos os clientes');
                return;
            }

            foreach ($this->clients as $client) {
                if ($xmsg === "Ativo") {
                    if ($connection === $client) {
                        $client->send($msg);
                    }
                } elseif ($xmsg === "ok") {
                    // Ignora resposta keepalive
                } else {
                    $client->send($msg);
                    log_message('info', 'WS: Replicando mensagem para cliente');
                }
            }
        } catch (\Throwable $e) {
            error_log("Erro onMessage: " . $e->getMessage());
        }
    }

    public function onClose($connection)
    {
        try {
            $this->clients->detach($connection);
            log_message('info', "WS: Cliente desconectado: {$connection->id}");
        } catch (\Throwable $e) {
            error_log("Erro onClose: " . $e->getMessage());
        }
    }
}

// -----------------------------------------------------------------------------
// 3. Inicializa servidor Workerman com SSL
// -----------------------------------------------------------------------------
$socket = new CommWsCeqweb();

$context = [
    'ssl' => [
        'local_cert'  => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.crt',
        'local_pk'    => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.key',
        'verify_peer' => false
    ]
];

$ws = new Worker("websocket://0.0.0.0:8443/ws", $context);
$ws->transport = 'ssl';

// -----------------------------------------------------------------------------
// 4. Eventos Workerman com tratamento
// -----------------------------------------------------------------------------
$ws->onConnect = function ($conn) use ($socket) {
    try {
        $socket->onConnect($conn);
    } catch (\Throwable $e) {
        error_log("Erro no onConnect externo: " . $e->getMessage());
    }
};

$ws->onMessage = function ($conn, $msg) use ($socket) {
    try {
        $socket->onMessage($conn, $msg);
    } catch (\Throwable $e) {
        error_log("Erro no onMessage externo: " . $e->getMessage());
    }
};

$ws->onClose = function ($conn) use ($socket) {
    try {
        $socket->onClose($conn);
    } catch (\Throwable $e) {
        error_log("Erro no onClose externo: " . $e->getMessage());
    }
};

// -----------------------------------------------------------------------------
// 5. KeepAlive: envia "Servidor Ativo" a cada 15 segundos
// -----------------------------------------------------------------------------
$ws->onWorkerStart = function () use ($socket) {
    try {
        Timer::add(15, function () use ($socket) {
            $msg = [
                'msg'       => 'Ativo',
                'controler' => 'Servidor',
                'tipo'      => 'Servidor Ativo',
                'usuario'   => '',
                'id'        => '',
            ];

            foreach ($socket->clients as $client) {
                try {
                    $client->send(json_encode($msg));
                    log_message('info', "WS: KeepAlive enviado para {$client->id}");
                } catch (\Throwable $e) {
                    error_log("Erro ao enviar KeepAlive: " . $e->getMessage());
                }
            }
        });

        log_message('info', 'WS: Servidor configurado com sucesso');
        log_message('info', 'WS: Rodando na porta 8443 (WSS)');
    } catch (\Throwable $e) {
        error_log("Erro no onWorkerStart: " . $e->getMessage());
    }
};

// -----------------------------------------------------------------------------
// 6. Inicia o servidor
// -----------------------------------------------------------------------------
try {
    Worker::runAll();
} catch (\Throwable $e) {
    error_log("Erro fatal no Workerman: " . $e->getMessage());
}
