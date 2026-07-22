# Documento de Entrega — CRUD de Regras de Notificação SMS + Consulta de SMS Enviados (Módulo Logística)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística — `NotifSmsConfig` (CRUD de Regras de Notificação SMS) · `NotifSmsEnviadas` (Consulta de SMS Enviados por período)
**Documento de origem:** `docs/desenvolvimento/notificacoes-sms-dev.md` (aprovado)
**Documentos de ciclo relacionados:** `docs/revisao/notificacoes-sms-revisao-01.md`, `docs/testes/notificacoes-sms-plano-testes.md`, `docs/testes/notificacoes-sms-resultado-testes.md`
**Requisito original:** `docs/notificacoes-sms.md`
**Status final:** **Pronto para produção, com pendências explícitas de deploy** — ver Seção 3 (checklist), que lista o que ainda precisa ser feito manualmente em produção. Nada listado na Seção 3 foi executado em produção; nada deve rodar sem confirmação explícita do Douglas.

------------------------------------------------------------------------

## 1. Resumo Executivo

Foi desenvolvida a parte administrativa da feature de Notificações SMS do novo módulo "Logística" do CeqWeb3:

- **CRUD de configuração de regras de notificação SMS** (`NotifSmsConfig`, tabela `log_notif_sms_config`): cadastro, edição, ativar/inativar e exclusão (com bloqueio quando há histórico associado) das regras que determinam quando um SMS deve ser disparado (por tipo de renovação/entrega ou por saldo baixo).
- **Tela de consulta de SMS enviados por período** (`NotifSmsEnviadas`, tabela `log_notif_sms_enviadas`): consulta somente leitura, com filtro obrigatório de período e filtro opcional por regra.

Esta entrega é baseada no desenho técnico já existente em `docs/notificacoes-sms.md` (motor de regras, `SmsService` — integração com o provedor SMS Dev —, Controller CLI de disparo e cron). **Esses três componentes continuam FORA de escopo deste ciclo e não foram tocados** — este ciclo cobriu exclusivamente a parte administrativa (CRUD + consulta) que complementa aquele desenho.

O ciclo completo foi executado conforme o fluxo padrão do time: planejamento aprovado pelo `byarq`, codificação pelo `bydev`, uma rodada de revisão de código (`byrev`) com 3 bloqueantes corrigidos e reconfirmados, e duas rodadas de testes (`bytest`) — a primeira por análise de código combinada com execução parcial (que encontrou 2 bugs reais de schema/soft-delete), a segunda com validação ao vivo completa em ambiente de DEV após as correções, incluindo um terceiro bug novo (`tel_ident` truncado) encontrado e corrigido durante essa validação.

**Status funcional:** CRUD completo, log de auditoria, bloqueio de exclusão com histórico, e consulta por período foram todos executados de verdade contra o ambiente de DEV e passaram, com limpeza total dos dados de teste ao final. Não restam bloqueadores técnicos de funcionalidade. As pendências que seguem para produção são exclusivamente de deploy/configuração (Seção 3), não de código.

------------------------------------------------------------------------

## 2. Arquivos Criados/Alterados (ciclo completo)

### 2.1 Migration

| Arquivo | Conteúdo |
|---|---|
| `app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php` | Infraestrutura: `cfg_modulo` (módulo "Logística"), `cfg_tela` (2 telas novas, `tel_ident` `T44`/`T45`), `cfg_perfil_item` (CAEXN para Super Admin), `cfg_tela_lista` (colunas de grid de `NotifSmsConfig`); checagem/correção condicional (via `information_schema`) do schema físico de `log_notif_sms_config`/`log_notif_sms_enviadas` quando divergente do schema aprovado; criação condicional das 2 tabelas caso ainda não existam. Idempotente — testada rodando 2x sem duplicar nada. |

### 2.2 Backend — Controllers

- `app/Controllers/Logistica/NotifSmsConfig.php` — CRUD completo (`index`/`lista`/`add`/`edit`/`show`/`store`/`ativinativ`/`delete`), bloqueio de exclusão via query direta escopada em `dbLogistica` (contagem em `log_notif_sms_enviadas` por `nse_nsc_id`).
- `app/Controllers/Logistica/NotifSmsEnviadas.php` — tela de consulta somente leitura (`index`/`lista`), filtro de período obrigatório + filtro opcional por regra.
- `app/Controllers/MyValidation.php` — 2 métodos novos: `obrigatorioSeTipoRegraEntrega`/`obrigatorioSeTipoRegraSaldo` (validação condicional conforme `nsc_tipo_regra`).

### 2.3 Backend — Models

- `app/Models/Logis/LogisNotifSmsConfigModel.php` — CRUD com callbacks de auditoria (`depoisInsert`/`depoisUpdate`/`depoisDelete` via `LogMonModel::insertLog()`, padrão `ConfigCorModel`), `getListaRegras()` (com filtro de soft-delete).
- `app/Models/Logis/LogisNotifSmsEnviadasModel.php` — somente leitura, `getEnviadasPeriodo()` com `JOIN` em `log_notif_sms_config` para trazer `nsc_nome`.

### 2.4 Backend — Entities

- `app/Entities/Logistica/EntLogNotifSmsConfig.php` — 10 campos via `MyCampo`, com `setLabel()` explícito em cada campo (não depende de `COLUMN_COMMENT` do banco), toggle de campos condicionais por `nsc_tipo_regra`.

### 2.5 Frontend — JavaScript (acréscimos em arquivos já existentes)

- `public/assets/jscript/my_filter.js` — `buscaSmsEnviadas()` e `montaListaSmsEnviadas(dados)` (moldadas em `buscaLogUser()`/`montaListaLogs()`, tela de consulta).
- `public/assets/jscript/my_fields.js` — `alternaCamposTipoRegra(obj)` (toggle de campos condicionais, delega obrigatoriedade a `mudaObrigatorioElemDiv()` já existente, sem reimplementação própria).

### 2.6 Configuração

- `app/Config/Database.php` — novo grupo `dbLogistica`, apontando para o schema `logistica_db`.
- `app/Models/Config/ConfigDicDadosModel.php` — prefixo `'log'`/`'vw_log'` mapeado para `dbLogistica` em `getDbGroupAndSchema()`.
- `app/Config/Routes.php` — bloco `$logisticaControllers` com as rotas de `NotifSmsConfig`/`NotifSmsEnviadas`.

### 2.7 Alteração revertida (decisão final de arquitetura)

- `app/Traits/ForeignKeyUsageChecker.php` — **revertido ao estado original**. `dbLogistica` **não** foi adicionado a `$conexoesRelacionadas`. A decisão final (resultado da correção do Bloqueante 1 da revisão-01) foi usar uma **query direta escopada no Controller** (`NotifSmsConfig::delete()`) em vez de estender o trait genérico — isso resolveu tanto o bug de bloqueio de exclusão quanto a regressão de performance apontada na Sugestão 6 da revisão-01 (o trait é usado por `delete()` em praticamente todas as telas do sistema; adicionar mais um `DBGroup` a ele impactaria a performance de exclusão em todo o sistema, não só nesta feature).

------------------------------------------------------------------------

## 3. Checklist de Deploy para Produção

**Nada nesta seção foi executado em produção. Nada deve rodar sem confirmação explícita do Douglas.**

### 3.1 Rodar a migration em produção

Rodar `app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php`. Ela vai fazer, nesta ordem:

1. Criar a infraestrutura `cfg_modulo`/`cfg_tela`/`cfg_perfil_item`/`cfg_tela_lista` (idempotente — seguro rodar mesmo se parte já existir; testado em DEV rodando 2x sem duplicar nada).
2. Checar (via `information_schema`, **antes de qualquer `ALTER`**) e corrigir o schema físico de `log_notif_sms_config`/`log_notif_sms_enviadas` **somente se divergirem** do schema aprovado — as divergências encontradas e corrigidas em DEV foram: `nsc_ativo` como `tinyint` (deveria ser `CHAR(1)`), ausência da coluna `nsc_excluido`, e ausência do índice `idx_nse_data_envio`.

> **IMPORTANTE:** as tabelas de produção foram criadas manualmente pelo próprio Douglas antes desta feature, então o schema físico de produção **PODE OU NÃO** ter as mesmas divergências encontradas em DEV. Recomenda-se:
> - Rodar `migrate:status` e inspecionar o schema real de produção (`SHOW CREATE TABLE`) **antes** de aplicar a migration.
> - Estar ciente de que a migration **pode fazer `ALTER TABLE` em produção** se encontrar divergência — o Douglas deve autorizar essa possibilidade explicitamente antes da execução, mesmo sabendo que a migration só altera o que estiver realmente divergente.

### 3.2 Confirmar `prf_id` do perfil Super Admin em produção

A migration está **hardcoded para `prf_id=1`** ao conceder a permissão `CAEXN` das 2 telas novas. Se em produção o perfil "Super Admin" tiver outro `prf_id`, a migration vai conceder a permissão ao perfil errado (ou a nenhum perfil de fato administrativo). **Confirmar o `prf_id` correto em produção antes de rodar a migration.**

### 3.3 Configurar `SMSDEV_API_KEY` no `.env` de produção

Pré-requisito do `SmsService` (que é escopo de `docs/notificacoes-sms.md`, não desta entrega, mas relacionado — sem essa chave, o motor de disparo de SMS não funciona, mesmo que o CRUD administrativo desta entrega esteja 100% operacional).

### 3.4 Validação visual pós-deploy

Após rodar a migration em produção, validar visualmente:
- Menu mostra o módulo "Logística".
- Formulário de regras (`NotifSmsConfig`) com labels corretos nos 10 campos.
- Toggle de campos condicionais (grupo Entrega/Saldo) funcionando conforme `nsc_tipo_regra` selecionado.
- Tela de consulta (`NotifSmsEnviadas`) filtrando corretamente por período e por regra.

------------------------------------------------------------------------

## 4. Decisões de Arquitetura Importantes (resumo)

Detalhamento completo em `docs/desenvolvimento/notificacoes-sms-dev.md`. Resumo das decisões mais relevantes para quem for dar manutenção:

- **Novo DBGroup `dbLogistica`**, apontando para o schema `logistica_db` — as 2 tabelas já existiam fisicamente em produção antes deste ciclo (criadas manualmente pelo Douglas); este ciclo cobriu o código, com criação condicional das tabelas apenas para ambientes onde ainda não existam (dev/homologação).
- **Auditoria simplificada em `log_notif_sms_config`** (sem `usu_criou`/timestamps na tabela SQL, só `nsc_ativo`/`nsc_excluido`), mas com o **log de auditoria em Mongo via `LogMonModel` mantido** por consistência com o resto do sistema (ressalva do `byarq` na rodada de revisão do documento de desenvolvimento) — implementado seguindo exatamente o padrão de `ConfigCorModel`.
- **`nsc_ren_tipo` como select estático** (valores fixos no Entity: Ceqnep/Transportadora/Hospital Retira), sem `criaSelectRelativo()` — os tipos de renovação/entrega hoje só existem no app Logística antigo (outro repositório/banco), cuja integração via API fica para uma fase futura.
- **Bloqueio de exclusão via query direta escopada**, não via `ForeignKeyUsageChecker` — decisão tomada durante a correção do Bloqueante 1 da revisão-01, que resolveu simultaneamente o bug de bloqueio (nome de coluna FK incompatível) e a regressão de performance que estender o trait genérico causaria em todo o sistema (ver Seção 2.7).
- **Toggle de campos condicionais (`alternaCamposTipoRegra`)** cuida apenas do show/hide dos wrappers `#divEntrega`/`#divSaldo`, delegando a obrigatoriedade condicional ao mecanismo já existente `mudaObrigatorioElemDiv()` — sem reimplementação própria, conforme ressalva do `byarq`.
- **Somente o perfil Super Admin recebe acesso (`CAEXN`)** às 2 telas novas nesta primeira entrega — outros perfis podem ser liberados posteriormente, fora deste ciclo.

------------------------------------------------------------------------

## 5. Histórico do Ciclo de Revisão e Testes (resumo gerencial)

### 5.1 Revisão de código (`byrev`)

| Rodada | Achados bloqueantes | Status |
|---|---|---|
| 1 | 3 bloqueantes + 4 sugestões | Todos os 3 bloqueantes corrigidos e reconfirmados. Rodada 2 de revisão não gerou documento próprio por não ter encontrado mais nada a contribuir. |

**Bloqueantes corrigidos:**

| Achado | Severidade | Status |
|---|---|---|
| Bloqueio de exclusão não funcionava — nome de coluna FK (`nsc_id`) incompatível com o que `ForeignKeyUsageChecker` procurava (`nse_nsc_id`) | Bloqueante | Corrigido — substituído por query direta escopada em `dbLogistica` (ver Seção 2.7) |
| Formulário sem nenhum label visível — Entity não chamava `setLabel()`, migration não definia `comment` nas colunas | Bloqueante | Corrigido — `setLabel()` explícito em todos os 10 campos da Entity |
| Toggle de campos condicionais só inicializado em `edit()`, não em `add()` | Bloqueante | Corrigido — `#divSaldo` nasce com `d-none` fixo no HTML de `add()` |

**Sugestões não-bloqueantes (aceitas como pendência fora de escopo desta entrega):**
- `nsc_condicao` sem `in_list` na validação — **incorporada** durante a correção (não ficou como pendência).
- Grid exibe valores brutos de enum (`nsc_tipo_regra`/`nsc_ativo`) sem tradução amigável — aceito como pendência.
- Custo de performance de estender `ForeignKeyUsageChecker` — resolvido ao trocar de abordagem (Seção 2.7), não pela sugestão original.
- `ConfigDicDadosModel::getTabelas()` não lista `dbLogistica` na ferramenta de documentação técnica automática — aceito como pendência.

### 5.2 Testes (`bytest`)

Executadas 2 rodadas de teste sobre um plano de 37 casos:

| Rodada | Objetivo | Resultado |
|---|---|---|
| 1 | Análise de código + execução parcial (lint PHP, queries reais em transação com `ROLLBACK`) | 2 bugs reais encontrados: **BUG CRÍTICO #1** (schema físico divergente — `nsc_ativo` como `tinyint` em vez de `CHAR(1)`, entre outras divergências — quebrava todo `store()`/`update()`/`ativinativ()` e o filtro `_ativo` de `criaSelectRelativo()` na consulta) e **BUG #2** (`getListaRegras()` não filtrava soft-delete). Um achado de infraestrutura (`cfg_modulo` "Logística" pré-existente em DEV) e um achado não-bloqueante herdado (mensagem de erro reaproveitada de `CfgCor.php`) também registrados. |
| 2 | Validação ao vivo pós-correção, com autorização explícita do usuário para rodar a migration contra DEV | BUG CRÍTICO #1 e BUG #2 confirmados corrigidos. **BUG NOVO encontrado e corrigido durante a própria validação:** `cfg_tela.tel_ident` (VARCHAR(5), convenção de código de tela por ciclo, ex. `T42`/`T43`) estava sendo gravado com `'nsc_id'`/`'nse_id'` (truncados pelo MySQL para `'nsc_i'`/`'nse_i'`) — corrigido para `'T44'`/`'T45'`, com correção idempotente para registros já gravados errado. CRUD completo, log de auditoria e consulta por período executados de verdade e passaram, com 0 resíduo de dados de teste ao final. |

**Resultado final:** ciclo de testes fechado, sem pendência bloqueante. Todos os bugs encontrados (2 na Rodada 1 + 1 na Rodada 2) foram corrigidos e revalidados com execução real.

------------------------------------------------------------------------

## 6. Conclusão

O desenvolvimento do CRUD de Regras de Notificação SMS e da tela de Consulta de SMS Enviados está funcionalmente completo, revisado e testado com validação real em ambiente de DEV, conforme o documento de desenvolvimento aprovado. A entrega está **pronta para produção**, condicionada à execução completa do checklist da Seção 3 — em especial a conferência do schema físico de produção antes de rodar a migration (item 3.1) e a confirmação do `prf_id` correto do Super Admin em produção (item 3.2), sem os quais a migration pode ter efeito diferente do esperado.
