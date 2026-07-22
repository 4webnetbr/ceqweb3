<?php

namespace App\Libraries;

use App\Libraries\Sms\SmsProviderInterface;
use App\Libraries\Sms\SmsDevProvider;
use App\Libraries\Sms\GtiSmsProvider;

/**
 * Fachada fina para o provedor de SMS ativo, isolando o resto do sistema do
 * provedor escolhido. Seleção via variável de ambiente SMS_PROVIDER
 * ('smsdev' ou 'gtisms'), padrão 'smsdev'. Ver
 * docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx.
 */
class SmsService
{
    private SmsProviderInterface $provider;

    public function __construct()
    {
        $this->provider = match (getenv('SMS_PROVIDER') ?: 'smsdev') {
            'gtisms' => new GtiSmsProvider(),
            default  => new SmsDevProvider(),
        };
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        return $this->provider->enviar($telefone, $mensagem);
    }

    public function consultarSaldo(): ?int
    {
        return $this->provider->consultarSaldo();
    }
}
