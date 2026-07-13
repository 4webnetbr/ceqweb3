# Apontamentos de Revisão — Módulo Fornecedores (T43)

**Rodada:** 01
**Revisor:** `byrev`
**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores (`app/Controllers/Fornecedores`)
**Tela revisada:** T43 — Notificação de Evento
**Documento de referência:** `docs/desenvolvimento/fornecedores-t42-t43-dev.md` (aprovado)
**Arquivos revisados:**
- `app/Entities/Fornecedores/EntOcoNotifEvento.php`
- `app/Entities/Fornecedores/EntOcoNotifEventoAcao.php`
- `app/Entities/Fornecedores/EntOcoNotifEventoProduto.php`
- `app/Models/Fornec/FornecNotifEventoModel.php`
- `app/Controllers/Fornecedores/NotifEvento.php`
- `app/Views/partials/pw_selecao_produtos_notif.php`
- `app/Views/partials/pw_grid_produtos_notif.php`
- `app/Views/partials/pw_acoes_notif.php`
- `app/Views/partials/pw_anexos_notif.php`
- `app/Config/Routes.php`
- `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php`
- `app/Database/Migrations/2026-07-12-000002_FornecedoresT43Infra.php`
- `public/assets/jscript/my_fornecedores.js`

**Destino:** `byarq` (ciência dos apontamentos) → `bydev` (correção)

---

## Contexto

Rodada de revisão do código de T43 (Fornecedores > Notificação de Evento), codificado conforme o documento de desenvolvimento aprovado em `docs/desenvolvimento/fornecedores-t42-t43-dev.md`. O `byrev` avaliou as Entities/Model/Controller de T43, as views parciais de seleção de produtos, grid de produtos, ações e anexos, a migration de infraestrutura/schema específica de T43, e o JS de front (`my_fornecedores.js`) referenciado pelas views.

---

## 1. Achados Bloqueantes

### Bloqueante 1 — Duas funções JS chamadas na tela nunca foram implementadas (quebram RN03.18/19 e RN03.16/20)

**Arquivos:**
- `app/Entities/Fornecedores/EntOcoNotifEvento.php:131` — `$notivisa->funcChan = 'mostraNotivisaNum(this)';`
- `app/Views/partials/pw_anexos_notif.php:32` — `onclick="excluiAnexoExistente(this, <?= $anexo->nva_id ?>, 'anexo_<?= $sufixo ?>')"`

**Situação encontrada:**
Busca recursiva no repositório não encontrou `mostraNotivisaNum` nem `excluiAnexoExistente` em nenhum arquivo `.js`. `public/assets/jscript/my_fornecedores.js` só define `geraEiquetaGenerico()`.

**Por que é um problema:**
O toggle Notivisa (RN03.18) nunca exibe/oculta `nev_notivisa_num` (RN03.19) — a função de callback do campo não existe. O botão "Excluir Anexo" de um anexo já persistido lança erro JS no console e nunca chama `deleteAnexo()` — o endpoint existe no controller mas fica inatingível pela UI (RN03.16/RN03.20).

**Correção sugerida:**
Implementar as duas funções em `my_fornecedores.js`:
- `mostraNotivisaNum(el)` — mostra/oculta o campo `nev_notivisa_num` conforme o estado do toggle, seguindo o padrão de outros callbacks `funcChan` já existentes no projeto.
- `excluiAnexoExistente(el, nvaId, containerId)` — deve usar `boxAlert`/`executaAjax` de confirmação (nunca `confirm()` nativo, conforme `rascunho-runtime-js.md`), chamar o endpoint de exclusão de anexo e remover o elemento do DOM apenas em caso de sucesso.

---

### Bloqueante 2 — RN03.19 não é validada no servidor

**Arquivo:** `app/Models/Fornec/FornecNotifEventoModel.php` (`$validationRules`, linhas 43-51).

**Situação encontrada:**
Não há regra de validação para `nev_notivisa_num`.

**Por que é um problema:**
Combinado com o Bloqueante 1, um POST com `nev_notivisa=S` e `nev_notivisa_num` vazio é aceito sem erro — nenhuma camada (client ou servidor) impede a gravação de uma notificação Notivisa sem o número correspondente, violando RN03.19.

**Correção sugerida:**
Adicionar validação condicional em `FornecNotifEventoModel` (obrigatório `nev_notivisa_num` se `nev_notivisa=S`) ou checagem explícita equivalente em `NotifEvento::store()`.

---

### Bloqueante 3 — Upload de anexo sem validação de tipo no servidor (RN03.16/RN03.20)

**Arquivo:** `app/Controllers/Fornecedores/NotifEvento.php`, método `salvaAnexos()` (linhas 357-388).

**Situação encontrada:**
O método só verifica `isValid()`/erro de upload do CI4, sem validar extensão/MIME contra whitelist antes de `$arquivo->move()`.

**Por que é um problema:**
A restrição de tipo (`.pdf,.png,.jpeg,.jpg`) existe apenas no client, via `setTipoArq()`. Qualquer requisição direta ao endpoint (fora da UI) pode enviar arquivo de qualquer tipo/extensão, sem checagem server-side, antes de mover o arquivo para `WRITEPATH.'uploads/notif_evento/'`.

**Correção sugerida:**
Adicionar checagem explícita de extensão/MIME-type contra a mesma whitelist do client, antes de `$arquivo->move()`, rejeitando com erro claro qualquer tipo fora da lista.

---

### Bloqueante 4 — RN03.1 não é revalidada no store()/selecionaProdutos(), e não há proteção no banco contra vínculo duplicado

**Arquivos:**
- `app/Models/Fornec/FornecNotifDesvioModel.php` (`getDisponiveisParaNotificacao()`)
- `app/Controllers/Fornecedores/NotifEvento.php` (`selecionaProdutos()`, `store()`)
- `app/Database/Migrations/2026-07-12-000002_FornecedoresT43Infra.php` (tabela `oco_notif_evento_produto`)

**Situação encontrada:**
`FornecNotifDesvioModel::getDisponiveisParaNotificacao()` implementa corretamente o filtro de elegibilidade (Concluída + `NOT EXISTS` em `oco_notif_evento_produto`), mas esse filtro só é usado na tela de seleção. `NotifEvento::selecionaProdutos()` e `NotifEvento::store()` aceitam qualquer `ndv_id[]` postado sem re-checar elegibilidade no momento da gravação. Na migration, `oco_notif_evento_produto` só tem `addKey('ndv_id')` (índice comum, não único) — diferente de `oco_notif_desvio`, que tem `addUniqueKey('oco_id')` para essa mesma proteção.

**Por que é um problema:**
Duas abas/usuários selecionando o mesmo produto em paralelo conseguem gravar duas notificações para o mesmo `ndv_id`, violando RN03.1 (um produto só pode ser notificado uma vez). Não há proteção de aplicação (revalidação no store) nem de banco (constraint única) contra essa condição de corrida.

**Correção sugerida:**
(a) Alterar `addKey('ndv_id')` para `addUniqueKey('ndv_id')` em `oco_notif_evento_produto`; e/ou
(b) Revalidar elegibilidade de cada `ndv_id` postado dentro da transação do `store()`, rejeitando o registro se algum produto já estiver vinculado a outra notificação.

---

## 2. Sugestões Não-Bloqueantes

### Sugestão 5 — Nome de grupo inconsistente em `EntOcoNotifEventoAcao::defCamposAcao()`

Usa grupo `'acoes'` (copiado de `EntOcoSubtOcorrencia`), mas o container real em `pw_acoes_notif.php` é `'acoes_notif'`. Hoje mascarado porque o design usa botão "Adicionar" estático, não por linha, mas quebra se o padrão for reaproveitado/estendido no futuro.

**Sugestão:** renomear para `'acoes_notif'` na Entity.

---

### Sugestão 6 — Escaping inconsistente em `resumeListaComTooltip()`

`app/Controllers/Fornecedores/NotifEvento.php`, linhas 119-134: o primeiro item exibido não passa por `esc()`, enquanto o texto do tooltip é escapado. Como `montaListaDados()` renderiza a célula como HTML bruto, nome de produto/lote com caracteres especiais pode quebrar o tooltip ou abrir brecha de injeção.

**Sugestão:** envolver `$itens[0]` também com `esc()`.

---

### Sugestão 7 — `GROUP_CONCAT` sem tratamento de limite

A view `vw_oco_notif_evento_relac` usa `GROUP_CONCAT` sujeito ao `group_concat_max_len` padrão (1024 bytes). Notificações com muitos produtos/nomes longos podem truncar a lista no meio de um item, quebrando o `explode()` em `resumeListaComTooltip()`.

**Sugestão:** considerar `SET SESSION group_concat_max_len` nas queries que usam essa view, ou ao menos documentar o limite prático para o `bytest` cobrir esse cenário.

---

### Sugestão 8 — Performance do `SELECT DISTINCT` sobre a view grande

Confirmado que a técnica é segura (sem divergência de dado, apenas fan-out do JOIN) e não altera a view pré-existente `vw_oco_ocorrencia_completa_relac`. Ponto de atenção de performance para o `bytest`: `DISTINCT` sobre ~10-16 colunas não usa índice para deduplicar — acompanhar tempo de resposta em volume real de dados.

---

### Sugestão 9 — Causa raiz não identificada do `tpa_tipo` gravado como `NULL`

O comentário da migration relata autocorreção sem explicar a causa raiz.

**Sugestão:** abrir item de acompanhamento para investigar antes que o problema se repita em produção.

---

### Sugestão 10 — Caminho de update do `store()` não exercitado

O branch de update "por simetria" no `store()` nunca é alcançado na prática, pois `edit()` bloqueia edição fora do status Pendente. Não é um bug, mas deveria constar no plano de testes do `bytest` como cenário a cobrir explicitamente, ou ser documentado como código morto por design.

---

## 3. Itens Já Aprovados Sem Apontamento (rastreabilidade)

Itens abaixo foram checados pelo `byrev` e estão conformes com o documento de desenvolvimento aprovado e com os padrões de `docs/referencia/` — registrados apenas para rastreabilidade:

- View pré-existente `vw_oco_ocorrencia_completa_relac` **não foi alterada** — apenas referenciada em subquery dentro das views novas.
- RN03.1 (elegibilidade de produtos para notificação) corretamente implementada em `getDisponiveisParaNotificacao()`.
- Fidelidade ao adendo do `byarq` sobre a listagem: Produto/Lote com "primeiro item + e mais N" e tooltip, Fabricante via `MIN()` sem tooltip, Data via `MIN()`.
- `criaSelectRelativo()`, `MyCampo`, `fmtEtiquetaCor()`, `boxAlert`/`executaAjax` usados corretamente, sem `<input>`/`<select>` cru nem `jQuery.ajax` solto.
- Bloqueio server-side de edição fora do status Pendente implementado em `edit()`.
- Assinatura/estrutura do array passado a `geraMovimentoRequisicoes()` em `store()` bate com a implementação real do helper e com o padrão de `OcoTrataOcorrencia::gerarMovimentacao()` — a generalização por N produtos parece correta, mas recomenda-se testar ponta a ponta antes de produção (não foi exercitada de verdade no smoke test, por segurança, já que dispara integração SOAP real com o ERP).
- Nenhuma chamada a `debug()` esquecida.

---

## 4. Conclusão do `byrev`

Itens 1 a 4 (Seção 1) são **bloqueantes** — impedem fechar o ciclo. Itens 5 a 10 (Seção 2) são sugestões/observações para o `byarq` decidir prioridade (entram neste ciclo ou ficam para o próximo).

---

## 5. Encaminhamento

1. **`byarq`** — tomar ciência dos 4 bloqueantes e das 6 sugestões; decidir se as sugestões 5-10 entram neste ciclo ou ficam para o próximo.
2. **`bydev`** — aplicar a correção dos Bloqueantes 1, 2, 3 e 4:
   - Implementar `mostraNotivisaNum()` e `excluiAnexoExistente()` em `my_fornecedores.js`;
   - Adicionar validação condicional de `nev_notivisa_num` no Model/Controller;
   - Adicionar validação de extensão/MIME server-side em `salvaAnexos()`;
   - Ajustar `oco_notif_evento_produto` para `addUniqueKey('ndv_id')` e/ou revalidar elegibilidade no `store()`.
3. Retornar ao `byrev` para nova rodada de revisão após as correções.
