<style>
  .tabela-pequena {
    font-size: 10px;
  }

  .circle-small {
    width: 25px;
    height: 25px;
    border-radius: 50%;
  }
</style>
<?php
if (!isset($show)) {
    $show = false;
}

$reqId = $produtos[0]['req_id'] ?? 'default';
?>
<div class="accordion" id="accProdutos">
  <div class="accordion-item">
    <h2 class="accordion-header border border-bottom-1" id="headprod<?= $reqId ?>">
      <button class="accordion-button" type="button" data-bs-toggle="collapse"
        data-bs-target="#collapseprod<?= $reqId ?>" aria-expanded="false"
        aria-controls="collapseprod<?= $reqId ?>" data-proid="<?= $reqId ?>">
        <div class='col-12 text-center'>Produtos</div>
      </button>
    </h2>
    <div id="collapseprod<?= $reqId ?>" class="accordion-collapse collapse show"
      aria-labelledby="headprod<?= $reqId ?>" data-bs-parent="#accProdutos">
      <div class="accordion-body p-1" style="max-height:49vh; height:49vh; overflow-y: auto">
        <div class="row">
          <?php foreach ($produtos as $produto): 
            $repId = $produto['rep_id'];
            $corLegenda = 'bg-white';
            $corLote = 'border-secondary';
          ?>
          <div id="<?= $repId ?>" class="col-3 mb-3">
            <?php if (!$show): ?>
              <div id="stt_<?= $repId ?>" class="circle-small border border-2 <?= $corLegenda ?>"></div>
            <?php endif; ?>

            <?php
            $hiddenFields = [
              'repid' => $repId,
              'cbfab' => $produto['pre_cbfabricante'],
              'undfab' => $produto['pre_undfabricante'],
              'cblot' => $produto['pre_cblote'],
              'undlot' => $produto['pre_undlote'],
              'cfreq' => $produto['prc_conf_req'],
              'ctalt' => '0',
              'ctafb' => '0',
              'proid' => $produto['pro_id'],
              'repqtia' => $produto['rep_quantia'],
            ];
            foreach ($hiddenFields as $id => $value): ?>
              <input type="hidden" id="<?= $id . '_' . $repId ?>" name="<?= $id . '_' . $repId ?>" value="<?= $value ?>">
            <?php endforeach; ?>

            <div class='col-12 text-start'><strong>Código ERP:</strong><br> <?= $produto['pro_codpro'] ?></div>
            <div class='col-12 text-start'><strong>Descrição:</strong><br> <?= $produto['pro_despro'] ?></div>
            <div class='col-12 text-start' id="<?= $produto['pro_codbar_fabricante'] ?>" data-id="cbFab">
              <strong>Fabricante:</strong><br><?= $produto['fab_apeFab'] ?>
            </div>

            <div id="fab_<?= $repId ?>" class="<?= !$show ? 'circle-small border border-2 ' . $corLote . ' p-1' : 'p-1' ?>">
              <?= $produto['pre_cbfabricante'] . $produto['pre_undfabricante'] ?>
            </div>

            <div id="<?= $produto['lot_codbar'] ?>" data-id="cbLot" class="text-start">
              <strong>Lote:</strong><br><?= $produto['lot_lote'] ?>
            </div>

            <div id="lot_<?= $repId ?>" class="<?= !$show ? 'circle-small border border-2 ' . $corLote . ' p-1' : 'p-1' ?>">
              <?= $produto['pre_cblote'] . $produto['pre_undlote'] ?>
            </div>

            <div><strong>Validade:</strong> <?= data_br($produto['lot_validade']) ?></div>
            <div id="cx_<?= $repId ?>"><strong>Caixas:</strong> <?= $produto['qtd_caixa'] ?></div>
            <div id="qt_<?= $repId ?>"><strong>Qtde. Requerida:</strong> <?= $produto['rep_quantia'] ?></div>
            <div id="at_<?= $repId ?>"><strong>Qtde. Atendida:</strong> <?= $produto['rpa_atendida_val'] . $produto['rpa_atendida'] ?></div>
            <div id="ca_<?= $repId ?>"><strong>Qtde. Cancelada:</strong> <?= $produto['rpa_cancelada'] ?></div>
            <div id="cf_<?= $repId ?>"><strong>Qtde. Conferida:</strong> <?= $produto['rpa_conferida'] ?></div>
            <div id="sl_<?= $repId ?>"><strong>Saldo:</strong> <?= $produto['saldo'] ?></div>

            <?php if (!$show): ?>
              <div><?= $produto['bt_ocorre'] ?></div>
              <div><?= $produto['bt_insvis'] ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
