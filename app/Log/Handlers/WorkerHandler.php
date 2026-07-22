<?php

namespace App\Log\Handlers;

use CodeIgniter\Log\Handlers\BaseHandler;
use Throwable;

class WorkerHandler extends BaseHandler
{
    protected string $logPath;
    protected string $filenameFormat;
    // protected string $dateFormat;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->logPath        = rtrim($config['path'] ?? WRITEPATH . 'logs/', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->filenameFormat = $config['filenameFormat'] ?? 'worker-{date}';
        // $this->dateFormat     = $config['dateFormat'] ?? 'd/m/Y H:i:s';

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function handle($level, $message): bool
    {
        // Filtra: só grava se vier da classe WorkAnalise
        if (!$this->isFromWorker()) {
            return true;
        }

        $filename = str_replace('{date}', date('Y-m-d'), $this->filenameFormat) . '.log';
        $filepath = $this->logPath . $filename;

        if ($message instanceof Throwable) {
            $message = $message->__toString();
        }

        $date = date($this->dateFormat);
        $text = '[' . strtoupper($level) . '] ' . $date . ' → ' . $message . PHP_EOL;

        return file_put_contents($filepath, $text, FILE_APPEND | LOCK_EX) !== false;
    }

    // Verifica se o log foi disparado de dentro de um Worker
    private function isFromWorker(): bool
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['class']) && str_contains($frame['class'], 'Controllers\\Work')) {
                return true;
            }
        }
        return false;
    }
}
