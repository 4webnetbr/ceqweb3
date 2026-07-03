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
            ->setDispForm('col-4')
            ->setLargura(30)
            ->setLeitura($show)
            ->crSelect();

        $ret['rfi_label'] = (new MyCampo('cfg_rel_filtros', 'rfi_label'))
            ->setValor($dados['rfi_label'] ?? '')
            ->setObrigatorio()
            ->setMinLength(3)
            ->setOrdem($pos)
            ->setDispForm('col-4')
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
