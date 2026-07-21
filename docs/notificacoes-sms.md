# Notificações SMS — CeqLog (API2026)

## Contexto

A API2026 já centraliza todas as transações de banco de dados do app CeqLog. Este documento
define o plano para adicionar notificações via SMS configuráveis, disparadas por regras de
negócio sobre entregas (tabela de renovação/entrega, campos `ren_tipo`, `ren_status`,
`ren_prev_chegada`) e também por alerta de saldo baixo no provedor de SMS.

## Decisões de arquitetura

- Motor de regras **configurável via tabela**, não hardcoded — permite adicionar/editar regras
  sem deploy.
- Execução via **job CLI agendado por cron do SO** (não dentro do fluxo síncrono da API2026, que
  atende o app).
- **Deduplicação obrigatória**: sem isso, cron rodando a cada poucos minutos reenviaria SMS
  repetidamente enquanto a condição permanecer verdadeira.
- Camada de abstração `SmsService`, isolando o provedor de SMS do resto do código.
- Provedor escolhido: **SMS Dev** (https://www.smsdev.com.br) — sem mensalidade, créditos
  pré-pagos sem validade, paga só por SMS enviado. Pacote inicial de 1.000 SMS = R$ 110,00
  (R$ 0,11/SMS). Conta de teste gratuita disponível.

## Schema (MySQL)

```sql
CREATE TABLE log_notif_sms_config (
    nsc_id INT PRIMARY KEY AUTO_INCREMENT,
    nsc_nome VARCHAR(100),
    nsc_tipo_regra ENUM('entrega','saldo_baixo') NOT NULL DEFAULT 'entrega',

    -- usados quando nsc_tipo_regra = 'entrega'
    nsc_ren_tipo INT NULL,
    nsc_ren_status_max INT NULL,
    nsc_condicao ENUM('antes_chegada','apos_chegada') NULL,
    nsc_minutos_limite INT NULL,

    -- usado quando nsc_tipo_regra = 'saldo_baixo'
    nsc_saldo_minimo INT NULL,

    nsc_telefones VARCHAR(255),
    nsc_mensagem_template TEXT,
    nsc_ativo TINYINT(1) DEFAULT 1
);

-- chave genérica para dedup, serve tanto regras de entrega quanto de saldo
CREATE TABLE log_notif_sms_enviadas (
    nse_id INT PRIMARY KEY AUTO_INCREMENT,
    nse_chave VARCHAR(100),     -- ex: "REN:1234" ou "SALDO:2026-07-16"
    nse_nsc_id INT,
    nse_data_envio DATETIME,
    UNIQUE KEY uk_chave_regra (nse_chave, nse_nsc_id)
);
```

Exemplo de regra de entrega (SMS 30 min antes da chegada prevista, tipo 2, status < 4):
```sql
INSERT INTO log_notif_sms_config
(nsc_nome, nsc_tipo_regra, nsc_ren_tipo, nsc_ren_status_max, nsc_condicao, nsc_minutos_limite, nsc_telefones, nsc_mensagem_template, nsc_ativo)
VALUES
('Alerta 30min antes chegada', 'entrega', 2, 4, 'antes_chegada', 30, '5548999998888', 'CeqLog: entrega {ren_id} chega em breve.', 1);
```

Exemplo de regra de saldo baixo:
```sql
INSERT INTO log_notif_sms_config
(nsc_nome, nsc_tipo_regra, nsc_saldo_minimo, nsc_telefones, nsc_mensagem_template, nsc_ativo)
VALUES
('Saldo SMS baixo', 'saldo_baixo', 50, '5548999998888', 'CeqLog: saldo de SMS abaixo de {limite}. Saldo atual: {saldo}.', 1);
```

## SmsService (app/Libraries/SmsService.php)

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

## Controller CLI (app/Controllers/Cli/NotificacaoSms.php)

```php
<?php
namespace App\Controllers\Cli;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Controller;

class NotificacaoSms extends Controller
{
    public function verificar()
    {
        $configModel   = new \App\Models\NotifSmsConfigModel();
        $enviadasModel = new \App\Models\NotifSmsEnviadasModel();
        $smsService    = new \App\Libraries\SmsService();

        $regras = $configModel->where('nsc_ativo', 1)->findAll();

        foreach ($regras as $regra) {
            if ($regra['nsc_tipo_regra'] === 'saldo_baixo') {
                $this->processarRegraSaldo($regra, $smsService, $enviadasModel);
            } else {
                $this->processarRegraEntrega($regra, $smsService, $enviadasModel);
            }
        }

        CLI::write('Verificação concluída.');
    }

    private function processarRegraSaldo($regra, $smsService, $enviadasModel): void
    {
        $saldo = $smsService->consultarSaldo();
        if ($saldo === null) {
            CLI::write('Não foi possível consultar o saldo.', 'red');
            return;
        }
        if ($saldo >= $regra['nsc_saldo_minimo']) return;

        // 1 alerta por dia por regra, evita flood enquanto o saldo estiver baixo
        $chave = 'SALDO:' . date('Y-m-d');
        if ($enviadasModel->jaEnviado($chave, $regra['nsc_id'])) return;

        $msg = strtr($regra['nsc_mensagem_template'], [
            '{limite}' => $regra['nsc_saldo_minimo'],
            '{saldo}'  => $saldo,
        ]);

        foreach (explode(',', $regra['nsc_telefones']) as $tel) {
            $smsService->enviar(trim($tel), $msg);
        }
        $enviadasModel->registrar($chave, $regra['nsc_id']);
    }

    private function processarRegraEntrega($regra, $smsService, $enviadasModel): void
    {
        $renModel = new \App\Models\RenovacaoModel();
        $builder = $renModel->where('ren_status <', $regra['nsc_ren_status_max']);
        if (!is_null($regra['nsc_ren_tipo'])) {
            $builder->where('ren_tipo', $regra['nsc_ren_tipo']);
        }

        foreach ($builder->findAll() as $ren) {
            $diffMin = (strtotime($ren['ren_prev_chegada']) - time()) / 60;

            $dispara = ($regra['nsc_condicao'] === 'antes_chegada' && $diffMin > 0 && $diffMin <= $regra['nsc_minutos_limite'])
                    || ($regra['nsc_condicao'] === 'apos_chegada' && $diffMin <= 0);

            if (!$dispara) continue;

            $chave = 'REN:' . $ren['ren_id'];
            if ($enviadasModel->jaEnviado($chave, $regra['nsc_id'])) continue;

            $msg = strtr($regra['nsc_mensagem_template'], [
                '{ren_id}'            => $ren['ren_id'],
                '{ren_prev_chegada}'  => $ren['ren_prev_chegada'],
            ]);

            foreach (explode(',', $regra['nsc_telefones']) as $tel) {
                $smsService->enviar(trim($tel), $msg);
            }
            $enviadasModel->registrar($chave, $regra['nsc_id']);
        }
    }
}
```

## Cron

```
*/5 * * * * php /caminho/spark cli:notificacaosms:verificar >> /var/log/ceqlog_sms.log 2>&1
```

## Pendente / próximos passos

- [ ] Criar migration CI4 formal para `log_notif_sms_config` e `log_notif_sms_enviadas`
- [ ] Criar `NotifSmsConfigModel` e `NotifSmsEnviadasModel` com métodos `jaEnviado()` e
      `registrar()`, seguindo padrão do projeto (soft delete, hooks de auditoria, `verificaUnico`)
- [ ] Criar conta na SMS Dev, testar envio e consulta de saldo com créditos de teste
- [ ] Definir `SMSDEV_API_KEY` no `.env`
- [ ] Validar mensagens (limite de 160 caracteres por crédito)

## Regra do projeto (lembrete)

Nenhuma alteração de código deve ser feita sem plano + diff review antes — regra estrita do
Douglas, especialmente crítica na fase de cutover de produção da API2026.
