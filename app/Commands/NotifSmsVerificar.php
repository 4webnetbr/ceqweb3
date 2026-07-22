<?php

namespace App\Commands;

use App\Models\Logis\LogisNotifSmsConfigModel;
use App\Models\Logis\LogisNotifSmsEnviadasModel;
use App\Libraries\SmsService;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;

/**
 * Varre as regras ativas de log_notif_sms_config e dispara SMS (entrega ou
 * saldo baixo) conforme a condição de cada regra. Execução única (sem
 * loop/daemon) — o agendamento periódico é responsabilidade do cron do SO
 * (a cada 5 minutos, ver docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx).
 */
class NotifSmsVerificar extends BaseCommand
{
    protected $group       = 'Logistica';
    protected $name        = 'notifsms:verificar';
    protected $description = 'Varre as regras ativas de log_notif_sms_config e dispara SMS (entrega ou saldo baixo) conforme condição.';
    protected $usage       = 'notifsms:verificar';

    public function run(array $params)
    {
        helper('api_cw2');

        $logger        = service('logger');
        $configModel   = new LogisNotifSmsConfigModel();
        $enviadasModel = new LogisNotifSmsEnviadasModel();
        $smsService    = new SmsService();

        foreach ($configModel->getRegrasAtivas() as $regra) {
            try {
                if ($regra->nsc_tipo_regra === 'saldo_baixo') {
                    $this->processarRegraSaldo($regra, $smsService, $enviadasModel);
                } else {
                    $this->processarRegraEntrega($regra, $smsService, $enviadasModel);
                }
            } catch (\Throwable $e) {
                $logger->error('notifsms:verificar - regra ' . $regra->nsc_id . ': ' . $e->getMessage());
                CLI::error('Erro na regra ' . $regra->nsc_id . ': ' . $e->getMessage());
            }
        }
        CLI::write('Verificação concluída.', 'green');
    }

    private function processarRegraSaldo(object $regra, SmsService $smsService, LogisNotifSmsEnviadasModel $enviadasModel): void
    {
        $logger = service('logger');

        $saldo = $smsService->consultarSaldo();
        if ($saldo === null) {
            $logger->error('notifsms:verificar - não foi possível consultar o saldo de SMS (regra ' . $regra->nsc_id . ').');
            CLI::error('Não foi possível consultar o saldo.');
            return;
        }
        if ($saldo >= $regra->nsc_saldo_minimo) return;

        // 1 alerta por dia por regra, evita flood enquanto o saldo estiver baixo
        $chave = 'SALDO:' . date('Y-m-d');
        if ($enviadasModel->jaEnviado($chave, $regra->nsc_id)) return;

        $msg = strtr($regra->nsc_mensagem_template, [
            '{limite}' => $regra->nsc_saldo_minimo,
            '{saldo}'  => $saldo,
        ]);

        foreach (explode(',', $regra->nsc_telefones) as $tel) {
            $smsService->enviar(trim($tel), $msg);
        }
        $enviadasModel->registrar($chave, $regra->nsc_id);
    }

    private function processarRegraEntrega(object $regra, SmsService $smsService, LogisNotifSmsEnviadasModel $enviadasModel): void
    {
        $logger = service('logger');

        $params = array_filter(
            ['ren_status_max' => $regra->nsc_ren_status_max, 'ren_tipo' => $regra->nsc_ren_tipo],
            fn($v) => $v !== null
        );

        $res = api_request(
            LINK_LOGISTICA . 'renovacoes/pendentes',
            $params,
            'get',
            ['Accept' => 'application/json', 'X-Api-Key' => getenv('LOGISTICA_API_KEY')],
            10,
            httpErrors: false
        );

        if ($res === null) {
            $logger->warning('notifsms:verificar - falha ao consultar API do Logística para a regra ' . $regra->nsc_id);
            return;
        }

        if (!is_array($res) || !$this->ehListaDeRenovacoes($res)) {
            $logger->warning('notifsms:verificar - resposta em formato inesperado da API do Logística para a regra ' . $regra->nsc_id);
            return;
        }

        foreach ($res as $ren) {
            if (!is_array($ren) || !isset($ren['ren_id'], $ren['ren_prev_chegada'])) {
                $logger->warning('notifsms:verificar - item de renovação em formato inesperado (regra ' . $regra->nsc_id . ')');
                continue;
            }

            $diffMin = (strtotime($ren['ren_prev_chegada']) - time()) / 60;

            $dispara = ($regra->nsc_condicao === 'antes_chegada' && $diffMin > 0 && $diffMin <= $regra->nsc_minutos_limite)
                    || ($regra->nsc_condicao === 'apos_chegada' && $diffMin <= 0);

            if (!$dispara) continue;

            $chave = 'REN:' . $ren['ren_id'];
            if ($enviadasModel->jaEnviado($chave, $regra->nsc_id)) continue;

            $msg = strtr($regra->nsc_mensagem_template, [
                '{ren_id}'           => $ren['ren_id'],
                '{ren_prev_chegada}' => $ren['ren_prev_chegada'],
            ]);

            foreach (explode(',', $regra->nsc_telefones) as $tel) {
                $smsService->enviar(trim($tel), $msg);
            }
            $enviadasModel->registrar($chave, $regra->nsc_id);
        }
    }

    /**
     * Confirma que $res é uma lista (array sequencial, vazia ou não) e não um
     * objeto associativo (ex: {"message": "..."}) devolvido por um endpoint
     * ainda instável/inexistente.
     */
    private function ehListaDeRenovacoes(array $res): bool
    {
        return array_is_list($res);
    }
}
