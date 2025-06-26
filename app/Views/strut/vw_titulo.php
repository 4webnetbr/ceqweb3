<!-- Section Menu -->
<?= $this->section('titulo');
if (!isset($title)) {
  $title = $controler;
}
$mostra_ajuda = false;
$ajuda        = '';
$heig = '1.5rem';
if (!isset($desc_edicao)) {
  $heig = '3rem';
  $desc_edicao  = '';
}
// if (!isset($desc_metodo)){
if ($metodo == 'index' || $metodo == '') {
  $ajuda = $regras_gerais;
  $desc_metodo = '';
} elseif ($metodo == 'add' || $metodo == '000') {
  $desc_metodo = 'Cadastro de ';
  $ajuda = $regras_cadastro;
} elseif ($metodo == 'edit' || $metodo == '200') {
  $ajuda = $regras_cadastro;
  $desc_metodo = 'Alteração de ';
} elseif ($metodo == 'show' || $metodo == '000') {
  $ajuda = $regras_cadastro;
  $desc_metodo = 'Consulta de ';
}
if (strlen($ajuda) > 5) {
  $mostra_ajuda = true;
}

// }
?>
<div id='title' class='title col-12 px-lg-4 px-1 bg-danger-subtle '>
  <div class='titulo col-lg-6 col-7 float-start text-nowrap'>
    <div class='d-block float-start col-1' style='font-size: calc(1.275rem + 1.1vw);margin-top: -.3rem;'>
      <?= $icone; ?>
    </div>
    <div class='d-inline-flex float-start col-11'>
      <?= "<span id='legenda' style='font-size:calc(1.3rem + 0.3vw);line-height: " . $heig . "'>" .
        $desc_metodo . " " . $title . "</span>"; ?>
    </div>
    <?
    if ($desc_edicao != '') { ?>
      <div class='d-inline-flex float-start col-11'>
        <?= "<span id='desc_edicao' style='font-size:calc(1rem + 0.1vw);line-height: 1.5rem'>" .
          $desc_edicao . "</span>"; ?>
      </div>
    <?
    } ?>
  </div>
  <div class='titulo col-lg-5 col-4 float-start text-right'>
    <?
    // VERIFICA O MÉTODO E AS PERMISSÕES PARA MOSTRAR OS BOTÕES
    // echo $metodo;
    if ($metodo == 'index' || $metodo == '') {
      if (strlen($bt_add) > 2 && strpbrk($permissao, 'A')) { ?>
        <button id="bt_add" class="btn btn-outline-primary bt-manut btn-sm mb-2 float-end add"
          onclick="redireciona('<?= $controler; ?>/add/')">
          <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">
            <i class="fa fa-circle-plus btn-info" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start txt-bt-manut d-none"><?= $bt_add; ?></div>
        </button>
      <?  }
    } elseif ((isset($mostrar)) || ($metodo == 'filtro' || $metodo == 'show') || ($destino == '')) { ?>
      <button id="bt_voltar" class="btn btn-outline-info bt-manut btn-sm mb-2 float-end"
        onclick="retorna_url()">
        <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">
          <i class="fas fa-arrow-left" style="font-size: 2rem;"></i>
        </div>
        <div class="align-items-start txt-bt-manut">Voltar</div>
      </button>
    <?
    } elseif ((!isset($mostrar) || $destino != '' &&
      (strpbrk($permissao, 'A') || strpbrk($permissao, 'E')) &&
      $erromsg == '')) { ?>
      <button id="bt_cancelar" class="btn btn-outline-secondary bt-manut btn-sm mb-2 ms-1 float-end"
        onclick="cancelar()">
        <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">
          <i class="fas fa-undo" style="font-size: 2rem;"></i>
        </div>
        <div class="align-items-start txt-bt-manut ">Cancelar</div>
      </button>
      <button id="bt_salvar" class="btn btn-primary bt-manut btn-sm mb-2 ms-1 float-end" form='form1' type='submit'>
        <div class="align-items-center py-15 text-start float-start font-weight-bold" style="">
          <i class="fas fa-save" style="font-size: 2rem;"></i>
        </div>
        <div class="align-items-start txt-bt-manut">Salvar</div>
      </button>
    <?  }
    if (isset($botao)) {
      echo $botao;
    }
    ?>
  </div>
  <div class='col-lg-1 col-1 float-end text-nowrap py-2'>
    <!-- <div class="align-items-center py-1 text-center float-end" style=""> -->
    <span id="badgenotif" class="badgenotif badge rounded-circle bg-danger d-none fs-7">
    </span>
    <button id="bt_notifica" class="btn btn-outline-light border-1 float-end position-relative collapsed px-2 py-1"
      data-bs-toggle="collapse"
      data-bs-target="#show_notifica"
      aria-expanded="false">
      <i class="fa-regular fa-bell" style='font-size: 1rem !important'>
      </i>
    </button>
    <?
    if ($mostra_ajuda) { ?>
      <button id="bt_ajuda" class="btn btn-outline-info border-1 float-end position-relative me-2 collapsed px-2 py-1"
        data-bs-toggle="collapse"
        data-bs-target="#show_ajuda"
        aria-expanded="false">
        <i class="fas fa-question" style='font-size: 1rem !important'>
        </i>
      </button>
    <?
    } ?>
    <!-- </div> -->
  </div>
</div>

<!-- Show AJUDA -->
<div id='show_ajuda' class="collapse card col-lg-3 col-6 border border-2 border-info shadow p-3 me-1 float-end">
  <div class="card-header">
    <i class='<?= $icone; ?>'>&nbsp;</i><?= $title; ?>
  </div>
  <div class="card-body">
    <h5 class="card-title">Ajuda</h5>
    <p class="card-text"><?= $ajuda; ?></p>
  </div>
</div>

<!-- Show NOTIFICA -->
<div id='show_notifica' class="collapse card col-2 border border-2 border-warning shadow p-3 me-1 float-end bg-warning-subtle ">
</div>

<?= $this->endSection(); ?>