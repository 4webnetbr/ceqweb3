/**
 * montaListaDados
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
var table;
var columHide = null;

function montaListaDados(tabela, url) {
    jQuery.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora

    // monta o Datable 
    table = jQuery('#' + tabela).DataTable(
        {
            // "serverSide": true,
            "ajax": {
                "url": url,
                "type": "Post",
                "datatype": "json"
            },
            "retrieve": true,
            "stateSave": true,
            "stateSaveParams": function (settings, data) {
                data.usuario = jQuery('#usu_id').val();
            },
            "stateLoadParams": function (settings, data) {
                // jQuery('#myInput').val(data.custom);
                // alert(data.length);
            },
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "columnDefs": [
                { "visible": false, "targets": [0] },
                { "max-width": "8em", "targets": [-1] },
                { "min-width": "8em", "targets": [-1] },
                { "width": "8em", "targets": [-1] },
                { "orderable": false, "targets": [-1] },
                { "searchable": false, "targets": [-1] },
                { "className": "acao text-center text-nowrap", "targets": [-1] },
            ],
            "buttons": {
                dom: {
                    button: {
                        className: "btn btn-outline-primary wauto",
                        style: "max-width: 31.5px!important;"
                    }
                },
                buttons: [
                    {
                        extend: 'searchBuilder',
                        text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                        titleAttr: 'Filtrar',
                        config: {
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            id: 'bt_filtro',
                            columns: [':not(.acao)', ':visible'],
                            defaultCondition: 'Igual',
                        },
                        preDefined: {
                            criteria: [
                                {
                                    condition: 'Igual',
                                },
                            ]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para Excel',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para PDF',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'print',
                        autoPrint: true,
                        text: '<i class="fa fa-print" aria-hidden="true"></i>',
                        titleAttr: 'Enviar para Impressora',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: ':not(.acao)'
                        },
                    },
                    {
                        text: '<i class="fa fa-refresh" aria-hidden="true"></i>',
                        action: function (e, dt, node, config) {
                            // var table = jQuery('#table').DataTable();
                            table.state.clear();
                            window.location.reload();
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa-solid fa-table-columns"></i>',
                        columns: [':not(.acao)'],
                        popoverTitle: 'Colunas'
                    },
                ]
            },
            "bSortCellsTop": true,
            "pageLength": 50,
            "bPaginate": true,
            // "sScrollX": "100vw",
            "scrollY": 'calc(100vh - 15rem)',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'lftrBip',
            "language": {
                "url": window.location.origin + '/assets/jscript/datatables-lang/pt-BR.json',
            },

            createdRow: function (row, data, dataIndex) {
                title = '';
                jQuery(row).find('td').each(function (index, td) {
                    valelement = jQuery(td)[0].innerHTML;
                    if (valelement.indexOf('<ttp>') > 0) {
                        title = valelement.substring(valelement.indexOf('<ttp>') + 5, valelement.length - 6);
                        valelement = valelement.substring(0, valelement.indexOf('<ttp>'));
                        jQuery(td)[0].innerHTML = valelement;
                        jQuery(td).attr('title', title);
                        jQuery(td).attr('data-bs-toggle', 'tooltip');
                        jQuery(td).attr('data-bs-placement', 'bottom');
                        jQuery(td).attr('data-bs-custom-class', 'ttpDataTable');
                    }
                    // jQuery(td).attr('data-bs-placement', 'bottom');
                });
                // }
            },

            // drawCallback: function () {
            //     jQuery('[data-toggle="tooltip"]').tooltip();
            // }
        });

    jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
        link = jQuery(this).parent().find('a')[0].href;
        if (link != null) {
            if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                redireciona(link);
            }
        }
    });

    table.on('draw', function () {
        jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        jQuery('[data-bs-toggle="tooltip"]').tooltip();
    });

    table.on('draw.dt', function (e, settings) {
        let api = new DataTable.Api(settings);

        settings.aoColumns.forEach((c, i) => {
            if (c.sType.includes('num')) {
                api
                    .cells(null, i, { page: 'current' })
                    .nodes()
                    .to$()
                    .addClass('text-end');
            }
        });
    });
}


