<?
if (!isset($show)) {
    $show = false;
}
?>
<div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headprod<?= $produtos[0]['req_id'] ?? '' ?>">
            <button class="accordion-button" type="button" 
                data-bs-target="#collapseprod<?= $produtos[0]['req_id'] ?? '' ?>" aria-expanded="true"
                aria-controls="collapseprod<?= $produtos[0]['req_id'] ?? '' ?>" data-proid="<?= $produtos[0]['req_id'] ?? '' ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapse<?= $produtos[0]['req_id'] ?>" class="accordion-collapse collapse show border border-2" aria-labelledby="heading<?= $produtos[0]['req_id'] ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-0" style="max-height:49vh; height:49vh; overflow-y: auto">
                <table class="display table table-bordered table-sm table-striped table-hover text-center align-middle tabela-pequena">
                    <thead class="table-info">
                        <tr>
                            <?
                            for ($c = 0; $c < count($colunas); $c++) { ?>
                                <th><?= $colunas[$c]; ?></th>
                            <?
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?php foreach ($produtos as $produto): ?>
                            <tr id='<?= $produto[0] ?>'>
                                <?
                                for ($c = 1; $c < count($produto); $c++) { ?>
                                    <th><?= $produto[$c]; ?></th>
                                <?
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>