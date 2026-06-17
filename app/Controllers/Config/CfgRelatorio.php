<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgRelatorios;
use App\Entities\Config\EntCfgRelFiltros;
use App\Entities\Config\EntCfgRelColunas;
use App\Models\CommonModel;
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
        $erel = new EntCfgRelatorios();

        // Aba Filtros — começa com uma linha vazia
        $fieldsFiltro = $erel->defCamposFiltro();
        $campos_filtros[0] = $this->_linhaFiltro($fieldsFiltro);

        // Aba Colunas — começa com uma linha vazia
        $fieldsColunas = $erel->defCamposColunas();
        $campos_colunas[0] = $this->_linhaColunas($fieldsColunas);

        $this->data['secoes'] = ['Dados Gerais', 'Filtros', 'Colunas'];
        $this->data['displ']  = [null, 'tabela', 'tabela'];

        $this->data['campos'] = [
            // Aba 0 — Dados Gerais
            [
                $erel->campos['rel_id'],
                $erel->campos['mod_id'],
                $erel->campos['tel_id'],
                $erel->campos['rel_titulo'],
                $erel->campos['rel_tabela_base'],
                $erel->campos['rel_formato'],
                $erel->campos['rel_tamanho_fonte'],
            ],
            // Aba 1 — Filtros
            $campos_filtros,
            // Aba 2 — Colunas
            $campos_colunas,
        ];

        $this->data['destino'] = 'store';
        $this->data['script']  = $this->_scriptTela();

        echo view('vw_edicao', $this->data);
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

        $erel = new EntCfgRelatorios((array) $dados, $show);

        // ── Aba Filtros ──────────────────────────────────────────────────────
        $lst_filtros = $this->filtros->getFiltros($id);

        if (count($lst_filtros) > 0) {
            foreach ($lst_filtros as $pos => $f) {
                $fields          = $erel->defCamposFiltro((array) $f, $show, $pos);
                $campos_filtros[$pos] = $this->_linhaFiltro($fields);
            }
        } else {
            $fields          = $erel->defCamposFiltro();
            $campos_filtros[0] = $this->_linhaFiltro($fields);
        }

        // ── Aba Colunas ──────────────────────────────────────────────────────
        $lst_colunas = $this->colunas->getColunas($id);

        if (count($lst_colunas) > 0) {
            foreach ($lst_colunas as $pos => $c) {
                $fields           = $erel->defCamposColunas((array) $c, $show, $pos);
                $campos_colunas[$pos] = $this->_linhaColunas($fields);
            }
        } else {
            $fields           = $erel->defCamposColunas();
            $campos_colunas[0] = $this->_linhaColunas($fields);
        }

        $this->data['secoes'] = ['Dados Gerais', 'Filtros', 'Colunas'];
        $this->data['displ']  = [null, 'tabela', 'tabela'];

        $this->data['campos'] = [
            [
                $erel->campos['rel_id'],
                $erel->campos['mod_id'],
                $erel->campos['tel_id'],
                $erel->campos['rel_titulo'],
                $erel->campos['rel_tabela_base'],
                $erel->campos['rel_formato'],
                $erel->campos['rel_tamanho_fonte'],
            ],
            $campos_filtros,
            $campos_colunas,
        ];

        $this->data['destino']         = 'store';
        $this->data['rel_chars_linha'] = $dados->rel_chars_por_linha;
        $this->data['log']             = buscaLog('cfg_relatorios', $id);
        $this->data['script']          = $this->_scriptTela($dados->rel_chars_por_linha);

        echo view('vw_edicao', $this->data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJAX — addFiltro
    //  Chamado pelo botão (+) da aba Filtros para adicionar nova linha vazia
    //  URL: CfgRelatorio/addFiltro/{pos}
    // ─────────────────────────────────────────────────────────────────────────

    public function addFiltro(int $pos)
    {
        $erel   = new EntCfgRelatorios();
        $fields = $erel->defCamposFiltro(false, false, $pos);
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

        $db     = db_connect('default');
        $campos = $db->getFieldData($tabela);
        $ret    = [];

        foreach ($campos as $col) {
            $isFk   = str_ends_with($col->name, '_id');
            $isDate = in_array(strtoupper($col->type), ['DATE', 'DATETIME', 'TIMESTAMP']);

            if ($isFk || $isDate) {
                $ret[] = [
                    'id'   => $col->name . '|' . $tabela . '|' . ($isDate ? 'DATE' : 'FK'),
                    'text' => ucwords(str_replace('_', ' ', $col->name)),
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

        $relacionamentos = getRelacionamentos($tabela);
        $db  = db_connect('default');
        $ret = [];

        // Inclui a própria tabela base
        array_unshift($relacionamentos, ['tabela' => $tabela]);

        foreach ($relacionamentos as $rel) {
            $campos = $db->getFieldData($rel['tabela']);
            foreach ($campos as $col) {
                $ret[] = [
                    // id carrega tabela|campo|tamanho — decodificado no JS ao selecionar
                    'id'   => $rel['tabela'] . '|' . $col->name . '|' . ($col->max_length ?? 0),
                    'text' => '[' . $rel['tabela'] . '] ' . ucwords(str_replace('_', ' ', $col->name)),
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

        $this->relatorios->transBegin();

        try {
            // ── 1. Grava cabeçalho (calcula chars_por_linha internamente) ────
            $erel   = new EntCfgRelatorios($postado);
            $rel_id = $this->relatorios->salvarRelatorio($erel->toRawArray(true));

            if (!$rel_id) {
                throw new \RuntimeException(implode('<br>', $this->relatorios->errors()));
            }

            // ── 2. Sincroniza filtros ────────────────────────────────────────
            $filtros = $this->_extrairFiltrosPost($postado);
            $this->filtros->sincronizarFiltros($rel_id, $filtros);

            // ── 3. Sincroniza colunas ────────────────────────────────────────
            $colunas = $this->_extrairColunasPost($postado);
            $this->colunas->sincronizarColunas($rel_id, $colunas);

            // ── 4. Reconstrói JOINs a partir das colunas gravadas ────────────
            $this->_sincronizarJoins($rel_id, $postado['rel_tabela_base']);

            // ── 5. Gera e salva o SQL ────────────────────────────────────────
            $this->relatorios->gerarSQL($rel_id);

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
     * Monta o array de campos de UMA linha da aba Filtros.
     * Espelho do padrão _linhaEtiqueta() da CfgEtiqueta.
     */
    private function _linhaFiltro(array $fields): array
    {
        return [
            $fields['rfi_id'],
            $fields['rel_id'],
            $fields['rfi_campo'],       // select dependente da tabela base
            $fields['rfi_tipo_filtro'], // oculto — preenchido ao selecionar campo
            $fields['rfi_tabela'],      // oculto — preenchido ao selecionar campo
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
            $fields['rel_id'],
            $fields['rco_campo'],           // select dependente da tabela base
            $fields['rco_tabela'],          // oculto — preenchido ao selecionar campo
            $fields['rco_tamanho'],         // oculto — preenchido ao selecionar campo
            $fields['rco_tamanho_efetivo'], // oculto — calculado ao marcar quebra
            $fields['rco_label'],
            $fields['rco_alias'],
            $fields['rco_alinhamento'],
            $fields['rco_quebra_linha'],    // habilitado via JS quando tamanho > 60
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

        foreach ($campos as $i => $campo) {
            if (empty($campo)) {
                continue;
            }
            $filtros[] = [
                'rfi_campo'       => $campo,
                'rfi_tabela'      => $tabelas[$i]      ?? '',
                'rfi_tipo_filtro' => $tipos[$i]        ?? 'FK',
                'rfi_label'       => $labels[$i]       ?? '',
                'rfi_obrigatorio' => $obrigatorios[$i] ?? 0,
            ];
        }

        return $filtros;
    }

    /**
     * Extrai do POST as colunas como array para sincronizarColunas().
     */
    private function _extrairColunasPost(array $postado): array
    {
        $colunas = [];

        $campos   = $postado['rco_campo']       ?? [];
        $tabelas  = $postado['rco_tabela']       ?? [];
        $labels   = $postado['rco_label']        ?? [];
        $aliases  = $postado['rco_alias']        ?? [];
        $tamanhos = $postado['rco_tamanho']      ?? [];
        $quebras  = $postado['rco_quebra_linha'] ?? [];
        $alinhams = $postado['rco_alinhamento']  ?? [];

        foreach ($campos as $i => $campo) {
            if (empty($campo)) {
                continue;
            }
            $colunas[] = [
                'rco_campo'       => $campo,
                'rco_tabela'      => $tabelas[$i]  ?? '',
                'rco_alias'       => $aliases[$i]  ?? null,
                'rco_label'       => $labels[$i]   ?? '',
                'rco_tamanho'     => (int) ($tamanhos[$i] ?? 0),
                'rco_quebra_linha' => !empty($quebras[$i]) ? 1 : 0,
                'rco_alinhamento' => $alinhams[$i] ?? 'E',
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
        $db = db_connect('default');

        $tabelas = $db->table('cfg_rel_colunas')
            ->select('DISTINCT rco_tabela')
            ->where('rel_id', $rel_id)
            ->where('rco_tabela !=', $tabelaBase)
            ->get()->getResultArray();

        if (empty($tabelas)) {
            $this->joins->sincronizarJoins($rel_id, []);
            return;
        }

        $relacionamentos = getRelacionamentos($tabelaBase);
        $relMap  = array_column($relacionamentos, null, 'tabela');
        $joins   = [];

        foreach ($tabelas as $row) {
            $tab = $row['rco_tabela'];
            if (!isset($relMap[$tab])) {
                continue;
            }
            $joins[] = [
                'rjo_tipo_join'   => 'LEFT',
                'rjo_tabela_join' => $tab,
                'rjo_alias_join'  => null,
                'rjo_condicao_on' => $relMap[$tab]['condicao_on'],
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
        $urlAddFiltro  = base_url('CfgRelatorio/addFiltro/');
        $urlAddColuna  = base_url('CfgRelatorio/addColuna/');
        $urlCamposFil  = base_url('CfgRelatorio/camposFiltro');
        $urlCamposCol  = base_url('CfgRelatorio/camposColunas');
        $urlCharsLinha = base_url('CfgRelatorio/charsLinha');

        return <<<JS
        <script>
            // Ativa botões + e lixeira nas duas abas repetíveis
            acerta_botoes_rep('filtros');
            acerta_botoes_rep('colunas');

            // Contador de largura disponível (recalculado via AJAX quando
            // o usuário altera formato ou tamanho de fonte)
            var charsLinha = {$charsLinha};

            // ── Recalcula chars ao mudar formato ou fonte ─────────────────
            $(document).on('change', '[name="rel_formato"], [name="rel_tamanho_fonte"]', function () {
                $.get('{$urlCharsLinha}', {
                    formato: $('[name="rel_formato"]').val(),
                    fonte:   $('[name="rel_tamanho_fonte"]').val()
                }, function (res) {
                    if (!res.erro) {
                        charsLinha = res.chars_por_linha;
                        verificaLargura();
                    }
                });
            });

            // ── Popula select de campo ao mudar a tabela base ─────────────
            $(document).on('change', '[name="rel_tabela_base"]', function () {
                var tabela = $(this).val();
                if (!tabela) return;

                // Filtros: busca campos _id e DATE
                $.get('{$urlCamposFil}', { tabela: tabela }, function (res) {
                    if (res.erro) return;
                    popularSelectCampo('rfi_campo', res.campos);
                });

                // Colunas: busca todos os campos via getRelacionamentos
                $.get('{$urlCamposCol}', { tabela: tabela }, function (res) {
                    if (res.erro) return;
                    popularSelectCampo('rco_campo', res.campos);
                });
            });

            // ── Ao selecionar um campo de coluna: preenche ocultos e
            //    verifica se habilita quebra de linha e alerta de estouro ──
            $(document).on('change', '[name^="rco_campo"]', function () {
                var partes  = $(this).val().split('|'); // tabela|campo|tamanho
                var tabela  = partes[0] ?? '';
                var tamanho = parseInt(partes[2] ?? 0);
                var linha   = $(this).closest('tr');

                linha.find('[name^="rco_tabela"]').val(tabela);
                linha.find('[name^="rco_tamanho"]').val(tamanho);

                // Habilita/desabilita quebra de linha
                var btnQuebra = linha.find('[name^="rco_quebra_linha"]');
                if (tamanho > 60) {
                    btnQuebra.prop('disabled', false);
                } else {
                    btnQuebra.prop('checked', false).prop('disabled', true);
                    linha.find('[name^="rco_tamanho_efetivo"]').val(tamanho);
                }

                verificaLargura();
            });

            // ── Ao marcar/desmarcar quebra: recalcula tamanho efetivo ─────
            $(document).on('change', '[name^="rco_quebra_linha"]', function () {
                var linha   = $(this).closest('tr');
                var tamanho = parseInt(linha.find('[name^="rco_tamanho"]').val() ?? 0);
                var efetivo = $(this).is(':checked') ? Math.ceil(tamanho / 2) : tamanho;
                linha.find('[name^="rco_tamanho_efetivo"]').val(efetivo);
                verificaLargura();
            });

            // ── Ao selecionar campo de filtro: preenche ocultos tipo e tabela
            $(document).on('change', '[name^="rfi_campo"]', function () {
                var partes = $(this).val().split('|'); // campo|tabela|tipo
                var linha  = $(this).closest('tr');
                linha.find('[name^="rfi_tabela"]').val(partes[1] ?? '');
                linha.find('[name^="rfi_tipo_filtro"]').val(partes[2] ?? 'FK');
            });

            // ── Verifica se a soma dos tamanhos efetivos ultrapassa o limite
            function verificaLargura() {
                if (charsLinha <= 0) return;
                var total = 0;
                $('[name^="rco_tamanho_efetivo"]').each(function () {
                    total += parseInt($(this).val() ?? 0);
                });
                if (total > charsLinha) {
                    alerta('Atenção: largura total das colunas (' + total
                        + ') ultrapassa o limite da linha (' + charsLinha + ' caracteres).');
                }
            }

            // ── Popula todos os selects de campo com o mesmo nome ─────────
            function popularSelectCampo(nome, opcoes) {
                $('[name^="' + nome + '"]').each(function () {
                    var sel = $(this);
                    var val = sel.val();
                    sel.empty().append('<option value="">Selecione...</option>');
                    $.each(opcoes, function (i, op) {
                        sel.append('<option value="' + op.id + '">' + op.text + '</option>');
                    });
                    sel.val(val); // preserva seleção existente (no edit)
                });
            }
        </script>
        JS;
    }
}
