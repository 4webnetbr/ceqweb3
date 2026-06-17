<?php echo $this->extend('templates/ajax_template') ?>
<?php echo $this->section('header_modal'); ?>
<?php echo view('strut/vw_titulo_modal'); ?>
<?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>
<div id='content' class='container page-content bg-light m-0'>
    <!-- <div id='content' class='vh-auto page-content dashboard dashboard-app dashboard-content '> -->
    <form id="form_modal" method="post" action="<?php echo site_url($controler . "/" . $destino) ?>" class="col-12" type="modal" enctype="multipart/form-data">
        <?
        if (sizeof($secoes)) {
            $active = 'show active';
            echo "<div class='tab-content bg-white' id='myTabContent'>";
            for ($s = 0; $s < sizeof($secoes); $s++) {
                $secao = url_amigavel($secoes[$s]);
                echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='" . $secao . "' role='tabpanel' aria-labelledby='" . $secao . "-tab' tabindex='0'>";
                $campos_se = $campos[$s];
                $contrep   = 0;
                $cta_campo = 0;
                $tipo      = '';
                if ($campos_se[0] == 'tabela') {
                    $tipo = $campos_se[0];
                    $cta_campo = 1;
                    echo "<div id='rep_" . $secao . "' class='rep_campos d-inline-table table2-responsive rep_$secao' data-" . $secao . "-index='$contrep'>";
                    echo "<table class='table2 table-sm'>";
                    echo "<tr>";
                }
                for ($c = $cta_campo; $c < sizeof($campos_se); $c++) {
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
                    if ($s == sizeof($campos_se) - 1) {
                        $none = '';
                    }
                    echo "<button id='bt_repete__$s' data-index='__$s' class='btn btn-outline-success btn-sm bt-repete $none' type='button' onclick='repete_campo(\"" . $secao . "\",this)'><i class='fas fa-plus-square'></i></button>";
                    echo "<button id='bt_exclui__$s' data-index='__$s' class='btn btn-outline-danger  btn-sm bt-exclui' type='button' onclick='exclui_campo(\"" . $secao . "\",this)'><i class='fas fa-trash'></i></button>";
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
        <?php if (! empty($hidden)): ?>
            <?php foreach ($hidden as $h): ?>
                <input type="hidden" name="<?php echo $h['name'] ?>" value="<?php echo esc($h['value']) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
    </form>
</div>

<script>
    carregamentos_iniciais();
</script>
<?
if (isset($script)) {
    echo $script;
}
?>
<?php echo $this->endSection(); ?>