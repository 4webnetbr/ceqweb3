# Referência interna — MyCampo (`app/Libraries/MyCampo.php`)

> Rascunho gerado por engenharia reversa do código-fonte. Revisar antes de formalizar como conhecimento fixo dos agentes.

## O que é

Biblioteca CodeIgniter 4 que gera campos de formulário HTML padronizados (input, select, checkbox, upload, etc.), com layout Bootstrap já embutido, integração com dicionário de dados (`ConfigDicDadosModel`) para autoconfiguração a partir do schema do banco, e diversas máscaras/validações de campo (CPF, CNPJ, telefone, CEP, moeda, etc.) já resolvidas via JS.

**Regra de ouro para os agentes:** sempre que uma tela precisar de um campo de formulário, usar `MyCampo` em vez de escrever `<input>`/`<select>` manual com Bootstrap puro — a lib já resolve layout, label, validação, máscara e integração com o dicionário de dados.

## Duas formas de uso

**1. Automático a partir do banco (preferencial)**
```php
$campo = new MyCampo('clientes', 'email');
echo $campo->crInput();
```
Ao informar `$tabela` e `$campo`, o construtor chama `doBanco()`, que busca o tipo da coluna no dicionário de dados e configura automaticamente: `label`, `hint`, `placeholder`, `tipo`, `size`, `maxLength`, `largura`, etc. Campos de chave primária (`COLUMN_KEY === 'PRI'`) viram campo oculto automaticamente, a menos que `$showchave = true` seja passado no construtor.

**2. Manual (sem banco)**
```php
$campo = new MyCampo();
$campo->objeto = 'input';
$campo->tipo   = 'text';
$campo->nome   = 'meu_campo';
echo $campo->crInput();
```
Usar quando o campo não corresponde a uma coluna real (campos calculados, filtros de tela, etc.).

Nos dois casos, os setters fluentes (`->setLabel()->setObrigatorio()->setValor(...)`) podem ser encadeados para sobrescrever o que veio do banco ou completar a configuração manual.

## Setters fluentes principais

Todos retornam `static`, permitindo encadeamento:

| Setter | Efeito |
|---|---|
| `setLabel(string)` | rótulo do campo |
| `setHint(string)` | tooltip |
| `setValor(mixed)` | valor atual |
| `setLeitura(bool = true)` | somente leitura (desativa edição e obrigatoriedade) |
| `setOpcoes(array)` | opções para select/radio/checkbox |
| `setSelecionado(mixed)` | item(ns) selecionado(s) |
| `setObrigatorio(bool = true)` | marca como obrigatório |
| `setDispForm(string)` | `'linha'` \| `'2col'` \| `'3col'` \| `'4col'` \| `'col-N'` (colunas Bootstrap) |
| `setTipo(string)` | tipo do campo (ver tabela de tipos abaixo) |
| `setUrlBusca(string)` | URL de busca AJAX (para `crSelbusca`/`crDepende`) |
| `setPai(string)` | nome do campo pai (para campos dependentes) |
| `setLargura(int)` | largura visual em `ch` |
| `setMinLength(int)` / `setMaxLength(int)` | limites de caracteres |
| `setColunas(int)` / `setLinhas(int)` | dimensões do textarea |
| `setCadModal(string)` | URL de modal de cadastro rápido vinculado ao campo |
| `setFunChan(string)` / `setFunBlur(string)` | JS para `onchange`/`onblur` |
| `setIcone(string)` | classe de ícone (botões) |
| `setPasta(string)` | pasta de armazenamento (upload de imagem) |
| `setTipoArq(string)` | extensões aceitas em upload de arquivo (ex: `.pdf,.docx`) |
| `setAttrData(array)` | atributos `data-*` extras (botões) |
| `setOrdem(int)` | índice em listas dinâmicas (afeta `name`/`id`, vira `campo[N]`) |

## Métodos de renderização (`cr*`) — qual usar em cada caso

| Método | Quando usar |
|---|---|
| `crInput()` | Campo principal. Decide sozinho se renderiza input, textarea (`objeto = 'texto'`) ou oculto (`objeto = 'oculto'`), e aplica máscara/validação conforme `$tipo` (ver tabela abaixo). **Ponto de entrada padrão para a maioria dos campos.** |
| `crTexto()` | Textarea simples, com contador de caracteres. |
| `crEditor()` | Textarea com editor de texto rico (classe `editor`). |
| `crDaterange()` | Campo de período (duas datas). |
| `crOculto()` | Campo hidden. |
| `crBotao()` | Botão de ação (usa `$i_cone` como conteúdo e `$funcChan` como `onclick`). |
| `crCheckbox()` | Checkbox estilo switch/toggle. |
| `crCheckbutton()` | Grupo de checkboxes com visual de botão (múltipla escolha). |
| `crRadio()` / `cr2opcoes()` (alias) | Grupo de radio buttons padrão. |
| `crRadiobutton()` | Grupo de radio com visual de botão (escolha única). |
| `crSelect()` | Select simples (Bootstrap Select / `selectpicker`). |
| `crMultiple()` | Select de múltipla escolha (checkboxes no dropdown). |
| `crSelectIcone()` | Select com ícone ao lado de cada opção. |
| `crCorbst()` | Select das cores padrão do Bootstrap (bg-primary, bg-danger, etc.). |
| `crSelectCor()` | Select de cores customizadas do sistema (opções com hex embutido no label). |
| `crSelbusca()` | Select com busca de opções via AJAX (`setUrlBusca()` obrigatório). |
| `crDepende()` | Select cujas opções dependem de outro campo (`setPai()` + `setUrlBusca()`). |
| `crDependeMultiplo()` | Igual ao anterior, mas com múltipla seleção. |
| `crDual()` | Dual listbox (mover itens entre duas listas). |
| `crImagem()` | Upload de imagem com preview inline. |
| `crArquivo()` | Upload de arquivo genérico (PDF, DOCX, etc. — default `.pdf,.docx`). |
| `crShow()` | Exibe o valor em modo somente-leitura estilizado (telas de visualização). |
| `crTextShow()` | Retorna só o valor puro, sem HTML (para uso fora do padrão de campo). |

## Tipos suportados por `crInput()` (propriedade `$tipo`)

`crInput()` aplica máscara, validação e ícone automaticamente conforme o valor de `$tipo`:

`text`, `color`, `number`, `moeda`, `date`, `datetime-local`, `senha`/`password`, `email`, `site`/`url`, `telefone`/`fone`, `celular`/`celul`/`whatsapp`/`whats`, `cnpj`, `cpf`, `cep`, `placaveiculo`, `ip`, `file`, `textselect`, `textselectoculto`, `calculo`.

Cada um já vem com: máscara JS (`mascara(this, '...')`), padrão de validação (`pattern`), ícone de apoio (quando aplicável) e mensagem de erro (`title`). **Nunca reimplementar essas máscaras/validações manualmente** — só definir `$tipo` (via `setTipo()` ou detecção automática pelo `doBanco()`).

## Autoconfiguração via banco (`doBanco()`)

Ao usar `new MyCampo($tabela, $campo)`, o tipo SQL da coluna é mapeado para uma categoria interna, que por sua vez configura tipo/tamanho/largura automaticamente:

| Tipo SQL | Categoria | Comportamento |
|---|---|---|
| `char`, `varchar` (≤100) | Caracter curto/longo | `input` texto, detecta `cep`/`fone`/`celular` pelo nome da coluna |
| `varchar` (>100) | Caracter longo | vira `textarea` (`objeto = 'texto'`) |
| `mediumtext`, `text` | Texto | `textarea` com editor (`classep = 'editor'`) |
| `int` | Inteiro | `input type="number"` |
| `decimal` | Decimal | tipo `quantia` |
| `float` | Moeda | tipo `moeda` |
| `date` | Data | `input type="date"` |
| `timestamp`, `datetime` | Data e Hora | `input type="datetime-local"` |

Placeholder também é gerado automaticamente: campos cujo nome contém `_id` recebem `"Selecione ..."`, os demais `"Informe ..."`.

## Layout e disposição (`dispForm` + `fmtDisplay()`)

Todo método `cr*()` (exceto `crTextShow()`) passa o HTML do campo por `fmtDisplay()`, que monta o wrapper completo: grid Bootstrap (conforme `dispForm`), label, mensagens de validação, texto informativo (`infotop`/`inforig`/`infotexto`) e botão de modal (`cadModal`), quando configurados.

`dispForm` aceita:
- `'linha'` → `col-12` (campo ocupa a linha toda)
- `'2col'` → `col-6`
- `'3col'` → `col-4`
- `'4col'` → `col-3`
- `'col-N'` (ex: `'col-5 col-lg-5'`) → usa a classe informada diretamente

## Campos em listas dinâmicas

Quando `setOrdem($indice)` é usado (índice ≥ 0), `acertaId()` transforma `name`/`id` em formato de array (`produto` → `produto[2]`), permitindo múltiplas linhas do mesmo campo em uma lista repetível (ex: itens de pedido).

## Observações para o `bydsgn`/`bydev`

- **Nunca** escrever `<input>`, `<select>`, `<textarea>` cru com classes Bootstrap manuais quando o campo corresponder a uma coluna real — usar `MyCampo` sempre.
- Campos somente leitura: usar `setLeitura(true)`, não adicionar `readonly`/`disabled` manualmente (a lib já cuida de desabilitar interação e obrigatoriedade).
- Máscaras (CPF, telefone, moeda, etc.): nunca reescrever em JS solto — usar `setTipo('cpf')`, `setTipo('moeda')`, etc.
- Campos dependentes (select que depende de outro): usar `crDepende()`/`crDependeMultiplo()` + `setPai()` + `setUrlBusca()`, não implementar o `onchange` manualmente.
