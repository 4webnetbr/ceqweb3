<?php

namespace App\Services;

use RuntimeException;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Query;

class SqlValidator
{
    /** Teto de linhas forçado quando a query não traz LIMIT. */
    protected int $limiteMaximo;

    /**
     * Comandos/palavras que JAMAIS podem aparecer numa consulta de leitura:
     * tudo que escreve, altera estrutura, lê/grava arquivo ou causa DoS.
     */
    protected array $palavrasProibidas = [
        // escrita / DDL
        'INSERT',
        'UPDATE',
        'DELETE',
        'DROP',
        'ALTER',
        'TRUNCATE',
        'CREATE',
        'REPLACE',
        'RENAME',
        'MERGE',
        // permissões / administração
        'GRANT',
        'REVOKE',
        'SET',
        'LOCK',
        'UNLOCK',
        'FLUSH',
        'KILL',
        // procedures / execução dinâmica
        'CALL',
        'EXEC',
        'EXECUTE',
        'HANDLER',
        'PREPARE',
        'DEALLOCATE',
        // acesso a arquivos / sistema (CRÍTICO)
        'INTO',
        'OUTFILE',
        'DUMPFILE',
        'LOAD_FILE',
        'LOAD',
        // negação de serviço
        'SLEEP',
        'BENCHMARK',
    ];

    public function __construct(int $limiteMaximo = 1000)
    {
        $this->limiteMaximo = $limiteMaximo;
    }

    /**
     * Valida o SQL vindo da IA e devolve uma versão segura (com LIMIT).
     * Lança RuntimeException no primeiro problema encontrado.
     */
    public function validar(string $sql): string
    {
        $sql = trim($sql);

        if ($sql === '') {
            throw new RuntimeException('SQL vazio.');
        }

        // Remove ; e espaços do final
        $sql = rtrim($sql, "; \t\n\r");

        // Versão sem o conteúdo das strings literais: assim um dado como
        // '...; DROP...' dentro de aspas não dispara um falso positivo,
        // nem esconde comando de uma checagem.
        $limpo = $this->mascararStrings($sql);

        // 1) Comando único — nada de ; no meio (stacked queries)
        if (str_contains($limpo, ';')) {
            throw new RuntimeException('Múltiplos comandos não são permitidos.');
        }

        // 2) Sem comentários (podem esconder código)
        if (preg_match('#(--|\#|/\*)#', $limpo)) {
            throw new RuntimeException('Comentários não são permitidos no SQL.');
        }

        // 3) Tem que ser leitura: começa com SELECT ou WITH
        if (! preg_match('/^\s*(SELECT|WITH)\b/i', $limpo)) {
            throw new RuntimeException('Apenas consultas SELECT são permitidas.');
        }

        // 4) Nenhuma palavra proibida (como palavra inteira)
        foreach ($this->palavrasProibidas as $palavra) {
            if (preg_match('/\b' . $palavra . '\b/i', $limpo)) {
                throw new RuntimeException("Comando não permitido detectado: {$palavra}.");
            }
        }

        // 5) Garante um teto de linhas
        return $this->garantirLimite($sql, $limpo);
    }

    /**
     * Substitui o conteúdo de strings 'aspas' e "aspas" por vazio,
     * para as checagens estruturais não confundirem dado com comando.
     */
    protected function mascararStrings(string $sql): string
    {
        $sql = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $sql);
        $sql = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $sql);

        return $sql ?? '';
    }

    /** Se não houver LIMIT, acrescenta um teto de segurança. */
    protected function garantirLimite(string $sql, string $limpo): string
    {
        if (preg_match('/\bLIMIT\s+\d+/i', $limpo)) {
            return $sql; // já possui LIMIT
        }

        return $sql . ' LIMIT ' . $this->limiteMaximo;
    }

    /**
     * Camada 2: garante que o SQL só referencia tabelas autorizadas.
     * $schema é a saída de ConfigDicDadosModel::getSchemaEstruturado()
     * — a MESMA fonte usada para montar o contexto da IA, então os
     * nomes de schema batem automaticamente em dev e em produção.
     */
    public function validarTabelas(string $sql, array $schema): void
    {
        // Conjunto de tabelas permitidas. Guardo nas duas formas
        // (schema.tabela e só tabela) para casar com qualquer
        // formato que o parser devolva.
        $autorizadas = [];
        foreach ($schema as $tabela => $info) {
            $sch = strtolower($info['schema'] ?? '');
            $tab = strtolower($tabela);
            if ($sch !== '') {
                $autorizadas[$sch . '.' . $tab] = true;
            }
            $autorizadas[$tab] = true;
        }

        $parser = new Parser($sql);

        // Se o parser não conseguiu analisar com segurança, barra.
        if (! empty($parser->errors)) {
            throw new \RuntimeException('SQL não pôde ser analisado com segurança.');
        }
        if (empty($parser->statements[0])) {
            throw new \RuntimeException('Nenhum comando SQL válido encontrado.');
        }

        $usadas = Query::getTables($parser->statements[0]);

        foreach ($usadas as $ref) {
            $norm = strtolower(str_replace('`', '', trim($ref)));

            if (! isset($autorizadas[$norm])) {
                throw new \RuntimeException("Tabela não autorizada no relatório: {$ref}");
            }
        }
    }
}
