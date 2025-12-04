<?= $this->extend('templates/default_template') ?>

<?= $this->section('header'); ?>
<?= view('strut/vw_titulo'); ?>
<?= $this->endSection(); ?>

<?= $this->section('menu'); ?>
<?= view('strut/vw_menu'); ?>
<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<?= view('strut/vw_rodape'); ?>
<?= $this->endSection(); ?>


<?= $this->section('content'); ?>

<div id='content' class='container page-content bg-light m-0'>

    <form id="form1" data-alter method="post"
          action="<?= site_url($controler . "/" . $destino) ?>"
          class="col-12" enctype="multipart/form-data">

        <?php if (sizeof($secoes)) : ?>

            <!--   ABAS MOBILE        -->

            <div id='operacoes' class='col-xs-12 d-flex d-lg-none d-md-none bg-light'>
                <div id='linksecoes' class='col-xs-12 linksecoes'>
                    <legend style='font-size:1.5em'>Ir para</legend>

                    <ul class='nav nav-pills nav-stacked'>
                        <?php foreach ($secoes as $i => $nome) : 
                            $secao = url_amigavel($nome); ?>
                            <li class='nav-item'>
                                <button class='nav-link <?= $i == 0 ? "active" : "" ?>'
                                        id="<?= $secao ?>-tabr"
                                        data-bs-toggle='tab'
                                        data-bs-target='#<?= $secao ?>'>
                                    <i class='far fa-hand-point-right'></i> - <?= $nome ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </div>

            <!--   ABAS DESKTOP       -->

            <div class='col-lg-12 col-md-12 d-lg-flex d-none'>
                <ul class='nav nav-tabs border-0 d-none d-lg-flex' id='myTab'>
                    <?php foreach ($secoes as $i => $nome) : 
                        $secao = url_amigavel($nome); ?>
                        <li class='nav-item'>
                            <span id="<?= $secao ?>-valid"
                                  class='float-end valid-tab badge rounded-pill bg-danger d-none'>!</span>

                            <button class='nav-link <?= $i == 0 ? "active" : "" ?>'
                                    id="<?= $secao ?>-tab"
                                    data-bs-toggle='tab'
                                    data-bs-target='#<?= $secao ?>'>
                                <i class='far fa-hand-point-right'></i> - <?= $nome ?>
                            </button>

                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!--     CONTEÚDO ABAS     -->

            <div class='tab-content bg-white' id='myTabContent'>

                <?php foreach ($secoes as $i => $nome) : 
                    $secao = url_amigavel($nome); ?>

                    <div class='tab-pane fade p-lg-3 p-2 <?= $i == 0 ? "show active" : "" ?>'
                         id="<?= $secao ?>" role='tabpanel'>

                        <!-- Campos normais -->
                        <?php
                        $campos_se = $campos[$i];
                        foreach ($campos_se as $campo) {
                            echo $campo;
                        }
                        ?>

                        <!--        BLOCO DA T9             -->

                        <?php if ($secao == "telas_aplicaveis") : ?>
                            
                            <div class="mt-4">
                                <h6>Tela(s)</h6>
                                <div id="div_telas"></div>
                            </div>

                        <?php endif; ?>


                        <?php if ($secao == "acoes") : ?>

                            <div class="mt-4">
                                <h6>Ação</h6>
                                <div id="div_acoes"></div>
                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </form>

</div>



<!--  ** IMPORTS ** -->


<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/bootstrap-select.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/summernote-lite.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/jquery.bootstrap-duallistbox.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/summ-lang/summernote-pt-BR.js'); ?>"></script>

<link rel="stylesheet" href="<?= base_url('assets/css/summernote-lite.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-select.css'); ?>">

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

<script src="<?= base_url('assets/jscript/my_mask.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_consulta.js'); ?>"></script>



<!--   SCRIPT ESPECÍFICO DA T9 (AJAX + EXIBIÇÃO)  -->


<script>
jQuery(document).ready(function () {

    // Procurar o select do Tipo de Ocorrência (em todas as possibilidades)
    let selectTipo = jQuery(
        "select[name='tpo_id'], " +
        "select[name='moc_tpo_id'], " +
        "select[id='moc_tpo_id'], " +
        "select[id='tpo_id']"
    );

    // Se achou o select, padroniza ID
    if (selectTipo.length > 0) {
        selectTipo.attr("id", "tpo_id");
    } else {
        console.warn("⚠ Nenhum select de Tipo de Ocorrência encontrado!");
    }

    // Carrega ao abrir a tela
    let id = jQuery("#tpo_id").val();
    if (id) {
        carregarTelasEAcoes(id);
    }

    // Carrega quando muda
    jQuery(document).on("change", "#tpo_id", function () {
        carregarTelasEAcoes(jQuery(this).val());
    });

});


//       FUNÇÃO PRINCIPAL — CARREGA TELAS E AÇÕES

function carregarTelasEAcoes(tpo_id) {

    jQuery.post(
        "<?= base_url('OcoModOcorrencia/buscaTelasEAcoes') ?>",
        { tpo_id: tpo_id },

        function (res) {

            jQuery("#div_telas").html("");
            jQuery("#div_acoes").html("");

            // TELAS
            if (!res.telas || res.telas.length === 0) {

                jQuery("#div_telas").html(`
                    <div class='alert alert-info text-center'>
                        Nenhuma Tela vinculada ao Tipo.
                    </div>
                `);

            } else {

                res.telas.forEach(t => {

                    jQuery("#div_telas").append(`

                        <div class="row tableDiv table2 mb-3 p-2" 
                             style="border-bottom:1px solid #eee;">

                            <div class="col-11 d-flex align-items-center">
                                <span 
                                    style="display:block; width:30%; background:#f1f5f9; padding:10px 15px;
                                           border-radius:20px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    ${t.tel_nome}
                                </span>
                            </div>

                            <div class="col-1 text-end">
                                <button class="btn btn-outline-danger btn-sm"
                                        onclick="removerTela(${t.tel_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                        </div>

                    `);
                });
            }

            // AÇÕES
           if (!res.acoes || res.acoes.length === 0) {

                jQuery("#div_acoes").html(`
                    <div class='alert alert-info text-center'>
                        Nenhuma Ação vinculada ao Tipo.
                    </div>
                `);

            } else {

                res.acoes.forEach(a => {

    jQuery("#div_acoes").append(`

        <div class="row tableDiv table2 mb-3 p-2" 
             style="border-bottom:1px solid #eee;">

            <div class="col-11 d-flex align-items-center">
                <span 
                    style="display:block; width:30%; background:#f1f5f9; padding:10px 15px;
                           border-radius:20px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    ${a.tpa_nome}
                </span>
            </div>

            <div class="col-1 text-end">
                <button class="btn btn-outline-danger btn-sm"
                        onclick="removerAcao(${a.tpa_id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

        </div>

    `);
});
            }

        },
        "json"
    );
}

//          FUNÇÕES REMOVER (TELAS/AÇÕES)

function removerTela(id) {
    jQuery("[data-tel='" + id + "']").remove();
}

function removerAcao(id) {
    jQuery("[data-tpa='" + id + "']").remove();
}
</script>

<?= $this->endSection(); ?>
