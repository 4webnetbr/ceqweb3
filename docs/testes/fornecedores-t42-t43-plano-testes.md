# Plano de Testes — T42 (Desvio de Qualidade) e T43 (Notificação de Evento)

**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores — T42 (Desvio de Qualidade) · T43 (Notificação de Evento)
**Origem:** Plano de testes definido pelo `bytest`, com base no documento de desenvolvimento aprovado (`docs/desenvolvimento/fornecedores-t42-t43-dev.md`, incluindo adendo de colunas 1:N) e no código efetivamente implementado e revisado (`NotifDesvio.php`, `NotifEvento.php`, `FornecNotifDesvioModel.php`, `FornecNotifEventoModel.php`, entities, migrations, views, `my_fornecedores.js`, `OcoTrataOcorrencia.php`).
**Status:** aguardando aprovação do `byarq` para execução.

Este documento define os casos de teste — a execução é etapa separada, registrada em `docs/testes/fornecedores-t42-t43-resultado-testes.docx`.

Legenda de prioridade: **P0** bloqueante (não pode ir a produção sem passar) · **P1** alta · **P2** média/monitoramento.

---

## 1. Introdução / Escopo

Este plano cobre as telas T42 (Fornecedores > Desvio de Qualidade) e T43 (Fornecedores > Notificação de Evento), abrangendo:

- Infraestrutura e permissões (`CAEXN`, fail-closed) em ambas as telas.
- Integração automática de origem T11 → T42 (RN02.3) e T42 → T43 (RN02.3, RN03.1).
- Regras de listagem, cadastro, validação de campos, salvar/cancelar e exclusão de T42.
- Regras de listagem (incluindo adendo de colunas 1:N), cadastro em abas (Dados Gerais, Providências, Parecer Final, Ações), upload de anexos, geração de movimentação e branch crítico de `store()`/`update()` em T43.
- Pontos de atenção explicitamente levantados pelo `byarq`: cenário de corrida em T43 (RN03.1), truncamento por `GROUP_CONCAT`, upload disfarçado de arquivo, geração de movimentação por produto, e o branch `update()` de `store()` em notificação já Concluída.

---

## 2. T42 — Desvio de Qualidade

### 2.1 Infraestrutura / Permissões (CAEXN)

| # | Caso | Passos | Dado de entrada | Resultado esperado | Prioridade |
|---|---|---|---|---|---|
| T42-01 | Acesso negado (fail-closed) | Logar com perfil sem linha em ConfigPerfilItemModel para T42 | Perfil qualquer sem permissão cadastrada | vw_semacesso (ou _modal), sem exibir dados; log de auditoria não deve registrar CRUD indevido | P0 |
| T42-02 | Permissão só-consulta (C) | Perfil com permissão "C" acessa index/lista/show e tenta acessar edit/delete via URL direta | perfil C | Consulta ok; edição/exclusão bloqueadas mesmo digitando a URL (LoginFilter, não só botão oculto) | P0 |
| T42-03 | Permissão completa (CAEXN) | Perfil com "CAEXN" | perfil completo | Todas as ações liberadas (respeitando ainda as regras de status) | P1 |
| T42-04 | Cadastro de cfg_tela/cfg_modulo/cfg_status | Conferir no banco se Fornecedores, T42, status Pendente/Concluída existem e tel_controler = NotifDesvio | — | Registros presentes, sem duplicidade de cfg_modulo "Fornecedores" | P1 |

### 2.2 Integração automática de origem (RN02.3)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T42-05 | Geração automática ao tratar ocorrência com ação "Notificação do Fornecedor" | Em T9, associar a ação nova (tpa_tipo=5) a um subtipo; em T11, tratar uma ocorrência desse subtipo executando a ação | Cria 1 registro em oco_notif_desvio com stt_id=Pendente, oco_id correto, usu_criou = usuário logado | P0 |
| T42-06 | Idempotência — reprocessamento do mesmo submit | Repetir o POST de tratativa de T11 para o mesmo oco_id já notificado (replay/duplo clique) | Não cria um segundo oco_notif_desvio para o mesmo oco_id | P0 |
| T42-07 | Status "Pendente" de T42 não configurado | Remover/renomear temporariamente o cfg_status "Pendente" de T42, disparar a ação em T11 | Retorna erro de negócio claro, não trava T11 silenciosamente nem estoura SQL cru | P1 |
| T42-08 | Ação nova aparece em T9 | Abrir cadastro de subtipo em T9 e conferir disponibilidade da ação | Ação selecionável normalmente | P2 |

### 2.3 Listagem (RN02.1, RN02.2, RN02.4)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T42-09 | Colunas da listagem | Abrir index de T42 | Colunas N°, Data, Produto, Fabricante, Lote, Usuário, Status, Ação | P1 |
| T42-10 | Coluna "Ação" não ordenável/pesquisável | Tentar ordenar/pesquisar pela coluna Ação | Não responde a ordenação/busca | P2 |
| T42-11 | Badge de status colorido | Conferir visualmente o status | Mesma cor/estilo de T1 | P2 |
| T42-12 | Botão Imprimir sempre disponível | Registro Pendente e Concluído | Aparece em ambos os casos | P1 |
| T42-13 | "Usuário" resolvido via log de T11 | Comparar usu_nome exibido com quem criou a ocorrência em T11 | Nome bate com log de oco_ocorrencia, não com usu_criou de oco_notif_desvio | P1 |

### 2.4 Cadastro — Aba Dados Gerais (RN03.1–RN03.14)

| # | Caso | Passos | Dado de entrada | Resultado esperado | Prioridade |
|---|---|---|---|---|---|
| T42-14 | Campos via T11 somente leitura | Abrir edit de registro Pendente | — | Todos os campos vindos de T11 com setLeitura(true), sem readonly manual | P1 |
| T42-15 | ndv_local obrigatório e limites | Salvar com vazio/4/5/50/51 caracteres | '', "abcd", "abcde", string(50), string(51) | Vazio e 4 → erro; 5 e 50 → ok; 51 → erro (client E server) | P0 |
| T42-16 | ndv_descreva obrigatório e limites | Mesma lógica, limites 5/200 | '', string(4), string(5), string(200), string(201) | Conforme limites | P0 |
| T42-17 | Editar via requisição forjada em registro Concluído | POST direto para store alterando ndv_id já Concluído | ndv_id de registro Concluído | Bloqueado no server-side, sem gravar | P0 |

### 2.5 Salvar / Cancelar (RN03.15–RN03.17)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T42-18 | Salvar grava e conclui na mesma transação | Preencher válido e Salvar | stt_id muda direto para Concluída | P0 |
| T42-19 | Impressão oferecida após Salvar, não bloqueante | Salvar registro válido | Fluxo de impressão oferecido em seguida; fechar sem imprimir não desfaz conclusão | P1 |
| T42-20 | Salvar sem alteração real → MSG 7 | Abrir edit, não alterar, Salvar | Exibe MSG ID 7, não grava novamente | P1 |
| T42-21 | Cancelar com alteração pendente → MSG 2 | Alterar campo, Cancelar | Confirmação MSG ID 2 | P1 |
| T42-22 | Falha simulada de update (rollback) | Forçar erro no update() | transRollback, retorno amigável, stt_id permanece Pendente | P1 |

### 2.6 Exclusão (bloqueio) e Etiqueta

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T42-23 | delete() sempre bloqueado | POST direto para delete/{id}, qualquer perfil | Erro "Exclusão não permitida"; registro não removido | P0 |
| T42-24 | GeraEtiqueta() — 0 etiquetas | Sem cfg_etiqueta para T42, clicar Imprimir | Erro amigável | P1 |
| T42-25 | GeraEtiqueta() — 1 etiqueta | Com 1 cfg_etiqueta | Usa direto, abre preview correto | P1 |
| T42-26 | GeraEtiqueta() — 2+ etiquetas | Com 2 cfg_etiqueta | boxAlert oferece seleção | P2 |
| T42-27 | Cache Redis da etiqueta expira | Gerar etiqueta, aguardar >900s, reimprimir | Regenera cache sem erro | P2 |

---

## 3. T43 — Notificação de Evento

### 3.1 Infraestrutura / Permissões (CAEXN)

| # | Caso | Resultado esperado | Prioridade |
|---|---|---|---|
| T43-01 | Acesso negado (fail-closed) | vw_semacesso, sem exibir nada | P0 |
| T43-02 | Perfil só C tenta acessar add/selecionaProdutos/store/delete via URL direta | Bloqueado no back mesmo sem botão na UI | P0 |
| T43-03 | Botão "+" na listagem | Aparece só para perfil com permissão A | P2 |

### 3.2 Integração automática T42→T43 e seleção de produtos (RN02.3, RN03.1)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-04 | Só T42 Concluídos aparecem disponíveis | Ter registros T42 Pendente e Concluído | Só os Concluídos e ainda não vinculados aparecem | P0 |
| T43-05 | Produto já vinculado não aparece de novo | Vincular ndv_id a uma notificação, abrir add() novamente | Não aparece mais | P0 |
| T43-06 | Pré-seleção por "mesmo fabricante" | Abrir add() com disponíveis de 2+ fabricantes | Validar semântica exata de RN03.1 — reportar se divergir do documento | P1 |
| T43-07 | Nenhum disponível | Sem T42 Concluído/não vinculado | Mensagem de negócio via boxAlert, não tela vazia/erro | P1 |
| T43-08 | Coluna "Usuário" na seleção | Conferir nome exibido | Resolvido via log de T11 | P2 |

### 3.3 Cenário de corrida — RN03.1 (exigência explícita do byarq)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-09 | Duas abas selecionam o mesmo ndv_id simultaneamente | 2 sessões, mesmo produto, store() em paralelo | Uma grava com sucesso; outra recebe mensagem de negócio clara via boxAlert, NUNCA erro de SQL cru | P0 |
| T43-10 | Janela exata entre validaDisponibilidade() e insertReg() | Forçar ambas transações chegarem ao insertReg() antes do commit da primeira | Confirmar se o catch genérico de NotifEvento::store() deixa vazar mensagem técnica de SQL quando a UNIQUE KEY barra o INSERT — bug real se vazar | P0 |
| T43-11 | Mesmo teste com DBDebug=false (produção) | Repetir T43-09/10 com debug de banco desligado | Documentar diferença de comportamento dev × produção | P1 |
| T43-12 | Duplo clique no botão Salvar (mesma aba) | Clicar Salvar duas vezes rapidamente | Front deve bloquear reenvio (debounce); se não, cai no caminho de corrida | P1 |

### 3.4 Aba 1 — Dados Gerais (RN03.2–RN03.13)

| # | Caso | Dado de entrada | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-13 | Grid de produtos read-only via JOIN | — | Campos não editáveis, vindos de T42/T11 | P1 |
| T43-14 | nvp_defeito — valor inicial copiado, editável | Abrir selecionaProdutos() | Nasce preenchido com texto de T42, mas alterável | P0 |
| T43-15 | nvp_defeito obrigatório, 5–200, por linha | vazio, 4, 5, 200, 201 chars, múltiplas linhas | Conforme limites, validado por linha | P0 |
| T43-16 | nev_qtd_adquirida obrigatório, numérico, até 5 dígitos | '', "abc", 12345, 123456 | Conforme limites | P0 |
| T43-17 | nev_numero_nf obrigatório, numérico, até 20 dígitos | '', 21 dígitos, 20 dígitos | Conforme limites | P0 |
| T43-18 | nev_fornecedor obrigatório, texto 5–200 | '', 4, 5, 200, 201 chars | Conforme limites | P0 |
| T43-19 | nev_fabricacao obrigatório, data válida | '', data inválida, data válida | Vazio/inválida → erro | P0 |
| T43-20 | Notificação com muitos produtos (grid grande) | 20+ produtos de uma vez | Renderiza tudo, sem perda de dado no submit | P1 |

### 3.5 GROUP_CONCAT / truncamento na listagem (exigência explícita do byarq)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-21 | Volume realista de produtos por notificação | 10–30 produtos, nomes 30–50 chars cada | resumeListaComTooltip() monta corretamente, sem string cortada | P1 |
| T43-22 | Forçar truncamento por group_concat_max_len | Reduzir group_concat_max_len para valor baixo (1024) e repetir | Verificar se GROUP_CONCAT trunca no meio de um nome/separador, quebrando o explode() | P0 se reproduzir com valor default do servidor de produção; P2 se só com valor artificialmente baixo |
| T43-23 | Conferir group_concat_max_len real (dev/produção) | SHOW VARIABLES LIKE 'group_concat_max_len' | Documentar valor; sinalizar risco se baixo | P1 |
| T43-24 | Separador \x1F literal em nome de produto/lote | Produto/lote com esse caractere no nome | Comportamento indefinido esperado — reportar como limitação conhecida | P2 |

### 3.6 Aba 2 — Providências (RN03.14–16) e Aba 3 — Parecer Final (RN03.17–20)

| # | Caso | Dado de entrada | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-25 | nev_providencias obrigatório, 5–500 | vazio, 4, 5, 500, 501 | Conforme limites | P0 |
| T43-26 | nev_notificado obrigatório, 5–200 | vazio, 4, 5, 200, 201 | Conforme limites | P0 |
| T43-27 | nev_parecer obrigatório, 5–500 | vazio, 4, 5, 500, 501 | Conforme limites | P0 |
| T43-28 | Toggle nev_notivisa nasce desmarcado (bug corrigido) | Abrir com registro nev_notivisa='N' | Checkbox nasce desmarcado — validar regressão do fix recente | P0 |
| T43-29 | nev_notivisa_num obrigatório só se notivisa=S | Marcar Sim sem número; 4 chars; 5-50 chars; Não vazio; Não preenchido | Client exige só quando marcado; Server bloqueia mesmo bypassando client | P0 |
| T43-30 | Contorno client-side de RN03.19 | POST direto com notivisa=S e num ausente | Bloqueado no server, não grava | P0 |
| T43-31 | Upload de anexo válido (Providências) | .pdf/.png/.jpeg/.jpg reais | Grava com nva_origem='PROVID' | P0 |
| T43-32 | Upload de anexo válido (Parecer Final) | Mesmo, nva_origem='PARECER' | Grava distinto do bloco Providências | P0 |
| T43-33 | Múltiplos anexos, adicionar/excluir linha antes de salvar | 3 linhas, remover 1 | Só 2 restantes são enviados | P1 |
| T43-34 | Excluir anexo já persistido (deleteAnexo) | Clicar excluir em anexo salvo | Confirmação via boxAlert, remove arquivo+linha; validar se deveria checar status antes | P1 |

### 3.7 Upload disfarçado — extensão/MIME (exigência explícita do byarq)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-35 | .php renomeado para .pdf | Upload via POST direto | MIME real via finfo detecta, bloqueado, arquivo não movido para disco | P0 |
| T43-36 | .jpg renomeado para .pdf (mismatch extensão/mime) | Renomear jpg legítimo | Validar se a checagem atual (extensão E mime, mas não pareados) deixa passar essa combinação — reportar se passar | P1 |
| T43-37 | Extensão maiúscula (.PDF, .JPG) | Upload | Aceito (strtolower já usado) | P2 |
| T43-38 | Tipo não permitido (.exe, .docx, .zip) | Upload direto | Bloqueado por extensão e mime | P1 |
| T43-39 | Upload sem arquivo | Submeter sem anexar | Não obrigatório, grava normalmente | P1 |

### 3.8 Aba 4 — Ações (RN03.21, RN03.22)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-40 | Ação obrigatória, múltiplas linhas | Salvar sem nenhuma ação cadastrada | Validar se RN03.21 é de fato bloqueado — hoje pode não haver checagem explícita de "pelo menos 1 ação" — reportar se salvar sem ação | P0 |
| T43-41 | Adicionar/excluir linha de ação | addCampoAcao/bt-repete | Novas linhas ok; campos condicionais aparecem conforme tpa_tipo | P1 |
| T43-42 | Execução sequencial por nac_ordem | 3 ações fora de ordem de digitação | Executa na sequência de nac_ordem, não na ordem de digitação | P0 |
| T43-43 | Ação "Alterar Status" (tpa_tipo=4) | Cadastrar com stt_id | Só registra, sem efeito colateral extra | P2 |
| T43-44 | Ação "Justificar"/"Abrir Tela" (tpa_tipo 1/2) | Cadastrar | Sem efeito colateral, só registro | P2 |

### 3.9 RN03.22 — Geração de movimentação por produto (exigência explícita do byarq)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-45 | Iteração por produto/lote (dry-run/mock) | Mockar geraMovimentoRequisicoes(), 3+ produtos, ação Gerar Movimentação | Array com 1 entrada por produto, valores corretos por linha | P0 (lógica) |
| T43-46 | Chamada real a geraMovimentoRequisicoes() — SOAP com ERP | NÃO EXECUTAR sem coordenação prévia com o usuário e ambiente controlado (homologação, nunca produção) | Quando autorizado: movimentação real corresponde 1:1; SoapFault tratado com rollback | P0 — EXECUTAR COM CUIDADO, coordenar com usuário antes |
| T43-47 | Idempotência — não duplicar movimentação | Repetir store() para o mesmo nev_id | Não existe caminho que reexecute a movimentação duas vezes | P0 |

### 3.10 Salvar/Cancelar e branch update() (ponto crítico do byarq)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-48 | Salvar sem alteração → MSG 7 | Abrir cadastro, não alterar, Salvar | MSG ID 7 | P1 |
| T43-49 | Cancelar com alteração → MSG 2 | Alterar campo, Cancelar | Confirmação MSG ID 2 | P1 |
| T43-50 | Forçar branch update() do store() — validar se é "inalcançável" | 1) Tentar edit() de notificação Concluída pela UI (deve estar oculto). 2) Forjar POST direto para edit/{id} Concluída. 3) Se bloqueado, forjar POST direto para store com nev_id existente + payload completo, contornando edit() inteiramente | Passo 3 é o teste crítico: store() pode não checar status antes do branch update(), diferente do edit(). Se passo 3 conseguir alterar/apagar produtos e ações de notificação já Concluída, é BUG REAL de segurança (mesmo gap já corrigido em T11 RN04.1/RN05.1) | P0 — validar concretamente, não assumir |

### 3.11 Listagem — Adendo colunas 1:N (RN02.1, RN02.2, RN02.4, RN03.1, RN03.2)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|---|
| T43-51 | 1 linha por notificação, nunca por produto | Notificação com 5 produtos | 1 linha na listagem, não 5 | P0 |
| T43-52 | Coluna Produto — "1º + e mais N" com tooltip | Notificação com 3 produtos | "Produto X e mais 2"; tooltip com lista completa | P0 |
| T43-53 | Coluna Lote — mesmo tratamento | Lotes distintos | "Lote X e mais N" + tooltip | P0 |
| T43-54 | Coluna Fabricante — sempre único, sem tooltip | Produtos do mesmo fabricante (regra RN03.1) | Mostra fabricante único, sem "e mais N" | P1 |
| T43-55 | Coluna Data — MIN do conjunto | Datas diferentes vinculadas | Listagem mostra data mais antiga; grid da aba mostra todas | P1 |
| T43-56 | Notificação com exatamente 1 produto | — | Só o nome, sem "e mais 0"/tooltip desnecessário | P2 |
| T43-57 | XSS em nome de produto/lote na célula/tooltip | Produto/lote de teste com <script>/HTML no nome | esc() aplicado em ambos os pontos — sem execução de script | P0 |

### 3.12 Botão Imprimir (RN04.1, RN04.2)

| # | Caso | Resultado esperado | Prioridade |
|---|---|---|---|
| T43-58 | Clique Imprimir com 0/1/2+ etiquetas | Mesmo comportamento de T42 | P1 |
| T43-59 | Fechar modal de impressão não altera status/dado | Status permanece Concluída | P0 |

### 3.13 Performance — SELECT DISTINCT sobre a view (monitoramento, não bug)

| # | Caso | Passos | Resultado esperado | Prioridade |
|---|---|---|---|
| T43-60 | Tempo de resposta em volume realista | Popular com volume real/amostra, medir tempo de lista() de T42 e T43 | Registrar tempo; não é critério de aprovação, é monitoramento pós-entrega | P2 |

---

## 4. Rastreabilidade

- T42: RN02.1–RN02.5 (T42-09 a T42-13), RN03.1–RN03.17 (T42-14 a T42-22)
- T43: RN02.1–RN02.5 (T43-04, T43-51 a T43-56), RN03.1–RN03.23 (T43-04 a T43-50, T43-58/59), RN04.1–RN04.2 (T43-58/59)
- Segurança server-side: T42-17, T42-23, T43-02, T43-30, T43-50, T43-57
- Idempotência: T42-06, T43-47

---

## 5. Observações para o byarq

1. T43-50 é o caso mais crítico: store()/branch update() pode não validar status antes de apagar/regravar produtos e ações — só edit() bloqueia o caminho normal da UI. Tratar como suspeita de bug até execução comprovar o contrário.
2. T43-09/10 (corrida) e T43-22 (truncamento) dependem de reproduzir condições específicas — se não reproduzirem, documentar como "não reproduzido, risco residual conhecido", não marcar como "passou".
3. T43-46 (SOAP real) não deve ser executado sem autorização explícita do usuário e ambiente controlado.
4. T43-06 (pré-seleção por fabricante) e T43-40 (ação obrigatória) podem revelar divergência entre leitura literal do documento e comportamento implementado — reportar como achado, não assumir que o código está certo.
