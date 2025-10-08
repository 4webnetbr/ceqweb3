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
  <!-- <div class="text-end d-inline position-absolute bg-info" style="right:10rem"> -->
    <!-- <div id="stat_server" class="spinner-grow spinner-grow-sm " role="status" title="Servidor Conectado" onclick="executa_php()"></div> -->
    <div id="stat_server" class="float-end truck-icon" role="status" title="Servidor Conectado" onclick="executa_php()">
      <i class="fa-solid fa-truck-moving"></i>
    </div>
  <!-- </div> -->
</div>
<style>
  .truck-icon {
    position: absolute;
    display: inline-block;
    width: 60px; /* garante o espaço da animação */
    right: 5rem;
    font-size: 12px;
    /* overflow: hidden; */
    cursor: pointer;
  }

  .truck-icon i {
    color: green;
    font-size: 1em;
    position: relative;
    animation: moverCaminhao 3s infinite ease-in-out;
  }

  @keyframes moverCaminhao {
    0% {
      transform: translateX(0) scaleX(1);/* normal */
    }
    50% {
      transform: translateX(60px) scaleX(1); /* indo para direita */
    }
    51% {
      transform: translateX(60px) scaleX(-1); /* começa a voltar */
    }
    100% {
      transform: translateX(0) scaleX(-1); /* voltando para a esquerda */
    }
  }
  </style>
<script>
  function executa_php() {
    redirec_blank('/Utils/executa_php');
    setInterval(function() {
      conectaWs();
    }, 2000);
  }
</script>
<?= $this->endSection(); ?>