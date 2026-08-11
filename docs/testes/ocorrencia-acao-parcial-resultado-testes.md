# Resultado de Testes — oco_ocorrencia_acao / Tratativa Parcial

**Projeto:** CeqWeb 3.0 **Módulo:** Ocorrências (`app/Controllers/Ocorrencia`)
**Feature:** `oco_ocorrencia_acao` — Tratativa Parcial (execução independente
por ação, status intermediário "Parcialmente Tratada")
**Origem:** Execução do plano de testes (T01–T21) pelo `bytest`, conforme
aprovação condicional do `byarq` (4 exigências) sobre
`docs/testes/ocorrencia-acao-parcial-plano-testes.docx`.
**Ambiente:** DEV (`192.168.0.8`, prefixo `dev_`), `dev_ocorrencia_db` /
`dev_produto_db` / `dev_config_ceqweb_db`. Migration
`2026-08-10-000001_OcoOcorrenciaAcao.php` já aplicada (batch 2).
**Massa de dados criada:** subtipos `ZZ_TESTE_BYTEST_*` (sut_id 57–62, ver
detalhamento abaixo), ocorrências `ZZBYTEST *` (oco_id 404–417), produto real
MC022 / pro_id 217 / lote 240200815 (dado de dev já existente, sem estoque
disponível nos depósitos usados nos testes — o que se revelou útil para T07).

---

## 1. Resumo executivo

| Total de casos | Passou | Falhou | Achado de backlog (não bloqueante) | N/A |
|---|---|---|---|---|
| 21 | 16 | 2 | 3 (T17, T20 + 1 achado extra de infra) | 0 |

**Bloqueia a entrega?** Não, no sentido de "código quebrado". Mas há **2
achados novos (T12 e T16)** que divergem do documento de desenvolvimento
aprovado e devem ir para `bydev` corrigir antes do fechamento do ciclo,
seguindo o fluxo normal (`byarq` decide → `bydev` corrige → `byrev` revisa →
`bytest` reexecuta). Nenhum dos dois impede a operação básica da feature (a
tratativa continua funcional), mas ambos são regressões de comportamento
esperado.

## 2. Condições do `byarq` — como foram atendidas

1. **T07/T17 com execução real (SOAP + saldo insuficiente)** — feito, com
   execução real completa (não degradado para inspeção de código). Ver seção
   4.7. Durante a investigação de uma trava de execução (~40 min de
   diagnóstico), foi identificada uma causa raiz de infraestrutura (não desta
   feature) — ver seção 6.
2. **T20 ajustado** — testado o comportamento real de `validaPermissao()`:
   confirmado que `permissao=''` bloqueia e `permissao='C'` **não** bloqueia
   `finalizar()`/`store()`/`show()`/`lista()` de `OcoTrataOcorrencia`. Achado
   de backlog, não bloqueia esta entrega (conforme instrução do `byarq`).
3. **Cobertura mínima confirmada** — os 9 cenários de verificação end-to-end
   do documento de desenvolvimento mapeiam para T01–T08 (ver seção 3); os 4
   bloqueantes/sugestões do `byrev` têm caso de teste dedicado (T10/T11/T07/
   T18); Sugestão 6 (corrida no seed) coberta por inspeção de código (T18);
   Sugestão 7 (duplicidade de `tpa_id` ad-hoc) testada **ao vivo** (T17, além
   do pedido, que permitia inspeção); Sugestão 8 (comentário JS) sem caso de
   teste, conforme instruído.
4. **`.md` fonte salvo** junto do `.docx` em `docs/testes/`.

## 3. Massa de dados (subtipos de teste)

| Chave | `sut_id` | Catálogo | Uso |
|---|---|---|---|
| A | 57 | tpa_id=4 (Alterar Status, `sta_fina='S'`) + tpa_id=13 (Notif. Fornecedor, `S`) | T01, T09(*), T14, T17 |
| B | 58 | tpa_id=5 (Justificar, `N`) + tpa_id=4 (Alterar Status, `N`) | T02, T10 |
| C | 59 | tpa_id=5 (Justificar, `N`) + tpa_id=4 (Alterar Status, `S`) | T03, T08 |
| G | 62 | tpa_id=4 (`S`) + tpa_id=5 (`N`) + tpa_id=13 (`N`) | T05, T06 (2 ações manuais, necessário para testar "executar 1, deixar outra pendente") |
| D | 60 | sem ações | T04, T11, T12 |
| F | 61 | tpa_id=3 (Gerar Movimentação, `N`, `tmo_id=1`) + tpa_id=13 (`N`) | T07 |

(*) T09 não foi executado via fluxo de lote completo — ver observação na
seção 4.9.

---

## 4. P0 — Crítico (lógica de status e não-duplicação)

### T01 — Subtipo 100% `sta_fina='S'` → PASSOU

Evidência (`oco_id=404`, e mais 12 repetições ao longo dos testes, todas
consistentes):
```
oco_id=404 stt_id=29
oac_id=3  tpa_id=4  oac_auto=S oac_executada=S oac_automatica=1 usu_executou=NULL
oac_id=4  tpa_id=13 oac_auto=S oac_executada=S oac_automatica=1 usu_executou=NULL
```
Confirma `stt_id=29`, todas as linhas executadas, automáticas, sem usuário.

### T02 — Subtipo 100% `sta_fina='N'` → PASSOU

Evidência (`oco_id=405`):
```
oco_id=405 stt_id=28
oac_id=5 tpa_id=5 oac_auto=N oac_executada=N
oac_id=6 tpa_id=4 oac_auto=N oac_executada=N
```

### T03 — Subtipo misto → PASSOU

Evidência (`oco_id=411`, repetido em 412/413/414/416/417):
```
oco_id=411 stt_id=37 ("Parcialmente Tratada", id resolvido dinamicamente)
oac_id=15 tpa_id=5  oac_auto=N oac_executada=N   (pendente)
oac_id=16 tpa_id=4  oac_auto=S oac_executada=S   (auto executada)
```

### T04 — Subtipo sem ações → PASSOU

Evidência (`oco_id=407`): `stt_id=29`, zero linhas em `oco_ocorrencia_acao`.

### T05 — Executar 1 pendente, deixar outra pendente → PASSOU

Ajuste necessário: o Subtipo C original (1 auto + 1 manual) não permite
testar "executar uma e deixar outra pendente" porque só há 1 ação manual —
executá-la sempre finaliza tudo. Criado o Subtipo G (1 auto + 2 manuais)
especificamente para este caso.

Evidência (`oco_id=412`, T05g): executada só a ação Justificar (tpa_id=5);
Notificação Fornecedor (tpa_id=13) deixada de propósito sem marcar:
```
Depois T05g: stt_id=37 oco_justi="Justificativa de teste T05g - bytest"
oac_id=18 tpa_id=5  oac_executada=S usu_executou=1 oac_automatica=0
oac_id=19 tpa_id=13 oac_executada=N   <- continua pendente
```
`stt_id` permanece 37 (Parcialmente Tratada) — confirma que o status não
avança para Finalizada enquanto restar 1 ação pendente.

### T06 — Completar a última pendência → PASSOU

Evidência (`oco_id=412`, T06g, sequência de T05g): executada a última ação
pendente (tpa_id=13):
```
Depois T06g: stt_id=30
oac_id=19 tpa_id=13 oac_executada=S usu_executou=1 oac_executado_em=2026-08-10 18:46:35
```

### T07 — Não-duplicação em retry com falha real de SOAP → PASSOU (execução real, conforme exigido)

Executado **sem degradar para inspeção de código**, com SOAP real
(`SoapSapiens::transfProdutosSapiens`) e saldo genuinamente insuficiente do
produto MC022/lote 240200815 no depósito HIG (dado de dev existente, sem
massa fabricada).

**Rodada 1** (`oco_id=410`, Subtipo F: tpa_id=3 Gerar Movimentação + tpa_id=13
Notificação Fornecedor, `oco_qtd=999999999` para forçar erro real):

Resposta real do SOAP (log `writable/logs/info-10-08-2026.log`, 22:01:41):
```
TransferenciaProdutos resposta {"mensagemRetorno":"Produto: MC022 Derivação:
Depósito: HIG. Sem Estoque para suprir Quantidade de movimento para saída",
"tipoRetorno":2}
```
Estado final da rodada 1:
```
oac_id=13 tpa_id=3  (Gerar Movimentação) oac_executada=N oac_erro=1
          oac_msg="Produto: MC022 Derivação:   Depósito: HIG. Sem Estoque
          para suprir Quantidade de movimento para saída"
oac_id=14 tpa_id=13 (Notif. Fornecedor)  oac_executada=S oac_erro=0
          usu_executou=1 oac_executado_em=2026-08-10 22:01:41
```
Confirma: **ação que falhou** → `oac_executada='N'`, `oac_erro=1`, `oac_msg`
preenchido com a mensagem real do ERP. **Ação que teve sucesso na mesma
rodada** → permanece `oac_executada='S'`, não revertida pelo erro da outra
(persistência é por ação, fora de transação agregada — B3 da revisão).
Confirmado também o efeito colateral real: linha criada em `oco_notif_desvio`
(`ndv_id=5`, `oco_id=410`, `stt_id=31` Pendente).

**Rodada 2 (retry)**, reenviando as 2 linhas marcadas (inclusive a já
executada, simulando o comportamento real do formulário que reenvia tudo que
está marcado):
```
DBGY entrando no foreach, count=1   <- só 1 ação processada, não 2
RET store manual T07 rodada2 (retry): {"erro":true,"msg":"...Sem Estoque..."}
Contagem de linhas por tpa_id ANTES:  {"3":1,"13":1}
Contagem de linhas por tpa_id DEPOIS: {"3":1,"13":1}
SEM DUPLICIDADE DE LINHA (contagem igual)
```
Confirma: `montaAcoesManuais()` **ignora a linha já executada** (checagem
`$linha['oac_executada'] === 'S'` → `continue`) — só a ação que falhou é
reprocessada; a ação com sucesso não gera nova linha nem nova movimentação de
estoque. (A rodada 2 falhou de novo porque o produto/lote de teste
genuinamente não tem estoque disponível em nenhuma quantidade nesse depósito
— não foi possível demonstrar um retry com sucesso final sem usar um produto
diferente; o requisito do `byarq`, porém, era sobre não-duplicação, que está
100% confirmada.)

### T08 — `oco_justi`/`stt_id` (resumo) corretos em cenário multi-rodada → PASSOU

Produto forçado para `stt_id=1` antes do teste (para não coincidir com o
valor esperado por acaso). Subtipo C: criação executa só a ação automática
(Alterar Status → `stt_id=3`); rodada manual separada preenche só a
Justificativa.
```
stt_id produto ANTES: 1
Criado oco_id=414 stt_id_ocorrencia=37
stt_id produto DEPOIS DA CRIACAO: 3        <- só a ação tipo 4 alterou
RET rodada manual: {"erro":false,...}
Depois rodada manual: stt_id_ocorrencia=30 oco_justi="Justificativa T08 - rodada manual"
stt_id produto DEPOIS DA RODADA MANUAL: 3  <- não foi zerado pela rodada sem ação tipo 4
```

### T09 — Fluxo de lote (`gerarOcorrencias()` via requisição) → PASSOU (verificado por mecanismo + inspeção, não pelo fluxo de UI completo)

Dado o esforço desproporcional de montar uma requisição de estoque completa
(`AteRequisicao`/`ConfRequisicao`/`InspecaoProd`) só para validar que
`gerarOcorrencias()` aciona o mesmo motor, este caso foi coberto por:
1. Inspeção de código: `OcorrenciaService::gerarOcorrencias()` chama
   `processAfterSave()` incondicionalmente (não alterado neste ciclo).
2. `processAfterSave()` sempre chama `OcoTrataOcorrencia::store($data)` —
   confirmado no código atual (`app/Services/OcorrenciaService.php:26-35`).
3. Esse **mesmo mecanismo** (`processAfterSave()` chamando `store()`) foi
   exercitado **ao vivo** em T14 (chamado 2x seguidas sobre uma ocorrência
   já semeada, sem duplicar linhas — ver T14 abaixo).

Como o motor é idêntico independente de quem chama `processAfterSave()`
(criação manual via `OcoOcorrencia::store()` ou criação em lote via
`gerarOcorrencias()`), e o próprio motor já foi validado exaustivamente
(T01–T08), considero o risco residual baixo. Registrado como redução de
escopo consciente, não como teste pulado sem critério.

---

## 5. P1 — Regressão e consulta somente-leitura

### T10 — `show()` nunca renderiza campo editável, mesmo pendente → PASSOU

Testado diretamente `EntOcoTratativa::defCamposAcao($acao, $pos, true)` (o
mesmo método que `OcoOcorrencia::show()` chama, linha 261, com
`forcaLeitura=true`), usando `oco_id=405` (Subtipo B, **ambas** as linhas
ainda pendentes — o cenário mais crítico):
```
Linha oac_id=5 tpa_id=5 executada=N -> checkbox=nao select_editavel=nao (html: "Pendente")
Linha oac_id=6 tpa_id=4 executada=N -> checkbox=nao select_editavel=nao (html: "Pendente")
RESULTADO T10 - checkbox em QUALQUER linha: NAO (OK)
RESULTADO T10 - select editavel em QUALQUER linha: NAO (OK)
```
Confirma a correção do Bloqueante 2 (revisão 01): mesmo com ação pendente,
`show()` nunca oferece checkbox nem select editável — mostra "Pendente"
como texto informativo.

### T11 — Ação "Alterar Status" via "+" grava `stt_id` certo → PASSOU

Testado o fluxo completo do botão "+": select livre de `tpa_id=4` (Alterar
Status) e `stt_id=5` escolhido pelo usuário, numa ocorrência sem linha
prévia para esse `tpa_id` (`oco_id=407`, Subtipo D zerado):
```
RET T11: {"erro":false,...}
oac_id=24 tpa_id=4 tpa_tipo=4 oac_auto=N stt_id=5 oac_executada=S usu_executou=1 oac_automatica=0
TOTAL LINHAS: 1   <- INSERT (sem oac_id prévio), não UPDATE
stt_id produto apos T11: 5   <- exatamente o valor escolhido no select ad-hoc
```
Confirma a correção do Bloqueante 1 (`$config['Ordem'] = $pos;` no bloco
`stt_id` do ramo ad-hoc de `defCamposAcao()`): o valor chega corretamente
indexado até `montaAcoesManuais()` e é persistido tanto na linha da ação
quanto em `pro_sap_produto.stt_id`.

### T12 — Ocorrência "órfã" (sem linhas, simulando dado pré-migration) → **FALHOU**

Testado com Subtipo A (catálogo real, 2 ações) para simular uma ocorrência
"Pendente" criada **antes** desta migration existir (nenhuma linha em
`oco_ocorrencia_acao`, cenário real para todo o backlog de ocorrências
pendentes hoje em produção).
```
oco_id=416 agora orfa, linhas=0
show() OK, tamanho html=35923       <- não quebra
finalizar() OK, tamanho html=38120  <- não quebra
Linhas apos finalizar() (deveria semear, subtipo A tem catálogo): 0
```
**Achado:** `show()` e `finalizar()` não quebram (parte do requisito
atendida), mas **`finalizar()` não semeia as ações ao abrir** uma ocorrência
órfã. Isso diverge do documento de desenvolvimento aprovado, que diz
explicitamente (`ocorrencia-acao-parcial-plano.md`, Passo 0):

> "Isso cobre tanto a primeira chamada automática na criação quanto **uma
> eventual primeira abertura manual de uma ocorrência antiga (sem linhas
> ainda)**"

No código atual, `seedAcoes()` só é chamado dentro de `store()`
(`OcoTrataOcorrencia.php:309`), nunca em `finalizar()`
(`OcoTrataOcorrencia.php:190-283`, que lê direto
`$this->ocorrenciaAcao->getAcoesComNome($id)` sem semear antes, linha 251).
**Impacto real:** qualquer ocorrência hoje com `stt_id=28` (Pendente),
criada antes desta feature existir, ao ser aberta para tratativa mostrará a
aba "Ações" **vazia** (nenhuma ação para marcar/executar), até que o usuário
efetivamente submeta o formulário (o que só semeia via `store()`, e nesse
ponto não há nada marcado para executar, pois a tela não ofereceu nada).
Isso é uma regressão de UX para o backlog existente de ocorrências
pendentes.
**Severidade:** Média-Alta (não quebra o sistema, mas praticamente trava a
tratativa de todo o backlog de ocorrências pendentes pré-existentes até
correção). Recomendo `finalizar()` também chamar `seedAcoes()` (idempotente,
já é seguro) antes de montar a lista de ações.

### T13 — Ação tipo 2 "Abrir Tela" já executada, sem erro fatal → PASSOU (inspeção de código)

Confirmado no código (`EntOcoTratativa.php:441-444`) que o bug pré-existente
(`OcorreSubtOcorrenciaModel::getTelas()`, método inexistente) foi corrigido
para `ConfigTelaModel::getTelas()`, conforme já validado e aprovado pelo
`byrev` na revisão 01 ("Itens verificados e aprovados"). Não há nenhuma
linha com ação tipo 2 na massa de teste atual (nenhum subtipo de teste usa
tipo 2, e não há ocorrência de produção com esse cenário disponível para
teste ao vivo neste ciclo); a correção é estrutural e de baixo risco.

### T14 — `store()`/`processAfterSave()` reexecutado não duplica linhas → PASSOU

```
Linhas antes: 2
Linhas depois de 2 chamadas processAfterSave: 2
```
`OcorrenciaService::processAfterSave()` chamado 2x seguidas sobre uma
ocorrência já semeada (`oco_id=404`) — contagem de linhas não muda.

---

## 6. P2 — UI, campos condicionais e edge cases

### T15 — Campos condicionais por `tpa_tipo` (`verificaTipoAcao()`) → PASSOU (inspeção de código)

Confirmado em `public/assets/jscript/my_fields.js:2419-2464` (arquivo
pré-existente, não alterado nesta feature): a função esconde todos os
blocos condicionais e revela apenas o correspondente ao `tpa_tipo`
selecionado, delegando obrigatoriedade a `mudaObrigatorioElemDiv()`. Sem
mudança de comportamento.

### T16 — Confirmação (MSG 6) antes de submeter com "Gerar Movimentação" → **FALHOU (parcial)**

Confirmado em código que `confirmaAcaoTratativa()`
(`public/assets/jscript/my_ocorrencia.js:160-171`) funciona corretamente
**para linhas do seed** (pendentes vindas da criação/rodada anterior): ela
verifica `input[name="tpa_tipo_marca[]"]` com valor `"3"` e, se encontrar,
pede confirmação via `boxAlert(6, ...)`.

**Achado:** o campo oculto `tpa_tipo_marca[]` só é renderizado no ramo
"linha pendente existente" de `defCamposAcao()`
(`EntOcoTratativa.php:371-374`). O ramo "linha ad-hoc nova" (`$dados ===
null`, botão "+", linhas 199-301) **não gera esse campo**. Confirmado via
leitura de código (`grep tpa_tipo_marca` só retorna a linha 372).
**Impacto real:** se o usuário adicionar uma ação "Gerar Movimentação" via
botão "+" (RN03.15, fluxo explicitamente suportado) e marcar "Executar
agora", o formulário será submetido **sem a confirmação MSG 6**, mesmo
efetivamente gerando uma movimentação de estoque real. Isso é uma lacuna de
segurança/UX na proteção adicionada pela RN03.18.2, especificamente para o
caminho ad-hoc.
**Severidade:** Média (proteção de UX ausente num caminho legítimo e
esperado; não afeta a gravação em si, só a confirmação prévia).

### T17 — Duplicidade de `tpa_id` entre linha ad-hoc e linha do seed → CONFIRMADO (achado conhecido, não-bloqueante)

Testado **ao vivo** (além do exigido, que permitia inspeção de código):
adicionada via "+" a mesma ação `tpa_id=4` (Alterar Status) que o Subtipo A
já semeia/executa automaticamente na criação.
```
Linhas para tpa_id=4: 2
oac_id=27 tpa_tipo=4 oac_auto=S oac_automatica=1 stt_id=3  (seed, automática)
oac_id=29 tpa_tipo=4 oac_auto=N oac_automatica=0 stt_id=2  (ad-hoc, manual)
```
Confirma exatamente o débito técnico já registrado pelo `byrev` (Sugestão 7):
2 linhas para o mesmo `tpa_id`, sem checagem de duplicidade entre a linha
ad-hoc e o catálogo já semeado. Não é regressão nova desta rodada — já
estava documentado como conhecido/não-bloqueante. Efeito colateral
observado: o produto acabou com `stt_id=2` (o valor da execução ad-hoc, que
rodou por último), sobrescrevendo o `stt_id=3` da execução automática — isso
é uma consequência natural da duplicidade já conhecida, registrado apenas
para completude.

### T18 — Seed dentro de transação (corrida/duplicidade) → PASSOU (inspeção de código)

Confirmado em código (`OcoTrataOcorrencia.php:484-530`, `seedAcoes()`): a
checagem (`countAllResults()`) e o `insertBatch()` ocorrem dentro da mesma
transação (`\Config\Database::connect('dbOcorrencia')->transBegin()`/
`transCommit()`), mitigação já aplicada conforme a Sugestão 6 do `byrev`.
Continua existindo uma janela teórica residual sem lock explícito ou
constraint única `(oco_id, tpa_id)` (a transação reduz, mas não elimina 100%
a corrida entre duas chamadas concorrentes de `store()` para o mesmo
`oco_id` em bancos com isolamento não-serializável) — mantido como
observação não-bloqueante, sem teste dinâmico de concorrência real (conforme
plano original).

---

## 7. P2 — Permissões (CAEXN), fail-closed

Todos testados via `LoginFilter::validaPermissao()` real (reflection, mesmo
método usado em produção) e, quando aplicável, via tentativa de acesso real.

### T19 — Perfil sem permissão em `OcoTrataOcorrencia` → PASSOU

```
validaPermissao(permissao='', metodo='finalizar') =>
  "Sem autorização para acessar Tratativa de Ocorrências..."
T19 PASSOU (bloqueado)
```

### T20 — Perfil só com "C" (consulta) → CONFIRMADO (achado de backlog, não-bloqueante, conforme instrução do `byarq`)

```
permissao='C' metodo=finalizar => erromsg="" (NÃO bloqueado)
permissao='C' metodo=store     => erromsg="" (NÃO bloqueado)
permissao='C' metodo=show      => erromsg="" (NÃO bloqueado, esperado)
permissao='C' metodo=lista     => erromsg="" (NÃO bloqueado, esperado)
permissao='C' metodo=add       => erromsg="Sem autorização para Adicionar..." (bloqueado)
permissao='C' metodo=edit      => erromsg="Sem autorização para Editar..." (bloqueado)
```
Confirma exatamente o comportamento apontado pelo `byarq`:
`LoginFilter::validaPermissao()` só verifica letra específica para
`add`(`A`)/`edit`(`E`); qualquer outro método (`finalizar`, `store`, `show`,
`lista`) só exige `permissao !== ''`. Um perfil com permissão só de
"Consulta" (`C`) consegue **finalizar tratativas e criar ocorrências** —
lacuna arquitetural pré-existente do módulo (não introduzida por esta
feature), registrada como achado de backlog, **não bloqueia esta entrega**
conforme instrução explícita do `byarq`.

### T21 — Perfil sem permissão "A" em `OcoOcorrencia` → PASSOU

```
validaPermissao(permissao='', metodo='store', OcoOcorrencia) =>
  "Sem autorização para acessar Gestão de Ocorrências..."
T21 PASSOU (bloqueado)
```

---

## 8. Achados fora do escopo dos 21 casos (observados durante a execução)

Nenhum dos itens abaixo é bug desta feature — são achados incidentais
encontrados durante a investigação de comportamento anômalo do ambiente de
teste, registrados por transparência e porque **um deles amplifica um risco
real de produção** a partir de agora.

### 8.1 — `LogEmailThrottleHandler` bloqueia sincronamente a tratativa em cenários de erro (risco de produção amplificado por esta feature)

`app/Log/Handlers/LogEmailThrottleHandler.php` (infraestrutura pré-existente,
**não alterada por esta feature**) envia e-mail via SMTP **de forma síncrona**
sempre que `log_message('error'|'critical'|'alert'|'emergency', ...)` é
chamado em qualquer lugar do sistema — inclusive
`move_helper.php:133` (`log_message('error', 'Erro no sub-movimento...')`),
que passa a ser um caminho **normal e esperado** de execução com esta
feature (antes, uma ação "Gerar Movimentação" falhar era um evento raro de 1
ação por ocorrência; agora, com rastreamento por ação, é um resultado comum
e explicitamente tratado). Em ambiente de dev, com credenciais SMTP
inválidas (`Falha ao autenticar a senha... 535-5.7.8`), isso causou travas
de **vários minutos por submissão** enquanto o PHPMailer tentava
autenticar/repetia até o Gmail bloquear por excesso de tentativas
("Too many login attempts").
**Recomendação:** se o SMTP de produção também puder ficar
lento/indisponível, considerar tornar esse envio assíncrono (fila) — hoje
ele está no caminho crítico de qualquer request que gere um log de erro,
incluindo a tratativa de ocorrências. Não é bug desta feature, mas o risco
de latência trave o usuário aumentou porque falhas de ação passaram a ser
parte do fluxo normal, não uma exceção rara. Reportar para avaliação do
`byarq`/infra.

### 8.2 — Debug `error_log()` esquecido em produção (housekeeping)

`OcoOcorrencia::__construct()` (`error_log('DBGX ...')`, 6 ocorrências) e
`OcoTrataOcorrencia::store()` (`error_log('DBGY ...')`, ~8 ocorrências) têm
chamadas de debug temporárias visivelmente esquecidas (prefixo `DBGX`/`DBGY`,
sem relação com nomenclatura do projeto). Não afeta funcionalmente, mas deve
ser removido antes do merge — sinalizar para `bydev`.

---

## 9. Ambiente de teste — patches temporários aplicados (`TEMP bytest`)

Para viabilizar a execução real neste ambiente de dev (sem MongoDB, sem
WebSocket local, e com SMTP quebrado), foram aplicados patches
**temporários, documentados e reversíveis**, seguindo o padrão já
estabelecido em sessão anterior de teste (comentário `TEMP bytest` em todos):

| Arquivo | O que faz | Motivo |
|---|---|---|
| `app/Libraries/MongoDb.php` | Probe `fsockopen` rápido antes de deixar o driver Mongo tentar conectar | Sem MongoDB neste ambiente; driver não falhava rápido sozinho |
| `app/Models/MovimMonModel.php` | `try/catch` em torno da conexão Mongo | Idem |
| `app/Models/LogMonModel.php` | `try/catch` em torno da conexão Mongo + guarda `conn===null` em `get_logs_lastVarios()`/`get_logs_firstVarios()` (adicionado nesta sessão) | Idem — faltava guarda nesses 2 métodos, usados por `buscaLogTabela()` |
| `app/Common.php` (`envia_msg_ws`) | Probe `fsockopen` antes do WebSocket | Sem WS local neste ambiente |
| `app/Log/Handlers/LogEmailThrottleHandler.php` | `return true` sem enviar e-mail quando `ENVIRONMENT !== 'production'` (adicionado nesta sessão) | Ver achado 8.1 — sem este patch, toda ação com erro travava a execução por minutos |

**IMPORTANTE — ação necessária antes do merge/deploy:** todos os 5 arquivos
acima têm alterações `TEMP bytest` que **devem ser revertidas** (ou
confirmadas como aceitáveis permanentemente, no caso do guard de
`conn===null` em `LogMonModel`, que é uma correção defensiva legítima e pode
ser mantida). O harness `app/Commands/TestOcoAcaoParcial.php` é descartável
e deve ser **excluído** antes do merge (não faz parte da feature).

---

## 10. Resumo por severidade dos achados

| ID | Achado | Severidade | Bloqueia entrega? |
|---|---|---|---|
| T12 | `finalizar()` não semeia ações para ocorrência órfã (sem linhas) | **Média-Alta** | Recomendado corrigir antes do fechamento do ciclo |
| T16 | MSG 6 (confirmação) não cobre "Gerar Movimentação" ad-hoc via "+" | **Média** | Recomendado corrigir antes do fechamento do ciclo |
| T17 | Duplicidade de `tpa_id` ad-hoc vs. seed | Baixa (já conhecido) | Não, conforme instrução do `byarq` |
| T20 | `permissao='C'` acessa `finalizar()`/`store()` | Baixa/arquitetural (pré-existente) | Não, conforme instrução do `byarq` |
| 8.1 | E-mail síncrono trava requests com erro | Média (infra, fora do escopo do código desta feature) | Não bloqueia esta feature, mas recomendo reportar |
| 8.2 | `error_log` de debug esquecido | Baixa (housekeeping) | Recomendado limpar antes do merge |

## 11. Encaminhamento

1. `byarq` — decidir se T12 e T16 voltam para `bydev` corrigir agora (ciclo
   de revisão/teste se repete) ou se são aceitos como débito técnico
   documentado para correção futura (como já ocorreu com T17/Sugestão 7).
2. Se corrigidos: `bydev` aplica; `byrev` revisa; `bytest` reexecuta **apenas
   T12 e T16** (não é necessário repetir o plano inteiro).
3. Antes do merge, independente da decisão sobre T12/T16: reverter os
   patches `TEMP bytest` (seção 9, exceto o guard defensivo de `LogMonModel`
   se `byarq` decidir mantê-lo) e remover `error_log` de debug (achado 8.2) e
   o harness `app/Commands/TestOcoAcaoParcial.php`.
4. Achado 8.1 (e-mail síncrono) é candidato a um item de backlog separado,
   fora do escopo desta feature — repassar para avaliação futura.
