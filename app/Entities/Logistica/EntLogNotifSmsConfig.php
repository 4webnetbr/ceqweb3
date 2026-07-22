<?php

namespace App\Entities\Logistica;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

/**
 * Regra de Notificação SMS (log_notif_sms_config, prefixo nsc_).
 *
 * Segue o mesmo padrão de App\Entities\Config\EntCfgCor /
 * App\Entities\Fornecedores\EntOcoNotifEvento: monta o array `campos`
 * (MyCampo já renderizado) em `defCampos()`, consumido pelo Controller
 * (add/edit/show) e pela view genérica `vw_edicao`.
 *
 * nsc_tipo_regra determina quais campos são obrigatórios/visíveis:
 * - 'entrega'     → nsc_ren_tipo, nsc_ren_status_max, nsc_condicao,
 *                    nsc_minutos_limite (wrapper #divEntrega).
 * - 'saldo_baixo' → nsc_saldo_minimo (wrapper #divSaldo).
 * O show/hide + (des)obrigatoriedade em runtime é feito via JS
 * (alternaCamposTipoRegra(), my_fields.js), disparado no onchange de
 * nsc_tipo_regra e no carregamento da tela (ver NotifSmsConfig::edit()).
 */
class EntLogNotifSmsConfig extends Entity
{
    protected $attributes = [
        'nsc_id'                => null,
        'nsc_nome'              => null,
        'nsc_tipo_regra'        => 'entrega',
        'nsc_ren_tipo'          => null,
        'nsc_ren_status_max'    => null,
        'nsc_condicao'          => null,
        'nsc_minutos_limite'    => null,
        'nsc_saldo_minimo'      => null,
        'nsc_telefones'         => null,
        'nsc_mensagem_template' => null,
        'nsc_ativo'             => 'A',
        'nsc_excluido'          => null,
    ];

    protected $datamap = [];
    protected $dates   = ['nsc_excluido'];
    protected $casts   = [
        'nsc_id'             => '?integer',
        'nsc_ren_tipo'       => '?integer',
        'nsc_ren_status_max' => '?integer',
        'nsc_minutos_limite' => '?integer',
        'nsc_saldo_minimo'   => '?integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret   = [];

        // ID (campo oculto)
        $id        = new MyCampo('log_notif_sms_config', 'nsc_id');
        $id->valor = $dados['nsc_id'] ?? '';
        $ret['nsc_id'] = $id->crOculto();

        // Nome da Regra
        $nome              = new MyCampo('log_notif_sms_config', 'nsc_nome');
        $nome->valor       = $dados['nsc_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura     = $show;
        $nome->dispForm    = 'col-6';
        $nome->setLabel('Nome');
        $ret['nsc_nome'] = $nome->crInput();

        // Tipo de Regra — dispara o toggle de campos condicionais
        $tipoRegra              = new MyCampo('log_notif_sms_config', 'nsc_tipo_regra');
        $tipoRegra->valor       = $dados['nsc_tipo_regra'] ?? 'entrega';
        $tipoRegra->selecionado = $tipoRegra->valor;
        $tipoRegra->opcoes      = [
            'entrega'     => 'Entrega',
            'saldo_baixo' => 'Saldo Baixo',
        ];
        $tipoRegra->obrigatorio = true;
        $tipoRegra->leitura     = $show;
        $tipoRegra->dispForm    = 'col-6';
        $tipoRegra->funcChan    = 'alternaCamposTipoRegra(this)';
        $tipoRegra->setLabel('Tipo de Regra');
        $ret['nsc_tipo_regra'] = $tipoRegra->crSelect();

        // ---- Grupo "Entrega" (wrapper #divEntrega) ----
        $renTipo              = new MyCampo('log_notif_sms_config', 'nsc_ren_tipo');
        $renTipo->valor       = $dados['nsc_ren_tipo'] ?? '';
        $renTipo->selecionado = $renTipo->valor;
        $renTipo->opcoes      = [
            1 => 'Ceqnep',
            2 => 'Transportadora',
            3 => 'Hospital Retira',
        ];
        $renTipo->leitura     = $show;
        $renTipo->dispForm    = 'col-4';
        $renTipo->setLabel('Tipo de Renovação');
        $ret['nsc_ren_tipo'] = $renTipo->crSelect();

        $renStatusMax              = new MyCampo('log_notif_sms_config', 'nsc_ren_status_max');
        $renStatusMax->valor       = $dados['nsc_ren_status_max'] ?? '';
        $renStatusMax->leitura     = $show;
        $renStatusMax->dispForm    = 'col-4';
        $renStatusMax->setLabel('Status Máximo');
        $ret['nsc_ren_status_max'] = $renStatusMax->crInput();

        $condicao              = new MyCampo('log_notif_sms_config', 'nsc_condicao');
        $condicao->valor       = $dados['nsc_condicao'] ?? '';
        $condicao->selecionado = $condicao->valor;
        $condicao->opcoes      = [
            'antes_chegada' => 'Antes da Chegada',
            'apos_chegada'  => 'Após a Chegada',
        ];
        $condicao->leitura     = $show;
        $condicao->dispForm    = 'col-4';
        $condicao->setLabel('Condição');
        $ret['nsc_condicao'] = $condicao->crSelect();

        $minutosLimite              = new MyCampo('log_notif_sms_config', 'nsc_minutos_limite');
        $minutosLimite->valor       = $dados['nsc_minutos_limite'] ?? '';
        $minutosLimite->leitura     = $show;
        $minutosLimite->dispForm    = 'col-4';
        $minutosLimite->setLabel('Minutos Limite');
        $ret['nsc_minutos_limite'] = $minutosLimite->crInput();

        // ---- Grupo "Saldo Baixo" (wrapper #divSaldo) ----
        $saldoMinimo              = new MyCampo('log_notif_sms_config', 'nsc_saldo_minimo');
        $saldoMinimo->valor       = $dados['nsc_saldo_minimo'] ?? '';
        $saldoMinimo->leitura     = $show;
        $saldoMinimo->dispForm    = 'col-4';
        $saldoMinimo->setLabel('Saldo Mínimo');
        $ret['nsc_saldo_minimo'] = $saldoMinimo->crInput();

        // Telefones (CSV texto livre)
        $telefones              = new MyCampo('log_notif_sms_config', 'nsc_telefones');
        $telefones->valor       = $dados['nsc_telefones'] ?? '';
        $telefones->obrigatorio = true;
        $telefones->leitura     = $show;
        $telefones->hint        = 'Informe os números separados por vírgula (ex.: 5548999998888,5548911112222)';
        $telefones->dispForm    = 'col-8';
        $telefones->setLabel('Telefones');
        $ret['nsc_telefones'] = $telefones->crInput();

        // Mensagem (template)
        $mensagem              = new MyCampo('log_notif_sms_config', 'nsc_mensagem_template');
        $mensagem->valor       = $dados['nsc_mensagem_template'] ?? '';
        $mensagem->obrigatorio = true;
        $mensagem->leitura     = $show;
        $mensagem->linhas      = 4;
        $mensagem->colunas     = 60;
        $mensagem->dispForm    = 'col-12';
        $mensagem->setLabel('Mensagem');
        $ret['nsc_mensagem_template'] = $mensagem->crTexto();

        // Ativo / Inativo
        $ativo              = new MyCampo('log_notif_sms_config', 'nsc_ativo');
        $ativo->valor       = $dados['nsc_ativo'] ?? 'A';
        $ativo->selecionado = $ativo->valor;
        $ativo->opcoes      = ['A' => 'Ativo', 'I' => 'Inativo'];
        $ativo->leitura     = $show;
        $ativo->setLabel('Status');
        $ret['nsc_ativo'] = $ativo->cr2opcoes();

        return $ret;
    }
}
