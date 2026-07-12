# Apontamentos de Revisão — Módulo Fornecedores (T42)

**Rodada:** 01
**Revisor:** `byrev`
**Projeto:** CeqWeb 3.0
**Módulo:** Fornecedores (`app/Controllers/Fornecedores`)
**Tela revisada:** T42 — Desvio de Qualidade
**Documento de referência:** `docs/desenvolvimento/fornecedores-t42-t43-dev.docx` (aprovado)
**Arquivos revisados:**
- `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php`
- `app/Entities/Fornecedores/EntOcoNotifDesvio.php`
- `app/Models/Fornec/FornecNotifDesvioModel.php`
- `app/Controllers/Fornecedores/NotifDesvio.php`
- `public/assets/jscript/my_fornecedores.js`
- `app/Config/Routes.php`
- `app/Controllers/Ocorrencia/OcoTrataOcorrencia.php`

**Destino:** `byarq` (ciência dos apontamentos e das notas de decisão já registradas) → `bydev` (correção)

---

## Contexto

Rodada de revisão do código de T42 (Fornecedores > Desvio de Qualidade), codificado conforme o documento de desenvolvimento aprovado `docs/desenvolvimento/fornecedores-t42-t43-dev.md`. O `byrev` avaliou a migration de infraestrutura/schema, a Entity/Model/Controller de T42, o JS de front reaproveitado, a rota cadastrada, e a integração automática de origem em T11 (`OcoTrataOcorrencia::gerarNotificacaoDesvio()`, disparada pela nova ação "Notificação do Fornecedor" de T8/T9).

---

## 1. Achados Bloqueantes

### Bloqueante 1 — Migration não cadastra `cfg_tela_lista` para T42 (colunas da listagem ficam vazias/quebradas)

**Arquivo:** `app/Controllers/Fornecedores/NotifDesvio.php` (`index()`/`lista()`), em conjunto com `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php`.

**Situação encontrada:**
`index()`/`lista()` usam `montaColunasLista()`/`montaColunasCampos()` (`app/Common.php:238-264`), que buscam as colunas exclusivamente em `ConfigTelaListaModel::getListagem($tel_id)` (tabela `cfg_tela_lista`, campos `lis_campo`/`lis_rotulo`). A migration cadastra `cfg_modulo`, `cfg_tela`, `cfg_status` e `oco_tipo_acao`, mas **não insere nenhuma linha em `cfg_tela_lista`**.

**Por que é um problema:**
Sem esse cadastro, `$lista = []` e a grid roda apenas com a coluna de identificação (`ndv_id`) + "Ação" — nenhuma das colunas exigidas pela RN02.1 do documento aprovado (N°, Data, Produto, Fabricante, Lote, Usuário, Status) aparece na listagem. A tela fica funcionalmente quebrada para o usuário final.

**Correção sugerida:**
Acrescentar o cadastro de `cfg_tela_lista` na migration, com uma linha por coluna exigida em RN02.1 (N°, Data, Produto, Fabricante, Lote, Usuário, Status), no mesmo padrão (`lis_campo`/`lis_rotulo`) já usado para outras telas do sistema.

---

### Bloqueante 2 — Migration não cadastra permissões `CAEXN` em `ConfigPerfilItemModel`

**Arquivo:** `app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php` (método `criaInfraestrutura()`).

**Situação encontrada:**
O documento de desenvolvimento (Seção 4.1 e Critérios de Pronto, Seção 9) exige permissões `CAEXN` configuradas para os perfis relevantes como parte do cadastro de infraestrutura. A migration não insere nada em `cfg_perfil_item`.

**Por que é um problema:**
Conforme `rascunho-helpers-php.md`, ausência de permissão é fail-closed (`LoginFilter` bloqueia tudo) — sem esse cadastro a tela fica inacessível para todos os perfis, inclusive em ambiente de teste.

**Correção sugerida:**
Inserir em `cfg_perfil_item` as permissões `CAEXN` para os perfis de Materiais/Qualidade relevantes.

**Nota:** o usuário/orquestrador já decidiu que essa pendência específica (definir os `prf_id` exatos dos perfis de Materiais/Qualidade) fica para ser resolvida manualmente por ele depois, fora deste ciclo. Este item permanece registrado como bloqueante tecnicamente correto — a ressalva é apenas sobre **quando** será resolvido, não sobre a validade do apontamento.

---

### Bloqueante 3 — `OcoTrataOcorrencia::gerarNotificacaoDesvio()` grava log de auditoria com o ID errado e perde o timestamp de criação

**Arquivo:** `app/Controllers/Ocorrencia/OcoTrataOcorrencia.php`, linhas ~528-533.

**Trecho:**
```php
(new CommonModel())->insertReg('dbOcorrencia', 'oco_notif_desvio', [
    'oco_id'    => $postado['oco_id'],
    'stt_id'    => $sttPendente,
    'usu_criou' => session()->get('usu_id'),
]);
```

**Por que é um problema:**
`CommonModel::insertReg()` (`app/Models/CommonModel.php:33-49`) resolve a chave de log escolhendo o primeiro campo do array que termina em `_id` — nesse array é `oco_id`, não `ndv_id` (a PK real da tabela). O log de inclusão de `oco_notif_desvio` fica gravado com `oco_id` como identificador, não com o `ndv_id` gerado. Isso quebra a busca feita depois em `NotifDesvio::show()` (`buscaLogTabela('oco_notif_desvio', [$id])`, onde `$id` é o `ndv_id`) — nunca encontra o log correspondente.

Além disso, `insertReg()` faz insert cru via Query Builder, sem passar pelo `FornecNotifDesvioModel`, então os hooks do Model (`$useTimestamps`/`$createdField = 'ndv_criado'`, `afterInsert => depoisInsert`) nunca são executados — registro criado automaticamente por T11 fica sem `ndv_criado` preenchido, diferente de um registro atualizado normalmente via `update()`.

**Correção sugerida pelo `byrev`:**
Usar `(new FornecNotifDesvioModel())->skipValidation(true)->insert([...])` em vez de `CommonModel::insertReg()` — mantém `depoisInsert`/timestamps corretos e ainda evita a validação de `ndv_local`/`ndv_descreva` (que legitimamente ainda não existem nesse momento, só serão preenchidos depois pelo usuário em T42).

---

## 2. Sugestões Não-Bloqueantes

### Sugestão 4 — Idempotência de `gerarNotificacaoDesvio()` depende só de checagem em nível de aplicação, sem garantia de banco

O método faz um `SELECT COUNT(*) WHERE oco_id = ...` antes de inserir (linhas ~512-519), fora de qualquer transação/lock, e a tabela `oco_notif_desvio` só tem um índice comum em `oco_id` (`addKey('oco_id')`), não uma `UNIQUE KEY`. Em cenário de concorrência real (duplo clique rápido, requisições quase simultâneas) ainda existe janela de corrida entre o `SELECT` e o `INSERT` que pode gerar duplicidade.

**Sugestão:** declarar `oco_id` como `UNIQUE KEY` na migration — reforça idempotência no nível de banco, não só na aplicação.

---

### Sugestão 5 — Confirmar nomes de coluna da view `vw_oco_notif_desvio_relac` contra o schema real antes de rodar a migration

Já sinalizado pelo `bydev` no cabeçalho do arquivo. A inferência bate com o padrão usado em `EntOcoOcorrencia`/`OcoOcorrencia` (`tpo_nome`, `sut_nome`, `pro_codpro`, `pro_despro`, `fab_apeFab`, `lot_lote`, `lot_validade`, `usu_nome`, `oco_qtd`), mas vale confirmar também se `vw_cfg_status_relac` expõe mesmo uma coluna `stt_cor` (usada em `st.stt_cor` na view nova e em `fmtEtiquetaCor($dados->stt_cor, ...)` no controller).

**Nota:** o usuário já confirmou que vai validar isso pessoalmente no HeidiSQL/DBGate antes de autorizar a migration — este item está registrado como já em andamento, não como bloqueante do ciclo de código.

---

## 3. Itens Já Aprovados Sem Apontamento (rastreabilidade)

Itens abaixo foram checados pelo `byrev` e estão conformes com o documento de desenvolvimento aprovado e com os padrões de `docs/referencia/` — registrados apenas para rastreabilidade:

- `GeraEtiqueta($id, $qtia)` em `NotifDesvio.php` é mirror fiel de `Estoque\EtqProduto::GeraEtiqueta()` (mesma estrutura de chave Redis, TTL 900s, `sAdd` de rastreio de sessão, retorno JSON `{link, chave}`).
- `my_fornecedores.js::geraEiquetaGenerico()` reaproveita corretamente `gerarEtiquetaZPL()`/`boxAlert()`, sem AJAX cru nem alert nativo.
- Bloqueio server-side de edição (`edit()`) e gravação (`store()`) fora do status "Pendente" implementado e robusto: `edit()` lança exceção antes de montar a Entity; `store()` refaz a consulta do registro e revalida o status logo antes do update, cobrindo manipulação de request/reenvio de formulário velho. `delete()` corretamente bloqueado.
- Uso de `MyCampo::setLeitura` (via `leitura=true`) em todos os campos vindos de T11, nenhum input/readonly cru.
- `EntOcoNotifDesvio` segue o padrão de `defCampos()` de `EntOcoOcorrencia`; nomenclatura com prefixo `ndv_` conforme RN03.7/RN03.14.
- Nomenclatura dos métodos do Controller (`index`/`lista`/`show`/`edit`/`store`/`delete` + `GeraEtiqueta` mirror) segue a convenção; sem método `add()`, coerente com registro criado só pela integração automática de T11.
- Rota em `Routes.php` segue exatamente o padrão de `AteRequisicao`/`GeraEtiqueta`/`(:num)`/`(:num)`.
- Nenhuma chamada ativa a `debug()` nos arquivos novos/alterados.
- `case 5` em `OcoTrataOcorrencia::store()` isolado, não interfere nos demais casos; `tpa_tipo=5` não exige campo condicional extra em `verificaTipoAcao()`.

---

## 4. Conclusão do `byrev`

Itens 1, 2 e 3 (Seção 1) são **bloqueantes** — impedem a tela de funcionar corretamente / quebram auditoria — e devem ser corrigidos antes de avançar para `bytest`. Itens 4 e 5 (Seção 2) são sugestões de robustez, a decidir pelo `byarq` se valem a pena neste ciclo ou no próximo.

---

## 5. Encaminhamento

1. **`byarq`** — tomar ciência dos 3 bloqueantes e das 2 sugestões; confirmar se as sugestões 4 e 5 entram neste ciclo ou ficam para o próximo.
2. **`bydev`** — aplicar a correção dos Bloqueantes 1, 2 e 3 (cadastro de `cfg_tela_lista` na migration; cadastro de `cfg_perfil_item`, respeitando a decisão do usuário sobre o momento de definir os `prf_id` exatos; troca de `CommonModel::insertReg()` por `FornecNotifDesvioModel::insert()` em `gerarNotificacaoDesvio()`).
3. Retornar ao `byrev` para nova rodada de revisão após as correções.
