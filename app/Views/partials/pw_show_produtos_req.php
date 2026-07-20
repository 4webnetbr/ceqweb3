<?
if (!isset($show)) {
    $show = false;
}
if (!isset($maxHeig)) {
    $maxHeig = '49vh';
}
?>
<div class="mb-5" id="accProdutos">
    <div>
        <h2 class="accordion-header" id="headprod<?php echo $produtos[0] ?>">
            <div class="accordion-button" data-proid="<?php echo $produtos[0] ?>">
                <div class='col-12 text-center'>Produtos</div>
            </div>
        </h2>
        <div id="collapseprod<?php echo $produtos[0] ?>" aria-labelledby="headprod<?php echo $produtos[0] ?>">
            <div class="p-0" style="max-height:<?php echo $maxHeig; ?>; height:auto; overflow-y: auto;border-top: 1px solid white;">
                <table class="display compact table table-sm table-info table-striped table-hover table-vertical-borders col-12 no-footer dataTable tabela-pequena" aria-describedby="table_info">
                    <thead class="table-default bg-table-blue-dark col-12 overflow-x-auto">
                        <tr class=' w-100' style='min-height:39px; height:39px;'>
                            <?
                            for ($c = 0; $c < count($colunas); $c++) { ?>
                                <th class="sticky-top text-center align-middle text-wrap"><?php echo $colunas[$c]; ?></th>
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
                                <tr class='linha_produto <?php echo ($p % 2 == 0) ? 'even' : 'odd'; ?>' id='<?php echo $prodt[0] ?>'>
                                    <?
                                    for ($pp = 1; $pp <= count($colunas); $pp++) { ?>
                                        <td class='px-2 text-<?php echo $alinha[$pp - 1]; ?>'><?php echo $prodt[$pp]; ?></td>
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