# Documento de Desenvolvimento — Módulo Logística (CeqWeb 3.0): Suporte a Múltiplos Provedores de SMS (SMS Dev + GTI SMS)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística (já existente — infraestrutura `dbLogistica`/`cfg_modulo` criada em ciclo anterior)
**Componentes envolvidos:** `SmsService` (fachada) · `SmsProviderInterface` (novo) · `SmsDevProvider` (novo) · `GtiSmsProvider` (novo)
**Tipo de trabalho:** Continuação de feature — `SmsService`/comando CLI/cron já entregues em ciclo anterior (`docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`). Este ciclo reestrutura `SmsService` para suportar dois provedores de SMS, selecionáveis via `.env`, sem alterar nenhum consumidor existente.
**Origem:** Plano técnico aprovado pelo usuário (Douglas), com contrato da API GTI SMS v3 levantado na documentação oficial (`gtisms.com/docs`).
**Status:** Plano aprovado pelo usuário. Aprovado para codificação.

------------------------------------------------------------------------

## 1. Objetivo

Adicionar suporte ao provedor **GTI SMS** como alternativa ao **SMS Dev** já implementado, mantendo ambos disponíveis e selecionáveis via variável de ambiente `SMS_PROVIDER` (`smsdev` ou `gtisms`), sem quebrar nenhum consumidor atual de `SmsService` (`NotifSmsVerificar`, Models, Controllers).

Este documento consolida o plano aprovado para uso direto do `bydev` na codificação — contém a interface a ser extraída, o contrato da API GTI SMS v3, as diferenças em relação ao SMS Dev, e a correção de um bug real encontrado em produção durante o levantamento.

------------------------------------------------------------------------

## 2. Escopo

**Dentro do escopo:**
- Extração de uma interface `SmsProviderInterface` (`app/Libraries/Sms/SmsProviderInterface.php`), definindo o contrato `enviar()`/`consultarSaldo()` já usado por `SmsService`.
- Migração do código atual de `SmsService` para `SmsDevProvider` (`app/Libraries/Sms/SmsDevProvider.php`), implementando a interface, com correção de bug (ver 3.3).
- Implementação de `GtiSmsProvider` (`app/Libraries/Sms/GtiSmsProvider.php`), novo provedor, conforme contrato da API GTI SMS v3 (seção 4).
- Reescrita de `SmsService` (`app/Libraries/SmsService.php`) como fachada fina, selecionando o provedor ativo via `getenv('SMS_PROVIDER')`.
- Especificação das novas chaves de `.env` necessárias (não criadas neste ciclo — pendência operacional).

**Fora do escopo deste ciclo:**
- Qualquer alteração em `NotifSmsVerificar.php`, Models ou Controllers — todos conhecem apenas `SmsService`, cujo contrato público (`enviar()`/`consultarSaldo()`) não muda.
- Consulta de status de envio por DLR (`GET /sms/{uid}`) — documentado no contrato da GTI SMS (seção 4) mas não implementado, não usado pela arquitetura atual (1 chamada por telefone, sem acompanhamento de status assíncrono).
- Envio para múltiplos destinatários numa única chamada (suportado pela GTI SMS via `recipient` separado por vírgula) — mantém-se a arquitetura já existente de 1 chamada por telefone.
- Geração/definição de `GTISMS_API_KEY` em produção e criação de conta na GTI SMS — pendências operacionais, não de código.
- Decisão de qual provedor fica ativo em produção — decisão de negócio do usuário, fora do escopo de codificação.

------------------------------------------------------------------------

## 3. Decisões de Arquitetura

### 3.1 Seleção de provedor via `.env`, não substituição

O usuário optou por manter os dois provedores implementados e configuráveis, em vez de substituir SMS Dev por GTI SMS. A seleção é feita pela chave `SMS_PROVIDER` no `.env` (`smsdev` ou `gtisms`), lida uma única vez no construtor de `SmsService`, com `smsdev` como padrão (`?: 'smsdev'`) caso a chave esteja ausente ou vazia — preserva o comportamento atual sem exigir alteração imediata do `.env` de produção.

### 3.2 Extração de interface (`SmsProviderInterface`) — padrão Strategy

`SmsService` deixa de conter lógica de integração HTTP e passa a ser uma fachada fina que delega a um objeto que implementa `SmsProviderInterface`:

```php
interface SmsProviderInterface
{
    public function enviar(string $telefone, string $mensagem): bool;
    public function consultarSaldo(): ?int;
}
```

Essa extração isola cada provedor em sua própria classe (`SmsDevProvider`/`GtiSmsProvider`), sem `if`/`switch` de provedor espalhado pelo restante do código — apenas `SmsService` conhece a variável de ambiente `SMS_PROVIDER`. Consistente com a decisão de arquitetura original (`docs/notificacoes-sms.md`): "camada de abstração `SmsService`, isolando o provedor de SMS do resto do código" — esta feature refina essa abstração, sem contradizê-la.

### 3.3 Correção de bug em produção — checagem de conteúdo da resposta, não só HTTP 200

Levantamento identificou que o `enviar()` atual de `SmsDevProvider` (código herdado de `SmsService`) checa apenas se a chamada HTTP retornou 200, sem inspecionar o corpo da resposta. Isso permitiu que um erro `415` da conta de teste (aparentemente reportado como HTTP 200 pela API do provedor, ou tratado incorretamente pela camada de checagem) passasse como sucesso silenciosamente em produção.

**Correção aplicada em `SmsDevProvider::enviar()`:** além do HTTP 200, o método passa a decodificar o corpo da resposta e checar `situacao === 'OK'` (mesmo campo já usado em `consultarSaldo()`), retornando `false` caso o conteúdo não confirme sucesso — mesmo com HTTP 200. **`GtiSmsProvider::enviar()`** já nasce com essa checagem (`status === 'success'` no corpo), pela mesma razão: nunca considerar sucesso baseado só no código HTTP.

### 3.4 Diferenças de contrato entre os dois provedores

| Aspecto | SMS Dev | GTI SMS v3 |
|---|---|---|
| Autenticação | Query param `key` | Header `Authorization: Bearer TOKEN` |
| Método de envio | `GET` | `POST` com corpo `JSON` |
| Formato do telefone | Local, sem DDI (ex.: `41991728188`) | Com DDI obrigatório (ex.: `5541991728188`) |
| Caracteres na mensagem | Sem restrição conhecida | Sem acento/emoji/caractere especial |
| Confirmação de sucesso (envio) | `situacao === 'OK'` no corpo (campo achatado) | `status === 'success'` no nível raiz + `data.status` granular (`Delivered`/`Sent`/`Pending`/`Failed`/`Rejected`/`Expired`) |
| Confirmação de sucesso (saldo) | `situacao === 'OK'` + `saldo_sms` (string) | `status === 'success'` + `data.remaining_balance` (string) |

Consequências para a implementação:
- `GtiSmsProvider` precisa de uma função de **normalização de telefone**, prefixando `55` quando o número não tiver DDI (heurística: 10 ou 11 dígitos após remover não-numéricos → prefixar `55`; 12 ou 13 dígitos já com DDI → manter).
- `GtiSmsProvider` precisa de uma função de **sanitização de mensagem**, removendo acentos via `iconv('UTF-8', 'ASCII//TRANSLIT', $mensagem)` antes do envio.
- Ambos os providers fazem `cast` explícito para `int` do valor de saldo retornado (string em ambas as APIs).

### 3.5 Sem biblioteca HTTP adicional

Mantida a decisão original de integração via cURL direto (sem Guzzle ou similar), consistente com `SmsDevProvider` (herdado) e com o restante do projeto (`api_request()` em `api_cw2_helper.php` também usa cURL). `GtiSmsProvider` usa cURL com `CURLOPT_POSTFIELDS` (JSON) e header `Authorization`, em vez de `http_build_query()` em query string.

------------------------------------------------------------------------

## 4. Contrato da API GTI SMS v3 (referência — `gtisms.com/docs`)

```
Base URL: https://sms.gtisms.com/api/v3

Autenticação:
  Header: Authorization: Bearer <GTISMS_API_KEY>

Envio:
  POST /sms/send
  Corpo (JSON): { "recipient": "5541991728188", "message": "..." }
  (telefone com DDI obrigatório; mensagem sem acento/emoji/caractere especial)

  Resposta 200 (JSON):
  {
    "status": "success",
    "data": {
      "status": "Delivered|Sent|Pending|Failed|Rejected|Expired",
      "cost": 1,
      "uid": "..."
    }
  }

Saldo:
  GET /balance

  Resposta 200 (JSON):
  {
    "status": "success",
    "data": {
      "remaining_balance": "100",
      "expired_on": "..."
    }
  }

Status por envio (DLR) — não usado nesta implementação, apenas documentado:
  GET /sms/{uid}
```

A API GTI SMS v3 suporta múltiplos destinatários numa única chamada (`recipient` separado por vírgula) — não utilizado nesta implementação, para manter a mesma arquitetura de 1 chamada por telefone já existente (ver seção 2, fora do escopo).

------------------------------------------------------------------------

## 5. `SmsProviderInterface` (`app/Libraries/Sms/SmsProviderInterface.php`)

Arquivo novo. Contrato mínimo já consumido por `SmsService`:

```php
<?php
namespace App\Libraries\Sms;

interface SmsProviderInterface
{
    public function enviar(string $telefone, string $mensagem): bool;
    public function consultarSaldo(): ?int;
}
```

------------------------------------------------------------------------

## 6. `SmsDevProvider` (`app/Libraries/Sms/SmsDevProvider.php`)

Arquivo novo — código atual de `SmsService` (`docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`, seção 5), movido para cá e adaptado para implementar `SmsProviderInterface`, com a correção de bug da seção 3.3 (checagem de `situacao === 'OK'` também em `enviar()`, não só em `consultarSaldo()`):

```php
<?php
namespace App\Libraries\Sms;

class SmsDevProvider implements SmsProviderInterface
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

        $response = $this->get($this->endpointSend, $payload);
        if ($response === null) return false;

        $data = json_decode($response, true);
        return ($data['situacao'] ?? '') === 'OK';
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

**Diferença em relação ao código original:** `enviar()` agora decodifica e checa o corpo da resposta (`situacao === 'OK'`) antes de retornar `true`, corrigindo o bug descrito em 3.3. Antes, `enviar()` retornava `true` sempre que `$this->get()` não fosse `null` (isto é, sempre que HTTP fosse 200), independentemente do conteúdo.

------------------------------------------------------------------------

## 7. `GtiSmsProvider` (`app/Libraries/Sms/GtiSmsProvider.php`)

Arquivo novo. Implementa `SmsProviderInterface` conforme contrato da seção 4:

```php
<?php
namespace App\Libraries\Sms;

class GtiSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://sms.gtisms.com/api/v3';

    public function __construct()
    {
        $this->apiKey = getenv('GTISMS_API_KEY');
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        $payload = [
            'recipient' => $this->normalizaTelefone($telefone),
            'message'   => $this->sanitizaMensagem($mensagem),
        ];

        $response = $this->post($this->baseUrl . '/sms/send', $payload);
        if ($response === null) return false;

        $data = json_decode($response, true);
        return ($data['status'] ?? '') === 'success';
    }

    public function consultarSaldo(): ?int
    {
        $response = $this->get($this->baseUrl . '/balance');
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (($data['status'] ?? '') !== 'success') return null;

        return (int) ($data['data']['remaining_balance'] ?? 0);
    }

    /**
     * Prefixa o DDI 55 quando o telefone não o contiver.
     * SMS Dev usa formato local (ex.: 41991728188); GTI exige DDI (ex.: 5541991728188).
     */
    private function normalizaTelefone(string $telefone): string
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        if (strlen($telefone) <= 11) {
            $telefone = '55' . $telefone;
        }
        return $telefone;
    }

    /**
     * GTI SMS não aceita acento/emoji/caractere especial na mensagem.
     */
    private function sanitizaMensagem(string $mensagem): string
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $mensagem);
    }

    private function post(string $url, array $payload): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha GTI SMS [$url]: " . $response);
            return null;
        }
        return $response;
    }

    private function get(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "Falha GTI SMS [$url]: " . $response);
            return null;
        }
        return $response;
    }
}
```

Métodos privados de apoio:
- `normalizaTelefone()` — regra descrita em 3.4: números com 10/11 dígitos (sem DDI) recebem prefixo `55`; números já com 12/13 dígitos (com DDI) são mantidos como estão.
- `sanitizaMensagem()` — remove acentuação via transliteração ASCII, requisito da API GTI SMS.

------------------------------------------------------------------------

## 8. `SmsService` — reescrito como fachada (`app/Libraries/SmsService.php`)

Passa a ser uma fachada fina, sem lógica de integração HTTP própria, delegando ao provedor selecionado por `SMS_PROVIDER`:

```php
<?php
namespace App\Libraries;

use App\Libraries\Sms\SmsProviderInterface;
use App\Libraries\Sms\SmsDevProvider;
use App\Libraries\Sms\GtiSmsProvider;

class SmsService
{
    private SmsProviderInterface $provider;

    public function __construct()
    {
        $this->provider = match (getenv('SMS_PROVIDER') ?: 'smsdev') {
            'gtisms' => new GtiSmsProvider(),
            default  => new SmsDevProvider(),
        };
    }

    public function enviar(string $telefone, string $mensagem): bool
    {
        return $this->provider->enviar($telefone, $mensagem);
    }

    public function consultarSaldo(): ?int
    {
        return $this->provider->consultarSaldo();
    }
}
```

**Nenhuma mudança exigida em `NotifSmsVerificar.php`, Models ou Controllers** — todos conhecem apenas `SmsService::enviar()`/`SmsService::consultarSaldo()`, cujas assinaturas são idênticas às já existentes.

------------------------------------------------------------------------

## 9. Configuração — `.env` (chaves necessárias, não criadas neste ciclo)

```
SMS_PROVIDER = smsdev      # ou "gtisms" — seleciona o provedor ativo
SMSDEV_API_KEY = ...       # já existe (ciclo anterior)
GTISMS_API_KEY = ...       # nova, Bearer token da conta GTI SMS
```

Se `SMS_PROVIDER` estiver ausente ou vazio, `SmsService` assume `smsdev` como padrão (ver 3.1) — nenhuma alteração é necessária no `.env` de produção atual para preservar o comportamento existente.

------------------------------------------------------------------------

## 10. Estrutura de Arquivos

**Arquivos a criar:**
```
app/Libraries/Sms/SmsProviderInterface.php
app/Libraries/Sms/SmsDevProvider.php
app/Libraries/Sms/GtiSmsProvider.php
```

**Arquivos a alterar:**
```
app/Libraries/SmsService.php   (reescrito como fachada/seletor de provedor)
```

Nenhuma view, rota, migration, Controller, Model ou tela nova neste ciclo — trabalho é 100% biblioteca/backend.

------------------------------------------------------------------------

## 11. Ordem de Implementação

1. `app/Libraries/Sms/SmsProviderInterface.php` — contrato, sem dependências.
2. `app/Libraries/Sms/SmsDevProvider.php` — migração do código atual de `SmsService`, com a correção de bug da seção 3.3 aplicada.
3. `app/Libraries/Sms/GtiSmsProvider.php` — novo provedor, conforme contrato da seção 4.
4. `app/Libraries/SmsService.php` — reescrita como fachada.
5. Verificação manual (ver seção 13), com atenção especial à regressão zero do fluxo SMS Dev (item 1 da verificação).

------------------------------------------------------------------------

## 12. Pendências Fora Deste Repositório

- Definir/gerar `GTISMS_API_KEY` (conta GTI SMS real, de teste ou produção).
- Confirmar comportamento da conta de teste gratuita da GTI SMS — a SMS Dev tinha restrição de só enviar para o número cadastrado até efetuar compra de créditos; verificar se a GTI SMS tem limitação equivalente, para não bloquear a verificação de ponta a ponta.
- Decidir qual provedor (`SMS_PROVIDER`) fica ativo em produção, quando o momento chegar — decisão de negócio do usuário, não de codificação.

Nenhum destes itens é responsabilidade de codificação do `bydev` neste ciclo.

------------------------------------------------------------------------

## 13. Verificação / Teste Manual

1. Com `SMS_PROVIDER=smsdev` (ou ausente/vazio no `.env`), o comportamento de `enviar()`/`consultarSaldo()` deve ser **idêntico** ao já testado e funcionando em produção — regressão zero. Exceção esperada e desejada: `enviar()` agora retorna `false` em casos que antes retornariam `true` incorretamente (HTTP 200 com corpo indicando falha) — ver bug corrigido na seção 3.3.
2. Com `SMS_PROVIDER=gtisms`, testar `consultarSaldo()`/`enviar()` contra uma conta GTI SMS real (depende de `GTISMS_API_KEY` de teste — pendência de infraestrutura, seção 12).
3. Confirmar que `normalizaTelefone()` funciona corretamente tanto para números sem DDI (ex.: `41991728188` → `5541991728188`) quanto para números que já chegam com DDI (ex.: `5541991728188` → mantido sem duplicar o `55`).
4. Confirmar que `sanitizaMensagem()` remove corretamente acentos em mensagens de template reais usadas pelas regras de notificação (com `ç`, `ã`, `é`, etc.), sem alterar o restante do texto.
5. Confirmar que `NotifSmsVerificar.php` continua funcionando sem nenhuma alteração de código, com qualquer um dos dois provedores selecionado via `.env`.
6. Confirmar, por inspeção de código/teste unitário, que `SmsDevProvider::enviar()` retorna `false` quando a API SMS Dev responde HTTP 200 com corpo indicando falha (ex.: `situacao` diferente de `'OK'`), reproduzindo o cenário do bug encontrado em produção (seção 3.3).

------------------------------------------------------------------------

## 14. Critérios de Pronto

- `SmsProviderInterface`, `SmsDevProvider` e `GtiSmsProvider` implementados exatamente conforme especificado nas seções 5, 6 e 7.
- `SmsService` reescrito como fachada fina (seção 8), selecionando o provedor via `SMS_PROVIDER` (padrão `smsdev`), sem lógica de integração HTTP própria.
- Bug de checagem de conteúdo da resposta (seção 3.3) corrigido em `SmsDevProvider::enviar()` — `enviar()` só retorna `true` quando o corpo da resposta confirma sucesso, não apenas HTTP 200.
- `GtiSmsProvider` implementa normalização de telefone (DDI) e sanitização de mensagem (acentos) conforme seção 3.4/7, antes de qualquer chamada à API.
- Nenhuma alteração em `NotifSmsVerificar.php`, Models ou Controllers — contrato público de `SmsService` inalterado.
- Regressão zero confirmada com `SMS_PROVIDER=smsdev` (ou ausente), exceto pelo comportamento corrigido do bug da seção 3.3 (comportamento anterior era incorreto).
- Novas chaves de `.env` (seção 9) especificadas neste documento e sinalizadas como pendência operacional no documento de entrega deste ciclo.
- `byrev` sem apontamentos pendentes; `bytest` com plano de testes cobrindo `SmsDevProvider` (regressão + correção de bug) de ponta a ponta, `GtiSmsProvider` (normalização/sanitização) via teste unitário e, quando `GTISMS_API_KEY` de teste estiver disponível, também de ponta a ponta.

------------------------------------------------------------------------

## 15. Rastreabilidade

Este documento formaliza, para codificação, o plano aprovado de suporte a múltiplos provedores de SMS, complementando:
- `docs/notificacoes-sms.md` — desenho técnico original (schema de banco, motor de regras).
- `docs/desenvolvimento/notificacoes-sms-dev.docx` — documento de desenvolvimento da parte administrativa (CRUD `NotifSmsConfig`/`NotifSmsEnviadas`), já entregue.
- `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` — documento de desenvolvimento do `SmsService` original (SMS Dev único), comando CLI e cron, já entregue. Este documento substitui a especificação de `SmsService` daquele documento (seção 5) pela arquitetura de múltiplos provedores descrita aqui (seções 5 a 8), sem alterar o restante daquele documento (comando CLI, Models, cron, contrato da API do Logística antigo).
- `docs/entrega/notificacoes-sms-entrega.docx` — documento de entrega do ciclo anterior, cuja pendência de `SMSDEV_API_KEY` permanece válida e é complementada aqui pela pendência de `GTISMS_API_KEY` (seção 12).

Qualquer apontamento de revisão (`byrev`) ou caso de teste (`bytest`) sobre esta parte da feature deve referenciar a seção correspondente deste documento (seção 4 para o contrato da API GTI SMS, seção 6/7 para os providers, seção 8 para a fachada `SmsService`).
