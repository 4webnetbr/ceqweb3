<?php

namespace App\Traits;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

trait StatusDependencyChecker
{
    /**
     * Lista de conexões (nomes conforme definidos em Config\Database)
     */
    protected array $statusConnections = [
        'dbEstoque',
        'dbProduto',
        // adicione outras conexões aqui
    ];

    /**
     * Verifica se um stt_id está em uso em outras tabelas de outros bancos
     *
     * @param int $sttId
     * @throws \Exception
     */
    protected function verificaRelacionamentosStatus(int $sttId): void
    {
        foreach ($this->statusConnections as $nomeConexao) {
            $db = Database::connect($nomeConexao);
            $schema = $db->getDatabase();

            $query = $db->query("
                SELECT TABLE_NAME, COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    REFERENCED_TABLE_NAME = 'cfg_status'
                    AND REFERENCED_COLUMN_NAME = 'stt_id'
                    AND CONSTRAINT_SCHEMA = '{$schema}'
            ");

            $referencias = $query->getResult();

            foreach ($referencias as $ref) {
                $tabela = $ref->TABLE_NAME;
                $coluna = $ref->COLUMN_NAME;

                $count = $db->table($tabela)
                    ->where($coluna, $sttId)
                    ->countAllResults();

                if ($count > 0) {
                    throw new \Exception("Status em uso na tabela '{$tabela}' do banco '{$schema}'.");
                }
            }
        }
    }
}
