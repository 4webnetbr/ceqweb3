<?php

use CodeIgniter\CodeIgniter;
use Config\Paths;

require_once __DIR__ . '/../../vendor/autoload.php';

$paths = new Paths(__DIR__ . '/../');

// Inicializa o sistema
$app = CodeIgniter::createApplication($paths);

// Retorna o app pronto para usar
return $app;
