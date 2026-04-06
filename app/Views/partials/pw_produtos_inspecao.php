<?
if (!isset($show)) {
    $show = false;
}
// debug($produtos, true);
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
        <div id="collapseprod<?= $produtos[0]['req_id'] ?? '' ?>" class="accordion-collapse collapse show border border-2" aria-labelledby="headprod<?= $produtos[0]['req_id'] ?? '' ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-0" style="max-height:49vh; height:auto;">
                <table class="display table table-bordered table-sm table-striped table-hover text-center align-middle tabela-pequena">
                    <thead class="table-info">
                        <tr>
                            <th>Cód.ERP</th>
                            <th>Descrição</th>
                            <th>Fabricante</th>
                            <th>Lote</th>
                            <th>Validade</th>
                            <th>Qtde.<br>Requisitada</th>
                            <th>Qtde.<br>Atendida</th>
                            <th>Data<br>Atendimento</th>
                            <th>Qtde.<br>Conferida</th>
                            <th>Data<br>Conferência</th>
                            <th>Qtde<br>Aprovada</th>
                            <?
                            if (!$show) { ?>
                                <th>Conformidade</th>
                            <? } ?>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?php foreach ($produtos as $produto):
                            $corlegenda = 'bg-white';
                            $corleglote = 'border-secondary';
                        ?>
                            <tr class='linha_produto' id='<?= $produto["rep_id"] ?>'>
                                <td>
                                    <input type='hidden' id='repid_<?= $produto["rep_id"] ?>' name='repid_<?= $produto["rep_id"] ?>' value='<?= $produto["rep_id"] ?>'></input>
                                    <input type='hidden' id='rpaid_<?= $produto["rep_id"] ?>' name='rpaid_<?= $produto["rep_id"] ?>' value='<?= $produto["rpa_id"] ?>'></input>
                                    <input type='hidden' id='cbfab_<?= $produto["rep_id"] ?>' name='cbfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cbfabricante"] ?>'></input>
                                    <input type='hidden' id='undfab_<?= $produto["rep_id"] ?>' name='undfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undfabricante"] ?>'></input>
                                    <input type='hidden' id='cblot_<?= $produto["rep_id"] ?>' name='cblot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cblote"] ?>'></input>
                                    <input type='hidden' id='undlot_<?= $produto["rep_id"] ?>' name='undlot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undlote"] ?>'></input>
                                    <input type='hidden' id='cfreq_<?= $produto["rep_id"] ?>' name='cfreq_<?= $produto["rep_id"] ?>' value='<?= $produto["prc_conf_req"] ?>'></input>
                                    <input type='hidden' id='ctalt_<?= $produto["rep_id"] ?>' name='ctalt_<?= $produto["rep_id"] ?>' value='0'></input>
                                    <input type='hidden' id='ctafb_<?= $produto["rep_id"] ?>' name='ctafb_<?= $produto["rep_id"] ?>' value='0'></input>
                                    <input type='hidden' id='ctami_<?= $produto["rep_id"] ?>' name='ctami_<?= $produto["rep_id"] ?>' value='0'></input>
                                    <input type='hidden' id='lotlote_<?= $produto["rep_id"] ?>' name='lotlote_<?= $produto["rep_id"] ?>' value='<?= $produto['lot_lote'] ?>'></input>
                                    <input type='hidden' id='proid_<?= $produto["rep_id"] ?>' name='proid_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_id'] ?>'></input>
                                    <input type='hidden' id='repqtia_<?= $produto["rep_id"] ?>' name='repqtia_<?= $produto["rep_id"] ?>' value='<?= $produto['rep_quantia'] ?>'></input>
                                    <input type='hidden' id='qtdemb_<?= $produto["rep_id"] ?>' name='qtdemb_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_qtdemb'] ?>'></input>
                                    <?= $produto['pro_codpro'] ?>
                                </td>
                                <td class='text-start'>
                                    <?= $produto['pro_despro'] ?>
                                </td>
                                <td id='<?= $produto['pro_codbar_fabricante'] ?>' data-id='cbFab' class='text-start'>
                                    <?= $produto['fab_apeFab'] ?>
                                </td>
                                <td data-id='cbLot' class='text-start'><?= $produto['lot_lote'] ?></td>
                                <td data-id='cbMis'><?= data_br($produto['lot_validade']) ?></td>
                                <td id='req_<?= $produto["rep_id"] ?>'><?= $produto['rep_quantia'] ?></td>
                                <td id='ate_<?= $produto["rep_id"] ?>'><?= $produto['rpa_atendida'] ?></td>
                                <td id='dat_<?= $produto["rep_id"] ?>'><?= $produto['rpa_data'] ?></td>
                                <td id='cfe_<?= $produto["rep_id"] ?>'><?= $produto['rpa_conferida'] ?></td>
                                <td id='dcf_<?= $produto["rep_id"] ?>'><?= $produto['rpa_data_conferencia'] ?></td>
                                <td id='apr_<?= $produto["rep_id"] ?>'><?= $produto['rpa_aprovada'] ?></td>
                                <? if (!$show) { ?>
                                    <td>
                                        <?= $produto['bt_insvis']; ?>
                                        <?= $produto['bt_ok']; ?>
                                    </td>
                                <? } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>