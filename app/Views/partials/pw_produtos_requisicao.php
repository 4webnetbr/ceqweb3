<style>
  .tabela-pequena {
    font-size: 10px;
  }
</style>
<div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header border border-bottom-1" id="heading<?= $produtos[0]['req_id'] ?>">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapse<?= $produtos[0]['req_id'] ?>" aria-expanded="false"
              aria-controls="collapse<?= $produtos[0]['req_id'] ?>" data-proid="<?= $produtos[0]['req_id'] ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapse<?= $produtos[0]['req_id'] ?>" class="accordion-collapse collapse show" aria-labelledby="heading<?= $produtos[0]['req_id'] ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-1" style="max-height:49vh; height:49vh; overflow-y: auto">
                <table class="table table-bordered table-sm text-center align-middle tabela-pequena">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
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
                            <th>OCA</th>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td>
                                <div id='stt_<?= $produto["rep_id"]?>' class='btn border border-1 bg-white' style='width: 10px; min-height: 10px'></div>
                            </td>
                            <!-- <td id='<?= $produto['lot_codbar'] ?>'><?= $produto['lot_codbar'] ?></td> -->
                            <td>
                                <input type='hidden' id='cbfab_<?= $produto["rep_id"]?>' value='<?= $produto["pre_cbfabricante"]?>'></input>
                                <input type='hidden' id='undfab_<?= $produto["rep_id"]?>' value='<?= $produto["pre_undfabricante"]?>'></input>
                                <input type='hidden' id='cblot_<?= $produto["rep_id"]?>' value='<?= $produto["pre_cblote"]?>'></input>
                                <input type='hidden' id='undlot_<?= $produto["rep_id"]?>' value='<?= $produto["pre_undlote"]?>'></input>
                                <input type='hidden' id='cfreq_<?= $produto["rep_id"]?>' value='<?= $produto["prc_conf_req"]?>'></input>
                                <?= $produto['pro_codpro'] ?></td>
                            <td class='text-start'><?= $produto['pro_despro'] ?></td>
                            <td  id='<?= extrairCodBarFab($produto['pro_codbar_fabricante']) ?>' class='text-start'><?= $produto['fab_apeFab'] ?></td>
                            <td><div id='fab_<?= $produto["rep_id"]?>' class='btn'><?= $produto['pre_cbfabricante'].$produto['pre_undfabricante'] ?></div></td>
                            <td class='text-end'><?= $produto['lot_lote'] ?></td>
                            <td><div id='lot_<?= $produto["rep_id"]?>' class='btn'><?= $produto['pre_cblote'].$produto['pre_undlote'] ?></div></td>
                            <td><?= data_br($produto['lot_validade']) ?></td>
                            <td><?= $produto['qtd_caixa'] ?></td>
                            <td id='qt_<?= $produto["rep_id"]?>'><?= $produto['rep_quantia'] ?></td>
                            <td id='ca_<?= $produto["rep_id"]?>'><?= $produto['rep_cancelada'] ?></td>
                            <td id='at_<?= $produto["rep_id"]?>'><?= $produto['rep_atendida'] ?></td>
                            <td id='sl_<?= $produto["rep_id"]?>'><?= $produto['rep_quantia'] ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>