<?
if (!isset($show)) {
    $show = false;
}
if (!isset($maxHeig)) {
    $maxHeig = '49vh';
}
?>
<div class="accordion mb-0" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headprod<?php echo $produtos[0]['req_id'] ?? '' ?>">
            <button class="accordion-button" type="button"
                data-bs-target="#collapseprod<?php echo $produtos[0]['req_id'] ?? '' ?>" aria-expanded="true"
                aria-controls="collapseprod<?php echo $produtos[0]['req_id'] ?? '' ?>" data-proid="<?php echo $produtos[0]['req_id'] ?? '' ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapseprod<?php echo $produtos[0]['req_id'] ?? '' ?>" class="accordion-collapse collapse show" aria-labelledby="headprod<?php echo $produtos[0]['req_id'] ?? '' ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-0" style="max-height:<?php echo $maxHeig; ?>; height:auto; overflow-y: auto; border-top: 1px solid white;">
                <table class="display compact table table-sm table-info table-striped table-hover table-vertical-borders col-12 no-footer dataTable tabela-pequena" aria-describedby="table_info">
                    <thead class="table-default bg-table-blue-dark col-12 overflow-x-auto">
                        <tr class=' w-100' style='min-height:39px; height:39px;'>
                            <?
                            if (!$show) { ?>
                                <th class="sticky-top text-center align-middle text-wrap">Status</th>
                            <? } ?>
                            <th class="sticky-top text-center align-middle text-wrap">Cód<br>ERP</th>
                            <th class="sticky-top text-center align-middle text-wrap">Descrição</th>
                            <th class="sticky-top text-center align-middle text-wrap">Fabricante</th>
                            <th class="sticky-top text-center align-middle text-wrap">Lote<br>Validade</th>
                            <th class="sticky-top text-center align-middle text-wrap">LF</th>
                            <th class="sticky-top text-center align-middle text-wrap">LP</th>
                            <th class="sticky-top text-center align-middle text-wrap">Saldo<br>Origem</th>
                            <th class="sticky-top text-center align-middle text-wrap">Caixas</th>
                            <th class="sticky-top text-center align-middle text-wrap">Qtde<br>Requ.</th>
                            <th class="sticky-top text-center align-middle text-wrap">Qtde<br>Cancelada</th>
                            <th class="sticky-top text-center align-middle text-wrap">Qtde<br>Atendida</th>
                            <th class="sticky-top text-center align-middle text-wrap">Saldo</th>
                            <?
                            if (!$show) { ?>
                                <th class="sticky-top text-center align-middle text-wrap">OC.A</th>
                            <? } ?>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?php $zeb = 0;
                        foreach ($produtos as $produto):
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
                            <tr class='linha_produto <?php echo ($zeb % 2 == 0) ? 'even' : 'odd'; ?>' id='<?php echo $produto["rep_id"] ?>'>
                                <?
                                if (!$show) { ?>
                                    <td>
                                        <div id='stt_<?php echo $produto["rep_id"] ?>' class='rounded-circle border border-2 <?php echo $corlegenda; ?>' style='width: 25px; height: 25px'></div>
                                    </td>
                                <? } ?>
                                <td>
                                    <input type='hidden' id='repid_<?php echo $produto["rep_id"] ?>' name='repid_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["rep_id"] ?>'></input>
                                    <input type='hidden' id='cbfab_<?php echo $produto["rep_id"] ?>' name='cbfab_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["pre_cbfabricante"] ?>'></input>
                                    <input type='hidden' id='undfab_<?php echo $produto["rep_id"] ?>' name='undfab_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["pre_undfabricante"] ?>'></input>
                                    <input type='hidden' id='cblot_<?php echo $produto["rep_id"] ?>' name='cblot_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["pre_cblote"] ?>'></input>
                                    <input type='hidden' id='undlot_<?php echo $produto["rep_id"] ?>' name='undlot_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["pre_undlote"] ?>'></input>
                                    <input type='hidden' id='cfreq_<?php echo $produto["rep_id"] ?>' name='cfreq_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto["prc_conf_req"] ?>'></input>
                                    <input type='hidden' id='ctalt_<?php echo $produto["rep_id"] ?>' name='ctalt_<?php echo $produto["rep_id"] ?>' value='<?php echo intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                                    <input type='hidden' id='ctafb_<?php echo $produto["rep_id"] ?>' name='ctafb_<?php echo $produto["rep_id"] ?>' value='<?php echo intval($produto["saldo"]) === 0 ? esc($produto["rpa_atendida_val"]) : 0 ?>'></input>
                                    <input type='hidden' id='proid_<?php echo $produto["rep_id"] ?>' name='proid_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['pro_id'] ?>'></input>
                                    <input type='hidden' id='lotid_<?php echo $produto["rep_id"] ?>' name='lotid_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['lot_id'] ?>'></input>
                                    <input type='hidden' id='lotlote_<?php echo $produto["rep_id"] ?>' name='lotlote_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['lot_lote'] ?>'></input>
                                    <input type='hidden' id='repqtia_<?php echo $produto["rep_id"] ?>' name='repqtia_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['rep_quantia'] ?>'></input>
                                    <input type='hidden' id='qtdemb_<?php echo $produto["rep_id"] ?>' name='qtdemb_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['pro_qtdemb'] ?>'></input>
                                    <input type='hidden' id='estorig_<?php echo $produto["rep_id"] ?>' name='estorig_<?php echo $produto["rep_id"] ?>' value='<?php echo $produto['estoque_origem'] ?>'></input>
                                    <?php echo $produto['pro_codpro'] ?>
                                </td>
                                <td class='text-start'><?php echo $produto['pro_despro'] ?></td>
                                <td id='<?php echo $produto['pro_codbar_fabricante'] ?>' data-codbar='<?php echo $produto['pro_codbar_fabricante'] ?>'
                                    data-id='cbFab' class='text-start'><?php echo $produto['fab_apeFab'] ?></td>
                                <td id='<?php echo $produto['lot_codbar'] ?>' data-codbar='<?php echo $produto['lot_codbar'] ?>'
                                    data-id='cbLot' class='text-start'><?php echo $produto['lot_lote'] . '<br>' . data_br($produto['lot_validade']) ?></td>

                                <? if (!$show) { ?>
                                    <td>
                                        <div id='fab_<?php echo $produto["rep_id"] ?>' class='rounded-circle border border-2  <?php echo $corleglote; ?> p-1' style='width: 25px; height: 25px'><?php echo $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                                    </td>
                                <? } else { ?>
                                    <td>
                                        <div id='fab_<?php echo $produto["rep_id"] ?>' class='p-1'><?php echo $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?></div>
                                    </td>
                                <? } ?>
                                <? if (!$show) { ?>
                                    <td>
                                        <div id='lot_<?php echo $produto["rep_id"] ?>' class='rounded-circle  border border-2 <?php echo $corleglote; ?> p-1' style='width: 25px; height: 25px'><?php echo $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                                    </td>
                                <? } else { ?>
                                    <td>
                                        <div id='lot_<?php echo $produto["rep_id"] ?>' class='p-1'><?php echo $produto['pre_cblote'] . $produto['pre_undlote'] ?></div>
                                    </td>
                                <? } ?>
                                <td id='so_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['estoque_origem'] ?></td>
                                <td id='cx_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['qtd_caixa'] ?></td>
                                <td id='qt_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['rep_quantia'] ?></td>
                                <td id='ca_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['rpa_cancelada'] ?></td>
                                <td id='at_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['rpa_atendida'] ?></td>
                                <!-- <td id='at_<?php echo $produto["rep_id"] ?>'><?php echo $produto['rpa_conferida'] ?></td> -->
                                <td id='sl_<?php echo $produto["rep_id"] ?>' class='text-end'><?php echo $produto['saldo'] ?></td>
                                <? if (!$show) { ?>
                                    <td><?php echo $produto['bt_ocorre']; ?></td>
                                <? } ?>
                            </tr>
                        <?php $zeb++;
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>