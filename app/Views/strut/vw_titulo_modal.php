<!-- Section Menu -->
<?=$this->section('titulo');
$mostra_ajuda = false;
$ajuda        = '';
// if(!isset($desc_metodo)){
  if($metodo == 'index'|| $metodo == ''){
    $ajuda = $regras_gerais;
    $desc_metodo = '';
  } else if($metodo == 'add'){
    $desc_metodo = 'Cadastro de ';
    $ajuda = $regras_cadastro;
  } else if($metodo == 'edit'){
    $ajuda = $regras_cadastro;
    $desc_metodo = 'Alteração de ';
  } else if($metodo == 'show'){
    $ajuda = $regras_cadastro;
    $desc_metodo = 'Consulta de ';
  }
  if(strlen($ajuda) > 5){
    $mostra_ajuda = true;
  }
// }
?>
<div id='title' class='col-11 position-absolute px-lg-4 px-1 bg-white border-1 border-bottom'>
  <div class='titulo col-lg-6 col-7 float-start text-nowrap'>
    <h4 class='d-inline-flex'  style='font-size: calc(1.275rem + 1.1vw)'>
      <?="<i class='".$icone."'></i> <span class='ms-4' style='font-size:calc(1.275rem + 0.3vw);line-height: 2.5rem'>".$desc_metodo." ".$title.'</span>';?>
    </h4>
  </div>
  <div class='titulo col-lg-5 col-4 float-start text-right'>
  <?
    if((strpbrk($permissao, 'A') || strpbrk($permissao, 'E')) && $erromsg == ''){?>
        <button id="bt_salvar" class="btn btn-outline-success bt-manut btn-sm mb-2 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          form="form_modal" 
          data-bs-original-title="Salvar os Dados"
          title="Salvar" 
          type="submit">
          <div class="align-items-center py-1 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-save" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start txt-bt-manut">
            Salvar
          </div>
        </button>
  <?  }
  ?>
  </div>
  <div class='col-lg-1 col-1 float-end text-nowrap py-2'>
    <!-- <div class="align-items-center py-1 text-center float-end" style=""> -->
      <?
      if($mostra_ajuda){?>
        <button id="bt_ajuda" type="button" class="btn btn-outline-info border-1 float-end position-relative me-2 collapsed px-2 py-1" tooltip='Ajuda' data-bs-toggle="collapse" data-bs-target="#show_ajuda" aria-expanded="false"  >
          <i class="fas fa-question" style='font-size: 1rem !important' >
          </i>
          <span class="position-absolute top- start-100 translate-middle badge rounded-pill bg-info d-none">
            <span class="visually-hidden">Ajuda</span>
          </span>
        </button>
      <?
      }?>
    <!-- </div> -->
  </div>
</div>
<div id='show_ajuda' class="collapse card col-lg-3 col-6 border border-2 border-info shadow p-3 me-1 float-end" >
  <div class="card-header">
    <i class='<?=$icone;?>'>&nbsp;</i><?=$title;?>
  </div>
  <div class="card-body">
    <h5 class="card-title">Ajuda</h5>
    <p class="card-text"><?=$ajuda;?></p>
  </div>
</div>

<?=$this->endSection();?>
