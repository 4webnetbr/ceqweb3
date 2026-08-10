# Plano — Tabela `oco_ocorrencia_acao` + Tratativa Parcial (status "Parcialmente Tratada") + Execução Automática por Ação

## Contexto

O módulo de Ocorrências foi originalmente desenhado assumindo **1 ação de
cada tipo por ocorrência**: `oco_ocorrencia` guarda o resultado da ação
executada em colunas escalares (`tpa_id`, `tmo_id`, `oco_justi`, `stt_id`,
`tel_id`). Isso mudou — uma ocorrência pode ter várias ações configuradas
no subtipo (`oco_subt_ocorrencia_acao`, já é 1:N) e o fluxo de tratativa
(`OcoTrataOcorrencia::store()`) já executa todas em loop, mas o resultado
individual de cada ação (principalmente Justificar e Alterar Status) se
perde — só sobrevive o primeiro valor encontrado.

Requisitos consolidados (definidos ao longo da conversa):

1. Registrar cada ação executada individualmente (auditoria completa, com
   sua própria justificativa/status/resultado) numa tabela nova
   `oco_ocorrencia_acao`.
2. **Execução parcial na tratativa manual**: checkbox "Executar agora" por
   ação; desmarcada = fica pendente para rodada futura.
3. **Novo status "Parcialmente Tratada"** para quando restar ≥1 ação
   pendente.
4. Ações adicionadas via botão "+" na tratativa (RN03.15) deixam de ser um
   conceito "extra" — mesmo tratamento das demais, vinculadas só àquela
   ocorrência (não gravam no catálogo do subtipo).
5. **Sem gap**: a ocorrência já nasce com suas ações vinculadas (semeadas
   na criação), nunca fica "sem ações" enquanto pendente.
6. **Execução automática por ação** (não mais por subtipo): cada ação do
   catálogo tem `sta_fina` ('S'/'N' — Finalização Automática). Na criação
   da ocorrência, toda ação com `sta_fina='S'` é executada imediatamente.
   O flag antigo em nível de subtipo (`oco_subt_ocorrencia.sut_fina`) está
   **confirmado como legado morto** pelo usuário — não deve mais ser
   consultado para decidir execução automática.
7. Status resultante da ocorrência **na criação**, conforme execução real
   (não mais previsão):
   - Nenhuma ação com `sta_fina='S'` existe (ou catálogo vazio) →
     Finalizada Automática (**29**) — nada a fazer, igual ao
     comportamento já existente hoje para catálogo vazio.
   - Existem ações `sta_fina='S'`, todas executadas com sucesso, e **não
     sobra nenhuma ação pendente** (ou seja, todas as ações do catálogo
     eram `sta_fina='S'`) → Finalizada Automática (**29**).
   - Nenhuma ação foi executada (nenhuma `sta_fina='S'`, ou todas as
     tentativas falharam) mas ainda existem ações no catálogo → **Pendente
     (28)**, como hoje.
   - Mistura: pelo menos uma ação foi executada com sucesso mas resta
     ao menos uma pendente (não-automática ou que falhou) → **Parcialmente
     Tratada (novo status)**.

## Descoberta importante: `sut_fina` vs `sta_fina`

Hoje existem dois flags de "finalização automática":
- `oco_subt_ocorrencia.sut_fina` (nível **subtipo**) — usado hoje como
  gate em `OcorrenciaService::processAfterSave()` (`app/Services/OcorrenciaService.php:30`):
  só chama `OcoTrataOcorrencia::store($data)` se `sut_fina === 'S'`.
- `oco_subt_ocorrencia_acao.sta_fina` (nível **ação**) — hoje só usado em
  `OcorreSubtOcorrenciaModel::getStatusInicial()` (linhas 186-207) para
  **prever** (sem executar nada) se a ocorrência nasce 28 ou 29.

**Confirmado pelo usuário**: `sut_fina` é legado morto (ideia original,
depois trocada por controle por ação). A partir de agora, **só `sta_fina`
importa**. Isso implica remover o gate de `sut_fina` em
`processAfterSave()` — a auto-execução por ação passa a ser tentada
**sempre**, para toda ocorrência nova, independente do subtipo.

## Desenho da tabela `oco_ocorrencia_acao`

Guarda **todas** as ações aplicáveis à ocorrência — pendentes e
executadas —, desde a criação (seed) ou desde que adicionada ad-hoc na
tratativa. Uma linha é **atualizada** (não recriada) quando executada.

```php
'oac_id'           => INT(11) UNSIGNED AUTO_INCREMENT  // PK
'oco_id'           => INT(11) UNSIGNED NOT NULL         // ocorrência (sem FK física, padrão do módulo)
'tpa_id'           => INT(11) UNSIGNED NOT NULL         // ação
'tpa_tipo'         => INT(3)  UNSIGNED NOT NULL         // snapshot do tipo no momento em que a linha foi criada
'oac_auto'         => CHAR(1) NOT NULL DEFAULT 'N'       // snapshot de sta_fina no momento do seed (ações ad-hoc = 'N', nunca auto)
'tmo_id'           => INT(11) UNSIGNED NULL              // config./preenchido quando tpa_tipo=3
'stt_id'           => INT(11) UNSIGNED NULL              // status do PRODUTO (quando executada, tpa_tipo=4)
'tel_id'           => INT(11) UNSIGNED NULL              // quando tpa_tipo=2
'oco_justi'        => TEXT NULL                           // justificativa desta ação (tpa_tipo=1)
'oac_executada'    => CHAR(1) NOT NULL DEFAULT 'N'        // 'S' quando executada
'oac_erro'         => TINYINT(1) NOT NULL DEFAULT 0        // se a execução retornou erro
'oac_msg'          => VARCHAR(255) NULL                   // mensagem de erro (gerarMovimentacao/gerarNotificacaoDesvio)
'usu_executou'     => INT(11) UNSIGNED NULL               // session()->get('usu_id'); null se automática ou ainda pendente
'oac_automatica'   => TINYINT(1) NOT NULL DEFAULT 0         // se a execução veio da criação (processAfterSave), não de tratativa manual
'oac_criado'       => DATETIME NOT NULL                     // quando a linha foi criada (seed ou ad-hoc)
'oac_executado_em' => DATETIME NULL                        // quando foi executada
```

Chaves: `addKey('oac_id', true)`, `addKey('oco_id')`, `addKey('tpa_id')` —
mesmo padrão de `oco_notif_evento_acao`
(`app/Database/Migrations/2026-07-12-000001_FornecedoresT42T43.php:444-489`).
Sem FK física (nenhuma tabela do módulo usa).

## Novo status "Parcialmente Tratada"

Migration insere uma linha em `cfg_status`, mesmo padrão de
`2026-07-12-000001_FornecedoresT42T43.php:114-148` (resolve `tel_id`/`mod_id`
da tela de Ocorrências via `cfg_tela`, idempotente por `where('tel_id', $telId)`).

## `OcoTrataOcorrencia::store()` — motor único (criação automática + tratativa manual)

`store()` passa a ser o **único lugar** que semeia e executa ações,
reutilizado tanto na criação (via `processAfterSave`, `$automatica=true`)
quanto na tratativa manual (tela `finalizar()`, `$automatica=false`):

**Passo 0 — Seed (idempotente):** se ainda não existir nenhuma linha em
`oco_ocorrencia_acao` para este `oco_id`, copia o catálogo
(`$this->subtocorrencia->getTOAcao($sut_id)`, já retorna todas as linhas,
incluindo `sta_fina`) para `oco_ocorrencia_acao` como pendentes
(`oac_executada='N'`), com `oac_auto` = `sta_fina` de cada uma e `tpa_tipo`
resolvido via `OcorreTipoAcaoModel::getTipoAcao()` (já usado hoje em
`store()`). Isso cobre tanto a primeira chamada automática na criação
quanto uma eventual primeira abertura manual de uma ocorrência antiga (sem
linhas ainda) — mas o caminho normal é sempre semear na criação.

**Passo 1 — Quais linhas processar nesta rodada:**
- `$automatica === true`: todas as linhas com `oac_auto='S'` e
  `oac_executada='N'` para este `oco_id` (query direta, não depende de
  POST/formulário).
- `$automatica === false` (tratativa manual): linhas submetidas no POST
  com o checkbox "Executar agora" marcado (ver seção de UI abaixo) —
  substitui o atual `$postado['tpa_id'] ?? []` / `$postado['tpa_id_extra'] ?? []`.

**Passo 2 — Execução:** mesmo switch por `tpa_tipo` já existente
(`gerarMovimentacao()`, `gerarNotificacaoDesvio()`, etc.), capturando
`erro`/`msg` por linha.

**Passo 3 — Persistência (dentro da transação já existente):** para cada
linha processada, `UPDATE oco_ocorrencia_acao` (se já tinha `oac_id`, caso
do seed) ou `INSERT` (linha nova ad-hoc, sem `oac_id`) com
`oac_executada='S'`, `oac_executado_em=now()`, `usu_executou`,
`oac_automatica`, `oac_erro`/`oac_msg`, e os campos específicos do tipo
(`tmo_id`/`stt_id`/`tel_id`/`oco_justi`) com o valor realmente usado.

**Passo 4 — Resumo (compatibilidade):** `resolveJustificativa()` e
`resolveStatusProduto()` continuam com o mesmo contrato — primeiro valor
não vazio **das ações processadas nesta rodada** grava em
`oco_ocorrencia.oco_justi`/`pro_sap_produto.stt_id`. Sem mudança de
comportamento (o `Model::update()` só altera colunas presentes no array).

**Passo 5 — Status final da ocorrência**, novo método
`resolveStatusOcorrencia()` substitui a linha fixa `$automatica ? 29 : 30`:

```php
$total      = contagem de linhas em oco_ocorrencia_acao para este oco_id
$executadas = contagem com oac_executada='S' (já refletindo esta rodada)
$pendentes  = $total - $executadas;

if ($total === 0 || $pendentes === 0) {
    $sttIdFinal = $automatica ? 29 : 30;   // nada a fazer, ou tudo concluído
} elseif ($executadas === 0) {
    $sttIdFinal = 28;                       // nada foi executado ainda
} else {
    $sttIdFinal = STT_PARCIALMENTE_TRATADA; // mistura: parte feita, parte pendente
}
```

Isso cobre exatamente os 4 cenários do requisito 7: subtipo sem ações →
29; todas `sta_fina='S'` e executadas com sucesso → 29; nenhuma executada
→ 28; mistura → Parcial. E funciona igual para tratativa manual (troca só
o `29` por `30`).

## `OcorrenciaService::processAfterSave()` — remover gate de `sut_fina`

```php
public function processAfterSave(array $data): void
{
    $controller = new OcoTrataOcorrencia();
    $controller->store($data); // sempre chama — sut_fina não é mais consultado
}
```

`gerarOcorrencias()` já chama `processAfterSave()` incondicionalmente
(linha 116) — sem mudança.

## `OcoOcorrencia::store()` — chamada de `processAfterSave` vira incondicional

Hoje (`app/Controllers/Ocorrencia/OcoOcorrencia.php:702-743`): calcula
`stt_id` via `getStatusInicial()` (previsão), pré-preenche
`tpa_id`/`tmo_id` via `getAcaoConfigurada()` se `stt_id != 29`, salva a
entity, e **só chama `processAfterSave()` se a previsão local já era 29**.
Isso significa que hoje, ocorrências previstas como 28 nunca dispensam
seed nem execução automática — precisa mudar:

```php
// ANTES (linha 728): if ($postado['stt_id'] == 29) { ...processAfterSave... }
// DEPOIS: sempre monta $data e chama processAfterSave, independente do
// valor previsto em $postado['stt_id'] — quem decide o stt_id real agora
// é OcoTrataOcorrencia::store() (resolveStatusOcorrencia()), que sempre
// roda em seguida e sobrescreve via update().
```

**Mantém sem alteração**: o cálculo prévio (`getStatusInicial()`/
`getAcaoConfigurada()`, linhas 702-713) — vira um valor transitório
inofensivo (é sobrescrito pelo `update()` final de `store()` logo
depois), não precisa ser removido agora. Isso é o único ponto onde o
plano toca de fato a lógica do controller de criação — é uma mudança
pequena e necessária (não apenas aditiva), decorrente direta do requisito
6/7 do usuário.

Aplica-se tanto a criação quanto a edição de ocorrência nesse mesmo
método (`store()` do `OcoOcorrencia` atende os dois casos, `$postado['oco_id']`
setado ou não) — no caso de edição, o Passo 0 (seed) do
`OcoTrataOcorrencia::store()` é automaticamente pulado (já existem linhas),
só o Passo 1 (auto ainda pendente) roda de novo — idempotente e inofensivo.

**Não alterados:** `OcoOcorrencia::storetmp()` e
`InspecaoProd::storeocor()` — gravam na tabela de STAGING
(`est_requisicao_produto_ocorrencia`, dbEstoque), a ocorrência real só
nasce depois via `gerarOcorrencias()` (já coberto). Nota: `storeocor()`
ainda faz sua própria previsão local via `sut_fina` (linha 477) para
pré-marcar `stt_id=29` no registro de staging — isso vira só um palpite
inofensivo e desatualizado (a decisão real acontece depois, na criação de
fato); não corrigido neste plano por estar fora do escopo (staging, não
autoritativo).

## Tela de tratativa — `finalizar()` e `EntOcoTratativa`

`finalizar()` troca a fonte de dados: em vez de
`$this->ocorrencia->getAcoesFinalizar($id)` (JOIN com catálogo), passa a
usar `OcorreOcorrenciaAcaoModel::where('oco_id', $id)->findAll()` — já
reflete tudo (seed + execuções, automáticas ou manuais, de qualquer
rodada).

`EntOcoTratativa::defCamposAcao()` — unifica renderização (elimina a
distinção origem/extra):
- Linha com `oac_executada==='S'`: somente-leitura, label "Executada em
  dd/mm/aaaa" (+ indicação se foi automática).
- Linha pendente: campo oculto `oac_id[$pos]` (identifica a linha a
  atualizar) + checkbox "Executar agora" (`crCheckbox()`, já existe em
  `MyCampo`, `app/Libraries/MyCampo.php:1029`) marcado por padrão + campos
  condicionais (`oco_justi`/`tmo_id`/`mod_id`+`tel_id`/`stt_id`, mesmo
  padrão visual `divjust`/`divmovi`/`divtela`/`divstat` +
  `verificaTipoAcao()` já implementado).

Botão "+" (`addCampoAcao()`) continua para adicionar ação fora do
catálogo — gera linha sem `oac_id` (select livre de `tpa_id`).

Nomenclatura unificada por posição (`MyCampo::setOrdem($pos)`):
`tpa_id[$pos]`, `oac_id[$pos]` (vazio se nova), `oco_justi[$pos]`,
`tmo_id[$pos]`, `stt_id[$pos]`, `tel_id[$pos]`, `executar[$pos]`
(checkbox) — **corrige de quebra** uma inconsistência pré-existente
encontrada na investigação: os campos da linha "extra" hoje são gerados
sem o sufixo `_extra` que o `store()` atual esperava.

## Arquivos

**Novos:**
- `app/Database/Migrations/2026-08-10-000001_OcoOcorrenciaAcao.php` — cria
  `oco_ocorrencia_acao` + insere status "Parcialmente Tratada" em
  `cfg_status`.
- `app/Models/Ocorre/OcorreOcorrenciaAcaoModel.php` — `$DBGroup='dbOcorrencia'`,
  `$table='oco_ocorrencia_acao'`, `$primaryKey='oac_id'`, `$allowedFields`
  conforme desenho acima.

**A alterar:**
- `app/Controllers/Ocorrencia/OcoTrataOcorrencia.php` — construtor
  (instancia `OcorreOcorrenciaAcaoModel`); `finalizar()` (fonte das ações);
  `store()` (motor único: seed, seleção da rodada, execução, persistência
  por linha, `resolveStatusOcorrencia()`).
- `app/Entities/Ocorrencia/EntOcoTratativa.php` — `defCamposAcao()`
  unificado com checkbox e índice posicional.
- `app/Services/OcorrenciaService.php` — `processAfterSave()` remove gate
  de `sut_fina`.
- `app/Controllers/Ocorrencia/OcoOcorrencia.php::store()` — chamada de
  `processAfterSave()` deixa de ser condicionada a `stt_id==29`.

**Explicitamente NÃO alterados:**
- `app/Controllers/Ocorrencia/OcoOcorrencia.php::storetmp()`,
  `app/Controllers/Preproces/InspecaoProd.php::storeocor()` — staging,
  fora de escopo.
- `app/Controllers/Ocorrencia/OcoNovOcorrencia.php` +
  `OcorreNovOcorrenciaModel.php` — confirmado legado morto.
- Telas de cadastro de subtipo (`OcoSubtOcorrencia.php`,
  `OcoModOcorrencia.php` e entities) — `sut_fina` continua existindo como
  campo/coluna ali (não removido do cadastro), só deixa de ser
  **consultado** para decidir execução automática.
- `app/Views/partials/pw_acoes_ocorrencia.php` — estrutura da view não
  muda, só o conteúdo HTML de cada campo (resolvido na entity).
- `app/Controllers/CriamPdf2026.php` — não lê a tabela nova (oportunidade
  futura, fora de escopo).

## Fases de implementação

1. **Migration + Model** — tabela + status novo, zero risco.
2. **Motor em `OcoTrataOcorrencia::store()`** (seed + auto-execução por
   `oac_auto` + `resolveStatusOcorrencia()`), mais o ajuste em
   `processAfterSave()`/`OcoOcorrencia::store()` para sempre disparar —
   testável isoladamente na criação, antes de mexer na tela.
3. **Tratativa manual** (`finalizar()` + `EntOcoTratativa` + checkbox) —
   UI final.

## Riscos / pontos em aberto

- **Troca de `sut_id` após a ocorrência já ter sido semeada**: não
  encontrado fluxo de edição de `sut_id` pós-criação no código revisado;
  sinalizar se existir.
- **`sut_fina` ainda presente no cadastro do subtipo**: fica visível/editável
  na tela mas sem efeito — decisão de limpeza de UI (remover o campo do
  formulário) fica fora deste plano, avisar se quiser fazer depois.
- **Tipos 3 e 5** (Movimentação, Notificação Fornecedor) passam a ter
  `oac_auto` igual às demais — nenhuma regra especial identificada além da
  idempotência já existente de `gerarNotificacaoDesvio()`.

## Verificação end-to-end (sem migration destrutiva)

1. Rodar a migration nova em dev (aditiva, guard `tableExists`).
2. Criar ocorrência de um subtipo com catálogo 100% `sta_fina='S'` →
   confirmar `stt_id=29`, todas as linhas em `oco_ocorrencia_acao`
   `oac_executada='S'`, `oac_automatica=1`.
3. Criar ocorrência de um subtipo com catálogo 100% `sta_fina='N'` →
   confirmar `stt_id=28`, linhas semeadas mas nenhuma executada.
4. Criar ocorrência de subtipo **misto** (algumas `sta_fina='S'`, outras
   `'N'`) → confirmar `stt_id` = "Parcialmente Tratada", só as `sta_fina='S'`
   marcadas executadas.
5. Abrir a tratativa da ocorrência do item 4 → ações já executadas
   travadas; executar o restante (checkbox) → `stt_id` vira Finalizada (30).
6. Desmarcar algum checkbox manualmente (deixar pendente de propósito) →
   confirmar que continua "Parcialmente Tratada".
7. Adicionar ação via "+" (sem `oac_id`) → confirmar INSERT correto, mesmo
   tratamento das demais.
8. Criar ocorrência de subtipo sem nenhuma ação cadastrada → confirmar
   `stt_id=29` (comportamento já existente, preservado).
9. Conferir `oco_ocorrencia.oco_justi`/`pro_sap_produto.stt_id` (resumo)
   corretos em cenário multi-rodada (criação automática parcial + tratativa
   manual completando depois).
