<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
<style>
.dataTables_scroll {
    min-height: 63vh!important;
    height: 63vh!important;
    max-height: 63vh!important;
}  
.dataTables_scrollBody{
    min-height: 57vh!important;
    height: 57vh!important;
    max-height: 57vh!important;
}
</style>
<div id='content' class='container page-content bg-light m-0'>
    <?php
    if (sizeof($secoes)) {
        // MOBILE
        echo "<div id='operacoes' class='col-xs-12 d-flex d-lg-none d-md-none bg-light'>";
        echo "<div id='linksecoes' class='col-xs-12 linksecoes'>";
        echo "<legend style='font-size:1.5em'>Ir para</legend>";
        echo "<ul class='nav nav-pills nav-stacked'>";
        for ($s = 0; $s < count($secoes); $s++) {
            $active = '';
            $secao = url_amigavel($secoes[$s]);
            if ($s == 0) {
                $active = "active";
            }
            echo "<li class='nav-item ' role='presentation'>";
            echo "<button class='nav-link $active' id='" . $secao . "-tab' 
                  data-bs-toggle='tab' data-bs-target='#" . $secao . "' 
                  type='button' role='tab' aria-controls='" . $secao . "' 
                  aria-selected='false'>
                    <i class='far fa-hand-point-right'></i>&nbsp;-&nbsp;" . $secoes[$s] .
                "</button>";
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
        echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
        $active = 'active';
        for ($s = 0; $s < sizeof($secoes); $s++) {
            echo "<li class='nav-item ' role='presentation'>";
            $secao = url_amigavel($secoes[$s]);
            // echo "Secao ".$secao;
            echo "<span id='" . $secao . "-valid' 
                class='float-end valid-tab badge rounded-pill bg-danger d-none'>!</span>";
            echo "<button class='nav-link $active' id='" . $secao . "-tab' 
                    data-bs-toggle='tab' data-bs-target='#" . $secao . "' 
                    type='button' role='tab' aria-controls='" . $secao . "' 
                    aria-selected='false'>";
            echo "<i class='far fa-hand-point-right'></i>";
            echo "&nbsp;-&nbsp;" . $secoes[$s];
            echo "</button>";
            echo "</li>";
            $active = '';
        }
        echo "</ul>";
        echo "</div>";
        $active = 'show active';
        echo "<div class='tab-content bg-white' id='myTabContent'>";
        for ($s = 0; $s < count($secoes); $s++) {
            $secao = url_amigavel($secoes[$s]);
            echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='" . $secao . "' role='tabpanel' 
                    aria-labelledby='" . $secao . "-tab' tabindex='0'>";
            $campos_se = $campos[$s];
            $display      = (isset($displ[$s])) ? $displ[$s] : '';
            for ($c = 0; $c < sizeof($campos_se); $c++) {
                echo $campos_se[$c];
            }
            // echo "</div>";
            $active = '';
        }
          // echo "</div>";
      }
      ?>
  <div class="col-12">
    <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
      <thead class="table-default col-12">
        <tr>
        <?
          for ($c = 0; $c < sizeof($colunas); $c++){
            echo "<th class='text-center text-nowrap'><h5>$colunas[$c]</h5></th>";
          }
        ?>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
</div>
</div>
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/bootstrap-select.css');?>">
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>

<script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_consulta.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_filter.js');?>"></script>

<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/moment.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/my_lista.js');?>"></script>

<script>
</script>
<?=$this->endSection();?>
