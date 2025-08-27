function buscaTipoMovimentacao(orig, depori, depdes) {
  url = window.location.origin + "/buscas/buscaTipoMovimentacao";
  dados = { busca: orig.value };
  retornoAjax = false;
  executaAjax(url, "json", dados);
  if (retornoAjax) {
    if (retornoAjax.id != -1) {
      jQuery("#" + depori)
        .closest(".dropdown")
        .removeClass("disabled");
      jQuery("#" + depori)
        .next()
        .removeClass("disabled");
      jQuery("#" + depdes)
        .closest(".dropdown")
        .removeClass("disabled");
      jQuery("#" + depdes)
        .next()
        .removeClass("disabled");
      jQuery("#" + depori).selectpicker("val", retornoAjax.depori);
      jQuery("#" + depdes).selectpicker("val", retornoAjax.depdes);
      if (retornoAjax.depori != null) {
        jQuery("#" + depori)
          .closest(".dropdown")
          .addClass("disabled");
        jQuery("#" + depori)
          .next()
          .addClass("disabled");
      }
      if (retornoAjax.depdes != null) {
        jQuery("#" + depdes)
          .closest(".dropdown")
          .addClass("disabled");
        jQuery("#" + depdes)
          .next()
          .addClass("disabled");
      }
    }
  }
}

function gerarCampoNumeroPadrao(
  id,
  classeExtra,
  valor = 0,
  min = 0,
  step = 1,
  index = ""
) {
  return `
        <div class="input-group input-group-sm d-inline-flex align-items-center" style="max-width: 20ch;min-width: 15ch;font-size:10px">
            <div class="input-group-text input-group-addon down-num pe-auto" data-refer="${id}">
                <i class="fas fa-minus"></i>
            </div>
            <input 
                type="number" 
                id="${id}" 
                class="form-control form-number ${classeExtra} text-end" 
                data-index="${index}" 
                min="${min}" 
                step="${step}" 
                value="${valor}" 
                dir="rtl" 
                autocomplete="off">
            <div class="input-group-text input-group-append up-num pe-auto" data-refer="${id}">
                <i class="fas fa-plus"></i>
            </div>
        </div>
    `;
}

let codigosRenderizados = new Set(); // Mantido fora da função, global no escopo da geração

function criarLinhaProduto(
  prod,
  index,
  dadosDep,
  codigosRepetidos,
  codigosRenderizados
) {
  const estoquePad = prod.lotepad?.pro_estpadrao ?? 0;
  const estoqueOri = prod.loteori?.pro_estorigem ?? 0;
  const estoqueDisp = estoquePad + estoqueOri;
  const estoqueDes = prod.lotedes?.pro_estdestino ?? 0;
  const padraoCol =
    dadosDep.deppadrao !== ""
      ? `<td class="text-end">${prod.lotepad?.pro_estpadrao ?? 0}</td>`
      : "";

  const codpro = prod.pro_codpro;
  const isDuplicado = codigosRenderizados.has(codpro);
  const toggleId = `toggle_${codpro.replace(/[^a-zA-Z0-9]/g, "")}`;
  const temDuplicatas = codigosRepetidos[codpro] > 1;

  if (!isDuplicado) codigosRenderizados.add(codpro);

  const iconeToggle =
    temDuplicatas && !isDuplicado
      ? `<i class="btn far fa-arrow-alt-circle-right text-primary toggle-linhas p-0" 
               id="${toggleId}" 
               data-codpro="${codpro}" 
               title="Mostrar mais"></i> `
      : "";
  const iconeDuplic =
    temDuplicatas && isDuplicado
      ? `<i class="fa-solid fa-arrow-turn-up text-secondary" 
    id="${toggleId}" style="padding-left: 12px;transform: rotate(90deg);" ></i> `
      : "";

  return `
        <tr class="centralizado linha-produto ${isDuplicado ? "d-none" : ""}" 
            data-classe="${dadosDep.classe}" 
            data-index="${index}" 
            data-codpro="${codpro}"
            data-consumo="${prod.pro_consumo}" 
            data-min="${prod.pro_minimo}" 
            data-max="${prod.pro_maximo}"
            data-saldo-destino="${estoqueDes}"
            data-saldo-disponivel="${estoqueDisp}"
            data-sugestao-base="${prod.pro_sugestao}">

            <td class="text-end"><span class="float-start">${iconeToggle}${iconeDuplic}</span>${codpro}</td>
            <td title="${
              prod.pro_inform ?? ""
            }" data-bs-toggle="tooltip" style="font-size: 10px;">${
    prod.pro_despro
  }</td>
            <td style="font-size: 10px;">${prod.fab_apeFab}</td>
            <td>${prod.lot_lote}</td>
            <td>${prod.lot_validade}</td>
            <td class="text-end">${prod.pro_qtdemb}</td>
            <td class="text-end">${estoqueOri}</td>
            ${padraoCol}
            <td class="text-end">${estoqueDes}</td>
            <td class="text-end">${
              prod.pro_mindiaanterior === "N"
                ? '<span class="float-start">S</span>'
                : ""
            }${prod.pro_consumo}</td>
            <td class="text-end">${gerarCampoNumeroPadrao(
              `pro_multiplica_${index}`,
              "multiplica",
              prod.pro_multiplica,
              1,
              1,
              index
            )}</td>
            <td class="text-end">
                ${gerarCampoNumeroPadrao(
                  `pro_pctseguranca_${index}`,
                  "seguranca",
                  prod.pro_pctseguranca,
                  0,
                  1,
                  index
                )}
                <span class="text-end d-none" id="seg_${index}">${
    prod.pro_seguranca
  }</span>
            </td>
            <td class="text-end sugestao" id="sug_${index}">${
    prod.pro_sugestao
  }</td>
            <td class="text-end">${gerarCampoNumeroPadrao(
              `requisicao_${index}`,
              "requisicao",
              prod.pro_requisicao,
              0,
              1,
              index
            )}</td>
        </tr>
    `;
}

function montarTabelaProdutos(classe, rt, dadosDep) {
  const text = [];
  const isFirst = rt === 0;

  // Contador de códigos duplicados
  const codigosRepetidos = {};
  classe.prod.forEach((prod) => {
    codigosRepetidos[prod.pro_codpro] =
      (codigosRepetidos[prod.pro_codpro] || 0) + 1;
  });

  // Set para controlar se já renderizou o código
  const codigosRenderizados = new Set();

  text.push(`<div class="accordion-item" data-cla_id="${classe.id}">`);
  text.push(`<h2 class="accordion-header">`);
  text.push(
    `<button class="accordion-button bg-gray-padrao collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsecl${rt}" aria-expanded="${isFirst}" aria-controls="collapsecl${rt}">${classe.nome}</button>`
  );
  text.push(`</h2>`);
  text.push(
    `<div id="collapsecl${rt}" class="accordion-collapse collapse" data-bs-parent="#accClas">`
  );
  text.push(`<div class="accordion-body p-1 bg-body-tertiary">`);

  text.push(`<div class="d-flex justify-content-end mb-2">`);
  text.push(`<div class="form-check">`);
  text.push(
    `<input class="form-check-input aceita-sugestao" type="checkbox" data-classe="${rt}" id="checkSug${rt}">`
  );
  text.push(
    `<label class="form-check-label" for="checkSug${rt}">Aceitar sugestões</label>`
  );
  text.push(`</div></div>`);

  text.push(
    `<table class="table table-bordered table-striped w-100" style="font-size: 10px;"><thead><tr class="text-center">`
  );
  text.push(`<th>Cód ERP</th>`);
  text.push(`<th>Descrição</th>`);
  text.push(`<th>Fabricante</th>`);
  text.push(`<th>Lote</th>`);
  text.push(`<th>Validade</th>`);
  text.push(`<th class="vertical-th">Qtd Caixa</th>`);
  text.push(
    `<th class="vertical-th">Saldo Origem<br>${dadosDep.deporigem}</th>`
  );
  if (dadosDep.deppadrao !== "") {
    text.push(
      `<th class="vertical-th">Saldo Padrão<br>${dadosDep.deppadrao}</th>`
    );
  }
  text.push(
    `<th class="vertical-th">Saldo Destino<br>${dadosDep.depdestino}</th>`
  );
  text.push(
    `<th class="vertical-th">${
      dadosDep.diaanterior === "S"
        ? "Consumo<br>" + dadosDep.dataOntem
        : "Média<br>" + dadosDep.meddias + " dias"
    }</th>`
  );
  text.push(`<th>Multiplica</th>`);
  text.push(`<th class="vertical-th">% Segurança</th>`);
  text.push(`<th class="vertical-th">Sugestão</th>`);
  text.push(`<th>Requisição</th>`);
  text.push(`</tr></thead><tbody>`);

  classe.prod.forEach((prod, el) => {
    const index = `cl${rt}_pr${el}`;
    dadosDep.classe = rt;
    text.push(
      criarLinhaProduto(
        prod,
        index,
        dadosDep,
        codigosRepetidos,
        codigosRenderizados
      )
    );
  });

  text.push(`</tbody></table></div></div></div>`);
  return text.join("");
}

function calcularSugestao(
  base,
  multiplicador,
  seguranca,
  max,
  saldoDestino,
  saldoDisponivel
) {
  let sug = base * multiplicador;
  const vsegura = Math.ceil(sug * (seguranca / 100));
  let maxMult = max * multiplicador;
  sug = sug + vsegura - saldoDestino;
  if (maxMult > 0) {
    const restanteMax = maxMult - saldoDestino;
    if (restanteMax <= 0) return 0;
    sug = Math.min(sug, restanteMax);
  }
  sug = Math.min(sug, saldoDisponivel);
  return Math.max(0, sug);
}

function preencherRequisicaoAutomatica(index, valor, classe) {
  const checkbox = jQuery(`#checkSug${classe}`);
  if (checkbox.is(":checked")) {
    jQuery(`.requisicao[data-index="${index}"]`)
      .data("ignore-validation", true)
      .val(valor)
      .trigger("change");
  }
}

function atualizarSugestao(index, novaSug) {
  jQuery(`#sug_${index}`).text(parseInt(novaSug));
}

function atualizarSeguranca(index, consumo, segurancaPct) {
  const novoSeg = Math.ceil(consumo * (segurancaPct / 100));
  jQuery(`#seg_${index}`).text(novoSeg);
  return novoSeg;
}

function handleMultiplicaChange() {
  const input = jQuery(this);
  const index = input.data("index");
  const tr = jQuery(`tr[data-index="${index}"]`);

  const saldoDisponivel = parseInt(tr.data("saldo-disponivel")) || 0;
  let max = parseInt(tr.data("max")) || 0;
  max = max === 0 ? saldoDisponivel : max;

  const codproAtual = tr.data("codpro");
  let saldoDestino = 0;
  jQuery("tr").each(function () {
    if (jQuery(this).data("codpro") == codproAtual) {
      saldoDestino += parseInt(jQuery(this).data("saldo-destino")) || 0;
    }
  });

  const multiplicador = parseInt(input.val()) || 1;
  const consumo = parseInt(tr.data("consumo")) || 0;
  const seguranca = parseInt(jQuery(`#pro_pctseguranca_${index}`).val()) || 0;

  const novaSug = calcularSugestao(
    consumo,
    multiplicador,
    seguranca,
    max,
    saldoDestino,
    saldoDisponivel
  );
  atualizarSugestao(index, novaSug);
  jQuery(".requisicao").trigger("change");
  preencherRequisicaoAutomatica(index, novaSug, tr.data("classe"));
}

function handleSegurancaChange() {
  const input = jQuery(this);
  const index = input.data("index");
  const tr = jQuery(`tr[data-index="${index}"]`);

  const consumo = parseInt(tr.data("consumo")) || 0;
  const segurancaPct = parseInt(input.val()) || 0;
  const multiplicador = parseInt(jQuery(`#pro_multiplica${index}`).val()) || 1;

  atualizarSeguranca(index, consumo, segurancaPct);

  const codproAtual = tr.data("codpro");
  let saldoDestino = 0;
  jQuery("tr").each(function () {
    if (jQuery(this).data("codpro") == codproAtual) {
      saldoDestino += parseInt(jQuery(this).data("saldo-destino")) || 0;
    }
  });

  const saldoDisponivel = parseInt(tr.data("saldo-disponivel")) || 0;
  let max = parseInt(tr.data("max")) || 0;
  max = max === 0 ? saldoDisponivel : max;

  const novaSug = calcularSugestao(
    consumo,
    multiplicador,
    segurancaPct,
    max,
    saldoDestino,
    saldoDisponivel
  );
  atualizarSugestao(index, novaSug);
  preencherRequisicaoAutomatica(index, novaSug, tr.data("classe"));
}

function handleAceitaSugestaoChange() {
  const classeId = jQuery(this).data("classe");
  const checked = jQuery(this).is(":checked");
  jQuery(`tr[data-classe="${classeId}"]`).each(function () {
    const index = jQuery(this).data("index");
    const valorSugestao =
      parseInt(jQuery(this).find(`#sug_${index}`).text()) || 0;
    jQuery(`.requisicao[data-index="${index}"]`)
      .data("ignore-validation", true)
      .val(checked ? valorSugestao : 0)
      .trigger("change");
  });
}

function handleRequisicaoChange() {
  const input = jQuery(this);
  if (input.data("ignore-validation")) {
    input.removeData("ignore-validation");
    return;
  }

  const index = input.data("index");
  const valAtual = Math.round(parseInt(input.val()) || 0);
  if (valAtual !== 0) {
    const tr = jQuery(`tr[data-index="${index}"]`)[0];
    const codigo = tr.getAttribute("data-codpro");
    const minOriginal = parseInt(tr.getAttribute("data-min")) || 0;
    const saldoDisponivelAtual =
      parseInt(tr.getAttribute("data-saldo-disponivel")) || 0;
    let maxOriginal = parseInt(tr.getAttribute("data-max")) || 0;
    const maxAntesOri = maxOriginal;
    maxOriginal = maxOriginal === 0 ? saldoDisponivelAtual : maxOriginal;

    let saldoDestinoAtual = 0;
    jQuery("tr").each(function () {
      if (this.getAttribute("data-codpro") == codigo) {
        saldoDestinoAtual +=
          parseInt(this.getAttribute("data-saldo-destino")) || 0;
      }
    });

    let motivo = 0;
    let novoValor = valAtual;

    if (saldoDestinoAtual > maxOriginal) {
      novoValor = 0;
      input.val(0);
      motivo = 12;
    } else {
      const max =
        maxAntesOri === 0
          ? maxOriginal
          : Math.max(0, maxOriginal - saldoDestinoAtual);
      const min = Math.min(minOriginal, minOriginal - saldoDestinoAtual);

      let somaOutros = 0;
      jQuery(".requisicao")
        .filter(function () {
          return jQuery(this).closest("tr").data("codpro") === codigo;
        })
        .each(function () {
          if (this !== input[0]) {
            somaOutros += parseInt(jQuery(this).val()) || 0;
          }
        });

      const restantePermitido = max - somaOutros;
      novoValor = Math.min(valAtual, restantePermitido);
      novoValor = Math.min(novoValor, saldoDisponivelAtual);
      novoValor = Math.max(min, novoValor);

      if (novoValor !== valAtual) {
        input.val(novoValor);
        if (valAtual > restantePermitido) motivo = 12;
        if (valAtual < min) motivo = 13;
        if (valAtual > saldoDisponivelAtual) motivo = 30;
      } else {
        input.val(novoValor);
      }
    }

    if (motivo > 0) {
      mostranoToast(motivo, true);
    }
  } else {
    input.val(0);
  }
}

function handleToggleLinhas() {
  const codpro = jQuery(this).data("codpro");
  const linhas = jQuery(`tr[data-codpro="${codpro}"]`).not(":first");
  const icone = jQuery(this);
  const isAberto = icone.hasClass("fa-arrow-alt-circle-down");

  linhas.toggleClass("d-none");
  icone
    .toggleClass("fa-arrow-alt-circle-down fa-arrow-alt-circle-right")
    .attr("title", isAberto ? "Mostrar mais" : "Ocultar");
}

function bindEvents() {
  jQuery(document)
    .off("change", ".multiplica")
    .on("change", ".multiplica", handleMultiplicaChange);
  jQuery(document)
    .off("change", ".seguranca")
    .on("change", ".seguranca", handleSegurancaChange);
  jQuery(document)
    .off("change", ".aceita-sugestao")
    .on("change", ".aceita-sugestao", handleAceitaSugestaoChange);
  jQuery(document)
    .off("change", ".requisicao")
    .on("change", ".requisicao", handleRequisicaoChange);
  jQuery(document)
    .off("click", ".toggle-linhas")
    .on("click", ".toggle-linhas", handleToggleLinhas);
  jQuery(document)
    .off("input change", ".requisicao")
    .on("input change", ".requisicao", atualizarEstadoBotaoSalvar);
}

async function carregarProdutos(url, aba, obj, event) {
  event.preventDefault();
  event.stopPropagation();

  const reqid = jQuery("#req_id").val();
  const deporigem = jQuery("#req_deporigem").val();
  const depdestino = jQuery("#req_depdestino").val();
  const tipomovim = jQuery("#tmo_id").val();
  const multiplica = jQuery("#req_repetedias").val();
  const diaanterior = jQuery('input[name="req_consdiaanterior"]:checked').val();
  const mediaconsumo = jQuery('input[name="req_medconsumodias"]:checked').val();
  const segurancaPct = jQuery("#req_percseguranca").val();
  const meddias = jQuery("#req_meddias").val();
  const proid = jQuery("#pro_id").val();

  if (!deporigem || !depdestino) {
    boxAlert(
      "Informe o Depósito de Origem e o Depósito de Destino",
      true,
      "",
      true,
      3,
      false,
      "Atenção"
    );
    return;
  }

  const dados = {
    reqid,
    deporigem,
    depdestino,
    tipomovim,
    multiplica,
    diaanterior,
    mediaconsumo,
    seguranca: segurancaPct,
    meddias,
    proid,
    obj,
  };

  try {
    executaAjax(url, "json", dados);
    if (!retornoAjax) return;

    const hoje = new Date();
    hoje.setDate(hoje.getDate() - 1);

    const dadosDep = {
      deporigem: retornoAjax.deporigem,
      depdestino: retornoAjax.depdestino,
      deppadrao: retornoAjax.deppadrao,
      diaanterior: retornoAjax.diaanterior,
      meddias: retornoAjax.meddias,
      dataOntem: hoje.toLocaleDateString("pt-BR"),
    };

    const text = ['<div class="accordion" id="accClas">'];
    retornoAjax.classe.forEach((classe, rt) => {
      text.push(montarTabelaProdutos(classe, rt, dadosDep));
    });
    text.push(`</div>`);

    jQuery("#" + aba).html(text.join(""));
    jQuery("#produtos-tabr").trigger("click");
    jQuery("#produtos-tab").trigger("click");
    atualizarEstadoBotaoSalvar();

    bindEvents();
  } catch (error) {
    console.error("Erro na requisição AJAX:", error);
  }
}

function atualizarEstadoBotaoSalvar() {
  let habilitar = false;

  jQuery(".requisicao").each(function () {
    const val = parseInt(jQuery(this).val()) || 0;
    if (val !== 0) {
      habilitar = true;
      return false; // já achamos uma, pode parar
    }
  });

  jQuery("#bt_salvar").prop("disabled", !habilitar);
  jQuery("#bt_envia").prop("disabled", !habilitar);
}

function normalizarNomeColuna(texto) {
  return texto
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^\w\s]/gi, "")
    .trim()
    .toLowerCase()
    .replace(/\s+/g, "_");
}

function enviarRequisicoes(tipo = 0) {
  const requisicoes = [];
  const form = jQuery("#form1");
  if (tipo == 1) {
    form.find('input[name="req_status"]').remove();
    form.append('<input type="hidden" name="req_status" value="' + tipo + '">');
  }

  jQuery("tr[data-index]").each(function () {
    const tr = jQuery(this);
    const index = tr.data("index");
    const inputRequisicao = jQuery(`.requisicao[data-index="${index}"]`);
    const valorRequisicao = parseInt(inputRequisicao.val()) || 0;

    if (valorRequisicao !== 0) {
      const dados = {};

      // Captura colunas visíveis relevantes (ignora colunas com input)
      tr.find("td").each(function (i) {
        const th = tr.closest("table").find("thead th").eq(i);
        const nomeColuna = normalizarNomeColuna(th.text());

        if (["multiplica", "seguranca", "requisicao"].includes(nomeColuna))
          return;

        const texto = jQuery(this)
          .clone()
          .children()
          .remove()
          .end()
          .text()
          .trim();
        if (nomeColuna) {
          dados[nomeColuna] = texto;
        }
      });

      // Campos de input da linha
      dados.multiplica = jQuery(`#pro_multiplica_${index}`).val();
      dados.seguranca = jQuery(`#pro_pctseguranca_${index}`).val();
      dados.requisicao = valorRequisicao;

      // Classe e cla_id do accordion
      const acc = tr.closest(".accordion-item");
      if (acc.length) {
        dados.cla_id = acc.data("cla_id") || acc.data("claid") || null;
        dados.classe = acc
          .find(".accordion-header, .accordion-button")
          .first()
          .text()
          .trim();
      }

      requisicoes.push(dados);
    }
  });

  // Injeta o JSON no form e envia
  form.find('input[name="json_requisicoes"]').remove();
  form.append(
    `<input type="hidden" name="json_requisicoes" value='${JSON.stringify(
      requisicoes
    )}'>`
  );
  if (tipo > 0) {
    form.trigger("submit");
  }
}
