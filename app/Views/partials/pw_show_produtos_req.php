<?
if (!isset($show)) {
    $show = false;
}
?>
<div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headprod<?= $produtos[0] ?>">
            <button class="accordion-button" type="button" 
                data-bs-target="#collapseprod<?= $produtos[0] ?>" aria-expanded="true"
                aria-controls="collapseprod<?= $produtos[0] ?>" data-proid="<?= $produtos[0] ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapseprod<?= $produtos[0] ?>" class="accordion-collapse collapse show border border-2" aria-labelledby="headprod<?= $produtos[0] ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-0" style="max-height:49vh; height:auto; overflow-y: auto">
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
                        <?
                        if (count($produtos) > 1) {
                            for ($p = 1; $p < count($produtos); $p++) {
                                $prodt = $produtos[$p];
                        ?>
                                <tr id='<?= $prodt[0] ?>'>
                                    <?
                                    for ($pp = 1; $pp <= count($colunas); $pp++) { ?>
                                        <td class='px-2 text-<?= $alinha[$pp - 1]; ?>'><?= $prodt[$pp]; ?></td>
                                    <?
                                    } ?>
                                </tr>
                            <?
                            };
                        } else { ?>
                            <tr>
                                <?
                                for ($c = 0; $c < count($colunas); $c++) { ?>
                                    <td></td>
                                <?
                                } ?>
                            </tr>
                        <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>