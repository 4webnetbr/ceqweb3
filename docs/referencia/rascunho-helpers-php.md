# Referência interna — Helpers PHP e sistema de telas/permissões

> Rascunho gerado por engenharia reversa de `funcoes_helper.php`, `mensagem_helper.php` e `LoginFilter.php`. Revisar antes de formalizar como conhecimento fixo dos agentes.

## Sistema de permissões e controle de tela (`LoginFilter`)

Todo acesso autenticado passa por um Filter do CodeIgniter 4 que:

1. Verifica sessão (`$session->logged_in`), senão redireciona ao login.
2. Identifica a "tela" atual pelo primeiro segmento da URL (nome do controller) e busca seus metadados em `ConfigTelaModel` (nome, ícone, controller, model, identificador, regras, texto do botão de adicionar).
3. Carrega e cacheia o menu do usuário na sessão (`montaMenu($perfilId, $tipoUsuario)`), evitando recarregar a cada requisição.
4. Verifica a permissão do perfil do usuário para aquela tela via `ConfigPerfilItemModel` — a permissão é uma **string de letras** (ex: `"CAEXN"`), onde cada letra libera uma ação:
   - Ausência de permissão (`''`) → bloqueia tudo.
   - `C` → permite **C**onsulta (`index`/`lista`/`show`).
   - `A` → permite **A**dição (`add`/`store`).
   - `E` → permite **E**dição (`edit`/`update`).
   - `X` → permite E**x**clusão (`delete`).
   - `N` → permite **N**otificações (recebimento/uso de notificações da tela via WebSocket).
   - A string é composta livremente combinando essas letras conforme o que o perfil pode fazer naquela tela (ex: `"CA"` = só consulta e adição; `"CAEXN"` = acesso completo).
5. Se não houver permissão, renderiza a view `vw_semacesso` (ou `vw_semacesso_modal` se a requisição for de modal) com a mensagem de erro, e interrompe a execução (`exit`).
6. Registra automaticamente um título de log de auditoria conforme o método chamado (`index`, `lista`, `show`, `add`, `store`, `edit`, `update`, `delete`, `ordena`, `ativinativ`) via `service('logContext')`.

**Regra para `byarq`/`bydev`:** toda tela nova (Controller) precisa ter um registro correspondente em `ConfigTelaModel` (tabela de telas) e as permissões correspondentes em `ConfigPerfilItemModel` para os perfis que devem acessá-la — senão a tela fica inacessível por padrão (fail-closed). Os métodos do Controller devem seguir a nomenclatura padrão (`index`, `lista`, `show`, `add`, `store`, `edit`, `update`, `delete`, `ordena`, `ativinativ`) para que o log de auditoria funcione automaticamente.

## Mensagens centralizadas (`mensagem_helper.php`)

- **`getMensagem(string $codigo): ?string`** — busca o texto de uma mensagem pelo código na tabela `cfg_mensagem` (com cache em memória por request). É isso que o `boxAlert()` do front-end usa quando recebe um código numérico em vez de uma string.
- **Regra:** mensagens de erro/aviso reutilizáveis devem ser cadastradas em `cfg_mensagem` e referenciadas pelo código, não escritas soltas no código PHP nem no JS.

## Helpers gerais (`funcoes_helper.php`)

### Documentação automática de estrutura de banco
- **`campos_tabela(array $campos)`** — gera uma tabela HTML com os campos de uma tabela do banco (rótulo, nome, tipo, tamanho, obrigatório, tipo de chave), a partir dos metadados do dicionário de dados.
- **`relacion_tabela(array $relacionamentos)`** — gera uma tabela HTML dos relacionamentos (FKs) de uma tabela.
- **`verCodigo($nomeTela)`** — exibe o código-fonte de uma tela (arquivo `.php`) numerado, usado provavelmente em uma tela interna de documentação/debug do próprio sistema.
- Esses três indicam que existe (ou deveria existir) uma **tela de documentação técnica automática** do próprio sistema, útil para o `bydoc` saber que já existe uma fonte "viva" de estrutura de banco, não precisa documentar schema manualmente do zero.

### Datas
- **`toDataBr(DateTime $data): string`**, **`data_br($data_bd)`**, **`data_db($data_br)`**, **`mongoDateToBr($utcDate)`**, **`dif_tempo($ini, $fim)`** — conversões padrão entre formato brasileiro (`dd/mm/yyyy`) e formato de banco/ISO/Mongo, e cálculo de diferença de tempo detalhado (anos/meses/dias/horas/minutos/segundos).
- **Regra:** sempre usar essas funções para exibir/converter datas — nunca formatar data manualmente com `date()`/`substr()` ad-hoc.

### Moeda e quantidade
- **`moedaToFloat($valor)`**, **`floatToMoeda($valor)`** (usa `NumberFormatter` pt-BR), **`floatToQuantia($valor, $decimais = 3)`** — conversão entre string formatada (BR) e float, incluindo formatação de quantidades com casas decimais variáveis (até 7).

### Texto e HTML
- **`url_amigavel($texto)`** — slugify (remove acentos, espaços viram `_`).
- **`formata_texto($texto)`** — remove tags HTML e decodifica entidades.
- **`montarLink($texto)`** — detecta URLs em texto livre e as transforma em `<a>` clicável, truncando links muito longos na exibição.
- **`get_string_between($str, $ini, $fim)`** — extrai substring entre dois delimitadores.
- **`debug($valor, $parada = false)`** — dump de debug visual em tela (substitui `var_dump`/`print_r` cru); se `$parada = true`, interrompe a execução (`exit`). **Nunca deve ir para produção** — o `byrev` deve sinalizar qualquer chamada a `debug()` esquecida no código como pendência de revisão.

### Cores e etiquetas visuais
- **`fmtEtiquetaCor($cor, $label, $tipo)`** / **`fmtEtiquetaCorBst($cor, $label, $tipo)`** — geram um "badge" HTML colorido (pill), aceitando tanto cor hexadecimal customizada (`#rrggbb`, com contraste de texto automático via `getContrastYIQ`) quanto classes utilitárias do Bootstrap (`bg-primary`, `bg-danger`, etc.). Essas são as funções por trás do `crSelectCor()`/`crCorbst()` da `MyCampo`.
- **Regra:** qualquer "etiqueta colorida" na interface (status, categoria, etc.) deve usar essas funções, não montar `<span>` com cor inline manualmente.

### Arquivos e uploads
- **`buscaTipoArquivo($dados)`** — retorna o ícone apropriado (PNG) conforme o mime-type do arquivo anexado (zip, xlsx, pdf, docx, etc.), ou a própria imagem se for `image/*`.
- **`buscaArquivos($pasta, $completo, $exceto)`** / **`recursivo(...)`** — varre recursivamente as pastas do projeto (lista restrita: Controllers, Models, Views, Libraries, Config, etc.) em busca de arquivos `.php`/`.js` — parece dar suporte a alguma tela de inventário/documentação do próprio código-fonte.

### Logs de auditoria
- **`buscaLog($tabela, $registro)`**, **`buscaLogTabela($tabela, $registros)`**, **`buscaLogTabelaFirst($tabela, $registros)`** — consultam o log de auditoria (armazenado via `LogMonModel`, aparentemente MongoDB pelos métodos `->document`) do(s) registro(s) de uma tabela, retornando quem alterou, quando e qual operação.
- **Regra:** telas que exibem "última alteração por/em" devem usar essas funções, não reimplementar consulta ao log.

### Select relacional automático — `criaSelectRelativo()`
Função importante: monta um campo `<select>` (simples, dependente ou múltiplo) **automaticamente a partir de uma tabela relacionada**, usando `MyCampo` por baixo dos panos. Resolve sozinho:
- Detecção do grupo/schema de banco correto pela tabela (`ConfigDicDadosModel::getDbGroupAndSchema`).
- Filtro automático por perfil do usuário logado, se a tabela tiver coluna `prf_id` (exceto em tabelas de configuração como `cfg_perfil`/`cfg_usuario`, ou campos somente-leitura).
- Filtro automático por "ativo", se existir uma coluna terminada em `_ativo`.
- Filtros adicionais arbitrários (`$filtros`), inclusive filtros do tipo `FIND_IN_SET` para campos multi-valor.
- Escolhe entre `crSelect()`, `crSelectCor()` (caso especial para a tabela `cfg_cor`), `crDepende()` ou `crMultiple()`/`crDependeMultiplo()` conforme o parâmetro `$tipo`.

**Regra para `bydev`:** ao precisar de um select que representa um relacionamento com outra tabela (FK), usar `criaSelectRelativo()` em vez de montar manualmente uma consulta + `MyCampo::crSelect()` — a função já resolve filtro por perfil, por ativo e pelo schema/grupo de banco corretos.

### Filtro de dados por perfil
- **`filtrarPorPerfil(array $dados, ?int $perfilId, ?string $campoprf = 'prf_id')`** — filtra um array de resultados já carregado (objetos ou arrays), mantendo só os itens cujo campo de perfil (que pode conter múltiplos IDs separados por vírgula) inclui o perfil do usuário. Útil quando o filtro não pôde ser feito via SQL diretamente.

## Observações para o `byarq`/`bydev`

- Antes de implementar qualquer conversão de data, moeda, texto, cor, ou consulta de log/permissão, **verificar se já existe um helper equivalente em `funcoes_helper.php`** — a convenção do projeto é centralizar essas utilidades ali, não duplicá-las em Controllers/Models.
- Toda tela nova precisa: (1) registro em `ConfigTelaModel`, (2) permissões em `ConfigPerfilItemModel` para os perfis relevantes, (3) métodos do Controller com os nomes padrão do fluxo (`index/lista/show/add/store/edit/update/delete/ordena/ativinativ`).
