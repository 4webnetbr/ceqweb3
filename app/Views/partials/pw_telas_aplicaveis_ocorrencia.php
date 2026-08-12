<div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header border border-bottom-1" id="headtela<?= $oco_id ?>">
            <!-- <button class="accordion-button text-center collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapsetela<?= $oco_id ?>"
                aria-expanded="false"
                aria-controls="collapsetela<?= $oco_id ?>">
                <div class='col-12 text-center'>Telas Aplicáveis</div>
            </button> -->
        </h2>

        <div id="collapsetela<?= $oco_id ?>"
            class="accordion-collapse collapse show"
            aria-labelledby="headtela<?= $oco_id ?>"
            data-bs-parent="#accTelas">

            <div class="accordion-body p-0">
                <table class="table table-bordered table-sm align-middle">
                    <tbody>
                        <?php foreach ($telas as $tela): ?>
                            <tr>
                                <td>
                                    <div class="row">
                                        <?php foreach ($tela as $chave => $campo): ?>
                                            <?php if (in_array($chave, ['bt_addta', 'bt_delta'])) continue; ?>
                                            <?= $campo ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>