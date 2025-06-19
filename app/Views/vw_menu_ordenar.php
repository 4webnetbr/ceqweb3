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
<form id="form1" data-alter="true" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12" enctype="multipart/form-data">
<div class="row justify-content-md-center">
<div class="col-lg-6 col-8 position-absolute bg-white shadow" id="menu">
  <div class="pt-2 pb-3 ps-1 pe-1">
  <ul class='grupo' id="gru_<?=$cont_accord;?>">
    <?
    $cont_accord++;
    for($m=0;$m<count($it_menu);$m++){
      $opcao = $it_menu[$m];
      if($opcao['men_metodo'] != 'index' && ($opcao['men_hierarquia'] == '1' || $opcao['men_hierarquia'] == '4')){
        $opcao['clas_controler'] = $opcao['clas_controler'].'/'.$opcao['men_metodo'];
      }

      if($opcao['men_hierarquia'] == '1'){ // é Opção do Menu
      ?>
        <li id='<?=$opcao['men_id'];?>' class="p-1 btn btn-outline-light list-group-item ui-state-defaul text-start">
          <input type='hidden' name='men_<?=$opcao['men_id'];?>' value='<?=$opcao['men_id'];?>'/>
          <h5 class='my-1'>
            <i class='<?=$opcao['men_icone'];?>'></i>
            <?=$opcao['men_etiqueta'];?>
          </h5>
        </li>
      <?
      } else if($opcao['men_hierarquia'] == '2'){ 
        if(!$accordraiz){
          $accordraiz = true;
        }
          $cont_accord++;
        ?>
        <li id='<?=$opcao['men_id'];?>' class="p-1 btn list-group-item ui-state-default text-start">
          <input type='hidden' name='men_<?=$opcao['men_id'];?>' value='<?=$opcao['men_id'];?>'/>
          <div class="btgrupo btn btn-outline-primary px-1 pt-0 pb-0 text-body-emphasis w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ord<?=$opcao['men_id'];?>" aria-expanded="false" aria-controls="collapse-ord<?=$opcao['men_id'];?>">
            <h3 class='my-1'>
              <i class='<?=$opcao['men_icone'];?>'></i>
              <?=$opcao['men_etiqueta'];?>
            </h3>
          </div>
          <div id="collapse-ord<?=$opcao['men_id'];?>" class="collapse order" aria-labelledby="head<?=$opcao['men_id'];?>">
            <ul class='grupo' id="gru_<?=$cont_accord;?>">
            <?
              $cont_accord++;
              $subopc = $it_menu[$m];
              if(isset($it_menu[$m]['niv1'])){
              for($sm=0;$sm<count($it_menu[$m]['niv1']);$sm++){
                $subopc = $it_menu[$m]['niv1'][$sm];
                if($subopc['men_hierarquia'] == '4'){ // é Opção do Menu
                ?>
                  <li id='<?=$subopc['men_id'];?>' class="p-1 btn list-group-item ui-state-default text-start">
                    <input type='hidden' name='men_<?=$subopc['men_id'];?>' value='<?=$subopc['men_id'];?>'/>
                    <h5 class='my-1'>
                      <i class='<?=$subopc['men_icone'];?>'></i>
                      <?=$subopc['men_etiqueta'];?>
                    </h5>
                  </li>
                <?
                } else if($subopc['men_hierarquia'] == '3'){
                  $cont_accord++;
                ?> 
                  <li id='<?=$subopc['men_id'];?>' class="p-1 btn list-group-item ui-state-default text-start">
                    <input type='hidden' name='men_<?=$subopc['men_id'];?>' value='<?=$subopc['men_id'];?>'/>
                    <div class="btn btn-outline-info px-1 pt-0 pb-0 text-body-emphasis w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ord<?=$subopc['men_id'];?>" aria-expanded="false" aria-controls="collapse-ord<?=$subopc['men_id'];?>">
                      <h4 class='my-1'>
                        <i class='<?=$subopc['men_icone'];?>'></i>
                        <?=$subopc['men_etiqueta'];?>
                      </h4>
                    </div>
                    <div id="collapse-ord<?=$subopc['men_id'];?>" class="collapse" aria-labelledby="head<?=$subopc['men_id'];?>" data-bs-parent="#accordion-ord<?=$cont_accord;?>">
                      <ul class='grupo' id="gru_<?=$cont_accord;?>">
                      <?
                        $cont_accord++;
                        if(isset($it_menu[$m]['niv1'][$sm]['niv2'])){
                        for($ss=0;$ss<count($it_menu[$m]['niv1'][$sm]['niv2']);$ss++){
                          $subsub = $it_menu[$m]['niv1'][$sm]['niv2'][$ss];
                        ?>
                          <li id='<?=$subsub['men_id'];?>' class="p-1 btn list-group-item ui-state-default text-start">
                            <input type='hidden' name='men_<?=$subsub['men_id'];?>' value='<?=$subsub['men_id'];?>'/>
                            <h6 class='my-1'>
                              <i class='<?=$subsub['men_icone'];?>'></i>
                              <?=$subsub['men_etiqueta'];?>
                            </h6>
                          </li>
                      <?
                        }
                        }
                      ?>
                      </ul>
                    </div>
                  </li>
                <?
                }
              }
              }
              ?>
            </ul>
          </div>
        </li>
      <?
      }
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
