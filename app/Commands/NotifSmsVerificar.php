<?php

namespace App\Commands;

use App\Models\Logis\LogisNotifSmsConfigModel;
use App\Models\Logis\LogisNotifSmsEnviadasModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Libraries\SmsService;
use App\Libraries\Sms\SmsRegraExecutor;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;

/**
 * Varre as regras ativas de log_notif_sms_config e dispara SMS (saldo
 * baixo, API ou consulta) conforme a condição de cada regra. Execução
 * única (sem loop/daemon) — o agendamento periódico é responsabilidade do
 * cron do SO (a cada 5 minutos, ver
 * docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx).
 *
 * Dispatch dos 3 tipos de alerta reformulado em
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md (seção 7):
 * 'saldo_baixo' (inalterado), 'api' e 'consulta' (novos, via
 * SmsRegraExecutor compartilhado).
 */
class NotifSmsVerificar extends BaseCommand
{
    protected $group       = 'Logistica';
    protected $name        = 'notifsms:verificar';
    protected $description = 'Varre as regras ativas de log_notif_sms_config e dispara SMS (saldo baixo, API ou consulta) conforme condição.';
    protected $usage       = 'notifsms:verificar';

    public function run(array $params)
    {
        helper('api_cw2');

        $logger        = service('logger');
        $configModel   = new LogisNotifSmsConfigModel();
        $enviadasModel = new LogisNotifSmsEnviadasModel();
        $smsService    = new SmsService();
        $executor      = new SmsRegraExecutor($smsService, $enviadasModel);

        foreach ($configModel->getRegrasAtivas() as $regra) {
            try {
                switch ($regra->nsc_tipo_regra) {
                    case 'saldo_baixo':
                        $this->processarRegraSaldo($regra, $smsService, $enviadasModel);
                        break;
                    case 'api':
                        $this->processarRegraApi($regra, $executor);
                        break;
                    case 'consulta':
                        $this->processarRegraConsulta($regra, $executor);
                        break;
                    default:
                        $logger->warning("notifsms:verificar - tipo de regra desconhecido '{$regra->nsc_tipo_regra}' (regra {$regra->nsc_id}).");
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

    /**
     * Tipo 'api': revalida nsc_metodo_api contra
     * SmsApiConsumer::metodosDisponiveis() (defesa em profundidade — nunca
     * confia apenas na validação de cadastro), instancia o consumidor,
     * chama o método sem argumentos e repassa o resultado ao
     * SmsRegraExecutor. Ver seção 4.3/7 do documento.
     */
    private function processarRegraApi(object $regra, SmsRegraExecutor $executor): void
    {
        $logger = service('logger');
        $classe = SMS_API_CONSUMER_CLASS;

        $metodosDisponiveis = $classe::metodosDisponiveis();
        if (!array_key_exists($regra->nsc_metodo_api, $metodosDisponiveis)) {
            $logger->warning("notifsms:verificar - método de API '{$regra->nsc_metodo_api}' inválido (regra {$regra->nsc_id}).");
            return;
        }

        $consumidor = new $classe();
        $resultado  = $consumidor->{$regra->nsc_metodo_api}();

        if (!is_array($resultado) || array_values($resultado) !== $resultado || empty($resultado)) {
            return;
        }

        $executor->processar($resultado, $regra->nsc_mensagem_template, $regra->nsc_telefones, $regra->nsc_id, 'API');
    }

    /**
     * Tipo 'consulta': revalida (nsc_view_dbgroup, nsc_view_consulta)
     * contra ConfigDicDadosModel::getViewsPorPrefixo('vw_sms_'), roda o
     * SELECT via Query Builder (nunca SQL concatenado) e repassa o
     * resultado ao SmsRegraExecutor. Ver seção 4.4/7 do documento.
     */
    private function processarRegraConsulta(object $regra, SmsRegraExecutor $executor): void
    {
        $logger   = service('logger');
        $admDados = new ConfigDicDadosModel();

        $valida = false;
        foreach ($admDados->getViewsPorPrefixo('vw_sms_') as $v) {
            if ($v['dbGroup'] === $regra->nsc_view_dbgroup && $v['view'] === $regra->nsc_view_consulta) {
                $valida = true;
                break;
            }
        }
        if (!$valida) {
            $logger->warning("notifsms:verificar - view '{$regra->nsc_view_consulta}' não é mais válida (regra {$regra->nsc_id}).");
            return;
        }

        $resultado = db_connect($regra->nsc_view_dbgroup)
            ->table($regra->nsc_view_consulta)
            ->get(SMS_CONSULTA_LIMITE_LINHAS)
            ->getResultArray();

        if (empty($resultado)) { return; }

        $executor->processar($resultado, $regra->nsc_mensagem_template, $regra->nsc_telefones, $regra->nsc_id, 'CONSULTA');
    }
}
