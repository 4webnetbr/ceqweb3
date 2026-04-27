<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use Config\Logger;
use Workerman\Timer;
use Workerman\Worker;

// -----------------------------------------------------------------------------
// Bootstrap mínimo do CodeIgniter
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
// Classe WebSocket
// -----------------------------------------------------------------------------
class CommWsCeqweb
{
    public $clients;
    public $tabs = []; // 🔥 precisa ser PUBLIC

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onConnect($connection)
    {
        $this->clients->attach($connection);
        log_message('info', 'WS: cliente conectado');
    }

    public function onMessage($connection, $msg)
    {
        $mensagem = json_decode($msg);
        $xmsg     = $mensagem->msg ?? '';
        $tipo     = $mensagem->tipo ?? 'Cliente';

        // 🔥 Registro da aba
        if ($tipo === 'REGISTER_TAB') {
            $tabId = $xmsg;

            $this->tabs[$tabId] = $connection;
            $connection->tabId  = $tabId;

            log_message('info', "WS: Aba registrada -> $tabId");
            return;
        }

        // Broadcast interno
        if ($tipo === 'Servidor') {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
            return;
        }

        // Replicação padrão
        foreach ($this->clients as $client) {
            if ($xmsg === "Ativo") {
                if ($connection === $client) {
                    $client->send($msg);
                }
            } elseif ($xmsg !== "ok") {
                $client->send($msg);
            }
        }
    }

    public function onClose($connection)
    {
        $this->clients->detach($connection);

        if (isset($connection->tabId)) {
            unset($this->tabs[$connection->tabId]);
        }

        log_message('info', "WS: Cliente desconectado");
    }

    // 🔥 Derruba aba específica
    public function killTab($tabId)
    {
        if (isset($this->tabs[$tabId])) {

            $this->tabs[$tabId]->send(json_encode([
                'tipo'  => 'SESSION_EXPIRED',
                'tabId' => $tabId,
            ]));

            unset($this->tabs[$tabId]);

            log_message('info', "WS: Aba derrubada -> $tabId");
        }
    }
}

// -----------------------------------------------------------------------------
// Inicialização
// -----------------------------------------------------------------------------
$socket = new CommWsCeqweb();

$context = [
    'ssl' => [
        'local_cert'  => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.crt',
        'local_pk'    => '/etc/nginx/ssl/ceqweb3.ceqnep.com.br/2933563/server.key',
        'verify_peer' => false,
    ],
];

$ws            = new Worker("websocket://0.0.0.0:8443/ws", $context);
$ws->transport = 'ssl';

// -----------------------------------------------------------------------------
// Eventos
// -----------------------------------------------------------------------------
$ws->onConnect = fn($conn) => $socket->onConnect($conn);
$ws->onMessage = fn($conn, $msg) => $socket->onMessage($conn, $msg);
$ws->onClose   = fn($conn) => $socket->onClose($conn);

// -----------------------------------------------------------------------------
// Worker Start
// -----------------------------------------------------------------------------
$ws->onWorkerStart = function () use ($socket) {

    // 🔥 Redis (reutilizado)
    static $redis;

    if (! $redis) {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
    }

    // 🔁 KeepAlive
    Timer::add(15, function () use ($socket) {

        $msg = [
            'msg'  => 'Ativo',
            'tipo' => 'Servidor Ativo',
        ];

        foreach ($socket->clients as $client) {
            $client->send(json_encode($msg));
        }

    });

    // 🔥 Verificação de expiração (CORE DO SISTEMA)
    Timer::add(5, function () use ($socket, $redis) {

        foreach ($socket->tabs as $tabId => $connection) {

            if (! $redis->exists("tab:$tabId")) {

                log_message('info', "WS: Expirou -> $tabId");

                $socket->killTab($tabId);
            }
        }

    });

    log_message('info', 'WS: Servidor iniciado com Redis + Timer');
};

// -----------------------------------------------------------------------------
// 6. Inicia o servidor
// -----------------------------------------------------------------------------
try {
    Worker::runAll();
} catch (\Throwable $e) {
    error_log("Erro fatal no Workerman: " . $e->getMessage());
}
