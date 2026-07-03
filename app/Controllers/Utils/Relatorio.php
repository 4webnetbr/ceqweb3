<?php

namespace App\Controllers\Utils;

use App\Controllers\BaseController;
use App\Controllers\CriamPdf2026;
use App\Libraries\MyCampo;
use App\Libraries\MymPdf2026;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigRelatoriosModel;
use App\Models\Config\ConfigRelColunasModel;
use App\Models\Config\ConfigRelFiltrosModel;

/**
 * Consumo dos relatórios cadastrados em Config\CfgRelatorio, por módulo,
 * para relatórios sem tela própria (tel_id IS NULL). Acessado a partir da
 * opção "Relatórios" injetada dinamicamente no menu (ver montaMenu() em
 * app/Common.php).
 *
 * Não existe tela cadastrada por relatório individual, então a única
 * proteção fina de acesso é a checagem manual contra cfg_rel_permissao,
 * feita em CADA método via ConfigRelatoriosModel::relatorioPermitido().
 */
class Relatorio extends BaseController
{
    protected ConfigRelatoriosModel $relatorios;
    protected ConfigRelFiltrosModel $filtros;
    protected ConfigRelColunasModel $colunas;
    protected ConfigDicDadosModel   $dicionario;
    protected CommonModel           $common;
    protected array                 $data;
    protected int                   $perfilId;

    public function __construct()
    {
        // Utils/Relatorio agora passa pelo loginFilter normalmente (ver
        // app/Config/Filters.php — só utilidade/upload e utilidade/executa_php ficam
        // de fora), então dados_tela vem populado como em qualquer outra tela.
        $this->data     = session()->getFlashdata('dados_tela') ?? [];
        $this->perfilId = (int) (session()->get('usu_perfil_id') ?? 0);

        // Defaults defensivos — mesmo padrão já usado em templates/default_template.php
        // ($title ??= $controler;). A tela "Utils" cadastrada em cfg_tela pode não ter
        // todos os campos preenchidos (ícone, regras, botão), e esses dados alimentam
        // strut/vw_titulo e strut/vw_menu, que não têm fallback próprio para eles.
        $this->data['controler']       ??= 'Relatorio';
        $this->data['title']           ??= 'Relatórios';
        $this->data['icone']           ??= 'fas fa-chart-bar';
        $this->data['metodo']          ??= 'index';
        $this->data['permissao']       ??= '';
        $this->data['bt_add']          ??= '';
        $this->data['regras_gerais']   ??= '';
        $this->data['regras_cadastro'] ??= '';
        $this->data['destino']         ??= '';
        $this->data['erromsg']         ??= '';
        $this->data['it_menu']         ??= (session()->get('menu') ?? []);

        $this->relatorios = new ConfigRelatoriosModel();
        $this->filtros    = new ConfigRelFiltrosModel();
        $this->colunas    = new ConfigRelColunasModel();
        $this->dicionario = new ConfigDicDadosModel();
        $this->common     = new CommonModel();

        if (!empty($this->data['erromsg'])) {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
        exit;
    }

    /**
     * Lista os relatórios do módulo informado, liberados para o perfil
     * logado, como botões empilhados.
     */
    public function index(int $mod_id)
    {
        $relatorios = $this->relatorios->getRelatoriosPorModuloPerfil($mod_id, $this->perfilId);
        $modulo = (new ConfigModuloModel())->getModulo($mod_id);

        $this->data['metodo']     = 'index';
        $this->data['title']     = 'Relatórios' . (!empty($modulo->mod_nome) ? ' - ' . $modulo->mod_nome : '');
        $this->data['mod_id']     = $mod_id;
        $this->data['mod_nome']   = $modulo->mod_nome ?? '';
        $this->data['relatorios'] = $relatorios;

        echo view('Utils/vw_relatorio_lista', $this->data);
    }

    /**
     * Monta a tela de filtro dinâmica do relatório: um campo por filtro
     * cadastrado (DATE -> daterange, FK -> multi-select com SELECT DISTINCT
     * na tabela base do relatório).
     */
    public function filtro(int $rel_id)
    {
        $relatorio = $this->relatorios->relatorioPermitido($rel_id, $this->perfilId);
        if (!$relatorio) {
            $this->data['erromsg'] = '<h2>Sem autorização para acessar este relatório</h2><br>'
                . 'Solicite acesso ao Administrador do Sistema';
            return $this->__erro();
        }

        $listaFiltros = $this->filtros->getFiltros($rel_id); // já ordenado por rfi_ordem

        $camposHtml = [];
        foreach ($listaFiltros as $f) {
            $camposHtml[] = $this->_montaCampoFiltro($f, $relatorio->rel_tabela_base);
        }

        $this->data['metodo']        = 'filtro';
        $this->data['desc_metodo']        = '';
        $this->data['title']         = $relatorio->rel_titulo;
        $this->data['rel_id']        = $rel_id;
        $this->data['relatorio']     = $relatorio;
        $this->data['campos_filtro'] = $camposHtml;
        $this->data['url_gerar']     = base_url('Relatorio/gerar/' . $rel_id);

        echo view('Utils/vw_relatorio_filtro', $this->data);
    }

    /**
     * Executa o relatório com WHERE real montado a partir dos filtros
     * selecionados (POST) e devolve o HTML puro (sem menu/template) para
     * ser exibido na nova aba aberta pelo form da tela de filtro. Inclui uma
     * barra fixa no topo com os botões de exportação/impressão.
     */
    public function gerar(int $rel_id)
    {
        $preparo = $this->_prepararRelatorioFiltrado($rel_id);
        if (!$preparo) {
            return $this->__erro();
        }

        $corpo = (new CriamPdf2026())->htmlRelatorioGenerico($preparo['config'], false, $preparo['valoresFiltro']);
        $barra = $this->_barraExportacao($rel_id, $preparo['post']);

        // htmlRelatorioGenerico() devolve só o fragmento do relatório (sem <html>/<head>);
        // embrulhamos aqui pra a aba do navegador mostrar título e favicon corretos, e pra
        // carregar Bootstrap/FontAwesome (necessários pros botões da barra de exportação).
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8">'
            . '<title>' . esc($preparo['relatorio']->rel_titulo) . '</title>'
            . '<link rel="shortcut icon" href="' . base_url('assets/images/favicon.ico') . '" type="image/x-icon">'
            . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">'
            . '<link rel="stylesheet" href="' . base_url('assets/fontawesome/css/all.css') . '">'
            . '<style>@media print { .barra-exportacao { display: none !important; } }</style>'
            . '</head><body>' . $barra . $corpo . '</body></html>';

        return $this->response->setContentType('text/html')->setBody($html);
    }

    /**
     * Exporta o relatório atual (mesmos filtros da tela) em Excel (.xlsx).
     */
    public function exportarExcel(int $rel_id)
    {
        $preparo = $this->_prepararRelatorioFiltrado($rel_id);
        if (!$preparo) {
            return $this->__erro();
        }

        $conteudo = (new CriamPdf2026())->exportarExcelRelatorio($preparo['config'], $preparo['valoresFiltro']);
        $arquivo  = $this->_nomeArquivoExport($preparo['relatorio']->rel_titulo, 'xlsx');

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $arquivo . '"')
            ->setBody($conteudo);
    }

    /**
     * Exporta o relatório atual (mesmos filtros da tela) em Word (.docx).
     */
    public function exportarWord(int $rel_id)
    {
        $preparo = $this->_prepararRelatorioFiltrado($rel_id);
        if (!$preparo) {
            return $this->__erro();
        }

        $conteudo = (new CriamPdf2026())->exportarWordRelatorio($preparo['config'], $preparo['valoresFiltro']);
        $arquivo  = $this->_nomeArquivoExport($preparo['relatorio']->rel_titulo, 'docx');

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $arquivo . '"')
            ->setBody($conteudo);
    }

    /**
     * Exporta o relatório atual (mesmos filtros da tela) em PDF, via mPDF —
     * mesmo motor de Config\CriamPdf2026::PrintRelatorioGenerico(), mas
     * respeitando os filtros aplicados nesta tela (aquele método não filtra).
     */
    public function exportarPdf(int $rel_id)
    {
        $preparo = $this->_prepararRelatorioFiltrado($rel_id);
        if (!$preparo) {
            return $this->__erro();
        }

        $criamPdf = new CriamPdf2026();
        $html = $criamPdf->htmlRelatorioGenerico($preparo['config'], false, $preparo['valoresFiltro'], true);

        $orientacao = ($preparo['config']['formato'] ?? 'P') === 'L' ? 'L' : 'P';
        // ANTES (BKP 30/06/2026): $pdf = new MymPdf2026(true, true, 'A4', $orientacao);
        // temHeader=false: htmlRelatorioGenerico(..., true) já injeta o cabeçalho via
        // <htmlpageheader> nativo do mPDF (ver CriamPdf2026::htmlRelatorioGenerico()).
        $pdf = new MymPdf2026(false, true, 'A4', $orientacao);
        $pdf->titulo($preparo['config']['titulo']);
        $pdf->html($html);

        $arquivo  = $this->_nomeArquivoExport($preparo['relatorio']->rel_titulo, 'pdf');
        $conteudo = $pdf->Output($arquivo, 'S');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $arquivo . '"')
            ->setBody($conteudo);
    }

    /**
     * Checa permissão do relatório e monta $config + $valoresFiltro a partir
     * do POST atual — compartilhado por gerar()/exportarExcel()/exportarWord()/
     * exportarPdf(), que precisam do MESMO relatório filtrado em formatos de
     * saída diferentes.
     *
     * @return array{relatorio: object, config: array, valoresFiltro: array, post: array}|null null = sem permissão (erromsg já setado em $this->data)
     */
    private function _prepararRelatorioFiltrado(int $rel_id): ?array
    {
        $relatorio = $this->relatorios->relatorioPermitido($rel_id, $this->perfilId);
        if (!$relatorio) {
            $this->data['erromsg'] = '<h2>Sem autorização para acessar este relatório</h2><br>'
                . 'Solicite acesso ao Administrador do Sistema';
            return null;
        }

        $colunasBD = $this->colunas->getColunas($rel_id);
        $filtrosBD = $this->filtros->getFiltros($rel_id);

        $colunas = [];
        foreach ($colunasBD as $c) {
            $colunas[] = [
                'tabela'        => $c->rco_tabela,
                'campo'         => $c->rco_campo,
                'tamanho'       => (int) $c->rco_tamanho,
                'tipo_dado'     => $c->rco_tipo_dado ?? '',
                'label'         => $c->rco_label,
                'alinhamento'   => $c->rco_alinhamento,
                'totalizar'     => (int) ($c->rco_totalizar ?? 0),
                'largura'       => (int) ($c->rco_largura ?? 0),
                'comportamento' => $c->rco_comportamento ?? 'cortar',
            ];
        }

        $filtros       = [];
        $valoresFiltro = [];
        $post          = $this->request->getPost();

        foreach ($filtrosBD as $f) {
            $filtros[] = [
                'campo'       => $f->rfi_campo,
                'tabela'      => $f->rfi_tabela,
                'tipo_filtro' => $f->rfi_tipo_filtro,
                'label'       => $f->rfi_label,
            ];

            $nomeParam = 'f_' . $f->rfi_campo;

            if ($f->rfi_tipo_filtro === 'DATE') {
                // valor vem do daterangepicker como "DD/MM/YYYY - DD/MM/YYYY"
                $bruto = trim($post[$nomeParam] ?? '');
                if (str_contains($bruto, ' - ')) {
                    [$deBr, $ateBr] = array_map('trim', explode(' - ', $bruto, 2));
                    $de  = \DateTime::createFromFormat('d/m/Y', $deBr);
                    $ate = \DateTime::createFromFormat('d/m/Y', $ateBr);
                    if ($de && $ate) {
                        // ANTES (BKP 01/07/2026): 'de' => $de->format('Y-m-d'), 'ate' => $ate->format('Y-m-d'),
                        // Colunas do tipo DATETIME comparadas via BETWEEN com só a data (sem
                        // horário) tratam ambos os limites como 00:00:00 — um período de um
                        // único dia (ex.: 25/06 a 25/06) não trazia nenhum registro, já que só
                        // bateria com algo exatamente à meia-noite. Fixando 00:00:00/23:59:59
                        // o dia inteiro fica coberto, tanto pra DATE quanto pra DATETIME.
                        $valoresFiltro[] = [
                            'campo'       => $f->rfi_campo,
                            'tipo_filtro' => 'DATE',
                            'de'          => $de->format('Y-m-d') . ' 00:00:00',
                            'ate'         => $ate->format('Y-m-d') . ' 23:59:59',
                        ];
                    }
                }
            } else {
                $vals = $post[$nomeParam] ?? [];
                if (!is_array($vals)) {
                    $vals = $vals !== '' ? [$vals] : [];
                }
                if (!empty($vals)) {
                    $valoresFiltro[] = [
                        'campo'       => $f->rfi_campo,
                        'tipo_filtro' => 'FK',
                        'valores'     => $vals,
                    ];
                }
            }
        }

        $config = [
            'titulo'              => $relatorio->rel_titulo,
            'nome'                => $relatorio->rel_nome ?? '',
            'formato'             => $relatorio->rel_formato,
            'tamanho_fonte'       => (int) $relatorio->rel_tamanho_fonte,
            'tabela_base'         => $relatorio->rel_tabela_base,
            'totalizar_registros' => (int) ($relatorio->rel_totalizar_registros ?? 0),
            'colunas'             => $colunas,
            'filtros'             => $filtros,
        ];

        return [
            'relatorio'     => $relatorio,
            'config'        => $config,
            'valoresFiltro' => $valoresFiltro,
            'post'          => $post,
        ];
    }

    /**
     * Monta a barra fixa no topo do relatório (gerar()) com os botões de
     * exportação — cada um é um mini-form que reenvia os MESMOS filtros
     * (via _hiddenInputsPost()) pro endpoint de exportação correspondente,
     * abrindo o download na mesma aba. "Imprimir" só chama window.print();
     * a própria barra fica oculta na impressão (ver <style> em gerar()).
     */
    private function _barraExportacao(int $rel_id, array $post): string
    {
        $hidden   = $this->_hiddenInputsPost($post);
        $urlExcel = base_url('Relatorio/exportarExcel/' . $rel_id);
        $urlWord  = base_url('Relatorio/exportarWord/' . $rel_id);
        $urlPdf   = base_url('Relatorio/exportarPdf/' . $rel_id);

        return '<div class="barra-exportacao" style="position:sticky; top:0; z-index:1000;'
            . ' background:#f8f9fa; border-bottom:1px solid #ccc; padding:8px 12px; text-align:center;">'
            . '<form method="post" action="' . $urlExcel . '" style="display:inline-block; margin:0 4px;">' . $hidden
            . '<button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel"></i> Exportar Excel</button></form>'
            . '<form method="post" action="' . $urlWord . '" style="display:inline-block; margin:0 4px;">' . $hidden
            . '<button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-word"></i> Exportar Docx</button></form>'
            . '<form method="post" action="' . $urlPdf . '" style="display:inline-block; margin:0 4px;">' . $hidden
            . '<button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i> Exportar PDF</button></form>'
            . '<button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm" style="margin:0 4px;">'
            . '<i class="fas fa-print"></i> Imprimir</button>'
            . '</div>';
    }

    /**
     * Converte os dados de $_POST em <input type="hidden">, preservando
     * arrays (ex.: multi-select "f_campo[]" vira múltiplos inputs), pra
     * reenviar os mesmos filtros aos botões da barra de exportação.
     */
    private function _hiddenInputsPost(array $post, string $prefixo = ''): string
    {
        $html = '';
        foreach ($post as $chave => $valor) {
            $nome = $prefixo === '' ? (string) $chave : $prefixo . '[' . $chave . ']';

            if (is_array($valor)) {
                $ehLista = array_keys($valor) === range(0, count($valor) - 1);
                foreach ($valor as $k => $v) {
                    if (is_array($v)) {
                        $html .= $this->_hiddenInputsPost([$k => $v], $nome);
                        continue;
                    }
                    $subNome = $ehLista ? $nome . '[]' : $nome . '[' . $k . ']';
                    $html .= '<input type="hidden" name="' . esc($subNome) . '" value="' . esc((string) $v) . '">';
                }
                continue;
            }

            $html .= '<input type="hidden" name="' . esc($nome) . '" value="' . esc((string) $valor) . '">';
        }

        return $html;
    }

    /**
     * Nome de arquivo seguro (sem acentos/espaços/caracteres especiais) pros
     * downloads de exportação, com timestamp pra evitar sobrescrever.
     */
    private function _nomeArquivoExport(string $titulo, string $extensao): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $titulo);
        $base = trim((string) $base, '_') ?: 'relatorio';

        return $base . '_' . date('Ymd_His') . '.' . $extensao;
    }

    /**
     * Monta o HTML de UM campo de filtro (daterange ou multi-select via
     * SELECT DISTINCT na tabela base do relatório).
     */
    private function _montaCampoFiltro($filtroEnt, string $tabelaBase): string
    {
        $campo = new MyCampo();
        $campo->nome = $campo->id = 'f_' . $filtroEnt->rfi_campo;
        $campo->label = $filtroEnt->rfi_label;
        $campo->obrigatorio = (bool) $filtroEnt->rfi_obrigatorio;
        $campo->setDispForm('col-6')->setLargura(35);

        if ($filtroEnt->rfi_tipo_filtro === 'DATE') {
            if (!$filtroEnt->rfi_obrigatorio) {
                // ANTES (BKP 30/06/2026): $campo->setValor('Não Considerar');
                // setValor() não tem efeito aqui: carregamentos_iniciais() em my_fields.js
                // sobrescreve o valor de TODO .daterange no document.ready. A classe
                // "daterange-opcional" sinaliza esse script pra deixar "Não Considerar"
                // em vez do período padrão — gerar() só aplica o filtro quando o valor
                // recebido contém " - " (formato do daterangepicker), então isso já
                // basta pro filtro ser ignorado se o usuário não mexer no campo.
                $campo->classep = 'daterange-opcional';
            }
            return $campo->crDaterange();
        }

        // FK: SELECT DISTINCT na tabela base
        $dbGrSche = $this->dicionario->getDbGroupAndSchema($tabelaBase);
        $linhas = $this->common->getListaTabela(
            $dbGrSche['dbGroup'],
            $tabelaBase,
            [$filtroEnt->rfi_campo],
            false,
            500
        );

        $valores = [];
        foreach ($linhas as $linha) {
            $valor = $linha[$filtroEnt->rfi_campo] ?? null;
            if ($valor === null || $valor === '') {
                continue;
            }
            $valores[] = $valor;
        }

        $opcoes = $this->_ordenaOpcoesFiltro($tabelaBase, $filtroEnt->rfi_campo, $valores);

        $campo->setOpcoes($opcoes);

        return $campo->crMultiple();
    }

    /**
     * Ordena as opções de um filtro pela coluna "{prefixo}_ordem", quando
     * existir. Dois casos cobertos:
     * 1) A própria tabela base do relatório já tem a coluna irmã (comum
     *    quando a base é uma view denormalizada, ex.: colunas "stt_nome" e
     *    "stt_ordem" lado a lado — o filtro usa "stt_nome" direto, sem FK).
     * 2) O campo é uma FK "crua" (ex.: stt_id) e a tabela REFERENCIADA
     *    (cfg_status) tem a coluna "*_ordem".
     * Sem nenhum dos dois, cai no ksort() por valor (comportamento anterior).
     */
    private function _ordenaOpcoesFiltro(string $tabelaBase, string $campo, array $valores): array
    {
        $opcoes = array_combine($valores, $valores);

        // 1) Coluna irmã "{prefixo}_ordem" na PRÓPRIA tabela base.
        $prefixo = strstr($campo, '_', true);
        if ($prefixo !== false) {
            $colunaOrdemLocal = $prefixo . '_ordem';
            $temColunaLocal = false;
            foreach ($this->dicionario->getCampos($tabelaBase) as $col) {
                if ($col['COLUMN_NAME'] === $colunaOrdemLocal) {
                    $temColunaLocal = true;
                    break;
                }
            }

            if ($temColunaLocal) {
                $dbGrSche = $this->dicionario->getDbGroupAndSchema($tabelaBase);
                $linhas = db_connect($dbGrSche['dbGroup'])
                    ->table($tabelaBase)
                    ->distinct()
                    ->select("{$campo} AS valor, {$colunaOrdemLocal} AS ordem")
                    ->whereIn($campo, $valores)
                    ->orderBy('ordem')
                    ->get()->getResultArray();

                $opcoesOrdenadas = [];
                foreach ($linhas as $linha) {
                    $chave = $linha['valor'];
                    if (isset($opcoes[$chave]) && !isset($opcoesOrdenadas[$chave])) {
                        $opcoesOrdenadas[$chave] = $opcoes[$chave];
                    }
                }
                if (!empty($opcoesOrdenadas)) {
                    return $opcoesOrdenadas;
                }
            }
        }

        // 2) Campo é FK crua: busca a coluna "*_ordem" na tabela referenciada.
        $tabelaRef = null;
        $colunaRef = null;
        $rels = $this->dicionario->getRelacionamentos($tabelaBase);
        foreach ($rels['relacionamentos'] as $r) {
            if (
                $r['TABLE_NAME'] === $tabelaBase
                && $r['COLUMN_NAME'] === $campo
                && !empty($r['REFERENCED_TABLE_NAME'])
            ) {
                $tabelaRef = $r['REFERENCED_TABLE_NAME'];
                $colunaRef = $r['REFERENCED_COLUMN_NAME'];
                break;
            }
        }

        if (!$tabelaRef) {
            ksort($opcoes);
            return $opcoes;
        }

        $colunaOrdem = null;
        foreach ($this->dicionario->getCampos($tabelaRef) as $col) {
            if (str_ends_with($col['COLUMN_NAME'], '_ordem')) {
                $colunaOrdem = $col['COLUMN_NAME'];
                break;
            }
        }

        if (!$colunaOrdem) {
            ksort($opcoes);
            return $opcoes;
        }

        $dbGrRef = $this->dicionario->getDbGroupAndSchema($tabelaRef);
        $ordenados = db_connect($dbGrRef['dbGroup'])
            ->table($tabelaRef)
            ->select("{$colunaRef}, {$colunaOrdem}")
            ->whereIn($colunaRef, $valores)
            ->orderBy($colunaOrdem)
            ->get()->getResultArray();

        $opcoesOrdenadas = [];
        foreach ($ordenados as $linha) {
            $chave = $linha[$colunaRef];
            if (isset($opcoes[$chave])) {
                $opcoesOrdenadas[$chave] = $opcoes[$chave];
                unset($opcoes[$chave]);
            }
        }
        // Sobra defensiva: valor que por algum motivo não veio na consulta de ordenação.
        foreach ($opcoes as $chave => $label) {
            $opcoesOrdenadas[$chave] = $label;
        }

        return $opcoesOrdenadas;
    }
}
