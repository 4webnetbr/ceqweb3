# Documento de Entrega — Módulo Fornecedores (T42, T43)

**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores — T42 (Desvio de Qualidade) · T43 (Notificação de Evento)
**Documento de origem:** `docs/desenvolvimento/fornecedores-t42-t43-dev.md` (aprovado, plano original + 5 adendos do `byarq`)
**Documentos de ciclo relacionados:** `docs/revisao/fornecedores-t42-revisao-01.md`, `docs/revisao/fornecedores-t43-revisao-01.md`, `docs/testes/fornecedores-t42-t43-plano-testes.md`, `docs/testes/fornecedores-t42-t43-resultado-testes.md`
**Status final:** **Pronto para produção, com ressalvas** — ver Seção 3 (checklist de deploy), que lista pendências explícitas do usuário e itens de infraestrutura fora de escopo.

------------------------------------------------------------------------

## 1. Resumo Executivo

Foram desenvolvidas duas telas novas do módulo Fornecedores (módulo inexistente até este ciclo):

- **T42 — Desvio de Qualidade** (`NotifDesvio`): registra o desvio de qualidade identificado a partir de uma ocorrência tratada em T11 (Gestão de Ocorrências), via nova ação de T8/T9 ("Notificação do Fornecedor"). Aba única de dados, gravação já conclui o registro, impressão de etiqueta oferecida em seguida.
- **T43 — Notificação de Evento** (`NotifEvento`): formaliza a notificação ao fornecedor a partir de um ou mais registros Concluídos de T42, com seleção de produtos por fabricante, cadastro em 4 abas (Dados Gerais, Providências, Parecer Final, Ações), upload de anexos, execução sequencial de ações configuráveis (incluindo geração de movimentação no ERP) e impressão de etiqueta via botão na listagem.

O ciclo completo foi executado conforme o fluxo padrão do time: planejamento aprovado pelo `byarq`, codificação pelo `bydev`, uma rodada de revisão de código por tela (`byrev`) com bloqueantes corrigidos e reconfirmados, e três rodadas de testes funcionais reais (`bytest`) com todos os bugs de conteúdo/regra de negócio encontrados corrigidos e reconfirmados por reexecução (não apenas leitura de código).

**Status funcional:** todos os casos de teste P0 de conteúdo/regra de negócio aplicáveis passam. Não restam bloqueadores técnicos de funcionalidade. As ressalvas que seguem para produção são: (a) um incidente de execução acidental de migration em produção durante o desenvolvimento, com artefatos deixados por decisão do usuário; (b) um achado de segurança pré-existente (`LoginFilter`) aceito conscientemente sem correção neste ciclo; (c) itens de configuração administrativa (etiquetas, permissões) que precisam ser cadastrados manualmente antes da tela funcionar em produção; (d) testes de integração SOAP real e da família de impressão de etiqueta não executados por limitação de ambiente/escopo, pendentes de validação futura.

------------------------------------------------------------------------

## 2. Arquivos Criados/Alterados (ciclo completo)

### 2.1 Migrations

| Arquivo | Conteúdo |
|---|---|
| `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php` | Infraestrutura base: `cfg_modulo` (Fornecedores), `cfg_tela` (T42 tel_id=68, T43 tel_id=69), `cfg_status` (Pendente/Concluída de cada tela), `oco_tipo_acao` (nova ação "Notificação do Fornecedor", tpa_id=13), tabelas `oco_notif_desvio` e `oco_notif_evento` (+ views de apoio `vw_oco_notif_desvio_relac`/`vw_oco_notif_evento_relac`). Corrigida após revisão para incluir `cfg_tela_lista` (colunas da listagem de T42) — ver Seção 5. |
| `app/Database/Migrations/2026-07-12-000002_FornecedoresT43Infra.php` | Tabelas `oco_notif_evento_produto`, `oco_notif_evento_anexo`, `oco_notif_evento_acao`, com FKs para `oco_notif_evento`, `oco_notif_desvio`, `oco_tipo_acao`, `cfg_status`. |
| `app/Database/Migrations/2026-07-12-000003_FornecedoresT43ProdutoUnique.php` | Correção pós-revisão (Bloqueante 4 de `byrev` T43): altera `oco_notif_evento_produto.ndv_id` de índice comum para `addUniqueKey('ndv_id')` — proteção de banco contra vínculo duplicado do mesmo produto a duas notificações (RN03.1). |

### 2.2 Backend — Entities

- `app/Entities/Fornecedores/EntOcoNotifDesvio.php`
- `app/Entities/Fornecedores/EntOcoNotifEvento.php`
- `app/Entities/Fornecedores/EntOcoNotifEventoProduto.php`
- `app/Entities/Fornecedores/EntOcoNotifEventoAcao.php`

### 2.3 Backend — Models

- `app/Models/Fornec/FornecNotifDesvioModel.php` (inclui `getDisponiveisParaNotificacao()` — filtro de elegibilidade RN03.1)
- `app/Models/Fornec/FornecNotifEventoModel.php`

### 2.4 Backend — Controllers

- `app/Controllers/Fornecedores/NotifDesvio.php` (T42 completo: `index`/`lista`/`show`/`edit`/`store`/`delete`/`GeraEtiqueta`)
- `app/Controllers/Fornecedores/NotifEvento.php` (T43 completo: listagem com agregação 1:N, `selecionaProdutos`, `store` com validações e execução sequencial de ações, `salvaAnexos`/`deleteAnexo`, `GeraEtiqueta`)
- `app/Controllers/Ocorrencia/OcoTrataOcorrencia.php` — alterado para incluir o `case` da nova ação (tpa_tipo=5, "Notificação do Fornecedor"): método `gerarNotificacaoDesvio()`, que cria automaticamente o registro em `oco_notif_desvio` a partir de T11.
- `app/Controllers/MyValidation.php` — nova regra customizada `obrigatorioSeNotivisaSim` (validação condicional de `nev_notivisa_num`, RN03.19).

### 2.5 Frontend — Views/Partials

- `app/Views/partials/pw_selecao_produtos_notif.php` (tela intermediária de seleção de produtos por fabricante, T43)
- `app/Views/partials/pw_grid_produtos_notif.php` (grid multi-linha de produtos na aba Dados Gerais, T43)
- `app/Views/partials/pw_acoes_notif.php` (aba Ações, T43)
- `app/Views/partials/pw_anexos_notif.php` (upload/lista de anexos, abas Providências e Parecer Final, T43)

### 2.6 Frontend — JavaScript

- `public/assets/jscript/my_fornecedores.js` — funções de front específicas do módulo: `geraEiquetaGenerico()` (mirror de `geraEiquetaProd()` para etiqueta de T42/T43), `mostraNotivisaNum()` (callback do toggle Notivisa, RN03.18/19 — adicionada após revisão), `excluiAnexoExistente()` (exclusão de anexo já persistido via `boxAlert`/`executaAjax` — adicionada após revisão).

### 2.7 Configuração

- `app/Config/Routes.php` — grupo de rotas `Fornecedores\NotifDesvio`/`Fornecedores\NotifEvento` (padrão `(:any)` já usado por outros módulos) + rotas específicas `GeraEtiqueta/(:num)/(:num)` para cada controller.

------------------------------------------------------------------------

## 3. Checklist de Deploy para Produção

### 3.1 Migrations a rodar em produção — ORDEM E ATENÇÃO CRÍTICA

Rodar, **nesta ordem**:

1. `2026-07-12-000001_FornecedoresT42T43.php`
2. `2026-07-12-000002_FornecedoresT43Infra.php`
3. `2026-07-12-000003_FornecedoresT43ProdutoUnique.php`

> **ATENÇÃO — antes de rodar a migration 000001 em produção, ela precisa ser CONFERIDA manualmente no banco de produção.** Durante este ciclo de desenvolvimento, a migration 000001 **já foi executada acidentalmente em produção uma vez** (ver incidente detalhado na Seção 3.3). Isso significa que parte do que ela cria (módulo, telas, status, ação T8, tabelas vazias) **pode já existir em produção**. Rodar a migration novamente sem conferir pode gerar erro de duplicidade (ex.: tentativa de recriar `cfg_modulo`/`cfg_tela` já existentes) ou, pior, duplicar dados de configuração. **Verificar linha a linha no banco de produção (via HeidiSQL/DBGate) o que já existe antes de autorizar a reexecução.**

### 3.2 Configuração administrativa pendente (dados, não migration)

Nenhum destes itens é código — são cadastros manuais nas telas de administração já existentes do sistema, e **sem eles as telas não funcionam** em produção:

- **Etiquetas de impressão:** cadastrar `cfg_etiqueta` / `cfg_layout_etiqueta` / `cfg_etiqueta_campo` para T42 (tel_id=68) e T43 (tel_id=69), via `Config\CfgEtiqueta` / `Config\CfgLayoutEtiq`. Sem isso, o botão Imprimir de ambas as telas retorna erro ("0 etiquetas cadastradas") — mecanismo de impressão reaproveitado, mas o dado de layout precisa existir.
- **Permissões `CAEXN`:** cadastrar em `cfg_perfil_item` as permissões das telas T42/T43 para os perfis de Materiais/Qualidade em produção. Conforme `rascunho-helpers-php.md`, ausência de permissão é *fail-closed* — sem esse cadastro, as telas ficam **inacessíveis para todos os perfis**, inclusive quem deveria ter acesso.
- **Associação da ação nova em T9:** a ação "Notificação do Fornecedor" (T8, `tpa_id=13`) precisa ser associada, em T9 (`oco_subt_ocorrencia_acao`), aos subtipos de ocorrência relevantes de cada cliente/ambiente de produção. Sem essa associação, a integração automática T11→T42 nunca dispara.

### 3.3 Incidente de produção durante o desenvolvimento — registro objetivo

Durante o ciclo, a migration `2026-07-12-000001_FornecedoresT42T43.php` **rodou uma vez em produção por engano**.

**Causa raiz:** o arquivo `.env` do ambiente não tinha override de `database.prefixo`/`database.prefuser`, fazendo a aplicação cair no valor padrão definido em `Config/Database.php` (`'prd_'`), isto é, apontando para o prefixo de produção mesmo em execução que deveria ser de desenvolvimento.

**O que foi criado em produção por esse engano:**
- Módulo "Fornecedores" em `cfg_modulo` (id = 25).
- 2 telas em `cfg_tela` (id = 68 e 69 — T42 e T43).
- 4 status em `cfg_status` (ids 31 a 34 — Pendente/Concluída de cada tela).
- 6 linhas em `cfg_tela_lista` (colunas de listagem).
- 1 ação em `oco_tipo_acao` (`tpa_id = 13`, "Notificação do Fornecedor").
- As 5 tabelas novas do módulo, **vazias**: `oco_notif_desvio`, `oco_notif_evento`, `oco_notif_evento_produto`, `oco_notif_evento_anexo`, `oco_notif_evento_acao`.

**Correção aplicada:** o `.env` já foi corrigido, com `database.prefixo='dev_'` / `database.prefuser='dev_'` explícitos, eliminando o risco de recorrência.

**Decisão do usuário — PENDÊNCIA EXPLÍCITA:** o usuário decidiu **deixar esses artefatos em produção por enquanto**, para limpeza manual posterior. Isso **não foi resolvido neste ciclo** — fica registrado aqui como pendência explícita, para não ser esquecido. Antes de rodar a migration 000001 "de verdade" em produção (item 3.1), é necessário decidir se esses artefatos já existentes serão reaproveitados (e a migration ajustada/pulada para não duplicar) ou removidos primeiro.

### 3.4 Achados de segurança/qualidade aceitos sem correção neste ciclo

Registrados como pendência consciente, não como "resolvido":

- **BUG #1 — `app/Filters/LoginFilter.php::validaPermissao()` não protege o método `store()` por permissão.** O filtro só verifica a permissão explicitamente para os métodos `add` (exige `A`) e `edit` (exige `E`); qualquer outro método, inclusive `store` (destino real de toda gravação, criação e edição), só exige que a permissão não seja vazia. Confirmado em runtime: um perfil com permissão só `C` (consulta) conseguiu concluir um Desvio de Qualidade via POST forjado direto a `store()`. É uma falha **pré-existente, compartilhada por todo o sistema**, não introduzida por T42/T43 — mas as regras de negócio de ambas as telas pressupõem esse controle. **O usuário decidiu não corrigir neste ciclo.** Recomendação: tratar em rodada futura dedicada de hardening do `LoginFilter`, especialmente antes de expor a aplicação a perfis externos/menos confiáveis.
- **Achados pré-existentes fora de escopo, encontrados durante o desenvolvimento (apenas registrados, não corrigidos):**
  - `LogMonModel::insertLog()` (e `update_log`/`delete_log`) chama `show_error()` — função de CodeIgniter 3, inexistente no CI4 — gerando erro fatal (HTTP 500) em qualquer INSERT/UPDATE/DELETE do sistema caso o MongoDB fique indisponível. Código compartilhado por praticamente todos os Models, não específico de T42/T43.
  - `vw_cfg_status_relac.stt_cor` está sempre vazio para qualquer status do sistema (não só T42/T43).
  - `vw_oco_ocorrencia_completa_relac` duplica linhas por `LEFT JOIN` mal formado — hoje mascarado porque todo o código existente usa `getRow()` (pega só a primeira linha), mas é um risco latente para qualquer código futuro que use `getResult()`/`findAll()` sobre essa view.
  - Um `tpa_tipo` gravado como `NULL` foi encontrado em produção durante o desenvolvimento; a causa raiz **não foi identificada**. Uma autocorreção defensiva foi deixada na migration (000001) para contornar o sintoma, mas a causa não foi encontrada — recomenda-se abrir item de acompanhamento futuro.

### 3.5 Testes não executados nesta rodada, pendentes de validação futura

- **T43-46 (chamada real a `geraMovimentoRequisicoes()`, integração SOAP com o ERP)** — não executado por decisão explícita do usuário (evitar acionar integração real sem coordenação prévia). Precisa ser validado em ambiente de homologação controlado, com autorização prévia, antes de confiar 100% no comportamento em produção. A lógica de montagem do array de movimentação por produto (T43-45) foi confirmada apenas por leitura de código, não por execução real.
- **Família `GeraEtiqueta()` (T42-24 a T42-27, T43-58/T43-59)** — não testada em runtime por ausência da extensão PHP Redis no ambiente de teste local (`Class "Redis" not found`). Recomenda-se reexecutar essa família de testes assim que o ambiente de teste tiver Redis habilitado.
- **Cenário de corrida T43-10 (janela exata entre `validaDisponibilidade()` e o `INSERT`)** — não reproduzido por limitação do servidor de desenvolvimento (PHP CLI single-threaded, incapaz de forçar duas transações a colidirem no instante exato). Risco residual conhecido, não bloqueante. **T43-09** (versão realista de concorrência, com duas sessões reais) foi executado e passou corretamente, sem vazamento de erro de SQL cru.

### 3.6 Fixtures de teste no banco de dev

Criados durante a execução dos testes, **permanecem no banco de desenvolvimento**:

- Perfis: `QA_T42T43_Full` (prf_id=12), `QA_T42T43_ConsultaSo` (prf_id=13), `QA_T42T43_SemAcesso` (prf_id=14).
- Usuários: `qa.full`, `qa.consulta`, `qa.semacesso`.
- Registros de teste em `oco_notif_desvio` (`ndv_id` 11–32) e `oco_notif_evento` (`nev_id` 5–10 em diante), com respectivos produtos/ações/anexos vinculados.

**Recomendação:** remover esses fixtures antes de qualquer carga de dados "limpa" para homologação ou produção.

------------------------------------------------------------------------

## 4. Decisões de Arquitetura Importantes (resumo)

Detalhamento completo em `docs/desenvolvimento/fornecedores-t42-t43-dev.md`. Resumo das decisões mais relevantes para quem for dar manutenção:

- **Mecanismo de impressão = etiqueta, não relatório.** Onde o requisito original menciona "relatório", a decisão final foi reaproveitar o mecanismo de etiqueta já existente (`gerarEtiquetaZPL()` / `buscaetiqcontroler` / `CriaEtiquetaZPL::emiteEtiqueta`), com `GeraEtiqueta($id)` em cada controller como mirror de `Estoque\EtqProduto::GeraEtiqueta()` (cache Redis, TTL 900s).
- **Status Pendente/Concluída como `cfg_status` próprios de cada tela nova** — T42 e T43 têm seus próprios status escopados por `tel_id`, seguindo o padrão do sistema (não reaproveitam status de outras telas).
- **Gatilho de "Concluída" no clique de Salvar**, não no fechamento do modal de impressão — o Salvar grava e conclui na mesma transação; a impressão é oferecida em seguida, de forma não bloqueante.
- **Ação nova em T8 ("Notificação do Fornecedor", tpa_id=13), configurável por subtipo em T9** — reaproveita 100% o mecanismo existente de `oco_subt_ocorrencia_acao`, sem alteração de schema em T9.
- **Listagem de T43 com agregação "1º item + e mais N"** para colunas 1:N (Produto/Lote), com lista completa disponível via tooltip `<ttp>` (mecanismo já existente de `montaListaDados()`/`my_lista.js`) — evita duplicar linha de listagem por produto vinculado, mantendo a semântica de 1 linha = 1 notificação.
- **RN03.1 de T43 (elegibilidade de produtos para notificação): produto disponível = Concluído em T42 + ainda não vinculado a nenhuma notificação** — protegido em duas camadas: `UNIQUE KEY(ndv_id)` em `oco_notif_evento_produto` (migration 000003, adicionada após revisão) e revalidação de elegibilidade dentro da transação do `store()`, cobrindo a janela de corrida entre a seleção e a gravação.

------------------------------------------------------------------------

## 5. Histórico do Ciclo de Revisão e Testes (resumo gerencial)

### 5.1 Revisão de código (`byrev`)

| Tela | Rodadas | Achados bloqueantes | Status |
|---|---|---|---|
| T42 | 1 | 3 bloqueantes + 2 sugestões | Todos os 3 bloqueantes corrigidos e reconfirmados. |
| T43 | 1 | 4 bloqueantes + 6 sugestões | Todos os 4 bloqueantes corrigidos e reconfirmados. |

**Principais achados corrigidos:**

| Rodada | Achado | Severidade | Status |
|---|---|---|---|
| T42-rev01 | Migration não cadastrava `cfg_tela_lista` — listagem ficava sem colunas | Bloqueante | Corrigido |
| T42-rev01 | Migration não cadastrava permissões `CAEXN` | Bloqueante | Corrigido (definição dos `prf_id` exatos de produção fica pendente — ver 3.2) |
| T42-rev01 | `gerarNotificacaoDesvio()` gravava log de auditoria com ID errado (`oco_id` em vez de `ndv_id`) via `CommonModel::insertReg()`, perdendo timestamp de criação | Bloqueante | Corrigido — trocado para `FornecNotifDesvioModel::insert()` |
| T42-rev01 | Idempotência de `gerarNotificacaoDesvio()` só em nível de aplicação | Sugestão | Aceita — reforçada com verificação adicional |
| T43-rev01 | `mostraNotivisaNum()` e `excluiAnexoExistente()` chamadas em JS mas nunca implementadas | Bloqueante | Corrigido — funções implementadas em `my_fornecedores.js` |
| T43-rev01 | RN03.19 (`nev_notivisa_num` obrigatório se Notivisa=Sim) não validada no servidor | Bloqueante | Corrigido — regra `obrigatorioSeNotivisaSim` |
| T43-rev01 | Upload de anexo sem validação de tipo/MIME no servidor | Bloqueante | Corrigido |
| T43-rev01 | RN03.1 não revalidada no `store()`, sem proteção de banco contra vínculo duplicado | Bloqueante | Corrigido — `UNIQUE KEY(ndv_id)` (migration 000003) + revalidação em transação |

Após as correções, `byrev` não apontou mais nada a contribuir em nenhuma das duas telas — ciclo de revisão fechado.

### 5.2 Testes (`bytest`)

Executadas 3 rodadas de teste funcional real (login, requisições HTTP autenticadas, gravação em banco):

| Rodada | Objetivo | Resultado |
|---|---|---|
| 1 | Execução inicial do plano completo | Identificado BUG #2 (erro fatal em `NotifEvento::store()` por regra de validação mal formatada) bloqueando toda a bateria de T43. |
| 2 | Reexecução dos P0 de T43 após correção do BUG #2; execução ampla do restante do plano | BUG #2 confirmado corrigido. Encontrados 3 novos achados: ACHADO #1 (`nvp_defeito` sem validação server-side, RN03.9), ACHADO #2 (data de fabricação inválida aceita e persistida como `0000-00-00`, RN03.13), ACHADO #3 (ação obrigatória não era enforced, RN03.21). |
| 3 | Reexecução dos 3 achados da Rodada 2, após correção | Os 3 achados confirmados corrigidos, sem regressão nos casos adjacentes (inclusive reconfirmação de T43-42, execução sequencial por `nac_ordem`, e do caminho normal completo de `store()`). |

**Resultado final:** T42 — 8 de 9 casos P0 aplicáveis passam (1 falha é o BUG #1, aceito). T43 — 26 casos P0 de conteúdo/regra de negócio passam; 1 falha é o mesmo BUG #1; 1 não reproduzido por limitação de ambiente (T43-10, risco residual conhecido); 1 mantido como verificação estática por restrição de escopo (T43-45, SOAP não acionado). Nenhum bloqueador técnico de funcionalidade remanescente.

------------------------------------------------------------------------

## 6. Conclusão

O desenvolvimento de T42 e T43 está funcionalmente completo, revisado e testado conforme o documento de desenvolvimento aprovado. A entrega está **pronta para produção**, condicionada à execução completa do checklist da Seção 3 — em especial a conferência cuidadosa da migration 000001 contra o que já existe em produção (incidente da Seção 3.3) e o cadastro administrativo de etiquetas/permissões/associação de ação (Seção 3.2), sem os quais as telas não funcionam corretamente mesmo com o código já em produção.
