<!-- Section Menu -->
<?=$this->section('menu');?>
<?php
// debug($it_menu);
  $url_sair = base_url('/login');
  $avatar = session()->get('usu_avatar'); 
  $nomeus = session()->get('usu_nome'); 
  $cont_accord = 1;
  $accordraiz = false;
  if($avatar != ''){
    $image_avatar = "<img src='$avatar' class='img-user rounded-circle me-3 float-start' />";
  } else {
    $image_avatar = "<i class='far fa-laugh-wink float-start'></i> ";
  }
  // echo $image_avatar; 
?>
<input type='hidden' id='controler' value='<?=strtolower($controler);?>' />
<div id='show_user' class="card col-lg-2 col-6 border border-1 border-info shadow p-3 me-1 float-start" >
  <div class="card-header d-flex">
  <?php
    if($avatar != ''){
        $image_user = "<img src='$avatar' class='rounded-circle m-auto card-img-top'/>";
    } else {
        $image_user = "<i class='far fa-laugh-wink float-start'></i> ";
    }
    echo $image_user;
  ?>
  </div>
  <div class="card-body">
    <h5 class="card-title"><?=$nomeus;?></h5>
    <p class="card-text"><?=session()->get('usu_perfil');?></p>
    <?php 
      echo "<hr>";
      echo anchor('SetUsuario/edit_senha', 'Alterar Senha');
      echo "<hr>";
      echo anchor(base_url('/login'), '<i class="fas fa-sign-out-alt"></i> - Sair');
    ?>
  </div>
</div>

<!-- Vertical navbar -->
<div class="col-lg-2 col-8 position-absolute bg-white shadow sidebar" id="sidebar">
  <div class="pt-2 pb-3 ps-1 pe-1">
    <div id='sistema' class='div-user text-start w-100'>
      <a href="<?=site_url();?>" >
        <button name="bt_empresa" type="button" id="bt_empresa" class="bt_empresa btn btn-outline-light w-100 px-2 text-start float-start mb-3" >
          <img src='<?=session()->icone.'?noc='.time();?>' class="img-empresa border border-2 rounded-circle me-2 " />
          <div class="usu_nome txt-bt-manut">
            <img src='<?=session()->logo.'?noc='.time();?>' class="logo-menu" />
          </div>
        </button>
      </a>
    </div>
    <div id='div_user' class='div-user text-start w-100' style='height: 4rem'>
      <button name="bt_user" type="button" id="bt_user" class="bt_user btn btn-outline-info px-2 w-100 text-start float-start mb-2" >
        <?=$image_avatar;?>
        <div class="usu_nome txt-bt-manut">
            <?=$nomeus;?>
        </div>
      </button>    
    </div>
    <?
    // início do Menu
    for($m=0;$m<count($it_menu);$m++){
      $opcao = $it_menu[$m];
      if($opcao['men_metodo'] != 'index' && ($opcao['men_hierarquia'] == '1' || $opcao['men_hierarquia'] == '4')){
        $opcao['clas_controler'] = $opcao['clas_controler'].'/'.$opcao['men_metodo'];
      }

      if($opcao['men_hierarquia'] == '1' ){ // é Opção do Menu
        $opc = cria_opcao($opcao);
        echo $opc;
      ?>
      <?
        continue;
      } else if($opcao['men_hierarquia'] == '2'){
        $accord = cria_acordion($opcao, $cont_accord,'menu');
        $cont_accord++;
        echo $accord;  
        if(isset($opcao['niv1'])){
          for($sm=0;$sm<count($opcao['niv1']);$sm++){
            $subopc = $opcao['niv1'][$sm];
            if($subopc['men_hierarquia'] == '4'){ // é Opção do Menu
              $sbo = cria_opcao($subopc);
              echo $sbo;
            } else if($subopc['men_hierarquia'] == '3'){ // é um submenu
              $subacord = cria_acordion($subopc, $cont_accord, 'submenu');
              $cont_accord++;
              echo $subacord;  
              if(isset($subopc['niv2'])){
                for($ss=0;$ss<count($subopc['niv2']);$ss++){
                  $subsub = $subopc['niv2'][$ss];
                  $sbsb = cria_opcao($subsub);
                  echo $sbsb;
                }
              }
              echo "</div></div>";
            }
          }
        }
        echo "</div></div>";
      }
    }
    ?>
    <div id='div_sair' class='div-user text-start w-100 position-absolute bottom-0 pe-2' style='height: 4rem'>
      <button name="bt_sair" type="button" id="bt_sair" class="bt_user btn btn-outline-dark px-2 py-0 w-100 text-start float-start"  data-mdb-toggle="tooltip" data-mdb-placement="top" title="Sair do Sistema" onclick="redireciona('<?=$url_sair;?>')">
        <div class='icon-menu float-start me-2'><i class="fas fa-sign-out-alt" aria-hidden="true"></i></div>
        <div class="usu_nome txt-bt-manut">
            Sair
        </div>
      </button>    
    </div>
  </div>
</div>
<?
function cria_opcao($opcao){
  // $txt = "";
  // $txt .= "<div class='ms-2 py-1 px-0 h-auto bg-blue-claro'>";
  // $txt .= "<a id='".strtolower($opcao["clas_controler"])."' href='".base_url($opcao["clas_controler"])."' class='text-body-emphasis'>";
  // $txt .= "  <div id='".strtolower($opcao["clas_controler"])."' class='nav-dropdown-menu mt-0 ms-1'>";
  // $txt .= "    <div class='align-items-center rounded-circle p-0 me-2 text-center float-start'  style='width:1.65rem; height:1.65rem;margin-top: 0.15rem !important'>";
  // $txt .= "      <i class='".$opcao["men_icone"]."'></i>";
  // $txt .= "    </div>";
  // $txt .= "    <div class='align-items-start ms-2 men_nome py-1'>".$opcao["men_etiqueta"]."</div>";
  // $txt .= "  </div>";
  // $txt .= "</a>";
  // $txt .= "</div>";

  return $txt;
}

function cria_acordion($opcao, $cont_accord, $classe){
  // $txt = "";
  // $txt .= "<div class='ms-2 py-1 px-0 h-auto bg-blue-claro' data-menu='accordion1' data-collapse='collapse".$opcao["men_id"]."'>";
  // $txt .= "  <button class='accordion-button px-1 pt-0 pb-0 collapsed text-body-emphasis' type='button' data-bs-toggle='collapse' data-bs-target='#collapse".$opcao["men_id"]."' aria-expanded='false' aria-controls='collapse".$opcao["men_id"]."'>";
  // $txt .= "    <div class='icon-menu'>";
  // $txt .= "      <i class='".$opcao["men_icone"]."'></i>";
  // $txt .= "    </div>";
  // $txt .= "    <div class='align-items-start ms-2 mod_nome'>";
  // $txt .=        $opcao["men_etiqueta"];
  // $txt .= "    </div>";
  // $txt .= "  </button>";
  // $txt .= "  <div id='collapse".$opcao["men_id"]."' class='accordion-collapse collapse $classe' aria-labelledby='head".$opcao["men_id"]."' data-bs-parent='#accord_$cont_accord'>";
  // $txt .= "    <div class='accordion' id='accordion".$cont_accord."' data-menu='accordion1' data-collapse='collapse".$opcao["men_id"].">";
  // $txt .= "      <div class='accordion-body ms-2 py-1 px-0 h-auto bg-blue-claro'>";

  return $txt;
}
?>
<?=$this->endSection();?>
