# Documento de Desenvolvimento — Módulo Logística (CeqWeb 3.0): Reformulação dos Tipos de Alerta de Notificações SMS (Saldo Baixo / API / Consulta)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (já existente — infraestrutura `dbLogistica`/`cfg_modulo` criada em ciclo anterior)
**Componentes envolvidos:** `NotifSmsConfig` (Controller/CRUD) · `EntLogNotifSmsConfig` (Entity) · `LogisNotifSmsConfigModel` · `NotifSmsVerificar` (comando CLI) · `SmsApiConsumer` (novo) · `SmsRegraExecutor` (novo) · `ConfigDicDadosModel` · `app/Config/Constants.php`
**Tipo de trabalho:** Reformulação completa de um dos eixos centrais da feature — troca dos 3 tipos de alerta de `nsc_tipo_regra`, de `entrega`/`saldo_baixo` para `saldo_baixo`/`api`/`consulta`. Alteração de schema, CRUD completo e motor de execução.
**Origem:** Pedido direto do usuário (Douglas), planejado e aprovado pelo `byarq`.
**Status:** Aguardando aprovação do `byarq`.

------------------------------------------------------------------------

## 1. Objetivo

Reformular os tipos de alerta disponíveis no cadastro de regras de Notificações SMS (`log_notif_sms_config` / tela `NotifSmsConfig`), substituindo o tipo `entrega` (nunca funcional de ponta a ponta em produção) por dois tipos novos, genéricos e extensíveis:

- **`saldo_baixo`** — já existe, funcionando, **nada é alterado** neste tipo.
- **`api`** — lista todos os métodos "chamáveis a seco" de uma nova classe consumidora de API (`SmsApiConsumer`) e permite escolher 1 deles. Ao executar a regra, o método escolhido é chamado e deve devolver um array de objetos (JSON); se não vazio, dispara 1 SMS por objeto retornado, para cada telefone cadastrado na regra.
- **`consulta`** — lista todas as views que começam com `vw_sms_` em todos os bancos (`dbGroups`) conhecidos do sistema, e permite escolher 1 delas. Ao executar a regra, roda um `SELECT` (via Query Builder, nunca SQL concatenado) na view escolhida e aplica a mesma lógica de disparo por objeto retornado.

Em ambos os tipos novos, a mensagem (`nsc_mensagem_template`) pode conter placeholders entre colchetes (ex.: `[ent_id_dispensacao]`), substituídos pela chave correspondente de primeiro nível do objeto JSON/linha retornada.

Pedido literal do usuário, transcrito integralmente para registro:

> "Terão 3 tipos de Alertas. 1 - saldo_baixo => já existe, funcionando, nada será alterado. 2 - API => vai listar todos os métodos do controller consumidor de APIs, e permite que eu escolha 1 deles. 3 - Consulta => vai listar todas as Views de todos os bancos que começarem com vw_sms_, e permite que eu escolha 1 delas. No campo mensagem, poderão ser introduzidos campos entre colchetes na mensagem, por exemplo [ent_id_dispensacao]. Ao executar a regra: se for API, vai chamar o método da API escolhido e esse método vai devolver um JSON; se o json retornado não for vazio, vai enviar um SMS para os telefones cadastrados, para cada objeto do JSON; no corpo da mensagem, se tiver algo entre colchetes, vai substituir pela chave no json; mesmo procedimento para Consulta, executando um select na view selecionada. Será necessário alterar a tabela no banco, refazer todo o CRUD, e também os serviços."

Este documento consolida o plano final do `byarq` (já revisado e completo) para uso direto do `bydev` na codificação — contém o schema novo, o código de cada arquivo criado/alterado, e a ordem de implementação.

------------------------------------------------------------------------

## 2. Escopo

**Dentro do escopo:**
- Migration alterando `log_notif_sms_config`: novas colunas (`nsc_metodo_api`, `nsc_view_consulta`, `nsc_view_dbgroup`), exclusão definitiva das regras `nsc_tipo_regra = 'entrega'`, troca do `ENUM` de `nsc_tipo_regra`, remoção das 4 colunas exclusivas do tipo `entrega`.
- Classe nova `app/Libraries/Sms/SmsApiConsumer.php` (consumidora de API, plana, sem sessão/`BaseController`).
- Classe nova `app/Libraries/Sms/SmsRegraExecutor.php` (motor de execução compartilhado entre os tipos `api` e `consulta`).
- Novo método `ConfigDicDadosModel::getViewsPorPrefixo()` + centralização de `$dbGroupsConhecidos` (incluindo a correção da ausência de `dbLogistica`).
- Reformulação completa do CRUD `NotifSmsConfig` (Controller, Entity, Model, validação) para os 3 tipos de alerta.
- Reformulação de `NotifSmsVerificar.php`: dispatch para os 3 tipos, novos métodos `processarRegraApi()`/`processarRegraConsulta()`, remoção de `processarRegraEntrega()`.
- Ajuste de `my_fields.js` (`alternaCamposTipoRegra()`) para 3 grupos condicionais em vez de 2.
- Novas constantes em `Constants.php` (`SMS_API_CONSUMER_CLASS`, `SMS_CONSULTA_LIMITE_LINHAS`).

**Fora do escopo deste ciclo (mantido tal como já entregue em ciclos anteriores, sem alteração):**
- `SmsService`, `SmsProviderInterface`, `SmsDevProvider`, `GtiSmsProvider` — motor de envio de SMS em si, entregue em `docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx`.
- `NotifSmsEnviadas` (tela de consulta de histórico) e `LogisNotifSmsEnviadasModel` — nenhuma alteração de schema ou código.
- `Routes.php` — rotas já existentes, cobrem os métodos padrão do CRUD, nenhuma rota nova necessária.
- Implementação do endpoint externo `/renovacoes/pendentes` no repositório do Logística antigo — segue como pendência de outro repositório (ver seção 10).

------------------------------------------------------------------------

## 3. Decisões de negócio confirmadas pelo Douglas

Durante o planejamento desta sessão, o `byarq` levantou pontos em aberto no pedido original e os submeteu ao usuário (Douglas) via pergunta direta. As respostas abaixo são decisões de negócio já confirmadas — citadas aqui com rastreabilidade, não deduzidas pelo `byarq`/`bydoc`:

1. **Classe consumidora de API.** Confirmado como classe nova e plana, `app/Libraries/Sms/SmsApiConsumer.php`, sem depender de sessão/`BaseController` (diferente de um Controller de tela, que não pode ser instanciado fora do ciclo HTTP), reaproveitando `api_request()` de `api_cw2_helper.php` — o mesmo helper já usado por `app/Controllers/Estoque/Requisicao.php`.
2. **Regras `nsc_tipo_regra = 'entrega'` já cadastradas.** Confirmado que devem ser **excluídas definitivamente** pela migration (não inativadas, não convertidas para outro tipo) — o tipo `entrega` nunca funcionou de ponta a ponta em produção (dependia do endpoint externo `/renovacoes/pendentes`, ainda inexistente).
3. **Limite de linhas do tipo Consulta.** Confirmado `SMS_CONSULTA_LIMITE_LINHAS = 100`.
4. **Constantes `LINK_LOGISTICA`/`LOGISTICA_API_KEY`.** Confirmado que permanecem no projeto, reaproveitadas como primeiro método real de `SmsApiConsumer` (`buscarRenovacoesPendentes()`), com o mesmo contrato já especificado em `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` (seção 4). O Douglas está ciente de que o endpoint externo ainda não existe no repositório do Logística antigo — o método fica pronto e selecionável na tela desde já, mas não é testável de ponta a ponta nesta rodada.

------------------------------------------------------------------------

## 4. Decisões de Arquitetura

### 4.1 Tipo "API" — `SmsApiConsumer` (classe nova, plana)

Arquivo `app/Libraries/Sms/SmsApiConsumer.php`:

```php
<?php

namespace App\Libraries\Sms;

/**
 * Classe plana (sem estender BaseController, sem depender de sessão/
 * request/response) reunindo os métodos "consumidores de API" que podem
 * ser escolhidos como tipo "api" em log_notif_sms_config. Cada método
 * público, sem parâmetro obrigatório, deve retornar um array de objetos
 * (lista associativa) ou null em caso de falha — nunca lançar exceção
 * para fora (api_request() já engole erro e retorna null).
 *
 * Reflectível com segurança pelo comando CLI (NotifSmsVerificar) e pela
 * tela administrativa (EntLogNotifSmsConfig), diferente de um Controller
 * de tela — que depende de session()->getFlashdata('dados_tela') e não
 * pode ser instanciado fora do ciclo HTTP.
 */
class SmsApiConsumer
{
    public function __construct()
    {
        helper('api_cw2');
    }

    /**
     * Lista os métodos públicos "chamáveis a seco" (sem parâmetro
     * obrigatório) desta classe — usado tanto para popular o select
     * nsc_metodo_api (tela) quanto para revalidação em tempo de
     * execução (NotifSmsVerificar::processarRegraApi()).
     */
    public static function metodosDisponiveis(): array
    {
        $reflection = new \ReflectionClass(self::class);
        $opcoes = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $metodo) {
            if ($metodo->isStatic() || $metodo->isConstructor()) {
                continue;
            }
            if ($metodo->getDeclaringClass()->getName() !== self::class) {
                continue;
            }
            if ($metodo->getNumberOfRequiredParameters() > 0) {
                continue;
            }
            $opcoes[$metodo->getName()] = $metodo->getName();
        }

        return $opcoes;
    }

    /**
     * Consulta o endpoint /renovacoes/pendentes do Logística antigo,
     * contrato especificado em
     * docs/desenvolvimento/notificacoes-sms-servico-envio-dev.md (seção 4):
     *   GET {LINK_LOGISTICA}renovacoes/pendentes
     *   Header: X-Api-Key: LOGISTICA_API_KEY
     *   Query: ren_status_max (obrigatório), ren_tipo (opcional)
     *   Resposta 200: array de objetos { ren_id, ren_tipo, ren_status, ren_prev_chegada }
     *
     * Endpoint ainda não implementado no repositório do Logística antigo
     * (pendência externa confirmada pelo Douglas, ver seção 3, item 4) —
     * método fica pronto e selecionável na tela desde já, mas só é
     * testável de ponta a ponta quando o endpoint existir do outro lado.
     *
     * $renStatusMax tem default 4, herdado do exemplo de regra do desenho
     * original (docs/notificacoes-sms.md) — ajustar quando o endpoint
     * existir de fato e o critério real de "pendente" for confirmado com
     * quem administra o Logística antigo.
     */
    public function buscarRenovacoesPendentes(int $renStatusMax = 4, ?int $renTipo = null): ?array
    {
        $params = array_filter(
            ['ren_status_max' => $renStatusMax, 'ren_tipo' => $renTipo],
            fn($v) => $v !== null
        );

        return api_request(
            LINK_LOGISTICA . 'renovacoes/pendentes',
            $params,
            'get',
            ['Accept' => 'application/json', 'X-Api-Key' => getenv('LOGISTICA_API_KEY')],
            10,
            httpErrors: false
        );
    }
}
```

Constante nova em `app/Config/Constants.php`:
```php
define('SMS_API_CONSUMER_CLASS', '\App\Libraries\Sms\SmsApiConsumer');
```

Uso (Controller/Entity/Command sempre por essa constante, nunca hardcoding o nome da classe em mais de um lugar):
```php
$classe = SMS_API_CONSUMER_CLASS;
$opcoes = $classe::metodosDisponiveis();
$obj    = new $classe();
$json   = $obj->{$metodoEscolhido}();
```

Novos métodos poderão ser adicionados a `SmsApiConsumer` no futuro (fora deste ciclo) sem nenhuma alteração em `NotifSmsConfig`/`NotifSmsVerificar` — eles aparecem automaticamente no select assim que forem criados como método público sem parâmetro obrigatório, por reflection.

### 4.2 Tipo "Consulta" — enumeração de views `vw_sms_*` em todos os bancos

`ConfigDicDadosModel`: centralizar `$dbGroupsConhecidos = ['default','dbEstoque','dbProduto','dbOcorrencia','dbLogistica']` (corrige de quebra a ausência de `dbLogistica` em `getTabelas()`/`getTabelasPorDbGroup()`), e novo método:

```php
public function getViewsPorPrefixo(string $prefixo): array
{
    $ret = [];
    foreach ($this->dbGroupsConhecidos as $dbGroup) {
        $db     = db_connect($dbGroup);
        $schema = $db->getDatabase();

        $builder = $db->table('information_schema.tables');
        $builder->select(['table_schema', 'table_name']);
        $builder->where('table_schema', $schema);
        $builder->where('table_type', 'VIEW');
        $builder->like('table_name', $prefixo, 'after');

        foreach ($builder->get()->getResultArray() as $row) {
            $ret[] = ['dbGroup' => $dbGroup, 'schema' => $row['table_schema'], 'view' => $row['table_name']];
        }
    }
    return $ret;
}
```

### 4.3 Execução dinâmica do tipo "API"

Revalida `nsc_metodo_api` contra `SmsApiConsumer::metodosDisponiveis()` no momento da execução (defesa em profundidade — nunca confia apenas na validação de cadastro), instancia `SmsApiConsumer`, chama o método sem argumentos (usa os defaults internos do método, ex.: `buscarRenovacoesPendentes()` roda com `$renStatusMax = 4`), confere `is_array()`/`array_is_list()`/não-vazio antes de repassar ao `SmsRegraExecutor`.

### 4.4 Execução dinâmica do tipo "Consulta"

Sem concatenar `nsc_view_consulta` em SQL cru. Revalida `(nsc_view_dbgroup, nsc_view_consulta)` contra `getViewsPorPrefixo('vw_sms_')` tanto no `store()` quanto na execução do comando; usa `db_connect($dbGroup)->table($view)->get($limite)->getResultArray()` (Query Builder, não string SQL manual); `$limite = SMS_CONSULTA_LIMITE_LINHAS`.

### 4.5 Novo default de `nsc_tipo_regra` e reflexo no HTML inicial

`EntLogNotifSmsConfig::$attributes['nsc_tipo_regra']` default muda de `'entrega'` para `'saldo_baixo'` (acompanha o novo `DEFAULT` da coluna, ver seção 5). Consequência no HTML inicial de `NotifSmsConfig::montaCamposFormulario()`: agora é `#divSaldo` que nasce sem `d-none` (grupo padrão visível), e `#divApi`/`#divConsulta` nascem com `d-none` — mesmo princípio já usado antes (o estado HTML inicial reflete o default sem depender de JS de inicialização), só que agora com 3 grupos em vez de 2.

### 4.6 Exclusão definitiva das regras `entrega`

A migration nova executa, antes de alterar o `ENUM`:

```php
$db = db_connect('dbLogistica');
$qtd = $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->countAllResults();
if ($qtd > 0) {
    $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->delete();
    CLI::write("{$qtd} regra(s) do tipo 'entrega' excluída(s) definitivamente (tipo descontinuado, confirmado pelo Douglas).", 'yellow');
}
```

Sem `FOREIGN KEY` física entre `log_notif_sms_enviadas.nse_nsc_id` e `log_notif_sms_config.nsc_id` (confirmado na migration original — só colunas `INT` simples, nunca houve `addForeignKey`), então a exclusão não quebra nada no banco. Eventuais linhas órfãs em `log_notif_sms_enviadas` (histórico de SMS de regras `entrega` já excluídas) já são tratadas graciosamente pelo `LEFT JOIN`/`COALESCE(..., 'Regra desconhecida/excluída')` existente em `LogisNotifSmsEnviadasModel::getEnviadasPeriodo()` — nenhuma alteração necessária ali.

**Ordem obrigatória dentro de `up()`:** (1) adicionar colunas novas → (2) `DELETE` das regras `entrega` → (3) `MODIFY` do `ENUM` removendo `'entrega'` → (4) `DROP COLUMN` das 4 colunas obsoletas. Nessa ordem, o `MODIFY ENUM` nunca encontra uma linha com valor `'entrega'` remanescente.

### 4.7 Limite de linhas do tipo Consulta

```php
define('SMS_CONSULTA_LIMITE_LINHAS', 100);
```
em `app/Config/Constants.php`, usado em `db_connect($dbGroup)->table($view)->get(SMS_CONSULTA_LIMITE_LINHAS)`.

### 4.8 Placeholders `[campo]` na mensagem

`strtr()`, mapa reconstruído por objeto do JSON, case-sensitive, só chaves de primeiro nível (aninhados ignorados). Placeholder sem correspondência no JSON permanece literal (`[campo]`) — comportamento natural do `strtr()`, sem tratamento especial, documentado como intencional (erro de digitação no template fica visível, não vira string vazia silenciosa).

### 4.9 Deduplicação para os novos tipos

`extrairIdDedup()`: usa `$objeto['id']` de primeiro nível se presente; senão `md5(json_encode($objeto))`. Chave final `'API:' . $id` / `'CONSULTA:' . $id`, combinada com o par único já existente `(nse_chave, nse_nsc_id)`.

### 4.10 Frontend/UX

`EntLogNotifSmsConfig::defCampos()` monta 3 grupos condicionais (`#divSaldo`, `#divApi`, `#divConsulta`). `alternaCamposTipoRegra()` em `my_fields.js` expande de 2 para 3 ramos, continua delegando obrigatoriedade a `mudaObrigatorioElemDiv()` — mesmo princípio já usado no ciclo anterior (`docs/desenvolvimento/notificacoes-sms-dev.docx`, seção 3.7), sem reimplementação própria de show/hide + obrigatoriedade.

Selects `nsc_metodo_api` (via `SmsApiConsumer::metodosDisponiveis()`) e `nsc_view_consulta` (via `getViewsPorPrefixo('vw_sms_')`, valor composto `dbGroup|view`, rótulo `view (dbGroup)`) são `crSelect()` estáticos montados na Entity — não `crSelbusca()`/`crDepende()` (conjuntos pequenos, sem necessidade de busca AJAX; conforme `rascunho-MyCampo.md`, `crDepende()`/`crSelbusca()` só se justificam quando há busca AJAX de fato). `store()` sempre revalida os dois contra a fonte de verdade corrente antes de gravar.

Hint de `nsc_mensagem_template` explica o uso de colchetes para os tipos API/Consulta; tipo `saldo_baixo` mantém `{limite}`/`{saldo}` sem nenhuma mudança — as duas convenções de placeholder coexistem intencionalmente, não são unificadas.

### 4.11 `NotifSmsEnviadas` — sem alteração

Nenhuma alteração de schema ou código nesta tela/Model neste ciclo (ver Escopo, seção 2).

### 4.12 `EntLogNotifSmsConfig::defCampos()` completo

Código completo do arquivo `app/Entities/Logistica/EntLogNotifSmsConfig.php`, com os 3 grupos condicionais (`#divSaldo` mantido igual ao já existente hoje, `#divApi`/`#divConsulta` novos). O select `nsc_metodo_api` é montado a partir de `SmsApiConsumer::metodosDisponiveis()` (via `SMS_API_CONSUMER_CLASS`, seção 4.1); o select `nsc_view_consulta` é montado a partir de `ConfigDicDadosModel::getViewsPorPrefixo('vw_sms_')` (seção 4.2), com valor composto `dbGroup|view` e rótulo `view (dbGroup)`. `nsc_view_dbgroup` **nunca** é campo exibido/editável nesta Entity — é gravado internamente pelo `NotifSmsConfig::store()` (seção 4.13), a partir do split do valor composto recebido deste select.

```php
<?php

namespace App\Entities\Logistica;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;

/**
 * Regra de Notificação SMS (log_notif_sms_config, prefixo nsc_).
 *
 * Segue o mesmo padrão de App\Entities\Config\EntCfgCor /
 * App\Entities\Fornecedores\EntOcoNotifEvento: monta o array `campos`
 * (MyCampo já renderizado) em `defCampos()`, consumido pelo Controller
 * (add/edit/show) e pela view genérica `vw_edicao`.
 *
 * nsc_tipo_regra determina quais campos são obrigatórios/visíveis (ver
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md, seções 4.5 e
 * 4.10):
 * - 'saldo_baixo' → nsc_saldo_minimo (wrapper #divSaldo). Nada alterado
 *                    neste ciclo.
 * - 'api'         → nsc_metodo_api (wrapper #divApi).
 * - 'consulta'    → nsc_view_consulta + nsc_view_dbgroup, exibidos juntos
 *                    em um único select de valor composto (wrapper
 *                    #divConsulta). nsc_view_dbgroup nunca é renderizado
 *                    isoladamente.
 * O show/hide + (des)obrigatoriedade em runtime é feito via JS
 * (alternaCamposTipoRegra(), my_fields.js, seção 4.16), disparado no
 * onchange de nsc_tipo_regra e no carregamento da tela (ver
 * NotifSmsConfig::edit()).
 */
class EntLogNotifSmsConfig extends Entity
{
    protected $attributes = [
        'nsc_id'                => null,
        'nsc_nome'              => null,
        'nsc_tipo_regra'        => 'saldo_baixo',
        'nsc_saldo_minimo'      => null,
        'nsc_metodo_api'        => null,
        'nsc_view_consulta'     => null,
        'nsc_view_dbgroup'      => null,
        'nsc_telefones'         => null,
        'nsc_mensagem_template' => null,
        'nsc_ativo'             => 'A',
        'nsc_excluido'          => null,
    ];

    protected $datamap = [];
    protected $dates   = ['nsc_excluido'];
    protected $casts   = [
        'nsc_id'           => '?integer',
        'nsc_saldo_minimo' => '?integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret   = [];

        // ID (campo oculto)
        $id        = new MyCampo('log_notif_sms_config', 'nsc_id');
        $id->valor = $dados['nsc_id'] ?? '';
        $ret['nsc_id'] = $id->crOculto();

        // Nome da Regra
        $nome              = new MyCampo('log_notif_sms_config', 'nsc_nome');
        $nome->valor       = $dados['nsc_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura     = $show;
        $nome->dispForm    = 'col-6';
        $nome->setLabel('Nome');
        $ret['nsc_nome'] = $nome->crInput();

        // Tipo de Regra — dispara o toggle de 3 grupos condicionais
        $tipoRegra              = new MyCampo('log_notif_sms_config', 'nsc_tipo_regra');
        $tipoRegra->valor       = $dados['nsc_tipo_regra'] ?? 'saldo_baixo';
        $tipoRegra->selecionado = $tipoRegra->valor;
        $tipoRegra->opcoes      = [
            'saldo_baixo' => 'Saldo Baixo',
            'api'         => 'API',
            'consulta'    => 'Consulta',
        ];
        $tipoRegra->obrigatorio = true;
        $tipoRegra->leitura     = $show;
        $tipoRegra->dispForm    = 'col-6';
        $tipoRegra->funcChan    = 'alternaCamposTipoRegra(this)';
        $tipoRegra->setLabel('Tipo de Regra');
        $ret['nsc_tipo_regra'] = $tipoRegra->crSelect();

        // ---- Grupo "Saldo Baixo" (wrapper #divSaldo) — mantido igual ao já existente, sem alteração ----
        $saldoMinimo              = new MyCampo('log_notif_sms_config', 'nsc_saldo_minimo');
        $saldoMinimo->valor       = $dados['nsc_saldo_minimo'] ?? '';
        $saldoMinimo->leitura     = $show;
        $saldoMinimo->dispForm    = 'col-4';
        $saldoMinimo->setLabel('Saldo Mínimo');
        $ret['nsc_saldo_minimo'] = $saldoMinimo->crInput();

        // ---- Grupo "API" (wrapper #divApi) ----
        // Opções montadas por reflection via SmsApiConsumer::metodosDisponiveis()
        // (seção 4.1 do documento) — sempre por SMS_API_CONSUMER_CLASS, nunca
        // hardcoding do nome da classe.
        $classeApi = SMS_API_CONSUMER_CLASS;

        $metodoApi              = new MyCampo('log_notif_sms_config', 'nsc_metodo_api');
        $metodoApi->valor       = $dados['nsc_metodo_api'] ?? '';
        $metodoApi->selecionado = $metodoApi->valor;
        $metodoApi->opcoes      = $classeApi::metodosDisponiveis();
        $metodoApi->leitura     = $show;
        $metodoApi->dispForm    = 'col-6';
        $metodoApi->setLabel('Método da API');
        $ret['nsc_metodo_api'] = $metodoApi->crSelect();

        // ---- Grupo "Consulta" (wrapper #divConsulta) ----
        // Select único, de valor composto "dbGroup|view" e rótulo
        // "view (dbGroup)", montado a partir de
        // ConfigDicDadosModel::getViewsPorPrefixo('vw_sms_') (seção 4.2 do
        // documento). Construção manual (sem doBanco()) porque o valor
        // exibido/selecionado é composto, diferente do valor bruto da
        // coluna VARCHAR (doBanco() assumiria input texto simples).
        //
        // nsc_view_dbgroup NUNCA é campo exibido/editável isoladamente
        // nesta tela — é gravado internamente por NotifSmsConfig::store()
        // (seção 4.13), a partir do split do valor composto recebido deste
        // select, nunca confiando no valor bruto do POST.
        $viewsConsulta  = (new ConfigDicDadosModel())->getViewsPorPrefixo('vw_sms_');
        $opcoesConsulta = [];
        foreach ($viewsConsulta as $v) {
            $valorComposto                  = $v['dbGroup'] . '|' . $v['view'];
            $opcoesConsulta[$valorComposto] = $v['view'] . ' (' . $v['dbGroup'] . ')';
        }
        $valorAtualConsulta = (($dados['nsc_view_dbgroup'] ?? '') !== '' && ($dados['nsc_view_consulta'] ?? '') !== '')
            ? $dados['nsc_view_dbgroup'] . '|' . $dados['nsc_view_consulta']
            : '';

        $viewConsulta             = new MyCampo();
        $viewConsulta->objeto     = 'select';
        $viewConsulta->nome       = 'nsc_view_consulta';
        $viewConsulta->valor      = $valorAtualConsulta;
        $viewConsulta->selecionado = $valorAtualConsulta;
        $viewConsulta->opcoes     = $opcoesConsulta;
        $viewConsulta->leitura    = $show;
        $viewConsulta->dispForm   = 'col-6';
        $viewConsulta->setLabel('View de Consulta');
        $ret['nsc_view_consulta'] = $viewConsulta->crSelect();

        // Telefones (CSV texto livre)
        $telefones              = new MyCampo('log_notif_sms_config', 'nsc_telefones');
        $telefones->valor       = $dados['nsc_telefones'] ?? '';
        $telefones->obrigatorio = true;
        $telefones->leitura     = $show;
        $telefones->hint        = 'Informe os números separados por vírgula (ex.: 5548999998888,5548911112222)';
        $telefones->dispForm    = 'col-8';
        $telefones->setLabel('Telefones');
        $ret['nsc_telefones'] = $telefones->crInput();

        // Mensagem (template) — hint explica colchetes para API/Consulta;
        // {limite}/{saldo} do tipo saldo_baixo continuam válidos, sem
        // unificação das duas convenções (ver seção 4.10).
        $mensagem              = new MyCampo('log_notif_sms_config', 'nsc_mensagem_template');
        $mensagem->valor       = $dados['nsc_mensagem_template'] ?? '';
        $mensagem->obrigatorio = true;
        $mensagem->leitura     = $show;
        $mensagem->linhas      = 4;
        $mensagem->colunas     = 60;
        $mensagem->dispForm    = 'col-12';
        $mensagem->hint        = "Tipos 'API'/'Consulta': use colchetes para inserir campos do retorno (ex.: [ent_id_dispensacao]). Tipo 'Saldo Baixo': mantém {limite}/{saldo}, sem alteração.";
        $mensagem->setLabel('Mensagem');
        $ret['nsc_mensagem_template'] = $mensagem->crTexto();

        // Ativo / Inativo
        $ativo              = new MyCampo('log_notif_sms_config', 'nsc_ativo');
        $ativo->valor       = $dados['nsc_ativo'] ?? 'A';
        $ativo->selecionado = $ativo->valor;
        $ativo->opcoes      = ['A' => 'Ativo', 'I' => 'Inativo'];
        $ativo->leitura     = $show;
        $ativo->setLabel('Status');
        $ret['nsc_ativo'] = $ativo->cr2opcoes();

        return $ret;
    }
}
```

### 4.13 Controller `NotifSmsConfig` — `montaCamposFormulario()` e `store()` completos

Código atualizado dos dois métodos do arquivo `app/Controllers/Logistica/NotifSmsConfig.php` (demais métodos — `index()`, `lista()`, `ativinativ()`, `add()`, `show()`, `edit()`, `delete()` — permanecem exatamente como já existem hoje, sem alteração).

`montaCamposFormulario()` — agora com 3 divs condicionais. `#divSaldo` nasce **sem** `d-none` (novo default de `nsc_tipo_regra` é `'saldo_baixo'`, ver seção 4.5); `#divApi`/`#divConsulta` nascem com `d-none`:

```php
    /**
     * Monta o array de campos da seção "Dados Gerais", envolvendo os
     * campos condicionais por nsc_tipo_regra nos wrappers
     * #divSaldo/#divApi/#divConsulta (mesmo mecanismo de div manual já
     * usado antes deste ciclo para #divEntrega/#divSaldo).
     *
     * #divSaldo já nasce SEM a classe "d-none" porque o novo default de
     * nsc_tipo_regra é 'saldo_baixo' (EntLogNotifSmsConfig::$attributes,
     * seção 4.5) — o estado HTML inicial já reflete isso tanto em add()
     * quanto em edit(), sem depender de JS de inicialização. #divApi e
     * #divConsulta nascem com "d-none". Em edit(), quando o registro salvo
     * é 'api'/'consulta', o script disparado no carregamento da tela (ver
     * edit()) corrige o toggle via alternaCamposTipoRegra() (seção 4.16),
     * que também usa a classe "d-none" (não jQuery.toggle()) para poder
     * alternar livremente a partir daqui.
     */
    private function montaCamposFormulario(EntLogNotifSmsConfig $regra): array
    {
        return [
            $regra->campos['nsc_id'],
            $regra->campos['nsc_nome'],
            $regra->campos['nsc_tipo_regra'],
            '<div id="divSaldo" class="row col-12 float-start">'
                . $regra->campos['nsc_saldo_minimo']
                . '</div>',
            '<div id="divApi" class="row col-12 float-start d-none">'
                . $regra->campos['nsc_metodo_api']
                . '</div>',
            '<div id="divConsulta" class="row col-12 float-start d-none">'
                . $regra->campos['nsc_view_consulta']
                . '</div>',
            $regra->campos['nsc_telefones'],
            $regra->campos['nsc_mensagem_template'],
            $regra->campos['nsc_ativo'],
        ];
    }
```

`store()` — mesma estrutura de sempre (`verificaUnico()`, `transBegin()`/`transCommit()`/`transRollback()`, mensagem centralizada), com a revalidação de `nsc_metodo_api`/`nsc_view_consulta`/`nsc_view_dbgroup` inserida **antes** de montar a Entity e chamar `save()`:

```php
    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret         = [];
        $ret['erro'] = false;
        $postado     = $this->request->getPost();

        // Revalidação da fonte de verdade corrente para os tipos 'api' e
        // 'consulta' (defesa em profundidade — nunca confia apenas na
        // validação de cadastro em LogisNotifSmsConfigModel, seção 4.14).
        if (($postado['nsc_tipo_regra'] ?? '') === 'api') {
            $classeApi          = SMS_API_CONSUMER_CLASS;
            $metodosDisponiveis = $classeApi::metodosDisponiveis();

            if (!array_key_exists($postado['nsc_metodo_api'] ?? '', $metodosDisponiveis)) {
                $ret['erro'] = true;
                $ret['msg']  = 'Método de API selecionado não é mais válido.';
                echo json_encode($ret);
                return;
            }
        }

        if (($postado['nsc_tipo_regra'] ?? '') === 'consulta') {
            // nsc_view_consulta chega do POST como valor composto
            // "dbGroup|view" (select montado em
            // EntLogNotifSmsConfig::defCampos(), seção 4.12). Faz o split
            // e SÓ ENTÃO revalida contra a fonte de verdade corrente
            // (getViewsPorPrefixo()) antes de gravar — nunca confia no
            // valor bruto do POST para nsc_view_dbgroup (poderia ser
            // manipulado no client).
            $valorComposto = (string) ($postado['nsc_view_consulta'] ?? '');
            $partes        = explode('|', $valorComposto, 2);
            $dbGroupPost   = $partes[0] ?? '';
            $viewPost      = $partes[1] ?? '';

            $admDados = new \App\Models\Config\ConfigDicDadosModel();
            $valido   = false;
            foreach ($admDados->getViewsPorPrefixo('vw_sms_') as $v) {
                if ($v['dbGroup'] === $dbGroupPost && $v['view'] === $viewPost) {
                    $valido = true;
                    break;
                }
            }

            if (!$valido) {
                $ret['erro'] = true;
                $ret['msg']  = 'View de consulta selecionada não é mais válida.';
                echo json_encode($ret);
                return;
            }

            // Só grava nsc_view_dbgroup/nsc_view_consulta com os valores já
            // revalidados acima, nunca com o valor bruto do POST.
            $postado['nsc_view_dbgroup']  = $dbGroupPost;
            $postado['nsc_view_consulta'] = $viewPost;
        }

        $regra = new EntLogNotifSmsConfig($postado);

        $exists = $this->common->verificaUnico($this->regras, 'nsc_nome', $postado['nsc_nome'], 'nsc_id', $postado['nsc_id']);

        if (intval($exists) > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 9;
        } else {
            $this->regras->transBegin();

            try {
                if (!$this->regras->save($regra)) {
                    throw new \Exception(implode(' ', $this->regras->errors()));
                }
                $this->regras->transCommit();
                session()->setFlashdata('msg', 'Regra gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Regra gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler']),
                ];
            } catch (\Throwable $e) {
                $this->regras->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Erro ao salvar regra.',
                ];
            }
        }
        echo json_encode($ret);
    }
```

### 4.14 `LogisNotifSmsConfigModel` — `$allowedFields`/`$validationRules`/`$validationMessages` completos

Código atualizado do arquivo `app/Models/Logis/LogisNotifSmsConfigModel.php` (callbacks de auditoria `depoisInsert()`/`depoisUpdate()`/`depoisDelete()` e os métodos `getListaRegras()`/`getRegra()`/`getRegrasAtivas()` permanecem exatamente como já existem hoje, sem alteração). A regra de `nsc_saldo_minimo`/`obrigatorioSeTipoRegraSaldo` (tipo `saldo_baixo`) está marcada explicitamente no código abaixo como **inalterada**:

```php
<?php

namespace App\Models\Logis;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Logistica\EntLogNotifSmsConfig;

/**
 * Regras de Notificação SMS (log_notif_sms_config, prefixo nsc_).
 * Segue o mesmo padrão de App\Models\Config\ConfigCorModel /
 * App\Models\Fornec\FornecNotifDesvioModel (callbacks de auditoria via
 * LogMonModel).
 */
class LogisNotifSmsConfigModel extends Model
{
    protected $DBGroup    = 'dbLogistica';
    protected $table      = 'log_notif_sms_config';
    protected $primaryKey = 'nsc_id';

    protected $returnType     = EntLogNotifSmsConfig::class;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'nsc_excluido';

    protected $allowedFields = [
        'nsc_nome',
        'nsc_tipo_regra',
        'nsc_saldo_minimo',
        'nsc_metodo_api',
        'nsc_view_consulta',
        'nsc_view_dbgroup',
        'nsc_telefones',
        'nsc_mensagem_template',
        'nsc_ativo',
    ];

    protected $validationRules = [
        'nsc_nome'              => 'required|min_length[3]|max_length[100]',
        'nsc_tipo_regra'        => 'required|in_list[saldo_baixo,api,consulta]',
        'nsc_telefones'         => 'required|max_length[255]',
        'nsc_mensagem_template' => 'required',
        // nsc_saldo_minimo / obrigatorioSeTipoRegraSaldo — REGRA NÃO
        // ALTERADA neste ciclo, permanece exatamente igual a antes.
        'nsc_saldo_minimo'      => 'permit_empty|obrigatorioSeTipoRegraSaldo[nsc_tipo_regra]',
        // Entradas NOVAS deste ciclo (tipos 'api'/'consulta').
        'nsc_metodo_api'        => 'permit_empty|obrigatorioSeTipoRegraApi[nsc_tipo_regra]',
        'nsc_view_consulta'     => 'permit_empty|obrigatorioSeTipoRegraConsulta[nsc_tipo_regra]',
    ];

    protected $validationMessages = [
        'nsc_nome' => [
            'required'   => 'O campo Nome da Regra é obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 3 caracteres',
        ],
        'nsc_telefones' => [
            'required' => 'O campo Telefones é obrigatório',
        ],
        'nsc_mensagem_template' => [
            'required' => 'O campo Mensagem é obrigatório',
        ],
        // Mensagem de nsc_saldo_minimo — NÃO ALTERADA neste ciclo.
        'nsc_saldo_minimo' => [
            'obrigatorioSeTipoRegraSaldo' => 'O campo Saldo Mínimo é obrigatório para regras do tipo Saldo Baixo',
        ],
        'nsc_metodo_api' => [
            'obrigatorioSeTipoRegraApi' => 'O campo Método da API é obrigatório para regras do tipo API',
        ],
        'nsc_view_consulta' => [
            'obrigatorioSeTipoRegraConsulta' => 'O campo View de Consulta é obrigatório para regras do tipo Consulta',
        ],
    ];

    // Callbacks de auditoria (LogMonModel) — inalterados
    protected $allowCallbacks = true;
    protected $afterInsert    = ['depoisInsert'];
    protected $afterUpdate    = ['depoisUpdate'];
    protected $afterDelete    = ['depoisDelete'];

    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }

    public function getListaRegras()
    {
        return $this->orderBy('nsc_ativo, nsc_nome')->findAll();
    }

    public function getRegra($id)
    {
        return $this
            ->when($id !== null, fn($q) => $q->where('nsc_id', $id))
            ->first();
    }

    public function getRegrasAtivas()
    {
        return $this->where('nsc_ativo', 'A')->findAll();
    }
}
```

### 4.15 `MyValidation.php` — `obrigatorioSeTipoRegraApi`/`obrigatorioSeTipoRegraConsulta` completos

`obrigatorioSeTipoRegraEntrega` é **removido** (tipo `entrega` descontinuado, ver seção 3, item 2). `obrigatorioSeTipoRegraSaldo` **não é tocado** — reproduzido abaixo apenas para registro de que permanece exatamente igual. Os 2 métodos novos seguem o mesmo molde exato (mesma assinatura de regra de validação customizada do CI4: `($value, ?string $params, array $data): bool`):

```php
    /**
     * Notificações SMS (Logística): campo nsc_saldo_minimo (grupo "Saldo
     * Baixo") só é obrigatório quando nsc_tipo_regra = 'saldo_baixo'.
     * NÃO ALTERADO neste ciclo (ver seção 4.10 do documento).
     * Uso: obrigatorioSeTipoRegraSaldo[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraSaldo($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'saldo_baixo') {
            return true;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Notificações SMS (Logística): campo nsc_metodo_api (grupo "API") só
     * é obrigatório quando nsc_tipo_regra = 'api'. Mesmo molde de
     * obrigatorioSeTipoRegraSaldo, só troca o valor de comparação.
     * Uso: obrigatorioSeTipoRegraApi[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraApi($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'api') {
            return true;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Notificações SMS (Logística): campo nsc_view_consulta (grupo
     * "Consulta") só é obrigatório quando nsc_tipo_regra = 'consulta'.
     * Mesmo molde de obrigatorioSeTipoRegraSaldo/Api.
     * Uso: obrigatorioSeTipoRegraConsulta[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraConsulta($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'consulta') {
            return true;
        }

        return trim((string) $value) !== '';
    }
```

Os demais métodos da classe (`nome_status_existe()`, `isUniqueValue()`, `obrigatorioSeNotivisaSim()`) permanecem exatamente como já existem hoje em `app/Controllers/MyValidation.php`, sem nenhuma alteração.

### 4.16 `my_fields.js` — `alternaCamposTipoRegra()` completo

Expande de 2 para 3 ramos (`saldo_baixo`/`api`/`consulta`), continuando a delegar a obrigatoriedade a `mudaObrigatorioElemDiv()` já existente (não reimplementada):

```js
/**
 * alternaCamposTipoRegra
 * Notificações SMS (Logística) — toggle dos grupos de campos condicionais
 * por nsc_tipo_regra (saldo_baixo / api / consulta), moldado em
 * verificaTipoAcao(), porém mais simples (3 valores estáticos, sem
 * consulta AJAX). Cuida apenas do show/hide dos wrappers
 * #divSaldo/#divApi/#divConsulta e delega a obrigatoriedade condicional
 * para mudaObrigatorioElemDiv() já existente (não reimplementada aqui).
 */
function alternaCamposTipoRegra(obj) {
  var tipo = jQuery(obj).val();
  var saldo = tipo == "saldo_baixo";
  var api = tipo == "api";
  var consulta = tipo == "consulta";

  // Usa a classe "d-none" (em vez de jQuery.toggle(), que só manipula o
  // style inline) porque o HTML inicial de #divApi/#divConsulta já nasce
  // com d-none por padrão (nsc_tipo_regra default = 'saldo_baixo') — ver
  // EntLogNotifSmsConfig/NotifSmsConfig::montaCamposFormulario() (seções
  // 4.12/4.13). Alternar apenas o display inline não sobrepõe o
  // "!important" da classe.
  jQuery("#divSaldo").toggleClass("d-none", !saldo);
  jQuery("#divApi").toggleClass("d-none", !api);
  jQuery("#divConsulta").toggleClass("d-none", !consulta);

  mudaObrigatorioElemDiv("#divSaldo", saldo);
  mudaObrigatorioElemDiv("#divApi", api);
  mudaObrigatorioElemDiv("#divConsulta", consulta);
}
```

`mudaObrigatorioElemDiv()` (já existente em `public/assets/jscript/my_fields.js`) não é alterada — continua recebendo o seletor da div e um booleano, aplicando `required` a todos os `input, select, textarea` (exceto `input[type='search']`) dentro dela.

------------------------------------------------------------------------

## 5. Novo Schema de `log_notif_sms_config`

| Coluna | Antes | Depois |
|---|---|---|
| `nsc_tipo_regra` | `ENUM('entrega','saldo_baixo')` | `ENUM('saldo_baixo','api','consulta') NOT NULL DEFAULT 'saldo_baixo'` — `'entrega'` removido do `ENUM` (exclusão definitiva das regras antigas, ver 4.6) |
| `nsc_ren_tipo` | usado por `entrega` | **removida** |
| `nsc_ren_status_max` | usado por `entrega` | **removida** |
| `nsc_condicao` | usado por `entrega` | **removida** |
| `nsc_minutos_limite` | usado por `entrega` | **removida** |
| `nsc_saldo_minimo` | usado por `saldo_baixo` | inalterada |
| `nsc_metodo_api` | — | **nova**, `VARCHAR(150) NULL` |
| `nsc_view_consulta` | — | **nova**, `VARCHAR(100) NULL` |
| `nsc_view_dbgroup` | — | **nova**, `VARCHAR(30) NULL` |
| demais colunas | — | inalteradas |

`log_notif_sms_enviadas`: **nenhuma alteração de schema**.

### 5.1 Migration — `2026-07-22-000001_LogisticaNotifSmsTiposApiConsulta.php`

`up()`, nesta ordem:
1. Adiciona `nsc_metodo_api`/`nsc_view_consulta`/`nsc_view_dbgroup` (idempotente via `information_schema`).
2. `DELETE` das regras `nsc_tipo_regra = 'entrega'` (definitivo, ver 4.6).
3. `MODIFY` do `ENUM` para `('saldo_baixo','api','consulta') DEFAULT 'saldo_baixo'`.
4. `DROP COLUMN` de `nsc_ren_tipo`/`nsc_ren_status_max`/`nsc_condicao`/`nsc_minutos_limite` (condicional/idempotente).

Código completo do arquivo `app/Database/Migrations/2026-07-22-000001_LogisticaNotifSmsTiposApiConsulta.php`, moldado exatamente no mesmo estilo de `app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php` (namespace `App\Database\Migrations`, `extends Migration`, checagem de existência de coluna via `information_schema` antes de qualquer `ALTER TABLE`, `CLI::write()` para log de execução em `up()`):

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Migration;

/**
 * Reformulação dos tipos de alerta de log_notif_sms_config (Notificações
 * SMS — Logística): troca de 'entrega'/'saldo_baixo' para
 * 'saldo_baixo'/'api'/'consulta', conforme
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md (seções 4.6 e
 * 5.1). Moldada em
 * app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php (mesmo
 * padrão de checagem de existência via information_schema e CLI::write()
 * para log de execução).
 *
 * ATENÇÃO OPERACIONAL: esta migration EXCLUI DEFINITIVAMENTE as regras
 * nsc_tipo_regra = 'entrega' já cadastradas (decisão de negócio confirmada
 * pelo Douglas, ver seção 3, item 2 do documento) — não roda em produção
 * sem confirmação explícita do usuário.
 */
class LogisticaNotifSmsTiposApiConsulta extends Migration
{
    public function up()
    {
        $this->adicionaColunasNovas();
        $this->excluiRegrasEntrega();
        $this->alteraEnumTipoRegra();
        $this->removeColunasObsoletas();
    }

    public function down()
    {
        $this->restauraColunasObsoletas();
        $this->restauraEnumTipoRegra();
        $this->removeColunasNovas();

        // Reversão apenas estrutural — não recupera as regras 'entrega'
        // excluídas definitivamente por up() nem quaisquer dados perdidos
        // pelas colunas removidas (ver seção 4.6/5.1 do documento).
    }

    /**
     * Passo 1 de up(): nsc_metodo_api / nsc_view_consulta / nsc_view_dbgroup.
     * Idempotente — só adiciona a coluna se ainda não existir.
     */
    private function adicionaColunasNovas(): void
    {
        $db = db_connect('dbLogistica');

        $colunasNovas = [
            'nsc_metodo_api'    => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_metodo_api VARCHAR(150) NULL',
            'nsc_view_consulta' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_view_consulta VARCHAR(100) NULL',
            'nsc_view_dbgroup'  => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_view_dbgroup VARCHAR(30) NULL',
        ];

        foreach ($colunasNovas as $coluna => $sql) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd === 0) {
                $db->query($sql);
                CLI::write("Coluna {$coluna} adicionada em log_notif_sms_config.", 'yellow');
            }
        }
    }

    /**
     * Passo 2 de up(): exclusão definitiva das regras 'entrega' (código
     * reaproveitado da seção 4.6 do documento). Precisa rodar ANTES do
     * MODIFY do ENUM (passo 3) — senão o MODIFY encontraria linha com
     * valor 'entrega' incompatível com o novo ENUM.
     */
    private function excluiRegrasEntrega(): void
    {
        $db  = db_connect('dbLogistica');
        $qtd = $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->countAllResults();

        if ($qtd > 0) {
            $db->table('log_notif_sms_config')->where('nsc_tipo_regra', 'entrega')->delete();
            CLI::write("{$qtd} regra(s) do tipo 'entrega' excluída(s) definitivamente (tipo descontinuado, confirmado pelo Douglas).", 'yellow');
        }
    }

    /**
     * Passo 3 de up(): troca do ENUM de nsc_tipo_regra. Só roda depois do
     * passo 2 (exclusão das linhas 'entrega'), garantindo que nenhuma
     * linha remanescente tenha valor incompatível com o novo ENUM.
     */
    private function alteraEnumTipoRegra(): void
    {
        $db = db_connect('dbLogistica');
        $db->query(
            "ALTER TABLE log_notif_sms_config
             MODIFY nsc_tipo_regra ENUM('saldo_baixo','api','consulta') NOT NULL DEFAULT 'saldo_baixo'"
        );
        CLI::write("ENUM de nsc_tipo_regra alterado para ('saldo_baixo','api','consulta') DEFAULT 'saldo_baixo'.", 'yellow');
    }

    /**
     * Passo 4 de up(): remoção das 4 colunas exclusivas do tipo 'entrega'
     * (descontinuado). Condicional/idempotente — só remove se a coluna
     * ainda existir.
     */
    private function removeColunasObsoletas(): void
    {
        $db = db_connect('dbLogistica');

        foreach (['nsc_ren_tipo', 'nsc_ren_status_max', 'nsc_condicao', 'nsc_minutos_limite'] as $coluna) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd > 0) {
                $db->query("ALTER TABLE log_notif_sms_config DROP COLUMN {$coluna}");
                CLI::write("Coluna {$coluna} removida de log_notif_sms_config (exclusiva do tipo 'entrega', descontinuado).", 'yellow');
            }
        }
    }

    /**
     * down() — passo 1: recria (condicional) as 4 colunas removidas por
     * removeColunasObsoletas(), com a mesma definição da tabela original
     * (2026-07-21-000001_LogisticaNotifSms.php). Estrutural apenas —
     * dados antigos não são recuperados.
     */
    private function restauraColunasObsoletas(): void
    {
        $db = db_connect('dbLogistica');

        $colunas = [
            'nsc_ren_tipo'       => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_ren_tipo INT NULL',
            'nsc_ren_status_max' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_ren_status_max INT NULL',
            'nsc_condicao'       => "ALTER TABLE log_notif_sms_config ADD COLUMN nsc_condicao ENUM('antes_chegada','apos_chegada') NULL",
            'nsc_minutos_limite' => 'ALTER TABLE log_notif_sms_config ADD COLUMN nsc_minutos_limite INT NULL',
        ];

        foreach ($colunas as $coluna => $sql) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd === 0) {
                $db->query($sql);
            }
        }
    }

    /**
     * down() — passo 2: volta o ENUM de nsc_tipo_regra ao formato anterior
     * a este ciclo.
     */
    private function restauraEnumTipoRegra(): void
    {
        $db = db_connect('dbLogistica');
        $db->query(
            "ALTER TABLE log_notif_sms_config
             MODIFY nsc_tipo_regra ENUM('entrega','saldo_baixo') NOT NULL DEFAULT 'entrega'"
        );
    }

    /**
     * down() — passo 3: remove as 3 colunas novas deste ciclo (condicional).
     */
    private function removeColunasNovas(): void
    {
        $db = db_connect('dbLogistica');

        foreach (['nsc_metodo_api', 'nsc_view_consulta', 'nsc_view_dbgroup'] as $coluna) {
            $existe = $db->query(
                "SELECT COUNT(*) AS qtd FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                ['log_notif_sms_config', $coluna]
            )->getRow();

            if ($existe && (int) $existe->qtd > 0) {
                $db->query("ALTER TABLE log_notif_sms_config DROP COLUMN {$coluna}");
            }
        }
    }
}
```

`down()`: reverte a estrutura (não recupera dados das linhas `entrega` excluídas nem das colunas removidas — reversão é apenas estrutural).

**Atenção operacional (regra do usuário — nunca rodar migration sem confirmação explícita):** esta migration não roda em produção sem confirmação explícita do Douglas, dado que exclui dados (regras `entrega`) de forma definitiva.

------------------------------------------------------------------------

## 6. Motor de execução compartilhado — `SmsRegraExecutor`

Arquivo novo `app/Libraries/Sms/SmsRegraExecutor.php`:

```php
namespace App\Libraries\Sms;

use App\Libraries\SmsService;
use App\Models\Logis\LogisNotifSmsEnviadasModel;

class SmsRegraExecutor
{
    public function __construct(
        private SmsService $smsService,
        private LogisNotifSmsEnviadasModel $enviadasModel
    ) {}

    /** @param array<int, array<string,mixed>> $objetos */
    public function processar(array $objetos, string $templateMensagem, string $telefonesCsv, int $nscId, string $prefixoChave): int
    {
        $enviados = 0;
        foreach ($objetos as $objeto) {
            if (!is_array($objeto)) { continue; }

            $chave = $prefixoChave . ':' . $this->extrairIdDedup($objeto);
            if ($this->enviadasModel->jaEnviado($chave, $nscId)) { continue; }

            $msg = $this->substituiPlaceholders($templateMensagem, $objeto);
            foreach (explode(',', $telefonesCsv) as $tel) {
                $this->smsService->enviar(trim($tel), $msg);
            }
            $this->enviadasModel->registrar($chave, $nscId);
            $enviados++;
        }
        return $enviados;
    }

    private function extrairIdDedup(array $objeto): string
    {
        return isset($objeto['id']) ? (string) $objeto['id'] : md5(json_encode($objeto));
    }

    private function substituiPlaceholders(string $template, array $objeto): string
    {
        $mapa = [];
        foreach ($objeto as $chave => $valor) {
            if (is_array($valor) || is_object($valor)) { continue; }
            $mapa['[' . $chave . ']'] = (string) $valor;
        }
        return strtr($template, $mapa);
    }
}
```

`SmsRegraExecutor` é a única classe compartilhada entre os tipos `api` e `consulta` — recebe já pronta a lista de objetos (vinda de `SmsApiConsumer` ou de uma view `vw_sms_*`), sem conhecer a origem dos dados. Reaproveita `SmsService` sem alteração (ver `docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx`) e `LogisNotifSmsEnviadasModel::jaEnviado()`/`registrar()` já existentes (ver `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`, seção 6.2).

------------------------------------------------------------------------

## 7. `NotifSmsVerificar.php` — dispatch final (3 tipos)

```php
public function run(array $params)
{
    helper('api_cw2');

    $logger        = service('logger');
    $configModel   = new LogisNotifSmsConfigModel();
    $enviadasModel = new LogisNotifSmsEnviadasModel();
    $smsService    = new SmsService();
    $executor      = new SmsRegraExecutor($smsService, $enviadasModel);

    foreach ($configModel->getRegrasAtivas() as $regra) {
        try {
            switch ($regra->nsc_tipo_regra) {
                case 'saldo_baixo':
                    $this->processarRegraSaldo($regra, $smsService, $enviadasModel);
                    break;
                case 'api':
                    $this->processarRegraApi($regra, $executor);
                    break;
                case 'consulta':
                    $this->processarRegraConsulta($regra, $executor);
                    break;
                default:
                    $logger->warning("notifsms:verificar - tipo de regra desconhecido '{$regra->nsc_tipo_regra}' (regra {$regra->nsc_id}).");
            }
        } catch (\Throwable $e) {
            $logger->error('notifsms:verificar - regra ' . $regra->nsc_id . ': ' . $e->getMessage());
            CLI::error('Erro na regra ' . $regra->nsc_id . ': ' . $e->getMessage());
        }
    }
    CLI::write('Verificação concluída.', 'green');
}

private function processarRegraApi(object $regra, SmsRegraExecutor $executor): void
{
    $logger = service('logger');
    $classe = SMS_API_CONSUMER_CLASS;

    $metodosDisponiveis = $classe::metodosDisponiveis();
    if (!array_key_exists($regra->nsc_metodo_api, $metodosDisponiveis)) {
        $logger->warning("notifsms:verificar - método de API '{$regra->nsc_metodo_api}' inválido (regra {$regra->nsc_id}).");
        return;
    }

    $consumidor = new $classe();
    $resultado  = $consumidor->{$regra->nsc_metodo_api}();

    if (!is_array($resultado) || !array_is_list($resultado) || empty($resultado)) {
        return;
    }

    $executor->processar($resultado, $regra->nsc_mensagem_template, $regra->nsc_telefones, $regra->nsc_id, 'API');
}

private function processarRegraConsulta(object $regra, SmsRegraExecutor $executor): void
{
    $logger   = service('logger');
    $admDados = new \App\Models\Config\ConfigDicDadosModel();

    $valida = false;
    foreach ($admDados->getViewsPorPrefixo('vw_sms_') as $v) {
        if ($v['dbGroup'] === $regra->nsc_view_dbgroup && $v['view'] === $regra->nsc_view_consulta) {
            $valida = true;
            break;
        }
    }
    if (!$valida) {
        $logger->warning("notifsms:verificar - view '{$regra->nsc_view_consulta}' não é mais válida (regra {$regra->nsc_id}).");
        return;
    }

    $resultado = db_connect($regra->nsc_view_dbgroup)
        ->table($regra->nsc_view_consulta)
        ->get(SMS_CONSULTA_LIMITE_LINHAS)
        ->getResultArray();

    if (empty($resultado)) { return; }

    $executor->processar($resultado, $regra->nsc_mensagem_template, $regra->nsc_telefones, $regra->nsc_id, 'CONSULTA');
}
```

`processarRegraSaldo()` permanece **100% inalterado** (mesma implementação de `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`, seção 7.1). `processarRegraEntrega()` é **removido** (tipo descontinuado, ver seção 3, item 2).

------------------------------------------------------------------------

## 8. Estrutura de Arquivos

**Migration (nova, incremental):**
```
app/Database/Migrations/2026-07-22-000001_LogisticaNotifSmsTiposApiConsulta.php
```
Ver detalhamento da ordem de operações e código completo (`up()`/`down()`) na seção 5.1.

**Backend — criar:**
```
app/Libraries/Sms/SmsApiConsumer.php
app/Libraries/Sms/SmsRegraExecutor.php
```

**Backend — alterar:**
```
app/Controllers/Logistica/NotifSmsConfig.php     (3 divs; store() revalida nsc_metodo_api/nsc_view_consulta — código completo na seção 4.13)
app/Entities/Logistica/EntLogNotifSmsConfig.php  (default 'saldo_baixo'; novos campos; remove campos de "entrega" — código completo na seção 4.12)
app/Models/Logis/LogisNotifSmsConfigModel.php    (allowedFields/validationRules/validationMessages — código completo na seção 4.14)
app/Controllers/MyValidation.php                  (remove obrigatorioSeTipoRegraEntrega; adiciona obrigatorioSeTipoRegraApi/Consulta — código completo na seção 4.15)
app/Commands/NotifSmsVerificar.php                (dispatch de 3 tipos; processarRegraApi/Consulta; remove processarRegraEntrega — código completo na seção 7)
app/Models/Config/ConfigDicDadosModel.php          (getViewsPorPrefixo(); $dbGroupsConhecidos incluindo dbLogistica — código completo na seção 4.2)
app/Config/Constants.php                           (SMS_API_CONSUMER_CLASS, SMS_CONSULTA_LIMITE_LINHAS = 100; LINK_LOGISTICA/LOGISTICA_API_KEY mantidos)
```

**Frontend — alterar:**
```
public/assets/jscript/my_fields.js   (alternaCamposTipoRegra para 3 grupos — código completo na seção 4.16)
```

**Sem alteração:** `NotifSmsEnviadas.php`, `LogisNotifSmsEnviadasModel.php`, `Routes.php`, `SmsService.php` e providers (`SmsProviderInterface`/`SmsDevProvider`/`GtiSmsProvider`).

------------------------------------------------------------------------

## 9. Ordem de Implementação

1. `ConfigDicDadosModel::getViewsPorPrefixo()` + centralização de `$dbGroupsConhecidos`.
2. `app/Libraries/Sms/SmsApiConsumer.php` (com `buscarRenovacoesPendentes()`) e `SmsRegraExecutor.php`.
3. Constantes novas em `Constants.php`.
4. Migration `2026-07-22-000001_...` — rodar só em dev.
5. `LogisNotifSmsConfigModel` + `MyValidation.php`.
6. `EntLogNotifSmsConfig` (3 grupos, novo default) + `NotifSmsConfig.php` (Controller).
7. `my_fields.js`.
8. `NotifSmsVerificar.php` (dispatch de 3 tipos).
9. Verificação manual em dev: cadastro dos 3 tipos, disparo do comando, `saldo_baixo` sem regressão, `consulta` de ponta a ponta contra uma `vw_sms_*` real de teste, `api` validado ao menos com um método de teste local.

------------------------------------------------------------------------

## 10. Pendências Fora Deste Repositório

- Implementação do endpoint `GET /renovacoes/pendentes` no repositório do Logística antigo — segue pendente desde o ciclo anterior (`docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`, seção 12). `SmsApiConsumer::buscarRenovacoesPendentes()` fica pronto/selecionável na tela desde já, mas **não é testável de ponta a ponta** enquanto o endpoint não existir do outro lado (ciente e confirmado pelo Douglas, ver seção 3, item 4).
- Definição do critério real de "renovação pendente" (`$renStatusMax`) com quem administra o Logística antigo, quando o endpoint existir — o default `4` usado em `buscarRenovacoesPendentes()` é herdado do exemplo de regra do desenho original (`docs/notificacoes-sms.md`) e deve ser revisado nesse momento.
- Criação de ao menos uma view `vw_sms_*` real de teste em algum dos `dbGroups` conhecidos, para viabilizar a verificação de ponta a ponta do tipo `consulta` (ver seção 9, item 9) — não é responsabilidade de codificação deste ciclo, mas é pré-requisito de ambiente para o teste manual.

Nenhum destes itens é responsabilidade de codificação do `bydev` neste ciclo.

------------------------------------------------------------------------

## 11. Verificação / Teste Manual

1. Rodar a migration em ambiente de dev; confirmar que regras `entrega` pré-existentes são excluídas definitivamente, que o `ENUM` de `nsc_tipo_regra` não contém mais `'entrega'`, que as 4 colunas obsoletas foram removidas e que as 3 colunas novas existem.
2. Tela `NotifSmsConfig`:
   - Criar regra tipo `saldo_baixo` — confirmar que o comportamento é **idêntico** ao já existente antes deste ciclo (regressão zero).
   - Criar regra tipo `api`, selecionando um método de `SmsApiConsumer::metodosDisponiveis()` — confirmar toggle de grupo (`#divApi`) e gravação de `nsc_metodo_api`.
   - Criar regra tipo `consulta`, selecionando uma view `vw_sms_*` de teste — confirmar toggle de grupo (`#divConsulta`) e gravação de `nsc_view_consulta`/`nsc_view_dbgroup`.
   - Confirmar que o toggle de 3 grupos funciona tanto no `onchange` do select (`add()`) quanto no carregamento da tela de edição (`edit()`), refletindo o estado salvo.
   - Confirmar que `store()` revalida `nsc_metodo_api` e `nsc_view_consulta`/`nsc_view_dbgroup` contra a fonte de verdade corrente (reflection / `getViewsPorPrefixo()`) antes de gravar, rejeitando valores que não existem mais.
3. Rodar `php spark notifsms:verificar` manualmente em ambiente de dev:
   - `saldo_baixo`: confirmar que não há nenhuma regressão de comportamento em relação ao ciclo anterior.
   - `consulta`: contra uma `vw_sms_*` real de teste, confirmar SELECT via Query Builder, respeito ao limite `SMS_CONSULTA_LIMITE_LINHAS`, substituição de placeholders `[campo]` e deduplicação (`'CONSULTA:' . id`).
   - `api`: com ao menos um método de teste local (mock/stub), confirmar chamada sem argumentos, checagem de `is_array()`/`array_is_list()`/não-vazio, substituição de placeholders e deduplicação (`'API:' . id`).
   - `buscarRenovacoesPendentes()`: **marcado como "não testável de ponta a ponta nesta rodada"** (endpoint externo ainda não existe) — não bloqueia o fechamento do ciclo (ver seção 10).
4. Confirmar que um placeholder sem correspondência no JSON/linha retornada permanece literal na mensagem (ex.: `[campo_inexistente]`), sem virar string vazia.
5. Confirmar que `nsc_tipo_regra` desconhecido (linha manipulada diretamente no banco, fora do CRUD) gera apenas um `warning` de log, sem interromper o processamento das demais regras.

------------------------------------------------------------------------

## 12. Critérios de Pronto

- `SmsApiConsumer`/`SmsRegraExecutor` implementados exatamente conforme especificado (seções 4.1 e 6), com `metodosDisponiveis()` reflectindo corretamente.
- Migration executada em dev com sucesso: regras `entrega` removidas, `ENUM` sem `'entrega'`, colunas obsoletas removidas, novas colunas presentes (seção 5).
- CRUD `NotifSmsConfig` cobrindo os 3 tipos, toggle de 3 grupos funcionando em `add()` e `edit()`, `nsc_metodo_api`/`nsc_view_consulta` sempre revalidados no `store()`.
- `NotifSmsVerificar` despachando corretamente para os 3 tipos (seção 7), com `saldo_baixo` sem nenhuma regressão de comportamento.
- `SmsRegraExecutor` coberto por teste unitário isolado.
- `NotifSmsEnviadas` continua funcionando sem alteração de código.
- `byrev` sem apontamentos pendentes; `bytest` cobrindo os 3 tipos, com `buscarRenovacoesPendentes()` marcado como "não testável de ponta a ponta nesta rodada" sem bloquear o fechamento do ciclo.

------------------------------------------------------------------------

## 13. Rastreabilidade

Este documento formaliza, para codificação, o plano aprovado pelo `byarq` para a reformulação completa dos tipos de alerta de Notificações SMS, **substituindo** a especificação de `NotifSmsConfig`/`EntLogNotifSmsConfig`/schema de `log_notif_sms_config` dos ciclos anteriores da feature:

- `docs/notificacoes-sms.md` — desenho técnico original (schema com `entrega`/`saldo_baixo`, motor de regras) — **schema e tipo `entrega` substituídos** pelo conteúdo deste documento (seções 4 a 7); demais decisões de arquitetura original (motor configurável via tabela, deduplicação, cron via SO) permanecem válidas.
- `docs/desenvolvimento/notificacoes-sms-dev.docx` — CRUD `NotifSmsConfig`/`NotifSmsEnviadas` original (2 tipos) — **seção 3.4 (`nsc_ren_tipo` estático), 3.7 (toggle 2 grupos) e schema da seção 4.2 substituídos** por este documento; demais decisões (infraestrutura `dbLogistica`, auditoria via `LogMonModel`, permissões `CAEXN`) permanecem válidas e inalteradas.
- `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` — `SmsService` original, comando CLI, contrato da API do Logística antigo — **`processarRegraEntrega()` (seção 7.2) removido e substituído** por `processarRegraApi()`/`processarRegraConsulta()` (seção 7 deste documento); o contrato do endpoint `/renovacoes/pendentes` (seção 4 daquele documento) permanece válido e é reaproveitado integralmente por `SmsApiConsumer::buscarRenovacoesPendentes()` (seção 4.1 deste documento).
- `docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx` — `SmsProviderInterface`/`SmsDevProvider`/`GtiSmsProvider`/`SmsService` — **não substituído, permanece válido e inalterado**; `SmsRegraExecutor` (seção 6 deste documento) apenas consome `SmsService::enviar()`, sem nenhuma alteração naquele documento.
- `docs/entrega/notificacoes-sms-entrega.docx` e `docs/entrega/notificacoes-sms-servico-envio-entrega.docx` — documentos de entrega dos ciclos anteriores, permanecem como registro histórico do que foi entregue em cada rodada; a pendência de `SMSDEV_API_KEY`/`GTISMS_API_KEY` ali sinalizada permanece válida, complementada pela pendência do endpoint `/renovacoes/pendentes` retomada na seção 10 deste documento.

Qualquer apontamento de revisão (`byrev`) ou caso de teste (`bytest`) sobre esta parte da feature deve referenciar a seção correspondente deste documento (seção 4 para as decisões de arquitetura, seção 5 para o schema, seção 6 para `SmsRegraExecutor`, seção 7 para o dispatch do comando CLI).
