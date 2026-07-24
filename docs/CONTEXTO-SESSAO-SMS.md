# Contexto para retomar — Notificações SMS (módulo Logística)

> Cole isto (ou peça pro Claude ler este arquivo) ao abrir uma nova sessão do Claude Code
> neste projeto, em outra máquina, para retomar exatamente de onde paramos.

## Comando para a nova sessão

Ao abrir o Claude Code neste projeto na outra máquina, diga algo como:

> "Leia docs/CONTEXTO-SESSAO-SMS.md e os documentos em docs/desenvolvimento, docs/revisao,
> docs/testes e docs/entrega (todos com prefixo notificacoes-sms) para entender o que já foi
> feito na feature de Notificações SMS. Depois disso, [diga o que quer fazer a seguir]."

## Estado atual (2026-07-22)

Feature de Notificações SMS, módulo "Logística" novo do CeqWeb3, construída em 3 ciclos nesta
mesma conversa (todos com documento de desenvolvimento → revisão → testes → entrega em `docs/`):

### Ciclo 1 — CRUD + consulta (entregue e testado em DEV)
- `app/Controllers/Logistica/NotifSmsConfig.php` (CRUD de regras) e `NotifSmsEnviadas.php`
  (consulta por período).
- `app/Models/Logis/LogisNotifSmsConfigModel.php` e `LogisNotifSmsEnviadasModel.php`.
- `app/Entities/Logistica/EntLogNotifSmsConfig.php`.
- `app/Database/Migrations/2026-07-21-000001_LogisticaNotifSms.php` — já rodada em DEV
  (`dev_logistica_db`), criou `cfg_modulo`/`cfg_tela` (tel_id 70/71, tel_ident='T44'/'T45')/
  `cfg_perfil_item` (Super Admin, prf_id=1).
- Docs: `docs/desenvolvimento/notificacoes-sms-dev.docx`,
  `docs/revisao/notificacoes-sms-revisao-01.docx`,
  `docs/testes/notificacoes-sms-plano-testes.docx` e `-resultado-testes.docx`,
  `docs/entrega/notificacoes-sms-entrega.docx`.

### Ciclo 2 — Serviço de envio (entregue e testado em DEV)
- `app/Libraries/SmsService.php` (nessa época, uma classe única pro SMS Dev).
- `app/Commands/NotifSmsVerificar.php` (`spark notifsms:verificar`).
- Docs: `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`,
  `docs/testes/notificacoes-sms-servico-envio-resultado-testes.docx`,
  `docs/entrega/notificacoes-sms-servico-envio-entrega.docx`.

### Ciclo 3 — Multi-provider (SMS Dev + GTI SMS), entregue e testado com envio real
- `SmsService` virou fachada fina: `app/Libraries/Sms/SmsProviderInterface.php`,
  `SmsDevProvider.php`, `GtiSmsProvider.php`. Escolha do provedor via `.env`:
  `SMS_PROVIDER=smsdev|gtisms`.
- `.env` do usuário JÁ ESTÁ com `SMS_PROVIDER=gtisms` e `GTISMS_API_KEY` reais (conta GTI SMS
  paga, não é teste).
- Doc: `docs/desenvolvimento/notificacoes-sms-multiprovider-dev.docx` (não chegou a ter
  documento de entrega formal — o ciclo terminou em testes reais bem-sucedidos direto nesta
  conversa, ver bugs corrigidos abaixo).

## Bugs reais encontrados e corrigidos ao longo dos testes (não hipotéticos)

1. `nsc_id` com cast `'integer'` (não anulável) na Entity quebrava `save()` no formulário de
   inclusão (virava `0` em vez de `null`, gerando erro "Invalid primary key: '0' is not
   allowed") — corrigido para `'?integer'`.
2. `getListaRegras()` não filtrava soft-delete — trocado `builder()->get()` por `findAll()`.
3. `cfg_tela.tel_ident` é `VARCHAR(5)` e é código de tela por ciclo de desenvolvimento (`T22`,
   `T42`...), não nome de coluna — migration corrigida para usar `T44`/`T45`.
4. PHP local (`c:/srvlocal/php/php.exe`) sem CA bundle configurado — toda chamada HTTPS via
   cURL falhava. Corrigido baixando `cacert.pem` e configurando `curl.cainfo`/`openssl.cafile`
   no `php.ini` (backup salvo como `php.ini.bak-*`). **Isso é config da máquina local, não do
   projeto — se a nova máquina também usa PHP CLI local pra rodar `spark`, pode precisar do
   mesmo ajuste lá.**
5. `GtiSmsProvider::consultarSaldo()` — GTI retorna saldo como string formatada em moeda com
   caractere invisível (`"‎R$5"`), `(int)` direto sempre dava 0 — corrigido extraindo dígitos
   via regex antes do cast.
6. `sanitizaMensagem()` da GTI dependia de `iconv(...,'ASCII//TRANSLIT',...)`, que produzia
   artefatos e deixava emoji passar sem logar — trocado por `strtr()` com mapa fixo de acentos.
7. **Bug de infraestrutura compartilhada** (fora desta feature, mas descoberto por causa dela):
   `app/Log/Handlers/WorkerHandler.php` engolia todo `log_message()` de código fora de
   `Controllers\Work*` (retornava `false`, que no CI4 aborta a cadeia de handlers de log) —
   corrigido para `return true`. Efeito colateral: reabriu o caminho pro
   `LogEmailThrottleHandler` (envia e-mail via SMTP síncrono a cada log de erro), que travava
   `notifsms:verificar` por minutos quando a regra "entrega" falhava. Resolvido rebaixando esse
   log específico (falha esperada, API do Logística antigo ainda não existe) de `error` pra
   `warning`, e adicionando validação defensiva de formato da resposta em
   `processarRegraEntrega()`.
8. GTI SMS recusa mensagem acima de 160 caracteres — não é bug, é regra do provedor.

## Pendências reais fora do controle deste repositório

- Endpoint `GET /renovacoes/pendentes` no projeto Logística antigo (Postgres, outro repositório)
  ainda não existe — regra tipo "entrega" sempre vai falhar (com log `warning`, sem travar mais)
  até esse endpoint ser criado lá. Contrato especificado em
  `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`.
- `LogEmailThrottleHandler` continua com risco de travar em SMTP se outro log de erro (fora do
  fluxo de SMS) disparar com o SMTP mal configurado — usuário decidiu não mexer nisso agora.

## Fluxo de trabalho deste projeto (seguir sempre)

Ver `CLAUDE.md` na raiz do repo: todo ciclo de feature passa por
`byarq → bydoc → bydev → byrev → bytest → bydoc (entrega)`, documentos em `.md`+`.docx` salvos
em `docs/desenvolvimento`, `docs/revisao`, `docs/testes`, `docs/entrega`. Nunca rodar migration/
comando destrutivo sem confirmação explícita do usuário.
