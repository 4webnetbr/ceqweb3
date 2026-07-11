# Referência interna — Runtime JS/CSS do front-end

> Rascunho gerado por engenharia reversa de `my_default.js`, `my_fields.js`, `my_mask.js`, `my_lista.js`, `my_filter.js`, `my_menu.js`, `my_consulta.js`, `my_wsconn.js`, `default.css`, `menu.css`, `login.css`. Revisar antes de formalizar como conhecimento fixo dos agentes.

## Stack confirmada

- **jQuery** em modo `noConflict()` — sempre usar `jQuery`, nunca `$`.
- **Bootstrap 5** (modais via `bootstrap.Modal`/`bootstrap.Offcanvas`, `bootstrap-select`/`selectpicker` para todos os selects).
- **DataTables** (com extensões `buttons`, `searchBuilder`, `excelHtml5`, `pdfHtml5`, `print`, `colvis`, `responsive`) para todas as listagens.
- **daterangepicker** + **moment.js** para filtros de período.
- **Summernote** para campos de texto rico (classe `.editor`, ligado ao `crEditor()` da `MyCampo`).
- **Font Awesome** para ícones.
- **WebSocket** próprio (`wss://.../ws`) para notificações em tempo real entre abas/usuários.

**Regra para o `bydsgn`:** nunca introduzir uma biblioteca de UI concorrente (ex: outro plugin de select, outro sistema de toast/alert, outro date range picker) — o sistema já padroniza em cima dessas.

## Infraestrutura de AJAX — nunca usar `jQuery.ajax` cru

Toda chamada ao back-end deve passar pelas funções já existentes:

- **`executaAjax(url, tipo, dados)`** — chamada AJAX síncrona-por-callback tradicional; popula a variável global `retornoAjax`.
- **`await executaAjaxWait(url, tipo, dados)`** — versão baseada em Promise, para uso com `async/await` (preferível em código novo).
- Ambas já tratam bloqueio de tela (`bloqueiaTela()`/`desBloqueiaTela()`) e erro padrão via `boxAlert(...)`.

**Nunca** escrever um novo `jQuery.ajax({...})` do zero — sempre usar essas duas funções.

## Feedback ao usuário — nunca usar `alert()`/`confirm()` nativos

- **`boxAlert(mensagem, erro, url, aguardaClique, tipo, ..., titulo, dadosExtra)`** — sistema central de alerta/confirmação/seleção em modal. `mensagem` também aceita **código numérico** que busca o texto via `getMensagem()` (helper PHP, tabela `cfg_mensagem`) — ou seja, mensagens padronizadas do sistema têm um código central, não string solta espalhada pelo código.
- **`mostranoToast(msg)`** — notificação tipo toast (usada em eventos de WebSocket: login, entrada/saída de usuário, etc.).
- **`fecharTodosModais(opcoes)`** — fecha todos os modais/offcanvas abertos de forma limpa (inclusive Bootbox), com opção de aguardar animação.

**Regra para o `bydsgn`:** qualquer alerta, confirmação ou mensagem ao usuário deve usar `boxAlert()`/`mostranoToast()` — nunca `alert()`, `confirm()` ou um modal Bootstrap construído do zero para essa finalidade.

## Bloqueio de tela

- **`bloqueiaTela()`** / **`desBloqueiaTela()`** — mostram/escondem o overlay de carregamento (`#bloqueiaTela`). Sempre usado ao redor de chamadas AJAX que demandam espera.

## Listagens (grids)

- **`montaListaDados(tabela, url)`** — monta um DataTable padrão a partir de uma URL (endpoint retorna JSON), já com: paginação em português, botões de exportação (Excel/PDF/Print), filtro (`searchBuilder`), controle de colunas visíveis (`colvis`), tooltip automático em células que contenham `<ttp>conteúdo</ttp>` (formato especial de "texto + tooltip" já suportado nativamente).
- Padrão de tabela: a **última coluna** com cabeçalho `"Ação"` vira automaticamente não ordenável/não pesquisável e ganha a classe `acao`. Clicar em qualquer célula da linha (fora da coluna de ação) aciona o primeiro botão de ação da linha.
- **Regra para o `bydsgn`:** toda grid nova deve usar `montaListaDados()`, não reinventar configuração de DataTable do zero. Se precisar de uma variação (ex: uma tabela de saldo/log específica), seguir o padrão de `montaListaSaldo`/`montaListaLogs` como exemplo, não a config crua do DataTable.

## Máscaras de campo (`my_mask.js`)

Função central: **`mascara(obj, tipo)`**, chamada via atributo `onkeyup="mascara(this, 'TIPO')"`. Tipos já implementados: `mcep`, `mtel`, `mcel`, `mcel2`, `mcnpj`, `mcpf`, `mdata`, `mtempo`, `mhora`, `mrg`, `mnum`, `mquantia`, `mvalor`, `mip`. Complementares: `converteMoedaFloat()` / `converteFloatMoeda()` (conversão string↔float de moeda BR), `entrar_moeda()`/`sair_moeda()` (foco em campo moeda).

**Nota:** essas máscaras já são aplicadas automaticamente pela `MyCampo::crInput()` conforme o `$tipo` do campo (ver referência da `MyCampo`) — o `bydsgn` só precisa saber que existem e nunca reimplementá-las manualmente.

## Autocomplete/consulta externa (`my_consulta.js`)

- **`pesquisacep(obj, valor)`** — ao digitar um CEP, consulta ViaCEP e preenche automaticamente rua/bairro/estado/cidade do formulário (suporta campos em lista indexada, ex: `end_rua[2]`).
- **`pesquisaCNPJ(valor)` / `pesquisaCPF(valor)`** — verifica duplicidade via endpoint próprio (`/buscas/cnpjcpfcadastrado`) e, para CNPJ, busca dados públicos da empresa via API externa (Speedio) para autopreencher nome/apelido.
- **Regra:** qualquer campo novo de CEP/CNPJ/CPF em formulário de cadastro deve reusar esses helpers, não reimplementar a consulta.

## Menu lateral (`my_menu.js`)

- Menu colapsável/hover (`atualizaMenu()`), com detecção de mobile (`isMobile()` via `matchMedia`) que alterna entre clique (mobile) e hover (desktop).
- Estado "aberto/fechado" do menu persiste via cookie (`menuaberto`).
- Busca de itens de menu embutida (`buscaMenu(termo)`), filtrando itens e ocultando submenus/acordeões vazios.
- Item ativo é marcado automaticamente comparando o controller da URL atual com o `id` do item de menu (`atualizaMenu()`).

## WebSocket (`my_wsconn.js`)

- Conexão única (`wss://.../ws`) registrada por aba/usuário, com keepalive automático a cada 30s e reconexão automática em caso de queda.
- Eventos tratados: `Entrou`/`Saiu`/`Login` (toast), `Servidor`/`MsgServer` (notificação dirigida a um usuário específico), `AtualizarControler` (recarrega o DataTable da tela atual se for a mesma tela), `NovaInspecao`/`NovaOcorrencia` (eventos de domínio específicos do negócio).

## Submissão de formulário padrão

- Botões `#bt_salvar` / `#bt_salvar_modal` já têm um handler genérico (`my_default.js`) que: bloqueia a tela, roda validação HTML5 (`was-validated`), verifica se algum campo com `data-valid` foi alterado sem confirmação (`boxAlert` de confirmação), e então submete via **`submeteForm()`**, que usa `executaAjax` e trata o retorno (erro via `boxAlert`, sucesso via redirecionamento ou fechamento de modal + toast).
- **Regra para o `bydev`/`bydsgn`:** telas de cadastro novas devem usar o `id="bt_salvar"` (ou `"bt_salvar_modal"` dentro de modal) e o formulário com `id="form1"` (ou `"form_modal"`) para herdar esse comportamento — não implementar um `submit` handler próprio do zero.

## Convenções visuais (`default.css`, `menu.css`, `login.css`)

- Variáveis CSS customizadas em `:root` (ex: `--bs-blue-dark`, `--bs-green-dark`, `--bs-gray-padrao`) além das nativas do Bootstrap — usar essas variáveis/classes utilitárias (`bg-blue-dark`, `bg-green-dark`, etc.) em vez de cores soltas.
- Fonte padrão: `"Open Sans", sans-serif`, `font-size: 14px` (12px em dispositivos com `hover: none`, ou seja, touch/mobile).
- Layout com sidebar fixa retrátil (`.sidebar`) que expande em hover (desktop) ou clique (mobile), e conteúdo (`.content`) que ajusta margem conforme `.menuaberto`.
- Campos obrigatórios recebem indicador visual próprio via CSS (`:required` com ícone de fundo), não é necessário adicionar `*` manualmente no label.
- Contador de caracteres em campos de texto usa a classe `.div-caract` (com variante `.acabou` ao se aproximar do limite) — ligado ao `crTexto()`/`crEditor()` da `MyCampo`.

## Observações gerais para `bydsgn`/`bydev`

- Antes de criar qualquer tela nova: reusar `montaListaDados()` para grids, `boxAlert`/`mostranoToast` para feedback, `executaAjax`/`executaAjaxWait` para chamadas, e os padrões de botão/formulário (`#bt_salvar`, `#form1`) para submissão.
- Nunca introduzir uma nova convenção de nome de função/variável global (ex: outro sistema de alerta) — sempre verificar primeiro se já existe equivalente nesses arquivos.
