/*!
 * Biblioteca JavaScript jQuery v4.0.0
 * https://jquery.com/
 *
 * Direitos autorais da OpenJS Foundation e outros colaboradores
 * Licenciado sob a licença MIT
 * https://jquery.com/license/
 *
 * Data: 18/01/2026 às 00:20Z
 */
( função( global, fábrica ) {

	"usar estrito";

	se ( typeof module === "object" && typeof module.exports === "object" ) {

		// Para ambientes CommonJS e similares, onde uma `window` adequada
		// Se estiver presente, execute a fábrica e obtenha o jQuery.
		módulo.exports = fábrica( global, verdadeiro );
	} outro {
		fábrica( global );
	}

// Passe este valor se a janela ainda não estiver definida
} )( typeof window !== "undefined" ? window : this, function( window, noGlobal ) {

"usar estrito";

se ( !window.document ) {
	throw new Error( "jQuery requer uma janela com um documento" );
}

var arr = [];

var getProto = Object.getPrototypeOf;

var slice = arr.slice;

// Suporte: IE 11+
// O IE não possui Array#flat; forneça uma alternativa.
var flat = arr.flat ? function( array ) {
	retornar arr.flat.call( array );
} : função( array ) {
	retornar arr.concat.apply( [], array );
};

var push = arr.push;

var indexOf = arr.indexOf;

// [[Classe]] -> pares de tipo
var class2type = {};

var toString = class2type.toString;

var hasOwn = class2type.hasOwnProperty;

var fnToString = hasOwn.toString;

var ObjectFunctionString = fnToString.call( Object );

// Todos os testes de suporte estão definidos em seus respectivos módulos.
var suporte = {};

função toType( obj ) {
	se (obj == nulo) {
		retornar obj + "";
	}

	return typeof obj === "object" ?
		class2type[ toString.call( obj ) ] || "object" :
		tipo de objeto;
}

função isWindow( obj ) {
	retornar obj != null && obj === obj.window;
}

função isArrayLike( obj ) {

	var length = !!obj && obj.length,
		tipo = toType( obj );

	se ( typeof obj === "function" || isWindow( obj ) ) {
		retornar falso;
	}

	tipo de retorno === "array" || comprimento === 0 ||
		typeof length === "number" && length > 0 && ( length - 1 ) in obj;
}

var document$1 = window.document;

var preservadoScriptAttributes = {
	tipo: verdadeiro,
	src: verdadeiro,
	nonce: verdadeiro,
	noModule: true
};

função DOMEval( código, nó, doc ) {
	doc = doc || document$1;

	var i,
		script = doc.createElement( "script" );

	script.text = código;
	para ( i em preservedScriptAttributes ) {
		se ( nó && nó[ i ] ) {
			script[ i ] = nó[ i ];
		}
	}

	se (doc.head.appendChild(script).parentNode) {
		script.parentNode.removeChild( script );
	}
}

var versão = "4.0.0",

	rhtmlSuffix = /HTML$/i,

	// Define uma cópia local do jQuery
	jQuery = função( seletor, contexto ) {

		// O objeto jQuery é, na verdade, apenas o construtor de inicialização 'aprimorado'
		// Necessário inicializar se o jQuery for chamado (basta permitir que um erro seja lançado caso não esteja incluído)
		retornar novo jQuery.fn.init( seletor, contexto );
	};

jQuery.fn = jQuery.prototype = {

	// A versão atual do jQuery em uso
	jQuery: versão,

	construtor: jQuery,

	// O comprimento padrão de um objeto jQuery é 0
	comprimento: 0,

	toArray: função() {
		retornar slice.call( this );
	},

	// Obter o enésimo elemento no conjunto de elementos correspondentes OU
	// Obtenha todo o conjunto de elementos correspondentes como um array limpo
	obter: função( num ) {

		// Retorna todos os elementos em um array limpo
		se (num == nulo) {
			retornar slice.call( this );
		}

		// Retorna apenas um elemento do conjunto
		retornar num < 0 ? this[ num + this.length ] : this[ num ];
	},

	// Pegue um array de elementos e insira-o na pilha
	// (retornando o novo conjunto de elementos correspondentes)
	pushStack: função( elementos ) {

		// Criar um novo conjunto de elementos correspondentes do jQuery
		var ret = jQuery.merge( this.constructor(), elems );

		// Adiciona o objeto antigo à pilha (como referência)
		ret.prevObject = this;

		// Retorna o conjunto de elementos recém-formado
		retornar ret;
	},

	// Executa uma função de retorno de chamada para cada elemento no conjunto correspondente.
	cada: função( callback ) {
		return jQuery.each( this, callback );
	},

	mapa: função( callback ) {
		retornar this.pushStack( jQuery.map( this, function( elem, i ) {
			retornar callback.call( elem, i, elem );
		} ) );
	},

	fatiar: função() {
		retornar this.pushStack( slice.apply( this, arguments ) );
	},

	primeiro: função() {
		retornar this.eq( 0 );
	},

	último: função() {
		retornar this.eq( -1 );
	},

	mesmo: função() {
		retornar this.pushStack( jQuery.grep( this, function( _elem, i ) {
			retornar ( i + 1 ) % 2;
		} ) );
	},

	ímpar: função() {
		retornar this.pushStack( jQuery.grep( this, function( _elem, i ) {
			retornar i % 2;
		} ) );
	},

	eq: função( i ) {
		var len = this.length,
			j = +i + ( i < 0 ? len : 0 );
		retorne this.pushStack( j >= 0 && j < len ? [ this[ j ] ] : [] );
	},

	fim: função() {
		retornar this.prevObject || this.constructor();
	}
};

jQuery.extend = jQuery.fn.extend = function() {
	var opções, nome, origem, copiar, copiarÉArray, clonar,
		alvo = argumentos[ 0 ] || {},
		i = 1,
		comprimento = argumentos.comprimento,
		profundo = falso;

	// Lidar com uma situação de cópia profunda
	se ( typeof target === "boolean" ) {
		profundo = alvo;

		// Ignore o booleano e o alvo
		alvo = argumentos[ i ] || {};
		i++;
	}

	// Tratar o caso em que o destino é uma string ou algo semelhante (possível em uma cópia profunda)
	se ( typeof target !== "objeto" && typeof target !== "função" ) {
		alvo = {};
	}

	// Estenda o próprio jQuery se apenas um argumento for passado
	se ( i === comprimento ) {
		alvo = isto;
		eu--;
	}

	para ( ; i < comprimento; i++ ) {

		// Lidar apenas com valores não nulos/indefinidos
		se ( ( opções = argumentos[ i ] ) != nulo ) {

			// Estenda o objeto base
			para (nome em opções) {
				copiar = opções[ nome ];

				// Evitar poluição de Object.prototype
				// Evitar loop infinito
				se (nome === "__proto__" || alvo === cópia) {
					continuar;
				}

				// Recursão se estivermos mesclando objetos simples ou arrays
				se ( profundo && cópia && ( jQuery.isPlainObject( cópia ) ||
					( copyIsArray = Array.isArray( copy ) ) ) ) {
					src = alvo[ nome ];

					// Garanta o tipo correto para o valor de origem
					se (copyIsArray && !Array.isArray(src)) {
						clone = [];
					} else if ( !copyIsArray && !jQuery.isPlainObject( src ) ) {
						clone = {};
					} outro {
						clone = src;
					}
					copyIsArray = falso;

					// Nunca mova os objetos originais, clone-os.
					alvo[ nome ] = jQuery.extend( deep, clone, copy );

				// Não inclua valores indefinidos
				} senão se ( cópia !== indefinido ) {
					alvo[ nome ] = cópia;
				}
			}
		}
	}

	// Retorna o objeto modificado
	retornar alvo;
};

jQuery.extend( {

	// Único para cada cópia do jQuery na página
	expando: "jQuery" + ( versão + Math.random() ).replace( /\D/g, "" ),

	// Assume que o jQuery está pronto sem o módulo ready
	estáPronto: verdadeiro,

	erro: função( msg ) {
		lançar novo Erro( msg );
	},

	noop: função() {},

	isPlainObject: função( obj ) {
		var proto, Ctor;

		// Detectar negativos óbvios
		// Use toString em vez de jQuery.type para capturar objetos do host
		if ( !obj || toString.call( obj ) !== "[object Object]" ) {
			retornar falso;
		}

		proto = getProto( obj );

		// Objetos sem protótipo (por exemplo, `Object.create(null)`) são simples
		se ( !proto ) {
			retornar verdadeiro;
		}

		// Objetos com protótipo são simples se e somente se foram construídos por uma função de objeto global
		Ctor = hasOwn.call( proto, "constructor" ) && proto.constructor;
		return typeof Ctor === "function" && fnToString.call( Ctor ) === ObjectFunctionString;
	},

	isEmptyObject: função( obj ) {
		nome da variável;

		para (nome em obj) {
			retornar falso;
		}
		retornar verdadeiro;
	},

	// Avalia um script em um contexto fornecido; caso contrário, utiliza o contexto global.
	// se não for especificado.
	globalEval: função( código, opções, doc ) {
		DOMEval( código, { nonce: opções && opções.nonce }, doc );
	},

	cada: função( obj, callback ) {
		var comprimento, i = 0;

		se ( isArrayLike( obj ) ) {
			comprimento = obj.comprimento;
			para ( ; i < comprimento; i++ ) {
				se ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					quebrar;
				}
			}
		} outro {
			para ( i em obj ) {
				se ( callback.call( obj[ i ], i, obj[ i ] ) === false ) {
					quebrar;
				}
			}
		}

		retornar obj;
	},


	// Recupera o valor de texto de uma matriz de nós DOM
	texto: função( elem ) {
		var nó,
			ret = "",
			i = 0,
			nodeType = elem.nodeType;

		se ( !tipo de nó ) {

			// Se não houver nodeType, espera-se que seja um array
			enquanto ( ( nó = elem[ i++ ] ) ) {

				// Não percorra nós de comentário
				ret += jQuery.text( node );
			}
		}
		se ( nodeType === 1 || nodeType === 11 ) {
			retornar elem.textConteúdo;
		}
		se ( nodeType === 9 ) {
			retornar elem.documentElement.textContent;
		}
		se ( nodeType === 3 || nodeType === 4 ) {
			retornar elem.nodeValue;
		}

		// Não inclua nós de comentários ou instruções de processamento

		retornar ret;
	},


	// Os resultados são apenas para uso interno
	makeArray: função( arr, resultados ) {
		var ret = resultados || [];

		se (arr != nulo) {
			if (isArrayLike(Object(arr))) {
				jQuery.merge( ret,
					typeof arr === "string" ?
						[ arr ] : arr
				);
			} outro {
				push.call( ret, arr );
			}
		}

		retornar ret;
	},

	inArray: função( elem, arr, i ) {
		return arr == null ? -1 : indexOf.call( arr, elem, i );
	},

	isXMLDoc: função( elem ) {
		var namespace = elem && elem.namespaceURI,
			docElem = elem && ( elem.ownerDocument || elem ).documentElement;

		// Assume HTML quando o documentElement ainda não existe, como dentro de
		// fragmentos de documentos.
		retornar !rhtmlSuffix.test( namespace || docElem && docElem.nodeName || "HTML" );
	},

	// Observação: um elemento não contém a si mesmo
	contém: função( a, b ) {
		var bup = b && b.parentNode;

		retornar um === bup || !!( bup && bup.nodeType === 1 && (

			// Compatível com: IE 9 - 11+
			// O IE não possui o atributo `contains` em SVG.
			a.contém?
				a.contém( bup ) :
				a.compareDocumentPosition && a.compareDocumentPosition( bup ) & 16
		) );
	},

	mesclar: função( primeiro, segundo ) {
		var len = +second.length,
			j = 0,
			i = primeiro.comprimento;

		para ( ; j < len; j++ ) {
			primeiro[ i++ ] = segundo[ j ];
		}

		primeiro.comprimento = i;

		retornar primeiro;
	},

	grep: função( elementos, callback, inverter ) {
		var callbackInverse,
			correspondências = [],
			i = 0,
			comprimento = elems.length,
			callbackExpect = !invert;

		// Percorra o array, salvando apenas os itens
		// que passam pela função de validação
		para ( ; i < comprimento; i++ ) {
			callbackInverse = !callback( elems[ i ], i );
			se ( callbackInverse !== callbackExpect ) {
				matches.push(elems[i]);
			}
		}

		retornar correspondências;
	},

	// O argumento é apenas para uso interno
	mapa: função( elementos, callback, arg ) {
		comprimento da variável, valor,
			i = 0,
			ret = [];

		// Percorra a matriz, traduzindo cada um dos itens para seus novos valores
		se ( isArrayLike( elementos ) ) {
			comprimento = elems.comprimento;
			para ( ; i < comprimento; i++ ) {
				valor = callback( elementos[ i ], i, arg );

				se (valor != nulo) {
					ret.push( valor );
				}
			}

		// Percorra todas as chaves do objeto,
		} outro {
			para ( i em elems ) {
				valor = callback( elementos[ i ], i, arg );

				se (valor != nulo) {
					ret.push( valor );
				}
			}
		}

		// Achatar quaisquer arrays aninhados
		retornar plano( ret );
	},

	// Um ​​contador GUID global para objetos
	guia: 1,

	// jQuery.support não é usado no Core, mas outros projetos incluem suas próprias dependências.
	// possui propriedades que o tornam necessário para que ele exista.
	suporte: suporte
} );

se ( typeof Symbol === "function" ) {
	jQuery.fn[ Symbol.iterator ] = arr[ Symbol.iterator ];
}

// Preencher o mapa class2type
jQuery.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),
	função( _i, nome ) {
		class2type[ "[object " + name + "]" ] = name.toLowerCase();
	} );

função nodeName(elem, nome) {
	retornar elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
}

var pop = arr.pop;

// https://www.w3.org/TR/css3-selectors/#whitespace
var whitespace = "[\\x20\\t\\r\\n\\f]";

var isIE = documento$1.documentMode;

var rbuggyQSA = isIE && new RegExp(

	// Compatível com: IE 9 - 11+
	// O seletor :disabled do IE não identifica os filhos de conjuntos de campos desabilitados
	":ativado|:desativado|" +

	// Suporte: IE 11+
	// O IE 11 não encontra elementos em uma consulta `[name='']` em alguns casos.
	// Adicionar um atributo temporário ao documento antes da seleção funciona
	// em torno do problema.
	"\\[" + espaço em branco + "*nome" + espaço em branco + "*=" +
	espaço em branco + "*(?:''|\"\")"

);

var rtrimCSS = new RegExp(
	"^" + espaço em branco + "+|((?:^|[^\\\\])(?:\\\\.)*)" + espaço em branco + "+$",
	"g"
);

// https://www.w3.org/TR/css-syntax-3/#ident-token-diagram
var identifier = "(?:\\\\[\\da-fA-F]{1,6}" + whitespace +
	"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+";

var rleadingCombinator = new RegExp( "^" + whitespace + "*([>+~]|" +
	espaço em branco + ")" + espaço em branco + "*" );

var rdescend = new RegExp( whitespace + "|>" );

var rsibling = /[+~]/;

var documentElement$1 = document$1.documentElement;

// Compatível com: IE 9 - 11+
// O IE requer um prefixo.
var correspondências = documentElement$1.matches || documentElement$1.msMatchesSelector;

/**
 * Criar caches de chave-valor de tamanho limitado
 * @returns {function(string, object)} Retorna os dados do objeto após armazená-los nele mesmo com
 * nome da propriedade a string (com sufixo de espaço) e (se o cache for maior que Expr.cacheLength)
 * excluindo a entrada mais antiga
 */
função criarCache() {
	var keys = [];

	função cache( chave, valor ) {

		// Use (tecla + " ") para evitar conflito com propriedades de protótipo nativas
		// (ver https://github.com/jquery/sizzle/issues/157)
		if ( keys.push( key + " " ) > jQuery.expr.cacheLength ) {

			// Manter apenas as entradas mais recentes
			excluir cache[ keys.shift() ];
		}
		retornar ( cache[ chave + " " ] = valor );
	}
	retornar cache;
}

/**
 * Verifica a validade de um nó como um contexto de seletor jQuery
 * @param {Element|Object=} contexto
 * @returns {Element|Object|Boolean} O nó de entrada, se aceitável; caso contrário, um valor falso.
 */
função testContext( contexto ) {
	retornar contexto && typeof contexto.getElementsByTagName !== "undefined" && contexto;
}

// Seletores de atributos: https://www.w3.org/TR/selectors/#attribute-selectors
var atributos = "\\[" + espaço em branco + "*(" + identificador + ")(?:" + espaço em branco +

	// Operador (captura 2)
	"*([*^$|!~]?=)" + espaço em branco +

	// "Os valores dos atributos devem ser identificadores CSS [captura 5] ou strings [captura 3 ou captura 4]"
	"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + identificador + "))|)" +
	espaço em branco + "*\\]";

var pseudos = ":(" + identificador + ")(?:\\((" +

	// Para reduzir o número de seletores que precisam ser tokenizados no preFilter, prefira os argumentos:
	// 1. citado (captura 3; captura 4 ou captura 5)
	"('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\\"])*)\")|" +

	// 2. simples (captura 6)
	"((?:\\\\.|[^\\\\()[\\]]|" + atributos + ")*)|" +

	// 3. qualquer outra coisa (captura 2)
	".*" +
	")\\)|)";

var filterMatchExpr = {
	ID: nova RegExp( "^#(" + identificador + ")" ),
	CLASSE: nova RegExp( "^\\.(" + identificador + ")" ),
	TAG: new RegExp( "^(" + identificador + "|[*])" ),
	ATTR: nova RegExp( "^" + atributos ),
	PSEUDO: novo RegExp( "^" + pseudos ),
	FILHO: nova RegExp(
		"^:(apenas|primeiro|último|enésimo|enésimo-último)-(filho|do-tipo)(?:\\(" +
		espaço em branco + "*(par|ímpar|(([+-]|)(\\d*)n|)" + espaço em branco + "*(?:([+-]|)" +
		espaço em branco + "*(\\d+)|))" + espaço em branco + "*\\)|)", "i" )
};

var rpseudo = new RegExp( pseudos );

// Escapes CSS
// https://www.w3.org/TR/CSS21/syndata.html#escaped-characters

var runescape = new RegExp( "\\\\[\\da-fA-F]{1,6}" + whitespace +
	"?|\\\\([^\\r\\n\\f])", "g" ),
	funescape = função( escape, nãoHex ) {
		var high = "0x" + escape.slice( 1 ) - 0x10000;

		se (nãoHex) {

			// Remove o prefixo de barra invertida de uma sequência de escape não hexadecimal
			retornar não hexadecimal;
		}

		// Substitui uma sequência de escape hexadecimal pelo ponto de código Unicode codificado
		// Suporte: IE <= 11+
		// Para valores fora do Plano Multilíngue Básico (BMP), construa manualmente um
		// par substituto
		retornar alto < 0?
			String.fromCharCode( high + 0x10000 ) :
			String.fromCharCode( high >> 10 | 0xD800, high & 0x3FF | 0xDC00 );
	};

função unescapeSelector( sel ) {
	retornar sel.replace( runescape, funescape );
}

função selectorError( msg ) {
	jQuery.error("Erro de sintaxe, expressão não reconhecida: " + msg);
}

var rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" );

var tokenCache = createCache();

função tokenize( seletor, parseOnly ) {
	var correspondente, correspondência, tokens, tipo,
		até agora, grupos, pré-filtros,
		cached = tokenCache[ seletor + " " ];

	se (em cache) {
		retornar parseOnly ? 0 : cached.slice( 0 );
	}

	soFar = seletor;
	grupos = [];
	preFilters = jQuery.expr.preFilter;

	enquanto (atéAgora) {

		// Vírgula e primeira execução
		se ( !correspondência || ( correspondência = rcomma.exec( até agora ) ) ) {
			se (correspondência) {

				// Não considere vírgulas finais como válidas
				soFar = soFar.slice( match[ 0 ].length ) || soFar;
			}
			grupos.push( ( tokens = [] ) );
		}

		correspondente = falso;

		// Combinadores
		se ( ( match = rleadingCombinator.exec( soFar ) ) ) {
			correspondente = correspondência.deslocamento();
			tokens.push( {
				valor: correspondente,

				// Lançar combinadores descendentes para o espaço
				tipo: correspondência[ 0 ].replace( rtrimCSS, " " )
			} );
			soFar = soFar.slice(matched.length);
		}

		// Filtros
		para ( tipo em filterMatchExpr ) {
			if ( ( match = jQuery.expr.match[ type ].exec( soFar ) ) && ( !preFilters[ type ] ||
				( match = preFilters[ type ]( match ) ) ) ) {
				correspondente = correspondência.deslocamento();
				tokens.push( {
					valor: correspondente,
					tipo: tipo,
					partidas: partida
				} );
				soFar = soFar.slice(matched.length);
			}
		}

		se ( !correspondeu ) {
			quebrar;
		}
	}

	// Retorna o comprimento do excesso inválido
	// se estivermos apenas analisando
	Caso contrário, lance um erro ou retorne os tokens.
	se (parseOnly) {
		retornar soFar.length;
	}

	retorno até agora?
		selectorError( selector ) :

		// Armazene os tokens em cache
		tokenCache( seletor, grupos ).slice( 0 );
}

var preFilter = {
	ATTR: função( correspondência ) {
		match[ 1 ] = unescapeSelector( match[ 1 ] );

		// Move o valor fornecido para match[3], esteja ele entre aspas ou não.
		match[ 3 ] = unescapeSelector( match[ 3 ] || match[ 4 ] || match[ 5 ] || "" );

		se (match[2] === "~=") {
			match[ 3 ] = " " + match[ 3 ] + " ";
		}

		retornar match.slice( 0, 4 );
	},

	FILHO: função( correspondência ) {

		/* correspondências de filterMatchExpr["CHILD"]
			1 tipo (apenas|nº|...)
			2 o que (filho|de tipo)
			3 argumentos (par|ímpar|\d*|\d*n([+-]\d+)?|...)
			4 componente xn do argumento xn+y ([+-]?\d*n|)
			5. Sinal do componente xn
			6 x do componente xn
			7. Sinal do componente y
			8 y do componente y
		*/
		match[ 1 ] = match[ 1 ].toLowerCase();

		se (match[1].slice(0, 3) === "nth") {

			// nth-* requer argumento
			se ( !match[ 3 ] ) {
				selectorError( match[ 0 ] );
			}

			// Parâmetros numéricos x e y para jQuery.expr.filter.CHILD
			// Lembre-se que falso/verdadeiro são convertidos respectivamente para 0/1
			match[ 4 ] = +( match[ 4 ] ?
				correspondência[ 5 ] + ( correspondência[ 6 ] || 1 ) :
				2 * ( match[ 3 ] === "par" || match[ 3 ] === "ímpar" )
			);
			match[ 5 ] = +( ( match[ 7 ] + match[ 8 ] ) || match[ 3 ] === "ímpar" );

		// outros tipos proíbem argumentos
		} senão se ( correspondência[ 3 ] ) {
			selectorError( match[ 0 ] );
		}

		retornar correspondência;
	},

	PSEUDO: função( correspondência ) {
		excesso de var,
			sem aspas = !match[ 6 ] && match[ 2 ];

		if ( filterMatchExpr.CHILD.test( match[ 0 ] ) ) {
			retornar nulo;
		}

		// Aceitar argumentos entre aspas tal como estão
		se ( correspondência[ 3 ] ) {
			correspondência[ 2 ] = correspondência[ 4 ] || correspondência[ 5 ] || "";

		// Remove caracteres em excesso de argumentos não entre aspas
		} else if ( unquoted && rpseudo.test( unquoted ) &&

			// Obter excesso de tokenização (recursivamente)
			(excesso = tokenize(unquoted, true)) &&

			// avançar para o próximo parêntese de fechamento
			(excesso = unquoted.indexOf(""), unquoted.length - excesso) -
				unquoted.length ) ) {

			// excesso é um índice negativo
			match[ 0 ] = match[ 0 ].slice( 0, excess );
			match[ 2 ] = unquoted.slice( 0, excess );
		}

		// Retorna apenas as capturas necessárias para o método de pseudofiltro (tipo e argumento)
		retornar match.slice( 0, 3 );
	}
};

função toSelector( tokens ) {
	var i = 0,
		len = tokens.length,
		seletor = "";
	para ( ; i < len; i++ ) {
		seletor += tokens[ i ].valor;
	}
	retornar seletor;
}

// Método multifuncional para obter e definir valores de uma coleção
// O(s) valor(es) pode(m) ser executado(s) opcionalmente se for(em) uma função
função acesso( elementos, fn, chave, valor, encadeável, obterVazio, bruto ) {
	var i = 0,
		len = elems.length,
		massa = chave == nula;

	// Define vários valores
	se ( toType( key ) === "object" ) {
		encadeável = verdadeiro;
		para ( i em chave ) {
			acesso( elementos, fn, i, chave[ i ], verdadeiro, emptyGet, raw );
		}

	// Define um valor
	} senão se ( valor !== indefinido ) {
		encadeável = verdadeiro;

		se ( typeof valor !== "função" ) {
			bruto = verdadeiro;
		}

		se (em massa) {

			// Operações em lote são executadas em todo o conjunto
			se (bruto) {
				fn.call(elems, value);
				fn = nulo;

			// ...exceto ao executar valores de função
			} outro {
				volume = fn;
				fn = função( elem, _key, value ) {
					retornar bulk.call( jQuery( elem ), valor );
				};
			}
		}

		se ( fn ) {
			para ( ; i < len; i++ ) {
				fn(
					elems[ i ], chave, bruto?
						valor :
						valor.call(elems[i], i, fn(elems[i], key))
				);
			}
		}
	}

	se (encadeável) {
		retornar elementos;
	}

	// Obtém
	se (em massa) {
		retornar fn.call(elems);
	}

	retornar len ? fn( elems[ 0 ], key ) : emptyGet;
}

// Contabilizar apenas espaços em branco HTML
// Outros espaços em branco devem ser contabilizados nos valores
// https://infra.spec.whatwg.org/#ascii-whitespace
var rnothtmlbranco = /[^\x20\t\r\n\f]+/g;

jQuery.fn.extend( {
	attr: função( nome, valor ) {
		retornar acesso( this, jQuery.attr, nome, valor, arguments.length > 1 );
	},

	removerAttr: função( nome ) {
		retorne this.each( function() {
			jQuery.removeAttr( this, nome );
		} );
	}
} );

jQuery.extend( {
	attr: função( elemento, nome, valor ) {
		var ret, anzóis,
			nType = elem.nodeType;

		// Não obtenha/defina atributos em nós de texto, comentário e atributo
		se ( nType === 3 || nType === 8 || nType === 2 ) {
			retornar;
		}

		// Recorrer à propriedade quando os atributos não forem suportados
		se ( typeof elem.getAttribute === "undefined" ) {
			retornar jQuery.prop( elemento, nome, valor );
		}

		// Os ganchos de atributo são determinados pela versão em minúsculas
		// Captura o gancho necessário, se houver um definido.
		se ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {
			hooks = jQuery.attrHooks[ name.toLowerCase() ];
		}

		se (valor !== indefinido) {
			se ( valor === nulo ||

				// Para compatibilidade com o tratamento anterior de atributos booleanos,
				// Remover quando `false` for passado. Para atributos ARIA -
				// muitos dos quais reconhecem um valor "falso" - continue para
				// Defina o valor "false" como o jQuery <4 fazia.
				( valor === falso && nome.paraLowerCase().indexOf( "aria-" ) !== 0 ) ) {

				jQuery.removeAttr(elem, name);
				retornar;
			}

			se ( ganchos && "definido" em ganchos &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				retornar ret;
			}

			elem.setAttribute( nome, valor );
			valor de retorno;
		}

		se ( hooks && "get" em hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			retornar ret;
		}

		ret = elem.getAttribute(name);

		// Atributos inexistentes retornam nulo, normalizamos para indefinido
		retornar ret == null ? undefined : ret;
	},

	attrHooks: {},

	removeAttr: função( elem, valor ) {
		nome da variável,
			i = 0,

			// Os nomes dos atributos podem conter caracteres de espaço em branco que não sejam HTML.
			// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
			attrNames = value && value.match( rnothtmlwhite );

		se ( attrNames && elem.nodeType === 1 ) {
			enquanto ( ( nome = attrNames[ i++ ] ) ) {
				elem.removeAttribute( nome );
			}
		}
	}
} );

// Suporte: IE <= 11+
// Um ​​input perde seu valor após se tornar um rádio
se ( isIE ) {
	jQuery.attrHooks.type = {
		definir: função( elemento, valor ) {
			se ( valor === "radio" && nodeName( elem, "input" ) ) {
				var val = elem.value;
				elem.setAttribute("type", value);
				se (val) {
					elem.value = val;
				}
				valor de retorno;
			}
		}
	};
}

// Serialização de strings/identificadores CSS
// https://drafts.csswg.org/cssom/#common-serializing-idioms
var rcssescape = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;

função fcssescape( ch, asCodePoint ) {
	se (comoPontoDeCódigo) {

		// U+0000 NULL torna-se U+FFFD CARACTER DE SUBSTITUIÇÃO
		se ( ch === "\0" ) {
			retornar "\uFFFD";
		}

		// Os caracteres de controle e (dependendo da posição) os números são escapados como pontos de código.
		return ch.slice( 0, -1 ) + "\\" + ch.charCodeAt( ch.length - 1 ).toString( 16 ) + " ";
	}

	// Outros caracteres ASCII potencialmente especiais são escapados com barra invertida
	retornar "\\" + ch;
}

jQuery.escapeSelector = function( sel ) {
	retornar ( sel + "" ).replace( rcssescape, fcssescape );
};

var sort = arr.sort;

var splice = arr.splice;

var hasDuplicate;

// Ordenação de documentos
função sortOrder( a, b ) {

	// Sinalizador para remoção de duplicados
	se ( a === b ) {
		hasDuplicate = true;
		retornar 0;
	}

	// Classificar pela existência do método se apenas uma entrada tiver compareDocumentPosition
	var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
	se (comparar) {
		retornar comparação;
	}

	// Calcula a posição se ambas as entradas pertencerem ao mesmo documento
	// Suporte: IE 11+
	// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
	// Dois documentos; comparações superficiais funcionam.
	// eslint-disable-next-line eqeqeq
	compare = ( a.ownerDocument || a ) == ( b.ownerDocument || b ) ?
		a.compareDocumentPosition( b ) :

		Caso contrário, sabemos que estão desconectados.
		1;

	// Nós desconectados
	se (comparar & 1) {

		// Escolha o primeiro elemento relacionado ao documento
		// Suporte: IE 11+
		// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
		// Dois documentos; comparações superficiais funcionam.
		// eslint-disable-next-line eqeqeq
		se ( a == document$1 || a.ownerDocument == document$1 &&
			jQuery.contains( document$1, a ) ) {
			retornar -1;
		}

		// Suporte: IE 11+
		// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
		// Dois documentos; comparações superficiais funcionam.
		// eslint-disable-next-line eqeqeq
		se (b == document$1 || b.ownerDocument == document$1 &&
			jQuery.contains( document$1, b ) ) {
			retornar 1;
		}

		// Manter a ordem original
		retornar 0;
	}

	retornar compare & 4 ? -1 : 1;
}

/**
 * Classificação de documentos e remoção de duplicados
 * @param {ArrayLike} resultados
 */
jQuery.uniqueSort = function( resultados ) {
	var elem,
		duplicados = [],
		j = 0,
		i = 0;

	hasDuplicate = false;

	sort.call(resultados, ordemDeClassificação);

	se (possui duplicado) {
		enquanto ( ( elem = resultados[ i++ ] ) ) {
			se (elem === results[i]) {
				j = duplicados.push( i );
			}
		}
		enquanto ( j-- ) {
			splice.call(resultados, duplicados[j], 1);
		}
	}

	retornar resultados;
};

jQuery.fn.uniqueSort = function() {
	return this.pushStack( jQuery.uniqueSort( slice.apply( this ) ) );
};

var i,
	contexto mais externo,

	// Variáveis ​​locais do documento
	documento,
	elementoDoDocumento,
	documentoÉHTML,

	// Dados específicos da instância
	dirruns = 0,
	feito = 0,
	classCache = criarCache(),
	compilerCache = createCache(),
	nonnativeSelectorCache = createCache(),

	// Expressões regulares

	// Espaços em branco iniciais e finais não escapados, capturando alguns caracteres que não sejam espaços em branco antes do último
	rwhitespace = new RegExp( whitespace + "+", "g") ),

	identificador = novo RegExp( "^" + identificador + "$" ),

	matchExpr = jQuery.extend( {

		// Para uso em bibliotecas que implementam .is()
		// Usamos isso para correspondência de POS em `select`
		precisaContexto: nova RegExp( "^" + espaço em branco +
			"*[>+~]|:(par|ímpar|igual|maior|menor|enésimo|primeiro|último)(?:\\(" + espaço em branco +
			"*((?:-\\d)?\\d*)" + espaço em branco + "*\\)|)(?=[^-]|$)", "i" )
	}, filterMatchExpr ),

	rinputs = /^(?:input|select|textarea|button)$/i,
	rheader = /^h\d$/i,

	// Seletores de ID, TAG ou CLASS facilmente analisáveis/recuperáveis
	rquickExpr$1 = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,

	// Usado para iframes; veja `setDocument`.
	// Compatível com: IE 9 - 11+
	// Remover o encapsulador da função causa uma mensagem de "Permissão negada"
	// erro no IE.
	unloadHandler = função() {
		definirDocumento();
	},

	inDisabledFieldset = addCombinator(
		função( elem ) {
			retornar elem.disabled === true && nodeName( elem, "fieldset" );
		},
		{ dir: "parentNode", próximo: "legenda" }
	);

função encontrar( seletor, contexto, resultados, semente ) {
	var m, i, elem, nid, match, groups, newSelector,
		novoContexto = contexto && contexto.documentoProprietário,

		// O tipo de nó (nodeType) tem como padrão o valor 9, já que o contexto tem como padrão o documento (document).
		nodeType = context ? context.nodeType : 9;

	resultados = resultados || [];

	// Retorna antecipadamente em chamadas com seletor ou contexto inválidos
	se ( typeof selector !== "string" || !selector ||
		nodeType !== 1 && nodeType !== 9 && nodeType !== 11 ) {

		retornar resultados;
	}

	// Tente simplificar as operações de busca (em oposição aos filtros) em documentos HTML
	se ( !semente ) {
		definirDocumento(contexto);
		contexto = contexto || documento;

		se (documentoIsHTML) {

			// Se o seletor for suficientemente simples, tente usar um método DOM "get*By*".
			// (exceto no contexto DocumentFragment, onde os métodos não existem)
			se ( nodeType !== 11 && ( match = rquickExpr$1.exec( selector ) ) ) {

				// Seletor de ID
				se ( ( m = match[ 1 ] ) ) {

					// Contexto do documento
					se ( nodeType === 9 ) {
						se ( ( elem = context.getElementById( m ) ) ) {
							push.call(resultados, elem);
						}
						retornar resultados;

					// Contexto do elemento
					} outro {
						if (newContext && (elem = newContext.getElementById(m)) &&
							jQuery.contains( context, elem ) ) {

							push.call(resultados, elem);
							retornar resultados;
						}
					}

				// Seletor de tipo
				} senão se ( correspondência[ 2 ] ) {
					push.apply(resultados, context.getElementsByTagName(selector));
					retornar resultados;

				// Seletor de classe
				} else if ( ( m = match[ 3 ] ) && context.getElementsByClassName ) {
					push.apply(resultados, context.getElementsByClassName(m));
					retornar resultados;
				}
			}

			// Aproveite as vantagens do querySelectorAll
			se ( !nonnativeSelectorCache[ seletor + " " ] &&
				( !rbuggyQSA || !rbuggyQSA.test( selector ) ) ) {

				novoSeletor = seletor;
				novoContexto = contexto;

				// O qSA considera elementos fora de um escopo raiz ao avaliar elementos filhos ou
				// Combinadores descendentes, o que não é o que queremos.
				// Nesses casos, contornamos o comportamento prefixando cada seletor no
				// lista com um seletor de ID que referencia o contexto do escopo.
				// A técnica também deve ser usada quando um combinador principal for utilizado.
				// pois esses seletores não são reconhecidos pelo querySelectorAll.
				// Agradeço a Andrew Dupont por esta técnica.
				se ( nodeType === 1 &&
					( rdescend.test( selector ) || rleadingCombinator.test( selector ) ) ) {

					// Expandir contexto para seletores irmãos
					novoContext = rsibling.test(seletor) &&
						testContext( context.parentNode ) ||
						contexto;

					// Fora do IE, se não estivermos alterando o contexto, podemos
					// Use :scope em vez de um ID.
					// Suporte: IE 11+
					// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
					// Dois documentos; comparações superficiais funcionam.
					// eslint-disable-next-line eqeqeq
					se (novoContexto != contexto || éIE) {

						// Captura o ID do contexto, definindo-o primeiro, se necessário.
						se ( ( nid = context.getAttribute( "id" ) ) ) {
							nid = jQuery.escapeSelector(nid);
						} outro {
							context.setAttribute("id", ( nid = jQuery.expando ) );
						}
					}

					// Prefixar todos os seletores da lista
					grupos = tokenizar( seletor );
					i = grupos.comprimento;
					enquanto ( i-- ) {
						grupos[ i ] = ( nid ? "#" + nid : ":scope" ) + " " +
							toSelector( grupos[ i ] );
					}
					novoSeletor = grupos.join( "," );
				}

				tentar {
					push.apply(resultados,
						newContext.querySelectorAll(newSelector)
					);
					retornar resultados;
				} catch ( qsaError ) {
					nonnativeSelectorCache( selector, true );
				} finalmente {
					se ( nid === jQuery.expando ) {
						context.removeAttribute("id");
					}
				}
			}
		}
	}

	// Todos os outros
	retornar select( selector.replace( rtrimCSS, "$1" ), context, results, seed );
}

/**
 * Marque uma função para uso especial pelo módulo seletor jQuery
 * @param {Function} fn A função a ser marcada
 */
função markFunction( fn ) {
	fn[ jQuery.expando ] = true;
	retornar fn;
}

/**
 * Retorna uma função para ser usada em pseudo-funções para tipos de entrada.
 * @param {String} tipo
 */
função criarPseudoDeEntrada( tipo ) {
	função de retorno (elemento) {
		retornar nodeName( elem, "input" ) && elem.type === type;
	};
}

/**
 * Retorna uma função para ser usada em pseudo-funções para botões
 * @param {String} tipo
 */
função criarPseudoBotão( tipo ) {
	função de retorno (elemento) {
		retornar ( nodeName( elem, "input" ) || nodeName( elem, "button" ) ) &&
			elem.type === tipo;
	};
}

/**
 * Retorna uma função para ser usada em pseudo-funções para :enabled/:disabled
 * @param {Boolean} disabled true para :disabled; false para :enabled
 */
função criarPseudoDesativado( desativado ) {

	// Falsos positivos conhecidos de :disabled: fieldset[disabled] > legend:nth-of-type(n+2) :can-disable
	função de retorno (elemento) {

		// Somente determinados elementos podem corresponder a :enabled ou :disabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-enabled
		// https://html.spec.whatwg.org/multipage/scripting.html#selector-disabled
		se ( "formulário" em elem ) {

			// Verificar se os elementos relevantes não desabilitados herdaram características de desabilitação:
			// * elementos associados ao formulário listados em um conjunto de campos desabilitado
			// https://html.spec.whatwg.org/multipage/forms.html#category-listed
			// https://html.spec.whatwg.org/multipage/forms.html#concept-fe-disabled
			// * elementos de opção em um grupo de opções desabilitado
			// https://html.spec.whatwg.org/multipage/forms.html#concept-option-disabled
			// Todos esses elementos possuem uma propriedade "form".
			if (elem.parentNode && elem.disabled === falso) {

				// Os elementos de opção adiam a escolha para um grupo de opções pai, se presente.
				se ( "rótulo" em elem ) {
					if ("rótulo" em elem.parentNode ) {
						retornar elem.parentNode.disabled === desativado;
					} outro {
						retornar elem.disabled === desativado;
					}
				}

				// Suporte: IE 6 - 11+
				// Use a propriedade de atalho isDisabled para verificar ancestrais de fieldset desativados
				retornar elem.isDisabled === desativado ||

					// Onde não houver isDisabled, verifique manualmente
					elem.isDisabled !== !disabled &&
						inDisabledFieldset( elem ) === disabled;
			}

			retornar elem.disabled === desativado;

		// Tente eliminar os elementos que não podem ser desativados antes de confiar na propriedade disabled.
		Algumas vítimas caem na nossa rede (rótulo, legenda, menu, faixa), mas não deveria.
		// sequer existem neles, muito menos têm um valor booleano.
		} else if ( "label" in elem ) {
			retornar elem.disabled === desativado;
		}

		// Os elementos restantes não estão nem habilitados nem desabilitados
		retornar falso;
	};
}

/**
 * Retorna uma função a ser usada em pseudo-funções para variáveis ​​posicionais
 * @param {Função} fn
 */
função criarPseudoPosicional( fn ) {
	retornar funçãoMarcar( função( argumento ) {
		argumento = +argumento;
		retornar funçãoMarcar( função( semente, correspondências ) {
			var j,
				matchIndexes = fn( [], seed.length, argument ),
				i = matchIndexes.length;

			// Corresponde aos elementos encontrados nos índices especificados
			enquanto ( i-- ) {
				se ( semente[ ( j = matchIndexes[ i ] ) ] ) {
					semente[ j ] = !( matches[ j ] = semente[ j ] );
				}
			}
		} );
	} );
}

/**
 * Define variáveis ​​relacionadas ao documento uma única vez, com base no documento atual.
 * @param {Element|Object} [node] Um elemento ou objeto de documento a ser usado para definir o documento
 */
função setDocument( nó ) {
	var subjanela,
		doc = nó ? nó.ownerDocument || nó : documento$1;

	// Retorna imediatamente se o documento for inválido ou já estiver selecionado
	// Suporte: IE 11+
	// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
	// Dois documentos; comparações superficiais funcionam.
	// eslint-disable-next-line eqeqeq
	se (doc == documento || doc.nodeType !== 9) {
		retornar;
	}

	// Atualizar variáveis ​​globais
	documento = doc;
	documentElement = document.documentElement;
	documentIsHTML = !jQuery.isXMLDoc( document );

	// Compatível com: IE 9 - 11+
	// O acesso a documentos iframe após o descarregamento gera erros de "permissão negada" (consulte trac-13936)
	// Suporte: IE 11+
	// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
	// Dois documentos; comparações superficiais funcionam.
	// eslint-disable-next-line eqeqeq
	se ( isIE && document$1 != document &&
		( subWindow = document.defaultView ) && subWindow.top !== subWindow ) {
		subWindow.addEventListener("unload", unloadHandler);
	}
}

encontrar.correspondências = função( expr, elementos ) {
	retornar encontrar( expr, null, null, elementos );
};

find.matchesSelector = function( elem, expr ) {
	definirDocumento( elemento );

	se (documentoIsHTML &&
		!nonnativeSelectorCache[ expr + " " ] &&
		( !rbuggyQSA || !rbuggyQSA.test( expr ) ) ) {

		tentar {
			retornar matches.call( elem, expr );
		} catch ( e ) {
			nonnativeSelectorCache( expr, true );
		}
	}

	retornar find( expr, document, null, [ elem ] ).length > 0;
};

jQuery.expr = {

	// Pode ser ajustado pelo usuário
	comprimento do cache: 50,

	criarPseudo: funçãoMarcar,

	correspondência: matchExpr,

	encontrar: {
		ID: função( id, contexto ) {
			if ( typeof context.getElementById !== "undefined" && documentIsHTML ) {
				var elemento = context.getElementById(id);
				retornar elem ? [ elem ] : [];
			}
		},

		TAG: função( tag, contexto ) {
			if ( typeof context.getElementsByTagName !== "undefined" ) {
				retornar context.getElementsByTagName( tag );

				// Os nós DocumentFragment não possuem gEBTN
			} outro {
				retornar context.querySelectorAll( tag );
			}
		},

		CLASSE: função( nomeDaClasse, contexto ) {
			if ( typeof context.getElementsByClassName !== "undefined" && documentIsHTML ) {
				retornar context.getElementsByClassName( className );
			}
		}
	},

	relativo: {
		">": { dir: "parentNode", primeiro: verdadeiro },
		" ": { dir: "parentNode" },
		"+": { dir: "previousSibling", first: true },
		"~": { dir: "previousSibling" }
	},

	pré-filtro: pré-filtro,

	filtro: {
		ID: função( id ) {
			var attrId = unescapeSelector( id );
			função de retorno (elemento) {
				retornar elem.getAttribute( "id" ) === attrId;
			};
		},

		TAG: função( nodeNameSelector ) {
			var expectedNodeName = unescapeSelector( nodeNameSelector ).toLowerCase();
			retornar nodeNameSelector === "*" ?

				função() {
					retornar verdadeiro;
				} :

				função( elem ) {
					retornar nodeName( elem, expectedNodeName );
				};
		},

		CLASSE: função( nomeDaClasse ) {
			var padrão = classCache[ className + " " ];

			padrão de retorno ||
				( padrão = novo RegExp( "(^|" + espaço em branco + ")" + nomeDaClasse +
					"(" + espaço em branco + "|$)" ) ) &&
				classCache( className, function( elem ) {
					retornar padrão.teste(
						typeof elem.className === "string" && elem.className ||
							typeof elem.getAttribute !== "undefined" &&
								elem.getAttribute("class") ||
							""
					);
				} );
		},

		ATTR: função( nome, operador, verificação ) {
			função de retorno (elemento) {
				var result = jQuery.attr( elem, name );

				se (resultado == nulo) {
					operador de retorno === "!=";
				}
				se ( !operador ) {
					retornar verdadeiro;
				}

				resultado += "";

				se ( operador === "=" ) {
					resultado de retorno === verificação;
				}
				se ( operador === "!=" ) {
					retorno resultado !== verificação;
				}
				se ( operador === "^=" ) {
					retornar check && result.indexOf( check ) === 0;
				}
				se ( operador === "*=" ) {
					retornar check && result.indexOf( check ) > -1;
				}
				se ( operador === "$=" ) {
					retornar check && result.slice( -check.length ) === check;
				}
				se ( operador === "~=" ) {
					retornar ( " " + result.replace( rwhitespace, " " ) + " " )
						.indexOf( check ) > -1;
				}
				se ( operador === "|=" ) {
					retorno resultado === verificação || resultado.slice( 0, verificação.length + 1 ) === verificação + "-";
				}

				retornar falso;
			};
		},

		FILHO: função( tipo, o que, _argumento, primeiro, último ) {
			var simple = type.slice( 0, 3 ) !== "nth",
				forward = type.slice( -4 ) !== "last",
				ofType = o que === "do tipo";

			retornar primeiro === 1 && último === 0 ?

				// Atalho para :nth-*(n)
				função( elem ) {
					retornar !!elem.parentNode;
				} :

				função( elem, _context, xml ) {
					var cache, outerCache, node, nodeIndex, start,
						dir = simple !== forward ? "nextSibling" : "previousSibling",
						pai = elem.parentNode,
						nome = ofType && elem.nodeName.toLowerCase(),
						useCache = !xml && !ofType,
						diferença = falso;

					se (pai) {

						// :(primeiro|último|único)-(filho|do-tipo)
						se (simples) {
							enquanto ( dir ) {
								nó = elem;
								enquanto ( ( nó = nó[ dir ] ) ) {
									se (do tipo ?
										nodeName( nó, nome ) :
										node.nodeType === 1 ) {

										retornar falso;
									}
								}

								// Inverter a direção para :only-* (se ainda não o fizemos)
								início = dir = tipo === "apenas" && !início && "próximoirmão";
							}
							retornar verdadeiro;
						}

						início = [ avançar ? pai.primeiroFilho : pai.últimoFilho ];

						// non-xml :nth-child(...) armazena dados em cache em `parent`
						se (forçar && usarCache) {

							// Busca `elem` em um índice previamente armazenado em cache
							outerCache = parent[ jQuery.expando ] ||
								( parent[ jQuery.expando ] = {} );
							cache = outerCache[ tipo ] || [];
							nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
							diff = nodeIndex && cache[ 2 ];
							nó = nodeIndex && parent.childNodes[nodeIndex];

							enquanto ( ( nó = ++nóIndex && nó && nó[ dir ] ||

								// Recorrer à busca de `elem` desde o início
								(diff = nodeIndex = 0 ) || start.pop() ) ) {

								// Quando encontrado, armazena em cache os índices em `parent` e interrompe.
								se ( node.nodeType === 1 && ++diff && node === elem ) {
									outerCache[ tipo ] = [ dirruns, nodeIndex, diff ];
									quebrar;
								}
							}

						} outro {

							// Use o índice do elemento previamente armazenado em cache, se disponível
							se ( usarCache ) {
								outerCache = elem[ jQuery.expando ] ||
									( elem[ jQuery.expando ] = {} );
								cache = outerCache[ tipo ] || [];
								nodeIndex = cache[ 0 ] === dirruns && cache[ 1 ];
								diferença = índiceDoNó;
							}

							// xml :nth-child(...)
							// ou :nth-last-child(...) ou :nth(-last)?-of-type(...)
							se (diff === falso) {

								// Use o mesmo loop acima para buscar `elem` a partir do início.
								enquanto ( ( nó = ++nóIndex && nó && nó[ dir ] ||
									(diff = nodeIndex = 0 ) || start.pop() ) ) {

									se ( ( deType ?
										nodeName( nó, nome ) :
										node.nodeType === 1 ) &&
										++diff ) {

										// Armazena em cache o índice de cada elemento encontrado
										se ( usarCache ) {
											outerCache = node[ jQuery.expando ] ||
												( node[ jQuery.expando ] = {} );
											outerCache[ tipo ] = [ dirruns, diff ];
										}

										se ( nó === elem ) {
											quebrar;
										}
									}
								}
							}
						}

						// Incorpore o deslocamento e, em seguida, verifique em relação ao tamanho do ciclo
						diferença -= último;
						retornar diff === first || ( diff % first === 0 && diff / first >= 0 );
					}
				};
		},

		PSEUDO: função( pseudo, argumento ) {

			// Os nomes de pseudo-classes não diferenciam maiúsculas de minúsculas
			// https://www.w3.org/TR/selectors/#pseudo-classes
			// Priorizar considerando a diferenciação entre maiúsculas e minúsculas caso pseudo-valores personalizados sejam adicionados com letras maiúsculas.
			// Lembre-se que setFilters herda de pseudo-classes
			var fn = jQuery.expr.pseudos[ pseudo ] ||
				jQuery.expr.setFilters[ pseudo.toLowerCase() ] ||
				selectorError( "pseudo não suportado: " + pseudo );

			// O usuário pode usar createPseudo para indicar que
			// São necessários argumentos para criar a função de filtro
			// assim como o jQuery faz
			se ( fn[ jQuery.expando ] ) {
				retornar fn( argumento );
			}

			retornar fn;
		}
	},

	pseudos: {

		// Pseudônimos potencialmente complexos
		não: markFunction( função( seletor ) {

			// Remove os espaços em branco do seletor passado para compilar
			// para evitar tratar início e fim
			// espaços como combinadores
			var input = [],
				resultados = [],
				matcher = compile( selector.replace( rtrimCSS, "$1" ) );

			retornar matcher[ jQuery.expando ] ?
				markFunction( function( seed, matches, _context, xml ) {
					var elem,
						não correspondido = matcher( seed, null, xml, [] ),
						i = comprimento da semente;

					// Encontrar elementos não correspondidos pelo `matcher`
					enquanto ( i-- ) {
						se ( ( elem = não correspondido[ i ] ) ) {
							semente[ i ] = !( matches[ i ] = elem );
						}
					}
				} ) :
				função( elem, _context, xml ) {
					input[ 0 ] = elem;
					matcher( entrada, nulo, xml, resultados );

					// Não mantenha o elemento
					// (ver https://github.com/jquery/sizzle/issues/299)
					input[ 0 ] = null;
					retornar !resultados.pop();
				};
		} ),

		tem: markFunction( função( seletor ) {
			função de retorno (elemento) {
				retornar encontrar( seletor, elem ).length > 0;
			};
		} ),

		contém: markFunction( função( texto ) {
			texto = unescapeSelector( texto );
			função de retorno (elemento) {
				retornar ( elem.textContent || jQuery.text( elem ) ).indexOf( text ) > -1;
			};
		} ),

		// Indica se um elemento é representado por um seletor :lang()
		// baseia-se exclusivamente no valor do idioma do elemento
		// sendo igual ao identificador C,
		// ou começando com o identificador C imediatamente seguido por "-".
		// A correspondência de C com o valor de idioma do elemento é realizada sem distinção entre maiúsculas e minúsculas.
		// O identificador C não precisa ser um nome de idioma válido.
		// https://www.w3.org/TR/selectors/#lang-pseudo
		lang: markFunction( function( lang ) {

			// O valor de lang deve ser um identificador válido
			if ( !ridentifier.test( lang || " " ) ) {
				selectorError( "idioma não suportado: " + lang );
			}
			lang = unescapeSelector( lang ).toLowerCase();
			função de retorno (elemento) {
				var elemLang;
				fazer {
					se ( ( elemLang = documentIsHTML ?
						elem.lang:
						elem.getAttribute("xml:lang") || elem.getAttribute("lang")) {

						elemLang = elemLang.toLowerCase();
						return elemLang === idioma || elemLang.indexOf(lang + "-" ) === 0;
					}
				} while ( ( elem = elem.parentNode ) && elem.nodeType === 1 );
				retornar falso;
			};
		} ),

		// Diversos
		alvo: função( elemento ) {
			var hash = window.location && window.location.hash;
			retornar hash && hash.slice( 1 ) === elem.id;
		},

		raiz: função( elemento ) {
			return elemento === documentElement;
		},

		foco: função( elemento ) {
			retornar elem === document.activeElement &&
				document.hasFocus() &&
				!!( elem.type || elem.href || ~elem.tabIndex );
		},

		// Propriedades booleanas
		ativado: criarPseudoDesativado( falso ),
		desativado: criarPseudoDesativado( verdadeiro ),

		verificado: função( elemento ) {

			// Em CSS3, :checked deve retornar elementos tanto marcados quanto selecionados.
			// https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			retornar ( nodeName( elem, "input" ) && !!elem.checked ) ||
				( nodeName( elem, "option" ) && !!elem.selected );
		},

		selecionado: função( elemento ) {

			// Suporte: IE <= 11+
			// Acessando a propriedade selectedIndex
			// força o navegador a tratar a opção padrão como
			// selecionado quando em um grupo de opções.
			if (isIE && elem.parentNode) {
				// eslint-disable-next-line no-unused-expressions
				elem.parentNode.selectedIndex;
			}

			retornar elem.selected === true;
		},

		// Conteúdo
		vazio: função( elemento ) {

			// https://www.w3.org/TR/selectors/#empty-pseudo
			// :empty é negado pelo elemento (1) ou nós de conteúdo (texto: 3; cdata: 4; referência da entidade: 5),
			// mas não por outros (comentário: 8; instrução de processamento: 7; etc.)
			// nodeType < 6 funciona porque os atributos (2) não aparecem como filhos
			para (elem = elem.firstChild; elem; elem = elem.nextSibling) {
				se (elem.nodeType < 6) {
					retornar falso;
				}
			}
			retornar verdadeiro;
		},

		pai: função( elemento ) {
			retornar !jQuery.expr.pseudos.empty( elem );
		},

		// Tipos de elemento/entrada
		cabeçalho: função( elemento ) {
			retornar rheader.test( elem.nodeName );
		},

		entrada: função( elemento ) {
			retornar rinputs.test( elem.nodeName );
		},

		botão: função( elemento ) {
			retornar nodeName( elem, "input" ) && elem.type === "button" ||
				nodeName( elem, "button" );
		},

		texto: função( elem ) {
			retornar nodeName( elem, "input" ) && elem.type === "text";
		},

		// Posição na coleção
		primeiro: criarPseudoPosicional( função() {
			retornar [ 0 ];
		} ),

		último: criarPseudoPosicional( função( _matchIndexes, comprimento ) {
			retornar [ comprimento - 1 ];
		} ),

		eq: createPositionalPseudo( function( _matchIndexes, length, argument ) {
			retornar [ argumento < 0 ? argumento + comprimento : argumento ];
		} ),

		mesmo: criarPseudoPosicional( função( índicesDeCorrespondência, comprimento ) {
			var i = 0;
			para ( ; i < comprimento; i += 2 ) {
				matchIndexes.push( i );
			}
			retornar índices de correspondência;
		} ),

		ímpar: criarPseudoPosicional( função( índicesDeCorrespondência, comprimento ) {
			var i = 1;
			para ( ; i < comprimento; i += 2 ) {
				matchIndexes.push( i );
			}
			retornar índices de correspondência;
		} ),

		lt: criarPseudoPosicional( função( índicesDeCorrespondência, comprimento, argumento ) {
			var i;

			se ( argumento < 0 ) {
				i = argumento + comprimento;
			} senão se ( argumento > comprimento ) {
				i = comprimento;
			} outro {
				i = argumento;
			}

			para ( ; --i >= 0; ) {
				matchIndexes.push( i );
			}
			retornar índices de correspondência;
		} ),

		gt: criarPseudoPosicional( função( índicesDeCorrespondência, comprimento, argumento ) {
			var i = argumento < 0 ? argumento + comprimento : argumento;
			para ( ; ++i < comprimento; ) {
				matchIndexes.push( i );
			}
			retornar índices de correspondência;
		} )
	}
};

jQuery.expr.pseudos.nth = jQuery.expr.pseudos.eq;

// Adicionar pseudo-tipos de botão/entrada
para ( i em { radio: verdadeiro, checkbox: verdadeiro, file: verdadeiro, password: verdadeiro, image: verdadeiro } ) {
	jQuery.expr.pseudos[ i ] = createInputPseudo( i );
}
para ( i em { enviar: verdadeiro, redefinir: verdadeiro } ) {
	jQuery.expr.pseudos[ i ] = createButtonPseudo( i );
}

// API fácil para criar novos conjuntos de filtros
função setFilters() {}
setFilters.prototype = jQuery.expr.pseudos;
jQuery.expr.setFilters = novo setFilters();

função adicionarCombinador( matcher, combinador, base ) {
	var dir = combinator.dir,
		pular = combinador.próximo,
		chave = pular || dir,
		verificarElementosNão = base && chave === "parentNode",
		doneName = done++;

	retornar combinador.primeiro?

		// Verificar em relação ao ancestral mais próximo/elemento precedente
		função( elemento, contexto, xml ) {
			enquanto ( ( elem = elem[ dir ] ) ) {
				se (elem.nodeType === 1 || checkNonElements) {
					retornar matcher( elemento, contexto, xml );
				}
			}
			retornar falso;
		} :

		// Verificar em relação a todos os elementos ancestrais/precedentes
		função( elemento, contexto, xml ) {
			var oldCache, outerCache,
				novoCache = [ dirruns, doneName ];

			Não podemos definir dados arbitrários em nós XML, portanto eles não se beneficiam do cache de combinadores.
			se (xml) {
				enquanto ( ( elem = elem[ dir ] ) ) {
					se (elem.nodeType === 1 || checkNonElements) {
						se (matcher(elemento, contexto, xml)) {
							retornar verdadeiro;
						}
					}
				}
			} outro {
				enquanto ( ( elem = elem[ dir ] ) ) {
					se (elem.nodeType === 1 || checkNonElements) {
						outerCache = elem[ jQuery.expando ] || ( elem[ jQuery.expando ] = {} );

						se ( pular && nodeName( elemento, pular ) ) {
							elem = elem[ dir ] || elem;
						} else if ( ( oldCache = outerCache[ key ] ) &&
							oldCache[ 0 ] === dirruns && oldCache[ 1 ] === doneName ) {

							// Atribua a newCache para que os resultados se propaguem de volta aos elementos anteriores
							retornar ( novoCache[ 2 ] = oldCache[ 2 ] );
						} outro {

							// Reutilize o newcache para que os resultados se propaguem de volta aos elementos anteriores
							outerCache[ chave ] = novoCache;

							// Uma correspondência significa que terminamos; uma falha significa que temos que continuar verificando.
							se ( ( novoCache[ 2 ] = matcher( elem, contexto, xml ) ) ) {
								retornar verdadeiro;
							}
						}
					}
				}
			}
			retornar falso;
		};
}

function elementMatcher(correspondentes) {
	retornar matchers.length > 1 ?
		função( elemento, contexto, xml ) {
			var i = matchers.length;
			enquanto ( i-- ) {
				se ( !matchers[ i ]( elem, context, xml ) ) {
					retornar falso;
				}
			}
			retornar verdadeiro;
		} :
		correspondentes[ 0 ];
}

função multipleContexts( seletor, contextos, resultados ) {
	var i = 0,
		len = contexts.length;
	para ( ; i < len; i++ ) {
		encontrar( seletor, contextos[ i ], resultados );
	}
	retornar resultados;
}

função condensar( não correspondido, mapa, filtro, contexto, xml ) {
	var elem,
		novoNão correspondido = [],
		i = 0,
		len = unmatched.length,
		mapeado = mapa != nulo;

	para ( ; i < len; i++ ) {
		se ( ( elem = não correspondido[ i ] ) ) {
			se ( !filtro || filtro( elemento, contexto, xml ) ) {
				novoUnmatched.push(elem);
				se ( mapeado ) {
					mapa.push( i );
				}
			}
		}
	}

	retornar novoNão correspondido;
}

função setMatcher( preFilter, selector, matcher, postFilter, postFinder, postSelector ) {
	if ( postFilter && !postFilter[ jQuery.expando ] ) {
		postFilter = setMatcher( postFilter );
	}
	se ( postFinder && !postFinder[ jQuery.expando ] ) {
		postFinder = setMatcher( postFinder, postSelector );
	}
	retornar funçãoMarcar( função( semente, resultados, contexto, xml ) {
		var temp, i, elem, matcherOut,
			preMap = [],
			postMap = [],
			preexistente = resultados.comprimento,

			// Obter elementos iniciais a partir de um seed ou contexto
			elementos = semente ||
				múltiplosContextos( seletor || "*",
					context.nodeType ? [ context ] : context, [] ),

			// Pré-filtro para obter a entrada do comparador, preservando um mapa para sincronização entre semente e resultados.
			matcherIn = preFilter && ( seed || !selector ) ?
				condensar(elementos, pré-Mapa, pré-Filtro, contexto, xml):
				elementos;

		se (matcher) {

			// Se tivermos um postFinder, ou um postFilter com seed filtrada, ou um postFilter sem seed
			// ou resultados preexistentes,
			matcherOut = postFinder || ( seed ? preFilter : preexisting || postFilter ) ?

				// ...processamento intermediário é necessário
				[] :

				// ...caso contrário, use os resultados diretamente
				resultados;

			// Encontrar correspondências primárias
			matcher( matcherIn, matcherOut, context, xml );
		} outro {
			matcherOut = matcherIn;
		}

		// Aplicar pós-filtro
		se ( postFilter ) {
			temp = condensar( matcherOut, postMap );
			postFilter( temp, [], context, xml );

			// Desfaça a correspondência de elementos que falharam, movendo-os de volta para matcherIn
			i = temp.comprimento;
			enquanto ( i-- ) {
				se ( ( elem = temp[ i ] ) ) {
					matcherOut[ postMap[ i ] ] = !( matcherIn[ postMap[ i ] ] = elem );
				}
			}
		}

		se ( semente ) {
			se ( postFinder || preFilter ) {
				se ( postFinder ) {

					// Obtenha o matcherOut final condensando este intermediário em contextos postFinder
					temp = [];
					i = matcherOut.length;
					enquanto ( i-- ) {
						se ( ( elem = matcherOut[ i ] ) ) {

							// Restaura matcherIn, pois elem ainda não é uma correspondência final
							temp.push( ( matcherIn[ i ] = elem ) );
						}
					}
					postFinder( null, ( matcherOut = [] ), temp, xml );
				}

				// Mover os elementos correspondentes da semente para os resultados para mantê-los sincronizados
				i = matcherOut.length;
				enquanto ( i-- ) {
					se ( ( elem = matcherOut[ i ] ) &&
						( temp = postFinder ? indexOf.call( seed, elem ) : preMap[ i ] ) > -1 ) {

						semente[temp] = !(resultados[temp] = elem);
					}
				}
			}

		// Adicionar elementos aos resultados, através do postFinder se definido
		} outro {
			matcherOut = condensar(
				matcherOut === resultados ?
					matcherOut.splice( preexisting, matcherOut.length ) :
					matcherOut
			);
			se ( postFinder ) {
				postFinder( null, results, matcherOut, xml );
			} outro {
				push.apply(resultados, matcherOut);
			}
		}
	} );
}

função matcherFromTokens( tokens ) {
	var checkContext, matcher, j,
		len = tokens.length,
		leadingRelative = jQuery.expr.relative[ tokens[ 0 ].type ],
		implicitRelative = leadingRelative || jQuery.expr.relative[ " " ],
		i = Relativo líder ? 1 : 0,

		// O mecanismo de correspondência fundamental garante que os elementos sejam acessíveis a partir do(s) contexto(s) de nível superior.
		matchContext = addCombinator( function( elem ) {
			retornar elem === checkContext;
		}, implicitRelative, true ),
		matchAnyContext = addCombinator( function( elem ) {
			retornar indexOf.call( checkContext, elem ) > -1;
		}, implicitRelative, true ),
		matchers = [ function( elem, context, xml ) {

			// Suporte: IE 11+
			// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
			// Dois documentos; comparações superficiais funcionam.
			// eslint-disable-next-line eqeqeq
			var ret = ( !leadingRelative && ( xml || context != outermostContext ) ) || (
				(checkContext = contexto).nodeType?
					matchContext(elem, context, xml):
					matchAnyContext( elem, context, xml ) );

			// Evite se agarrar ao elemento
			// (ver https://github.com/jquery/sizzle/issues/299)
			checkContext = nulo;
			retornar ret;
		} ];

	para ( ; i < len; i++ ) {
		if ( ( matcher = jQuery.expr.relative[ tokens[ i ].type ] ) ) {
			matchers = [addCombinator(elementMatcher(matchers), matcher)];
		} outro {
			matcher = jQuery.expr.filter[ tokens[ i ].type ].apply( null, tokens[ i ].matches );

			// Retorna especial ao encontrar um correspondente posicional
			se ( matcher[ jQuery.expando ] ) {

				// Encontre o próximo operador relativo (se houver) para o tratamento adequado.
				j = ++i;
				para ( ; j < len; j++ ) {
					se ( jQuery.expr.relative[ tokens[ j ].type ] ) {
						quebrar;
					}
				}
				retornar setMatcher(
					i > 1 && elementMatcher(correspondentes),
					i > 1 && toSelector(

						// Se o token anterior for um combinador descendente, insira um `*` implícito que aceite qualquer elemento.
						tokens.slice( 0, i - 1 )
							.concat( { value: tokens[ i - 2 ].type === " " ? "*" : "" } )
					).replace( rtrimCSS, "$1" ),
					correspondente,
					i < j && matcherFromTokens( tokens.slice( i, j ) ),
					j < len && matcherFromTokens( ( tokens = tokens.slice( j ) ) ),
					j < len && toSelector( tokens )
				);
			}
			matchers.push( matcher );
		}
	}

	return elementMatcher(correspondentes);
}

função matcherFromGroupMatchers( elementMatchers, setMatchers ) {
	var bySet = setMatchers.length > 0,
		porElemento = elementMatchers.length > 0,
		superMatcher = função( semente, contexto, xml, resultados, mais externo ) {
			var elem, j, matcher,
				matchedCount = 0,
				i = "0",
				não correspondido = semente && [],
				setMatched = [],
				contextBackup = outermostContext,

				// Devemos sempre ter elementos semente ou o contexto mais externo
				elems = seed || byElement && jQuery.expr.find.TAG( "*", outermost ),

				// Use dirruns inteiros se este for o matcher mais externo
				dirrunsUnique = ( dirruns += contextBackup == null ? 1 : Math.random() || 0.1 );

			se (mais externo) {

				// Suporte: IE 11+
				// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
				// Dois documentos; comparações superficiais funcionam.
				// eslint-disable-next-line eqeqeq
				outermostContext = context == document || context || outermost;
			}

			// Adicionar elementos passando elementMatchers diretamente aos resultados
			para ( ; ( elem = elems[ i ] ) != null; i++ ) {
				se (porElemento && elem ) {
					j = 0;

					// Suporte: IE 11+
					// O IE às vezes exibe um erro de "Permissão negada" ao realizar comparações rigorosas.
					// Dois documentos; comparações superficiais funcionam.
					// eslint-disable-next-line eqeqeq
					se ( !context && elem.ownerDocument != document ) {
						definirDocumento( elemento );
						xml = !documentIsHTML;
					}
					enquanto ( ( matcher = elementMatchers[ j++ ] ) ) {
						se (matcher(elemento, contexto || documento, xml)) {
							push.call(resultados, elem);
							quebrar;
						}
					}
					se (mais externo) {
						dirruns = dirrunsUnique;
					}
				}

				// Rastrear elementos não correspondentes para filtros definidos
				se (porSet) {

					Eles terão analisado todas as correspondências possíveis.
					if ( (elem = !matcher && elem ) ) {
						matchedCount--;
					}

					// Aumenta o tamanho do array para cada elemento, correspondido ou não
					se ( semente ) {
						não correspondido.push( elemento );
					}
				}
			}

			// `i` agora representa a contagem de elementos visitados acima e está sendo adicionado a `matchedCount`
			// torna o último não negativo.
			contagemCorrespondente += i;

			// Aplicar filtros definidos a elementos não correspondentes
			// NOTA: Esta etapa pode ser ignorada se não houver elementos não correspondentes (ou seja, `matchedCount`
			// é igual a `i`), a menos que não tenhamos visitado _nenhum_ elemento no loop acima porque temos
			// Sem correspondência de elementos e sem semente.
			// Incrementar uma string inicial "0" `i` permite que `i` permaneça uma string apenas nesse sentido.
			// caso, que resultará em um `matchedCount` "00" que difere de `i`, mas também é
			// numericamente zero.
			if ( bySet && i !== matchedCount ) {
				j = 0;
				enquanto ( ( matcher = setMatchers[ j++ ] ) ) {
					matcher( não correspondido, setMatched, contexto, xml );
				}

				se ( semente ) {

					// Reintegrar correspondências de elementos para eliminar a necessidade de classificação
					se (matchedCount > 0) {
						enquanto ( i-- ) {
							se ( !( unmatched[ i ] || setMatched[ i ] ) ) {
								setMatched[ i ] = pop.call( resultados );
							}
						}
					}

					// Descartar valores de espaço reservado do índice para obter apenas correspondências reais
					setMatched = condense( setMatched );
				}

				// Adicionar correspondências aos resultados
				push.apply(resultados, setMatched);

				// Conjuntos sem sementes que correspondem a múltiplos correspondentes bem-sucedidos estipulam a ordenação
				se (mais externo && !semente && setMatched.length > 0 &&
					( matchedCount + setMatchers.length ) > 1 ) {

					jQuery.uniqueSort(resultados);
				}
			}

			// Substituir a manipulação de variáveis ​​globais por meio de comparadores aninhados
			se (mais externo) {
				dirruns = dirrunsUnique;
				outermostContext = contextBackup;
			}

			retornar não correspondido;
		};

	retornar por conjunto?
		markFunction( superMatcher ) :
		superMatcher;
}

function compile( selector, match /* Uso interno apenas */ ) {
	var i,
		setMatchers = [],
		elementMatchers = [],
		cached = compilerCache[ selector + " " ];

	se ( !em cache ) {

		// Gere uma função de funções recursivas que podem ser usadas para verificar cada elemento
		se ( !correspondência ) {
			correspondência = tokenizar( seletor );
		}
		i = match.length;
		enquanto ( i-- ) {
			cached = matcherFromTokens( match[ i ] );
			se ( cached[ jQuery.expando ] ) {
				setMatchers.push( cached );
			} outro {
				elementMatchers.push( cached );
			}
		}

		// Armazene em cache a função compilada
		cache = compilerCache(seletor,
			matcherFromGroupMatchers( elementMatchers, setMatchers ) );

		// Salvar seletor e tokenização
		cached.selector = seletor;
	}
	retornar em cache;
}

/**
 * Uma função de seleção de baixo nível que funciona com o compilador do jQuery.
 * funções de seleção
 * @param {String|Function} selector Um seletor ou uma função pré-compilada
 * função seletora construída com jQuery selector compile
 * @param {Elemento} contexto
 * @param {Array} [resultados]
 * @param {Array} [seed] Um conjunto de elementos para comparação
 */
função selecionar( seletor, contexto, resultados, semente ) {
	var i, tokens, token, tipo, encontrar,
		compilado = seletor typeof === "função" && seletor,
		correspondência = !semente && tokenizar( ( seletor = compilado.seletor || seletor ) );

	resultados = resultados || [];

	// Tente minimizar as operações se houver apenas um seletor na lista e nenhum valor inicial.
	// (este último nos garante contexto)
	se ( match.length === 1 ) {

		// Reduzir o contexto se o seletor composto principal for um ID
		tokens = match[ 0 ] = match[ 0 ].slice( 0 );
		se (tokens.length > 2 && (token = tokens[0]).type === "ID" &&
				context.nodeType === 9 && documentIsHTML &&
				jQuery.expr.relative[ tokens[ 1 ].type ] ) {

			contexto = ( jQuery.expr.find.ID(
				unescapeSelector( token.matches[ 0 ] ),
				contexto
			) || [] )[ 0 ];
			se ( !contexto ) {
				retornar resultados;

			// Os comparadores pré-compilados ainda verificarão a ancestralidade, então suba um nível.
			} senão se (compilado) {
				contexto = contexto.nópai;
			}

			seletor = seletor.slice( tokens.shift().value.length );
		}

		// Obtenha um conjunto de sementes para correspondência da direita para a esquerda
		i = matchExpr.needsContext.test( selector ) ? 0 : tokens.length;
		enquanto ( i-- ) {
			token = tokens[ i ];

			// Abortar se encontrarmos um combinador
			if ( jQuery.expr.relative[ ( type = token.type ) ] ) {
				quebrar;
			}
			se ( ( encontrar = jQuery.expr.find[ tipo ] ) ) {

				// Pesquisa, expandindo o contexto para combinadores irmãos principais
				se ( ( semente = encontrar(
					unescapeSelector( token.matches[ 0 ] ),
					rsibling.test( tokens[ 0 ].type ) &&
						testContext(context.parentNode) || contexto
				) ) ) {

					// Se a semente estiver vazia ou não houver mais tokens, podemos retornar antecipadamente.
					tokens.splice( i, 1 );
					seletor = seed.length && toSelector( tokens );
					se ( !seletor ) {
						push.apply(resultados, semente);
						retornar resultados;
					}

					quebrar;
				}
			}
		}
	}

	// Compila e executa uma função de filtragem, caso nenhuma tenha sido fornecida.
	// Forneça `match` para evitar a re-tokenização caso tenhamos modificado o seletor acima.
	(compilado || compilar(seletor, correspondência))(
		semente,
		contexto,
		!documentIsHTML,
		resultados,
		!contexto || rsibling.test(selector) && testContext(contexto.parentNode) || contexto
	);
	retornar resultados;
}

// Inicializar com o documento padrão
definirDocumento();

jQuery.find = encontrar;

// Essas informações sempre foram privadas, mas costumavam ser documentadas como parte de
// Sizzle, então vamos mantê-los por enquanto para fins de compatibilidade com versões anteriores.
encontrar.compilar = compilar;
encontrar.selecionar = selecionar;
encontrar.setDocumento = setDocumento;
encontrar.tokenizar = tokenizar;

função dir( elem, dir, until ) {
	var correspondente = [],
		truncar = até que !== indefinido;

	enquanto ( ( elem = elem[ dir ] ) && elem.nodeType !== 9 ) {
		se (elem.nodeType === 1) {
			se ( truncar && jQuery( elem ).is( until ) ) {
				quebrar;
			}
			correspondente.push( elemento );
		}
	}
	retorno correspondente;
}

função irmãos( n, elem ) {
	var correspondente = [];

	para ( ; n; n = n.nextSibling ) {
		se ( n.nodeType === 1 && n !== elem ) {
			correspondente.push( n );
		}
	}

	retorno correspondente;
}

var rneedsContext = jQuery.expr.match.needsContext;

// rsingleTag corresponde a uma string que consiste em um único elemento HTML sem atributos
// e captura o nome do elemento
var rsingleTag = /^<([az][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;

função isObviousHtml( entrada ) {
	retornar input[ 0 ] === "<" &&
		input[ input.length - 1 ] === ">" &&
		input.length >= 3;
}

// Implemente a mesma funcionalidade para filtro e não filtro
função winnow( elementos, qualificador, não ) {
	se (qualificador typeof === "função" ) {
		return jQuery.grep( elements, function( elem, i ) {
			retornar !!qualifier.call( elem, i, elem ) !== not;
		} );
	}

	// Elemento único
	se (qualificador.tipo de nó) {
		return jQuery.grep( elementos, function( elem ) {
			retornar (elemento === qualificador) !== não;
		} );
	}

	// Array de elementos (jQuery, argumentos, Array)
	se ( typeof qualificador !== "string" ) {
		return jQuery.grep( elementos, function( elem ) {
			retornar ( indexOf.call( qualifier, elem ) > -1 ) !== não;
		} );
	}

	// Filtrado diretamente para seletores simples e complexos
	retornar jQuery.filter( qualificador, elementos, não );
}

jQuery.filter = function( expr, elems, not ) {
	var elem = elems[ 0 ];

	se não ) {
		expr = ":not(" + expr + ")";
	}

	se (elems.length === 1 && elem.nodeType === 1) {
		retornar jQuery.find.matchesSelector( elem, expr ) ? [ elem ] : [];
	}

	return jQuery.find.matches( expr, jQuery.grep( elems, function( elem ) {
		retornar elem.nodeType === 1;
	} ) );
};

jQuery.fn.extend( {
	encontrar: função( seletor ) {
		var i, ret,
			len = this.length,
			próprio = isto;

		se ( typeof selector !== "string" ) {
			retornar this.pushStack( jQuery( selector ).filter( function() {
				para ( i = 0; i < len; i++ ) {
					if ( jQuery.contains( self[ i ], this ) ) {
						retornar verdadeiro;
					}
				}
			} ) );
		}

		ret = this.pushStack( [] );

		para ( i = 0; i < len; i++ ) {
			jQuery.find(selector, self[i], ret);
		}

		return len > 1 ? jQuery.uniqueSort( ret ) : ret;
	},
	filtro: função( seletor ) {
		retornar this.pushStack( winnow( this, selector || [], false ) );
	},
	não: função( seletor ) {
		retornar this.pushStack( winnow( this, selector || [], true ) );
	},
	é: função( seletor ) {
		retornar !!winnow(
			esse,

			// Se este for um seletor posicional/relativo, verifique a pertinência ao conjunto retornado
			// então $("p:first").is("p:last") não retornará verdadeiro para um documento com dois "p".
			typeof selector === "string" && rneedsContext.test( selector ) ?
				jQuery(seletor):
				seletor || [],
			falso
		).comprimento;
	}
} );

// Inicializar um objeto jQuery

// Uma referência central ao jQuery raiz (documento)
var rootjQuery,

	// Uma maneira simples de verificar strings HTML
	// Priorizar #id em relação a <tag> para evitar XSS via location.hash (trac-9521)
	// Reconhecimento estrito de HTML (trac-11290: deve começar com <)
	// Atalho simples para #id para maior velocidade
	rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/,

	init = jQuery.fn.init = function( selector, context ) {
		var match, elem;

		// HANDLE: $(""), $(null), $(undefined), $(false)
		se ( !seletor ) {
			devolva isto;
		}

		// HANDLE: $(DOMElement)
		se (selector.nodeType) {
			este[ 0 ] = seletor;
			this.length = 1;
			devolva isto;

		// HANDLE: $(function)
		// Atalho para documento pronto
		} else if ( typeof selector === "function" ) {
			return rootjQuery.ready !== undefined ?
				rootjQuery.ready(selector):

				// Executar imediatamente se pronto não estiver presente
				seletor( jQuery );

		} outro {

			// Lidar com strings HTML óbvias
			correspondência = seletor + "";
			se ( isObviousHtml( correspondência ) ) {

				// Assume que as strings que começam e terminam com <> são HTML e omite-se
				// a verificação de expressão regular. Isso também lida com wrappers HTML suportados pelo navegador.
				// como TrustedHTML.
				correspondência = [ nulo, seletor, nulo ];

			// Manipular strings ou seletores HTML
			} else if ( typeof selector === "string" ) {
				correspondência = rquickExpr.exec( seletor );
			} outro {
				return jQuery.makeArray( selector, this );
			}

			// Corresponda ao HTML ou certifique-se de que nenhum contexto seja especificado para #id
			// Observação: match[1] pode ser uma string ou um wrapper TrustedHTML
			se (correspondência && (correspondência[1] || !contexto)) {

				// MANIPULADOR: $(html) -> $(array)
				se ( correspondência[ 1 ] ) {
					contexto = contexto instanceof jQuery ? contexto[ 0 ] : contexto;

					// A opção para executar scripts está ativada para compatibilidade com versões anteriores.
					// Permita que o erro seja lançado intencionalmente se parseHTML não estiver presente
					jQuery.merge( this, jQuery.parseHTML(
						correspondência[ 1 ],
						contexto && contexto.tipoDeNó ? contexto.DocumentoProprietário || contexto : documento$1,
						verdadeiro
					) );

					// HANDLE: $(html, props)
					if ( rsingleTag.test( match[ 1 ] ) && jQuery.isPlainObject( context ) ) {
						para (correspondência no contexto) {

							// As propriedades do contexto são chamadas como métodos, se possível.
							se ( typeof this[ match ] === "function" ) {
								este[correspondência](contexto[correspondência]);

							// ...e definidos como atributos
							} outro {
								this.attr( match, context[ match ] );
							}
						}
					}

					devolva isto;

				// HANDLE: $(#id)
				} outro {
					elem = document$1.getElementById( match[ 2 ] );

					se (elem) {

						// Injetar o elemento diretamente no objeto jQuery
						este[ 0 ] = elem;
						this.length = 1;
					}
					devolva isto;
				}

			// HANDLE: $(expr) & $(expr, $(...))
			} else if ( !context || context.jquery ) {
				retornar (contexto || rootjQuery).find(seletor);

			// HANDLE: $(expr, context)
			// (que é equivalente a: $(context).find(expr)
			} outro {
				retornar this.constructor(context).find(selector);
			}
		}

	};

// Atribua à função de inicialização o protótipo jQuery para posterior instanciação
init.prototype = jQuery.fn;

// Inicializar referência central
rootjQuery = jQuery( document$1 );

var rparentsprev = /^(?:parents|prev(?:Até|Todos))/,

	// Métodos que garantem a produção de um conjunto único quando se parte de um conjunto único
	garantidoUnique = {
		crianças: verdade,
		conteúdo: verdadeiro,
		próximo: verdadeiro,
		anterior: verdadeiro
	};

jQuery.fn.extend( {
	tem: função( alvo ) {
		var targets = jQuery( target, this ),
			l = targets.length;

		retornar this.filter( function() {
			var i = 0;
			para ( ; i < l; i++ ) {
				if ( jQuery.contains( this, targets[ i ] ) ) {
					retornar verdadeiro;
				}
			}
		} );
	},

	mais próximo: função( seletores, contexto ) {
		var cur,
			i = 0,
			l = este.comprimento,
			correspondente = [],
			targets = typeof selectors !== "string" && jQuery( selectors );

		// Seletores posicionais nunca correspondem, já que não há contexto de _seleção_
		if ( !rneedsContext.test( selectors ) ) {
			para ( ; i < l; i++ ) {
				for (cur = this[ i ]; cur && cur !== contexto; cur = cur.parentNode ) {

					// Sempre ignore fragmentos de documento
					se ( cur.nodeType < 11 && ( targets ?
						targets.index( cur ) > -1 :

						// Não passe elementos inválidos para jQuery#find
						cur.nodeType === 1 &&
							jQuery.find.matchesSelector( cur, selectors ) ) ) {

						correspondente.push( cur );
						quebrar;
					}
				}
			}
		}

		return this.pushStack( matched.length > 1 ? jQuery.uniqueSort( matched ) : matched );
	},

	// Determina a posição de um elemento dentro do conjunto
	índice: função( elemento ) {

		// Sem argumento, retorna o índice no elemento pai
		se ( !elem ) {
			retornar ( this[ 0 ] && this[ 0 ].parentNode ) ? this.first().prevAll().length : -1;
		}

		// Índice no seletor
		se ( typeof elem === "string" ) {
			return indexOf.call( jQuery( elem ), this[ 0 ] );
		}

		// Localize a posição do elemento desejado
		retornar indexOf.call( this,

			// Se receber um objeto jQuery, o primeiro elemento será usado.
			elem.jquery ? elem[ 0 ] : elem
		);
	},

	adicionar: função( seletor, contexto ) {
		retornar this.pushStack(
			jQuery.uniqueSort(
				jQuery.merge( this.get(), jQuery( selector, context ) )
			)
		);
	},

	addBack: função( seletor ) {
		retornar this.add( selector == null ?
			this.prevObject : this.prevObject.filter( selector )
		);
	}
} );

função irmão( cur, dir ) {
	enquanto ( ( cur = cur[ dir ] ) && cur.nodeType !== 1 ) {}
	retornar cur;
}

jQuery.each( {
	pai: função( elemento ) {
		var parent = elem.parentNode;
		retornar pai && parent.nodeType !== 11 ? pai : nulo;
	},
	pais: função( elem ) {
		retornar dir( elem, "parentNode" );
	},
	paisAté: função( elem, _i, até ) {
		retornar dir( elem, "parentNode", até );
	},
	próximo: função( elemento ) {
		retornar irmão(elemento, "próximoIrmão");
	},
	anterior: função( elemento ) {
		retornar irmão(elemento, "irmãoanterior");
	},
	nextAll: função(elem) {
		retornar dir( elem, "nextSibling" );
	},
	prevAll: função( elemento ) {
		retornar dir( elem, "previousSibling" );
	},
	nextUntil: função( elem, _i, until ) {
		retornar dir( elem, "nextSibling", until );
	},
	prevUntil: função( elem, _i, until ) {
		retornar dir( elem, "previousSibling", until );
	},
	irmãos: função( elem ) {
		retornar irmãos( ( elem.parentNode || {} ).firstChild, elem );
	},
	filhos: função( elem ) {
		retornar irmãos(elem.primeiroFilho);
	},
	conteúdo: função( elemento ) {
		se (elem.contentDocument != null &&

			// Suporte: IE 11+
			// Elementos <object> sem o atributo `data` possuem um objeto
			// `contentDocument` com um protótipo `null`.
			getProto(elem.contentDocument)) {

			retornar elem.contentDocument;
		}

		// Compatível com: IE 9 - 11+
		// Trate o elemento de modelo como um elemento comum nos navegadores que
		// Não dê suporte a isso.
		se ( nodeName( elem, "template" ) ) {
			elem = elem.content || elem;
		}

		return jQuery.merge( [], elem.childNodes );
	}
}, função( nome, fn ) {
	jQuery.fn[ nome ] = function( até, seletor ) {
		var matched = jQuery.map( this, fn, until );

		se ( nome.slice( -5 ) !== "Até" ) {
			seletor = até;
		}

		se (seletor && tipo do seletor === "string" ) {
			correspondente = jQuery.filter(seletor, correspondente);
		}

		se ( this.length > 1 ) {

			// Remover duplicados
			se ( !guaranteedUnique[ nome ] ) {
				jQuery.uniqueSort(matched);
			}

			// Ordem inversa para pais* e derivados anteriores
			se ( rparentsprev.test( nome ) ) {
				correspondido.reverse();
			}
		}

		retornar this.pushStack( correspondido );
	};
} );

// Converter opções formatadas como string em opções formatadas como objeto
função criarOpções( opções ) {
	var objeto = {};
	jQuery.each( options.match( rnothtmlwhite ) || [], function( _, flag ) {
		objeto[ flag ] = verdadeiro;
	} );
	retornar objeto;
}

/*
 * Crie uma lista de retorno de chamada usando os seguintes parâmetros:
 *
 * opções: uma lista opcional de opções separadas por espaço que irão alterar a forma como
 * a lista de retorno de chamada se comporta como um objeto de opção mais tradicional
 *
 * Por padrão, uma lista de retornos de chamada se comportará como uma lista de retornos de chamada de evento e pode ser
 * "Demitido" várias vezes.
 *
 * Opções possíveis:
 *
 * once: garantirá que a lista de retornos de chamada só possa ser acionada uma vez (como um Deferred)
 *
 * memória: manterá o controle dos valores anteriores e chamará qualquer função de retorno adicionada.
 * após a lista ter sido disparada imediatamente com o último item "memorizado"
 * valores (como um valor adiado)
 *
 * único: garantirá que um callback só possa ser adicionado uma vez (sem duplicatas na lista)
 *
 * stopOnFalse: interrompe as chamadas quando um retorno de chamada retorna falso
 *
 */
jQuery.Callbacks = function( opções ) {

	// Converter opções de formato de string para formato de objeto, se necessário
	// (verificamos primeiro no cache)
	opções = tipo de opções === "string" ?
		criarOpções( opções ) :
		jQuery.extend( {}, opções );

	var // Flag para saber se a lista está sendo executada no momento
		disparos,

		// Último valor de disparo para listas não esquecíveis
		memória,

		// Sinalizador para saber se a lista já foi executada
		despedido,

		// Sinalizador para impedir disparos
		trancado,

		// Lista de retorno de chamada real
		lista = [],

		// Fila de dados de execução para listas repetíveis
		fila = [],

		// Índice da função de retorno de chamada atualmente em execução (modificável por adição/remoção conforme necessário)
		firingIndex = -1,

		// Disparar funções de retorno de chamada
		fogo = função() {

			// Impor disparo único
			bloqueado = bloqueado || opções.uma vez;

			// Executar funções de retorno de chamada para todas as execuções pendentes,
			// Respeitando as substituições de firingIndex e as alterações em tempo de execução
			demitido = demitindo = verdadeiro;
			para ( ; queue.length; firingIndex = -1 ) {
				memória = fila.deslocar();
				enquanto ( ++disparandoIndex < list.length ) {

					// Executar função de retorno e verificar se houve término antecipado
					se ( lista[ índiceDeAcionamento ].apply( memória[ 0 ], memória[ 1 ] ) === falso &&
						options.stopOnFalse ) {

						// Ir para o final e descartar os dados para que .add não seja executado novamente.
						firingIndex = list.length;
						memória = falso;
					}
				}
			}

			// Ignore os dados se já tivermos terminado de usá-los
			se ( !options.memory ) {
				memória = falso;
			}

			disparo = falso;

			// Limpe tudo se já tivermos terminado de disparar definitivamente.
			se (trancado) {

				// Mantenha uma lista vazia se tivermos dados para futuras chamadas de adição.
				se (memória) {
					lista = [];

				Caso contrário, este objeto será gasto.
				} outro {
					lista = "";
				}
			}
		},

		// Objeto de retorno de chamada real
		self = {

			// Adicione uma função de retorno de chamada ou uma coleção de funções de retorno de chamada à lista
			adicionar: função() {
				se ( lista ) {

					// Se tivermos memória de uma execução anterior, devemos disparar após adicionar
					se (memória && !disparando) {
						firingIndex = list.length - 1;
						fila.push(memória);
					}

					( função adicionar( args ) {
						jQuery.each( args, function( _, arg ) {
							se ( typeof arg === "function" ) {
								se ( !options.unique || !self.has( arg ) ) {
									lista.push(arg);
								}
							} else if ( arg && arg.length && toType( arg ) !== "string" ) {

								// Inspecionar recursivamente
								adicionar( arg );
							}
						} );
					} )( argumentos );

					se (memória && !disparando) {
						fogo();
					}
				}
				devolva isto;
			},

			// Remover um callback da lista
			remover: função() {
				jQuery.each( argumentos, função( _, arg ) {
					var índice;
					enquanto ( ( índice = jQuery.inArray( arg, lista, índice ) ) > -1 ) {
						lista.splice( índice, 1 );

						// Gerenciar índices de disparo
						se (índice <= índiceDeDisparo) {
							índice de disparo--;
						}
					}
				} );
				devolva isto;
			},

			// Verifica se uma determinada função de retorno de chamada está na lista.
			// Se nenhum argumento for fornecido, retorna se a lista possui ou não funções de retorno de chamada (callbacks) associadas.
			tem: função( fn ) {
				retornar fn?
					jQuery.inArray( fn, list ) > -1 :
					list.length > 0;
			},

			// Remover todas as funções de retorno de chamada da lista
			vazio: função() {
				se ( lista ) {
					lista = [];
				}
				devolva isto;
			},

			// Desativar .fire e .add
			// Abortar quaisquer execuções em andamento/pendentes
			// Limpar todos os callbacks e valores
			desativar: função() {
				bloqueado = fila = [];
				lista = memória = "";
				devolva isto;
			},
			desativado: função() {
				retornar !lista;
			},

			// Desativar .fire
			// Desabilite também o método .add, a menos que haja memória disponível (pois não teria efeito).
			// Abortar quaisquer execuções pendentes
			bloqueio: função() {
				bloqueado = fila = [];
				se ( !memória && !disparando ) {
					lista = memória = "";
				}
				devolva isto;
			},
			bloqueado: função() {
				retornar !!bloqueado;
			},

			// Chama todas as funções de retorno de chamada com o contexto e os argumentos fornecidos
			fireWith: função(contexto, argumentos) {
				se ( !bloqueado ) {
					args = args || [];
					args = [ contexto, args.slice ? args.slice() : args ];
					fila.push( args );
					se ( !disparando ) {
						fogo();
					}
				}
				devolva isto;
			},

			// Chama todas as funções de retorno de chamada com os argumentos fornecidos
			fogo: função() {
				self.fireWith( this, arguments );
				devolva isto;
			},

			Para saber se as funções de retorno de chamada já foram invocadas pelo menos uma vez.
			disparado: função() {
				retornar !!disparado;
			}
		};

	retornar a si mesmo;
};

função Identidade( v ) {
	retornar v;
}
função Thrower( ex ) {
	lançar ex;
}

função adoptValue( valor, resolve, reject, noValue ) {
	var método;

	tentar {

		// Verifique primeiro o aspecto de promessa para priorizar o comportamento síncrono
		se ( valor && typeof( método = valor.promise ) === "função" ) {
			método.call( valor ).done( resolver ).fail( reject );

		// Outras variáveis
		} else if ( value && typeof( method = value.then ) === "function" ) {
			método.chamar( valor, resolver, rejeitar );

		// Outros não-thenáveis
		} outro {

			// Controle os argumentos de `resolve` permitindo que Array#slice converta o valor booleano `noValue` em inteiro:
			// * falso: [ valor ].slice( 0 ) => resolve( valor )
			// * verdadeiro: [ valor ].slice( 1 ) => resolve()
			resolve.apply( undefined, [ value ].slice( noValue ) );
		}

	// Para Promises/A+, converta exceções em rejeições
	Como o jQuery.when não desembrulha thenables, podemos ignorar as verificações extras que aparecem em
	// Deferred#then para suprimir condicionalmente a rejeição.
	} catch ( valor ) {
		rejeitar( valor );
	}
}

jQuery.extend( {

	Adiado: função( func ) {
		var tuplas = [

				// ação, adicionar ouvinte, retornos de chamada,
				// ... manipuladores .then, índice do argumento, [estado final]
				[ "notificar", "progresso", jQuery.Callbacks( "memória" ),
					jQuery.Callbacks("memória"), 2 ],
				[ "resolver", "concluído", jQuery.Callbacks( "uma vez na memória" ),
					jQuery.Callbacks("once memory"), 0, "resolved" ],
				[ "rejeitar", "falhar", jQuery.Callbacks( "uma vez na memória" ),
					jQuery.Callbacks("once memory"), 1, "rejected" ]
			],
			estado = "pendente",
			promessa = {
				estado: função() {
					retornar estado;
				},
				sempre: função() {
					deferred.done(argumentos).fail(argumentos);
					devolva isto;
				},
				captura: função( fn ) {
					retornar promessa.então(nulo, fn);
				},

				// Manter o pipe para compatibilidade com versões anteriores
				pipe: function( /* fnDone, fnFail, fnProgress */ ) {
					var fns = argumentos;

					return jQuery.Deferred( function( newDefer ) {
						jQuery.each( tuplas, function( _i, tupla ) {

							// Mapear tuplas (progresso, concluído, falha) para argumentos (concluído, falha, progresso)
							var fn = typeof fns[ tuple[ 4 ] ] === "function" &&
								fns[ tuple[ 4 ] ];

							// deferred.progress(function() { bind to newDefer or newDefer.notify })
							// deferred.done(function() { bind to newDefer or newDefer.resolve })
							// deferred.fail(function() { bind to newDefer or newDefer.reject })
							deferred[ tuple[ 1 ] ]( function() {
								var returned = fn && fn.apply( this, arguments );
								se (retornou && typeof returned.promise === "function" ) {
									retornou.promessa()
										.progresso( novoDefer.notificar )
										.concluído( novoDefer.resolve )
										.fail( newDefer.reject );
								} outro {
									novoDefer[ tupla[ 0 ] + "Com" ](
										esse,
										fn ? [ retornado ] : argumentos
									);
								}
							} );
						} );
						fns = nulo;
					} ).promessa();
				},
				então: função( onFulfilled, onRejected, onProgress ) {
					var maxDepth = 0;
					função resolver( profundidade, diferido, manipulador, especial ) {
						retornar função() {
							var that = isto,
								args = argumentos,
								mightThrow = função() {
									A variável foi retornada, então;

									// Suporte: Promessas/Seção A+ 2.3.3.3.3
									// https://promisesaplus.com/#point-59
									// Ignorar tentativas de resolução dupla
									se (profundidade < profundidademáxima) {
										retornar;
									}

									retornado = manipulador.aplicar(isso, args);

									// Suporte: Promessas/Seção A+ 2.3.1
									// https://promisesaplus.com/#point-48
									se (retornou === promessa adiada()) {
										throw new TypeError( "Em seguida, habilite a autorresolução" );
									}

									// Suporte: Promessas/Seções A+ 2.3.3.1, 3.5
									// https://promisesaplus.com/#point-54
									// https://promisesaplus.com/#point-75
									// Recuperar `then` apenas uma vez
									então = retornado &&

										// Suporte: Promessas/Seção A+ 2.3.4
										// https://promisesaplus.com/#point-64
										// Verificar somente objetos e funções quanto à possibilidade de execução simultânea
										( typeof retornado === "objeto" ||
											typeof retornado === "função" ) &&
										retornou.então;

									// Lidar com um thenable retornado
									se ( typeof então === "função" ) {

										// Processadores especiais (notificar) apenas aguardam a resolução
										se (especial) {
											então.chamar(
												retornou,
												resolver( maxDepth, adiado, Identidade, especial ),
												resolver( maxDepth, adiado, Lançador, especial )
											);

										// Os processadores normais (resolve) também se conectam ao progresso
										} outro {

											// ...e desconsidere valores de resolução mais antigos
											profundidademáx++;

											então.chamar(
												retornou,
												resolver( maxDepth, adiado, Identidade, especial ),
												resolver( maxDepth, adiado, Lançador, especial ),
												resolver( maxDepth, adiado, Identidade,
													notificar adiado )
											);
										}

									// Lidar com todos os outros valores retornados
									} outro {

										// Somente manipuladores substitutos passam o contexto
										// e múltiplos valores (comportamento não especificado)
										se ( manipulador !== Identidade ) {
											isso = indefinido;
											args = [ retornado ];
										}

										// Processar o(s) valor(es)
										// O processo padrão é resolver
										( especial || adiado.resolveWith )( isso, args );
									}
								},

								// Somente processadores normais (resolve) capturam e rejeitam exceções
								processo = especial?
									mightThrow:
									função() {
										tentar {
											podeLançar();
										} catch ( e ) {

											se ( jQuery.Deferred.exceptionHook ) {
												jQuery.Deferred.exceptionHook( e,
													process.error );
											}

											// Suporte: Promessas/Seção A+ 2.3.3.3.4.1
											// https://promisesaplus.com/#point-61
											// Ignorar exceções pós-resolução
											se (profundidade + 1 >= profundidademáxima) {

												// Somente manipuladores substitutos passam o contexto
												// e múltiplos valores (comportamento não especificado)
												se ( manipulador !== Lançador ) {
													isso = indefinido;
													args = [ e ];
												}

												deferred.rejectWith( isso, args );
											}
										}
									};

							// Suporte: Promessas/Seção A+ 2.3.3.3.1
							// https://promisesaplus.com/#point-57
							// Resolva as promessas imediatamente para evitar rejeições falsas.
							// erros subsequentes
							se (profundidade) {
								processo();
							} outro {

								// Chama um gancho opcional para registrar o erro, em caso de exceção.
								// pois, caso contrário, se perde quando a execução se torna assíncrona
								se ( jQuery.Deferred.getErrorHook ) {
									process.error = jQuery.Deferred.getErrorHook();
								}
								janela.setTimeout(processo);
							}
						};
					}

					return jQuery.Deferred( function( newDefer ) {

						// progress_handlers.add( ... )
						tuplas[ 0 ][ 3 ].adicionar(
							resolver(
								0,
								novoAdiar,
								typeof onProgress === "function" ?
									Em andamento:
									Identidade,
								novoDefer.notifyWith
							)
						);

						// held_handlers.add( ... )
						tuplas[ 1 ][ 3 ].adicionar(
							resolver(
								0,
								novoAdiar,
								typeof onFulfilled === "função" ?
									onFulfilled:
									Identidade
							)
						);

						// rejected_handlers.add( ... )
						tuplas[ 2 ][ 3 ].adicionar(
							resolver(
								0,
								novoAdiar,
								typeof onRejected === "function" ?
									onRejected:
									Atirador
							)
						);
					} ).promessa();
				},

				// Obtenha uma promessa para este evento adiado
				// Se obj for fornecido, o aspecto de promessa é adicionado ao objeto
				promessa: função( obj ) {
					return obj != null ? jQuery.extend( obj, promise ) : promise;
				}
			},
			diferido = {};

		// Adicionar métodos específicos para listas
		jQuery.each( tuplas, function( i, tupla ) {
			var lista = tupla[ 2 ],
				stateString = tupla[ 5 ];

			// promise.progress = lista.adicionar
			// promise.done = lista.adicionar
			// promise.fail = list.add
			promessa[ tupla[ 1 ] ] = lista.adicionar;

			// Lidar com o estado
			se ( stateString ) {
				lista.adicionar(
					função() {

						// estado = "resolvido" (ou seja, cumprido)
						// estado = "rejeitado"
						estado = string de estado;
					},

					// rejected_callbacks.disable
					// completed_callbacks.disable
					tuplas[ 3 - i ][ 2 ].desativar,

					// rejected_handlers.disable
					// held_handlers.disable
					tuplas[ 3 - i ][ 3 ].desativar,

					// progress_callbacks.lock
					tuplas[ 0 ][ 2 ].bloqueio,

					// progress_handlers.lock
					tuplas[ 0 ][ 3 ].lock
				);
			}

			// progress_handlers.fire
			// held_handlers.fire
			// rejected_handlers.fire
			lista.adicionar( tupla[ 3 ].fogo );

			// deferred.notify = function() { deferred.notifyWith(...) }
			// deferred.resolve = function() { deferred.resolveWith(...) }
			// deferred.reject = function() { deferred.rejectWith(...) }
			deferred[ tuple[ 0 ] ] = function() {
				deferred[ tuple[ 0 ] + "With" ]( this === deferred ? undefined : this, arguments );
				devolva isto;
			};

			// deferred.notifyWith = list.fireWith
			// deferred.resolveWith = list.fireWith
			// deferred.rejectWith = list.fireWith
			deferred[ tuple[ 0 ] + "With" ] = list.fireWith;
		} );

		// Transforme o objeto adiado em uma promessa
		promessa.promessa(adiada);

		// Chama a função fornecida, se houver.
		se (func) {
			func.call( adiado, adiado );
		}

		// Tudo pronto!
		retorno adiado;
	},

	// Auxiliar diferido
	quando: função( valor único ) {
		var

			// contagem de subordinados não concluídos
			restante = argumentos.comprimento,

			// contagem de argumentos não processados
			i = restante,

			// dados de cumprimento subordinados
			resolveContexts = Array( i ),
			resolveValues ​​= slice.call(argumentos),

			// o principal Diferido
			primário = jQuery.Deferred(),

			// fábrica de retorno de chamada subordinada
			updateFunc = função( i ) {
				função de retorno (valor) {
					resolveContexts[ i ] = isto;
					resolveValues[ i ] = arguments.length > 1 ? slice.call( arguments ) : value;
					se ( !( --restante ) ) {
						primary.resolveWith( resolveContexts, resolveValues ​​);
					}
				};
			};

		// Argumentos únicos e vazios são adotados da mesma forma que Promise.resolve
		se (restante <= 1) {
			adoptValue( singleValue, primary.done( updateFunc( i ) ).resolve, primary.reject,
				!restante );

			// Use .then() para desembrulhar thenables secundários (cf. gh-3000)
			se ( primary.state() === "pendente" ||
				typeof( resolveValues[ i ] && resolveValues[ i ].then ) === "function" ) {

				retornar primário.então();
			}
		}

		// Vários argumentos são agregados como elementos de array Promise.all
		enquanto ( i-- ) {
			adoptValue( resolveValues[ i ], updateFunc( i ), primary.reject );
		}

		retornar primária.promise();
	}
} );

// Geralmente, isso indica um erro de programação durante o desenvolvimento.
// Avise sobre eles o mais rápido possível, em vez de ignorá-los por padrão.
var rerrorNames = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;

// Se `jQuery.Deferred.getErrorHook` estiver definido, `asyncError` será um erro.
// Capturado antes da barreira assíncrona para obter a causa original do erro
// que de outra forma poderia ficar oculto.
jQuery.Deferred.exceptionHook = function( error, asyncError ) {

	se ( erro && errorNames.test( error.name ) ) {
		janela.console.aviso(
			"Exceção jQuery.Deferred",
			erro,
			asyncError
		);
	}
};

jQuery.readyException = function( error ) {
	janela.setTimeout( função() {
		lançar erro;
	} );
};

// O recurso de adiamento é usado quando o DOM está pronto
var readyList = jQuery.Deferred();

jQuery.fn.ready = function( fn ) {

	lista pronta
		.então( fn )

		// Envolva jQuery.readyException em uma função para que a pesquisa
		// ocorre no momento do tratamento de erros em vez do retorno de chamada
		// inscrição.
		.catch( function( error ) {
			jQuery.readyException( erro );
		} );

	devolva isto;
};

jQuery.extend( {

	// O DOM está pronto para ser usado? Defina como verdadeiro assim que isso ocorrer.
	estáPronto: falso,

	// Um ​​contador para controlar quantos itens esperar antes de
	// O evento de prontidão é disparado. Veja trac-6781
	prontoAguardando: 1,

	// Lidar com a situação em que o DOM estiver pronto
	pronto: função( esperar ) {

		// Abortar se houver pendências ou se já estivermos prontos
		se (wait === true ? --jQuery.readyWait : jQuery.isReady ) {
			retornar;
		}

		// Lembre-se de que o DOM está pronto
		jQuery.isReady = true;

		// Se um evento DOM Ready normal for disparado, decremente e aguarde, se necessário.
		se (wait !== true && --jQuery.readyWait > 0) {
			retornar;
		}

		// Se houver funções vinculadas, execute-as.
		readyList.resolveWith( document$1, [ jQuery ] );
	}
} );

jQuery.ready.then = readyList.then;

// O manipulador de eventos de pronto e o método de autolimpeza
função concluída() {
	document$1.removeEventListener("DOMContentLoaded", concluído);
	window.removeEventListener("load", concluído);
	jQuery.ready();
}

// Captura os casos em que $(document).ready() é chamado
// após o evento do navegador já ter ocorrido.
se ( document$1.readyState !== "loading" ) {

	// Trate isso de forma assíncrona para permitir que os scripts tenham a oportunidade de atrasar a preparação.
	janela.setTimeout( jQuery.ready );

} outro {

	// Use o prático retorno de chamada de evento
	document$1.addEventListener("DOMContentLoaded", concluído);

	// Uma alternativa ao window.onload, que sempre funcionará
	janela.adicionarOuvinteDeEvento("carregar", concluído);
}

// Corresponde à string com hífen para a conversão em camelídeo.
var rdashAlpha = /-([az])/g;

// Usado por camelCase como função de retorno para replace()
função fcamelCase( _all, letra ) {
	retornar letra.paraMaiúsculas();
}

// Converter texto com traços para camelCase
função camelCase( string ) {
	retornar string.replace( rdashAlpha, fcamelCase );
}

/**
 * Determina se um objeto pode ter dados
 */
função aceitarDados( proprietário ) {

	// Aceita apenas:
	// - Nó
	// - Node.ELEMENT_NODE
	// - Nó.DOCUMENT_NODE
	// - Objeto
	// - Qualquer
	retornar owner.nodeType === 1 || owner.nodeType === 9 || !( +owner.nodeType );
}

função Dados() {
	this.expando = jQuery.expando + Data.uid++;
}

Dados.uid = 1;

Data.prototype = {

	cache: função( proprietário ) {

		// Verifica se o objeto proprietário já possui um cache
		var valor = proprietário[ this.expando ];

		Caso contrário, crie um.
		se ( !valor ) {
			valor = Objeto.criar(nulo);

			// Podemos aceitar dados para nós que não sejam elementos em navegadores modernos.
			// mas não deveríamos, veja trac-8335.
			// Sempre retorna um objeto vazio.
			se ( aceitarDados( proprietário ) ) {

				// Se for um nó com pouca probabilidade de ser transformado em string ou iterado
				// usar atribuição simples
				se ( proprietário.tipo de nó ) {
					proprietário[ this.expando ] = valor;

				Caso contrário, proteja-o em uma propriedade não enumerável.
				// O parâmetro "configurável" deve ser verdadeiro para permitir que a propriedade seja
				// excluído quando os dados são removidos
				} outro {
					Objeto.definirPropriedade( proprietário, this.expando, {
						valor: valor,
						configurável: verdadeiro
					} );
				}
			}
		}

		valor de retorno;
	},
	conjunto: função( proprietário, dados, valor ) {
		var prop,
			cache = this.cache( proprietário );

		// Identificador: [ proprietário, chave, valor ] argumentos
		// Sempre use a chave camelCase (gh-2257)
		se ( tipo de dados === "string" ) {
			cache[ camelCase( dados ) ] = valor;

		// Identificador: [ proprietário, { propriedades } ] argumentos
		} outro {

			// Copie as propriedades uma a uma para o objeto de cache
			para (prop em dados) {
				cache[ camelCase( prop ) ] = data[ prop ];
			}
		}
		valor de retorno;
	},
	obter: função( proprietário, chave ) {
		chave de retorno === indefinida?
			this.cache( proprietário ) :

			// Sempre use a chave camelCase (gh-2257)
			proprietário[ this.expando ] && proprietário[ this.expando ][ camelCase( chave ) ];
	},
	acesso: função( proprietário, chave, valor ) {

		// Nos casos em que:
		//
		// 1. Nenhuma chave foi especificada
		// 2. Uma chave de string foi especificada, mas nenhum valor foi fornecido.
		//
		// Pegue o caminho de "leitura" e permita que o método get determine
		// Qual valor retornar, respectivamente:
		//
		// 1. O objeto de cache inteiro
		// 2. Os dados armazenados na chave
		//
		se ( chave === indefinido ||
				( ( chave && typeof chave === "string" ) && valor === undefined ) ) {

			retornar this.get( proprietário, chave );
		}

		// Quando a chave não é uma string, ou é uma chave e um valor.
		// são especificados, definidos ou estendidos (objetos existentes) com:
		//
		// 1. Um objeto de propriedades
		// 2. Uma chave e um valor
		//
		this.set( proprietário, chave, valor );

		// Visto que o caminho "definido" pode ter dois pontos de entrada possíveis
		// retorna os dados esperados com base no caminho percorrido[*]
		valor de retorno !== undefined ? valor : chave;
	},
	remover: função( proprietário, chave ) {
		var i,
			cache = proprietário[ this.expando ];

		se ( cache === undefined ) {
			retornar;
		}

		se (chave !== indefinida) {

			// Suporta array ou string de chaves separadas por espaço
			se ( Array.isArray( chave ) ) {

				// Se a chave for uma matriz de chaves...
				// Sempre definimos chaves em camelCase, então remova isso.
				chave = chave.map( camelCase );
			} outro {
				chave = camelCase( chave );

				// Se existir uma chave com espaços, use-a.
				Caso contrário, crie uma matriz correspondendo a caracteres que não sejam espaços em branco.
				chave = chave no cache?
					[ chave ] :
					( key.match( rnothtmlwhite ) || [] );
			}

			i = key.length;

			enquanto ( i-- ) {
				excluir cache[ chave[ i ] ];
			}
		}

		// Remova o expando se não houver mais dados
		if ( key === undefined || jQuery.isEmptyObject( cache ) ) {

			// Compatível com Chrome <=35 - 45+
			O desempenho do Webkit e do Blink é afetado ao excluir propriedades.
			// de nós DOM, portanto, defina como indefinido em vez de
			// https://bugs.chromium.org/p/chromium/issues/detail?id=378607 (bug restrito)
			se ( proprietário.tipo de nó ) {
				proprietário[ this.expando ] = indefinido;
			} outro {
				excluir proprietário[ this.expando ];
			}
		}
	},
	hasData: função( proprietário ) {
		var cache = owner[ this.expando ];
		return cache !== undefined && !jQuery.isEmptyObject( cache );
	}
};

var dataPriv = novo Data();

var dataUser = novo Data();

// Resumo da Implementação
//
// 1. Impor a superfície da API e a compatibilidade semântica com a ramificação 1.9.x
// 2. Melhore a capacidade de manutenção do módulo reduzindo o armazenamento.
// caminhos para um único mecanismo.
// 3. Utilize o mesmo mecanismo único para dar suporte a dados "privados" e "do usuário".
// 4. _Nunca_ exponha dados "privados" ao código do usuário (TODO: Remover _data, _removeData)
// 5. Evite expor detalhes de implementação em objetos do usuário (ex.: propriedades expandidas)
// 6. Fornecer um caminho claro para a atualização da implementação para WeakMap em 2014

var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
	rmultiDash = /[AZ]/g;

função obterDados( dados ) {
	se (dados === "verdadeiro") {
		retornar verdadeiro;
	}

	se (dados === "falso") {
		retornar falso;
	}

	se (dados === "nulo") {
		retornar nulo;
	}

	// Converta para um número somente se isso não alterar a string
	se (dados === +dados + "") {
		retornar +dados;
	}

	se ( rbrace.test( dados ) ) {
		retornar JSON.parse( dados );
	}

	retornar dados;
}

função dataAttr( elem, key, data ) {
	nome da variável;

	// Se nada for encontrado internamente, tente buscar algo.
	// dados do atributo data-* do HTML5
	se (dados === indefinidos && elem.nodeType === 1) {
		nome = "data-" + key.replace( rmultiDash, "-$&" ).toLowerCase();
		dados = elem.getAttribute(nome);

		se ( tipo de dados === "string" ) {
			tentar {
				dados = obterDados( dados );
			} catch ( e ) {}

			// Certifique-se de que definimos os dados para que não sejam alterados posteriormente.
			dataUser.set(elem, key, data);
		} outro {
			dados = indefinido;
		}
	}
	retornar dados;
}

jQuery.extend( {
	hasData: função( elem ) {
		retornar dataUser.hasData(elem) || dataPriv.hasData(elem);
	},

	dados: função( elemento, nome, dados ) {
		retornar dataUser.access( elem, nome, dados );
	},

	removerDados: função( elemento, nome ) {
		dataUser.remove(elem, name);
	},

	// TODO: Agora que todas as chamadas para _data e _removeData foram substituídas
	// Com chamadas diretas a métodos dataPriv, estes podem ser considerados obsoletos.
	_data: função( elemento, nome, dados ) {
		retornar dataPriv.access( elem, nome, dados );
	},

	_removeData: função( elemento, nome ) {
		dataPriv.remove(elem, nome);
	}
} );

jQuery.fn.extend( {
	dados: função( chave, valor ) {
		var i, nome, dados,
			elem = this[ 0 ],
			attrs = elem && elem.attributes;

		// Obtém todos os valores
		se ( chave === indefinido ) {
			se ( this.length ) {
				dados = dataUser.get(elem);

				if (elem.nodeType === 1 && !dataPriv.get( elem, "hasDataAttrs" ) ) {
					i = attrs.length;
					enquanto ( i-- ) {

						// Suporte: IE 11+
						// Os elementos attrs podem ser nulos (trac-14894)
						se ( attrs[ i ] ) {
							nome = attrs[ i ].nome;
							se ( nome.indexOf( "data-" ) === 0 ) {
								nome = camelCase( nome.slice( 5 ) );
								dataAttr( elem, nome, data[ nome ] );
							}
						}
					}
					dataPriv.set(elem, "hasDataAttrs", true);
				}
			}

			retornar dados;
		}

		// Define vários valores
		se ( typeof key === "object" ) {
			retorne this.each( function() {
				dataUser.set( this, key );
			} );
		}

		retornar acesso( isto, função( valor ) {
			dados variáveis;

			// O objeto jQuery que fez a chamada (elemento matches) não está vazio
			// (e, portanto, um elemento aparece em this[ 0 ]) e o
			// O parâmetro `value` não estava indefinido. Um objeto jQuery vazio.
			// resultará em `undefined` para elem = this[ 0 ], o que irá
			// Lança uma exceção se houver uma tentativa de leitura do cache de dados.
			se (elemento && valor === indefinido) {

				// Tentativa de obter dados do cache
				// A chave sempre estará em camelCase nos dados.
				dados = dataUser.get(elemento, chave);
				se (dados !== indefinidos) {
					retornar dados;
				}

				// Tentativa de "descobrir" os dados em
				// Atributos de dados personalizados HTML5 -*
				dados = dataAttr( elemento, chave );
				se (dados !== indefinidos) {
					retornar dados;
				}

				Nós nos esforçamos muito, mas os dados não existem.
				retornar;
			}

			// Defina os dados...
			this.each( function() {

				// Sempre armazenamos a chave em camelCase
				dataUser.set( this, key, value );
			} );
		}, null, value, arguments.length > 1, null, true );
	},

	removerDados: função( chave ) {
		retorne this.each( function() {
			dataUser.remove( this, key );
		} );
	}
} );

jQuery.extend( {
	fila: função( elemento, tipo, dados ) {
		var fila;

		se (elem) {
			tipo = ( tipo || "fx" ) + "fila";
			fila = dataPriv.get(elemento, tipo);

			// Acelere a remoção da fila, saindo rapidamente se for apenas uma pesquisa.
			se (dados) {
				if ( !queue || Array.isArray( data ) ) {
					fila = dataPriv.set( elem, type, jQuery.makeArray( data ) );
				} outro {
					fila.push( dados );
				}
			}
			retornar fila || [];
		}
	},

	remover da fila: função( elemento, tipo ) {
		tipo = tipo || "fx";

		var fila = jQuery.queue(elemento, tipo),
			comprimentoInicial = comprimentoDaFila,
			fn = fila.shift(),
			hooks = jQuery._queueHooks(elem, type),
			próximo = função() {
				jQuery.dequeue(elem, type);
			};

		// Se a fila de funções for esvaziada, remova sempre o indicador de progresso.
		se (fn === "em andamento") {
			fn = fila.shift();
			comprimento_inicial--;
		}

		se ( fn ) {

			// Adicione um indicador de progresso para evitar que a fila de funções seja
			// removido automaticamente da fila
			se ( tipo === "fx" ) {
				fila.unshift("em andamento");
			}

			// Limpar a última função de parada da fila
			excluir hooks.stop;
			fn.call(elem, next, hooks);
		}

		se ( !startLength && hooks ) {
			hooks.empty.fire();
		}
	},

	// Não público - gera um objeto queueHooks ou retorna o atual
	_queueHooks: função( elem, tipo ) {
		var chave = tipo + "queueHooks";
		retornar dataPriv.get(elem, chave) || dataPriv.set(elem, chave, {
			vazio: jQuery.Callbacks("once memory").add(function() {
				dataPriv.remove( elem, [ type + "queue", key ] );
			} )
		} );
	}
} );

jQuery.fn.extend( {
	fila: função( tipo, dados ) {
		var setter = 2;

		se ( typeof tipo !== "string" ) {
			dados = tipo;
			tipo = "fx";
			setter--;
		}

		se (argumentos.comprimento < setter) {
			return jQuery.queue( this[ 0 ], type );
		}

		dados de retorno === indefinidos?
			esse :
			this.each( function() {
				var fila = jQuery.queue( this, tipo, dados );

				// Garanta que haja ganchos para esta fila
				jQuery._queueHooks( this, type );

				se ( tipo === "fx" && fila[ 0 ] !== "em andamento" ) {
					jQuery.dequeue( this, type );
				}
			} );
	},
	remover da fila: função( tipo ) {
		retorne this.each( function() {
			jQuery.dequeue( this, type );
		} );
	},
	clearQueue: função( tipo ) {
		retornar this.queue( tipo || "fx", [] );
	},

	// Obtenha a resolução de uma promessa quando filas de um determinado tipo
	// estão vazios (fx é o tipo por padrão)
	promessa: função( tipo, obj ) {
		var tmp,
			contagem = 1,
			defer = jQuery.Deferred(),
			elementos = isto,
			i = este.comprimento,
			resolver = função() {
				se ( !( --contagem ) ) {
					defer.resolveWith( elementos, [ elementos ] );
				}
			};

		se ( typeof tipo !== "string" ) {
			obj = tipo;
			tipo = indefinido;
		}
		tipo = tipo || "fx";

		enquanto ( i-- ) {
			tmp = dataPriv.get( elements[ i ], type + "queueHooks" );
			se ( tmp && tmp.vazio ) {
				contagem++;
				tmp.empty.add( resolve );
			}
		}
		resolver();
		retornar defer.promise( obj );
	}
} );

var pnum = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source;

var rcssNum = new RegExp( "^(?:([+-])=|)(" + pnum + ")([az%]*)$", "i" );

var cssExpand = [ "Superior", "Direita", "Inferior", "Esquerda" ];

// isHiddenWithinTree informa se um elemento possui um estilo de exibição diferente de "none" (inline e/ou
// através da cascata CSS), o que é útil para decidir se deve ou não torná-lo visível.
// Difere do seletor :hidden (jQuery.expr.pseudos.hidden) em dois aspectos importantes:
// * Um ancestral oculto não força um elemento a ser classificado como oculto.
// * Estar desconectado do documento não força um elemento a ser classificado como oculto.
Essas diferenças melhoram o comportamento de .toggle() e outros métodos quando aplicados a elementos que são
// destacado ou contido em ancestrais ocultos (gh-2404, gh-2863).
função isHiddenWithinTree( elem, el ) {

	// isHiddenWithinTree pode ser chamado a partir da função jQuery#filter;
	// Nesse caso, o elemento será o segundo argumento
	elem = el || elem;

	// O estilo embutido prevalece sobre tudo
	retornar elem.style.display === "nenhum" ||
		elem.style.display === "" &&
		jQuery.css( elem, "display" ) === "none";
}

var ralphaStart = /^[az]/,

	// A expressão regular visualizada:
	//
	// /----------\
	// | | /-------\
	// | / Topo \ | | |
	// /--- Borda ---+-| Direita |-+---+- Largura -+---\
	// | | Parte inferior | |
	// | \ Esquerda / |
	// | |
	// | /----------\ |
	// | /-------------\ | | |- FIM
	// | | | | / Topo \ | |
	// | | / Margem \ | | | Direita | | |
	// |---------+-| |-+---+-| Parte inferior |-+----|
	// | \ Preenchimento / \ Esquerda / |
	// INÍCIO -| |
	// | /---------\ |
	// | | | |
	// | | / Mín. \ | / Largura \ |
	// \--------------+-| |-+---| |---/
	// \ Máx / \ Altura /
	rautoPx = /^(?:Border(?:Top|Right|Bottom|Left)?(?:Width|)|(?:Margin|Padding)?(?:Top|Right|Bottom|Left)?|(?:Min|Max)?(?:Width|Height))$/;

função isAutoPx( prop ) {

	// O primeiro teste serve para garantir que:
	// 1. A propriedade começa com uma letra minúscula (pois a convertemos para maiúscula na segunda expressão regular).
	// 2. A propriedade não está vazia.
	retornar ralphaStart.test( prop ) &&
		rautoPx.test( prop[ 0 ].toUpperCase() + prop.slice( 1 ) );
}

função ajustarCSS( elemento, prop, partesValor, tween ) {
	variável ajustada, escala,
		maxIterations = 20,
		currentValue = tween ?
			função() {
				retornar tween.cur();
			} :
			função() {
				retornar jQuery.css( elem, prop, "" );
			},
		inicial = valorAtual(),
		unidade = partesValor && partesValor[3] || (isAutoPx(prop) ? "px" : ""),

		// O cálculo do valor inicial é necessário para possíveis incompatibilidades de unidades.
		inicialInUnit = elem.nodeType &&
			( !isAutoPx( prop ) || unit !== "px" && +initial ) &&
			rcssNum.exec( jQuery.css( elem, prop ) );

	se ( inicialNaUnidade && inicialNaUnidade[ 3 ] !== unidade ) {

		// Suporte: Firefox <=54 - 66+
		// Reduza pela metade o valor alvo da iteração para evitar interferência dos limites superiores do CSS (gh-2144)
		inicial = inicial / 2;

		// Unidades de confiança relatadas por jQuery.css
		unidade = unidade || inicialInUnit[3];

		// Aproximar iterativamente a partir de um ponto inicial diferente de zero
		inicialNaUnidade = +inicial || 1;

		enquanto ( maxIterações-- ) {

			// Avaliar e atualizar nossa melhor estimativa (dobrando as estimativas que resultam em zero).
			// Finalizar se a escala for igual ou superior a 1 (tornando o produto antigo*novo não positivo).
			jQuery.style(elem, prop, inicialInUnit + unidade);
			se ( ( 1 - escala ) * ( 1 - ( escala = valorAtual() / inicial || 0,5 ) ) <= 0 ) {
				maxIterations = 0;
			}
			inicialNaUnidade = inicialNaUnidade / escala;

		}

		inicialInUnit = inicialInUnit * 2;
		jQuery.style(elem, prop, inicialInUnit + unidade);

		// Certifique-se de atualizar as propriedades de interpolação posteriormente
		partesValores = partesValores || [];
	}

	se ( partesValor ) {
		inicialNaUnidade = +inicialNaUnidade || +inicial || 0;

		// Aplicar deslocamento relativo (+=/-=) se especificado
		ajustado = partesValor[ 1 ] ?
			initialInUnit + ( valueParts[ 1 ] + 1 ) * valueParts[ 2 ] :
			+valueParts[ 2 ];
		se (intervalo) {
			tween.unit = unidade;
			tween.start = initialInUnit;
			tween.end = ajustado;
		}
	}
	retorno ajustado;
}

// Corresponde à string com hífen para a conversão em camelídeo.
var rmsPrefix = /^-ms-/;

// Converter caracteres travessões para camelCase, lidar com prefixos de fornecedores.
// Usado pelos módulos de CSS e efeitos.
// Suporte: IE <= 9 - 11+
// A Microsoft se esqueceu de incluir o prefixo do fornecedor (trac-9572)
função cssCamelCase( string ) {
	retornar camelCase( string.replace( rmsPrefix, "ms-" ) );
}

var defaultDisplayMap = {};

função getDefaultDisplay( elem ) {
	var temp,
		doc = elem.ownerDocument,
		nodeName = elem.nodeName,
		exibir = defaultDisplayMap[ nodeName ];

	se ( exibir ) {
		retornar exibição;
	}

	temp = doc.body.appendChild( doc.createElement( nodeName ) );
	display = jQuery.css( temp, "display" );

	temp.parentNode.removeChild(temp);

	se ( exibir === "nenhum" ) {
		exibir = "bloco";
	}
	defaultDisplayMap[ nodeName ] = exibir;

	retornar exibição;
}

função mostrarOcultar( elementos, mostrar ) {
	var display, elem,
		valores = [],
		índice = 0,
		comprimento = elementos.comprimento;

	// Determinar o novo valor de exibição para os elementos que precisam ser alterados
	para ( ; índice < comprimento; índice++ ) {
		elem = elementos[ índice ];
		se ( !elem.style ) {
			continuar;
		}

		exibir = elem.estilo.exibir;
		se (mostrar) {

			// Como forçamos a visibilidade de elementos ocultos em cascata, isso ocorre de forma imediata (e lenta)
			// A verificação é necessária neste primeiro loop, a menos que tenhamos um valor de exibição não vazio (ou
			// embutido ou prestes a ser restaurado)
			se ( exibir === "nenhum" ) {
				valores[índice] = dataPriv.get(elem, "display" ) || nulo;
				se ( !valores[ índice ] ) {
					elem.style.display = "";
				}
			}
			se (elem.style.display === "" && isHiddenWithinTree(elem)) {
				valores[ índice ] = obterExibiçãoPadrão( elemento );
			}
		} outro {
			se ( exibir !== "nenhum" ) {
				valores[ índice ] = "nenhum";

				// Lembre-se do que estamos sobrescrevendo
				dataPriv.set(elem, "exibir", exibir);
			}
		}
	}

	// Defina a exibição dos elementos em um segundo loop para evitar o reflow constante
	para ( índice = 0; índice < comprimento; índice++ ) {
		se ( valores[ índice ] != nulo ) {
			elementos[ índice ].estilo.exibir = valores[ índice ];
		}
	}

	retornar elementos;
}

jQuery.fn.extend( {
	mostrar: função() {
		retornar showHide( this, true );
	},
	ocultar: função() {
		retornar mostrarOcultar( isto );
	},
	alternar: função( estado ) {
		se ( typeof estado === "booleano" ) {
			retornar estado ? this.show() : this.hide();
		}

		retorne this.each( function() {
			se ( estáOcultoNaÁrvore( isto ) ) {
				jQuery( this ).show();
			} outro {
				jQuery( this ).hide();
			}
		} );
	}
} );

var estáAnexado = função( elem ) {
		retornar jQuery.contains( elem.ownerDocument, elem ) ||
			elem.getRootNode(composed) === elem.ownerDocument;
	},
	composto = { composto: verdadeiro };

// Compatível com: IE 9 - 11+
// Verificar anexos através de limites do DOM sombra quando possível (gh-3504).
// Fornece uma alternativa para navegadores sem suporte ao Shadow DOM v1.
if (!documentElement$1.getRootNode) {
	estáAnexado = função( elemento ) {
		return jQuery.contains( elem.ownerDocument, elem );
	};
}

// rtagName captura o nome da primeira tag de abertura em uma string HTML
// https://html.spec.whatwg.org/multipage/syntax.html#tag-open-state
// https://html.spec.whatwg.org/multipage/syntax.html#tag-name-state
var rtagName = /<([az][^\/\0>\x20\t\r\n\f]*)/i;

var wrapMap = {

	// Os elementos da tabela precisam ser envolvidos por `<table>` ou serão exibidos incorretamente.
	// são removidos, restando apenas o conteúdo original quando colocados em uma div.
	// Os analisadores XHTML não inserem elementos magicamente no
	// Da mesma forma que os analisadores de sopa de tags, portanto não podemos encurtar
	// Isso é feito omitindo <tbody> ou outros elementos obrigatórios.
	cabeçalho: [ "tabela" ],
	col: [ "colgroup", "table" ],
	tr: [ "tbody", "table" ],
	td: [ "tr", "tbody", "table" ]
};

wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;

função obterTodos( contexto, tag ) {

	// Suporte: IE <= 9 - 11+
	// Use typeof para evitar a invocação de métodos sem argumentos em objetos do host (trac-15151)
	var ret;

	if ( typeof context.getElementsByTagName !== "undefined" ) {

		// Use slice para capturar a coleção ativa do gEBTN
		ret = arr.slice.call( context.getElementsByTagName( tag || "*" ) );

	} else if ( typeof context.querySelectorAll !== "undefined" ) {
		ret = context.querySelectorAll( tag || "*" );

	} outro {
		ret = [];
	}

	se ( tag === undefined || tag && nodeName( context, tag ) ) {
		return jQuery.merge( [ context ], ret );
	}

	retornar ret;
}

var rscriptType = /^$|^module$|\/(?:java|ecma)script/i;

// Marcar scripts como já avaliados
função setGlobalEval( elementos, refElements ) {
	var i = 0,
		l = elems.length;

	para ( ; i < l; i++ ) {
		dataPriv.set(
			elementos[ i ],
			"globalEval",
			!refElementos || dataPriv.get(refElements[i], "globalEval" )
		);
	}
}

var rhtml = /<|&#?\w+;/;

função buildFragment( elementos, contexto, scripts, seleção, ignorado ) {
	var elem, tmp, tag, wrap, attached, j,
		fragmento = contexto.criarFragmentoDocumento(),
		nós = [],
		i = 0,
		l = elems.length;

	para ( ; i < l; i++ ) {
		elem = elems[ i ];

		se (elem || elem === 0) {

			// Adicionar nós diretamente
			se ( toType( elem ) === "object" && ( elem.nodeType || isArrayLike( elem ) ) ) {
				jQuery.merge(nodes, elem.nodeType ? [elem] : elem);

			// Converter conteúdo não HTML em um nó de texto
			} else if ( !rhtml.test( elem ) ) {
				nodes.push( context.createTextNode( elem ) );

			// Converter HTML em nós DOM
			} outro {
				tmp = tmp || fragment.appendChild( context.createElement( "div" ) );

				// Desserializar uma representação padrão
				tag = ( rtagName.exec( elem ) || [ "", "" ] )[ 1 ].toLowerCase();
				wrap = wrapMap[ tag ] || arr;

				// Criar contêineres e descer neles.
				j = wrap.length;
				enquanto ( --j > -1 ) {
					tmp = tmp.appendChild( context.createElement( wrap[ j ] ) );
				}

				tmp.innerHTML = jQuery.htmlPrefilter( elem );

				jQuery.merge(nodes, tmp.childNodes);

				// Lembre-se do contêiner de nível superior
				tmp = fragment.firstChild;

				// Garantir que os nós criados sejam órfãos (trac-12392)
				tmp.textContent = "";
			}
		}
	}

	// Remover o elemento envolvente do fragmento
	fragment.textContent = "";

	i = 0;
	enquanto ( ( elem = nós[ i++ ] ) ) {

		// Ignorar elementos já presentes na coleção de contexto (trac-4087)
		se ( seleção && jQuery.inArray( elemento, seleção ) > -1 ) {
			se (ignorado) {
				ignorado.push(elemento);
			}
			continuar;
		}

		anexado = estáAnexado( elemento );

		// Adicionar ao fragmento
		tmp = getAll( fragment.appendChild( elem ), "script" );

		// Preservar o histórico de avaliação do script
		se (anexado) {
			definirGlobalEval( tmp );
		}

		// Capturar executáveis
		se ( scripts ) {
			j = 0;
			enquanto ( ( elem = tmp[ j++ ] ) ) {
				se ( rscriptType.test( elem.type || " " ) ) {
					scripts.push( elem );
				}
			}
		}
	}

	retornar fragmento;
}

// Substitui/restaura o atributo type de elementos de script para manipulação segura do DOM
função disableScript( elem ) {
	elem.type = ( elem.getAttribute( "type" ) !== null ) + "/" + elem.type;
	retornar elemento;
}
função restoreScript( elem ) {
	se ( ( elem.type || "" ).slice( 0, 5 ) === "true/" ) {
		elem.type = elem.type.slice( 5 );
	} outro {
		elem.removeAttribute("type");
	}

	retornar elemento;
}

função domManip( coleção, args, callback, ignored ) {

	// Achatar quaisquer arrays aninhados
	args = plano( args );

	var fragmento, primeiro, scripts, temScripts, nó, documento,
		i = 0,
		l = comprimento da coleção,
		iNoClone = l - 1,
		valor = args[ 0 ],
		valorÉFunção = tipo de valor === "função";

	se (valorÉFunção) {
		retornar coleção.each( função( índice ) {
			var self = collection.eq( index );
			args[ 0 ] = value.call( this, index, self.html() );
			domManip( self, args, callback, ignored );
		} );
	}

	se ( l ) {
		fragmento = construirFragmento( args, coleção[ 0 ].ownerDocument, false, coleção, ignorado );
		primeiro = fragmento.primeiroFilho;

		se ( fragment.childNodes.length === 1 ) {
			fragmento = primeiro;
		}

		// Exija conteúdo novo ou interesse em elementos ignorados para invocar a função de retorno de chamada.
		se (primeiro || ignorado) {
			scripts = jQuery.map( getAll( fragment, "script" ), disableScript );
			hasScripts = scripts.length;

			// Use o fragmento original para o último item
			// em vez do primeiro, porque pode acabar
			// sendo esvaziado incorretamente em certas situações (trac-8070).
			para ( ; i < l; i++ ) {
				nó = fragmento;

				se ( i !== iNoClone ) {
					nó = jQuery.clone( nó, true, true );

					// Mantenha referências aos scripts clonados para restauração posterior
					se (hasScripts) {
						jQuery.merge( scripts, getAll( node, "script" ) );
					}
				}

				callback.call(collection[i], node, i);
			}

			se (hasScripts) {
				doc = scripts[ scripts.length - 1 ].ownerDocument;

				// Reativar scripts
				jQuery.map( scripts, restoreScript );

				// Avaliar scripts executáveis ​​na primeira inserção de documento
				para ( i = 0; i < hasScripts; i++ ) {
					nó = scripts[ i ];
					se ( rscriptType.test( node.type || " " ) &&
						!dataPriv.get(nó, "globalEval") &&
						jQuery.contains(doc, node)) {

						if ( node.src && ( node.type || " " ).toLowerCase() !== "module" ) {

							// Dependência AJAX opcional, mas os scripts não serão executados se não estiverem presentes.
							if ( jQuery._evalUrl && !node.noModule ) {
								jQuery._evalUrl( node.src, {
									nonce: node.nonce,
									origem cruzada: nó.origem cruzada
								}, doc );
							}
						} outro {
							DOMEval(node.textContent, node, doc);
						}
					}
				}
			}
		}
	}

	devolução da cobrança;
}

var rcheckableType = /^(?:checkbox|radio)$/i;

var rtypenamespace = /^([^.]*)(?:\.(.+)|)/;

função retornarVerdadeiro() {
	retornar verdadeiro;
}

função retornarFalso() {
	retornar falso;
}

função on( elem, tipos, seletor, dados, fn, um ) {
	var origFn, tipo;

	// Os tipos podem ser um mapa de tipos/manipuladores
	se ( typeof tipos === "objeto" ) {

		// ( tipos-Objeto, seletor, dados )
		se ( typeof selector !== "string" ) {

			// ( tipos-Objeto, dados )
			dados = dados || seletor;
			seletor = indefinido;
		}
		para (tipo em tipos) {
			on( elem, type, selector, data, types[ type ], one );
		}
		retornar elemento;
	}

	se (dados == nulos && função == nula) {

		// ( tipos, fn )
		fn = seletor;
		dados = seletor = indefinido;
	} senão se ( fn == nulo ) {
		se ( typeof selector === "string" ) {

			// ( tipos, seletor, função )
			fn = dados;
			dados = indefinido;
		} outro {

			// ( tipos, dados, função )
			fn = dados;
			dados = seletor;
			seletor = indefinido;
		}
	}
	se (fn === falso) {
		fn = retornarFalso;
	} senão se ( !fn ) {
		retornar elemento;
	}

	se (um === 1) {
		origFn = fn;
		fn = função(evento) {

			// Pode-se usar um conjunto vazio, já que o evento contém as informações.
			jQuery().off(evento);
			retornar origFn.apply( this, arguments );
		};

		// Use o mesmo GUID para que o chamador possa remover usando origFn
		fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
	}
	retornar elem.each( function() {
		jQuery.event.add( this, types, fn, data, selector );
	} );
}

/*
 * Funções auxiliares para gerenciar eventos -- não fazem parte da interface pública.
 * Agradecimentos à biblioteca addEvent de Dean Edwards por muitas das ideias.
 */
jQuery.event = {

	adicionar: função( elemento, tipos, manipulador, dados, seletor ) {

		var handleObjIn, eventHandle, tmp,
			eventos, t, manipularObj,
			especial, manipuladores, tipo, namespaces, origType,
			elemData = dataPriv.get(elem);

		// Associe eventos apenas a objetos que aceitam dados
		se ( !acceptData( elem ) ) {
			retornar;
		}

		// O chamador pode passar um objeto de dados personalizados em vez do manipulador.
		se ( manipulador.manipulador ) {
			handleObjIn = manipulador;
			manipulador = handleObjIn.handler;
			seletor = handleObjIn.selector;
		}

		// Garantir que seletores inválidos lancem exceções no momento da anexação
		// Avaliar em relação a documentElement caso elem seja um nó que não seja um elemento (ex: document)
		se (seletor) {
			jQuery.find.matchesSelector( documentElement$1, selector );
		}

		// Certifique-se de que o manipulador tenha um ID exclusivo, usado para encontrá-lo/removê-lo posteriormente.
		se ( !handler.guid ) {
			handler.guid = jQuery.guid++;
		}

		// Inicializa a estrutura de eventos e o manipulador principal do elemento, se esta for a primeira vez.
		se ( !( eventos = elemData.events ) ) {
			eventos = elemData.eventos = Object.create(null);
		}
		se ( !( eventHandle = elemData.handle ) ) {
			eventHandle = elemData.handle = function( e ) {

				// Descartar o segundo evento de um jQuery.event.trigger() e
				// quando um evento é acionado após a página ter sido descarregada
				return typeof jQuery !== "undefined" && jQuery.event.triggered !== e.type ?
					jQuery.event.dispatch.apply( elem, arguments ) : undefined;
			};
		}

		// Lidar com múltiplos eventos separados por um espaço
		tipos = ( tipos || "" ).match( rnothtmlwhite ) || [ "" ];
		t = tipos.comprimento;
		enquanto ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			tipo = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// Deve haver um tipo, não é permitido anexar manipuladores que só aceitam namespaces.
			se ( !tipo ) {
				continuar;
			}

			// Se o evento mudar de tipo, use os manipuladores de eventos especiais para o tipo alterado.
			especial = jQuery.event.special[ tipo ] || {};

			// Se o seletor estiver definido, determine o tipo de API do evento especial; caso contrário, forneça o tipo.
			tipo = ( seletor ? tipoDelegadoEspecial : tipoVinculadoEspecial ) || tipo;

			// Atualização especial baseada no tipo recém-redefinido
			especial = jQuery.event.special[ tipo ] || {};

			// handleObj é passado para todos os manipuladores de eventos
			handleObj = jQuery.extend( {
				tipo: tipo,
				origType: origType,
				dados: dados,
				manipulador: manipulador,
				guia: handler.guid,
				seletor: seletor,
				needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
				namespace: namespaces.join( "." )
			}, handleObjIn );

			// Inicializa a fila de manipuladores de eventos se formos os primeiros
			se ( !( handlers = eventos[ tipo ] ) ) {
				manipuladores = eventos[ tipo ] = [];
				handlers.delegateCount = 0;

				// Use addEventListener somente se o manipulador de eventos especiais retornar falso
				se ( !especial.configuração ||
					special.setup.call( elem, data, namespaces, eventHandle ) === false ) {

					se (elem.addEventListener) {
						elem.addEventListener( type, eventHandle );
					}
				}
			}

			se ( especial.adicionar ) {
				especial.adicionar.chamar(elemento, manipularObj);

				se ( !handleObj.handler.guid ) {
					handleObj.handler.guid = handler.guid;
				}
			}

			// Adiciona à lista de manipuladores do elemento, delegações na frente
			se (seletor) {
				handlers.splice( handlers.delegateCount++, 0, handleObj );
			} outro {
				handlers.push( handleObj );
			}
		}

	},

	// Desvincular um evento ou conjunto de eventos de um elemento
	remover: função( elemento, tipos, manipulador, seletor, tiposMapeados ) {

		var j, origCount, tmp,
			eventos, t, manipularObj,
			especial, manipuladores, tipo, namespaces, origType,
			elemData = dataPriv.hasData(elem) && dataPriv.get(elem);

		if ( !elemData || !( events = elemData.events ) ) {
			retornar;
		}

		// Uma vez para cada namespace de tipo em tipos; o tipo pode ser omitido
		tipos = ( tipos || "" ).match( rnothtmlwhite ) || [ "" ];
		t = tipos.comprimento;
		enquanto ( t-- ) {
			tmp = rtypenamespace.exec( types[ t ] ) || [];
			tipo = origType = tmp[ 1 ];
			namespaces = ( tmp[ 2 ] || "" ).split( "." ).sort();

			// Desvincula todos os eventos (neste namespace, se fornecido) para o elemento
			se ( !tipo ) {
				para (digite em eventos) {
					jQuery.event.remove(elem, type + types[t], handler, selector, true);
				}
				continuar;
			}

			especial = jQuery.event.special[ tipo ] || {};
			tipo = ( seletor ? tipoDelegadoEspecial : tipoVinculadoEspecial ) || tipo;
			manipuladores = eventos[ tipo ] || [];
			tmp = tmp[ 2 ] &&
				novo RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" );

			// Remover eventos correspondentes
			origCount = j = handlers.length;
			enquanto ( j-- ) {
				handleObj = handlers[ j ];

				se ( ( mappedTypes || origType === handleObj.origType ) &&
					( !handler || handler.guid === handleObj.guid ) &&
					( !tmp || tmp.test( handleObj.namespace ) ) &&
					( !selector || selector === handleObj.selector ||
						selector === "**" && handleObj.selector ) ) {
					handlers.splice( j, 1 );

					se ( handleObj.selector ) {
						handlers.delegateCount--;
					}
					se ( especial.remover ) {
						especial.remove.call(elem, handleObj);
					}
				}
			}

			// Remove o manipulador de eventos genérico se algo foi removido e não existem mais manipuladores.
			// (evita a possibilidade de recursão infinita durante a remoção de manipuladores de eventos especiais)
			se (origCount && !handlers.length) {
				se ( !special.teardown ||
					special.teardown.call( elem, namespaces, elemData.handle ) === false ) {

					jQuery.removeEvent(elem, type, elemData.handle);
				}

				excluir eventos[ tipo ];
			}
		}

		// Remover dados e o expando se não forem mais usados
		if ( jQuery.isEmptyObject( events ) ) {
			dataPriv.remove( elem, "manipular eventos" );
		}
	},

	despacho: função( eventoNativo ) {

		var i, j, ret, correspondente, handleObj, handlerQueue,
			args = new Array( arguments.length ),

			// Cria um objeto jQuery.Event gravável a partir do objeto de evento nativo
			evento = jQuery.event.fix(nativeEvent),

			manipuladores = (
				dataPriv.get( this, "eventos" ) || Object.create( null )
			)[ event.type ] || [],
			especial = jQuery.event.special[ event.type ] || {};

		// Use o jQuery.Event corrigido em vez do evento nativo (somente leitura)
		args[ 0 ] = evento;

		para ( i = 1; i < arguments.length; i++ ) {
			args[ i ] = argumentos[ i ];
		}

		event.delegateTarget = isto;

		// Chame o gancho preDispatch para o tipo mapeado e permita que ele seja interrompido, se desejado.
		se ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
			retornar;
		}

		// Determinar manipuladores
		handlerQueue = jQuery.event.handlers.call( this, event, handlers );

		// Execute os delegados primeiro; eles podem querer interromper a propagação abaixo de nós.
		i = 0;
		enquanto ( ( matched = handlerQueue[ i++ ] ) && !event.isPropagationStopped() ) {
			evento.alvoAtual = elementoCorrespondido;

			j = 0;
			enquanto ( ( handleObj = matched.handlers[ j++ ] ) &&
				!event.isImmediatePropagationStopped() ) {

				// Se o evento estiver em um namespace, cada manipulador só será invocado se estiver.
				// especialmente universal ou seus espaços de nomes são um superconjunto do evento.
				se ( !event.rnamespace || handleObj.namespace === falso ||
					event.rnamespace.test( handleObj.namespace ) ) {

					evento.handleObj = handleObj;
					evento.dados = handleObj.dados;

					ret = ( ( jQuery.event.special[ handleObj.origType ] || {} ).handle ||
						handleObj.handler).apply(matched.elem, args);

					se ( ret !== undefined ) {
						se ( ( evento.resultado = ret ) === falso ) {
							evento.prevenirPadrão();
							evento.pararPropagação();
						}
					}
				}
			}
		}

		// Chama o gancho postDispatch para o tipo mapeado
		se ( especial.postDispatch ) {
			special.postDispatch.call( this, event );
		}

		retornar evento.resultado;
	},

	manipuladores: função(evento, manipuladores) {
		var i, handleObj, sel, matchedHandlers, matchedSelectors,
			handlerQueue = [],
			delegateCount = handlers.delegateCount,
			cur = evento.alvo;

		// Encontrar manipuladores de delegados
		se (delegateCount &&

			// Suporte: Firefox <=42 - 66+
			// Suprimir cliques que violam as especificações indicando um botão de ponteiro não primário (trac-3861)
			// https://www.w3.org/TR/DOM-Level-3-Events/#event-type-click
			// Suporte: IE 11+
			// ...mas não os "cliques" das teclas de seta das entradas de rádio, que podem ter `button` -1 (gh-2343)
			!( event.type === "click" && event.button >= 1 ) ) {

			para ( ; cur !== this; cur = cur.parentNode || this ) {

				// Não verificar não-elementos (trac-13208)
				// Não processar cliques em elementos desativados (trac-6911, trac-8165, trac-11382, trac-11764)
				se ( cur.nodeType === 1 && !( event.type === "click" && cur.disabled === true ) ) {
					matchedHandlers = [];
					seletorescorados = {};
					para ( i = 0; i < delegateCount; i++ ) {
						handleObj = handlers[ i ];

						// Não entrar em conflito com as propriedades de Object.prototype (trac-13203)
						sel = handleObj.selector + " ";

						se (matchedSelectors[sel] === undefined) {
							matchedSelectors[sel] = handleObj.needsContext ?
								jQuery(sel, this).index(cur) > -1:
								jQuery.find( sel, this, null, [ cur ] ).length;
						}
						se (seletorescorrespondentes[sel]) {
							matchedHandlers.push( handleObj );
						}
					}
					se (matchedHandlers.length) {
						handlerQueue.push( { elem: cur, handlers: matchedHandlers } );
					}
				}
			}
		}

		// Adicione os manipuladores restantes (diretamente vinculados)
		cur = isto;
		se (delegateCount < handlers.length) {
			handlerQueue.push( { elem: cur, handlers: handlers.slice( delegateCount ) } );
		}

		retornar handlerQueue;
	},

	addProp: função( nome, gancho ) {
		Object.defineProperty( jQuery.Event.prototype, name, {
			enumerável: verdadeiro,
			configurável: verdadeiro,

			obter: tipo de gancho === "função" ?
				função() {
					se ( this.originalEvent ) {
						retornar gancho( this.originalEvent );
					}
				} :
				função() {
					se ( this.originalEvent ) {
						retornar this.originalEvent[ nome ];
					}
				},

			definir: função( valor ) {
				O objeto.definirPropriedade( isto, nome, {
					enumerável: verdadeiro,
					configurável: verdadeiro,
					gravável: verdadeiro,
					valor: valor
				} );
			}
		} );
	},

	correção: função( eventoOriginal ) {
		retornar eventoOriginal[ jQuery.expando ] ?
			eventoOriginal:
			novo jQuery.Event(eventoOriginal);
	},

	especial: jQuery.extend( Object.create( null ), {
		carregar: {

			// Impede que eventos image.load disparados se propaguem para window.load
			sem bolha: verdadeiro
		},
		clique: {

			// Utilize eventos nativos para garantir o estado correto de entradas verificáveis
			configuração: função( dados ) {

				// Para compressibilidade mútua com _default, substitua o acesso a `this` por uma variável local.
				// `|| data` é código morto, destinado apenas a preservar a variável durante a minificação.
				var el = este || dados;

				// Reivindique o primeiro manipulador
				se ( rcheckableType.test( el.type ) &&
					el.click && nodeName( el, "entrada" ) ) {

					//dataPriv.set(el, "clique", ... )
					alavancarNativo( el, "clique", verdadeiro );
				}

				// Retorna falso para permitir o processamento normal no chamador
				retornar falso;
			},
			gatilho: função( dados ) {

				// Para compressibilidade mútua com _default, substitua o acesso a `this` por uma variável local.
				// `|| data` é código morto, destinado apenas a preservar a variável durante a minificação.
				var el = este || dados;

				// Forçar configuração antes de acionar um clique
				se ( rcheckableType.test( el.type ) &&
					el.click && nodeName( el, "entrada" ) ) {

					alavancarNativo( el, "clique" );
				}

				// Retorna um valor diferente de falso para permitir a propagação normal do caminho do evento
				retornar verdadeiro;
			},

			// Para garantir a consistência entre navegadores, suprima o método .click() nativo em links.
			// Também impeça isso se estivermos atualmente dentro de uma pilha de eventos nativos alavancada.
			_default: função(evento) {
				var alvo = evento.alvo;
				retornar rcheckableType.test( target.type ) &&
					target.click && nodeName( target, "input" ) &&
					dataPriv.get(target, "click") ||
					nodeName( alvo, "a" );
			}
		},

		antes de descarregar: {
			postDispatch: função( evento ) {
				se (event.result !== undefined) {

					// Definindo `event.originalEvent.returnValue` em modern
					// Os navegadores fazem o mesmo que simplesmente chamar `preventDefault()`,
					// Os navegadores ignoram o valor de qualquer forma.
					Aliás, o IE 11 é o único navegador da nossa lista de navegadores suportados.
					// aqueles que respeitam o valor retornado de um `beforeunload`
					// manipulador anexado por `addEventListener`; outros navegadores fazem
					// Portanto, apenas para manipuladores embutidos, sem definir o valor.
					// Isso não deve reduzir nenhuma funcionalidade.
					evento.prevenirPadrão();
				}
			}
		}
	} )
};

// Garanta a presença de um ouvinte de eventos que lide com eventos acionados manualmente
// eventos sintéticos interrompendo o progresso até serem reinvocados em resposta a
// Eventos *nativos* que ele dispara diretamente, garantindo que as mudanças de estado tenham
// já ocorreu antes que outros ouvintes sejam invocados.
função leverageNative( el, type, isSetup ) {

	// A ausência de `isSetup` indica uma chamada de gatilho, que deve forçar a configuração por meio de jQuery.event.add
	se ( !isSetup ) {
		se ( dataPriv.get( el, type ) === undefined ) {
			jQuery.event.add( el, type, returnTrue );
		}
		retornar;
	}

	// Registre o controlador como um manipulador universal especial para todos os namespaces de eventos
	dataPriv.set(el, tipo, falso);
	jQuery.event.add( el, type, {
		namespace: falso,
		manipulador: função(evento) {
			resultado da variável,
				salvo = dataPriv.get( this, tipo );

			// Esta função de controle é invocada em múltiplas circunstâncias,
			// diferenciado pelo valor armazenado em `saved`:
			// 1. Para um evento sintético externo acionado por `.trigger()` (detectado por
			// `event.isTrigger & 1` e `saved` (que não é um array), registra os argumentos.
			// como um array e dispara um evento nativo [interno] para solicitar o estado
			// alterações que devem ser observadas pelos ouvintes registrados (como
			// alternar a caixa de seleção e atualizar o foco), em seguida, limpa o valor armazenado.
			// 2. Para um evento nativo [interno] (detectado por `saved` sendo
			// um array), ele aciona um evento sintético interno, registra o
			// resultado, e impede a propagação para outros ouvintes do jQuery.
			// 3. Para um evento sintético interno (detectado por `event.isTrigger & 1` e
			// array `saved`), isso impede a dupla propagação de eventos substitutos
			// mas, de resto, permite que tudo prossiga (particularmente incluindo
			// outros ouvintes).
			// Possíveis formatos de dados `salvos`: `[...], `{ valor }`, `false`.
			se ( ( evento.isTrigger & 1 ) && this[ tipo ] ) {

				// Interrompe o processamento do evento sintético externo acionado por .trigger()
				se ( !saved.length ) {

					// Armazena argumentos para uso ao lidar com o evento nativo interno
					// Sempre haverá pelo menos um argumento (um objeto de evento),
					// para que este array não seja confundido com um objeto de captura remanescente.
					salvo = fatiar.chamar( argumentos );
					dataPriv.set( this, type, saved );

					// Acione o evento nativo e capture seu resultado
					este[ tipo ]();
					resultado = dataPriv.get( this, tipo );
					dataPriv.set(este, tipo, falso);

					se ( salvo !== resultado ) {

						// Cancelar o evento sintético externo
						evento.stopImmediatePropagation();
						evento.prevenirPadrão();

						// Compatível com: Chrome 86+
						// No Chrome, se um elemento tiver um manipulador de evento focusout
						// Ao clicar fora da área desfocada, o manipulador é acionado.
						// de forma síncrona. Se esse manipulador chamar `.remove()` em
						// o elemento, os dados são apagados, deixando `result`
						// indefinido. Precisamos nos precaver contra isso.
						retornar resultado && resultado.valor;
					}

				// Se este for um evento sintético interno para um evento com propagação
				// substituto (foco ou desfoque), assume-se que o substituto já
				// propagado a partir do acionamento do evento nativo e impede isso
				// para que não aconteça novamente aqui.
				} else if ( ( jQuery.event.special[ type ] || {} ).delegateType ) {
					evento.pararPropagação();
				}

			// Se este for um evento nativo acionado acima, agora está tudo em ordem.
			// Dispara um evento sintético interno com os argumentos originais.
			} else if ( saved.length ) {

				// ...e capturar o resultado
				dataPriv.set( this, type, {
					valor: jQuery.event.trigger(
						salvo[ 0 ],
						fatiado.salvo( 1 ),
						esse
					)
				} );

				// Interrompe o tratamento do evento nativo por todos os manipuladores jQuery, permitindo
				// Manipuladores nativos no mesmo elemento para serem executados. No alvo, isso é alcançado.
				// interrompendo a propagação imediata apenas no evento jQuery. No entanto,
				// O evento nativo é reempacotado por um evento jQuery em cada nível do
				// propagação, então a única maneira de pará-la para o jQuery é pará-la para
				// todos via `stopPropagation()` nativo. Isso não é um problema para
				// Foco/desfoque que não criam bolhas, mas também impedem cliques em caixas de seleção
				// e rádios. Aceitamos essa limitação.
				evento.pararPropagação();
				event.isImmediatePropagationStopped = returnTrue;
			}
		}
	} );
}

jQuery.removeEvent = function( elem, type, handle ) {

	// Este "se" é necessário para objetos simples
	se (elem.removeEventListener) {
		elem.removeEventListener(tipo, identificador);
	}
};

jQuery.Event = function( src, props ) {

	// Permite a instanciação sem a palavra-chave 'new'
	se ( !( esta instância de jQuery.Event ) ) {
		return new jQuery.Event( src, props );
	}

	// Objeto de evento
	se ( src && src.type ) {
		this.originalEvent = src;
		this.type = src.type;

		// Os eventos que se propagam pelo documento podem ter sido marcados como impedidos.
		// por um manipulador mais abaixo na árvore; reflita o valor correto.
		this.isDefaultPrevented = src.defaultPrevented ?
			retornar Verdadeiro:
			retornarFalso;

		// Criar propriedades de destino
		this.target = src.target;
		this.currentTarget = src.currentTarget;
		this.relatedTarget = src.relatedTarget;

	// Tipo de evento
	} outro {
		this.type = src;
	}

	// Adiciona propriedades explicitamente fornecidas ao objeto de evento
	se (props) {
		jQuery.extend( this, props );
	}

	// Crie um registro de data e hora se o evento recebido não tiver um.
	this.timeStamp = src && src.timeStamp || Date.now();

	// Marque como resolvido
	this[ jQuery.expando ] = true;
};

// jQuery.Event é baseado em eventos DOM3, conforme especificado pela vinculação da linguagem ECMAScript.
// https://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
jQuery.Event.prototype = {
	construtor: jQuery.Event,
	isDefaultPrevented: retornarFalso,
	isPropagationStopped: returnFalse,
	isImmediatePropagationStopped: returnFalse,
	éSimulado: falso,

	preventDefault: função() {
		var e = this.originalEvent;

		this.isDefaultPrevented = returnTrue;

		se ( e && !this.isSimulated ) {
			e.preventDefault();
		}
	},
	stopPropagation: função() {
		var e = this.originalEvent;

		this.isPropagationStopped = returnTrue;

		se ( e && !this.isSimulated ) {
			e.stopPropagation();
		}
	},
	stopImmediatePropagation: função() {
		var e = this.originalEvent;

		this.isImmediatePropagationStopped = returnTrue;

		se ( e && !this.isSimulated ) {
			e.stopImmediatePropagation();
		}

		this.stopPropagation();
	}
};

// Inclui todas as propriedades de evento comuns, incluindo as propriedades específicas de KeyEvent e MouseEvent
jQuery.each( {
	altKey: verdadeiro,
	bolhas: verdadeiro,
	cancelável: verdadeiro,
	changedTouches: verdadeiro,
	ctrlKey: verdadeiro,
	detalhe: verdadeiro,
	faseEvento: verdadeiro,
	metaKey: verdadeiro,
	páginaX: verdadeiro,
	páginaY: verdadeiro,
	shiftKey: verdadeiro,
	visão: verdadeira,
	"char": verdadeiro,
	código: verdadeiro,
	charCode: verdadeiro,
	chave: verdadeiro,
	keyCode: true,
	botão: verdadeiro,
	botões: verdadeiro,
	clienteX: verdadeiro,
	clienteY: verdadeiro,
	offsetX: verdadeiro,
	offsetY: verdadeiro,
	pointerId: verdadeiro,
	tipoPonteiro: verdadeiro,
	screenX: verdadeiro,
	screenY: verdadeiro,
	targetTouchs: verdadeiro,
	toElement: verdadeiro,
	toques: verdadeiro,
	que: verdadeiro
}, jQuery.event.addProp );

jQuery.each( { focus: "focusin", blur: "focusout" }, function( type, delegateType ) {

	// Suporte: IE 11+
	// Anexe um único manipulador de foco (focusin/focusout) ao documento enquanto alguém deseja focar/desfocar o documento.
	Isso ocorre porque os primeiros são síncronos no IE, enquanto os últimos são assíncronos. Em outras palavras,
	// Nos navegadores, todos esses manipuladores são invocados de forma síncrona.
	função focusMappedHandler( nativeEvent ) {

		// `eventHandle` já encapsularia o evento, mas precisamos alterar o `type` aqui.
		var evento = jQuery.event.fix(nativeEvent);
		event.type = nativeEvent.type === "focusin" ? "focus" : "blur";
		evento.isSimulado = verdadeiro;

		// O foco/desfoque não se propagam enquanto o foco de entrada/saída de foco sim; simule o primeiro apenas
		// Invocando o manipulador em um nível inferior.
		se (event.target === event.currentTarget) {

			// A parte de configuração chama `leverageNative`, que, por sua vez, chama
			// `jQuery.event.add`, então o manipulador de eventos já terá sido definido.
			// a esta altura.
			dataPriv.get( this, "handle" )( event );
		}
	}

	jQuery.event.special[ type ] = {

		// Utilize o evento nativo, se possível, para que a sequência de desfoque/foco seja correta.
		configuração: função() {

			// Reivindique o primeiro manipulador
			// dataPriv.set( this, "focus", ... )
			// dataPriv.set( this, "blur", ... )
			alavancagemNativa( isto, tipo, verdadeiro );

			se ( isIE ) {
				this.addEventListener(delegateType, focusMappedHandler);
			} outro {

				// Retorna falso para permitir o processamento normal no chamador
				retornar falso;
			}
		},
		gatilho: função() {

			// Forçar configuração antes do gatilho
			alavancarNativo( isto, tipo );

			// Retorna um valor diferente de falso para permitir a propagação normal do caminho do evento
			retornar verdadeiro;
		},

		desmontagem: função() {
			se ( isIE ) {
				this.removeEventListener(delegateType, focusMappedHandler);
			} outro {

				// Retorna falso para indicar que a limpeza padrão deve ser aplicada.
				retornar falso;
			}
		},

		// Suprimir o foco ou desfoque nativo se estivermos atualmente dentro de
		// uma pilha de eventos nativos alavancada
		_default: função(evento) {
			retornar dataPriv.get(evento.target, tipo);
		},

		tipoDelegado: tipoDelegado
	};
} );

// Criar eventos de entrada/saída do mouse usando verificações de sobreposição/saída do mouse e tempo do evento
// para que a delegação de eventos funcione no jQuery.
// Faça o mesmo para pointerenter/pointerleave e pointerover/pointerout
jQuery.each( {
	mouseenter: "mouseover",
	mouseleave: "mouseout",
	ponteiroenter: "ponteiroover",
	pointerleave: "pointerout"
}, função( orig, fix ) {
	jQuery.event.special[orig] = {
		delegateType: fix,
		bindType: fix,

		manipular: função(evento) {
			var ret,
				alvo = isto,
				relacionado = evento.alvo relacionado,
				handleObj = evento.handleObj;

			// Para eventos mouseenter/leave, chame o manipulador se o evento relacionado estiver fora do alvo.
			// Observação: Não há relatedTarget se o cursor do mouse saiu/entrou na janela do navegador.
			if ( !related || ( related !== target && !jQuery.contains( target, related ) ) ) {
				event.type = handleObj.origType;
				ret = handleObj.handler.apply( this, arguments );
				event.type = fix;
			}
			retornar ret;
		}
	};
} );

jQuery.fn.extend( {

	em: função( tipos, seletor, dados, fn ) {
		retornar em( isto, tipos, seletor, dados, fn );
	},
	uma: função( tipos, seletor, dados, fn ) {
		retornar em( this, types, selector, data, fn, 1 );
	},
	desligado: função( tipos, seletor, fn ) {
		var handleObj, tipo;
		se ( tipos && tipos.preventDefault && tipos.handleObj ) {

			// (evento) jQuery.Event despachado
			handleObj = tipos.handleObj;
			jQuery( types.delegateTarget ).off(
				handleObj.namespace?
					handleObj.origType + "." + handleObj.namespace :
					handleObj.origType,
				handleObj.selector,
				handleObj.handler
			);
			devolva isto;
		}
		se ( typeof tipos === "objeto" ) {

			// ( objetos-tipos [, seletor] )
			para (tipo em tipos) {
				this.off( tipo, seletor, tipos[ tipo ] );
			}
			devolva isto;
		}
		se (seletor === falso || tipo do seletor === "função") {

			// ( tipos [, fn] )
			fn = seletor;
			seletor = indefinido;
		}
		se (fn === falso) {
			fn = retornarFalso;
		}
		retorne this.each( function() {
			jQuery.event.remove( this, types, fn, selector );
		} );
	}
} );

var

	// Suporte: IE <=10 - 11+
	// No IE, usar grupos de expressões regulares aqui causa lentidão significativa.
	rnoInnerhtml = /<script|<style|<link/i;

// Prefira um tbody à sua tabela pai para conter novas linhas
função manipulaçãoAlvo( elemento, conteúdo ) {
	se ( nodeName( elem, "table" ) &&
		nodeName( content.nodeType !== 11 ? content : content.firstChild, "tr" ) ) {

		retornar jQuery( elem ).children( "tbody" )[ 0 ] || elem;
	}

	retornar elemento;
}

função cloneCopyEvent( src, dest ) {
	tipo de variável, i, l,
		eventos = dataPriv.get( src, "eventos" );

	se (dest.nodeType !== 1) {
		retornar;
	}

	// 1. Copiar dados privados: eventos, manipuladores, etc.
	se (eventos) {
		dataPriv.remove(dest, "manipular eventos");
		para (digite em eventos) {
			para ( i = 0, l = eventos[ tipo ].comprimento; i < l; i++ ) {
				jQuery.event.add(dest, type, events[type][i]);
			}
		}
	}

	// 2. Copiar dados do usuário
	se ( dataUser.hasData( src ) ) {
		dataUser.set(dest, jQuery.extend({}, dataUser.get(src)));
	}
}

função remover( elemento, seletor, manterDados ) {
	var nó,
		nós = seletor ? jQuery.filter( seletor, elemento ) : elemento,
		i = 0;

	para ( ; ( nó = nós[ i ] ) != nulo; i++ ) {
		se ( !keepData && node.nodeType === 1 ) {
			jQuery.cleanData(getAll(nó));
		}

		se ( nó.parentNode ) {
			se (manterDados && estáAnexado(nó)) {
				setGlobalEval(getAll(nó, "script" ) );
			}
			nó.parentNode.removeChild( nó );
		}
	}

	retornar elemento;
}

jQuery.extend( {
	htmlPrefilter: função( html ) {
		retornar html;
	},

	clone: ​​função( elem, dataAndEvents, deepDataAndEvents ) {
		var i, l, srcElements, destElements,
			clone = elem.cloneNode(true),
			inPage = isAttached(elem);

		// Corrigir problemas de clonagem no IE
		if (isIE && (elem.nodeType === 1 || elem.nodeType === 11) &&
				!jQuery.isXMLDoc( elem ) ) {

			// Optamos por não usar jQuery#find aqui por motivos de desempenho:
			// https://jsperf.com/getall-vs-sizzle/2
			elementosDest = obterTodos( clone );
			srcElements = getAll( elem );

			para ( i = 0, l = srcElements.length; i < l; i++ ) {

				// Suporte: IE <= 11+
				// O IE não consegue definir o valor padrão para o valor correto quando
				// clonando áreas de texto.
				se ( nodeName( destElements[ i ], "textarea" ) ) {
					destElements[ i ].defaultValue = srcElements[ i ].defaultValue;
				}
			}
		}

		// Copiar os eventos do original para o clone
		se (dadosEOventos) {
			se ( deepDataAndEvents ) {
				srcElements = srcElements || getTodos(elem);
				destElements = destElements || getAll( clone );

				para ( i = 0, l = srcElements.length; i < l; i++ ) {
					cloneCopyEvent( srcElements[ i ], destElements[ i ] );
				}
			} outro {
				cloneCopyEvent(elem, clone);
			}
		}

		// Preservar o histórico de avaliação do script
		elementosDest = getAll( clone, "script" );
		se (destElements.length > 0) {
			setGlobalEval(destElements, !inPage && getAll( elem, "script" ) );
		}

		// Retorna o conjunto clonado
		retornar clone;
	},

	cleanData: function( elems ) {
		var data, elem, type,
			especial = jQuery.event.special,
			i = 0;

		for ( ; ( elem = elems[ i ] ) !== undefined; i++ ) {
			se ( aceitarDados( elemento ) ) {
				if ((dados=elem[dataPriv.expando])) {
					se (dados.eventos) {
						para ( tipo em data.events ) {
							se ( tipo[ especial ] ) {
								jQuery.event.remove(elem, type);

							// Este é um atalho para evitar a sobrecarga do jQuery.event.remove
							} outro {
								jQuery.removeEvent(elem, type, data.handle);
							}
						}
					}

					// Compatível com Chrome <=35 - 45+
					// Atribua undefined em vez de usar delete, veja Data#remove
					elem[ dataPriv.expando ] = indefinido;
				}
				se (elem[dataUser.expando]) {

					// Compatível com Chrome <=35 - 45+
					// Atribua undefined em vez de usar delete, veja Data#remove
					elem[ dataUser.expando ] = indefinido;
				}
			}
		}
	}
} );

jQuery.fn.extend( {
	desanexar: função( seletor ) {
		retornar remover( isto, seletor, verdadeiro );
	},

	remover: função( seletor ) {
		retornar remover( isto, seletor );
	},

	texto: função( valor ) {
		retornar acesso( isto, função( valor ) {
			valor de retorno === indefinido?
				jQuery.text( this ) :
				this.empty().each( function() {
					se ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
						this.textContent = valor;
					}
				} );
		}, null, valor, argumentos.comprimento );
	},

	adicionar: função() {
		retornar domManip( this, argumentos, função( elem ) {
			se ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var alvo = alvoDeManipulação( this, elem );
				alvo.appendChild(elemento);
			}
		} );
	},

	prepend: function() {
		retornar domManip( this, argumentos, função( elem ) {
			se ( this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9 ) {
				var alvo = alvoDeManipulação( this, elem );
				alvo.inserirAntes(elemento, alvo.primeiroFilho);
			}
		} );
	},

	antes: função() {
		retornar domManip( this, argumentos, função( elem ) {
			se (this.parentNode) {
				this.parentNode.insertBefore( elem, this );
			}
		} );
	},

	depois: função() {
		retornar domManip( this, argumentos, função( elem ) {
			se (this.parentNode) {
				this.parentNode.insertBefore( elem, this.nextSibling );
			}
		} );
	},

	vazio: função() {
		var elem,
			i = 0;

		para ( ; ( elem = this[ i ] ) != null; i++ ) {
			se (elem.nodeType === 1) {

				// Evitar vazamentos de memória
				jQuery.cleanData(getAll(elem, false));

				// Remova quaisquer nós restantes
				elem.textContent = "";
			}
		}

		devolva isto;
	},

	clone: ​​função( dadosEeventos, dadosDeepEvents ) {
		dataAndEvents = dataAndEvents == nulo? falso: dataAndEvents;
		deepDataAndEvents = deepDataAndEvents == nulo? dataAndEvents: deepDataAndEvents;

		retorne this.map( function() {
			return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
		} );
	},

	html: função( valor ) {
		retornar acesso( isto, função( valor ) {
			var elem = this[ 0 ] || {},
				i = 0,
				l = este.comprimento;

			se ( valor === indefinido && elem.nodeType === 1 ) {
				retornar elem.innerHTML;
			}

			// Vamos ver se conseguimos usar um atalho e simplesmente usar innerHTML
			se ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
				!wrapMap[ ( rtagName.exec( value ) || [ "", "" ] )[ 1 ].toLowerCase() ] ) {

				valor = jQuery.htmlPrefilter( valor );

				tentar {
					para ( ; i < l; i++ ) {
						elem = this[ i ] || {};

						// Remover nós de elementos e evitar vazamentos de memória
						se (elem.nodeType === 1) {
							jQuery.cleanData(getAll(elem, false));
							elem.innerHTML = valor;
						}
					}

					elem = 0;

				// Se o uso de innerHTML gerar uma exceção, use o método alternativo.
				} catch ( e ) {}
			}

			se (elem) {
				this.empty().append( valor );
			}
		}, null, valor, argumentos.comprimento );
	},

	substituirCom: função() {
		var ignored = [];

		// Faça as alterações, substituindo cada elemento de contexto não ignorado pelo novo conteúdo.
		retornar domManip( this, argumentos, função( elem ) {
			var parent = this.parentNode;

			if ( jQuery.inArray( this, ignored ) < 0 ) {
				jQuery.cleanData( getAll( this ) );
				se (pai) {
					parent.replaceChild(elem, this);
				}
			}

		// Forçar invocação de callback
		}, ignorado );
	}
} );

jQuery.each( {
	appendTo: "append",
	prependTo: "prepend",
	inserirAntes: "antes",
	inserirDepois: "após",
	substituirTudo: "substituirPor"
}, função( nome, original ) {
	jQuery.fn[ nome ] = function( seletor ) {
		var elementos,
			ret = [],
			inserir = jQuery(seletor),
			último = inserir.comprimento - 1,
			i = 0;

		para ( ; i <= último; i++ ) {
			elems = i === último ? isto : isto.clonar( verdadeiro );
			jQuery(insert[i])[original](elems);
			push.apply( ret, elems );
		}

		retorne this.pushStack( ret );
	};
} );

var rnumnonpx = new RegExp( "^(" + pnum + ")(?!px)[az%]+$", "i" );

var rcustomProp = /^--/;

função obterEstilos( elem ) {

	// Suporte: IE <=11+ (trac-14150)
	// No IE, a `window` do popup é a janela que o abriu, o que faz com que `window.getComputedStyle(elem)`
	// break. Usar `elem.ownerDocument.defaultView` evita o problema.
	var view = elem.ownerDocument.defaultView;

	// `document.implementation.createHTMLDocument( "" )` possui uma `defaultView` nula
	// propriedade; verifique se `defaultView` é verdadeiro para usar `window` como alternativa nesse caso.
	se ( !visualização ) {
		vista = janela;
	}

	retornar view.getComputedStyle( elem );
}

// Um ​​método para trocar rapidamente propriedades CSS para obter cálculos corretos.
função swap( elemento, opções, callback ) {
	var ret, nome,
		antigo = {};

	// Guarde os valores antigos e insira os novos.
	para (nome em opções) {
		antigo[nome] = elem.estilo[nome];
		elem.style[ nome ] = opções[ nome ];
	}

	ret = callback.call( elem );

	// Reverter os valores antigos
	para (nome em opções) {
		elem.style[ nome ] = antigo[ nome ];
	}

	retornar ret;
}

função curCSS(elemento, nome, computado) {
	var ret,
		isCustomProp = rcustomProp.test( nome );

	computado = computado || obterEstilos( elemento );

	// getPropertyValue é necessário para `.css('--customProperty')` (gh-3144)
	se (calculado) {

		// É necessário um recurso alternativo para acesso direto à propriedade, já que `computed` é um valor calculado.
		// a saída de `getComputedStyle` contém chaves em camelCase e
		// `getPropertyValue` requer valores em kebab-case.
		//
		// Suporte: IE <= 9 - 11+
		// O IE só suporta `"float"` em `getPropertyValue`; em estilos computados
		// Está disponível apenas como `"cssFloat"`. Não modificamos mais propriedades.
		// enviado para `.css()` além do camelCase, então precisamos verificar ambos.
		Normalmente, isso criaria uma diferença de comportamento: se
		// `getPropertyValue` retorna uma string vazia, o valor retornado
		// por `.css()` seria `undefined`. Isso geralmente acontece para
		// elementos desconectados. No entanto, no IE, até mesmo elementos desconectados
		// Sem estilos, retorna `"none"` para `getPropertyValue( "float" )`
		ret = computed.getPropertyValue( name ) || computed[ name ];

		se (isCustomProp && ret) {

			// Compatível com: Firefox 105 - 135+
			// A especificação exige a remoção de espaços em branco para propriedades personalizadas (gh-4926).
			// O Firefox remove apenas os espaços em branco iniciais.
			//
			// Caso uma string vazia seja retornada, o valor retornado será `undefined`.
			// Isso elimina uma definição ausente com a propriedade definida
			// e definido como uma string vazia, mas não existe uma API padrão.
			// permitindo-nos diferenciá-los sem perda de desempenho
			// e retornar `undefined` está de acordo com versões mais antigas do jQuery.
			//
			// rtrimCSS trata U+000D RETORNO DE CARRO e U+000C ALIMENTAÇÃO DE FORMULÁRIO
			// como espaço em branco, enquanto o CSS não, mas isso não é um problema
			// porque o pré-processamento CSS os substitui por U+000A QUEBRA DE LINHA
			// (que *é* um espaço em branco CSS)
			// https://www.w3.org/TR/css-syntax-3/#input-preprocessing
			ret = ret.replace( rtrimCSS, "$1" ) || undefined;
		}

		se ( ret === "" && !isAttached( elem ) ) {
			ret = jQuery.style(elem, nome);
		}
	}

	retornar ret !== undefined ?

		// Suporte: IE <= 9 - 11+
		// O IE retorna o valor de zIndex como um número inteiro.
		ret + "" :
		ret;
}

var cssPrefixes = [ "Webkit", "Moz", "ms" ],
	emptyStyle = document$1.createElement("div").style;

// Retorna uma propriedade com prefixo de fornecedor ou indefinido
função vendorPropName( nome ) {

	// Verificar nomes de fornecedores com prefixo
	var capName = name[ 0 ].toUpperCase() + name.slice( 1 ),
		i = cssPrefixes.length;

	enquanto ( i-- ) {
		nome = cssPrefixes[ i ] + capName;
		se (nome em emptyStyle) {
			retornar nome;
		}
	}
}

// Retorna uma propriedade com prefixo de fornecedor potencialmente mapeada
function finalPropName( nome ) {
	se (nome em emptyStyle) {
		retornar nome;
	}
	retornar vendorPropName( nome ) || nome;
}

var confiávelTrDimensionsVal, confiávelColDimensionsVal,
	tabela = document$1.createElement("tabela");

// A execução de testes de tabela requer apenas um layout, portanto, eles são executados.
// ao mesmo tempo para salvar o segundo cálculo.
função computeTableStyleTests() {
	se (

		// Este é um singleton, precisamos executá-lo apenas uma vez
		!tabela ||

		// Finalizar antecipadamente em ambientes limitados (não baseados em navegador)
		!table.style
	) {
		retornar;
	}

	var trStyle,
		col = document$1.createElement("col"),
		tr = document$1.createElement("tr"),
		td = document$1.createElement( "td" );

	table.style.cssText = "position:absolute;left:-11111px;" +
		"border-collapse:separate;border-spacing:0";
	tr.style.cssText = "box-sizing:content-box;border:1px solid;height:1px";
	td.style.cssText = "altura:9px;largura:9px;preenchimento:0";

	col.span = 2;

	documentElement$1
		.appendChild( tabela )
		.appendChild( col )
		.parentNode
		.appendChild( tr )
		.appendChild( td )
		.parentNode
		.appendChild( td.cloneNode( true ) );

	// Não execute até que a janela esteja visível
	se (table.offsetWidth === 0) {
		documentElement$1.removeChild( tabela );
		retornar;
	}

	trStyle = window.getComputedStyle( tr );

	// Compatível com: Firefox 135+
	// O Firefox sempre reporta a largura calculada como se `span` fosse 1.
	// Compatível com Safari 18.3+
	// No Safari, a largura calculada para as colunas é sempre 0.
	// Em ambos os navegadores, usar `offsetWidth` resolve o problema.
	// Suporte: IE 11+
	// No IE, a largura calculada de `<col>` é `"auto"` a menos que `width` seja definida.
	// explicitamente via CSS, portanto as medições permanecem incorretas. Devido a
	// Na ausência de uma solução alternativa adequada, aceitamos essa limitação, tratando
	// IE como tendo passado no teste.
	reliableColDimensionsVal = isIE || Math.round( parseFloat(
		window.getComputedStyle( col ).width )
	) === 18;

	// Compatível com: IE 10 - 11+
	// O IE reporta incorretamente o `getComputedStyle` das linhas da tabela com largura/altura.
	// Definido em CSS enquanto as propriedades `offset*` reportam valores corretos.
	// Compatível com: Firefox 70 - 135+
	// Somente o Firefox inclui larguras de borda
	// em dimensões calculadas para linhas da tabela. (gh-4529)
	reliableTrDimensionsVal = Math.round( parseFloat( trStyle.height ) +
		parseFloat(trStyle.borderTopWidth) +
		parseFloat( trStyle.borderBottomWidth ) ) === tr.offsetHeight;

	documentElement$1.removeChild( tabela );

	// Anule a tabela para que ela não seja armazenada na memória;
	// Também será um sinal de que as verificações já foram realizadas.
	tabela = nula;
}

jQuery.extend( suporte, {
	dimensõesTrconfiáveis: função() {
		computeTableStyleTests();
		retornar valorTrDimensionsval confiável;
	},

	dimensõesColunasRefiáveis: função() {
		computeTableStyleTests();
		retornar reliableColDimensionsVal;
	}
} );

var cssShow = { position: "absolute", visibility: "hidden", display: "block" },
	cssNormalTransform = {
		Espaçamento entre letras: "0",
		Peso da fonte: "400"
	};

função definirNúmeroPositivo( _elem, valor, subtrair ) {

	// Quaisquer valores relativos (+/-) já foram
	// normalizado neste ponto
	var matches = rcssNum.exec( valor );
	Retornar correspondências?

		// Proteger contra "subtract" indefinido, por exemplo, quando usado como em cssHooks
		Math.max( 0, matches[ 2 ] - ( subtract || 0 ) ) + ( matches[ 3 ] || "px" ) :
		valor;
}

função boxModelAdjustment( elem, dimension, box, isBorderBox, styles, computedVal ) {
	var i = dimensão === "largura" ? 1 : 0,
		extra = 0,
		delta = 0,
		margemDelta = 0;

	// Pode não ser necessário fazer ajustes
	se ( caixa === ( isBorderBox ? "borda" : "conteúdo" ) ) {
		retornar 0;
	}

	para ( ; i < 4; i += 2 ) {

		// Ambos os modelos de caixa excluem a margem
		// Calcule a diferença de margem separadamente para adicioná-la somente após o ajuste da margem de rolagem.
		// Isto é necessário para que as margens negativas funcionem com `outerHeight( true )` (gh-3982).
		se ( caixa === "margem" ) {
			marginDelta += jQuery.css( elem, box + cssExpand[ i ], true, styles );
		}

		// Se chegarmos aqui com uma caixa de conteúdo, estamos procurando por "preenchimento" ou "borda" ou "margem"
		se ( !isBorderBox ) {

			// Adicionar preenchimento
			delta += jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );

			// Para "borda" ou "margem", adicione borda
			se ( caixa !== "preenchimento" ) {
				delta += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );

			// Mas continue monitorando isso de qualquer forma
			} outro {
				extra += jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}

		// Se chegarmos aqui com uma caixa de borda (conteúdo + preenchimento + borda), estamos buscando "conteúdo" ou
		// "preenchimento" ou "margem"
		} outro {

			// Para "conteúdo", subtraia o preenchimento
			se ( caixa === "conteúdo" ) {
				delta -= jQuery.css( elem, "padding" + cssExpand[ i ], true, styles );
			}

			// Para "conteúdo" ou "preenchimento", subtraia a borda
			se ( caixa !== "margem" ) {
				delta -= jQuery.css( elem, "border" + cssExpand[ i ] + "Width", true, styles );
			}
		}
	}

	// Leva em consideração a margem de rolagem positiva da caixa de conteúdo quando solicitado, fornecendo computedVal
	se ( !isBorderBox && computedVal >= 0 ) {

		// offsetWidth/offsetHeight é a soma arredondada do conteúdo, preenchimento, espaçamento entre as barras de rolagem e borda.
		// Considerando uma margem de rolagem inteira, subtraia o restante e arredonde para baixo.
		delta += Math.max( 0, Math.ceil(
			elem["offset" + dimension[0].toUpperCase() + dimension.slice(1)] -
			valor computado -
			delta -
			extra -
			0,5

		// Se offsetWidth/offsetHeight for desconhecido, não podemos determinar a margem de rolagem da caixa de conteúdo.
		// Use um zero explícito para evitar NaN (gh-3964)
		) ) || 0;
	}

	retornar delta + margemDelta;
}

função obterLarguraOuAltura( elemento, dimensão, extra ) {

	// Comece com o estilo computado
	var estilos = getStyles(elem),

		// Para evitar forçar um reflow, busque boxSizing somente se precisarmos dele (gh-4322).
		// Caixa de conteúdo falsa até sabermos que ela é necessária para conhecer o valor real.
		boxSizingNeeded = isIE || extra,
		isBorderBox = boxSizingNeeded &&
			jQuery.css(elem, "boxSizing", false, styles) === "border-box",
		valorIsBorderBox = isBorderBox,

		val = curCSS(elemento, dimensão, estilos),
		offsetProp = "offset" + dimension[ 0 ].toUpperCase() + dimension.slice( 1 );

	// Retorna um valor não pixelado que cause confusão ou finge ignorância, conforme apropriado.
	se ( rnumnonpx.test( val ) ) {
		se ( !extra ) {
			retornar val;
		}
		val = "auto";
	}


	se (
		(

			// Recorrer a offsetWidth/offsetHeight quando o valor for "auto"
			// Isso ocorre para elementos embutidos sem configuração explícita (gh-3571)
			val === "auto" ||

			// Compatível com: IE 9 - 11+
			// Use offsetWidth/offsetHeight quando o dimensionamento da caixa não for confiável.
			// Nesses casos, pode-se confiar que o valor calculado corresponde à caixa delimitadora.
			( isIE && isBorderBox ) ||

			( !support.reliableColDimensions() && nodeName( elem, "col" ) ) ||

			( !support.reliableTrDimensions() && nodeName( elem, "tr" ) )
		) &&

		// Certifique-se de que o elemento esteja visível e conectado
		elem.getClientRects().length ) {

		isBorderBox = jQuery.css( elem, "boxSizing", false, styles ) === "border-box";

		// Quando disponíveis, offsetWidth/offsetHeight são as dimensões aproximadas da caixa de borda.
		// Quando não disponível (por exemplo, SVG), assuma que o dimensionamento da caixa não é confiável e interprete o
		// Valor recuperado como uma dimensão da caixa de conteúdo.
		valueIsBorderBox = offsetProp em elem;
		se (valorIsBorderBox) {
			val = elem[ offsetProp ];
		}
	}

	// Normalizar "" e auto
	val = parseFloat( val ) || 0;

	// Ajustar para o modelo de caixa do elemento
	retornar ( val +
		boxModelAdjustment(
			elemento,
			dimensão,
			extra || ( isBorderBox ? "border" : "content" ),
			valorÉCaixaDeBorda,
			estilos,

			// Forneça o tamanho calculado atual para solicitar o cálculo da margem de rolagem (gh-3589)
			val
		)
	) + "px";
}

jQuery.extend( {

	// Adicione ganchos de propriedade de estilo para substituir o padrão
	// comportamento de obtenção e definição de uma propriedade de estilo
	cssHooks: {},

	// Obter e definir a propriedade de estilo em um nó DOM
	estilo: função( elemento, nome, valor, extra ) {

		// Não defina estilos em nós de texto e comentário
		if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
			retornar;
		}

		// Certifique-se de que estamos trabalhando com o nome correto
		var ret, tipo, ganchos,
			origName = cssCamelCase( nome ),
			isCustomProp = rcustomProp.test( nome ),
			estilo = elem.estilo;

		// Certifique-se de que estamos trabalhando com o nome correto. Nós não
		// Deseja consultar o valor para verificar se é uma propriedade personalizada do CSS?
		// já que são definidos pelo usuário.
		se ( !isCustomProp ) {
			nome = finalPropName(origName);
		}

		// Obtém o gancho para a versão com prefixo e, em seguida, para a versão sem prefixo.
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// Verificar se estamos definindo um valor
		se (valor !== indefinido) {
			tipo = tipo de valor;

			// Converter "+=" ou "-=" em números relativos (trac-7345)
			se ( type === "string" && ( ret = rcssNum.exec( value ) ) && ret[ 1 ] ) {
				valor = ajustarCSS(elemento, nome, ret);

				// Corrige o bug trac-9237
				tipo = "número";
			}

			// Certifique-se de que os valores nulos e NaN não estejam definidos (trac-7116)
			se (valor == nulo || valor !== valor) {
				retornar;
			}

			// Se o valor for um número, adicione `px` para determinadas propriedades CSS.
			se ( tipo === "número" ) {
				valor += ret && ret[ 3 ] || ( isAutoPx( origName ) ? "px" : "" );
			}

			// Suporte: IE <= 9 - 11+
			// As propriedades background-* de um elemento clonado afetam o elemento de origem (trac-8908)
			se ( isIE && valor === "" && nome.indexOf( "background" ) === 0 ) {
				estilo[ nome ] = "herdar";
			}

			// Se um gancho foi fornecido, use esse valor; caso contrário, defina o valor especificado.
			se ( !ganchos || !( "definido" em ganchos ) ||
				( valor = hooks.set( elem, valor, extra ) ) !== undefined ) {

				se (isCustomProp) {
					estilo.setProperty( nome, valor );
				} outro {
					estilo[nome] = valor;
				}
			}

		} outro {

			// Se um gancho foi fornecido, obtenha o valor não computado a partir dele.
			se ( ganchos && "obter" em ganchos &&
				( ret = hooks.get( elem, false, extra ) ) !== undefined ) {

				retornar ret;
			}

			Caso contrário, basta obter o valor do objeto de estilo.
			retornar estilo[ nome ];
		}
	},

	css: função( elemento, nome, extra, estilos ) {
		var val, num, hooks,
			origName = cssCamelCase( nome ),
			isCustomProp = rcustomProp.test( nome );

		// Certifique-se de que estamos trabalhando com o nome correto. Nós não
		// Deseja modificar o valor se for uma propriedade personalizada do CSS?
		// já que são definidos pelo usuário.
		se ( !isCustomProp ) {
			nome = finalPropName(origName);
		}

		// Tente o nome com prefixo seguido do nome sem prefixo
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// Se um gancho foi fornecido, obtenha o valor calculado a partir dele.
		se ( hooks && "get" em hooks ) {
			val = hooks.get( elem, true, extra );
		}

		Caso contrário, se existir uma maneira de obter o valor calculado, use-a.
		se (val === indefinido) {
			val = curCSS(elemento, nome, estilos);
		}

		// Converter "normal" para valor calculado
		se ( val === "normal" && nome em cssNormalTransform ) {
			val = cssNormalTransform[ nome ];
		}

		// Tornar numérico se forçado ou se um qualificador foi fornecido e o valor parece numérico
		se ( extra === "" || extra ) {
			num = parseFloat( val );
			retornar extra === verdadeiro || éFinito(num)? num || 0: valor;
		}

		retornar val;
	}
} );

jQuery.each( [ "altura", "largura" ], function( _i, dimensão ) {
	jQuery.cssHooks[ dimensão ] = {
		obter: função( elemento, computado, extra ) {
			se (calculado) {

				// Elementos com `display: none` podem ter informações de dimensão se
				// Nós os mostramos de forma invisível.
				retornar jQuery.css( elem, "display" ) === "none" ?
					trocar( elemento, cssShow, função() {
						retornar obterLarguraOuAltura( elemento, dimensão, extra );
					} ) :
					obterLarguraOuAltura( elemento, dimensão, extra );
			}
		},

		definir: função( elemento, valor, extra ) {
			var corresponde,
				estilos = obterEstilos( elemento ),

				// Para evitar forçar um reflow, busque boxSizing somente se necessário (gh-3991)
				isBorderBox = extra &&
					jQuery.css(elem, "boxSizing", false, styles) === "border-box",
				subtrair = extra?
					boxModelAdjustment(
						elemento,
						dimensão,
						extra,
						isBorderBox,
						estilos
					):
					0;

			// Converter para pixels se for necessário ajustar os valores
			se ( subtrair && ( correspondências = rcssNum.exec( valor ) ) &&
				(matches[3] || "px") !== "px") {

				elem.style[ dimensão ] = valor;
				valor = jQuery.css(elemento, dimensão);
			}

			retornar setPositiveNumber( elem, value, subtract );
		}
	};
} );

// Esses ganchos são usados ​​pelo animate para expandir propriedades
jQuery.each( {
	margem: "",
	preenchimento: "",
	borda: "Largura"
}, função( prefixo, sufixo ) {
	jQuery.cssHooks[ prefixo + sufixo ] = {
		expandir: função( valor ) {
			var i = 0,
				expandido = {},

				// Assume um único número se não for uma string
				partes = tipo de valor === "string" ? valor.split( " " ) : [ valor ];

			para ( ; i < 4; i++ ) {
				expandido[ prefixo + cssExpand[ i ] + sufixo ] =
					partes[ i ] || partes[ i - 2 ] || partes[ 0 ];
			}

			retorno expandido;
		}
	};

	se (prefixo !== "margem" ) {
		jQuery.cssHooks[ prefixo + sufixo ].set = setPositiveNumber;
	}
} );

jQuery.fn.extend( {
	css: função( nome, valor ) {
		retornar acesso( this, função( elem, nome, valor ) {
			var estilos, len,
				mapa = {},
				i = 0;

			se ( Array.isArray( nome ) ) {
				estilos = obterEstilos( elemento );
				len = nome.comprimento;

				para ( ; i < len; i++ ) {
					mapa[ nome[ i ] ] = jQuery.css( elem, nome[ i ], false, estilos );
				}

				retornar mapa;
			}

			valor de retorno !== indefinido?
				jQuery.style(elemento, nome, valor):
				jQuery.css(elemento, nome);
		}, nome, valor, argumentos.comprimento > 1 );
	}
} );

função Tween( elem, opções, prop, end, easing ) {
	retornar novo Tween.prototype.init( elem, options, prop, end, easing );
}
jQuery.Tween = Tween;

Tween.prototype = {
	construtor: Intermediário,
	init: função( elem, opções, prop, end, easing, unit ) {
		este.elemento = elemento;
		this.prop = prop;
		this.easing = easing || jQuery.easing._default;
		this.options = opções;
		this.start = this.now = this.cur();
		isto.fim = fim;
		this.unit = unit || ( isAutoPx( prop ) ? "px" : "" );
	},
	cur: função() {
		var hooks = Tween.propHooks[ this.prop ];

		retornar hooks && hooks.get ?
			hooks.get( this ) :
			Tween.propHooks._default.get( this );
	},
	executar: função( porcentagem ) {
		var facilitado,
			hooks = Tween.propHooks[ this.prop ];

		se (this.options.duration) {
			this.pos = eased = jQuery.easing[ this.easing ](
				porcentagem, this.options.duration * porcentagem, 0, 1, this.options.duration
			);
		} outro {
			this.pos = facilitado = percentual;
		}
		isto.agora = ( isto.fim - isto.início ) * facilitado + isto.início;

		se ( this.options.step ) {
			this.options.step.call( this.elem, this.now, this );
		}

		se ( hooks && hooks.set ) {
			hooks.set( this );
		} outro {
			Tween.propHooks._default.set( this );
		}
		devolva isto;
	}
};

Tween.prototype.init.prototype = Tween.prototype;

Tween.propHooks = {
	_padrão: {
		obter: função( tween ) {
			resultado da variável;

			// Use uma propriedade diretamente no elemento quando ele não for um elemento DOM,
			// ou quando não existe nenhuma propriedade de estilo correspondente.
			se ( tween.elem.nodeType !== 1 ||
				tween.elem[ tween.prop ] != null && tween.elem.style[ tween.prop ] == null ) {
				retornar tween.elem[ tween.prop ];
			}

			// Passar uma string vazia como terceiro parâmetro para .css irá automaticamente
			// Tenta usar parseFloat e, se a análise falhar, recorre a uma string.
			// Valores simples como "10px" são analisados ​​e convertidos para Float;
			// Valores complexos como "rotate(1rad)" são retornados como estão.
			resultado = jQuery.css( tween.elem, tween.prop, "" );

			// Strings vazias, nulas, indefinidas e "auto" são convertidas em 0.
			retornar !resultado || resultado === "automático" ? 0 : resultado;
		},
		definir: função( tween ) {

			// Use o gancho de etapas para compatibilidade com versões anteriores.
			// Use cssHook se estiver presente.
			// Use .style se disponível e use propriedades simples quando disponíveis.
			se ( jQuery.fx.step[ tween.prop ] ) {
				jQuery.fx.step[ tween.prop ]( tween );
			} senão se ( tween.elem.nodeType === 1 && (
				jQuery.cssHooks[ tween.prop ] ||
					tween.elem.style[ finalPropName( tween.prop ) ] != null ) ) {
				jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
			} outro {
				tween.elem[ tween.prop ] = tween.now;
			}
		}
	}
};

jQuery.easing = {
	linear: função( p ) {
		retornar p;
	},
	swing: função( p ) {
		retornar 0,5 - Math.cos( p * Math.PI ) / 2;
	},
	_padrão: "balanço"
};

jQuery.fx = Tween.prototype.init;

// Ponto de extensão de retrocompatibilidade <1.8
jQuery.fx.step = {};

var
	fxAgora, em andamento,
	rfxtypes = /^(?:toggle|show|hide)$/,
	rrun = /queueHooks$/;

função agendar() {
	se (em andamento) {
		se ( document$1.hidden === false && window.requestAnimationFrame ) {
			janela.requestAnimationFrame( agendamento );
		} outro {
			janela.setTimeout( agendamento, 13 );
		}

		jQuery.fx.tick();
	}
}

// Animações criadas de forma síncrona serão executadas de forma síncrona
função criarFxAgora() {
	janela.setTimeout( função() {
		fxNow = indefinido;
	} );
	retornar ( fxNow = Date.now() );
}

// Gere parâmetros para criar uma animação padrão
função genFx( tipo, incluirLargura ) {
	var qual,
		i = 0,
		atributos = { altura: tipo };

	// Se incluirmos a largura, o valor do passo é 1 para todos os valores de cssExpand,
	// Caso contrário, o valor do passo é 2 para ignorar Esquerda e Direita
	incluirLargura = incluirLargura ? 1 : 0;
	para ( ; i < 4; i += 2 - larguraInclusa ) {
		qual = cssExpand[ i ];
		attrs["margin" + which] = attrs["padding" + which] = type;
	}

	se (incluirLargura) {
		attrs.opacity = attrs.width = type;
	}

	retornar atributos;
}

função criarTween( valor, prop, animação ) {
	var tween,
		coleção = ( Animation.tweeners[ prop ] || [] ).concat( Animation.tweeners[ "*" ] ),
		índice = 0,
		comprimento = comprimento da coleção;
	para ( ; índice < comprimento; índice++ ) {
		if ( ( tween = collection[ index ].call( animation, prop, value ) ) ) {

			// Terminamos com esta propriedade
			retornar interpolação;
		}
	}
}

função defaultPrefilter( elem, props, opts ) {
	var prop, value, toggle, hooks, oldfire, propTween, restoreDisplay, display,
		isBox = "width" in props || "height" in props,
		anim = isto,
		orig = {},
		estilo = elem.estilo,
		oculto = elem.nodeType && isHiddenWithinTree( elem ),
		dataShow = dataPriv.get( elem, "fxshow" );

	// Animações que ignoram a fila sequestram os ganchos de efeitos
	se ( !opts.queue ) {
		hooks = jQuery._queueHooks( elem, "fx" );
		se ( hooks.unqueued == null ) {
			hooks.unqueued = 0;
			oldfire = hooks.empty.fire;
			hooks.empty.fire = function() {
				se ( !hooks.unqueued ) {
					fogo antigo();
				}
			};
		}
		hooks.unqueued++;

		anim.always( function() {

			// Garanta que o manipulador completo seja chamado antes que isso seja concluído
			anim.always( function() {
				hooks.unqueued--;
				if ( !jQuery.queue( elem, "fx" ).length ) {
					hooks.empty.fire();
				}
			} );
		} );
	}

	// Detectar animações de mostrar/ocultar
	para (prop em props) {
		valor = props[ prop ];
		se ( rfxtypes.test( valor ) ) {
			excluir props[ prop ];
			alternar = alternar || valor === "alternar";
			se ( valor === ( oculto ? "ocultar" : "mostrar" ) ) {

				// Finja estar oculto se isto for um "show" e
				// Ainda existem dados de uma operação de mostrar/ocultar interrompida
				se ( valor === "mostrar" && dataShow && dataShow[ prop ] !== undefined ) {
					oculto = verdadeiro;

				// Ignorar todos os outros dados de exibição/ocultação que não têm efeito
				} outro {
					continuar;
				}
			}
			orig[ prop ] = dataShow && dataShow[ prop ] || jQuery.style(elem, prop);
		}
	}

	// Abortar se esta operação não tiver efeito, como .hide().hide()
	propTween = !jQuery.isEmptyObject( props );
	if ( !propTween && jQuery.isEmptyObject( orig ) ) {
		retornar;
	}

	// Restringir os estilos "overflow" e "display" durante as animações da caixa
	se ( isBox && elem.nodeType === 1 ) {

		// Suporte: IE <= 9 - 11+
		// Registre todos os 3 atributos de estouro, pois o IE não infere a abreviação.
		// de overflowX e overflowY com valores idênticos.
		opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];

		// Identificar um tipo de exibição, dando preferência aos dados antigos de mostrar/ocultar em vez da cascata CSS
		restaurarDisplay = dataShow && dataShow.display;
		se (restoreDisplay == null) {
			restaurarDisplay = dataPriv.get(elem, "display");
		}
		display = jQuery.css( elem, "display" );
		se ( exibir === "nenhum" ) {
			se ( restaurarExibir ) {
				exibir = restaurarExibir;
			} outro {

				// Obtenha valores não vazios forçando temporariamente a visibilidade.
				mostrarOcultar( [ elem ], verdadeiro );
				restoreDisplay = elem.style.display || restoreDisplay;
				display = jQuery.css( elem, "display" );
				mostrarOcultar( [ elem ] );
			}
		}

		// Animar elementos em linha como blocos em linha
		se ( exibir === "inline" || exibir === "inline-block" && restaurarExibição != nulo ) {
			if ( jQuery.css( elem, "float" ) === "none" ) {

				// Restaura o valor de exibição original ao final das animações de mostrar/ocultar.
				se ( !propTween ) {
					anim.done( function() {
						estilo.exibir = restaurarExibição;
					} );
					se (restoreDisplay == null) {
						exibir = estilo.exibir;
						restoreDisplay = display === "nenhum" ? "" : display;
					}
				}
				style.display = "inline-block";
			}
		}
	}

	se (opts.overflow) {
		style.overflow = "oculto";
		anim.always( function() {
			style.overflow = opts.overflow[ 0 ];
			style.overflowX = opts.overflow[ 1 ];
			style.overflowY = opts.overflow[ 2 ];
		} );
	}

	// Implementar animações de mostrar/ocultar
	propTween = falso;
	para (prop em orig) {

		// Configuração geral de exibição/ocultação para esta animação de elemento
		se ( !propTween ) {
			se ( dataShow ) {
				se ( "oculto" em dataShow ) {
					oculto = dataShow.oculto;
				}
			} outro {
				dataShow = dataPriv.set( elem, "fxshow", { display: restoreDisplay } );
			}

			// Armazena o valor oculto/visível para a alternância, de forma que `.stop().toggle()` "inverta"
			se (alternar) {
				dataShow.hidden = !hidden;
			}

			// Exibir elementos antes de animá-los
			se (oculto) {
				mostrarOcultar( [ elem ], verdadeiro );
			}

			// eslint-disable-next-line no-loop-func
			anim.done( function() {

				// A etapa final de uma animação de "ocultar" consiste em, de fato, ocultar o elemento.
				se ( !oculto ) {
					mostrarOcultar( [ elem ] );
				}
				dataPriv.remove( elem, "fxshow" );
				para (prop em orig) {
					jQuery.style(elem, prop, orig[prop]);
				}
			} );
		}

		// Configuração por propriedade
		propTween = createTween( hidden ? dataShow[ prop ] : 0, prop, anim );
		se ( !( prop em dataShow ) ) {
			dataShow[ prop ] = propTween.start;
			se (oculto) {
				propTween.end = propTween.start;
				propTween.start = 0;
			}
		}
	}
}

função propFilter( props, specialEasing ) {
	var index, name, easing, value, hooks;

	// camelCase, specialEasing e expand cssHook pass
	para ( índice em props ) {
		nome = cssCamelCase( índice );
		easing = specialEasing[ nome ];
		valor = props[ índice ];
		se ( Array.isArray( valor ) ) {
			suavização = valor[ 1 ];
			valor = props[ índice ] = valor[ 0 ];
		}

		se (índice !== nome) {
			props[ nome ] = valor;
			excluir props[ index ];
		}

		hooks = jQuery.cssHooks[ nome ];
		se ( hooks && "expandir" em hooks ) {
			valor = hooks.expand( valor );
			excluir props[ nome ];

			// Não é exatamente $.extend, isso não sobrescreverá as chaves existentes.
			// Reutilizando 'index' porque temos o "nome" correto
			para (índice em valor) {
				se ( !( índice em props ) ) {
					props[ index ] = value[ index ];
					specialEasing[ índice ] = easing;
				}
			}
		} outro {
			specialEasing[ nome ] = easing;
		}
	}
}

função Animação( elemento, propriedades, opções ) {
	resultado da variável,
		parou,
		índice = 0,
		comprimento = Animation.prefilters.length,
		adiado = jQuery.Deferred().always( function() {

			// Não selecione o elemento correspondente no seletor :animated
			excluir tick.elem;
		} ),
		tick = função() {
			se (parado) {
				retornar falso;
			}
			var currentTime = fxNow || createFxNow(),
				restante = Math.max( 0, animation.startTime + animation.duration - currentTime ),

				porcentagem = 1 - ( restante / duração.da.animação || 0 ),
				índice = 0,
				comprimento = animação.interpolações.comprimento;

			para ( ; índice < comprimento; índice++ ) {
				animação.tweens[ índice ].run( percent );
			}

			deferred.notifyWith(elem, [animation, percent, remaining]);

			Se ainda houver mais a fazer, ceda o lugar.
			se (percentual < 1 && comprimento) {
				devolver o restante;
			}

			// Se esta fosse uma animação vazia, sintetize uma notificação de progresso final.
			se ( !comprimento ) {
				deferred.notifyWith( elem, [ animação, 1, 0 ] );
			}

			// Resolva a animação e relate sua conclusão.
			deferred.resolveWith(elem, [animation]);
			retornar falso;
		},
		animação = promessa adiada( {
			elemento: elemento,
			props: jQuery.extend( {}, properties ),
			opts: jQuery.extend( true, {
				Alívio especial: {},
				suavização: jQuery.easing._default
			}, opções ),
			propriedadesoriginais: propriedades,
			opçõesOriginal: opções,
			startTime: fxNow || createFxNow(),
			duração: opções.duração,
			pré-adolescentes: [],
			criarTween: função( prop, end ) {
				var tween = jQuery.Tween( elem, animation.opts, prop, end,
					animation.opts.specialEasing[ prop ] || animation.opts.easing );
				animação.tweens.push( tween );
				retornar interpolação;
			},
			parar: função( ir para o fim ) {
				var index = 0,

					// Se formos até o final, queremos executar todas as animações intermediárias.
					// caso contrário, pulamos esta parte
					comprimento = gotoEnd ? animation.tweens.length : 0;
				se (parado) {
					devolva isto;
				}
				parado = verdadeiro;
				para ( ; índice < comprimento; índice++ ) {
					animação.tweens[ índice ].run( 1 );
				}

				// Resolver quando reproduzirmos o último quadro; caso contrário, rejeitar
				se (gotoEnd) {
					deferred.notifyWith( elem, [ animação, 1, 0 ] );
					deferred.resolveWith( elem, [ animação, gotoEnd ] );
				} outro {
					deferred.rejectWith( elem, [ animação, gotoEnd ] );
				}
				devolva isto;
			}
		} ),
		props = animation.props;

	propFilter( props, animation.opts.specialEasing );

	para ( ; índice < comprimento; índice++ ) {
		resultado = Animation.prefilters[ index ].call( animation, elem, props, animation.opts );
		se (resultado) {
			se ( typeof result.stop === "function" ) {
				jQuery._queueHooks( animation.elem, animation.opts.queue ).stop =
					resultado.parar.vincular(resultado);
			}
			retornar resultado;
		}
	}

	jQuery.map( props, createTween, animation );

	se ( typeof animation.opts.start === "function" ) {
		animation.opts.start.call( elem, animation );
	}

	// Anexar funções de retorno de chamada das opções
	animação
		.progresso( animação.opções.progresso )
		.concluído(animação.opts.concluído, animação.opts.concluído)
		.fail( animation.opts.fail )
		.sempre( animação.opções.sempre );

	jQuery.fx.timer(
		jQuery.extend( tick, {
			elemento: elemento,
			anim: animação,
			fila: animation.opts.queue
		} )
	);

	retornar animação;
}

jQuery.Animation = jQuery.extend( Animation, {

	interlúdios: {
		"*": [ função( prop, valor ) {
			var tween = this.createTween( prop, value );
			ajustarCSS( tween.elem, prop, rcssNum.exec( valor ), tween );
			retornar interpolação;
		} ]
	},

	interpolador: função( props, callback ) {
		se ( typeof props === "function" ) {
			callback = props;
			props = [ "*" ];
		} outro {
			props = props.match( rnothtmlwhite );
		}

		var prop,
			índice = 0,
			comprimento = props.length;

		para ( ; índice < comprimento; índice++ ) {
			prop = props[ index ];
			Animation.tweeners[ prop ] = Animation.tweeners[ prop ] || [];
			Animation.tweeners[ prop ].unshift( callback );
		}
	},

	pré-filtros: [ pré-filtro padrão ],

	pré-filtro: função( callback, prepend ) {
		se (prepend) {
			Animação.prefilters.unshift( callback );
		} outro {
			Animação.prefilters.push( callback );
		}
	}
} );

jQuery.speed = function( speed, easing, fn ) {
	var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
		completo: fn || suavização ||
			tipo de velocidade === "função" && velocidade,
		duração: velocidade,
		suavização: fn && suavização || suavização && tipo de suavização !== "função" && suavização
	};

	// Vá para o estado final se os efeitos estiverem desativados
	se ( jQuery.fx.off ) {
		opt.duration = 0;

	} outro {
		se ( typeof opt.duration !== "número" ) {
			se (opt.duration em jQuery.fx.speeds) {
				opt.duration = jQuery.fx.speeds[ opt.duration ];

			} outro {
				opt.duration = jQuery.fx.speeds._default;
			}
		}
	}

	// Normalizar opt.queue - verdadeiro/indefinido/nulo -> "fx"
	se ( opt.queue == null || opt.queue === true ) {
		opt.queue = "fx";
	}

	// Enfileiramento
	opt.old = opt.complete;

	opt.complete = função() {
		se ( typeof opt.old === "function" ) {
			opt.old.call( this );
		}

		se ( opt.fila ) {
			jQuery.dequeue( this, opt.queue );
		}
	};

	retornar opção;
};

jQuery.fn.extend( {
	fadeTo: função( velocidade, para, suavização, retorno de chamada ) {

		// Exibir quaisquer elementos ocultos após definir a opacidade para 0
		return this.filter( isHiddenWithinTree ).css( "opacity", 0 ).show()

			// Animar até o valor especificado
			.end().animate( { opacity: to }, speed, easing, callback );
	},
	animar: função( prop, velocidade, easing, callback ) {
		var empty = jQuery.isEmptyObject( prop ),
			optall = jQuery.speed( velocidade, easing, callback ),
			doAnimation = função() {

				// Operar em uma cópia da propriedade para que o ajuste por propriedade não seja perdido
				var anim = Animation( this, jQuery.extend( {}, prop ), optall );

				// Animações vazias ou finalizações são resolvidas imediatamente
				se ( vazio || dataPriv.get( this, "finish" ) ) {
					anim.stop(true);
				}
			};

		doAnimation.finish = doAnimation;

		retornar vazio || optall.queue === falso ?
			this.each( doAnimation ) :
			this.queue( optall.queue, doAnimation );
	},
	parar: função( tipo, limparFila, irParaFim ) {
		var stopQueue = function( hooks ) {
			var stop = hooks.stop;
			excluir hooks.stop;
			parar( ir para o fim );
		};

		se ( typeof tipo !== "string" ) {
			gotoEnd = clearQueue;
			clearQueue = tipo;
			tipo = indefinido;
		}
		se ( limparFila ) {
			this.queue( type || "fx", [] );
		}

		retorne this.each( function() {
			var dequeue = true,
				índice = tipo != nulo && tipo + "queueHooks",
				temporizadores = jQuery.timers,
				dados = dataPriv.get( this );

			se ( índice ) {
				se (dados[índice] && dados[índice].parar) {
					pararFila( dados[ índice ] );
				}
			} outro {
				para (índice em dados) {
					se (dados[índice] && dados[índice].parar && rrun.test(índice)) {
						pararFila( dados[ índice ] );
					}
				}
			}

			para ( índice = timers.length; índice--; ) {
				se ( timers[ index ].elem === this &&
					( tipo == nulo || temporizadores[ índice ].fila === tipo ) ) {

					timers[ index ].anim.stop( gotoEnd );
					remover da fila = falso;
					temporizadores.splice( índice, 1 );
				}
			}

			// Inicie o próximo passo na fila se o último passo não foi forçado.
			// Atualmente, os temporizadores chamarão seus callbacks completos, o que
			// irá remover da fila, mas somente se eles foram gotoEnd.
			se ( remover da fila || !ir para o fim ) {
				jQuery.dequeue( this, type );
			}
		} );
	},
	finalizar: função( tipo ) {
		se (tipo !== falso) {
			tipo = tipo || "fx";
		}
		retorne this.each( function() {
			índice da variável,
				dados = dataPriv.get( this ),
				fila = dados[ tipo + "fila" ],
				hooks = data[ type + "queueHooks" ],
				temporizadores = jQuery.timers,
				comprimento = fila ? comprimento.da.fila : 0;

			// Habilitar sinalizador de finalização em dados privados
			data.finish = true;

			// Esvazie a fila primeiro
			jQuery.queue( this, type, [] );

			se ( hooks && hooks.stop ) {
				hooks.stop.call( this, true );
			}

			Procure por animações ativas e finalize-as.
			para ( índice = timers.length; índice--; ) {
				se ( timers[ index ].elem === this && timers[ index ].queue === type ) {
					timers[ index ].anim.stop( true );
					temporizadores.splice( índice, 1 );
				}
			}

			// Procure por animações na fila antiga e finalize-as.
			para ( índice = 0; índice < comprimento; índice++ ) {
				se ( fila[ índice ] && fila[ índice ].finish ) {
					fila[ índice ].finalizar.chamar( isto );
				}
			}

			// Desativar a flag de finalização
			excluir dados.finalizar;
		} );
	}
} );

jQuery.each( [ "alternar", "mostrar", "ocultar" ], function( _i, nome ) {
	var cssFn = jQuery.fn[nome];
	jQuery.fn[ nome ] = function( velocidade, easing, callback ) {
		retornar velocidade == nulo || tipo de velocidade === "booleano" ?
			cssFn.apply( this, arguments ) :
			this.animate( genFx( nome, true ), velocidade, easing, callback );
	};
} );

// Gerar atalhos para animações personalizadas
jQuery.each( {
	slideDown: genFx( "show" ),
	slideUp: genFx( "ocultar" ),
	slideToggle: genFx( "toggle" ),
	fadeIn: { opacity: "show" },
	fadeOut: { opacidade: "ocultar" },
	fadeToggle: { opacity: "toggle" }
}, função( nome, propriedades ) {
	jQuery.fn[ nome ] = function( velocidade, easing, callback ) {
		retornar this.animate( props, speed, easing, callback );
	};
} );

jQuery.timers = [];
jQuery.fx.tick = função() {
	temporizador variável,
		i = 0,
		temporizadores = jQuery.tempores;

	fxNow = Date.now();

	para ( ; i < timers.length; i++ ) {
		temporizador = temporizadores[ i ];

		// Execute o temporizador e remova-o com segurança quando terminar (permitindo a remoção externa)
		se ( !timer() && timers[ i ] === timer ) {
			temporizadores.splice( i--, 1 );
		}
	}

	se ( !timers.length ) {
		jQuery.fx.stop();
	}
	fxNow = indefinido;
};

jQuery.fx.timer = function( timer ) {
	jQuery.timers.push( timer );
	jQuery.fx.start();
};

jQuery.fx.start = function() {
	se (em andamento) {
		retornar;
	}

	em andamento = verdadeiro;
	agendar();
};

jQuery.fx.stop = função() {
	em andamento = nulo;
};

jQuery.fx.speeds = {
	lento: 600,
	rápido: 200,

	// Velocidade padrão
	_padrão: 400
};

// Baseado no plugin de Clint Helfers, com permissão.
jQuery.fn.delay = function( tempo, tipo ) {
	tempo = jQuery.fx ? jQuery.fx.speeds[ tempo ] || tempo : tempo;
	tipo = tipo || "fx";

	retornar this.queue( tipo, função( próximo, hooks ) {
		var timeout = window.setTimeout( next, time );
		hooks.stop = função() {
			janela.limparTempoExtra ( tempo limite );
		};
	} );
};

var rfocusable = /^(?:input|select|textarea|button)$/i,
	rclickable = /^(?:a|area)$/i;

jQuery.fn.extend( {
	prop: função( nome, valor ) {
		retornar acesso( this, jQuery.prop, nome, valor, arguments.length > 1 );
	},

	removeProp: função( nome ) {
		retorne this.each( function() {
			delete this[ jQuery.propFix[ name ] || name ];
		} );
	}
} );

jQuery.extend( {
	prop: function( elem, name, value ) {
		var ret, anzóis,
			nType = elem.nodeType;

		// Não obtenha/defina propriedades em nós de texto, comentário e atributo
		se ( nType === 3 || nType === 8 || nType === 2 ) {
			retornar;
		}

		se ( nType !== 1 || !jQuery.isXMLDoc( elem ) ) {

			// Corrigir nome e anexar ganchos
			nome = jQuery.propFix[ nome ] || nome;
			hooks = jQuery.propHooks[ nome ];
		}

		se (valor !== indefinido) {
			se ( ganchos && "definido" em ganchos &&
				( ret = hooks.set( elem, value, name ) ) !== undefined ) {
				retornar ret;
			}

			retornar (elem[nome] = valor);
		}

		se ( hooks && "get" em hooks && ( ret = hooks.get( elem, name ) ) !== null ) {
			retornar ret;
		}

		retornar elem[ nome ];
	},

	propHooks: {
		tabIndex: {
			obter: função( elemento ) {

				// Suporte: IE <= 9 - 11+
				// elem.tabIndex nem sempre retorna o
				// Valor correto quando não tiver sido explicitamente definido
				// Use a recuperação de atributos adequada (trac-12072)
				var tabindex = elem.getAttribute("tabindex");

				se ( tabindex ) {
					return parseInt( tabindex, 10 );
				}

				se (
					rfocusable.test( elem.nodeName ) ||

					// O valor da propriedade `tabIndex` de uma âncora sem href é `0` e
					// O valor do atributo `tabindex` é: `null`. Queremos `-1`.
					rclickable.test( elem.nodeName ) && elem.href
				) {
					retornar 0;
				}

				retornar -1;
			}
		}
	},

	propFix: {
		"para": "htmlFor",
		"classe": "nomeDaClasse"
	}
} );

// Suporte: IE <= 11+
// Acessar a propriedade selectedIndex força o navegador a respeitar
// Definindo a opção selecionada. O getter garante uma opção padrão.
// é selecionado quando em um grupo de opções. Regra ESLint "no-unused-expressions"
// está desativado para este código, pois considera tais acessos como operações nulas.
se ( isIE ) {
	jQuery.propHooks.selected = {
		obter: função( elemento ) {

			var parent = elem.parentNode;
			se (pai && pai.nóPai) {
				// eslint-disable-next-line no-unused-expressions
				parent.parentNode.selectedIndex;
			}
			retornar nulo;
		},
		definir: função( elemento ) {


			var parent = elem.parentNode;
			se (pai) {
				// eslint-disable-next-line no-unused-expressions
				parent.selectedIndex;

				se (parent.parentNode) {
					// eslint-disable-next-line no-unused-expressions
					parent.parentNode.selectedIndex;
				}
			}
		}
	};
}

jQuery.each( [
	"tabIndex",
	"somente leitura",
	"comprimento máximo",
	"espaçamento entre células",
	"cellPadding",
	"rowSpan",
	"colSpan",
	"useMap",
	"frameBorder",
	"contentEditable"
], função() {
	jQuery.propFix[ this.toLowerCase() ] = this;
} );

// Remover e ocultar espaços em branco de acordo com a especificação HTML
// https://infra.spec.whatwg.org/#strip-and-collapse-ascii-whitespace
função stripAndCollapse( valor ) {
	var tokens = value.match( rnothtmlwhite ) || [];
	retornar tokens.join( " " );
}

função obterClasse( elemento ) {
	retornar elem.getAttribute && elem.getAttribute( "class" ) || "";
}

função classesToArray( valor ) {
	se ( Array.isArray( valor ) ) {
		valor de retorno;
	}
	se ( typeof valor === "string" ) {
		return value.match( rnothtmlwhite ) || [];
	}
	retornar [];
}

jQuery.fn.extend( {
	adicionarClasse: função( valor ) {
		var classNames, cur, curValue, className, i, finalValue;

		se ( typeof valor === "função" ) {
			retornar this.each( function( j ) {
				jQuery( this ).addClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		classNames = classesToArray( valor );

		se (classNames.length) {
			retorne this.each( function() {
				curValue = getClass( this );
				cur = this.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				se (cur) {
					para ( i = 0; i < classNames.length; i++ ) {
						className = classNames[ i ];
						se ( cur.indexOf( " " + className + " " ) < 0 ) {
							cur += className + " ";
						}
					}

					// Atribua apenas se for diferente para evitar renderização desnecessária.
					valorFinal = stripAndCollapse( cur );
					se ( curValue !== finalValue ) {
						this.setAttribute("class", finalValue);
					}
				}
			} );
		}

		devolva isto;
	},

	removerClasse: função( valor ) {
		var classNames, cur, curValue, className, i, finalValue;

		se ( typeof valor === "função" ) {
			retornar this.each( function( j ) {
				jQuery( this ).removeClass( value.call( this, j, getClass( this ) ) );
			} );
		}

		se ( !argumentos.comprimento ) {
			retornar this.attr( "class", "" );
		}

		classNames = classesToArray( valor );

		se (classNames.length) {
			retorne this.each( function() {
				curValue = getClass( this );

				// Esta expressão está aqui para melhor compressibilidade (veja addClass)
				cur = this.nodeType === 1 && ( " " + stripAndCollapse( curValue ) + " " );

				se (cur) {
					para ( i = 0; i < classNames.length; i++ ) {
						className = classNames[ i ];

						// Remover *todas* as instâncias
						enquanto ( cur.indexOf( " " + className + " " ) > -1 ) {
							cur = cur.replace( " " + className + " ", " " );
						}
					}

					// Atribua apenas se for diferente para evitar renderização desnecessária.
					valorFinal = stripAndCollapse( cur );
					se ( curValue !== finalValue ) {
						this.setAttribute("class", finalValue);
					}
				}
			} );
		}

		devolva isto;
	},

	toggleClass: função( valor, valorEstado ) {
		var classNames, className, i, self;

		se ( typeof valor === "função" ) {
			retornar this.each( function( i ) {
				jQuery( this ).toggleClass(
					valor.chamar( isto, i, obterClasse( isto ), valorEstado ),
					valor do estado
				);
			} );
		}

		se ( typeof stateVal === "boolean" ) {
			retornar stateVal ? this.addClass( valor ) : this.removeClass( valor );
		}

		classNames = classesToArray( valor );

		se (classNames.length) {
			retorne this.each( function() {

				// Alternar nomes de classe individuais
				self = jQuery( this );

				para ( i = 0; i < classNames.length; i++ ) {
					className = classNames[ i ];

					// Verificar cada nome de classe fornecido, lista separada por espaços
					se ( self.hasClass( className ) ) {
						self.removeClass(nomeDaClasse);
					} outro {
						self.addClass(nomeDaClasse);
					}
				}
			} );
		}

		devolva isto;
	},

	hasClass: função( seletor ) {
		var className, elem,
			i = 0;

		className = " " + selector + " ";
		enquanto ( ( elem = this[ i++ ] ) ) {
			se (elem.nodeType === 1 &&
				( " " + stripAndCollapse( getClass( elem ) ) + " " ).indexOf( className ) > -1 ) {
				retornar verdadeiro;
			}
		}

		retornar falso;
	}
} );

jQuery.fn.extend( {
	val: função( valor ) {
		var ganchos, ret, valueIsFunction,
			elem = this[ 0 ];

		se ( !argumentos.comprimento ) {
			se (elem) {
				hooks = jQuery.valHooks[ elem.type ] ||
					jQuery.valHooks[ elem.nodeName.toLowerCase() ];

				se ( ganchos &&
					"pegar" em ganchos &&
					(ret = hooks.get(elem, "valor ")) !== undefined
				) {
					retornar ret;
				}

				ret = elem.value;

				// Lidar com casos em que o valor é nulo/indefinido ou numérico
				retornar ret == nulo? "" : ret;
			}

			retornar;
		}

		valorÉFunção = tipo de valor === "função";

		retornar this.each( function( i ) {
			var val;

			se ( this.nodeType !== 1 ) {
				retornar;
			}

			se (valorÉFunção) {
				val = value.call( this, i, jQuery( this ).val() );
			} outro {
				val = valor;
			}

			// Tratar nulo/indefinido como ""; converter números em string
			se (val == nulo) {
				val = "";

			} else if ( typeof val === "number" ) {
				val += "";

			} else if ( Array.isArray( val ) ) {
				val = jQuery.map( val, function( value ) {
					valor de retorno == nulo ? "" : valor + "";
				} );
			}

			hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];

			// Se o conjunto retornar indefinido, recorra à configuração normal.
			if ( !hooks || !( "set" in hooks ) || hooks.set( this, val, "value" ) === undefined ) {
				este.valor = val;
			}
		} );
	}
} );

jQuery.extend( {
	valHooks: {
		selecionar: {
			obter: função( elemento ) {
				valor da variável, opção, i,
					opções = elem.opções,
					índice = elem.selectedIndex,
					um = elem.type === "selecione-um",
					valores = um ? nulo : [],
					max = um ? índice + 1 : opções.comprimento;

				se (índice < 0) {
					i = máximo;

				} outro {
					i = um ? índice : 0;
				}

				// Percorra todas as opções selecionadas
				para ( ; i < max; i++ ) {
					opção = opções[ i ];

					se (opção.selecionada &&

							// Não retorne opções que estejam desabilitadas ou em um grupo de opções desabilitado
							!option.disabled &&
							( !option.parentNode.disabled ||
								!nodeName( option.parentNode, "optgroup" ) ) ) {

						// Obtenha o valor específico da opção
						valor = jQuery( opção ).val();

						// Não precisamos de um array para uma única seleção
						se (um) {
							valor de retorno;
						}

						// As seleções múltiplas retornam uma matriz
						valores.push( valor );
					}
				}

				valores de retorno;
			},

			definir: função( elemento, valor ) {
				var optionSet, opção,
					opções = elem.opções,
					valores = jQuery.makeArray( valor ),
					i = opções.comprimento;

				enquanto ( i-- ) {
					opção = opções[ i ];

					se ( ( opção.selecionada =
						jQuery.inArray( jQuery( option ).val(), values ​​) > -1
					) ) {
						optionSet = true;
					}
				}

				// Forçar os navegadores a se comportarem de forma consistente quando um valor diferente for definido.
				se ( !optionSet ) {
					elem.selectedIndex = -1;
				}
				valores de retorno;
			}
		}
	}
} );

se ( isIE ) {
	jQuery.valHooks.option = {
		obter: função( elemento ) {

			var val = elem.getAttribute("valor");
			retornar val != null ?
				valor:

				// Suporte: IE <=10 - 11+
				// option.text gera exceções (trac-14686, trac-14858)
				// Remover e recolher espaços em branco
				// https://html.spec.whatwg.org/#strip-and-collapse-whitespace
				stripAndCollapse( jQuery.text( elem ) );
		}
	};
}

// Obtentor/definidor de botões de opção e caixas de seleção
jQuery.each( [ "radio", "checkbox" ], function() {
	jQuery.valHooks[ this ] = {
		definir: função( elemento, valor ) {
			se ( Array.isArray( valor ) ) {
				retornar ( elem.checked = jQuery.inArray( jQuery( elem ).val(), value ) > -1 );
			}
		}
	};
} );

var rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
	stopPropagationCallback = function( e ) {
		e.stopPropagation();
	};

jQuery.extend( jQuery.event, {

	gatilho: função( evento, dados, elemento, somenteManipuladores ) {

		var i, cur, tmp, bubbleType, ontype, handle, special, lastElement,
			eventPath = [ elem || document$1 ],
			tipo = hasOwn.call(event, "tipo") ? event.type : event,
			namespaces = hasOwn.call( event, "namespace" ) ? event.namespace.split( "." ) : [];

		cur = lastElement = tmp = elem = elem || documento$1;

		// Não execute eventos em nós de texto e comentário
		se ( elem.nodeType === 3 || elem.nodeType === 8 ) {
			retornar;
		}

		// As transformações de foco/desfoque se transformam em foco interno/externo; certifique-se de que não as estejamos acionando agora.
		if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
			retornar;
		}

		se ( type.indexOf( "." ) > -1 ) {

			// Gatilho com namespace; crie uma expressão regular para corresponder ao tipo de evento em handle()
			namespaces = type.split( "." );
			tipo = namespaces.shift();
			namespaces.sort();
		}
		ontype = type.indexOf( ":" ) < 0 && "on" + type;

		// O chamador pode passar um objeto jQuery.Event, um objeto ou apenas uma string de tipo de evento.
		evento = evento[ jQuery.expando ] ?
			evento:
			novo jQuery.Event( tipo, typeof evento === "objeto" && evento );

		// Máscara de bits de acionamento: & 1 para manipuladores nativos; & 2 para jQuery (sempre verdadeiro)
		event.isTrigger = onlyHandlers ? 2 : 3;
		event.namespace = namespaces.join( "." );
		event.rnamespace = event.namespace ?
			novo RegExp( "(^|\\.)" + namespaces.join( "\\.(?:.*\\.|)" ) + "(\\.|$)" ) :
			nulo;

		// Limpar o evento caso esteja sendo reutilizado
		evento.resultado = indefinido;
		se ( !evento.alvo ) {
			evento.alvo = elem;
		}

		// Clone quaisquer dados recebidos e anexe o evento, criando a lista de argumentos do manipulador
		dados = dados == nulo ?
			[ evento ] :
			jQuery.makeArray( dados, [ evento ] );

		// Permitir que eventos especiais desenhem fora das linhas
		especial = jQuery.event.special[ tipo ] || {};
		if ( !onlyHandlers && special.trigger && special.trigger.apply( elem, data ) === false ) {
			retornar;
		}

		// Determinar o caminho de propagação do evento antecipadamente, conforme a especificação de eventos da W3C (trac-9951)
		// Propaga-se para o documento e depois para a janela; observe a existência de uma variável global ownerDocument (trac-9724)
		if ( !onlyHandlers && !special.noBubble && !isWindow( elem ) ) {

			bubbleType = special.delegateType || type;
			se ( !rfocusMorph.test( bubbleType + type ) ) {
				cur = cur.parentNode;
			}
			for (; cur; cur = cur.parentNode) {
				eventPath.push( cur );
				tmp = cur;
			}

			// Adicione a janela somente se chegarmos ao documento (ou seja, não a um objeto simples ou a um DOM desanexado)
			se ( tmp === ( elem.ownerDocument || document$1 ) ) {
				eventPath.push( tmp.defaultView || tmp.parentWindow || window );
			}
		}

		// Disparar manipuladores no caminho do evento
		i = 0;
		enquanto ( ( cur = eventPath[ i++ ] ) && !event.isPropagationStopped() ) {
			últimoElemento = atual;
			event.type = i > 1 ?
				Tipo de bolha:
				tipo de vinculação especial || tipo;

			// Manipulador jQuery
			handle = ( dataPriv.get( cur, "events" ) || Object.create( null ) )[ event.type ] &&
				dataPriv.get(cur, "handle");
			se ( identificador ) {
				handle.apply( cur, data );
			}

			// Manipulador nativo
			identificador = ontype && cur[ ontype ];
			se (handle && handle.apply && acceptData(cur)) {
				evento.resultado = handle.apply( cur, data );
				se (evento.resultado === falso) {
					evento.prevenirPadrão();
				}
			}
		}
		event.type = tipo;

		// Se ninguém impediu a ação padrão, faça-a agora
		se ( !onlyHandlers && !event.isDefaultPrevented() ) {

			se ( ( !special._padrão ||
				special._default.apply( eventPath.pop(), data ) === false ) &&
				aceitarDados(elem)) {

				// Chama um método DOM nativo no alvo com o mesmo nome do evento.
				// Não execute ações padrão na janela, é aí que entram as variáveis ​​globais (trac-6170)
				se ( ontype && typeof elem[ type ] === "function" && !isWindow( elem ) ) {

					// Não acione novamente um evento onFOO quando chamarmos seu método FOO()
					tmp = elem[ ontype ];

					se ( tmp ) {
						elem[ ontype ] = null;
					}

					// Impede que o mesmo evento seja acionado novamente, visto que já o propagamos acima
					jQuery.event.triggered = tipo;

					se (evento.isPropagationStopped()) {
						últimoElemento.adicionarOuvinteDeEvento( tipo, callbackDePropagaçãoDeParar );
					}

					elem[ tipo ]();

					se (evento.isPropagationStopped()) {
						últimoElemento.removeEventListener( tipo, stopPropagationCallback );
					}

					jQuery.event.triggered = indefinido;

					se ( tmp ) {
						elem[ ontype ] = tmp;
					}
				}
			}
		}

		retornar evento.resultado;
	},

	// Aproveitar a oportunidade de um evento de doadores para simular um evento diferente
	// Usado apenas para eventos `focus(in | out)`
	simular: função( tipo, elemento, evento ) {
		var e = jQuery.extend(
			novo jQuery.Event(),
			evento,
			{
				tipo: tipo,
				éSimulado: verdadeiro
			}
		);

		jQuery.event.trigger( e, null, elem );
	}

} );

jQuery.fn.extend( {

	gatilho: função( tipo, dados ) {
		retorne this.each( function() {
			jQuery.event.trigger( tipo, dados, this );
		} );
	},
	triggerHandler: função( tipo, dados ) {
		var elem = this[ 0 ];
		se (elem) {
			return jQuery.event.trigger( type, data, elem, true );
		}
	}
} );

var localização = janela.localização;

var nonce = { guid: Date.now() };

var rquery = /\?/;

// Análise XML compatível com vários navegadores
jQuery.parseXML = function( data ) {
	var xml, parserErrorElem;
	se ( !dados || tipo de dados !== "string" ) {
		retornar nulo;
	}

	// Compatível com: IE 9 - 11+
	// O IE gera um erro ao usar parseFromString com entrada inválida.
	tentar {
		xml = ( new window.DOMParser() ).parseFromString( data, "text/xml" );
	} catch ( e ) {}

	parserErrorElem = xml && xml.getElementsByTagName( "parsererror" )[ 0 ];
	se ( !xml || parserErrorElem ) {
		jQuery.error("XML inválido: " + (
			parserErrorElem?
				jQuery.map( parserErrorElem.childNodes, function( el ) {
					retornar el.textConteúdo;
				} ).join( "\n" ) :
				dados
		) );
	}
	retornar xml;
};

var
	rbracket = /\[\]$/,
	rCRLF = /\r?\n/g,
	rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
	rsubmittable = /^(?:input|select|textarea|keygen)/i;

função buildParams( prefixo, obj, tradicional, adicionar ) {
	nome da variável;

	se ( Array.isArray( obj ) ) {

		// Serializar item do array.
		jQuery.each( obj, function( i, v ) {
			se ( tradicional || rbracket.test( prefixo ) ) {

				// Tratar cada item da matriz como um escalar.
				adicionar( prefixo, v );

			} outro {

				// Se o item não for escalar (matriz ou objeto), codifique seu índice numérico.
				buildParams(
					prefixo + "[" + ( typeof v === "object" && v != null ? i : "" ) + "]",
					v,
					tradicional,
					adicionar
				);
			}
		} );

	} else if ( !traditional && toType( obj ) === "object" ) {

		// Serializar objeto item.
		para (nome em obj) {
			buildParams( prefix + "[" + nome + "]", obj[ nome ], tradicional, adicionar );
		}

	} outro {

		// Serializa um item escalar.
		adicionar( prefixo, obj );
	}
}

// Serializa uma matriz de elementos de formulário ou um conjunto de
// parênteses chave/valor em uma string de consulta
jQuery.param = function( a, traditional ) {
	prefixo variável,
		s = [],
		adicionar = função( chave, valorOuFunção ) {

			// Se o valor for uma função, invoque-a e use seu valor de retorno.
			var value = typeof valueOrFunction === "function" ?
				valorOuFunção():
				valorOuFunção;

			s[ s.length ] = encodeURIComponent( key ) + "=" +
				encodeURIComponent( valor == null ? "" : valor );
		};

	se ( a == nulo ) {
		retornar "";
	}

	// Se um array foi passado como argumento, assuma que se trata de um array de elementos de formulário.
	if ( Array.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {

		// Serializa os elementos do formulário
		jQuery.each( a, function() {
			adicionar( this.name, this.value );
		} );

	} outro {

		// Se for tradicional, codifique da maneira "antiga" (a maneira 1.3.2 ou anterior).
		// fez isso), caso contrário, codifique os parâmetros recursivamente.
		para (prefixo em a) {
			buildParams( prefixo, a[ prefixo ], tradicional, adicionar );
		}
	}

	// Retorna a serialização resultante
	retornar s.join( "&" );
};

jQuery.fn.extend( {
	serializar: função() {
		return jQuery.param( this.serializeArray() );
	},
	serializarArray: função() {
		retorne this.map( function() {

			// É possível adicionar propHook para "elementos" para filtrar ou adicionar elementos de formulário.
			var elements = jQuery.prop( this, "elements" );
			retornar elementos ? jQuery.makeArray( elementos ) : this;
		} ).filter( function() {
			var tipo = este.tipo;

			// Use .is( ":disabled" ) para que fieldset[disabled] funcione
			return this.name && !jQuery( this ).is( ":disabled" ) &&
				rsubmittable.test( this.nodeName ) && !rsubmitterTypes.test( type ) &&
				( this.checked || !rcheckableType.test( type ) );
		} ).map( function( _i, elem ) {
			var val = jQuery( this ).val();

			se (val == nulo) {
				retornar nulo;
			}

			se ( Array.isArray( val ) ) {
				return jQuery.map( val, function( val ) {
					return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
				} );
			}

			return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
		} ).pegar();
	}
} );

var
	r20 = /%20/g,
	rhash = /#.*$/,
	rantiCache = /([?&])_=[^&]*/,
	rheaders = /^(.*?):[ \t]*([^\r\n]*)$/mg,

	// trac-7653, trac-8125, trac-8152: detecção de protocolo local
	rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
	rnoContent = /^(?:GET|HEAD)$/,
	rprotocol = /^\/\//,

	/* Pré-filtros
	 * 1) São úteis para introduzir tipos de dados personalizados (veja ajax/jsonp.js para um exemplo)
	 * 2) Estes são chamados de:
	 * - ANTES de solicitar um transporte
	 * - APÓS a serialização dos parâmetros (s.data é uma string se s.processData for verdadeiro)
	 * 3) A chave é o tipo de dados
	 * 4) O símbolo genérico "*" pode ser usado
	 * 5) A execução começará com o tipo de dados de transporte e, em seguida, continuará até "*", se necessário.
	 */
	pré-filtros = {},

	/* Associações de transporte
	 * 1) A chave é o tipo de dados
	 * 2) O símbolo genérico "*" pode ser usado
	 * 3) A seleção começará com o tipo de dados de transporte e, em seguida, passará para "*" se necessário.
	 */
	transports = {},

	// Evitar sequência de caracteres de prólogo de comentário (trac-10098); necessário para evitar erros de lint e compressão
	todosOsTipos = "*/".concat( "*" ),

	// Tag de âncora para analisar a origem do documento
	originAnchor = document$1.createElement( "a" );

originAnchor.href = location.href;

// Construtor base para jQuery.ajaxPrefilter e jQuery.ajaxTransport
função adicionarAosPré-filtrosOuTransportes( estrutura ) {

	// dataTypeExpression é opcional e tem como padrão "*"
	retornar função( dataTypeExpression, func ) {

		se ( typeof dataTypeExpression !== "string" ) {
			func = dataTypeExpression;
			dataTypeExpression = "*";
		}

		var tipoDeDados,
			i = 0,
			dataTypes = dataTypeExpression.toLowerCase().match( rnothtmlwhite ) || [];

		se ( typeof func === "function" ) {

			// Para cada tipo de dados na expressão de tipo de dados
			enquanto ( ( dataType = dataTypes[ i++ ] ) ) {

				// Adicionar no início, se solicitado
				se ( dataType[ 0 ] === "+" ) {
					dataType = dataType.slice( 1 ) || "*";
					( estrutura[ tipoDeDados ] = estrutura[ tipoDeDados ] || [] ).unshift( func );

				// Caso contrário, acrescente
				} outro {
					( estrutura[ tipoDeDados ] = estrutura[ tipoDeDados ] || [] ).push( func );
				}
			}
		}
	};
}

// Função de inspeção básica para pré-filtros e transportes
função inspectPrefiltersOrTransports( estrutura, opções, opçõesOriginal, jqXHR ) {

	var inspecionado = {},
		seekingTransport = ( estrutura === transportes );

	função inspecionar( tipoDeDados ) {
		variável selecionada;
		inspecionado[ tipoDeDados ] = verdadeiro;
		jQuery.each( structure[ dataType ] || [], function( _, prefilterOrFactory ) {
			var dataTypeOrTransport = prefilterOrFactory( options, originalOptions, jqXHR );
			se ( typeof dataTypeOrTransport === "string" &&
				!seekingTransport && !inspected[ dataTypeOrTransport ] ) {

				options.dataTypes.unshift( dataTypeOrTransport );
				inspecionar( tipoDeDadosOuTransporte );
				retornar falso;
			} senão se (em busca de transporte) {
				retornar !( selecionado = dataTypeOrTransport );
			}
		} );
		retornar selecionado;
	}

	retornar inspecionar( opções.dataTypes[ 0 ] ) || !inspected[ "*" ] && inspecionar( "*" );
}

// Uma extensão especial para opções Ajax
// que aceita opções "planas" (não para serem estendidas profundamente)
// Corrige o problema trac-9887
função ajaxExtend( alvo, src ) {
	chave variável, profunda,
		flatOptions = jQuery.ajaxSettings.flatOptions || {};

	para (chave em src) {
		se ( src[ key ] !== undefined ) {
			( flatOptions[ key ] ? target : ( deep || ( deep = {} ) ) )[ key ] = src[ key ];
		}
	}
	se (profundo) {
		jQuery.extend(true, target, deep);
	}

	retornar alvo;
}

/* Lida com as respostas a uma solicitação AJAX:
 * - Encontra o tipo de dados correto (intermedia entre o tipo de conteúdo e o tipo de dados esperado)
 * - retorna a resposta correspondente
 */
função ajaxHandleResponses( s, jqXHR, respostas ) {

	var ct, tipo, finalDataType, firstDataType,
		conteúdo = s.conteúdo,
		dataTypes = s.dataTypes;

	// Remover auto dataType e obter content-type no processo
	enquanto ( dataTypes[ 0 ] === "*" ) {
		dataTypes.shift();
		se ( ct === indefinido ) {
			ct = s.mimeType || jqXHR.getResponseHeader( "Content-Type" );
		}
	}

	// Verificar se estamos lidando com um tipo de conteúdo conhecido
	se ( ct ) {
		para (digite no conteúdo) {
			se (conteúdo[tipo] && conteúdo[tipo].teste(ct)) {
				dataTypes.unshift( tipo );
				quebrar;
			}
		}
	}

	// Verificar se temos uma resposta para o tipo de dados esperado
	se ( dataTypes[ 0 ] em respostas ) {
		finalDataType = dataTypes[0];
	} outro {

		// Experimente tipos de dados conversíveis
		para (digite em respostas) {
			if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[ 0 ] ] ) {
				tipoDadoFinal = tipo;
				quebrar;
			}
			se ( !primeiroTipoDeDado ) {
				primeiroTipoDeDado = tipo;
			}
		}

		Ou simplesmente use a primeira opção.
		finalDataType = finalDataType || primeiroDataType;
	}

	// Se encontrarmos um tipo de dados
	// Adicionamos o tipo de dados à lista, se necessário.
	// e retorne a resposta correspondente
	se ( tipoDadosFinal ) {
		if (finalDataType! == tipos de dados[0]) {
			dataTypes.unshift(finalDataType);
		}
		retornar respostas[finalDataType];
	}
}

/* Conversões em cadeia, considerando a solicitação e a resposta original
 * Também define os campos responseXXX na instância jqXHR.
 */
função ajaxConvert( s, resposta, jqXHR, isSuccess ) {
	var conv2, atual, conv, tmp, anterior,
		conversores = {},

		// Trabalhe com uma cópia dos tipos de dados caso precisemos modificá-los para a conversão.
		dataTypes = s.dataTypes.slice();

	// Criar mapa de conversores com chaves em minúsculas
	se ( dataTypes[ 1 ] ) {
		para (conv em s.conversores) {
			converters[ conv.toLowerCase() ] = s.converters[ conv ];
		}
	}

	atual = dataTypes.shift();

	// Converter para cada tipo de dados sequencial
	enquanto (atual) {

		se ( s.responseFields[ atual ] ) {
			jqXHR[ s.responseFields[ current ] ] = resposta;
		}

		// Aplicar o filtro de dados, se fornecido
		se ( !prev && isSuccess && s.dataFilter ) {
			resposta = s.dataFilter(resposta, s.dataType);
		}

		anterior = atual;
		atual = dataTypes.shift();

		se (atual) {

			// Só há trabalho a fazer se o tipo de dados atual não for automático.
			se (atual === "*") {

				atual = anterior;

			// Converter resposta se o tipo de dados anterior não for automático e for diferente do atual
			} else if ( prev !== "*" && prev !== current ) {

				// Procure um conversor direto
				conv = converters[ prev + " " + current ] || converters[ "* " + current ];

				// Se nenhum for encontrado, procure um par
				se ( !conv ) {
					para (conv2 em conversores) {

						// Se conv2 produzir a corrente
						tmp = conv2.split( " " );
						se ( tmp[ 1 ] === atual ) {

							// Se o anterior puder ser convertido em entrada aceita
							conv = converters[ prev + " " + tmp[ 0 ] ] ||
								conversores[ "* " + tmp[ 0 ] ];
							se (conv) {

								// Conversores de equivalência condensados
								se (conv === verdadeiro) {
									conv = conversores[ conv2 ];

								Caso contrário, insira o tipo de dados intermediário.
								} else if ( converters[ conv2 ] !== true ) {
									atual = tmp[ 0 ];
									dataTypes.unshift( tmp[ 1 ] );
								}
								quebrar;
							}
						}
					}
				}

				// Aplicar conversor (se não houver equivalência)
				se (conv !== verdadeiro) {

					A menos que os erros possam se propagar, capture-os e retorne-os.
					se (conv && s.throws) {
						resposta = conv(resposta);
					} outro {
						tentar {
							resposta = conv(resposta);
						} catch ( e ) {
							retornar {
								estado: "erro de análise",
								erro: conv ? e : "Nenhuma conversão de " + prev + " para " + current
							};
						}
					}
				}
			}
		}
	}

	retornar { estado: "sucesso", dados: resposta };
}

jQuery.extend( {

	// Contador para armazenar o número de consultas ativas
	ativo: 0,

	// Cache do cabeçalho Last-Modified para a próxima solicitação
	últimaModificação: {},
	etag: {},

	ajaxSettings: {
		URL: location.href,
		tipo: "GET",
		isLocal: rlocalProtocol.test( location.protocol ),
		global: verdadeiro,
		processData: verdadeiro,
		assíncrono: verdadeiro,
		contentType: "application/x-www-form-urlencoded; charset=UTF-8",

		/*
		tempo limite: 0,
		dados: nulos,
		tipoDeDados: nulo,
		nome de usuário: nulo,
		senha: nula,
		cache: nulo,
		lança: falso,
		tradicional: falso,
		cabeçalhos: {},
		*/

		aceita: {
			"*": todos os tipos,
			texto: "texto/simples",
			html: "texto/html",
			xml: "application/xml, text/xml",
			json: "application/json, text/javascript"
		},

		conteúdo: {
			xml: /\bxml\b/,
			html: /\bhtml/,
			json: /\bjson\b/
		},

		responseFields: {
			xml: "responseXML",
			texto: "texto de resposta",
			json: "responseJSON"
		},

		// Conversores de dados
		// As chaves separam os tipos de origem (ou o caractere genérico "*") e destino com um único espaço.
		conversores: {

			// Converter qualquer coisa em texto
			"* texto": String,

			// Texto para HTML (verdadeiro = sem transformação)
			"texto html": verdadeiro,

			// Avaliar texto como uma expressão JSON
			"texto json": JSON.parse,

			// Analisar texto como XML
			"texto xml": jQuery.parseXML
		},

		// Para opções que não devem ser estendidas em profundidade:
		Você pode adicionar suas próprias opções personalizadas aqui, se necessário.
		// E quando você cria um que não deveria existir
		// extensão profunda (consulte ajaxExtend)
		flatOptions: {
			URL: verdadeiro,
			contexto: verdadeiro
		}
	},

	// Cria um objeto de configurações completo no destino
	// com ajaxSettings e campos de configurações.
	// Se o alvo for omitido, grava em ajaxSettings.
	ajaxSetup: função( alvo, configurações ) {
		Retornar configurações?

			// Construindo um objeto de configurações
			ajaxExtend(ajaxExtend(alvo, jQuery.ajaxSettings), configurações):

			// Estendendo ajaxSettings
			ajaxExtend(jQuery.ajaxSettings, alvo);
	},

	ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
	ajaxTransport: adicionarAosPré-filtrosOuTransportes( transportes ),

	// Método principal
	ajax: função( url, opções ) {

		// Se a URL for um objeto, simule a assinatura anterior à versão 1.5.
		se ( typeof url === "objeto" ) {
			opções = url;
			url = indefinido;
		}

		// Forçar que as opções sejam um objeto
		opções = opções || {};

		transporte de var,

			// URL sem parâmetro anti-cache
			cacheURL,

			// Cabeçalhos de resposta
			string de cabeçalhos de resposta,
			cabeçalhos de resposta,

			// manipulador de tempo limite
			timeoutTimer,

			// Variável de limpeza de URL
			Âncora de URL,

			// Estado da solicitação (torna-se falso ao enviar e verdadeiro ao concluir)
			concluído,

			// Para saber se eventos globais devem ser despachados
			fogoGlobais,

			// Variável de loop
			eu,

			// parte não armazenada em cache da URL
			não armazenado em cache,

			// Criar o objeto de opções final
			s = jQuery.ajaxSetup( {}, opções ),

			// Contexto de retornos de chamada
			callbackContext = s.context || s,

			// O contexto para eventos globais é callbackContext se for um nó DOM ou uma coleção jQuery
			globalEventContext = s.context &&
				( callbackContext.nodeType || callbackContext.jquery ) ?
				jQuery( callbackContext ) :
				jQuery.event,

			// Adiados
			adiado = jQuery.Deferred(),
			completeDeferred = jQuery.Callbacks("once memory"),

			// Callbacks dependentes do status
			statusCode = s.statusCode || {},

			// Cabeçalhos (são enviados todos de uma vez)
			requestHeaders = {},
			requestHeadersNames = {},

			// Mensagem de aborto padrão
			strAbort = "cancelado",

			// XHR falso
			jqXHR = {
				readyState: 0,

				// Constrói a tabela hash dos cabeçalhos, se necessário
				obterCabeçalhoDeResposta: função( chave ) {
					var match;
					se (concluído) {
						se ( !responseHeaders ) {
							responseHeaders = {};
							enquanto ( ( correspondência = rheaders.exec( responseHeadersString ) ) ) {

								// Suporte: IE 11+
								// `getResponseHeader( key )` no IE não combina todos os cabeçalhos
								// valores para a chave fornecida em um único resultado com valores
								// unidos por vírgulas, como fazem outros navegadores. Em vez disso, retorna
								// Em linhas separadas.
								responseHeaders[ match[ 1 ].toLowerCase() + " " ] =
									(responseHeaders[match[1].toLowerCase() + " " ] || [] )
										.concat( match[ 2 ] );
							}
						}
						correspondência = cabeçalhos_resposta[ chave.paraLowerCase() + " " ];
					}
					return match == null ? null : match.join( ", " );
				},

				// String bruta
				getAllResponseHeaders: função() {
					retornar concluído ? responseHeadersString : nulo;
				},

				// Armazena em cache o cabeçalho
				setRequestHeader: função( nome, valor ) {
					se (concluído == nulo) {
						nome = requestHeadersNames[ nome.toLowerCase() ] =
							requestHeadersNames[ nome.toLowerCase() ] || nome;
						requestHeaders[ nome ] = valor;
					}
					devolva isto;
				},

				// Substitui o cabeçalho content-type da resposta
				overrideMimeType: função( tipo ) {
					se (concluído == nulo) {
						s.mimeType = tipo;
					}
					devolva isto;
				},

				// Callbacks dependentes do status
				statusCode: função( mapa ) {
					código da variável;
					se (mapa) {
						se (concluído) {

							// Executar as funções de retorno de chamada apropriadas
							jqXHR.always( map[ jqXHR.status ] );
						} outro {

							// Adicione os novos callbacks de forma preguiçosa, preservando os antigos.
							para (código no mapa) {
								statusCode[ code ] = [ statusCode[ code ], map[ code ] ];
							}
						}
					}
					devolva isto;
				},

				// Cancelar a solicitação
				abortar: função( statusText ) {
					var TextoFinal = textoTexto || strAbort;
					se ( transporte ) {
						transporte.abortar( textoFinal );
					}
					feito( 0, textoFinal );
					devolva isto;
				}
			};

		// Anexar dados diferidos
		promessa adiada( jqXHR );

		// Adicionar protocolo se não fornecido (os pré-filtros podem esperá-lo)
		// Tratar URL falsa no objeto de configurações (trac-10093: consistência com a assinatura antiga)
		// Também utilizamos o parâmetro de URL, se disponível.
		s.url = ( ( url || s.url || location.href ) + "" )
			.replace( rprotocol, location.protocol + "//" );

		// Opção de método de alias para digitar conforme o ticket trac-12004
		s.type = options.method || options.type || s.method || s.type;

		// Extrair lista de tipos de dados
		s.dataTypes = ( s.dataType || "*" ).toLowerCase().match( rnothtmlwhite ) || [ "" ];

		// Uma solicitação entre domínios diferentes é necessária quando a origem não corresponde à origem atual.
		se ( s.crossDomain == null ) {
			urlAnchor = document$1.createElement( "a" );

			// Suporte: IE <=8 - 11+
			O Internet Explorer gera uma exceção ao acessar a propriedade href se a URL estiver malformada.
			// Exemplo: http://example.com:80x/
			tentar {
				urlAnchor.href = s.url;

				// Suporte: IE <=8 - 11+
				// A propriedade host do link não é definida corretamente quando s.url é relativo
				urlAnchor.href = urlAnchor.href;
				s.crossDomain = originAnchor.protocol + "//" + originAnchor.host !==
					urlAnchor.protocol + "//" + urlAnchor.host;
			} catch ( e ) {

				// Se ocorrer um erro ao analisar a URL, assuma que se trata de uma URL entre domínios diferentes.
				// Pode ser rejeitado pelo sistema de transporte se for inválido.
				s.crossDomain = true;
			}
		}

		// Aplicar pré-filtros
		inspecionarPrefiltersOrTransports( prefilters, s, opções, jqXHR );

		// Converter dados se ainda não forem uma string
		se ( s.data && s.processData && typeof s.data !== "string" ) {
			s.data = jQuery.param( s.data, s.traditional );
		}

		// Se a solicitação foi abortada dentro de um pré-filtro, pare ali.
		se (concluído) {
			retornar jqXHR;
		}

		// Agora podemos disparar eventos globais se solicitado.
		// Não disparar eventos se jQuery.event não estiver definido em um cenário de uso de ESM (trac-15118)
		fireGlobals = jQuery.event && s.global;

		// Fique atento a um novo conjunto de solicitações
		se ( fireGlobals && jQuery.active++ === 0 ) {
			jQuery.event.trigger("ajaxStart");
		}

		// Digite o tipo em maiúsculas
		s.type = s.type.toUpperCase();

		// Determinar se a solicitação possui conteúdo
		s.hasContent = !rnoContent.test( s.type );

		// Salve a URL caso precisemos usar o parâmetro `If-Modified-Since`.
		// e/ou cabeçalho If-None-Match posteriormente
		// Remover o símbolo de cerquilha para simplificar a manipulação da URL
		cacheURL = s.url.replace( rhash, "" );

		// Mais opções de tratamento para solicitações sem conteúdo
		se ( !s.hasContent ) {

			// Lembre-se do símbolo de hash para que possamos inseri-lo novamente
			não armazenado em cache = s.url.slice( cacheURL.length );

			// Se os dados estiverem disponíveis e precisarem ser processados, anexe-os à URL.
			se ( s.data && ( s.processData || typeof s.data === "string" ) ) {
				cacheURL += ( rquery.test( cacheURL ) ? "&" : "?" ) + s.data;

				// trac-9682: remover dados para que não sejam usados ​​em uma eventual nova tentativa
				excluir s.data;
			}

			// Adicione ou atualize o parâmetro anti-cache, se necessário
			se ( s.cache === falso ) {
				cacheURL = cacheURL.replace(rantiCache, "$1" );
				não armazenado em cache = ( rquery.test( cacheURL ) ? "&" : "?" ) + "_=" +
					( nonce.guid++ ) + não armazenado em cache;
			}

			// Colocar hash e anti-cache na URL que será solicitada (gh-1732)
			s.url = cacheURL + não armazenado em cache;

		// Altere '%20' para '+' se este for o conteúdo do corpo do formulário codificado (gh-2658)
		} senão se ( s.data && s.processData &&
			( s.contentType || "" ).indexOf( "application/x-www-form-urlencoded" ) === 0 ) {
			s.data = s.data.replace( r20, "+" );
		}

		// Define o cabeçalho If-Modified-Since e/ou If-None-Match, se estiver no modo ifModified.
		se ( s.ifModified ) {
			if ( jQuery.lastModified[ cacheURL ] ) {
				jqXHR.setRequestHeader("If-Modified-Since", jQuery.lastModified[ cacheURL ] );
			}
			se ( jQuery.etag[ cacheURL ] ) {
				jqXHR.setRequestHeader("If-None-Match", jQuery.etag[ cacheURL ]);
			}
		}

		// Defina o cabeçalho correto, caso dados estejam sendo enviados
		se ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
			jqXHR.setRequestHeader("Content-Type", s.contentType);
		}

		// Define o cabeçalho Accepts para o servidor, dependendo do tipo de dados.
		jqXHR.setRequestHeader(
			"Aceitar",
			s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[ 0 ] ] ?
				s.accepts[ s.dataTypes[ 0 ] ] +
					( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
				s.aceita[ "*" ]
		);

		// Verificar opção de cabeçalhos
		para ( i em s.headers ) {
			jqXHR.setRequestHeader( i, s.headers[ i ] );
		}

		// Permitir cabeçalhos/tipos MIME personalizados e aborto antecipado
		se ( s.antesDeEnviar &&
			( s.beforeSend.call( callbackContext, jqXHR, s ) === false || completed ) ) {

			// Abortar se ainda não tiver sido feito e retornar
			retornar jqXHR.abortar();
		}

		// Abortar não é mais um cancelamento
		strAbort = "abortar";

		// Instalar callbacks em tarefas adiadas
		completeDeferred.add( s.complete );
		jqXHR.done( s.success );
		jqXHR.fail( s.error );

		// Obter transporte
		transporte = inspecionarPrefiltersOrTransports( transportes, s, opções, jqXHR );

		// Se não houver transporte, abortamos automaticamente.
		se ( !transporte ) {
			feito( -1, "Sem transporte" );
		} outro {
			jqXHR.readyState = 1;

			// Enviar evento global
			se (fireGlobals) {
				globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
			}

			// Se a requisição foi abortada dentro do ajaxSend, pare por aí.
			se (concluído) {
				retornar jqXHR;
			}

			// Tempo esgotado
			se ( s.async && s.timeout > 0 ) {
				timeoutTimer = window.setTimeout( function() {
					jqXHR.abort("timeout");
				}, s.timeout );
			}

			tentar {
				concluído = falso;
				transport.send(requestHeaders, concluído);
			} catch ( e ) {

				// Relançar exceções pós-conclusão
				se (concluído) {
					lançar e;
				}

				// Propagar outros como resultados
				feito( -1, e );
			}
		}

		// Função de retorno para quando tudo estiver concluído
		função concluída( status, nativeStatusText, respostas, cabeçalhos ) {
			var isSuccess, success, error, response, modified,
				statusText = nativeStatusText;

			// Ignorar invocações repetidas
			se (concluído) {
				retornar;
			}

			concluído = verdadeiro;

			// Limpar tempo limite, se existir
			se ( timeoutTimer ) {
				janela.limparTempoExtra (tempo limiteExtra);
			}

			// Desreferenciar transporte para coleta antecipada de lixo
			// (independentemente de quanto tempo o objeto jqXHR for usado)
			transporte = indefinido;

			// Armazenar em cache os cabeçalhos de resposta
			responseHeadersString = headers || "";

			// Definir readyState
			jqXHR.readyState = status> 0? 4: 0;

			// Determinar se foi bem-sucedido
			isSuccess = status >= 200 && status < 300 || status === 304;

			// Obter dados de resposta
			se (respostas) {
				resposta = ajaxHandleResponses( s, jqXHR, respostas );
			}

			// Use um conversor noop para script ausente, mas não se for jsonp
			se ( !isSuccess &&
				jQuery.inArray("script", s.dataTypes) > -1 &&
				jQuery.inArray("json", s.dataTypes) < 0) {
				s.converters[ "text script" ] = function() {};
			}

			// Converter independentemente do que aconteça (dessa forma os campos responseXXX estarão sempre definidos)
			resposta = ajaxConvert( s, resposta, jqXHR, isSuccess );

			// Se bem-sucedido, lide com o encadeamento de tipos
			se (éSucesso) {

				// Define o cabeçalho If-Modified-Since e/ou If-None-Match, se estiver no modo ifModified.
				se ( s.ifModified ) {
					modificado = jqXHR.getResponseHeader( "Última modificação" );
					se (modificado) {
						jQuery.lastModified[ cacheURL ] = modificado;
					}
					modificado = jqXHR.getResponseHeader( "etag" );
					se (modificado) {
						jQuery.etag[ cacheURL ] = modificado;
					}
				}

				// se não houver conteúdo
				se ( status === 204 || s.type === "HEAD" ) {
					statusText = "sem conteúdo";

				// se não modificado
				} senão se ( status === 304 ) {
					statusText = "não modificado";

				Se tivermos dados, vamos convertê-los.
				} outro {
					statusText = resposta.estado;
					sucesso = resposta.dados;
					erro = resposta.erro;
					isSuccess = !erro;
				}
			} outro {

				// Extrair o erro do statusText e normalizar para não-abortos
				erro = texto de status;
				se ( status || !statusText ) {
					statusText = "erro";
					se ( status < 0 ) {
						status = 0;
					}
				}
			}

			// Define os dados para o objeto xhr falso
			jqXHR.status = status;
			jqXHR.statusText = ( nativeStatusText || statusText ) + "";

			// Sucesso/Erro
			se (éSucesso) {
				deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
			} outro {
				deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
			}

			// Callbacks dependentes do status
			jqXHR.statusCode( statusCode );
			statusCode = indefinido;

			se (fireGlobals) {
				globalEventContext.trigger( isSuccess ? "ajaxSuccess" : "ajaxError",
					[ jqXHR, s, isSuccess ? sucesso : erro ] );
			}

			// Completo
			completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );

			se (fireGlobals) {
				globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );

				// Lidar com o contador AJAX global
				se ( !( --jQuery.ativo ) ) {
					jQuery.event.trigger("ajaxStop");
				}
			}
		}

		retornar jqXHR;
	},

	obterJSON: função( url, dados, retorno de chamada ) {
		return jQuery.get( url, data, callback, "json" );
	},

	getScript: função( url, callback ) {
		return jQuery.get( url, undefined, callback, "script" );
	}
} );

jQuery.each( [ "get", "post" ], function( _i, method ) {
	jQuery[ método ] = função( url, dados, callback, tipo ) {

		// Desloque os argumentos se o argumento de dados foi omitido.
		// Lidar com o espaço reservado de retorno de chamada nulo.
		se ( typeof data === "function" || data === null ) {
			tipo = tipo || retorno de chamada;
			retorno de chamada = dados;
			dados = indefinido;
		}

		// A URL pode ser um objeto de opções (que então deve conter .url)
		retornar jQuery.ajax( jQuery.extend( {
			URL: URL,
			tipo: método,
			tipoDeDados: tipo,
			dados: dados,
			sucesso: retorno de chamada
		}, jQuery.isPlainObject( url ) && url ) );
	};
} );

jQuery.ajaxPrefilter( function( s ) {
	var i;
	para ( i em s.headers ) {
		se ( i.toLowerCase() === "content-type" ) {
			s.contentType = s.headers[ i ] || "";
		}
	}
} );

jQuery._evalUrl = function( url, options, doc ) {
	retornar jQuery.ajax( {
		URL: URL,

		// Torne isso explícito, já que o usuário pode sobrescrever isso através do ajaxSetup (trac-11264)
		tipo: "GET",
		tipo de dados: "script",
		cache: verdadeiro,
		assíncrono: falso,
		global: falso,
		scriptAttrs: options.crossOrigin ? { "crossOrigin": options.crossOrigin } : undefined,

		// Avalie a resposta somente se ela for bem-sucedida (gh-4126)
		// O dataFilter não é invocado para respostas de falha, portanto, estamos usando-o em vez disso.
		// O conversor padrão é improvisado, mas funciona.
		conversores: {
			"script de texto": função() {}
		},
		dataFilter: função(resposta) {
			jQuery.globalEval(resposta, opções, doc);
		}
	} );
};

jQuery.fn.extend( {
	wrapAll: function( html ) {
		var wrap;

		se ( isto[ 0 ] ) {
			se ( typeof html === "function" ) {
				html = html.call( this[ 0 ] );
			}

			// Os elementos que envolverão o alvo
			wrap = jQuery( html, this[ 0 ].ownerDocument ).eq( 0 ).clone( true );

			se ( this[ 0 ].parentNode ) {
				wrap.insertBefore( this[ 0 ] );
			}

			wrap.map( function() {
				var elem = isto;

				enquanto (elem.primeiroElementoFilho) {
					elem = elem.firstElementChild;
				}

				retornar elemento;
			} ).append( this );
		}

		devolva isto;
	},

	wrapInner: function( html ) {
		se ( typeof html === "function" ) {
			retornar this.each( function( i ) {
				jQuery( this ).wrapInner( html.call( this, i ) );
			} );
		}

		retorne this.each( function() {
			var self = jQuery( this ),
				conteúdo = self.conteúdo();

			se (conteúdo.comprimento) {
				conteúdo.wrapAll( html );

			} outro {
				self.append(html);
			}
		} );
	},

	wrap: function( html ) {
		var htmlIsFunction = typeof html === "function";

		retornar this.each( function( i ) {
			jQuery( this ).wrapAll( htmlIsFunction ? html.call( this, i ) : html );
		} );
	},

	desembrulhar: função( seletor ) {
		this.parent( selector ).not( "body" ).each( function() {
			jQuery( this ).replaceWith( this.childNodes );
		} );
		devolva isto;
	}
} );

jQuery.expr.pseudos.hidden = function( elem ) {
	retornar !jQuery.expr.pseudos.visible( elem );
};
jQuery.expr.pseudos.visible = function( elem ) {
	retornar !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length );
};

jQuery.ajaxSettings.xhr = function() {
	retornar nova janela.XMLHttpRequest();
};

var xhrSuccessStatus = {

	// O protocolo de arquivo sempre retorna o código de status 0, assuma 200.
	0: 200
};

jQuery.ajaxTransport( function( options ) {
	var callback;

	retornar {
		enviar: função( cabeçalhos, completo ) {
			var i,
				xhr = opções.xhr();

			xhr.abrir(
				opções.tipo,
				opções.url,
				opções.async,
				opções.nome de usuário,
				opções.senha
			);

			// Aplicar campos personalizados, se fornecidos
			se (opções.xhrFields) {
				para ( i em options.xhrFields ) {
					xhr[ i ] = options.xhrFields[ i ];
				}
			}

			// Substitua o tipo MIME, se necessário
			se (opções.mimeType && xhr.overrideMimeType) {
				xhr.overrideMimeType(options.mimeType);
			}

			// Cabeçalho X-Requested-With
			// Para solicitações entre domínios, considerando que as condições para uma verificação prévia são
			// Semelhante a um quebra-cabeça, simplesmente nunca o montamos para termos certeza.
			// (pode sempre ser configurado individualmente para cada solicitação ou até mesmo usando ajaxSetup)
			// Para solicitações do mesmo domínio, o cabeçalho não será alterado se já tiver sido fornecido.
			if ( !options.crossDomain && !headers[ "X-Requested-With" ] ) {
				headers[ "X-Requested-With" ] = "XMLHttpRequest";
			}

			// Definir cabeçalhos
			para ( i em cabeçalhos ) {
				xhr.setRequestHeader( i, headers[ i ] );
			}

			// Ligar de volta
			callback = função( tipo ) {
				retornar função() {
					se ( callback ) {
						callback = xhr.onload = xhr.onerror = xhr.onabort = xhr.ontimeout = null;

						se ( tipo === "abortar" ) {
							xhr.abortar();
						} else if ( type === "error" ) {
							completo(

								// Arquivo: o protocolo sempre retorna o status 0; veja trac-8605, trac-14207
								xhr.status,
								xhr.statusText
							);
						} outro {
							completo(
								xhrSuccessStatus[ xhr.status ] || xhr.status,
								xhr.statusText,

								// Para XHR2 não textual, deixe que o chamador lide com isso (gh-2498)
								( xhr.responseType || "texto" ) === "texto" ?
									{ texto: xhr.responseText } :
									{ binary: xhr.response },
								xhr.getAllResponseHeaders()
							);
						}
					}
				};
			};

			// Ouça os eventos
			xhr.onload = callback();
			xhr.onabort = xhr.onerror = xhr.ontimeout = callback( "erro" );

			// Criar a função de retorno de chamada de aborto
			callback = callback( "abortar" );

			tentar {

				// Envie a solicitação (isso pode gerar uma exceção)
				xhr.send(options.hasContent && options.data || null);
			} catch ( e ) {

				// trac-14683: Relance a exceção somente se isso ainda não tiver sido notificado como um erro.
				se ( callback ) {
					lançar e;
				}
			}
		},

		abortar: função() {
			se ( callback ) {
				ligar de volta();
			}
		}
	};
} );

função podeUsarScriptTag( s ) {

	// A tag script só pode ser usada para solicitações assíncronas, entre domínios ou forçadas por atributos.
	// Requisições com cabeçalhos não podem usar uma tag de script. No entanto, quando ambos `scriptAttrs` e
	// Se as opções `headers` forem especificadas, é impossível satisfazê-las simultaneamente; nós
	// Prefira `scriptAttrs` então.
	// As solicitações de sincronização continuam sendo tratadas de forma diferente para preservar a ordem estrita dos scripts.
	retornar s.scriptAttrs || (
		!s.headers &&
		(
			s.crossDomain ||

			// Ao lidar com JSONP (`s.dataTypes` inclui "json" então)
			// Não use a tag script para que as respostas de erro ainda possam ter
			// `responseJSON` definido. Continue usando uma tag de script para solicitações JSONP que:
			// * são de domínio cruzado, pois as solicitações AJAX não funcionarão sem uma configuração CORS
			// * Defina `scriptAttrs`, pois essa é uma funcionalidade exclusiva de scripts
			// Observe que isso significa que as solicitações JSONP violam as configurações estritas de script-src do CSP.
			// Uma solução adequada é migrar do uso de JSONP para uma configuração CORS.
			( s.async && jQuery.inArray( "json", s.dataTypes ) < 0 )
		)
	);
}

// Instale o tipo de dados do script. Não especifique `contents.script` para que um explícito
// `dataType: "script"` é obrigatório (consulte gh-2432, gh-4822)
jQuery.ajaxSetup( {
	aceita: {
		script: "text/javascript, application/javascript, " +
			"application/ecmascript, application/x-ecmascript"
	},
	conversores: {
		"script de texto": função( texto ) {
			jQuery.globalEval( texto );
			retornar texto;
		}
	}
} );

// Lidar com o caso especial do cache e com a interconexão entre domínios
jQuery.ajaxPrefilter( "script", function( s ) {
	se ( s.cache === undefined ) {
		s.cache = falso;
	}

	// Esses tipos de solicitações são tratados por meio de uma tag de script
	// então, force seus métodos a usar o método GET.
	se ( podeUsarScriptTag( s ) ) {
		s.type = "GET";
	}
} );

// Hack de tag de script de vinculação de transporte
jQuery.ajaxTransport( "script", function( s ) {
	se ( podeUsarScriptTag( s ) ) {
		var script, callback;
		retornar {
			enviar: função( _, completo ) {
				script = jQuery("<script>")
					.attr( s.scriptAttrs || {} )
					.prop( { charset: s.scriptCharset, src: s.url } )
					.on("erro de carregamento", callback = function(evt) {
						script.remove();
						callback = null;
						se (event) {
							complete( evt.type === "error" ? 404 : 200, evt.type );
						}
					} );

				// Use manipulação DOM nativa para evitar nossos truques de AJAX do domManip
				document$1.head.appendChild( script[ 0 ] );
			},
			abortar: função() {
				se ( callback ) {
					ligar de volta();
				}
			}
		};
	}
} );

var oldCallbacks = [],
	rjsonp = /(=)\?(?=&|$)|\?\?/;

// Configurações JSONP padrão
jQuery.ajaxSetup( {
	jsonp: "callback",
	jsonpCallback: função() {
		var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce.guid++ ) );
		this[ callback ] = true;
		retornar callback;
	}
} );

// Detectar, normalizar opções e instalar funções de retorno de chamada para solicitações JSONP
jQuery.ajaxPrefilter( "jsonp", function( s, originalSettings, jqXHR ) {

	var callbackName, sobrescrito, responseContainer,
		jsonProp = s.jsonp !== false && ( rjsonp.test( s.url ) ?
			"url" :
			typeof s.data === "string" &&
				( s.contentType || "" )
					.indexOf( "application/x-www-form-urlencoded" ) === 0 &&
				rjsonp.test( s.data ) && "data"
		);

	// Obter o nome da função de retorno, lembrando o valor preexistente associado a ela.
	callbackName = s.jsonpCallback = typeof s.jsonpCallback === "function" ?
		s.jsonpCallback() :
		s.jsonpCallback;

	// Inserir função de retorno de chamada na URL ou nos dados do formulário
	se ( jsonProp ) {
		s[ jsonProp ] = s[ jsonProp ].replace( rjsonp, "$1" + callbackName );
	} else if ( s.jsonp !== false ) {
		s.url += ( rquery.test( s.url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
	}

	// Use o conversor de dados para recuperar o JSON após a execução do script
	s.converters["script json"] = function() {
		se ( !responseContainer ) {
			jQuery.error( callbackName + " não foi chamado" );
		}
		retornar responseContainer[ 0 ];
	};

	// Forçar tipo de dados JSON
	s.dataTypes[ 0 ] = "json";

	// Instalar retorno de chamada
	sobrescrito = janela[ nomeDeRetornoDeChamada ];
	janela[nomeDeRetornoDeChamada] = função() {
		responseContainer = argumentos;
	};

	// Função de limpeza (disparada após os conversores)
	jqXHR.always( function() {

		// Se o valor anterior não existir, remova-o.
		se (sobrescrito === indefinido) {
			jQuery( window ).removeProp( callbackName );

		// Caso contrário, restaure o valor preexistente
		} outro {
			janela[ callbackName ] = sobrescrito;
		}

		// Salvar como grátis
		se ( s[ callbackName ] ) {

			// Certifique-se de que reutilizar as opções não cause problemas.
			s.jsonpCallback = originalSettings.jsonpCallback;

			// Salve o nome da função de retorno para uso futuro
			oldCallbacks.push( callbackName );
		}

		// Chame se for uma função e tivermos uma resposta
		se (responseContainer && typeof overwritten === "function" ) {
			sobrescrito(responseContainer[0]);
		}

		responseContainer = sobrescrito = indefinido;
	} );

	// Delegar ao script
	retornar "script";
} );

jQuery.ajaxPrefilter( function( s, origOptions ) {

	// Os dados binários precisam ser passados ​​para o XHR exatamente como estão, sem conversão para string.
	se ( typeof s.data !== "string" && !jQuery.isPlainObject( s.data ) &&
			!Array.isArray( s.data ) &&

			// Não desative o processamento de dados se ele tiver sido explicitamente definido pelo usuário.
			!( "processData" em origOptions ) ) {
		s.processData = falso;
	}

	// O atributo `Content-Type` para solicitações com corpos `FormData` precisa ser definido.
	// pelo navegador, pois ele precisa anexar o `limite` que gerou.
	se ( s.data instância de window.FormData ) {
		s.contentType = false;
	}
} );

// O argumento "data" deve ser uma string de HTML ou um wrapper TrustedHTML de HTML óbvio.
// contexto (opcional): Se especificado, o fragmento será criado neste contexto.
// O padrão é o documento
// keepScripts (opcional): Se verdadeiro, incluirá os scripts passados ​​na string HTML
jQuery.parseHTML = function( data, context, keepScripts ) {
	se ( typeof data !== "string" && !isObviousHtml( data + " " ) ) {
		retornar [];
	}
	se ( typeof contexto === "booleano" ) {
		manterScripts = contexto;
		contexto = falso;
	}

	var analisado, scripts;

	se ( !contexto ) {

		// Impede a execução imediata de scripts ou manipuladores de eventos embutidos
		// usando DOMParser
		contexto = (novo window.DOMParser())
			.parseFromString( "", "texto/html" );
	}

	analisado = rsingleTag.exec( dados );
	scripts = !keepScripts && [];

	// Etiqueta única
	se (analisado) {
		retornar [ context.createElement( parsed[ 1 ] ) ];
	}

	analisado = construirFragmento( [ dados ], contexto, scripts );

	se ( scripts && scripts.length ) {
		jQuery( scripts ).remove();
	}

	return jQuery.merge( [], parsed.childNodes );
};

/**
 * Carregar um URL em uma página
 */
jQuery.fn.load = function( url, params, callback ) {
	seletor de variáveis, tipo, resposta,
		eu = isto,
		off = url.indexOf( " " );

	se (desligado > -1) {
		seletor = stripAndCollapse( url.slice( off ) );
		url = url.slice( 0, off );
	}

	// Se for uma função
	se ( typeof params === "function" ) {

		// Assumimos que seja o retorno de chamada
		callback = parâmetros;
		parâmetros = indefinido;

	Caso contrário, construa uma string de parâmetros.
	} else if ( params && typeof params === "object" ) {
		tipo = "POST";
	}

	// Se tivermos elementos para modificar, faça a solicitação
	se ( self.length > 0 ) {
		jQuery.ajax( {
			URL: URL,

			// Se a variável "type" não estiver definida, o método "GET" será usado.
			// Tornar o valor deste campo explícito, pois
			// O usuário pode sobrescrevê-lo através do método ajaxSetup
			tipo: tipo || "GET",
			tipo de dados: "html",
			dados: parâmetros
		} ).done( function( responseText ) {

			// Salvar resposta para uso em retorno de chamada completo
			resposta = argumentos;

			self.html( seletor ?

				// Se um seletor foi especificado, localize os elementos corretos em uma div fictícia
				// Excluir scripts para evitar erros de "Permissão negada" no IE
				jQuery("<div>").append(jQuery.parseHTML(responseText)).find(selector):

				Caso contrário, utilize o resultado completo.
				texto_da_resposta);

		// Se a solicitação for bem-sucedida, esta função obtém "data", "status", "jqXHR"
		// mas são ignorados porque a resposta foi definida acima.
		// Se falhar, esta função recebe "jqXHR", "status", "error"
		} ).always( callback && function( jqXHR, status ) {
			self.each( function() {
				callback.apply( this, response || [ jqXHR.responseText, status, jqXHR ] );
			} );
		} );
	}

	devolva isto;
};

jQuery.expr.pseudos.animated = function( elem ) {
	return jQuery.grep( jQuery.timers, function( fn ) {
		retornar elem === fn.elem;
	} ).comprimento;
};

jQuery.offset = {
	setOffset: função( elem, opções, i ) {
		var curPosition, curLeft, curCSSTop, curTop, curOffset, curCSSLeft, calculatePosition,
			posição = jQuery.css(elem, "posição"),
			curElem = jQuery(elem),
			props = {};

		// Defina a posição primeiro, caso as propriedades superior e esquerda estejam definidas mesmo em elementos estáticos.
		se ( posição === "estático" ) {
			elem.style.position = "relative";
		}

		curOffset = curElem.offset();
		curCSSTop = jQuery.css( elem, "top" );
		curCSSLeft = jQuery.css( elem, "esquerda" );
		calcularPosição = ( posição === "absoluta" || posição === "fixa" ) &&
			(curCSSTop + curCSSLeft).indexOf("auto") > -1;

		// Precisa ser capaz de calcular a posição se
		// A posição superior ou esquerda é automática e pode ser absoluta ou fixa.
		se (calcularPosição) {
			curPosition = curElem.position();
			curTop = curPosition.top;
			curLeft = curPosition.left;

		} outro {
			curTop = parseFloat( curCSSTop ) || 0;
			curLeft = parseFloat( curCSSLeft ) || 0;
		}

		se ( typeof opções === "função" ) {

			// Use jQuery.extend aqui para permitir a modificação do argumento de coordenadas (gh-1848)
			opções = opções.call( elem, i, jQuery.extend( {}, curOffset ) );
		}

		se (opções.top != null) {
			props.top = ( options.top - curOffset.top ) + curTop;
		}
		se (opções.esquerda != nulo) {
			props.left = ( options.left - curOffset.left ) + curLeft;
		}

		se ( "usando" nas opções ) {
			options.using.call(elem, props);

		} outro {
			curElem.css( props );
		}
	}
};

jQuery.fn.extend( {

	// offset() relaciona a caixa de borda de um elemento à origem do documento
	deslocamento: função( opções ) {

		// Preservar encadeamento para o setter
		se (argumentos.comprimento) {
			opções de retorno === indefinidas?
				esse :
				this.each( function( i ) {
					jQuery.offset.setOffset( this, options, i );
				} );
		}

		var retângulo, vitória,
			elem = this[ 0 ];

		se ( !elem ) {
			retornar;
		}

		// Retorna zeros para elementos desconectados e ocultos (display: none) (gh-2310)
		// Suporte: IE <= 11+
		// Executando getBoundingClientRect em um
		// Um ​​nó desconectado no IE gera um erro
		se ( !elem.getClientRects().length ) {
			retornar { topo: 0, esquerda: 0 };
		}

		// Obtenha a posição relativa ao documento adicionando a rolagem da viewport ao gBCR relativo à viewport
		retângulo = elem.getBoundingClientRect();
		vitória = elem.ownerDocument.defaultView;
		retornar {
			topo: rect.top + win.pageYOffset,
			esquerda: rect.left + win.pageXOffset
		};
	},

	// position() relaciona a margem de um elemento com o preenchimento (padding) do elemento pai ao qual ele está deslocado.
	// Isso corresponde ao comportamento do posicionamento absoluto em CSS
	posição: função() {
		se ( !this[ 0 ] ) {
			retornar;
		}

		var offsetParent, offset, doc,
			elem = this[ 0 ],
			parentOffset = { top: 0, left: 0 };

		// Elementos com position:fixed são deslocados da viewport, que por sua vez sempre tem deslocamento zero.
		if ( jQuery.css( elem, "position" ) === "fixed" ) {

			// Assume-se que position:fixed implica a disponibilidade de getBoundingClientRect
			deslocamento = elem.getBoundingClientRect();

		} outro {
			deslocamento = este.deslocamento();

			// Leva em consideração o elemento pai de deslocamento *real*, que pode ser o documento ou seu elemento raiz.
			// quando um elemento posicionado estaticamente é identificado
			doc = elem.ownerDocument;
			offsetParent = elem.offsetParent || doc.documentElement;
			enquanto ( offsetParent &&
				offsetParent !== doc.documentElement &&
				jQuery.css( offsetParent, "position" ) === "static" ) {

				offsetParent = offsetParent.offsetParent || doc.documentElement;
			}
			se ( offsetParent && offsetParent !== elem && offsetParent.nodeType === 1 &&
				jQuery.css( offsetParent, "position" ) !== "static" ) {

				// Incorpore as bordas ao seu deslocamento, já que elas estão fora da origem do conteúdo.
				parentOffset = jQuery( offsetParent ).offset();
				parentOffset.top += jQuery.css( offsetParent, "borderTopWidth", true );
				parentOffset.left += jQuery.css( offsetParent, "borderLeftWidth", true );
			}
		}

		// Subtrair os deslocamentos do elemento pai e as margens do elemento
		retornar {
			topo: offset.top - parentOffset.top - jQuery.css( elem, "marginTop", true ),
			esquerda: offset.left - parentOffset.left - jQuery.css( elem, "marginLeft", true )
		};
	},

	// Este método retornará um documentElement nos seguintes casos:
	// 1) Para o elemento dentro do iframe sem offsetParent, este método retornará
	// documentElement da janela pai
	// 2) Para o elemento oculto ou destacado
	// 3) Para elementos do corpo ou HTML, ou seja, no caso do nó HTML, ele retornará a si mesmo.
	//
	// Mas essas exceções nunca foram apresentadas como casos de uso da vida real.
	// e podem ser considerados resultados mais preferíveis.
	//
	// Essa lógica, no entanto, não é garantida e pode mudar a qualquer momento no futuro.
	offsetParent: função() {
		retorne this.map( function() {
			var offsetParent = this.offsetParent;

			enquanto ( offsetParent && jQuery.css( offsetParent, "position" ) === "static" ) {
				offsetParent = offsetParent.offsetParent;
			}

			retornar offsetParent || documentElement$1;
		} );
	}
} );

// Criar métodos scrollLeft e scrollTop
jQuery.each( { scrollLeft: "pageXOffset", scrollTop: "pageYOffset" }, function( method, prop ) {
	var top = "pageYOffset" === prop;

	jQuery.fn[ método ] = função( val ) {
		retornar acesso( this, função( elem, método, val ) {

			// Unir documentos e janelas
			var vitória;
			se ( isWindow( elem ) ) {
				ganhar = elem;
			} else if ( elem.nodeType === 9 ) {
				vitória = elem.defaultView;
			}

			se (val === indefinido) {
				retornar vitória ? vitória[ prop ] : elem[ método ];
			}

			se (ganhar) {
				ganhar.rolarPara(
					!top ? val : win.pageXOffset,
					topo ? valor : win.pageYOffset
				);

			} outro {
				elem[ método ] = val;
			}
		}, método, val, argumentos.comprimento );
	};
} );

// Criar métodos innerHeight, innerWidth, height, width, outerHeight e outerWidth
jQuery.each( { Altura: "altura", Largura: "largura" }, function( nome, tipo ) {
	jQuery.each( {
		preenchimento: "interno" + nome,
		conteúdo: tipo,
		"": "externo" + nome
	}, função( defaultExtra, funcName ) {

		// A margem se aplica apenas a outerHeight e outerWidth
		jQuery.fn[ funcName ] = function( margin, value ) {
			var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
				extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );

			retornar acesso( this, função( elem, tipo, valor ) {
				var doc;

				se ( isWindow( elem ) ) {

					// $( window ).outerWidth/Height retorna largura/altura incluindo as barras de rolagem (gh-1729)
					return funcName.indexOf("outer") === 0 ?
						elem[ "inner" + nome ] :
						elem.document.documentElement[ "cliente" + nome ];
				}

				// Obter a largura ou altura do documento
				se (elem.nodeType === 9) {
					doc = elem.documentElement;

					// Pode ser scroll[Largura/Altura] ou offset[Largura/Altura] ou client[Largura/Altura],
					// o que for maior
					retornar Math.max(
						elem.body["scroll" + nome], doc["scroll" + nome],
						elem.body["offset" + name], doc["offset" + name],
						doc["cliente" + nome]
					);
				}

				valor de retorno === indefinido?

					// Obter a largura ou altura do elemento, solicitando, mas não forçando o uso de parseFloat
					jQuery.css(elemento, tipo, extra):

					// Defina a largura ou altura do elemento
					jQuery.style(elemento, tipo, valor, extra);
			}, tipo, encadeável ? margem : indefinido, encadeável );
		};
	} );
} );

jQuery.each( [
	"ajaxStart",
	"ajaxStop",
	"ajaxComplete",
	"ajaxError",
	"ajaxSuccess",
	"ajaxSend"
], função( _i, tipo ) {
	jQuery.fn[ type ] = function( fn ) {
		retornar this.on( tipo, fn );
	};
} );

jQuery.fn.extend( {

	bind: função( tipos, dados, fn ) {
		retornar this.on( tipos, null, dados, fn );
	},
	desvincular: função( tipos, fn ) {
		retornar this.off( tipos, null, fn );
	},

	delegado: função( seletor, tipos, dados, fn ) {
		retornar this.on( tipos, seletor, dados, fn );
	},
	deselecionar: função( seletor, tipos, fn ) {

		// ( namespace ) ou ( seletor, tipos [, fn] )
		retornar arguments.length === 1 ?
			this.off(selector, "**" ) :
			this.off( tipos, seletor || "**", fn );
	},

	hover: função( fnOver, fnOut ) {
		devolva isto
			.on("mouseenter", fnOver)
			.on( "mouseleave", fnOut || fnOver );
	}
} );

jQuery.each(
	( "desfocar foco focando para dentro foco para fora redimensionar rolagem clique duplo " +
	"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
	"alterar selecionar enviar tecla pressionada tecla pressionada tecla solta menu de contexto" ).split( " " ),
	função( _i, nome ) {

		// Lidar com a vinculação de eventos
		jQuery.fn[nome] = function(dados, fn) {
			retornar arguments.length > 0 ?
				this.on( nome, null, dados, fn ) :
				este.gatilho( nome );
		};
	}
);

// Vincula uma função a um contexto, aplicando opcionalmente qualquer condição parcialmente.
// argumentos.
// jQuery.proxy está obsoleto para promover padrões (especificamente Function#bind)
No entanto, não está previsto que seja removido tão cedo.
jQuery.proxy = function( fn, context ) {
	var tmp, args, proxy;

	se ( typeof contexto === "string" ) {
		tmp = fn[contexto];
		contexto = fn;
		fn = tmp;
	}

	// Verificação rápida para determinar se o alvo é chamável, conforme especificado.
	// Isso gera um TypeError, mas vamos simplesmente retornar undefined.
	se ( typeof fn !== "function" ) {
		retornar indefinido;
	}

	// Ligação simulada
	args = slice.call(argumentos, 2);
	proxy = função() {
		return fn.apply( context || this, args.concat( slice.call( arguments ) ) );
	};

	// Defina o GUID do manipulador exclusivo para o mesmo do manipulador original, para que ele possa ser removido.
	proxy.guid = fn.guid = fn.guid || jQuery.guid++;

	retornar proxy;
};

jQuery.holdReady = function( hold ) {
	se (segurar) {
		jQuery.readyWait++;
	} outro {
		jQuery.ready(true);
	}
};

jQuery.expr[ ":" ] = jQuery.expr.filters = jQuery.expr.pseudos;

// Registre-se como um módulo AMD nomeado, já que o jQuery pode ser concatenado com outros.
// arquivos que podem usar define, mas não por meio de um script de concatenação adequado que
// Entende módulos AMD anônimos. Um módulo AMD com nome específico é o mais seguro e robusto.
// forma de registro. O jquery em minúsculas é usado porque os nomes dos módulos AMD são
// derivado de nomes de arquivos, e jQuery normalmente é fornecido em letras minúsculas
// nome do arquivo. Faça isso depois de criar a variável global para que, se um módulo AMD precisar de alguma alteração, ela seja utilizada.
// Para chamar noConflict e ocultar esta versão do jQuery, funcionará.

// Observe que, para máxima portabilidade, as bibliotecas que não são jQuery devem
// Declarem-se como módulos anônimos e evitem definir uma variável global se houver
// O carregador AMD está presente. O jQuery é um caso especial. Para mais informações, consulte
// https://github.com/jrburke/requirejs/wiki/Updating-existing-libraries#wiki-anon

se ( typeof define === "function" && define.amd ) {
	define( "jquery", [], function() {
		retornar jQuery;
	} );
}

var

	// Mapear sobre o jQuery em caso de sobrescrita
	_jQuery = window.jQuery,

	// Mapear o valor $ em caso de sobrescrita
	_$ = janela.$;

jQuery.noConflict = function( deep ) {
	se (window.$ === jQuery) {
		janela.$ = _$;
	}

	se ( profundo && window.jQuery === jQuery ) {
		janela.jQuery = _jQuery;
	}

	retornar jQuery;
};

// Exponha os identificadores jQuery e $, mesmo em AMD
// (trac-7102#comentário:10, gh-557)
// e CommonJS para emuladores de navegador (trac-13566)
se ( typeof noGlobal === "undefined" ) {
	janela.jQuery = janela.$ = jQuery;
}

retornar jQuery;

} );