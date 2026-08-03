<?php

namespace App\Database\Migrations;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Migration;

/**
 * Reformulação dos tipos de alerta de log_notif_sms_config (Notificações
 * SMS — Logística): troca de 'entrega'/'saldo_baixo' para
 * 'saldo_baixo'/'api'/'consulta', conforme
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md (seções 4.6 e
 * 5.1). Moldada em
 * app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php (mesmo
 * padrão de checagem de existência via information_schema e CLI::write()
 * para log de execução).
 *
 * ATENÇÃO OPERACIONAL: esta migration EXCLUI DEFINITIVAMENTE as regras
 * nsc_tipo_regra = 'entrega' já cadastradas (decisão de negócio confirmada
 * pelo Douglas, ver seção 3, item 2 do documento) — não roda em produção
 * sem confirmação explícita do usuário.
 */
class LogisticaNotifSmsTiposApiConsulta extends Migration
{
    public function up()
    {
        $this->adicionaColunasNovas();
        $this->excluiRegrasEntrega();
        $this->alteraEnumTipoRegra();
        $this->removeColunasObsoletas();
    }

    public function down()
    {
        $this->restauraColunasObsoletas();
        $this->restauraEnumTipoRegra();
        $this->removeColunasNovas();

        // Reversão apenas estrutural — não recupera as regras 'entrega'
        // excluídas definitivamente por up() nem quaisquer dados perdidos
        // pelas colunas removidas (ver seção 4.6/5.1 do documento).
    }

    /**
     * Passo 1 de up(): nsc_metodo_api / nsc_view_consulta / nsc_view_dbgroup.
     * Idempotente — só adiciona a coluna se ainda não existir.
     */
    private function adicionaColunasNovas(): void
    {
        $db = db_connect('dbLogistica');

        $colunasNovas = [
            'nsc_metodo_api'    => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_metodo_api VARCHAR(150) NULL',
            'nsc_view_consulta' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_view_consulta VARCHAR(100) NULL',
            'nsc_view_dbgroup'  => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_view_dbgroup VARCHAR(30) NULL',
        ];

        foreach ($colunasNovas as $coluna => $sql) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd === 0) {
                $db->query($sql);
                CLI::write("Coluna {$coluna} adicionada em log_notif_sms_config.", 'yellow');
            }
        }
    }

    /**
     * Passo 2 de up(): exclusão definitiva das regras 'entrega' (código
     * reaproveitado da seção 4.6 do documento). Precisa rodar ANTES do
     * MODIFY do ENUM (passo 3) — senão o MODIFY encontraria linha com
     * valor 'entrega' incompatível com o novo ENUM.
     */
    private function excluiRegrasEntrega(): void
    {
        $db  = db_connect('dbLogistica');
        $qtd = $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->countAllResults();

        if ($qtd > 0) {
            $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->delete();
            CLI::write("{$qtd} regra(s) do tipo 'entrega' excluída(s) definitivamente (tipo descontinuado, confirmado pelo Douglas).", 'yellow');
        }
    }

    /**
     * Passo 3 de up(): troca do ENUM de nsc_tipo_regra. Só roda depois do
     * passo 2 (exclusão das linhas 'entrega'), garantindo que nenhuma
     * linha remanescente tenha valor incompatível com o novo ENUM.
     */
    private function alteraEnumTipoRegra(): void
    {
        $db = db_connect('dbLogistica');
        $db->query(
            "ALTER TABLE log_notif_sms_config
             MODIFY nsc_tipo_regra ENUM('saldo_baixo','api','consulta') NOT NULL DEFAULT 'saldo_baixo'"
        );
        CLI::write("ENUM de nsc_tipo_regra alterado para ('saldo_baixo','api','consulta') DEFAULT 'saldo_baixo'.", 'yellow');
    }

    /**
     * Passo 4 de up(): remoção das 4 colunas exclusivas do tipo 'entrega'
     * (descontinuado). Condicional/idempotente — só remove se a coluna
     * ainda existir.
     */
    private function removeColunasObsoletas(): void
    {
        $db = db_connect('dbLogistica');

        foreach (['nsc_ren_tipo', 'nsc_ren_status_max', 'nsc_condicao', 'nsc_minutos_limite'] as $coluna) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd > 0) {
                $db->query("ALTER TABLE log_notif_sms_config DROP COLUMN {$coluna}");
                CLI::write("Coluna {$coluna} removida de log_notif_sms_config (exclusiva do tipo 'entrega', descontinuado).", 'yellow');
            }
        }
    }

    /**
     * down() — passo 1: recria (condicional) as 4 colunas removidas por
     * removeColunasObsoletas(), com a mesma definição da tabela original
     * (2026-07-21-000001_LogisticaNotifSms.php). Estrutural apenas —
     * dados antigos não são recuperados.
     */
    private function restauraColunasObsoletas(): void
    {
        $db = db_connect('dbLogistica');

        $colunas = [
            'nsc_ren_tipo'       => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_ren_tipo INT NULL',
            'nsc_ren_status_max' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_ren_status_max INT NULL',
            'nsc_condicao'       => "ALTER TABLE log_notif_sms_config ADD COLUMN nsc_condicao ENUM('antes_chegada','apos_chegada') NULL",
            'nsc_minutos_limite' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_minutos_limite INT NULL',
        ];

        foreach ($colunas as $coluna => $sql) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd === 0) {
                $db->query($sql);
            }
        }
    }

    /**
     * down() — passo 2: volta o ENUM de nsc_tipo_regra ao formato anterior
     * a este ciclo.
     */
    private function restauraEnumTipoRegra(): void
    {
        $db = db_connect('dbLogistica');
        $db->query(
            "ALTER TABLE log_notif_sms_config
             MODIFY nsc_tipo_regra ENUM('entrega','saldo_baixo') NOT NULL DEFAULT 'entrega'"
        );
    }

    /**
     * down() — passo 3: remove as 3 colunas novas deste ciclo (condicional).
     */
    private function removeColunasNovas(): void
    {
        $db = db_connect('dbLogistica');

        foreach (['nsc_metodo_api', 'nsc_view_consulta', 'nsc_view_dbgroup'] as $coluna) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd > 0) {
                $db->query("ALTER TABLE log_notif_sms_config DROP COLUMN {$coluna}");
            }
        }
    }
}
