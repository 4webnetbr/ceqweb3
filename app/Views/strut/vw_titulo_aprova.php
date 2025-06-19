<!-- Section Menu -->
<?=$this->section('titulo');
$mostra_ajuda = false;
$ajuda        = '';
$heig = '1.5rem';
if (!isset($desc_edicao)) {
  $heig = '3rem';
  $desc_edicao  = '';
}
if (strlen($ajuda) > 5) {
  $mostra_ajuda = true;
}
// }
?>
<div id='title' class='title col-12 px-lg-4 px-1 bg-danger-subtle '>
  <div class='titulo col-lg-6 col-7 float-start text-nowrap'>
    <div class='d-block float-start col-1'  style='font-size: calc(1.275rem + 1.1vw);margin-top: -.3rem;'>
      <?=$icone;?>
    </div>
    <div class='d-inline-flex float-start col-11'>
      <?="<span id='legenda' style='font-size:calc(1.3rem + 0.3vw);line-height: " . $heig . "'>" .
      $desc_metodo . " " . $title . "</span>";?>
    </div>
  </div>
  <div class='titulo col-lg-5 col-4 float-start text-right'>
  <?
    // VERIFICA O MÉTODO E AS PERMISSÕES PARA MOSTRAR OS BOTÕES
    if (isset($botao)){
      echo $botao;
    }
    if ((strpbrk($permissao, 'A') || strpbrk($permissao, 'E')) && $erromsg == ''){?>
        <button id="bt_cancelar" class="btn btn-outline-secondary bt-manut btn-sm mb-2 ms-1 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          data-bs-original-title="Cancelar Edição"
          title="Cancelar" 
          onclick="cancelar()">
          <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-undo" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start txt-bt-manut ">Cancelar</div>
        </button>
        <button id="bt_rejeitar" class="btn btn-outline-danger bt-manut btn-sm mb-2 ms-1 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          form="form1" 
          data-bs-original-title="Rejeitar"
          title="Rejeitar" 
          type="button">
          <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-xmark" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start txt-bt-manut">Rejeitar</div>
        </button>
        <button id="bt_aprovar" class="btn btn-primary bt-manut btn-sm mb-2 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          form="form1" 
          data-bs-original-title="Aprovar"
          title="Aprovar" 
          type="button">
          <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-check" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start txt-bt-manut">Aprovar</div>
        </button>
  <?  }
  ?>
  </div>
  <div class='col-lg-1 col-1 float-end text-nowrap py-2'>
    <!-- <div class="align-items-center py-1 text-center float-end" style=""> -->
      <div class="badgenotif position-absolute badge rounded-circle bg-danger d-none">
          <span id='not_novas' class='fs-7' ></span>
          <span class="visually-hidden">Notificações Novas</span>
      </div>
      <button id="bt_notifica" type="button" class="btn btn-outline-warning disabled border-1 float-end position-relative collapsed px-2 py-1" tooltip='Notificações' data-bs-toggle="collapse" data-bs-target="#show_notifica" aria-expanded="false" >
        <i class="fa-regular fa-bell" style='font-size: 1rem !important' >
        </i>
        <span class="position-absolute top- start-100 translate-middle badge rounded-pill bg-info d-none">
          <span class="visually-hidden">Mensagens</span>
        </span>
      </button>
      <?
      if ($mostra_ajuda){?>
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

<!-- Show AJUDA -->
<div id='show_ajuda' class="collapse card col-lg-3 col-6 border border-2 border-info shadow p-3 me-1 float-end" >
  <div class="card-header">
    <i class='<?=$icone;?>'>&nbsp;</i><?=$title;?>
  </div>
  <div class="card-body">
    <h5 class="card-title">Ajuda</h5>
    <p class="card-text"><?=$ajuda;?></p>
  </div>
</div>

<!-- Show NOTIFICA -->
<div id='show_notifica' class="bg-warning-subtle card col-2 border border-2 border-warning shadow p-3 me-1 float-end" >
</div>

<?=$this->endSection();?>
