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
// debug($it_menu);
  $cont_accord = 1;
  $cont_sort = 0;
  $accordraiz = false;
  // echo $image_avatar; 
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
<form id="form1" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12" enctype="multipart/form-data">
<div class="row justify-content-md-center">
<div class="col-lg-6 col-8 position-absolute bg-white shadow" id="menu">
  <div class="pt-2 pb-3 ps-1 pe-1">
  <ul class='grupo' id="gru_<?=$cont_accord;?>">
    <?
    $cont_accord++;
    for($m=0;$m<count($itens);$m++){
      $opcao = $itens[$m];
      ?>
        <li id='<?=$opcao['eta_id'];?>' class="p-1 btn btn-outline-light list-group-item ui-state-defaul text-start">
          <input type='hidden' name='men_<?=$opcao['eta_id'];?>' value='<?=$opcao['eta_id'];?>'/>
          <h5 class='my-1'>
            <i class='<?=$opcao['eta_icone'];?>'></i>
            <?=$opcao['eta_nome'];?>
          </h5>
        </li>
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
