<?php

use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\LogMonModel;

/**
 * detalhesTela
 * Retorna os detalhes da Tela Especificada
 * @param string $nomeTela
 * @return array
 */
function detalhesTela(string $nomeTela, array $retorno)
{
    preg_match('/\\\\([\w]+)$/', $nomeTela, $nome);
    $perfil_usu             = session()->get('usu_perfil_id');
    $tela                 = new ConfigTelaModel();
    $perfil_pit             = new ConfigPerfilItemModel();
    $menu                   = new ConfigMenuModel();
    $busca_tela           = $tela->getTela($nome[1])[0];
    $retorno['title']       = $busca_tela['tel_icone'] . ' ' . $busca_tela['tel_nome'];
    $retorno['controler']   = $busca_tela['tel_controler'];
    $retorno['opc_menu']    = $busca_tela['men_id'];
    $retorno['bt_add']      = $busca_tela['tel_texto_botao'];
    $retorno['perfil_usu']  = $perfil_usu;
    $retorno['it_menu']     = $menu->getMenuPerfil($perfil_usu);

    if ($busca_tela['men_id']) {
        $busca_permissoes       = $perfil_pit->getItemPerfil($perfil_usu, $busca_tela['men_id']);
        if (sizeof($busca_permissoes) == 0) {
            $retorno['permissao']   = false;
        } else {
            $retorno['permissao']   = $busca_permissoes[0]['pit_permissao'];
        }
    } else {
        $retorno['permissao']   = 'CAEX';
    }
    // debug($retorno, false);
    return $retorno;
};;

/**
 * metodosTela
 *  Retorna uma lista de métodos da Tela Especificada
 * @param array $metodos
 * @param string $classe
 * @return string
 */
function metodosTela(array $metodos, string $classe)
{
    $text = '';
    foreach ($metodos as $nome => $valor) {
        if ($valor->class == $classe) {
            $text .= "$valor->name<br>";
        }
    }
    return $text;
};;

/**
 * campos_tabela
 *  Retorna uma lista dos Campos da Tabela Especificada
 * @param array $campos
 * @return string
 */
function campos_tabela(array $campos)
{
    $tipos['char']      = 'Caracter curto';
    $tipos['varchar']   = 'Caracter longo';
    $tipos['mediumtext'] = 'Texto';
    $tipos['text']      = 'Texto';
    $tipos['int']       = 'Inteiro';
    $tipos['float']     = 'Moeda';
    $tipos['decimal']     = 'Decimal';
    $tipos['date']      = 'Data';
    $tipos['timestamp'] = 'Data e Hora';
    $tipos['datetime']  = 'Data e Hora';

    $simnao['YES'] = 'Não';
    $simnao['NO']  = 'Sim';

    $chave['PRI'] = "Primária";
    $chave['MUL'] = "Múltipla";
    $chave['UNI'] = "Única";
    $chave['']    = "";

    $text = '<table border="1" class="table table-light table-bordered table-striped table-hover table-responsive">';
    $text .= '<tr>';
    $text .= '<thead class="table-primary">';
    $text .= '<th>Rótulo</th>';
    $text .= '<th>Nome</th>';
    $text .= '<th>Tipo</th>';
    $text .= '<th>Tamanho</th>';
    $text .= '<th>Obrigatório</th>';
    $text .= '<th>Campo Chave</th>';
    $text .= '</thead>';
    $text .= '</tr>';
    $tabela = '';
    foreach ($campos as $camp) {
        if (trim($camp['COLUMN_COMMENT']) != 'Excluído em') {
            if ($tabela != $camp['TABLE_NAME']) {
                $text .= '<tr>';
                $text .= '<td colspan="6"><h3>TABELA => ' . $camp['TABLE_NAME'] . '</h3></td>';
                $text .= '</tr>';
                $tabela = $camp['TABLE_NAME'];
            }
            $text .= '<tr>';
            $text .= '<td>' . $camp['COLUMN_COMMENT'] . '</td>';
            $text .= '<td>' . $camp['COLUMN_NAME'] . '</td>';
            $text .= '<td>' . $tipos[$camp['DATA_TYPE']] . '</td>';
            $text .= '<td>' . $camp['COLUMN_SIZE'] . '</td>';
            $text .= '<td>' . $simnao[$camp['IS_NULLABLE']] . '</td>';
            $text .= '<td>' . $chave[$camp['COLUMN_KEY']] . '</td>';
            $text .= '</tr>';
        }
    }
    $text .= '</table>';
    return $text;
};;

/**
 * relacion_tabela
 *  Retorna uma lista dos Relacionamentos da Tabela Especificada
 * @param array $relacionamentos
 * @return string
 */
function relacion_tabela(array $relacionamentos)
{
    $text = '<table border="1" class="table table-light table-bordered table-striped table-hover table-responsive">';
    $text .= '<tr>';
    $text .= '<thead class="table-primary">';
    $text .= '<th>Nome</th>';
    $text .= '<th>Tabela</th>';
    $text .= '<th>Relacionamento</th>';
    $text .= '</thead>';
    $text .= '</tr>';
    foreach ($relacionamentos as $relac) {
        $text .= '<tr>';
        $text .= '<td>' . $relac['CONSTRAINT_NAME'] . '</td>';
        $text .= '<td>' . $relac['REFERENCED_TABLE_NAME'] . ' (' . $relac['TABLE_COMMENT'] . ')</td>';
        $text .= '<td>' . $relac['TABLE_NAME'] . '[' . $relac['COLUMN_NAME'] . ']  =>  ' . $relac['REFERENCED_TABLE_NAME'] . '[' . $relac['REFERENCED_COLUMN_NAME'] . ']</td>';
        $text .= '</tr>';
    }
    $text .= '</table>';
    return $text;
};;

/**
 * verCodigo
 *  Retorna o Código Fonte da Tela Especificada
 * @param string $nomeTela
 * @return string
 */
function verCodigo($nomeTela)
{
    $arq    = APPPATH . $nomeTela . '.php';
    $text   = '';
    $text   .= $arq . '<br>';
    // $text  = file_get_contents($arq);
    if (file_exists($arq)) {
        $text_arr  = file($arq);
        for ($l = 0; $l < count($text_arr); $l++) {
            $linha = $text_arr[$l];
            $text .= ($l + 1) . ' => ' . htmlspecialchars($linha);
        }
    } else {
        $text .= 'Tela ainda não Codificada!';
    }
    return $text;
};;

/**
 * toDataBr
 * Retorna uma data em formato americano para o formato brasileiro
 * @param DateTime $datahora
 * @return string
 */
function toDataBr(DateTime $datahora): string
{
    return $datahora->format('d/m/Y H:i:s');
};;

/**
 * data_br
 * Converte uma data no formato de banco para o formato brasileiro
 * YYYY-MM-DD => dd/mm/yyyy
 * @param    string
 * @return    string
 */
function data_br($data_bd)
{
    $ret = '';
    if ($data_bd != '') {
        $final_  = '' . substr($data_bd, 10);
        $data_or = substr($data_bd, 0, 10);
        $parts   = explode('-', $data_or);
        $ret = $parts[2] . '/' . $parts[1] . '/' . $parts[0];
        if (trim($final_) != '00:00:00' && trim($final_) != '') {
            $ret = $ret . $final_;
        }
    }
    // $data_cn = implode('/', array_reverse(explode('-', $data_or)));
    // return $data_bd.' => ' . $ret;
    return $ret;
};;

/**
 * data_db
 * Converte uma data no formato brasileiro para o formato de banco
 * dd/mm/yyyy => YYYY-MM-DD
 * @param    string
 * @return    string
 */
function data_db($data_br)
{
    return implode('-', array_reverse(explode('/', $data_br)));
}

/**
 * dif_tempo
 * Retorna diferença entre data-hora inicial e data-hora final
 * 
 * [#diferenca]
 * [#anos]
 * [#meses]
 * [#dias]
 * [#horas]
 * [#minutos]
 * [#seconds]
 * @param mixed $tempo_ini
 * @param mixed $tempo_fim
 * @return array
 */
function dif_tempo($tempo_ini, $tempo_fim)
{
    $tempo_ini = strtotime($tempo_ini);
    $tempo_fim = strtotime($tempo_fim);

    $diff = $tempo_fim - $tempo_ini;
    log_message('info', 'diferenca tempo ' . $diff);
    $ret['diferenca'] = $diff;

    $years = floor($diff / (365 * 60 * 60 * 24));
    $ret['anos'] = $years;

    $months = floor(($diff - $years * 365 * 60 * 60 * 24)
        / (30 * 60 * 60 * 24));
    $ret['meses'] = $months;

    $days = floor(($diff - $years * 365 * 60 * 60 * 24 -
        $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
    $ret['dias'] = $days;

    $hours = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24)
        / (60 * 60));
    $ret['horas'] = $hours;

    $minutes = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60) / 60);
    $ret['minutos'] = $minutes;

    $seconds = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60 - $minutes * 60));
    $ret['seconds'] = $seconds;

    return $ret;
};;

/**
 * url_amigavel
 * Retira acentos, substitui espaço por - e
 * deixa tudo minúsculo
 *
 * @param    string
 * @return    string
 */
function url_amigavel($variavel)
{
    $procurar = array('á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', ' ', '.', '+');
    $substituir = array('a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', '_', '.', '_');
    $variavel = mb_strtolower($variavel);
    $variavel = str_replace($procurar, $substituir, $variavel);
    $variavel = htmlentities($variavel);
    $variavel = preg_replace("/&(acute|cedil|circ|ring|tilde|uml);/", "$1", $variavel);
    return trim($variavel, "-");
};;
/**
 * formata_texto
 * Formata Texto para exibição sem códigos HTML
 * 
 * @param mixed $texto
 * @return string
 */
function formata_texto($texto)
{
    $texto_ret = html_entity_decode(trim(strip_tags(utf8_decode($texto))));
    return $texto_ret;
};;

/**
 * debug
 * Mostra mensagens de Debug
 * @param mixed $valor
 * @param mixed $parada // continua ou para o processamento
 * @return void
 */
function debug($valor, $parada = false)
{
    if (is_array($valor) || is_object($valor)) {
        echo "array";
        echo "<div style = 'border:1px solid green; background-color: #ccc; color:green;left:10%;width:80%; max-height:80vh; overflow-y:auto; top:10px; position: relative; z-index:5000'>";
        echo "<pre>";
        print_r($valor);
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style = 'border:1px solid green; background-color: #ccc; color:green;left:10%;width:80%; max-height:80vh; overflow-y:auto; top:10px; position: relative; z-index:5000'>";
        echo ($valor);
        echo "</div>";
    }
    if ($parada) {
        exit;
    }
    return;
};;

/**
 * isMobile
 * Retorna se o dispositivo usado para acessar é um dispositivo móvel
 * 
 * @return bool
 */
function isMobile()
{
    $mobile = FALSE;
    $user_agents = array("iPhone", "iPad", "Android", "webOS", "BlackBerry", "iPod", "Symbian", "IsGeneric");

    foreach ($user_agents as $user_agent) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], $user_agent) !== FALSE) {
            $mobile = TRUE;
            break;
        }
    }
    return $mobile;
};;


/**
 * post_url
 * Executa uma URL externa com CURL
 *
 * @param string $url 
 * @param string $params 
 */

function post_url($url, $params = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json"));

    if ($params) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // echo $ch;
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
};;

/**
 * montarLink
 * Monta um link a partir de um texto
 * @param mixed $texto
 * @return mixed
 */
function montarLink($texto)
{
    if (!is_string($texto)) {
        return $texto;
    }

    $er = "/((http|https|ftp|ftps):\/\/(w+\.)?|\.)([a-zA-Z0-9]+|_|-)+(\.(([0-9a-zA-Z]|-|_|\/|\?|=|&)+))+/i";
    preg_match_all($er, $texto, $match);

    foreach ($match[0] as $link) {
        //coloca o 'http://' caso o link não o possua
        if (stristr($link, "http://") === false && stristr($link, "https://") === false) {
            $link_completo = "http://" . $link;
        } else {
            $link_completo = $link;
        }

        $link_len = strlen($link);

        //troca "&" por "&", tornando o link válido pela W3C
        $web_link = str_replace("&", "&amp;", $link_completo);
        $texto = str_ireplace($link, "<a href=\"" . $web_link . "\" target=\"_blank\">" . (($link_len > 60) ?
            substr($web_link, 0, 25) . "..." . substr($web_link, -15) :
            $web_link) . "</a>", $texto);
    }
    return $texto;
};;


/**
 * get_string_between
 * Retorna a String contida, entre a String inicial e String final informada
 * 
 * @param string $string - Texto completo
 * @param string $start  - String inicial
 * @param string $end    - String final
 * @return string
 */
function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
};;


/**
 * buscaLog
 * Retorna os Logs do Registro da Tabela informados
 *
 * @param mixed $tabela
 * @param mixed $registro
 * @return array
 */
function buscaLog($tabela, $registro)
{
    $logs      = new LogMonModel();
    $logId = $logs->get_logs_last($tabela, $registro);
    $dados = [];
    $operacao = '';
    if ($logId) {
        foreach ($logId as $document) {
            $operacao    = $document->log_operacao;
            $user_nam    = $document->log_id_usuario;
            $data_alt    = data_br($document->log_data);
        }
        if ($operacao != '') {
            $dados['operacao']      = $operacao;
            $dados['usua_alterou']  = $user_nam;
            $dados['data_alterou']  = $data_alt;
            $dados['tabela']        = $tabela;
            $dados['registro']      = $registro;
        }
    }

    return $dados;
};;

/**
 * buscaLog
 * Retorna os Logs do Registro da Tabela informados
 *
 * @param mixed $tabela
 * @param mixed $registro
 * @return array
 */
function buscaLogTabela($tabela, $registros)
{
    $logs      = new LogMonModel();
    $logId = $logs->get_logs_lastVarios($tabela, $registros);
    $dados = [];
    $operacao = '';
    // $ct = 0;
    if ($logId) {
        foreach ($logId as $document) {
            // debug($document, true);
            $operacao    = $document->last_record->log_operacao;
            $user_nam    = $document->last_record->log_id_usuario;
            $registro    = $document->last_record->log_id_registro;
            $data_alt    = data_br($document->last_record->log_data);
            if ($operacao != '') {
                $dados[$registro]['operacao']      = $operacao;
                $dados[$registro]['usua_alterou']  = $user_nam;
                $dados[$registro]['data_alterou']  = $data_alt;
                $dados[$registro]['tabela']        = $tabela;
                $dados[$registro]['registro']      = $registro;
                // $ct++;
            }
        }
    }

    return $dados;
};;


/**
 * moedaToFloat
 * Converte o Valor em String para um float com ponto
 * separando os decimais
 * @param mixed $valor
 * @return float
 */
function moedaToFloat($valor)
{
    if ($valor == 'null' || $valor == null) {
        $valor_ins = 0;
    } else {
        if (strpos($valor, '.') != '') {
            $valor_ini = str_replace(',', '', $valor);
            $valor_ins = $valor_ini;
        } else {
            $valor_ins = str_replace(',', '.', $valor);
        }
    }
    return $valor_ins;
};;

/**
 * floatToMoeda
 * Converte o Valor em Float para uma String formatada com 2 casas decimais
 * separadas por vírgula
 * @param mixed $valor
 * @return string
 */
// use NumberFormatter;
function floatToMoeda($valor)
{

    $moeda = $valor;
    if (floatval($valor)) {
        $fmt = numfmt_create('pt-BR', NumberFormatter::CURRENCY);
        $moeda = numfmt_format($fmt, floatval($valor));
    }
    return $moeda;
};;

/**
 * floatToQuantia
 * Converte o Valor em Float para uma String formatada com 
 * as casas decimais informadas no parametro decimais
 * separadas por ponto
 * @param mixed $valor
 * @param mixed $decimais
 * @return string
 */
function floatToQuantia($valor, $decimais = 3)
{
    debug('chegou Qtia ' . $valor);
    debug('chegou decimal ' . $decimais);
    $valor = str_replace(',', '.', $valor);

    $achadec = explode('.', $valor);
    if (isset($achadec[1]) && intval($achadec[1]) > 0) {
        $quantia = floatval($valor);
        if ($decimais > 7) {
            $decimais = 7;
        }
    } else {
        $decimais = 0;
        $quantia  = intval($valor);
    }
    debug('Decimais ' . $decimais);
    $quantia = number_format($quantia, $decimais, '.');
    return $quantia;
};;

/**
 * buscaArquivos
 * Busca os arquivos da pasta informada
 * @param string $pastaapp
 * @param boolean $completo
 * @param array $exceto
 * @return array
 */
function buscaArquivos($pastaapp, $completo = true, $exceto = [])
{
    $dir = new DirectoryIterator($pastaapp);
    // array contendo os diretórios permitidos    
    $diretoriosPermitidos = array(
        "app",
        "Controllers",
        "Filters",
        "Helpers",
        "Libraries",
        "Views",
        "Models",
        "Config",
        "Micro",
        "Microb",
        "Estoque",
        "Estoqu",
        "Produto",
        "Produt",
        "Ocorrencia",
        "Ocorre",
        "public",
        "assets",
        "jscript"
    );
    $arquivosPermitidos = array("php", "js");

    $arquivos = [];
    foreach ($dir as $file) {
        // verifica se $file é diferente de '.' ou '..'
        if (!$file->isDot()) {
            // listando somente os diretórios
            if ($file->isDir()) {
                // atribui o nome do diretório a variável
                $dirName = $file->getFilename();
                // listando somente o diretório permitido
                if (in_array($dirName, $diretoriosPermitidos)) {
                    // subdiretórios
                    $caminho = $file->getPathname();
                    // chamada da função de recursividade
                    $recurs = recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto);
                    for ($r = 0; $r < count($recurs); $r++) {
                        array_push($arquivos, $recurs[$r]);
                    }
                }
            }
            // listando somente os arquivos do diretório
            if ($file->isFile()) {
                if (in_array($file->getExtension(), $arquivosPermitidos)) {
                    if (!in_array($file->getFilename(), $exceto)) {
                        if ($completo) {
                            $arquiv = $file->getPathname();
                        } else {
                            $arquiv = $file->getBasename('.php');
                        }
                        array_push($arquivos, $arquiv);
                    }
                }
                //
            }
        }
    }
    return $arquivos;
};;

function recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto)
{
    global $dirName;
    $DI = new DirectoryIterator($caminho);
    $arq = [];
    foreach ($DI as $file) {
        if (!$file->isDot()) {
            if ($file->isDir()) {
                // atribui o nome do diretório a variável
                $dirName = $file->getFilename();
                // listando somente o diretório permitido
                if (in_array($dirName, $diretoriosPermitidos)) {
                    // subdiretórios
                    $caminho = $file->getPathname();
                    // chamada da função de recursividade
                    $recurs = recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto);
                    for ($r = 0; $r < count($recurs); $r++) {
                        array_push($arq, $recurs[$r]);
                    }
                }
            }
            // listando somente os arquivos do diretório
            if ($file->isFile()) {
                if (in_array($file->getExtension(), $arquivosPermitidos)) {
                    // atribui o nome do arquivo a variável
                    if (!in_array($file->getFilename(), $exceto)) {
                        $caminho = $file->getPathname();
                        if ($completo) {
                            $arquiv = $file->getPathname();
                        } else {
                            $arquiv = $file->getBasename('.php');
                        }
                        array_push($arq, $arquiv);
                    }
                }
                //
            }
        }
    }
    return $arq;
};;

/**
 * fmtEtiquetaCorBst
 * Formata a Etiqueta de Cor do Bootstrap
 * @param mixed $cor
 * @return string
 */
function fmtEtiquetaCorBst($cor, $label = '')
{
    $cores["bg-primary"] = "&nbsp";
    $texto['bg-primary'] = 'text-black';
    $cores['bg-secondary'] = "&nbsp";
    $texto['bg-secondary'] = 'text-white';
    $cores['bg-success'] = "&nbsp";
    $texto['bg-success'] = 'text-black';
    $cores['bg-danger'] = "&nbsp";
    $texto['bg-danger'] = 'text-black';
    $cores['bg-warning'] = "&nbsp";
    $texto['bg-warning'] = 'text-black';
    $cores['bg-info'] = "&nbsp";
    $texto['bg-info'] = 'text-black';
    $cores['bg-dark'] = "&nbsp";
    $texto['bg-dark'] = 'text-white';
    $cores['bg-white'] = "&nbsp";
    $texto['bg-white'] = 'text-black';
    if ($label == '') {
        $label = $cores[$cor];
    }

    $ret = "<span class='$cor $texto[$cor] px-3 py-1 w-100 d-inline-block rounded rounded-4 text-center text-black text-nowrap'>$label</span>";
    return $ret;
}

/**
 * fmtEtiquetaCorBst
 * Formata a Etiqueta de Cor do Bootstrap
 * @param mixed $cor
 * @return string
 */
function fmtEtiquetaCor($cor, $label = '')
{
    if ($label == '') {
        $label = $cor;
    }
    $cortexto = getContrastYIQ(substr($cor, 1, strlen($cor)));
    $ret = "<span style='background-color:$cor;color:$cortexto;border:1px solid $cortexto;width: 40ch' class='px-3 py-1 d-inline-block rounded rounded-4 text-center text-nowrap '>$label</span>";
    return $ret;
}
function getContrast50($hexcolor)
{
    return (hexdec($hexcolor) > 0xffffff / 2) ? 'black' : 'white';
}
function getContrastYIQ($hexcolor)
{
    $r = hexdec(substr($hexcolor, 0, 2));
    $g = hexdec(substr($hexcolor, 2, 2));
    $b = hexdec(substr($hexcolor, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($yiq >= 128) ? 'black' : 'white';
}

function buscaTipoArquivo($dados)
{
    $imgarquivo    = '';
    if (substr($dados->arq_tipo, 0, 5) == 'image') {
        $imgarquivo = $dados->arq_conteudo;
    } else {
        $tipo[''] = '/assets/uploads/tipo_arquivo/vazio.png';
        $tipo['application/x-zip-compressed'] = '/assets/uploads/tipo_arquivo/zip.png';
        $tipo['application/zip']              = '/assets/uploads/tipo_arquivo/zip.png';
        $tipo['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = '/assets/uploads/tipo_arquivo/xlsx.png';
        $tipo['application/xlsx']             = '/assets/uploads/tipo_arquivo/xlsx.png';
        $tipo['application/xls']              = '/assets/uploads/tipo_arquivo/xls.png';
        $tipo['text/plain']              = '/assets/uploads/tipo_arquivo/txt.png';
        $tipo['application/txt']              = '/assets/uploads/tipo_arquivo/txt.png';
        $tipo['application/x-compressed']     = '/assets/uploads/tipo_arquivo/rar.png';
        $tipo['application/rar']              = '/assets/uploads/tipo_arquivo/rar.png';
        $tipo['application/psd']              = '/assets/uploads/tipo_arquivo/psd.png';
        $tipo['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '/assets/uploads/tipo_arquivo/pptx.png';
        $tipo['application/pptx']             = '/assets/uploads/tipo_arquivo/pptx.png';
        $tipo['application/ppt']              = '/assets/uploads/tipo_arquivo/ppt.png';
        $tipo['application/pdf']              = '/assets/uploads/tipo_arquivo/pdf.png';
        $tipo['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = '/assets/uploads/tipo_arquivo/docx.png';
        $tipo['application/docx']             = '/assets/uploads/tipo_arquivo/docx.png';
        $tipo['application/doc']              = '/assets/uploads/tipo_arquivo/doc.png';
        $tipo['application/postscript'] = '/assets/uploads/tipo_arquivo/ai.png';
        $tipo['application/x-vnd.corel.zcf.draw.document+zip']              = '/assets/uploads/tipo_arquivo/cdr.png';
        $tipo['application/cdr']              = '/assets/uploads/tipo_arquivo/cdr.png';
        if ($tipo[$dados->arq_tipo]) {
            $imgarquivo = $tipo[$dados->arq_tipo];
        }
    }
    return $imgarquivo;
}
