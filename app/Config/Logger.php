<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

class Logger extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Error Logging Threshold
     * --------------------------------------------------------------------------
     *
     * You can enable error logging by setting a threshold over zero. The
     * threshold determines what gets logged.
     *
     * @var array|int
     */
    public $threshold = (ENVIRONMENT === 'production') ? 4 : 9;

    /**
     * --------------------------------------------------------------------------
     * Date Format for Logs
     * --------------------------------------------------------------------------
     *
     * Each item that is logged has an associated date.
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * --------------------------------------------------------------------------
     * Log Handlers
     * --------------------------------------------------------------------------
     *
     * The logging system supports multiple handlers.
     */
    public array $handlers = [

        /*
         * --------------------------------------------------------------------
         * File Handler padrão
         * --------------------------------------------------------------------
         */
        // FileHandler::class => [
        //    'handles' => [],
        // ],

        /*
         * Handler customizado para logs de Worker
         */
        \App\Log\Handlers\WorkerHandler::class => [
            'handles' => ['info', 'error', 'debug'],
            'fileExtension'   => 'log',
            'filePermissions' => 0644,
            'path'            => WRITEPATH . 'logs/',
            'filenameFormat'  => 'workanalise-{date}',
            'dateFormat'      => 'Y-m-d H:i:s',
        ],

        /*
         * Handler customizado para logs separados por nível
         */
        \App\Log\Handlers\MultiLevelFileHandler::class => [
            'handles' => [
                'info',
                'debug',
                'error',
                'warning',
                'critical',
                'alert',
                'emergency',
                'notice',
                'log',
            ],
            'filePermissions' => 0644,
            'path' => WRITEPATH . 'logs/',
        ],
        
        \App\Log\Handlers\LogEmailThrottleHandler::class => [
            'handles' => ['error', 'critical', 'alert', 'emergency'],
            'to' => 'douglas@4web.net.br',
        ],
    ];
}
