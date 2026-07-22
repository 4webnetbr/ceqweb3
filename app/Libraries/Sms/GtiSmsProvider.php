<?php

namespace App\Libraries\Sms;

/**
 * Provedor GTI SMS v3. Ver docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx,
 * seções 4 e 7.
 */
class GtiSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://sms.gtisms.com/api/v3';

    public function __construct()
    {
        $this->apiKey = getenv('GTISMS_API_KEY');
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        $telefoneNormalizado = $this->normalizaTelefone($telefone);
        if ($telefoneNormalizado === null) {
            log_message('error', "GTI SMS: telefone inválido para envio: '$telefone'");
            return false;
        }

        $payload = [
            'recipient' => $telefoneNormalizado,
            'message'   => $this->sanitizaMensagem($mensagem),
        ];

        $response = $this->post($this->baseUrl . '/sms/send', $payload);
        if ($response === null) return false;

        $data = json_decode($response, true);
        return ($data['status'] ?? '') === 'success';
    }

    public function consultarSaldo(): ?int
    {
        $response = $this->get($this->baseUrl . '/balance');
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (($data['status'] ?? '') !== 'success') return null;

        $remainingBalance = $data['data']['remaining_balance'] ?? null;

        // A API retorna o saldo formatado como moeda (ex.: "‎R$5", com marca de
        // direção invisível), não como número puro. Extrai só os dígitos antes
        // do cast, robusto a qualquer formatação (marca invisível, "R$",
        // separador decimal, espaços).
        $somenteDigitos = preg_replace('/[^\d]/u', '', (string) $remainingBalance);

        if ($somenteDigitos === '') {
            log_message('error', "GTI SMS: remaining_balance sem dígitos após limpeza: '" . (string) $remainingBalance . "'");
            return null;
        }

        return (int) $somenteDigitos;
    }

    /**
     * Prefixa o DDI 55 quando o telefone não o contiver. SMS Dev usa formato
     * local (ex.: 41991728188); GTI exige DDI (ex.: 5541991728188).
     * Retorna null para casos de borda que não devem ser enviados
     * silenciosamente (vazio, muito curto ou muito longo).
     */
    private function normalizaTelefone(string $telefone): ?string
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $tamanho  = strlen($telefone);

        if ($tamanho === 0) {
            log_message('error', 'GTI SMS: telefone vazio após limpeza.');
            return null;
        }

        if ($tamanho === 10 || $tamanho === 11) {
            return '55' . $telefone;
        }

        if ($tamanho === 12 || $tamanho === 13) {
            return $telefone;
        }

        log_message('error', "GTI SMS: telefone com tamanho inválido ($tamanho dígitos): '$telefone'");
        return null;
    }

    /**
     * Mapa fixo de transliteração de acentos latinos comuns (pt-BR) para ASCII.
     * Usado no lugar de iconv('UTF-8','ASCII//TRANSLIT',...), cujo resultado
     * depende de locale/biblioteca do SO (ex.: no Windows "á" pode virar "'a"
     * em vez de "a", e emoji podem virar emoticon ASCII como ":-D" — mascarando
     * caracteres não suportados antes do filtro de whitelist rodar). Com
     * strtr() o resultado é determinístico e idêntico em qualquer ambiente.
     */
    private const MAPA_ACENTOS = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ý' => 'y',
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N', 'Ý' => 'Y',
    ];

    /**
     * GTI SMS não aceita acento/emoji/caractere especial na mensagem.
     * Translitera acentos latinos comuns para ASCII via mapa fixo (determinístico,
     * independente de SO) e, em seguida, filtra qualquer caractere remanescente
     * que não seja alfanumérico/pontuação básica/espaço ou um dos símbolos comuns
     * em templates de mensagem (R$, %, (), '', "", @, &, *, +, =).
     * Caso o filtro final remova algum caractere (ex.: emoji, que não faz parte
     * do mapa de acentos), loga original x sanitizada para rastreabilidade
     * (não bloqueia o envio).
     */
    private function sanitizaMensagem(string $mensagem): string
    {
        $transliterada = strtr($mensagem, self::MAPA_ACENTOS);

        $sanitizada = preg_replace('/[^A-Za-z0-9 .,;:!?\/\-R$%()\'"@&*+=]/', '', $transliterada);

        if ($sanitizada !== $transliterada) {
            log_message('error', "GTI SMS: mensagem continha caractere(s) não suportado(s) e foi alterada. Original: '$transliterada' | Sanitizada: '$sanitizada'");
        }

        return $sanitizada;
    }

    private function post(string $url, array $payload): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha GTI SMS [$url]: " . $response);
            return null;
        }
        return $response;
    }

    private function get(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha GTI SMS [$url]: " . $response);
            return null;
        }
        return $response;
    }
}
