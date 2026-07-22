<?php

namespace App\Libraries\Sms;

/**
 * Provedor SMS Dev (já testado e funcionando em produção). Ver
 * docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx, seção 6.
 */
class SmsDevProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $endpointSend    = 'https://api.smsdev.com.br/v1/send';
    private string $endpointBalance = 'https://api.smsdev.com.br/v1/balance';

    public function __construct()
    {
        $this->apiKey = getenv('SMSDEV_API_KEY');
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $payload = [
            'key'    => $this->apiKey,
            'type'   => 9,
            'number' => $telefone,
            'msg'    => $mensagem,
        ];

        $response = $this->get($this->endpointSend, $payload);
        if ($response === null) return false;

        $data = json_decode($response, true);
        return ($data['situacao'] ?? '') === 'OK';
    }

    public function consultarSaldo(): ?int
    {
        $response = $this->get($this->endpointBalance, ['key' => $this->apiKey]);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (($data['situacao'] ?? '') !== 'OK') return null;

        return (int) $data['saldo_sms'];
    }

    private function get(string $url, array $params): ?string
    {
        $ch = curl_init($url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha SMSDev [$url]: " . $response);
            return null;
        }
        return $response;
    }
}
