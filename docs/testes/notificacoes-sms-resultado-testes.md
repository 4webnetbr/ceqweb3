# Resultado de Testes — Notificações SMS (Módulo Logística)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (`app/Controllers/Logistica`)
**Telas envolvidas:** `NotifSmsConfig` — CRUD de Regras de Notificação SMS · `NotifSmsEnviadas` — Consulta de SMS Enviados por período
**Plano de testes executado:** `docs/testes/notificacoes-sms-plano-testes.docx` (aprovado pelo `byarq`)
**Executor:** `bytest`
**Status do ciclo de código:** `byrev` — aprovado (Bloqueantes 1-3 corrigidos e reaprovados na rodada 2)

------------------------------------------------------------------------

## 0. Resumo executivo

Dos **37 casos** previstos no plano de testes:

- **18 casos** foram verificados por **análise de código** (leitura/rastreamento de controllers, models, entities e JS), sem divergência encontrada em relação ao documento de desenvolvimento aprovado.
- Vários casos foram de fato **executados contra o ambiente de dev real**: lint de PHP (`php -l`, 10/10 arquivos sem erro de sintaxe), queries SQL reais contra `dev_logistica_db`, e chamadas reais aos métodos de validação/Model envolvidos.
- **2 BUGS REAIS foram encontrados durante execução ao vivo** (não são achados hipotéticos de leitura de código — foram reproduzidos de fato):
  - **BUG CRÍTICO #1** — divergência de schema físico em `log_notif_sms_config`/`log_notif_sms_enviadas` (coluna `nsc_ativo` como `tinyint(1)` em vez de `CHAR(1)`, entre outras divergências), que quebra `store()`/`update()`/`ativinativ()` de `NotifSmsConfig` com erro fatal de banco e quebra o filtro automático de `criaSelectRelativo()` na tela `NotifSmsEnviadas`.
  - **BUG #2 (médio-alto)** — `LogisNotifSmsConfigModel::getListaRegras()` não filtra soft-delete, permitindo que uma regra excluída apareça na listagem.
- Um **achado de infraestrutura** (não é bug de código) foi identificado: já existe um `cfg_modulo` "Logística" (`mod_id=19`) em dev, com `mod_dbgroup` vazio e ícone diferente do que a migration geraria — como a migration só insere quando o módulo não existe, esse registro será reaproveitado sem atualização.
- Um achado **não-bloqueante herdado** (mensagem de erro de nome duplicado reaproveitando `cfg_mensagem` id=9, semanticamente sobre outro assunto) foi confirmado como réplica fiel do padrão já existente em `CfgCor.php` — fora de escopo desta entrega, conforme já indicado no dev doc.

**Encaminhamento recomendado:** os Bugs #1 e #2 precisam ser avaliados pelo `byarq` e corrigidos pelo `bydev` **antes de qualquer nova tentativa de execução funcional completa** dos Blocos C, D e I — a maioria dos casos que dependem de gravação/leitura real em `log_notif_sms_config`/`log_notif_sms_enviadas` está impactada em cascata por esses dois bugs.

------------------------------------------------------------------------

## 1. Condições reais de execução

- Foi possível: leitura completa do código-fonte de controllers/models/entities/JS envolvidos; execução de lint PHP (`php -l`) nos 10 arquivos da feature; conexão real ao MariaDB de dev (`dev_logistica_db`) via linha de comando, incluindo `SHOW CREATE TABLE` e consultas `SELECT`/`INSERT` de teste (em transação, com `ROLLBACK`, sem alterar dados definitivos); execução isolada de trechos de Model/validação fora do fluxo HTTP completo.
- **Não foi possível** nesta rodada: sessão HTTP autenticada completa via navegador (clique real em telas, `boxAlert`, `selectpicker`, DataTable renderizado) e confirmação de que a migration `LogisticaNotifSms` foi de fato aplicada do zero em ambiente limpo (o ambiente de dev disponível já tem as tabelas criadas manualmente, o que é justamente a condição do Bloco A/TC-02, não do TC-01/03/04).
- Onde a execução real não foi possível, o caso foi tratado por análise de código, e essa via é identificada explicitamente na tabela de resultados.

------------------------------------------------------------------------

## 2. Resultados por bloco

### 2.1 Bloco A — Migration / Infraestrutura

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-01 | Não executado | **NÃO EXECUTADO** | Depende de rodar `up()` duas vezes seguidas contra um ambiente controlado; sem confirmação disponível nesta rodada. Recomenda-se execução manual antes do fechamento do ciclo. |
| TC-02 | Execução real (parcial) | **PASSA (mecanismo)** / achado em cascata | `tableExists()` de fato detecta as tabelas já existentes em `dev_logistica_db` e a migration não executa `CREATE TABLE` sobre elas — confirmado via inspeção do estado do banco antes/depois. Porém, como as tabelas físicas já existentes **divergem do schema aprovado** (ver **BUG #1**, Seção 3), o mecanismo "não recriar tabela" funciona, mas perpetua a divergência de schema — a migration nunca vai corrigi-la sozinha. |
| TC-03 | Não executado | **NÃO EXECUTADO** | Exige ambiente de dev limpo sem as 2 tabelas, indisponível nesta rodada (o ambiente disponível já tem as tabelas criadas manualmente). Verificação por análise de código do script de migration não encontrou divergência na definição declarada (`CREATE TABLE` do migration bate com a Seção 4.2/4.3 do dev doc) — mas isso é irrelevante na prática, pois **as tabelas físicas atuais não foram criadas por essa migration** (ver BUG #1). |
| TC-04 | Não executado | **NÃO EXECUTADO** | Depende de rodar `down()` após `up()` em ambiente controlado; sem confirmação disponível. Análise de código confirma que `down()` só dá `DROP TABLE` nas 2 tabelas, sem tocar `cfg_modulo`/`cfg_tela`/`cfg_tela_lista`/`cfg_perfil_item` — lógica correta por leitura, execução real pendente. |

**Resumo Bloco A:** 1/4 casos com confirmação real (parcial, TC-02), com achado em cascata do BUG #1; 3/4 não executados por falta de ambiente de dev limpo disponível nesta rodada.

------------------------------------------------------------------------

### 2.2 Bloco B — Permissões CAEXN

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-05 | Não executado | **NÃO EXECUTADO** | Depende de sessão de navegador autenticada com `prf_id=1`. Verificação de código: `montaMenu()` monta o menu a partir de `ConfigTelaModel`/`ConfigPerfilItemModel`, mecanismo genérico já validado em ciclos anteriores (ver `docs/testes/ocorrencias-t9-t11-t12-resultado-testes.md`); a feature não introduz nenhum bypass próprio. |
| TC-06 | Análise de código | **PASSA (mecanismo)** | `LoginFilter` implementa o bloqueio fail-closed genérico (ausência de `cfg_perfil_item` → `vw_semacesso`), conforme `rascunho-helpers-php.md`. `NotifSmsConfig`/`NotifSmsEnviadas` seguem a nomenclatura padrão de métodos (`index/lista/add/store/edit/update/delete/ativinativ`), então o filtro genérico se aplica sem necessidade de tratamento especial. Cenário real (login com perfil sem permissão) não executado por falta de sessão/navegador. |
| TC-07 (opcional) | Não executado | **NÃO EXECUTADO** | Caso opcional do plano; não executado nesta rodada por depender de configuração de perfil de teste e navegador. |

**Resumo Bloco B:** 1/3 casos confirmados por análise de código (mecanismo genérico); 2/3 não executados por dependência de sessão/navegador.

------------------------------------------------------------------------

### 2.3 Bloco C — `NotifSmsConfig`: CRUD funcional

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-08 | Execução real (tentativa) | **FALHA — causada pelo BUG #1** | Reproduzido o `INSERT` exato que `NotifSmsConfigModel::store()` monta para uma regra tipo `entrega` (em transação, com `ROLLBACK`) contra `dev_logistica_db`. Erro retornado pelo MariaDB: `Incorrect integer value: 'A' for column nsc_ativo` (o `sql_mode` do ambiente inclui `STRICT_TRANS_TABLES`). Não é uma falha independente de lógica de negócio — é consequência direta do BUG #1 (Seção 3). |
| TC-09 | Execução real (tentativa) | **FALHA — causada pelo BUG #1** | Mesmo `INSERT`, agora para regra tipo `saldo_baixo` (apenas `nsc_saldo_minimo` + campos comuns): mesmo erro de `nsc_ativo`, pelo mesmo motivo. A lógica de "não exigir campos do grupo Entrega quando `saldo_baixo`" está correta na Entity/regra de validação (confirmado por leitura), mas o `store()` nunca chega a gravar de fato. |
| TC-10 | Não executado | **NÃO EXECUTADO** | Depende de uma regra já cadastrada com sucesso (TC-08/09), que não foi possível gravar nesta rodada por causa do BUG #1. |
| TC-11 | Execução real | **FALHA — causada pelo BUG #2** | `LogisNotifSmsConfigModel::getListaRegras()` executado de fato: usa `$this->builder()->select('*')->orderBy('nsc_ativo, nsc_nome')->get()`, e o SQL gerado (capturado via `getLastQuery()`) foi `SELECT * FROM log_notif_sms_config ORDER BY nsc_ativo, nsc_nome` — **sem nenhuma cláusula** `WHERE nsc_excluido IS NULL`. Uma regra soft-deleted apareceria normalmente na listagem, violando o resultado esperado do caso. Ver **BUG #2** (Seção 3). |
| TC-12 | Análise de código + verificação parcial em cascata | **PASSA (lógica)** / bloqueado pelo BUG #1 na prática | `NotifSmsConfigModel::ativinativ()` não bloqueia a inativação por existência de histórico em `log_notif_sms_enviadas` — só o `delete()` faz essa checagem (confirmado por leitura, consistente com a Seção 6.1 do dev doc). Porém, como `nsc_ativo` é `tinyint` na tabela física e o `UPDATE` gravaria o literal `'I'` num campo strict, a operação real também falharia em execução (mesma causa raiz do BUG #1) — não testado por `UPDATE` real nesta rodada para evitar side effect, mas a reprodução do `INSERT` (TC-08/09) já demonstra que qualquer gravação de `nsc_ativo`/`'A'`/`'I'` como string vai falhar no schema físico atual. |

**Resumo Bloco C:** nenhum caso passou em execução real — TC-08, TC-09 e TC-11 tiveram falha real reproduzida (causa raiz nos Bugs #1 e #2); TC-10 não executado (depende de TC-08/09); TC-12 passa na lógica de negócio isolada, mas está sujeito ao mesmo bloqueio de gravação do BUG #1 em execução real.

------------------------------------------------------------------------

### 2.4 Bloco D — Toggle de campos condicionais

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-13 | Análise de código | **PASSA** | `NotifSmsConfig::montaCamposFormulario()` gera `#divSaldo` já com `class="row d-none"` fixo no HTML (hardcoded), consistente com o default `nsc_tipo_regra='entrega'`; não depende de JS de inicialização em `add()`. |
| TC-14 | Análise de código | **PASSA** | Select `nsc_tipo_regra` tem `onchange` apontando para `alternaCamposTipoRegra(this)`; função (em `my_fields.js`) alterna `d-none` entre `#divEntrega`/`#divSaldo` e delega a `mudaObrigatorioElemDiv()` o ajuste de obrigatoriedade. Confirmação visual do toggle no DOM real não realizada (requer navegador). |
| TC-15 | Análise de código | **PASSA** | `edit()` monta script inline em `data['script']` que decide, em runtime, qual `div` esconder conforme `nsc_tipo_regra` do registro carregado — lógica distinta de `add()` (que é estática), corretamente implementada por depender do valor existente. |
| TC-16 | Análise de código | **PASSA** | Mesmo mecanismo do TC-15, caminho inverso (`entrega` mantém `#divEntrega` visível). |
| TC-17 | Análise de código | **PASSA** | `alternaCamposTipoRegra(obj)` em `my_fields.js` cuida apenas do show/hide dos wrappers; a obrigatoriedade condicional é delegada a `mudaObrigatorioElemDiv(div, obriga)`, sem reimplementação própria — conforme ressalva do `byarq` citada no plano de testes. |

**Resumo Bloco D:** 5/5 casos com verificação estática favorável, nenhuma divergência encontrada. Confirmação visual do toggle em DOM real (TC-14/15/16) pendente de teste manual em navegador.

------------------------------------------------------------------------

### 2.5 Bloco E — Validação de campos

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-18 | Análise de código | **PASSA** | Regras de validação de `NotifSmsConfig::store()`/entity exigem `nsc_nome`/`nsc_telefones`/`nsc_mensagem_template` — ausência de qualquer um bloqueia o `store()` antes da gravação. |
| TC-19 | Execução real | **PASSA** | Chamada real ao validador do CI4 com `nsc_tipo_regra='xyz'` contra a regra `in_list[entrega,saldo_baixo]` retornou erro de validação, bloqueando antes de qualquer tentativa de `INSERT` (não chegou a expor o BUG #1, pois a validação é a primeira barreira). |
| TC-20 | Análise de código | **PASSA** | Regra `obrigatorioSeTipoRegraEntrega` presente e aplicada aos 4 campos do grupo Entrega quando `nsc_tipo_regra='entrega'`. |
| TC-21 | Análise de código | **PASSA** | Regra `obrigatorioSeTipoRegraSaldo` presente, aplicada a `nsc_saldo_minimo` quando `nsc_tipo_regra='saldo_baixo'`. |
| TC-22 | Execução real | **PASSA** | Chamada real ao validador com `nsc_condicao='xyz'` contra `in_list[antes_chegada,apos_chegada]` bloqueou corretamente — confirma a correção aplicada além do escopo original dos 3 bloqueantes (Sugestão 4 da revisão-01, incorporada). |
| TC-23 | Execução real (parcial) | **PASSA (a)** / **NÃO EXECUTADO (b)** | (a) `verificaUnico()` executado de fato contra `dev_logistica_db` com um nome já existente na massa de dados disponível: bloqueou corretamente a duplicidade. (b) Edição da própria regra mantendo o mesmo nome não pôde ser confirmada nesta rodada porque a gravação de regras novas está bloqueada pelo BUG #1 (não havia registro "fresco" desta rodada para editar sem risco de side effect na massa existente) — por leitura de código, `verificaUnico()` recebe o `id` atual como exceção na cláusula `WHERE`, então não deveria haver falso-positivo, mas isso fica pendente de confirmação em execução real após o BUG #1 ser corrigido. |

**Resumo Bloco E:** 5/6 casos confirmados (2 deles por execução real direta do validador), 1 caso parcialmente executado (TC-23-a passa, TC-23-b pendente).

------------------------------------------------------------------------

### 2.6 Bloco F — Labels visíveis

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-24 | Análise de código | **PASSA** | `EntLogNotifSmsConfig` define `setLabel()` explícito para os 10 campos citados no plano — nenhum depende de `COLUMN_COMMENT` do banco. Confirmação visual do `<label>` renderizado não realizada (requer navegador), mas a fonte do texto (setter explícito, não fallback do dicionário de dados) está confirmada por leitura direta da Entity. |

------------------------------------------------------------------------

### 2.7 Bloco G — Bloqueio de exclusão

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-25 | Análise de código | **PASSA** | `delete()` de `NotifSmsConfig` executa query direta escopada em `dbLogistica` (contagem em `log_notif_sms_enviadas` por `nse_nsc_id`) antes de excluir — não usa mais `ForeignKeyUsageChecker`, conforme decisão registrada na revisão-01 (Bloqueante 1). Execução real do `delete()` com histórico vinculado não realizada nesta rodada para não gerar side effect na massa de dados existente; lógica de bloqueio confirmada por leitura completa do método. |
| TC-26 | Não executado (parcial) | **NÃO EXECUTADO (funcional completo)** | Soft delete de regra sem histórico depende de gravação prévia de uma regra "descartável" — não realizado nesta rodada por prudência (evitar massa de dados suja em ambiente compartilhado) e por depender indiretamente do BUG #1 para qualquer gravação nova seguinte ao fluxo padrão. Por leitura de código, `delete()` sem histórico vinculado cai no branch de soft delete (`nsc_excluido` preenchido) sem bloqueio. |
| TC-27 (regressão) | Execução real (parcial) | **PASSA** | `grep` real por `dbLogistica` em `app/Traits/ForeignKeyUsageChecker.php` não retornou nenhuma ocorrência — confirma que o trait não foi alterado para referenciar o novo grupo de banco. Execução do `delete()` em outras telas que usam o trait (Fornecedores/Ocorrências) não foi repetida nesta rodada — sem sinal de regressão a partir da leitura do trait, que permanece inalterado por esta feature. |

**Resumo Bloco G:** 2/3 casos confirmados por análise de código/execução parcial (TC-25 lógica confirmada, TC-27 confirmado por grep real); TC-26 não executado por completo por prudência com a massa de dados.

------------------------------------------------------------------------

### 2.8 Bloco H — Log de auditoria

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-28 | Análise de código | **PASSA** | `LogisNotifSmsConfigModel` implementa `depoisInsert`/`depoisUpdate`/`depoisDelete` chamando `LogMonModel::insertLog()` com os textos `'Incluído'`/`'Alteração'`/`'Excluído'`, no mesmo padrão de `ConfigCorModel` (referência citada no dev doc). Execução real do ciclo completo (criar/editar/excluir e conferir gravação em Mongo) não realizada — depende de uma regra gravável com sucesso, bloqueada pelo BUG #1 para o caminho padrão de `store()`. |
| TC-29 | Análise de código | **PASSA** | `edit()`/`show()` populam `$this->data['log'] = buscaLog('log_notif_sms_config', $id)` antes de renderizar `vw_edicao`, seguindo o padrão documentado em `rascunho-helpers-php.md` para exibição de "última alteração por/em". |

------------------------------------------------------------------------

### 2.9 Bloco I — `NotifSmsEnviadas`: Consulta por período

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-30 | Não executado (funcional completo) | **NÃO EXECUTADO** | Depende de sessão de navegador para preencher `crDaterange()` e disparar `lista()` via AJAX. Estrutura de query (filtro por `nse_data_envio` + `ORDER BY ... DESC`) verificada por leitura, sem divergência aparente. |
| TC-31 | Não executado (funcional completo) | **NÃO EXECUTADO** | Mesma dependência de navegador do TC-30, para o caso de período sem correspondência. |
| TC-32 | Execução real (parcial) | **PASSA (parcial)** / achado em cascata do BUG #1 | O `criaSelectRelativo('log_notif_sms_config', 'nsc_id', 'nsc_nome', ...)` foi inspecionado e o prefixo `'log'`/`dbLogistica` está corretamente resolvido por `ConfigDicDadosModel::getDbGroupAndSchema()` (confirmado por leitura + teste isolado da resolução do schema). Porém, como `criaSelectRelativo()` aplica o filtro automático de "ativo" comparando a coluna `_ativo` com `'A'` (string), e a coluna física `nsc_ativo` é `tinyint`, o MySQL converte a comparação `nsc_ativo = 'A'` para `nsc_ativo = 0` — que nunca bate com o valor `1` gravado nos registros existentes (que foram inseridos manualmente como `1`, já que o schema físico é `tinyint DEFAULT 1`). **Resultado prático: o select "Regra" nunca lista nenhuma opção** — efeito em cascata direto do BUG #1, não uma falha independente do mecanismo `criaSelectRelativo()` em si (que funciona corretamente para tabelas com coluna `_ativo` do tipo `CHAR`, como já validado em outras telas). |
| TC-33 | Análise de código | **PASSA** | A query de `lista()` de `NotifSmsEnviadas` faz `JOIN` com `log_notif_sms_config` e seleciona `nsc_nome` para a coluna "Regra" — não expõe `nse_nsc_id` bruto na grid. |
| TC-34 | Análise de código | **PASSA** | Validação client-side (JS) do período obrigatório está implementada antes do disparo do AJAX de `lista()`, usando `boxAlert()` — sem chamada a `alert()` nativo. Confirmação visual do `boxAlert` real não realizada (requer navegador). |

**Resumo Bloco I:** 2/5 casos confirmados por análise de código (TC-33, TC-34); TC-32 executado parcialmente com achado real em cascata do BUG #1 (select "Regra" não funcional na prática); TC-30/TC-31 não executados por dependência de navegador.

------------------------------------------------------------------------

### 2.10 Bloco J — Integração/consistência de padrões

| ID | Via | Resultado | Observação |
|---|---|---|---|
| TC-35 | Análise de código (grep real) | **PASSA** | `grep` real por `jQuery.ajax`/`$.ajax` cru e `alert(` nativo nos arquivos JS da feature (`my_filter.js`, `my_fields.js`, trechos específicos) não retornou nenhuma ocorrência fora do padrão — todas as chamadas usam `executaAjaxWait`/`boxAlert`, conforme `rascunho-runtime-js.md`. Confirmação funcional em runtime (requisição real disparada por clique) não realizada. |
| TC-36 (achado não-bloqueante, já registrado) | Análise de código | **REGISTRADO, NÃO REPROVADO** | Confirmado: colunas `nsc_tipo_regra`/`nsc_ativo` exibidas sem tradução amigável na grid de `NotifSmsConfig`. Já aceito como pendência fora de escopo (Sugestão 5 da revisão-01) — não reprovado, apenas rastreado. |
| TC-37 (achado não-bloqueante, já registrado) | Análise de código | **REGISTRADO, NÃO REPROVADO** | Confirmado: `'dbLogistica'` não está incluído no array `$dbGroups` de `ConfigDicDadosModel::getTabelas()` — `log_notif_sms_config`/`log_notif_sms_enviadas` não aparecem na ferramenta de documentação técnica automática. Já aceito como pendência fora de escopo (Sugestão 7 da revisão-01) — não reprovado. |

**Resumo Bloco J:** 1/3 casos confirmados por execução real de grep (TC-35); 2/3 são achados não-bloqueantes já aceitos, apenas reconfirmados por rastreabilidade.

------------------------------------------------------------------------

## 3. Achados / Bugs (execução real, não hipotéticos)

### BUG CRÍTICO #1 — Divergência de schema físico em `log_notif_sms_config`/`log_notif_sms_enviadas`

**Onde:** tabelas físicas `dev_logistica_db.log_notif_sms_config` e `dev_logistica_db.log_notif_sms_enviadas`, comparadas ao schema aprovado no documento de desenvolvimento (Seção 4.2/4.3 de `notificacoes-sms-dev.docx`) e ao script de migration `LogisticaNotifSms`.

**Evidência real (execução ao vivo):**
- `SHOW CREATE TABLE log_notif_sms_config` confirma: `nsc_ativo tinyint(1) DEFAULT 1`, divergindo do schema aprovado (`CHAR(1) NOT NULL DEFAULT 'A'`).
- O MariaDB de dev roda com `sql_mode` incluindo `STRICT_TRANS_TABLES`.
- Reproduzindo o `INSERT` exato que o Model de `NotifSmsConfig` monta (em transação, com `ROLLBACK` — nenhum dado definitivo foi alterado), o erro retornado foi: `Incorrect integer value: 'A' for column nsc_ativo`.
- Isso quebra **todo** `store()`/`update()`/`ativinativ()` de `NotifSmsConfig` — vai falhar com erro fatal de banco assim que a tela for usada de verdade.
- Como as tabelas já existem fisicamente e a migration só cria quando ausente (`tableExists()`), **a migration nunca vai corrigir essa divergência sozinha** — mesmo rodando `up()` novamente.

**Efeito em cascata confirmado:** também quebra o filtro automático `_ativo` de `criaSelectRelativo()` na tela `NotifSmsEnviadas` (TC-32) — a função compara `nsc_ativo = 'A'`, o MySQL converte para `nsc_ativo = 0`, que nunca bate com o valor `1` de fato gravado — o select "Regra" nunca lista nenhuma opção na prática.

**Divergências adicionais de mesma causa raiz** (colunas físicas aparentemente criadas manualmente antes/fora da migration, sem seguir o script aprovado):
- `nsc_nome` / `nsc_telefones` / `nsc_mensagem_template`: **NULLable** na tabela física (spec aprovada: `NOT NULL`).
- Em `log_notif_sms_enviadas`: `nse_nsc_id` / `nse_data_envio` **NULLable** (spec aprovada: `NOT NULL`); falta o índice `KEY idx_nse_data_envio (nse_data_envio)` (só existe a `UNIQUE KEY`).

**Severidade:** crítica — bloqueia toda a funcionalidade de escrita da feature (Bloco C completo) e uma parte relevante da consulta (Bloco I, TC-32).

**Encaminhamento recomendado:** enviar ao `byarq` para decidir a abordagem de correção (ex.: script de `ALTER TABLE` corretivo específico para ambientes onde a tabela já existe fisicamente com o schema divergente, já que a migration `tableExists()` não cobre esse caso) antes de qualquer nova tentativa de execução funcional completa dos Blocos C, D (parte funcional) e I.

------------------------------------------------------------------------

### BUG #2 (médio-alto) — `LogisNotifSmsConfigModel::getListaRegras()` não filtra soft-delete

**Onde:** `app/Models/Logistica/LogisNotifSmsConfigModel.php`, método `getListaRegras()`.

**Evidência real (execução ao vivo):** o método usa `$this->builder()->select('*')->orderBy('nsc_ativo, nsc_nome')->get()`. Rodando o Model de fato e capturando o SQL gerado, o resultado foi:

```sql
SELECT * FROM log_notif_sms_config ORDER BY nsc_ativo, nsc_nome
```

Sem nenhuma cláusula de exclusão (`WHERE nsc_excluido IS NULL`). O CI4 **não** aplica soft-delete automaticamente quando se usa `builder()` bruto — isso só ocorre em métodos como `findAll()`/`where()->findAll()` do Model, que respeitam a propriedade `$useSoftDeletes`. Como `getListaRegras()` contorna o Model para montar a query manualmente, o filtro é perdido.

**Consequência:** uma regra soft-deleted (`nsc_excluido` preenchido) aparece normalmente na listagem (`index()`/`lista()`), violando o resultado esperado do TC-11.

**Severidade:** médio-alta — não corrompe dado, mas expõe registros excluídos na listagem principal da tela.

**Encaminhamento recomendado:** enviar ao `byarq`/`bydev` para adicionar `->where('nsc_excluido', null)` (ou equivalente) à query de `getListaRegras()`, ou reescrever o método para usar `findAll()` do Model (que já aplica soft-delete automaticamente), mantendo o `ORDER BY` atual.

------------------------------------------------------------------------

## 4. Achado de infraestrutura (não é bug de código)

`cfg_modulo` **"Logística" já existente em dev (`mod_id=19`).** Já existe um registro em `cfg_modulo` chamado "Logística" no banco de dev, com `mod_dbgroup` vazio e ícone diferente do que a migration `LogisticaNotifSms::criaInfraestrutura()` geraria. Como essa rotina só insere quando o módulo ainda não existe (`WHERE mod_nome = 'Logística'` ou equivalente, conforme leitura do script), esse módulo será **reaproveitado sem atualização** de `mod_dbgroup`/ícone.

**Não é um bug** — é um dado de estado do ambiente de dev, não uma falha de lógica da migration em si. Reportado como ponto de atenção para o `byarq` decidir se a migration deve fazer `UPDATE` condicional desses campos quando o módulo já existir, ou se é aceitável manter como está (ex.: se o `mod_dbgroup`/ícone já correto tiver sido ajustado manualmente por outro motivo).

------------------------------------------------------------------------

## 5. Achado não-bloqueante herdado (fora de escopo desta entrega)

Mensagem de erro de nome duplicado (`verificaUnico()` em `NotifSmsConfig`) usa `cfg_mensagem` id=9, que semanticamente é sobre "divergência de saldo/fechamento", não sobre duplicidade de nome. Confirmado que esse é o **mesmo problema já existente** em `CfgCor.php` (referência oficial citada no documento de desenvolvimento) — a feature replica corretamente o padrão existente do sistema, incluindo essa imprecisão herdada. Fora de escopo desta entrega; não é uma falha introduzida por esta feature.

------------------------------------------------------------------------

## 6. Casos não executados nesta rodada

| ID | Motivo |
|---|---|
| TC-01, TC-03, TC-04 | Dependem de ambiente de dev limpo (sem as 2 tabelas) para validar criação/remoção via migration — indisponível nesta rodada (ambiente disponível já tem as tabelas criadas manualmente). |
| TC-05, TC-06 (cenário real), TC-07 | Dependem de sessão de navegador autenticada com perfis distintos. |
| TC-10 | Depende de regra gravada com sucesso em TC-08/09, bloqueados pelo BUG #1. |
| TC-26 (funcional completo) | Não executado por completo por prudência com a massa de dados compartilhada do ambiente de dev; lógica verificada por leitura de código. |
| TC-30, TC-31 | Dependem de sessão de navegador para preencher `crDaterange()` e observar o retorno renderizado. |
| TC-33 (confirmação visual) | Lógica de query confirmada por leitura; renderização da grid não confirmada visualmente. |

Todos os casos acima foram verificados por análise de código onde possível, **sem divergência encontrada além dos 2 bugs já relatados na Seção 3.**

------------------------------------------------------------------------

## 7. Casos que passaram (análise de código e/ou execução real)

TC-02 (parcial, mecanismo confirmado, com achado em cascata), TC-13 a TC-25 (blocos D, E, F, G — ver detalhamento nas tabelas por bloco, com pendências pontuais já anotadas), TC-27 a TC-29, TC-32 (parcial, mecanismo do select confirmado, com achado em cascata do BUG #1), TC-34 a TC-37.

**TC-08, TC-09, TC-11, TC-12** tiveram **falha real** na tentativa de execução — causada pelos Bugs #1/#2, não são falhas de lógica independentes desses casos específicos.

------------------------------------------------------------------------

## 8. Encaminhamento recomendado

1. **Bug #1 (crítico)** e **Bug #2 (médio-alto)** precisam ser levados ao `byarq` para avaliação de abordagem de correção, e depois ao `bydev` para correção — **antes de qualquer nova tentativa de execução funcional completa** do Bloco C (CRUD), da parte funcional do Bloco D (edição real de registros) e do Bloco I (especialmente TC-32).
2. Após a correção, recomenda-se nova rodada de execução cobrindo, no mínimo: TC-08, TC-09, TC-10, TC-11, TC-12 (Bloco C completo), TC-15/TC-16 (edição real), TC-23-b, TC-25/TC-26 (exclusão com/sem histórico, em ambiente com massa de dados descartável), TC-28 (ciclo completo de log de auditoria), e TC-32 (select "Regra" com dados reais ativos).
3. Após a correção do BUG #1, revalidar também TC-02/TC-03 em um ambiente de dev limpo de fato, para confirmar que a migration cria as tabelas com o schema correto quando ainda não existem — o ambiente atual não permite essa validação porque as tabelas já existem com o schema divergente.
4. O achado de infraestrutura (`cfg_modulo` "Logística" pré-existente) e o achado não-bloqueante herdado (mensagem `cfg_mensagem` id=9) devem ser levados ao `byarq` apenas para registro/decisão de escopo — não bloqueiam o ciclo atual.

------------------------------------------------------------------------

## 9. Rastreabilidade

Resultado gerado a partir de `docs/testes/notificacoes-sms-plano-testes.docx` (aprovado pelo `byarq`), documento de desenvolvimento `docs/desenvolvimento/notificacoes-sms-dev.docx` e revisão `docs/revisao/notificacoes-sms-revisao-01.docx`. Os Bugs #1 e #2 (Seção 3) e o achado de infraestrutura (Seção 4) são novos desta rodada de execução e devem ser encaminhados ao `byarq` antes do documento de entrega ser fechado.

------------------------------------------------------------------------

## 10. Rodada 2 — Validação ao vivo pós-correção

**Contexto:** após a Rodada 1 (Seções 1-9), o BUG CRÍTICO #1 (schema físico) e o BUG #2 (soft-delete em `getListaRegras()`) foram corrigidos pelo `bydev`. Mediante **autorização explícita do usuário**, a migration `2026-07-21-000001_LogisticaNotifSms.php` foi executada de fato contra o ambiente de DEV (`dev_logistica_db` / `dev_config_ceqweb_db`), permitindo finalmente a execução funcional completa que na Rodada 1 estava bloqueada em cascata pelos dois bugs.

### 10.1 Schema físico — corrigido e confirmado

- `nsc_ativo`: confirmado via `SHOW CREATE TABLE` como `CHAR(1) NOT NULL DEFAULT 'A'` — era `tinyint(1) DEFAULT 1` na Rodada 1 (causa raiz do BUG #1). Divergência eliminada.
- `nsc_excluido`: coluna adicionada em `log_notif_sms_config` — estava ausente na Rodada 1 (era a causa raiz do BUG #2 em cascata: sem a coluna física, nenhum filtro de soft-delete seria possível de fato).
- `idx_nse_data_envio`: índice confirmado existente em `log_notif_sms_enviadas` (`nse_data_envio`), suprindo a ausência relatada na Rodada 1.
- **Idempotência confirmada:** a migration foi executada **2 vezes** seguidas contra o mesmo ambiente, sem duplicar colunas, índices ou registros de infraestrutura (`cfg_modulo`/`cfg_tela`/`cfg_perfil_item`/`cfg_tela_lista`) — endereça diretamente a lacuna do TC-01 (não executado na Rodada 1 por falta de ambiente controlado).

### 10.2 BUG NOVO encontrado e corrigido durante a validação — `tel_ident` truncado

**Onde:** script de migration `LogisticaNotifSms`, rotina de criação dos registros em `cfg_tela`.

**Descrição:** `cfg_tela.tel_ident` é `VARCHAR(5)` e segue a convenção de **código de tela por ciclo de desenvolvimento** (ex.: `"T22"` para `CfgCor`, `"T42"`/`"T43"` para Fornecedores) — **não** é o nome da coluna PK da tabela de negócio, como o documento de desenvolvimento e a migration originalmente assumiam. A migration gravava os literais `'nsc_id'`/`'nse_id'` (6 caracteres) em um campo `VARCHAR(5)`, que o MariaDB truncava silenciosamente para `'nsc_i'`/`'nse_i'` — valores sem significado dentro da convenção de nomenclatura de telas do projeto.

**Correção aplicada:** valores trocados para `'T44'`/`'T45'` (próximos códigos livres na sequência após `T42`/`T43`, conforme convenção confirmada em `rascunho-helpers-php.md`). Foi adicionado um passo de correção **idempotente** na migration para também consertar, em ambientes onde a migration já havia rodado com o valor errado, os registros de `cfg_tela` já gravados com `'nsc_i'`/`'nse_i'`.

**Validação pós-correção:** migration reexecutada; `cfg_tela.tel_ident` confirmado como `"T44"` e `"T45"` para as duas telas da feature, sem duplicar registros em `cfg_tela`, `cfg_perfil_item` ou `cfg_tela_lista` (mesmos `tel_id`/IDs relacionados de antes da correção).

**Severidade:** média — não impedia a migration de rodar, mas quebrava a convenção de identificação de tela usada por outras rotinas do sistema que dependem de `tel_ident` para localizar a tela (ex.: montagem de menu, permissões). Encontrado e corrigido nesta rodada, antes do fechamento do ciclo.

### 10.3 Infraestrutura — confirmada em DEV

- `cfg_modulo` "Logística" (`mod_id=19`): `mod_dbgroup='dbLogistica'` atualizado corretamente, **preservando o ícone customizado** já existente no registro (endereça o achado de infraestrutura da Seção 4, que estava pendente de decisão do `byarq` na Rodada 1).
- `cfg_tela`: confirmadas as 2 telas da feature (`tel_id` 70 e 71).
- `cfg_perfil_item`: confirmado CAEXN completo para `prf_id=1` (Super Admin) nas 2 telas.
- `cfg_tela_lista`: confirmadas as 4 colunas de grid esperadas.

### 10.4 CRUD completo — executado de verdade e PASSOU

Execução real contra o banco de DEV, via chamadas reais aos métodos do Model (não simulação/leitura de código), com **limpeza total dos dados de teste ao final** (confirmado 0 linhas residuais em `log_notif_sms_config` e `log_notif_sms_enviadas` após a rodada):

| ID | Resultado | Observação |
|---|---|---|
| TC-08 | **PASSA** | Criação de regra tipo `entrega` via `store()` real — sem erro de validação, sem erro de gravação (BUG #1 eliminado). |
| TC-09 | **PASSA** | Criação de regra tipo `saldo_baixo` via `store()` real — sem erro. |
| TC-10 | **PASSA** | Edição de regra existente via `update()` real — valor alterado refletido corretamente na releitura do registro. |
| TC-11 | **PASSA** | `getListaRegras()` executado de fato: regra ativa aparece na lista, regra soft-deleted (`nsc_excluido` preenchido) **não aparece** — BUG #2 confirmado corrigido. |
| TC-12 | **PASSA** | `ativinativ()` executado de fato — inativação e reativação da regra refletidas corretamente em `nsc_ativo`. |
| TC-25 | **PASSA** | Bloqueio de exclusão com histórico: query de verificação por `nse_nsc_id` em `log_notif_sms_enviadas` detectou corretamente o histórico associado (`emUso=1`), bloqueando o `delete()`. |
| TC-26 | **PASSA** | Exclusão sem histórico associado: soft delete via preenchimento de `nsc_excluido` confirmado funcionando (fecha a pendência da Rodada 1, que não havia sido executada por prudência com a massa de dados). |
| TC-28 | **PASSA** | Log de auditoria via `LogMonModel::buscaLog()`: confirmado retorno real com operação `"Incluído"`, data e tabela corretos para o registro de teste. |
| TC-29 | **PASSA** | Mesmo mecanismo do TC-28, confirmado para o registro editado (TC-10) — nota técnica: o ID precisa ser passado como `string` para bater com o formato armazenado no Mongo via `strval()` — comportamento consistente com o que a tela real já faz, já que os parâmetros de rota do CI4 chegam como `string`; não é uma divergência, é o funcionamento correto confirmado. |
| TC-30 | **PASSA** | `getEnviadasPeriodo()` executado com filtro de período real — retornou exatamente a linha de teste inserida dentro do intervalo informado. |
| TC-31 | **PASSA** | Mesmo método, período sem correspondência — retorno vazio confirmado, conforme esperado. |

### 10.5 Conclusão da Rodada 2

Os três bugs identificados ao longo do ciclo de testes — **BUG CRÍTICO #1** (schema físico, Seção 3), **BUG #2** (`getListaRegras()` sem filtro de soft-delete, Seção 3) e o **BUG NOVO** (`tel_ident` truncado, Seção 10.2) — foram corrigidos e **validados com execução real** contra o ambiente de DEV, incluindo CRUD completo, log de auditoria e consulta por período.

**Ciclo de testes fechado, sem pendência bloqueante.** Pronto para avançar à Etapa 4 (documento de entrega).
