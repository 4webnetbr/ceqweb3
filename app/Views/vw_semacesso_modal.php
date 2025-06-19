<?=$this->extend('templates/ajax_template')?>

<?=$this->section('content');?>
  <div id='content' class='container page-content bg-light m-0 text-center'>
  	<div class="row mt-5 justify-content-center h-100">
    	<div class="col col-lg-8 col-12 mt-5 bg-login align-middle p-0">
        <div class="alert alert-danger" role="alert">
        <i class="fas fa-ban fa-5x"></i>
            <?=$erromsg;?>
        </div>
        <button class='btn btn-warning w-auto' onclick=''>Retornar ao Sistema</button>
      </div>
    </div>
  </div>
<?=$this->endSection();?>