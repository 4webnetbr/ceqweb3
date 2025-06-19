<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('footer');?>
  <?=view('strut/vw_rodape');?>
<?=$this->endSection();?>


<?=$this->section('content');?>
<script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
    
    <div id='content' class='container page-content bg-light m-0'>
    <!-- <div id='content' class='vh-auto page-content dashboard dashboard-app dashboard-content '> -->
    <form id="form1" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12" enctype="multipart/form-data">
	  <? 
        if (sizeof($secoes)) {
            // MOBILE
            echo "<div id='operacoes' class='col-xs-12 d-flex d-lg-none d-md-none bg-light'>";
			echo "<div id='linksecoes' class='col-xs-12 linksecoes'>";
		    echo "<legend style='font-size:1.5em'>Ir para</legend>";
			echo "<ul class='nav nav-pills nav-stacked'>";
            for($s = 0; $s < count($secoes);$s++){
                $active = '';
                $secao = url_amigavel($secoes[$s]);
                if($s == 0){
                    $active = "active";
                } 
                echo "<li class='nav-item ' role='presentation'>";
				echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'><i class='far fa-hand-point-right'></i>&nbsp;-&nbsp;".$secoes[$s]."</button>";
                echo "</li>";
            }
			echo "</ul>";
			echo "</div>";
		    echo "</div>";
            echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
            echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
			$active = 'active';
            for ($s=0;$s<sizeof($secoes);$s++) {
                echo "<li class='nav-item ' role='presentation'>";
                $secao = url_amigavel($secoes[$s]);
                // echo "Secao ".$secao;
				echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'><i class='far fa-hand-point-right fa-rotate-45'></i>&nbsp;-&nbsp;".$secoes[$s]."</button>";
                echo "</li>";
				$active = '';
            }
			echo "</ul>";
            echo "</div>";
			$active = 'show active';
            echo "<div class='tab-content bg-white' id='myTabContent'>";
            for ($s=0;$s<sizeof($secoes);$s++) {
                $secao = url_amigavel($secoes[$s]);
				echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='".$secao."' role='tabpanel' aria-labelledby='".$secao."-tab' tabindex='0'>";
                $campos_se = $campos[$s];
                $contrep   = 0;
                for ($cs=0;$cs<sizeof($campos_se);$cs++) {
                    $campos_r = $campos_se[$cs];
                    $cta_campo = 0; 
                    $tipo      = '';
                    if ($campos_r[0] == 'ite_perfil') {
                        $cta_campo = 0;
                        $cta_mod = 0;
                        echo "<div class='text-center align-items-center justify-content-center'>";
                        echo "<div class='col-7 d-inline-flex'>";
                        echo "<div class='float-left col-3 text-center text-dark-emphasis fw-bold'>Módulo</div>";
                        echo "<div class='float-left col-3 text-center text-dark-emphasis fw-bold'>Tela</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Todos</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Consulta</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Adição</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Edição</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Exclusão</div>";
                        echo "<div class='float-left col-1 text-center text-dark-emphasis fw-bold'>Notificação</div>";
                        echo "</div>";
                        echo "<div class='ondeacaba'>";
                        for ($c = $cta_campo; $c < sizeof($campos_r); $c++) {
                            $campos_it = $campos2[$s]; // MÓDULOS
                            foreach ($campos_it as $item) {
                                // debug($item, false);
                                $prim  = 0;
                                $modu = '';
                                $i_collap = false;
                                for ($it=0;$it<count($item);$it++) {
                                    $item_x = $item[$it];
                                    
                                    for($mm=0;$mm<count($item_x);$mm++) {
                                        if($mm == 0){
                                            if($modu != $item_x[$mm]){
                                                $prim = 0;
                                                $cta_mod++;
                                                if($cta_mod > 1){
                                                    echo "</div>";
                                                }
                                                echo "<div class='col-7 d-inline-flex border border-1 border-info rounded-4 rounded-bottom-0 pt-2 bg-info-subtle' >";
                                                echo "<div class='float-left col-3 p-2' type='button' data-bs-toggle='collapse' data-bs-target='#collapse-mod$cta_mod' aria-expanded='false' aria-controls='collapse-mod$cta_mod'>";
                                                echo "<h4>$item_x[$mm]</h4>";
                                                echo "</div>";
                                                echo "<div class='float-left col-3' type='button' data-bs-toggle='collapse' data-bs-target='#collapse-mod$cta_mod' aria-expanded='false' aria-controls='collapse-mod$cta_mod'>";
                                                echo "";
                                                echo "</div>";
                                                $modu = $item_x[$mm];
                                                $i_collap = true;
                                            }
                                        } else if($mm == 1){
                                            if($prim > 0){
                                                echo "<div class='float-left col-3'>";
                                                echo "</div>";
                                                echo "<div class='float-left col-3 p-2'>";
                                                echo $item_x[$mm];
                                                echo "</div>";
                                            }
                                        } else {
                                            echo "<div class='float-left col-1'>";
                                            echo $item_x[$mm];
                                            echo "</div>";
                                        }
                                    }
                                    if($i_collap){
                                        echo "</div>";
                                        echo "<div id='collapse-mod$cta_mod' class='col-7 pt-2 collapse-perfil collapse border border-1 rounded-4 rounded-top-0'>";
                                        echo "<div class='col-12 d-inline-flex'>";
                                        $i_collap = false;
                                    } else {
                                        echo "</div>";
                                        echo "<div class='col-12 d-inline-flex'>";
                                    }
                                    $prim++;
                                }
                                echo "</div>";
                            }
                        }
                        echo "</div>";
                    } else { 
                        for ($c=$cta_campo;$c<sizeof($campos_r);$c++) {
                            echo $campos_r[$c];
                        }
                    }
                    $contrep++; 
                }
                echo "</div>";
                $active = '';
            }
            echo "</div>";
        }
		?>
    </form>    
    </div>
    <script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_consulta.js');?>"></script>

    <script src="<?=base_url('assets/jscript/summernote-lite.js');?>"></script>
    <script src="<?=base_url('assets/jscript/jquery.bootstrap-duallistbox.js');?>"></script>
    <!-- include summernote-pt-BR -->
    <script src="<?=base_url('assets/jscript/summ-lang/summernote-pt-BR.js');?>"></script>

    <link rel="stylesheet" href="<?=base_url('assets/css/summernote-lite.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/summernote.min.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-duallistbox.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-select.css');?>">

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjscript/latest/moment.min.js"></script> -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="<?=base_url('assets/jscript/my_mask.js?noc='.time());?>"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/js/fontawesome-iconpicker.min.js" integrity="sha512-7dlzSK4Ulfm85ypS8/ya0xLf3NpXiML3s6HTLu4qDq7WiJWtLLyrXb9putdP3/1umwTmzIvhuu9EW7gHYSVtCQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/css/fontawesome-iconpicker.min.css" integrity="sha512-BfgviGirSi7OFeVB2z9bxp856rzU1Tyy9Dtq2124oRUZSKXIQqpy+2LPuafc2zMd8dNUa+F7cpxbvUsZZXFltQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
    <script>
        carregamentos_iniciais();        
    </script>
    <?
    if(isset($script)){
        echo $script;
    }
    ?>
<?=$this->endSection();?>

