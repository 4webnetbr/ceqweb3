<?
if (!isset($show)) {
    $show = false;
}
if (!isset($maxHeig)) {
    $maxHeig = '49vh';
}
$nomeacc = 'acc' . ($titulo ?? 'Produtos');
?>
<div class="accordion mb-5" id="<?php echo $nomeacc ?>">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headprod<?php echo $id ?? '' ?>">
            <button class="accordion-button" type="button"
                data-bs-target="#collapse<?php echo $id ?? '' ?>" aria-expanded="true"
                aria-controls="collapse<?php echo $id ?? '' ?>" data-proid="<?php echo $id ?? '' ?>">
                <div class='col-12 text-center'><?php echo $titulo ?? 'Produtos' ?></div>
            </button>
        </h2>
        <div id="collapse<?php echo $id ?? '' ?>" class="accordion-collapse collapse show " aria-labelledby="heading<?php echo $id ?? '' ?>" data-bs-parent="<?php echo $nomeacc ?>">
            <div class="accordion-body p-0" style="max-height:<?php echo $maxHeig; ?>; height:auto; overflow-y: auto;">
                <table class="display compact table table-sm table-striped table-hover table-vertical-borders col-12 no-footer tabela-pequena" aria-describedby="table_info">
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
                        <?php foreach ($produtos as $produto): ?>
                            <tr class='linha_produto' id='<?php echo $produto[0] ?>'>
                                <?
                                for ($c = 0; $c < count($produto); $c++) { ?>
                                    <td class="text-<?php echo $alinha[$c] ?? 'left'; ?> align-middle"><?php echo $produto[$c]; ?></td>
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