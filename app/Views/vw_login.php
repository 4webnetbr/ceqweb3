<?php echo $this->extend('templates/login_template') ?>
<?php echo $this->section('content'); ?>
<style>
  /* Container principal */
  #divgeral {
    height: 100vh;
  }

  /* Logo */
  .logo-image {
    max-width: 60%;
    height: auto;
    object-fit: contain;
  }

  /* Div IDENT (lado esquerdo / topo no mobile) */
  #divident {
    background-color: #fff;
    position: relative;
    z-index: 2;
    height: 100vh !important;
    overflow: hidden;

    box-shadow: -10px 0px 20px 5px rgba(0, 0, 0, 0.25);
  }

  #divident::before {
    content: "";
    position: absolute;
    inset: 0;
    /* background-color: aliceblue; */
    background-image: url("../assets/images/icone.png");
    background-repeat: no-repeat;
    background-position: 5%;
    background-size: 150%;
    opacity: 0.055;
    z-index: 0;
  }

  #divident>* {
    position: relative;
    z-index: 1;
  }

  /* Div LOGO */
  #divlogo {
    height: 100vh !important;
    z-index: 1;
  }


  /* ===== RESPONSIVO ===== */
  @media (max-width: 991.98px) {
    #divgeral {
      flex-direction: column;
    }

    #divident {
      height: 40vh !important;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
      /* sombra inferior no mobile */
    }

    #divlogo {
      height: 60vh !important;
    }
  }
</style>
<section class="container-fluid p-0 h-100">
  <div id="divgeral" class="d-flex flex-column flex-lg-row h-100">
    <!-- divident -->
    <div id="divident" class="divident col-12 col-lg-3 d-flex justify-content-center align-items-center order-1 order-lg-2 ps-3">
      <form method="post" type="normal" action="<?php echo site_url($destino) ?>" class="">
        <div class="card-body">
          <!-- <img src="https://ceqweb3.ceqnep.com.br/assets/images/logo_header.jpg" class="logo-image" style='width:70%' /> -->
          <h3 class="card-title mt-2 mb-4 ps-3 ">Identificação</h3>
          <?php
          for ($c = 0; $c < sizeof($campos); $c++) {
            echo $campos[$c];
          }
          ?>
          <br>
          <span style='color:red;font-size:15px;'><?php echo session('msg'); ?></span>
        </div>
      </form>
    </div>

    <!-- divlogo -->
    <div id="divlogo"
      class="col-12 col-lg-9 d-flex justify-content-center align-items-center order-2 order-lg-1">
      <img src="https://ceqweb3.ceqnep.com.br/assets/images/logo_header.jpg" class="logo-image" />
    </div>

  </div>
</section>
<?php echo $this->endSection(); ?>