<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Ollama extends BaseConfig
{
    // Endpoint do Ollama (padrão local)
    public string $baseURL = 'http://localhost:11434';

    // Modelo instalado. Veja os seus com: ollama list
    // Bons p/ SQL: qwen2.5-coder, codellama, sqlcoder, llama3.1
    public string $model = 'qwen2.5-coder:7b';

    // 0 = determinístico (ideal p/ SQL). Não invente, não floreie.
    public float $temperature = 0.0;

    // Modelos locais podem demorar; folga no timeout (segundos)
    public int $timeout = 120;
}
