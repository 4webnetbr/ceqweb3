# Apontamentos de Revisão — Notificações SMS (Logística)

**Rodada:** 01
**Revisor:** `byrev`
**Projeto:** CeqWeb 3.0
**Módulo:** Logística (`app/Controllers/Logistica`)
**Feature revisada:** CRUD de Regras de Notificação SMS + Consulta de SMS Enviados
**Documento de referência:** `docs/desenvolvimento/notificacoes-sms-dev.docx` (aprovado)
**Arquivos revisados:**
- `app/Controllers/Logistica/NotifSmsConfig.php`
- `app/Entities/Logistica/EntLogNotifSmsConfig.php`
- `app/Database/Migrations/` (migration de `log_notif_sms_config` / `log_notif_sms_enviadas`)
- `app/Models/Logistica/` (Models envolvidos)
- `public/assets/jscript/my_filter.js` / `my_fields.js`
- `app/Config/Database.php` / `app/Config/Routes.php`
- `app/Models/ConfigDicDadosModel.php`

**Destino:** `byarq` (ciência dos apontamentos) → `bydev` (correção)

------------------------------------------------------------------------

## Contexto

Rodada de revisão do código da feature "CRUD de Regras de Notificação SMS + Consulta de SMS Enviados", codificado conforme o documento de desenvolvimento aprovado `docs/desenvolvimento/notificacoes-sms-dev.docx`. O `byrev` avaliou o Controller e a Entity de `NotifSmsConfig`, a migration das novas tabelas (`log_notif_sms_config`/`log_notif_sms_enviadas`), o uso do trait de bloqueio de exclusão por relacionamento (`ForeignKeyUsageChecker`), o JS de front, a integração com `LogMonModel` (auditoria), as validações (`MyValidation`) e o cadastro nos arquivos de configuração compartilhados (`Database.php`, `ConfigDicDadosModel.php`, `Routes.php`).

------------------------------------------------------------------------

## 1. Achados Bloqueantes

### Bloqueante 1 — Bloqueio de exclusão (`delete()`) não funciona: nome de coluna FK incompatível com `ForeignKeyUsageChecker`

**Arquivo:** `app/Controllers/Logistica/NotifSmsConfig.php:214`

**Situação encontrada:** O método `delete()` chama `verificarUsoEmRelacionamentos('log_notif_sms_config', 'nsc_id', (int) $id)`. O trait `ForeignKeyUsageChecker` procura, em todos os schemas cadastrados, uma coluna cujo **nome literal** seja igual ao terceiro parâmetro informado (`'nsc_id'`). Porém a FK real em `log_notif_sms_enviadas` se chama `nse_nsc_id`, não `nsc_id`.

**Por que é um problema:** Como a busca do trait é por igualdade exata de nome de coluna, a checagem nunca encontra o relacionamento entre `log_notif_sms_enviadas.nse_nsc_id` e `log_notif_sms_config.nsc_id`. Resultado: a exclusão de uma regra de notificação **nunca é bloqueada**, mesmo quando já existe histórico de SMS enviados vinculado a ela — contradizendo diretamente a regra de negócio da Seção 6.1 do documento de desenvolvimento e o roteiro de teste manual previsto para essa validação.

**Correção sugerida:** Ajustar a chamada em `delete()` para passar o nome real da coluna de FK (`'nse_nsc_id'`) como terceiro parâmetro — ou, se o trait aceitar/precisar de outro ajuste (ex.: mapeamento de nomes divergentes entre PK e FK), tratar isso de forma explícita para que a busca encontre o relacionamento correto.

------------------------------------------------------------------------

### Bloqueante 2 — Formulário de `NotifSmsConfig` fica sem nenhum label visível

**Arquivo:** `app/Entities/Logistica/EntLogNotifSmsConfig.php` (em conjunto com a migration de `log_notif_sms_config`)

**Situação encontrada:** A Entity monta praticamente todos os campos via `new MyCampo('log_notif_sms_config', 'campo')`, sem nunca chamar `setLabel()`/`->label`. Conforme `rascunho-MyCampo.md`, o label de `MyCampo::doBanco()` vem exclusivamente do `COLUMN_COMMENT` da coluna no banco, e `fmtDisplay()` só renderiza `<label>` quando `$this->label !== ''` — não há fallback para o nome técnico do campo. A migration de `log_notif_sms_config` não define `'comment'` em nenhum dos `addField()`.

**Por que é um problema:** Em qualquer ambiente onde a tabela seja criada a partir da migration (dev, homologação, e futuramente produção), todos os campos do formulário de "Regra de Notificação" aparecem **sem rótulo algum**, prejudicando o uso da tela pelo usuário final.

**Correção sugerida:** Optar por uma das duas abordagens (conforme padrão já usado em outras telas do sistema):
- (a) adicionar `'comment'` em cada `addField()` da migration, com o texto do rótulo desejado para cada campo; ou
- (b) setar `->label` explicitamente em cada `MyCampo` da Entity.

------------------------------------------------------------------------

### Bloqueante 3 — Toggle de campos condicionais não é inicializado em `add()` — só em `edit()`

**Arquivo:** `app/Controllers/Logistica/NotifSmsConfig.php`

**Situação encontrada:** `NotifSmsConfig::add()` não seta `$this->data['script']`, diferente de `edit()`, que dispara `alternaCamposTipoRegra(...)` já no carregamento da tela. O wrapper `#divSaldo` não possui a classe `d-none` por padrão no HTML gerado.

**Por que é um problema:** Como uma "Nova Regra" nasce com `nsc_tipo_regra = 'entrega'` por default, ao abrir a tela de inclusão os dois grupos de campos condicionais — Entrega e Saldo — ficam **visíveis simultaneamente** até o usuário interagir manualmente com o select de tipo de regra. Isso é inconsistente com o comportamento de `edit()` e pode confundir o usuário ou permitir preenchimento de campos que não se aplicam ao tipo de regra selecionado.

**Correção sugerida:** Replicar em `add()` a mesma inicialização de `$this->data['script']` feita em `edit()`, disparando `alternaCamposTipoRegra(...)` já no carregamento, considerando o valor default de `nsc_tipo_regra`.

------------------------------------------------------------------------

## 2. Sugestões Não-Bloqueantes

### Sugestão 4 — `nsc_condicao` sem `in_list` na validação

Diferente de `nsc_tipo_regra` (que tem `in_list[entrega,saldo_baixo]`), o campo `nsc_condicao` está definido apenas como `permit_empty|obrigatorioSeTipoRegraEntrega`, sem `in_list`. Como `strictOn` é `false` em `dbLogistica`, um valor de ENUM inválido pode ser truncado silenciosamente pelo MySQL em vez de ser barrado pela validação do CI4.

**Sugestão:** acrescentar `in_list[...]` com os valores válidos de `nsc_condicao`, no mesmo padrão de `nsc_tipo_regra`.

------------------------------------------------------------------------

### Sugestão 5 — Grid de `NotifSmsConfig` exibe valores brutos de enum

A listagem mostra `nsc_tipo_regra` (`entrega`/`saldo_baixo`) e `nsc_ativo` (`A`/`I`) sem tradução amigável. Comportamento genérico do framework, não introduzido especificamente por este código, mas vale considerar exibir rótulo amigável nesses campos na grid.

------------------------------------------------------------------------

### Sugestão 6 — Repetição de `ForeignKeyUsageChecker::$conexoesRelacionadas` para `dbLogistica` amplia custo de performance já existente

A inclusão de `dbLogistica` em `$conexoesRelacionadas` adiciona mais uma repetição do `SHOW DATABASES` + varredura de tabelas/colunas em **todo** `delete()` do sistema que usa o trait — já era redundante antes desta mudança, mas fica ~25% mais lento (5 iterações em vez de 4) em toda tela do sistema que usa o trait. Ponto de atenção de performance amplificado por uma mudança compartilhada entre módulos.

------------------------------------------------------------------------

### Sugestão 7 — `ConfigDicDadosModel::getTabelas()` não inclui `dbLogistica`

`ConfigDicDadosModel::getTabelas()` (linha 39) não inclui `'dbLogistica'` no array `$dbGroups` usado pela tela de documentação técnica automática. Não foi pedido explicitamente no documento de desenvolvimento, mas por consistência as tabelas novas (`log_notif_sms_config`/`log_notif_sms_enviadas`) não aparecerão nessa ferramenta de inventário.

------------------------------------------------------------------------

## 3. Itens Já Aprovados Sem Apontamento (rastreabilidade)

Itens abaixo foram checados pelo `byrev` e estão conformes com o documento de desenvolvimento aprovado e com os padrões de `docs/referencia/` — registrados apenas para rastreabilidade:

- Wrapper de divs condicionais montado no Controller — equivalente ao mecanismo já usado em `pw_acoes_notif.php`.
- `$this->data['script']` com jQuery em `edit()` — padrão real do projeto (`CfgEtiqueta.php`, `CfgMenu.php`, etc.).
- `MyValidation::obrigatorioSeTipoRegraEntrega`/`obrigatorioSeTipoRegraSaldo` seguem o molde de `obrigatorioSeNotivisaSim`.
- Log de auditoria via `LogMonModel` implementado corretamente (ressalva crítica do `byarq` na rodada anterior, resolvida).
- Migration idempotente correta (checagens de existência, `prf_id=1`, `down()` preserva infraestrutura).
- `Database.php`/`ConfigDicDadosModel`/`Routes.php` seguem o padrão dos grupos existentes, sem colisão de prefixo.
- `my_filter.js`/`my_fields.js` seguem `executaAjaxWait`/`boxAlert`, sem `jQuery.ajax`/`alert()` cru, sem duplicar `mudaObrigatorioElemDiv()`.
- Nomenclatura de métodos dos Controllers correta; `ordena()` corretamente omitido.

------------------------------------------------------------------------

## 4. Conclusão do `byrev`

Itens 1, 2 e 3 (Seção 1) são **bloqueantes** — impedem regra de negócio de bloqueio de exclusão de funcionar, deixam o formulário sem rótulos visíveis e quebram a consistência do toggle de campos condicionais entre inclusão e edição — e devem ser corrigidos antes de avançar para `bytest`. Itens 4 a 7 (Seção 2) são sugestões de robustez/consistência, a decidir pelo `byarq` se entram neste ciclo ou ficam para o próximo.

------------------------------------------------------------------------

## 5. Encaminhamento

1. `byarq` — tomar ciência dos 3 bloqueantes e das 4 sugestões; confirmar se as sugestões 4 a 7 entram neste ciclo ou ficam para o próximo.
2. `bydev` — aplicar a correção dos Bloqueantes 1, 2 e 3 (ajuste do nome da coluna FK em `verificarUsoEmRelacionamentos()`; adição de labels/`comment` nos campos de `log_notif_sms_config`; inicialização do toggle de campos condicionais também em `add()`).
3. Retornar ao `byrev` para nova rodada de revisão após as correções.
