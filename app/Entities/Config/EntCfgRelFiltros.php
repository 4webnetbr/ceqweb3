<?php

namespace App\Entities\Config;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgRelFiltros extends Entity
{
    protected $attributes = [
        'rfi_id'          => null,
        'rel_id'          => null,
        'rfi_tabela'      => null,
        'rfi_campo'       => null,
        // *claude* nome do rfi_campo (mesmo relatório) do qual este filtro depende, ou null
        'rfi_campo_pai'   => null,
        'rfi_tipo_filtro' => 'FK',
        'rfi_label'       => null,
        'rfi_obrigatorio' => 0,
        'rfi_ordem'       => 0,
    ];

    public static function defCampos($dados = false, bool $show = false, int $pos = 0, array $opcoesCampo = []): array
    {
        $ret = [];

        $ret['rfi_id'] = (new MyCampo('cfg_rel_filtros', 'rfi_id'))
            ->setValor($dados['rfi_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rfi_tipo_filtro'] = (new MyCampo('cfg_rel_filtros', 'rfi_tipo_filtro'))
            ->setValor($dados['rfi_tipo_filtro'] ?? 'FK')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rfi_tabela'] = (new MyCampo('cfg_rel_filtros', 'rfi_tabela'))
            ->setValor($dados['rfi_tabela'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rfi_ordem'] = (new MyCampo('cfg_rel_filtros', 'rfi_ordem'))
            ->setValor($dados['rfi_ordem'] ?? $pos)
            ->setOrdem($pos)
            ->crOculto();

        $selecionadoVal = '';
        if (!empty($dados['rfi_campo'])) {
            $selecionadoVal = $dados['rfi_campo'] . '|' . ($dados['rfi_tabela'] ?? '') . '|' . ($dados['rfi_tipo_filtro'] ?? 'FK');
            if (empty($opcoesCampo)) {
                $opcoesCampo[$selecionadoVal] = ucwords(str_replace('_', ' ', $dados['rfi_campo']));
            }
        }

        $ret['rfi_campo'] = (new MyCampo('cfg_rel_filtros', 'rfi_campo'))
            ->setValor($selecionadoVal)
            ->setSelecionado($selecionadoVal)
            ->setOpcoes($opcoesCampo)
            ->setObrigatorio()
            ->setOrdem($pos)
            // *claude* col-4 -> col-3 pra abrir espaço pro novo campo "depende de" na mesma linha
            ->setDispForm('col-3')
            ->setLargura(30)
            ->setLeitura($show)
            ->crSelect();

        // *claude* select "depende de": só semeia aqui a opção já salva (se houver), igual o
        // padrão usado acima pra rfi_campo. A lista real de candidatos (os rfi_campo já
        // escolhidos nas OUTRAS linhas da grade) é montada em tempo real por
        // atualizaDependeDe() em my_relatorio.js, porque isso muda conforme o usuário
        // adiciona/remove/edita linhas — não dá pra saber isso só no render do servidor.
        $campoPaiVal = $dados['rfi_campo_pai'] ?? '';
        $opcoesPai   = ['' => '(Nenhum)'];
        if (!empty($campoPaiVal)) {
            $opcoesPai[$campoPaiVal] = ucwords(str_replace('_', ' ', $campoPaiVal));
        }

        $ret['rfi_campo_pai'] = (new MyCampo('cfg_rel_filtros', 'rfi_campo_pai'))
            // *claude* label explícito — a coluna nova não tem COLUMN_COMMENT no banco
            ->setLabel('Depende de')
            ->setValor($campoPaiVal)
            ->setSelecionado($campoPaiVal)
            ->setOpcoes($opcoesPai)
            ->setOrdem($pos)
            ->setLargura(30)
            ->setDispForm('col-3')
            ->setLeitura($show)
            ->crSelect();

        $ret['rfi_label'] = (new MyCampo('cfg_rel_filtros', 'rfi_label'))
            ->setValor($dados['rfi_label'] ?? '')
            ->setObrigatorio()
            ->setMinLength(3)
            ->setOrdem($pos)
            // *claude* col-4 -> col-3 pra abrir espaço pro novo campo "depende de" na mesma linha
            ->setDispForm('col-3')
            ->setLargura(30)
            ->setLeitura($show)
            ->crInput();

        $ret['rfi_obrigatorio'] = (new MyCampo('cfg_rel_filtros', 'rfi_obrigatorio'))
            ->setValor($dados['rfi_obrigatorio'] ?? 0)
            ->setSelecionado($dados['rfi_obrigatorio'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setOrdem($pos)
            ->setDispForm('col-2')
            ->setLeitura($show)
            ->cr2opcoes();

        $atrib = ['data-index' => $pos];

        $add           = new MyCampo();
        $add->attrdata = $atrib;
        $add->tipo     = 'button';
        $add->dispForm = '1col';
        $add->nome     = "bt_add_fil[{$pos}]";
        $add->id       = "bt_add_fil[{$pos}]";
        $add->i_cone   = "<i class='fas fa-plus'></i>";
        $add->place    = 'Adicionar Filtro';
        $add->classep  = 'btn-outline-success btn-sm bt-repete';
        $add->funcChan = "addCampo('" . base_url('CfgRelatorio/addFiltro/') . "','filtros',this)";
        $ret['bt_add'] = $add->crBotao();

        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->tipo     = 'button';
        $del->dispForm = '1col';
        $del->nome     = "bt_del_fil[{$pos}]";
        $del->id       = "bt_del_fil[{$pos}]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = 'btn-outline-danger btn-sm bt-exclui';
        $del->funcChan = "exclui_campo('filtros',this)";
        $del->place    = 'Excluir Filtro';
        $ret['bt_del'] = $del->crBotao();

        return $ret;
    }

    public function getTipoLabel(): string
    {
        return $this->rfi_tipo_filtro === 'DATE' ? 'Data' : 'Chave Estrangeira';
    }

    public function isData(): bool
    {
        return $this->rfi_tipo_filtro === 'DATE';
    }

    public function getPlaceholders(): array
    {
        if ($this->isData()) {
            return [":{$this->rfi_campo}_de", ":{$this->rfi_campo}_ate"];
        }
        return [":{$this->rfi_campo}"];
    }
}
