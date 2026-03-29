<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Events\Events;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AccessLogFilter implements FilterInterface
{
    private array $excludedPrefixes = [
        'assets/',
        'css/',
        'js/',
        'img/',
        'images/',
        'uploads/',
        'favicon.ico',
    ];

    private array $excludedExtensions = [
        'css','js','map','jpg','jpeg','png','gif','svg',
        'webp','ico','woff','woff2','ttf','eot','pdf','zip'
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($this->shouldSkip($request)) {
            return;
        }

        $session = session();

        if (!$session->has('usu_id')) {
            return;
        }

        $router = service('router');
        $logContext = service('logContext');

        $controller = class_basename($router->controllerName());
        $method     = $router->methodName();
        $params = $router->params();
        $id = $params[0] ?? null;
        $id = is_numeric($id) ? (int) $id : null;

        $titulo  = $logContext->get('titulo');
        $detalhe = $logContext->get('detalhe');

        $agent = $request->getUserAgent();
        $browser = $agent->getBrowser();
        $version = $agent->getVersion();
        $platform = $agent->getPlatform();

        Events::trigger('access:log', [
            'log_id_usuario' => $session->get('usu_id'),
            'log_usuario'    => $session->get('usu_nome'),
            'log_tela'       => $controller,
            'log_metodo'     => $method,
            'log_titulo'     => $titulo,
            'log_detalhe'    => $detalhe,
            'log_registro'   => $id,
            'log_ip'         => $request->getIPAddress(),
            'log_user_agent' => 'Sistema: '.$platform.'<br>Navegador: '.$browser.' - '.$version,
        ]);
    }


    private function shouldSkip(RequestInterface $request): bool
    {
        $path = trim($request->getUri()->getPath(), '/');

        foreach ($this->excludedPrefixes as $prefix) {
            if (str_starts_with($path, trim($prefix, '/'))) {
                return true;
            }
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, $this->excludedExtensions, true);
    }
}