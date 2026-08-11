# Plano de Testes --- oco_ocorrencia_acao / Tratativa Parcial

**Projeto:** CeqWeb 3.0 **Módulo:** Ocorrências
(`app/Controllers/Ocorrencia`) **Feature:** `oco_ocorrencia_acao` ---
Tratativa Parcial (execução independente por ação, status intermediário
"Parcialmente Tratada") **Origem:** Plano de testes definido pelo
`bytest`. **Status:** aguardando aprovação do `byarq` para execução.

------------------------------------------------------------------------

## 1. Pré-requisitos de massa de dados

- **Subtipo A:** catálogo 100% `sta_fina='S'` (≥2 ações, tipos variados,
  ex: tipo 4 "Alterar Status" + tipo 5 "Notificação Fornecedor").
- **Subtipo B:** catálogo 100% `sta_fina='N'` (≥2 ações).
- **Subtipo C (misto):** ≥1 ação `sta_fina='S'` + ≥1 `sta_fina='N'`,
  incluindo ao menos uma ação tipo 1 (Justificar) e uma tipo 4 (Alterar
  Status).
- **Subtipo D:** sem nenhuma linha em `oco_subt_ocorrencia_acao`
  (catálogo vazio).
- **Subtipo E:** ação tipo 3 "Gerar Movimentação" com `sta_fina='S'`,
  associada a config que permita forçar erro em
  `geraMovimentoRequisicoes()` (ex: lote/quantidade inválida).
- Usuário com permissão completa (CAEXN) em
  `OcoOcorrencia`/`OcoTrataOcorrencia`, e outro sem permissão (ou só
  "C"), para os testes de acesso.
- Migration `2026-08-10-000001_OcoOcorrenciaAcao.php` já rodada em dev.

------------------------------------------------------------------------

## 2. P0 --- Crítico (lógica de status e não-duplicação)

  --------------------------------------------------------------------------------------------------------------------------------------
  ID              Pré-condição       Passos                                            Resultado esperado
  --------------- ------------------ ------------------------------------------------- -------------------------------------------------
  T01             Subtipo A          Criar ocorrência vinculada ao Subtipo A           `stt_id=29`; todas as linhas de
                  cadastrado (100%                                                     `oco_ocorrencia_acao` com `oac_executada='S'`,
                  `sta_fina='S'`)                                                      `oac_automatica=1`, `usu_executou=NULL`,
                                                                                       `oac_auto='S'`

  T02             Subtipo B          Criar ocorrência vinculada ao Subtipo B           `stt_id=28`; linhas semeadas (`oac_auto='N'`) mas
                  cadastrado (100%                                                     `oac_executada='N'` em todas
                  `sta_fina='N'`)                                                      

  T03             Subtipo C          Criar ocorrência vinculada ao Subtipo C           `stt_id` = "Parcialmente Tratada" (id real da
                  cadastrado (misto)                                                   linha inserida pela migration); só as
                                                                                       `oac_auto='S'` ficam executadas

  T04             Subtipo D          Criar ocorrência vinculada ao Subtipo D           `stt_id=29` (comportamento antigo preservado);
                  cadastrado (sem                                                      zero linhas em `oco_ocorrencia_acao`
                  ações)                                                               

  T05             Ocorrência         Abrir `finalizar()` da ocorrência do T03;         Ação desmarcada continua `oac_executada='N'`;
                  resultante de T03  desmarcar checkbox de uma ação pendente; executar demais viram `'S'`, `oac_automatica=0`,
                                     as demais                                         `usu_executou` preenchido; status recalculado
                                                                                       continua "Parcialmente Tratada"

  T06             Ocorrência         Completar a última ação pendente do T05           `stt_id=30` (Finalizada manual); todas
                  resultante de T05,                                                   `oac_executada='S'`
                  com uma pendência                                                    
                  restante                                                             

  T07             Subtipo E (ou 2    Executar tratativa com falha proposital em uma    Após a 1ª tentativa: ação com sucesso já
                  ações na mesma     ação; verificar persistência; corrigir a causa da persistida `oac_executada='S'` (fora de transação
                  rodada, uma        falha e reenviar                                  agregada); a que falhou continua `'N'` com
                  forçada a falhar)                                                    `oac_erro=1`/`oac_msg`; `stt_id` não avança para
                                                                                       29/30 nesse submit. Após corrigir e reenviar:
                                                                                       ação já executada não reprocessa (não duplica
                                                                                       movimentação de estoque, pois sai de
                                                                                       `montaAcoesAutomaticas()`/`montaAcoesManuais()`
                                                                                       ao ficar `'S'`); notificação de fornecedor também
                                                                                       não duplica (guard em `gerarNotificacaoDesvio()`)

  T08             Subtipo C com ação Criar a ocorrência (rodada automática); depois,   Na criação: só a ação tipo 4 atualiza
                  tipo 1             em rodada manual separada, preencher a            `pro_sap_produto.stt_id`. Na tratativa manual
                  (Justificar)       justificativa da ação pendente                    posterior: `oco_ocorrencia.oco_justi` grava o
                  `sta_fina='N'` +                                                     texto da rodada manual sem sobrescrever/zerar o
                  ação tipo 4                                                          `stt_id` do produto já gravado antes
                  (Alterar Status)                                                     
                  `sta_fina='S'`                                                       

  T09             Produtos de        Rodar `gerarOcorrencias()` via                    Cada ocorrência criada passa pelo motor novo
                  Subtipo A e        `AteRequisicao`/`ConfRequisicao`/`InspecaoProd`   igual ao caso manual; fluxo de
                  Subtipo C                                                            atendimento/requisição conclui sem erro; claim
                  disponíveis no                                                       atômico do `oco_id` não quebra
                  fluxo de lote                                                        
  --------------------------------------------------------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 3. P1 --- Regressão e consulta somente-leitura

  ----------------------------------------------------------------------------------------------------
  ID              Pré-condição             Passos                           Resultado esperado
  --------------- ------------------------ -------------------------------- --------------------------
  T10             Ocorrência com mistura   Abrir `OcoOcorrencia::show()`    Nenhum checkbox/select
                  de ações executadas e    dessa ocorrência                 editável (nem em linha
                  pendentes                                                 pendente --- mostra
                                                                            "Pendente" via
                                                                            `forcaLeitura=true`);
                                                                            `tpa_nome` preenchido em
                                                                            todas as linhas; linha
                                                                            executada mostra
                                                                            "Executada em dd/mm/aaaa
                                                                            (Automática/Manual)"

  T11             Ocorrência aberta em     Adicionar ação via botão "+" com `pro_sap_produto.stt_id`
                  tratativa                tipo 4 (Alterar Status),         grava exatamente o valor
                                           escolhendo `stt_id` específico   escolhido (valida fix do
                                                                            bug de indexação do
                                                                            Ordem); nova linha sem
                                                                            `oac_id` prévio (INSERT),
                                                                            `oac_auto='N'`,
                                                                            `oac_automatica=0`

  T12             Ocorrência "órfã" (sem   Acessar `show()` e `finalizar()` `show()`/`finalizar()` não
                  linhas em                dessa ocorrência                 quebram; `finalizar()`
                  `oco_ocorrencia_acao`,                                    semeia na hora se aberta
                  simulando dado                                            pendente
                  pré-migration)                                            

  T13             Ocorrência com ação tipo Acessar a exibição dessa ação    Sem erro fatal (fix de
                  2 "Abrir Tela" já                                         `getTelas()` →
                  executada                                                 `ConfigTelaModel`); nome
                                                                            da tela aparece
                                                                            corretamente

  T14             Ocorrência existente já  Editar a ocorrência re-chamando  `seedAcoes()` não duplica
                  com linhas em            `store()`/`processAfterSave()`   linhas (já existem);
                  `oco_ocorrencia_acao`                                     apenas ações ainda
                                                                            pendentes com
                                                                            `oac_auto='S'` seriam
                                                                            reprocessadas, sem
                                                                            duplicidade
  ----------------------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 4. P2 --- UI, campos condicionais e edge cases

  ---------------------------------------------------------------------------
  ID              Pré-condição      Passos          Resultado esperado
  --------------- ----------------- --------------- -------------------------
  T15             Tela de tratativa Selecionar, na  Só o bloco correspondente
                  manual aberta     tratativa       ao `tpa_tipo` aparece
                                    manual, cada    visível
                                    `tpa_tipo`      (`verificaTipoAcao()`);
                                    disponível      `oco_justi` obrigatório
                                                    quando não
                                                    somente-leitura

  T16             Ocorrência com    Marcar a ação   Front pede confirmação
                  ação "Gerar       "Gerar          (MSG 6) antes de enviar
                  Movimentação"     Movimentação" e 
                  disponível        tentar submeter 

  T17             Linha ad-hoc e    Reproduzir o    Documentar se ocorrem 2
                  linha do seed com cenário de      linhas para o mesmo
                  o mesmo `tpa_id`  duplicidade de  `tpa_id` --- não é
                  (débito técnico   `tpa_id` entre  regressão nova, é achado
                  conhecido,        linha ad-hoc e  já registrado na revisão
                  não-bloqueante)   linha do seed   

  T18             Nenhuma           Inspecionar     Confirmar no código que
                  (validação        código-fonte do checagem + `insertBatch`
                  estática de       seed de ações   do seed ocorrem dentro da
                  código)                           mesma transação
                                                    (mitigação já aplicada);
                                                    sem teste dinâmico de
                                                    concorrência real
  ---------------------------------------------------------------------------

------------------------------------------------------------------------

## 5. P2 --- Permissões (CAEXN), fail-closed

  ------------------------------------------------------------------------------------------
  ID              Pré-condição           Passos                    Resultado esperado
  --------------- ---------------------- ------------------------- -------------------------
  T19             Perfil sem permissão   Tentar acessar            `vw_semacesso`, nenhuma
                  em                     `finalizar()`/`store()`   gravação
                  `OcoTrataOcorrencia`                             

  T20             Perfil só com          Tentar acessar            `show()`/lista
                  permissão "C"          `show()`/lista e depois   acessíveis,
                  (consulta)             `finalizar()`/`store()`   `finalizar()`/`store()`
                                                                   bloqueados --- confirmar
                                                                   letra de permissão
                                                                   correta (E vs A) com
                                                                   `byarq` antes de executar

  T21             Perfil sem permissão   Tentar acessar `store()`  Bloqueado antes de
                  "A" em `OcoOcorrencia` (criação)                 `processAfterSave()`
  ------------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 6. Observações para o byarq

- T07 e T17 dependem de forçar erro controlado em
  `geraMovimentoRequisicoes()` --- se não houver forma limpa de simular
  em dev, documentar como "teste de código" (inspeção) em vez de
  execução real.
- T20: `bytest` tem incerteza sobre qual letra de permissão (E vs A) o
  Filter espera para `finalizar()`/`store()` de `OcoTrataOcorrencia` ---
  pedir para `byarq` confirmar a convenção antes da execução.
- Nenhum teste destrutivo (DROP/DELETE em massa) está previsto; T12 usa
  deleção pontual de linhas de teste em dev, com confirmação explícita
  antes de executar.
