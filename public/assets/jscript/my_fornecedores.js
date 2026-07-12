/**
 * my_fornecedores.js
 * Módulo Fornecedores — T42 (Desvio de Qualidade) / T43 (Notificação de Evento).
 *
 * Reaproveita 100% o mecanismo de etiqueta já existente em my_default.js
 * (gerarEtiquetaZPL() / openImgModal()) — ver GeraEtiqueta() nos
 * controllers App\Controllers\Fornecedores\NotifDesvio (e, futuramente,
 * NotifEvento). Não introduz nenhum componente novo de seleção de etiqueta.
 *
 * Mirror de geraEiquetaProd() (my_default.js, ~linha 1976), mas sem depender
 * de um campo de quantidade na tela (a listagem sempre imprime 1 etiqueta
 * por clique — RN03.15 de T42 / RN04.1 de T43).
 */
function geraEiquetaGenerico(obj, url, qtia = 1) {
  jQuery(obj).removeClass("btn-outline-dark");
  jQuery(obj).addClass("btn-outline-danger");
  jQuery.getJSON(url, function (res) {
    if (res.erro) {
      boxAlert(res.erro, true, "");
      return;
    }
    gerarEtiquetaZPL(res.link, false, res.chave, qtia);
  });
}
