<?php

namespace App\Traits;

use App\Models\Config\ConfigDicDadosModel;
use Config\Database;

trait ForeignKeyUsageChecker
{
    protected array $conexoesRelacionadas = [
        'default',
        'dbEstoque',
        'dbProduto',
        'dbOcorrencia'
        // adicione outras conexões
    ];

    protected function verificarUsoEmRelacionamentosAnt(
        string $tabelaReferenciada,
        string $colunaReferenciada,
        $valor
    ): void {
        foreach ($this->conexoesRelacionadas as $nomeConexao) {
            $db = Database::connect($nomeConexao);
            $schema = $db->getDatabase();

            $consulta = "
                SELECT TABLE_NAME, COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    REFERENCED_TABLE_NAME = '{$tabelaReferenciada}'
                    AND REFERENCED_COLUMN_NAME = '{$colunaReferenciada}'
                    AND CONSTRAINT_SCHEMA = '{$schema}'
            ";
            // debug($consulta, true);
            $query = $db->query($consulta);

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

    protected function verificarUsoEmRelacionamentos(
        string $tabelaReferenciada,
        string $colunaReferenciada,
        $valor
    ): void {
        $tabelasVerificadas = [];

        foreach ($this->conexoesRelacionadas as $nomeConexao) {
            $db = Database::connect($nomeConexao);

            $schemasQuery = $db->query("SHOW DATABASES");
            $schemas = $schemasQuery->getResult();

            foreach ($schemas as $schemaObj) {
                $schema = $schemaObj->Database;

                if (in_array($schema, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
                    continue;
                }

                // Busca apenas tabelas reais (exclui views)
                $consulta = "
                SELECT c.TABLE_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS c
                JOIN INFORMATION_SCHEMA.TABLES t ON 
                    c.TABLE_SCHEMA = t.TABLE_SCHEMA AND 
                    c.TABLE_NAME = t.TABLE_NAME
                WHERE 
                    c.COLUMN_NAME = '{$colunaReferenciada}' 
                    AND c.TABLE_SCHEMA = '{$schema}'
                    AND t.TABLE_TYPE = 'BASE TABLE'
                    ";

                $query = $db->query($consulta);
                // debug($consulta);
                $tabelas = $query->getResult();

                foreach ($tabelas as $tabelaObj) {
                    $tabela = $tabelaObj->TABLE_NAME;

                    // Ignora a tabela original
                    if (str_contains($tabela, $tabelaReferenciada)) {
                        continue;
                    }

                    // Garante que não processe a mesma tabela mais de uma vez
                    $chaveTabela = "{$schema}.{$tabela}";
                    if (isset($tabelasVerificadas[$chaveTabela])) {
                        continue;
                    }
                    $tabelasVerificadas[$chaveTabela] = true;

                    $tabelaCompleta = "`{$schema}`.`{$tabela}`";

                    $checkQuery = "
                    SELECT COUNT(*) as total 
                    FROM {$tabelaCompleta} 
                    WHERE `{$colunaReferenciada}` = ?
                ";
                    $result = $db->query($checkQuery, [$valor])->getRow();

                    if ($result && $result->total > 0) {
                        $comentario = $this->obterComentarioTabela($db, $schema, $tabela);
                        $nomeExibicao = $comentario ?: $tabela;

                        throw new \Exception("Valor está sendo usado na tabela '{$nomeExibicao}' do banco '{$schema}'.");
                    }
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
