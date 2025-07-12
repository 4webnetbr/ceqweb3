<?php

namespace App\Log\Handlers;

use CodeIgniter\Log\Handlers\HandlerInterface;
use Config\Services;
use Throwable;

class LogEmailThrottleHandler implements HandlerInterface
{
    protected array $config;
    protected string $emailTo;
    protected string $dateFormat = 'Y-m-d H:i:s';

    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->emailTo = $config['to'] ?? 'admin@example.com';
    }

    public function handle($level, $message): bool
    {
        try {
            $level = strtoupper($level);
            $date  = date($this->dateFormat);
            $text  = $message instanceof Throwable
                ? $message->__toString()
                : (string) $message;

            // Protege o acesso aos serviços
            try {
                $request = Services::request();
                log_message('info', 'Request '.json_encode($request));
                $session = Services::session();
                log_message('info', 'Session '.json_encode($session));
                $router  = Services::router();
                log_message('info', 'Router '.json_encode($router));

                $ip         = $request->getIPAddress() ?? 'n/d';
                $userAgent  = $request->getUserAgent() ?? 'n/d';
                $uri        = $request->getUri();
                $path       = $uri->getPath() ?? 'n/d';
                $module     = $uri->getSegment(1) ?? 'n/d';
                $controller = method_exists($router, 'controllerName') ? $router->controllerName() : 'n/d';
                $method     = method_exists($router, 'methodName') ? $router->methodName() : 'n/d';

                $usuId      = $session->get('usu_id') ?? 'n/d';
                $usuNome    = $session->get('usu_nome') ?? 'n/d';
            } catch (Throwable $e) {
                // fallback em caso de erro de inicialização de serviços
                $ip = $userAgent = $path = $module = $controller = $method = $usuId = $usuNome = 'n/d';
            }

            // Throttle: bloqueia logs repetidos
            $cacheKey    = 'log_email_hash_' . md5($level . '_' . $ip . '_' . $module);
            $currentHash = md5($level . $text);
            $cache       = Services::cache();
            $lastHash    = $cache->get($cacheKey);

            if ($currentHash === $lastHash) {
                return false;
            }

            $cache->save($cacheKey, $currentHash, 600); // 10 minutos

            // Carrega config de email explicitamente
            $emailConfig = config('Email');
            $email = Services::email($emailConfig, false);

            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
            $email->setTo($this->emailTo);
            $email->setSubject("[$level][$module][$ip] Log automático em $date");

            $body = <<<HTML
<b>Nível:</b> $level<br>
<b>Data:</b> $date<br>
<b>IP:</b> $ip<br>
<b>Módulo:</b> $module<br>
<b>URI:</b> $path<br>
<b>Controller:</b> $controller<br>
<b>Método:</b> $method<br>
<b>User-Agent:</b> $userAgent<br><br>

<b>Usuário logado:</b><br>
ID: $usuId<br>
Nome: $usuNome<br><br>

<b>Mensagem do log:</b><br>
<pre>$text</pre>
HTML;

            $email->setMessage($body);

            if (! $email->send()) {
                file_put_contents(WRITEPATH . 'logs/email-handler-error.log', $email->printDebugger(['headers', 'subject', 'body']) . PHP_EOL, FILE_APPEND);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            file_put_contents(WRITEPATH . 'logs/email-handler-error.log', $e->__toString() . PHP_EOL, FILE_APPEND);
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
