<style>
    .tabela-pequena {
        font-size: 12px;
    }
</style>

<div class="accordion" id="accProdutos">
    <div class="accordion-item">

        <h2 class="accordion-header border border-bottom-1" id="headacao<?= $oco_id ?>">
            <?php if (!empty($permiteAcaoExtra)): ?>
                <button type="button"
                    class="btn btn-outline-success btn-sm bt-repete float-start m-1"
                    data-index="<?= count($acoes) ?>"
                    title="Adicionar Ação"
                    onclick="adicionaAcaoExtra('<?= base_url('OcoTrataOcorrencia/addCampoAcao/' . $oco_id) ?>', this)">
                    <i class="fas fa-plus"></i> Adicionar Ação
                </button>
            <?php endif; ?>
        </h2>

        <div id="collapseacao<?= $oco_id ?>"
            class="accordion-collapse collapse show"
            aria-labelledby="headacao<?= $oco_id ?>"
            data-bs-parent="#accAcoes">

            <div class="accordion-body p-0">
                <table id='tbacoes' class="table table-bordered table-sm align-middle">
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