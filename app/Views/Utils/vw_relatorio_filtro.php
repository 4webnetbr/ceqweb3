<?= $this->extend('templates/default_template') ?>
<?= $this->section('header'); ?>
<?= view('strut/vw_titulo'); ?>
<?= $this->endSection(); ?>

<?= $this->section('menu'); ?>
<?= view('strut/vw_menu'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div id="content" class="container page-content bg-light m-0">
  <div class='tab-content bg-white mt-3' id='myTabContent'>
    <div class='tab-pane fade p-lg-3 p-2 show active' id='rellista' role='tabpanel' aria-labelledby='rellista-tab' tabindex='0'>
      <h3 class="mt-3 mb-3">
        <i class="fas fa-filter"></i> Escolha as opções de Filtro - <?= esc($relatorio->rel_titulo) ?>
      </h3>

      <form id="form_filtro_relatorio" class="col-12" method="post" target="_blank"
        action="<?= $url_gerar ?>">
        <?php if (empty($campos_filtro)): ?>
          <div class="alert alert-info col-12">Este relatório não possui filtros cadastrados.</div>
        <?php endif; ?>
        <?php foreach ($campos_filtro as $campoHtml): ?>
          <?= $campoHtml ?>
        <?php endforeach; ?>

        <div class="row float-start col-12 mt-3">
          <button type="submit" class="btn btn-primary col-2">
            <i class="fas fa-print"></i> Gerar Relatório
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bootstrap-select.css'); ?>">
<script src="<?= base_url('assets/jscript/bootstrap-select.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?= $this->endSection(); ?>