<?php

namespace App\Database\Migrations;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Migration;

/**
 * Infraestrutura + criação condicional das tabelas do módulo Logística
 * (Notificações SMS), conforme docs/desenvolvimento/notificacoes-sms-dev.docx
 * (seções 4 e 5). Moldada em
 * app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php.
 *
 * IMPORTANTE:
 * - As tabelas log_notif_sms_config / log_notif_sms_enviadas já foram
 *   criadas manualmente pelo usuário em produção (schema logistica_db) —
 *   criaTabelasSeNecessario() só cria se ainda não existirem (cobre
 *   dev/homologação).
 * - prf_id = 1 (Super Admin) confirmado pelo usuário — não resolvido por
 *   nome, ao contrário de outras migrations do projeto.
 * - corrigeSchemaFisicoSeNecessario() ajusta divergências encontradas em
 *   dev entre o schema físico criado manualmente e o spec da feature
 *   (docs/notificacoes-sms.md) — bug apontado pelo bytest. É condicional/
 *   idempotente: sempre confere o schema real via information_schema antes
 *   de alterar, nunca altera cegamente.
 * - ESTA MIGRATION (incluindo o bloco de correção de schema físico) NÃO
 *   deve ser executada sem confirmação explícita do usuário — não faz
 *   parte de deploy automático.
 */
class LogisticaNotifSms extends Migration
{
    public function up()
    {
        $this->criaInfraestrutura();
        $this->criaTabelasSeNecessario();
        $this->corrigeSchemaFisicoSeNecessario();
    }

    public function down()
    {
        $forge = \Config\Database::forge('dbLogistica');

        $forge->dropTable('log_notif_sms_enviadas', true);
        $forge->dropTable('log_notif_sms_config', true);

        // Infraestrutura (cfg_modulo / cfg_tela / cfg_tela_lista /
        // cfg_perfil_item) não é removida no down() — mesmo critério das
        // demais migrations do projeto (dado de configuração, não é
        // seguro apagar automaticamente customização feita pelo usuário).
    }

    private function criaInfraestrutura()
    {
        $dbDefault = db_connect('default');

        // 1) Módulo "Logística" (não duplica se já existir). Em dev o
        // registro já existia previamente com mod_dbgroup vazio (criado
        // manualmente antes desta feature) — bug apontado pelo bytest:
        // nesse caso só corrige mod_dbgroup, nunca mexe em mod_icone/
        // mod_nome (preserva customização já feita pelo usuário).
        $mod = $dbDefault->table('cfg_modulo')->where('mod_nome', 'Logística')->get()->getRow();
        if (!$mod) {
            $dbDefault->table('cfg_modulo')->insert([
                'mod_nome'    => 'Logística',
                'mod_icone'   => 'fa-solid fa-truck-fast',
                'mod_dbgroup' => 'dbLogistica',
                'mod_ativo'   => 'A',
            ]);
        } elseif (empty($mod->mod_dbgroup)) {
            $dbDefault->table('cfg_modulo')
                ->where('mod_id', $mod->mod_id)
                ->update(['mod_dbgroup' => 'dbLogistica']);
        }
        $modId = $dbDefault->table('cfg_modulo')->where('mod_nome', 'Logística')->get()->getRow()->mod_id;

        // 2) Telas NotifSmsConfig / NotifSmsEnviadas em cfg_tela (idempotente
        // por tel_controler)
        $telasNovas = [
            [
                'tel_nome'        => 'Regras de Notificação SMS',
                'tel_controler'   => 'NotifSmsConfig',
                'tel_model'       => 'log_notif_sms_config',
                'tel_icone'       => 'fa-solid fa-comment-sms',
                'tel_texto_botao' => 'Nova Regra',
                // tel_ident é VARCHAR(5) e segue a convenção de código de tela por
                // ciclo de desenvolvimento (ex.: T22=CfgCor, T42/T43=Fornecedores),
                // não o nome da coluna PK — próximo código livre após T42/T43.
                'tel_ident'       => 'T44',
            ],
            [
                'tel_nome'        => 'SMS Enviados',
                'tel_controler'   => 'NotifSmsEnviadas',
                'tel_model'       => 'log_notif_sms_enviadas',
                'tel_icone'       => 'fa-solid fa-paper-plane',
                'tel_texto_botao' => null,
                'tel_ident'       => 'T45',
            ],
        ];

        $telIds = [];
        foreach ($telasNovas as $t) {
            $existe = $dbDefault->table('cfg_tela')->where('tel_controler', $t['tel_controler'])->get()->getRow();
            if (!$existe) {
                $dbDefault->table('cfg_tela')->insert([
                    'mod_id'          => $modId,
                    'tel_nome'        => $t['tel_nome'],
                    'tel_icone'       => $t['tel_icone'],
                    'tel_controler'   => $t['tel_controler'],
                    'tel_texto_botao' => $t['tel_texto_botao'],
                    'tel_model'       => $t['tel_model'],
                    'tel_ident'       => $t['tel_ident'],
                ]);
                $telIds[$t['tel_controler']] = (int) $dbDefault->insertID();
            } else {
                $telIds[$t['tel_controler']] = (int) $existe->tel_id;
                // Corrige tel_ident gravado errado em execuções anteriores desta
                // migration (valor antigo 'nsc_id'/'nse_id' truncava para 'nsc_i'/
                // 'nse_i' em VARCHAR(5) — tel_ident não é nome de coluna, é código
                // de tela).
                if ($existe->tel_ident !== $t['tel_ident']) {
                    $dbDefault->table('cfg_tela')
                        ->where('tel_id', $existe->tel_id)
                        ->update(['tel_ident' => $t['tel_ident']]);
                }
            }
        }

        // 3) Colunas da listagem — só para NotifSmsConfig (nome / tipo /
        // telefones / status)
        if (isset($telIds['NotifSmsConfig'])) {
            $telIdConfig  = $telIds['NotifSmsConfig'];
            $existeLista = $dbDefault->table('cfg_tela_lista')->where('tel_id', $telIdConfig)->countAllResults();

            if ($existeLista === 0) {
                $colunas = [
                    ['lis_campo' => 'nsc_nome',       'lis_rotulo' => 'Nome'],
                    ['lis_campo' => 'nsc_tipo_regra', 'lis_rotulo' => 'Tipo'],
                    ['lis_campo' => 'nsc_telefones',  'lis_rotulo' => 'Telefones'],
                    ['lis_campo' => 'nsc_ativo',      'lis_rotulo' => 'Status'],
                ];

                foreach ($colunas as $col) {
                    $dbDefault->table('cfg_tela_lista')->insert([
                        'tel_id'         => $telIdConfig,
                        'lis_campo'      => $col['lis_campo'],
                        'lis_rotulo'     => $col['lis_rotulo'],
                        'lis_atualizado' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // 4) Permissão CAEXN para o perfil Super Admin (prf_id = 1,
        // confirmado pelo usuário) nas 2 telas novas (idempotente)
        foreach ($telIds as $telId) {
            $existePermissao = $dbDefault->table('cfg_perfil_item')
                ->where('prf_id', 1)
                ->where('tel_id', $telId)
                ->countAllResults();

            if ($existePermissao === 0) {
                $dbDefault->table('cfg_perfil_item')->insert([
                    'prf_id'        => 1,
                    'mod_id'        => $modId,
                    'tel_id'        => $telId,
                    'pit_permissao' => 'CAEXN',
                ]);
            }
        }
    }

    private function criaTabelasSeNecessario()
    {
        $forge = \Config\Database::forge('dbLogistica');

        // ---------------------------------------------------------------
        // log_notif_sms_config — prefixo nsc_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbLogistica', 'log_notif_sms_config')) {
            $forge->addField([
                'nsc_id' => [
                    'type'           => 'INT',
                    'auto_increment' => true,
                ],
                'nsc_nome' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'nsc_tipo_regra' => [
                    'type'       => 'ENUM',
                    'constraint' => ['entrega', 'saldo_baixo'],
                    'default'    => 'entrega',
                ],
                'nsc_ren_tipo' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'nsc_ren_status_max' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'nsc_condicao' => [
                    'type'       => 'ENUM',
                    'constraint' => ['antes_chegada', 'apos_chegada'],
                    'null'       => true,
                ],
                'nsc_minutos_limite' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'nsc_saldo_minimo' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'nsc_telefones' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nsc_mensagem_template' => [
                    'type' => 'TEXT',
                ],
                'nsc_ativo' => [
                    'type'       => 'CHAR',
                    'constraint' => 1,
                    'default'    => 'A',
                ],
                'nsc_excluido' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('nsc_id', true);
            $forge->createTable('log_notif_sms_config', true);
        }

        // ---------------------------------------------------------------
        // log_notif_sms_enviadas — prefixo nse_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbLogistica', 'log_notif_sms_enviadas')) {
            $forge->addField([
                'nse_id' => [
                    'type'           => 'INT',
                    'auto_increment' => true,
                ],
                'nse_chave' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'nse_nsc_id' => [
                    'type' => 'INT',
                ],
                'nse_data_envio' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $forge->addKey('nse_id', true);
            $forge->addUniqueKey(['nse_chave', 'nse_nsc_id'], 'uk_chave_regra');
            $forge->addKey('nse_data_envio', false, false, 'idx_nse_data_envio');
            $forge->createTable('log_notif_sms_enviadas', true);
        }
    }

    /**
     * Corrige divergências entre o schema físico de dev (tabelas criadas
     * manualmente antes desta feature, com tipos/nulidade diferentes do
     * spec em docs/notificacoes-sms.md) e o que a feature espera. Bug
     * apontado pelo bytest ao rodar contra o ambiente de dev.
     *
     * Sempre condicional: consulta information_schema antes de alterar,
     * nunca faz ALTER TABLE "cego". Idempotente — pode rodar múltiplas
     * vezes sem erro.
     */
    private function corrigeSchemaFisicoSeNecessario()
    {
        $db = db_connect('dbLogistica');

        // -----------------------------------------------------------
        // a) nsc_excluido — em dev a tabela física de log_notif_sms_config
        // foi criada manualmente sem essa coluna. getListaRegras() usa
        // findAll() (soft-delete automático do CI4, que monta
        // "WHERE nsc_excluido IS NULL"), então sem a coluna a query quebra
        // com "Unknown column 'nsc_excluido'" (bug apontado pelo bytest).
        // Nenhuma outra correção deste método depende de nsc_excluido
        // existir, então a ordem em relação às demais não importa — feita
        // primeiro por ser pré-requisito da tabela estar utilizável.
        // -----------------------------------------------------------
        $existeColunaExcluido = $db->query(
            "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            ['log_notif_sms_config', 'nsc_excluido']
        )->getRow();

        if ($existeColunaExcluido && (int) $existeColunaExcluido->qtd === 0) {
            $db->query('ALTER TABLE log_notif_sms_config ADD COLUMN nsc_excluido DATETIME NULL');
            CLI::write(
                'Coluna nsc_excluido adicionada em log_notif_sms_config (ausente na tabela física de dev).',
                'yellow'
            );
        }

        // -----------------------------------------------------------
        // b) nsc_ativo — em dev a tabela física foi criada manualmente
        // como TINYINT antes desta feature; o spec exige CHAR(1) ('A'/'I').
        // Ajuste em 3 passos para não quebrar sob STRICT_TRANS_TABLES.
        // -----------------------------------------------------------
        $tipoAtivo = $db->query(
            "SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            ['log_notif_sms_config', 'nsc_ativo']
        )->getRow();

        if ($tipoAtivo && strtolower($tipoAtivo->DATA_TYPE) !== 'char') {
            $db->query('ALTER TABLE log_notif_sms_config MODIFY nsc_ativo VARCHAR(1) NULL');
            $db->query(
                "UPDATE log_notif_sms_config SET nsc_ativo = CASE WHEN nsc_ativo IN ('1','A') THEN 'A' ELSE 'I' END"
            );
            CLI::write(
                $db->affectedRows() . ' linhas de nsc_ativo corrigidas (TINYINT -> CHAR(1) A/I).',
                'yellow'
            );
            $db->query("ALTER TABLE log_notif_sms_config MODIFY nsc_ativo CHAR(1) NOT NULL DEFAULT 'A'");
        }

        // -----------------------------------------------------------
        // c) colunas NOT NULL no spec, mas NULLable na tabela física de
        // dev — preenche NULL residual com um default razoável antes de
        // travar a coluna como NOT NULL.
        // -----------------------------------------------------------
        // valorDefaultSql = usado no UPDATE de preenchimento; definicaoSql =
        // definição completa de coluna exigida pelo MODIFY (MySQL não aceita
        // "MODIFY coluna NOT NULL" sem repetir o tipo).
        $colunasNotNull = [
            'log_notif_sms_config'   => [
                'nsc_nome'              => ['valorDefaultSql' => "'Sem nome'", 'definicaoSql' => 'VARCHAR(100) NOT NULL'],
                'nsc_telefones'         => ['valorDefaultSql' => "''",         'definicaoSql' => 'VARCHAR(255) NOT NULL'],
                'nsc_mensagem_template' => ['valorDefaultSql' => "''",         'definicaoSql' => 'TEXT NOT NULL'],
            ],
            'log_notif_sms_enviadas' => [
                'nse_nsc_id'     => ['valorDefaultSql' => '0', 'definicaoSql' => 'INT NOT NULL'],
                // Sentinela '1970-01-01 00:00:00' em vez de NOW(): um dado de
                // auditoria de "data de envio" preenchido com NOW() ficaria
                // indistinguível de um envio real. A sentinela deixa o
                // registro corrompido/incompleto visualmente identificável
                // (apontamento não-bloqueante do byrev).
                'nse_data_envio' => ['valorDefaultSql' => "'1970-01-01 00:00:00'", 'definicaoSql' => 'DATETIME NOT NULL'],
            ],
        ];

        foreach ($colunasNotNull as $tabela => $colunas) {
            foreach ($colunas as $coluna => $def) {
                $info = $db->query(
                    "SELECT IS_NULLABLE FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                    [$tabela, $coluna]
                )->getRow();

                if ($info && strtoupper($info->IS_NULLABLE) === 'YES') {
                    $db->query("UPDATE {$tabela} SET {$coluna} = {$def['valorDefaultSql']} WHERE {$coluna} IS NULL");
                    CLI::write(
                        $db->affectedRows() . " linhas de {$tabela}.{$coluna} corrigidas (NULL -> {$def['valorDefaultSql']}).",
                        'yellow'
                    );
                    $db->query("ALTER TABLE {$tabela} MODIFY {$coluna} {$def['definicaoSql']}");
                }
            }
        }

        // -----------------------------------------------------------
        // d) idx_nse_data_envio — MySQL/MariaDB não suporta
        // "ADD INDEX IF NOT EXISTS", checa existência via
        // information_schema.STATISTICS antes de criar.
        // -----------------------------------------------------------
        $existeIndice = $db->query(
            "SELECT COUNT(*) AS qtd FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            ['log_notif_sms_enviadas', 'idx_nse_data_envio']
        )->getRow();

        if ($existeIndice && (int) $existeIndice->qtd === 0) {
            $db->query('ALTER TABLE log_notif_sms_enviadas ADD INDEX idx_nse_data_envio (nse_data_envio)');
        }
    }

    /**
     * Helper local — evita depender de $this->forge->fieldExists (que não
     * cobre "tabela existe?") e evita erro em reexecução da migration.
     */
    private function tableExists(string $group, string $table): bool
    {
        return db_connect($group)->tableExists($table);
    }
}
