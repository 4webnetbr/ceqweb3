<?php

namespace App\Entities\Config;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgRelColunas extends Entity
{
    protected $attributes = [
        'rco_id'              => null,
        'rel_id'              => null,
        'rco_tabela'          => null,
        'rco_campo'           => null,
        'rco_alias'           => null,
        'rco_label'           => null,
        'rco_tamanho'         => 0,
        'rco_alinhamento'     => 'E',
        'rco_totalizar'       => 0,
        'rco_tipo_dado'       => '',
        'rco_largura'         => 0,
        'rco_comportamento'   => 'cortar',
        'rco_ordem'           => 0,
    ];

    public static function defCampos($dados = false, bool $show = false, int $pos = 0): array
    {
        $ret = [];

        // Ocultos
        $ret['rco_id'] = (new MyCampo('cfg_rel_colunas', 'rco_id'))
            ->setValor($dados['rco_id'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rco_tabela'] = (new MyCampo('cfg_rel_colunas', 'rco_tabela'))
            ->setValor($dados['rco_tabela'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rco_tamanho'] = (new MyCampo('cfg_rel_colunas', 'rco_tamanho'))
            ->setValor($dados['rco_tamanho'] ?? 0)
            ->setOrdem($pos)
            ->crOculto();

        $ret['rco_ordem'] = (new MyCampo('cfg_rel_colunas', 'rco_ordem'))
            ->setValor($dados['rco_ordem'] ?? $pos)
            ->setOrdem($pos)
            ->crOculto();

        $ret['rco_tipo_dado'] = (new MyCampo('cfg_rel_colunas', 'rco_tipo_dado'))
            ->setValor($dados['rco_tipo_dado'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        $ret['rco_alias'] = (new MyCampo('cfg_rel_colunas', 'rco_alias'))
            ->setValor($dados['rco_alias'] ?? '')
            ->setOrdem($pos)
            ->crOculto();

        // Select de campo — dependente da tabela base
        $opcaoCampo = [];
        if (!empty($dados['rco_campo'])) {
            $selecionado = ($dados['rco_tabela'] ?? '') . '|' . $dados['rco_campo'] . '|' . ($dados['rco_tamanho'] ?? 0) . '|' . ($dados['rco_tipo_dado'] ?? '');
            $opcaoCampo[$selecionado] = '[' . ($dados['rco_tabela'] ?? '') . '] ' . ucwords(str_replace('_', ' ', $dados['rco_campo']));
        }

        $ret['rco_campo'] = (new MyCampo('cfg_rel_colunas', 'rco_campo'))
            ->setValor($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setSelecionado($opcaoCampo ? array_key_first($opcaoCampo) : '')
            ->setOpcoes($opcaoCampo)
            ->setObrigatorio()
            ->setOrdem($pos)
            ->setDispForm('col-9 float-start')
            ->setLargura(80)
            ->setLeitura($show)
            ->setPai('rel_tabela_base')
            ->setUrlbusca(base_url('buscas/busca_campos_colunas_rel'))
            ->crDepende();

        $ret['rco_label'] = (new MyCampo('cfg_rel_colunas', 'rco_label'))
            ->setValor($dados['rco_label'] ?? '')
            ->setObrigatorio()
            ->setMinLength(3)
            ->setOrdem($pos)
            ->setDispForm('col-3 float-start')
            ->setLargura(30)
            ->setLeitura($show)
            ->crInput();

        $ret['rco_alinhamento'] = (new MyCampo('cfg_rel_colunas', 'rco_alinhamento'))
            ->setValor($dados['rco_alinhamento'] ?? 'E')
            ->setSelecionado($dados['rco_alinhamento'] ?? 'E')
            ->setOpcoes(['E' => ' Esquerda', 'C' => ' Centro', 'D' => ' Direita'])
            ->setOrdem($pos)
            ->setDispForm('col-3 float-start')
            ->setLargura(25)
            ->setLeitura($show)
            ->crSelect();

        $ret['rco_totalizar'] = (new MyCampo('cfg_rel_colunas', 'rco_totalizar'))
            ->setValor($dados['rco_totalizar'] ?? 0)
            ->setSelecionado($dados['rco_totalizar'] ?? 0)
            ->setOpcoes([0 => 'Não', 1 => 'Sim'])
            ->setOrdem($pos)
            ->setDispForm('col-2 float-start')
            ->setLeitura($show)
            ->cr2opcoes();

        $ret['rco_largura'] = (new MyCampo('cfg_rel_colunas', 'rco_largura'))
            ->setValor($dados['rco_largura'] ?? $dados['rco_tamanho'] ?? 0)
            ->setOrdem($pos)
            ->setDispForm('col-2 float-start')
            ->setLargura(10)
            ->setLeitura($show)
            ->crInput();

        $ret['rco_comportamento'] = (new MyCampo('cfg_rel_colunas', 'rco_comportamento'))
            ->setValor($dados['rco_comportamento'] ?? 'cortar')
            ->setSelecionado($dados['rco_comportamento'] ?? 'cortar')
            ->setOpcoes(['cortar' => 'Cortar', 'quebrar' => 'Quebrar', 'linha' => 'Linha inteira'])
            ->setOrdem($pos)
            ->setDispForm('col-2 float-start')
            ->setLeitura($show)
            ->crSelect();

        // Botões
        $atrib = ['data-index' => $pos];

        $add           = new MyCampo();
        $add->attrdata = $atrib;
        $add->tipo     = 'button';
        $add->dispForm = '1col';
        $add->nome     = "bt_add_col[{$pos}]";
        $add->id       = "bt_add_col[{$pos}]";
        $add->i_cone   = "<i class='fas fa-plus'></i>";
        $add->place    = 'Adicionar Coluna';
        $add->classep  = 'btn-outline-success btn-sm bt-repete';
        $add->funcChan = "addCampo('" . base_url('CfgRelatorio/addColuna/') . "','colunas',this)";
        $ret['bt_add'] = $add->crBotao();

        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->tipo     = 'button';
        $del->dispForm = '1col';
        $del->nome     = "bt_del_col[{$pos}]";
        $del->id       = "bt_del_col[{$pos}]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = 'btn-outline-danger btn-sm bt-exclui';
        $del->funcChan = "exclui_campo('colunas',this)";
        $del->place    = 'Excluir Coluna';
        $ret['bt_del'] = $del->crBotao();

        return $ret;
    }

    public function getAlinhamentoLabel(): string
    {
        return match ($this->rco_alinhamento) {
            'C'     => 'Centro',
            'D'     => 'Direita',
            default => 'Esquerda',
        };
    }

    public function toSelectFragment(): string
    {
        $campo = $this->rco_alias ?: $this->rco_campo;
        return "{$this->rco_tabela}.{$campo} AS '{$this->rco_label}'";
    }
}
