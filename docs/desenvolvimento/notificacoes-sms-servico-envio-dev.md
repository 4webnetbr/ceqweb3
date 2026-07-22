# Documento de Desenvolvimento — Módulo Logística (CeqWeb 3.0): Serviço de Envio de SMS (SmsService + Comando CLI + Cron)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (já existente — infraestrutura `dbLogistica`/`cfg_modulo` criada no ciclo anterior)
**Componentes envolvidos:** `SmsService` (biblioteca de integração com o provedor SMS Dev) · `NotifSmsVerificar` (comando CLI, disparado por cron) · consumo de API nova do Logística antigo (endpoint a ser criado, fora deste repositório)
**Tipo de trabalho:** Continuação de feature — parte administrativa (CRUD `NotifSmsConfig` + consulta `NotifSmsEnviadas`) já entregue em `docs/entrega/notificacoes-sms-entrega.docx`. Este ciclo cobre a parte que efetivamente envia os SMS.
**Origem:** Desenho técnico original em `docs/notificacoes-sms.md`, com ajustes de convenção aprovados pelo usuário (Douglas) e formalizados neste documento.
**Status:** Plano aprovado pelo usuário. Aprovado para codificação.

------------------------------------------------------------------------

## 1. Objetivo

Implementar a parte da feature de Notificações SMS que efetivamente dispara os SMS: a biblioteca `SmsService` (integração com o provedor SMS Dev), o comando CLI que lê as regras ativas em `log_notif_sms_config` e decide quando disparar (`NotifSmsVerificar`), e a documentação do cron do SO que o executa periodicamente.

Este documento consolida o plano aprovado para uso direto do `bydev` na codificação — contém toda a estrutura de arquivos, o contrato da API nova a ser consumida, e os métodos de Model necessários.

------------------------------------------------------------------------

## 2. Escopo

**Dentro do escopo:**
- Biblioteca `SmsService` (`app/Libraries/SmsService.php`) — envio de SMS e consulta de saldo via cURL direto para o provedor SMS Dev.
- Comando CLI `NotifSmsVerificar` (`app/Commands/NotifSmsVerificar.php`) — execução única (sem loop/daemon), varre as regras ativas e dispara os SMS conforme o tipo de regra (`entrega` ou `saldo_baixo`).
- Consumo de uma API nova do Logística antigo (`https://logistica.ceqnep.com.br/api2026/renovacoes/pendentes`) para obter dados de entrega/renovação (`ren_tipo`/`ren_status`/`ren_prev_chegada`), via helper `api_request()` já existente (`api_cw2_helper.php`).
- Nova constante `LINK_LOGISTICA` em `app/Config/Constants.php`.
- Novos métodos `getRegrasAtivas()` em `LogisNotifSmsConfigModel` e `jaEnviado()`/`registrar()` em `LogisNotifSmsEnviadasModel`.
- Especificação do contrato do endpoint novo `/renovacoes/pendentes` (a ser implementado no lado do Logística antigo, fora deste repositório) e documentação do cron do SO (não é arquivo do repo).

**Fora do escopo deste ciclo:**
- Implementação do endpoint `/renovacoes/pendentes` em si — pertence ao repositório do Logística antigo, não ao CeqWeb3. Este documento apenas especifica o contrato esperado.
- Geração/definição de `LOGISTICA_API_KEY` e `SMSDEV_API_KEY` em produção — pendências operacionais, não de código.
- Configuração da linha de cron no servidor — documentado aqui apenas como referência para quem administra o servidor.
- Qualquer alteração nas telas administrativas (`NotifSmsConfig`/`NotifSmsEnviadas`), já entregues e fechadas no ciclo anterior.

------------------------------------------------------------------------

## 3. Decisões de Arquitetura

### 3.1 Convenção de comando CLI — `App\Commands\*`, não `App\Controllers\Cli\*`

O desenho original em `docs/notificacoes-sms.md` propunha um Controller CLI (`app/Controllers/Cli/NotificacaoSms.php`). A convenção real já em uso no projeto é `App\Commands\*` (extends `BaseCommand`, registrado via `spark`), conforme o único comando existente hoje: `app/Commands/WorkAnalise.php`.

O novo comando (`NotifSmsVerificar`) reaproveita desse arquivo:
- O padrão de log: `service('logger')` + `CLI::write()`/`CLI::error()`.
- O padrão de tratamento de exceção: `try/catch (\Throwable $e)` — porém aplicado **por regra**, dentro do loop de regras ativas, e não em torno de todo o comando, para que uma regra com erro não impeça a execução das demais.

**Diferença importante:** `WorkAnalise` é um daemon (`while (true) { ... sleep(300); }`), pensado para rodar continuamente em background. `NotifSmsVerificar` **não deve ser daemon** — é execução única, porque o disparo periódico já é responsabilidade do cron do SO (a cada 5 minutos, ver 3.6). Rodar como daemon aqui duplicaria a responsabilidade de agendamento e complicaria o gerenciamento do processo (não há um supervisor de processo tipo `systemd`/`supervisord` previsto para este comando).

### 3.2 Origem dos dados de entrega/renovação — API nova do Logística antigo

O desenho original (`docs/notificacoes-sms.md`) previa um `RenovacaoModel` local consultando diretamente uma tabela de renovação/entrega. Esse Model **nunca existiu neste projeto** e os dados de `ren_tipo`/`ren_status`/`ren_prev_chegada` vivem no banco Postgres do projeto Logística antigo (outro repositório), sem tabela local nem API já existente para isso no CeqWeb3.

**Decisão confirmada com o usuário:** existe uma API do Logística antigo em `https://logistica.ceqnep.com.br/api2026`, mas ainda sem endpoint para consultar entregas/renovações pendentes. Este documento:
- Especifica o **contrato** de um endpoint novo, `/renovacoes/pendentes` (ver seção 4), a ser criado no lado do Logística antigo — **fora deste repositório**, fora do escopo de codificação deste ciclo.
- Implementa, no CeqWeb3, o **consumo** desse endpoint via `api_request()` (helper genérico já existente em `api_cw2_helper.php`, também usado por `app/Controllers/Estoque/Requisicao.php`), sem reinventar cliente HTTP.

Autenticação do endpoint via header `X-Api-Key`, valor em `LOGISTICA_API_KEY` (variável de ambiente, `.env` nos dois lados) — **decisão a validar com quem administra o Logística antigo**, não é algo que o CeqWeb3 decide unilateralmente.

### 3.3 `SmsService` — mantido como desenhado originalmente

Sem mudanças em relação a `docs/notificacoes-sms.md`: integração via cURL direto (sem biblioteca HTTP adicional) com os endpoints `https://api.smsdev.com.br/v1/send` e `https://api.smsdev.com.br/v1/balance`, chave lida de `getenv('SMSDEV_API_KEY')`. Ver especificação completa na seção 5.

### 3.4 Tratamento de falha da API do Logística antigo — não travar as demais regras

Se `api_request()` retornar `null` (API fora do ar, erro de rede, timeout), a regra de entrega correspondente é **pulada** (não processada nesta execução) e o erro é logado — sem interromper o processamento das demais regras ativas. Consistente com a decisão de `try/catch` por regra (ver 3.1).

### 3.5 Deduplicação — reaproveitada do desenho original, sem mudanças

- Regra `saldo_baixo`: chave `'SALDO:' . date('Y-m-d')` — no máximo 1 alerta por dia por regra, evita flood enquanto o saldo permanecer baixo.
- Regra `entrega`: chave `'REN:' . $ren['ren_id']` — 1 alerta por renovação/entrega por regra, evita reenvio a cada execução do cron enquanto a condição de tempo permanecer verdadeira.
- Ambas as chaves usam a mesma tabela `log_notif_sms_enviadas` (`nse_chave`/`nse_nsc_id`), já criada no ciclo anterior, agora com os métodos `jaEnviado()`/`registrar()` implementados (ver seção 6).

### 3.6 Cron do SO — não é artefato do repositório

O agendamento é feito pelo cron do sistema operacional do servidor, fora do controle de versão deste projeto. Este documento apenas registra a linha esperada (ver seção 7) para quem administra o servidor configurar — o caminho real do `spark` e do arquivo de log deve ser confirmado com quem administra o servidor antes de ativar.

------------------------------------------------------------------------

## 4. Contrato da API do Logística Antigo (endpoint novo, a ser criado lá)

**Este endpoint não é implementado neste ciclo** — pertence ao repositório do Logística antigo. A especificação abaixo é o contrato que o CeqWeb3 espera consumir.

```
GET https://logistica.ceqnep.com.br/api2026/renovacoes/pendentes

Query params:
  ren_status_max  (int, obrigatório)
  ren_tipo        (int, opcional) — 1=Ceqnep, 2=Transportadora, 3=Hospital Retira; omitido = todos

Header de autenticação:
  X-Api-Key: <LOGISTICA_API_KEY>

Resposta 200 (application/json), array de objetos:
[
  { "ren_id": 1234, "ren_tipo": 2, "ren_status": 3, "ren_prev_chegada": "2026-07-21 15:30:00" },
  ...
]
```

Os valores de `ren_tipo` (1=Ceqnep, 2=Transportadora, 3=Hospital Retira) correspondem exatamente ao select estático já definido em `EntLogNotifSmsConfig` (campo `nsc_ren_tipo`, ver `docs/desenvolvimento/notificacoes-sms-dev.docx`, seção 3.4) — nenhum novo mapeamento de valores é necessário no CeqWeb3.

------------------------------------------------------------------------

## 5. `SmsService` (`app/Libraries/SmsService.php`)

Biblioteca nova, sem dependência de outros componentes do sistema (isola o provedor SMS Dev do resto do código, conforme decisão de arquitetura original em `docs/notificacoes-sms.md`).

```php
<?php
namespace App\Libraries;

class SmsService
{
    private string $apiKey;
    private string $endpointSend    = 'https://api.smsdev.com.br/v1/send';
    private string $endpointBalance = 'https://api.smsdev.com.br/v1/balance';

    public function __construct()
    {
        $this->apiKey = getenv('SMSDEV_API_KEY');
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $payload = [
            'key'    => $this->apiKey,
            'type'   => 9,
            'number' => $telefone,
            'msg'    => $mensagem,
        ];
        return $this->get($this->endpointSend, $payload) !== null;
    }

    public function consultarSaldo(): ?int
    {
        $response = $this->get($this->endpointBalance, ['key' => $this->apiKey]);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (($data['situacao'] ?? '') !== 'OK') return null;

        return (int) $data['saldo_sms'];
    }

    private function get(string $url, array $params): ?string
    {
        $ch = curl_init($url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha SMSDev [$url]: " . $response);
            return null;
        }
        return $response;
    }
}
```

Métodos públicos:
- `enviar(string $telefone, string $mensagem): bool` — envia um SMS a um telefone (normaliza para dígitos), retorna `true`/`false` conforme sucesso HTTP.
- `consultarSaldo(): ?int` — consulta o saldo de créditos disponíveis na conta SMS Dev; retorna `null` se a consulta falhar ou a API não confirmar `situacao = 'OK'`.

------------------------------------------------------------------------

## 6. Models — Métodos Novos

### 6.1 `LogisNotifSmsConfigModel::getRegrasAtivas()`

Arquivo já existente (`app/Models/Logis/LogisNotifSmsConfigModel.php`), método novo:

```php
public function getRegrasAtivas()
{
    return $this->where('nsc_ativo', 'A')->findAll();
}
```

Reaproveita `$useSoftDeletes`/`$deletedField` já configurados no Model (excluídas não voltam), respeitando o mesmo padrão do método `getListaRegras()` já existente (que também usa `findAll()` para respeitar soft delete automaticamente, conforme nota de correção de bug do ciclo anterior).

### 6.2 `LogisNotifSmsEnviadasModel::jaEnviado()` / `registrar()`

Arquivo já existente (`app/Models/Logis/LogisNotifSmsEnviadasModel.php`), com uma propriedade nova e dois métodos novos:

```php
protected $allowedFields = ['nse_chave', 'nse_nsc_id', 'nse_data_envio'];

public function jaEnviado(string $chave, int $nscId): bool
{
    return $this->where('nse_chave', $chave)->where('nse_nsc_id', $nscId)->countAllResults() > 0;
}

public function registrar(string $chave, int $nscId): void
{
    $this->insert(['nse_chave' => $chave, 'nse_nsc_id' => $nscId, 'nse_data_envio' => date('Y-m-d H:i:s')]);
}
```

**`$allowedFields` é obrigatório aqui, não opcional.** O Model real hoje não define essa propriedade. Sem ela, `BaseModel::doProtectFields()` do CI4 lança `DataException` em **todo** `insert()` quando `$allowedFields` está vazio — o que faria `registrar()` falhar sempre. Como o comando trata exceção por regra (ver 3.1/7), essa falha seria silenciosa do ponto de vista do SMS: o SMS é enviado normalmente, mas o registro de dedup nunca é gravado, e a mesma regra dispara de novo a cada execução do cron (flood de SMS repetido). Apontamento do `byarq` (revisão do documento de desenvolvimento), correção incorporada antes da codificação.

`jaEnviado()` é a checagem de deduplicação (ver 3.5); `registrar()` é chamado após o disparo bem-sucedido dos SMS de uma regra, gravando a chave que impede reenvio na próxima execução do cron.

------------------------------------------------------------------------

## 7. Comando CLI — `app/Commands/NotifSmsVerificar.php`

Namespace `App\Commands`, `extends BaseCommand`, grupo `Logistica`, nome `notifsms:verificar`. **Sem loop/`sleep`** — execução única; o cron do SO é quem repete a cada 5 minutos.

Wrapper completo da classe (sugestão não-bloqueante do `byarq`, para eliminar ambiguidade de implementação — namespace, `use`s, propriedades do comando e assinaturas completas dos métodos privados):

```php
<?php

namespace App\Commands;

use App\Models\Logis\LogisNotifSmsConfigModel;
use App\Models\Logis\LogisNotifSmsEnviadasModel;
use App\Libraries\SmsService;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;

class NotifSmsVerificar extends BaseCommand
{
    protected $group       = 'Logistica';
    protected $name        = 'notifsms:verificar';
    protected $description = 'Varre as regras ativas de log_notif_sms_config e dispara SMS (entrega ou saldo baixo) conforme condição.';
    protected $usage       = 'notifsms:verificar';

    public function run(array $params)
    {
        $logger        = service('logger');
        $configModel   = new LogisNotifSmsConfigModel();
        $enviadasModel = new LogisNotifSmsEnviadasModel();
        $smsService    = new SmsService();

        foreach ($configModel->getRegrasAtivas() as $regra) {
            try {
                if ($regra->nsc_tipo_regra === 'saldo_baixo') {
                    $this->processarRegraSaldo($regra, $smsService, $enviadasModel);
                } else {
                    $this->processarRegraEntrega($regra, $smsService, $enviadasModel);
                }
            } catch (\Throwable $e) {
                $logger->error('notifsms:verificar - regra ' . $regra->nsc_id . ': ' . $e->getMessage());
                CLI::error('Erro na regra ' . $regra->nsc_id . ': ' . $e->getMessage());
            }
        }
        CLI::write('Verificação concluída.', 'green');
    }

    private function processarRegraSaldo(object $regra, SmsService $smsService, LogisNotifSmsEnviadasModel $enviadasModel): void
    {
        // corpo conforme 7.1
    }

    private function processarRegraEntrega(object $regra, SmsService $smsService, LogisNotifSmsEnviadasModel $enviadasModel): void
    {
        // corpo conforme 7.2
    }
}
```

### 7.1 `processarRegraSaldo()`

Igual ao desenho original (`docs/notificacoes-sms.md`): consulta `SmsService::consultarSaldo()`; se `null`, loga e retorna sem disparar; se o saldo estiver acima de `nsc_saldo_minimo`, não dispara; senão, monta a mensagem a partir de `nsc_mensagem_template` (placeholders `{limite}`/`{saldo}`), dispara para cada telefone de `nsc_telefones` (CSV) e registra a chave de dedup `'SALDO:' . date('Y-m-d')` (1 alerta/dia).

### 7.2 `processarRegraEntrega()`

Mesma lógica de janela de tempo do desenho original (`antes_chegada`/`apos_chegada` combinado com `nsc_minutos_limite`, calculado sobre `ren_prev_chegada`), com uma única mudança: a fonte dos dados de renovação/entrega passa a ser a chamada `api_request()` ao endpoint novo do Logística antigo (ver seção 4), em vez de um `RenovacaoModel` local que nunca existiu:

```php
$res = api_request(
    LINK_LOGISTICA . 'renovacoes/pendentes',
    ['ren_status_max' => $regra->nsc_ren_status_max, 'ren_tipo' => $regra->nsc_ren_tipo],
    'get',
    ['Accept' => 'application/json', 'X-Api-Key' => getenv('LOGISTICA_API_KEY')]
);

if ($res === null) {
    $logger->error('notifsms:verificar - falha ao consultar API do Logística para a regra ' . $regra->nsc_id);
    return;
}
```

Se `$res === null` (API fora do ar/erro), a regra é pulada e o erro é logado, sem travar as demais (ver 3.4). Para cada item retornado, calcula a janela de tempo, dispara se a condição bater, e registra a chave de dedup `'REN:' . $ren['ren_id']`.

Ambos os métodos (`processarRegraSaldo`/`processarRegraEntrega`), após montar a mensagem final, seguem o mesmo padrão de disparo:

```php
foreach (explode(',', $regra->nsc_telefones) as $tel) {
    $smsService->enviar(trim($tel), $msg);
}
$enviadasModel->registrar($chave, $regra->nsc_id);
```

------------------------------------------------------------------------

## 8. Configuração — `app/Config/Constants.php`

Nova constante:

```php
define('LINK_LOGISTICA', 'https://logistica.ceqnep.com.br/api2026/');
```

Seguindo o padrão já existente de `LINK_CEQWEB2` (mesma convenção de nome/formato, barra final incluída para concatenação direta do path do endpoint).

------------------------------------------------------------------------

## 9. Estrutura de Arquivos

**Arquivos a criar:**
```
app/Libraries/SmsService.php
app/Commands/NotifSmsVerificar.php
```

**Arquivos a alterar:**
```
app/Config/Constants.php                              (nova constante LINK_LOGISTICA)
app/Models/Logis/LogisNotifSmsConfigModel.php          (novo método getRegrasAtivas())
app/Models/Logis/LogisNotifSmsEnviadasModel.php        (nova propriedade $allowedFields + novos métodos jaEnviado()/registrar() — ver 6.2)
```

Nenhuma view, rota, migration ou tela nova neste ciclo — trabalho é 100% backend/CLI.

------------------------------------------------------------------------

## 10. Cron (documentação, não é arquivo do repositório)

```
*/5 * * * * php /caminho/do/projeto/spark notifsms:verificar >> /var/log/ceqlog_sms.log 2>&1
```

**Confirmar com quem administra o servidor:** caminho real do `spark` no ambiente de produção e caminho/rotação do arquivo de log.

------------------------------------------------------------------------

## 11. Ordem de Implementação

1. `app/Libraries/SmsService.php` — sem dependências, pode ser codificado e testado isoladamente (contra conta de teste SMS Dev) antes do restante.
2. `app/Config/Constants.php` — constante `LINK_LOGISTICA`.
3. `LogisNotifSmsConfigModel::getRegrasAtivas()` e `LogisNotifSmsEnviadasModel::jaEnviado()`/`registrar()`.
4. `app/Commands/NotifSmsVerificar.php` — comando completo, com `processarRegraSaldo()` e `processarRegraEntrega()`.
5. Verificação manual (ver seção 13).
6. Registro da linha de cron no servidor — **somente após confirmação explícita do usuário e de quem administra o servidor** (ver pendências, seção 12).

------------------------------------------------------------------------

## 12. Pendências Fora Deste Repositório

- Implementar o endpoint `GET /renovacoes/pendentes` no Logística antigo, conforme contrato da seção 4.
- Definir/gerar `LOGISTICA_API_KEY` nos dois lados (CeqWeb3 e Logística antigo).
- Configurar `SMSDEV_API_KEY` no `.env` de produção do CeqWeb3 — pendência já sinalizada desde o ciclo anterior (`docs/entrega/notificacoes-sms-entrega.docx`, seção 3.3), ainda não resolvida.
- Registrar a linha de cron no servidor (seção 10), com caminho de `spark`/log confirmado por quem administra o servidor.

Nenhum destes itens é responsabilidade de codificação do `bydev` neste ciclo — são pré-requisitos operacionais/de outro repositório para a feature funcionar de ponta a ponta em produção.

------------------------------------------------------------------------

## 13. Verificação / Teste Manual

1. `SmsService`: testar `enviar()`/`consultarSaldo()` contra conta de teste SMS Dev (depende de `SMSDEV_API_KEY` de teste).
2. `processarRegraSaldo()`: testável de ponta a ponta assim que `SMSDEV_API_KEY` existir — criar regra `saldo_baixo` com limite acima do saldo real de teste, rodar o comando, confirmar disparo e registro em `log_notif_sms_enviadas`.
3. `processarRegraEntrega()`: só testável de ponta a ponta depois que o endpoint `/renovacoes/pendentes` existir no Logística antigo. Até lá, validar via mock local (resposta simulada de `api_request()`) cobrindo: janela `antes_chegada`, janela `apos_chegada`, e caso de API retornando `null`.
4. Rodar `php spark notifsms:verificar` manualmente em ambiente de dev, conferir logs (`service('logger')`) e as linhas gravadas em `log_notif_sms_enviadas`.
5. Confirmar que rodar o comando 2 vezes seguidas dentro da janela de dedup **não** reenvia SMS (nem para regra de saldo, nem para regra de entrega).
6. Confirmar que uma falha em uma regra (ex.: API do Logística fora do ar) não interrompe o processamento das demais regras ativas na mesma execução.

**Observação para o plano de testes (`bytest`):** o item 5 acima ("rodar 2x não reenvia") não é suficiente sozinho para pegar uma falha silenciosa de `registrar()` — se `insert()` sempre lançar exceção (ex.: `$allowedFields` vazio, ver 6.2) e essa exceção for engolida pelo `try/catch` por regra, o SMS é enviado normalmente na primeira execução e o teste "2x não reenvia" passaria por acaso na mesma execução, mas voltaria a reenviar na próxima chamada do cron. O plano de testes deve incluir um caso **explícito** que confirme que `registrar()` de fato persiste uma linha em `log_notif_sms_enviadas` (consulta direta à tabela após a chamada), não apenas o comportamento indireto de "não reenviar".

------------------------------------------------------------------------

## 14. Critérios de Pronto

- `SmsService` implementado exatamente conforme especificado na seção 5, testado contra conta de teste SMS Dev.
- `NotifSmsVerificar` registrado como comando `spark` (`notifsms:verificar`), execução única (sem daemon/loop), com tratamento de exceção por regra.
- `getRegrasAtivas()`, `jaEnviado()` e `registrar()` implementados e cobrindo soft delete/deduplicação conforme especificado (seção 6), incluindo `$allowedFields` em `LogisNotifSmsEnviadasModel` (seção 6.2) — sem isso `registrar()` falha em todo `insert()`.
- Consumo da API do Logística antigo via `api_request()` (helper genérico já existente), sem cliente HTTP próprio; falha de API não trava as demais regras.
- `LINK_LOGISTICA` cadastrada em `Constants.php`, seguindo o padrão de `LINK_CEQWEB2`.
- Deduplicação validada manualmente (rodar 2x seguidas não reenvia).
- Nenhuma alteração nas telas administrativas (`NotifSmsConfig`/`NotifSmsEnviadas`) ou nas migrations/infraestrutura já entregues no ciclo anterior.
- Pendências fora deste repositório (seção 12) explicitamente sinalizadas no documento de entrega deste ciclo, sem serem tratadas como bloqueio de codificação.
- `byrev` sem apontamentos pendentes; `bytest` com plano de testes cobrindo `SmsService` e `processarRegraSaldo()` de ponta a ponta, e `processarRegraEntrega()` ao menos via mock (até o endpoint externo existir), incluindo confirmação explícita de que `registrar()` persiste linha em `log_notif_sms_enviadas` (ver observação na seção 13).

------------------------------------------------------------------------

## 15. Rastreabilidade

Este documento formaliza, para codificação, o plano aprovado para a parte de disparo efetivo de SMS (`SmsService` + comando CLI + cron) da feature de Notificações SMS, complementando:
- `docs/notificacoes-sms.md` — desenho técnico original (schema de banco, motor de regras).
- `docs/desenvolvimento/notificacoes-sms-dev.docx` — documento de desenvolvimento da parte administrativa (CRUD `NotifSmsConfig` + consulta `NotifSmsEnviadas`), já entregue.
- `docs/entrega/notificacoes-sms-entrega.docx` — documento de entrega do ciclo anterior, cuja seção 3.3 já sinalizava a pendência de `SMSDEV_API_KEY` retomada aqui.

Qualquer apontamento de revisão (`byrev`) ou caso de teste (`bytest`) sobre esta parte da feature deve referenciar a seção correspondente deste documento (seção 4 para o contrato de API, seção 5 para `SmsService`, seção 7 para o comando CLI).
