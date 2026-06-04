<?php

namespace App\Services;

use Config\Services;
use RuntimeException;

class SqlGeneratorService
{
    protected \Config\Gemini $config;

    public function __construct()
    {
        $this->config = config('Gemini');
    }

    public function gerarSql(string $pergunta, string $schemaContext): string
    {
        if (empty($this->config->apiKey)) {
            throw new \RuntimeException('Chave da API Gemini não configurada (.env).');
        }

        $systemPrompt = $this->montarSystemPrompt($schemaContext);

        $client = \Config\Services::curlrequest([
            'baseURI' => $this->config->baseURL,
            'timeout' => $this->config->timeout,
        ]);

        $endpoint = '/v1beta/models/' . $this->config->model . ':generateContent';

        try {
            $response = $client->post($endpoint, [
                'headers' => [
                    'x-goog-api-key' => $this->config->apiKey,
                    'content-type'   => 'application/json',
                ],
                'json' => [
                    // system prompt vai aqui, separado da conversa
                    'system_instruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [
                        ['parts' => [['text' => $pergunta]]],
                    ],
                    'generationConfig' => [
                        'temperature'     => $this->config->temperature,
                        'maxOutputTokens' => $this->config->maxOutputTokens,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Falha ao contatar a API Gemini: ' . $e->getMessage());
        }

        $data = json_decode($response->getBody(), true);

        if (! isset($data['candidates'][0]['content']['parts'])) {
            // pode ter sido bloqueio de segurança ou erro de chave/modelo
            $erro = $data['error']['message'] ?? 'Resposta inesperada da API Gemini.';
            throw new \RuntimeException($erro);
        }

        $texto = '';
        foreach ($data['candidates'][0]['content']['parts'] as $parte) {
            if (isset($parte['text'])) {
                $texto .= $parte['text'];
            }
        }

        return $this->limparSql($texto);
    }


    /**
     * Monta o prompt de sistema com as regras e o schema.
     */
    protected function montarSystemPrompt(string $schemaContext): string
    {
        return <<<PROMPT
Você é um gerador de SQL para o banco de dados MariaDB.

A partir da pergunta do usuário (em português), gere UMA consulta SQL.

REGRAS OBRIGATÓRIAS:
- Gere SOMENTE comandos SELECT. Nunca INSERT, UPDATE, DELETE, DROP, ALTER, etc.
- Use SOMENTE as tabelas e colunas listadas no schema abaixo. Não invente nomes.
- O sistema é multi-banco no MESMO servidor: sempre qualifique as tabelas
  com o schema, no formato schema.tabela (ex: estoque_db.est_requisicao).
- Para juntar tabelas, use as Foreign Keys (FK) indicadas no schema.
- Datas vêm em formato brasileiro (DD/MM/AAAA). Converta para AAAA-MM-DD no SQL.
- Quando o usuário citar um produto/item por nome, filtre pela coluna de nome
  com LIKE (ex: WHERE pro_produto.pro_produto_nome LIKE '%X%').
- Responda APENAS com o SQL puro. Sem explicações, sem comentários, sem
  marcação markdown, sem crases.

SCHEMA DISPONÍVEL:
{$schemaContext}
PROMPT;
    }

    /**
     * Remove cercas markdown, texto extra e isola o SELECT.
     */
    protected function limparSql(string $raw): string
    {
        $sql = trim($raw);

        // Remove cercas ```sql ... ``` se o modelo insistir
        $sql = preg_replace('/^```(?:sql)?\s*/i', '', $sql);
        $sql = preg_replace('/\s*```$/', '', $sql);

        // Isola a partir do primeiro SELECT ou WITH (descarta preâmbulo)
        if (preg_match('/\b(SELECT|WITH)\b/i', $sql, $m, PREG_OFFSET_CAPTURE)) {
            $sql = substr($sql, $m[0][1]);
        }

        return trim($sql);
    }
}
