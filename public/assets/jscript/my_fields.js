jQuery(document).ajaxError(function (event, xhr, settings) {
  if (xhr.status === 303) {
    urllogin = window.location.origin + "/login";
    boxAlert(23, false, urllogin, false, 1, false, "Sessão Expirada");
  }
}); // carregamentos_iniciais();
jQuery(document).ready(function () {});

function carregamentos_iniciais() {
  var temNumero = /[0-9]/;
  var temMaiusc = /[A-Z]/;
  var temMinusc = /[a-z]/;
  var temSimbol = /[!@#$%*()_+^&}{:;?.]/;
  var temDuplic = /(.)\1/;
  var msg1 = " Pelo menos 6 Digitos";
  var msg2 = " 1 número";
  var msg3 = " 1 Letra MAIÚSCULA";
  var msg4 = " 1 Letra minúscula";
  var msg5 = " 1 Símbolo";
  var msg6 = " Sem caracteres repetidos";
  var linn = '<span class="text-danger">';
  var lino = '<span class="text-success">';
  var tem = '<i class="fa-solid fa-check"></i>';
  var nao = '<i class="fa-solid fa-xmark"></i>';

  /**
   * Validação de Senha Forte
   * Executa validações para Senha Forte
   * Document Ready my_fields
   */
  jQuery(".password").on("keyup", function (e) {
    var passwordsInfo = jQuery("#pass-info");
    passwordsInfo.show();
    val = this.value;
    var valid = true;
    var text = "";
    if (val.length > 0) {
      if (val.length >= 6) {
        text += lino + tem + msg1 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg1 + "</span><br>";
      }
      if (temNumero.test(val)) {
        text += lino + tem + msg2 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg2 + "</span><br>";
      }
      if (temMaiusc.test(val)) {
        text += lino + tem + msg3 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg3 + "</span><br>";
      }
      if (temMinusc.test(val)) {
        text += lino + tem + msg4 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg4 + "</span><br>";
      }
      if (temSimbol.test(val)) {
        text += lino + tem + msg5 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg5 + "</span><br>";
      }
      if (!temDuplic.test(val)) {
        text += lino + tem + msg6 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg6 + "</span><br>";
      }
      if (!jQuery(this).hasClass("is-invalid")) {
        jQuery(this).addClass("is-invalid");
      }
      if (valid) {
        text = lino + tem + " SENHA SEGURA</span><br>";
        passwordsInfo.removeClass("border-danger");
        passwordsInfo.addClass("border-success");
      } else {
        passwordsInfo.removeClass("border-success");
        passwordsInfo.addClass("border-danger");
      }
      passwordsInfo.html(text);
    } else {
      passwordsInfo.removeClass("border-danger");
      jQuery(this).removeClass("is-invalid");
    }
  });

  /**
   * Se existir campos sortable
   * Document Ready my_fields
   */
  // jQuery('.sort').sortable();

  /**
   * .editor summernote()
   * Se existir um campo Editor, aplica o summernote do Bootstrap
   * Document Ready my_fields
   */
  if (jQuery("input, textarea").hasClass("editor")) {
    jQuery(".editor").summernote({
      height: 200,
      width: "80%",
      lang: "pt-BR",
      callbacks: {
        onImageUpload: function (files, editor, welEditable) {
          sendFile(files[0], this);
        },
      },
    });
  }
  /**
   * sendFile
   * Função utilizada pelo Summernote para envio de arquivos
   * Document Ready my_fields
   */
  function sendFile(file, el) {
    data = new FormData();
    data.append("file", file);
    url = "/Utils/upload";
    retornoAjax = false;
    executaAjax(url, "html", data);
    if (retornoAjax) {
      jQuery(el).summernote("editor.insertImage", retornoAjax);
    }
    // jQuery.ajax({
    //     data: data,
    //     type: "POST",
    //     url: '/Utils/upload',
    //     cache: false,
    //     contentType: false,
    //     processData: false,
    //     success: function (url) {
    //         jQuery(el).summernote('editor.insertImage', url);
    //     }
    // });
  }

  /**
   * Clique no Campo Editor
   * Document Ready my_fields
   */
  jQuery(".note-editor .dropdown-toggle").on("click", (e) => {
    if (jQuery(e.currentTarget).attr("aria-expanded")) {
      jQuery(e.currentTarget).dropdown("toggle");
      jQuery(e.currentTarget.nextElementSibling).toggleClass("show");
    }
  });

  /**
   * Clique no Menu
   * Mostra ou oculta elemento pelo clique
   * Document Ready my_fields
   */
  jQuery(".dropdown-menu").on("click", (e) => {
    jQuery(e.currentTarget).toggleClass("show");
  });

  /**
   * Show Password
   * Mostra ou oculta a Senha
   * Document Ready my_fields
   */
  jQuery(".show_password").hover(function (e) {
    e.preventDefault();
    field = this.getAttribute("data-field");
    alvo = document.getElementById(field);
    if (jQuery(alvo).attr("type") == "password") {
      jQuery(alvo).attr("type", "text");
      jQuery("#ada_" + fields).attr("class", "fa fa-eye");
      // jQuery('#ada_'+ fields).removeClass('bi bi-eye-slash-fill');
      // jQuery('#ada_'+ fields).addClass('bi bi-eye');
    } else {
      jQuery(alvo).attr("type", "password");
      jQuery("#ada_" + fields).attr("class", "fa fa-eye-slash");
      // jQuery('#ada_'+ fields).removeClass('bi bi-eye');
      // jQuery('#ada_'+ fields).addClass('bi bi-eye-slash-fill');
    }
  });

  /**
   * Clique upnumber
   * Incrementa o campo number
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".up-num", (event) => {
    event.preventDefault();
    id = event.currentTarget.getAttribute("data-refer");
    inpnumb = document.getElementById(id);
    if (!inpnumb.readOnly) {
      jQuery("#form1").attr("data-alter", true);
      step = inpnumb.step ? parseInt(inpnumb.step) : 1;
      maximo = inpnumb.max ? parseInt(inpnumb.max) : 100000;
      valororig = inpnumb.value ? parseInt(inpnumb.value) : 0;
      if (valororig < maximo) {
        valor = valororig + step;
        jQuery(inpnumb).val(valor);
      }
      event.stopImmediatePropagation();
      jQuery(inpnumb).trigger("change");
    }
  });

  /**
   * Clique downnumber
   * Decrementa o campo number
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".down-num", (event) => {
    event.preventDefault();
    id = event.currentTarget.getAttribute("data-refer");
    inpnumb = document.getElementById(id);
    if (!inpnumb.readOnly) {
      jQuery("#form1").attr("data-alter", true);
      step = inpnumb.step ? parseInt(inpnumb.step) : 1;
      minimo = inpnumb.min ? parseInt(inpnumb.min) : 0;
      valororig = inpnumb.value ? parseInt(inpnumb.value) : 0;
      if (valororig > minimo) {
        valor = valororig - step;
        jQuery(inpnumb).val(valor);
      }
      event.stopImmediatePropagation();
      jQuery(inpnumb).trigger("change");
    }
  });

  /**
   * Clique CheckBox
   * Coloca ou tira a classe Checked no checkbox
   * Document Ready my_fields
   */
  jQuery('input[type="checkbox"]').click(function () {
    var id = jQuery(this).attr("id");
    var opcao = id.substr(0, id.indexOf("["));
    if (opcao.substr(0, 3) != "pit") {
      return;
    }
    var checked = jQuery(this).prop("checked");
    mod = id.match(/[0-9]+/g)[0];
    cla = id.match(/[0-9]+/g)[1];
    if (cla == 0) {
      // todas as classes
      if (opcao == "pit_all") {
        jQuery("input[id^='pit_consulta[" + mod + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_adicao[" + mod + "]']").prop("checked", checked);
        jQuery("input[id^='pit_edicao[" + mod + "]']").prop("checked", checked);
        jQuery("input[id^='pit_exclusao[" + mod + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_notifica[" + mod + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_all[" + mod + "]']").prop("checked", checked);
      } else {
        jQuery("input[id^='" + opcao + "[" + mod + "]']").prop(
          "checked",
          checked
        );
        if (
          opcao == "pit_consulta" &&
          jQuery("input[id^='pit_consulta[" + mod + "]']").prop("checked") ==
            false
        ) {
          jQuery("input[id^='pit_adicao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_edicao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_exclusao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_notifica[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_all[" + mod + "]']").prop("checked", checked);
        } else {
          if (checked == true) {
            jQuery("input[id^='pit_consulta[" + mod + "]']").prop(
              "checked",
              checked
            );
          }
        }
      }
    } else {
      var opcao = id.substr(0, id.indexOf("["));
      if (opcao == "pit_all") {
        jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_notifica[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
      } else {
        jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        if (opcao == "pit_consulta" && checked == false) {
          jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_notifica[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
        } else {
          if (checked == true) {
            jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
              "checked",
              checked
            );
          }
        }
        if (
          jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_notifica[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
            "checked" == false
          )
        ) {
          jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
            "checked",
            false
          );
        }
      }
      if (checked == false) {
        jQuery("input[id^='" + opcao + "[" + mod + "][0]']").prop(
          "checked",
          checked
        );
      }
    }
  });

  /**
   * Clique 2opcoes
   * Coloca ou tira a classe Checked no checkbox
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".duasOpcoes", function (event) {
    // jQuery('.duasOpcoes').on('click', function (event) {
    event.preventDefault();
    event.stopPropagation();
    obj = this;
    input = false;
    if (this.localName == "div") {
      obj = this.children[0].children[0];
    } else if (this.localName == "label") {
      objfor = this.getAttribute("for");
      objfor = objfor.replaceAll("[", "\\[");
      objfor = objfor.replaceAll("]", "\\]");
      obj = jQuery("#" + objfor)[0];
    } else if (this.localName == "input") {
      objfor = obj.id.replaceAll("[", "\\[");
      objfor = objfor.replaceAll("]", "\\]");
      obj = jQuery("#" + objfor)[0];
      input = true;
    }
    obj.setAttribute("data-alter", true);
    jQuery("#form1").attr("data-alter", true);

    ordem = obj.getAttribute("data-index");
    outro = 0;
    if (ordem == 0) {
      outro = 1;
    }
    var radio = obj.id.substr(0, obj.id.lastIndexOf("["));
    radio = radio.replaceAll("[", "\\[");
    radio = radio.replaceAll("]", "\\]");
    jQuery(obj).parents().eq(1).addClass("d-none");
    jQuery("#" + radio + "\\[" + outro + "\\]")
      .parents()
      .eq(1)
      .removeClass("d-none");
    if (!input) {
      jQuery(obj).removeAttr("checked");
      obj.checked == false;
      jQuery("#" + radio + "\\[" + outro + "\\]").attr("checked", "checked");
    }
    if (obj.getAttribute("onchange") != "") {
      jQuery(obj).trigger("change");
    }
  });

  /**
   * Campo Select Dual
   * Se existir um campo Form dual, aplica o DualListBox do Bootstrap
   * Document Ready my_fields
   */
  if (jQuery(".form-dual")[0]) {
    jQuery(".form-dual").bootstrapDualListbox({});
  }

  /**
   * Campo Icone
   * Se existir um campo Icone, aplica o inconPicker
   * Document Ready my_fields
   */
  if (jQuery(".icone")[0]) {
    jQuery(".icone").iconpicker();
  }

  /**
   * Disable Option Select
   * desabilita a opção do Select, se o valor for -1
   * Document Ready my_fields
   */
  if (jQuery("select")[0]) {
    jQuery("select option").each(function () {
      var value = this.value;
      if (value == "-1") {
        jQuery(this).prop("readonly", "true");
      }
      if (value == this.parentElement.getAttribute("data-selec")) {
        jQuery(this.parentElement).selectpicker("val", value);
      }
    });
    jQuery("select").selectpicker();
  }

  /**
   * Campo Selpic
   * Se existir um campo selpic, aplica a Select Picker
   * Document Ready my_fields
   */
  // if (jQuery(".selbusca")[0]){
  jQuery(".selbusca").on("keydown", function () {
    elemen = jQuery(this)[0].children[0];
    if (elemen.tagName == "SELECT") {
      busca = elemen.getAttribute("data-busca");
      jQuery(elemen).selectpicker({
        source: {
          data: function (callback) {
            jQuery.ajax({
              type: "POST",
              async: true,
              dataType: "json",
              url: busca,
              success: function (retornoAjax) {
                callback(retornoAjax);
              },
            });
          },
          search: function (callback, page, searchTerm) {
            let data = { page, busca: searchTerm };
            jQuery.ajax({
              type: "POST",
              async: true,
              dataType: "json",
              url: busca,
              data: data,
              success: function (retornoAjax) {
                jQuery(elemen).empty();
                retornoAjax.forEach(function (item) {
                  jQuery(elemen).append(
                    jQuery("<option>", {
                      value: item.id,
                      text: item.text,
                    })
                  );
                });
                const options = retornoAjax.map((item) => ({
                  value: item.id, // ou item.valor
                  text: item.text, // ou item.descricao
                }));
                callback(options);
              },
            });
          },
        },
      });
    }
  });

  /**
   * Campo Select Dependente
   * Se existir um campo dependente, adiciona o onchange no elemento pai
   * Document Ready my_fields
   */
  jQuery(".dependente").each(function () {
    elemen = jQuery(this)[0];
    if (elemen.tagName == "SELECT") {
      busca = elemen.getAttribute("data-busca");
      valor = elemen.getAttribute("data-valor");
      funcao_busca =
        "busca_dependente(this,'" +
        elemen.name +
        "','" +
        busca +
        "','" +
        valor +
        "')";
      pai = elemen.getAttribute("data-pai");
      _chan_ant = jQuery('[name="' + pai + '"]').attr("onchange");
      if (
        _chan_ant != "" &&
        _chan_ant != undefined &&
        _chan_ant.substr(0, 16) != "busca_dependente"
      ) {
        jQuery('[name="' + pai + '"]').attr(
          "onchange",
          _chan_ant + ";" + funcao_busca
        );
      } else {
        jQuery('[name="' + pai + '"]').attr("onchange", funcao_busca);
      }
      jQuery('[name="' + pai + '"]').trigger("change");
    }
  });

  /**
   * Campo Select Desabilitado
   * Desabilita as opções com valor vazio do Select, se o Select for obrigatório
   * Document Ready my_fields
   */
  jQuery("select:required option[value='']").attr("disabled", "disabled");
  /**
   * Contador de caracteres digitados
   * Mostra quantos caracteres já foram digitados e qual o total de Caracteres aceitos
   *
   */
  jQuery("input, textarea").on("keyup", function () {
    var id = jQuery(this)[0].id;
    id = id.replace("[", "\\[");
    id = id.replace("]", "\\]");
    var tam = jQuery(this)[0].maxLength;
    var dig = jQuery(this)[0].value.length;
    tdiv = dig + "/" + tam;
    jQuery("#dc-" + id).removeClass("acabou");
    jQuery("#dc-" + id).html(tdiv);
    if (tam == dig) {
      tdiv = "Limite atingido => " + tdiv;
      jQuery("#dc-" + id).html(tdiv);
      jQuery("#dc-" + id).addClass("acabou");
    }
    console.log("Tamanho " + tam);
    console.log("Digitado " + dig);
  });

  /**
   * Limpa o contador de Caracteres
   * na saída do input limpa o contador e oculta
   *
   */
  jQuery("input:text, textarea").on("blur", function () {
    var id = jQuery(this)[0].id;
    id = id.replace("[", "\\[");
    id = id.replace("]", "\\]");
    jQuery("#dc-" + id).html("");
  });

  // VERIFICA SE O CAMPO SOFREU ALTERNATES E CASO POSITIVO, ALTERA A VARIAVE data-alter PARA VERDADEIRO
  // ISSO PODE SER USADO NA SAÍDA DO FORMULÁRIO, PARA TESTAR SE HOUVE ALTERAÇÕES NOS DADOS
  jQuery("body").on("keyup change", "input,select,textarea", function (event) {
    valorigem = this.getAttribute("data-valor");
    if (this.tagName == "SELECT") {
      valorigem = this.getAttribute("data-selec");
    }
    if (valorigem != jQuery(this).val().toString()) {
      this.setAttribute("data-alter", true);
      jQuery("#form1").attr("data-alter", true);
      console.log("Alterou");
    }
    if (this.validity.valid) {
      nid = jQuery(this)[0].id;
      jQuery("[id='" + nid + "-fival']").removeClass("d-block");
      jQuery("[id='" + nid + "-fival']").addClass("d-none");
      tab = jQuery(this)[0].closest(".tab-pane").id;
      jQuery("[id='" + tab + "-valid']").removeClass("d-block");
    }
  });

  jQuery("select").selectpicker();
}

function oculta_passinfo() {
  jQuery("#pass-info").hide();
}

/**
 * mostraOcultaCampo
 * Mostra ou Oculta os Campos conforme a regra
 * @param {object} obj - objeto a ser testado na regra
 * @param {string} regra - regra para ocultar os campos
 * @param {string} fields - campos que serão ocultados
 */
function mostraOcultaCampo(obj, regra, fields) {
  if (typeof obj === "object" && obj !== null) {
    nomecampo = obj.name;
  } else {
    nomecampo = obj;
  }
  nomecampo = nomecampo.replaceAll("[", "\\[");
  nomecampo = nomecampo.replaceAll("]", "\\]");
  valor = jQuery('input[name="' + nomecampo + '"]:checked').val();
  campos = fields.split(",");
  if (valor == regra) {
    jQuery.each(campos, function (key, value) {
      value = value.replaceAll("[", "\\[");
      value = value.replaceAll("]", "\\]");
      div = "#ig_" + value;
      camp = "#" + value;
      if (
        jQuery(camp).is("button") ||
        jQuery(camp).is("input[type='button']")
      ) {
        if (jQuery(camp).hasClass("d-none")) {
          jQuery(camp).removeClass("d-none");
        }
      } else {
        jQuery(div).removeClass("opacity-0");
        jQuery(div).addClass("opacity-1");
        jQuery(div).css("height", "");
        obriga = jQuery("[name='" + value + "']").attr("data-obrig");
        if (obriga == "required") {
          jQuery(camp).attr("required", "required");
        }
        jQuery(camp).removeClass("d-none");
        if (jQuery("[name='" + value + "']").is("select")) {
          if (
            jQuery("[name='" + value + "']")[0].value == "" ||
            jQuery("[name='" + value + "']")[0].value == "-1"
          ) {
            if (
              jQuery("[name='" + value + "']").attr("data-default") != undefined
            ) {
              valor = jQuery("[name='" + value + "']").attr("data-default");
              jQuery(camp).selectpicker("val", valor);
            }
          }
        }
      }
    });
  } else {
    jQuery.each(campos, function (key, value) {
      value = value.replaceAll("[", "\\[");
      value = value.replaceAll("]", "\\]");
      div = "#ig_" + value;
      camp = "#" + value;
      if (
        jQuery(camp).is("button") ||
        jQuery(camp).is("input[type='button']")
      ) {
        if (!jQuery(camp).hasClass("d-none")) {
          jQuery(camp).addClass("d-none");
        }
      } else {
        jQuery(camp).val("");
        if (jQuery("[name='" + value + "']").is("select")) {
          valor = "";
          if (
            jQuery("[name='" + value + "']").attr("data-default") != undefined
          ) {
            valor = jQuery("[name='" + value + "']").attr("data-default");
          }
          jQuery(camp).selectpicker("val", valor);
        }
        jQuery(camp).addClass("d-none");
        jQuery(camp).removeAttr("required");
        jQuery(div).removeClass("opacity-1");
        jQuery(div).addClass("opacity-0");
        jQuery(div).css("height", "0px");
      }
    });
  }
}

/**
 * mostraOcultaCampoTodos
 * Mostra ou Oculta os Campos conforme a regra
 * @param {object} obj - objeto a ser testado na regra
 * @param {string} regra - regra para ocultar os campos
 * @param {string} fields - campos que serão ocultados
 */
function mostraOcultaCampoTodos(nomecampo, regra, fields) {
  campos = fields.split(",");
  jQuery("input[name^='" + nomecampo + "']:checked").each(function (
    index,
    elem
  ) {
    var valor = jQuery(elem).val();
    // jQuery("input[name^='" + nomecampo + "']").each(function (indexInArray, valueOfElement) {
    //     var valor = jQuery('input[name="' + nomecampo+ '"]:checked').val();
    //     if (this.checked) {
    //         valor = this.value;
    final = this.name.substr(this.name.indexOf("["));
    if (valor == regra) {
      jQuery.each(campos, function (key, value) {
        // MOSTRA
        campox = value + final;
        value = escIdColchetes(campox);
        // value = campox.replaceAll('[', '\\[');
        // value = value.replaceAll(']', '\\]');
        div = "#ig_" + value;
        camp = "#" + value;
        jQuery(div).removeClass("opacity-0");
        jQuery(div).addClass("opacity-1");
        jQuery(div).css("height", "");
        obriga = jQuery("[name='" + value + "']").attr("data-obrig");
        if (obriga == "required") {
          jQuery(camp).attr("required", "required");
        }
        if (jQuery("[name='" + value + "']").is("select")) {
          if (
            jQuery("[name='" + value + "']").attr("data-default") != undefined
          ) {
            valor = jQuery("[name='" + value + "']").attr("data-default");
            jQuery(camp).selectpicker("val", valor);
          }
        }
      });
    } else {
      jQuery.each(campos, function (key, value) {
        // OCULTA
        campox = value + final;
        value = escIdColchetes(campox);
        // value = campox.replaceAll('[', '\\[');
        // value = value.replaceAll(']', '\\]');
        div = "#ig_" + value;
        camp = "#" + value;
        jQuery(camp).val("");
        if (jQuery("[name='" + value + "']").is("select")) {
          valor = "";
          if (
            jQuery("[name='" + value + "']").attr("data-default") != undefined
          ) {
            valor = jQuery("[name='" + value + "']").attr("data-default");
          }
          jQuery(camp).selectpicker("val", valor);
        }
        jQuery(camp).removeAttr("required");
        jQuery(div).removeClass("opacity-1");
        jQuery(div).addClass("opacity-0");
        jQuery(div).css("height", "0px");
      });
    }
    // }
  });
}

/**
 * mostraOcultaDiv
 * Mostra ou Oculta as Divs conforme a regra
 * @param {object} obj - objeto a ser testado na regra
 * @param {string} regra - regra para ocultar as divs
 * @param {string} divs - divs que serão ocultados
 */
function mostraOcultaDiv(obj, regra, divs) {
  if (typeof obj === "object" && obj !== null) {
    nomecampo = obj.name;
  } else {
    nomecampo = obj;
  }
  nomecampo = nomecampo.replaceAll("[", "\\[");
  nomecampo = nomecampo.replaceAll("]", "\\]");
  valor = jQuery('input[name="' + nomecampo + '"]:checked').val();
  divs = divs.split(",");
  if (valor == regra) {
    jQuery.each(divs, function (key, value) {
      value = value.replaceAll("[", "\\[");
      value = value.replaceAll("]", "\\]");
      div = "#" + value;
      jQuery(div).removeClass("d-none");
      // jQuery(div).addClass('opacity-1');
      // jQuery(div).css('height', '');
    });
  } else {
    jQuery.each(divs, function (key, value) {
      value = value.replaceAll("[", "\\[");
      value = value.replaceAll("]", "\\]");
      div = "#" + value;
      jQuery(div).addClass("d-none");
      // jQuery(div).addClass('opacity-0');
      // jQuery(div).css('height', '0px');
    });
  }
}

/**
 * mostraOcultaDivTodos
 * Mostra ou Oculta as Divs conforme a regra
 * @param {object} obj - objeto a ser testado na regra
 * @param {string} regra - regra para ocultar as divs
 * @param {string} divs - divs que serão ocultados
 */
function mostraOcultaDivTodos(nomecampo, regra, divs) {
  divs = divs.split(",");
  jQuery("input[name^='" + nomecampo + "']").each(function (
    indexInArray,
    valueOfElement
  ) {
    if (this.checked) {
      valor = this.value;
      final = this.name.substr(this.name.indexOf("["));
      if (valor == regra) {
        jQuery.each(divs, function (key, value) {
          campox = value + final;
          value = campox.replaceAll("[", "\\[");
          value = value.replaceAll("]", "\\]");
          div = "#" + value;
          jQuery(div).removeClass("d-none");
        });
      } else {
        jQuery.each(divs, function (key, value) {
          campox = value + final;
          value = campox.replaceAll("[", "\\[");
          value = value.replaceAll("]", "\\]");
          div = "#" + value;
          jQuery(div).addClass("d-none");
        });
      }
    }
  });
}

/**
 * buscar
 * Cria a caixa de listagem, com os resultados da busca do campo "selbusca"
 * @param {string} url - URL de pesquisa
 * @param {object} obj - Campo de Busca
 * @param {string} lista - Lista de Opções do Objeto
 */
function buscar(url, obj, lista) {
  var busca = jQuery(obj).val();
  if (busca.length >= 3) {
    jQuery("#dd_" + lista).empty();
    jQuery("#dd_" + lista).append(
      "<li><h6 class='dropdown-header disabled'>Buscando...</h6></li>"
    );
    jQuery("#dd_" + lista).css({
      position: "absolute",
      inset: "0px auto auto 0px",
      margin: "0px",
      transform: "translate(0px, 40px)",
    });
    jQuery("#dd_" + lista).show("slow");
    retornoAjax = false;
    dados = { busca: busca };
    executaAjax(url, "json", dados);
    if (retornoAjax) {
      jQuery("#dd_" + lista).empty();
      jQuery.each(retornoAjax, function (i, item) {
        jQuery("#dd_" + lista).append(
          "<li><a class='dropdown-item' onclick='seleciona_item(\"" +
            item.id +
            '","' +
            item.text +
            '","' +
            lista +
            "\")'>" +
            item.text +
            "</a></li>"
        );
      });
    }
  } else {
    jQuery("#dd_" + lista).empty();
  }
}

/**
 * seleciona_item
 * trata a seleção de um ítem de um campo do tipo "selbusca"
 * @param {string} id - id do item selecionado
 * @param {string} texto - Texto do item selecionado
 * @param {object} obj - Campo de Busca
 */
function seleciona_item(id, texto, obj) {
  jQuery("#dd_" + obj).css({
    position: "",
    inset: "",
    margin: "",
    transform: "",
  });
  jQuery("#dd_" + obj).hide("slow");
  jQuery("#bus_" + obj).val(texto);
  jQuery("#" + obj).val(id);
  jQuery("#" + obj).trigger("change");
}

/**
 * exclui_campo
 * Exclui um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser excluído
 */

function exclui_campo(objdest, obj) {
  jQuery(obj).closest(".row").remove();
  // indice = parseInt(obj.getAttribute('data-index'));
  // jQuery(obj).parents().eq(2).remove()
  jQuery("#form1").attr("data-alter", true);
  acerta_botoes_rep(objdest);
}

/**
 * addCampo
 * add um campo, ou uma lista de campos, para adição de ítens
 * @param {string} url - url de criação dos campos
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido
 */
function addCampo(url, objdest, obj) {
  atual = parseInt(obj.getAttribute("data-index"));
  secao = jQuery(obj).parents().eq(5)[0].id;
  proximo = atual + 1;
  jQuery(".bt-exclui").each(function (i) {
    if (parseInt(this.getAttribute("data-index")) > proximo) {
      proximo = parseInt(this.getAttribute("data-index"));
    }
  });
  let data = { ind: proximo };
  url = url + "/" + proximo;
  retornoAjax = false;
  executaAjax(url, "json");
  if (retornoAjax) {
    ctador = retornoAjax.length;
    text =
      "<div class='row tableDiv table2 mb-4 table-" +
      objdest +
      "' width='100 % ' data-index=" +
      proximo +
      " >";
    text += "<div class='col-11'>";
    // text = '<table class="table2 table-sm" data-index="' + proximo + '"><tbody><tr>';
    indice = 0;
    for (const ind in retornoAjax) {
      if (ind < ctador - 2) {
        quebra = retornoAjax[ind].indexOf("quebralinha");
        oculto = retornoAjax[ind].indexOf("hidden");
        text += retornoAjax[ind];
      }
    }
    text += "</div>";
    text += "<div class='col-1 d-initial h-auto p-0'>";
    text += "<div class='col-9 d-block float-start text-center p-0'>";
    text += retornoAjax[ctador - 2];
    text += retornoAjax[ctador - 1];
    text += "</div>";
    text += "<div class='col-3 d-block float-end text-end'>";
    text +=
      "<button name='bt_up[" +
      proximo +
      "]' type='button' id='bt_up[" +
      proximo +
      "]' class='btn btn-outline-info btn-sm bt-up mt-0 float-end' onclick='sobe_desce_item(this,\"sobe\",\"" +
      objdest +
      "\")' title='Acima' data-index='" +
      proximo +
      "'><i class='fa fa-arrow-up' aria-hidden='true'></i></button>";
    text +=
      "<button name='bt_down[" +
      proximo +
      "]' type='button' id='bt_down[" +
      proximo +
      "]' class='btn btn-outline-info btn-sm bt-down mt-0 float-end' onclick='sobe_desce_item(this,\"desce\",\"" +
      objdest +
      "\")' title='Abaixo' data-index='" +
      proximo +
      "'><i class='fa fa-arrow-down' aria-hidden='true'></i></button>";
    text += "</div>";
    text += "</div>";
    text += "</div>";
    jQuery("#rep_" + objdest).append(text);
    const divSelector = "#rep_" + objdest;

    // Encontre todos os <select>s dentro da div e aplique o plugin
    jQuery(divSelector)
      .find("select")
      .each(function () {
        jQuery(this).selectpicker(); // Substitua "seuPlugin" pelo nome do plugin
      });

    /**
     * Campo Select Dependente
     * Se existir um campo dependente, adiciona o onchange no elemento pai
     * Document Ready my_fields
     */
    jQuery(".dependente").each(function () {
      elemen = jQuery(this)[0];
      if (elemen.tagName == "SELECT") {
        busca = elemen.getAttribute("data-busca");
        valor = elemen.getAttribute("data-valor");
        funcao_busca =
          "busca_dependente(this,'" +
          elemen.name +
          "','" +
          busca +
          "','" +
          valor +
          "')";
        pai = elemen.getAttribute("data-pai");
        _chan_ant = jQuery('[name="' + pai + '"]').attr("onchange");
        if (
          _chan_ant != "" &&
          _chan_ant != undefined &&
          _chan_ant.substr(0, 16) != "busca_dependente"
        ) {
          jQuery('[name="' + pai + '"]').attr(
            "onchange",
            _chan_ant + ";" + funcao_busca
          );
        } else {
          jQuery('[name="' + pai + '"]').attr("onchange", funcao_busca);
        }
        jQuery('[name="' + pai + '"]').trigger("change");
      }
    });

    // carregamentos_iniciais();
    acerta_botoes_rep(objdest);
    if (typeof acertaOcultos === "function") {
      acertaOcultos();
    }
  }
  // jQuery.ajax({
  //     type: 'POST',
  //     async: true,
  //     dataType: 'json',
  //     url: url + '/' + proximo,
  //     success: function (retorno) {
  //         text = "<div class='row tableDiv table2 table-" + objdest + "' width='100 % ' data-index=" + proximo + " >";
  //         text += "<div class='col-11'>";
  //         // text = '<table class="table2 table-sm" data-index="' + proximo + '"><tbody><tr>';
  //         indice = 0;
  //         for (const ind in retorno) {
  //             if (ind < retorno.length - 2) {
  //                 quebra = retorno[ind].indexOf("quebralinha");
  //                 oculto = retorno[ind].indexOf("hidden");
  //                 text += retorno[ind];
  //             }
  //         }
  //         text += "</div>";
  //         text += "<div class='col-1 d-initial h-auto p-0'>";
  //         text += "<div class='col-9 d-block float-start text-center p-0'>";
  //         text += retorno[retorno.length - 2];
  //         text += retorno[retorno.length - 1];
  //         text += '</div>';
  //         text += "<div class='col-3 d-block float-end text-end'>";
  //         text += "<button name='bt_up[" + proximo + "]' type='button' id='bt_up[" + proximo + "]' class='btn btn-outline-info btn-sm bt-up mt-0 float-end' onclick='sobe_desce_item(this,\"sobe\",\"" + objdest + "\")' title='Acima' data-index='" + proximo + "'><i class='fa fa-arrow-up' aria-hidden='true'></i></button>";
  //         text += "<button name='bt_down[" + proximo + "]' type='button' id='bt_down[" + proximo + "]' class='btn btn-outline-info btn-sm bt-down mt-0 float-end' onclick='sobe_desce_item(this,\"desce\",\"" + objdest + "\")' title='Abaixo' data-index='" + proximo + "'><i class='fa fa-arrow-down' aria-hidden='true'></i></button>";
  //         text += '</div>';
  //         text += '</div>';
  //         text += '</div>';
  //         jQuery('#rep_' + objdest).append(text);
  //         jQuery('select').selectpicker();
  //         carregamentos_iniciais();
  //         acerta_botoes_rep(objdest);
  //     }
  // });
}

/**
 * repete_campo
 * repete um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido
 */
function repete_campo(objdest, obj) {
  var objrep = obj.parentElement;
  var objpre = objdest.substr(0, 3);

  atual = parseInt(obj.getAttribute("data-index"));

  proximo = atual + 1;
  jQuery(".bt-exclui").each(function (i) {
    if (parseInt(this.getAttribute("data-index")) > proximo) {
      proximo = parseInt(this.getAttribute("data-index"));
    }
  });

  var $template = jQuery("*[data-" + objdest + '-index="0"]');
  var $pai = $template[0].parentElement;
  $clone = $template
    .clone()
    .attr("data-" + objdest + "-index", proximo)
    .appendTo($pai);

  jQuery("*[data-" + objdest + '-index="' + proximo + '"]').removeClass(
    "d-none"
  );
  // Update the name attributes
  jQuery("*[data-" + objdest + '-index="' + proximo + '"]').each(function (
    i,
    t
  ) {
    var primeiro = true;
    jQuery(this)
      .find('[id*="[' + atual + ']"]')
      .each(function () {
        if (!jQuery(this).hasClass("form-check-input")) {
          jQuery(this).val("");
        }

        $nome = jQuery(this)[0].name;
        if ($nome == undefined || $nome == "") {
          $nome = jQuery(this).attr("id");
        }
        if ($nome != undefined) {
          pos = $nome.indexOf("__");
          ini = $nome.substr(0, $nome.indexOf("__"));
          fim = $nome.substr(pos + 3);
          jQuery(this).attr("name", ini + "__" + proximo + fim);
          jQuery(this).attr("id", ini + "__" + proximo + fim);
        }
        var tipo = jQuery(this)[0].type;
        if (primeiro && tipo != "hidden" && tipo != undefined) {
          jQuery("#" + ini + "__" + proximo + fim).focus();
          jQuery("#" + ini + "__" + proximo + fim).trigger("click");
          primeiro = false;
        }

        // Atualiza o evento onchange
        // Ocorre quando o campo é alterado
        var texto = jQuery(this).attr("onchange");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onchange", novotexto);
          }
        }
        // Atualiza a tag for (refere a label)
        var texto = jQuery(this).attr("for");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("for", novotexto);
          }
        }
        // Atualiza o evento onfocus
        // Ocorre quando o campo recebe o foco
        var texto = jQuery(this).attr("onfocus");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onfocus", novotexto);
          }
        }
        // Atualiza o evento onkeyup
        // Ocorre quando a Tecla pressionada foi solta
        // Ultimo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeyup");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onkeyup", novotexto);
          }
        }
        // Atualiza o evento onkeydown
        // Ocorre no momento que uma Tecla é pressionada
        // Primeiro evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeydown");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onkeydown", novotexto);
          }
        }
        // Atualiza o evento onkeypress
        // Ocorre depos do Pressionamento de uma Tecla
        // Segundo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeypress");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onkeypress", novotexto);
          }
        }
        // Atualiza o evento onblur
        // Ocorre quando o campo perde o foco
        var texto = jQuery(this).attr("onblur");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onblur", novotexto);
          }
        }
        // Atualiza o atributo data-retorno
        var texto = jQuery(this).attr("data-retorno");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-retorno", novotexto);
          }
        }
        // Atualiza o atributo data-refer
        var texto = jQuery(this).attr("data-refer");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-refer", novotexto);
          }
        }
        // Atualiza o atributo data-index
        var texto = jQuery(this).attr("data-index");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > -1) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-index", novotexto);
          }
        }
      });
  });
  acerta_botoes_rep();
  return;
}

/**
 * sobe_desce_item
 * Sobe ou desce um ítem da lista
 * @param {object} obj - Campo que deverá ser repetido
 * @param {integer} sobedesce - Sobe 0, Desce 1
 */
function sobe_desce_item(obj, sobedesce, repete, indtab = -1) {
  fim = 999;
  atual = parseInt(obj.getAttribute("data-index"));
  origem = "";
  destino = "";
  if (sobedesce == "sobe") {
    // sobe
    nova_pos = atual - 1;
  } else {
    // desce
    nova_pos = atual + 1;
  }
  repetetab = repete;
  if (indtab >= 0) {
    repetetab += "\\[" + indtab + "\\]";
  }
  jQuery("#form1").attr("data-alter", true);

  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // PRIMEIRO O ELEMENTO Q VAI DEIXAR DE SER É ALTERADO PARA 999
    if (parseInt(this.getAttribute("data-index")) == nova_pos) {
      jQuery(this).attr("data-index", fim);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, nova_pos, fim);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, nova_pos, fim);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, nova_pos, fim);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, nova_pos, fim);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, nova_pos, fim);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, nova_pos, fim);
        }
        // });
      });
    }
  });
  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // MUDA O ATUAL PARA A NOVA POSIÇÃO
    if (parseInt(this.getAttribute("data-index")) == atual) {
      jQuery(this).attr("data-index", nova_pos);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, atual, nova_pos);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, atual, nova_pos);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, atual, nova_pos);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, atual, nova_pos);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, atual, nova_pos);
        }
        var botoes = jQuery(this).find("button[data-index*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, atual, nova_pos);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, atual, nova_pos);
        }
        // });
      });
      destino = this;
    }
  });
  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // volta o 999 para a posição do Atual
    if (parseInt(this.getAttribute("data-index")) == fim) {
      jQuery(this).attr("data-index", atual);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, fim, atual);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, fim, atual);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, fim, atual);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, fim, atual);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, fim, atual);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, fim, atual);
        }
        // });
      });
      origem = this;
    }
  });
  jQuery(origem).swap(destino);
  acerta_botoes_rep(repete, indtab);
}

(function (jQuery) {
  jQuery.fn.swap = function (anotherElement) {
    var a = jQuery(this).get(0);
    var b = jQuery(anotherElement).get(0);
    var swap = document.createElement("span");
    a.parentNode.insertBefore(swap, a);
    b.parentNode.insertBefore(a, b);
    swap.parentNode.insertBefore(b, swap);
    swap.remove();
  };
})(jQuery);

function altera_index(obj, ind_a, ind_n) {
  i_ant = "[" + ind_a + "]";
  console.log("I Antes " + i_ant);
  i_dep = "[" + ind_n + "]";
  console.log("I Depois " + i_dep);
  for (i = 0; i < obj.length; i++) {
    if (obj[i].getAttribute("for") != undefined) {
      id_antes = obj[i].getAttribute("for").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(i_ant, i_dep);
      console.log("Depois " + id_depois);
      obj[i].htmlFor = id_depois;
    } else if (obj[i].getAttribute("data-id") != undefined) {
      id_antes = obj[i].getAttribute("data-id").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(i_ant, i_dep);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-id", id_depois.toString());
    } else if (obj[i].getAttribute("data-index") != undefined) {
      id_antes = obj[i].getAttribute("data-index").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(ind_a, ind_n);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-index", id_depois.toString());
    } else if (obj[i].getAttribute("data-selec") != undefined) {
      id_antes = obj[i].getAttribute("data-selec").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(ind_a, ind_n);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-selec", id_depois.toString());
    }
    id_antes = obj[i].id.toString();
    console.log("Antes " + id_antes);
    id_depois = id_antes.replace(i_ant, i_dep);
    console.log("Depois " + id_depois);
    obj[i].id = id_depois;
    if (obj[i].name != undefined) {
      nm_antes = obj[i].name.toString();
      console.log("Antes " + nm_antes);
      nm_depois = nm_antes.replace(i_ant, i_dep);
      console.log("Depois " + nm_depois);
      obj[i].name = nm_depois;
      console.log("Nome Depois" + obj[i].name);
    }
  }
}

/**
 * acerta_botoes_rep
 * Acerta Botões de Adicionar e Excluir campos
 *
 */
function acerta_botoes_rep(repete, pos = -1) {
  repetepos = repete;
  if (pos >= 0) {
    repetepos += "\\[" + pos + "\\]";
  }
  visiveis = jQuery("#rep_" + repetepos + " .bt-repete").length;
  ultimo = visiveis - 1;

  jQuery("#rep_" + repetepos + " .bt-repete").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-exclui").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-up").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-down").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-repete").addClass("d-none");

  if (visiveis == 1) {
    // quando tem só 1, não pode excluir
    jQuery("#rep_" + repetepos + " .bt-exclui").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-up").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-down").addClass("d-none");
  }
  // o botão de Adicionar só aparece no último
  jQuery(jQuery("#rep_" + repetepos + " .bt-repete")[ultimo]).removeClass(
    "d-none"
  );
  jQuery(jQuery("#rep_" + repetepos + " .bt-up")[0]).addClass("d-none");
  jQuery(jQuery("#rep_" + repetepos + " .bt-down")[ultimo]).addClass("d-none");
}

/**
 * testa_dep
 * trata se o campo pai, de um dependente, está preenchido
 * Caso não esteja, muda o foco para o campo pai
 * @param {object} id_dep - Campo Pai do Dependente
 */
function testa_dep(id_dep) {
  var nodes = document.getElementById(id_dep);
  var jqObj = jQuery(nodes);
  if (parseInt(jQuery(jqObj).val()) < 1) {
    jQuery(jqObj).focus();
  }
}

/**
 * busca_dependente
 * Preenche a lista de opções de um campo dependente, conforme a seleção do campo pai
 * @param {object} obj - campo pai
 * @param {object} id_dep - campo dependente
 * @param {url} url_busca - URL de busca de dependentes
 * @param {integer} selec - Dependente pré-selecionado
 */
function busca_dependente(obj, id_dep, url_busca, selec) {
  id_dep = id_dep.replace("[", "\\[");
  id_dep = id_dep.replace("]", "\\]");
  if (selec == "") {
    if (jQuery("#" + id_dep).data("valor")) {
      selec = jQuery("#" + id_dep).getAttribute("data-valor");
    } else {
      selec = jQuery("#" + id_dep).val();
    }
  }
  if (parseInt(jQuery(obj).val()) != -1) {
    var nodes = document.getElementById(id_dep);
    var jqObj = jQuery(nodes);

    var datarr = new Array();
    datarr[0] = {};
    datarr[0].id_dep = jQuery(obj).val();

    dados = { busca: jQuery(obj).val() };
    retornoAjax = false;
    executaAjax(url_busca, "json", dados);
    if (retornoAjax) {
      console.log(retornoAjax);
      arr_ret = [];
      jQuery.each(retornoAjax, function (key, value) {
        arr_ret[key] = value;
      });
      arr_ret.sort(function (a, b) {
        return a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : 0;
      });
      console.log(arr_ret);

      jQuery('[name="' + id_dep + '"]')
        .children("option")
        .remove();
      jQuery.each(retornoAjax, function (key, value) {
        if (Array.isArray(selec)) {
          if (jQuery.inArray(value.id, selec) !== -1) {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option selected></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          } else if (jQuery.inArray(value.id, selec) === -10) {
            // DIVISOR
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option class='divider' data-divider='true'></option>")
                .attr("value", value.id)
                .text("")
            );
          } else {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          }
        } else {
          if (value.id == selec) {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option selected></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          } else if (value.id === -10) {
            // DIVISOR
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option class='divider' data-divider='true'></option>")
                .attr("value", value.id)
                .text("")
            );
          } else {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          }
        }
      });
      jQuery('[name="' + id_dep + '"]').selectpicker("destroy");
      jQuery('[name="' + id_dep + '"]').selectpicker("deselectAll");
      // jQuery.each(retorno, function (key, value) {
      //     if (value.id == selec || selec.indexOf(value.id) >= 0) {
      aSelec = selec;
      if (selec.indexOf(",") > 0) {
        aSelec = selec.split(",");
      }
      jQuery('[id="' + id_dep + '"]').selectpicker("val", aSelec);
    }
  } else {
    var nodes = document.getElementById(id_dep);
    var jqObj = jQuery(nodes);
    jQuery(jqObj).children("option").remove();
    jQuery(jqObj).append(
      jQuery("<option></option>")
        .attr("value", -1)
        .text(nodes.getAttribute("placeholder"))
    );
  }
}

/**
 * readURL
 * Le um arquivo de imagem local e mostra na tela
 * @param {object} input  - campo file
 * @param {object} id     - destino da imagem
 * @param {integer} largura - largura da Div onde será mostrada a imagem
 * @param {integer} altura - altura da Div onde será mostrada a imagem
 */
function readURL(input, id, largura, altura) {
  const tipo = {
    "": "/assets/uploads/tipo_arquivo/vazio.png",
    "application/x-zip-compressed": "/assets/uploads/tipo_arquivo/zip.png",
    "application/zip": "/assets/uploads/tipo_arquivo/zip.png",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
      "/assets/uploads/tipo_arquivo/xlsx.png",
    "application/xlsx": "/assets/uploads/tipo_arquivo/xlsx.png",
    "application/xls": "/assets/uploads/tipo_arquivo/xls.png",
    "application/txt": "/assets/uploads/tipo_arquivo/txt.png",
    "application/x-compressed": "/assets/uploads/tipo_arquivo/rar.png",
    "application/rar": "/assets/uploads/tipo_arquivo/rar.png",
    "application/psd": "/assets/uploads/tipo_arquivo/psd.png",
    "application/vnd.openxmlformats-officedocument.presentationml.presentation":
      "/assets/uploads/tipo_arquivo/pptx.png",
    "application/pptx": "/assets/uploads/tipo_arquivo/pptx.png",
    "application/ppt": "/assets/uploads/tipo_arquivo/ppt.png",
    "application/pdf": "/assets/uploads/tipo_arquivo/pdf.png",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
      "/assets/uploads/tipo_arquivo/docx.png",
    "application/docx": "/assets/uploads/tipo_arquivo/docx.png",
    "application/doc": "/assets/uploads/tipo_arquivo/doc.png",
    "application/postscript": "/assets/uploads/tipo_arquivo/ai.png",
    "application/x-vnd.corel.zcf.draw.document+zip":
      "/assets/uploads/tipo_arquivo/cdr.png",
    "application/cdr": "/assets/uploads/tipo_arquivo/cdr.png",
  };

  if (input.files && input.files[0]) {
    const file = input.files[0];

    // Verifica tamanho do arquivo
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
      boxAlert("O arquivo excede o tamanho máximo permitido de 10MB.", true);
      input.value = ""; // Cancela a seleção
      jQuery('img[id="' + id + '"]')
        .attr("src", tipo[""])
        .width(largura)
        .height(altura); // Reset imagem
      jQuery("#nome_arquivo_" + id).text(""); // Limpa nome do arquivo
      return;
    }

    // Exibe o nome do arquivo na div apropriada
    jQuery("#nome_arquivo_" + id).text(file.name);

    let tipoarq = file.type || "application/" + file.name.slice(-3);

    if (tipo[tipoarq]) {
      jQuery('img[id="' + id + '"]')
        .attr("src", tipo[tipoarq])
        .width(largura)
        .height(altura);
    } else {
      const reader = new FileReader();
      reader.onload = function (e) {
        jQuery('img[id="' + id + '"]')
          .attr("src", e.target.result)
          .width(largura)
          .height(altura);
      };
      reader.readAsDataURL(file);
    }
  }
}

/**
 * compara_senha
 * Compara senha e contra senha, para garantir a senha correta
 *
 * @param {*} contra - nome do campo da contra senha
 * @param {*} senha  - nome do campo da senha
 */
function compara_senha(contra, senha) {
  var contra_senha = jQuery("#" + contra).val();
  var nova_senha = jQuery("#" + senha).val();
  if (contra_senha != nova_senha) {
    jQuery("#msg_senha").html("<b>Senhas não conferem! REVISE!</b>");
    jQuery("#msg_senha").addClass("p-2 px-4");
    jQuery("#bt_salvar").attr("disabled", true);
  } else {
    jQuery("#msg_senha").html("");
    jQuery("#msg_senha").removeClass("p-2 px-4");
    jQuery("#bt_salvar").attr("disabled", false);
  }
}

/**
 * calcula_diferenca
 * Calcula a diferença entre 2 valores informados em campos lidos por jQuery
 *
 * @param {string} origem    - id do campo original
 * @param {string} informado - id do campo informado
 * @param {string} retorno   - id do campo de retorno
 */
function calcula_diferenca(origem, informado, retorno) {
  var orig = converteMoedaFloat(jQuery("#" + origem).val());
  var info = converteMoedaFloat(jQuery("#" + informado).val());
  var dife = orig - info;
  jQuery("#" + retorno).val(converteFloatMoeda(dife));
}

/**
 * habilita_campos
 * Mostra ou oculta campos na tela de edição
 *
 * @param {string} condicao    - campo a ser testado
 * @param {string} valor       - valor a ser testado
 * @param {string} ocultos     - campos que serão ocultados
 */
function habilita_campos(condicao, valor, ocultos) {
  if (jQuery("#" + condicao).val() == valor) {
    jQuery('div[id^="ig_"]').show();
    oculto = ocultos.split(",");
    for (o = 0; o < oculto.length; o++) {
      jQuery("#ig_" + oculto[o]).hide();
    }
  }
}

/**
 * busca_atributos
 * Retorna a Etiqueta e o Icone da Opção selecionada
 * Faz os acertos dos campos conforme a Hierarquia
 * @param {string} tipo  - Identifica se é Módulo ou Classe
 * @param {object} opcao  - Opcao Selecionada
 * @param {string} etiqueta  - campo da Etiqueta
 * @param {string} icone  - Campo do ícone
 *
 */
function busca_atributos(tipo, opcao, etiqueta, icone) {
  opc = jQuery("#" + opcao).val();
  if (opc != "") {
    url = "/buscas/busca_modulo_id";
    if (tipo == "tela") {
      url = "/buscas/busca_tela_id";
    }
    dados = { busca: opc };
    retornoAjax = false;
    executaAjax(url, "json", dados);
    if (retornoAjax) {
      jQuery("#" + etiqueta).val(retornoAjax[0].text);
      jQuery("#" + icone).val(retornoAjax[0].icone);
    }
    // jQuery.ajax({
    //     type: "POST",
    //     headers: { 'X-Requested-With': 'XMLHttpRequest' },
    //     url: url,
    //     async: true,
    //     dataType: 'json',
    //     data: { 'busca': opc },
    //     success: function (data) {
    //         jQuery("#" + etiqueta).val(data[0].text);
    //         jQuery("#" + icone).val(data[0].icone);
    //     }
    // });
  }
}

/**
 * busca_textselect
 * Pega o texto do Select e coloca no campo
 * @param {object} obj  - Select de Origem
 * @param {string} id_destino  - Campo de Destino
 */
function busca_textselect(obj, id_destino) {
  //pega o texto do select informado
  var id_res = obj.id;
  var val_ant = jQuery("#" + id_destino).val();
  if (val_ant != undefined && val_ant.length() > 0) {
    val_ant += " - ";
  }
  if (obj.nodeName == "SELECT") {
    if (obj.selectedOptions[0].value >= 0) {
      var valor = obj.selectedOptions[0].text;
    } else {
      var valor = "";
    }
  } else {
    var valor = obj.value;
  }
  if (val_ant != undefined) {
    var val_fim = (val_ant + valor).trim();
    jQuery("#" + id_destino).val(val_fim);
  }
}

function busca_selectvalue(obj, id_destino) {
  //pega o valor do select informado
  var id_res = obj.id;
  var valor = obj.value;
  jQuery("#" + id_destino)
    .val(valor)
    .trigger("change");
}

/**
 * validaSenha
 * valida se a Senha contém os caracteres obrigatórios
 * Pelo menos
 * 1 letra maiuscula
 * 1 letra minuscula
 * 1 símbolo
 * 1 número
 * sem caracteres repetidos
 * @param {object} obj  - Campo de Origem
 */
function validaSenha(obj) {
  var senha = obj.value;
  if (senha.length > 0) {
    let regex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%*()_+^&}{:;?.])(?:([0-9a-zA-Z!@#$%;*(){}_+^&])(?!\1)){6,8}$/;
    if (regex.test(senha)) {
      jQuery(obj).removeClass("is-invalid");
      return true;
    } else {
      jQuery(obj).addClass("is-invalid");
      jQuery(obj).focus();
    }
  }
}

// MUDA O TIPO DE PESSOA FÍSICA OU JURÍDICA
function muda_pessoa(obj) {
  if (jQuery("input[id^='" + obj + "']:checked").val() == "F") {
    // CPF
    jQuery("#ig_cli_cnpj").removeClass("d-inline-flex");
    jQuery("#ig_cli_cnpj").addClass("d-none");
    jQuery("#cli_cnpj").attr("data-salva", 0);
    jQuery("#ig_cli_cpf").removeClass("d-none");
    jQuery("#ig_cli_cpf").addClass("d-inline-flex");
    jQuery("#cli_cpf").attr("data-salva", 1);
    jQuery("#ig_cli_setor").removeClass("d-inline-flex");
    jQuery("#ig_cli_setor").addClass("d-none");
    jQuery("#cli_setor").attr("data-salva", 0);
    jQuery("#ig_cli_ac").removeClass("d-inline-flex");
    jQuery("#ig_cli_ac").addClass("d-none");
    jQuery("#cli_ac").attr("data-salva", 0);
    // RG
    jQuery("#ig_cli_ie").removeClass("d-inline-flex");
    jQuery("#ig_cli_ie").addClass("d-none");
    jQuery("#cli_ie").attr("data-salva", 0);
    jQuery("#ig_cli_rg").removeClass("d-none");
    jQuery("#ig_cli_rg").addClass("d-inline-flex");
    jQuery("#cli_rg").attr("data-salva", 1);
  } else {
    // CNPJ
    jQuery("#ig_cli_cpf").removeClass("d-inline-flex");
    jQuery("#ig_cli_cpf").addClass("d-none");
    jQuery("#cli_cpf").attr("data-salva", 0);
    jQuery("#ig_cli_cnpj").removeClass("d-none");
    jQuery("#ig_cli_cnpj").addClass("d-inline-flex");
    jQuery("#cli_cnpj").attr("data-salva", 1);
    jQuery("#ig_cli_setor").removeClass("d-none");
    jQuery("#ig_cli_setor").addClass("d-inline-flex");
    jQuery("#cli_setor").attr("data-salva", 1);
    jQuery("#ig_cli_ac").removeClass("d-none");
    jQuery("#ig_cli_ac").addClass("d-inline-flex");
    jQuery("#cli_ac").attr("data-salva", 1);
    // IE
    jQuery("#ig_cli_rg").removeClass("d-inline-flex");
    jQuery("#ig_cli_rg").addClass("d-none");
    jQuery("#cli_rg").attr("data-salva", 0);
    jQuery("#ig_cli_ie").removeClass("d-none");
    jQuery("#ig_cli_ie").addClass("d-inline-flex");
    jQuery("#cli_ie").attr("data-salva", 1);
  }
}

/**
 * ValidaCPF
 * valida o CPF informado
 * @param {object} obj  - Campo de Origem
 */
function ValidaCPF(obj) {
  var valor = obj.value;
  // Remove caracteres inválidos do valor
  valor = valor.replace(/[^0-9]/g, "");

  // Captura os 9 primeiros dígitos do CPF
  // Ex.: 02546288423 = 025462884
  var digitos = valor.substr(0, 9);

  // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
  var novo_cpf = calc_digitos_posicoes(digitos);

  // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
  var novo_cpf = calc_digitos_posicoes(novo_cpf, 11);

  // Verifica se o novo CPF gerado é idêntico ao CPF enviado
  if (novo_cpf === valor) {
    // CPF válido
    jQuery(obj).removeClass("is-invalid");
    return true;
  } else {
    // CPF inválido
    // boxAlert('CPF Inválido', true, '', true, 1, false);
    jQuery(obj).addClass("is-invalid");
    jQuery(obj).focus();
    return false;
  }
}

/**
 * calc_digitos_posicoes
 *
 * Multiplica dígitos vezes posições
 *
 * @param string digitos Os digitos desejados
 * @param string posicoes A posição que vai iniciar a regressão
 * @param string soma_digitos A soma das multiplicações entre posições e dígitos
 * @return string Os dígitos enviados concatenados com o último dígito
 */
function calc_digitos_posicoes(digitos, posicoes = 10, soma_digitos = 0) {
  // Garante que o valor é uma string
  digitos = digitos.toString();
  // Faz a soma dos dígitos com a posição
  // Ex. para 10 posições:
  //   0    2    5    4    6    2    8    8   4
  // x10   x9   x8   x7   x6   x5   x4   x3  x2
  //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
  for (var i = 0; i < digitos.length; i++) {
    // Preenche a soma com o dígito vezes a posição
    soma_digitos = soma_digitos + digitos[i] * posicoes;
    // Subtrai 1 da posição
    posicoes--;
    // Parte específica para CNPJ
    // Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
    if (posicoes < 2) {
      // Retorno a posição para 9
      posicoes = 9;
    }
  }
  // Captura o resto da divisão entre soma_digitos dividido por 11
  // Ex.: 196 % 11 = 9
  soma_digitos = soma_digitos % 11;
  // Verifica se soma_digitos é menor que 2
  if (soma_digitos < 2) {
    // soma_digitos agora será zero
    soma_digitos = 0;
  } else {
    // Se for maior que 2, o resultado é 11 menos soma_digitos
    // Ex.: 11 - 9 = 2
    // Nosso dígito procurado é 2
    soma_digitos = 11 - soma_digitos;
  }
  // Concatena mais um dígito aos primeiro nove dígitos
  // Ex.: 025462884 + 2 = 0254628842
  var cpf = digitos + soma_digitos;
  // Retorna
  return cpf;
}

function formata_campo(objtipo, campo_alvo) {
  tipo = objtipo.value;
  url = "/buscas/busca_tipo_contato";
  dados = { busca: tipo };
  retornoAjax = false;
  executaAjax(url, "json", dados);
  if (retornoAjax) {
    if (retornoAjax[0].text == "celu" || retornoAjax[0].text == "whats") {
      jQuery("[id='" + campo_alvo + "']").prop("type", "tel");
      jQuery("[id='" + campo_alvo + "']").prop(
        "pattern",
        /^\(\d{2}\) \d{4,5}\-\d{4}$/
      );
      jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
      jQuery("[id='" + campo_alvo + "']").prop(
        "placeholder",
        "Informe Celular"
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "aria-describedby",
        "ad_" + campo_alvo
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "data-original-title",
        "Informe um Celular válido! (99) 99999-9999"
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "title",
        "Informe um Celular válido! (99) 99999-9999"
      );
      jQuery("[id='" + campo_alvo + "']").keyup(function () {
        mascara(this, "mcel2");
      });
    } else if (retornoAjax[0].text == "fone") {
      jQuery("[id='" + campo_alvo + "']").prop("type", "tel");
      jQuery("[id='" + campo_alvo + "']").prop(
        "pattern",
        /^\(\d{2}\) \d{4}\-\d{4}$/
      );
      jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
      jQuery("[id='" + campo_alvo + "']").prop("placeholder", "Informe Fone");
      jQuery("[id='" + campo_alvo + "']").prop(
        "aria-describedby",
        "ad_" + campo_alvo
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "data-original-title",
        "Informe um Fone válido! (99) 9999-9999"
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "title",
        "Informe um Fone válido! (99) 9999-9999"
      );
      jQuery("[id='" + campo_alvo + "']").attr(
        "onkeyup",
        mascara(this, "mtel")
      );
    } else if (retornoAjax[0].text == "email") {
      jQuery("[id='" + campo_alvo + "']").prop("type", "email");
      jQuery("[id='" + campo_alvo + "']").prop(
        "pattern",
        /^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/
      );
      jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
      jQuery("[id='" + campo_alvo + "']").prop(
        "aria-describedby",
        "ad_" + campo_alvo
      );
      jQuery("[id='" + campo_alvo + "']").prop("placeholder", "Informe E-mail");
      jQuery("[id='" + campo_alvo + "']").prop(
        "data-original-title",
        "Informe um E-mail válido!"
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "title",
        "Informe um E-mail válido!"
      );
    } else if (retornoAjax[0].text == "url" || retornoAjax[0].text == "site") {
      jQuery("[id='" + campo_alvo + "']").prop("type", "url");
      jQuery("[id='" + campo_alvo + "']").prop(
        "pattern",
        /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/
      );
      jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
      jQuery("[id='" + campo_alvo + "']").prop(
        "aria-describedby",
        "ad_" + campo_alvo
      );
      jQuery("[id='" + campo_alvo + "']").prop("placeholder", "Informe url");
      jQuery("[id='" + campo_alvo + "']").prop(
        "data-original-title",
        "Informe uma url válida!"
      );
      jQuery("[id='" + campo_alvo + "']").prop(
        "title",
        "Informe uma url válida!"
      );
    }
    jQuery("[id='" + campo_alvo + "']").focus();
  }
  // jQuery.ajax({
  //     type: "POST",
  //     headers: { 'X-Requested-With': 'XMLHttpRequest' },
  //     url: url,
  //     async: true,
  //     dataType: 'json',
  //     data: { 'busca': tipo },
  //     success: function (data) {
  //         if (data[0].text == 'celu' || data[0].text == 'whats') {
  //             jQuery("[id='" + campo_alvo + "']").prop('type', 'tel');
  //             jQuery("[id='" + campo_alvo + "']").prop('pattern', /^\(\d{2}\) \d{4,5}\-\d{4}$/);
  //             jQuery("[id='" + campo_alvo + "']").prop('style', 'text-align: left');
  //             jQuery("[id='" + campo_alvo + "']").prop('placeholder', 'Informe Celular');
  //             jQuery("[id='" + campo_alvo + "']").prop('aria-describedby', 'ad_' + campo_alvo);
  //             jQuery("[id='" + campo_alvo + "']").prop('data-original-title', 'Informe um Celular válido! (99) 99999-9999');
  //             jQuery("[id='" + campo_alvo + "']").prop('title', 'Informe um Celular válido! (99) 99999-9999');
  //             jQuery("[id='" + campo_alvo + "']").keyup(function () {
  //                 mascara(this, 'mcel2');
  //             });
  //         } else if (data[0].text == 'fone') {
  //             jQuery("[id='" + campo_alvo + "']").prop('type', 'tel');
  //             jQuery("[id='" + campo_alvo + "']").prop('pattern', /^\(\d{2}\) \d{4}\-\d{4}$/);
  //             jQuery("[id='" + campo_alvo + "']").prop('style', 'text-align: left');
  //             jQuery("[id='" + campo_alvo + "']").prop('placeholder', 'Informe Fone');
  //             jQuery("[id='" + campo_alvo + "']").prop('aria-describedby', 'ad_' + campo_alvo);
  //             jQuery("[id='" + campo_alvo + "']").prop('data-original-title', 'Informe um Fone válido! (99) 9999-9999');
  //             jQuery("[id='" + campo_alvo + "']").prop('title', 'Informe um Fone válido! (99) 9999-9999');
  //             jQuery("[id='" + campo_alvo + "']").attr('onkeyup', mascara(this, 'mtel'));
  //         } else if (data[0].text == 'email') {
  //             jQuery("[id='" + campo_alvo + "']").prop('type', 'email');
  //             jQuery("[id='" + campo_alvo + "']").prop('pattern', /^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/);
  //             jQuery("[id='" + campo_alvo + "']").prop('style', 'text-align: left');
  //             jQuery("[id='" + campo_alvo + "']").prop('aria-describedby', 'ad_' + campo_alvo);
  //             jQuery("[id='" + campo_alvo + "']").prop('placeholder', 'Informe E-mail');
  //             jQuery("[id='" + campo_alvo + "']").prop('data-original-title', 'Informe um E-mail válido!');
  //             jQuery("[id='" + campo_alvo + "']").prop('title', 'Informe um E-mail válido!');
  //         } else if (data[0].text == 'url' || data[0].text == 'site') {
  //             jQuery("[id='" + campo_alvo + "']").prop('type', 'url');
  //             jQuery("[id='" + campo_alvo + "']").prop('pattern', /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/);
  //             jQuery("[id='" + campo_alvo + "']").prop('style', 'text-align: left');
  //             jQuery("[id='" + campo_alvo + "']").prop('aria-describedby', 'ad_' + campo_alvo);
  //             jQuery("[id='" + campo_alvo + "']").prop('placeholder', 'Informe url');
  //             jQuery("[id='" + campo_alvo + "']").prop('data-original-title', 'Informe uma url válida!');
  //             jQuery("[id='" + campo_alvo + "']").prop('title', 'Informe uma url válida!');
  //         }
  //         jQuery("[id='" + campo_alvo + "']").focus();
  //     }
  // });
}

function acertaObrigatorio(obj, aCampos) {
  jQuery("#tmo_transacao_erp").attr("required", false);
  jQuery("#tmo_transacao_erp_saida").attr("required", false);
  jQuery("#tmo_transacao_erp_entrada").attr("required", false);
  jQuery("#tmm_deposito_origem\\[0\\]").attr("required", false);
  jQuery("#tmm_deposito_destino\\[0\\]").attr("required", false);
  valor = jQuery("#" + obj).val();
  if (valor == "T") {
    jQuery("#tmo_transacao_erp").attr("required", "required");
    jQuery("#tmm_deposito_origem\\[0\\]").attr("required", true);
    jQuery("#tmm_deposito_destino\\[0\\]").attr("required", true);
    if (jQuery("input[name='tmo_entrefiliais']:checked").val() == "S") {
      jQuery("#tmo_transacao_erp_saida").attr("required", "required");
    }
  } else if (valor == "E") {
    jQuery("#tmo_transacao_erp_entrada").attr("required", "required");
  }
}

function reprovar(obj, campo) {
  if (obj.value == "N") {
    // se está indo de Não pra Sim
    jQuery("#" + campo + "\\[0\\]").removeProp("checked");
    jQuery("#" + campo + "\\[0\\]").removeAttr("checked");
    jQuery("#" + campo + "\\[0\\]")
      .closest(".form-check")
      .addClass("d-none");
    jQuery("#" + campo + "\\[1\\]").attr("checked", "checked");
    jQuery("#" + campo + "\\[1\\]").prop("checked", true);
    jQuery("#" + campo + "\\[1\\]")
      .closest(".form-check")
      .removeClass("d-none");
  }
}

function mudaCheck2opcoes(obj, campo) {
  if (obj.value == "N") {
    // se está indo de Não pra Sim
    jQuery("#" + campo + "\\[0\\]").removeProp("checked");
    jQuery("#" + campo + "\\[0\\]").removeAttr("checked");
    jQuery("#" + campo + "\\[0\\]")
      .closest(".form-check")
      .addClass("d-none");
    jQuery("#" + campo + "\\[1\\]").attr("checked", "checked");
    jQuery("#" + campo + "\\[1\\]").prop("checked", true);
    jQuery("#" + campo + "\\[1\\]")
      .closest(".form-check")
      .removeClass("d-none");
  } else {
    jQuery("#" + campo + "\\[1\\]").removeProp("checked");
    jQuery("#" + campo + "\\[1\\]").removeAttr("checked");
    jQuery("#" + campo + "\\[1\\]")
      .closest(".form-check")
      .addClass("d-none");
    jQuery("#" + campo + "\\[0\\]").attr("checked", "checked");
    jQuery("#" + campo + "\\[0\\]").prop("checked", true);
    jQuery("#" + campo + "\\[0\\]")
      .closest(".form-check")
      .removeClass("d-none");
  }
}

function mudaObrigatorio(obj, regra, fields) {
  if (typeof obj === "object" && obj !== null) {
    nomecampo = obj.name;
  } else {
    nomecampo = obj;
  }
  nomecampo = nomecampo.replaceAll("[", "\\[");
  nomecampo = nomecampo.replaceAll("]", "\\]");
  valor = jQuery('input[name="' + nomecampo + '"]:checked').val();
  campos = fields.split(",");
  if (valor == regra) {
    if (jQuery.type(campos) == "array") {
      for (v = 0; v < campos.length; v++) {
        campos[v] = escIdColchetes(campos[v]);
        jQuery("#" + campos[v]).attr("required", "required");
        // jQuery('#' + campos[v]).attr('readonly', false);
        // jQuery('#' + campos[v]).attr('disabled', false);
        // if (jQuery('#' + campos[v].tagName == "select")) {
        //     jQuery('#' + campos[v]).selectpicker();
        // }
      }
    } else {
      campos = escIdColchetes(campos);
      jQuery("#" + campos).attr("required", "required");
      // jQuery('#' + campos).attr('readonly', false);
      // jQuery('#' + campos).attr('disabled', false);
    }
  } else {
    if (jQuery.type(campos) == "array") {
      for (v = 0; v < campos.length; v++) {
        campos[v] = escIdColchetes(campos[v]);
        jQuery("#" + campos[v]).attr("required", false);
        // jQuery('#' + campos[v]).attr('disabled', 'disabled');
        // jQuery('#' + campos[v]).attr('readonly', true);
        // if (jQuery('#' + campos[v].tagName == "select")) {
        //     jQuery('#' + campos[v]).selectpicker('destroy');
        // }
      }
    } else {
      campos = escIdColchetes(campos);
      jQuery("#" + campos).attr("required", false);
      // jQuery('#' + campos).attr('disabled', 'disabled');
      // jQuery('#' + campos).attr('readonly', true);
    }
  }
}

function escIdColchetes(id) {
  if (typeof id !== "string") return id;

  // Só escapa se ainda não estiver escapado
  return id
    .replace(/(?<!\\)\[/g, "\\[") // escapa [ se não tiver barra antes
    .replace(/(?<!\\)\]/g, "\\]"); // escapa ] se não tiver barra antes
}

function validaDataMinima(obj) {
  minimo = obj.min;
  valor = obj.value;
  if (valor < minimo) {
    boxAlert(5, true, "", true, 1, false, "");
    obj.value = "";
  }
}
