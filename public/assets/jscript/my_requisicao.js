function buscaTipoMovimentacao(orig, depori, depdes, deppad) {
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
      jQuery("#" + deppad).val(retornoAjax.deppad);
      if (retornoAjax.depori != null) {
        jQuery("#" + depori)
          .closest(".dropdown")
          .addClass("disabled");
        jQuery("#" + depori)
          .next()
          .addClass("disabled");
      }
      jQuery("#" + depori).trigger("change");
      if (retornoAjax.depdes != null) {
        jQuery("#" + depdes)
          .closest(".dropdown")
          .addClass("disabled");
        jQuery("#" + depdes)
          .next()
          .addClass("disabled");
      }
      jQuery("#" + depdes).trigger("change");
    }
  }
}

function gerarCampoNumeroPadrao(
  id,
  classeExtra,
  valor = 0,
  min = 0,
  step = 1,
  index = "",
  classeDiv,
) {
  return `
        <div class="input-group input-group-sm d-inline-flex align-items-center ${classeDiv}" style="max-width: 20ch;min-width: 15ch;font-size:10px">
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
  codigosRenderizados,
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
      ? //   ? `<i class="fa-solid fa-arrow-turn-up text-secondary"
        // id="${toggleId}" style="padding-left: 12px;transform: rotate(90deg);" ></i> `
        `<div class="subline" 
    id="${toggleId}" >&nbsp;</div> `
      : "";

  // <td class="text-end"><span class="float-start">${iconeToggle}${iconeDuplic}</span>${codpro}</td>
  // <td class="text-start p-0"><div class="float-end">${codpro}</div><div class="float-start">${iconeToggle}${iconeDuplic}</div></td>
  classoculta = isDuplicado ? "d-none" : "";

  return `
        <tr class="linha-produto ${classoculta}" 
            data-classe="${dadosDep.classe}" 
            data-index="${index}" 
            data-codpro="${codpro}"
            data-consumo="${prod.pro_consumo}" 
            data-min="${prod.pro_minimo}" 
            data-max="${prod.pro_maximo}"
            data-saldo-destino="${estoqueDes}"
            data-saldo-disponivel="${estoqueDisp}"
            data-sugestao-base="${prod.pro_sugestao}">

            <td class="text-end p-0"><div class="float-start">${iconeToggle}${iconeDuplic}</div>${codpro}</td>
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
              "multiplica ",
              prod.pro_multiplica,
              1,
              1,
              index,
              classoculta,
            )}</td>
            <td class="text-end">
                ${gerarCampoNumeroPadrao(
                  `pro_pctseguranca_${index}`,
                  "seguranca ",
                  prod.pro_pctseguranca,
                  0,
                  1,
                  index,
                  classoculta,
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
              index,
            )}</td>
        </tr>
    `;
}

function montarTabelaProdutos(classe, rt, dadosDep) {
  const text = [];
  const isFirst = rt === 0;
  let temReq = 0;

  // Contador de códigos duplicados
  const codigosRepetidos = {};
  classe.prod.forEach((prod) => {
    codigosRepetidos[prod.pro_codpro] =
      (codigosRepetidos[prod.pro_codpro] || 0) + 1;
    if (prod.pro_requisicao > 0) {
      temReq++;
    }
  });

  // Set para controlar se já renderizou o código
  const codigosRenderizados = new Set();

  text.push(`<div class="accordion-item" data-cla_id="${classe.id}">`);
  text.push(`<h2 class="accordion-header">`);
  text.push(
    `<button class="accordion-button bg-gray-padrao collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsecl${rt}" aria-expanded="${isFirst}" aria-controls="collapsecl${rt}"><div class='col-10 text-start'>${classe.nome + "</div> " + (temReq > 0 ? "<div class='float-end col-1 text-white text-end border border-3 border-danger rounded-pill bg-danger text-center'>" + temReq + "</div>" : "")}</button>`,
  );
  text.push(`</h2>`);
  text.push(
    `<div id="collapsecl${rt}" class="accordion-collapse collapse" data-bs-parent="#accClas">`,
  );
  text.push(`<div class="accordion-body p-1 bg-body-tertiary">`);

  text.push(`<div class="d-flex justify-content-end mb-2">`);
  text.push(`<div class="form-check">`);
  text.push(
    `<input class="form-check-input aceita-sugestao" type="checkbox" data-classe="${rt}" id="checkSug${rt}">`,
  );
  text.push(
    `<label class="form-check-label" for="checkSug${rt}">Aceitar sugestões</label>`,
  );
  text.push(`</div></div>`);

  text.push(
    `<table class="table table-bordered table-striped w-100" style="font-size: 10px;"><thead><tr class="text-center">`,
  );
  text.push(`<th>Cód ERP</th>`);
  text.push(`<th>Descrição</th>`);
  text.push(`<th>Fabricante</th>`);
  text.push(`<th>Lote</th>`);
  text.push(`<th>Validade</th>`);
  text.push(`<th class="vertical-th">Qtd Caixa</th>`);
  text.push(
    `<th class="vertical-th">Saldo Origem<br>${dadosDep.deporigem}</th>`,
  );
  if (dadosDep.deppadrao !== "") {
    text.push(
      `<th class="vertical-th">Saldo Padrão<br>${dadosDep.deppadrao}</th>`,
    );
  }
  text.push(
    `<th class="vertical-th">Saldo Destino<br>${dadosDep.depdestino}</th>`,
  );
  text.push(
    `<th class="vertical-th">${
      dadosDep.diaanterior === "S"
        ? "Consumo<br>" + dadosDep.dataOntem
        : "Média<br>" + dadosDep.meddias + " dias"
    }</th>`,
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
        codigosRenderizados,
      ),
    );
  });

  text.push(`</tbody></table></div></div></div>`);
  return text.join("");
}

async function carregarProdutos(url, aba, obj) {
  const reqid = jQuery("#req_id").val();
  const deporigem = jQuery("#req_deporigem").val();
  const depdestino = jQuery("#req_depdestino").val();
  const tipomovim = jQuery("#tmo_id").val();
  const multiplica = jQuery("#req_repetedias").val();
  const diaanterior = jQuery('input[name="req_consdiaanterior"]:checked').val();
  const mediaconsumo = jQuery('input[name="req_medconsumodias"]:checked').val();
  const seguranca = jQuery("#req_percseguranca").val();
  const meddias = jQuery("#req_meddias").val();
  const proid = jQuery("#pro_id\\[\\]").val();

  if (!deporigem || !depdestino) {
    boxAlert(
      "Informe o Depósito de Origem e o Depósito de Destino",
      true,
      "",
      true,
      3,
      false,
      "Atenção",
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
    seguranca,
    meddias,
    proid,
  };

  try {
    const retornoAjax = await executaAjaxWait(url, "json", dados);
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
    jQuery(".seguranca").each(function () {
      recalculaSeguranca(this);
    });
    atualizarEstadoBotaoSalvar();

    function calcularSugestao(
      base,
      multiplicador,
      seguranca,
      max,
      saldoDestino,
      saldoDisponivel,
      indice,
    ) {
      let posicao = 0; // sou único
      let match = indice.match(/^(.*?)(\d+)$/);

      let inicio = match[1]; // "cl7_pr"
      let ind = parseInt(match[2]); // "14"
      // verificar se o produto da linha atual é o mesmo da linha anterior ou da próxima linha
      const trAnte = jQuery(`tr[data-index="${inicio + (ind - 1)}"]`);
      const codproAnte = trAnte.attr("data-codpro");
      const trAtual = jQuery(`tr[data-index="${indice}"]`);
      const codproAtual = trAtual.attr("data-codpro");
      const trProxi = jQuery(`tr[data-index="${inicio + (ind + 1)}"]`);
      const codproProxi = trProxi.attr("data-codpro");
      if (codproAnte == codproAtual) {
        posicao = 2; // sou igual ao anterior
      } else if (codproAtual == codproProxi) {
        posicao = 1; // sou igual ao próximo
      }

      let sug = base * multiplicador;
      vsegura = Math.ceil(sug * (seguranca / 100));
      sug = sug + vsegura;
      sug = sug - saldoDestino;

      // Ajustar para que o total não ultrapasse o máximo (se definido)
      if (max > 0) {
        let restanteMax = max - saldoDestino;
        if (restanteMax <= 0) return 0; // Não há espaço para sugerir
        sug = Math.min(sug, restanteMax);
      }

      // if (sug > saldoDisponivel) {
      // Limitar ao saldo disponível
      dif = Math.max(0, sug - saldoDisponivel);
      sug = Math.min(sug, saldoDisponivel);
      if (posicao > 0) {
        if (posicao == 1) {
          // atualizarSugestao(inicio + (ind + 1), dif);
          saldoDestino = 0;
          saldoDisponivel =
            parseInt(trProxi.attr("data-saldo-disponivel")) || 0;

          const novaSug = calcularSugestao(
            dif,
            1,
            seguranca,
            max,
            saldoDestino,
            saldoDisponivel,
            inicio + (ind + 1),
          );
          atualizarSugestao(inicio + (ind + 1), novaSug);
          preencherRequisicaoAutomatica(
            inicio + (ind + 1),
            novaSug,
            trAtual.attr("data-classe"),
          );
        }
      }
      // }

      // Se sugestão é negativa ou zero, retorna zero
      return Math.max(0, sug);
    }

    function preencherRequisicaoAutomatica(index, valor, classe) {
      const checkbox = jQuery(`#checkSug${classe}`);
      if (checkbox.is(":checked")) {
        jQuery(`.requisicao[data-index="${index}"]`)
          .attr("data-ignore-validation", true)
          .val(valor)
          .trigger("change");
      }
    }

    function atualizarSugestao(index, novaSug) {
      jQuery(`#sug_${index}`).text(parseInt(novaSug));
    }

    function atualizarSeguranca(index, consumo, seguranca) {
      const novoSeg = Math.ceil(consumo * (seguranca / 100));
      jQuery(`#seg_${index}`).text(novoSeg);
      return novoSeg;
    }

    function recalculaSeguranca(obj) {
      const input = jQuery(obj);
      const indice = input.attr("data-index");
      let match = indice.match(/^(.*?)(\d+)$/);

      let inicio = match[1]; // "cl7_pr"
      let ind = parseInt(match[2]); // "14"
      let posicao = 1;

      // verificar se o produto da linha atual é o mesmo da linha anterior ou da próxima linha
      const trAnte = jQuery(`tr[data-index="${inicio + (ind - 1)}"]`);
      const codproAnte = trAnte.attr("data-codpro");
      const trAtual = jQuery(`tr[data-index="${indice}"]`);
      const codproAtual = trAtual.attr("data-codpro");
      const trProxi = jQuery(`tr[data-index="${inicio + (ind + 1)}"]`);
      const codproProxi = trProxi.attr("data-codpro");
      let sugAnte = 0;
      if (codproAnte == codproAtual) {
        posicao = 2; // sou igual ao anterior
        sugAnte = parseInt(jQuery(`#sug_${inicio + (ind - 1)}`).text());
      } else if (codproAtual == codproProxi) {
        posicao = 1; // sou igual ao próximo
      }

      // const tr = jQuery(`tr[data-index="${index}"]`);

      const baseSugOriginal = parseInt(trAtual.find(".sugestao").text()) || 0;
      // const baseSugOriginal = parseInt(tr.data('sugestao-base')) || 0;
      const saldoDisponivel =
        parseInt(trAtual.attr("data-saldo-disponivel")) || 0;
      const min = parseInt(trAtual.attr("data-min")) || 0;
      let max = parseInt(trAtual.attr("data-max")) || 0;
      max = max === 0 ? saldoDisponivel : max;

      // const codproAtual = tr.attr("data-codpro");
      let saldoDestino = 0;

      jQuery("tr").each(function () {
        const linha = jQuery(this);
        if (linha.attr("data-codpro") == codproAtual) {
          saldoDestino += parseInt(linha.attr("data-saldo-destino")) || 0;
        }
      });

      // const saldoDestino = parseInt(tr.data('saldo-destino')) || 0;
      let consumo = 0;
      if (posicao == 1) {
        consumo = parseInt(trAtual.attr("data-consumo")) || 0;
      } else {
        consumo = parseInt(trAnte.attr("data-consumo")) - sugAnte || 0;
      }
      const seguranca = parseInt(input.val()) || 0;

      const multiplicador =
        parseInt(jQuery(`#pro_multiplica_${indice}`).val()) || 1;
      max = max * multiplicador;

      const segAnterior = parseInt(jQuery(`#seg_${indice}`).text()) || 0;

      let baseSug = baseSugOriginal - segAnterior;
      const novoSeg = atualizarSeguranca(indice, consumo, seguranca);
      baseSug += novoSeg;

      const novaSug = calcularSugestao(
        consumo,
        multiplicador,
        seguranca,
        max,
        saldoDestino,
        saldoDisponivel,
        indice,
      );

      // const novaSug = calcularSugestao(consumo, multiplicador, min, max, saldoDestino, saldoDisponivel);

      atualizarSugestao(indice, novaSug);
      preencherRequisicaoAutomatica(
        indice,
        novaSug,
        trAtual.attr("data-classe"),
      );
    }

    // Evento para multiplicador
    jQuery(".multiplica").on("change", function () {
      const input = jQuery(this);
      const index = input.attr("data-index");
      const tr = jQuery(`tr[data-index="${index}"]`);

      const baseSug = parseInt(tr.find(".sugestao").text()) || 0;

      const saldoDisponivel = parseInt(tr.attr("data-saldo-disponivel")) || 0;
      let min = parseInt(tr.attr("data-min")) || 0;
      let max = parseInt(tr.attr("data-max")) || 0;
      max = max === 0 ? saldoDisponivel : max;

      const codproAtual = tr.attr("data-codpro");
      let saldoDestino = 0;

      jQuery("tr").each(function () {
        const linha = jQuery(this);
        if (linha.attr("data-codpro") == codproAtual) {
          saldoDestino += parseInt(linha.attr("data-saldo-destino")) || 0;
        }
      });
      // const saldoDestino = parseInt(tr.data('saldo-destino')) || 0;
      const multiplicador = parseInt(input.val()) || 1;
      max = max * multiplicador;
      min = min * multiplicador;

      const consumo = parseInt(tr.attr("data-consumo")) || 0;
      const seguranca =
        parseInt(jQuery(`#pro_pctseguranca_${index}`).val()) || 0;

      const novaSug = calcularSugestao(
        consumo,
        multiplicador,
        seguranca,
        max,
        saldoDestino,
        saldoDisponivel,
        index,
      );

      atualizarSugestao(index, novaSug);

      preencherRequisicaoAutomatica(index, novaSug, tr.attr("data-classe"));
    });

    // Evento para segurança
    jQuery(".seguranca").on("change", function () {
      recalculaSeguranca(this);
    });

    jQuery(".aceita-sugestao").on("change", function () {
      const classeId = jQuery(this).attr("data-classe");
      const checked = jQuery(this).is(":checked");
      jQuery(`tr[data-classe="${classeId}"]`).each(function () {
        const index = jQuery(this).attr("data-index");
        const valorSugestao =
          parseInt(jQuery(this).find(`#sug_${index}`).text()) || 0;
        jQuery(`.requisicao[data-index="${index}"]`)
          .attr("data-ignore-validation", true)
          .val(checked ? valorSugestao : 0)
          .trigger("change");
      });
    });

    jQuery(".requisicao").on("change", async function () {
      const input = jQuery(this);
      if (input.attr("data-ignore-validation")) {
        input.removeAttr("data-ignore-validation");
        return;
      }

      const index = input.attr("data-index");
      let posicao = 0; // sou único
      let indexMultip = index;

      let match = index.match(/^(.*?)(\d+)$/);

      let inicio = match[1]; // "cl7_pr"
      let ind = parseInt(match[2]); // "14"
      // verificar se o produto da linha atual é o mesmo da linha anterior ou da próxima linha
      let trAnte = jQuery(`tr[data-index="${inicio + (ind - 1)}"]`);
      const codproAnte = trAnte.attr("data-codpro");
      const trAtual = jQuery(`tr[data-index="${index}"]`);
      const codproAtual = trAtual.attr("data-codpro");
      let jaSolicitado = 0;
      if (codproAnte == codproAtual) {
        saldoAnte = trAnte.attr("data-saldo-disponivel");
        solicAnte = jQuery("#requisicao_" + index).val();
        jaSolicitado = jQuery(`#requisicao_${inicio + (ind - 1)}`).val();
        if (saldoAnte > jaSolicitado) {
          conf = await boxAlert(39, false, "", false, 1, true);
          if (!conf) {
            jQuery(this).val(0);
            return;
          }
        }
        indexMultip = inicio + (ind - 1);
      } else {
        trAnte = trAtual;
      }

      const multiplicador =
        parseInt(jQuery(`#pro_multiplica_${indexMultip}`).val()) || 1;
      const valAtual = Math.round(parseInt(input.val()) || 0);
      if (valAtual != 0) {
        // const tr = jQuery(`tr[data-index="${indexMultip}"]`);

        const codigo = trAtual.attr("data-codpro");
        let minOriginal = parseInt(trAnte.attr("data-min")) || 0;
        const saldoDisponivelAtual =
          parseInt(trAtual.attr("data-saldo-disponivel")) || 0;
        let maxOriginal = parseInt(trAnte.attr("data-max")) || 0;
        let maxAntesOri = maxOriginal;

        maxOriginal = maxOriginal * multiplicador;
        minOriginal = minOriginal * multiplicador;

        // maxOriginal = maxOriginal === 0 ? saldoDisponivelAtual : maxOriginal;

        const desconsideraMaximo = minOriginal === 0 && maxOriginal === 0;

        // if (!desconsideraMaximo) {
        const codproAtual = trAtual.attr("data-codpro");
        let saldoDestinoAtual = 0;

        jQuery("tr").each(function () {
          const linha = jQuery(this);
          if (linha.attr("data-codpro") == codproAtual) {
            saldoDestinoAtual +=
              parseInt(linha.attr("data-saldo-destino")) || 0;
          }
        });
        // const saldoDestinoAtual = parseInt(tr.data('saldo-destino')) || 0;

        let motivo = 0;
        let novoValor = valAtual;

        if (saldoDestinoAtual > maxOriginal && !desconsideraMaximo) {
          novoValor = 0;
          input.val(novoValor);
          // motivos.push(12);
          motivo = 12;
          // motivos.push(`Saldo Atual (${saldoDestinoAtual}) maior que o Máximo (${maxOriginal})`);
        } else {
          let max = 0;
          if (!desconsideraMaximo) {
            if (maxAntesOri == 0) {
              max = Math.max(0, maxOriginal);
            } else {
              max = Math.max(0, maxOriginal - saldoDestinoAtual);
            }
            let min = Math.min(minOriginal, minOriginal - saldoDestinoAtual);

            min = min - jaSolicitado;

            let restantePermitido = 0;

            // if (!desconsideraMaximo) {
            const lotesDoProduto = jQuery(`.requisicao`).filter(function () {
              return jQuery(this).closest("tr").attr("data-codpro") === codigo;
            });

            let somaOutros = 0;

            lotesDoProduto.each(function () {
              const otherInput = jQuery(this);
              if (otherInput.is(input)) return;

              const val = parseInt(otherInput.val()) || 0;
              somaOutros += val;
            });

            restantePermitido = max - somaOutros;
            novoValor = Math.min(novoValor, restantePermitido);
            // }

            novoValor = Math.max(min, novoValor);
            novoValor = Math.min(novoValor, saldoDisponivelAtual);

            if (novoValor !== valAtual) {
              input.val(novoValor);

              if (valAtual > restantePermitido) {
                motivo = 12;
                // motivos.push(`Máximo permitido (${max})`);
              }
              if (valAtual < min) {
                motivo = 13;
              }

              if (valAtual > saldoDisponivelAtual) {
                motivo = 30;
                // motivos.push(`Saldo disponível do lote (${saldoDisponivelAtual})`);
              }
              // motivos.push(`Valor ajustado para não ultrapassar`);
            } else {
              input.val(novoValor); // Garante valor inteiro mesmo se não alterado
            }
          } else {
            if (valAtual > saldoDisponivelAtual) {
              novoValor = Math.min(valAtual, saldoDisponivelAtual);
              input.val(novoValor);
              motivo = 30;
            }
          }
        }
        if (motivo > 0) {
          // const mensagem = `${motivos.join(' ')}.`;
          msg_id = msg_cfg[motivo - 1];
          const mensagem = msg_id.msg_mensagem;
          mostranoToast(motivo, true);
        }
        // }
      } else {
        input.val(valAtual); // Garante valor inteiro mesmo se não alterado
      }
    });

    // 👉 evento para toggle de linhas duplicadas
    jQuery(document)
      .off("click", ".toggle-linhas")
      .on("click", ".toggle-linhas", function () {
        const codpro = jQuery(this).attr("data-codpro");
        const linhas = jQuery(`tr[data-codpro="${codpro}"]`).not(":first");
        const icone = jQuery(this);

        const isAberto = icone.hasClass("fa-arrow-alt-circle-down");

        if (isAberto) {
          linhas.addClass("d-none");
          icone
            .removeClass("fa-arrow-alt-circle-down")
            .addClass("fa-arrow-alt-circle-right")
            .attr("title", "Mostrar mais");
        } else {
          linhas.removeClass("d-none");
          icone
            .removeClass("fa-arrow-alt-circle-right")
            .addClass("fa-arrow-alt-circle-down")
            .attr("title", "Ocultar");
        }
      });

    jQuery(document).on("input change", ".requisicao", function () {
      atualizarEstadoBotaoSalvar();
    });
    // bindEvents();
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
  regra = "S";
  if (habilitar) {
    regra = "N";
  }
  bloqueiaCampo(
    "S",
    regra,
    "tmo_id,req_consdiaanterior[0],req_consdiaanterior[1],req_medconsumodias[0],req_medconsumodias[1],req_meddias,req_percseguranca,pro_id[],bt_carregar",
  );
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

async function enviarRequisicoes(tipo = 0, event) {
  const requisicoes = [];
  const form = document.getElementById("form1");
  const jqForm = jQuery(form);
  let repeteDias = parseInt(jQuery("#req_repetedias").val()) || 0;

  if (tipo === 1) {
    jqForm.find('input[name="req_status"]').remove();
    jqForm.append(`<input type="hidden" name="req_status" value="${tipo}">`);
  }

  const linhasProduto = jQuery("tr.linha-produto");

  for (let i = 0; i < linhasProduto.length; i++) {
    const tr = jQuery(linhasProduto[i]);
    const index = tr.data("index");
    const inputRequisicao = jQuery(`.requisicao[data-index="${index}"]`);
    const valorRequisicao = parseInt(inputRequisicao.val()) || 0;
    const saldoDisponivel = parseInt(tr.data("saldo-disponivel")) || 0;

    if (valorRequisicao !== 0) {
      if (repeteDias > 0) {
        const saldoNecessario = valorRequisicao * (repeteDias + 1);
        if (saldoNecessario > saldoDisponivel) {
          // event.preventDefault();
          // event.stopPropagation();
          await boxAlert(35, true, "", true, 1, false);
          if (tipo === 1) return;
          return false;
        }
      }

      const dados = {};

      tr.find("td").each(function (i) {
        const th = tr.closest("table").find("thead th").eq(i);
        const nomeColuna = normalizarNomeColuna(th.text());

        const texto = jQuery(this)
          .clone()
          .children()
          .remove()
          .end()
          .contents()
          .filter(function () {
            return this.nodeType === 3; // Text node
          })
          .text()
          .trim();
        if (nomeColuna) {
          dados[nomeColuna] = texto;
        }
      });

      dados.multiplica = jQuery(`#pro_multiplica_${index}`).val();
      dados.seguranca = jQuery(`#pro_pctseguranca_${index}`).val();
      dados.requisicao = valorRequisicao;

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
  }

  jqForm.find('input[name="json_requisicoes"]').remove();
  jqForm.append(
    `<input type="hidden" name="json_requisicoes" value='${JSON.stringify(
      requisicoes,
    )}'>`,
  );

  if (tipo > 0) {
    form.classList.add("was-validated");

    const isValido = validador(form);
    if (!isValido) {
      event.preventDefault();
      event.stopPropagation();
      desBloqueiaTela();
    } else {
      jQuery(form).trigger("submit");
    }
  } else {
    return true;
  }
}

async function enviarAteRequisicoes(event) {
  const trs = jQuery("tr").toArray();

  for (let tr of trs) {
    if (tr.id != "" && tr.id != undefined) {
      const idBase = parseInt(tr.id);

      var lf = jQuery("#fab_" + idBase).text();
      var ctafab = jQuery("#ctafb_" + idBase).val();
      var lp = jQuery("#lot_" + idBase).text();
      var ctalot = jQuery("#ctalt_" + idBase).val();
      var ctamis = jQuery("#ctami_" + idBase).val();
      var qtcaixa = parseInt(jQuery("#cx_" + idBase).text());
      var qtde = jQuery("#qt_" + idBase).text();
      var aten = jQuery("#rpa_atendida_" + idBase).val();
      var canc = jQuery("#rpa_cancelada_" + idBase).val();
      var saldo = jQuery("#sl_" + idBase).text();
      fabok = false;
      lotof = false;
      misok = true;

      if (lf == "SN") {
        if (parseInt(ctafab) >= parseInt(qtcaixa)) {
          fabok = true;
        } else if (parseInt(ctafab) > 0) {
          fabok = false;
        }
      } else if (lf == "NN") {
        fabok = true;
      } else if (lf == "SS") {
        if (parseInt(ctafab) == parseInt(qtde) - parseInt(canc)) {
          fabok = true;
        } else if (parseInt(ctafab) > 0) {
          fabok = false;
        }
      }
      if (lp == "SN") {
        if (parseInt(ctalot) >= parseInt(qtcaixa)) {
          lotok = true;
        } else if (parseInt(ctalot) > 0) {
          lotok = false;
        }
      } else if (lp == "NN") {
        if (parseInt(aten) == parseInt(qtde) - parseInt(canc)) {
          lotok = true;
        }
      } else if (lp == "SS") {
        if (parseInt(ctalot) == parseInt(qtde) - parseInt(canc)) {
          lotok = true;
        } else if (parseInt(ctalot) > 0) {
          lotok = false;
        }
      }
      msg = 0;
      //NÃO SCANIEI NENHUM PRODUTO E SCANIEI OU FABRICANTE OU MISTURADOR
      if (parseInt(aten) == 0 && parseInt(ctafab) > 0) {
        event.preventDefault();
        event.stopPropagation();
        resposta = await boxAlert(40, true, "", false, 1, false);
        return false;
      } else if (
        (aten > 0 || ctafab > 0 || ctalot > 0 || canc > 0) &&
        (parseInt(saldo) > 0 || !lotok || !fabok)
      ) {
        event.preventDefault();
        event.stopPropagation();
        if (parseInt(saldo) === 0 && !fabok) {
          resposta = await boxAlert(38, true, "", false, 1, false);
          return false;
        } else {
          resposta = await boxAlert(33, false, "", false, 1, true);
          return resposta; // Interrompe o processamento
        }
      }
    }
  }
  return true;
}

async function enviarConfRequisicoes(event) {
  const trs = jQuery("tr").toArray();

  for (let tr of trs) {
    if (tr.id != "" && tr.id != undefined) {
      const idBase = parseInt(tr.id);
      var conf = jQuery("#rpa_conferida_" + idBase).val();
      var ctafab = jQuery("#ctafb_" + idBase).val();
      var ctalot = jQuery("#ctalt_" + idBase).val();
      var ctamis = jQuery("#ctami_" + idBase).val();
      if (parseInt(conf) > 0 || parseInt(ctamis) > 0 || parseInt(ctafab) > 0) {
        var lf = jQuery("#fab_" + idBase).text();
        var lp = jQuery("#lot_" + idBase).text();
        var lm = jQuery("#mis_" + idBase).text();
        var qtcaixa = parseInt(jQuery("#cx_" + idBase).text());
        var qtde = jQuery("#qt_" + idBase).text();
        var canc = jQuery("#ca_" + idBase).text();
        var saldo = jQuery("#sl_" + idBase).text();
        fabok = false;
        lotok = false;
        misok = false;

        if (lf == "SN") {
          if (parseInt(ctafab) >= parseInt(qtcaixa)) {
            fabok = true;
          } else if (parseInt(ctafab) > 0) {
            fabok = false;
          }
        } else if (lf == "NN") {
          fabok = true;
        } else if (lf == "SS") {
          if (parseInt(ctafab) == parseInt(qtde) - parseInt(canc)) {
            fabok = true;
          } else if (parseInt(ctafab) > 0) {
            fabok = false;
          }
        }
        if (lp == "SN") {
          if (parseInt(ctalot) >= parseInt(qtcaixa)) {
            lotok = true;
          } else if (parseInt(ctalot) > 0) {
            lotok = false;
          }
        } else if (lp == "NN") {
          lotok = true;
        } else if (lp == "SS") {
          if (parseInt(ctalot) == parseInt(qtde) - parseInt(canc)) {
            lotok = true;
          } else if (parseInt(ctalot) > 0) {
            lotok = false;
          }
        }
        if (lm == "SN") {
          if (parseInt(ctamis) >= parseInt(qtcaixa)) {
            misok = true;
          } else if (parseInt(ctamis) > 0) {
            misok = false;
          }
        } else if (lm == "NN") {
          misok = true;
        } else if (lm == "SS") {
          if (parseInt(ctamis) == parseInt(qtde) - parseInt(canc)) {
            misok = true;
          } else if (parseInt(ctamis) > 0) {
            misok = false;
          }
        }
      }
      msg = 0;
      //NÃO SCANIEI NENHUM PRODUTO E SCANIEI OU FABRICANTE OU MISTURADOR
      if (
        parseInt(conf) == 0 &&
        (parseInt(ctafab) > 0 || parseInt(ctamis) > 0)
      ) {
        msg = 40;
      }
      if (parseInt(conf) > 0) {
        // SCANIEI PELO MENOS 1 PRODUTO
        if (parseInt(saldo) > 0) {
          msg = 9;
        } else if (
          parseInt(saldo) == 0 &&
          ((!fabok && parseInt(ctafab) > 0) || (!misok && parseInt(ctamis) > 0))
        ) {
          msg = 38;
        }
      }
      if (msg > 0) {
        event.preventDefault();
        event.stopPropagation();
        if (msg == 9) {
          resposta = await boxAlert(msg, false, "", false, 1, true);
          if (resposta) {
            var qtnacaixa = parseInt(jQuery("#qtdemb_" + idBase).val());
            qtconferida = Math.ceil(parseInt(ctalot) / qtnacaixa);
            if (lf == "SN") {
              if (parseInt(ctafab) >= parseInt(qtconferida)) {
                fabok = true;
              } else if (parseInt(ctafab) > 0) {
                fabok = false;
              }
            } else if (lf == "NN") {
              fabok = true;
            } else if (lf == "SS") {
              if (parseInt(ctafab) == parseInt(ctalot)) {
                fabok = true;
              } else if (parseInt(ctafab) > 0) {
                fabok = false;
              }
            }
            if (lm == "SN") {
              if (parseInt(ctamis) >= parseInt(qtconferida)) {
                misok = true;
              } else if (parseInt(ctamis) > 0) {
                misok = false;
              }
            } else if (lp == "NN") {
              misok = true;
            } else if (lp == "SS") {
              if (parseInt(ctamis) == parseInt(ctalot)) {
                misok = true;
              } else if (parseInt(ctamis) > 0) {
                misok = false;
              }
            }
            if (!fabok || !misok) {
              msg = 38;
              resposta = await boxAlert(msg, true, "", false, 1, false);
              resposta = false;
            }
          }
          return resposta;
        } else {
          resposta = await boxAlert(msg, true, "", false, 1, false);
          return false;
        }
      }
    }
  }
  return true;
}

async function gerarOcorrencia(tela, indice) {
  url = window.location.origin + "/OcoOcorrencia/addOutraTela";
  telid = tela;
  regid = jQuery("#req_id").val();
  proid = jQuery("#proid_" + indice).val();
  lotlote = jQuery("#lotlote_" + indice).val();
  titulo = "Gerar Ocorrência";
  dados = JSON.stringify({
    pro_id: proid,
    lot_lote: lotlote,
    req_id: regid,
    tel_id: telid,
  });
  const retornoAjax = await executaAjaxWait(url, "html", dados);
  if (retornoAjax) {
    if (titulo) {
      jQuery(".modal-title").html(titulo);
    }
    jQuery(".modal-body").html(retornoAjax);
    var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
    // document.onreadystatechange = function () {
    myModal.show();
  }
}

async function copiar_requisicao(url) {
  const retornoAjax = await executaAjaxWait(url, "html", dados);
}

async function gerarInspecao(tela, indice) {
  url = window.location.origin + "/InspecaoProd/inspeciona";
  telid = tela;
  regid = jQuery("#req_id").val();
  proid = jQuery("#proid_" + indice).val();
  lotlote = jQuery("#lotlote_" + indice).val();
  titulo = "Gerar Inspeção";
  dados = JSON.stringify({
    pro_id: proid,
    lot_lote: lotlote,
    req_id: regid,
    tel_id: telid,
  });
  const retornoAjax = await executaAjaxWait(url, "html", dados);
  if (retornoAjax) {
    if (titulo) {
      jQuery(".modal-title").html(titulo);
    }
    jQuery(".modal-body").html(retornoAjax);
    var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
    // document.onreadystatechange = function () {
    myModal.show();
  }
}
