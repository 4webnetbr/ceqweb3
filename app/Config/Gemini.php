<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Gemini extends BaseConfig
{
    public string $baseURL = 'https://generativelanguage.googleapis.com';

    // Confira no AI Studio quais modelos sua chave free acessa.
    // gemini-2.5-flash é o padrão seguro do free tier; se houver
    // uma versão flash mais nova liberada, é só trocar aqui.
    public string $model = 'gemini-2.5-flash';

    public float $temperature     = 0.0;  // determinístico
    public int   $maxOutputTokens = 5000; // query SQL é curta; sobra
    public int   $timeout         = 60;

    public string $apiKey = '';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('gemini.apiKey', '');
    }
}
