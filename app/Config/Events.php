<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('post_controller', static function () {

    $request = service('request');
    $ctx     = service('logContext');

    // pega os dados globais do request (setados pelo CI internamente)
    $data = $request->getVar();

    if (!is_array($data)) {
        return;
    }

    if (!empty($data['desc_edicao'])) {
        $ctx->set('detalhe', $data['desc_edicao']);
    }
});

Events::on('access:log', static function (array $data): void {

    /** @var \App\Services\AccessLogService $service */
    $service = service('accessLog');

    $service->insertAccessLog(
        userId: $data['log_id_usuario']  ?? null,
        userName: $data['log_usuario']     ?? null,
        screen: $data['log_tela']        ?? '',
        method: $data['log_metodo']      ?? '',
        record: $data['log_registro']    ?? null,
        userIp: $data['log_ip']          ?? null,
        userAgent: $data['log_user_agent']  ?? null,
        titulo: $data['log_titulo']      ?? null,
        detalhe: $data['log_detalhe']     ?? null,
        ambiente: $data['log_ambiente']    ?? null
    );
});

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * Se você deletar, eles não serão mais coletados.
     *
     * ATENÇÃO: O listener de DBQuery do Toolbar é registrado aqui dentro
     * do pre_system. O listener customizado abaixo (fora deste bloco)
     * é o único responsável pela lógica de negócio — sem duplicatas.
     * --------------------------------------------------------------------
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        Services::toolbar()->respond();
    }
});

/*
 * --------------------------------------------------------------------
 * Listener de DBQuery customizado
 * --------------------------------------------------------------------
 * Protegido contra recursão: queries internas disparadas por este
 * próprio handler (ex.: consulta à cfg_tela) não acionam o handler
 * novamente, evitando loop e duplicação de logs.
 * --------------------------------------------------------------------
 */
// Events::on('DBQuery', function ($query) {

//     // Proteção contra re-entrada (loop/recursão)
//     static $isProcessing = false;

//     if ($isProcessing) {
//         return;
//     }

//     $isProcessing = true;

//     try {
//         $sql = $query->getQuery();

//         // Verifica se é INSERT, UPDATE ou DELETE
//         if (! preg_match('/^(insert into|update|delete from)\s+`?(\w+)`?/i', $sql, $matches)) {
//             return;
//         }

//         $tabelaAfetada = $matches[2];

//         // Ignora tabelas de configuração (prefixo cfg_)
//         if (str_starts_with($tabelaAfetada, 'cfg')) {
//             return;
//         }

//         log_message('info', 'Sql ' . $sql);
//         log_message('info', 'Tabela Afetada: ' . $tabelaAfetada);

//         // Busca qual Model usa essa tabela
//         $modelEncontrado = null;
//         $nomeModel       = null;

//         foreach (get_declared_classes() as $class) {
//             if (! is_subclass_of($class, \CodeIgniter\Model::class)) {
//                 continue;
//             }

//             $reflection = new \ReflectionClass($class);

//             if ($reflection->isAbstract()) {
//                 continue;
//             }

//             $instance = new $class();

//             if (property_exists($instance, 'table') && $instance->table === $tabelaAfetada) {
//                 $modelEncontrado = $class;
//                 $nomeModel       = $reflection->getShortName();
//                 break;
//             }
//         }

//         log_message('info', 'Model Encontrada: ' . ($nomeModel ?? 'nenhuma'));

//         if (! $nomeModel) {
//             log_message('warning', "Nenhuma Model encontrada para a tabela [{$tabelaAfetada}].");
//             return;
//         }

//         // Consulta cfg_tela para obter o controller associado
//         // (esta query interna não será reprocessada graças ao flag $isProcessing)
//         $db     = \Config\Database::connect();
//         $result = $db->table('cfg_tela')
//             ->select('tel_controler')
//             ->where('tel_model', $nomeModel)
//             ->get()
//             ->getRow();

//         if (! $result) {
//             log_message('warning', "Model [{$modelEncontrado}] encontrada, mas nenhum tel_controler correspondente na cfg_tela.");
//             return;
//         }

//         $telController = $result->tel_controler;

//         if ($telController !== '') {
//             envia_msg_ws($telController, $telController, 'AtualizarControler', 0, 1);
//             log_message('info', "Tabela [{$tabelaAfetada}] atualizada. Model: [{$modelEncontrado}]. Controller: [{$telController}]");
//         }
//     } finally {
//         // Garante que o flag é sempre liberado, mesmo em caso de exceção
//         $isProcessing = false;
//     }
// });
Events::on('DBQuery', function ($query) {

    // ── Sua lógica original de notificação via WebSocket ──

    // Proteção contra re-entrada (loop/recursão)
    static $isProcessing = false;

    if ($isProcessing) {
        return;
    }

    $isProcessing = true;

    try {
        $sql = $query->getQuery();

        // Verifica se é INSERT, UPDATE ou DELETE
        if (! preg_match('/^(insert into|update|delete from)\s+`?(\w+)`?/i', $sql, $matches)) {
            return;
        }

        $tabelaAfetada = $matches[2];

        // Ignora tabelas de configuração (prefixo cfg_)
        if (str_starts_with($tabelaAfetada, 'cfg')) {
            return;
        }

        log_message('info', 'Sql ' . $sql);
        log_message('info', 'Tabela Afetada: ' . $tabelaAfetada);

        // Busca qual Model usa essa tabela
        $modelEncontrado = null;
        $nomeModel       = null;

        foreach (get_declared_classes() as $class) {
            if (! is_subclass_of($class, \CodeIgniter\Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            $instance = new $class();

            if (property_exists($instance, 'table') && $instance->table === $tabelaAfetada) {
                $modelEncontrado = $class;
                $nomeModel       = $reflection->getShortName();
                break;
            }
        }

        log_message('info', 'Model Encontrada: ' . ($nomeModel ?? 'nenhuma'));

        if (! $nomeModel) {
            log_message('warning', "Nenhuma Model encontrada para a tabela [{$tabelaAfetada}].");
            return;
        }

        // Consulta cfg_tela para obter o controller associado
        // (esta query interna não será reprocessada graças ao flag $isProcessing)
        $db     = \Config\Database::connect();
        $result = $db->table('cfg_tela')
            ->select('tel_controler')
            ->where('tel_model', $nomeModel)
            ->get()
            ->getRow();

        if (! $result) {
            log_message('warning', "Model [{$modelEncontrado}] encontrada, mas nenhum tel_controler correspondente na cfg_tela.");
            return;
        }

        $telController = $result->tel_controler;

        if ($telController !== '') {
            envia_msg_ws($telController, $telController, 'AtualizarControler', 0, 1);
            log_message('info', "Tabela [{$tabelaAfetada}] atualizada. Model: [{$modelEncontrado}]. Controller: [{$telController}]");
        }
    } finally {
        // Garante que o flag é sempre liberado, mesmo em caso de exceção
        $isProcessing = false;
    }
});
