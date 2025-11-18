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

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        Services::toolbar()->respond();
    }
});

Events::on('DBQuery', function ($query) {
    $sql = strtolower($query->getQuery());

    // Verifica se é INSERT ou UPDATE
    if (preg_match('/^(insert into|update|delete from) `?(\w+)`?/i', $sql, $matches)) {
        log_message('info', "Sql ".$sql);
        $tabelaAfetada = $matches[2]; // Nome da tabela
        if(substr($tabelaAfetada,0,3) != 'cfg'){
            log_message('info', "Tabela Afetada ".$tabelaAfetada);

            // Buscar qual Model usa essa tabela
            $models = get_declared_classes();
            $modelEncontrado = null;
            $nomeModel = null;
            log_message('info', "Models ".json_encode($tabelaAfetada));
            foreach ($models as $model) {
                if (is_subclass_of($model, \CodeIgniter\Model::class)) {
                    $reflection = new \ReflectionClass($model);
                    
                    // Ignora classes abstratas
                    if ($reflection->isAbstract()) {
                        continue;
                    }
                    
                    // Instancia a Model e pega o nome da tabela
                    $instance = new $model();
                    
                    if (property_exists($instance, 'table') && $instance->table === $tabelaAfetada) {
                        $modelEncontrado = $model;
                        $reflection = new \ReflectionClass($modelEncontrado);
                        $nomeModel = $reflection->getShortName();

                        break;
                    }
                }
            }
            log_message('info', "Model Encontrada ".$nomeModel);

            if ($nomeModel) {
                // Agora vamos buscar na tabela cfg_tela
                // usando tel_model = $modelEncontrado

                $db = \Config\Database::connect();
                $builder = $db->table('cfg_tela');
                $result = $builder->select('tel_controler')
                                ->where('tel_model', $nomeModel)
                                ->get()
                                ->getRow();

                if ($result) {
                    $telController = $result->tel_controler;
                    if($telController != ''){
                        envia_msg_ws($telController, $telController, 'AtualizarControler', session()->get('usu_id'), 1);
                        log_message('info', "Tabela [$tabelaAfetada] atualizada. Model: [$modelEncontrado]. Controller: [$telController]");
                    }
                } else {
                    log_message('warning', "Model [$modelEncontrado] encontrada, mas nenhum tel_controler correspondente na cfg_tela.");
                }
            } else {
                log_message('warning', "Nenhuma Model encontrada para a tabela [$tabelaAfetada].");
            }
        }
    }
});