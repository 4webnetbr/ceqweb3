<?=$this->extend('templates/tabpane_template')?>

<?=$this->section('content');?>
    <?=$this->section('header');?>
    <?=view('strut/vw_titulo');?>
    <?=$this->endSection();?>
    <div id='content' class='container page-content bg-light m-0'>
    <!-- <div id='content' class='container page-content dashboard dashboard-app dashboard-content'> -->
      <div class="table-responsive">
      <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless w-100">
        <thead class="table-default">
        <tr>
        <?
        for($c=0;$c<sizeof($colunas);$c++){
          echo "<th class='text-center'>$colunas[$c]</th>";
        }
        ?>
        </tr>
        </thead>
      </table>
      </div>
    </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha512-a9NgEEK7tsCvABL7KqtUTQjl69z7091EVPpw5KxPlZ93T141ffe1woLtbXTX+r2/8TtTvRX/v4zTL2UlMUPgwg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha512-pAoMgvsSBQTe8P3og+SAnjILwnti03Kz92V3Mxm0WOtHuA482QeldNM5wEdnKwjOnQ/X11IM6Dn3nbmvOz365g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.2/b-2.3.4/b-colvis-2.3.4/b-html5-2.3.4/b-print-2.3.4/cr-1.6.1/date-1.3.0/fc-4.2.1/fh-3.3.1/kt-2.8.1/r-2.4.0/rg-1.3.0/rr-1.3.2/sc-2.1.0/sb-1.4.0/sp-2.1.1/sl-1.6.0/sr-1.2.1/datatables.min.js"></script>

  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.2/b-2.3.4/b-colvis-2.3.4/b-html5-2.3.4/b-print-2.3.4/cr-1.6.1/date-1.3.0/fc-4.2.1/fh-3.3.1/kt-2.8.1/r-2.4.0/rg-1.3.0/rr-1.3.2/sc-2.1.0/sb-1.4.0/sp-2.1.1/sl-1.6.0/sr-1.2.1/datatables.min.css"/>
 

    <script>
      jQuery('#table').dataTable({
        "ajax" : "<?=$url_lista;?>",
        "bJQueryUI": true,
        "responsive": false,
        "sPaginationType": "full_numbers",
        "aaSorting": [],
        "oLanguage": {
          "sEmptyTable": "Nenhum registro encontrado",
          "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
          "sInfoEmpty": "Nenhum registro",
          "sInfoFiltered": "(Filtrados de _MAX_ registros)",
          "sInfoPostFix": "",
          "sInfoThousands": ".",
          "sLengthMenu": "_MENU_ linhas por página",
          "sLoadingRecords": "Carregando...",
          "sProcessing": "Processando...",
          "sZeroRecords": "Nenhum registro encontrado",
          "sSearch": "Busca: ",
          "oPaginate": {
              "sNext": "Próx. <i class='fa fa-angle-right'></i>",
              "sPrevious": "<i class='fa fa-angle-left'></i> Ante.",
              "sFirst": "<i class='fa fa-angle-double-left'></i> Prim.",
              "sLast": "Ulti. <i class='fa fa-angle-double-right'></i>"
          },
          "oAria": {
            "sSortAscending": ": Ordenar Ascendente",
            "sSortDescending": ": Ordenar Descendente"
          }
        },
        "columnDefs": [
          { "visible": false, "targets": 0 }, 
          { "width": "8em", "targets": -1 },
          { "orderable": false, "targets": [-1] },
          { "className": "text-center acao", "targets": -1},
          { "className": "acao", "targets": 0 },
          { "className": "text-wrap", "targets": 1},
        ],
        buttons: {
          dom: {
              button: {
                  className: "btn btn-outline-primary btn-sm"
              }
          },
          buttons: [
            {
                extend: 'excelHtml5',
                title: function() {
                  return document.title + ' - ' + jQuery('#legenda').text() ; 
                },
                filename: function() {
                  return document.title + ' - ' + jQuery('#legenda').text() ; 
                }, 
                exportOptions: {
                    columns: [':not(.acao)'],
                },
                // className: 'btn-outline-primary' 
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: function() {
                  return document.title + ' - ' + jQuery('#legenda').text() ; 
                },
                filename: function() {
                  return document.title + ' - ' + jQuery('#legenda').text() ; 
                }, 
                exportOptions: {
                    columns: [':not(.acao)'],
                },
                // className: 'btn-outline-primary' 
            },
            {
                extend: 'print', 
                text: 'Imprimir',
                title: function() {
                  return document.title + ' - ' + jQuery('#legenda').text() ; 
                },
                exportOptions: {
                    columns: [':not(.acao)'],
                },
                // className: 'btn-outline-primary' 
            },
          ]
        },
        "bProcessing": true,
        "bScrollCollapse": false,
        "bPaginate": true,
        "sDom": 'lrftBip', 
        // "sDom": 'fltBip',  
        "fnDrawCallback": function (oSettings) {
          jQuery('table#table > tbody > tr').on("mouseover",function() {
            jQuery(this).children().find('.btn').addClass('hover');
          }).on('mouseleave', function () {
            jQuery(this).children().find('.btn').removeClass('hover');
          });
        }
      });


      jQuery('#table').on('click', 'tbody tr td:not(".acao")', function() {
        link = jQuery(this).parent().find('a')[0].href;
        if(link != null){
          if(link.indexOf('edit/') > -1 || link.indexOf('show/') > -1){
            redireciona(link);
          }
        }
      });
    </script>
<?=$this->endSection();?>
