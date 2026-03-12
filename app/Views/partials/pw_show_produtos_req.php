<style>
    .tabela-pequena {
        font-size: 10px;
    }
</style>
<?
if (!isset($show)) {
    $show = false;
}
?>
<div class='row col-12 float-start align-items-center d-inline-flex mb-3'>
    <div class='row col-12 bg-blue-dark text-center text-white p-1'><h3 class='p-0 m-0'>Produtos</h3></div>
        <table class="display compact table table-sm table-info table-striped table-hover table-bordered col-12 dataTable no-footer tabela-pequena">
            <thead class="table-default col-12 overflow-x-auto">
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
<!-- <div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header border border-bottom-1" id="headprod<?= $produtos[0] ?>">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseprod<?= $produtos[0] ?>" aria-expanded="false"
                aria-controls="collapseprod<?= $produtos[0] ?>" data-proid="<?= $produtos[0] ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapseprod<?= $produtos[0] ?>" class="accordion-collapse collapse show" aria-labelledby="headprod<?= $produtos[0] ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-1" style="max-height:49vh; height:49vh; overflow-y: auto">
                <table class="table table-bordered table-sm text-center align-middle tabela-pequena">
                    <thead class="table-light">
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