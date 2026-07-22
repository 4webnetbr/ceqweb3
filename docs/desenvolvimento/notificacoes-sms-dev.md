# Documento de Desenvolvimento — Módulo Logística (CeqWeb 3.0): Notificações SMS

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (novo — `app/Controllers/Logistica`, a criar)
**Telas envolvidas:** `NotifSmsConfig` — CRUD de Regras de Notificação SMS · `NotifSmsEnviadas` — Consulta de SMS Enviados por período
**Tipo de trabalho:** Telas novas (parte administrativa de uma feature já parcialmente especificada).
**Origem:** Plano de arquitetura aprovado pelo `byarq`, complementando `docs/notificacoes-sms.md` (desenho de motor de regras, `SmsService`, Controller CLI e cron — já implementado/definido, fora do escopo deste ciclo).
**Status:** Aprovado com ressalvas pelo `byarq` (rodada de revisão) — ressalvas incorporadas nesta versão (ver 3.3, 3.7, 5, 6.1). Aprovado para codificação.

------------------------------------------------------------------------

## 1. Objetivo

Implementar a parte administrativa da feature de Notificações SMS do módulo Logística: uma tela de CRUD para cadastrar e gerenciar as regras de disparo de SMS (`NotifSmsConfig`) e uma tela de consulta do histórico de SMS enviados por período (`NotifSmsEnviadas`).

Este documento consolida o plano do `byarq` para uso direto do `bydev` na codificação — contém toda a estrutura de arquivos, campos, tabelas e regras necessárias.

------------------------------------------------------------------------

## 2. Escopo

**Dentro do escopo:**
- Cadastro de infraestrutura: módulo "Logística" em `cfg_modulo`, as 2 telas novas em `cfg_tela`, colunas de grid em `cfg_tela_lista` (só para `NotifSmsConfig`), permissão `CAEXN` para o perfil Super Admin em `cfg_perfil_item`.
- Novo grupo de conexão de banco `dbLogistica` apontando para o schema `logistica_db`, incluindo os ajustes necessários em `ConfigDicDadosModel::getDbGroupAndSchema()` e `ForeignKeyUsageChecker`.
- Controllers/Models/Entity/Views (reaproveitadas) das 2 telas completas.
- Migration de infraestrutura + criação condicional (idempotente) das 2 tabelas, já criadas fisicamente em produção pelo usuário.
- Pequenos acréscimos em JS já existente (`my_filter.js`, `my_fields.js`) para a tela de consulta e para o toggle de campos condicionais do formulário de regra.
- Rotas das 2 telas novas em `app/Config/Routes.php`.

**Fora do escopo deste ciclo (já resolvido em `docs/notificacoes-sms.md`, não mexer):**
- `SmsService` (integração com provedor SMS Dev).
- Controller CLI `NotificacaoSms::verificar()` e o cron de disparo.
- Qualquer integração via API com o app Logística antigo (Postgres, outro repositório) — os tipos de renovação/entrega (`nsc_ren_tipo`) são tratados como informação estática conhecida hoje (select com valores fixos no Entity), sem consumo de API.

------------------------------------------------------------------------

## 3. Decisões de Arquitetura

### 3.1 Banco de dados — novo DBGroup `dbLogistica`

- Banco: `dbLogistica` (grupo CI4), schema físico `logistica_db`, mesmo host/usuário/senha dos demais DBGroups do projeto.
- As 2 tabelas (`log_notif_sms_config`, `log_notif_sms_enviadas`) **já foram criadas fisicamente** pelo usuário em `logistica_db`. Este ciclo cobre apenas o código (Controllers/Models/Entities/migration de infraestrutura/rotas) — não a criação das tabelas em si, embora a migration inclua criação condicional para cobrir outros ambientes (ver 4.1).

### 3.2 Auditoria e soft delete — padrão simplificado

Ao contrário do padrão de auditoria completo usado em outras telas do projeto (`usu_criou`, timestamps de criação/alteração, `cfg_status` de workflow), `log_notif_sms_config` segue um schema simples, já definido e criado pelo usuário:
- `nsc_ativo CHAR(1)` — `'A'` (Ativo) / `'I'` (Inativo).
- Soft delete apenas via `nsc_excluido` (DATETIME).
- Sem `usu_criou`/`nsc_criado`/`nsc_alterado`.

**Decisão confirmada:** não introduzir `cfg_status` (workflow Pendente/Concluída) em nenhuma das 2 telas — são cadastro/consulta simples, sem workflow de aprovação.

**Ressalva do `byarq` (rodada de revisão):** a ausência de `usu_criou`/timestamps na tabela SQL é só sobre as colunas da tabela — o log de auditoria em Mongo (`LogMonModel`) é um mecanismo à parte, já usado em todo o sistema (ver `ConfigCorModel`/`CfgCor.php` e `FornecNotifDesvioModel`), e **deve ser mantido também aqui** por consistência. Ver detalhamento em 3.3.

### 3.3 Log de auditoria via `LogMonModel` (Model + Controller)

`LogisNotifSmsConfigModel` deve seguir o mesmo padrão de `ConfigCorModel`:

- `protected $allowCallbacks = true;`
- `protected $afterInsert = ['depoisInsert'];`
- `protected $afterUpdate = ['depoisUpdate'];`
- `protected $afterDelete = ['depoisDelete'];`
- `depoisInsert(array $data)`, `depoisUpdate(array $data)`, `depoisDelete(array $data)`: cada um instancia `new LogMonModel()` e chama `insertLog($this->table, 'Incluído'|'Alteração'|'Excluído', $registro, $data['data'])`, igual a `ConfigCorModel::depoisInsert/depoisUpdate/depoisDelete`.

O Controller `NotifSmsConfig` deve, no `edit($id, $show)`/`show($id)` (igual a `CfgCor::edit()`), popular `$this->data['log'] = buscaLog('log_notif_sms_config', $id);` antes de `echo view('vw_edicao', ...)`, para exibir "última alteração por/em" na tela — sem isso, `vw_edicao.php` não teria essa informação disponível.

`LogisNotifSmsEnviadasModel` (tela só de leitura, sem Entity, mesmo padrão de `CfgLoguser`) não recebe callbacks de log — não há insert/update/delete pela tela administrativa.

### 3.4 Campo `nsc_ren_tipo` — select estático

Sem FK, sem `criaSelectRelativo()` (conforme `rascunho-helpers-php.md`), valores fixos definidos diretamente no Entity:

| Valor | Descrição |
|---|---|
| `1` | Ceqnep |
| `2` | Transportadora |
| `3` | Hospital Retira |

Justificativa: os tipos de renovação/entrega hoje só existem no app Logística antigo (outro repositório/banco), cuja migração/integração via API fica para uma fase futura. Por ora, tratado como informação estática conhecida.

### 3.5 Permissões — apenas Super Admin nas telas novas

Via migration, só o perfil Super Admin recebe `pit_permissao = 'CAEXN'` (conforme `rascunho-helpers-php.md`: C-Consulta, A-Adição, E-Edição, X-Exclusão, N-Notificações) nas 2 telas novas, de cara. Outros perfis podem ser liberados posteriormente, fora deste ciclo.

### 3.6 Campo `nsc_telefones` — texto livre

CSV em texto livre (ex.: `5548999998888,5548911112222`), via `MyCampo::crInput()`. Sem componente dinâmico de lista de telefones (sem `bt-repete`/`addCampo`).

### 3.7 Toggle de campos condicionais por tipo de regra

`nsc_tipo_regra` (`entrega` / `saldo_baixo`) determina quais campos são exibidos:
- `entrega` → exibe `nsc_ren_tipo`, `nsc_ren_status_max`, `nsc_condicao`, `nsc_minutos_limite`.
- `saldo_baixo` → exibe `nsc_saldo_minimo`.

Implementado via nova função JS `alternaCamposTipoRegra(obj)` em `my_fields.js`, moldada em `verificaTipoAcao(obj)` (`my_fields.js:2419`), porém mais simples (2 valores estáticos, sem consulta AJAX). Deve disparar tanto no `onchange` do select (via `setFunChan('alternaCamposTipoRegra(this)')`) quanto no carregamento da tela de edição (para refletir o estado salvo).

Os campos de cada grupo ficam envolvidos em wrappers `<div id="divEntrega">` e `<div id="divSaldo">`.

**Ressalva do `byarq` (rodada de revisão):** `alternaCamposTipoRegra(obj)` **não deve reimplementar** show/hide + obrigatoriedade do zero. Ela deve cuidar apenas do show/hide dos wrappers (`$('#divEntrega').show()/hide()`, idem `#divSaldo`, conforme o valor de `obj`) e delegar a obrigatoriedade condicional dos campos para a função já existente em `my_fields.js`, `mudaObrigatorioElemDiv(div, obriga)` (o mesmo mecanismo usado em `my_fornecedores.js`, moldado em `verificaTipoAcao()`):

```javascript
function alternaCamposTipoRegra(obj) {
    var entrega = ($(obj).val() == 'entrega');
    $('#divEntrega').toggle(entrega);
    $('#divSaldo').toggle(!entrega);
    mudaObrigatorioElemDiv('#divEntrega', entrega);
    mudaObrigatorioElemDiv('#divSaldo', !entrega);
}
```

(pseudocódigo ilustrativo — sintaxe exata a confirmar durante a codificação com o `bydev`, ver seção 9).

------------------------------------------------------------------------

## 4. Estrutura de Banco de Dados

### 4.1 Migration de infraestrutura + criação condicional das tabelas

Arquivo: `app/Database/Migrations/2026-0X-XX-XXXXXX_LogisticaNotifSms.php`, moldado em `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php`.

**`up()`:**
1. `criaInfraestrutura()` (via `db_connect('default')`):
   - `cfg_modulo` — módulo "Logística" (`mod_dbgroup = 'dbLogistica'`).
   - `cfg_tela` — 2 telas novas: `NotifSmsConfig` e `NotifSmsEnviadas` (idempotente por `tel_controler`).
   - `cfg_tela_lista` — colunas da grid, apenas para `NotifSmsConfig` (nome / tipo / telefones / status).
   - `cfg_perfil_item` — perfil Super Admin, `pit_permissao = 'CAEXN'` nas 2 telas novas (idempotente).
2. `criaTabelasSeNecessario()` (via `\Config\Database::forge('dbLogistica')`, guardado por `tableExists()`): cria as 2 tabelas **somente se não existirem** — cobre ambientes onde ainda não foram criadas manualmente (dev/homologação); em produção, onde já existem, esta etapa não faz nada.

**`down()`:** dropa as 2 tabelas; não remove a infraestrutura `cfg_*` (mesmo padrão da migration de referência).

**Atenção operacional (regra do usuário — nunca rodar migration sem confirmação explícita):** como as tabelas já existem em produção, rodar a migration lá deve apenas cadastrar infraestrutura (módulo/tela/permissão), sem tocar nas tabelas. Confirmar com o usuário antes de rodar em qualquer ambiente.

### 4.2 Tabela `log_notif_sms_config`

Prefixo de coluna: `nsc_`. Schema já criado em produção:

```sql
CREATE TABLE log_notif_sms_config (
    nsc_id                INT PRIMARY KEY AUTO_INCREMENT,
    nsc_nome              VARCHAR(100) NOT NULL,
    nsc_tipo_regra        ENUM('entrega','saldo_baixo') NOT NULL DEFAULT 'entrega',
    nsc_ren_tipo          INT NULL,
    nsc_ren_status_max    INT NULL,
    nsc_condicao          ENUM('antes_chegada','apos_chegada') NULL,
    nsc_minutos_limite    INT NULL,
    nsc_saldo_minimo      INT NULL,
    nsc_telefones         VARCHAR(255) NOT NULL,
    nsc_mensagem_template TEXT NOT NULL,
    nsc_ativo             CHAR(1) NOT NULL DEFAULT 'A',
    nsc_excluido          DATETIME NULL
);
```

Sem auditoria de usuário/timestamps (ver 3.2).

### 4.3 Tabela `log_notif_sms_enviadas`

Prefixo: `nse_`. Tabela de histórico/log, apenas leitura pela tela administrativa (gravação é feita pelo Controller CLI, fora do escopo deste ciclo).

```sql
CREATE TABLE log_notif_sms_enviadas (
    nse_id         INT PRIMARY KEY AUTO_INCREMENT,
    nse_chave      VARCHAR(100) NOT NULL,
    nse_nsc_id     INT NOT NULL,
    nse_data_envio DATETIME NOT NULL,
    UNIQUE KEY uk_chave_regra (nse_chave, nse_nsc_id),
    KEY idx_nse_data_envio (nse_data_envio)
);
```

### 4.4 Diagrama de relacionamento (resumo textual)

```
log_notif_sms_config (nsc_id)
   └─< log_notif_sms_enviadas (nse_nsc_id)   [FK lógica, sem constraint física exigida]
```

------------------------------------------------------------------------

## 5. Estrutura de Arquivos a Criar

```
app/Controllers/Logistica/NotifSmsConfig.php     (namespace App\Controllers\Logistica)
app/Controllers/Logistica/NotifSmsEnviadas.php   (namespace App\Controllers\Logistica)

app/Models/Logis/LogisNotifSmsConfigModel.php    (namespace App\Models\Logis)
app/Models/Logis/LogisNotifSmsEnviadasModel.php  (namespace App\Models\Logis)

app/Entities/Logistica/EntLogNotifSmsConfig.php     (namespace App\Entities\Logistica)
(sem Entity para "Enviadas" — tela só de consulta/leitura, igual a CfgLoguser)

app/Database/Migrations/2026-0X-XX-XXXXXX_LogisticaNotifSms.php
```

Nenhuma view nova: reusar `vw_lista.php`/`vw_edicao.php` (tela de CRUD) e `vw_filtro.php` (tela de consulta), padrão já usado pelas demais telas do sistema.

**Arquivos de configuração existentes a editar:**
- `app/Config/Database.php` — novo grupo `public array $dbLogistica`, apontando para o schema `logistica_db`.
- `app/Models/Config/ConfigDicDadosModel.php::getDbGroupAndSchema()` (linha ~745) — adicionar `'log' => 'dbLogistica'` no `$groupMap`/`$prefixes`.
- `app/Traits/ForeignKeyUsageChecker.php` — adicionar `'dbLogistica'` em `$conexoesRelacionadas`.

**JS a adicionar em arquivos já existentes (sem novo arquivo JS):**
- `public/assets/jscript/my_filter.js`: `buscaSmsEnviadas()` (moldada em `buscaLogUser()`) e `montaListaSmsEnviadas(dados)` (moldada em `montaListaLogs(dados)`), conforme `rascunho-runtime-js.md` (não reinventar configuração de grid).
- `public/assets/jscript/my_fields.js`: `alternaCamposTipoRegra(obj)` (ver 3.6).

------------------------------------------------------------------------

## 6. Especificação Funcional por Tela

### 6.1 `NotifSmsConfig` — CRUD de Regras de Notificação SMS

Modelo de referência: `app/Controllers/Config/CfgCor.php`.

#### Model `LogisNotifSmsConfigModel`

- `$DBGroup = 'dbLogistica'`
- `$table = 'log_notif_sms_config'`
- `$primaryKey = 'nsc_id'`
- `$returnType = EntLogNotifSmsConfig::class`
- `$useSoftDeletes = true`, `$deletedField = 'nsc_excluido'`
- `$allowedFields` (todas as colunas de `log_notif_sms_config` exceto `nsc_id` e `nsc_excluido`, conforme ressalva do `byarq`):

```php
protected $allowedFields = [
    'nsc_nome',
    'nsc_tipo_regra',
    'nsc_ren_tipo',
    'nsc_ren_status_max',
    'nsc_condicao',
    'nsc_minutos_limite',
    'nsc_saldo_minimo',
    'nsc_telefones',
    'nsc_mensagem_template',
    'nsc_ativo',
];
```

- Validação: `nsc_nome`, `nsc_tipo_regra`, `nsc_telefones`, `nsc_mensagem_template` obrigatórios; demais campos condicionais conforme `nsc_tipo_regra` (validação condicional a espelhar sintaxe já usada em algum Model do projeto — a confirmar durante a codificação, ver seção 9).
- Callbacks de auditoria via `LogMonModel` — ver 3.3.
- Métodos: `getListaRegras()` / `getRegra($id)`.

#### Entity `EntLogNotifSmsConfig::defCampos()`

**Ressalva do `byarq` (rodada de revisão):** no projeto, Entities são nomeadas a partir do nome da tabela (com prefixo de domínio), não do nome funcional limpo — ex. tabela `oco_notif_desvio` → `EntOcoNotifDesvio`, não `EntNotifDesvio`. Por isso a Entity de `log_notif_sms_config` é `EntLogNotifSmsConfig` (e não `EntNotifSmsConfig`), em `app/Entities/Logistica/EntLogNotifSmsConfig.php` (ver seção 5).

| Campo | Componente `MyCampo` | Observação |
|---|---|---|
| `nsc_id` | `crOculto()` | |
| `nsc_nome` | `crInput()`, obrigatório | |
| `nsc_tipo_regra` | `crSelect()`, opções `['entrega' => 'Entrega', 'saldo_baixo' => 'Saldo Baixo']` | `setFunChan('alternaCamposTipoRegra(this)')` |
| `nsc_ren_tipo` | `crSelect()`, opções estáticas `[1 => 'Ceqnep', 2 => 'Transportadora', 3 => 'Hospital Retira']` | grupo "entrega" (`div#divEntrega`) |
| `nsc_ren_status_max` | `crInput()` tipo `number` | grupo "entrega" |
| `nsc_condicao` | `crSelect()`, opções `['antes_chegada' => 'Antes da Chegada', 'apos_chegada' => 'Após a Chegada']` | grupo "entrega" |
| `nsc_minutos_limite` | `crInput()` tipo `number` | grupo "entrega" |
| `nsc_saldo_minimo` | `crInput()` tipo `number` | grupo "saldo_baixo" (`div#divSaldo`) |
| `nsc_telefones` | `crInput()` — texto livre CSV | ver 3.6 |
| `nsc_mensagem_template` | `crTexto()` (textarea) | |
| `nsc_ativo` | `cr2opcoes()`, opções `['A' => 'Ativo', 'I' => 'Inativo']` | |

#### Controller `NotifSmsConfig` (espelhando `CfgCor.php`)

- `index()` → `montaColunasLista()` + `url_lista` → `view('vw_lista')`.
- `lista()` → `getListaRegras()` → `echo json_encode(['data' => ...])`.
- `add()` / `edit($id, $show)` / `show($id)` → `view('vw_edicao')`. `edit()`/`show()` populam `$this->data['log'] = buscaLog('log_notif_sms_config', $id);` antes da view, igual a `CfgCor::edit()` (ver 3.3).
- `store()` → `verificaUnico()` por `nsc_nome`, em transação.
- `ativinativ($id, $tipo)` → seta `nsc_ativo` `'A'`/`'I'`; permite inativar livremente mesmo havendo histórico em `log_notif_sms_enviadas`.
- `delete($id)` → checa via `ForeignKeyUsageChecker` se há registros associados em `log_notif_sms_enviadas`; se houver, **bloqueia a exclusão**.

Nomenclatura de métodos segue o padrão exigido por `LoginFilter`/log de auditoria automático (`rascunho-helpers-php.md`): `index/lista/show/add/store/edit/update/delete/ordena/ativinativ`.

### 6.2 `NotifSmsEnviadas` — Consulta de SMS Enviados por período

Modelo de referência: `app/Controllers/Config/CfgLoguser.php`. Tela somente leitura, sem Entity própria (mesmo padrão de `CfgLoguser`).

#### Model `LogisNotifSmsEnviadasModel`

- `$DBGroup = 'dbLogistica'`
- `$table = 'log_notif_sms_enviadas'`
- `$primaryKey = 'nse_id'`
- `$returnType = 'array'`
- Método `getEnviadasPeriodo(string $dataIni, string $dataFim, ?int $nscId = null)`: `JOIN` com `log_notif_sms_config` para trazer `nsc_nome`; filtra por período (`nse_data_envio`) e, opcionalmente, por regra (`nse_nsc_id`); ordenado por `nse_data_envio DESC`.

#### Controller `NotifSmsEnviadas`

- `index()`:
  - Filtro de período obrigatório via `crDaterange()`.
  - Filtro opcional por regra via `criaSelectRelativo('log_notif_sms_config', 'nsc_id', 'nsc_nome', null, 1, 'log_notif_sms_enviadas', [], $config, 'nse_nsc_id')` (conforme `rascunho-helpers-php.md`).
  - Botão "Buscar"; `colunas = ['Data Envio', 'Chave', 'Regra']`; `view('vw_filtro')`.
- `lista()`: lê `getPost()`, converte datas com `data_db()` (helper padrão do projeto, `rascunho-helpers-php.md`), chama `getEnviadasPeriodo()`, `echo json_encode($dados)`.

------------------------------------------------------------------------

## 7. Rotas (`app/Config/Routes.php`)

```php
$logisticaControllers = ['NotifSmsConfig', 'NotifSmsEnviadas'];
foreach ($logisticaControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Logistica\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Logistica\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}
```

------------------------------------------------------------------------

## 8. Ordem de Implementação

1. **Infraestrutura:** novo grupo `dbLogistica` em `Database.php`; ajustes em `ConfigDicDadosModel::getDbGroupAndSchema()` e `ForeignKeyUsageChecker`.
2. **Migration:** módulo Logística em `cfg_modulo`, as 2 telas em `cfg_tela`, colunas de grid em `cfg_tela_lista` (só `NotifSmsConfig`), permissão `CAEXN` para Super Admin em `cfg_perfil_item`; criação condicional das 2 tabelas.
3. `NotifSmsConfig` **completo:** Model, Entity, Controller, toggle de campos condicionais (`alternaCamposTipoRegra`), views reaproveitadas (`vw_lista`/`vw_edicao`).
4. `NotifSmsEnviadas` **completo:** Model, Controller, funções JS de filtro/listagem (`buscaSmsEnviadas`/`montaListaSmsEnviadas`), view reaproveitada (`vw_filtro`).
5. **Rotas** das 2 telas novas.
6. **Verificação manual** (ver seção 10).

------------------------------------------------------------------------

## 9. Itens a Confirmar Durante a Implementação

- Onde os wrappers `<div id="div...">` de campos condicionais são gerados hoje no projeto (via `MyCampo`/`dispForm` ou array manual na view) — checar antes de codificar o toggle `alternaCamposTipoRegra()`.
- Se `ordena()` é obrigatório em todo Controller CRUD do projeto (`CfgCor.php`, usado como referência, não o implementa).
- Sintaxe exata de validação condicional (campo obrigatório apenas conforme valor de outro campo) já usada em algum Model existente do projeto.
- Nome exato do perfil "Super Admin" cadastrado em `cfg_perfil` (para a migration de `cfg_perfil_item`).

### Arquivos críticos de referência para o `bydev`

- `app/Config/Database.php`
- `app/Models/Config/ConfigDicDadosModel.php` (`getDbGroupAndSchema`, linha ~745)
- `app/Traits/ForeignKeyUsageChecker.php`
- `app/Controllers/Config/CfgCor.php` e `app/Controllers/Config/CfgLoguser.php`
- `app/Models/Config/ConfigCorModel.php` e `app/Models/Fornec/FornecNotifDesvioModel.php` (padrão de callbacks `afterInsert/afterUpdate/afterDelete` + `LogMonModel::insertLog()`, ver 3.3)
- `app/Models/LogMonModel.php`
- `app/Entities/Config/EntCfgCor.php` e `app/Entities/Ocorrencia/EntOcoTipoOcorre.php`
- `app/Config/Routes.php`
- `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php`
- `public/assets/jscript/my_filter.js` e `public/assets/jscript/my_fields.js` (incluindo `mudaObrigatorioElemDiv()` e o uso em `my_fornecedores.js`, ver 3.7)
- `docs/notificacoes-sms.md` (desenho do motor de regras/`SmsService`/CLI/cron, fora deste escopo mas necessário para contexto de schema)

------------------------------------------------------------------------

## 10. Verificação / Teste Manual

1. Confirmar conexão `dbLogistica`/`logistica_db` antes de rodar a migration.
2. Rodar a migration em ambiente de dev, confirmar idempotência (rodar 2x sem duplicar infraestrutura) e que não recria tabelas já existentes.
3. Logar como Super Admin, confirmar que o menu mostra o módulo Logística com as 2 telas novas.
4. Tela `NotifSmsConfig`:
   - Criar regra tipo "entrega" (confirmar que o toggle esconde os campos de saldo).
   - Criar regra tipo "saldo_baixo" (confirmar toggle inverso).
   - Editar uma regra existente.
   - Inativar uma regra (`ativinativ`).
   - Testar bloqueio de exclusão quando há histórico em `log_notif_sms_enviadas`.
5. Tela `NotifSmsEnviadas`:
   - Inserir linhas de teste em `log_notif_sms_enviadas`.
   - Filtrar por período.
   - Filtrar por regra específica.
   - Confirmar que o `JOIN` traz corretamente o nome da regra (`nsc_nome`).

------------------------------------------------------------------------

## 11. Critérios de Pronto

- Novo grupo de banco `dbLogistica` configurado e reconhecido por `ConfigDicDadosModel::getDbGroupAndSchema()` e `ForeignKeyUsageChecker`.
- Módulo Logística cadastrado em `cfg_modulo`; as 2 telas cadastradas em `cfg_tela`, com permissão `CAEXN` configurada para o perfil Super Admin.
- Migration idempotente: rodar múltiplas vezes não duplica infraestrutura nem recria tabelas existentes.
- `NotifSmsConfig`: listagem, cadastro, edição, ativar/inativar e exclusão (com bloqueio quando há histórico) funcionais; toggle de campos condicionais por `nsc_tipo_regra` funcionando tanto no `onchange` quanto no carregamento da tela de edição.
- `NotifSmsEnviadas`: filtro por período (obrigatório) e por regra (opcional) funcionais, com `JOIN` trazendo `nsc_nome` corretamente.
- `LogisNotifSmsConfigModel` grava log de auditoria em Mongo via `LogMonModel::insertLog()` em `afterInsert/afterUpdate/afterDelete` (padrão `ConfigCorModel`); `NotifSmsConfig::edit()`/`show()` exibem "última alteração por/em" via `buscaLog('log_notif_sms_config', $id)`.
- `alternaCamposTipoRegra(obj)` cuida apenas do show/hide dos wrappers e delega a obrigatoriedade condicional a `mudaObrigatorioElemDiv()` (sem reimplementação própria).
- Nenhuma alteração de código nas partes já resolvidas em `docs/notificacoes-sms.md` (`SmsService`, Controller CLI, cron).
- `byrev` sem apontamentos pendentes; `bytest` com plano de testes cobrindo as duas telas.

------------------------------------------------------------------------

## 12. Rastreabilidade

Este documento formaliza, para codificação, o plano de arquitetura aprovado pelo `byarq` para a parte administrativa da feature de Notificações SMS, complementando o desenho técnico já existente em `docs/notificacoes-sms.md`. Qualquer apontamento de revisão (`byrev`) ou caso de teste (`bytest`) sobre esta feature deve referenciar a seção correspondente deste documento (6.1/6.2 para as regras de tela, 4.1–4.3 para o schema).
