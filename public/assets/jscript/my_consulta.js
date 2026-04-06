function pesquisacep(obj, valor) {
  // regex = '\[(\w+)\]';
  // pos = obj['id'].indexOf("__");
  posi = obj.id.indexOf("[") + 1;
  posf = obj.id.indexOf("]");
  pos = obj.id.substr(posi, posf - posi);
  if (pos >= 0) {
    pos_seq = obj["id"].substring(pos + 2, obj["id"].length);
  }
  //Nova variável "cep" somente com dígitos.
  var cep = valor.replace(/\D/g, "");
  //Verifica se campo cep possui valor informado.
  if (cep != "") {
    //Expressão regular para validar o CEP.
    var validacep = /^[0-9]{8}$/;
    //Valida o formato do CEP.
    if (validacep.test(cep)) {
      // limpa_formulário_cep();
      var url = "https://viacep.com.br/ws/" + cep + "/json/?callback=?";
      var uf = [];
      uf["AC"] = "1";
      uf["AL"] = "2";
      uf["AM"] = "3";
      uf["AP"] = "4";
      uf["BA"] = "5";
      uf["CE"] = "6";
      uf["DF"] = "7";
      uf["ES"] = "8";
      uf["GO"] = "9";
      uf["MA"] = "10";
      uf["MT"] = "11";
      uf["MS"] = "12";
      uf["MG"] = "13";
      uf["PA"] = "14";
      uf["PB"] = "15";
      uf["PR"] = "16";
      uf["PE"] = "17";
      uf["PI"] = "18";
      uf["RJ"] = "19";
      uf["RN"] = "20";
      uf["RS"] = "21";
      uf["RO"] = "22";
      uf["RR"] = "23";
      uf["SC"] = "24";
      uf["SP"] = "25";
      uf["SE"] = "26";
      uf["TO"] = "27";

      // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
      // caso ocorra algum erro (o cep pode não existir, por exemplo) a
      // usabilidade não seja afetada, assim o usuário pode continuar//
      // preenchendo os campos normalmente
      jQuery.getJSON(url, function (dadosRetorno) {
        try {
          // Preenche os campos de acordo com o retorno da pesquisa
          // jQuery("#end_rua__"+pos_seq).val(dadosRetorno.logradouro);
          // jQuery("#end_bairro__"+pos_seq).val(dadosRetorno.bairro);
          // jQuery("#end_id_estado__"+pos_seq).val(uf[dadosRetorno.uf]);
          // jQuery("#busca_end_id_cidade__"+pos_seq).val(dadosRetorno.localidade);
          // jQuery('#busca_end_id_cidade__'+pos_seq).trigger('onkeyup');
          jQuery("input[id='end_rua[" + pos + "]']").val(
            dadosRetorno.logradouro,
          );
          jQuery("input[id='end_bairro[" + pos + "]']").val(
            dadosRetorno.bairro,
          );
          jQuery(
            "select[id='end_id_estado[" +
              pos +
              "]'] option[value='" +
              uf[dadosRetorno.uf] +
              "']",
          ).attr("selected", "selected");
          jQuery("[name='end_id_estado[" + pos + "]']").selectpicker("refresh");
          jQuery("[name='end_id_estado[" + pos + "]']").selectpicker("render");
          jQuery("select[id='end_id_estado[" + pos + "]']").trigger("onchange");
          jQuery(
            "select[id='end_id_cidade[" +
              pos +
              "]'] option:contains(" +
              dadosRetorno.localidade +
              ")",
          ).attr("selected", "selected");
          jQuery("[name='end_id_cidade[" + pos + "]']").selectpicker("refresh");
          jQuery("[name='end_id_estado[" + pos + "]']").selectpicker("render");
          // jQuery("[id='end_id_cidade["+pos+"]']").trigger('onkeyup');
        } catch (ex) {}
      });
    } //end if.
    else {
      //cep é inválido.
      // limpa_formulário_cep();
      alert("Formato de CEP inválido.");
    }
  } //end if.
  else {
    //cep sem valor, limpa formulário.
    // limpa_formulário_cep();
  }
}

function pesquisaCNPJ(valor) {
  //Nova variável "CNPJ" somente com dígitos.
  var CNPJ = valor.replace(/\D/g, "");
  //Verifica se campo CNPJ possui valor informado.
  if (CNPJ != "") {
    //Expressão regular para validar o CEP.
    var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
    //Valida o formato do CEP.
    if (validaCNPJ.test(CNPJ)) {
      // testaCliente = CNPJCPFcadastrado(valor);
      url = window.location.origin + "/buscas/cnpjcpfcadastrado";
      dados = { cpfcnpf: valor };
      retornoAjax = false;
      executaAjax(url, "json", dados);
      if (retornoAjax) {
        if (retornoAjax.tem == "1") {
          boxAlert("CNPJ Já Cadastrado", true, "", false, 1, false);
          jQuery("#cli_cnpj").val("");
          jQuery("#cli_cnpj").focus();
        } else {
          var url =
            "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

          // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
          // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
          // usabilidade não seja afetada, assim o usuário pode continuar//
          // preenchendo os campos normalmente

          jQuery.getJSON(url, function (dadosRetorno) {
            jQuery("#cli_nome").val(dadosRetorno["RAZAO SOCIAL"]);
            jQuery("#cli_apelido").val(dadosRetorno["NOME FANTASIA"]);
            localStorage.setItem("dadoscnpj", dadosRetorno);
            // pesquisacepcnpj();
          });
        }
      }
      // jQuery.ajax({
      //     type: 'POST',
      //     async: false,
      //     dataType: 'json',
      //     url: url,
      //     data: { 'cpfcnpf': valor },
      //     success: function (retorno) {
      //         if (retorno.tem == '1') {
      //             boxAlert('CNPJ Já Cadastrado', true, '', false, 1, false);
      //             jQuery('#cli_cnpj').val('');
      //             jQuery('#cli_cnpj').focus();
      //         } else {
      //             var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

      //             // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
      //             // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
      //             // usabilidade não seja afetada, assim o usuário pode continuar//
      //             // preenchendo os campos normalmente

      //             jQuery.getJSON(url, function (dadosRetorno) {
      //                 jQuery('#cli_nome').val(dadosRetorno['RAZAO SOCIAL']);
      //                 jQuery('#cli_apelido').val(dadosRetorno['NOME FANTASIA']);
      //                 localStorage.setItem('dadoscnpj', dadosRetorno);
      //                 // pesquisacepcnpj();
      //             });
      //         }
      //     }
      // });
    } //end if.
    else {
      //CNPJ é inválido.
      // limpa_formulário_CNPJ();
      boxAlert("Formato de CNPJ inválido", true, "", false, 1, false);
    }
  } //end if.
  else {
    //CNPJ sem valor, limpa formulário.
    // limpa_formulário_CNPJ();
  }
}

function pesquisaCPF(valor) {
  if (valor != "") {
    // testaCliente = ;
    url = window.location.origin + "/buscas/cnpjcpfcadastrado";
    dados = { cpfcnpf: valor };
    retornoAjax = false;
    executaAjax(url, "json", dados);
    if (retornoAjax) {
      if (retornoAjax.tem == "1") {
        boxAlert("CPF Já Cadastrado", true, "", false, 1, false);
        jQuery("#cli_cpf").val("");
        jQuery("#cli_cpf").focus();
      }
    }
    // jQuery.ajax({
    //     type: 'POST',
    //     async: false,
    //     dataType: 'json',
    //     url: url,
    //     data: { 'cpfcnpf': valor },
    //     success: function (retorno) {
    //         if (retorno.tem == '1') {
    //             boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
    //             jQuery('#cli_cpf').val('');
    //             jQuery('#cli_cpf').focus();
    //         }
    //     }
    // });
  }
}

function pesquisacepcnpj() {
  dadoscnpj = localStorage.getItem("dadoscnpj");
  if (dadoscnpj != undefined) {
    jQuery("#end_logradouro").val(dadoscnpj["LOGRADOURO"]);
    jQuery("#end_cep").val(dadoscnpj["CEP"]);
    jQuery("#end_bairro").val(dadoscnpj["BAIRRO"]);
    jQuery("#end_cidade").val(dadoscnpj["MUNICIPIO"]);
    jQuery("#end_uf").val(dadoscnpj["UF"]);
  }
}

function busca_dados_material(orig, obj, url, base) {
  var datarr = new Array();
  pref = obj.substring(0, 3);
  posi = orig.id.indexOf("[") + 1;
  posf = orig.id.indexOf("]");
  pos = orig.id.substr(posi, posf - posi);
  // sufi = '';
  // if(orig.id.indexOf('__') > 0){
  //     sufi = orig.id.substr(-3);
  // }
  datarr[0] = {};
  datarr[0].valor = orig.value;
  dados = { campo: datarr };
  retornoAjax = false;
  executaAjax(url, "json", dados);
  if (retornoAjax) {
    jQuery("#" + pref + "_unidade\\[" + pos + "\\]").selectpicker(
      "val",
      retornoAjax.mat_unidade,
    );
    jQuery("#" + pref + "_unitario\\[" + pos + "\\]").val(
      converteFloatMoeda(retornoAjax.mat_compra / retornoAjax.mat_quantia),
    );
    if (retornoAjax.mpc_unitario != null) {
      jQuery("#" + pref + "_unitario\\[" + pos + "\\]").val(
        converteFloatMoeda(retornoAjax.mpc_unitario / 1),
      );
    }
  }
}

function prevEtiqueta(url) {
  const campos = [];
  jQuery("#etqPreview").html("Renderizando...");

  jQuery('select[name^="etc_campo["]').each(function () {
    const index = jQuery(this)
      .attr("name")
      .match(/\[(\d+)\]/)[1];

    let campo = {
      etc_campo: jQuery(`select[name="etc_campo[${index}]"]`).val(),
      etc_codbar:
        jQuery(`input[name="etc_codbar[${index}]"]:checked`).val() || "N",
      etc_rotulo: jQuery(`input[name="etc_rotulo[${index}]"]`).val(),
      etc_caracteres: jQuery(`input[name="etc_caracteres[${index}]"]`).val(),
      etc_linhas: jQuery(`input[name="etc_linhas[${index}]"]`).val(),
      etc_colunas: jQuery(`input[name="etc_colunas[${index}]"]`).val(),
      etc_fonte: jQuery(`select[name="etc_fonte[${index}]"]`).val(),
      etc_tamanho: jQuery(`input[name="etc_tamanho[${index}]"]`).val(),
      etc_alinhamento: jQuery(`select[name="etc_alinhamento[${index}]"]`).val(),
      etc_negrito:
        jQuery(`input[name="etc_negrito[${index}]"]:checked`).val() || "N",
      etc_italico:
        jQuery(`input[name="etc_italico[${index}]"]:checked`).val() || "N",
      etc_sublinhado:
        jQuery(`input[name="etc_sublinhado[${index}]"]:checked`).val() || "N",
    };

    if (campo.etc_campo !== null && campo.etc_campo !== "") {
      campos.push(campo);
    }
  });

  const let_id = jQuery('select[name="let_id"]').val();
  const tel_id = jQuery('select[name="tel_id"]').val();

  jQuery.ajax({
    url: url,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      let_id: let_id,
      tel_id: tel_id,
      campos: campos,
    }),
    // xhrFields: {
    //   responseType: "blob",
    // },
    success: function (res) {
      imgRetornada =
        "<img src='data:image/png;base64," +
        res.imagem +
        "' style='width:95%' />";
      jQuery("#etqPreview").html(imgRetornada);
    },
  });
}

var lotok = false;
var fabok = false;
var misok = false;

function validaCodBar(obj) {
  codbar = obj.value;
  if (codbar.length > 0) {
    let origem = "aten";
    let msgqterrada = 32;
    const controller = jQuery("#controler").val();
    if (controller.toLowerCase() === "confrequisicao") {
      origem = "conf";
      msgqterrada = 34;
      validaCodBarConf(obj);
    }
    if (codbar.length < 13) {
      codbar = String(codbar).padStart(13, "0");
    }
    codbar = extrairCodBarFab(codbar);
    var tdCodbar = jQuery("#" + codbar);

    if (tdCodbar.length) {
      // Encontra a <tr> pai
      var linha = tdCodbar.closest("tr")[0]; // pega a linha

      // Extrai o ID base (assumindo que o codbar está na mesma linha do stt_169)
      var idBase = linha.id;

      let saldoatual = parseInt(jQuery("#sl_" + idBase).text());
      saldo = saldoatual;
      var tipo = tdCodbar[0].dataset.id;
      var qtcaixa = parseInt(jQuery("#cx_" + idBase).text());
      var qtde = jQuery("#qt_" + idBase).text();
      var canc = jQuery("#rpa_cancelada_" + idBase).val();

      if (tipo == "cbFab") {
        // SCANEOU CODIGO DO FABRICANTE
        var ctafab = jQuery("#ctafb_" + idBase).val();
        ctafab++;
        jQuery("#ctafb_" + idBase).val(ctafab);

        var lf = jQuery("#fab_" + idBase).text();
        var ctafab = jQuery("#ctafb_" + idBase).val();
        // var fabok = false;
        if (lf == "SN") {
          if (parseInt(ctafab) == parseInt(qtcaixa)) {
            fabok = true;
            var lp = jQuery("#lot_" + idBase).text();
            if (lp == "NN") {
              var qtnacaixa = parseInt(jQuery("#qtdemb_" + idBase).val());
              qtatendida = ctafab * qtnacaixa;
              qtdpen = qtde - canc;
              qtatendida = Math.min(qtatendida, qtdpen);
              jQuery("#rpa_atendida_" + idBase).val(qtatendida);
            }
          } else if (parseInt(ctafab) > parseInt(qtcaixa)) {
            boxAlert(msgqterrada, false, "", true, 1, false);
            obj.value = "";
            obj.focus();
            return;
          } else {
            fabok = false;
            // jQuery("#fab_" + idBase).removeClass("border-secondary");
            // jQuery("#fab_" + idBase).addClass("border-warning bg-warning");
          }
        } else if (lf == "NN") {
          fabok = true;
        } else if (lf == "SS") {
          if (parseInt(ctafab) == parseInt(qtde) - parseInt(canc)) {
            fabok = true;
          } else if (parseInt(ctafab) > parseInt(qtde) - parseInt(canc)) {
            ctafab--;
            jQuery("#ctafb_" + idBase).val(ctafab);
            boxAlert(msgqterrada, false, "", true, 1, false);
            obj.value = "";
            obj.focus();
            return;
          } else {
            fabok = false;
          }
        }
      } else if (tipo == "cbLot") {
        // SCANEOU CÓDIGO DO LOTE
        if (parseInt(saldoatual) == 0) {
          boxAlert(msgqterrada, false, "", true, 1, false);
          obj.value = "";
          obj.focus();
          return;
        } else {
          var ctalot = jQuery("#ctalt_" + idBase).val();
          ctalot++;
          jQuery("#ctalt_" + idBase).val(ctalot);
          jQuery("#rpa_atendida_" + idBase).val(ctalot);
        }
        var lp = jQuery("#lot_" + idBase).text();
        var ctalot = jQuery("#ctalt_" + idBase).val();
        // lotok = false;
        if (lp == "SN") {
          var qtnacaixa = parseInt(jQuery("#qtdemb_" + idBase).val());
          qtatendida = ctalot * qtnacaixa;
          qtdpen = qtde - canc;
          qtatendida = Math.min(qtatendida, qtdpen);
          jQuery("#rpa_atendida_" + idBase).val(qtatendida);
          if (parseInt(ctalot) == parseInt(qtcaixa)) {
            jQuery("#rpa_cancelada_" + idBase).prop("disabled", true);
            jQuery("#rpa_cancelada_" + idBase).prop("readonly", true);
            saldo = 0;
            jQuery("#sl_" + idBase).text(saldo);
            lotok = true;
          } else {
            lotok = false;
            // jQuery("#lot_" + idBase).removeClass("border-secondary");
            // jQuery("#lot_" + idBase).addClass("border-warning bg-warning");
          }
        } else if (lp == "SS") {
          if (parseInt(ctalot) == parseInt(qtde) - parseInt(canc)) {
            lotok = true;
            // jQuery("#lot_" + idBase).removeClass("border-secondary");
            // jQuery("#lot_" + idBase).removeClass("border-warning bg-warning");
            // jQuery("#lot_" + idBase).addClass("border-success bg-success");
            jQuery("#rpa_cancelada_" + idBase).prop("disabled", true);
            jQuery("#rpa_cancelada_" + idBase).prop("readonly", true);
          } else {
            lotok = false;
            // jQuery("#lot_" + idBase).removeClass("border-secondary");
            // jQuery("#lot_" + idBase).addClass("border-warning bg-warning");
          }
          saldo = qtde - canc - ctalot;
          jQuery("#sl_" + idBase).text(saldo);
        }
      }

      // fundo = "";
      // if ((ctafab > 0 || ctalot > 0) && (!lotok || !fabok) && saldo > 0) {
      //   fundo = "bg-warning";
      // } else if ((lotok && fabok) || (saldo == 0 && canc == 0)) {
      //   fundo = "bg-success";
      // } else if (lotok && fabok && canc > 0) {
      //   fundo = "bg-danger";
      // }
      // jQuery("#stt_" + idBase).removeClass("bg-white");
      // jQuery("#stt_" + idBase).removeClass("bg-warning");
      // jQuery("#stt_" + idBase).removeClass("bg-success");
      // jQuery("#stt_" + idBase).removeClass("bg-danger");
      // jQuery("#stt_" + idBase).addClass(fundo);
    } else {
      obj.value = "";
      boxAlert(10, true);
      obj.focus();
    }
    acertaSaldoReq(jQuery("#rpa_atendida_" + idBase));
    obj.value = "";
    obj.focus();
  }
  // }
}

function acertaCorStt(idBase, tipo = "ate") {
  var lf = jQuery("#fab_" + idBase).text();
  var ctafab = parseInt(jQuery("#ctafb_" + idBase).val());
  var lp = jQuery("#lot_" + idBase).text();
  var ctalot = parseInt(jQuery("#ctalt_" + idBase).val());
  // var ctamis = jQuery("#ctami_" + idBase).val();
  var qtcaixa = parseInt(jQuery("#cx_" + idBase).text());
  var qtde = parseInt(jQuery("#qt_" + idBase).text());
  var aten = parseInt(jQuery("#rpa_atendida_" + idBase).val());
  var conf = parseInt(jQuery("#rpa_conferida_" + idBase).val());
  var canc = parseInt(jQuery("#rpa_cancelada_" + idBase).val());
  var saldo = parseInt(jQuery("#sl_" + idBase).text());
  fabok = false;
  lotof = false;
  misok = false;
  if (tipo == "ate") {
    misok = true;
    ctamis = 0;
    conf = 0;
  } else {
    var canc = parseInt(jQuery("#ca_" + idBase).text());
  }

  jQuery("#fab_" + idBase).removeClass("border-success bg-success");
  jQuery("#fab_" + idBase).removeClass("border-secondary");
  jQuery("#fab_" + idBase).removeClass("border-warning bg-warning");
  if (lf == "SN") {
    if (parseInt(ctafab) == parseInt(qtcaixa)) {
      jQuery("#fab_" + idBase).addClass("border-success bg-success");
      fabok = true;
    } else if (parseInt(ctafab) > 0) {
      fabok = false;
      if (parseInt(ctafab) > 0) {
        jQuery("#fab_" + idBase).removeClass("border-secondary");
        jQuery("#fab_" + idBase).addClass("border-warning bg-warning");
      }
    }
  } else if (lf == "NN") {
    fabok = true;
  } else if (lf == "SS") {
    if (parseInt(ctafab) == parseInt(qtde) - parseInt(canc)) {
      fabok = true;
      jQuery("#fab_" + idBase).addClass("border-success bg-success");
    } else if (parseInt(ctafab) > 0) {
      fabok = false;
      if (parseInt(ctafab) > 0) {
        jQuery("#fab_" + idBase).removeClass("border-secondary");
        jQuery("#fab_" + idBase).addClass("border-warning bg-warning");
      }
    }
  }
  jQuery("#lot_" + idBase).removeClass("border-success bg-success");
  jQuery("#lot_" + idBase).removeClass("border-secondary");
  jQuery("#lot_" + idBase).removeClass("border-warning bg-warning");
  if (lp == "SN") {
    if (parseInt(ctalot) == parseInt(qtcaixa)) {
      jQuery("#lot_" + idBase).addClass("border-success bg-success");
      lotok = true;
    } else if (parseInt(ctalot) > 0) {
      lotok = false;
      if (parseInt(ctalot) > 0) {
        jQuery("#lot_" + idBase).removeClass("border-secondary");
        jQuery("#lot_" + idBase).addClass("border-warning bg-warning");
      }
    }
  } else if (lp == "NN") {
    if (parseInt(aten) == parseInt(qtde) - parseInt(canc)) {
      lotok = true;
    }
  } else if (lp == "SS") {
    if (parseInt(ctalot) == parseInt(qtde) - parseInt(canc)) {
      lotok = true;
      jQuery("#lot_" + idBase).addClass("border-success bg-success");
    } else if (parseInt(ctalot) > 0) {
      lotok = false;
      if (parseInt(ctalot) > 0) {
        jQuery("#lot_" + idBase).removeClass("border-secondary");
        jQuery("#lot_" + idBase).addClass("border-warning bg-warning");
      }
    }
  }
  if (tipo == "conf") {
    // SCANEOU CÓDIGO DO MISTURADOR
    var lm = jQuery("#mis_" + idBase).text();
    var ctamis = jQuery("#ctami_" + idBase).val();
    // lotok = false;
    jQuery("#mis_" + idBase).removeClass("border-success bg-success");
    jQuery("#mis_" + idBase).removeClass("border-secondary");
    jQuery("#mis_" + idBase).removeClass("border-warning bg-warning");
    if (lm == "SN") {
      if (parseInt(ctamis) == parseInt(qtcaixa)) {
        jQuery("#mis_" + idBase).addClass("border-success bg-success");
        misok = true;
      } else {
        misok = false;
        if (parseInt(ctamis) > 0) {
          jQuery("#mis_" + idBase).removeClass("border-secondary");
          jQuery("#mis_" + idBase).addClass("border-warning bg-warning");
        }
      }
    } else if (lm == "SS") {
      if (parseInt(ctamis) == parseInt(qtde) - parseInt(canc)) {
        misok = true;
        jQuery("#mis_" + idBase).addClass("border-success bg-success");
      } else {
        misok = false;
        if (parseInt(ctamis) > 0) {
          jQuery("#mis_" + idBase).removeClass("border-secondary");
          jQuery("#mis_" + idBase).addClass("border-warning bg-warning");
        }
      }
    } else if (lm == "NN") {
      misok = true;
    }
    fundo = "";
    if (
      (ctafab > 0 || ctalot > 0 || ctamis > 0 || aten + conf > 0) &&
      (!lotok || !fabok || !misok)
    ) {
      fundo = "bg-warning";
    } else if (lotok && fabok && misok) {
      fundo = "bg-success";
    }
  } else {
    fundo = "";
    if (
      (ctafab > 0 || ctalot > 0 || ctamis > 0 || aten + conf > 0) &&
      (!lotok || !fabok || !misok) &&
      canc == 0
    ) {
      fundo = "bg-warning";
    } else if (lotok && fabok && misok && canc == 0) {
      fundo = "bg-success";
    } else if (canc > 0) {
      fundo = "bg-danger";
    }
  }
  jQuery("#stt_" + idBase).removeClass("bg-white");
  jQuery("#stt_" + idBase).removeClass("bg-warning");
  jQuery("#stt_" + idBase).removeClass("bg-success");
  jQuery("#stt_" + idBase).removeClass("bg-danger");
  jQuery("#stt_" + idBase).addClass(fundo);
  jQuery("#stt_" + idBase).off("dblclick");
  if (fundo == "bg-warning") {
    jQuery("#stt_" + idBase).on("dblclick", () =>
      zerarConferencia(idBase, tipo),
    );
  }
}

function zerarConferencia(idBase, tipo) {
  jQuery("#ctafb_" + idBase).val(0);
  jQuery("#ctalt_" + idBase).val(0);
  jQuery("#ctami_" + idBase).val(0);
  jQuery("#rpa_conferida_" + idBase).val(0);
  jQuery("#rpa_atendida_" + idBase).val(0);
  if (tipo == "ate") {
    jQuery("#sl_" + idBase).text(
      parseInt(jQuery("#qt_" + idBase).text()) -
        parseInt(jQuery("#rpa_cancelada_" + idBase).val()),
    );
  } else {
    jQuery("#sl_" + idBase).text(
      parseInt(jQuery("#rpa_atendida_" + idBase).val()),
    );
  }
  acertaCorStt(idBase, tipo);
}

function extrairCodBarFab(str) {
  const pos = str.indexOf("789");
  if (pos === -1) {
    return str;
  }
  return str.substring(pos, pos + 13);
}

function validaCodBarConf(obj) {
  codbar = obj.value;
  if (codbar.length > 0) {
    let origem = "conf";
    let msgqterrada = 34;
    const controller = jQuery("#controler").val();
    if (controller.toLowerCase() === "aterequisicao") {
      origem = "aten";
      msgqterrada = 32;
      validaCodBar(obj);
    }
    var tdCodbar = "";
    if (codbar.length <= 8) {
      // se o código lido é menor ou igual a 8
      codbar = String(codbar).padStart(8, "0");
      tdCodbar = jQuery("#" + codbar);
    }
    if (tdCodbar.length === 0) {
      if (codbar.length < 13) {
        codbar = String(codbar).padStart(13, "0");
        tdCodbar = jQuery("#" + codbar);
      } else {
        codbar = extrairCodBarFab(codbar);
        tdCodbar = jQuery("#" + codbar);
      }
    }
    if (tdCodbar.length) {
      // Encontra a <tr> pai
      var linha = tdCodbar.closest("tr")[0]; // pega a linha

      // Extrai o ID base (assumindo que o codbar está na mesma linha do stt_169)
      var idBase = linha.id;

      let saldoatual = parseInt(jQuery("#sl_" + idBase).text());
      var tipo = tdCodbar[0].dataset.id;
      var qtcaixa = parseInt(jQuery("#cx_" + idBase).text());
      var qtde = jQuery("#qt_" + idBase).text();
      var canc = jQuery("#ca_" + idBase).text();

      if (tipo == "cbFab") {
        // SCANEOU CODIGO DO FABRICANTE
        var ctafab = jQuery("#ctafb_" + idBase).val();
        ctafab++;
        jQuery("#ctafb_" + idBase).val(ctafab);

        var lf = jQuery("#fab_" + idBase).text();
        var ctafab = jQuery("#ctafb_" + idBase).val();
        // var fabok = false;
        if (lf == "SN") {
          if (parseInt(ctafab) == parseInt(qtcaixa)) {
            fabok = true;
          } else if (parseInt(ctafab) > parseInt(qtcaixa)) {
            ctafab--;
            jQuery("#ctafb_" + idBase).val(ctafab);
            boxAlert(msgqterrada, false, "", true, 1, false);
            obj.value = "";
            obj.focus();
            return;
          } else {
            fabok = false;
          }
        } else if (lf == "SS") {
          if (parseInt(ctafab) == parseInt(qtde) - parseInt(canc)) {
            fabok = true;
          } else if (parseInt(ctafab) > parseInt(qtde) - parseInt(canc)) {
            ctafab--;
            jQuery("#ctafb_" + idBase).val(ctafab);
            boxAlert(msgqterrada, false, "", true, 1, false);
            obj.value = "";
            obj.focus();
            return;
          } else {
            fabok = false;
          }
        }
      } else if (tipo == "cbLot") {
        // SCANEOU CÓDIGO DO LOTE
        if (parseInt(saldoatual) == 0) {
          boxAlert(msgqterrada, false, "", true, 1, false);
          obj.value = "";
          obj.focus();
          return;
        } else {
          var ctalot = jQuery("#ctalt_" + idBase).val();
          ctalot++;
          jQuery("#ctalt_" + idBase).val(ctalot);
          jQuery("#rpa_conferida_" + idBase).val(ctalot);
        }
        var lp = jQuery("#lot_" + idBase).text();
        var ctalot = jQuery("#ctalt_" + idBase).val();
        // lotok = false;
        if (lp == "SN") {
          var qtnacaixa = parseInt(jQuery("#qtdemb_" + idBase).val());
          qtconferida = ctalot * qtnacaixa;
          qtdpen = qtde - canc;
          qtconferida = Math.min(qtconferida, qtdpen);
          jQuery("#rpa_conferida_" + idBase).val(qtconferida);
          if (parseInt(ctalot) >= parseInt(qtcaixa)) {
            // qtconferida = ctalot;
            // jQuery("#rpa_conferida_" + idBase).val(qtconferida);
            // qtde = qtconferida;
            saldo = 0;
            jQuery("#sl_" + idBase).text(saldo);
            lotok = true;
          } else {
            lotok = false;
          }
        } else if (lp == "SS") {
          if (parseInt(ctalot) == parseInt(qtde) - parseInt(canc)) {
            lotok = true;
          } else {
            lotok = false;
          }
          saldo = qtde - ctalot;
          jQuery("#sl_" + idBase).text(saldo);
        }
      } else if (tipo == "cbMis") {
        // SCANEOU CÓDIGO DO MISTURADOR
        var lm = jQuery("#mis_" + idBase).text();
        var ctamis = jQuery("#ctami_" + idBase).val();
        ctamis++;
        jQuery("#ctami_" + idBase).val(ctamis);
        // lotok = false;
        if (lm == "SN") {
          if (parseInt(ctamis) == parseInt(qtcaixa)) {
            misok = true;
          } else if (parseInt(ctamis) > parseInt(qtcaixa)) {
            ctamis--;
            jQuery("#ctami_" + idBase).val(ctamis);
            boxAlert(msgqterrada, false, "", true, 1, false);
            obj.value = "";
            obj.focus();
            return;
          } else {
            misok = false;
          }
        } else if (lm == "SS") {
          if (parseInt(ctamis) == parseInt(qtde) - parseInt(canc)) {
            misok = true;
          } else {
            misok = false;
          }
        }
      }
    } else {
      boxAlert(10, true);
    }
    acertaSaldoConf(jQuery("#rpa_conferida_" + idBase));
    obj.value = "";
    obj.focus();
  }
  // }
}

function acertaSaldoReq(obj) {
  var linha = jQuery(obj).closest("tr")[0]; // pega a linha

  // Extrai o ID base (assumindo que o codbar está na mesma linha do stt_169)
  var idBase = linha.id;

  var qtde = parseInt(jQuery("#qt_" + idBase).text());

  var esto = parseInt(jQuery("#estorig_" + idBase).val());
  var aten = parseInt(jQuery("#rpa_atendida_" + idBase).val());
  var canc = parseInt(jQuery("#rpa_cancelada_" + idBase).val());

  if (aten > esto) {
    // se a quantidade atendida for maior que o Estoque na Origem
    jQuery("#rpa_atendida_" + idBase).val(0);
    aten = 0;
    boxAlert(37, true);
  }
  if (canc > 0) {
    // se teve cancelada, recalcula qte caixa
    qtemb = parseInt(jQuery("#qtdemb_" + idBase).val());
    neces = qtde - canc;
    qtcx = Math.ceil(parseFloat(neces / qtemb));
    jQuery("#cx_" + idBase).text(qtcx);
  }
  saldo = qtde - canc - aten;
  if (saldo < 0) {
    jQuery("#rpa_atendida_" + idBase).val(0);
    aten = 0;
    boxAlert(32, true);
    saldo = qtde - canc - aten;
  }
  jQuery("#sl_" + idBase).text(saldo);
  acertaCorStt(idBase);
}

function acertaSaldoConf(obj) {
  var linha = jQuery(obj).closest("tr")[0]; // pega a linha

  // Extrai o ID base (assumindo que o codbar está na mesma linha do stt_169)
  var idBase = linha.id;

  var aten = parseInt(jQuery("#rpa_atendida_" + idBase).val());
  var conf = parseInt(jQuery("#rpa_conferida_" + idBase).val());

  saldo = aten - conf;
  jQuery("#sl_" + idBase).text(saldo);
  acertaCorStt(idBase, "conf");
}

function carregaTelaAcaoTipo(obj) {
  tipo = obj.value;
  objdest = "telas_aplicaveis";
  if (tipo != "") {
    url =
      window.location.origin + "/OcoSubtOcorrencia/addCampoTa/" + tipo + "/0";
    retornoAjax = false;
    executaAjax(url, "json");
    if (retornoAjax) {
      text = "";
      for (tt = 0; tt < retornoAjax.length; tt++) {
        text +=
          "<div class='row tableDiv table2 mb-4 table-" +
          objdest +
          "' width='100 % ' data-index=" +
          tt +
          " >";
        text += "<div class='col-11'>";
        // text = '<table class="table2 table-sm" data-index="' + proximo + '"><tbody><tr>';
        indice = retornoAjax[tt].length;
        for (ct = 0; ct < indice - 2; ct++) {
          quebra = retornoAjax[tt][ct].indexOf("quebralinha");
          oculto = retornoAjax[tt][ct].indexOf("hidden");
          text += retornoAjax[tt][ct];
        }
        text += "</div>";
        text += "<div class='col-1 d-initial h-auto p-0'>";
        text += "<div class='col-12 d-block float-start text-center p-0'>";
        text += retornoAjax[tt][indice - 2];
        text += retornoAjax[tt][indice - 1];
        text += "</div>";
        // text += "</div>";
        text += "</div>";
        text += "</div>";
      }
      jQuery("#rep_" + objdest).html(text);
      jQuery("select").selectpicker();
    }
  } else {
    jQuery("#rep_" + objdest).html("");
  }
}

function carregaAcaoTipo(obj) {
  let tipo = obj.value;
  let objdest = "acoes";
  if (tipo != "") {
    let url =
      window.location.origin + "/OcoSubtOcorrencia/addCampoTp/" + tipo + "/0";

    retornoAjax = false;
    executaAjax(url, "json");

    if (retornoAjax) {
      let text = "";

      for (let tt = 0; tt < retornoAjax.length; tt++) {
        text +=
          "<div class='row tableDiv table2 mb-4 table-" +
          objdest +
          "' data-index='" +
          tt +
          "'>";

        // COL-11
        text += "<div class='col-11 d-flex row'>";

        text += retornoAjax[tt][0];
        text += retornoAjax[tt][1];
        text += retornoAjax[tt][2];
        text += retornoAjax[tt][3];

        text += "</div>";

        // COL-1
        text += "<div class='col-1 p-0'>";
        text += retornoAjax[tt][5];
        text += "</div>";

        text += "</div>";
      }

      jQuery("#rep_" + objdest).html(text);

      acertaDependente();
      jQuery("select").selectpicker();

      jQuery(".tableDiv.table-" + objdest + " select[id^='tpa']").each(
        function () {
          verificaTipoAcao(this);
        },
      );
    }
  } else {
    jQuery("#rep_" + objdest).html("");
  }
}

async function buscaLoteProduto(obj, url) {
  if (typeof obj === "object" && obj !== null) {
    obj = obj;
  } else {
    obj = jQuery("#" + obj)[0];
  }
  lote = obj.value;
  sut_id = jQuery("#sut_id").val();
  if (lote.trim() != "" && sut_id != "") {
    dados = { busca: lote, sutid: sut_id };
    const retornoAjax = await executaAjaxWait(url, "json", dados);

    // retornoAjax = false;
    // executaAjaxWait(url, "json", dados);

    if (retornoAjax) {
      if (retornoAjax.lotid > 0) {
        jQuery("#pro_id").val(retornoAjax.proid);
        jQuery("#lot_id").val(retornoAjax.lotid);
        jQuery("#pro_despro").val(retornoAjax.despro);
      } else {
        jQuery("#pro_id").val("");
        jQuery("#lot_id").val("");
        jQuery("#pro_despro").val("");
        boxAlert(42, true, "", true);
        desbloqueiaTela();
        obj.value = "";
        jQuery(obj).focus();
      }
    }
  }
}

async function verificaInspecao(indice, req, prod, lote) {
  url = window.location.origin + "/InspecaoProd/verificainspecao";
  dados = { requisicao: req, produto: prod, lote: lote };
  const retornoAjax = await executaAjaxWait(url, "json", dados);

  if (retornoAjax) {
    insp = parseInt(retornoAjax.insp, 10) || 0;
    if (insp > 0) {
      jQuery("#badgeinsp_" + indice).text(insp);
    } else {
      jQuery("#badgeinsp_" + indice).text("");
    }
    var conf = parseInt(jQuery("#cfe_" + indice).text());
    var apro = conf - insp;
    jQuery("#apr_" + indice).text(apro);
    if (insp > 0) {
      jQuery("#btok_" + indice).addClass("d-none");
    } else {
      jQuery("#btok_" + indice).removeClass("d-none");
    }
  }
}
