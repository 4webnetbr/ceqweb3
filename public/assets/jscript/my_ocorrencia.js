/**
 * my_ocorrencia.js
 * Scripts específicos do módulo Ocorrências (T9/T11/T12).
 */

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
  await executaAjaxWait(url + "/" + atual, "json");

  if (retornoAjax && retornoAjax.html) {
    jQuery("#rep_acoesextra").append(retornoAjax.html);

    jQuery("#rep_acoesextra select")
      .last()
      .selectpicker({ container: "body" })
      .selectpicker("render");

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
