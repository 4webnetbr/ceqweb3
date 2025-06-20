<?php

// Define o path base do projeto
chdir(__DIR__ . '/../');

// Carrega o bootstrap do CodeIgniter
require 'vendor/autoload.php';
$app = require 'public/index.php';

// Simula uma requisição para o controller WorkAnalise
$request = \Config\Services::request();
$request->setPath('WorkAnalise');

$response = $app->run($request);
