# Projeto — Orquestração da equipe de agentes

Este projeto usa PHP + CodeIgniter 4, com as bibliotecas internas documentadas em
`docs/referencia/` (`rascunho-MyCampo.md`, `rascunho-runtime-js.md`, `rascunho-helpers-php.md`).
**Todo agente deve ler esses três documentos antes de planejar, codificar, revisar,
testar ou documentar qualquer coisa** — eles descrevem convenções obrigatórias do
projeto (uso de `MyCampo`, `criaSelectRelativo()`, `boxAlert`/`executaAjax`,
sistema de permissões `CAEXN`, etc.) e evitam reinventar o que já existe.

## A equipe

| Agente | Papel |
|---|---|
| `byarq` | Engenheiro de arquitetura — planeja, revisa e aprova em cada etapa |
| `bydoc` | Documentador — escreve todos os documentos do ciclo |
| `bydev` | Desenvolvedor — codifica; aciona `bydsgn` quando precisa desenhar tela/validar UX |
| `bydsgn` | Designer/UX — desenha telas e valida padrões de UX, **sob demanda do `bydev`** |
| `byrev` | Revisor de código — aponta problemas e sugestões de melhoria |
| `bytest` | Testador — define e executa os testes |

## Papel do agente principal (orquestrador)

Subagentes não conversam entre si — cada um roda isolado e devolve um resultado.
**Você (a sessão principal do Claude Code) é o orquestrador**: é você quem invoca
cada subagente na ordem certa (via `Task`), repassa o resultado de um para o
próximo, e decide quando um ciclo se fecha ou precisa repetir. Siga o fluxo abaixo
à risca — não pule etapas, não avance uma etapa sem o "aprovado" da etapa anterior.

## Fluxo obrigatório

Disparado sempre que o usuário pedir algo do tipo *"crie o que está sendo
solicitado nesse documento"* + um documento de requisito.

**Etapa 1 — Planejamento**
1. Invoque `byarq` com o documento de requisito. Ele planeja todo o desenvolvimento.
2. Invoque `bydoc` para escrever o **documento de desenvolvimento** a partir do plano do `byarq`.
3. Invoque `byarq` novamente para revisar/aprovar esse documento.
   - Se `byarq` não aprovar, volte para o passo 2 com os apontamentos dele.
   - Só avance com o documento de desenvolvimento **aprovado**.

**Etapa 2 — Codificação e revisão (ciclo)**
4. Invoque `bydev` para codificar conforme o documento aprovado.
   - `bydev` aciona `bydsgn` sempre que precisar desenhar uma tela nova ou validar um padrão de UX; incorpora o retorno e segue codificando.
5. Invoque `byrev` para revisar o código produzido.
   - **Se `byrev` não encontrar mais nada a contribuir** → siga para a Etapa 3.
   - **Se `byrev` encontrar problemas/sugestões** →
     a. Invoque `bydoc` para documentar os apontamentos do `byrev`.
     b. Invoque `byarq` para revisar esses apontamentos.
     c. Invoque `bydev` para aplicar as correções.
     d. Volte ao passo 5 (nova revisão do `byrev`). Repita até `byrev` não ter mais nada a contribuir.

**Etapa 3 — Testes (ciclo)**
6. Invoque `bytest` para definir os testes, com base nos documentos do `bydoc` e no que foi desenvolvido.
7. Invoque `bydoc` para documentar o plano de testes definido.
8. Invoque `byarq` para revisar o plano de testes.
9. Invoque `bytest` para **executar** os testes e produzir o documento de testes (resultados).
10. Entregue o documento de testes a `byarq`.
    - **Se houver bugs/correções** → invoque `bydev` para corrigir, e volte ao passo 5 (nova revisão de código) — o ciclo da Etapa 2 e da Etapa 3 se repete até não haver mais nada a corrigir/melhorar e tudo estar de acordo com o documento de desenvolvimento aprovado.
    - **Se não houver mais nada a corrigir** → siga para a Etapa 4.

**Etapa 4 — Entrega**
11. Invoque `bydoc` para escrever o **documento de entrega**, detalhando todo o desenvolvimento, todos os arquivos alterados, e tudo que deve ser feito upload para produção.

## Convenção de documentos

Todos os documentos do ciclo são entregues em **`.docx`** (o `bydoc` gera um
rascunho em Markdown internamente e converte via `pandoc` — ver `bydoc.md`).
Salvar cada documento gerado no ciclo em `docs/` do projeto, por exemplo:
- `docs/desenvolvimento/<feature>-dev.docx` — documento de desenvolvimento (e revisões)
- `docs/revisao/<feature>-revisao-NN.docx` — apontamentos de cada rodada de `byrev`
- `docs/testes/<feature>-plano-testes.docx` e `<feature>-resultado-testes.docx`
- `docs/entrega/<feature>-entrega.docx` — documento final de entrega

Isso mantém rastreável qual documento corresponde a qual etapa/rodada do ciclo.
