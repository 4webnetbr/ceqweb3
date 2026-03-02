<!-- Section Menu -->
<?= $this->section('footer'); ?>
<div id='rodape' class='rodape col-12 footer bg-light text-center position-fixed bottom-0 border-top' style="height:1.5rem">
  <!-- inicio da div de mensagem -->
  <div id='msgrodape' class="col-10 float-start text-center d-block px-0">
    <?
    if (isset($log['operacao']) && $log['operacao'] != '') { ?>
      <?= $log['operacao']; ?>: <?= $log['data_alterou']; ?> por: <?= $log['usua_alterou']; ?>
      <a href='<?= base_url('Logger/' . $log['tabela'] . '/' . $log['registro']); ?>'>Ver Log</a>
    <?
    }
    ?>
    &nbsp;
  </div>
  <!-- fim da div de mensagem -->
  <!-- div do status do servidor -->
<!-- websocket status -->
  <div id="stat_server" class="col-1 float-start d-block justify-content-center px-0"  role="button" title="Servidor Conectado" onclick="executa_php()">
    <i class="fa-solid fa-satellite-dish antenaLeft"></i>
    
    <div class="ws-link mx-1">
      <div class="packet packet-right"></div>
      <div class="packet packet-left"></div>
    </div>
    
    <i class="fa-solid fa-satellite-dish antenaRight"></i>
  </div>
  <!-- <div id="stat_server" class="col-1 float-start d-grid text-success cursor-pointer truck-icon pt-1" role="button" title="Servidor Conectado" onclick="executa_php()"> -->
    <!-- <i class="fa-solid fa-truck-moving"></i> -->
    <!-- <div class="ws-container">
      <i class="fa-solid fa-wifi"></i>
      <div class="packet"></div>
      <i class="fa-solid fa-wifi"></i>
    </div> -->
  <!-- </div> -->
  <!-- div do identificador da tela -->
  <div class="col-1 float-start d-block px-0"><?= $identificador; ?></div>
</div>


<style>
/* ocupa altura total do rodapé */


.antenaLeft{
  display: inline-block; /* necessário para transform funcionar corretamente */
  position: relative;
  float: left;
  left: 0px;
  top: 2px;
    color: #0dcaf0;
}
.antenaRight{
  display: block;
    transform: scaleX(-1);
    position: relative;
    float: right;
    right: 0px;
    top: -8px;
    color: #198754;
}

/* ===================== */
/* Linha conexão         */
/* ===================== */

.ws-link {
    height: 10px;
    background: rgba(0, 0, 0, .0);
    position: relative;
    overflow: hidden;
    left: -10px;
    /* width: 71%; */
    top: 0px;
}

.packet {
  position: absolute;
  top: 2px;
  width: 20px;
  height: 98%;
  border-radius: 2px;
}

.packet-right {
  position: absolute;
  top: 0;
  width: 18px;
  height: 100%;
  background: linear-gradient(90deg, transparent, #0dcaf0);
  animation: moveRight 1.2s linear infinite;

  clip-path: polygon(
    0% 0%,
    85% 0%,
    100% 50%,
    85% 100%,
    0% 100%
  );
}

.packet-left {
  position: absolute;
  top: 0;
  width: 18px;
  height: 100%;
  background: linear-gradient(270deg, transparent, #198754);
  animation: moveLeft 1.5s linear infinite;

  clip-path: polygon(
    15% 0%,
    100% 0%,
    100% 100%,
    15% 100%,
    0% 50%
  );
}
#rodape.parado .fa-satellite-dish {
  color: var(--bs-danger) !important; /* vermelho bootstrap */
  animation: pulse;
}

#rodape.parado .packet-right {
  animation:
    moveRight 1.2s linear infinite paused,
    blinkRed 1s ease-in-out infinite;
}

#rodape.parado .packet-left {
  animation:
    moveLeft 1.5s linear infinite paused,
    blinkRed 1s ease-in-out infinite;
}

#rodape.parado .packet {
  background: var(--bs-danger);
}
/* piscar */
@keyframes blinkRed {
  0%,100% { opacity: .3; }
  50% { opacity: 1; }
}

@keyframes moveRight {
  0% { left: -20px; }
  100% { left: 100%; }
}

@keyframes moveLeft {
  0% { left: 100%; }
  100% { left: -20px; }
}

@keyframes pulse {
  0%,100% { opacity: .3; }
  50% { opacity: 1; }
}
/* .ws-container {
    display: flex;
    align-items: center;
    gap: 40px;
  }

  .fa-wifi {
    font-size: 32px;
    color: #444;
  }

  .packet {
    width: 10px;
    height: 10px;
    background: #007bff;
    border-radius: 50%;
    animation: moveData 2s linear infinite;
  }

  @keyframes moveData {
    0% {
      transform: translateX(0);
    }

    50% {
      transform: translateX(60px);
    }

    100% {
      transform: translateX(0);
    }
  } */

  /* .truck-icon i {
    font-size: 1em;
    position: relative;
    animation: moverCaminhao 8s infinite ease-in-out;
  } */

  @keyframes moverCaminhao {
    0% {
      left: 5px;
      transform: scaleX(1);
      /* normal */
    }

    50% {
      left: calc(100% - 20px);
      transform: scaleX(1);
      /* indo para direita */
    }

    51% {
      left: calc(100% - 20px);
      transform: scaleX(-1);
      /* começa a voltar */
    }

    100% {
      left: 5px;
      transform: scaleX(-1);
      /* voltando para a esquerda */
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