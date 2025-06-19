<?=$this->extend('templates/tab_template')?>

<?=$this->section('menu');?>
    <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
    <div id='content' class='container page-content bg-light m-0 p-0 col-12'>
      <?=$this->section('header');?>
        <?=view('strut/vw_titulo');?>
      <?=$this->endSection();?>
      <form id="form1" method="post"  action="" class="col-12" enctype="multipart/form-data">
        <?
          echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
          echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
          $active = 'active';
          for ($s=0;$s<sizeof($secoes);$s++) {
            echo "<li class='nav-item ' role='presentation'>";
              $secao = url_amigavel($secoes[$s]);
              echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'><i class='far fa-hand-point-right'></i>&nbsp;-&nbsp;".$secoes[$s]."</button>";
            echo "</li>";
            $active = '';
          }
          echo "</ul>";
          echo "</div>";
          $active = 'show active';
          echo "<div class='tab-content bg-white' id='myTabContent'>";
          for ($s=0;$s<sizeof($secoes);$s++) {
            $secao = url_amigavel($secoes[$s]);
            $stat = $secoes[$s];
            echo "<div class='tab-pane fade p-lg-3 p-0 $active' id='".$secao."' role='tabpanel' aria-labelledby='".$secao."-tab' tabindex='0'>";
            // debug($ops[$stat]);
            for ($ctop=0; $ctop < count($ops[$stat]); $ctop++) { 
              if($ctop == 0){
                echo "<div class='row mb-4'>"; 
              }
              $op_mostra = $ops[$stat][$ctop];
              echo monta_card($op_mostra);
              if($ctop > 0 && (($ctop+1) % 5) == 0){
                echo "</div>";
                if($ctop < count($ops[$stat])){
                  echo "<div class='row mb-4'>";
                }
              }
            }
            echo "</div>";
            echo "</div>";
            $active = '';
          }
          echo "</div>";
          echo "</div>";
        ?>
      </form>
    </div>
<?=$this->endSection();?>
