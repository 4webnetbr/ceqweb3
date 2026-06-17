<?php
namespace Config;

use App\Services\AccessLogService;
use App\Services\DeviceService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    /**
     * Retorna uma instância do serviço de log de acesso (AccessLogService).
     *
     * O que faz:
     * - Centraliza o registro de acessos ao sistema (ex: login, navegação, ações do usuário).
     *
     * Como usar:
     * - Services::accessLog()->registrar(...);
     *
     * Observação:
     * - Por padrão usa instância compartilhada (singleton do CI4).
     * - Passe false se quiser uma nova instância isolada.
     */
    public static function accessLog(bool $getShared = true): AccessLogService
    {
        if ($getShared) {
            return static::getSharedInstance('accessLog');
        }

        return new \App\Services\AccessLogService();
    }

    /**
     * Retorna uma instância da biblioteca de contexto de log (LogContext).
     *
     * O que faz:
     * - Armazena informações adicionais para logs (ex: usuário, tela, operação atual).
     * - Útil para enriquecer logs com contexto sem precisar passar tudo manualmente.
     *
     * Como usar:
     * - Services::logContext()->set('usuario', $id);
     * - Services::logContext()->get('usuario');
     *
     * Observação:
     * - Ideal para logs mais estruturados e rastreáveis.
     */
    public static function logContext(bool $getShared = true): \App\Libraries\LogContext
    {
        if ($getShared) {
            return static::getSharedInstance('logContext');
        }

        return new \App\Libraries\LogContext();
    }

    /**
     * Retorna uma instância do serviço de ocorrências (OcorrenciaService).
     *
     * O que faz:
     * - Gerencia ocorrências do sistema (ex: erros, eventos, registros operacionais).
     * - Pode envolver gravação em banco, regras de negócio, notificações, etc.
     *
     * Como usar:
     * - Services::ocorrenciaService()->registrar($dados);
     *
     * Observação:
     * - Sem tipagem explícita no retorno (dá pra melhorar isso depois).
     */
    public static function ocorrenciaService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('ocorrenciaService');
        }

        return new \App\Services\OcorrenciaService();
    }

    /**
     * Retorna uma instância do cliente Redis configurado.
     *
     * O que faz:
     * - Conecta no servidor Redis usando dados do .env
     * - Autentica (se houver senha)
     * - Seleciona o banco configurado
     *
     * Como usar:
     * - $redis = Services::redis();
     * - $redis->set('chave', 'valor');
     * - $redis->get('chave');
     *
     * Observação:
     * - Ideal para cache, sessão, fila, controle de estado, etc.
     * - Usa instância compartilhada por padrão.
     */
    public static function redis(bool $getShared = true): \Redis
    {
        if ($getShared) {
            return static::getSharedInstance('redis');
        }

        $redis = new \Redis();

        $redis->connect(
            env('redis.host', '127.0.0.1'),
            (int) env('redis.port', 6379)
        );

        if ($password = env('redis.password')) {
            $redis->auth($password);
        }

        $redis->select((int) env('redis.database', 0));

        return $redis;
    }

    /**
     * Retorna uma instância do serviço de identificação de dispositivo (DeviceService).
     *
     * O que faz:
     * - Detecta informações do dispositivo do usuário (mobile, tablet, desktop, etc).
     * - Normalmente baseado no User-Agent da requisição.
     *
     * Como usar:
     * - Services::device()->isMobile();
     * - Services::device()->isTablet();
     * - Services::device()->isDesktop();
     *
     * Observação:
     * - Útil para adaptar layout, regras ou comportamento conforme o dispositivo.
     */
    public static function device(bool $getShared = true): DeviceService
    {
        if ($getShared) {
            return static::getSharedInstance('device');
        }

        return new DeviceService();
    }
}
