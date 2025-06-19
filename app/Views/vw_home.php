<?=$this->extend('templates/default_template')?>

<?=$this->section('header');?>
  <?=view('strut/vw_titulo');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
    <?=view('strut/vw_menu');?>
<?=$this->endSection();?>
<?=$this->section('footer');?>
  <?=view('strut/vw_rodape');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
  <div id='content' class='container '>
    <div class="row d-flex justify-content-between px-3">
      <?php
      // =monta_card();
      ?>
    </div>
  </div>
<?=$this->endSection();?>
