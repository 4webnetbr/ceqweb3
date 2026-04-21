<?php echo $this->extend('templates/default_template') ?>
<?php echo $this->section('header'); ?>
<?php echo view('strut/vw_titulo'); ?>
<? //=view('strut/vw_header');
?>
<?php echo $this->endSection(); ?>

<?php echo $this->section('menu'); ?>
<?php echo view('strut/vw_menu'); ?>
<?php echo $this->endSection(); ?>
<?php echo $this->section('footer'); ?>
<?php echo view('strut/vw_rodape'); ?>
<?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<!-- CSS -->
<link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
<!-- JS -->
<script type="text/javascript" src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>
<div id='content' class='container page-content bg-light m-0'>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <div class="d-flex align-items-center gap-2">
      <label for="page-size" class="mb-0">Exibir</label>
      <select id="page-size" class="form-select form-select-sm" style="width: 90px;">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50" selected>50</option>
        <option value="100">100</option>
      </select>
      <span>registros</span>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <button id="btnFiltro" class="btn btn-outline-primary btn-sm" title="Filtrar">
        <i class="fas fa-filter" aria-hidden="true"></i>
      </button>

      <button id="btnExcel" class="btn btn-outline-primary btn-sm" title="Exportar para Excel">
        <i class="far fa-file-excel" aria-hidden="true"></i>
      </button>

      <button id="btnPdf" class="btn btn-outline-primary btn-sm" title="Exportar para PDF">
        <i class="far fa-file-pdf" aria-hidden="true"></i>
      </button>

      <button id="btnPrint" class="btn btn-outline-primary btn-sm" title="Imprimir">
        <i class="fas fa-print" aria-hidden="true"></i>
      </button>

      <button id="btnRefresh" class="btn btn-outline-primary btn-sm" title="Recarregar">
        <i class="fas fa-refresh" aria-hidden="true"></i>
      </button>

      <button id="btnColunas" class="btn btn-outline-primary btn-sm" title="Colunas">
        <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
      </button>

      <label class="mb-0 ms-2" for="table-search">Pesquisar</label>
      <input id="table-search" type="text" class="form-control form-control-sm" style="width:220px;">
    </div>
  </div>

  <div id="searchBuilderPanel" class="card card-body mb-2 d-none"></div>
  <div id="menuColunas" class="dropdown-menu p-2"></div>
  <div id="table" class="table-responsive col-12"></div>
</div>
<script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/my_lista_tab.js'); ?>"></script>
<script>
  const colunasTabela = <?php echo json_encode($colunas) ?>;
  document.addEventListener("DOMContentLoaded", function () {
    montaListaDadosTab('table', '<?php echo $url_lista ?>', colunasTabela);
    salvaPagina();
  });
  salvaPagina();
</script>

<?
$modal = session()->getFlashdata('modal');

if ($modal) {
  $link  = $modal;
  $titu = session()->getFlashdata('modal-title');
  $chave = session()->getFlashdata('chave');
  $script = session()->getFlashdata('script');
  echo "<script>{$script}</script>";
}
?>


<?php echo $this->endSection(); ?>