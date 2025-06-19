<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
  <div id='content' class='container page-content bg-light m-0 overflow-auto'>
    <?php
      $ant = 1;
      for ($l = 0; $l < count($dados); $l++) {
        $dad = $dados[$l];
        if($l > 0){
          $ant = $l + 1;
        }
    ?>
        <div class="card col-4 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Data</h5></div>
          <div class="card-body">
            <?=$dad['data_alterou'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Operação</h5></div>
          <div class="card-body">
            <?=$dad['operacao'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Usuário</h5></div>
          <div class="card-body">
            <?=$dad['usua_alterou'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Tabela</h5></div>
          <div class="card-body">
            <?=$dad['tabela'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Registro</h5></div>
          <div class="card-body">
            <?=$dad['registro'];?>
          </div>
        </div>
        <div class="card col-12 float-start d-block mb-5">
          <div class="card-header bg-info-subtle"><h5>Informações Salvas na Operação</h5></div>
          <div class="card-body">
        <?php
          $anterior = [];
          $atual    = $dad['dados'][0];
          echo "<div class='card col-4 float-start d-inline card-header bg-info-subtle'>Campo</div>";
          echo "<div class='card col-4 float-start d-inline card-header bg-info-subtle'>Anterior</div>";
          echo "<div class='card col-4 float-start d-inline card-header bg-info-subtle'>Salvo</div>";
          $anterior = [];
          if($ant < count($dados)){
            $anterior = $dados[$ant]['dados'][0];
          }
          // debug($atual);
          // debug($anterior);
          foreach ($atual as $key => $value) {
            echo "<div class='row'>";
              echo "<div class='card col-4 float-start d-inline card-body'><b>" . $key . "</b></div>";
              $bg = 'bg-white';
              if(isset($anterior[$key])){
                if($value != $anterior[$key]){
                  $bg = 'bg-warning';
                }
                echo "<div class='card col-4 float-start d-inline card-body '>". $anterior[$key]."&nbsp;&nbsp;&nbsp;</div>";
              } else {
                echo "<div class='card col-4 float-start d-inline card-body'>&nbsp;&nbsp;&nbsp;</div>";
              }
              echo "<div class='card col-4 float-start d-inline card-body $bg'>" . $value . "&nbsp;&nbsp;&nbsp;</div>";
              echo "</div>";
          }
        ?>
          </div>
        </div>
    <?php
      }
      // debug($dados);
    ?>
  </div>
<?=$this->endSection();?>
