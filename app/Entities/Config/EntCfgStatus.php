<?php

namespace App\Entities\Config;

use App\Traits\HasTela;
use App\Traits\HasModulo;
use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntCfgStatus extends Entity
{
    use HasModulo, HasTela;

    protected $attributes = [
        'stt_id'         => null,
        'stt_nome'       => null,
        'mod_id'         => null,
        'tel_id'         => null,
        'cor_id'         => null,
        'stt_exclusao'   => null,
        'stt_edicao'     => null,
        'stt_impressao'  => null,
        'stt_disponivel' => null,
        'stt_ativo'      => 'A',
        'stt_ordem'      => null,
        'stt_excluido'   => null,
    ];

    protected $dates = ['stt_excluido'];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret = [];

        // ID
        $ret['stt_id'] = (new MyCampo('cfg_status', 'stt_id'))
            ->setValor($dados['stt_id'] ?? '')
            ->crOculto();

        $ret['stt_ordem'] = (new MyCampo('cfg_status', 'stt_ordem'))
            ->setValor($dados['stt_ordem'] ?? '')
            ->crOculto();

        // Nome
        $ret['stt_nome'] = (new MyCampo('cfg_status', 'stt_nome'))
            ->setValor($dados['stt_nome'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-12')
            ->setLeitura($show)
            ->crInput();

        $config['Leitura'] = $show;
        $config['Largura'] = 50;
        $ret['cor_id'] = criaSelectRelativo(
            'cfg_cor',
            'cor_id',
            'cor_nome_rgb',
            $dados['cor_id'] ?? '',
            1,
            'cfg_status',
            [],
            $config
        );

        // debug($dados);
        // Módulo (reutilizado via trait EntCfgModulo)
        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'cfg_status',
            [],
            $config
        );

        $config['Pai'] = 'mod_id';
        $config['Urlbusca'] = base_url('buscas/busca_tela_modulo');

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            2,
            'cfg_status',
            [],
            $config
        );
        // $ret['mod_id'] = EntCfgModulo::campoSelectModulo($dados['mod_id'] ?? '', $show, 'cfg_status');

        // $ret['tel_id'] = EntCfgTela::campoSelectTela($dados['tel_id'] ?? '', $show, 'cfg_status');

        // Cor
        $ret['stt_cor'] = (new MyCampo('cfg_status', 'stt_cor'))
            ->setValor($dados['stt_cor'] ?? '')
            ->setObrigatorio()
            ->setDispForm('col-12')
            ->setLeitura($show)
            ->crCorbst();

        // Opções Sim/Não
        $op = ['S' => 'Sim', 'N' => 'Não'];
        foreach (['stt_exclusao', 'stt_edicao', 'stt_disponivel', 'stt_impressao'] as $campo) {
            // debug($campo);
            // debug($dados[$campo]);
            $ret[$campo] = (new MyCampo('cfg_status', $campo))
                ->setValor($dados[$campo] ?? 'S')
                ->setSelecionado($dados[$campo] ?? 'S')
                ->setOpcoes($op)
                ->setDispForm('col-12')
                ->setLeitura($show)
                ->cr2opcoes();
        }

        $config = [];
        $config['Largura']      = 50;
        $config['Leitura']      = $show;
        $config['Obrigatorio']  = true;
        $config['Selecionado']  = $dados->prf_id ?? [];

        // debug($dados['prf_id']);
        $ret['prf_id'] = criaSelectRelativo(
            'cfg_perfil',
            'prf_id',
            'prf_nome',
            (isset($dados['prf_id']) ? $dados['prf_id'] : ''),
            3,
            'cfg_status_permissao',
            [],
            $config
        );

        return $ret;
    }
}
