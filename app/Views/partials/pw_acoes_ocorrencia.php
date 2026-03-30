<style>
    .tabela-pequena {
        font-size: 12px;
    }
</style>

<div class="accordion" id="accProdutos">
    <div class="accordion-item">

        <h2 class="accordion-header border border-bottom-1" id="headacao<?= $oco_id ?>">
            <button class="accordion-button text-center"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseacao<?= $oco_id ?>"
                aria-expanded="true"
                aria-controls="collapseacao<?= $oco_id ?>">
                <div class='col-12 text-center'>Ações</div>
            </button>
        </h2>

        <div id="collapseacao<?= $oco_id ?>" 
             class="accordion-collapse collapse show"
             aria-labelledby="headacao<?= $oco_id ?>"
             data-bs-parent="#accAcoes">

            <div class="accordion-body p-0">

                <table class="table table-bordered table-sm align-middle">
                    <!-- <thead class="table-info">
                        <tr>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
 -->
                    <tbody>

                        <?php foreach ($acoes as $acao): ?>
                            <tr>
                                <td>
                                    <div class="row">
                                        <?php foreach ($acao as $campo): ?>
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
