<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgRelatorios;
use App\Entities\Config\EntCfgRelFiltros;
use App\Entities\Config\EntCfgRelColunas;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigRelatoriosModel;
use App\Models\Config\ConfigRelFiltrosModel;
use App\Models\Config\ConfigRelColunasModel;
use App\Models\Config\ConfigRelJoinsModel;
use App\Traits\ForeignKeyUsageChecker;


class CfgRelatorio extends BaseController
{
    use ForeignKeyUsageChecker;

    protected ConfigRelatoriosModel $relatorios;
    protected ConfigRelFiltrosModel $filtros;
    protected ConfigRelColunasModel $colunas;
    protected ConfigRelJoinsModel   $joins;
    protected ConfigDicDadosModel   $dicionario;
    protected CommonModel           $common;
    protected array                 $data;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela') ?? [];
        $this->permissao = $this->data['permissao'];

        $this->relatorios = new ConfigRelatoriosModel();
        $this->filtros    = new ConfigRelFiltrosModel();
        $this->colunas    = new ConfigRelColunasModel();
        $this->joins      = new ConfigRelJoinsModel();
        $this->dicionario      = new ConfigDicDadosModel();
        $this->common     = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'mod_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    public function lista()
    {
        $dados = $this->relatorios->getRelatorios(false, 1);

        foreach ($dados as $ent) {
            $ent->tabela = 'cfg_relatorios';
        }

        $this->data['exclusao'] = false;
        echo json_encode([
            'data' => montaListaColunasEnt($this->data, 'rel_id', $dados, 'rel_titulo')
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ADD
    // ─────────────────────────────────────────────────────────────────────────

    public function add()
    {
        // Limpa a tabela base da sessão (será atualizada via AJAX quando o usuário selecionar)
        session()->set('rel_tabela_base_atual', '');

        $erel = new EntCfgRelatorios();

        // Aba Filtros — começa com uma linha vazia
        $fieldsFiltro = $erel->defCamposFiltro();
        $campos_filtros[0] = $this->_linhaFiltro($fieldsFiltro);

        // Aba Colunas — começa com uma linha vazia
        $fieldsColunas = $erel->defCamposColunas();
        $campos_colunas[0] = $this->_linhaColunas($fieldsColunas);

        $this->data['secoes'] = ['Dados Gerais', 'Filtros', 'Colunas', 'Permissões'];
        $this->data['displ']  = [null, 'tabela', 'tabela', null];

        $this->data['campos'] = [
            // Aba 0 — Dados Gerais
            [
                $erel->campos['rel_id'],
                $erel->campos['rel_nome'],
                $erel->campos['mod_id'],
                $erel->campos['tel_id'],
                $erel->campos['rel_tabela_base'],
                $erel->campos['rel_totalizar_registros'],
                $erel->campos['rel_formato'],
                $erel->campos['rel_tamanho_fonte'],
                $erel->campos['rel_titulo'],
                $erel->campos['rel_chars_display'],
            ],
            // Aba 1 — Filtros
            $campos_filtros,
            // Aba 2 — Colunas
            $campos_colunas,
            // Aba 3 — Permissões
            [
                $erel->campos['prf_id'],
            ],
        ];

        $this->data['destino'] = 'store';
        $this->data['script']  = $this->_scriptTela();

        echo view('vw_edicao_relatorio', $this->data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EDIT
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(int $id, bool $show = false)
    {
        $dados = $this->relatorios->getRelatorios($id);

        if (!$dados) {
            return redirectWithError($this->data['controler'], 41);
        }

        $dadosArray = is_object($dados) && method_exists($dados, 'toRawArray') ? $dados->toRawArray() : (array) $dados;

        // Guarda a tabela base na sessão para uso em addFiltro/addColuna (AJAX não envia o form completo)
        session()->set('rel_tabela_base_atual', $dadosArray['rel_tabela_base'] ?? '');

        $erel = new EntCfgRelatorios($dadosArray, $show);

        // ── Aba Filtros ──────────────────────────────────────────────────────
        $lst_filtros = $this->filtros->getFiltros($id);
        $tabelaBase  = $dadosArray['rel_tabela_base'] ?? '';
        $opcoesFil   = $this->_opcoesFiltroPorTabela($tabelaBase);
        // debug($opcoesFil);
        $campos_filtros = [];
        if (count($lst_filtros) > 0) {
            foreach ($lst_filtros as $pos => $f) {
                $fArray = is_object($f) && method_exists($f, 'toRawArray') ? $f->toRawArray() : (array) $f;
                $fields              = $erel->defCamposFiltro($fArray, $show, $pos, $opcoesFil);
                $campos_filtros[$pos] = $this->_linhaFiltro($fields);
            }
        } else {
            $fields              = $erel->defCamposFiltro(false, false, 0, $opcoesFil);
            $campos_filtros[0]   = $this->_linhaFiltro($fields);
        }

        // ── Aba Colunas ──────────────────────────────────────────────────────
        $lst_colunas = $this->colunas->getColunas($id);

        $campos_colunas = [];
        if (count($lst_colunas) > 0) {
            foreach ($lst_colunas as $pos => $c) {
                $cArray = is_object($c) && method_exists($c, 'toRawArray') ? $c->toRawArray() : (array) $c;
                $fields           = $erel->defCamposColunas($cArray, $show, $pos);
                $campos_colunas[$pos] = $this->_linhaColunas($fields);
            }
        } else {
            $fields           = $erel->defCamposColunas();
            $campos_colunas[0] = $this->_linhaColunas($fields);
        }

        $this->data['secoes'] = ['Dados Gerais', 'Filtros', 'Colunas', 'Permissões'];
        $this->data['displ']  = [null, 'tabela', 'tabela', null];

        $this->data['campos'] = [
            [
                $erel->campos['rel_id'],
                $erel->campos['rel_nome'],
                $erel->campos['mod_id'],
                $erel->campos['tel_id'],
                $erel->campos['rel_tabela_base'],
                $erel->campos['rel_totalizar_registros'],
                $erel->campos['rel_formato'],
                $erel->campos['rel_tamanho_fonte'],
                $erel->campos['rel_titulo'],
                $erel->campos['rel_chars_display'],
            ],
            $campos_filtros,
            $campos_colunas,
            [
                $erel->campos['prf_id'],
            ],
        ];

        $this->data['destino']         = 'store';
        $charsLinha = $dadosArray['rel_chars_por_linha'] ?? 0;
        $this->data['rel_chars_linha'] = $charsLinha;
        $this->data['log']             = buscaLog('cfg_relatorios', $id);
        $this->data['script']          = $this->_scriptTela((int) $charsLinha);

        echo view('vw_edicao_relatorio', $this->data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — addFiltro
    //  Chamado pelo botão (+) da aba Filtros para adicionar nova linha vazia
    //  URL: CfgRelatorio/addFiltro/{pos}
    // ─────────────────────────────────────────────────────────────────────────

    public function addFiltro(int $pos)
    {
        // O AJAX do addCampo() não envia o form inteiro, por isso a tabela base
        // vem da sessão (atualizada em add/edit e nos endpoints busca_campos_*_rel)
        $tabelaBase = session()->get('rel_tabela_base_atual') ?? '';
        $opcoesFil  = $this->_opcoesFiltroPorTabela($tabelaBase);
        $fields     = (new EntCfgRelatorios())->defCamposFiltro(false, false, $pos, $opcoesFil);
        echo json_encode($this->_linhaFiltro($fields));
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — addColuna
    //  Chamado pelo botão (+) da aba Colunas para adicionar nova linha vazia
    //  URL: CfgRelatorio/addColuna/{pos}
    // ─────────────────────────────────────────────────────────────────────────

    public function addColuna(int $pos)
    {
        $erel   = new EntCfgRelatorios();
        $fields = $erel->defCamposColunas(false, false, $pos);
        echo json_encode($this->_linhaColunas($fields));
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — previewRelatorio
    //  Retorna HTML do preview do relatório com dados fictícios (10 registros)
    // ─────────────────────────────────────────────────────────────────────────

    public function previewRelatorio()
    {
        $postado = $this->request->getPost();

        $colunas       = [];
        $campos        = $postado['rco_campo']         ?? [];
        $labels        = $postado['rco_label']         ?? [];
        $alinhams      = $postado['rco_alinhamento']   ?? [];
        $totalizar     = $postado['rco_totalizar']     ?? [];
        $tiposDado     = $postado['rco_tipo_dado']     ?? [];
        $larguras      = $postado['rco_largura']       ?? [];
        $comportaments = $postado['rco_comportamento'] ?? [];

        foreach ($campos as $i => $campo) {
            if (empty($campo)) continue;
            $partes = explode('|', $campo);
            $colunas[] = [
                'tabela'        => $partes[0] ?? '',
                'campo'         => $partes[1] ?? $campo,
                'tamanho'       => (int) ($partes[2] ?? 0),
                'tipo_dado'     => $partes[3] ?? ($tiposDado[$i] ?? ''),
                'label'         => $labels[$i] ?? '',
                'alinhamento'   => $alinhams[$i] ?? 'E',
                'totalizar'     => !empty($totalizar[$i]) ? 1 : 0,
                'largura'       => (int) ($larguras[$i] ?? 0),
                'comportamento' => $comportaments[$i] ?? 'cortar',
            ];
        }

        $filtros = [];
        $fCampos = $postado['rfi_campo'] ?? [];
        $fLabels = $postado['rfi_label'] ?? [];
        $fTabelas = $postado['rfi_tabela'] ?? [];
        $fTipos  = $postado['rfi_tipo_filtro'] ?? [];

        foreach ($fCampos as $i => $fc) {
            if (empty($fc)) continue;
            $partes = explode('|', $fc);
            $filtros[] = [
                'campo'       => $partes[0] ?? $fc,
                'tabela'      => !empty($fTabelas[$i]) ? $fTabelas[$i] : ($partes[1] ?? ''),
                'tipo_filtro' => !empty($fTipos[$i]) ? $fTipos[$i] : ($partes[2] ?? 'FK'),
                'label'       => $fLabels[$i] ?? '',
            ];
        }

        $config = [
            'titulo'                => $postado['rel_titulo'] ?? '',
            'nome'                  => $postado['rel_nome'] ?? '',
            'formato'               => $postado['rel_formato'] ?? 'P',
            'tamanho_fonte'         => (int) ($postado['rel_tamanho_fonte'] ?? 10),
            'tabela_base'           => $postado['rel_tabela_base'] ?? '',
            'totalizar_registros'   => !empty($postado['rel_totalizar_registros']) ? 1 : 0,
            'colunas'               => $colunas,
            'filtros'               => $filtros,
        ];

        $html = (new \App\Controllers\CriamPdf2026())->htmlRelatorioGenerico($config, true);
        echo json_encode(['html' => $html]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — camposFiltro
    //  Retorna campos _id e DATE da tabela base para popular o select de campo
    //  URL: CfgRelatorio/camposFiltro?tabela=xxx
    // ─────────────────────────────────────────────────────────────────────────

    public function camposFiltro()
    {
        $tabela = $this->request->getGet('tabela');

        if (!$tabela) {
            echo json_encode(['erro' => true, 'msg' => 'Tabela não informada.']);
            return;
        }
        // $dbGrSche = $this->dicionario->getDbGroupAndSchema($tabela);

        // $db     = db_connect($dbGrSche['dbGroup']);
        $campos = $this->dicionario->getCampos($tabela);
        // debug($campos, true);
        $ret    = [];

        foreach ($campos as $col) {
            $isDate = in_array(strtolower($col['DATA_TYPE']), ['date', 'datetime', 'timestamp']);
            if (str_ends_with($col['COLUMN_NAME'], '_excluido')) continue;
            if ($col['COLUMN_KEY'] !== '' || $isDate) {
                $ret[] = [
                    'id'   => $col['COLUMN_NAME'],
                    'text' => $col['NOME_COMPLETO'],
                ];
            }
        }

        echo json_encode(['erro' => false, 'campos' => $ret]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — camposColunas
    //  Retorna TODOS os campos de todas as tabelas relacionadas via
    //  getRelacionamentos(), com tamanho, para popular o select de campo
    //  URL: CfgRelatorio/camposColunas?tabela=xxx
    // ─────────────────────────────────────────────────────────────────────────

    public function camposColunas()
    {
        $tabela = $this->request->getGet('tabela');

        if (!$tabela) {
            echo json_encode(['erro' => true, 'msg' => 'Tabela não informada.']);
            return;
        }

        $relacionamentos = $this->dicionario->getRelacionamentos($tabela);
        // debug($relacionamentos);
        $ret = [];

        $tabelas = [];
        $tabelas[] = $tabela;
        foreach ($relacionamentos['relacionamentos'] as $rel) {
            // debug($rel);
            if (!empty($rel['REFERENCED_TABLE_NAME'])) {
                $tabelas[] = $rel['REFERENCED_TABLE_NAME'];
            }
        }
        // debug($tabelas, true);
        $tabelas = array_unique($tabelas);
        foreach ($tabelas as $tab) {
            $campos = $this->dicionario->getCampos($tab);

            foreach ($campos as $col) {
                if (str_ends_with($col['COLUMN_NAME'], '_excluido')) continue;
                if ($col['COLUMN_KEY'] !== '') continue;

                $ret[] = [
                    'id'   => $tab . '|' . $col['COLUMN_NAME'] . '|' . ($col['CHARACTER_MAXIMUM_LENGTH'] ?? 0),
                    'text' => '[' . $tab . '] ' . $col['NOME_COMPLETO'],
                ];
            }
        }

        echo json_encode(['erro' => false, 'campos' => $ret]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — charsLinha
    //  Recalcula e devolve chars_por_linha quando formato ou fonte mudam
    //  URL: CfgRelatorio/charsLinha?formato=P&fonte=10
    // ─────────────────────────────────────────────────────────────────────────

    public function charsLinha()
    {
        $formato = $this->request->getGet('formato') ?? 'P';
        $fonte   = (int) ($this->request->getGet('fonte') ?? 10);
        $chars   = $this->relatorios->calcCharsPorLinha($formato, $fonte);

        echo json_encode(['erro' => false, 'chars_por_linha' => $chars]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DELETE
    // ─────────────────────────────────────────────────────────────────────────

    public function delete(int $id)
    {
        $ret = [];
        try {
            // ON DELETE CASCADE cuida das filhas automaticamente
            $this->relatorios->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Relatório excluído com sucesso!');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
        echo json_encode($ret);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ATIVAR / INATIVAR
    // ─────────────────────────────────────────────────────────────────────────

    public function ativinativ(int $id, int $tipo)
    {
        $ret = [];
        try {
            $this->relatorios->update($id, ['rel_ativo' => $tipo === 1 ? 1 : 0]);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Relatório alterado com sucesso!');
            $ret['msg']  = 'Relatório alterado com sucesso!';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        }
        echo json_encode($ret);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STORE
    // ─────────────────────────────────────────────────────────────────────────

    public function store()
    {
        $ret     = ['erro' => false];
        $postado = $this->request->getPost();
        // debug($postado);

        $this->relatorios->transBegin();

        try {
            // ── 1. Grava cabeçalho (calcula chars_por_linha internamente) ────
            $camposHeader = [
                'rel_id',
                'rel_nome',
                'mod_id',
                'tel_id',
                'rel_titulo',
                'rel_tabela_base',
                'rel_formato',
                'rel_tamanho_fonte',
                'rel_totalizar_registros',
            ];
            $dados = array_intersect_key($postado, array_flip($camposHeader));
            // debug($dados);
            $rel_id = $this->relatorios->salvarRelatorio($dados);
            // debug($rel_id);
            if (!$rel_id) {
                throw new \RuntimeException(implode('<br>', $this->relatorios->errors()));
            }

            // ── 2. Sincroniza filtros ────────────────────────────────────────
            $filtros = $this->_extrairFiltrosPost($postado);
            // debug($filtros);
            $this->filtros->sincronizarFiltros($rel_id, $filtros);

            // ── 3. Sincroniza colunas ────────────────────────────────────────
            $colunas = $this->_extrairColunasPost($postado);
            // debug($colunas);
            $this->colunas->sincronizarColunas($rel_id, $colunas);

            // ── 4. Reconstrói JOINs a partir das colunas gravadas ────────────
            $this->_sincronizarJoins($rel_id, $postado['rel_tabela_base']);

            // ── 5. Gera e salva o SQL ────────────────────────────────────────
            $this->relatorios->gerarSQL($rel_id);

            // ── 6. Sincroniza permissões por perfil ─────────────────────────
            $this->common->deleteReg('default', 'cfg_rel_permissao', 'rel_id = ' . $rel_id);

            if (!empty($postado['prf_id'])) {
                $prfIds = is_array($postado['prf_id'][0] ?? null)
                    ? array_merge(...$postado['prf_id'])
                    : $postado['prf_id'];

                foreach ($prfIds as $prf) {
                    $this->common->insertReg('default', 'cfg_rel_permissao', [
                        'rel_id'         => $rel_id,
                        'prf_id'         => $prf,
                        'rlp_atualizado' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->relatorios->transCommit();

            session()->setFlashdata('msg', 'Relatório gravado com sucesso!');
            $ret['url'] = site_url($this->data['controler']);
        } catch (\Throwable $e) {
            $this->relatorios->transRollback();
            $ret = [
                'erro' => true,
                'msg'  => $e->getMessage() ?: 'Erro ao salvar o Relatório.',
            ];
        }

        echo json_encode($ret);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna as opções do select de campo de filtro para a tabela informada.
     */
    private function _opcoesFiltroPorTabela(string $tabela): array
    {
        if (empty($tabela)) return [];

        $campos = $this->dicionario->getCampos($tabela);

        // Verifica se é VIEW — se for, todos os campos não-inteiros são filtro
        $dbGrSche = $this->dicionario->getDbGroupAndSchema($tabela);
        $dbInfo   = db_connect($dbGrSche['dbGroup']);
        $chkView  = $dbInfo->table('information_schema.TABLES')
            ->select('TABLE_TYPE')
            ->where('TABLE_NAME', $tabela)
            ->where('TABLE_SCHEMA', $dbGrSche['schema'])
            ->get();
        $rowView = $chkView ? $chkView->getFirstRow() : null;
        $isView  = ($rowView && $rowView->TABLE_TYPE === 'VIEW');

        $ret = [];
        foreach ($campos as $col) {
            if (str_ends_with($col['COLUMN_NAME'], '_excluido')) continue;

            $isDate = in_array(strtolower($col['DATA_TYPE']), ['date', 'datetime', 'timestamp']);
            $isFK   = $col['COLUMN_KEY'] !== '';
            $isInt  = in_array(strtolower($col['DATA_TYPE']), ['int', 'tinyint', 'smallint', 'mediumint', 'bigint']);

            // View: todos os campos não-inteiros | Tabela: apenas FK e date
            if (($isView && !$isInt) || $isFK || $isDate) {
                $tipo = $isDate ? 'DATE' : 'FK';
                $chave = $col['COLUMN_NAME'] . '|' . $tabela . '|' . $tipo;
                $ret[$chave] = $col['NOME_COMPLETO'];
            }
        }

        return $ret;
    }

    /**
     * Monta o array de campos de UMA linha da aba Filtros.
     * Espelho do padrão _linhaEtiqueta() da CfgEtiqueta.
     */
    private function _linhaFiltro(array $fields): array
    {
        return [
            $fields['rfi_id'],
            $fields['rfi_campo'],       // select dependente da tabela base
            $fields['rfi_tipo_filtro'], // oculto — preenchido ao selecionar campo
            $fields['rfi_tabela'],      // oculto — preenchido ao selecionar campo
            $fields['rfi_campo_pai'],   // *claude* novo: de qual outro filtro este depende (cascata) — opções montadas via JS (my_relatorio.js) a partir dos rfi_campo já escolhidos nas outras linhas
            $fields['rfi_label'],
            $fields['rfi_obrigatorio'],
            $fields['rfi_ordem'],       // oculto
            $fields['bt_add'],
            $fields['bt_del'],
        ];
    }

    /**
     * Monta o array de campos de UMA linha da aba Colunas.
     */
    private function _linhaColunas(array $fields): array
    {
        return [
            $fields['rco_id'],
            $fields['rco_campo'],           // select dependente da tabela base
            $fields['rco_tabela'],          // oculto — preenchido ao selecionar campo
            $fields['rco_tamanho'],         // oculto — preenchido ao selecionar campo
            $fields['rco_label'],
            $fields['rco_alias'],
            $fields['rco_largura'],         // largura editável
            $fields['rco_comportamento'],   // cortar / quebrar / linha inteira
            $fields['rco_alinhamento'],
            $fields['rco_tipo_dado'],       // oculto — preenchido ao selecionar campo
            $fields['rco_totalizar'],       // habilitado via JS quando tipo numérico
            $fields['rco_ordem'],           // oculto
            $fields['bt_add'],
            $fields['bt_del'],
        ];
    }

    /**
     * Extrai do POST os filtros como array para sincronizarFiltros().
     * O front envia arrays paralelos indexados: rfi_campo[0], rfi_campo[1]...
     */
    private function _extrairFiltrosPost(array $postado): array
    {
        $filtros = [];

        $campos       = $postado['rfi_campo']       ?? [];
        $tabelas      = $postado['rfi_tabela']       ?? [];
        $tipos        = $postado['rfi_tipo_filtro']  ?? [];
        $labels       = $postado['rfi_label']        ?? [];
        $obrigatorios = $postado['rfi_obrigatorio']  ?? [];
        // *claude* array paralelo do novo select "depende de"
        $camposPai    = $postado['rfi_campo_pai']    ?? [];

        foreach ($campos as $i => $campo) {
            if (empty($campo)) {
                continue;
            }

            $partes = explode('|', $campo);

            $filtros[] = [
                'rfi_campo'       => $partes[0] ?? $campo,
                'rfi_tabela'      => !empty($tabelas[$i]) ? $tabelas[$i] : ($partes[1] ?? ''),
                'rfi_tipo_filtro' => !empty($tipos[$i])   ? $tipos[$i]   : ($partes[2] ?? 'FK'),
                // *claude* nome puro da coluna (não é composto igual rfi_campo) — vazio = sem cascata
                'rfi_campo_pai'   => !empty($camposPai[$i]) ? $camposPai[$i] : null,
                'rfi_label'       => $labels[$i]        ?? '',
                'rfi_obrigatorio' => $obrigatorios[$i]  ?? 0,
            ];
        }

        // *claude* valida as dependências antes de persistir (sem custo de DB — só cruza o
        // array já montado, na mesma ordem de exibição/rfi_ordem). Mesma regra do
        // atualizaDependeDe() em my_relatorio.js: um filtro só pode depender de outro que
        // apareça ANTES dele na lista — por isso o array de campos válidos é acumulado
        // conforme percorre $filtros, nunca contendo a própria linha nem as seguintes.
        // Filtro DATA não depende de nada (usa daterange, não select) nem serve de "pai".
        $camposAteAqui = [];
        foreach ($filtros as $i => &$f) {
            $tipo = $f['rfi_tipo_filtro'] ?? 'FK';

            if ($tipo === 'DATE') {
                $f['rfi_campo_pai'] = null;
            } elseif (!empty($f['rfi_campo_pai']) && !in_array($f['rfi_campo_pai'], $camposAteAqui, true)) {
                throw new \RuntimeException(
                    "O filtro \"{$f['rfi_label']}\" só pode depender de um filtro que apareça ANTES dele na lista."
                );
            }

            if ($tipo !== 'DATE' && !empty($f['rfi_campo'])) {
                $camposAteAqui[] = $f['rfi_campo'];
            }
        }
        unset($f);

        return $filtros;
    }

    /**
     * Extrai do POST as colunas como array para sincronizarColunas().
     */
    private function _extrairColunasPost(array $postado): array
    {
        $colunas = [];

        $campos        = $postado['rco_campo']         ?? [];
        $tabelas       = $postado['rco_tabela']        ?? [];
        $labels        = $postado['rco_label']         ?? [];
        $aliases       = $postado['rco_alias']         ?? [];
        $tamanhos      = $postado['rco_tamanho']       ?? [];
        $alinhams      = $postado['rco_alinhamento']   ?? [];
        $totalizar     = $postado['rco_totalizar']     ?? [];
        $tiposDado     = $postado['rco_tipo_dado']     ?? [];
        $larguras      = $postado['rco_largura']       ?? [];
        $comportaments = $postado['rco_comportamento'] ?? [];

        foreach ($campos as $i => $campo) {
            if (empty($campo)) {
                continue;
            }

            $partes = explode('|', $campo);

            $colunas[] = [
                'rco_campo'         => $partes[1] ?? $campo,
                'rco_tabela'        => !empty($tabelas[$i])   ? $tabelas[$i]   : ($partes[0] ?? ''),
                'rco_alias'         => $aliases[$i]   ?? null,
                'rco_label'         => $labels[$i]    ?? '',
                'rco_tamanho'       => !empty($tamanhos[$i])  ? (int) $tamanhos[$i]  : (int) ($partes[2] ?? 0),
                'rco_alinhamento'   => $alinhams[$i]  ?? 'E',
                'rco_totalizar'     => !empty($totalizar[$i]) ? 1 : 0,
                'rco_tipo_dado'     => !empty($tiposDado[$i]) ? $tiposDado[$i] : ($partes[3] ?? ''),
                'rco_largura'       => (int) ($larguras[$i] ?? 0),
                'rco_comportamento' => $comportaments[$i] ?? 'cortar',
            ];
        }

        return $colunas;
    }

    /**
     * Reconstrói cfg_rel_joins a partir das tabelas distintas em cfg_rel_colunas
     * que diferem da tabela base. Usa getRelacionamentos() para obter o ON.
     */
    private function _sincronizarJoins(int $rel_id, string $tabelaBase): void
    {
        $colunasSalvas = $this->colunas->getColunas($rel_id);

        $tabelasDistintas = [];
        foreach ($colunasSalvas as $col) {
            if (!empty($col->rco_tabela) && $col->rco_tabela !== $tabelaBase) {
                $tabelasDistintas[$col->rco_tabela] = true;
            }
        }

        if (empty($tabelasDistintas)) {
            $this->joins->sincronizarJoins($rel_id, []);
            return;
        }

        $rels  = $this->dicionario->getRelacionamentos($tabelaBase);
        // debug($rels);
        $relMap = [];
        foreach ($rels['relacionamentos'] as $r) {
            if (!empty($r['REFERENCED_TABLE_NAME'])) {
                $tabRef = $r['REFERENCED_TABLE_NAME'];
                if (!isset($relMap[$tabRef])) {
                    $relMap[$tabRef] = $r['TABLE_NAME'] . '.' . $r['COLUMN_NAME']
                        . ' = ' . $r['REFERENCED_TABLE_NAME'] . '.' . $r['REFERENCED_COLUMN_NAME'];
                }
            }
        }

        $joins = [];
        foreach (array_keys($tabelasDistintas) as $tab) {
            if (!isset($relMap[$tab])) {
                continue;
            }
            $joins[] = [
                'rjo_tipo_join'   => 'LEFT',
                'rjo_tabela_join' => $tab,
                'rjo_alias_join'  => null,
                'rjo_condicao_on' => $relMap[$tab],
            ];
        }

        $this->joins->sincronizarJoins($rel_id, $joins);
    }

    /**
     * Monta o bloco <script> injetado na view, ativando os botões
     * das tabelas repetíveis e inicializando o contador de largura.
     */
    private function _scriptTela(int $charsLinha = 0): string
    {
        $urlCharsLinha = base_url('CfgRelatorio/charsLinha');
        $urlCamposFil  = base_url('buscas/busca_campos_filtro_rel');
        $urlPreview    = base_url('CfgRelatorio/previewRelatorio');

        return "<script>
            var charsLinha    = {$charsLinha};
            var urlCharsLinha = '{$urlCharsLinha}';
            var urlCamposFil  = '{$urlCamposFil}';
            var urlPreview    = '{$urlPreview}';
        </script>";
    }
}
