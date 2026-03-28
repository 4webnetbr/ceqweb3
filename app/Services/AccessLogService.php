<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\MongoDb;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\Exception as MongoException;
use Throwable;

final class AccessLogService
{
    private \MongoDB\Driver\Manager $manager;

    private string $database = 'MongoDB';
    private string $collection = 'logs_acesso';

    public function __construct(?MongoDb $mongoDb = null)
    {
        $mongoDb ??= new MongoDb();
        $this->manager = $mongoDb->getConn();
    }

    public function insertAccessLog(
        int|string|null $userId,
        ?string $userName,
        string $screen,
        string $method,
        array $record = null,
        string $userIp = null,
        string $userAgent = null
    ): bool {
        try {
            $document = [
                'log_id_usuario' => $userId,
                'log_usuario'    => $userName,
                'log_data'       => new UTCDateTime(),
                'log_tela'       => $screen,
                'log_metodo'     => $method,
                'log_registro'   => $record,
                'log_ip'         => $userIp,
                'log_user_agent' => $userAgent,
            ];

            $bulk = new BulkWrite();
            $bulk->insert($document);

            $result = $this->manager->executeBulkWrite(
                "{$this->database}.{$this->collection}",
                $bulk
            );

            return $result->getInsertedCount() === 1;

        } catch (MongoException|Throwable $e) {
            log_message('error', '[AccessLogService] {msg}', [
                'msg' => $e->getMessage()
            ]);

            return false;
        }
    }
}