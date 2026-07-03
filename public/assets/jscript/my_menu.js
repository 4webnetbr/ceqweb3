/**
 * mobileScreen
 * variável que diz se é um dispositivo móvel
 */
const mobileScreen = window.matchMedia("(max-width: 990px )");
var menuaberto = getCookie("menuaberto") == "false" ? false : true;

/**
 * Document Ready my_menu
 * Executado no carregamento da página
 */
jQuery(document).ready(function () {
  atualizaMenu();
});

function atualizaMenu() {
  acertamenuaberto();

  const controllerVal = String(jQuery("#controler").val() || "").toLowerCase();
  const idRefVal = jQuery("#id_ref").val();

  // Marca como "active" o menu de etapa correspondente
  jQuery(".nav-dropdown-menu-etapa").each(function () {
    const $item = jQuery(this);
    if ($item.attr("id") === idRefVal) {
      $item.addClass("active");
    } else {
      $item.removeClass("active");
    }
  });

  // Fecha todos os menus antes
  // Fecha todos os colapsáveis: submenus, accordions, etc.
  jQuery("#accordionMenu .collapse.show").each(function () {
    const $collapseEl = jQuery(this);
    const collapseId = $collapseEl.attr("id");

    if (collapseId) {
      // Encontra qualquer botão que controla esse collapse
      const $button = jQuery('[data-bs-target="#' + collapseId + '"]');
      $button.addClass("collapsed");
    }

    $collapseEl.removeClass("show");
  });

  // Remove a classe 'active' de todos os itens do menu (se necessário)
  jQuery(".nav-dropdown-menu").removeClass("active");

  // Agora ativa apenas o item correspondente ao controller
  jQuery(".nav-dropdown-menu").each(function () {
    const $menuItem = jQuery(this);
    const itemId = String($menuItem.attr("id") || "").toLowerCase();

    if (itemId === controllerVal) {
      const submenu = $menuItem.data("submenu");
      let collapseId, $collapseEl, $button;

      if (submenu) {
        const collapsesub = $menuItem.data("collapse");
        const $collapseSubEl = jQuery("#" + collapsesub);

        const $subButton = $collapseSubEl
          .parent()
          .children()
          .children("button")
          .first();

        $subButton.removeClass("collapsed");
        $collapseSubEl.addClass("show");

        const $subMenu = jQuery("#" + submenu);
        collapseId = $subMenu.length ? $subMenu.data("collapse") : collapsesub;
      } else {
        collapseId = $menuItem.data("collapse");
      }

      if (collapseId) {
        $collapseEl = jQuery("#" + collapseId);
        $button = $collapseEl.parent().children().children("button").first();

        $button.removeClass("collapsed");
        $collapseEl.addClass("show");
      }

      $menuItem.addClass("active");
    }
  });

  jQuery("#bt_user")
    .off("click")
    .on("click", function () {
      jQuery("#show_user").toggleClass("active");
    });

  // jQuery(".content, .title, .titulo")
  //   .off("click")
  //   .on("click", function (e) {
  //     // Ignora cliques nos botões internos
  //     if (jQuery(e.target).closest("#bt_ajuda, #bt_notifica").length) {
  //       return;
  //     }
  //     if (jQuery("#show_user").hasClass("active")) {
  //       jQuery("#show_user").toggleClass("active");
  //       if (!menuaberto) {
  //         jQuery(".sidebar").toggleClass("active");
  //       }
  //     }
  //     if (jQuery("#show_ajuda").hasClass("show")) {
  //       jQuery("#bt_ajuda").triggerHandler("click");
  //     }
  //     if (jQuery("#show_notifica").hasClass("show")) {
  //       jQuery("#bt_notifica").trigger("click");
  //     }
  //   });

  if (!isMobile()) {
    function closeAllOverlays() {
      jQuery("#show_user").removeClass("active");
      jQuery("#show_ajuda, #show_notifica").removeClass("show");

      if (!menuaberto) {
        jQuery(".sidebar").removeClass("active");
      }
    }
  }

  jQuery(document).on("click", function (e) {
    const $target = jQuery(e.target);

    if (
      $target.closest("#show_user, #show_ajuda, #show_notifica").length ||
      $target.closest("#bt_user, #bt_ajuda, #bt_notifica").length
    ) {
      return;
    }

    closeAllOverlays();
  });

  function isMobile() {
    return window.matchMedia("(hover: none)").matches;
  }

  // jQuery(".sidebar").off();

  if (isMobile()) {
    // alert("sou mobile");
    // MOBILE = CLICK
    jQuery(".sidebar").on("click", function () {
      // if (!menuaberto && !jQuery(this).hasClass("active")) {
      jQuery(this).toggleClass("active");
      // alert("clicou");
      // }
    });
  } else {
    // DESKTOP = HOVER
    jQuery(".sidebar").hover(
      function () {
        if (!menuaberto && !jQuery(this).hasClass("active")) {
          jQuery(this).toggleClass("active");
        }
      },
      function () {
        if (!menuaberto && !jQuery("#show_user").hasClass("active")) {
          jQuery(this).toggleClass("active");
        }
      },
    );
  }

  // jQuery(".sidebar")
  //   .off("mouseenter mouseleave")
  //   .hover(
  //     function () {
  //       if (!menuaberto && !jQuery(".sidebar").hasClass("active")) {
  //         jQuery(".sidebar").toggleClass("active");
  //       }
  //     },
  //     function () {
  //       if (!menuaberto && !jQuery("#show_user").hasClass("active")) {
  //         jQuery(".sidebar").toggleClass("active");
  //       }
  //     },
  //   );

  jQuery(".bt-manut.add")
    .off("mouseenter mouseleave")
    .hover(
      function () {
        jQuery(this).find(".txt-bt-manut").removeClass("d-none");
      },
      function () {
        jQuery(this).find(".txt-bt-manut").addClass("d-none");
      },
    );

  jQuery(".manutencao")
    .off("mouseenter mouseleave")
    .hover(
      function () {
        jQuery(".manut").toggleClass("active");
        if (jQuery("#show_user").hasClass("active")) {
          jQuery("#show_user").removeClass("active");
        }
      },
      function () {
        jQuery(".manut").toggleClass("active");
      },
    );

  jQuery("#menuaberto").prop("checked", menuaberto);
  verificaNotificacao();
}

/**
 * btn_menu clique
 * Evento Clique do botão de menu
 */
jQuery(".btn_menu").click(function () {
  jQuery(this).toggleClass("click");
  jQuery(".sidebar").toggleClass("show");
});

/**
 * sidebar clique
 * Evento Clique na Barra de Menus
 */
jQuery(".sidebar ul li a").click(function () {
  var id = jQuery(this).attr("id");
  jQuery("nav ul li ul.item-show-" + id).toggleClass("show");
  jQuery("nav ul li #" + id + " span").toggleClass("rotate");
});

/**
 * nav ul li clique
 * Evento Clique na Opção do Menu
 */
jQuery("nav ul li").click(function () {
  jQuery(this).addClass("active").siblings().removeClass("active");
});

/**
 * acertaCamposCadMenu
 * Função específica para o CAdastro de Menus
 * Faz os acertos dos campos conforme a Hierarquia
 * @param {object} hierarquia  - Campo da Hierarquia
 */
function acertaCamposCadMenu(hierarquia) {
  val_hier = jQuery("#" + hierarquia).val();
  jQuery("#tel_id").prop("required", false);
  if (val_hier == 1) {
    // Raiz do Menu
    jQuery("#ig_mod_id").addClass("d-none");
    jQuery("#ig_men_menupai_id").addClass("d-none");
    jQuery("#ig_men_submenu_id").addClass("d-none");
    jQuery("#ig_tel_id").removeClass("d-none");
    jQuery("#bus_tel_id").prop("required", true);
    //nao permite alterar o ícone, nem a etiqueta, usa o que foi definido na tela escolhida
    jQuery("#men_icone").prop("readonly", true);
    jQuery("#men_etiqueta").prop("readonly", true);
    jQuery("#men_icone").prop("disabled", true);
    jQuery("#men_etiqueta").prop("disabled", true);
  } else if (val_hier == 2) {
    // Menu
    jQuery("#ig_men_menupai_id").addClass("d-none");
    jQuery("#ig_men_submenu_id").addClass("d-none");
    jQuery("#ig_tel_id").addClass("d-none");
    jQuery("#ig_mod_id").removeClass("d-none");
    jQuery("#bus_tel_id").prop("required", false);
    jQuery("#tel_id").prop("required", false);
  } else if (val_hier == 3) {
    // SubMenu
    jQuery("#ig_tel_id").addClass("d-none");
    jQuery("#bus_tel_id").prop("required", false);
    jQuery("#ig_mod_id").addClass("d-none");
    jQuery("#ig_men_menupai_id").removeClass("d-none");
    jQuery("#men_menupai_id").prop("required", true);
    jQuery("#ig_men_submenu_id").addClass("d-none");
  } else if (val_hier == 4) {
    // Opção do Menu
    jQuery("#ig_mod_id").addClass("d-none");
    jQuery("#ig_men_menupai_id").removeClass("d-none");
    jQuery("#ig_men_submenu_id").removeClass("d-none");
    jQuery("#ig_tel_id").removeClass("d-none");
    jQuery("#tel_id").prop("required", true);
    jQuery("#bus_tel_id").prop("required", true);
    jQuery("#men_menupai_id").prop("required", true);
    //nao permite alterar o ícone, nem a etiqueta, usa o que foi definido na tela escolhida
    jQuery("#men_icone").prop("readonly", true);
    jQuery("#men_etiqueta").prop("readonly", true);
    jQuery("#men_icone").prop("disabled", true);
    jQuery("#men_etiqueta").prop("disabled", true);
  }
}

function mudamenuaberto() {
  menuaberto = !menuaberto;
  setCookie("menuaberto", menuaberto);
  acertamenuaberto();
}

function acertamenuaberto() {
  if (menuaberto) {
    jQuery(".sidebar").addClass("active");
    jQuery(".content").addClass("menuaberto");
    jQuery(".title").addClass("menuaberto");
    jQuery(".rodape").addClass("menuaberto");
  } else {
    jQuery(".sidebar").removeClass("active");
    jQuery(".content").removeClass("menuaberto");
    jQuery(".title").removeClass("menuaberto");
    jQuery(".rodape").removeClass("menuaberto");
    jQuery("#show_user").removeClass("active");
  }
}

function verificaSessao() {
  urlver = window.location.origin + "/buscas/verificaSessao";
  jQuery.ajax({
    url: urlver,
    method: "GET", // Pode ser POST se preferir
    dataType: "json",
    success: function (response) {
      if (response.status === "sessao_expirada") {
        // Sessão expirada, redireciona para o login
        urllogin = window.location.origin + "/";
        clearInterval(intervalId);
        boxAlert(23, false, urllogin, false, 1, false, "Sessão Expirada");
        // window.location.href = "/"; // Redireciona para a página de login
      } else {
        // Sessão ainda válida, nada a fazer
        console.log("Sessão válida");
      }
    },
    error: function (xhr, status, error) {
      // Caso haja algum erro na requisição
      console.error("Erro na requisição Ajax: ", error);
    },
  });
}

// const intervalId = setInterval(() => {
//     verificaSessao();
// }, 145000);
// Executar a função de verificação a cada 2,5 minutos
function carregarPaginaComSecoes(url, adicionarNoHistorico = true) {
  jQuery("#bloqueiaTela").show();

  jQuery.ajax({
    url: url,
    method: "GET",
    dataType: "html",
    success: function (html) {
      const temp = jQuery("<div>").html(html);

      const secoes = ["header", "menu", "content", "manutencao", "footer"];

      secoes.forEach(function (secao) {
        const nova = temp.find("section." + secao);
        const atual = jQuery("section." + secao);

        if (nova.length && atual.length) {
          atual.html(nova.html());
        }
      });

      // Extrair e executar scripts diretamente do HTML carregado
      //   executarScriptsInline(temp);
      atualizaMenu();

      if (adicionarNoHistorico) {
        history.pushState(null, "", url);
      }
    },
    error: function (xhr, status, error) {
      console.error("Erro ao carregar página:", error);
      alert("Erro ao carregar a página.");
    },
    complete: function () {
      jQuery("#loader").hide();
    },
  });
}

function executarScriptsInline($html) {
  $html.find("script").each(function () {
    const $oldScript = jQuery(this);
    const newScript = document.createElement("script");

    if ($oldScript.attr("src")) {
      newScript.src = $oldScript.attr("src");
      newScript.async = false;
    } else {
      newScript.textContent = $oldScript.html();
    }

    // Importante: colocar no <body> para executar corretamente
    document.body.appendChild(newScript);
    document.body.removeChild(newScript);
  });
}

// Suporte a botão voltar/avançar
window.addEventListener("popstate", function () {
  carregarPaginaComSecoes(location.href, false);
});

function buscaMenu(busca) {
  const termoBusca = busca.value.trim().toLowerCase();
  // Reexibe todos os elementos dentro do menu antes de filtrar
  jQuery("#accordionMenu .d-none").removeClass("d-none");
  jQuery("#accordionMenu .nav-dropdown-menu.d-none").removeClass("d-none");

  if (termoBusca === "") {
    atualizaMenu();
    return;
  }

  // Abre todos os colapsáveis (submenus e accordions)
  jQuery("#accordionMenu .collapse").each(function () {
    const $collapse = jQuery(this);
    const collapseId = $collapse.attr("id");

    if (collapseId) {
      const $botao = jQuery('[data-bs-target="#' + collapseId + '"]');
      $botao.removeClass("collapsed");
      $collapse.addClass("show");
    }
  });

  // Filtra os itens dentro do menu
  jQuery(
    "#accordionMenu .nav-dropdown-menu, #accordionMenu .accordion-item",
  ).each(function () {
    const $item = jQuery(this);
    const textoItem = $item.text().trim().toLowerCase();

    if (!textoItem.includes(termoBusca)) {
      $item.addClass("d-none");
    } else {
      $item.removeClass("d-none");
    }
  });

  // Oculta submenus cujos itens estão todos ocultos
  jQuery("#accordionMenu .nav-dropdown-menu").each(function () {
    const $submenu = jQuery(this);
    const collapseId = $submenu.data("collapse");
    const $collapse = jQuery("#" + collapseId);

    if ($collapse.length) {
      const $visiveis = $collapse.find(".nav-dropdown-menu:not(.d-none)");
      if ($visiveis.length === 0) {
        $submenu.addClass("d-none");
      }
    }
  });

  // Oculta accordions que não têm nenhum item visível
  jQuery("#accordionMenu .accordion").each(function () {
    const $accordion = jQuery(this);
    const $visiveis = $accordion.find(".accordion-item:not(.d-none)");

    if ($visiveis.length === 0) {
      $accordion.addClass("d-none");
    }
  });
}
