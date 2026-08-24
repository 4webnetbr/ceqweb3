<?php

namespace App\Libraries\Sms;

use App\Libraries\SmsService;
use App\Models\Logis\LogisNotifSmsEnviadasModel;

/**
 * Motor de execução compartilhado entre os tipos de alerta 'api' e
 * 'consulta' de Notificações SMS (log_notif_sms_config). Recebe já pronta
 * a lista de objetos (vinda de SmsApiConsumer ou de uma view vw_sms_*),
 * sem conhecer a origem dos dados. Ver
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md, seção 6.
 */
class SmsRegraExecutor
{
    public function __construct(
        private SmsService $smsService,
        private LogisNotifSmsEnviadasModel $enviadasModel
    ) {}

    /** @param array<int, array<string,mixed>> $objetos */
    public function processar(array $objetos, string $templateMensagem, string $telefonesCsv, int $nscId, string $prefixoChave): int
    {
        $enviados = 0;
        foreach ($objetos as $objeto) {
            if (!is_array($objeto)) { continue; }

            $chave = $prefixoChave . ':' . $this->extrairIdDedup($objeto);
            if ($this->enviadasModel->jaEnviado($chave, $nscId)) { continue; }

            $msg = $this->substituiPlaceholders($templateMensagem, $objeto);
            foreach (explode(',', $telefonesCsv) as $tel) {
                $this->smsService->enviar(trim($tel), $msg);
            }
            $this->enviadasModel->registrar($chave, $nscId);
            $enviados++;
        }
        return $enviados;
    }

    private function extrairIdDedup(array $objeto): string
    {
        return (isset($objeto['id']) && is_scalar($objeto['id'])) ? (string) $objeto['id'] : md5(json_encode($objeto));
    }

    private function substituiPlaceholders(string $template, array $objeto): string
    {
        $mapa = [];
        foreach ($objeto as $chave => $valor) {
            if (is_array($valor) || is_object($valor)) { continue; }
            $mapa['[' . $chave . ']'] = (string) $valor;
        }
        return strtr($template, $mapa);
    }
}
