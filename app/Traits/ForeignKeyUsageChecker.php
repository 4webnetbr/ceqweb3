<?php

namespace App\Traits;

use Config\Database;

trait ForeignKeyUsageChecker
{
    protected array $conexoesRelacionadas = [
        'default',
        'dbEstoque',
        'dbProduto',
        // adicione outras conexões
    ];

    protected function verificarUsoEmRelacionamentos(
        string $tabelaReferenciada,
        string $colunaReferenciada,
        $valor
    ): void {
        foreach ($this->conexoesRelacionadas as $nomeConexao) {
            $db = Database::connect($nomeConexao);
            $schema = $db->getDatabase();

            $query = $db->query("
                SELECT TABLE_NAME, COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    REFERENCED_TABLE_NAME = '{$tabelaReferenciada}'
                    AND REFERENCED_COLUMN_NAME = '{$colunaReferenciada}'
                    AND CONSTRAINT_SCHEMA = '{$schema}'
            ");

            $referencias = $query->getResult();

            foreach ($referencias as $ref) {
                $tabela = $ref->TABLE_NAME;
                $coluna = $ref->COLUMN_NAME;

                $count = $db->table($tabela)
                    ->where($coluna, $valor)
                    ->countAllResults();

                if ($count > 0) {
                    // Pegar comentário da tabela para exibir nome mais amigável
                    $comentario = $this->obterComentarioTabela($db, $schema, $tabela);
                    $nomeExibicao = $comentario ?: $tabela;

                    throw new \Exception("Valor está sendo usado na tabela '{$nomeExibicao}' do banco '{$schema}'.");
                }
            }
        }
    }

    protected function obterComentarioTabela($db, string $schema, string $tabela): ?string
    {
        $result = $db->query("
            SELECT TABLE_COMMENT 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_NAME = '{$tabela}' AND TABLE_SCHEMA = '{$schema}'
        ")->getRow();

        return $result->TABLE_COMMENT ?: null;
    }
}
