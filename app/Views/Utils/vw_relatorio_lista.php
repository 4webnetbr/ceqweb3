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
        <i class="fas fa-check"></i> Escolha o Relatório
      </h3>

      <div class="d-flex flex-column gap-2 col-lg-6 col-12">
        <?php if (empty($relatorios)): ?>
          <div class="alert alert-info">Nenhum relatório disponível para este módulo.</div>
        <?php else: ?>
          <?php foreach ($relatorios as $rel): ?>
            <button type="button" class="btn btn-outline-primary text-start py-2 px-3"
              onclick="redireciona('<?= base_url('Relatorio/filtro/' . $rel->rel_id) ?>', event)">
              <i class="fas fa-file-alt me-2"></i> <?= esc($rel->rel_titulo) ?>
            </button>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection(); ?>