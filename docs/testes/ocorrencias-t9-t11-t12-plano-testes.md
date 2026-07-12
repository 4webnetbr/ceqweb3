# Plano de Testes — Módulo Ocorrências (T9, T11, T12)

**Projeto:** CeqWeb 3.0
**Módulo:** Ocorrências (`app/Controllers/Ocorrencia`)
**Telas envolvidas:** T9 — Subtipos de Ocorrências (tel_id=49) · T11 — Gestão de Ocorrências (tel_id=56) · T12 — Tratativa de Ocorrências (tel_id=61)
**Origem:** Plano de testes definido pelo `bytest`.
**Documentos de referência:**
- Documento de desenvolvimento aprovado: `docs/desenvolvimento/ocorrencias-t9-t11-t12-dev.md`
- Documento de revisão, rodada 01 (bloqueantes corrigidos): `docs/revisao/ocorrencias-t9-t11-t12-revisao-01.md`
- Ciclo de revisão de código (`byrev`): **aprovado formalmente após 3 rodadas**, documentado em `docs/revisao/`.
**Status:** aguardando aprovação do `byarq` para execução.

---

## 1. Pré-condições gerais

### 1.1 Migration

A migration abaixo precisa estar **aplicada em `dev_ocorrencia_db`** antes da execução dos casos marcados **[MIGR]** neste plano:

`docs/desenvolvimento/migration-sta_fina-oco_subt_ocorrencia_acao.sql`

```sql
ALTER TABLE `oco_subt_ocorrencia_acao`
  ADD COLUMN `sta_fina` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Finalização Automática' AFTER `stt_id`;
```

Sem a migration aplicada, os casos **[MIGR]** ficam **bloqueados — pré-condição pendente** (não devem ser reportados como "falha").

### 1.2 Massa de dados mínima

- Subtipo (T9) com 2+ ações, mix de `sta_fina='S'` e `'N'`.
- Subtipo com todas as ações `sta_fina='S'`.
- Subtipo sem nenhuma ação cadastrada.
- Ocorrência (T11) com `stt_id=28` (Pendente), sem `req_id`/`rep_id`.
- Ocorrência com `req_id` ou `rep_id` preenchido (gerada por outra tela).
- Ocorrência já `stt_id=30` (Finalizada), para testar bloqueio de exclusão fora da regra.
- Catálogo T8 (`oco_tipo_acao`) com ao menos um `tpa_id` de cada tipo (1 Justificar, 2 Abrir Tela, 3 Gerar Movimentação, 4 Alterar Status), todos ativos.
- Um `tpa_id` tipo "Gerar Movimentação" já configurado como ação de origem de um subtipo específico (necessário para os casos de duplicidade em T12).

### 1.3 Perfis de teste

- Um perfil com permissão `CAEXN` completa para `tel_id 49` (T9) / `56` (T11) / `61` (T12).
- Um perfil **sem** permissão nessas telas, para os casos de bloqueio *fail-closed* (conforme `rascunho-helpers-php.md` — permissão ausente bloqueia tudo).

---

## 2. T9 — Subtipos de Ocorrências (tel_id=49)

| ID | RN | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|---|
| CT-T9-01 | RN03.6 | `sut_fina` não é mais editável em Dados Gerais | Nenhuma | Abrir `add()` e `edit()` de um subtipo | — | Campo `sut_fina` **ausente** da aba Dados Gerais em ambos os formulários |
| CT-T9-02 **[MIGR]** | RN03.6 | Checkbox `sta_fina` por linha na aba Ações | Migration aplicada | Abrir aba Ações de um subtipo com 2+ ações; alternar `sta_fina` de forma independente em cada linha | Subtipo com 2 ações | Cada linha mantém seu próprio valor de `sta_fina`, sem afetar as demais |
| CT-T9-03 **[MIGR]** | RN03.6 | `sut_fina` derivado = 'S' quando todas as ações são 'S' | Migration aplicada; subtipo com 2 ações, ambas `sta_fina='S'` | Salvar o subtipo (`store()`) | Subtipo com 2 ações `sta_fina='S'` | `oco_subt_ocorrencia.sut_fina` gravado como `'S'` |
| CT-T9-04 **[MIGR]** | RN03.6 | `sut_fina` derivado = 'N' quando há mix de S/N | Migration aplicada; subtipo com 2 ações, uma `'S'` e uma `'N'` | Salvar o subtipo (`store()`) | Subtipo com 1 ação `'S'` + 1 ação `'N'` | `oco_subt_ocorrencia.sut_fina` gravado como `'N'` |
| CT-T9-05 **[MIGR]** | RN03.6 | `sut_fina` derivado = 'S' quando não há ação cadastrada | Migration aplicada; subtipo sem nenhuma ação | Salvar o subtipo (`store()`) | Subtipo sem ações | `oco_subt_ocorrencia.sut_fina` gravado como `'S'` (mantém comportamento decidido na Seção 1, decisão 5, do documento de desenvolvimento) |
| CT-T9-06 **[MIGR]** | RN06.2 | Bloqueio de inativação com pendência | Migration aplicada; subtipo vinculado a ocorrência(s) pendente(s) | Acionar `ativinativ()` sobre o subtipo | Subtipo com ocorrência(s) pendente(s) vinculada(s) | `ativinativ()` retorna `{erro:true, msg:14, pendencias:[...]}`; front (`my_ocorrencia.js`) monta `<ul>` via `getMensagem(14)` e chama `boxAlert()` com o texto concatenado; registro **permanece ativo** |
| CT-T9-07 **[MIGR]** | RN06.2 | Inativação sem pendência | Migration aplicada; subtipo sem nenhuma ocorrência pendente vinculada | Acionar `ativinativ()` sobre o subtipo | Subtipo sem pendências | Grava `sut_ativo='I'` normalmente; `boxAlert()` de sucesso padrão |
| CT-T9-08 | RN06.2 | Erro genérico não mascarado como pendência | Nenhuma (força exceção não relacionada a pendência) | Provocar exceção genérica dentro de `ativinativ()` (ex.: erro de banco simulado) | Condição de erro forçada | Resposta com `msg=17` ("Problema no Sistema"), **sem** campo `pendencias` |
| CT-T9-09 | — | Verificação estática: `getUsoGestao()` removido | Nenhuma | Grep por `getUsoGestao` em todo o projeto | — | Nenhuma ocorrência do método em `OcorreSubtOcorrenciaModel.php` nem em nenhum chamador. **Caso documental**, sem critério passa/falha funcional |
| CT-T9-10 (regressão) | RN04.3 | Salvar sem alteração real | Nenhuma | Abrir `edit()` de um subtipo e clicar em salvar sem alterar nenhum campo | — | Comportamento genérico do handler `#bt_salvar`/`data-valid` dispara `boxAlert()` com MSG 7, sem interferência de lógica nova |
| CT-T9-11 (regressão) | RN03.7 | Aba Permissões persiste corretamente | Nenhuma | Cadastrar 2+ perfis na aba Permissões, salvar, reabrir o subtipo | 2+ perfis selecionados | Perfis persistidos e recarregados corretamente via `defPermissoes()` |
| CT-T9-12 | — (fail-closed) | Bloqueio de acesso sem permissão | Perfil sem permissão para `tel_id=49` | Tentar acessar `index`/`lista`/`add`/`edit`/`delete`/`ativinativ` | Usuário logado com perfil sem permissão | Acesso bloqueado em todos os métodos (`vw_semacesso`), conforme `LoginFilter` |
| CT-T9-13 | Seção 3 do documento de desenvolvimento (remoção de arquivos residuais) | Remoção dos arquivos residuais não deixou resíduo no classmap do Composer nem quebrou a tela real `OcoNovOcorrencia` (compartilhava namespace/nome de classe com `nov.php`/`teste.php` antes da remoção) | Todos os 14 arquivos residuais ausentes do working tree (`Controllers/Ocorrencia`: `OcoOcorrenciaBKP.php`, `OcoTrataOcorrenciaBKP.php`, `OcoModOcorrenciaBKP.php`, `OcoTipoOcorrenciaBKP.php`, `OcoTipoAcaoBKP.php`, `nov.php`, `teste.php`; `Models/Ocorre`: `OcorreOcorrenciaModelBKP.php`, `OcorreTrataOcorrenciaModelBKP.php`, `OcorreModOcorrenciaModelBKP.php`, `OcorreTipoOcorrenciaModelBKP.php`, `OcorreTipoAcaoModelBKP.php`, `OcorreSubtOcorrenciaModel-DESK-DOUGLAS.php`, `OcorreOcorrenciaModel-DESK-DOUGLAS-2.php`), checagem via `ls`/`git status`; Composer disponível via CLI | (1) Confirmar ausência dos 14 arquivos via listagem de diretório; (2) rodar `composer dump-autoload`; (3) capturar saída completa; (4) acessar a tela `OcoNovOcorrencia` (`index`/`add`/`edit`) normalmente logado; (5) repetir smoke test nas telas que tinham BKP homônimo (`OcoOcorrencia`, `OcoTrataOcorrencia`, `OcoModOcorrencia`, `OcoTipoOcorrencia`, `OcoTipoAcao`) | — | `composer dump-autoload` sem warning de classe duplicada/PSR-4, exit code 0; `OcoNovOcorrencia` carrega sem erro 500/`Cannot redeclare class`; demais telas idem; nenhuma rota resolve para arquivo residual |
| CT-T9-14 | Bloqueante 1 (revisão-01) | Verificação estática: sem chamadas ao helper PHP `getMensagem()` nos controllers | Nenhuma (checagem estática de código-fonte) | (1) `grep "getMensagem("` em `OcoSubtOcorrencia.php`; (2) mesmo grep em `OcoOcorrencia.php`; (3) mesmo grep em `OcoTrataOcorrencia.php`; (4) inspecionar visualmente que todo `$ret['msg']` nos pontos afetados (RN06.2/RN04.1/RN05.1/RN03.15/RN03.18.1/RN03.18.2) é inteiro literal, nunca string de código nem retorno de `getMensagem()` | — | Os 3 greps retornam vazio ou só comentários explicativos (sem chamada real à função); todos os `$ret['msg']` são inteiros literais. Caso documental (espelha CT-T9-09) para impedir regressão ao padrão incorreto do Bloqueante 1 — controller nunca deve chamar o helper PHP `getMensagem(string $codigo)`, só retornar `msg_id` inteiro; front resolve via `boxAlert()`/`GET /mensagem/{id}`. **Guardrail permanente: reexecutar a cada rodada de correção futura destas telas.** |

---

## 3. T11 — Gestão de Ocorrências (tel_id=56)

| ID | RN | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|---|
| CT-T11-01 **[MIGR]** | RN03.1.6 / RN03.2.5 | Subtipo sem ação → Finalização Automática | Migration aplicada; subtipo sem nenhuma ação cadastrada | Criar ocorrência (`store()`/`storetmp()`) vinculada a esse subtipo | Subtipo sem ações | `stt_id = 29` (Finalização Automática) |
| CT-T11-02 **[MIGR]** | RN03.1.6 / RN03.2.5 | Todas as ações `sta_fina='S'` → Finalização Automática | Migration aplicada; subtipo com todas as ações `sta_fina='S'` | Criar ocorrência vinculada a esse subtipo | Subtipo com ações 100% `'S'` | `stt_id = 29` |
| CT-T11-03 **[MIGR]** | RN03.1.6 / RN03.2.5 | Ao menos uma ação `sta_fina='N'` → Pendente | Migration aplicada; subtipo com mix de `sta_fina`, incluindo o antigo caso especial `tpa_id=12` | Criar ocorrência vinculada a esse subtipo, incluindo cenário que antes usava a regra especial `tpa_id==12` | Subtipo com ao menos uma ação `'N'` | `stt_id = 28` (Pendente) em todos os cenários, inclusive o antigo caso especial `tpa_id=12` — confirma que essa regra antiga foi eliminada e substituída por `getStatusInicial()` |
| CT-T11-04 | RN04.1 | Bloqueio de `edit()` com vínculo | Ocorrência com `req_id` ou `rep_id` preenchido | Requisição direta simulando bypass do botão oculto no front, para `edit($id)` | Ocorrência vinculada | Erro sem carregar formulário, `msg=15`. **Nota:** `edit()` usa `throw` não capturado (resulta em página de erro padrão do CI4, aceito como não-bloqueante conforme revisão-01) — documentar o comportamento real observado na execução |
| CT-T11-05 | RN04.1 | Bloqueio de `store()` (update) com vínculo | Ocorrência com `req_id` ou `rep_id` preenchido | POST direto para `store()` com `oco_id` existente | Ocorrência vinculada | `{erro:true, msg:15}`, sem gravar a alteração |
| CT-T11-06 (regressão) | RN04.1 | Alteração normal sem vínculo | Ocorrência sem `req_id`/`rep_id` | Alterar a ocorrência via `edit()`/`store()` | Ocorrência sem vínculo | Alteração processada normalmente, sem bloqueio |
| CT-T11-07 | RN05.1 | Bloqueio de exclusão por vínculo com requisição | Ocorrência com `req_id` preenchido, qualquer `stt_id` | Acionar `delete()` | Ocorrência com `req_id` preenchido | `MSG 3`, não exclui |
| CT-T11-08 | RN05.1 | Bloqueio de exclusão por status diferente de Pendente | Ocorrência com `stt_id=30`, sem `req_id` | Acionar `delete()` | Ocorrência finalizada, sem vínculo | `MSG 3`, não exclui |
| CT-T11-09 | RN05.1 | Exclusão permitida | Ocorrência com `stt_id=28`, `req_id` nulo | Acionar `delete()` | Ocorrência pendente sem vínculo | Exclusão executada com sucesso |
| CT-T11-10 (regressão) | RN04.2 / RN04.3 | Confirmações genéricas do handler padrão | Nenhuma | Cancelar edição com alteração pendente (espera MSG 2); salvar sem alteração real (espera MSG 7) | — | Ambos os comportamentos preservados, nada quebrado pelo handler `#bt_salvar`/`data-valid` |
| CT-T11-11 | — (fail-closed) | Bloqueio de acesso sem permissão | Perfil sem permissão para `tel_id=56` | Tentar acessar os métodos da tela | Usuário logado com perfil sem permissão | Acesso bloqueado (`vw_semacesso`) |

---

## 4. T12 — Tratativa de Ocorrências (tel_id=61)

| ID | RN | Situação/Objetivo | Pré-condição | Passos | Dados de entrada | Resultado esperado |
|---|---|---|---|---|---|---|
| CT-T12-01 | RN03.15 | Bloco "Adicionar Ação" disponível e sem restrição de tipo | Nenhuma | Abrir `finalizar()` de uma ocorrência | — | Bloco "Adicionar Ação" presente; select traz **qualquer** tipo de ação ativo do catálogo T8 (não só "Justificar") |
| CT-T12-02 | RN03.15 | Campos condicionais por tipo de ação extra | Nenhuma | Selecionar, na linha de ação extra, cada um dos 4 tipos de T8 | tpa_tipo 1, 2, 3, 4 | Campos condicionais (`divmovi`/`divtela`/`divstat` conforme `tpa_tipo`) aparecem corretamente via `verificaTipoAcao()`, mesmo padrão de T9 |
| CT-T12-03 | RN03.15 | Exclusão restrita à ação de origem | Ocorrência com ao menos 1 ação de origem e 1 ação extra adicionada | Tentar excluir a linha de origem e a linha extra | — | Exclusão bloqueada para a linha de origem; `removeAcaoExtra()` funcional para a linha extra |
| CT-T12-04 **[MIGR]** | RN03.15 (Bloqueante 2) | Ação extra "Gerar Movimentação" produz efeito real | Migration aplicada; `tpa_id` tipo 3 **não** configurado como origem no subtipo da ocorrência | Adicionar ação extra "Gerar Movimentação", preencher `tmo_id_extra`, submeter tratativa | `tmo_id_extra` preenchido | Movimentação gerada de fato (sem no-op silencioso) |
| CT-T12-05 **[MIGR]** | RN03.15 / RN03.18.1 | Ação extra "Alterar Status" produz efeito real | Migration aplicada; `tpa_id` tipo 4 não configurado como origem | Adicionar ação extra "Alterar Status", preencher `stt_id_extra`, submeter tratativa | `stt_id_extra` preenchido | Status final = `stt_id_extra` informado, não cai no default 30 |
| CT-T12-06 | RN03.18 | `case 4` não chama mais `gerarMovimentacao()` | Ocorrência cuja tratativa só tem ação "Alterar Status" de origem, sem "Gerar Movimentação" | Executar a tratativa (`store()`) | Só ação "Alterar Status" | Nenhuma movimentação gerada |
| CT-T12-07 **[MIGR]** | RN03.18.1 | Status final segue "Alterar Status" configurado na origem | Migration aplicada; ação "Alterar Status" de origem configurada com `stt_id=28` como status alvo (simulando "voltar para pendente" via ação de tratativa — `cfg_status` só tem `28`/`29`/`30` cadastrados para o módulo Ocorrência) | Executar a tratativa com essa ação | Ação de origem configurada com `stt_id=28` | Status final da ocorrência = `28` |
| CT-T12-08 | RN03.18.1 | Status final default = Finalizada | Tratativa sem nenhuma ação "Alterar Status" entre as executadas (ex.: só "Justificar") | Executar a tratativa | Só ação "Justificar" | Status final = `stt_id=30` (Finalizada) |
| CT-T12-09 | RN03.18.2 | Confirmação MSG 6 antes de submeter (Gerar Movimentação marcada) | Ocorrência com ação "Gerar Movimentação" disponível para marcação | Marcar a ação "Gerar Movimentação" e tentar submeter o formulário; cancelar a confirmação | Ação marcada | `confirmaAcaoTratativa()` detecta a marcação (hidden `tpa_tipo_marca[]` por linha marcada) e dispara `boxAlert(6, ...)`; cancelar a confirmação impede o POST |
| CT-T12-10 | RN03.18.2 | Sem confirmação quando não há Gerar Movimentação marcada | Nenhuma ação "Gerar Movimentação" marcada | Submeter o formulário de tratativa | Nenhuma ação de movimentação marcada | `confirmaAcaoTratativa()` retorna `true` direto, sem `boxAlert(6)`; POST imediato |
| CT-T12-11 (regressão) | RN03.1–14, RN03.16–17 | Amostragem representativa de RNs não alteradas | Nenhuma | Verificar `showCabecalho()` e bloco "Telas Aplicáveis" | — | Nenhuma alteração indevida identificada nessas RNs |
| CT-T12-12 | — (fail-closed) | Bloqueio de acesso sem permissão | Perfil sem permissão para `tel_id=61` | Tentar acessar os métodos da tela, em especial `finalizar()`/`store()` | Usuário logado com perfil sem permissão | Acesso bloqueado (`vw_semacesso`) |
| CT-T12-13 | RN03.15 (correção de duplicidade origem × extra) | Ação extra duplicando `tpa_id` já configurado como origem (Gerar Movimentação) | Subtipo com ação de origem `tpa_id=X` tipo 3 já configurada | Em `finalizar()`, adicionar ação extra escolhendo o **mesmo** `tpa_id=X`; preencher `tmo_id_extra`; submeter | `tpa_id=X` repetido (origem + extra) | Apenas **1** movimentação gerada (não duas) — o guard `$tpaIdsUsados` deve ignorar a entrada extra duplicada |
| CT-T12-14 (novo, adicionado pelo `byrev` na 3ª rodada) | RN03.15 (correção de duplicidade extra × extra) | Duas linhas de ação extra com o mesmo `tpa_id` (Gerar Movimentação, não configurado como origem) | `tpa_id` não configurado como origem no subtipo | Adicionar **duas** linhas de ação extra escolhendo o mesmo `tpa_id` (tipo Gerar Movimentação); preencher `tmo_id_extra` nas duas; submeter | 2 linhas extras com o mesmo `tpa_id` | Apenas **1** movimentação gerada — a segunda linha é ignorada pelo guard `$tpaIdsUsados`, que agora cobre também o cenário extra × extra |

---

## 5. Observações de execução

- Casos marcados **[MIGR]** só devem ser executados após confirmação de que a migration da Seção 1.1 está aplicada em `dev_ocorrencia_db`. Se não estiver, reportar como **bloqueado — pré-condição pendente**, não como falha.
- CT-T9-09 é um caso documental (verificação estática via grep), sem critério de passa/falha funcional.
- CT-T11-04 tem uma nota de comportamento conhecido e aceito (uso de `throw` não capturado em `edit()`) — o resultado a documentar é o comportamento observado, não uma reprovação automática, desde que a página de erro do CI4 seja de fato exibida sem carregar o formulário e sem gravar dados.
- CT-T12-13 e CT-T12-14 validam especificamente a correção de duplicidade do guard `$tpaIdsUsados`, decidida ao longo das rodadas de revisão de código (`byrev`) documentadas em `docs/revisao/`.
- A integração `Buscas/buscaProdutoporLote` e as tabelas ERP `E075PRO`/`E207DLS` estão **intencionalmente fora do escopo de teste** deste ciclo — decisão do usuário registrada no plano de arquitetura (Seção 1, decisão 6, do documento de desenvolvimento). Qualquer problema encontrado incidentalmente nessa integração durante os testes de T11 deve ser tratado como **bug novo**, não como falha deste plano.

---

## 6. Rastreabilidade

Cada caso de teste referencia a RN correspondente do documento de desenvolvimento aprovado (`docs/desenvolvimento/ocorrencias-t9-t11-t12-dev.md`), permitindo rastrear diretamente do requisito até o caso de teste e, na próxima etapa, até o resultado de execução (`docs/testes/ocorrencias-t9-t11-t12-resultado-testes.docx`).
