<?=$this->extend('templates/tab_template')?>

<?=$this->section('header');?>
  <?=view('strut/vw_titulo');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
    <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('desktop');?>
  <ul class="nav nav-tabs">
    <li id='<?=$title;?>'>
      <button class='nav-link $active' id='<?=$title;?>-tab' data-bs-toggle='tab' data-bs-target='#<?=$title;?>' type='button' role='tab' aria-controls='<?=$title;?>' aria-selected='false'><i class='far fa-hand-point-right'></i>&nbsp;-&nbsp;<?=$title;?></button>";
    </li>
  </ul>
  <div class='tab-content bg-white' id='myTabContent'>
    <div class='tab-pane fade p-lg-3 p-2 $active' id='<?=$title;?>' role='tabpanel' aria-labelledby='<?=$title;?>-tab' tabindex='0'>";
      <section class="header">
        <?=$this->renderSection('titulo');?>
      </section>
      <div id='content' class='container '>
        <div class="row d-flex justify-content-between px-3">
        </div>
      </div>
  </div>
<?=$this->endSection();?>
