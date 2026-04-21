var table = null;
var columHide = null;

function montaListaDadosTab(tabela, url, colunas) {
  const container = document.getElementById(tabela);
  if (!container) {
    console.error("Container não encontrado:", tabela);
    return;
  }

  const usuario = jQuery("#usu_id").val() || "anon";
  const persistenceID = "table_" + tabela + "_" + usuario;
  let searchBuilderState = [];

  container.innerHTML = "";

  function debounce(fn, wait) {
    let t = null;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function getTituloExportacao() {
    return document.title + " - " + jQuery("#legenda").text();
  }

  function parseTtp(value) {
    if (typeof value !== "string") {
      return { html: value, title: null };
    }

    const open = value.indexOf("<ttp>");
    const close = value.indexOf("</ttp>");

    if (open > -1 && close > open) {
      return {
        html: value.substring(0, open),
        title: value.substring(open + 5, close),
      };
    }

    return { html: value, title: null };
  }

  function buildTabulatorColumns(colunas) {
    return colunas.map((titulo, index) => {
      const isFirst = index === 0;
      const isLast = index === colunas.length - 1;

      return {
        title: titulo,
        field: "col_" + index,
        visible: !isFirst,
        headerSort: !isLast,
        headerHozAlign: "left",
        hozAlign: "left",
        width: isLast ? 130 : undefined,
        minWidth: isLast ? 120 : 120,
        maxWidth: isLast ? 140 : undefined,
        cssClass: isLast ? "acao text-start text-nowrap" : "",
        formatter: function (cell) {
          const parsed = parseTtp(cell.getValue());
          return parsed.html;
        },
        cellMouseEnter: function (e, cell) {
          const parsed = parseTtp(cell.getValue());
          const el = cell.getElement();

          if (parsed.title) {
            el.setAttribute("title", parsed.title);
            el.setAttribute("data-bs-toggle", "tooltip");
            el.setAttribute("data-bs-placement", "bottom");
            el.setAttribute("data-bs-custom-class", "ttpDataTable");
            bootstrap.Tooltip.getOrCreateInstance(el);
          }
        },
      };
    });
  }

  function mapResponseToObjects(response) {
    if (!response || !response.data || !Array.isArray(response.data)) {
      return [];
    }

    return response.data.map((row) => {
      const obj = {};
      row.forEach((value, i) => {
        obj["col_" + i] = value;
      });
      return obj;
    });
  }

  function getVisibleNonActionColumns() {
    return table.getColumns().filter((col) => {
      const def = col.getDefinition();
      return !String(def.cssClass || "").includes("acao") && col.isVisible();
    });
  }

  function restoreSearchBuilder() {
    const saved = localStorage.getItem(persistenceID + "_searchBuilder");
    if (!saved) return;

    try {
      searchBuilderState = JSON.parse(saved) || [];
    } catch (e) {
      searchBuilderState = [];
    }
  }

  function saveSearchBuilder() {
    localStorage.setItem(
      persistenceID + "_searchBuilder",
      JSON.stringify(searchBuilderState),
    );
  }

  function applySearchBuilder() {
    if (!table) return;

    const filters = [];

    searchBuilderState.forEach((rule) => {
      if (!rule.field || !rule.condition) return;

      if (rule.condition === "contains") {
        filters.push({ field: rule.field, type: "like", value: rule.value });
      } else if (rule.condition === "=") {
        filters.push({ field: rule.field, type: "=", value: rule.value });
      } else if (rule.condition === "!=") {
        filters.push({ field: rule.field, type: "!=", value: rule.value });
      } else if (rule.condition === ">") {
        filters.push({ field: rule.field, type: ">", value: rule.value });
      } else if (rule.condition === "<") {
        filters.push({ field: rule.field, type: "<", value: rule.value });
      } else if (rule.condition === "starts") {
        filters.push({
          field: rule.field,
          type: function (headerValue, rowValue) {
            const row = String(rowValue ?? "").toLowerCase();
            const search = String(rule.value ?? "").toLowerCase();
            return row.startsWith(search);
          },
          value: rule.value,
        });
      } else if (rule.condition === "ends") {
        filters.push({
          field: rule.field,
          type: function (headerValue, rowValue) {
            const row = String(rowValue ?? "").toLowerCase();
            const search = String(rule.value ?? "").toLowerCase();
            return row.endsWith(search);
          },
          value: rule.value,
        });
      }
    });

    table.clearFilter(true);

    if (filters.length) {
      table.setFilter(filters);
    }
  }

  function renderSearchBuilder() {
    const panel = jQuery("#searchBuilderPanel");
    const colunasFiltraveis = table.getColumns().filter((col) => {
      const def = col.getDefinition();
      return !String(def.cssClass || "").includes("acao");
    });

    panel.html(`
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Filtro avançado</strong>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-primary" id="sb-add">Adicionar regra</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="sb-clear">Limpar</button>
          <button type="button" class="btn btn-sm btn-primary" id="sb-apply">Aplicar</button>
        </div>
      </div>
      <div id="sb-rules"></div>
    `);

    function drawRules() {
      if (!searchBuilderState.length) {
        jQuery("#sb-rules").html(
          `<div class="text-muted small">Nenhuma regra adicionada.</div>`,
        );
        return;
      }

      const html = searchBuilderState
        .map(
          (rule, idx) => `
        <div class="row g-2 align-items-center mb-2 sb-rule" data-index="${idx}">
          <div class="col-md-4">
            <select class="form-select form-select-sm sb-field">
              <option value="">Selecione a coluna</option>
              ${colunasFiltraveis
                .map((c) => {
                  const def = c.getDefinition();
                  return `<option value="${def.field}" ${rule.field === def.field ? "selected" : ""}>${def.title}</option>`;
                })
                .join("")}
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm sb-condition">
              <option value="contains" ${rule.condition === "contains" ? "selected" : ""}>Contém</option>
              <option value="=" ${rule.condition === "=" ? "selected" : ""}>Igual</option>
              <option value="!=" ${rule.condition === "!=" ? "selected" : ""}>Diferente</option>
              <option value="starts" ${rule.condition === "starts" ? "selected" : ""}>Começa com</option>
              <option value="ends" ${rule.condition === "ends" ? "selected" : ""}>Termina com</option>
              <option value=">" ${rule.condition === ">" ? "selected" : ""}>Maior que</option>
              <option value="<" ${rule.condition === "<" ? "selected" : ""}>Menor que</option>
            </select>
          </div>
          <div class="col-md-4">
            <input type="text" class="form-control form-control-sm sb-value" value="${rule.value || ""}" placeholder="Valor">
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger sb-remove w-100">&times;</button>
          </div>
        </div>
      `,
        )
        .join("");

      jQuery("#sb-rules").html(html);
    }

    drawRules();

    panel.off("click", "#sb-add").on("click", "#sb-add", function () {
      searchBuilderState.push({
        field: "",
        condition: "contains",
        value: "",
      });
      drawRules();
    });

    panel.off("click", "#sb-clear").on("click", "#sb-clear", function () {
      searchBuilderState = [];
      saveSearchBuilder();
      drawRules();
      table.clearFilter(true);
    });

    panel.off("click", "#sb-apply").on("click", "#sb-apply", function () {
      jQuery(".sb-rule").each(function () {
        const idx = Number(jQuery(this).data("index"));
        searchBuilderState[idx] = {
          field: jQuery(this).find(".sb-field").val(),
          condition: jQuery(this).find(".sb-condition").val(),
          value: jQuery(this).find(".sb-value").val(),
        };
      });

      saveSearchBuilder();
      applySearchBuilder();
    });

    panel.off("click", ".sb-remove").on("click", ".sb-remove", function () {
      const idx = Number(jQuery(this).closest(".sb-rule").data("index"));
      searchBuilderState.splice(idx, 1);
      drawRules();
    });
  }

  function renderColumnMenu() {
    const menu = jQuery("#menuColunas");
    menu.empty();

    table.getColumns().forEach((col) => {
      const def = col.getDefinition();
      if (String(def.cssClass || "").includes("acao")) return;

      menu.append(`
        <label class="dropdown-item d-flex align-items-center gap-2">
          <input type="checkbox" class="form-check-input mt-0 col-toggle" data-field="${def.field}" ${col.isVisible() ? "checked" : ""}>
          <span>${def.title}</span>
        </label>
      `);
    });
  }

  restoreSearchBuilder();

  table = new Tabulator("#" + tabela, {
    ajaxURL: url,
    ajaxConfig: "POST",
    ajaxContentType: "form",

    ajaxParams: function () {
      return {
        usuario: jQuery("#usu_id").val(),
      };
    },

    ajaxResponse: function (url, params, response) {
      return mapResponseToObjects(response);
    },

    layout: "fitColumns",
    height: "calc(100vh - 15rem)",
    placeholder: "Nenhum registro encontrado",

    columns: buildTabulatorColumns(colunas),

    pagination: "local",
    paginationSize: Number(jQuery("#page-size").val() || 50),
    paginationSizeSelector: [10, 25, 50, 100],

    persistence: {
      sort: true,
      filter: true,
      columns: true,
      page: true,
    },
    persistenceID: persistenceID,

    headerSortTristate: true,

    renderComplete: function () {
      jQuery('[data-bs-toggle="tooltip"]').each(function () {
        bootstrap.Tooltip.getOrCreateInstance(this);
      });

      renderColumnMenu();
    },

    dataLoaded: function () {
      applySearchBuilder();
    },
  });

  renderSearchBuilder();

  jQuery("#" + tabela)
    .off("click.rowaction")
    .on(
      "click.rowaction",
      ".tabulator-row .tabulator-cell:not(.acao)",
      function () {
        const btn = jQuery(this)
          .closest(".tabulator-row")
          .find(".btn")
          .first()[0];
        if (btn && typeof btn.onclick === "function") {
          btn.onclick();
        }
      },
    );

  jQuery("#page-size")
    .off("change")
    .on("change", function () {
      table.setPageSize(Number(this.value));
    });

  jQuery("#table-search")
    .off("input")
    .on(
      "input",
      debounce(function () {
        const value = this.value;

        if (!value) {
          table.clearFilter(true);
          applySearchBuilder();
          return;
        }

        const filtros = getVisibleNonActionColumns().map((col) => {
          return {
            field: col.getField(),
            type: "like",
            value: value,
          };
        });

        table.setFilter([filtros]);
      }, 250),
    );

  jQuery("#btnFiltro")
    .off("click")
    .on("click", function () {
      jQuery("#searchBuilderPanel").toggleClass("d-none");
    });

  jQuery("#btnRefresh")
    .off("click")
    .on("click", function () {
      localStorage.removeItem(persistenceID);
      localStorage.removeItem(persistenceID + "_searchBuilder");
      window.location.reload();
    });

  jQuery("#btnExcel")
    .off("click")
    .on("click", function () {
      table.download("xlsx", getTituloExportacao() + ".xlsx", {
        sheetName: "Dados",
      });
    });

  jQuery("#btnPdf")
    .off("click")
    .on("click", function () {
      table.download("pdf", getTituloExportacao() + ".pdf", {
        orientation: "landscape",
        title: getTituloExportacao(),
      });
    });

  jQuery("#btnPrint")
    .off("click")
    .on("click", function () {
      table.print(false, true);
    });

  jQuery("#btnColunas")
    .off("click")
    .on("click", function () {
      const menu = jQuery("#menuColunas");
      const pos = jQuery(this).offset();

      menu.css({
        display: "block",
        position: "absolute",
        top: pos.top + jQuery(this).outerHeight() + 4,
        left: pos.left,
        zIndex: 1055,
      });
    });

  jQuery(document)
    .off("click.menuColunas")
    .on("click.menuColunas", function (e) {
      if (!jQuery(e.target).closest("#btnColunas, #menuColunas").length) {
        jQuery("#menuColunas").hide();
      }
    });

  jQuery("#menuColunas")
    .off("change", ".col-toggle")
    .on("change", ".col-toggle", function () {
      const field = jQuery(this).data("field");
      const col = table.getColumn(field);

      if (!col) return;

      if (this.checked) {
        col.show();
      } else {
        col.hide();
      }
    });
}
