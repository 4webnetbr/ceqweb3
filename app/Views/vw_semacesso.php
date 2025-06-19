<?=$this->extend('templates/default_template')?>

<?=$this->section('content');?>
  <div class="container text-center col-lg-6 col-10">
  	<div class="row mt-5 justify-content-center h-100">
    	<div class="col col-lg-8 col-12 mt-5 bg-login align-middle p-0">
        <div class="alert alert-danger" role="alert">
        <i class="fas fa-ban fa-5x"></i>
            <?=$erromsg;?>
        </div>
        <button class='btn btn-warning w-auto' onclick='history.back()'>Retornar ao Sistema</button>
      </div>
    </div>
  </div>
<?=$this->endSection();?>