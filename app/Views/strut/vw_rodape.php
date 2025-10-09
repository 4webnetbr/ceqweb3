<!-- Section Menu -->
<?= $this->section('footer'); ?>
<div id='rodape' class='rodape col-12 footer bg-light text-center position-fixed bottom-0 border-top' style="height:1.5rem">
  <!-- inicio da div de mensagem -->
  <div id='msgrodape' class="col-10 float-start text-center d-grid ">
  <?
    if (isset($log['operacao']) && $log['operacao'] != '') {?>
      <?= $log['operacao']; ?>: <?= $log['data_alterou']; ?> por: <?= $log['usua_alterou']; ?>
      <a href='<?= base_url('Logger/show/' . $log['tabela'] . '/' . $log['registro']); ?>'>Ver Log</a>
    <?
    }
  ?>
  </div>
  <!-- fim da div de mensagem -->
  <!-- div do identificador da tela -->
  <!-- div do status do servidor -->
  <div id="stat_server" class="col-1 float-start d-grid text-success cursor-pointer truck-icon pt-1" role="button" title="Servidor Conectado" onclick="executa_php()">
    <i class="fa-solid fa-truck-moving"></i>
  </div>
  <div class="col-1 float-start d-grid"><?= $identificador; ?></div>
</div>
<style>
  .truck-icon i {
    font-size: 1em;
    position: relative;
    animation: moverCaminhao 2s infinite ease-in-out;
  }

  @keyframes moverCaminhao {
    0% {
      left: 5px;
      transform: scaleX(1);/* normal */
    }
    50% {
      left: calc(100% - 20px);
      transform: scaleX(1); /* indo para direita */
    }
    51% {
      left: calc(100% - 20px);
      transform: scaleX(-1); /* começa a voltar */
    }
    100% {
      left: 5px;
      transform: scaleX(-1); /* voltando para a esquerda */
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