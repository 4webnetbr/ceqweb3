<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Infraestrutura + tabelas novas do módulo Fornecedores (T42 — Desvio de
 * Qualidade / T43 — Notificação de Evento), conforme
 * docs/desenvolvimento/fornecedores-t42-t43-dev.md (seções 4 e 7).
 *
 * IMPORTANTE:
 * - Colunas de `vw_oco_ocorrencia_completa_relac` conferidas via
 *   `php spark db:table vw_oco_ocorrencia_completa_relac --metadata --dbgroup dbOcorrencia`
 *   (ambiente prd, 2026-07-12): tpo_nome, sut_nome, pro_despro, pro_codpro,
 *   lot_lote, lot_validade, fab_apeFab, tel_nome, oco_qtd, oco_data — todas
 *   confirmadas. A view NÃO tem coluna `usu_nome` — ao contrário do que uma
 *   primeira versão desta migration assumia, `usu_nome` não é resolvido por
 *   JOIN, e sim via log de auditoria (`buscaLogTabela()`/`buscaLogTabelaFirst()`,
 *   mesmo padrão de `OcoOcorrencia::show()`/`lista()`); por isso não entra
 *   nesta VIEW — é responsabilidade de
 *   `FornecNotifDesvioModel`/`NotifDesvio` (controller) preencher em runtime.
 * - Colunas de `vw_cfg_status_relac` também conferidas (schema `default`):
 *   stt_nome, stt_cor, stt_ordem, stt_exclusao, stt_edicao, stt_ativo,
 *   mod_id, tel_id — todas confirmadas.
 * - `cor_id` de `cfg_status` é resolvido por nome em `cfg_cor` (Amarelo /
 *   Verde) com fallback NULL — ajustar cor definitiva via tela CfgStatus,
 *   se preciso, depois de migrado.
 * - Esta migration NÃO deve ser executada sem confirmação do usuário.
 */
class FornecedoresT42T43 extends Migration
{
    public function up()
    {
        $this->criaInfraestrutura();
        $this->criaTabelasNovas();
        $this->criaViewsT42();
    }

    public function down()
    {
        $dbOco = \Config\Database::forge('dbOcorrencia');

        db_connect('dbOcorrencia')->query('DROP VIEW IF EXISTS vw_oco_notif_desvio_relac');

        $dbOco->dropTable('oco_notif_evento_acao', true);
        $dbOco->dropTable('oco_notif_evento_anexo', true);
        $dbOco->dropTable('oco_notif_evento_produto', true);
        $dbOco->dropTable('oco_notif_evento', true);
        $dbOco->dropTable('oco_notif_desvio', true);

        // Infraestrutura (cfg_status / cfg_tela / cfg_modulo / oco_tipo_acao)
        // não é removida no down() — remoção de dados de configuração deve
        // ser feita manualmente, para não arriscar apagar customização feita
        // pelo usuário entre o up() e um eventual rollback.
    }

    private function criaInfraestrutura()
    {
        $dbDefault = db_connect('default');
        $dbOco     = db_connect('dbOcorrencia');

        // 1) Módulo "Fornecedores" (não duplica se já existir)
        $mod = $dbDefault->table('cfg_modulo')->where('mod_nome', 'Fornecedores')->get()->getRow();
        if (!$mod) {
            $dbDefault->table('cfg_modulo')->insert([
                'mod_nome'    => 'Fornecedores',
                'mod_icone'   => 'fa-solid fa-truck-field',
                'mod_dbgroup' => 'dbOcorrencia',
                'mod_ativo'   => 'A',
            ]);
        }
        $modId = $dbDefault->table('cfg_modulo')->where('mod_nome', 'Fornecedores')->get()->getRow()->mod_id;

        // 2) Telas T42 / T43 em cfg_tela (idempotente por tel_controler)
        $telasNovas = [
            [
                'tel_nome'        => 'Desvio de Qualidade',
                'tel_controler'   => 'NotifDesvio',
                'tel_model'       => 'oco_notif_desvio',
                'tel_icone'       => 'fa-solid fa-triangle-exclamation',
                'tel_texto_botao' => null, // T42 não tem botão "+" (integração automática de T11)
                'tel_ident'       => 'ndv_id',
            ],
            [
                'tel_nome'        => 'Notificação de Evento',
                'tel_controler'   => 'NotifEvento',
                'tel_model'       => 'oco_notif_evento',
                'tel_icone'       => 'fa-solid fa-bullhorn',
                'tel_texto_botao' => 'Nova Notificação',
                'tel_ident'       => 'nev_id',
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
            }
        }

        // 3) Status Pendente / Concluída, um par por tela nova (cfg_status é
        // escopado por tel_id — ver decisão 3.3 do documento de desenvolvimento)
        $corAmarelo = $dbDefault->table('cfg_cor')->like('cor_nome', 'Amarelo')->get()->getRow();
        $corVerde   = $dbDefault->table('cfg_cor')->like('cor_nome', 'Verde')->get()->getRow();

        foreach ($telIds as $telId) {
            $existeStatus = $dbDefault->table('cfg_status')->where('tel_id', $telId)->countAllResults();
            if ($existeStatus > 0) {
                continue; // já cadastrado (reexecução da migration)
            }

            $dbDefault->table('cfg_status')->insert([
                'stt_nome'       => 'Pendente',
                'mod_id'         => $modId,
                'tel_id'         => $telId,
                'cor_id'         => $corAmarelo->cor_id ?? null,
                'stt_exclusao'   => 'N', // registro de integração automática — sem exclusão manual
                'stt_edicao'     => 'S', // editável enquanto pendente
                'stt_impressao'  => 'S',
                'stt_disponivel' => 'S',
                'stt_ativo'      => 'A',
                'stt_ordem'      => 1,
            ]);
            $dbDefault->table('cfg_status')->insert([
                'stt_nome'       => 'Concluída',
                'mod_id'         => $modId,
                'tel_id'         => $telId,
                'cor_id'         => $corVerde->cor_id ?? null,
                'stt_exclusao'   => 'N',
                'stt_edicao'     => 'N', // RN — bloqueio server-side depois de concluído
                'stt_impressao'  => 'S',
                'stt_disponivel' => 'S',
                'stt_ativo'      => 'A',
                'stt_ordem'      => 2,
            ]);
        }

        // 3.1) Colunas da listagem de T42 (cfg_tela_lista) — RN02.1: N°,
        // Data, Produto, Fabricante, Lote, Usuário, Status. `montaColunasLista()`/
        // `montaColunasCampos()` (app/Common.php) dependem exclusivamente
        // desta tabela (via ConfigTelaListaModel::getListagem($tel_id)); sem
        // isso a grid de NotifDesvio só mostra a chave (ndv_id) + Ação. O N°
        // já é coberto automaticamente pelo `array_unshift($arr_campos, $chave)`
        // de montaColunasLista() — por isso não entra aqui, mesmo padrão
        // seguido pelas demais telas do sistema (ver App\Controllers\Config\CfgTela).
        if (isset($telIds['NotifDesvio'])) {
            $telIdDesvio = $telIds['NotifDesvio'];
            $existeLista = $dbDefault->table('cfg_tela_lista')->where('tel_id', $telIdDesvio)->countAllResults();

            if ($existeLista === 0) {
                $colunasT42 = [
                    ['lis_campo' => 'oco_data',   'lis_rotulo' => 'Data'],
                    ['lis_campo' => 'pro_despro', 'lis_rotulo' => 'Produto'],
                    ['lis_campo' => 'fab_apeFab', 'lis_rotulo' => 'Fabricante'],
                    ['lis_campo' => 'lot_lote',   'lis_rotulo' => 'Lote'],
                    ['lis_campo' => 'usu_nome',   'lis_rotulo' => 'Usuário'],
                    ['lis_campo' => 'stt_nome',   'lis_rotulo' => 'Status'],
                ];

                foreach ($colunasT42 as $col) {
                    $dbDefault->table('cfg_tela_lista')->insert([
                        'tel_id'         => $telIdDesvio,
                        'lis_campo'      => $col['lis_campo'],
                        'lis_rotulo'     => $col['lis_rotulo'],
                        'lis_atualizado' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // 4) Ação nova "Notificação do Fornecedor" em oco_tipo_acao (T8),
        // tpa_tipo = 5 (novo valor — 1 Justificar / 2 Abrir Tela /
        // 3 Gerar Movimentação / 4 Alterar Status / 5 Notificação do
        // Fornecedor). Reaproveita 100% o cadastro de T9 já existente
        // (oco_subt_ocorrencia_acao) para associar por subtipo — sem
        // alteração de schema em T9 (ver decisão 3.2 do doc de desenvolvimento).
        $acaoExistente = $dbOco->table('oco_tipo_acao')->where('tpa_nome', 'Notificação do Fornecedor')->get()->getRow();
        if (!$acaoExistente) {
            $dbOco->table('oco_tipo_acao')->insert([
                'tpa_nome'  => 'Notificação do Fornecedor',
                'tpa_tipo'  => 5,
                'tpa_ativo' => 'A',
            ]);
        } elseif ((string) $acaoExistente->tpa_tipo !== '5') {
            // Autocorreção (achado em execução real, 2026-07-12): uma
            // tentativa anterior desta migration (que falhou depois, na
            // criação da VIEW) deixou esta linha gravada com tpa_tipo NULL
            // em vez de '5' — causa não identificada com certeza (não é
            // limitação de tipo de coluna: char(1) aceita '5' normalmente,
            // confirmado via UPDATE manual de diagnóstico). Reforça aqui,
            // de forma idempotente, para não depender de reprocessar o
            // INSERT.
            $dbOco->table('oco_tipo_acao')->where('tpa_id', $acaoExistente->tpa_id)->update(['tpa_tipo' => 5]);
        }
    }

    private function criaTabelasNovas()
    {
        $forge = \Config\Database::forge('dbOcorrencia');

        // ---------------------------------------------------------------
        // oco_notif_desvio (T42) — prefixo ndv_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbOcorrencia', 'oco_notif_desvio')) {
            $forge->addField([
                'ndv_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'oco_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'comment'  => 'Origem T11 (oco_ocorrencia) — tipo/subtipo/data/usuário/produto/lote via JOIN, não duplicado aqui',
                ],
                'ndv_local' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 50,
                    'comment'  => 'RN03.7 — texto livre, 5 a 50 caracteres',
                ],
                'ndv_descreva' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 200,
                    'comment'  => 'RN03.14 — texto livre, 5 a 200 caracteres',
                ],
                'stt_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'comment'  => 'FK cfg_status (Pendente/Concluída de T42)',
                ],
                'usu_criou' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null'     => true,
                ],
                'ndv_criado' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                ],
                'ndv_alterado' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                ],
                'ndv_excluido' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                ],
            ]);
            $forge->addKey('ndv_id', true);
            // RN02.3 — 1 notificação de desvio por ocorrência de origem;
            // reforça no banco a idempotência de
            // OcoTrataOcorrencia::gerarNotificacaoDesvio() (a checagem por
            // SELECT antes do INSERT tem janela de corrida em duplo
            // clique/requisições concorrentes).
            $forge->addUniqueKey('oco_id');
            $forge->addKey('stt_id');
            $forge->createTable('oco_notif_desvio', true);
        }

        // ---------------------------------------------------------------
        // oco_notif_evento (T43 — cabeçalho) — prefixo nev_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbOcorrencia', 'oco_notif_evento')) {
            $forge->addField([
                'nev_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nev_qtd_adquirida' => [
                    'type'     => 'INT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'comment'  => 'RN03.10',
                ],
                'nev_numero_nf' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 20,
                    'comment'  => 'RN03.11 — numérico, até 20 dígitos (varchar para não perder zero à esquerda)',
                ],
                'nev_fornecedor' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 200,
                    'comment'  => 'RN03.12 — texto livre, sem cadastro de fornecedor',
                ],
                'nev_fabricacao' => [
                    'type'    => 'DATE',
                    'comment' => 'RN03.13',
                ],
                'nev_providencias' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 500,
                    'comment'  => 'RN03.14',
                ],
                'nev_notificado' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 200,
                    'comment'  => 'RN03.15',
                ],
                'nev_parecer' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 500,
                    'comment'  => 'RN03.17',
                ],
                'nev_notivisa' => [
                    'type'     => 'CHAR',
                    'constraint' => 1,
                    'default'  => 'N',
                    'comment'  => 'RN03.18 — S/N',
                ],
                'nev_notivisa_num' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 50,
                    'null'     => true,
                    'comment'  => 'RN03.19 — obrigatório somente se nev_notivisa = S',
                ],
                'stt_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'comment'  => 'FK cfg_status (Pendente/Concluída de T43)',
                ],
                'usu_criou' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null'     => true,
                ],
                'nev_criado' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'nev_alterado' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'nev_excluido' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('nev_id', true);
            $forge->addKey('stt_id');
            $forge->createTable('oco_notif_evento', true);
        }

        // ---------------------------------------------------------------
        // oco_notif_evento_produto (T43 — grid produtos) — prefixo nvp_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbOcorrencia', 'oco_notif_evento_produto')) {
            $forge->addField([
                'nvp_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nev_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'ndv_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'comment'  => 'Rastreia de qual desvio (T42) veio o produto/lote selecionado',
                ],
                'nvp_defeito' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 200,
                    'comment'  => 'RN03.9 — cópia editável de ndv_descreva (T42)',
                ],
            ]);
            $forge->addKey('nvp_id', true);
            $forge->addKey('nev_id');
            $forge->addKey('ndv_id');
            $forge->createTable('oco_notif_evento_produto', true);
        }

        // ---------------------------------------------------------------
        // oco_notif_evento_anexo (Providências + Parecer Final) — prefixo nva_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbOcorrencia', 'oco_notif_evento_anexo')) {
            $forge->addField([
                'nva_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nev_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nva_origem' => [
                    'type'     => 'ENUM',
                    'constraint' => ['PROVID', 'PARECER'],
                    'comment'  => 'RN03.16 (Providências) / RN03.20 (Parecer Final)',
                ],
                'nva_arquivo' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nva_nome_original' => [
                    'type'     => 'VARCHAR',
                    'constraint' => 255,
                ],
                'usu_criou' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null'     => true,
                ],
                'nva_criado' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('nva_id', true);
            $forge->addKey('nev_id');
            $forge->createTable('oco_notif_evento_anexo', true);
        }

        // ---------------------------------------------------------------
        // oco_notif_evento_acao (Aba Ações) — prefixo nac_
        // ---------------------------------------------------------------
        if (!$this->tableExists('dbOcorrencia', 'oco_notif_evento_acao')) {
            $forge->addField([
                'nac_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nev_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'tpa_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nac_ordem' => [
                    'type'     => 'INT',
                    'constraint' => 5,
                    'comment'  => 'RN03.22 — ordem de execução sequencial',
                ],
                'stt_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null'     => true,
                    'comment'  => 'presente quando tpa_tipo = 4 (Alterar Status)',
                ],
                'tmo_id' => [
                    'type'     => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null'     => true,
                    'comment'  => 'presente quando tpa_tipo = 3 (Gerar Movimentação)',
                ],
            ]);
            $forge->addKey('nac_id', true);
            $forge->addKey('nev_id');
            $forge->addKey('tpa_id');
            $forge->createTable('oco_notif_evento_acao', true);
        }
    }

    /**
     * Cria a view de listagem/JOIN de T42, reaproveitando a view já
     * existente `vw_oco_ocorrencia_completa_relac` (dados de T11) e
     * cruzando com `cfg_status` (schema "default", cross-database — mesmo
     * padrão de resolução dinâmica de schema usado em
     * ConfigDicDadosModel::getDbGroupAndSchema()).
     */
    private function criaViewsT42()
    {
        $dbOco     = db_connect('dbOcorrencia');
        $schemaDef = db_connect('default')->getDatabase();

        $dbOco->query('DROP VIEW IF EXISTS vw_oco_notif_desvio_relac');

        // ACHADO EM EXECUÇÃO REAL (2026-07-12, smoke test): `vw_oco_ocorrencia_completa_relac`
        // NÃO é 1 linha por oco_id — o JOIN dela com `pro_sap_prod_fabric`
        // (`pro.pro_codpro = pfb.pro_codPro`) tem múltiplas linhas
        // duplicadas por produto naquela tabela (dado pré-existente, fora
        // do escopo desta tarefa), causando explosão cartesiana ao juntar
        // (chegou a devolver 562 linhas para 1 único oco_id em teste real).
        // Todo o resto do sistema "escapa" disso porque só usa essa view
        // via `->getRow()` (pega só a 1ª linha, ex.: OcorreOcorrenciaModel::getOcorrencia()) —
        // aqui, como preciso fazer JOIN de verdade (1 registro por
        // ndv_id/nev_id), envolvo a view numa subquery com SELECT DISTINCT
        // das colunas realmente usadas, o que elimina as duplicatas (os
        // valores em si já são idênticos entre as linhas duplicadas,
        // confirmado em teste — SELECT DISTINCT fab_apeFab/pro_despro/
        // lot_lote para o mesmo oco_id sempre retornou 1 linha).
        $sql = "CREATE VIEW vw_oco_notif_desvio_relac AS
            SELECT
                nd.ndv_id,
                nd.oco_id,
                nd.ndv_local,
                nd.ndv_descreva,
                nd.stt_id,
                nd.usu_criou,
                nd.ndv_criado,
                nd.ndv_alterado,
                nd.ndv_excluido,
                o.tpo_id,
                o.tpo_nome,
                o.sut_id,
                o.sut_nome,
                o.oco_data,
                o.tel_id   AS oco_tel_id,
                o.tel_nome AS oco_tel_nome,
                o.pro_id,
                o.pro_codpro,
                o.pro_despro,
                o.lot_id,
                o.lot_lote,
                o.lot_validade,
                o.fab_apeFab,
                o.oco_qtd,
                st.stt_nome,
                st.stt_cor,
                st.stt_ordem,
                st.stt_exclusao,
                st.stt_edicao,
                st.stt_ativo,
                st.mod_id,
                st.tel_id AS notif_tel_id
            FROM oco_notif_desvio nd
            INNER JOIN (
                SELECT DISTINCT
                    oco_id, tpo_id, tpo_nome, sut_id, sut_nome, oco_data,
                    tel_id, tel_nome, pro_id, pro_codpro, pro_despro,
                    lot_id, lot_lote, lot_validade, fab_apeFab, oco_qtd
                FROM vw_oco_ocorrencia_completa_relac
            ) o ON o.oco_id = nd.oco_id
            INNER JOIN {$schemaDef}.vw_cfg_status_relac st ON st.stt_id = nd.stt_id
        ";

        $dbOco->query($sql);
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
