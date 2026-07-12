# Resultado de Testes — Módulo Ocorrências (T9, T11, T12)

**Projeto:** CeqWeb 3.0
**Módulo:** Ocorrências (`app/Controllers/Ocorrencia`)
**Telas:** T9 — Subtipos de Ocorrências (tel_id=49) · T11 — Gestão de Ocorrências (tel_id=56) · T12 — Tratativa de Ocorrências (tel_id=61)
**Plano de testes executado:** `docs/testes/ocorrencias-t9-t11-t12-plano-testes.md` (aprovado pelo `byarq`)
**Executor:** `bytest`
**Status do ciclo de código:** `byrev` — aprovado após 4 rodadas (guard `$tpaIdsUsados` origem×extra e extra×extra incluído)
**Migration `sta_fina`:** aplicada em `dev_ocorrencia_db` (confirmado pelo usuário) — casos **[MIGR]** liberados

---

## 0. Condições reais de execução (leia antes dos resultados)

### 0.1 Limitações do ambiente de execução

Este ambiente **não possui**: PHP CLI (`php`/`composer` retornam `command not found` — o binário `composer` do Windows depende de PHP CLI, indisponível no PATH), cliente `mysql`/acesso direto ao banco `dev_ocorrencia_db`, nem servidor HTTP do CeqWeb em execução acessível (`curl` a `localhost`/`127.0.0.1` retornou sem resposta). Não há navegador disponível para interações de UI (cliques, `boxAlert`, DataTable, selects dinâmicos).

Diante disso, cada caso foi tratado por uma das três vias abaixo — identificada explicitamente no resultado de cada caso:

- **Verificação estática de código** — leitura e rastreamento de código-fonte (controllers, entities, models, JS), incluindo `grep` estrutural, para confirmar que a implementação corresponde ao comportamento especificado. **Não é execução em runtime** — não substitui teste funcional real (requisição HTTP de fato, gravação em banco, renderização de tela). Alta confiança para lógica determinística e linear, confiança reduzida para efeitos colaterais que dependem de dado de banco (ex.: JOIN retornando linhas específicas) ou de timing/concorrência.
- **Não executável neste ambiente — requer teste manual/navegador** — casos que dependem de clique, renderização visual, `boxAlert`, DataTable, `selectpicker`, comportamento de JS no DOM real.
- **Bloqueado — pré-condição pendente** — não se aplicou neste ciclo (migration já está aplicada, então nenhum caso ficou bloqueado por esse motivo).

### 0.2 Verificação de estabilidade do working tree (pré-requisito pedido pelo coordenador)

Antes de iniciar a execução, foram conferidos os marcadores de código combinados com o coordenador, via `grep`, para garantir que o working tree está no estado aprovado (o ciclo anterior teve reversões de arquivo causadas por um bug de configuração do editor do usuário — `sftp downloadOnOpen` — já corrigido):

| Marcador | Resultado |
|---|---|
| `getStatusInicial` | Presente em `OcorreSubtOcorrenciaModel.php:186` (implementação) e usado em `OcoOcorrencia.php:535` (`storetmp()`) e `:704` (`store()`) |
| `addCampoAcaoExtra` | Presente em `OcoTrataOcorrencia.php:148`, referenciado em `my_ocorrencia.js:104` |
| `resolveStatusFinal` | Presente em `OcoTrataOcorrencia.php:429`, chamado em `store()` linha 395 |
| `tpaIdsUsados` | Presente em `OcoTrataOcorrencia.php:321-349` — guard de deduplicação origem×extra e extra×extra implementado |
| `sta_fina` | Presente em `OcoSubtOcorrencia.php`, `EntOcoSubtOcorrencia.php`, `OcorreSubtOcorrenciaModel.php` |
| `permiteAcaoExtra` / `rep_acoesextra` | Presente em `OcoTrataOcorrencia.php:242`, `pw_acoes_ocorrencia.php:50-57`, `my_ocorrencia.js:114-116` |
| `getMensagem(` (chamada real, não comentário) nos 3 controllers | **Ausente** — únicas ocorrências são comentários explicativos em `OcoSubtOcorrencia.php:346-348` e `OcoOcorrencia.php:349` |
| Arquivos residuais (Seção 3 do dev.md) | Ausentes de `app/Controllers/Ocorrencia/` e `app/Models/Ocorre/` (`ls` + filtro por `BKP`/`nov.php`/`teste.php`/`DESK-DOUGLAS` retornou vazio) |

**Conclusão:** working tree confirmado no estado aprovado no momento do início da execução (verificado via `git diff HEAD` — apenas `OcoSubtOcorrencia.php` tinha alteração não commitada, e o diff foi inspecionado linha a linha, confirmando que é a versão correta do Bloqueante 1/RN06.2, não uma reversão). Execução prosseguiu com confiança na leitura do código.

**Alerta permanente para o `byarq`:** como o bug do editor (`sftp downloadOnOpen`) já causou reversões silenciosas de arquivo durante os ciclos anteriores (percebidas apenas porque o `bytest` comparou o conteúdo lido em momentos diferentes da mesma sessão), recomendo que, antes de qualquer deploy, seja feita uma conferência final via `git status`/`git diff` comparando o working tree com o commit que será de fato enviado a produção — não assumir que "o editor está corrigido" é suficiente sem essa checagem final.

---

## 1. Resultados — T9 (Subtipos de Ocorrências)

| ID | Via | Resultado | Evidência / Observação |
|---|---|---|---|
| CT-T9-01 | Estática | **PASSA** | `EntOcoSubtOcorrencia::defCampos()` não define/retorna `sut_fina` — grep por `sut_fina` no arquivo mostra apenas o atributo interno (linha 16, `'sut_fina' => null` no array de dados) e o comentário da RN03.6 (linha 99-101), nenhuma referência dentro de `defCampos()`. Confirma ausência do campo na aba Dados Gerais. |
| CT-T9-02 | Estática | **PASSA** (lógica) / manual pendente para o visual do toggle | `EntOcoSubtOcorrencia::defCamposAcao()` linha ~286-293 gera `sta_fina_tpa[$pos]` como `crCheckbox()` com `$pos` indexando a linha — cada linha tem `nome`/`id` próprios (`sta_fina_tpa[$pos]`), garantindo independência entre linhas por construção (não há estado compartilhado). Confirmação visual de que o toggle realmente reflete/alterna sem afetar outras linhas no DOM real fica pendente de teste manual/navegador. |
| CT-T9-03 | Estática | **PASSA** | `OcoSubtOcorrencia::store()` linhas 451-487: monta `$acoes[]` lendo `sta_fina_tpa[$i]` por linha (default `'N'` se ausente), grava em `oco_subt_ocorrencia_acao`, depois calcula `$sutFina = 'S'` e só rebaixa para `'N'` se alguma ação tiver `sta_fina !== 'S'` (linha 477-483). Com as 2 ações `'S'`, o loop não entra no `if`, `$sutFina` permanece `'S'` — grava `sut_fina='S'`. Lógica correta por rastreamento de código; a gravação real em `dev_ocorrencia_db` não pôde ser confirmada por ausência de acesso a banco/PHP CLI neste ambiente. |
| CT-T9-04 | Estática | **PASSA** | Mesmo trecho: com uma ação `'N'`, o `foreach` entra no `if` na primeira ação `'N'` encontrada e faz `break`, resultando em `$sutFina='N'`. Rastreamento de código confirma a regra; gravação real não confirmada por falta de acesso a banco. |
| CT-T9-05 | Estática | **PASSA** | Com `$acoes = []` (nenhuma ação), o `foreach` não executa, `$sutFina` permanece no valor inicial `'S'` (linha 477) — confirma decisão 5 do dev.md (subtipo sem ação = Finalização Automática). |
| CT-T9-06 | Parcial: Estática (backend) PASSA / manual pendente (front) | **PASSA** (backend) / **NÃO EXECUTÁVEL NESTE AMBIENTE** (front) | Backend: `OcoSubtOcorrencia::ativinativ()` linhas 343-360 — branch de inativação chama `getPendenciasGestao((int) $id)`; se não vazio, retorna `{erro:true, msg:14, pendencias:[...]}` e sai (`return` antes de gravar `sut_ativo`) — registro não é gravado como inativo, confirmando "permanece ativo". `msg` é inteiro literal `14`, sem `getMensagem()` no controller (consistente com CT-T9-14). Front (`my_ocorrencia.js::ativInativ()`, linhas 57-88) trata `retornoAjax.pendencias` corretamente no código-fonte (monta `<ul>`, chama `getMensagem(14)`, concatena e chama `boxAlert()`) — mas a renderização visual real (modal, lista formatada, texto de MSG 14 vindo de `cfg_mensagem`) exige navegador/clique real, não simulável aqui. |
| CT-T9-07 | Estática (backend) | **PASSA** (backend) / **NÃO EXECUTÁVEL** (confirmação visual do `boxAlert` de sucesso) | Sem pendências, `getPendenciasGestao()` retorna array vazio, cai no `else` (linha 361-363) e grava `sut_ativo='I'` normalmente. |
| CT-T9-08 | Estática | **PASSA** | `catch (\Exception $e)` em `ativinativ()` (linhas 372-377) sempre atribui `msg=17` **sem** `pendencias` — não há caminho de código que misture os dois. Distinção entre "bloqueio por pendência" (branch específico, `return` antecipado) e "erro genérico" (`catch`) está estruturalmente separada — não há como um erro genérico cair no ramo de pendências. |
| CT-T9-09 | Estática | **PASSA** (documental) | `grep -rn "getUsoGestao" app/` → nenhuma ocorrência em `app/Controllers/Ocorrencia`, `app/Models/Ocorre` (comando executado nesta sessão, ver Seção 3). Método efetivamente removido, conforme decisão do `byarq` (grep prévio confirmou ausência de outro chamador). |
| CT-T9-10 | Não executável | **NÃO EXECUTÁVEL NESTE AMBIENTE** | Depende do handler `#bt_salvar`/`data-valid` de `my_default.js` rodando no navegador contra um DOM real. Verificação estática: nenhuma alteração foi feita em `my_default.js` nesta feature (confirmado via `git status`/leitura), e T9 não sobrescreve esse handler — risco de regressão nesse ponto é baixo, mas o comportamento em si (clique, `boxAlert` MSG 7) requer teste manual. |
| CT-T9-11 | Estática (parcial) | **PASSA** (lógica de persistência) / **NÃO EXECUTÁVEL** (fluxo completo salvar→reabrir) | `OcoSubtOcorrencia::store()` grava `oco_subt_ocorrencia_permissao` via `insertBatch` (linhas 558-567); `edit()` recarrega via `getPermissoesSubtipo($id)` e popula `dados_SubtOcorrencia['prf_id']` (linhas 288-291), repassado a `defPermissoes()`. Fluxo de código íntegro e simétrico (grava e relê pela mesma tabela/chave). Execução real do ciclo completo (salvar → reabrir na tela) não realizada por falta de banco/navegador. |
| CT-T9-12 | Estática (mecanismo genérico) | **PASSA** (mecanismo) / **NÃO EXECUTÁVEL** (cenário real com perfil sem permissão) | `app/Filters/LoginFilter.php` implementa o mecanismo fail-closed genérico (ausência de permissão bloqueia tudo, renderiza `vw_semacesso`), conforme `rascunho-helpers-php.md`. T9 não implementa nenhum bypass próprio de permissão — os métodos do controller seguem a nomenclatura padrão (`index/lista/add/edit/delete/ativinativ`), então o filtro genérico se aplica normalmente. Teste real (logar com perfil sem permissão e tentar acessar) requer sessão/navegador. |
| **CT-T9-13** | Parcial (estática + comando) | **PASSA (parcial)** — ver detalhe | Ver Seção 4 (detalhamento específico abaixo) |
| **CT-T9-14** | Estática (grep) | **PASSA** | Ver Seção 4 (detalhamento específico abaixo) |

**Resumo T9:** 14/14 casos com verificação estática favorável (nenhuma divergência de lógica de backend encontrada); 6 casos (CT-T9-02 parcial, 06 parcial, 07, 10, 11 parcial, 12) têm componente de UI/fluxo completo pendente de teste manual em navegador.

---

## 2. Resultados — T11 (Gestão de Ocorrências)

| ID | Via | Resultado | Evidência / Observação |
|---|---|---|---|
| CT-T11-01 | Estática | **PASSA** | `OcorreSubtOcorrenciaModel::getStatusInicial()` (linhas 186-207): `$acoes = []` (sem ação) → `return 29` (linha 196-198) antes de qualquer outro processamento. Usado por `storetmp()` (linha 535) e `store()` (linha 704). |
| CT-T11-02 | Estática | **PASSA** | Mesmo método: `foreach` percorre todas as ações; se nenhuma tiver `sta_fina !== 'S'`, o `foreach` termina sem `return 28`, cai no `return 29` final (linha 206). |
| CT-T11-03 | Estática | **PASSA** | Com qualquer ação `sta_fina='N'` (inclusive uma configurada com `tpa_id=12`), o `foreach` retorna `28` na primeira ocorrência de `'N'` (linha 200-203) — **não há mais nenhuma referência a `tpa_id==12` no código atual** (`grep -n "tpa_id.*12\|== 12\|=== 12" app/Controllers/Ocorrencia/OcoOcorrencia.php` não retornou nenhuma ocorrência dentro de `store()`/`storetmp()`), confirmando que o caso especial antigo foi de fato eliminado e substituído pela regra genérica de `getStatusInicial()`. |
| CT-T11-04 | Estática | **PASSA (com nota já registrada no plano)** | `OcoOcorrencia::edit()` linhas 345-352: `if (!empty($dados->req_id) \|\| !empty($dados->rep_id)) { throw new \Exception('Alteração não Permitida'); }` — bloqueia **antes** de montar `$this->data['campos']`/renderizar `vw_edicao`, ou seja, o formulário não é carregado. Confirma-se a nota já registrada no plano (CT-T11-04 e Observações §5): o bloqueio usa `throw` não capturado (sem `try/catch` em `edit()`), resultando na página de erro padrão do CI4, não um JSON — comportamento aceito como não-bloqueante pela revisão 01. Execução real (ver a página de erro de fato) requer navegador/servidor ativo. Sem `getMensagem()` chamado (texto agora é string literal `'Alteração não Permitida'` embutida na exceção, não é `msg=15` porque `edit()` não retorna JSON — isso é consistente com o padrão aceito, mas vale registrar como observação: o texto da exceção é diferente do texto oficial de MSG_15 em `cfg_mensagem`, já que quem exibe aqui é a página de erro genérica do CI4, não o `boxAlert()`; ver "Risco não formal" na Seção 5). |
| CT-T11-05 | Estática | **PASSA** | `OcoOcorrencia::store()` linhas 688-700: no branch de update (`oco_id` já existe), busca `$existente = $this->ocorrencia->getOcorrencia($postado['oco_id'])` e, se `req_id`/`rep_id` preenchidos, `return` imediato com `{erro:true, msg:15}` — **antes** de qualquer chamada a `getStatusInicial()`/gravação. `msg=15` é inteiro literal, sem `getMensagem()` (consistente com CT-T9-14/Bloqueante 1). |
| CT-T11-06 | Estática | **PASSA** | Ocorrência sem `req_id`/`rep_id`: `$existente` é obtido mas a condição do `if` (linha 694) não é satisfeita, segue fluxo normal de `store()` sem bloqueio. |
| CT-T11-07 | Estática | **PASSA** | `OcoOcorrencia::delete()` linhas 638-645: `if (!empty($oco->req_id) \|\| (int)$oco->stt_id !== 28) { return [...'msg'=>3] }` — `req_id` preenchido já satisfaz a condição via `\|\|`, independente do `stt_id`, retorna MSG 3 sem chamar `$this->ocorrencia->delete($id)`. |
| CT-T11-08 | Estática | **PASSA** | Mesma condição: `stt_id=30 !== 28` também satisfaz o bloqueio via `\|\|`, mesmo com `req_id` nulo — MSG 3, sem excluir. |
| CT-T11-09 | Estática | **PASSA** | `stt_id=28` e `req_id` nulo → condição falsa em ambos os termos do `\|\|` → não entra no bloqueio → `$this->ocorrencia->delete($id)` executado, retorno de sucesso. |
| CT-T11-10 | Não executável | **NÃO EXECUTÁVEL NESTE AMBIENTE** | Comportamento genérico de `my_default.js`, não alterado por esta feature (confirmado que T11 não sobrescreve `#bt_salvar`). Requer navegador. |
| CT-T11-11 | Estática (mecanismo genérico) | **PASSA** (mecanismo) / **NÃO EXECUTÁVEL** (cenário real) | Mesmo mecanismo genérico do `LoginFilter` citado em CT-T9-12, aplicado a `tel_id=56`. |

**Resumo T11:** 11/11 casos com verificação estática favorável — nenhuma divergência de lógica de backend encontrada para RN03.1.6/RN03.2.5/RN04.1/RN05.1. 2 casos (CT-T11-04 parcial, CT-T11-10) dependem de navegador/servidor para confirmação final do comportamento visual.

---

## 3. Resultados — T12 (Tratativa de Ocorrências)

| ID | Via | Resultado | Evidência / Observação |
|---|---|---|---|
| CT-T12-01 | Estática (parcial) | **PASSA** (código) / **NÃO EXECUTÁVEL** (render visual do select) | `pw_acoes_ocorrencia.php` linhas 50-69: bloco "Adicionar Ação" renderizado quando `permiteAcaoExtra=true`, passado por `OcoTrataOcorrencia::finalizar()` (linha 242). `EntOcoTratativa::defCamposAcaoExtra()` usa `criaSelectRelativo('oco_tipo_acao', 'tpa_id', 'tpa_nome', ..., ['tpa_ativo'=>'A'])` **sem nenhum filtro adicional por `tpa_tipo`** — confirma que qualquer tipo ativo do catálogo T8 aparece, não só "Justificar" (Bloqueante 2, alternativa b). Renderização real do dropdown (`selectpicker`) requer navegador. |
| CT-T12-02 | Estática (parcial) | **PASSA** (estrutura) / **NÃO EXECUTÁVEL** (toggle JS real) | `addCampoAcaoExtra()` (controller) monta os 3 `<div>` condicionais (`divmovi[$ind]`, `divtela[$ind]`, `divstat[$ind]`) com classe `d-none` por padrão, mesma estrutura de T9. `FunChan` do select `tpa_id_extra` é `verificaTipoAcao(this)` (mesma função JS já usada em T9, não reimplementada). Rastreamento de código confirma a estrutura; alternância visual real do `d-none` no clique depende de `my_fields.js::verificaTipoAcao()` rodando no DOM — não executável aqui. |
| CT-T12-03 | Estática | **PASSA** | Ações de origem (`defCamposAcao()` em `EntOcoTratativa.php`) não geram nenhum botão de exclusão — nenhuma referência a `bt_del`/`removeAcaoExtra`/`bt-exclui` nesse método (confirmado por leitura completa do método, linhas 166-~280). Ações extra (`defCamposAcaoExtra()`) geram `bt_del` com `funcChan="removeAcaoExtra(this)"` (linhas 388-395). Confirma exclusão estruturalmente restrita à linha extra. |
| CT-T12-04 | Estática | **PASSA** | `store()` linhas 340-358: para `tpa_id` presente só em `tpa_id_extra` (não em `tpa_id`/origem), `origem=false`, e `gerarMovimentacao()` (linhas 456-486) usa `$acao->tmo_id` (vindo de `tmo_id_extra[$i]` do POST) quando `!$acao->origem` — não depende de `getTOAcao()`/configuração prévia no subtipo. Se `$tmoId` resolvido, `geraMovimentoRequisicoes()` é chamado normalmente. Confirma que a ação extra de fato produz efeito (sem no-op silencioso), corrigindo o Bloqueante 2. Execução real da movimentação de estoque (gravação em `est_...`) não realizada por falta de banco. |
| CT-T12-05 | Estática | **PASSA** | `resolveStatusFinal()` linhas 429-447: para ação `tpa_tipo=4` com `origem=false`, usa `$acao->stt_id` (vindo de `stt_id_extra[$i]`) diretamente (`elseif (!empty($acao->stt_id)) return (int) $acao->stt_id;`) — não cai no default 30 quando informado. |
| CT-T12-06 | Estática | **PASSA** | `switch` em `store()`, `case 4` (linhas 377-381): apenas `$retAcao = ['erro' => false];` com comentário explícito "NÃO gera movimentação" — nenhuma chamada a `gerarMovimentacao()`/`geraMovimentoRequisicoes()` nesse case. Bug do Bloqueante RN03.18 confirmado corrigido. |
| CT-T12-07 | Estática | **PASSA** — usar `stt_id=28` como status alvo, conforme ajuste do `byarq` | `resolveStatusFinal()`, ramo `$acao->origem === true` (linhas 436-440): `getAcaoPorId($acao->tpa_id, $postado['sut_id'])` retorna a linha de `oco_subt_ocorrencia_acao`; se `$acaoSubt->stt_id` não vazio, `return (int) $acaoSubt->stt_id`. Com a ação de origem configurada em T9 com `stt_id=28`, o método retorna `28` — nenhuma lógica intermediária altera esse valor antes de ser gravado em `oco_ocorrencia.stt_id` (linha 397-403 de `store()`). Gravação real em banco não confirmada por falta de acesso a `dev_ocorrencia_db`. |
| CT-T12-08 | Estática | **PASSA** | Tratativa só com "Justificar" (`tpa_tipo=1`): o `foreach` de `resolveStatusFinal()` nunca encontra `tpa_tipo===4`, sai do loop sem `return` interno, cai no `return 30` final (linha 446). |
| CT-T12-09 | Estática (parcial) | **PASSA (ação de origem) / FALHA (ação extra) — achado novo, ver Seção 5** | Ver detalhamento no achado **F-01** (Seção 5) — a confirmação MSG 6 funciona para "Gerar Movimentação" configurada como ação de **origem**, mas **não** é disparada quando a única ação "Gerar Movimentação" é adicionada como ação **extra** (RN03.15), porque `defCamposAcaoExtra()` não emite o hidden `tpa_tipo_marca[]` que `confirmaAcaoTratativa()` usa para detectar o tipo 3. |
| CT-T12-10 | Estática | **PASSA** | `confirmaAcaoTratativa()` (`my_ocorrencia.js:152-163`): se nenhum `input[name="tpa_tipo_marca[]"]` tiver valor `"3"`, `temGeraMovimentacao=false`, retorna `true` direto sem `boxAlert`. Código coerente para o caso "sem Gerar Movimentação entre as ações de origem" (a limitação de cobertura de ações extra é a mesma do achado F-01, mas não invalida este caso específico, que testa a ausência). |
| CT-T12-11 | Estática (amostragem) | **PASSA** | `showCabecalho()` (chamado de `OcoOcorrencia`, reaproveitado por `finalizar()` linha 189) e o bloco "Telas Aplicáveis" (linhas 193-223) não foram alterados nesta feature — nenhuma referência a `sta_fina`/`tpa_id_extra`/`resolveStatusFinal`/guard de deduplicação nesses trechos; a única mudança na função `finalizar()` foi a adição do bloco de Ações com `permiteAcaoExtra=true` e do `scripts='my_ocorrencia'`. Sem indício de alteração indevida em RN03.1-14/RN03.16-17. |
| CT-T12-12 | Estática (mecanismo genérico) | **PASSA** (mecanismo) / **NÃO EXECUTÁVEL** (cenário real) | Mesmo mecanismo do `LoginFilter`, aplicado a `tel_id=61`. `finalizar()` e `store()` seguem a nomenclatura padrão de métodos — nenhum bypass de permissão introduzido. |
| **CT-T12-13** | Estática | **PASSA** | Ver Seção 4 (detalhamento) |
| **CT-T12-14** | Estática | **PASSA** | Ver Seção 4 (detalhamento) |

**Resumo T12:** 13/14 casos com verificação estática favorável. **1 achado novo (F-01, CT-T12-09)** relacionado a uma lacuna de cobertura da confirmação MSG 6 para ações extras do tipo "Gerar Movimentação" — detalhado na Seção 5.

---

## 4. Detalhamento dos casos específicos pedidos pelo `byarq`

### CT-T9-13 — Remoção de arquivos residuais / classmap Composer

**Sub-passo 1 (listagem de ausência dos 14 arquivos residuais):** **PASSA**
```
ls app/Controllers/Ocorrencia/ | grep -iE "BKP|^nov\.php$|^teste\.php$"   → vazio
ls app/Models/Ocorre/ | grep -iE "BKP|DESK-DOUGLAS"                       → vazio
```
Nenhum dos 14 arquivos listados na Seção 3 do dev.md está presente no working tree.

**Sub-passo 2 (única declaração de `OcoNovOcorrencia`):** **PASSA**
```
grep -rl "class OcoNovOcorrencia" app/Controllers/   → app/Controllers/Ocorrencia/OcoNovOcorrencia.php (único resultado)
```
Confirma que não há mais nenhum arquivo declarando essa classe, eliminando o risco de ambiguidade relatado pelo `byarq`.

**Sub-passo 3 (rotas):** **PASSA (indireto)** — `app/Config/Routes.php` (linhas 39-52) referencia os controllers `OcoTipoOcorrencia`, `OcoSubtOcorrencia`, `OcoOcorrencia`, `OcoTrataOcorrencia` pelo nome de classe dentro do namespace `Ocorrencia`; nenhuma rota referencia `nov`/`teste`/`*BKP`. Já verificado no ciclo de definição do plano e reconfirmado nesta execução.

**Sub-passo 4 (`composer dump-autoload`):** **NÃO EXECUTÁVEL NESTE AMBIENTE**
```
composer dump-autoload
→ /c/ProgramData/ComposerSetup/bin/composer: line 14: php: command not found
```
Não há PHP CLI disponível neste ambiente (nem para `composer`, que depende dele, nem para rodar scripts diretamente). **Este sub-passo precisa ser executado manualmente pelo `bydev`/usuário em um ambiente com PHP CLI antes do deploy**, capturando a saída completa e conferindo ausência de warning de classe duplicada/PSR-4, exit code 0.

**Sub-passo 5 (smoke test das telas via navegador):** **NÃO EXECUTÁVEL NESTE AMBIENTE** — requer servidor HTTP ativo e navegador/sessão autenticada. Não há servidor CeqWeb respondendo em `localhost`/`127.0.0.1` neste ambiente.

**Resultado consolidado CT-T9-13: PASSA (parcial)** — tudo que podia ser verificado estaticamente (ausência de arquivos, ausência de classe duplicada declarada, ausência de referência em rotas) confirma que a remoção foi limpa. **Os sub-passos 4 e 5 (execução real do `composer dump-autoload` e smoke test em navegador) ficam pendentes de execução manual** em ambiente com PHP CLI/servidor ativo — recomendo que sejam rodados antes do documento de entrega ser fechado, já que são baratos e rápidos de confirmar.

---

### CT-T9-14 — Ausência de `getMensagem()` real nos 3 controllers (Bloqueante 1)

```
grep -n "getMensagem(" app/Controllers/Ocorrencia/OcoSubtOcorrencia.php
  → 346: (comentário) "getMensagem() no controller (Bloqueante 1) — retorna apenas"
  → 348: (comentário) "o texto é o front (getMensagem()/boxAlert() em my_ocorrencia.js)."

grep -n "getMensagem(" app/Controllers/Ocorrencia/OcoOcorrencia.php
  → 349: (comentário) "Bloqueante 1 (revisão 01) — não chamar getMensagem() no"

grep -n "getMensagem(" app/Controllers/Ocorrencia/OcoTrataOcorrencia.php
  → (nenhum resultado)
```

Nenhuma das três ocorrências nos dois primeiros arquivos é uma chamada real de função (todas dentro de comentários `//`); `OcoTrataOcorrencia.php` não tem nenhuma menção. Inspeção visual complementar dos `$ret['msg']`/retornos JSON associados às RNs desta feature:

| Local | Valor de `msg` | Tipo |
|---|---|---|
| `OcoSubtOcorrencia::ativinativ()`, bloqueio de pendência | `14` | inteiro literal |
| `OcoSubtOcorrencia::ativinativ()`, `catch` genérico | `17` | inteiro literal |
| `OcoOcorrencia::edit()`, bloqueio RN04.1 | `'Alteração não Permitida'` (string, via `throw`) | **string literal embutida na exceção, não um `msg_id`** — ver observação abaixo |
| `OcoOcorrencia::store()`, bloqueio RN04.1 (update) | `15` | inteiro literal |
| `OcoOcorrencia::delete()`, bloqueio RN05.1 | `3` | inteiro literal |

**Resultado CT-T9-14: PASSA** — não há regressão ao padrão incorreto do Bloqueante 1 (chamada ao helper PHP `getMensagem()`); todos os `msg` retornados como JSON são inteiros literais.

**Observação (não é falha do caso, é achado complementar — ver F-02 na Seção 5):** `OcoOcorrencia::edit()` não retorna JSON (usa `throw` não capturado, aceito pela revisão), então seu texto de erro é uma **string literal PHP** (`'Alteração não Permitida'`) embutida na `Exception`, exibida pela página de erro padrão do CI4 — não é `getMensagem()` (então não viola o Bloqueante 1 em si), mas também não é o texto oficial de MSG_15 cadastrado em `cfg_mensagem` (que só é resolvido pelo `boxAlert()` no front, e aqui não há front nenhum envolvido, é uma página de erro do framework). Registrado como observação de UX, não como falha deste caso de teste.

---

### CT-T12-13 — Duplicidade origem × extra (mesmo `tpa_id`)

**Rastreamento de código (`OcoTrataOcorrencia::store()`, linhas 320-358):**
1. `$tpaIdsOrigem` é populado com todos os `tpa_id` de `postado['tpa_id']` (ações de origem) — para `tpa_id=X`, `$tpaIdsOrigem[X] = true`.
2. `$tpaIdsUsados = $tpaIdsOrigem` (cópia).
3. No loop de `postado['tpa_id_extra']`, se o mesmo `tpa_id=X` aparecer, `isset($tpaIdsUsados[X])` é `true` → `continue` (linha 346-348) → **a entrada extra é descartada, não entra em `$acoesExecutar`**.
4. Resultado: `$acoesExecutar` contém **apenas uma** entrada para `tpa_id=X` (a de origem, `origem=true`).
5. No `switch` de execução, `gerarMovimentacao()` é chamado **uma única vez** para `tpa_id=X`, usando os dados de origem (`getTOAcao()`), não os da linha extra.

**Resultado CT-T12-13: PASSA** — o guard `$tpaIdsUsados` elimina corretamente o cenário de duplicidade origem × extra relatado originalmente pelo `byrev`. Execução real (contagem de registros gerados em `est_...` após um submit real) não realizada por falta de acesso a banco/servidor — recomendo que este caso específico seja o primeiro a ser revalidado manualmente assim que o ambiente permitir, dado que é uma correção de um bug de dados reais (duplicidade de movimentação de estoque).

---

### CT-T12-14 — Duplicidade extra × extra (duas linhas extras, mesmo `tpa_id`)

**Rastreamento de código:** com `tpa_id=Y` **não** presente em `postado['tpa_id']` (não é origem), `$tpaIdsOrigem` não contém `Y`. No loop de `tpa_id_extra`, a **primeira** ocorrência de `Y` (índice `$i` menor) não está em `$tpaIdsUsados` → processada normalmente, `$tpaIdsUsados[Y] = true` (linha 349). Na **segunda** ocorrência de `Y` (outra linha extra, índice maior), `isset($tpaIdsUsados[Y])` já é `true` → `continue` — a segunda linha é descartada.

**Resultado CT-T12-14: PASSA** — o mesmo guard cobre corretamente o cenário extra × extra, mantendo apenas a primeira ocorrência (`$i` menor) de cada `tpa_id` repetido entre as linhas extras. Mesma ressalva de CT-T12-13 quanto à ausência de confirmação via execução real em banco.

---

## 5. Achados novos (fora do escopo formal do plano, reportados conforme solicitado)

### F-01 — RN03.18.2: confirmação MSG 6 não cobre ação "Gerar Movimentação" adicionada como ação extra

**Onde:** `app/Entities/Ocorrencia/EntOcoTratativa.php::defCamposAcaoExtra()` (linhas 301-390) vs. `public/assets/jscript/my_ocorrencia.js::confirmaAcaoTratativa()` (linhas 152-163).

**Descrição:** `defCamposAcao()` (ações de **origem**) emite um hidden `tpa_tipo_marca[]` com o `tpa_tipo` da ação (linhas 178-185 de `EntOcoTratativa.php`), usado por `confirmaAcaoTratativa()` para detectar se há alguma ação tipo 3 (Gerar Movimentação) entre as ações **marcadas/presentes** no formulário, disparando `boxAlert(6, ...)` antes do submit. Porém `defCamposAcaoExtra()` (ações **extra**, adicionadas via RN03.15) **não emite esse mesmo hidden** — não há nenhum `tpa_tipo_marca[]` gerado nessa função (confirmado por leitura completa do método, linhas 301-390, nenhuma referência a `tpa_tipo_marca`).

**Consequência:** se o usuário adicionar, em T12, uma ação extra do tipo "Gerar Movimentação" (fluxo explicitamente habilitado pela RN03.15/Bloqueante 2 — alternativa b) **sem** nenhuma ação de origem também ser do tipo 3, o seletor `jQuery('input[name="tpa_tipo_marca[]"]')` não encontra nenhum elemento com valor `"3"`, `confirmaAcaoTratativa()` retorna `true` direto, e o formulário é submetido **sem a confirmação MSG 6** — apesar de uma movimentação de estoque real ser gerada (confirmado em F-CT-T12-04 acima, que a ação extra tipo 3 tem efeito real).

**Classificação:** gap de cobertura da RN03.18.2 em relação ao escopo ampliado da RN03.15 (a RN03.18.2 foi escrita/revisada antes de "ação extra" incluir Gerar Movimentação com efeito real — o Bloqueante 2 que habilitou isso é de uma revisão posterior). Não é regressão de nenhuma RN "sem alteração necessária"; é uma lacuna de integração entre duas RNs desta mesma feature.

**Impacto:** médio — o usuário pode confirmar uma tratativa que gera movimentação de estoque sem o aviso de confirmação esperado, quando a única fonte da movimentação é uma ação extra. Não há risco de dado incorreto/duplicado (isso já foi coberto pelo guard `$tpaIdsUsados`), é puramente uma lacuna de UX/confirmação.

**Recomendação:** `EntOcoTratativa::defCamposAcaoExtra()` deveria emitir o mesmo hidden `tpa_tipo_marca[]` (com `valor` inicial correspondente ao `tpa_tipo` selecionado, atualizado via JS a cada troca do select `tpa_id_extra`, já que aqui o tipo não é fixo como nas ações de origem) — ou `confirmaAcaoTratativa()` precisa também inspecionar o valor selecionado nos selects `tpa_id_extra[]` da tabela `#rep_acoesextra`, cruzando com o catálogo de tipos de ação (T8) carregado no cliente. Encaminhar ao `byarq` para decidir a abordagem antes de codificar (pode ser tratado como um bug pequeno pontual, não exige nova rodada completa de revisão de arquitetura).

### F-02 — `OcoOcorrencia::edit()` (RN04.1): texto de erro não é a MSG_15 oficial

Já detalhado em CT-T9-14 acima — registrado apenas como observação de UX (não bloqueante), já que a revisão 01 aceitou o uso de `throw` não capturado para este caso.

---

## 6. Observações de execução (herdadas do plano + reforços desta rodada)

- Casos **[MIGR]** foram todos tratados como **liberados** nesta execução (migration confirmada aplicada em `dev_ocorrencia_db` pelo usuário) — nenhum caso ficou bloqueado por essa pré-condição.
- CT-T9-09 permanece caso documental, sem critério de passa/falha funcional — resultado: método `getUsoGestao()` confirmado ausente.
- CT-T11-04 mantém a nota já registrada no plano (uso de `throw` não capturado em `edit()`, aceito como não-bloqueante) — resultado desta execução reforça que o comportamento de código é exatamente o descrito, sem novidade.
- CT-T12-13 e CT-T12-14 confirmam a correção do guard `$tpaIdsUsados` (origem×extra e extra×extra) por rastreamento de código — recomenda-se confirmação end-to-end assim que houver acesso a banco/navegador.
- **A integração `Buscas/buscaProdutoporLote` e as tabelas ERP `E075PRO`/`E207DLS` permaneceram intencionalmente fora do escopo de teste deste ciclo**, conforme decisão do usuário registrada no plano de arquitetura (Seção 1, decisão 6, do documento de desenvolvimento) e reforçada no plano de testes aprovado (Seção 5). Nenhum teste desta rodada tocou esse código; nenhum problema incidental foi observado ou buscado nesse componente.
- **Nenhum caso deste ciclo foi reprovado por divergência de lógica de backend** em relação ao documento de desenvolvimento aprovado — todas as RNs rastreadas (T9: RN03.6, RN06.2; T11: RN03.1.6/RN03.2.5, RN04.1, RN05.1; T12: RN03.15, RN03.18, RN03.18.1, RN03.18.2, correção de duplicidade) correspondem ao código-fonte atual.
- **Pendências reais para fechar o ciclo, antes do documento de entrega:**
  1. Executar `composer dump-autoload` de fato (CT-T9-13, sub-passo 4) em ambiente com PHP CLI e confirmar ausência de warning.
  2. Smoke test em navegador das telas citadas em CT-T9-13 (sub-passo 5).
  3. Execução end-to-end real (navegador + banco) de pelo menos: CT-T9-02/06/07 (toggle/pendências visuais), CT-T9-10/11/12, CT-T11-04/10/11, CT-T12-01/02/09/12/13/14 — todos os casos aqui marcados como "não executável neste ambiente".
  4. Decisão do `byarq` sobre o achado **F-01** (cobertura de MSG 6 para ação extra tipo Gerar Movimentação) — novo achado, ainda sem rodada de revisão associada.

---

## 7. Rastreabilidade

Resultado gerado a partir de `docs/testes/ocorrencias-t9-t11-t12-plano-testes.md` (aprovado pelo `byarq`), documento de desenvolvimento `docs/desenvolvimento/ocorrencias-t9-t11-t12-dev.md` e revisão `docs/revisao/ocorrencias-t9-t11-t12-revisao-01.md` (+ rodadas 02-04 do `byrev`, citadas no plano). Achados novos (F-01, F-02) devem ser encaminhados ao `byarq` para decisão antes do documento de entrega ser fechado.
