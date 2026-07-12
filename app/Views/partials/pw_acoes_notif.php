<?php
/**
 * Aba "Ações" (RN03.21/RN03.22) — mirror de partials/pw_acoes_ocorrencia.php,
 * com bt-repete/addCampo() apontando para NotifEvento::addCampoAcao().
 * $linhas array de arrays de campos já renderizados (EntOcoNotifEventoAcao::campos)
 */
?>
<div class="col-12">
    <table class="table table-bordered table-sm align-middle">
        <tbody id="rep_acoes_notif" data-index="<?= count($linhas) ?>">
            <?php foreach ($linhas as $i => $linha): ?>
                <tr>
                    <td>
                        <div class="row tableDiv table2 table-acoes_notif" data-index="<?= $i ?>">
                            <?= $linha['tpa_id'] ?>
                            <div id="divmovi[<?= $i ?>]" class="d-none row col-6 float-start"><?= $linha['tmo_id'] ?></div>
                            <div id="divstat[<?= $i ?>]" class="d-none row col-6 float-start"><?= $linha['stt_id'] ?></div>
                            <?= $linha['nac_ordem'] ?>
                            <div class="col-2 text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm bt-exclui"
                                    data-index="<?= $i ?>" title="Excluir Ação"
                                    onclick="exclui_campo('acoes_notif', this)">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row px-2 pb-2">
        <div class="col-12 text-end">
            <button type="button"
                class="btn btn-outline-success btn-sm bt-repete"
                data-index="<?= count($linhas) ?>"
                title="Adicionar Ação"
                onclick="addCampo('<?= base_url('NotifEvento/addCampoAcao/') ?>','acoes_notif', this)">
                <i class="fas fa-plus"></i> Adicionar Ação
            </button>
        </div>
    </div>
</div>
