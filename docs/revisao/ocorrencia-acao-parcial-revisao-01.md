# Apontamentos de Revisão --- Ocorrências / Tratativa Parcial (oco_ocorrencia_acao)

**Rodada:** 01 **Revisor:** `byrev` **Projeto:** CeqWeb 3.0 **Módulo:**
Ocorrências (`app/Controllers/Ocorrencia`, `app/Entities/Ocorrencia`)
**Feature:** Tratativa Parcial (`oco_ocorrencia_acao`) **Documento de
referência:** `docs/desenvolvimento/ocorrencia-acao-parcial-plano.docx`
**Destino:** `byarq` (decisão sobre itens bloqueantes) → `bydev`
(correção)

------------------------------------------------------------------------

## 1. Bloqueantes

### Bloqueante 1 --- Bug de indexação em `stt_id` na linha adicionada via botão "+"

**Arquivo/linha:** `app/Entities/Ocorrencia/EntOcoTratativa.php:263-275`

**Situação encontrada:** No bloco de montagem de linha nova via botão
"+" (`$dados === null`), o campo `stt_id` (Alterar Status) tem
`$config = [];` resetado antes de montar o select, sem redefinir
`$config['Ordem'] = $pos`. Todos os outros campos do mesmo bloco
(`tpa_id`, `tmo_id`, `mod_id`, `tel_id`) preservam o `Ordem` porque
reaproveitam o `$config` anterior; apenas `stt_id` esquece essa
atribuição.

**Consequência:** O campo é renderizado como `stt_id` (escalar), e não
como `stt_id[$pos]`. Em `OcoTrataOcorrencia::montaAcoesManuais()`, a
expressão `$sttIds[$pos] ?? null` faz *string offset access* sobre um
valor escalar (não array), retornando um caractere aleatório da string
em vez de `null` ou do valor correto --- corrompendo silenciosamente o
`stt_id` de qualquer ação "Alterar Status" adicionada via botão "+".

------------------------------------------------------------------------

### Bloqueante 2 --- Regressão em `OcoOcorrencia::show()` (tela de consulta)

**Arquivo/linha:**
`app/Controllers/Ocorrencia/OcoOcorrencia.php::show()` (linhas 237-258)

**Situação encontrada:** Essa tela de consulta (somente leitura) não
constava na lista de arquivos alterados pelo plano aprovado, mas é
afetada indiretamente pela mudança:

- Ainda usa `$this->ocorrencia->getAcoesFinalizar($id)` --- que faz JOIN
  com o catálogo `oco_subt_ocorrencia_acao` e não reflete a nova tabela
  `oco_ocorrencia_acao`.
- Chama `$entity->defCamposAcao($acao)` sem o parâmetro `$pos` (sempre
  `0`), causando colisão de `name`/`id` entre ações.
- A `defCamposAcao()` unificada decide leitura-only exclusivamente com
  base em `$dados->oac_executada === 'S'`. Como as linhas retornadas por
  `getAcoesFinalizar()` (catálogo) não possuem essa coluna, o valor é
  sempre falso.

**Consequência:** A tela `show()` passa a renderizar checkbox "Executar
agora" e selects editáveis para uma ocorrência já tratada, e nunca
reflete o estado real de execução por ação.

**Decisão necessária:** `show()` precisa migrar para
`OcorreOcorrenciaAcaoModel` (como `finalizar()` já fez), ou
`defCamposAcao()` precisa receber um flag explícito de somente-leitura,
independente de `oac_executada`.

------------------------------------------------------------------------

### Bloqueante 3 --- Falha em uma ação da rodada aborta a persistência de todas as demais

**Arquivo/linha:**
`app/Controllers/Ocorrencia/OcoTrataOcorrencia.php::store()`

**Situação encontrada:** O Passo 2 (execução das ações) roda todas as
ações da rodada fora de qualquer transação. Se qualquer uma delas
falhar, o Passo 3 inteiro (persistência) é pulado --- nenhuma linha é
marcada como executada, mesmo aquela que já produziu efeito real no
mundo externo (ex.: movimentação de estoque já gerada).

**Consequência:** Em um retry da tratativa, a ação que já rodou executa
novamente. `gerarMovimentacao()` / `geraMovimentoRequisicoes()` não são
idempotentes (ao contrário de `gerarNotificacaoDesvio()`, que já
verifica duplicidade), gerando movimentação de estoque duplicada.

**Observação de risco:** esse risco é novo/agravado pela mudança ---
antes o modelo assumia 1 ação por ocorrência (baixa chance de mistura
sucesso/erro no mesmo submit); agora múltiplas ações rodam juntas
rotineiramente.

------------------------------------------------------------------------

### Bloqueante 4 --- Escopo de transação em `store()` insuficiente (pré-existente, superfície de risco aumentada)

**Arquivo/linha:**
`app/Controllers/Ocorrencia/OcoTrataOcorrencia.php::store()`

**Situação encontrada:** A "transação" usa `\Config\Database::connect()`
(grupo `default`) + `transBegin()/transCommit()`, protegendo apenas o
`update()` de `oco_ocorrencia` (grupo `dbOcorrencia`). Essa proteção já
era um no-op real antes desta mudança (confirmado via
`git show d75ab01`).

**O que mudou:** Agora, dentro desse mesmo bloco, também são feitos N
`insert`/`update` em `oco_ocorrencia_acao` (grupo `dbOcorrencia`) e
potencialmente um `update` em `pro_sap_produto` (grupo `dbProduto`).

**Consequência:** Se uma exceção ocorrer no meio do loop de
persistência, o `catch`/`transRollback()` roda sobre a conexão `default`
(nada a desfazer) --- linhas de `oco_ocorrencia_acao` já gravadas ficam
parcialmente comprometidas (algumas executadas, outras não), sem o
`update` final de `oco_ocorrencia.stt_id`.

**Sugestão levantada pelo** `byrev` **(não é decisão):** usar
`\Config\Database::connect('dbOcorrencia')` como escopo mínimo, cobrindo
`oco_ocorrencia_acao` + `oco_ocorrencia` --- aceitando que
`pro_sap_produto` (grupo diferente) fica fora dessa proteção.

------------------------------------------------------------------------

## 2. Sugestões de melhoria (não-bloqueantes)

### Sugestão 5 --- Campos editáveis na tratativa manual (alerta de governança)

**Arquivo:**
`app/Entities/Ocorrencia/EntOcoTratativa.php::defCamposAcao()`

Os campos `tmo_id` / `stt_id` / `tel_id` passam a ser editáveis em
linhas pendentes da tratativa manual (antes vinham fixos do catálogo).
Confirmado que está de acordo com o plano aprovado --- não é decisão
isolada do `bydev`. Fica registrado o alerta de negócio: o usuário que
finaliza a tratativa manual agora pode alterar esses valores por linha
antes de executar. Vale confirmar se é intencional do ponto de vista de
governança.

### Sugestão 6 --- Idempotência apenas otimista em `seedAcoes()`

**Arquivo:**
`app/Controllers/Ocorrencia/OcoTrataOcorrencia.php::seedAcoes()`

Checa `countAllResults()` antes de `insertBatch()`, sem lock nem
constraint única em `(oco_id, tpa_id)`. Existe janela teórica de corrida
entre a criação em lote (`gerarOcorrencias()`) e uma abertura manual
quase simultânea da tratativa, ambas vendo `countAllResults() === 0` e
duplicando o seed.

**Sugestão:** índice único em `(oco_id, tpa_id)` ou lock explícito.

### Sugestão 7 --- Falta checagem de duplicidade de `tpa_id` em linha ad-hoc

**Arquivo:**
`app/Controllers/Ocorrencia/OcoTrataOcorrencia.php::montaAcoesManuais()`

A linha ad-hoc (adicionada via botão "+") não checa duplicidade de
`tpa_id` contra o catálogo já semeado --- a defesa de duplicidade atual
só cobre a mesma rodada. Um usuário pode escolher, na linha nova, um
`tpa_id` que já existe como linha pendente do seed, resultando em duas
linhas para o mesmo `tpa_id` na mesma ocorrência (uma do seed ainda
pendente, outra nova já executada).

### Sugestão 8 --- Comentário desatualizado (limpeza, não funcional)

**Arquivo/linha:** `public/assets/jscript/my_ocorrencia.js:115` (arquivo
não alterado nesta feature)

Comentário desatualizado em `adicionaAcaoExtra()`, cita a URL
`OcoTrataOcorrencia/addCampoAcaoExtra/<oco_id>`, mas o método real é
`addCampoAcao`. Não é bug funcional, apenas limpeza de comentário.

------------------------------------------------------------------------

## 3. Itens verificados e aprovados (sem pendência)

Itens abaixo foram checados pelo `byrev` e estão conformes com o
documento de desenvolvimento aprovado e com os padrões de
`docs/referencia/` --- registrados para completude da revisão:

- Migration `2026-08-10-000001_OcoOcorrenciaAcao.php`:
  `tel_id`/`mod_id`/`cor_id` do status "Parcialmente Tratada" resolvidos
  via consulta dinâmica em runtime (sem hardcode). Idempotente.
- Troca de `OcorreSubtOcorrenciaModel::getTelas()` (inexistente) para
  `ConfigTelaModel::getTelas()` em `EntOcoTratativa.php:415-419` ---
  correta, corrige bug pré-existente real.
- Comparações `tpa_id === 3` / `=== 2` corrigidas para `tpa_tipo` ---
  correto.
- `getAcoesFinalizar()` não ficou órfão --- ainda usado por
  `OcoOcorrencia::show()` (é justamente o objeto do Bloqueante 2 acima).
- `pw_acoes_ocorrencia.php` continua compatível (`defCamposAcao()`
  sempre retorna array de strings HTML).
- Uso de `MyCampo` / `criaSelectRelativo()` / `crCheckbox()` /
  `crOculto()` correto em todo código novo, sem HTML cru --- conforme
  `rascunho-MyCampo.md`.
- Sem `debug()` ativo.
- Gate de `sut_fina` removido corretamente de
  `OcorrenciaService::processAfterSave()`.

------------------------------------------------------------------------

## 4. Encaminhamento

1.  `byarq` --- decidir sobre o Bloqueante 2 (migração de `show()` para
    `OcorreOcorrenciaAcaoModel` vs. flag explícito de somente-leitura em
    `defCamposAcao()`) e sobre o escopo de transação do Bloqueante 4
    (adotar conexão `dbOcorrencia` como escopo mínimo, ou outra
    abordagem).
2.  `bydev` --- aplicar a correção do Bloqueante 1 (indexação de
    `stt_id`), do Bloqueante 3 (idempotência/proteção contra reexecução
    de ações já efetivadas) e do Bloqueante 4, conforme decisão do
    `byarq`; avaliar as sugestões não-bloqueantes 5 a 8.
3.  Retornar ao `byrev` para nova rodada de revisão após as correções.
