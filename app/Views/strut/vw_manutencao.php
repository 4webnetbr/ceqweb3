<!-- Section Menu -->
<?=$this->section('manutencao');?>
<!-- Toggle button -->
<!-- Vertical navbar -->
<div class="col-lg-2 col-8 position-absolute bg-white shadow manut" id="manut">
  <div class="py-3 px-2">
  <?
    // VERIFICA O MÉTODO E AS PERMISSÕES PARA MOSTRAR OS BOTÕES
    // echo $metodo;
    if($metodo == 'index'|| $metodo == ''){
      if($bt_add != '' && strpbrk($permissao, 'A')){?>
        <button class="btn btn-outline-primary bt-manut btn-sm mb-2" 
            data-mdb-toggle="tooltip"
            data-mdb-placement="top" 
            title="Novo Registro" 
            onclick="redireciona('<?=$controler;?>/add/')">
            <div class="align-items-center py-1 text-center float-start font-weight-bold" style="width: 4rem !important">            
              <i class="fa fa-circle-plus btn-info" style="font-size: 4rem;"></i>
            </div>
            <div class="align-items-start ms-2 txt-bt-manut w-75">
              <?=$bt_add;?>
            </div>
        </button>
  <?  }
      if($metodo == 'filtro' || $metodo == 'show' ){?>
        <button class="btn btn-outline-info bt-manut btn-sm mb-2" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          title="Voltar" 
          onclick="redireciona('<?=base_url($controler);?>')">
          <div class="align-items-center py-1 text-center float-start font-weight-bold" style="width: 4rem !important">            
            <i class="fas fa-arrow-left" style="font-size: 4rem;"></i>
          </div>
          <div class="align-items-start ms-2 txt-bt-manut w-75">
            Voltar
          </div>
        </button>
  <?
      }
    } else if((strpbrk($permissao, 'A') || strpbrk($permissao, 'E')) && $erromsg == ''){?>
        <button class="btn btn-outline-success bt-manut btn-sm mb-2" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          form="form1" 
          data-bs-original-title="Salvar os Dados"
          title="Salvar" 
          type="submit">
          <div class="align-items-center py-1 text-center float-start font-weight-bold" style="width: 4rem !important">            
            <i class="fas fa-save" style="font-size: 4rem;"></i>
          </div>
          <div class="align-items-start ms-2 txt-bt-manut w-75">
            Salvar
          </div>
        </button>
        <button class="btn btn-outline-warning bt-manut btn-sm mb-2" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          data-bs-original-title="Cancelar Edição"
          title="Cancelar" 
          onclick="retorna_url()">
          <div class="align-items-center py-1 text-center float-start font-weight-bold" style="width: 4rem !important">            
            <i class="fas fa-undo" style="font-size: 4rem;"></i>
          </div>
          <div class="align-items-start ms-2 txt-bt-manut w-75">
            Cancelar
          </div>
        </button>
  <? }?>
  </div>
</div>
<?=$this->endSection();?>
