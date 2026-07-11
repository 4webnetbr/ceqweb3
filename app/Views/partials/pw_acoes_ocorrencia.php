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
                    <?php if (!empty($permiteAcaoExtra)): ?>
                        <!-- RN03.15 — ações extras adicionadas manualmente pelo usuário -->
                        <tbody id="rep_acoesextra" data-index="<?= count($acoes) ?>">
                        </tbody>
                    <?php endif; ?>
                </table>

                <?php if (!empty($permiteAcaoExtra)): ?>
                    <div class="row px-2 pb-2">
                        <div class="col-12 text-end">
                            <button type="button"
                                class="btn btn-outline-success btn-sm bt-repete"
                                data-index="<?= count($acoes) ?>"
                                title="Adicionar Ação"
                                onclick="adicionaAcaoExtra('<?= base_url('OcoTrataOcorrencia/addCampoAcaoExtra/' . $oco_id) ?>', this)">
                                <i class="fas fa-plus"></i> Adicionar Ação
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
