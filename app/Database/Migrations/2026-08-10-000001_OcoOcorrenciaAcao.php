<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela `oco_ocorrencia_acao` — registro individual de cada ação aplicável
 * a uma ocorrência (pendente ou executada), substituindo o modelo anterior
 * de colunas escalares em `oco_ocorrencia` (1 ação por ocorrência).
 *
 * Também insere o novo status "Parcialmente Tratada" em `cfg_status`,
 * escopado pelo mesmo `tel_id`/`mod_id` já usado pelos status existentes de
 * Ocorrências (28 Pendente / 29 Finalização Automática / 30 Finalizada),
 * resolvidos dinamicamente via JOIN com `cfg_tela` (tel_controler =
 * 'OcoOcorrencia') — sem hardcode de id numérico.
 *
 * Ver docs/desenvolvimento/ocorrencia-acao-parcial-plano.md.
 *
 * IMPORTANTE: esta migration NÃO deve ser executada sem confirmação do
 * usuário.
 */
class OcoOcorrenciaAcao extends Migration
{
    public function up()
    {
        $this->criaTabela();
        $this->criaStatusParcial();
    }

    public function down()
    {
        $dbOco = db_connect('dbOcorrencia');
        $dbOco->dropTable('oco_ocorrencia_acao', true);

        // Status "Parcialmente Tratada" (cfg_status) não é removido no
        // down() — remoção de dados de configuração deve ser feita
        // manualmente, para não arriscar apagar customização feita pelo
        // usuário entre o up() e um eventual rollback.
    }

    private function criaTabela()
    {
        if ($this->tableExists('dbOcorrencia', 'oco_ocorrencia_acao')) {
            return;
        }

        $forge = \Config\Database::forge('dbOcorrencia');

        $forge->addField([
            'oac_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'oco_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Ocorrência dona da ação (oco_ocorrencia) — sem FK física, padrão do módulo',
            ],
            'tpa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Ação (oco_tipo_acao)',
            ],
            'tpa_tipo' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'comment'    => 'Snapshot de oco_tipo_acao.tpa_tipo no momento em que a linha foi criada',
            ],
            'oac_auto' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'default'    => 'N',
                'comment'    => 'Snapshot de oco_subt_ocorrencia_acao.sta_fina no momento do seed (ações ad-hoc = N, nunca auto)',
            ],
            'tmo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Config./preenchido quando tpa_tipo = 3 (Gerar Movimentação)',
            ],
            'stt_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Status do PRODUTO quando executada (tpa_tipo = 4 — Alterar Status)',
            ],
            'tel_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Presente quando tpa_tipo = 2 (Abrir Tela)',
            ],
            'oco_justi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'oac_executada' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'default'    => 'N',
            ],
            'oac_erro' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'oac_msg' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'usu_executou' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'null quando execução automática ou ainda pendente',
            ],
            'oac_automatica' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Execução veio da criação (processAfterSave), não de tratativa manual',
            ],
            'oac_criado' => [
                'type' => 'DATETIME',
            ],
            'oac_executado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $forge->addKey('oac_id', true);
        $forge->addKey('oco_id');
        $forge->addKey('tpa_id');
        $forge->createTable('oco_ocorrencia_acao', true);
    }

    /**
     * Insere o status "Parcialmente Tratada" em cfg_status, reaproveitando
     * o mesmo tel_id/mod_id já usado pelos status existentes de Ocorrências
     * (resolvido via JOIN com cfg_tela.tel_controler = 'OcoOcorrencia', sem
     * hardcode de id numérico). Idempotente: não duplica em reexecução.
     */
    private function criaStatusParcial()
    {
        $dbDefault = db_connect('default');

        $statusRef = $dbDefault->table('cfg_status s')
            ->select('s.tel_id, s.mod_id')
            ->join('cfg_tela t', 't.tel_id = s.tel_id')
            ->whereIn('s.stt_nome', ['Pendente', 'Finalizada'])
            ->where('t.tel_controler', 'OcoOcorrencia')
            ->get()->getRow();

        if (!$statusRef) {
            // Não deveria acontecer em ambiente já com o módulo de
            // Ocorrências configurado — aborta sem quebrar a migration.
            return;
        }

        $telId = (int) $statusRef->tel_id;
        $modId = (int) $statusRef->mod_id;

        $jaExiste = $dbDefault->table('cfg_status')
            ->where('tel_id', $telId)
            ->where('stt_nome', 'Parcialmente Tratada')
            ->countAllResults();

        if ($jaExiste > 0) {
            return;
        }

        $corLaranja = $dbDefault->table('cfg_cor')
            ->like('cor_nome', 'Laranja')
            ->orderBy('cor_id')
            ->get()->getRow();

        // stt_exclusao/stt_edicao/stt_impressao/stt_disponivel alinhados com
        // os status irmãos já existentes da tela (28 Pendente / 29
        // Finalização Automática): exclusão e impressão permitidas,
        // disponível para seleção, sem edição livre do nome (mesmo padrão
        // dos três).
        $dbDefault->table('cfg_status')->insert([
            'stt_nome'       => 'Parcialmente Tratada',
            'mod_id'         => $modId,
            'tel_id'         => $telId,
            'cor_id'         => $corLaranja->cor_id ?? null,
            'stt_exclusao'   => 'S',
            'stt_edicao'     => 'N',
            'stt_impressao'  => 'S',
            'stt_disponivel' => 'S',
            'stt_ativo'      => 'A',
            'stt_ordem'      => 2, // entre Pendente (1) e Finalizada (3)
        ]);
    }

    private function tableExists(string $group, string $table): bool
    {
        return db_connect($group)->tableExists($table);
    }
}
