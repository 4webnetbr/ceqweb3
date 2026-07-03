/**
 * my_relatorio.js
 * Funções JS para o Gerador de Relatórios (CfgRelatorio)
 *
 * Variáveis globais esperadas (setadas pelo PHP):
 *   var charsLinha, urlCharsLinha, urlCamposFil, urlPreview;
 */

jQuery(function () {
  // ── Inicialização ────────────────────────────────────────────────────
  acerta_botoes_rep("filtros");
  acerta_botoes_rep("colunas");

  // Restaura seleção dos selects dependentes nas abas repetíveis
  jQuery('[name^="rfi_campo"], [name^="rco_campo"]').each(function () {
    var val = jQuery(this).data("valor") || jQuery(this).data("selec");
    if (val) {
      jQuery(this).selectpicker("val", val);
    }
  });

  // Carrega preview inicial (edit)
  atualizarPreview();

  // ── Ao mudar tabela base: repopula selects de filtro ─────────────────
  jQuery(document).on("change", '[name="rel_tabela_base"]', function () {
    var tabela = jQuery(this).val();
    if (!tabela) return;
    jQuery.get(
      urlCamposFil,
      { busca: tabela },
      function (res) {
        jQuery('[name^="rfi_campo"]').each(function () {
          var sel = jQuery(this);
          var val = sel.val();
          sel.selectpicker("destroy");
          sel.empty().append('<option value="">Selecione...</option>');
          jQuery.each(res, function (i, op) {
            sel.append(
              '<option value="' + op.id + '">' + op.text + "</option>",
            );
          });
          sel.val(val);
          sel.selectpicker();
        });
      },
      "json",
    );

    atualizarPreview();
  });

  // ── Recalcula chars ao mudar formato ou fonte ────────────────────────
  jQuery(document).on(
    "change",
    '[name="rel_formato"], [name="rel_tamanho_fonte"]',
    function () {
      jQuery.get(
        urlCharsLinha,
        {
          // :checked para radio buttons (cr2opcoes)
          formato: jQuery('[name="rel_formato"]:checked').val() || "P",
          fonte: jQuery('[name="rel_tamanho_fonte"]').val(),
        },
        function (res) {
          if (!res.erro) {
            charsLinha = res.chars_por_linha;
            // Atualiza campo visual de chars/linha
            jQuery('[name="rel_chars_display"]').val(charsLinha);
            // Verifica se as colunas cabem na nova orientação/fonte
            verificaLargura();
          }
        },
        "json",
      );

      atualizarPreview();
    },
  );

  // ── Ao selecionar campo de coluna: preenche ocultos ──────────────────
  jQuery(document).on("change", '[name^="rco_campo"]', function () {
    var partes = jQuery(this).val().split("|");
    var tabela = partes[0] || "";
    var tamanho = parseInt(partes[2] || 0);
    var tipo = partes[3] || "";
    var linha = jQuery(this).closest(".table-colunas");

    linha.find('[name^="rco_tabela"]').val(tabela);
    linha.find('[name^="rco_tamanho"]').val(tamanho);
    linha.find('[name^="rco_tipo_dado"]').val(tipo);
    // Largura padrão baseada no tipo: date=12, int/float/double=10, datetime=20, demais=tamanho original
    var tipoLower = tipo.toLowerCase();
    var larguraAuto = tamanho;
    if (tipoLower === "date") {
      larguraAuto = 12;
    } else if (["int", "float", "double", "decimal"].indexOf(tipoLower) >= 0) {
      larguraAuto = 10;
    } else if (["datetime", "timestamp"].indexOf(tipoLower) >= 0) {
      larguraAuto = 20;
    }
    linha.find('[name^="rco_largura"]').val(larguraAuto);

    var numerico =
      ["int", "float", "double", "decimal"].indexOf(tipo.toLowerCase()) >= 0;
    var btnTotal = linha.find('[name^="rco_totalizar"]');
    if (numerico) {
      btnTotal.prop("disabled", false);
    } else {
      btnTotal.filter('[value="0"]').prop("checked", true);
      btnTotal.prop("disabled", true);
    }

    verificaLargura();
    atualizarPreview();
  });

  // ── Ao selecionar campo de filtro: preenche ocultos ──────────────────
  jQuery(document).on("change", '[name^="rfi_campo"]', function () {
    var partes = jQuery(this).val().split("|");
    var linha = jQuery(this).closest(".table-filtros");
    linha.find('[name^="rfi_tabela"]').val(partes[1] || "");
    linha.find('[name^="rfi_tipo_filtro"]').val(partes[2] || "FK");
    atualizarPreview();
  });

  // ── Ao alterar largura ou comportamento: recalcula limite + preview ───
  jQuery(document).on(
    "change",
    '[name^="rco_largura"], [name^="rco_comportamento"]',
    function () {
      verificaLargura();
      atualizarPreview();
    },
  );

  // ── Eventos que disparam preview ─────────────────────────────────────
  jQuery(document).on("blur", '[name="rel_titulo"]', atualizarPreview);
  jQuery(document).on(
    "change",
    '[name="rel_totalizar_registros"], [name^="rco_label"], ' +
      '[name^="rco_alinhamento"], [name^="rco_totalizar"], [name^="rfi_label"]',
    atualizarPreview,
  );
});

// ── Verifica largura total das colunas (soma rco_largura, ignorando linha inteira) ──
function verificaLargura() {
  if (typeof charsLinha === "undefined" || charsLinha <= 0) return;
  var total = 0;
  jQuery('[name^="rco_largura"]').each(function () {
    var linha = jQuery(this).closest(".table-colunas");
    var comport = linha.find('[name^="rco_comportamento"]').val() || "cortar";
    if (comport !== "linha") {
      total += parseInt(jQuery(this).val() || 0);
    }
  });
  if (total > charsLinha) {
    boxAlert(
      "Atenção: largura total das colunas (" +
        total +
        ") ultrapassa o limite da linha (" +
        charsLinha +
        " caracteres).",
      true,
      "",
      true,
      1,
      false,
      "Largura excedida",
    );
  }
}

// ── Preview com debounce ─────────────────────────────────────────────────
var previewTimer = null;
function atualizarPreview() {
  if (typeof urlPreview === "undefined" || !urlPreview) return;
  clearTimeout(previewTimer);
  previewTimer = setTimeout(function () {
    var formData = jQuery("#form1").serialize();
    jQuery.post(
      urlPreview,
      formData,
      function (res) {
        if (res.html) {
          jQuery("#prevRelatorio").html(res.html);
        }
      },
      "json",
    );
  }, 500);
}
