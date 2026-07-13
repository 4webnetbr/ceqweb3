<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * RN03.1 (byarq, revisão 02) — 2ª camada de proteção contra vínculo
 * duplicado de produto em T43: 1 desvio de qualidade (ndv_id) só pode estar
 * vinculado a UMA notificação de evento, nunca duas. A 1ª camada é a
 * revalidação de elegibilidade em runtime
 * (App\Models\Fornec\FornecNotifDesvioModel::validaDisponibilidade(),
 * chamada em NotifEvento::selecionaProdutos()/store()); esta migration
 * adiciona a UNIQUE KEY no banco, que cobre a janela de corrida que a
 * checagem em PHP sozinha não pega (duplo clique / requisições concorrentes).
 *
 * Migration separada (não uma edição de 2026-07-12-000001_FornecedoresT42T43,
 * que já rodou em dev no batch 1, 2026-07-12 09:40:37) — migrations já
 * executadas não são reeditadas.
 *
 * Pré-requisito: a tabela `oco_notif_evento_produto` não deve ter linhas
 * com `ndv_id` duplicado no momento de rodar esta migration (checar antes,
 * a migration NÃO faz limpeza automática de dados).
 *
 * Correção (falha na 1ª tentativa): a tabela já tinha um índice comum
 * chamado `ndv_id` (criado pelo `addKey('ndv_id')` original em
 * 2026-07-12-000001). Nomes de índice são únicos por tabela no MySQL,
 * independente do tipo — não dá pra ter um KEY e um UNIQUE KEY com o mesmo
 * nome ao mesmo tempo. Em vez de dar um nome diferente à unique key (o que
 * deixaria dois índices redundantes na mesma coluna), o `up()` troca o
 * índice comum pela unique key NUM SÓ `ALTER TABLE` (DROP + ADD do mesmo
 * nome `ndv_id`) — fica só 1 índice na coluna, agora único.
 *
 * Esta migration NÃO deve ser executada sem confirmação do usuário.
 */
class FornecedoresT43ProdutoUnique extends Migration
{
    protected $DBGroup = 'dbOcorrencia';

    public function up()
    {
        $this->db->query('ALTER TABLE oco_notif_evento_produto DROP INDEX ndv_id, ADD UNIQUE KEY ndv_id (ndv_id)');
    }

    public function down()
    {
        // Reverte para o estado original: unique key -> índice comum de novo.
        $this->db->query('ALTER TABLE oco_notif_evento_produto DROP INDEX ndv_id, ADD KEY ndv_id (ndv_id)');
    }
}
