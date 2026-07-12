<?php
/**
 * RN03.1 — tela intermediária de seleção de produtos: desvios (T42)
 * Concluídos e ainda não vinculados a nenhuma notificação. Seleção inicial
 * = todos os produtos do mesmo fabricante do primeiro item da lista;
 * usuário pode ajustar antes de confirmar (submete para selecionaProdutos()).
 *
 * $disponiveis array de stdClass (FornecNotifDesvioModel::getDisponiveisParaNotificacao())
 * $fabricante  fabricante usado para a pré-seleção inicial
 */
?>
<?php if (empty($disponiveis)): ?>
    <div class="col-12">
        <div class="alert alert-warning">Não há produtos disponíveis para notificação no momento.</div>
    </div>
<?php else: ?>
    <div class="col-12">
        <table class="table table-bordered table-sm align-middle" id="tbl_selecao_produtos_notif">
            <thead class="table-light">
                <tr>
                    <th>N°</th>
                    <th>Data</th>
                    <th>Produto</th>
                    <th>Fabricante</th>
                    <th>Lote</th>
                    <th>Usuário</th>
                    <th class="text-center">Selecione</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disponiveis as $d): ?>
                    <?php $marcado = ($d->fab_apeFab === $fabricante) ? 'checked' : ''; ?>
                    <tr>
                        <td><?= str_pad($d->ndv_id, 6, '0', STR_PAD_LEFT) ?></td>
                        <td><?= data_br($d->oco_data) ?></td>
                        <td><?= esc($d->pro_despro) ?></td>
                        <td><?= esc($d->fab_apeFab) ?></td>
                        <td><?= esc($d->lot_lote) ?></td>
                        <td><?= esc($d->usu_nome ?? '') ?></td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" name="ndv_id[]" value="<?= $d->ndv_id ?>" <?= $marcado ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
