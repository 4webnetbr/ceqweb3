/**
 * buscaSaldo
 * Processa a busca de Saldos de Estoque
 */
async function buscaSaldo() {
    // bloqueiaTela();
    var codDep = jQuery.trim(jQuery('#codDep').val());
    var codPro = jQuery.trim(jQuery('#codPro').val());
    urlBusca = 'SaldoEstoque/lista';
    dados = { 'codDep': codDep, 'codPro': codPro };
    try {
        const retornoAjax = await executaAjaxWait(urlBusca, 'json', dados);
        montaListaSaldo(retornoAjax);
    } catch (error) {
        console.log('Erro na requisição AJAX:', error);
    }
    // retornoAjax = executaAjaxWait(urlBusca, 'json', dados, 'montaListaSaldo(retornoAjax)');
    // // desBloqueiaTela();
    // if (retornoAjax) {
    // }
    // desBloqueiaTela();
};;

function montaListaSaldo(dados) {
    jQuery('#table').DataTable().destroy();
    removeLinhas("table", 0);

    linha = '';
    for (var index in dados) {
        item = dados[index];
        linha = linha + "<tr>";
        linha = linha + "<td class='align-middle'>" + item.codDep + "</td>";
        linha = linha + "<td class='align-middle'>" + item.Coderp + "</td>";
        linha = linha + "<td class='align-middle'>" + item.DescProduto + "</td>";
        linha = linha + "<td class='align-middle'>" + item.lote + "</td>";
        linha = linha + "<td class='align-middle' data-sort='" + item.validadeord + "'>" + item.validade + "</td>";
        linha = linha + "<td class='align-middle text-end pe-4'>" + item.saldo + "</td>";
        linha = linha + "<td class='align-middle text-start'>" + item.und + "</td>";
        linha = linha + "<td class='align-middle' data-sort='" + item.entradaord + "'>" + item.entrada + "</td>";
        linha = linha + "</tr>";
    }
    jQuery("#table tbody").append(linha);
    dtResult('table');
}

function dtResult(tabela) {
    // monta o Datable 
    var table = jQuery('#' + tabela).DataTable(
        {
            "responsive": false,
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "columnDefs": [
                // { "max-width": "8em", "targets": ['all'] },
                { "min-width": "8em", "targets": ['all'] },
                { "width": "8em", "targets": ['all'] },
                { "className": "text-wrap text-nowrap", "targets": ['all'] },
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
                        text: '<i class="fa fa-print" aria-hidden="true"></i>',
                        titleAttr: 'Enviar para Impressora',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                ]
            },
            // header options
            "orderCellsTop": true,
            "fixedHeader": true,
            "bSortCellsTop": true,
            "pageLength": 50,
            "bPaginate": true,
            "scrollY": '58vh !important',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'ftrBip',
            "language": {
                "url": 'assets/jscript/datatables-lang/pt-BR.json',
            },
            "fnDrawCallback": function (oSettings) {
                jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                    jQuery(this).children().find('.btn').addClass('hover');
                }).on('mouseleave', function () {
                    jQuery(this).children().find('.btn').removeClass('hover');
                });
            },
        });
}


function removeLinhas(idTabela, indice = 1) {
    if (indice != 1) {
        jQuery("#" + idTabela + " tbody").children().remove()
    } else {
        jQuery("#" + idTabela).find("tr:gt(" + indice + ")").remove();
    }
}

