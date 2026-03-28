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
        $controller = class_basename($router->controllerName());
        $method     = $router->methodName();

        Events::trigger('access:log', [
            'log_id_usuario' => $session->get('usu_id'),
            'log_usuario'    => $session->get('usu_nome'),
            'log_tela'       => $controller,
            'log_metodo'     => $method,
            'log_registro'   => $this->extractRegistro($request),
            'log_ip'         => $request->getIPAddress(),
            'log_user_agent' => $request->getUserAgent()
        ]);
    }

    private function extractRegistro(\CodeIgniter\HTTP\RequestInterface $request): array|null
    {
        $registro = [];

        // 1. Parâmetros da URL (ex: /cliente/edit/15)
        $segments = $request->getUri()->getSegments();

        if (!empty($segments)) {
            $last = end($segments);

            // se for número, assume como ID
            if (is_numeric($last)) {
                $registro['id'] = (int) $last;
            }
        }

        // 2. Query string (?status=ativo)
        $query = $request->getGet();

        if (!empty($query)) {
            $registro['query'] = $query;
        }

        // 3. Body (POST/PUT)
        if (in_array($request->getMethod(), ['post', 'put'], true)) {

            $post = $request->getPost();

            if (!empty($post)) {

                // 🔐 remove campos sensíveis
                unset($post['senha'], $post['password']);

                $registro['body'] = $post;
            }
        }

        return !empty($registro) ? $registro : null;
    }

    private function shouldSkip(RequestInterface $request): bool
    {
        // if ($request->isAJAX()) {
        //     return true;
        // }

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