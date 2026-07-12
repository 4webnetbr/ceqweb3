<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Infraestrutura de T43 (Notificação de Evento) — as 4 tabelas
 * (`oco_notif_evento`, `oco_notif_evento_produto`, `oco_notif_evento_anexo`,
 * `oco_notif_evento_acao`) e o cadastro em `cfg_tela`/`cfg_modulo` já foram
 * criados por `2026-07-12-000001_FornecedoresT42T43` (rodada anterior, já
 * aplicada em dev). Esta migration completa o que faltou para T43 ficar
 * funcional:
 *  - `cfg_tela_lista` da listagem de T43 (RN02.1 — mesmas colunas de T42).
 *  - VIEW `vw_oco_notif_evento_relac` (listagem/show do cabeçalho da
 *    notificação — 1 linha por nev_id, agregando produto/fabricante/lote/
 *    data via GROUP_CONCAT quando há mais de um produto selecionado, já que
 *    a notificação é 1:N com produtos — ver RN03.2/RN03.6 do documento de
 *    desenvolvimento, que preveem múltiplas datas/lotes por notificação).
 *  - VIEW `vw_oco_notif_evento_produto_relac` (grid "Dados do Produto" da
 *    aba Dados Gerais — 1 linha por nvp_id, com os campos read-only via T42/T11).
 *
 * IMPORTANTE: colunas de `vw_oco_ocorrencia_completa_relac` e
 * `vw_cfg_status_relac` já conferidas contra o schema real em
 * 2026-07-12-000001 (ver comentário daquele arquivo) — reaproveitadas aqui
 * sem novas suposições.
 *
 * Esta migration NÃO deve ser executada sem confirmação do usuário.
 */
class FornecedoresT43Infra extends Migration
{
    public function up()
    {
        $this->criaColunasListaT43();
        $this->criaViewsT43();
    }

    public function down()
    {
        $dbOco = db_connect('dbOcorrencia');
        $dbOco->query('DROP VIEW IF EXISTS vw_oco_notif_evento_produto_relac');
        $dbOco->query('DROP VIEW IF EXISTS vw_oco_notif_evento_relac');

        // cfg_tela_lista de T43 não é removida no down() — mesmo critério
        // já usado em 2026-07-12-000001 (dado de configuração).
    }

    private function criaColunasListaT43()
    {
        $dbDefault = db_connect('default');

        $tela = $dbDefault->table('cfg_tela')->where('tel_controler', 'NotifEvento')->get()->getRow();
        if (!$tela) {
            // Não deveria acontecer — cfg_tela de T43 já é criada pela
            // migration anterior (2026-07-12-000001). Aborta com segurança.
            return;
        }

        $existeLista = $dbDefault->table('cfg_tela_lista')->where('tel_id', $tela->tel_id)->countAllResults();
        if ($existeLista > 0) {
            return; // já cadastrado (reexecução da migration)
        }

        // RN02.1 — mesmas colunas de T42 (N°, Data, Produto, Fabricante,
        // Lote, Usuário, Status); N° coberto automaticamente pela chave
        // (nev_id) via montaColunasLista(), igual à T42.
        $colunasT43 = [
            ['lis_campo' => 'nev_data',       'lis_rotulo' => 'Data'],
            ['lis_campo' => 'nev_produtos',   'lis_rotulo' => 'Produto'],
            ['lis_campo' => 'nev_fabricante', 'lis_rotulo' => 'Fabricante'],
            ['lis_campo' => 'nev_lotes',      'lis_rotulo' => 'Lote'],
            ['lis_campo' => 'usu_nome',       'lis_rotulo' => 'Usuário'],
            ['lis_campo' => 'stt_nome',       'lis_rotulo' => 'Status'],
        ];

        foreach ($colunasT43 as $col) {
            $dbDefault->table('cfg_tela_lista')->insert([
                'tel_id'         => $tela->tel_id,
                'lis_campo'      => $col['lis_campo'],
                'lis_rotulo'     => $col['lis_rotulo'],
                'lis_atualizado' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function criaViewsT43()
    {
        $dbOco     = db_connect('dbOcorrencia');
        $schemaDef = db_connect('default')->getDatabase();

        $dbOco->query('DROP VIEW IF EXISTS vw_oco_notif_evento_produto_relac');
        $dbOco->query('DROP VIEW IF EXISTS vw_oco_notif_evento_relac');

        // ------------------------------------------------------------
        // vw_oco_notif_evento_produto_relac — 1 linha por produto
        // selecionado (aba Dados Gerais, grid "Dados do Produto").
        // Reaproveita vw_oco_ocorrencia_completa_relac (via oco_notif_desvio)
        // para os campos read-only (Data/Código ERP/Descrição/Fabricante/
        // Lote/Validade/Quantidade — RN03.2 a RN03.8 de T43).
        //
        // ACHADO EM EXECUÇÃO REAL (2026-07-12, smoke test de T43):
        // `vw_oco_ocorrencia_completa_relac` NÃO é 1 linha por oco_id (fan-out
        // pré-existente via `pro_sap_prod_fabric`, mesmo problema documentado
        // em 2026-07-12-000001_FornecedoresT42T43::criaViewsT42()) — sem a
        // subquery DISTINCT abaixo, cada produto da notificação vinha
        // multiplicado centenas de vezes. Mesma correção aplicada aqui.
        // ------------------------------------------------------------
        $sqlProduto = "CREATE VIEW vw_oco_notif_evento_produto_relac AS
            SELECT
                nvp.nvp_id,
                nvp.nev_id,
                nvp.ndv_id,
                nvp.nvp_defeito,
                o.oco_data,
                o.pro_id,
                o.pro_codpro,
                o.pro_despro,
                o.lot_id,
                o.lot_lote,
                o.lot_validade,
                o.fab_apeFab,
                o.oco_qtd
            FROM oco_notif_evento_produto nvp
            INNER JOIN oco_notif_desvio nd ON nd.ndv_id = nvp.ndv_id
            INNER JOIN (
                SELECT DISTINCT
                    oco_id, oco_data, pro_id, pro_codpro, pro_despro,
                    lot_id, lot_lote, lot_validade, fab_apeFab, oco_qtd
                FROM vw_oco_ocorrencia_completa_relac
            ) o ON o.oco_id = nd.oco_id
        ";
        $dbOco->query($sqlProduto);

        // ------------------------------------------------------------
        // vw_oco_notif_evento_relac — 1 linha por notificação (cabeçalho),
        // usada na listagem (RN02.1) e no show()/edit() do cabeçalho.
        // Decisão do byarq (revisão 2026-07-12), substituindo a versão
        // anterior (GROUP_CONCAT com vírgula em Produto/Fabricante/Lote):
        //  - nev_data: MIN(p.oco_data) — mantido, já estava correto.
        //  - nev_fabricante: MIN(p.fab_apeFab), SEM agregação/tooltip — RN03.1
        //    já garante que todos os produtos de uma notificação são do
        //    mesmo fabricante, então não há lista a resumir aqui.
        //  - nev_produtos_raw / nev_lotes_raw: GROUP_CONCAT bruto (separador
        //    \x1F, não usado em nenhum dado de produto/lote real), consumido
        //    por NotifEvento::lista() (App\Controllers\Fornecedores\NotifEvento),
        //    que formata como "primeiro item + e mais N" com a lista
        //    completa em <ttp>...</ttp> (tooltip nativo de montaListaDados(),
        //    já documentado em rascunho-runtime-js.md — nenhum mecanismo
        //    novo). As colunas finais nev_produtos/nev_lotes (usadas por
        //    cfg_tela_lista) são setadas em runtime pelo controller, não
        //    saem prontas da view.
        // ------------------------------------------------------------
        $sqlEvento = "CREATE VIEW vw_oco_notif_evento_relac AS
            SELECT
                nev.nev_id,
                nev.nev_qtd_adquirida,
                nev.nev_numero_nf,
                nev.nev_fornecedor,
                nev.nev_fabricacao,
                nev.nev_providencias,
                nev.nev_notificado,
                nev.nev_parecer,
                nev.nev_notivisa,
                nev.nev_notivisa_num,
                nev.stt_id,
                nev.usu_criou,
                nev.nev_criado,
                nev.nev_alterado,
                nev.nev_excluido,
                MIN(p.oco_data)                                      AS nev_data,
                GROUP_CONCAT(DISTINCT p.pro_despro SEPARATOR 0x1F)   AS nev_produtos_raw,
                MIN(p.fab_apeFab)                                    AS nev_fabricante,
                GROUP_CONCAT(DISTINCT p.lot_lote   SEPARATOR 0x1F)   AS nev_lotes_raw,
                st.stt_nome,
                st.stt_cor,
                st.stt_ordem,
                st.stt_exclusao,
                st.stt_edicao,
                st.stt_ativo,
                st.mod_id,
                st.tel_id AS notif_tel_id
            FROM oco_notif_evento nev
            LEFT JOIN vw_oco_notif_evento_produto_relac p ON p.nev_id = nev.nev_id
            INNER JOIN {$schemaDef}.vw_cfg_status_relac st ON st.stt_id = nev.stt_id
            GROUP BY nev.nev_id
        ";
        $dbOco->query($sqlEvento);
    }
}
