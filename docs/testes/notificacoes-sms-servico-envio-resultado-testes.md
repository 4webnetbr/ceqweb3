# Resultado de Testes — Serviço de Envio de SMS (SmsService + Comando CLI NotifSmsVerificar, Módulo Logística)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística — `SmsService` (`app/Libraries/SmsService.php`) · `NotifSmsVerificar` (comando CLI `notifsms:verificar`)
**Documento de origem:** `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` (aprovado pelo `byarq`)
**Executor:** `bytest`
**Ambiente de execução:** PHP CLI + MySQL, banco `dev_logistica_db` — mesmo ambiente de dev usado no ciclo anterior (CRUD/consulta)
**Status do ciclo de código:** `byrev` — sem bloqueantes. 1 sugestão não-bloqueante (array_filter de `ren_tipo`/`ren_status_max` nulos) já incorporada antes desta rodada de testes.

------------------------------------------------------------------------

## 0. Resumo executivo

**Todos os 7 testes previstos foram executados contra o ambiente real de dev e PASSARAM. Nenhum bug foi encontrado nesta rodada.**

Diferente da rodada de testes do ciclo anterior (CRUD/consulta), que havia encontrado 2 bugs reais na primeira tentativa, esta rodada validou de fato — com execução real, não apenas leitura de código — os pontos mais sensíveis do desenho aprovado: a persistência da deduplicação em `log_notif_sms_enviadas` (item que motivou a correção crítica de `$allowedFields` apontada pelo `byarq` ainda na fase de desenvolvimento, ver seção 6.2 do documento de desenvolvimento) e as lógicas de janela de tempo/dedup dos dois tipos de regra (`entrega`/`saldo_baixo`).

Nenhum resíduo de dados de teste ficou no banco ao final da rodada.

------------------------------------------------------------------------

## 1. Condições reais de execução

- Ambiente: PHP CLI + MySQL `dev_logistica_db`, mesmo ambiente de dev do ciclo anterior desta feature.
- Executado de fato: lint de sintaxe PHP nos 5 arquivos envolvidos; consultas e gravações reais contra o banco (`getRegrasAtivas()`, `registrar()`/`jaEnviado()`, incluindo `SELECT` direto de confirmação); execução isolada da lógica de `processarRegraSaldo()`/`processarRegraEntrega()` e do `array_filter` de parâmetros nulos, sem depender de chamada real às APIs externas.
- **Não executado nesta rodada** (pendência externa confirmada, fora de escopo): teste de ponta a ponta contra o provedor SMS Dev real (falta `SMSDEV_API_KEY` de teste) e contra a API do Logística antigo (endpoint `GET /renovacoes/pendentes` ainda não existe naquele repositório — ver seção 4 do documento de desenvolvimento).
- Dados de teste inseridos durante a execução foram removidos ao final; nenhum resíduo permanece em `log_notif_sms_config`/`log_notif_sms_enviadas`.

------------------------------------------------------------------------

## 2. Resultados por caso

| ID | Descrição | Via | Resultado |
|---|---|---|---|
| TC-01 | `php -l` nos 5 arquivos da feature (`SmsService.php`, `NotifSmsVerificar.php`, `Constants.php`, `LogisNotifSmsConfigModel.php`, `LogisNotifSmsEnviadasModel.php`) | Execução real | **PASSA** — sem erro de sintaxe em nenhum dos 5 arquivos. |
| TC-02 | `getRegrasAtivas()` contra o banco real | Execução real | **PASSA** — regra ativa (`nsc_ativo='A'`) retornada corretamente; regra inativa excluída do resultado, conforme `where('nsc_ativo', 'A')->findAll()`. Dados de teste inseridos para o caso foram removidos ao final. |
| TC-03 (crítico) | `registrar()`/`jaEnviado()` — persistência real da deduplicação | Execução real | **PASSA** — `insert()` de `registrar()` persistiu de fato (confirmado por `SELECT` direto na tabela, não apenas pelo retorno do método); `jaEnviado()` reconsultou a mesma chave e confirmou `true`. Este é exatamente o ponto do bug crítico do ciclo de desenvolvimento (`$allowedFields` ausente, seção 6.2 do dev doc) — confirmado corrigido e funcional em execução real, não apenas por leitura de código. |
| TC-04 | `jaEnviado()` para uma chave nunca registrada | Execução real | **PASSA** — retornou `false` corretamente, sem falso positivo. |
| TC-05 | Lógica de `processarRegraSaldo()` (saldo/dedup/`strtr`) | Execução isolada (sem chamar API real) | **PASSA** — comparação de saldo (`nsc_saldo_minimo` x saldo retornado) correta; chave de dedup montada como `'SALDO:' . date('Y-m-d')`; substituição de placeholders (`{limite}`/`{saldo}`) na mensagem via `strtr` funcionando como esperado. |
| TC-06 | Lógica de janela de tempo de `processarRegraEntrega()`, casos de borda | Execução isolada (sem chamar API real) | **PASSA** — casos testados: `antes_chegada` no limite exato do `nsc_minutos_limite` (dispara), `antes_chegada` 1 minuto além do limite (não dispara), `antes_chegada` com `diffMin <= 0` (já chegou/passou, não dispara); `apos_chegada` com `diffMin = 0` (dispara) e `apos_chegada` 1 minuto antes da chegada prevista (não dispara). Todos os 5 casos de borda bateram com o comportamento esperado do código conforme especificado na seção 7.2 do documento de desenvolvimento. |
| TC-07 | `array_filter` dos parâmetros `ren_tipo`/`ren_status_max` nulos antes da chamada a `api_request()` | Execução isolada | **PASSA** — chave `null` (ex. `ren_tipo` não definido na regra) omitida corretamente do array de parâmetros; valor `0` preservado (não é confundido com "vazio"). Confirma a sugestão não-bloqueante do `byrev` incorporada antes desta rodada. |

------------------------------------------------------------------------

## 3. Achados

Nenhum bug encontrado nesta rodada. Nenhum apontamento novo de `byrev` pendente.

------------------------------------------------------------------------

## 4. Pendências confirmadas como fora de escopo (não testáveis agora)

Conforme já sinalizado no documento de desenvolvimento (seções 12 e 13):

1. **Teste de ponta a ponta contra o provedor SMS Dev real** — depende de `SMSDEV_API_KEY` de teste, ainda não configurada. `enviar()`/`consultarSaldo()` não foram chamados contra a API real do SMS Dev nesta rodada.
2. **Teste de ponta a ponta de `processarRegraEntrega()` contra a API do Logística antigo** — depende do endpoint `GET /renovacoes/pendentes`, que ainda não existe naquele repositório (fora do CeqWeb3). A lógica interna (janela de tempo, dedup, tratamento de `null`) foi validada isoladamente (TC-06), mas a chamada real via `api_request()` ao endpoint de produção não pôde ser exercida.

Nenhuma dessas duas pendências é um bug ou reprovação — são dependências externas já previstas e documentadas desde a fase de desenvolvimento.

------------------------------------------------------------------------

## 5. Limpeza de dados de teste

Confirmado: nenhum resíduo de dados de teste ficou em `log_notif_sms_config`/`log_notif_sms_enviadas` ao final da rodada.

------------------------------------------------------------------------

## 6. Conclusão

**7 de 7 testes passaram. Nenhum bug encontrado.** Ciclo de testes desta parte da feature (Serviço de Envio de SMS) fechado, sem pendência bloqueante de código. Restam apenas as duas pendências externas da seção 4, que já constavam como fora de escopo desde o documento de desenvolvimento aprovado. Pronto para avançar ao documento de entrega.

------------------------------------------------------------------------

## 7. Rastreabilidade

- Documento de desenvolvimento: `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` (aprovado pelo `byarq`, com 1 correção crítica de `$allowedFields` incorporada antes da codificação).
- Revisão de código: sem documento formal de apontamentos — `byrev` não encontrou bloqueante nesta feature; única sugestão não-bloqueante (`array_filter` de parâmetros nulos) já aplicada e confirmada nesta rodada (TC-07).
- Requisito original: `docs/notificacoes-sms.md`.
- Ciclo anterior desta feature (CRUD/consulta): `docs/entrega/notificacoes-sms-entrega.docx`.
