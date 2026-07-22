<?php

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

if (!function_exists('api_request')) {
    /**
     * Faz uma requisição GET ou POST e retorna o JSON como array
     *
     * @param string $url         URL da API
     * @param array  $params      Parâmetros para query (GET) ou corpo (POST)
     * @param string $method      'get' ou 'post'
     * @param array  $headers     Headers adicionais (opcional)
     * @param int    $timeout     Tempo máximo em segundos (opcional)
     * @param bool   $httpErrors  Se false, não lança exceção para status HTTP de erro (opcional)
     *
     * @return array|null
     */
    function api_request(
        string $url,
        array $params = [],
        string $method = 'get',
        array $headers = ['Accept' => 'application/json'],
        int $timeout = 10,
        bool $httpErrors = true
    ): ?array {
        $client = Services::curlrequest([
            'timeout'     => $timeout,
            'http_errors' => $httpErrors,
        ]);

        try {
            $options = [
                'headers' => $headers,
            ];

            if (strtolower($method) === 'get') {
                $options['query'] = $params;
                $response = $client->get($url, $options);
            } elseif (strtolower($method) === 'post') {
                $options['form_params'] = $params;
                $response = $client->post($url, $options);
            } else {
                throw new \InvalidArgumentException('Método HTTP inválido: use "get" ou "post".');
            }

            $body = $response->getBody();
            return json_decode($body, true);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao consumir API: ' . $e->getMessage());
            return null;
        }
    }
}
