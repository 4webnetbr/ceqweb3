<?php

namespace App\Entities\Logistica;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;

/**
 * Regra de Notificação SMS (log_notif_sms_config, prefixo nsc_).
 *
 * Segue o mesmo padrão de App\Entities\Config\EntCfgCor /
 * App\Entities\Fornecedores\EntOcoNotifEvento: monta o array `campos`
 * (MyCampo já renderizado) em `defCampos()`, consumido pelo Controller
 * (add/edit/show) e pela view genérica `vw_edicao`.
 *
 * nsc_tipo_regra determina quais campos são obrigatórios/visíveis (ver
 * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md, seções 4.5 e
 * 4.10):
 * - 'saldo_baixo' → nsc_saldo_minimo (wrapper #divSaldo). Nada alterado
 *                    neste ciclo.
 * - 'api'         → nsc_metodo_api (wrapper #divApi).
 * - 'consulta'    → nsc_view_consulta + nsc_view_dbgroup, exibidos juntos
 *                    em um único select de valor composto (wrapper
 *                    #divConsulta). nsc_view_dbgroup nunca é renderizado
 *                    isoladamente.
 * O show/hide + (des)obrigatoriedade em runtime é feito via JS
 * (alternaCamposTipoRegra(), my_fields.js, seção 4.16), disparado no
 * onchange de nsc_tipo_regra e no carregamento da tela (ver
 * NotifSmsConfig::edit()).
 */
class EntLogNotifSmsConfig extends Entity
{
    protected $attributes = [
        'nsc_id'                => null,
        'nsc_nome'              => null,
        'nsc_tipo_regra'        => 'saldo_baixo',
        'nsc_saldo_minimo'      => null,
        'nsc_metodo_api'        => null,
        'nsc_view_consulta'     => null,
        'nsc_view_dbgroup'      => null,
        'nsc_telefones'         => null,
        'nsc_mensagem_template' => null,
        'nsc_ativo'             => 'A',
        'nsc_excluido'          => null,
    ];

    protected $datamap = [];
    protected $dates   = ['nsc_excluido'];
    protected $casts   = [
        'nsc_id'           => '?integer',
        'nsc_saldo_minimo' => '?integer',
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

        // Tipo de Regra — dispara o toggle de 3 grupos condicionais
        $tipoRegra              = new MyCampo('log_notif_sms_config', 'nsc_tipo_regra');
        $tipoRegra->valor       = $dados['nsc_tipo_regra'] ?? 'saldo_baixo';
        $tipoRegra->selecionado = $tipoRegra->valor;
        $tipoRegra->opcoes      = [
            'saldo_baixo' => 'Saldo Baixo',
            'api'         => 'API',
            'consulta'    => 'Consulta',
        ];
        $tipoRegra->obrigatorio = true;
        $tipoRegra->leitura     = $show;
        $tipoRegra->dispForm    = 'col-6';
        $tipoRegra->funcChan    = 'alternaCamposTipoRegra(this)';
        $tipoRegra->setLabel('Tipo de Regra');
        $ret['nsc_tipo_regra'] = $tipoRegra->crSelect();

        // ---- Grupo "Saldo Baixo" (wrapper #divSaldo) — mantido igual ao já existente, sem alteração ----
        $saldoMinimo              = new MyCampo('log_notif_sms_config', 'nsc_saldo_minimo');
        $saldoMinimo->valor       = $dados['nsc_saldo_minimo'] ?? '';
        $saldoMinimo->leitura     = $show;
        $saldoMinimo->dispForm    = 'col-4';
        $saldoMinimo->setLabel('Saldo Mínimo');
        $ret['nsc_saldo_minimo'] = $saldoMinimo->crInput();

        // ---- Grupo "API" (wrapper #divApi) ----
        // Opções montadas por reflection via SmsApiConsumer::metodosDisponiveis()
        // (seção 4.1 do documento) — sempre por SMS_API_CONSUMER_CLASS, nunca
        // hardcoding do nome da classe.
        $classeApi = SMS_API_CONSUMER_CLASS;

        $metodoApi              = new MyCampo('log_notif_sms_config', 'nsc_metodo_api');
        $metodoApi->valor       = $dados['nsc_metodo_api'] ?? '';
        $metodoApi->selecionado = $metodoApi->valor;
        $metodoApi->opcoes      = $classeApi::metodosDisponiveis();
        $metodoApi->leitura     = $show;
        $metodoApi->dispForm    = 'col-6';
        $metodoApi->setLabel('Método da API');
        $ret['nsc_metodo_api'] = $metodoApi->crSelect();

        // ---- Grupo "Consulta" (wrapper #divConsulta) ----
        // Select único, de valor composto "dbGroup|view" e rótulo
        // "view (dbGroup)", montado a partir de
        // ConfigDicDadosModel::getViewsPorPrefixo('vw_sms_') (seção 4.2 do
        // documento). Construção manual (sem doBanco()) porque o valor
        // exibido/selecionado é composto, diferente do valor bruto da
        // coluna VARCHAR (doBanco() assumiria input texto simples).
        //
        // nsc_view_dbgroup NUNCA é campo exibido/editável isoladamente
        // nesta tela — é gravado internamente por NotifSmsConfig::store()
        // (seção 4.13), a partir do split do valor composto recebido deste
        // select, nunca confiando no valor bruto do POST.
        $viewsConsulta  = (new ConfigDicDadosModel())->getViewsPorPrefixo('vw_sms_');
        $opcoesConsulta = [];
        foreach ($viewsConsulta as $v) {
            $valorComposto                  = $v['dbGroup'] . '|' . $v['view'];
            $opcoesConsulta[$valorComposto] = $v['view'] . ' (' . $v['dbGroup'] . ')';
        }
        $valorAtualConsulta = (($dados['nsc_view_dbgroup'] ?? '') !== '' && ($dados['nsc_view_consulta'] ?? '') !== '')
            ? $dados['nsc_view_dbgroup'] . '|' . $dados['nsc_view_consulta']
            : '';

        $viewConsulta              = new MyCampo();
        $viewConsulta->id          = 'nsc_view_consulta';
        $viewConsulta->objeto      = 'select';
        $viewConsulta->nome        = 'nsc_view_consulta';
        $viewConsulta->valor       = $valorAtualConsulta;
        $viewConsulta->selecionado = $valorAtualConsulta;
        $viewConsulta->opcoes      = $opcoesConsulta;
        $viewConsulta->leitura     = $show;
        $viewConsulta->dispForm    = 'col-6';
        $viewConsulta->setLabel('View de Consulta');
        $ret['nsc_view_consulta'] = $viewConsulta->crSelect();

        // Telefones (CSV texto livre)
        $telefones              = new MyCampo('log_notif_sms_config', 'nsc_telefones');
        $telefones->valor       = $dados['nsc_telefones'] ?? '';
        $telefones->obrigatorio = true;
        $telefones->leitura     = $show;
        $telefones->hint        = 'Informe os números separados por vírgula (ex.: 5548999998888,5548911112222)';
        $telefones->dispForm    = 'col-8';
        $telefones->setLabel('Telefones');
        $ret['nsc_telefones'] = $telefones->crInput();

        // Mensagem (template) — hint explica colchetes para API/Consulta;
        // {limite}/{saldo} do tipo saldo_baixo continuam válidos, sem
        // unificação das duas convenções (ver seção 4.10).
        $mensagem              = new MyCampo('log_notif_sms_config', 'nsc_mensagem_template');
        $mensagem->valor       = $dados['nsc_mensagem_template'] ?? '';
        $mensagem->obrigatorio = true;
        $mensagem->leitura     = $show;
        $mensagem->linhas      = 4;
        $mensagem->colunas     = 60;
        $mensagem->dispForm    = 'col-12';
        $mensagem->hint        = "Tipos 'API'/'Consulta': use colchetes para inserir campos do retorno (ex.: [ent_id_dispensacao]). Tipo 'Saldo Baixo': mantém {limite}/{saldo}, sem alteração.";
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
