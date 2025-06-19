<?=$this->extend('templates/ajax_template')?>
<?=$this->section('header_modal');?>
<?=view('strut/vw_titulo_modal');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
    <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
    
    <div id='content' class='container page-content bg-light m-0'>
    <!-- <div id='content' class='vh-auto page-content dashboard dashboard-app dashboard-content '> -->
    <form id="form_modal" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12" type="modal" enctype="multipart/form-data">
        <?
        if (sizeof($secoes)) {
            // MOBILE
            // echo "<div id='operacoes' class='col-xs-12 d-flex d-lg-none d-md-none bg-light'>";
			// echo "<div id='linksecoes' class='col-xs-12 linksecoes'>";
		    // echo "<legend style='font-size:1.5em'>Ir para</legend>";
			// echo "<ul class='nav nav-pills nav-stacked'>";
            // for($s = 0; $s < count($secoes);$s++){
            //     $active = '';
            //     $secao = url_amigavel($secoes[$s]);
            //     if($s == 0){
            //         $active = "active";
            //     }
            //     echo "<li class='nav-item ' role='presentation'>";
			// 	echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'><i class='far fa-hand-point-right'></i>&nbsp;-&nbsp;".$secoes[$s]."</button>";
            //     echo "</li>";
            // }
			// echo "</ul>";
			// echo "</div>";
		    // echo "</div>";
            echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
            echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
			$active = 'active';
            for ($s=0;$s<sizeof($secoes);$s++) {
                echo "<li class='nav-item ' role='presentation'>";
                $secao = url_amigavel($secoes[$s]);
                // echo "Secao ".$secao;
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
				echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='".$secao."' role='tabpanel' aria-labelledby='".$secao."-tab' tabindex='0'>";
                $campos_se = $campos[$s];
                $contrep   = 0;
                $cta_campo = 0;
                $tipo      = '';
                if ($campos_se[0] == 'tabela') {
                    $tipo = $campos_se[0];
                    $cta_campo = 1;
                    echo "<div id='rep_".$secao."' class='rep_campos d-inline-table table2-responsive rep_$secao' data-".$secao."-index='$contrep'>";
                    echo "<table class='table2 table-sm'>";
                    echo "<tr>";
                }
                for ($c=$cta_campo;$c<sizeof($campos_se);$c++) {
                    if ($tipo == 'tabela') {
                        echo "<td class='d-initial h-auto align-top'>";
                    }
                    echo $campos_se[$c];
                    if ($tipo  == 'tabela') {
                        echo "</td>";
                    }
                }
                if ($tipo == 'tabela') {
                    echo "<td style='min-width:5vw;text-align:center'>";
                    $none = 'd-none';
                    if($s == sizeof($campos_se)-1){
                        $none = '';
                    }
                    echo "<button id='bt_repete__$s' data-index='__$s' class='btn btn-outline-success btn-sm bt-repete $none' type='button' onclick='repete_campo(\"".$secao."\",this)'><i class='fas fa-plus-square'></i></button>";
                    echo "<button id='bt_exclui__$s' data-index='__$s' class='btn btn-outline-danger  btn-sm bt-exclui' type='button' onclick='exclui_campo(\"".$secao."\",this)'><i class='fas fa-trash'></i></button>";
                    echo "</td>";
                    echo "</tr>";
                    echo "</table>";
                    echo "</div>";
                }
                $contrep++;
                echo "</div>";
                $active = '';
            }
            echo "</div>";
        }
		?>
    </form>    
    </div>

    <script src="<?=base_url('assets/jscript/summernote-lite.js');?>"></script>
    <script src="<?=base_url('assets/jscript/jquery.bootstrap-duallistbox.js');?>"></script>
    <!-- include summernote-pt-BR -->
    <script src="<?=base_url('assets/jscript/summ-lang/summernote-pt-BR.js');?>"></script>

    <link rel="stylesheet" href="<?=base_url('assets/css/summernote-lite.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/summernote.min.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-duallistbox.css');?>">

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjscript/latest/moment.min.js"></script> -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="<?=base_url('assets/jscript/my_mask.js?noc='.time());?>"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/js/fontawesome-iconpicker.min.js" integrity="sha512-7dlzSK4Ulfm85ypS8/ya0xLf3NpXiML3s6HTLu4qDq7WiJWtLLyrXb9putdP3/1umwTmzIvhuu9EW7gHYSVtCQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/css/fontawesome-iconpicker.min.css" integrity="sha512-BfgviGirSi7OFeVB2z9bxp856rzU1Tyy9Dtq2124oRUZSKXIQqpy+2LPuafc2zMd8dNUa+F7cpxbvUsZZXFltQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
    <?
    if(isset($script)){
        echo $script;
    }
    ?>
<?=$this->endSection();?>
