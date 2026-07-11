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

  // *claude* monta as opções do select "depende de" (aba Filtros) a partir dos rfi_campo
  // já escolhidos nas OUTRAS linhas — na primeira linha (nenhum outro filtro configurado)
  // não sobra nenhuma opção além de "(Nenhum)".
  atualizaDependeDe();

  // *claude* observa inclusão/remoção de linhas na grade de Filtros (botões + / lixeira)
  // pra manter as opções de "depende de" sempre em dia, sem precisar mexer em addCampo()/exclui_campo()
  var repFiltros = document.getElementById("rep_filtros");
  if (repFiltros && typeof MutationObserver !== "undefined") {
    new MutationObserver(atualizaDependeDe).observe(repFiltros, {
      childList: true,
    });
  }

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
        // *claude* tabela base mudou -> campos de filtro escolhidos ficaram inválidos,
        // então as opções de "depende de" precisam ser recalculadas também
        atualizaDependeDe();
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
    // *claude* o campo escolhido aqui pode virar candidato a "pai" de outra linha (ou deixar
    // de sê-lo, se foi trocado) — recalcula as opções de "depende de" em todas as linhas
    atualizaDependeDe();
    atualizarPreview();
  });

  // *claude* o texto do label também é usado como rótulo das opções em "depende de"
  jQuery(document).on("change", '[name^="rfi_label"]', atualizaDependeDe);

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

// *claude* ── "Depende de" (cascata de filtros) ──────────────────────────
// Recalcula, em TODAS as linhas da grade de Filtros, as opções do select
// "depende de": só os rfi_campo dos filtros que aparecem ANTES da linha atual na
// tela (a ordem de exibição É a ordem de montagem — um filtro nunca pode depender
// de outro que só vai aparecer depois dele). A 1ª linha nunca tem candidato.
// *claude* esconde/mostra um wrapper de campo vencendo o "display:inline-flex !important"
// que fmtDisplay() (MyCampo.php) aplica via classe Bootstrap "d-inline-flex" — jQuery
// .hide()/.show()/.css() só setam display sem !important, então uma classe !important
// sempre ganha deles. Setar o !important direto no style inline é o único jeito de vencer.
function esconderCampo($el) {
  $el.each(function () {
    this.style.setProperty("display", "none", "important");
  });
}
function mostrarCampo($el) {
  $el.each(function () {
    this.style.removeProperty("display");
  });
}

function atualizaDependeDe() {
  // *claude* candidatos acumulados conforme percorre as linhas EM ORDEM — ao processar
  // uma linha, só entram na lista os campos das linhas já processadas (ou seja, as
  // anteriores). Só um .each(): a linha só entra na lista de candidatos DEPOIS de
  // montado o próprio select, então nunca aparece nas próprias opções.
  var candidatosAteAqui = [];

  jQuery(".table-filtros").each(function () {
    var $linha = jQuery(this);
    var campoVal = $linha.find('[name^="rfi_campo"]').val() || "";
    var campo = campoVal.split("|")[0];
    var tipo = $linha.find('[name^="rfi_tipo_filtro"]').val() || "FK";
    var label = $linha.find('[name^="rfi_label"]').val();

    var selPai = $linha.find('[name^="rfi_campo_pai"]');
    if (selPai.length > 0) {
      var wrapperPai = selPai.closest(".row");

      // *claude* esconde "depende de" quando: (a) o filtro é do tipo DATE (usa daterange,
      // não select) ou (b) não existe nenhum candidato válido ainda (ex.: 1ª linha, ou só
      // filtros DATE antes dela) — sem opção nenhuma pra oferecer, não faz sentido mostrar
      // o campo. Limpa qualquer valor que tenha ficado de uma configuração anterior.
      if (tipo === "DATE" || candidatosAteAqui.length === 0) {
        esconderCampo(wrapperPai);
        if (selPai.val()) {
          selPai.selectpicker("val", "");
        }
      } else {
        mostrarCampo(wrapperPai);

        var atual = selPai.val();
        selPai.selectpicker("destroy");
        selPai.empty().append('<option value="">(Nenhum)</option>');
        candidatosAteAqui.forEach(function (c) {
          selPai.append(
            '<option value="' + c.campo + '">' + c.label + "</option>",
          );
        });

        var validas = selPai
          .find("option")
          .map(function () {
            return jQuery(this).val();
          })
          .get();
        selPai.val(validas.indexOf(atual) >= 0 ? atual : "");
        selPai.selectpicker();
      }
    }

    // *claude* só entra pra lista de candidatos AGORA — depois de montado o select desta
    // linha — assim ela mesma nunca aparece nas próprias opções, e as linhas seguintes só
    // enxergam quem veio antes.
    if (campo && tipo !== "DATE") {
      candidatosAteAqui.push({ campo: campo, label: label || campo });
    }
  });
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
