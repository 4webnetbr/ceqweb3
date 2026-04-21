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
<!-- Tabulator CSS (tema Bootstrap 5) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.1/css/tabulator.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.1/css/tabulator_bootstrap5.min.css" rel="stylesheet">

<div id='content' class='container page-content bg-light m-0'>
  <div class="table-responsive col-12">
    <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
      <thead class="table-default col-12 overflow-x-auto">
        <tr class=' w-100' style='min-height:49px; height:49px;'>
          <?
          for ($c = 0; $c < sizeof($colunas); $c++) {
            echo "<th class='text-center align-middle text-wrap'>
                  <h5 class='m-0'>$colunas[$c]</h5>
                  </th>";
          }
          ?>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/bootstrap-select.css'); ?>"> -->
<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/datatables.min.css'); ?>" /> -->


<!-- Tabulator JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.1/js/tabulator.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>


<!-- <script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/bootstrap-select.js'); ?>"></script> -->
<script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/pdfmake.min.js'); ?>"></script>
<script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/vfs_fonts.js'); ?>"></script>
<!-- <script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/datatables.min.js'); ?>"></script> -->
<!-- <script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/accent-neutralize.js'); ?>"></script> -->
<!-- <script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/moment.min.js'); ?>"></script> -->
<!-- <script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/datetime-moment.js'); ?>"></script> -->
<script type="text/javascript" language="javascript" src="<?php echo base_url('assets/jscript/my_lista_tab.js'); ?>"></script>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    montaListaDadosTab("table", '<?php echo $url_lista; ?>');
    salvaPagina();
  });
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