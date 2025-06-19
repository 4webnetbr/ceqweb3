<?php namespace App\Libraries;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class CommCeqweb implements MessageComponentInterface {
    public $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        log_message('info','cliente conectado ');
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $mensagem = json_decode($msg);
        log_message('info','Msg '.$mensagem->msg);
        $xmsg = $mensagem->msg;
        $numRecv = count($this->clients) - 1;
        foreach ($this->clients as $client) {
            if($xmsg === 'Ativo'){
                if ($from === $client) {
                    $client->send($msg);
                }
            } else if($xmsg === 'ok'){
            } else {
                log_message('info','Recebi Mensagem '.$xmsg);
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        log_message('info',"Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        log_message('info', "An error has occurred: {$e->getMessage()}");
        $conn->close();
    }
}