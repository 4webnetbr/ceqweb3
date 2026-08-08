/**
 * my_ocorrencia.js
 * Scripts específicos do módulo Ocorrências (T9/T11/T12).
 */

/**
 * ativInativ (override local — apenas nas páginas de T9/OcoSubtOcorrencia)
 * RN06.2 (revisão 01) — a tela T9 precisa de uma UX diferente do
 * ativInativ() genérico (my_default.js) quando a inativação é bloqueada por
 * ocorrência(s) Pendente(s) vinculada(s) ao subtipo: além da mensagem base
 * (MSG 14), deve listar as pendências para o usuário.
 *
 * Esta função é carregada só nas páginas de T9 (`OcoSubtOcorrencia::index()`
 * define `$this->data['scripts'] = 'my_ocorrencia'`) e, por ser um `function`
 * declarado no escopo global, sobrescreve — só nessas páginas — a
 * `ativInativ()` genérica de `my_default.js` (que permanece intocada; o
 * botão "Ativar/Inativar" da grid continua chamando `ativInativ(url, texto,
 * ativo)`, conforme gerado por `app/Common.php`).
 *
 * Reaproveita 100% do que já existe: `boxAlert()` (confirmação + exibição de
 * erro) e `getMensagem()` (texto da MSG 14) — nenhuma alteração em
 * my_default.js.
 *
 * @param {string} url - url de ativinativ() do registro
 * @param {string} registro - descrição do registro (nome do subtipo)
 * @param {boolean} ativo - true = está ativo (vai inativar), false = vai ativar
 */
async function ativInativ(url, registro, ativo) {
  let msg, tit;

  if (ativo) {
    msg =
      "Confirma a Inativação do Registro?<br><h5 class='text-center'>" +
      registro +
      "</h5>";
    tit = "Inativação de Registro";
  } else {
    msg =
      "Confirma a Ativação do Registro?<br><h5 class='text-center'>" +
      registro +
      "</h5>";
    tit = "Ativação de Registro";
  }

  const confirmado = await boxAlert(msg, false, "", true, 1, true, tit);
  if (!confirmado) {
    return;
  }

  retornoAjax = false;
  await executaAjaxWait(url, "json");

  if (!retornoAjax) {
    return;
  }

  if (retornoAjax.erro) {
    if (
      Array.isArray(retornoAjax.pendencias) &&
      retornoAjax.pendencias.length
    ) {
      // RN06.2 — MSG 14 (base) + lista de pendências (ramo string literal do
      // boxAlert(), já que o parâmetro opcoes/dadosExtra é exclusivo do ramo
      // bootbox.prompt, não se aplica aqui).
      const msgvar = await getMensagem(14);

      const lista =
        "<ul class='text-start mb-0'>" +
        retornoAjax.pendencias
          .map((p) => {
            const numero = String(p.oco_id).padStart(6, "0");
            return `<li>Nº ${numero} - ${p.sut_nome ?? ""} - ${p.oco_data ?? ""}</li>`;
          })
          .join("") +
        "</ul>";

      const tituloComIcone =
        '<h3><i class="fas fa-exclamation-triangle"></i><span class="mx-2">' +
        (msgvar?.msg_titulo ?? "Atenção") +
        "</span></h3>";

      const textoConcatenado = (msgvar?.msg_mensagem ?? "") + "<br>" + lista;

      await boxAlert(
        textoConcatenado,
        true,
        "",
        true,
        1,
        false,
        tituloComIcone,
      );
    } else {
      // Erro genérico (ex.: MSG 17 "Problema no Sistema") — msg numérico,
      // boxAlert() resolve o texto sozinho via getMensagem()/GET /mensagem/{id}.
      await boxAlert(retornoAjax.msg, true, "", true, 1, false);
    }
    return;
  }

  jQuery("#table").DataTable().ajax.reload(null, false);
  if (retornoAjax.msg) {
    mostranoToast(retornoAjax.msg);
  }
}

/**
 * adicionaAcaoExtra
 * RN03.15 — Adiciona uma linha de ação extra (avulsa) na aba Ações da
 * tratativa (T12), abaixo das ações de origem do subtipo. Segue o mesmo
 * padrão visual de botões usado em bt-repete/addCampo (T9/T10), mas com um
 * fluxo de AJAX próprio, mais simples, já que a tela de finalizar() não usa
 * o mecanismo genérico de listas repetíveis (`vw_edicao.php`/`addCampo()`).
 *
 * @param {string} url - url base (OcoTrataOcorrencia/addCampoAcaoExtra/<oco_id>)
 * @param {object} btn - botão "+" que disparou a inclusão (mantém o índice atual)
 */
async function adicionaAcaoExtra(url, btn) {
  const atual = parseInt(btn.getAttribute("data-index")) || 0;

  retornoAjax = false;
  await executaAjax(url + "/" + atual, "json");

  if (retornoAjax && retornoAjax.html) {
    jQuery("#tbacoes tbody").append(retornoAjax.html);

    jQuery("#tbacoes select").selectpicker();

    btn.setAttribute("data-index", atual + 1);
    jQuery("#form1").attr("data-alter", true);
  }
}

/**
 * removeAcaoExtra
 * RN03.15 — Remove uma linha de ação extra adicionada manualmente.
 * Não reaproveita `exclui_campo()` (my_fields.js) porque essa função remove
 * o `.closest('.row')`, e aqui o `.row` é apenas o wrapper interno dos
 * campos dentro do `<tr><td>` (mantido para consistência visual com as
 * linhas de ação de origem, que já usam essa mesma estrutura de tabela) —
 * `exclui_campo` deixaria um `<tr>` vazio para trás.
 *
 * @param {object} btn - botão "excluir" da linha
 */
function removeAcaoExtra(btn) {
  jQuery(btn).closest("tr").remove();
  jQuery("#form1").attr("data-alter", true);
}

/**
 * confirmaAcaoTratativa
 * RN03.18.2 — Antes de submeter o formulário de tratativa (T12), se houver
 * alguma ação "Gerar Movimentação" (tpa_tipo=3) marcada entre as ações da
 * tratativa, pede confirmação (MSG 6) ao usuário. Sem mudança de contrato no
 * store() do controller — é apenas uma confirmação adicional no front antes
 * do POST já existente.
 *
 * @returns {Promise<boolean>} true para prosseguir com o submit, false para cancelar
 */
async function confirmaAcaoTratativa() {
  const temGeraMovimentacao = jQuery('input[name="tpa_tipo_marca[]"]')
    .toArray()
    .some((el) => String(el.value) === "3");

  if (!temGeraMovimentacao) {
    return true;
  }

  const confirmado = await boxAlert(6, false, "", false, 1, true);
  return !!confirmado;
}
