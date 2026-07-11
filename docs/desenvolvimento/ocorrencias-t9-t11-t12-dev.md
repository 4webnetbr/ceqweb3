# Documento de Desenvolvimento — Módulo Ocorrências (T9, T11, T12)

**Projeto:** CeqWeb 3.0
**Módulo:** Ocorrências (`app/Controllers/Ocorrencia`)
**Telas envolvidas:** T9 — Subtipos de Ocorrências (tel_id=49) · T11 — Gestão de Ocorrências (tel_id=56) · T12 — Tratativa de Ocorrências (tel_id=61)
**Tipo de trabalho:** Evolução de código existente (gap analysis) — **não é tela nova**. Base de comparação: documentos de requisito v1.5 (T9), v1.6 (T11), v1.4 (T12).
**Origem:** Plano de arquitetura aprovado pelo `byarq`.
**Status:** Aprovado para codificação.

---

## 1. Contexto e premissas

T9, T11 e T12 já estão em produção. Este ciclo não recria as telas — ajusta regras de negócio (RN) específicas identificadas como divergentes do requisito atual, corrige um bug confirmado e remove resíduos de código sem uso. Toda RN não listada como gap abaixo já está implementada corretamente e **não deve ser tocada** (mudança cirúrgica — alterar somente o necessário).

Telas já cadastradas em `cfg_tela` (tel_id 33→T10, 49→T9, 56→T11, 61→T12) e rotas já registradas em `app/Config/Routes.php` (linhas 39-52) para os 4 controllers do namespace `Ocorrencia`. Nenhum cadastro novo de tela é necessário neste ciclo.

Mensagens `cfg_mensagem` reaproveitadas (nenhum cadastro novo): MSG 1, 2, 3, 4, 6, 7, 8, 14, 15. Conforme `rascunho-helpers-php.md`, mensagens reutilizáveis devem ser referenciadas por código via `getMensagem()`/`boxAlert(codigo, ...)`, nunca string solta — este ponto deve ser respeitado em todas as RNs abaixo que envolvem mensagem de erro/confirmação.

Status utilizados (tabela `cfg_status`, mod_id 22, tela 56) — já existentes, nenhum cadastro novo:
- `stt_id = 28` — Pendente
- `stt_id = 29` — Finalização Automática
- `stt_id = 30` — Finalizada (sinônimo de "concluída" nos documentos de requisito — não criar status novo)

### Arquivos-chave por tela

| Tela | Controller | Entity | Model |
|---|---|---|---|
| T9 | `app/Controllers/Ocorrencia/OcoSubtOcorrencia.php` | `app/Entities/Ocorrencia/EntOcoSubtOcorrencia.php` | `app/Models/Ocorre/OcorreSubtOcorrenciaModel.php` |
| T11 | `app/Controllers/Ocorrencia/OcoOcorrencia.php` | `app/Entities/Ocorrencia/EntOcoOcorrencia.php` | `app/Models/Ocorre/OcorreOcorrenciaModel.php` |
| T12 | `app/Controllers/Ocorrencia/OcoTrataOcorrencia.php` | `app/Entities/Ocorrencia/EntOcoTratativa.php` | `app/Models/Ocorre/OcorreTrataOcorrenciaModel.php` |

Referências de padrão (já implementadas corretamente, usar como espelho de convenção):
- T10 — `OcoTipoOcorrencia.php` / `EntOcoTipoOcorre.php` / `OcorreTipoOcorrenciaModel.php`
- T8 — `OcoTipoAcao.php` / `EntOcoTipoAcao.php` / `OcorreTipoAcaoModel.php`

### Decisões já fechadas pelo usuário (não estão em discussão)

1. `oco_subt_ocorrencia.sut_fina` é **mantido** no schema como campo derivado/calculado — não é eliminado.
2. Bloqueio de "ação não permitida" reaproveita **MSG_15** existente — nenhuma mensagem nova.
3. Bug confirmado no `case 4` de `OcoTrataOcorrencia::store()` (Alterar Status chamando `gerarMovimentacao()` indevidamente) — corrigir.
4. "Concluída" (linguagem dos documentos de requisito) = `stt_id=30` Finalizada — sinônimo, não é status novo.
5. Subtipo sem nenhuma ação cadastrada em T9 → mantém tratamento como Finalização Automática (`stt_id=29`).
6. `Buscas/buscaProdutoporLote` (integração `E075PRO`/`E207DLS`) — **não investigado, fora de escopo**. Assumido correto; qualquer problema será tratado como bug encontrado em testes, não antecipado aqui.
7. Arquivos residuais listados na Seção 2 serão removidos — confirmado sem referências ativas no sistema.
8. Ícones/cores: manter o padrão visual atual do sistema (`fas fa-check`, classes `btn-outline-*`) — **não** alinhar aos hex/ícones exatos descritos nos documentos de requisito.

---

## 2. Ordem de codificação

1. Remoção dos arquivos residuais (Seção 3).
2. Migration `sta_fina` em `dev_ocorrencia_db` — roda automaticamente neste ciclo em ambiente de dev; produção **exige nova confirmação explícita do usuário** antes do deploy (ver Seção 4).
3. T9 — mover finalização automática para a aba Ações, persistir `sta_fina` + `sut_fina` derivado, reativar bloqueio de inativação com listagem de pendências (RN06.2).
4. T11 — criar `getStatusInicial()`, substituir regra de status em `store()`/`storetmp()`, reativar bloqueios de alterar (RN04.1) e excluir (RN05.1).
5. T12 — UI de "incluir ação extra" (RN03.15), corrigir bug do `case 4` (RN03.18), reescrever status final (RN03.18.1), confirmação MSG 6 no front (RN03.18.2).

---

## 3. Remoção de arquivos residuais (primeira etapa)

Confirmado pelo `byarq`: nenhuma rota referencia esses arquivos. `nov.php` e `teste.php` declaram a mesma classe `OcoNovOcorrencia` (mesmo namespace) que já existe corretamente em `OcoNovOcorrencia.php` — duplicidade de nome de classe, risco de ambiguidade no classmap do Composer.

**`app/Controllers/Ocorrencia/`:**
- `OcoOcorrenciaBKP.php`
- `OcoTrataOcorrenciaBKP.php`
- `OcoModOcorrenciaBKP.php`
- `OcoTipoOcorrenciaBKP.php`
- `OcoTipoAcaoBKP.php`
- `nov.php`
- `teste.php`

**`app/Models/Ocorre/`:**
- `OcorreOcorrenciaModelBKP.php`
- `OcorreTrataOcorrenciaModelBKP.php`
- `OcorreModOcorrenciaModelBKP.php`
- `OcorreTipoOcorrenciaModelBKP.php`
- `OcorreTipoAcaoModelBKP.php`
- `OcorreSubtOcorrenciaModel-DESK-DOUGLAS.php`
- `OcorreOcorrenciaModel-DESK-DOUGLAS-2.php`

**Explicitamente NÃO remover neste ciclo** (fora de escopo, não autorizado): resíduos em `app/Entities/Ocorrencia/` (`EntOcoOcorrencia-DESK-DOUGLAS*.php`, `EntOcoTipoOcorre-DESK-DOUGLAS*.php`, `EntOcoTratativa-DESK-DOUGLAS.php`, `EntOcoTipoAcao-DESK-DOUGLAS.php`).

---

## 4. Migration aprovada

**Escopo:** aplicar em ambiente de **dev** (`dev_ocorrencia_db`) automaticamente como parte deste ciclo de codificação.

**Produção:** **NÃO aplicar** junto com o commit desta feature. Exige confirmação explícita e separada do usuário antes do deploy — ver passo a passo no documento de entrega (`docs/entrega/`), a ser produzido ao final do ciclo.

```sql
ALTER TABLE `oco_subt_ocorrencia_acao`
  ADD COLUMN `sta_fina` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Finalização Automática' AFTER `stt_id`;
```

`oco_subt_ocorrencia.sut_fina` permanece no schema (decisão 1, Seção 1) — passa a ser campo **derivado**, calculado em `OcoSubtOcorrencia::store()`:
- `'S'` se todas as ações do subtipo tiverem `sta_fina='S'` (ou não houver nenhuma ação cadastrada);
- `'N'` caso contrário.

Isso preserva compatibilidade com `OcoOcorrencia::storetmp()`, que hoje lê `subtipo->sut_fina` diretamente — nenhuma mudança de contrato nesse ponto de leitura.

---

## 5. T9 — Subtipos de Ocorrências

Arquivos: `OcoSubtOcorrencia.php` / `EntOcoSubtOcorrencia.php` / `OcorreSubtOcorrenciaModel.php`.

### RN03.6 — Finalização automática movida para a aba Ações

**Situação atual:** campo único `sut_fina` (Sim/Não) declarado em `defCampos()`, na aba Dados Gerais.

**O que muda:**
- Remover o campo `sut_fina` de `defCampos()` (aba Dados Gerais). Ele deixa de ser editável diretamente pelo usuário.
- Adicionar um checkbox `sta_fina` por linha em `defCamposAcao()` (aba Ações), seguindo o padrão `MyCampo::crCheckbox()` (checkbox estilo switch/toggle) já usado nas demais colunas de linha dessa aba.
- Ajustar `store()`:
  1. Gravar `sta_fina` em cada linha de `oco_subt_ocorrencia_acao` (persistência 1:1 com o checkbox da linha).
  2. Calcular o `sut_fina` derivado (regra da Seção 4) e gravar em `oco_subt_ocorrencia` — mantém compatibilidade com o consumidor atual (`OcoOcorrencia::storetmp()`).

**Arquivos afetados:** `OcoSubtOcorrencia.php` (`defCampos()`, `defCamposAcao()`, `store()`), `EntOcoSubtOcorrencia.php` (se necessário expor `sta_fina` como atributo de linha), `OcorreSubtOcorrenciaModel.php` (persistência de `oco_subt_ocorrencia_acao`). Depende da migration da Seção 4.

### RN06.2 — Bloqueio de inativação com listagem de pendências

**Situação atual:** checagem de uso em gestão (bloco `getUsoGestao`) está **comentada** no método `ativinativ()` do controller. `OcorreSubtOcorrenciaModel::getUsoGestao()` retorna apenas um `bool` (existe/não existe uso).

**O que muda:**
- Reativar a checagem em `ativinativ()` (remover comentário/reabilitar chamada).
- Evoluir o método do model: substituir/complementar `getUsoGestao()` por `getPendenciasGestao(int $sut_id)`, que retorna a **lista** de ocorrências pendentes vinculadas ao subtipo (não apenas um booleano).
- No controller, ao bloquear a inativação, exibir **MSG 14** via `boxAlert()`, passando a lista retornada em `dadosExtra` (conforme padrão de `boxAlert(mensagem, erro, url, aguardaClique, tipo, ..., titulo, dadosExtra)` documentado em `rascunho-runtime-js.md`), para o usuário visualizar quais ocorrências pendentes impedem a inativação.

**Arquivos afetados:** `OcoSubtOcorrencia.php` (`ativinativ()`), `OcorreSubtOcorrenciaModel.php` (novo método `getPendenciasGestao()` ou evolução de `getUsoGestao()`), front da tela T9 (chamada `boxAlert(14, ...)` com `dadosExtra`).

### RNs sem alteração necessária

Conforme levantamento do `byarq`, as seguintes RNs de T9 já estão implementadas corretamente e não são tocadas neste ciclo: RN02.1–03, RN03.1–05, RN03.7, RN03.8–09, RN04.3, RN05.1–02, RN06.1, RN06.3.

- **RN03.7** (aba Permissões) — já implementada via `defPermissoes()`.
- **RN04.3** (salvar sem alteração real → MSG 7) — comportamento genérico do handler padrão `#bt_salvar`/`data-valid` (`rascunho-runtime-js.md`: verifica se algum campo com `data-valid` foi alterado antes de confirmar/submeter), sem gap de código server-side a corrigir em T9.

---

## 6. T11 — Gestão de Ocorrências

Arquivos: `OcoOcorrencia.php` / `EntOcoOcorrencia.php` / `OcorreOcorrenciaModel.php` / `OcorreSubtOcorrenciaModel.php`.

### RN03.1.6 / RN03.2.5 — Cálculo do status inicial da ocorrência

**Situação atual:** `store()` e `storetmp()` decidem o `stt_id` inicial a partir do `sut_fina` único do subtipo, com um caso especial adicional para `tpa_id == 12`.

**O que muda:**
- Criar `OcorreSubtOcorrenciaModel::getStatusInicial(int $sut_id): int`, que consulta todas as linhas de `oco_subt_ocorrencia_acao` do subtipo e resolve:
  - sem nenhuma ação cadastrada → `stt_id = 29` (Finalização Automática, mantido — decisão 5, Seção 1);
  - todas as ações com `sta_fina = 'S'` → `stt_id = 29`;
  - ao menos uma ação com `sta_fina = 'N'` → `stt_id = 28` (Pendente).
- Substituir, em `store()` e `storetmp()`, a lógica atual (baseada em `sut_fina` único + caso especial `tpa_id==12`) por uma chamada a `getStatusInicial()`. O caso especial `tpa_id==12` é eliminado — a nova regra é genérica por ações do subtipo.

**Arquivos afetados:** `OcorreSubtOcorrenciaModel.php` (novo método `getStatusInicial()`), `OcoOcorrencia.php` (`store()`, `storetmp()`). Depende do gap RN03.6 de T9 (existência confiável de `sta_fina` por linha).

### RN04.1 — Bloqueio de alteração quando há vínculo (GAP DE SEGURANÇA)

**Situação atual:** o botão "Alterar" é apenas **ocultado no front-end** quando `req_id`/`rep_id` estão preenchidos. Não há validação correspondente no backend — uma requisição forjada diretamente ao endpoint de `edit`/`store` contorna o bloqueio.

**O que muda:**
- Adicionar validação server-side em `edit($id)`: se `req_id`/`rep_id` do registro não forem nulos, retornar erro sem carregar o formulário de edição.
- Adicionar a mesma validação no início de `store()`, no branch em que `oco_id` já existe (fluxo de update): se `req_id`/`rep_id` não nulos, retornar erro **sem gravar**.
- Mensagem de erro: reaproveitar **MSG_15** ("Alteração não Permitida"), conforme decisão 2 (Seção 1).

**Arquivos afetados:** `OcoOcorrencia.php` (`edit()`, `store()`).

### RN05.1 — Bloqueio de exclusão (reativar checagem)

**Situação atual:** checagem de "só pode excluir se pendente e sem vínculo" está **comentada** em `delete()`.

**O que muda:**
- Reativar/reescrever a checagem em `delete()`: bloquear a exclusão se `req_id` estiver preenchido **OU** `stt_id != 28` (Pendente). Retornar **MSG 3** quando bloqueado.

**Arquivos afetados:** `OcoOcorrencia.php` (`delete()`).

### RNs sem alteração necessária

RN02.1–03, RN03.1.1–05, RN03.2.1–04, RN03.2.6, RN03.3–04, RN04.2, RN04.3, RN05.2 já implementadas sem gap — não tocar.

- **RN04.2** (cancelar com alteração pede confirmação MSG 2 no fluxo de alterar) — comportamento genérico do handler padrão `#bt_salvar`/`data-valid` (`rascunho-runtime-js.md`), sem gap de código server-side a corrigir em T11.
- **RN04.3** (salvar sem alteração real → MSG 7) — mesmo comportamento genérico do handler padrão `#bt_salvar`/`data-valid` (`rascunho-runtime-js.md`), sem gap de código server-side a corrigir em T11.

---

## 7. T12 — Tratativa de Ocorrências

Arquivos: `OcoTrataOcorrencia.php` / `EntOcoTratativa.php` / `OcorreTrataOcorrenciaModel.php`.

### RN03.15 — Inclusão de ação extra na aba Ações

**Situação atual:** aba Ações em `finalizar()` exibe apenas as ações de origem trazidas do subtipo (`sta_fina='S'`), sem opção de o usuário adicionar uma ação avulsa.

**O que muda:**
- Adicionar bloco "Adicionar Ação" na aba Ações de `finalizar()`, usando `criaSelectRelativo('oco_tipo_acao', 'tpa_id', 'tpa_nome', ..., ['tpa_ativo' => 'A'])` (helper documentado em `rascunho-helpers-php.md` — resolve filtro por ativo e pelo schema/grupo de banco automaticamente), seguindo o padrão de repetição de linha (`bt-repete`/`addCampo`) já usado em T9/T10 para listas dinâmicas.
- Em `store()`, distinguir explicitamente:
  - ações **de origem** (vindas do subtipo, `sta_fina='S'`);
  - ações **adicionadas manualmente** pelo usuário na tratativa.
- Bloquear exclusão apenas das ações de origem (o usuário não pode remover uma ação que veio configurada no subtipo; pode remover as que ele mesmo adicionou).

**Arquivos afetados:** `OcoTrataOcorrencia.php` (view/campos de `finalizar()`, `store()`), `OcorreTrataOcorrenciaModel.php` (se a distinção origem/manual exigir persistência de flag adicional — a confirmar durante codificação; se necessário, sinalizar a `byarq` antes de alterar schema).

### RN03.18 — Correção de bug: case 4 (Alterar Status) chamando `gerarMovimentacao()` (confirmado)

**Situação atual:** em `store()`, o `switch` de tipos de ação tem um `case 4` (Alterar Status) que, por engano, executa o mesmo bloco de código do `case 3` (Gerar Movimentação) — ou seja, chama `gerarMovimentacao()` indevidamente ao processar uma ação que deveria apenas alterar status.

**O que muda:**
- Separar o `case 4` em lógica própria: apenas resolve o `stt_id` alvo da ação, **sem** chamar `gerarMovimentacao()`/`geraMovimentoRequisicoes`.

**Arquivos afetados:** `OcoTrataOcorrencia.php` (`store()`, `switch` de tipos de ação).

### RN03.18.1 — Reescrita da definição de status final

**Situação atual:** a lógica que determina o `stt_id` final da ocorrência ao concluir a tratativa não segue de forma confiável a configuração de "Alterar Status" do subtipo (consequência direta do bug do `case 4`).

**O que muda:**
- Reescrever a definição de `stt_id` final em `store()`:
  - buscar, entre as ações executadas na tratativa, se há alguma do tipo "Alterar Status" (`tpa_tipo='4'` no catálogo T8 — `OcoTipoAcao`) vinculada ao subtipo;
  - se sim, usar o `stt_id` configurado em `oco_subt_ocorrencia_acao.stt_id` dessa ação;
  - se não, usar `stt_id = 30` (Finalizada — "concluída", decisão 4, Seção 1).

**Arquivos afetados:** `OcoTrataOcorrencia.php` (`store()`). Depende da correção RN03.18 (case 4).

### RN03.18.2 — Confirmação MSG 6 antes de submeter (front-end apenas)

**Situação atual:** o formulário de tratativa submete diretamente via `submeteForm()`, sem alerta prévio quando a ação "Gerar Movimentação" está selecionada.

**O que muda:**
- Ajuste **apenas de front-end**: antes de submeter o formulário de tratativa, se houver ação "Gerar Movimentação" selecionada entre as ações marcadas, disparar `boxAlert(6, ...)` (confirmação, conforme `rascunho-runtime-js.md`) antes de chamar `submeteForm()`.
- **Sem mudança de contrato** no `store()` do controller — é só uma confirmação adicional client-side antes do POST já existente.

**Arquivos afetados:** view/JS da tela T12 (arquivo JS da tela ou bloco de script da view de `finalizar()`).

### RNs sem alteração necessária

RN02.1–04, RN03.1–14, RN03.16–17 já implementadas sem gap — não tocar.

---

## 8. Itens fora de escopo deste ciclo

- **`Buscas/buscaProdutoporLote`** e integração **E075PRO/E207DLS** — não investigar, não alterar. Qualquer problema encontrado aqui durante os testes deve ser tratado como bug novo, fora deste plano.
- **Ícones/cores** — manter o padrão visual atual do sistema (`fas fa-check`, classes `btn-outline-*`); não alinhar aos hex/ícones exatos descritos nos documentos de requisito v1.4/v1.5/v1.6.
- **Resíduos em `app/Entities/Ocorrencia/*-DESK-DOUGLAS*.php`** — não remover neste ciclo (não autorizado).
- **Deploy da migration em produção** — não incluído neste ciclo de codificação; requer confirmação explícita e separada do usuário (ver Seção 4 e documento de entrega).
- Qualquer RN não listada nas Seções 5, 6 e 7 como gap está fora de escopo por já estar conforme — não deve sofrer alteração "de aproveitamento".

---

## 9. Critérios de pronto

- Todos os arquivos residuais listados na Seção 3 removidos, sem quebra de rotas/classmap (`composer dump-autoload` limpo).
- Migration `sta_fina` aplicada em `dev_ocorrencia_db`; produção **não tocada** sem nova confirmação explícita.
- T9: `sut_fina` não é mais editável em Dados Gerais; `sta_fina` funcional por linha em Ações; `sut_fina` calculado corretamente em `store()`; `ativinativ()` bloqueia com listagem de pendências (MSG 14 + `dadosExtra`).
- T11: `getStatusInicial()` implementado e usado em `store()`/`storetmp()`; `edit()`/`store()` bloqueiam alteração com `req_id`/`rep_id` preenchidos (MSG 15) tanto no front quanto no backend; `delete()` bloqueia exclusão fora da regra pendente/sem vínculo (MSG 3).
- T12: aba Ações permite adicionar ação extra via `criaSelectRelativo()`; exclusão bloqueada apenas para ações de origem; `case 4` não chama mais `gerarMovimentacao()`; status final segue a ação "Alterar Status" do subtipo quando existir, senão `stt_id=30`; confirmação MSG 6 disparada no front quando há ação "Gerar Movimentação" selecionada.
- Nenhuma RN listada como "sem alteração necessária" neste documento foi tocada.
- `byrev` sem novos apontamentos pendentes; `bytest` com plano de testes cobrindo cada RN desta lista.

---

## 10. Rastreabilidade

Cada RN listada nas Seções 5–7 deve ser referenciada pelo seu código (ex: "RN03.18") em commits, comentários de revisão (`byrev`) e casos de teste (`bytest`), para permitir rastrear diretamente do requisito até a mudança de código correspondente.
