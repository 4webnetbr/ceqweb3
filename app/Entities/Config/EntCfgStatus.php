<?php

namespace App\Entities\Config;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigModuloModel;

class EntCfgStatus extends Entity
{
    /**
     * Atributos mapeados diretamente da tabela / view
     */
    protected $attributes = [
        'stt_id'         => null,
        'stt_nome'       => null,
        'stt_cor'        => null,
        'mod_id'         => null,
        'tel_id'         => null,
        'stt_exclusao'   => 'S',
        'stt_edicao'     => 'S',
        'stt_disponivel' => 'S',
        'stt_ativo'      => 'A',
        'stt_ordem'      => null,
        'stt_excluido'   => null,
    ];

    /**
     * Datas tratadas automaticamente pelo CI4
     */
    protected $dates = [
        'stt_excluido',
    ];

    /**
     * Campos de formulário (MyCampo)
     */
    public array $campos = [];

    /**
     * Cache interno do relacionamento
     */
    protected ?EntCfgModulo $modulo = null;

    /**
     * Construtor
     */
    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /* =====================================================
     * RELACIONAMENTO
     * ===================================================== */

    /**
     * Getter mágico:
     * permite usar $status->modulo
     */
    public function getModulo(): ?EntCfgModulo
    {
        // Se não houver mod_id
        if (empty($this->attributes['mod_id'])) {
            return null;
        }

        // Se já carregou, retorna do cache
        if ($this->modulo instanceof EntCfgModulo) {
            return $this->modulo;
        }

        // Lazy loading do módulo
        $model = new ConfigModuloModel();
        $this->modulo = $model->find($this->attributes['mod_id']);

        return $this->modulo;
    }

    /**
     * Setter opcional (caso queira injetar o módulo manualmente)
     */
    public function setModulo(EntCfgModulo $modulo): self
    {
        $this->modulo = $modulo;
        $this->attributes['mod_id'] = $modulo->mod_id;
        return $this;
    }

    /* =====================================================
     * CAMPOS DE FORMULÁRIO
     * ===================================================== */

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        // ID
        $mid = new MyCampo('cfg_status', 'stt_id');
        $mid->valor = $dados['stt_id'] ?? '';
        $ret['stt_id'] = $mid->crOculto();

        // Nome
        $nome = new MyCampo('cfg_status', 'stt_nome');
        $nome->valor = $dados['stt_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->dispForm = 'col-12';
        $nome->leitura = $show;
        $ret['stt_nome'] = $nome->crInput();

        // Módulo (usa mod_id, mas relacionamento é via Entity)
        $modModel = new ConfigModuloModel();
        $mods = array_column($modModel->getModulo(), 'mod_nome', 'mod_id');

        $mod = new MyCampo('cfg_status', 'mod_id');
        $mod->valor = $dados['mod_id'] ?? '';
        $mod->selecionado = $mod->valor;
        $mod->opcoes = $mods;
        $mod->obrigatorio = true;
        $mod->dispForm = 'col-4';
        $mod->leitura = $show;
        $ret['mod_id'] = $mod->crSelect();

        // Tela
        $telModel = new ConfigTelaModel();
        $telas = array_column(
            $telModel->getTelaId($dados['tel_id'] ?? false),
            'tel_nome',
            'tel_id'
        );

        $tel = new MyCampo('cfg_status', 'tel_id');
        $tel->valor = $dados['tel_id'] ?? '';
        $tel->selecionado = $tel->valor;
        $tel->opcoes = $telas;
        $tel->urlbusca = base_url('buscas/busca_tela_modulo');
        $tel->pai = 'mod_id';
        $tel->obrigatorio = true;
        $tel->dispForm = 'col-4';
        $tel->leitura = $show;
        $ret['tel_id'] = $tel->crDepende();

        // Cor
        $cor = new MyCampo('cfg_status', 'stt_cor');
        $cor->valor = $dados['stt_cor'] ?? '';
        $cor->obrigatorio = true;
        $cor->dispForm = 'col-4';
        $cor->leitura = $show;
        $ret['stt_cor'] = $cor->crCorbst();

        $op = ['S' => 'Sim', 'N' => 'Não'];

        foreach (['stt_exclusao','stt_edicao','stt_disponivel'] as $campo) {
            $c = new MyCampo('cfg_status', $campo);
            $c->valor = $dados[$campo] ?? 'S';
            $c->selecionado = $c->valor;
            $c->opcoes = $op;
            $c->dispForm = 'col-4';
            $c->leitura = $show;
            $ret[$campo] = $c->cr2opcoes();
        }

        return $ret;
    }
}
