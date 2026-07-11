# Apontamentos de Revisão — Módulo Ocorrências (T9, T11, T12)

**Rodada:** 01
**Revisor:** `byrev`
**Projeto:** CeqWeb 3.0
**Módulo:** Ocorrências (`app/Controllers/Ocorrencia`)
**Telas revisadas:** T9 — Subtipos de Ocorrências (tel_id=49) · T11 — Gestão de Ocorrências (tel_id=56) · T12 — Tratativa de Ocorrências (tel_id=61)
**Documento de referência:** `docs/desenvolvimento/ocorrencias-t9-t11-t12-dev.docx` (aprovado)
**Destino:** `byarq` (decisão sobre Bloqueante 2) → `bydev` (correção)

---

## 1. Bloqueantes

### Bloqueante 1 — Uso incorreto de `getMensagem()` (RN06.2 em T9, RN04.1 em T11)

**Status:** decisão do `byarq` recebida — proposta original de correção **rejeitada** e substituída pela correção abaixo. Pode seguir para `bydev`.

**Situação encontrada:**
- `OcoSubtOcorrencia.php`, linha 369, dentro de `ativinativ()` (RN06.2):
  ```php
  $texto = getMensagem('MSG_14') ?? '...';
  ```
- `OcoOcorrencia.php`, linha ~349, dentro do bloqueio de alteração RN04.1: chamada equivalente com `getMensagem('MSG_15')`.

Em ambos os casos o código de mensagem foi passado como **string** (`'MSG_14'`, `'MSG_15'`).

**Proposta original (rejeitada pelo `byarq`):** trocar as duas chamadas para `getMensagem(14)` e `getMensagem(15)` (inteiros em vez de strings), mantendo a chamada ao helper PHP.

**Correção real decidida pelo `byarq`:** **remover completamente** a chamada ao helper PHP `getMensagem()` nos dois pontos (`OcoSubtOcorrencia::ativinativ()` e o bloqueio RN04.1 em `OcoOcorrencia`). O controller deve apenas retornar `$ret['msg'] = 14;` / `$ret['msg'] = 15;` (**inteiro puro**, sem montar texto em PHP) — o mesmo padrão já usado corretamente em `store()`/`delete()` dessas mesmas telas. Quem resolve o texto da mensagem é o `boxAlert()` no front, via `GET /mensagem/{id}` — não o backend.

> Nota de rastreabilidade: o helper `mensagem_helper.php::getMensagem()` (que usa colunas `msg_codigo`/`msg_texto` inexistentes no schema real de `cfg_mensagem` — schema real tem `msg_id`/`msg_titulo`/`msg_mensagem`/`msg_tipo`/`msg_cor`) fica **fora de escopo** desta feature. É uma divergência pré-existente mais ampla, não deve ser corrigida agora — registrar como item para ticket de arquitetura separado.

**Ação para o `bydev`:**
1. Em `OcoSubtOcorrencia::ativinativ()` e no bloqueio RN04.1 de `OcoOcorrencia`, remover a chamada a `getMensagem()` e retornar apenas o `msg_id` inteiro (`$ret['msg'] = 14;` / `$ret['msg'] = 15;`), conforme o novo fluxo detalhado no documento de desenvolvimento (RN06.2 na Seção 5, RN04.1 na Seção 6).
2. Revisar **todas** as chamadas a `getMensagem()` introduzidas nesta feature (T9, T11, T12) — nenhuma deve permanecer no backend para resolver texto de mensagem ao usuário; o padrão é sempre retornar o `msg_id` inteiro e deixar o front (`boxAlert()`) resolver o texto.
3. Não alterar `mensagem_helper.php` neste ciclo — está fora de escopo (ver nota de rastreabilidade acima).

---

### Bloqueante 2 — "Ação extra" em T12 (RN03.15) não produz efeito real para ações não pré-configuradas no subtipo

**Status:** decisão de arquitetura/produto pendente do `byarq` antes de qualquer correção pelo `bydev`.

**Situação encontrada:**

Em `OcoTrataOcorrencia::store()`, o `switch ($valor->tpa_tipo)` resolve a configuração da ação executada via:

- `case 3` (Gerar Movimentação) → `gerarMovimentacao()` chama `$this->subtocorrencia->getTOAcao($postado['sut_id'], $acao->tpa_id)[0] ?? false` — só encontra configuração se aquele `tpa_id` já estiver cadastrado em `oco_subt_ocorrencia_acao` para aquele subtipo (ou seja, configurado previamente via T9).
- `case 4` (Alterar Status, RN03.18.1) → `resolveStatusFinal()` chama `getAcaoPorId($tpa_id, $sut_id)` — mesma limitação de dependência de configuração prévia no subtipo.

**Consequência:** uma ação verdadeiramente "extra" (adicionada manualmente pelo usuário em T12 via o bloco "Adicionar Ação" da RN03.15 — por definição, uma ação que **não** está configurada no subtipo) nunca encontra `tmo_id`/`stt_id` correspondentes:

- Para `tpa_tipo=3`: resultado é um no-op silencioso — não gera movimentação nenhuma, sem avisar o usuário.
- Para `tpa_tipo=4`: cai no `stt_id` default (30/Finalizada — RN03.18.1), mascarando que a ação escolhida pelo usuário não teve efeito real.
- Apenas `tpa_tipo=1` (Justificar, que não depende de configuração prévia) funciona como esperado quando adicionado como ação extra.

**Divergência identificada:** isso contraria a RN03.15 do documento de requisito de T12 ("permite inclusão de ações extras da tela T8"). Hoje a inclusão existe na UI (select alimentado por `criaSelectRelativo('oco_tipo_acao', ...)`, conforme implementado), mas o efeito de negócio da ação escolhida é ignorado silenciosamente quando ela não está pré-configurada no subtipo.

**Decisão necessária do `byarq`** (não é escolha do `bydoc` nem do `bydev`) — duas alternativas levantadas pelo `byrev`:

| Alternativa | Descrição | Implicação |
|---|---|---|
| **(a)** Restringir o select | Limitar as opções de "ação extra" em T12 a apenas tipos que fazem sentido sem configuração adicional (ex.: apenas `tpa_tipo=1` "Justificar") | Menor esforço de código; reduz o alcance da RN03.15 em relação ao requisito original (não permite mais qualquer ação de T8 como extra) |
| **(b)** Coletar dados extras na linha | Coletar, na própria linha de "ação extra" em T12, os dados que os outros tipos precisam (ex.: tipo de movimentação para `tpa_tipo=3`, status alvo para `tpa_tipo=4`) — equivalente ao que já existe hoje na aba Ações de T9 para ações pré-configuradas | Mantém a RN03.15 fiel ao requisito original; exige UI adicional condicional por tipo de ação e possivelmente ajuste de persistência em `OcorreTrataOcorrenciaModel` |

Esta decisão também afeta diretamente o item "Pontos aceitáveis" abaixo referente a `resolveStatusFinal()`, que está acoplado a este gap.

**Decisão do `byarq`:** alternativa **(b)** escolhida — coletar os dados extras na própria linha da ação adicionada. **Não** restringir a "Justificar". Justificativa: a RN03.15 aprovada permite explicitamente ações extras sem restrição de tipo; o padrão de UI necessário já existe em `EntOcoSubtOcorrencia::defCamposAcao()` (campos condicionais `divmovi`/`divtela`/`divstat` conforme `tpa_tipo`) — é reaproveitamento, não UX nova.

**Encaminhamento concreto para o `bydev`:**

- Na linha de "ação extra" em T12 (`finalizar()`), replicar o mesmo padrão condicional de campos (`tmo_id` para `tpa_tipo=3`, `stt_id` para `tpa_tipo=4`, `tel_id` para `tpa_tipo=2`) já usado em T9.
- Em `store()`, quando a ação executada for uma ação extra (sem correspondência em `oco_subt_ocorrencia_acao`), usar os valores informados na própria linha (`tmo_id`/`stt_id`/`tel_id` vindos do POST daquela linha) em vez de buscar via `getTOAcao()`/`getAcaoPorId()` — que continuam sendo usados normalmente para ações de origem (pré-configuradas no subtipo).
- `gerarMovimentacao()`/`resolveStatusFinal()` precisam aceitar dados vindos da linha quando a ação for extra, e só cair para `getTOAcao()`/`getAcaoPorId()` quando for ação de origem.
- **Persistência:** não é necessário nova tabela/coluna — os dados da ação extra trafegam apenas no POST do formulário de tratativa e são processados dentro do próprio `store()`; não persistem em `oco_subt_ocorrencia_acao` (exclusivo do cadastro em T9). Se o `bydev` perceber necessidade real de tabela de histórico/auditoria da tratativa, deve sinalizar antes de criar schema novo — não criar unilateralmente.

Detalhamento completo já incorporado ao documento de desenvolvimento aprovado (RN03.15, Seção 7).

---

## 2. Pontos já avaliados como aceitáveis (registro de rastreabilidade)

Itens abaixo foram avaliados pelo `byrev` e **não geram apontamento** — não precisam de ação do `bydev` neste ciclo:

- **`removeAcaoExtra()` customizado em vez de `exclui_campo()`** — aceitável; justificado pela estrutura HTML diferente entre a linha de ação extra (`<tr>`) e o padrão de linha repetida em `.row` usado por `exclui_campo()`.
- **`resolveStatusFinal()` reaproveitando `getAcaoPorId()` existente** — aceitável como reaproveitamento de código, mas **acoplado ao Bloqueante 2** acima: a correção definitiva desse método depende da decisão do `byarq` sobre como ações extras não configuradas devem se comportar.
- **`edit()` em T11 usando `throw new \Exception(...)` não capturado para RN04.1** (resulta em página de erro padrão do CI4 em vez de resposta JSON) — aceitável neste ciclo por reaproveitar padrão pré-existente no mesmo método. Registrado como **sugestão de melhoria futura não-bloqueante** (inconsistência de UX entre `edit()` e `store()` para a mesma RN) — não precisa ser corrigido neste ciclo.

---

## 3. Sugestões de melhoria (não-bloqueantes)

- **`OcoSubtOcorrencia::ativinativ()`** — no bloco `catch`, o ramo `else` mantém o comportamento pré-existente de sempre atribuir `$ret['msg'] = 14` para qualquer exceção capturada, mesmo erros de banco não relacionados a pendências de ocorrência. Isso é enganoso (mensagem incorreta para o usuário em cenários de erro genérico). É um bug pré-existente, não introduzido nesta sessão — mas como o bloco foi reescrito por completo nesta feature (RN06.2), é uma boa oportunidade para corrigir junto.
  **Decisão do `byarq`:** **aprovado corrigir agora** — já está sendo reescrito para o Bloqueante 1. Diferenciar exceção de "possui pendência" (→ `$ret['msg'] = 14` com `$ret['pendencias']`) de qualquer outro erro genérico (→ usar `$ret['msg'] = 17`, "Problema no Sistema", já cadastrada em `cfg_mensagem`, em vez de mascarar como pendência). Detalhamento já incorporado ao documento de desenvolvimento (RN06.2, Seção 5).
- **`OcorreSubtOcorrenciaModel::getUsoGestao()`** ficou sem nenhuma chamada após a RN06.2 (substituído por `getPendenciasGestao()`). O documento de desenvolvimento permitia "substituir ou complementar" (Seção 5, RN06.2) — manter o método é aceitável, mas considerar removê-lo se não há intenção de reaproveitá-lo, para evitar código morto.
  **Decisão do `byarq`:** `bydev` deve rodar grep por `getUsoGestao` em todo o projeto antes de remover — se não houver nenhum outro chamador (nem em T10/`OcoTipoOcorrencia` nem em outro lugar), remover; se houver, manter e só registrar como está.

---

## 4. Conformidade geral verificada (sem apontamentos)

Itens abaixo foram checados pelo `byrev` e estão conformes com o documento de desenvolvimento aprovado e com os padrões de `docs/referencia/` — registrados apenas para rastreabilidade:

- Uso correto de `MyCampo`/`criaSelectRelativo()`/`crCheckbox()` em `EntOcoSubtOcorrencia`/`EntOcoTratativa`, conforme `rascunho-MyCampo.md`.
- `executaAjaxWait`/`boxAlert` seguidos corretamente, sem `jQuery.ajax`/`alert`/`confirm` nativo introduzido — conforme `rascunho-runtime-js.md`.
- Hook em `my_default.js` segue o padrão já usado para outros controllers.
- Nenhum arquivo fora do escopo foi tocado, exceto `vw_titulo.php` — identificado como edição paralela do próprio usuário, fora deste ciclo.
- Nenhuma RN listada no documento aprovado como "sem alteração necessária" foi mexida (T9: RN02.1–03, RN03.1–05, RN03.7, RN03.8–09, RN04.3, RN05.1–02, RN06.1, RN06.3; T11: RN02.1–03, RN03.1.1–05, RN03.2.1–04, RN03.2.6, RN03.3–04, RN04.2, RN04.3, RN05.2; T12: RN02.1–04, RN03.1–14, RN03.16–17).
- RN03.6, RN03.1.6/RN03.2.5, RN03.18, RN03.18.2 conferem exatamente com o documento aprovado.
- Nome da coluna da migration (`sta_fina`) bate 100% com o código que a referencia.
- Sem `debug()` novo não comentado introduzido no código revisado.

---

## 5. Encaminhamento

1. ~~**`byarq`** — decidir entre as alternativas (a) e (b) do **Bloqueante 2**; validar a observação sobre a assinatura de `getMensagem()` no **Bloqueante 1**.~~ **Concluído** — decisões registradas nas Seções 1 e 3 acima (Bloqueante 1: remoção do `getMensagem()` no controller, retorno de `msg_id` inteiro puro; Bloqueante 2: alternativa (b); catch genérico de `ativinativ()`: aprovado corrigir; `getUsoGestao()` órfão: remover somente após grep confirmando ausência de outros chamadores).
2. **`bydev`** — aplicar a correção do Bloqueante 1 (remoção de `getMensagem()` do controller, RN06.2/RN04.1); aplicar a correção do Bloqueante 2 (alternativa (b) — ação extra com dados coletados na própria linha, RN03.15); aplicar as duas sugestões não-bloqueantes da Seção 3 (catch diferenciado de `ativinativ()`; remoção condicional de `getUsoGestao()` após grep). Referências completas de implementação: documento de desenvolvimento aprovado, Seção 5 (RN06.2), Seção 6 (RN04.1) e Seção 7 (RN03.15).
3. Retornar ao `byrev` para nova rodada de revisão após as correções.
