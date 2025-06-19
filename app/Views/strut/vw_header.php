<!-- Section Header -->
<?=$this->section('header');?>
  <div class="row cols-12 bg-gradient h-100">
    <div id='sistema' class='col-lg-2 col-4 m-auto p-1 text-center'>
      <a href="<?=site_url();?>" class='d-inline-block w-50'>
        <img src='<?=session()->logo.'?noc='.time();?>' class="img-thumbnail" />
      </a>
    </div>
    <div id='titulo' class='col-8 d-lg-block d-none fst-italic text-center m-auto'>
    </div>
    <div id='div_user' class='col-lg-2 col-6 d-block p-0 m-auto text-center'>
       <button name="bt_user" type="button" id="bt_user" class="bt_user btn btn-outline-info px-4" >
      <?php
          $avatar = session()->get('usu_avatar'); 
          $nomeus = session()->get('usu_nome'); 
          if($avatar != ''){
              $image_avatar = "<img src='$avatar' class='rounded-circle me-3' width='25px' height='25px'/>";
          } else {
              $image_avatar = "<i class='far fa-laugh-wink'></i> ";
          }
          echo $image_avatar; 
          echo $nomeus;
      ?>
      </button>
    </div>
  </div>
  <div id='show_user' class="card col-lg-2 col-6 border border-1 border-dark rounded-4 shadow p-3 me-1 float-end" >
    <div class="card-header">
    <?php
      if($avatar != ''){
          $image_user = "<img src='$avatar' class='rounded-circle me-3 card-img-top'/>";
      } else {
          $image_user = "<i class='far fa-laugh-wink'></i> ";
      }
      echo $image_user;
    ?>
    </div>
    <div class="card-body">
      <h5 class="card-title"><?=$nomeus;?></h5>
      <p class="card-text"><?=session()->get('usu_perfil');?></p>
      <?php 
        echo "<hr>";
        echo anchor('AdmUsuario/edit_senha', 'Alterar Senha');
        echo "<hr>";
        echo anchor('login', '<i class="fas fa-sign-out-alt"></i> - Sair');
      ?>
    </div>
  </div>  
<?=$this->endSection();?>
