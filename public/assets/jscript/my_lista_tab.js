var table;
var columHide = null;

function montaListaDadosTab(tabela, url) {
  table = new Tabulator("#" + tabela, {
    ajaxURL: url,
    ajaxConfig: "POST",
    ajaxContentType: "json",

    pagination: "remote",
    paginationSize: 50,

    layout: "fitColumns",
    height: "calc(100vh - 15rem)",

    progressiveRender: true,

    persistence: {
      sort: true,
      filter: true,
      columns: true,
    },

    persistenceID: "table_" + tabela + "_" + jQuery("#usu_id").val(),

    columns: [
      { title: "ID", field: "id", visible: false },

      {
        title: "Ações",
        field: "acao",
        hozAlign: "left",
        headerSort: false,
        cssClass: "acao text-start text-nowrap",
        width: 130,
        minWidth: 130,
      },
    ],

    locale: "pt-br",

    rowFormatter: function (row) {
      let rowEl = row.getElement();

      jQuery(rowEl)
        .find("td")
        .each(function () {
          let html = jQuery(this).html();

          if (html && html.indexOf("<ttp>") > -1) {
            let title = html.substring(
              html.indexOf("<ttp>") + 5,
              html.length - 6,
            );

            let value = html.substring(0, html.indexOf("<ttp>"));

            jQuery(this)
              .html(value)
              .attr("title", title)
              .attr("data-bs-toggle", "tooltip")
              .attr("data-bs-placement", "bottom")
              .attr("data-bs-custom-class", "ttpDataTable");
          }
        });
    },

    renderComplete: function () {
      jQuery('[data-bs-toggle="tooltip"]').tooltip();

      this.getColumns().forEach((col) => {
        if (col.getDefinition().sorter === "number") {
          col.getCells().forEach((cell) => {
            jQuery(cell.getElement()).addClass("text-end");
          });
        }
      });
    },
  });

  // clique na linha
  jQuery("#" + tabela).on(
    "click",
    ".tabulator-row .tabulator-cell:not(.acao)",
    function () {
      let row = jQuery(this).closest(".tabulator-row");
      let btn = row.find(".btn").first()[0];

      if (btn && typeof btn.onclick === "function") {
        btn.onclick();
      }
    },
  );

  // botões

  jQuery("#btnRefresh").on("click", function () {
    table.clearData();
    table.setData();
  });

  jQuery("#btnExcel").on("click", function () {
    table.download(
      "xlsx",
      document.title + " - " + jQuery("#legenda").text() + ".xlsx",
    );
  });

  jQuery("#btnPDF").on("click", function () {
    table.download(
      "pdf",
      document.title + " - " + jQuery("#legenda").text() + ".pdf",
    );
  });

  jQuery("#btnPrint").on("click", function () {
    table.print(false, true);
  });

  jQuery("#btnColunas").on("click", function () {
    table.getColumns().forEach((col) => {
      col.toggle();
    });
  });
}
