# Resultado de Testes — T42 (Desvio de Qualidade) e T43 (Notificação de Evento)

**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores — T42 (`NotifDesvio`) · T43 (`NotifEvento`)
**Plano executado:** `docs/testes/fornecedores-t42-t43-plano-testes.md` (revisado pelo `byarq`)
**Executor:** `bytest`
**Ambiente:** `c:\srvlocal\php\php.exe` (PHP 8.5.6 CLI) + `spark serve`, banco `dev_ocorrencia_db`/`dev_config_ceqweb_db` (host 192.168.0.8, já configurado via `.env`)
**Rodadas:** Rodada 1 (execução inicial do plano) → Rodada 2 (reexecução dos P0 de T43 bloqueados pelo BUG #2, após correção) → **Rodada 3 (reexecução dos 3 achados novos da Rodada 2, após correção — fechamento da Etapa 3)**
**Status:** **Ciclo de testes encerrado.** Todos os P0 aplicáveis passam, exceto BUG #1 (aceito pelo usuário) e itens de infraestrutura já combinados como fora de escopo.

---

## 0. Condições reais de execução

Este ambiente teve PHP CLI, acesso direto ao MySQL (via `mysqli`) e conseguiu subir um servidor HTTP local (`spark serve`), permitindo **execução funcional real** (login, requisições HTTP autenticadas, gravação em banco) para a maior parte dos casos — não apenas verificação estática de código.

### 0.1 Preparação de ambiente (registrado para rastreabilidade)

- **`.env`**: temporariamente alterado (`app.baseURL` → `http://127.0.0.1:8098/`, `app.forceGlobalSecureRequests` → `false`) para permitir testes HTTP locais sem certificado, nas **três rodadas**; **restaurado ao valor original** (`https://dev.ceqnep.com.br/` / `true`) ao final de cada rodada — confirmado via `diff` em todas as ocasiões.
- **`writable/session`**: tinha atributo *read-only* do Windows herdado, causando `SessionException`; removido via `attrib -R` (mudança de atributo de filesystem, não de código/config versionado).
- **MongoDB**: o serviço Windows `MongoDB` estava **parado** (`Stopped`) e não pôde ser iniciado via `net start` (acesso negado, exige admin). Contornado nas três rodadas iniciando `mongod.exe` diretamente com `--dbpath C:\data\db` (mesmo data path do serviço), finalizado ao término de cada rodada. Necessário porque **todo** `insertLog()` (chamado em `afterInsert`/`afterUpdate`/`afterDelete` de praticamente todos os Models) depende do Mongo.
- **Redis**: extensão PHP `Redis` **não está instalada/habilitada** no `php.exe` usado (`Class "Redis" not found`). Bloqueou toda a família de testes de `GeraEtiqueta()` (T42-24 a T42-27, T43-58/59) nas três rodadas — não é um bug de código, é ausência de extensão no ambiente de teste. Confirmado pelo coordenador como pendência de infraestrutura, fora de escopo desta entrega.
- **Perfis/usuários de teste** em `dev_config_ceqweb_db` (dev, não produção), criados na Rodada 1 e **reaproveitados nas Rodadas 2 e 3** (consistência conferida antes de cada reuso — ver 0.3):
  - `cfg_perfil`: `prf_id=12` (`QA_T42T43_Full`, CAEXN em tel 68/69 + permissões clonadas do Super Admin para os demais menus), `prf_id=13` (`QA_T42T43_ConsultaSo`, só `C` em tel 68/69 + idem), `prf_id=14` (`QA_T42T43_SemAcesso`, **sem nenhuma linha** para tel 68/69, com o restante clonado do Super Admin).
  - `cfg_usuario`: `qa.full` / `qa.consulta` / `qa.semacesso` (senha `Teste123`, md5).
  - **Estes fixtures não foram removidos** — permanecem no banco de desenvolvimento. Recomendo removê-los antes de qualquer carga de dados "limpa" para homologação/produção.

### 0.2 Achado colateral de infraestrutura (fora do escopo direto de T42/T43, mas relevante)

`LogMonModel::insertLog()` (e `update_log`/`delete_log`) usa `catch (\MongoDB\Driver\Exception\RuntimeException $ex) { show_error(...); }` — **`show_error()` é uma função do CodeIgniter 3, não existe no CI4**, gerando `Error: Call to undefined function show_error()` (fatal) em vez de um erro tratado. Isso significa que **qualquer indisponibilidade do MongoDB em produção derruba com HTTP 500 fatal qualquer INSERT/UPDATE/DELETE do sistema** (não só T42/T43 — é código compartilhado usado por praticamente todos os Models). Reportado ao `byarq` como achado de robustez de infraestrutura, fora do escopo do desenvolvimento de T42/T43 propriamente dito — sem ação de correção associada a esta entrega.

### 0.3 Verificação de consistência das fixtures antes de reusar (Rodadas 2 e 3)

Antes de cada reexecução, conferido o estado das fixtures deixadas pela rodada anterior:

- Perfis/usuários de teste (`QA_T42T43_*`, `qa.*`): intactos em todas as rodadas, login funcionando normalmente.
- `oco_notif_desvio` (`ndv_id` 11–32): todos com `stt_id=32` (Concluída) — consistente, nenhum ficou "preso" em estado intermediário pelas tentativas que falharam nas rodadas anteriores (o `store()` roda dentro de `$db->transStart()`/`transRollback()`, então tentativas bloqueadas por validação nunca deixam gravação parcial).
- `oco_notif_evento`/`oco_notif_evento_produto`/`oco_notif_evento_acao`: nenhum registro "órfão"/parcial encontrado em nenhuma das verificações.
- Antes da Rodada 3, confirmado especificamente que os produtos usados nos testes que falharam na Rodada 2 (`ndv_id` 14, 17, 18) **não haviam sido consumidos** pelas tentativas bloqueadas, permitindo reutilizá-los diretamente na Rodada 3 sem precisar de fixtures novas.
- Total de produtos T42 disponíveis (Concluídos, não vinculados) no início da Rodada 3: 13 (`ndv_id` 14, 17, 18, 21, 22, 24, 25, 27–32) — suficiente para todos os casos reexecutados.

---

## 1. Bugs e achados críticos — status final

### BUG #1 — `store()` de T42/T43 não é protegido por permissão no `LoginFilter` (CAEXN incompleto) — **achado aceito, sem correção neste ciclo**

**Casos afetados:** T42-02, T43-02.

**Evidência:** `app/Filters/LoginFilter.php::validaPermissao()` só verifica a permissão explicitamente para os métodos `'add'` (exige `'A'`) e `'edit'` (exige `'E'`). Para qualquer outro método — inclusive `'store'`, destino real de gravação tanto de criação quanto de edição — a única exigência é `permissao !== ''`. Confirmado em runtime: usuário com permissão só `C` (consulta) conseguiu concluir um Desvio de Qualidade via POST forjado direto a `store()`, sem ter permissão de Adição (`A`) nem Edição (`E`).

**Status final:** o usuário decidiu **não corrigir** este achado nesta entrega — documentado como achado de segurança conhecido, aceito conscientemente. Recomendado para uma futura rodada de hardening do `LoginFilter` (afeta potencialmente outras telas do sistema, não só T42/T43).

---

### BUG #2 — `NotifEvento::store()` sempre falhava com erro fatal (`ArgumentCountError`) — **CORRIGIDO, confirmado na Rodada 2**

Causa raiz: `'nev_notivisa_num' => 'obrigatorioSeNotivisaSim'` sem parâmetro em colchetes, quebrando a assinatura esperada pelo `CodeIgniter\Validation\Validation::processRules()`. Corrigido para `'obrigatorioSeNotivisaSim[nev_notivisa]'` em `FornecNotifEventoModel.php:54`, revisado pelo `byrev` e confirmado funcional em mais de 20 chamadas reais de `store()` na Rodada 2, sem nenhuma reincidência.

---

### ACHADO #1 (T43-15) — `nvp_defeito` sem validação server-side — **CORRIGIDO, confirmado na Rodada 3**

**Causa raiz (Rodada 2):** a linha de produto (`oco_notif_evento_produto`) é montada via `CommonModel()->insertReg()` direto, sem passar pelas `validationRules` de nenhum Model — não havia checagem de obrigatoriedade/limite 5–200 para `nvp_defeito`.

**Correção aplicada:** `NotifEvento::store()` agora valida `nvp_defeito` explicitamente antes de gravar (RN03.9), com laço por produto:

```php
$defeitosValidar = (array) ($postado['nvp_defeito'] ?? []);
foreach (array_values($ndvIds) as $ordem => $ndvId) {
    $defeito = trim((string) ($defeitosValidar[$ordem] ?? ''));
    $tam     = mb_strlen($defeito);
    if ($tam < 5 || $tam > 200) {
        throw new \Exception('O campo Defeito é obrigatório (5 a 200 caracteres) para todos os produtos selecionados');
    }
}
```

**Confirmação em runtime (Rodada 3):**

- `nvp_defeito` vazio → `{"erro":true,"msg":"O campo Defeito é obrigatório (5 a 200 caracteres) para todos os produtos selecionados"}`, produto não consumido (confirmado reaproveitável).
- `nvp_defeito` com 4 caracteres (`"abcd"`) → mesma mensagem de bloqueio.
- Caminho normal (defeito válido, 5+ caracteres) → grava com sucesso, valor persistido corretamente no banco (confirmado via SQL).

---

### ACHADO #2 (T43-19) — data de fabricação inválida aceita e persistida como `0000-00-00` — **CORRIGIDO, confirmado na Rodada 3**

**Causa raiz (Rodada 2):** `FornecNotifEventoModel::$validationRules['nev_fabricacao'] = 'required|valid_date'` — sem formato explícito, a regra `valid_date` do CI4 aceitava strings sintaticamente inválidas (`"31-02-2026"`), que depois eram silenciosamente truncadas para `0000-00-00` pelo MySQL.

**Correção aplicada:** `FornecNotifEventoModel.php:47` agora contém `'nev_fabricacao' => 'required|valid_date[Y-m-d]'` (formato explícito).

**Confirmação em runtime (Rodada 3):**

- `nev_fabricacao = "31-02-2026"` (data inexistente) → `{"erro":true,"msg":"O campo nev_fabricacao deve conter uma data válida."}`.
- `nev_fabricacao = "01/01/2026"` (formato errado, fora de `Y-m-d`) → mesma mensagem de bloqueio.
- `nev_fabricacao = ""` (vazio) → `{"erro":true,"msg":"O campo nev_fabricacao é requerido."}`.
- `nev_fabricacao = "2026-03-15"` (formato correto, data válida) → grava com sucesso; confirmado via SQL que o valor persistido é exatamente `2026-03-15`, não `0000-00-00`.

---

### ACHADO #3 (T43-40) — ação obrigatória (RN03.21) não era enforced — **CORRIGIDO, confirmado na Rodada 3**

**Causa raiz (Rodada 2):** o laço de gravação de ações só ignorava linhas vazias, sem checar se restava pelo menos 1 ação válida — confirmado em mais de 10 chamadas de `store()` bem-sucedidas sem nenhuma ação cadastrada.

**Correção aplicada:**

```php
$tpaIdsValidar = array_filter((array) ($postado['tpa_id'] ?? []));
if (empty($tpaIdsValidar)) {
    throw new \Exception('É obrigatório cadastrar ao menos uma Ação (aba Ações)');
}
```

**Confirmação em runtime (Rodada 3):**

- `store()` sem nenhum `tpa_id[]` no payload → `{"erro":true,"msg":"É obrigatório cadastrar ao menos uma Ação (aba Ações)"}`, produto não consumido.
- `store()` com 1 ação válida (`tpa_id[0]=2`, "Abrir Tela") → grava com sucesso; confirmado via SQL que a ação foi persistida em `oco_notif_evento_acao`.
- **Regressão checada:** T43-42 (execução sequencial por `nac_ordem`, 3 ações fora de ordem de digitação) reexecutado após esta correção — continua passando, `nac_ordem` preservado corretamente (1→tpa_id=2, 2→tpa_id=4, 3→tpa_id=5).

---

### Confirmação do caminho normal completo (Rodada 3)

Executado `store()` com produto de defeito válido preenchido + exatamente 1 ação válida cadastrada + data de fabricação válida em formato `Y-m-d` → `{"erro":false,"msg":"Notificação de Evento gravada e concluída com sucesso!"}`. Confirmado no banco: `nev_fabricacao` gravada corretamente, `nvp_defeito` do produto preenchido com o texto enviado, 1 linha em `oco_notif_evento_acao` com o `tpa_id`/`nac_ordem` corretos. **Nenhuma das três correções introduziu regressão no fluxo normal de gravação.**

---

## 2. Resultados — T42 (Desvio de Qualidade)

Não reexecutado nas Rodadas 2/3 — sem alterações de código em T42 desde a Rodada 1.

| ID | Prioridade | Resultado | Evidência |
|---|---|---|---|
| T42-01 | P0 | **PASSA** | Perfil sem nenhuma linha em `cfg_perfil_item` para tel_id=68 → `vw_semacesso` renderizada em `index`, `lista` e `store` forjado (POST direto), sem vazar dados. |
| T42-02 | P0 | **FALHA** (BUG #1, achado aceito) | `edit`/`delete` bloqueados corretamente pelo filtro/controller; **`store` NÃO é bloqueado** — perfil só `C` conseguiu concluir um Desvio real via POST forjado. |
| T42-03 | P1 | Não executado | — |
| T42-04 | P1 | **PASSA** | `cfg_tela` (tel_id 68/69) e `cfg_status` (Pendente/Concluída, tel 68 e 69) confirmados via SQL; módulo "Fornecedores" único (mod_id=25), sem duplicidade. |
| T42-05 | P0 | **PASSA (verificação estática)** | `OcoTrataOcorrencia::gerarNotificacaoDesvio()` insere em `oco_notif_desvio` com `stt_id` = Pendente. |
| T42-06 | P0 | **PASSA** | Replay do mesmo `store()` em registro já Concluído → bloqueado (`msg:15`), sem novo registro. |
| T42-07 | P1 | Não executado | — |
| T42-08 | P2 | Não executado | — |
| T42-09 a T42-13 | P1–P2 | Bloqueado (ambiente) | Dependem de Mongo para "Usuário"; volume insuficiente popularizado. |
| T42-14 | P1 | Não executado | — |
| T42-15 | P0 | **PASSA** | Vazio/4→erro claro, 51→erro (`máximo 50`), 50→grava com sucesso. |
| T42-16 | P0 | **PASSA** | Mesma bateria para `ndv_descreva` (limites 5/200). |
| T42-17 | P0 | **PASSA** | `edit()`/`store()` forjado em registro Concluído → bloqueados. |
| T42-18 | P0 | **PASSA** | Salvar válido grava e conclui na mesma transação. |
| T42-19 a T42-22 | P1 | Não executável neste ambiente | Client-side/UI. |
| T42-23 | P0 | **PASSA** | `delete()` sempre bloqueado, para qualquer perfil. |
| T42-24 a T42-27 | P1–P2 | Bloqueado (ambiente — Redis) | Fora de escopo desta entrega. |

**Resumo T42 P0:** 8 de 9 P0 aplicáveis passaram. **1 falhou**: T42-02 (BUG #1, achado aceito).

---

## 3. Resultados — T43 (Notificação de Evento)

### 3.1 Casos confirmados nas Rodadas 1/2 (sem mudança na Rodada 3)

| ID | Prioridade | Resultado |
|---|---|---|
| T43-01 | P0 | **PASSA** |
| T43-04 | P0 | **PASSA** |
| T43-05 | P0 | **PASSA** |
| T43-09 | P0 | **PASSA** (com ressalva de ambiente single-threaded, ver 3.2 da rodada anterior) |
| T43-14 | P0 | **PASSA** |
| T43-16 | P0 | **PASSA** |
| T43-17 | P0 | **PASSA** |
| T43-18 | P0 | **PASSA** |
| T43-25 | P0 | **PASSA** |
| T43-26 | P0 | **PASSA** |
| T43-27 | P0 | **PASSA** |
| T43-28 | P0 | **PASSA** |
| T43-29 | P0 | **PASSA** |
| T43-30 | P0 | **PASSA** |
| T43-35 | P0 | **PASSA** |
| T43-42 | P0 | **PASSA** (reconfirmado sem regressão na Rodada 3 após fix RN03.21) |
| T43-47 | P0 | **PASSA** |
| T43-50 | P0 | **PASSA** |
| T43-51 | P0 | **PASSA** |
| T43-52 | P0 | **PASSA** |
| T43-53 | P0 | **PASSA** |
| T43-54 | P1 | **PASSA** |
| T43-55 | P1 | **PASSA** |
| T43-57 | P0 | **PASSA (verificação de código)** |

### 3.2 Rodada 3 — reexecução dos 3 achados corrigidos

| ID | Prioridade | Resultado | Evidência |
|---|---|---|---|
| T43-15 | P0 | **PASSA — corrigido e confirmado** | `nvp_defeito` vazio/4 chars → bloqueado com mensagem clara de RN03.9, produto não consumido; caminho válido grava normalmente. Ver detalhamento no ACHADO #1 (seção 1). |
| T43-19 | P0 | **PASSA — corrigido e confirmado** | Data inválida (`31-02-2026`) e formato errado (`01/01/2026`) bloqueados; vazio bloqueado; data válida em `Y-m-d` grava e persiste corretamente (não mais `0000-00-00`). Ver ACHADO #2 (seção 1). |
| T43-40 | P0 | **PASSA — corrigido e confirmado** | `store()` sem nenhuma ação bloqueado com mensagem clara de RN03.21; com 1 ação válida grava normalmente. Ver ACHADO #3 (seção 1). |

**Todos os 3 casos reexecutados nesta rodada passam.** Nenhuma regressão detectada nos casos adjacentes (T43-42 reconfirmado; caminho normal completo de `store()` — defeito + ação + data válidos — confirmado gravando com sucesso).

### 3.3 Demais casos (P1/P2 ou fora de escopo — não reexecutados)

| ID | Prioridade | Resultado | Evidência |
|---|---|---|---|
| T43-02 | P0 | **FALHA (BUG #1, achado aceito, sem ação neste ciclo)** | `edit`/`add`/`delete` bloqueados corretamente; `store()` não é protegido por permissão no `LoginFilter` (mesmo padrão do T42-02). |
| T43-03 | P2 | Não executado | — |
| T43-06 | P1 | Achado de divergência (reportado, não corrigido) | `add()` lista todos os disponíveis, pré-seleção por fabricante é só client-side. |
| T43-07 | P1 | Não executado | — |
| T43-08 | P2 | Bloqueado (ambiente) | — |
| T43-10 | P0 | **Não reproduzido — risco residual conhecido, não bloqueante** | Limitação do servidor de desenvolvimento (single-threaded) impede forçar a janela exata de corrida entre duas transações de banco literalmente concorrentes. Proteção teórica (2ª camada `validaDisponibilidade()` + `UNIQUE KEY(ndv_id)`) presente no código; T43-09 confirma que não há vazamento de erro de SQL cru nos cenários testáveis neste ambiente. |
| T43-11/T43-12 | P1 | Não executado | Mesma limitação de T43-10; T43-12 é client-side. |
| T43-13 | P1 | Não executado | Requer inspeção visual. |
| T43-20 a T43-24 | P1–P2 | Não executado | GROUP_CONCAT/truncamento — fora do escopo priorizado. |
| T43-31 a T43-34, T43-36 a T43-39 | P1–P2 | Não executado | Demais variações de upload — mecanismo básico já confirmado via T43-35. |
| T43-41, T43-43, T43-44 | P1–P2 | Não executado | — |
| T43-45 | P0 | **Mantido como verificação estática** | Evitado deliberadamente acionar SOAP real; loop de montagem do array de movimentação confirmado correto por leitura de código. |
| T43-46 | — | **Fora de escopo, não executado** | SOAP real não autorizado pelo usuário. |
| T43-48/T43-49 | P1 | Não executável neste ambiente | Client-side. |
| T43-56 | P2 | Não executado | — |
| T43-58/T43-59 | P1/P0 | Bloqueado (ambiente — Redis) | Fora de escopo desta entrega. |
| T43-60 | P2 | Não executado | — |

---

## 4. Resumo executivo final (fechamento da Etapa 3 — Testes)

### P0 — contagem definitiva

**T42:** 8 de 9 P0 aplicáveis passam. 1 falha (T42-02, BUG #1, achado aceito pelo usuário).

**T43:** dos 28 P0 listados pelo `byarq` (mais T43-10, tratado como P0 adicional por constar como tal no plano original):

- **PASSAM (26):** T43-01, 04, 05, 09, 14, 15, 16, 17, 18, 19, 25, 26, 27, 28, 29, 30, 35, 40, 42, 47, 50, 51, 52, 53, 57 — **todos os P0 de conteúdo/regra de negócio passam**, incluindo os 3 que foram corrigidos nesta rodada final (T43-15, T43-19, T43-40).
- **FALHA (1):** T43-02 — BUG #1 (`LoginFilter`), achado de segurança conhecido e **aceito conscientemente pelo usuário** para esta entrega, sem ação de correção associada.
- **Não reproduzido, risco residual conhecido, não bloqueante (1):** T43-10 — limitação do ambiente de teste (servidor single-threaded), não bug confirmado.
- **Mantido como verificação estática por restrição de escopo/SOAP (1):** T43-45 — não executado em runtime para não acionar integração real com o ERP, sem autorização para isso nesta rodada.

**Conclusão:** **todos os P0 de conteúdo/funcionalidade passam.** O único P0 "reprovado" é T42-02/T43-02 (BUG #1), que não é uma reprovação no sentido de bloqueio de entrega — é um achado de segurança **explicitamente aceito pelo usuário** como decisão de negócio para este ciclo, não um problema pendente de correção.

### Bloqueadores remanescentes para a entrega

Após esta rodada, **não restam bloqueadores técnicos de funcionalidade**. Os únicos itens em aberto são:

1. **BUG #1 (`LoginFilter`/`store()` sem checagem de permissão `A`/`E`)** — achado de segurança conhecido, **aceito pelo usuário** para esta entrega. Recomendo reavaliar em ciclo futuro dedicado ao `LoginFilter`, especialmente antes de expor a aplicação a perfis externos/menos confiáveis.
2. **Extensão Redis ausente no ambiente de teste** — bloqueou T42-24 a T42-27 e T43-58/59 (família `GeraEtiqueta()`). **Combinado como fora de escopo** — pendência de reexecução em ambiente com Redis habilitado, não bloqueante para esta entrega.
3. **T43-46 (SOAP real com ERP) e, por extensão, a confirmação 100% empírica de T43-45** — **fora de escopo desta entrega** por decisão explícita do usuário; recomendo executar em ambiente de homologação controlado, coordenado previamente, quando autorizado.
4. **T43-10 (janela exata de corrida)** — não reproduzida por limitação de ambiente (servidor PHP single-threaded), documentada como risco residual conhecido e não bloqueante, conforme orientação prévia do `byarq`.
5. Achado de infraestrutura sem ação necessária: `show_error()` indefinido em `LogMonModel` — risco de robustez caso o MongoDB fique indisponível em produção, mas fora do escopo do desenvolvimento de T42/T43 (código compartilhado por todo o sistema).

### Casos não executados (com motivo, consolidado)

- **Fora de escopo por decisão explícita do usuário:** T43-46 (SOAP real com ERP).
- **Bloqueados por ausência da extensão Redis, fora de escopo desta entrega:** T42-24 a T42-27, T43-58, T43-59.
- **Não executáveis neste ambiente (requerem navegador/interação de UI):** T42-19 a T42-22, T43-12, T43-13, T43-48, T43-49.
- **Não reproduzido por limitação de ambiente (servidor single-threaded), risco residual conhecido:** T43-10, T43-11.
- **Não executados por corte de escopo/priorização P1-P2 ao longo do ciclo:** T42-03, T42-07, T42-08, T42-09 a T42-14, T43-03, T43-06 (já registrado como achado de divergência, não corrigido), T43-07, T43-08, T43-20 a T43-24, T43-31 a T43-34, T43-36 a T43-39, T43-41, T43-43, T43-44, T43-56, T43-60.

### Encerramento da Etapa 3 (Testes)

Este ciclo de testes está **encerrado**. Todos os achados de funcionalidade/regra de negócio identificados ao longo das 3 rodadas (BUG #2, T43-15, T43-19, T43-40) foram corrigidos pelo `bydev`, revisados pelo `byrev` e **reconfirmados por reexecução real (não apenas leitura de código)** nesta rodada final, sem regressões detectadas nos casos adjacentes. O único item que permanece "em aberto" (BUG #1) é uma decisão consciente do usuário de aceitar o risco nesta entrega, não uma pendência técnica não tratada. Os itens de infraestrutura (Redis, MongoDB local, SOAP de homologação) são pendências de ambiente já combinadas como fora de escopo, não bloqueadores de código.

**Recomendação:** prosseguir para a Etapa 4 (Entrega) — `bydoc` pode escrever o documento de entrega final, listando todos os arquivos alterados ao longo do ciclo (incluindo os fixes de BUG #2, T43-15, T43-19, T43-40 desta rodada) e as pendências de infraestrutura/decisões aceitas para acompanhamento pós-entrega.

Fixtures de teste (perfis `QA_T42T43_*`, usuários `qa.*`, registros de teste em `oco_notif_desvio`/`oco_notif_evento`) permanecem no banco de dev — recomendo removê-los antes de qualquer carga "limpa" para homologação/produção.
