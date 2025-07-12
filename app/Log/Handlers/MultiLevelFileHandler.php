<?php

namespace App\Log\Handlers;

use CodeIgniter\Log\Handlers\FileHandler;
use Throwable;

class MultiLevelFileHandler extends FileHandler
{
    protected $logPath;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->logPath = rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function handle($level, $message): bool
    {
        $level = strtolower($level);

        $customLevels = [
            'info'     => 'info',
            'debug'    => 'debg',
            'error'    => 'erro',
            'warning'  => 'warn',
            'critical' => 'crit',
            'alert'    => 'aler',
            'emergency'=> 'emer',
            'notice'   => 'noti',
        ];

        if (!isset($customLevels[$level])) {
            return false;
        }

        $filename = $customLevels[$level] . '-' . date('d-m-Y') . '.log';
        $filepath = $this->logPath . $filename;

        $text = $this->formatCustomMessage($level, $message);

        return file_put_contents($filepath, $text, FILE_APPEND | LOCK_EX) !== false;
    }

    /**
     * Formata a mensagem de log no mesmo estilo do FileHandler original.
     */
    protected function formatCustomMessage(string $level, $message): string
    {
        $date = date($this->dateFormat ?? 'Y-m-d H:i:s');

        // Suporte a exceções
        if ($message instanceof Throwable) {
            $message = $message->__toString();
        }

        return strtoupper($level) . ' - ' . $date . ' --> ' . $message . PHP_EOL;
    }
}
