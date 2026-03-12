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
                    if (!$show) { ?>
                        <th>Status</th>
                    <? } ?>
                    <th>Cód.ERP</th>
                    <th>Descrição</th>
                    <th>Fabricante</th>
                    <th>LF</th>
                    <th>Lote</th>
                    <th>LP</th>
                    <th>Validade</th>
                    <th>Caixas</th>
                    <th>Qtde. Requerida</th>
                    <th>Qtde. Cancelada</th>
                    <th>Qtde. Atendida</th>
                    <th>Saldo</th>
                    <?
                    if (!$show) { ?>
                        <th>OC.A</th>
                    <? } ?>
                </tr>
            </thead>
            <tbody style="max-height: 45vh; overflow-y: auto;">
                <?php foreach ($produtos as $produto):
                    $corlegenda = 'bg-white';
                    $corleglote = 'border-secondary';
                    if (intval($produto['saldo']) == 0) {
                        $corleglote = 'bg-success';
                        if (intval($produto['rpa_cancelada_val']) == 0) {
                            $corlegenda = 'bg-success';
                        } else {
                            $corlegenda = 'bg-danger';
                        }
                    }
                ?>
                    <tr id='<?= $produto["rep_id"] ?>'>
                        <?
                        if (!$show) { ?>
                            <td>
                                <div id='stt_<?= $produto["rep_id"] ?>' class='rounded-circle border border-2 <?= $corlegenda; ?>' style='width: 25px; height: 25px'></div>
                            </td>
                        <? } ?>
                        <td>
                            <input type='hidden' id='repid_<?= $produto["rep_id"] ?>' name='repid_<?= $produto["rep_id"] ?>' value='<?= $produto["rep_id"] ?>'></input>
                            <input type='hidden' id='cbfab_<?= $produto["rep_id"] ?>' name='cbfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cbfabricante"] ?>'></input>
                            <input type='hidden' id='undfab_<?= $produto["rep_id"] ?>' name='undfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undfabricante"] ?>'></input>
                            <input type='hidden' id='cblot_<?= $produto["rep_id"] ?>' name='cblot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cblote"] ?>'></input>
                            <input type='hidden' id='undlot_<?= $produto["rep_id"] ?>' name='undlot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undlote"] ?>'></input>
                            <input type='hidden' id='cfreq_<?= $produto["rep_id"] ?>' name='cfreq_<?= $produto["rep_id"] ?>' value='<?= $produto["prc_conf_req"] ?>'></input>
                            <input type='hidden' id='ctalt_<?= $produto["rep_id"] ?>' name='ctalt_<?= $produto["rep_id"] ?>' value='<?= intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                            <input type='hidden' id='ctafb_<?= $produto["rep_id"] ?>' name='ctafb_<?= $produto["rep_id"] ?>' value='<?= intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                            <input type='hidden' id='proid_<?= $produto["rep_id"] ?>' name='proid_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_id'] ?>'></input>
                            <input type='hidden' id='lotid_<?= $produto["rep_id"] ?>' name='lotid_<?= $produto["rep_id"] ?>' value='<?= $produto['lot_id'] ?>'></input>
                            <input type='hidden' id='lotlote_<?= $produto["rep_id"] ?>' name='lotlote_<?= $produto["rep_id"] ?>' value='<?= $produto['lot_lote'] ?>'></input>
                            <input type='hidden' id='repqtia_<?= $produto["rep_id"] ?>' name='repqtia_<?= $produto["rep_id"] ?>' value='<?= $produto['rep_quantia'] ?>'></input>
                            <input type='hidden' id='qtdemb_<?= $produto["rep_id"] ?>' name='qtdemb_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_qtdemb'] ?>'></input>
                            <input type='hidden' id='estorig_<?= $produto["rep_id"] ?>' name='estorig_<?= $produto["rep_id"] ?>' value='<?= $produto['estoque_origem'] ?>'></input>
                            <?= $produto['pro_codpro'] ?>
                        </td>
                        <td class='text-start'><?= $produto['pro_despro'] ?></td>
                        <td id='<?= $produto['pro_codbar_fabricante'] ?>' data-id='cbFab' class='text-start'><?= $produto['fab_apeFab'] ?></td>
                        <? if (!$show) { ?>
                            <td>
                                <div id='fab_<?= $produto["rep_id"] ?>' class='rounded-circle border border-2  <?= $corleglote; ?> p-1' style='width: 25px; height: 25px'><?= $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                            </td>
                        <? } else { ?>
                            <td>
                                <div id='fab_<?= $produto["rep_id"] ?>' class='p-1'><?= $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                            </td>
                        <? } ?>
                        <td id='<?= $produto['lot_codbar'] ?>' data-id='cbLot' class='text-start'><?= $produto['lot_lote'] ?></td>
                        <? if (!$show) { ?>
                            <td>
                                <div id='lot_<?= $produto["rep_id"] ?>' class='rounded-circle  border border-2 <?= $corleglote; ?> p-1' style='width: 25px; height: 25px'><?= $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                            </td>
                        <? } else { ?>
                            <td>
                                <div id='lot_<?= $produto["rep_id"] ?>' class='p-1'><?= $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                            </td>
                        <? } ?>
                        <td><?= data_br($produto['lot_validade']) ?></td>
                        <td id='cx_<?= $produto["rep_id"] ?>'><?= $produto['qtd_caixa'] ?></td>
                        <td id='qt_<?= $produto["rep_id"] ?>'><?= $produto['rep_quantia'] ?></td>
                        <td id='ca_<?= $produto["rep_id"] ?>'><?= $produto['rpa_cancelada'] ?></td>
                        <td id='at_<?= $produto["rep_id"] ?>'><?= $produto['rpa_atendida'] ?></td>
                        <!-- <td id='at_<?= $produto["rep_id"] ?>'><?= $produto['rpa_conferida'] ?></td> -->
                        <td id='sl_<?= $produto["rep_id"] ?>'><?= $produto['saldo'] ?></td>
                        <? if (!$show) { ?>
                            <td><?= $produto['bt_ocorre']; ?></td>
                        <? } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- <div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header border border-bottom-1 " id="headprod<?= $produtos[0]['req_id'] ?? '' ?>">
            <button class="accordion-button text-center"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseprod<?= $produtos[0]['req_id'] ?? '' ?>"
                aria-expanded="false"
                aria-controls="collapseprod<?= $produtos[0]['req_id'] ?? '' ?>"
                data-proid="<?= $produtos[0]['req_id'] ?? '' ?>">
                Produtos
            </button>
        </h2>
        <div id="collapseprod<?= $produtos[0]['req_id'] ?? '' ?>" class="accordion-collapse collapse show" aria-labelledby="headprod<?= $produtos[0]['req_id'] ?? '' ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-1" style="max-height:49vh; height:49vh; overflow-y: auto">
                <table class="table table-bordered table-sm text-center align-middle tabela-pequena">
                    <thead class="table-light">
                        <tr>
                            <?
                            if (!$show) { ?>
                                <th>Status</th>
                            <? } ?>
                            <th>Cód.ERP</th>
                            <th>Descrição</th>
                            <th>Fabricante</th>
                            <th>LF</th>
                            <th>Lote</th>
                            <th>LP</th>
                            <th>Validade</th>
                            <th>Caixas</th>
                            <th>Qtde. Requerida</th>
                            <th>Qtde. Cancelada</th>
                            <th>Qtde. Atendida</th>
                            <th>Saldo</th>
                            <?
                            if (!$show) { ?>
                                <th>OC.A</th>
                            <? } ?>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?php foreach ($produtos as $produto):
                            $corlegenda = 'bg-white';
                            $corleglote = 'border-secondary';
                            if (intval($produto['saldo']) == 0) {
                                $corleglote = 'bg-success';
                                if (intval($produto['rpa_cancelada_val']) == 0) {
                                    $corlegenda = 'bg-success';
                                } else {
                                    $corlegenda = 'bg-danger';
                                }
                            }
                        ?>
                            <tr id='<?= $produto["rep_id"] ?>'>
                                <?
                                if (!$show) { ?>
                                    <td>
                                        <div id='stt_<?= $produto["rep_id"] ?>' class='rounded-circle border border-2 <?= $corlegenda; ?>' style='width: 25px; height: 25px'></div>
                                    </td>
                                <? } ?>
                                <td>
                                    <input type='hidden' id='repid_<?= $produto["rep_id"] ?>' name='repid_<?= $produto["rep_id"] ?>' value='<?= $produto["rep_id"] ?>'></input>
                                    <input type='hidden' id='cbfab_<?= $produto["rep_id"] ?>' name='cbfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cbfabricante"] ?>'></input>
                                    <input type='hidden' id='undfab_<?= $produto["rep_id"] ?>' name='undfab_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undfabricante"] ?>'></input>
                                    <input type='hidden' id='cblot_<?= $produto["rep_id"] ?>' name='cblot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_cblote"] ?>'></input>
                                    <input type='hidden' id='undlot_<?= $produto["rep_id"] ?>' name='undlot_<?= $produto["rep_id"] ?>' value='<?= $produto["pre_undlote"] ?>'></input>
                                    <input type='hidden' id='cfreq_<?= $produto["rep_id"] ?>' name='cfreq_<?= $produto["rep_id"] ?>' value='<?= $produto["prc_conf_req"] ?>'></input>
                                    <input type='hidden' id='ctalt_<?= $produto["rep_id"] ?>' name='ctalt_<?= $produto["rep_id"] ?>' value='<?= intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                                    <input type='hidden' id='ctafb_<?= $produto["rep_id"] ?>' name='ctafb_<?= $produto["rep_id"] ?>' value='<?= intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                                    <input type='hidden' id='proid_<?= $produto["rep_id"] ?>' name='proid_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_id'] ?>'></input>
                                    <input type='hidden' id='lotid_<?= $produto["rep_id"] ?>' name='lotid_<?= $produto["rep_id"] ?>' value='<?= $produto['lot_id'] ?>'></input>
                                    <input type='hidden' id='lotlote_<?= $produto["rep_id"] ?>' name='lotlote_<?= $produto["rep_id"] ?>' value='<?= $produto['lot_lote'] ?>'></input>
                                    <input type='hidden' id='repqtia_<?= $produto["rep_id"] ?>' name='repqtia_<?= $produto["rep_id"] ?>' value='<?= $produto['rep_quantia'] ?>'></input>
                                    <input type='hidden' id='qtdemb_<?= $produto["rep_id"] ?>' name='qtdemb_<?= $produto["rep_id"] ?>' value='<?= $produto['pro_qtdemb'] ?>'></input>
                                    <input type='hidden' id='estorig_<?= $produto["rep_id"] ?>' name='estorig_<?= $produto["rep_id"] ?>' value='<?= $produto['estoque_origem'] ?>'></input>
                                    <?= $produto['pro_codpro'] ?>
                                </td>
                                <td class='text-start'><?= $produto['pro_despro'] ?></td>
                                <td id='<?= $produto['pro_codbar_fabricante'] ?>' data-id='cbFab' class='text-start'><?= $produto['fab_apeFab'] ?></td>
                                <? if (!$show) { ?>
                                    <td>
                                        <div id='fab_<?= $produto["rep_id"] ?>' class='rounded-circle border border-2  <?= $corleglote; ?> p-1' style='width: 25px; height: 25px'><?= $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                                    </td>
                                <? } else { ?>
                                    <td>
                                        <div id='fab_<?= $produto["rep_id"] ?>' class='p-1'><?= $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                                    </td>
                                <? } ?>
                                <td id='<?= $produto['lot_codbar'] ?>' data-id='cbLot' class='text-start'><?= $produto['lot_lote'] ?></td>
                                <? if (!$show) { ?>
                                    <td>
                                        <div id='lot_<?= $produto["rep_id"] ?>' class='rounded-circle  border border-2 <?= $corleglote; ?> p-1' style='width: 25px; height: 25px'><?= $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                                    </td>
                                <? } else { ?>
                                    <td>
                                        <div id='lot_<?= $produto["rep_id"] ?>' class='p-1'><?= $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                                    </td>
                                <? } ?>
                                <td><?= data_br($produto['lot_validade']) ?></td>
                                <td id='cx_<?= $produto["rep_id"] ?>'><?= $produto['qtd_caixa'] ?></td>
                                <td id='qt_<?= $produto["rep_id"] ?>'><?= $produto['rep_quantia'] ?></td>
                                <td id='ca_<?= $produto["rep_id"] ?>'><?= $produto['rpa_cancelada'] ?></td>
                                <td id='at_<?= $produto["rep_id"] ?>'><?= $produto['rpa_atendida'] ?></td>
                                <!-- <td id='at_<?= $produto["rep_id"] ?>'><?= $produto['rpa_conferida'] ?></td> -->
                                <td id='sl_<?= $produto["rep_id"] ?>'><?= $produto['saldo'] ?></td>
                                <? if (!$show) { ?>
                                    <td><?= $produto['bt_ocorre']; ?></td>
                                <? } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> -->