<?php

namespace App\Log\Handlers;

use Throwable;
use CodeIgniter\Email\Email;
use Config\Cache as CacheConfig;
use Config\Email as EmailConfig;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

class LogEmailThrottleHandler implements HandlerInterface
{
    protected array $config;
    protected string $emailTo;
    protected string $dateFormat = 'd-m-Y H:i:s';

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->emailTo = $config['to'] ?? 'douglasjf1973@gmail.com';
    }

    public function handle($level, $message): bool
    {
        try {
            $level = strtoupper($level);
            $dataatual  = date($this->dateFormat);

            // Captura mensagem do log
            $text = $message instanceof Throwable
                ? $message->__toString()
                : (string) $message;

            // Informações básicas
            $ip        = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'indefinido';
            $uri       = $_SERVER['REQUEST_URI'] ?? 'indefinido';
            $method    = $_SERVER['REQUEST_METHOD'] ?? 'indefinido';
            $module    = explode('/', trim($uri, '/'))[0] ?? 'indefinido';
            $baseURL = config('App')->baseURL;

            // Dados do usuário
            $usuId   = $_SESSION['usu_id'] ?? 'não identificado';
            $usuNome = $_SESSION['usu_nome'] ?? 'não identificado';

            // Cache (evitar repetições)
            // Instancia o handler de cache diretamente
            $cacheConfig = new CacheConfig();
            $cache = new FileHandler($cacheConfig);

            // Criação da chave e verificação do hash
            $cacheKey = 'log_email_hash_' . md5($level . '_' . $ip . '_' . $module);
            $currentHash = md5($level . $text);
            $lastHash = $cache->get($cacheKey);

            if ($currentHash === $lastHash) {
                // log_message('info', 'Erro Repetido ' . $text);
                return false; // Log repetido, ignora envio
            }

            // Salva o novo hash com tempo de expiração
            $cache->save($cacheKey, $currentHash, 600);
            // Envio do e-mail
            $emailConfig = new EmailConfig();
            // log_message('info', 'EmailConfig '.json_encode($emailConfig));
            $email = new Email($emailConfig);
            // log_message('info', 'Email '.json_encode($email));

            $email->setTo($this->emailTo);
            $email->setSubject("DevCeqWeb3 Log automático em $dataatual - [$level]");

            $body = <<<HTML
<b>Nível:</b> $level<br>
<b>Data:</b> $dataatual<br>
<b>IP:</b> $ip<br>
<b>Controler:</b> $module<br>
<b>URI:</b> $uri<br>
<b>Método:</b> $method<br>
<b>Ambiente:</b>$baseURL<br><br>

<b>Usuário logado:</b><br>
ID: $usuId<br>
Nome: $usuNome<br><br>

<b>Mensagem do log:</b><br>
<pre>$text</pre>
HTML;

            $email->setMessage($body);

            /** Desabilitado o envio de email no Dev */
            // $enviar = $email->send();

            // if (!$enviar) {
            //     file_put_contents(WRITEPATH . 'logs/email-handler-error.log', $email->printDebugger(['headers', 'subject', 'body']) . PHP_EOL, FILE_APPEND);
            //     return false;
            // }
            // log_message('info', 'Enviar '.json_encode($enviar));
            return true;
        } catch (Throwable $e) {
            file_put_contents(WRITEPATH . 'logs/email-handler-exception.log', $e->__toString() . PHP_EOL, FILE_APPEND);
            return false;
        }
    }

    public function canHandle($level): bool
    {
        return in_array(strtolower($level), array_map('strtolower', $this->config['handles'] ?? []));
    }

    public function setDateFormat(string $format): void
    {
        $this->dateFormat = $format;
    }
}
