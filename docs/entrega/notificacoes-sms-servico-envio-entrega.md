# Documento de Entrega — Serviço de Envio de SMS (SmsService + Comando CLI NotifSmsVerificar, Módulo Logística)

**Projeto:** CeqWeb 3.0
**Módulo:** Logística — `SmsService` (biblioteca de integração com o provedor SMS Dev) · `NotifSmsVerificar` (comando CLI `notifsms:verificar`)
**Documento de origem:** `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.md` (aprovado)
**Documentos de ciclo relacionados:** `docs/testes/notificacoes-sms-servico-envio-resultado-testes.docx`
**Requisito original:** `docs/notificacoes-sms.md`
**Status final:** **Pronto para produção, com pendências explícitas de deploy/integração** — ver seção "Pendências", que lista o que ainda precisa ser feito manualmente ou em outro repositório antes de o disparo de SMS funcionar de ponta a ponta. Nada listado nessa seção foi executado em produção; nada deve rodar sem confirmação explícita do Douglas.

------------------------------------------------------------------------

## Resumo

Foi desenvolvida a parte da feature de Notificações SMS que efetivamente dispara os SMS: a biblioteca `SmsService` (integração com o provedor SMS Dev) e o comando CLI `notifsms:verificar`, que lê as regras cadastradas em `log_notif_sms_config` e decide quando disparar — regras do tipo `entrega` (consultando uma API nova a ser criada no projeto Logística antigo) e regras do tipo `saldo_baixo` (consultando o saldo de créditos do próprio provedor SMS Dev). A deduplicação de disparos é feita via `log_notif_sms_enviadas`.

Este ciclo é a continuação do módulo Logística cujo CRUD/consulta administrativa (`NotifSmsConfig`/`NotifSmsEnviadas`) já foi entregue anteriormente — ver `docs/entrega/notificacoes-sms-entrega.docx`. Este ciclo cobriu exclusivamente o motor de disparo (biblioteca + comando CLI), sem tocar nas telas administrativas, migrations ou infraestrutura já entregues.

O ciclo foi executado conforme o fluxo padrão do time: planejamento aprovado pelo `byarq` (com 1 correção crítica incorporada antes da codificação — ver "Decisões de Arquitetura Importantes"), codificação pelo `bydev`, revisão de código pelo `byrev` sem bloqueantes (1 sugestão não-bloqueante aceita e incorporada), e uma rodada de testes com validação real em ambiente de dev, com **7 de 7 testes passando e nenhum bug encontrado**.

------------------------------------------------------------------------

## Arquivos Criados

| Arquivo | Conteúdo |
|---|---|
| `app/Libraries/SmsService.php` | Biblioteca de integração com o provedor SMS Dev via cURL direto. Métodos públicos: `enviar(string $telefone, string $mensagem): bool` (normaliza o telefone para dígitos e envia o SMS) e `consultarSaldo(): ?int` (consulta o saldo de créditos disponíveis; retorna `null` se a consulta falhar ou a API não confirmar `situacao = 'OK'`). Chave de API lida de `getenv('SMSDEV_API_KEY')`. |
| `app/Commands/NotifSmsVerificar.php` | Comando `spark` (`notifsms:verificar`, grupo `Logistica`), execução única (sem loop/daemon — o agendamento é responsabilidade do cron do SO). Varre as regras ativas retornadas por `getRegrasAtivas()` e, para cada uma, chama `processarRegraSaldo()` ou `processarRegraEntrega()` conforme `nsc_tipo_regra`, dentro de um `try/catch` por regra (uma regra com erro não interrompe o processamento das demais). |

------------------------------------------------------------------------

## Arquivos Alterados

| Arquivo | Alteração |
|---|---|
| `app/Config/Constants.php` | Nova constante `LINK_LOGISTICA`, seguindo o mesmo padrão já existente de `LINK_CEQWEB2`. |
| `app/Models/Logis/LogisNotifSmsConfigModel.php` | Novo método `getRegrasAtivas()` — retorna as regras com `nsc_ativo = 'A'`, respeitando soft delete automaticamente via `findAll()` (mesmo padrão de `getListaRegras()`, corrigido no ciclo anterior). |
| `app/Models/Logis/LogisNotifSmsEnviadasModel.php` | Propriedade `$allowedFields` adicionada (`nse_chave`, `nse_nsc_id`, `nse_data_envio`) + novos métodos `jaEnviado(string $chave, int $nscId): bool` e `registrar(string $chave, int $nscId): void`, usados para a deduplicação de disparos. |

Nenhuma migration, view, rota ou tela nova neste ciclo — trabalho 100% backend/CLI.

------------------------------------------------------------------------

## Já Validado (dev, execução real)

Todos os 7 testes do plano foram executados de fato contra o ambiente de dev (PHP CLI + MySQL `dev_logistica_db`) e passaram, sem bug encontrado — detalhamento completo em `docs/testes/notificacoes-sms-servico-envio-resultado-testes.docx`:

- Sintaxe PHP dos 5 arquivos da feature, sem erro.
- `getRegrasAtivas()` contra o banco real: regra ativa retornada, inativa excluída corretamente.
- **Ponto crítico validado**: `registrar()`/`jaEnviado()` testados de verdade contra o banco — `insert()` persistiu de fato (confirmado por `SELECT` direto na tabela), `jaEnviado()` confirmou `true` para a chave gravada e `false` para chave nunca registrada. Este era exatamente o risco apontado pelo `byarq` na revisão do documento de desenvolvimento (`$allowedFields` ausente no Model original, que faria todo `insert()` falhar e o SMS reenviar a cada execução do cron sem que ninguém percebesse) — confirmado corrigido e funcional em execução real.
- Lógica de `processarRegraSaldo()` (comparação de saldo, chave de dedup, substituição de placeholders via `strtr`) e de `processarRegraEntrega()` (janela de tempo `antes_chegada`/`apos_chegada`, incluindo casos de borda no limite exato e imediatamente além dele) validadas isoladamente, sem depender de chamadas reais às APIs externas.
- `array_filter` dos parâmetros `ren_tipo`/`ren_status_max` nulos, antes de chamar `api_request()`, confirmado correto (chave nula omitida, valor `0` preservado).

Nenhum resíduo de dados de teste ficou no banco ao final da rodada.

------------------------------------------------------------------------

## Decisões de Arquitetura Importantes (resumo)

Detalhamento completo em `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`. Resumo das decisões mais relevantes para quem for dar manutenção:

- **Convenção de comando CLI corrigida em relação ao desenho original**: `docs/notificacoes-sms.md` propunha um Controller CLI (`app/Controllers/Cli/*`); a convenção real do projeto é `App\Commands\*` (extends `BaseCommand`, registrado via `spark`), conforme o único comando já existente, `WorkAnalise`. `NotifSmsVerificar` segue essa convenção, mas **não é daemon** (diferente de `WorkAnalise`, que roda em `while(true)`) — é execução única, pois o agendamento periódico é responsabilidade do cron do SO.
- **Origem dos dados de entrega/renovação corrigida em relação ao desenho original**: não existe (e nunca existiu) um `RenovacaoModel` local; os dados vivem no projeto Logística antigo (outro repositório). Este ciclo implementa o **consumo** de uma API nova a ser criada lá (`GET /renovacoes/pendentes`, contrato especificado na seção 4 do documento de desenvolvimento), via `api_request()` (helper genérico já existente, também usado por `Estoque/Requisicao.php`) — sem cliente HTTP próprio.
- **Falha da API do Logística antigo não trava as demais regras**: se `api_request()` retornar `null`, a regra de entrega correspondente é pulada e o erro é logado, sem interromper o processamento das demais regras ativas na mesma execução (mesmo princípio do `try/catch` por regra).
- **`$allowedFields` obrigatório em `LogisNotifSmsEnviadasModel`** — correção crítica incorporada **antes** da codificação, apontada pelo `byarq` na revisão do documento de desenvolvimento: sem essa propriedade, `registrar()` falharia em todo `insert()` (exceção engolida pelo `try/catch` por regra), o SMS seria enviado normalmente mas o dedup nunca seria persistido, causando reenvio/flood a cada execução do cron. Validada com execução real nesta rodada de testes (ver seção anterior).
- **Deduplicação reaproveitada do desenho original, sem mudanças**: chave `'SALDO:' . date('Y-m-d')` para regras de saldo (1 alerta/dia) e `'REN:' . $ren['ren_id']` para regras de entrega (1 alerta por renovação/entrega), ambas gravadas em `log_notif_sms_enviadas` (tabela já criada no ciclo anterior).

------------------------------------------------------------------------

## Pendências Fora Deste Repositório / Ações Manuais Antes de Funcionar em Produção

**Nada nesta seção foi executado em produção. Nada deve rodar sem confirmação explícita do Douglas.**

1. **Configurar `SMSDEV_API_KEY` no `.env` de produção** (chave real do provedor SMS Dev). Pendência já sinalizada desde o ciclo anterior (`docs/entrega/notificacoes-sms-entrega.docx`, seção 3.3), ainda não resolvida.
2. **Implementar o endpoint `GET /renovacoes/pendentes` no projeto Logística antigo** (contrato especificado em `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx`, seção 4), com parâmetros `ren_status_max` (obrigatório) e `ren_tipo` (opcional), retornando array JSON com `ren_id`/`ren_tipo`/`ren_status`/`ren_prev_chegada`. Este endpoint pertence a outro repositório e não foi tocado neste ciclo.
3. **Definir `LOGISTICA_API_KEY`** (chave compartilhada, header `X-Api-Key`) nos dois lados (CeqWeb3 e Logística antigo) — decisão de infraestrutura a validar com quem administra o Logística antigo, não é algo que o CeqWeb3 decide unilateralmente.
4. **Registrar a linha de cron no servidor de produção:**
   ```
   */5 * * * * php /caminho/do/projeto/spark notifsms:verificar >> /var/log/ceqlog_sms.log 2>&1
   ```
   Confirmar o caminho real do PHP/`spark` e do arquivo de log com quem administra o servidor antes de ativar.
5. **Testar de ponta a ponta o fluxo de regras `entrega`** assim que o endpoint do Logística antigo existir — não foi possível nesta rodada de testes (pendência externa, item 2 acima). A lógica interna (janela de tempo, dedup, tratamento de falha da API) já foi validada isoladamente.
6. **Cadastrar as regras reais de negócio na tela `NotifSmsConfig`** (já entregue no ciclo anterior) antes de o cron começar a rodar de verdade em produção — sem regras cadastradas e ativas, o comando não dispara nada.

------------------------------------------------------------------------

## Rastreabilidade

- **Requisito original:** `docs/notificacoes-sms.md`.
- **Desenvolvimento:** `docs/desenvolvimento/notificacoes-sms-servico-envio-dev.docx` (aprovado pelo `byarq`; 1 correção crítica de `$allowedFields` incorporada antes da codificação).
- **Revisão de código:** sem documento formal — `byrev` não encontrou bloqueante nesta feature; a única sugestão não-bloqueante (`array_filter` de parâmetros nulos, `ren_tipo`/`ren_status_max`) foi aplicada já durante a codificação e confirmada na rodada de testes.
- **Testes:** `docs/testes/notificacoes-sms-servico-envio-resultado-testes.docx` (7 de 7 testes passaram, nenhum bug encontrado).
- **Ciclo anterior desta feature (CRUD/consulta):** `docs/entrega/notificacoes-sms-entrega.docx`.

------------------------------------------------------------------------

## Conclusão

O desenvolvimento do Serviço de Envio de SMS (`SmsService` + comando CLI `NotifSmsVerificar`) está funcionalmente completo, revisado sem bloqueantes e testado com validação real em ambiente de dev, conforme o documento de desenvolvimento aprovado. A entrega está **pronta para produção**, condicionada à execução do checklist de pendências acima — em especial a configuração de `SMSDEV_API_KEY` (item 1) e a implementação do endpoint `/renovacoes/pendentes` no Logística antigo (item 2), sem os quais o disparo de SMS não funciona de ponta a ponta mesmo com o código desta entrega 100% operacional.
