<?php

namespace App\Libraries\Sms;

interface SmsProviderInterface
{
    public function enviar(string $telefone, string $mensagem): bool;
    public function consultarSaldo(): ?int;
}
