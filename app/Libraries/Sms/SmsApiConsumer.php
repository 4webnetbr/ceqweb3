<?php

namespace App\Libraries\Sms;

/**
 * Classe plana (sem estender BaseController, sem depender de sessão/
 * request/response) reunindo os métodos "consumidores de API" que podem
 * ser escolhidos como tipo "api" em log_notif_sms_config. Cada método
 * público, sem parâmetro obrigatório, deve retornar um array de objetos
 * (lista associativa) ou null em caso de falha — nunca lançar exceção
 * para fora (api_request() já engole erro e retorna null).
 *
 * Reflectível com segurança pelo comando CLI (NotifSmsVerificar) e pela
 * tela administrativa (EntLogNotifSmsConfig), diferente de um Controller
 * de tela — que depende de session()->getFlashdata('dados_tela') e não
 * pode ser instanciado fora do ciclo HTTP.
 */
class SmsApiConsumer
{
    public function __construct()
    {
        helper('api_cw2');
    }

    /**
     * Lista os métodos públicos "chamáveis a seco" (sem parâmetro
     * obrigatório) desta classe — usado tanto para popular o select
     * nsc_metodo_api (tela) quanto para revalidação em tempo de
     * execução (NotifSmsVerificar::processarRegraApi()).
     */
    public static function metodosDisponiveis(): array
    {
        $reflection = new \ReflectionClass(self::class);
        $opcoes = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $metodo) {
            if ($metodo->isStatic() || $metodo->isConstructor()) {
                continue;
            }
            if ($metodo->getDeclaringClass()->getName() !== self::class) {
                continue;
            }
            if ($metodo->getNumberOfRequiredParameters() > 0) {
                continue;
            }
            $opcoes[$metodo->getName()] = $metodo->getName();
        }

        return $opcoes;
    }

    /**
     * Consulta o endpoint /renovacoes/pendentes do Logística antigo,
     * contrato especificado em
     * docs/desenvolvimento/notificacoes-sms-servico-envio-dev.md (seção 4):
     *   GET {LINK_LOGISTICA}renovacoes/pendentes
     *   Header: X-Api-Key: LOGISTICA_API_KEY
     *   Query: ren_status_max (obrigatório), ren_tipo (opcional)
     *   Resposta 200: array de objetos { ren_id, ren_tipo, ren_status, ren_prev_chegada }
     *
     * Endpoint ainda não implementado no repositório do Logística antigo
     * (pendência externa confirmada pelo Douglas, ver seção 3, item 4) —
     * método fica pronto e selecionável na tela desde já, mas só é
     * testável de ponta a ponta quando o endpoint existir do outro lado.
     *
     * $renStatusMax tem default 4, herdado do exemplo de regra do desenho
     * original (docs/notificacoes-sms.md) — ajustar quando o endpoint
     * existir de fato e o critério real de "pendente" for confirmado com
     * quem administra o Logística antigo.
     */
    public function buscarRenovacoesPendentes(int $renStatusMax = 4, ?int $renTipo = null): ?array
    {
        $params = array_filter(
            ['ren_status_max' => $renStatusMax, 'ren_tipo' => $renTipo],
            fn($v) => $v !== null
        );

        return api_request(
            LINK_LOGISTICA . 'renovacoes/pendentes',
            $params,
            'get',
            ['Accept' => 'application/json', 'X-Api-Key' => getenv('LOGISTICA_API_KEY')],
            10,
            httpErrors: false
        );
    }
}
