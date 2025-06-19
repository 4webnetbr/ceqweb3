<!-- Section Menu -->
<?= $this->section('footer'); ?>
<div id='rodape' class='footer bg-light col-12 text-center position-fixed bottom-0 border-top' style="height:1.5rem">
  <div id='msgrodape' class="col-8 text-center d-inline "></div>
  <?
  if (isset($log['operacao']) && $log['operacao'] != '') {
  ?>
    <div>
      <?= $log['operacao']; ?>: <?= $log['data_alterou']; ?> por: <?= $log['usua_alterou']; ?>
      <a href='<?= base_url('Logger/show/' . $log['tabela'] . '/' . $log['registro']); ?>'>Ver Log</a>
      <div class="text-end d-inline position-absolute pe-5" style="right:1rem"><?= $identificador; ?></div>
    </div>
  <?
  } else { ?>
    <div class="text-end d-inline position-absolute pe-5" style="right:1rem"><?= $identificador; ?></div>
  <?
  } ?>
  <div class="text-end d-inline position-absolute pe-5" style="right:0rem">
    <div id="stat_server" class="spinner-grow spinner-grow-sm " role="status" title="Servidor Conectado" onclick="executa_php()"></div>
  </div>
</div>
<script>
  function executa_php() {
    redirec_blank('/Utils/executa_php');
    setInterval(function() {
      conectaWs();
    }, 2000);
  }
</script>
<?= $this->endSection(); ?>