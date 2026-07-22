# Plano de Testes — Notificações SMS (Módulo Logística)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (`app/Controllers/Logistica`)
**Telas envolvidas:** `NotifSmsConfig` — CRUD de Regras de Notificação SMS · `NotifSmsEnviadas` — Consulta de SMS Enviados por período
**Origem:** Plano de testes definido pelo `bytest`.
**Documentos de referência:**
- Documento de desenvolvimento aprovado: `docs/desenvolvimento/notificacoes-sms-dev.docx` (seções 4, 6.1, 6.2, 10, 11)
- Documento de revisão, rodada 01: `docs/revisao/notificacoes-sms-revisao-01.docx` — Bloqueantes 1, 2 e 3, **todos corrigidos e reaprovados na rodada 2** de revisão do `byrev`.

**Status:** aguardando aprovação do `byarq` para execução.

------------------------------------------------------------------------

## 1. Pré-condições gerais

### 1.1 Ambiente / migration

- Conexão `dbLogistica` (schema `logistica_db`) configurada e acessível a partir do ambiente de teste.
- Migration `LogisticaNotifSms` aplicada em ambiente de dev limpo (para os casos do Bloco A, que exigem estado "tabelas ausentes").
- Ambiente adicional com as tabelas `log_notif_sms_config`/`log_notif_sms_enviadas` **já existentes** (simulando produção), para validar que a migration não recria tabelas.

### 1.2 Massa de dados mínima

- Ao menos 2 regras cadastradas em `log_notif_sms_config`: uma `nsc_tipo_regra='entrega'` e uma `nsc_tipo_regra='saldo_baixo'`, ambas ativas.
- Uma regra com histórico associado em `log_notif_sms_enviadas` (para os testes de bloqueio de exclusão).
- Uma regra sem nenhum histórico associado (para o teste de exclusão permitida).
- Ao menos 3 registros em `log_notif_sms_enviadas` com `nse_data_envio` distribuídos em datas diferentes, vinculados a regras diferentes (para os testes de filtro por período/regra).
- `cfg_perfil_item` com o perfil Super Admin (`prf_id=1`) já contendo `pit_permissao='CAEXN'` para as 2 telas novas (via migration).

### 1.3 Perfis de teste

- Perfil Super Admin (`prf_id=1`), com acesso `CAEXN` completo às 2 telas.
- Um perfil **sem** `cfg_perfil_item` cadastrado para as 2 telas novas, para os casos de bloqueio *fail-closed* (conforme `rascunho-helpers-php.md` — permissão ausente bloqueia tudo, resolve para `vw_semacesso`).
- (Opcional, TC-07) um perfil de teste com permissão configurável letra a letra de `CAEXN`, para checagem isolada de cada operação.

------------------------------------------------------------------------

## 2. Bloco A — Migration / Infraestrutura

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-01 | Migration idempotente | Migration ainda não aplicada, ou já aplicada uma vez | Rodar `up()` 2 vezes seguidas | Nenhuma duplicação de registros em `cfg_modulo`, `cfg_tela`, `cfg_tela_lista` e `cfg_perfil_item` após a 2ª execução |
| TC-02 | Não recria tabelas já existentes | Ambiente com `log_notif_sms_config`/`log_notif_sms_enviadas` já criadas manualmente (simula produção) | Rodar `up()` | `tableExists()` detecta as tabelas existentes; `criaTabelasSeNecessario()` não executa `CREATE TABLE`; dados pré-existentes nas tabelas permanecem intactos |
| TC-03 | Cria tabelas quando ausentes | Ambiente de dev limpo, sem as 2 tabelas | Rodar `up()` | As 2 tabelas são criadas com o schema exato da Seção 4.2/4.3 do documento de desenvolvimento, incluindo `UNIQUE KEY uk_chave_regra (nse_chave, nse_nsc_id)` e `KEY idx_nse_data_envio (nse_data_envio)` em `log_notif_sms_enviadas` |
| TC-04 | `down()` preserva infraestrutura | Migration aplicada (`up()` executado) | Rodar `down()` | As 2 tabelas (`log_notif_sms_config`/`log_notif_sms_enviadas`) são removidas; registros de infraestrutura em `cfg_modulo`/`cfg_tela`/`cfg_tela_lista`/`cfg_perfil_item` **permanecem** |

------------------------------------------------------------------------

## 3. Bloco B — Permissões CAEXN

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-05 | Super Admin enxerga módulo e telas | Login com perfil `prf_id=1` | Acessar o menu do sistema | Módulo "Logística" visível no menu, com as 2 telas (`NotifSmsConfig`, `NotifSmsEnviadas`) listadas |
| TC-06 | Bloqueio fail-closed sem `cfg_perfil_item` | Login com perfil sem registro em `cfg_perfil_item` para as 2 telas novas | Tentar acessar diretamente as rotas de `NotifSmsConfig`/`NotifSmsEnviadas` (`index`/`lista`/`add`/`edit`/`delete`/`ativinativ`) | Acesso bloqueado em todos os métodos, resolvendo para `vw_semacesso` (conforme `rascunho-helpers-php.md`) |
| TC-07 (opcional) | Checagem isolada por letra de `CAEXN` | Perfil de teste configurável, uma letra de cada vez | Configurar o perfil só com `C`, depois só com `A`, `E`, `X`, `N`, testando o método correspondente a cada letra | Cada operação só é permitida quando a letra correspondente está presente no perfil; demais operações bloqueadas |

------------------------------------------------------------------------

## 4. Bloco C — `NotifSmsConfig`: CRUD funcional

| ID | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|
| TC-08 | Criar regra tipo "entrega" | Login com permissão `A` | Abrir `add()`, selecionar `nsc_tipo_regra='entrega'`, preencher todos os campos do grupo Entrega (`nsc_ren_tipo`, `nsc_ren_status_max`, `nsc_condicao`, `nsc_minutos_limite`) + campos comuns, salvar (`store()`) | Regra completa tipo entrega | Registro gravado em `log_notif_sms_config` com todos os campos corretos; `nsc_ativo='A'` por default |
| TC-09 | Criar regra tipo "saldo_baixo" | Login com permissão `A` | Abrir `add()`, selecionar `nsc_tipo_regra='saldo_baixo'`, preencher apenas `nsc_saldo_minimo` + campos comuns, salvar | Regra só com `nsc_saldo_minimo` | Registro gravado corretamente, sem exigir preenchimento dos campos do grupo Entrega |
| TC-10 | Editar regra existente | Regra cadastrada (TC-08 ou TC-09); permissão `E` | Abrir `edit($id)`, alterar `nsc_nome`/`nsc_telefones`, salvar | — | Alteração persistida; log de auditoria gravado (ver Bloco H) |
| TC-11 | Listagem (`lista()`) | Ao menos 3 regras cadastradas, incluindo 1 excluída (soft delete) | Acessar `index()`/`lista()` | — | Retorno JSON `{"data": [...]}` com as regras não excluídas, ordenadas conforme `getListaRegras()`; regra soft-deleted **não** aparece |
| TC-12 | `ativinativ()` — inativar regra com histórico | Regra COM histórico em `log_notif_sms_enviadas`; permissão `E` | Acionar `ativinativ($id, 'I')` | — | `nsc_ativo` gravado como `'I'` com sucesso — inativação **não** é bloqueada por existência de histórico (só a exclusão é bloqueada, conforme Seção 6.1 do documento de desenvolvimento) |

------------------------------------------------------------------------

## 5. Bloco D — Toggle de campos condicionais

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-13 | `add()` nasce só com grupo Entrega visível | Nenhuma | Abrir `add()` (bloqueante 3 da revisão-01, corrigido) | `#divEntrega` visível (sem `d-none`); `#divSaldo` já nasce com a classe `d-none` fixa no HTML gerado por `NotifSmsConfig::montaCamposFormulario()` — hardcoded diretamente no wrapper (`<div id="divSaldo" class="row d-none">`), já que o valor default de `nsc_tipo_regra` é `'entrega'`. Não depende de JS/script de inicialização em `add()` |
| TC-14 | Trocar select em `add()` dispara o toggle | Tela `add()` aberta | Alterar o select `nsc_tipo_regra` para "Saldo Baixo" | `alternaCamposTipoRegra(this)` é chamada via `onchange`; `#divEntrega` recebe `d-none`, `#divSaldo` perde `d-none`; `mudaObrigatorioElemDiv()` ajusta a obrigatoriedade dos campos de cada grupo |
| TC-15 | `edit()` de regra "saldo_baixo" nasce correto | Regra existente com `nsc_tipo_regra='saldo_baixo'`; permissão `E` | Abrir `edit($id)` | `#divSaldo` visível, `#divEntrega` escondido (`d-none`) já no carregamento, via script inline em `data['script']` — diferente de `add()`, que sempre nasce com o default `'entrega'` fixo no HTML, `edit()` precisa de lógica em runtime porque o registro existente pode ter `nsc_tipo_regra='entrega'` ou `'saldo_baixo'` |
| TC-16 | `edit()` de regra "entrega" mantém Entrega visível | Regra existente com `nsc_tipo_regra='entrega'` | Abrir `edit($id)` | `#divEntrega` visível, `#divSaldo` escondido, já no carregamento |
| TC-17 | Toggle não reimplementa obrigatoriedade na unha | Nenhuma (checagem estática de código) | Inspecionar `alternaCamposTipoRegra(obj)` em `my_fields.js` | A função cuida apenas de show/hide dos wrappers (`#divEntrega`/`#divSaldo`); a obrigatoriedade condicional é delegada a `mudaObrigatorioElemDiv(div, obriga)`, sem reimplementação própria — conforme ressalva do `byarq` na Seção 3.7 do documento de desenvolvimento |

------------------------------------------------------------------------

## 6. Bloco E — Validação de campos

| ID | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|
| TC-18 | Campos obrigatórios comuns | Nenhuma | Submeter `store()` sem preencher `nsc_nome`/`nsc_telefones`/`nsc_mensagem_template` | Campos comuns vazios | Validação bloqueia o `store()`, sem gravar registro |
| TC-19 | `nsc_tipo_regra` restrito a `in_list` | Nenhuma | Submeter `store()` com `nsc_tipo_regra` fora de `['entrega','saldo_baixo']` (via requisição direta) | Valor inválido, ex. `'xyz'` | Validação `in_list[entrega,saldo_baixo]` bloqueia o `store()` |
| TC-20 | Campos de entrega obrigatórios quando `nsc_tipo_regra=entrega` | Nenhuma | Submeter `store()` com `nsc_tipo_regra='entrega'` e algum campo do grupo Entrega vazio | `nsc_ren_tipo`/`nsc_ren_status_max`/`nsc_condicao`/`nsc_minutos_limite` faltando | `obrigatorioSeTipoRegraEntrega` bloqueia o `store()` |
| TC-21 | `nsc_saldo_minimo` obrigatório quando `nsc_tipo_regra=saldo_baixo` | Nenhuma | Submeter `store()` com `nsc_tipo_regra='saldo_baixo'` e `nsc_saldo_minimo` vazio | `nsc_saldo_minimo` faltando | `obrigatorioSeTipoRegraSaldo` bloqueia o `store()` |
| TC-22 | `nsc_condicao` restrito a `in_list` | Nenhuma | Submeter `store()` com `nsc_tipo_regra='entrega'` e `nsc_condicao` fora de `['antes_chegada','apos_chegada']` (via requisição direta) | Valor inválido, ex. `'xyz'` | Validação `in_list[antes_chegada,apos_chegada]` bloqueia o `store()` — correção aplicada além do escopo original dos 3 bloqueantes (Sugestão 4 da revisão-01, incorporada) |
| TC-23 | `verificaUnico()` por `nsc_nome` | Regra existente com `nsc_nome='Regra X'` | (a) tentar criar nova regra com `nsc_nome='Regra X'`; (b) editar a própria "Regra X" mantendo o mesmo nome, alterando outro campo | (a) nome duplicado; (b) mesmo nome, edição normal | (a) bloqueado por `verificaUnico()`; (b) permitido normalmente, sem falso-positivo de duplicidade contra o próprio registro |

------------------------------------------------------------------------

## 7. Bloco F — Labels visíveis

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-24 | Todos os campos exibem label visível | Nenhuma (bloqueante 2 da revisão-01, corrigido) | Abrir `add()`/`edit()` e inspecionar visualmente/via HTML os 10 campos do formulário (`nsc_nome`, `nsc_tipo_regra`, `nsc_ren_tipo`, `nsc_ren_status_max`, `nsc_condicao`, `nsc_minutos_limite`, `nsc_saldo_minimo`, `nsc_telefones`, `nsc_mensagem_template`, `nsc_ativo`) | Todos os 10 campos exibem `<label>` com texto legível, via `setLabel()` explícito na Entity (`EntLogNotifSmsConfig`) — não dependendo de `COLUMN_COMMENT` |

------------------------------------------------------------------------

## 8. Bloco G — Bloqueio de exclusão

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-25 | Excluir regra COM histórico é bloqueado | Regra vinculada a ao menos 1 registro em `log_notif_sms_enviadas`; permissão `X` | Acionar `delete($id)` | Exclusão bloqueada. Checagem feita via query direta escopada em `dbLogistica` (contagem em `log_notif_sms_enviadas` por `nse_nsc_id`), **não mais via** `ForeignKeyUsageChecker` — mudança de abordagem em relação à sugestão original da revisão-01 (Bloqueante 1), que resolveu tanto o bug de bloqueio quanto a regressão de performance apontada na Sugestão 6 |
| TC-26 | Excluir regra SEM histórico funciona | Regra sem nenhum vínculo em `log_notif_sms_enviadas`; permissão `X` | Acionar `delete($id)` | Soft delete executado (`nsc_excluido` preenchido); log de exclusão gravado em Mongo (ver Bloco H) |
| TC-27 (regressão) | `ForeignKeyUsageChecker` não referencia mais `dbLogistica` | Nenhuma (checagem estática de código) | (1) Grep por `dbLogistica` em `app/Traits/ForeignKeyUsageChecker.php`; (2) executar `delete()` em ao menos 2 outras telas do sistema que usam o trait (ex.: telas de Fornecedores/Ocorrências já cobertas em ciclos anteriores) | (1) Nenhuma referência a `dbLogistica` no trait; (2) demais telas continuam funcionando normalmente, sem impacto de performance adicional nem regressão de comportamento do bloqueio de exclusão |

------------------------------------------------------------------------

## 9. Bloco H — Log de auditoria

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-28 | Auditoria em Mongo nas 3 operações | Nenhuma | Criar (TC-08/09), editar (TC-10) e excluir (TC-26) uma regra | `LogMonModel::insertLog()` é chamado em `depoisInsert`/`depoisUpdate`/`depoisDelete` de `LogisNotifSmsConfigModel`, gravando `'Incluído'`/`'Alteração'`/`'Excluído'` respectivamente, igual ao padrão de `ConfigCorModel` |
| TC-29 | "Última alteração por/em" exibida | Regra já alterada ao menos uma vez (log existente em Mongo) | Abrir `edit($id)`/`show($id)` | Tela exibe corretamente usuário e data/hora da última alteração, populados via `$this->data['log'] = buscaLog('log_notif_sms_config', $id)` antes da `view('vw_edicao')` |

------------------------------------------------------------------------

## 10. Bloco I — `NotifSmsEnviadas`: Consulta por período

| ID | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|
| TC-30 | Filtro por período — dados dentro do intervalo | Registros em `log_notif_sms_enviadas` com `nse_data_envio` dentro de um intervalo conhecido | Preencher `crDaterange()` com o intervalo que cobre os registros; acionar "Buscar" (`lista()`) | Período cobrindo os registros de teste | Retorno JSON com os registros esperados, ordenados por `nse_data_envio DESC` |
| TC-31 | Filtro por período — dados fora do intervalo | Mesma massa de dados de TC-30 | Preencher `crDaterange()` com um intervalo que **não** cobre nenhum registro | Período sem correspondência | Retorno vazio (`[]`/`{"data":[]}`), sem erro |
| TC-32 | Filtro adicional por regra específica | Registros de ao menos 2 regras diferentes em `log_notif_sms_enviadas`, dentro do período testado | Selecionar uma regra específica via `criaSelectRelativo('log_notif_sms_config', 'nsc_id', 'nsc_nome', ...)`; acionar "Buscar" | Período + `nse_nsc_id` de uma regra | Retorno filtrado só com os registros da regra selecionada; select alimentado corretamente, validando prefixo `'log'` cadastrado em `ConfigDicDadosModel::getDbGroupAndSchema()` |
| TC-33 | Coluna "Regra" mostra nome, não o ID | Registros de teste vinculados a regras nomeadas | Executar a busca e inspecionar a coluna "Regra" na grid | — | Coluna exibe `nsc_nome` (via `JOIN` com `log_notif_sms_config`), nunca o `nse_nsc_id` bruto |
| TC-34 | Período obrigatório (client-side) | Nenhuma | Tentar acionar "Buscar" sem preencher o período | Período vazio | `boxAlert()` client-side exibido, sem disparar a requisição AJAX de `lista()` |

------------------------------------------------------------------------

## 11. Bloco J — Integração/consistência de padrões

| ID | Situação/Objetivo | Pré-condição | Passos | Resultado esperado |
|---|---|---|---|---|
| TC-35 | AJAX/alertas seguem padrão do projeto | Nenhuma (checagem estática + funcional) | (1) Grep por `jQuery.ajax`/`$.ajax` cru e `alert(` nativo nos arquivos JS envolvidos (`my_filter.js`, `my_fields.js`, trechos específicos da feature); (2) confirmar visualmente que as chamadas usam `executaAjaxWait`/`boxAlert` | Nenhuma ocorrência de `jQuery.ajax` cru nem `alert()` nativo; todas as chamadas seguem `executaAjaxWait`/`boxAlert`, conforme `rascunho-runtime-js.md` |
| TC-36 (achado não-bloqueante, registrado) | Grid de `NotifSmsConfig` exibe enum bruto | Nenhuma | Acessar a listagem de `NotifSmsConfig` | Colunas `nsc_tipo_regra` (`entrega`/`saldo_baixo`) e `nsc_ativo` (`A`/`I`) exibidas sem tradução amigável. **Não é falha deste ciclo** — corresponde à Sugestão 5 do `byrev` (revisão-01), aceita como pendência fora de escopo desta entrega. Registrar apenas para rastreabilidade, sem reprovar o teste por isso |
| TC-37 (achado não-bloqueante, registrado) | `ConfigDicDadosModel::getTabelas()` não lista tabelas do `dbLogistica` na documentação técnica automática | Nenhuma (checagem estática de código) | Inspecionar o array `$dbGroups` em `ConfigDicDadosModel::getTabelas()` (linha ~39) | Tabelas `log_notif_sms_config`/`log_notif_sms_enviadas` não aparecem na ferramenta de documentação técnica automática/inventário, pois `'dbLogistica'` não está incluído em `$dbGroups`. **Não é falha desta entrega** — corresponde à Sugestão 7 do `byrev` (revisão-01), aceita como pendência fora de escopo desta entrega (é uma ferramenta de plataforma à parte, decisão do `byarq` na rodada de revisão de código). Registrar apenas para rastreabilidade, sem reprovar o teste por isso |

------------------------------------------------------------------------

## 12. Observações de execução

- TC-01 a TC-04 (Bloco A) dependem de dois ambientes distintos: um com tabelas ausentes (dev limpo) e outro simulando produção (tabelas já existentes) — não confundir os resultados esperados entre eles.
- TC-17, TC-27 e TC-35 são casos parcialmente documentais (checagem estática de código-fonte combinada com verificação funcional) — reportar tanto o resultado do grep quanto o comportamento observado em execução.
- TC-25 reflete a solução final adotada para o Bloqueante 1 da revisão-01: **não** foi apenas corrigido o nome da coluna passada para `ForeignKeyUsageChecker` (correção sugerida originalmente), e sim substituída a abordagem por uma query direta escopada em `dbLogistica` — decisão tomada ao longo do ciclo de correção/revisão que resolveu simultaneamente o bug de bloqueio (Bloqueante 1) e o ponto de performance levantado na Sugestão 6. TC-27 valida especificamente que essa mudança de abordagem não deixou resíduo no trait nem afetou outras telas.
- TC-36 e TC-37 são achados não-bloqueantes já aceitos como pendência (Sugestões 5 e 7 do `byrev`, respectivamente) — não devem ser reportados como falha de teste, apenas registrados para rastreabilidade.
- Todos os casos de bloqueio de acesso (TC-06) e de permissão (TC-07) seguem o comportamento *fail-closed* documentado em `rascunho-helpers-php.md`.

------------------------------------------------------------------------

## 13. Rastreabilidade

Cada bloco de casos de teste referencia a seção correspondente do documento de desenvolvimento aprovado (`docs/desenvolvimento/notificacoes-sms-dev.docx`, seções 4, 6.1, 6.2, 10 e 11) e os achados da rodada 01 de revisão (`docs/revisao/notificacoes-sms-revisao-01.docx`, Bloqueantes 1-3 e Sugestões 4-7), permitindo rastrear diretamente do requisito/apontamento até o caso de teste e, na próxima etapa, até o resultado de execução (`docs/testes/notificacoes-sms-resultado-testes.docx`).
