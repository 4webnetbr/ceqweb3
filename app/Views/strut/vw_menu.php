<!-- Section Menu -->
<?

echo $this->section('menu');

$url_sair = base_url('/login/logout');
$avatar = session()->get('usu_avatar')??'avatar';
$nomeus = session()->get('usu_nome')??'Sem nome';
$idusua = session()->get('usu_id')??'Sem id';

$cont_accord = 1;
$accordraiz = false;
if ($avatar != '') {
    $image_avatar = "<img src='$avatar' class='img-user rounded-circle me-3 float-start' />";
} else {
    $image_avatar = "<i class='far fa-laugh-wink float-start'></i> ";
}
?>
<input type='hidden' id='controler' value='<?php echo $controler; ?>' />
<input type='hidden' id='usu_id' value='<?php echo $idusua; ?>' />
<div id='show_user' class="card col-lg-2 col-6 border border-1 border-info shadow p-3 me-1 float-start" >
  <div class="card-header d-flex">
    <?
    if ($avatar != '') {
        $image_user = "<img src='$avatar' class='rounded-circle m-auto card-img-top'/>";
    } else {
        $image_user = "<i class='far fa-laugh-wink float-start'></i> ";
    }
    echo $image_user;
    ?>
  </div>
  <div class="card-body">
    <h5 class="card-title"><?php echo $nomeus; ?></h5>
    <p class="card-text"><?php echo session()->get('usu_perfil'); ?></p>
    <?
        echo "<hr>";
    ?>
    <div class="form-check form-switch form-check-inline
                form-control p-1 btn btn-outline-primary w-100" >
      <div class="d-inline-flex">
          <label class="form-check-label px-1 m-auto mx-0" for="menuaberto"> Manter menu aberto </label>
          <input type="checkbox" name="menuaberto" id="menuaberto" class="form-check-input ml-2 "
                onchange="mudamenuaberto()" />
      </div>
    </div>
    <?
        echo anchor('CfgUsuario/edit_senha/' . session()->usu_id, 'Editar Meus Dados', 'class="form-control px-1 btn btn-outline-secondary w-100"');
        echo "<hr>";
        echo anchor($url_sair, '<div class="icon-menu float-start me-2">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i></div>
                    <div class="text-start txt-bt-manut">
                      Sair
                    </div>', 'class="bt_user btn btn-outline-dark px-2 py-0 w-100 text-start float-start"');
    ?>
  </div>
</div>

<!-- Vertical navbar -->
<div class="col-lg-2 col-8 position-absolute bg-white shadow sidebar active" id="sidebar">
  <div class="pt-2 pb-1 ps-1 pe-1">
    <div id='sistema' class='div-user text-start w-100'>
      <a href="<?php echo site_url(); ?>" >
        <div id="bt_empresa" name="bt_empresa" title="Empresa" type="button"
                    class="bt_empresa w-100 px-2 text-start float-start mb-3" >

          <img src='<?php echo session()->icone; ?>'
                    class="img-empresa rounded-circle me-2 " />

          <div class="usu_nome txt-bt-manut">
            <img src='<?php echo session()->logo; ?>' class="logo-menu" />
          </div>
        </div>
      </a>
    </div>
    <div id='div_user' class='div-user text-start w-100' style='height: 4rem'>
      <button name="bt_user" type="button" id="bt_user"
              class="bt_user btn btn-outline-info px-2 w-100 text-start float-start mb-2" >

        <?php echo $image_avatar; ?>
        <div class="usu_nome txt-bt-manut">
            <?php echo $nomeus; ?>
        </div>
      </button>
    </div>
    <div id='div_search' class='text-start w-100 float-start' style='height: 2.5rem'>
        <div class="input-group mt-0  " style="width: 34ch !important; max-width: 34ch !important;">
            <div class="input-group-text input-group-addon pe-auto" data-refer="inp_search" id="dw_inp_search"><i class="fas fa-search"></i></div>
            <input type="search" name="inp_search" value="" id="inp_search" size="30" maxlength="30" class="form-control p-0 ps-1" data-live-search="true" placeholder="" hint="Pesquisar" autocomplete="off" onchange="" onblur="" onkeyup="buscaMenu(this)" data-mdb-toggle="tooltip" data-mdb-placement="top" title="Pesquisar">
        </div>
    </div>
    <?
    foreach ($it_menu as $ke => $valu) {
      $opcao = $it_menu[$ke];
      if ($opcao['men_metodo'] != 'index' && ($opcao['men_hierarquia'] == '1' || $opcao['men_hierarquia'] == '4')) {
        $opcao['tel_controler'] = $opcao['tel_controler'] . '/' . $opcao['men_metodo'];
      }

      if ($opcao['men_hierarquia'] == '1') { // é Opção do Menu
      ?>
        <div class="accordion accordion-item accordion-body ms-2 py-1 px-0 h-auto bg-blue-claro">
        <a id="<?php echo strtolower($opcao['tel_controler']); ?>" href="<?php echo base_url($opcao['tel_controler']); ?>"
                class="text-body-emphasis w-100" >
          <div id="<?php echo strtolower($opcao['tel_controler']); ?>" data-menu='' class='nav-dropdown-menu mt-0 ms-1 mb-1'>
            <div class='align-items-center rounded-circle p-0 me-2 text-center float-start'
                  style='width:1.65rem; height:1.65rem;margin-top: 0.15rem !important'>

              <i class='<?php echo $opcao['men_icone']; ?>'></i>
            </div>
            <div class='align-items-start ms-2 men_nome py-1 w-100'><?php echo $opcao['men_etiqueta']; ?></div>
          </div>
        </a>
        </div>
      <?
        continue;
      } elseif ($opcao['men_hierarquia'] == '2') {
        // debug($opcao);
        if (isset($opcao['niv1'])) {
          if (!$accordraiz) {
            $accordraiz = true;
            echo "<div class='accordion acc_base' id='accordionMenu'>";
          }
      ?>
            <div class="accordion-item">
              <h2 class="accordion-header" id="head<?php echo $opcao['men_id']; ?>">
                <button class="accordion-button menu px-1 pt-0 pb-0 collapsed text-body-emphasis" type="button" data-bs-toggle="collapse" data-bs-target="#collapsemenu<?php echo $opcao['men_id']; ?>" aria-expanded="false" aria-controls="collapsemenu<?php echo $opcao['men_id']; ?>">
                  <div class='align-items-center rounded-circle p-0 text-center float-start font-weight-bold'>
                      <div class='icon-menu'><i class='<?php echo $opcao['men_icone']; ?>'></i></div>
                  </div>
                  <div class='align-items-start ms-2 mod_nome'>
                      <?php echo $opcao['men_etiqueta']; ?>
                  </div>
                </button>
              </h2>
              <div id="collapsemenu<?php echo $opcao['men_id']; ?>" class="accordion-collapse collapse" aria-labelledby="head<?php echo $opcao['men_id']; ?>" data-bs-parent="#accordionMenu">
                <div class="accordion-body ms-1 py-1 px-0 h-auto bg-blue-claro">
                <?
                  // $subopc = $opcao[$m];
                  // debug($opcao['niv1'], true);
                  foreach ($opcao['niv1'] as $key => $value) {
                    $subopc = $opcao['niv1'][$key];
                    if ($subopc['men_hierarquia'] == '4') { // é Opção do Menu
                    ?>
                      <a id="<?php echo strtolower($subopc['tel_controler']); ?>" href="<?php echo base_url($subopc['tel_controler']); ?>" class="text-body-emphasis" >
                        <div id="<?php echo strtolower($subopc['tel_controler']); ?>" data-menu='accordionMenu'  data-collapse='collapsemenu<?php echo $opcao['men_id']; ?>' class='nav-dropdown-menu mt-0 ms-1 mb-1'>
                          <div class='align-items-center rounded-circle p-0 me-2 text-center float-start'
                                style='width:1.65rem; height:1.65rem;'>
                            <i class='<?php echo $subopc['men_icone']; ?>'></i>
                          </div>
                          <div class='align-items-start ms-2 men_nome py-0 w-100'><?php echo $subopc['men_etiqueta']; ?></div>
                        </div>
                      </a>
                    <?
                    } elseif ($subopc['men_hierarquia'] == '3') {
                      $cont_accord++;
                      if (isset($subopc['niv2'])) {
                      ?>
                        <div class="accordion" id="accordion<?php echo $cont_accord; ?>" data-menu='accordionMenu' data-collapse='collapsemenu<?php echo $opcao['men_id']; ?>'>
                          <div class="accordion-item">
                            <h2 class="accordion-header" id="head<?php echo $subopc['men_id']; ?>">
                              <button class="accordion-button submenu px-0 pt-0 pb-0 collapsed text-body-emphasis" type="button" data-bs-toggle="collapse" data-bs-target="#collapsemenu<?php echo $subopc['men_id']; ?>" aria-expanded="false" aria-controls="collapsemenu<?php echo $subopc['men_id']; ?>">
                                <div class='align-items-center rounded-circle p-0 text-center float-start font-weight-bold'>
                                  <div class='icon-submenu'><i class='<?php echo $subopc['men_icone']; ?>'></i></div>
                                </div>
                                <div class='align-items-start ms-2 mod_nome'>
                                    <?php echo $subopc['men_etiqueta']; ?>
                                </div>
                              </button>
                            </h2>
                            <div id="collapsemenu<?php echo $subopc['men_id']; ?>" class="accordion-collapse submenu collapse " aria-labelledby="head<?php echo $subopc['men_id']; ?>" data-bs-parent="#accordion<?php echo $cont_accord; ?>">
                              <div class="accordion-body ms-2 py-1 px-0 h-auto bg-blue-claro">
                              <?
                                foreach($subopc['niv2'] as $key2 => $value2) {
                                  $subsub = $subopc['niv2'][$key2];
                                  $onclic = base_url($subsub['tel_controler']);
                                  ?>
                                  <a id="<?php echo strtolower($subsub['tel_controler']); ?>" href="<?php echo $onclic; ?>" class="text-body-emphasis" >
                                    <div id="<?php echo strtolower($subsub['tel_controler']); ?>" data-submenu='accordion<?php echo $cont_accord; ?>' data-collapse='collapsemenu<?php echo $subopc['men_id']; ?>' class='nav-dropdown-menu mt-0 ms-1 mb-1'>
                                      <div class='align-items-center rounded-circle p-0 me-2 text-center float-start'  style='width:1.45rem; height:1.45rem;'>
                                        <i class='<?php echo $subsub['men_icone']; ?>'></i>
                                      </div>
                                      <div class='align-items-start ms-2 men_nome py-0 w-100'><?php echo $subsub['men_etiqueta']; ?></div>
                                    </div>
                                  </a>
                                <?
                                }
                                ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?
                      }
                    }
                  }
                  ?>
                </div>
              </div>
            </div>
          <?
          }
        }
    }
    ?>
    </div>
    <div id='div_sair' class='div-user text-start w-100 position-absolute bottom-0 pe-2' style='height: 4rem'>
      <button name="bt_sair" type="button" id="bt_sair" class="bt_user btn btn-outline-dark px-2 py-0 w-100 text-start float-start"  data-mdb-toggle="tooltip" data-mdb-placement="top" title="Sair do Sistema" onclick="redireciona('<?php echo $url_sair; ?>')">
        <div class='icon-menu float-start me-2'><i class="fas fa-sign-out-alt" aria-hidden="true"></i></div>
        <div class="usu_nome txt-bt-manut">
            Sair
        </div>
      </button>
    </div>
</div>
<?php echo $this->endSection(); ?>
