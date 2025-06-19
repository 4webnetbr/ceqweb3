<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default;
    public array $dbEstoque;
    public array $dbProduto;
    public array $dbOcorrencia;

    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        $this->default = [
            'DSN'      => '',
            'hostname' => env('database.hostname', 'localhost'),
            'username' => 'estoque_user',
            'password' => 'n3hoUV3LwAIyE2gv8C55',
            'database' => 'config_ceqweb_db',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 3306,
        ];

        $this->dbEstoque = [
            'DSN'      => '',
            'hostname' => env('database.hostname', 'localhost'),
            'username' => 'estoque_user',
            'password' => 'n3hoUV3LwAIyE2gv8C55',
            'database' => 'estoque_db',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 3306,
        ];

        $this->dbProduto = [
            'DSN'      => '',
            'hostname' => env('database.hostname', 'localhost'),
            'username' => 'estoque_user',
            'password' => 'n3hoUV3LwAIyE2gv8C55',
            'database' => 'produto_db',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 3306,
        ];

        $this->dbOcorrencia = [
            'DSN'      => '',
            'hostname' => env('database.hostname', 'localhost'),
            'username' => 'estoque_user',
            'password' => 'n3hoUV3LwAIyE2gv8C55',
            'database' => 'ocorrencia_db',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 3306,
        ];
    }
}
