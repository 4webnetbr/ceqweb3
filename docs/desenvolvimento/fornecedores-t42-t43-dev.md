# Documento de Desenvolvimento — Módulo Fornecedores (T42, T43)

**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores (novo — `app/Controllers/Fornecedores`, a criar)
**Telas envolvidas:** T42 — Desvio de Qualidade · T43 — Notificação de Evento
**Tipo de trabalho:** Telas novas (não é evolução de código existente).
**Origem:** Plano de arquitetura aprovado pelo `byarq` (plano original + 5 adendos).
**Status:** Aprovado para codificação.

------------------------------------------------------------------------

## 1. Objetivo

Implementar as telas T42 (Fornecedores > Desvio de Qualidade) e T43 (Fornecedores > Notificação de Evento), que formalizam o tratamento de desvios de qualidade identificados em ocorrências (T11 — Gestão de Ocorrências) e a notificação formal de eventos a fornecedores, incluindo geração de etiqueta, controle de status e execução de ações configuráveis (reaproveitando o catálogo de ações T8/T9 já existente no módulo Ocorrências).

Este documento consolida o plano do `byarq` (documento original + 5 adendos que fecham as decisões pendentes) para uso direto do `bydev` na codificação — contém todos os campos, tabelas, regras e decisões necessárias, sem necessidade de consultar o requisito original em paralelo.

------------------------------------------------------------------------

## 2. Escopo

**Dentro do escopo:** - Cadastro de infraestrutura: módulo “Fornecedores” em `cfg_modulo`, telas T42/T43 em `cfg_tela`, status Pendente/Concluída por tela em `cfg_status`, permissões `CAEXN` em `ConfigPerfilItemModel`, nova ação em `oco_tipo_acao` (T8) associada por subtipo em T9. - 5 tabelas novas de banco (`oco_notif_desvio`, `oco_notif_evento`, `oco_notif_evento_produto`, `oco_notif_evento_anexo`, `oco_notif_evento_acao`). - Controllers/Entities/Models/Views de T42 e T43 completos (listagem, cadastro, integrações entre telas, execução de ações, impressão de etiqueta). - Cadastro de `cfg_etiqueta`/`cfg_layout_etiqueta`/`cfg_etiqueta_campo` para as duas telas (dado de configuração, não é código novo).

**Fora do escopo deste ciclo:** - Qualquer alteração em T9/T11/T12 além da associação de ação nova por subtipo em T9 (mecanismo já existente, sem alteração de schema). - Cadastro de tabela de “locais” para o campo `ndv_local` de T42 — decisão final é texto livre, sem select. - Cadastro de fornecedor no sistema — `nev_fornecedor` é texto livre.

------------------------------------------------------------------------

## 3. Decisões de Arquitetura

### 3.1 Mecanismo de impressão = etiqueta (não relatório)

Onde o requisito original menciona “relatório” (RN03.15 de T42; RN04.1/RN04.2 de T43), a decisão final do `byarq` é que se trata de **impressão de etiqueta**, usando o mecanismo já existente no projeto — não um relatório novo.

**Fluxo reaproveitado (sem código novo no runtime):** - Front: `gerarEtiquetaZPL(url, etiq = false, chave = false, qtia = 1)` (`public/assets/jscript/my_default.js`, ~linha 1834) resolve via `/buscas/buscaetiqcontroler` quantas etiquetas existem cadastradas para a tela atual (usa `#controler` padrão de toda tela): 0 → erro; 1 → usa direto; 2+ → seleção via `boxAlert` tipo 9. Em seguida chama `openImgModal()`. - Back: `Buscas::buscaetiqcontroler()` (`app/Controllers/Buscas.php`, ~linha 787) resolve a tela pelo nome do controller via `ConfigTelaModel::getTelaSearch` e chama `ConfigEtiquetaModel::getEtiquetaTela($tel_id)`.

**Código novo necessário em T42/T43 (mirror de** `Estoque\EtqProduto::GeraEtiqueta()`**):** - Método `GeraEtiqueta($id)` em cada controller (`ndv_id` em T42 / `nev_id` em T43): busca os dados do registro, grava em cache Redis com chave única, retorna JSON `{ link: base_url('CriaEtiquetaZPL/emiteEtiqueta'), chave }`. - Wrapper JS (mirror de `geraEiquetaProd()`): `jQuery.getJSON(urlGeraEtiqueta, res => gerarEtiquetaZPL(res.link, false, res.chave, qtia))`.

**Rota:** já existe — `CriaEtiquetaZPL/emiteEtiqueta/(:any)/(:any)/(:any)` (`Routes.php`, ~linha 131). Apesar do nome “ZPL”, a saída passa por `openImgModal()` (imagem/preview), não depende de impressora térmica.

**Cadastro de dado (não código):** `cfg_etiqueta` + `cfg_layout_etiqueta` + `cfg_etiqueta_campo` por `tel_id` de T42/T43, via telas de administração já existentes (`Config\CfgEtiqueta` / `Config\CfgLayoutEtiq`). Campos impressos mapeiam os nomes retornados pelo array cacheado em `GeraEtiqueta()`. Validar apenas `let_largura`/`let_altura`/`let_colunas` compatíveis com um layout de documento maior — via parametrização de cadastro, sem mudança de código.

**Gatilho de impressão:** - T42: oferecida logo após o Salvar, **não bloqueante**. - T43: oferecida via botão “Imprimir” na listagem.

### 3.2 Nomenclatura e mapeamento de telas do requisito

- “T1” no fluxo macro de T42 é inconsistência de digitação do requisito original — na prática é sempre **T11** (Gestão de Ocorrências, `OcoOcorrencia`/`oco_ocorrencia`), confirmado pela própria RN02.3 de T42, que já usa “T11” explicitamente.
- “T8” = `OcoTipoAcao` / `oco_tipo_acao` (catálogo de ações do Ceqweb 3.0).
- “T2” = tela de administração de `cfg_mensagem`; MSG ID 2 e MSG ID 7 já existem, reaproveitadas sem alteração.
- **“Ação ID 13 da tela T8” (RN02.3 de T42) — a ação NÃO existe hoje, precisa ser criada:**
  - Nome: “Notificação do Fornecedor” (nome definitivo a confirmar com `bydev` na codificação).
  - É **configurável por subtipo de ocorrência em T9** (`oco_subt_ocorrencia_acao`), seguindo o mesmo mecanismo já usado pelas demais ações (`EntOcoSubtOcorrencia::defCamposAcao()`) — reaproveita 100% a tela T9 já existente, **sem alteração de schema em T9**.
  - Cadastro necessário: nova linha em `oco_tipo_acao` + associação por subtipo relevante em T9 (dado de configuração, feito na etapa de infraestrutura).

### 3.3 Status “Pendente”/“Concluída”

`cfg_status` é escopado por tela (`tel_id`). T42 e T43 são telas novas, sem status próprio ainda.

**Decisão final:** cadastrar, para cada tela nova, dois `cfg_status` próprios (`mod_id` do módulo Fornecedores novo, `tel_id` de cada tela nova): - **“Pendente”** — status inicial ao integrar da tela de origem. - **“Concluída”** — status final.

**Gatilho de “Concluída” — decisão final:** o clique em Salvar já grava e conclui o registro (`stt_id` = Concluída direto no `store()`, na mesma transação da gravação). **Não há estado intermediário** esperando fechamento do modal de impressão. A impressão da etiqueta é oferecida em seguida, como ação independente e não bloqueante.

### 3.4 Ícones (ambas as telas, mesmo padrão do resto do sistema)

| Ação | Classe | Cor | Hover |
|----|----|----|----|
| Alterar | `fa-regular fa-pen-to-square` | `#ffa500` | “Alterar” + aumentar ícone |
| Visualizar | `fa-light fa-eye` | `#0d6efd` | “Visualizar” + aumentar ícone |
| Imprimir | `fa-solid fa-print` | `#000000` | “Imprimir” + aumentar ícone |
| Excluir | `fa-regular fa-trash-can` | `#ff0000` | “Excluir” + aumentar ícone |

------------------------------------------------------------------------

## 4. Estrutura de Banco de Dados

### 4.1 Cadastro de infraestrutura (pré-requisito, dado de configuração)

- `cfg_modulo` — módulo novo “Fornecedores” (não existe hoje; verificar antes de criar, para não duplicar).
- `cfg_tela` — duas novas linhas: T42 e T43, com `tel_controler`, `tel_model`, `tel_texto_botao` (T43 tem botão “+” na listagem para nova notificação — mecanismo nativo de `cfg_tela`/`vw_lista`, não é feature nova).
- `ConfigPerfilItemModel` — permissões `CAEXN` (conforme `rascunho-helpers-php.md`) para os perfis relevantes (materiais/qualidade).
- `cfg_status` — Pendente/Concluída de cada tela (ver 3.3).
- `oco_tipo_acao` — nova ação “Notificação do Fornecedor” (ver 3.2) + associação por subtipo em T9.

### 4.2 Tabela `oco_notif_desvio` (T42)

Prefixo de coluna: `ndv_`.

| Coluna | Tipo/Regra | Observação |
|----|----|----|
| `ndv_id` | PK |  |
| `oco_id` | FK → `oco_ocorrencia.oco_id` | Origem T11. Tipo, subtipo, data, usuário, tela geradora, código ERP, descrição, fabricante, lote, validade, quantidade **vêm via JOIN/view até T11** — **não duplicados** nesta tabela. |
| `ndv_local` | texto simples (`input text`), obrigatório, min 5 / max 50 caracteres | RN03.7. Editável. **Sem select, sem tabela de locais nova** — segue literalmente o requisito. |
| `ndv_descreva` | texto, obrigatório, min 5 / max 200 caracteres | RN03.14. Editável. |
| `stt_id` | FK → `cfg_status` (Pendente/Concluída da T42) |  |
| `usu_criou` + timestamps padrão |  | Log de auditoria via `LogMonModel`, padrão já usado no projeto — não reinventar. |

### 4.3 Tabelas de T43

#### `oco_notif_evento` (cabeçalho — Dados Gerais nível-notificação + Providências + Parecer Final)

Prefixo: `nev_`. Colunas diretas na tabela (relações 1:1 com a notificação):

| Coluna | Tipo/Regra | RN | Aba |
|----|----|----|----|
| `nev_id` | PK |  |  |
| `nev_qtd_adquirida` | numérico, 5 dígitos, obrigatório | RN03.10 | Dados Gerais |
| `nev_numero_nf` | numérico, 20 dígitos, obrigatório | RN03.11 | Dados Gerais |
| `nev_fornecedor` | texto livre, 5–200 caracteres, obrigatório | RN03.12 | Dados Gerais (sem cadastro de fornecedor no sistema — texto livre confirmado) |
| `nev_fabricacao` | data, obrigatório | RN03.13 | Dados Gerais |
| `nev_providencias` | texto, 5–500, obrigatório | RN03.14 | Providências |
| `nev_notificado` | texto, 5–200, obrigatório (“empresa/responsável/setor”) | RN03.15 | Providências |
| `nev_parecer` | texto, 5–500, obrigatório | RN03.17 | Parecer Final |
| `nev_notivisa` | `char(1)` S/N, default `N` | RN03.18 | Parecer Final |
| `nev_notivisa_num` | `varchar(50)`, obrigatório somente se `nev_notivisa = 'S'` | RN03.19 | Parecer Final |
| `stt_id` | FK → `cfg_status` (Pendente/Concluída da T43) |  |  |
| `usu_criou` + timestamps/log padrão |  |  |  |

#### `oco_notif_evento_produto` (aba Dados Gerais, grid “Dados do Produto”, 1:N)

Prefixo: `nvp_`.

| Coluna | Tipo/Regra | Observação |
|----|----|----|
| `nvp_id` | PK |  |
| `nev_id` | FK → `oco_notif_evento.nev_id` |  |
| `ndv_id` | FK → `oco_notif_desvio.ndv_id` | Rastreia de qual desvio T42 veio cada linha/produto/lote selecionado. |
| `nvp_defeito` | texto, 5–200, obrigatório | RN03.9 — cópia **editável** do campo “Descreva” de T42: valor inicial copiado de `ndv_descreva` do T42 de origem, mas permite alteração aqui. |

Data, Código ERP, Descrição, Fabricante, Lote, Validade, Quantidade **não são colunas próprias** — vêm por JOIN até `oco_notif_desvio` → `oco_ocorrencia` (read-only, não duplicar dado).

#### `oco_notif_evento_anexo` (aba Providências RN03.16 + aba Parecer Final RN03.20 — upload múltiplo, mesmo padrão compartilhado pelas duas abas)

Prefixo: `nva_`.

| Coluna | Tipo/Regra | Observação |
|----|----|----|
| `nva_id` | PK |  |
| `nev_id` | FK → `oco_notif_evento.nev_id` |  |
| `nva_origem` | indica `'PROVID'` ou `'PARECER'` | diferencia os dois blocos de upload |
| `nva_arquivo` | caminho do arquivo |  |
| `nva_nome_original` |  |  |
| timestamps/usuário |  |  |

Usar `MyCampo::crArquivo()` + `setPasta()` / `setTipoArq('.pdf,.png,.jpeg,.jpg')` (conforme `rascunho-MyCampo.md`).

**PENDÊNCIA NÃO BLOQUEANTE:** não foi localizado no código um padrão pronto de “lista de anexos com botão + e excluir linha”. `bydsgn` deve validar/desenhar esse padrão de UX durante a codificação — `bydev` aciona `bydsgn` quando chegar nessa parte.

#### `oco_notif_evento_acao` (aba Ações, RN03.21/RN03.22, 1:N)

Prefixo: `nac_`. Reaproveita diretamente o padrão de campo condicional por `tpa_tipo` já usado em T9/T12 via `verificaTipoAcao()` (`my_fields.js`) — **não recriar**.

| Coluna | Tipo/Regra | Observação |
|----|----|----|
| `nac_id` | PK |  |
| `nev_id` | FK → `oco_notif_evento.nev_id` |  |
| `tpa_id` | FK → `oco_tipo_acao` (T8) |  |
| `nac_ordem` |  | Define ordem de execução (RN03.22). |
| `stt_id` | condicional — presente quando `tpa_tipo = 4` (Alterar Status) | Espelha `EntOcoSubtOcorrencia`/`EntOcoTratativa`. |
| `tmo_id` | condicional — presente quando `tpa_tipo = 3` (Gerar Movimentação) | Espelha `EntOcoSubtOcorrencia`/`EntOcoTratativa`. |

**RN03.22 — execução sequencial:** ao Salvar, executar as ações cadastradas **na sequência de cadastro** (`nac_ordem`). Se a ação for “gerar movimentação”, o saldo movimentado é a Quantidade (RN03.8) de **cada produto/lote selecionado** (iterar por linha de `oco_notif_evento_produto`) — generalização do que `OcoTrataOcorrencia::gerarMovimentacao()` faz hoje para 1 produto único: replicar a lógica, mas iterando por N produtos.

### 4.4 Diagrama de relacionamento (resumo textual)

    oco_ocorrencia (T11, existente)
       └─< oco_notif_desvio (T42)                                 [stt_id → cfg_status T42]
              └─< oco_notif_evento_produto (T43) >──── oco_notif_evento (T43)
                                                            ├─< oco_notif_evento_anexo
                                                            └─< oco_notif_evento_acao ──> oco_tipo_acao (T8)
                                                                  [stt_id → cfg_status T43]

------------------------------------------------------------------------

## 5. Especificação Funcional por Tela/Aba

### 5.1 T42 — Desvio de Qualidade

#### Listagem

- Colunas: N°, Data, Produto, Fabricante, Lote, Usuário, Status, Ação.
- Botões por linha (visualizar/alterar/excluir/imprimir) conforme status — usar `montaListaDados()` (padrão de grid do projeto, conforme `rascunho-runtime-js.md`), última coluna “Ação” automaticamente não ordenável/pesquisável.
- **Integração automática de origem:** produtos de T11 associados à nova ação de T8 (“Notificação do Fornecedor”) geram automaticamente um registro em `oco_notif_desvio` com status Pendente, disparada a partir da execução da ação em T11 (`stt_id = 28` em T11, conforme convenção do módulo Ocorrências).
- Coloração/ordenação de status seguindo padrão já usado em T1 (referência visual existente no sistema — badge de status via `fmtEtiquetaCor`/ `fmtEtiquetaCorBst`, conforme `rascunho-helpers-php.md`).

#### Aba única — Dados Gerais

| Campo | Origem | Editável | Regra |
|----|----|----|----|
| Desvio n° | `ndv_id` | Não | Exibição (`setLeitura(true)` / `crShow()`) |
| Tipo | via T11 | Não | JOIN até `oco_ocorrencia` |
| Subtipo | via T11 | Não | JOIN até `oco_ocorrencia` |
| Data da Ocorrência | via T11 | Não | JOIN até `oco_ocorrencia` |
| Usuário | via T11 | Não | JOIN até `oco_ocorrencia` |
| Tela Geradora | via T11 | Não | JOIN até `oco_ocorrencia` |
| **Local** | `ndv_local` | **Sim** | RN03.7 — texto livre, min 5 / max 50 |
| Grid de produto (Código ERP, Descrição, Fabricante, Lote, Validade, Quantidade) | via T11 | Não | Todos não-editáveis, vindos de T11 |
| **Descreva** | `ndv_descreva` | **Sim** | RN03.14 — texto livre, min 5 / max 200 |

Campos não-editáveis devem usar `MyCampo::setLeitura(true)` (nunca `readonly`/ `disabled` manual, conforme `rascunho-MyCampo.md`).

#### Salvar / Cancelar

- Ao Salvar: grava e já conclui (`stt_id` = Concluída da T42), conforme decisão 3.3. Oferece impressão de etiqueta em seguida (não bloqueante, ver 3.1).
- Validação de “campo alterado”: comportamento genérico do runtime já existente (`data-valid`, `rascunho-runtime-js.md`) — sem necessidade de código server-side extra.
  - Cancelar com alteração pendente → confirmação **MSG ID 2** (T2).
  - Salvar sem alteração real → **MSG ID 7** (T2).

### 5.2 T43 — Notificação de Evento

#### Listagem

- Mesmas colunas de T42 (N°, Data, Produto, Fabricante, Lote, Usuário, Status, Ação) + botão “+” para nova notificação (mecanismo nativo de `cfg_tela`/`vw_lista`, `tel_texto_botao`).
- **Integração automática de origem:** registros **Concluídos** de T42 alimentam a base de seleção de produtos disponível para nova notificação.
- Botão “Imprimir” na listagem (gatilho de impressão de etiqueta, ver 3.1).

#### Tela intermediária — Seleção de produtos (RN03.1)

- Grid: N°, Data, Produto, Fabricante, Lote, Usuário, Selecione (checkbox).
- Seleção inicial: todos os produtos do **mesmo fabricante** com status Pendente em T42 (nota: leia-se “Pendente em T42” no sentido de registros de T42 já Concluídos disponíveis para notificação, cujos produtos ainda não foram vinculados a uma notificação de evento — usar `ndv_id`/status conforme fluxo de integração acima).
- Abre o cadastro (4 abas) somente após confirmação da seleção.

#### Aba 1 — Dados Gerais

- Grid multi-linha dos produtos selecionados (`oco_notif_evento_produto`): Código ERP, Descrição, Fabricante, Lote, Validade, Quantidade (read-only, via JOIN) + **Defeito** (`nvp_defeito`, editável, RN03.9, valor inicial copiado de `ndv_descreva` de T42).
- Campos de cabeçalho: Quantidade adquirida (`nev_qtd_adquirida`, RN03.10), Número NF (`nev_numero_nf`, RN03.11), Fornecedor (`nev_fornecedor`, RN03.12, texto livre), Fabricação (`nev_fabricacao`, RN03.13, data).

#### Aba 2 — Providências

- Textarea Providências (`nev_providencias`, RN03.14, `MyCampo::crTexto()`).
- Campo Notificado (`nev_notificado`, RN03.15).
- Upload múltiplo de anexos (RN03.16) — `oco_notif_evento_anexo` com `nva_origem = 'PROVID'`. Ver pendência de UX na seção 4.3.

#### Aba 3 — Parecer Final

- Textarea Parecer (`nev_parecer`, RN03.17).
- Toggle Notivisa (`nev_notivisa`, RN03.18, `MyCampo::crCheckbox()` estilo switch/toggle) com campo n° condicional (`nev_notivisa_num`, RN03.19, obrigatório somente se `nev_notivisa = 'S'` — exibição condicional via `verificaTipoAcao()`/padrão equivalente de campo condicional já usado no projeto).
- Upload múltiplo de anexos (RN03.20) — `oco_notif_evento_anexo` com `nva_origem = 'PARECER'`.

#### Aba 4 — Ações (RN03.21/RN03.22)

- Dropdown de ações T8 (`criaSelectRelativo('oco_tipo_acao', 'tpa_id', 'tpa_nome', ...)`, conforme `rascunho-helpers-php.md`) com sub-campos condicionais por `tpa_tipo` (reaproveitando `verificaTipoAcao()` de `my_fields.js` — não recriar, mesmo padrão de T9/T12).
- Múltiplas linhas com adicionar/excluir (padrão `bt-repete`/`addCampo` já usado em T9/T10).
- Ao Salvar: execução sequencial das ações por `nac_ordem` (ver 4.3, RN03.22).

#### Salvar / Cancelar

- Mesma regra de T42: Salvar já grava e conclui (`stt_id` = Concluída da T43), impressão de etiqueta oferecida em seguida via botão Imprimir na listagem (não no fluxo de salvar, diferente de T42).
- Validação de campo alterado (Salvar/Cancelar) via runtime genérico (`data-valid`), mesmas mensagens MSG 2 / MSG 7 de T2.

------------------------------------------------------------------------

## 6. Regras de Validação (resumo consolidado)

| Regra | Tela | Campo | Validação |
|----|----|----|----|
| RN03.7 | T42 | `ndv_local` | Obrigatório, 5–50 caracteres |
| RN03.14 | T42 | `ndv_descreva` | Obrigatório, 5–200 caracteres |
| RN03.9 | T43 | `nvp_defeito` | Obrigatório, 5–200 caracteres, valor inicial copiado de `ndv_descreva` |
| RN03.10 | T43 | `nev_qtd_adquirida` | Obrigatório, numérico, até 5 dígitos |
| RN03.11 | T43 | `nev_numero_nf` | Obrigatório, numérico, até 20 dígitos |
| RN03.12 | T43 | `nev_fornecedor` | Obrigatório, texto livre, 5–200 caracteres |
| RN03.13 | T43 | `nev_fabricacao` | Obrigatório, data |
| RN03.14 | T43 | `nev_providencias` | Obrigatório, 5–500 caracteres |
| RN03.15 | T43 | `nev_notificado` | Obrigatório, 5–200 caracteres |
| RN03.16 | T43 | anexos Providências | Upload múltiplo, `.pdf,.png,.jpeg,.jpg` |
| RN03.17 | T43 | `nev_parecer` | Obrigatório, 5–500 caracteres |
| RN03.18 | T43 | `nev_notivisa` | S/N, default N |
| RN03.19 | T43 | `nev_notivisa_num` | Obrigatório somente se `nev_notivisa = 'S'` |
| RN03.20 | T43 | anexos Parecer Final | Upload múltiplo, `.pdf,.png,.jpeg,.jpg` |
| RN03.21/22 | T43 | Ações | Execução sequencial por `nac_ordem`, movimentação iterada por produto/lote |
| RN04.3 (T2) | T42/T43 | Salvar sem alteração | MSG ID 7, via `data-valid` (runtime) |
| RN04.2 (T2) | T42/T43 | Cancelar com alteração | MSG ID 2, via `data-valid` (runtime) |

**Segurança server-side (obrigatório desde o primeiro commit — não deixar para depois):** bloqueio de edição/exclusão fora de status permitido em T42/T43, mesmo tipo de gap já corrigido anteriormente em outra tela do projeto (ver histórico do módulo Ocorrências, RN04.1/RN05.1 de T11 em `ocorrencias-t9-t11-t12-dev.md`) — validar no backend, não só ocultar botão no front.

**Idempotência:** geração de movimentação por produto em T43 deve evitar duplicidade (não gerar movimentação duas vezes para o mesmo produto/ação).

------------------------------------------------------------------------

## 7. Ordem de Implementação

1.  **Infraestrutura:** módulo Fornecedores, telas T42/T43 em `cfg_tela`, status Pendente/Concluída de cada uma, permissões `CAEXN`, ação nova em T8 + associação em T9.
2.  **Migrations** das 5 tabelas novas (`oco_notif_desvio`, `oco_notif_evento`, `oco_notif_evento_produto`, `oco_notif_evento_anexo`, `oco_notif_evento_acao`) com FKs para `oco_ocorrencia`, `oco_tipo_acao`, `cfg_status`.
3.  **T42 completo** (listagem, integração com T11, cadastro Dados Gerais, salvar com conclusão automática, `GeraEtiqueta`). T42 deve estar 100% funcional **antes** de iniciar T43, pois T43 depende de registros Concluídos de T42.
4.  **Etiqueta T42:** cadastro de `cfg_etiqueta`/`cfg_layout_etiqueta`/ `cfg_etiqueta_campo` para T42, validação end-to-end da impressão.
5.  **T43:** seleção de produtos → cadastro 4 abas → upload de anexos (`bydsgn` valida UX) → aba Ações (reaproveitando `verificaTipoAcao()`) → salvar com execução sequencial de ações → `GeraEtiqueta` → botão “+” na listagem.
6.  **Revisão de segurança:** bloqueio server-side de edição/exclusão fora de status permitido em T42/T43 desde o primeiro commit; idempotência da geração de movimentação por produto em T43.

------------------------------------------------------------------------

## 8. Pendências Não-Bloqueantes

- **Padrão de UX de lista de anexos (upload múltiplo com adicionar/excluir linha)** — não foi localizado padrão pronto equivalente no código existente. `bydsgn` deve validar/desenhar esse padrão durante a codificação de T43 (aba Providências e aba Parecer Final), acionado pelo `bydev` ao chegar nessa etapa.
- **Nome definitivo da ação nova de T8** (“Notificação do Fornecedor”) — a confirmar com `bydev` durante a codificação (nome provisório definido pelo `byarq`).

------------------------------------------------------------------------

## 9. Critérios de Pronto

- Módulo Fornecedores cadastrado em `cfg_modulo`; T42 e T43 cadastradas em `cfg_tela` com permissões `CAEXN` configuradas para os perfis relevantes.
- Status Pendente/Concluída cadastrados em `cfg_status` para cada tela nova.
- Ação nova “Notificação do Fornecedor” cadastrada em `oco_tipo_acao` (T8) e associável por subtipo em T9, sem alteração de schema em T9.
- 5 tabelas novas criadas com FKs corretas para `oco_ocorrencia`, `oco_tipo_acao`, `cfg_status`.
- T42: listagem funcional com integração automática de T11; cadastro Dados Gerais com campos editáveis (`ndv_local`, `ndv_descreva`) e não-editáveis (via JOIN T11) corretos; Salvar grava e conclui na mesma transação; `GeraEtiqueta()` funcional e oferecida após Salvar (não bloqueante).
- T43: listagem funcional com integração automática de T42 Concluídos; tela de seleção de produtos por fabricante/Pendente; 4 abas completas (Dados Gerais, Providências, Parecer Final, Ações) com todos os campos e uploads; execução sequencial de ações por `nac_ordem` ao Salvar; movimentação iterada corretamente por produto/lote, sem duplicidade; `GeraEtiqueta()` funcional via botão Imprimir na listagem.
- Bloqueio server-side de edição/exclusão fora de status permitido implementado em ambas as telas desde o primeiro commit.
- Validação de campo alterado (Salvar/Cancelar) funcionando via runtime genérico (`data-valid`), sem código server-side extra.
- `byrev` sem apontamentos pendentes; `bytest` com plano de testes cobrindo todas as RNs deste documento.

------------------------------------------------------------------------

## 10. Rastreabilidade

Cada RN referenciada neste documento (RN02.x, RN03.x, RN04.x de T42 e T43, conforme os documentos de requisito originais) deve ser citada pelo seu código em commits, comentários de revisão (`byrev`) e casos de teste (`bytest`), para permitir rastrear diretamente do requisito até a mudança de código correspondente.
