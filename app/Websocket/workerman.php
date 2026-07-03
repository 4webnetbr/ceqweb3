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

require_once APPPATH . 'Config/Constants.php';

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
    public $tabs     = [];
    public $usuarios = []; // mapa usuarioId → [conexões]

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onConnect($connection)
    {
        $this->clients->attach($connection);
        log_message('info', 'WS: cliente conectado -> ' . $connection->id);
    }

    public function onMessage($connection, $msg)
    {
        $mensagem = json_decode($msg);
        $xmsg     = $mensagem->msg     ?? '';
        $tipo     = $mensagem->tipo    ?? 'Cliente';
        $usuId    = (int) ($mensagem->usuario ?? 0);

        // ----------------------------------------------------------------
        // Registro de aba — sem roteamento de mensagem
        // ----------------------------------------------------------------
        if ($tipo === 'REGISTER_TAB') {
            $tabId = $xmsg;

            $this->tabs[$tabId]    = $connection;
            $connection->tabId     = $tabId;
            $connection->usuarioId = $usuId;

            if ($usuId !== 0) {
                $this->usuarios[$usuId][] = $connection;
                log_message('info', "WS: Aba registrada -> $tabId (usuário $usuId)");
            } else {
                log_message('info', "WS: Aba registrada -> $tabId (sem usuário)");
            }

            return;
        }

        // ----------------------------------------------------------------
        // Roteamento único:
        //   usuId != 0  → envia apenas para as conexões daquele usuário
        //   usuId == 0  → broadcast para todos
        // ----------------------------------------------------------------
        if ($usuId !== 0) {
            if (isset($this->usuarios[$usuId])) {
                foreach ($this->usuarios[$usuId] as $conn) {
                    $conn->send($msg);
                }
                log_message('info', "WS: [$tipo] enviado ao usuário $usuId");
            } else {
                log_message('info', "WS: [$tipo] usuário $usuId não tem conexões ativas");
            }
        } else {
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
            log_message('info', "WS: [$tipo] broadcast enviado a todos os clientes");
        }
    }


    public function onClose($connection)
    {
        $this->clients->detach($connection);

        if (isset($connection->tabId)) {
            unset($this->tabs[$connection->tabId]);
        }

        if (isset($connection->usuarioId)) {
            $usuId = $connection->usuarioId;

            if (isset($this->usuarios[$usuId])) {
                $this->usuarios[$usuId] = array_values(array_filter(
                    $this->usuarios[$usuId],
                    fn($conn) => $conn !== $connection
                ));

                if (empty($this->usuarios[$usuId])) {
                    unset($this->usuarios[$usuId]);
                }
            }
        }

        log_message('info', "WS: Cliente desconectado -> " . $connection->id);
    }

    // Derruba aba específica (expiração de sessão via Redis)
    public function killTab($tabId)
    {
        if (! isset($this->tabs[$tabId])) {
            return;
        }

        $connection = $this->tabs[$tabId];

        $connection->send(json_encode([
            'tipo'  => 'SESSION_EXPIRED',
            'tabId' => $tabId,
            'msg'   => "Sessão Expirou em: " . date('H:i:s'),
        ]));

        unset($this->tabs[$tabId]);

        log_message('info', "WS: Aba derrubada -> $tabId");
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
$ws->onConnect = fn($conn)       => $socket->onConnect($conn);
$ws->onMessage = fn($conn, $msg) => $socket->onMessage($conn, $msg);
$ws->onClose   = fn($conn)       => $socket->onClose($conn);

// -----------------------------------------------------------------------------
// Worker Start
// -----------------------------------------------------------------------------
$ws->onWorkerStart = function () use ($socket) {

    static $redis;

    if (! $redis) {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
    }

    // KeepAlive — ping a todos os clientes a cada 15s
    Timer::add(15, function () use ($socket) {
        $msg = json_encode([
            'msg'  => 'Ativo',
            'tipo' => 'Servidor Ativo',
        ]);

        foreach ($socket->clients as $client) {
            $client->send($msg);
        }
    });

    // Verificação de expiração de sessão via Redis a cada 5s
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
// Inicia o servidor
// -----------------------------------------------------------------------------
try {
    Worker::runAll();
} catch (\Throwable $e) {
    error_log("Erro fatal no Workerman: " . $e->getMessage());
}
