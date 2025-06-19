<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('footer');?>
  <?=view('strut/vw_rodape');?>
<?=$this->endSection();?>


<?=$this->section('content');?>
<?php
  $cont_accord = 1;
  $cont_sort = 0;
?>
<style>
  .grupo{
    max-height: 70vh;
    overflow-y: auto;
  }
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
<form id="form1" data-alter="true" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12" enctype="multipart/form-data">
<div class="row justify-content-md-center">
<div class="col-lg-6 col-8 position-absolute bg-white shadow" id="menu">
  <div class="pt-2 pb-3 ps-1 pe-1">
        <div class="btgrupo btn btn-outline-primary px-1 pt-0 pb-0 text-body-emphasis w-100 text-start">
          <h3 class='my-1'>
            Classes de Produtos
          </h3>
        </div>
        <div id="collapse-ord" class="order">
          <ul class='grupo' id="gru_<?=$cont_accord;?>">
            <?
            for($st=0;$st<count($lst_classe);$st++){
              $opcao = $lst_classe[$st];
            ?>
              <div class="col-12 mb-2">
              <li id='<?=$opcao['cla_id'];?>' class="p-1 px-4 btn btn-outline-info ui-state-defaul text-start">
                <input type='hidden' name='cla_<?=$opcao['cla_id'];?>' value='<?=$opcao['cla_id'];?>'/>
                <h3 class='my-1'>
                    <?=$opcao['cla_nome'];?>
                </h3>
              </li>
              </div>
            <?
            }
            ?>
          </ul>
        </div>
  </div>
</div>
</div>
<script>
  var arrayObjetos = [];
  seletor = '';
  jQuery(Object).find('.grupo').each(function(){
    var id = jQuery(this).attr("id"); 
    seletor += '#'+id+',';
    arrayObjetos .push({id:id});
  });
  seletor = seletor.slice(0,-1);
  jQuery(".btgrupo").on( "click", function() {
    clicado = jQuery(this).attr('data-bs-target');
    jQuery('div.order.show').each(function(){
      if(this.id != clicado){
        jQuery(this).removeClass('show');
      }
    })
  })
  jQuery(seletor).sortable();
</script>
<?=$this->endSection();?>
