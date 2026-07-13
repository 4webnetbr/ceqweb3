<?php

namespace App\Entities\Fornecedores;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

/**
 * T43 — Notificação de Evento (oco_notif_evento, prefixo nev_).
 * Campos de CABEÇALHO das abas "Dados Gerais" (parte fixa, RN03.10-RN03.13),
 * "Providências" (RN03.14/RN03.15) e "Parecer Final" (RN03.17-RN03.19).
 * O grid de produtos (RN03.2-RN03.9) é tratado por EntOcoNotifEventoProduto,
 * e a aba Ações por EntOcoNotifEventoAcao — mantendo o mesmo padrão de
 * separação usado em EntOcoSubtOcorrencia (defCampos / defCamposTelasAplicaveis
 * / defCamposAcao).
 */
class EntOcoNotifEvento extends Entity
{
    protected $attributes = [
        'nev_id'            => null,
        'nev_qtd_adquirida' => null,
        'nev_numero_nf'     => null,
        'nev_fornecedor'    => null,
        'nev_fabricacao'    => null,
        'nev_providencias'  => null,
        'nev_notificado'    => null,
        'nev_parecer'       => null,
        'nev_notivisa'      => 'N',
        'nev_notivisa_num'  => null,
        'stt_id'            => null,
        'usu_criou'         => null,
    ];

    protected $casts = [
        'nev_id'            => 'integer',
        'nev_qtd_adquirida' => 'integer',
        'stt_id'            => 'integer',
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

        $nevId        = new MyCampo('oco_notif_evento', 'nev_id');
        $nevId->valor = $dados['nev_id'] ?? '';
        $ret['nev_id'] = $nevId->crOculto();

        // ---- Dados Gerais (cabeçalho) ----
        $qtdAdquirida              = new MyCampo('oco_notif_evento', 'nev_qtd_adquirida');
        $qtdAdquirida->valor       = $dados['nev_qtd_adquirida'] ?? '';
        $qtdAdquirida->obrigatorio = true;
        $qtdAdquirida->leitura     = $show;
        $qtdAdquirida->dispForm    = 'col-3';
        $ret['nev_qtd_adquirida'] = $qtdAdquirida->crInput();

        $numeroNf              = new MyCampo('oco_notif_evento', 'nev_numero_nf');
        $numeroNf->valor       = $dados['nev_numero_nf'] ?? '';
        $numeroNf->obrigatorio = true;
        $numeroNf->leitura     = $show;
        $numeroNf->maximo      = 20;
        $numeroNf->dispForm    = 'col-3';
        $ret['nev_numero_nf'] = $numeroNf->crInput();

        $fornecedor              = new MyCampo('oco_notif_evento', 'nev_fornecedor');
        $fornecedor->valor       = $dados['nev_fornecedor'] ?? '';
        $fornecedor->obrigatorio = true;
        $fornecedor->leitura     = $show;
        $fornecedor->minimo      = 5;
        $fornecedor->maximo      = 200;
        $fornecedor->dispForm    = 'col-6';
        $ret['nev_fornecedor'] = $fornecedor->crInput();

        $fabricacao              = new MyCampo('oco_notif_evento', 'nev_fabricacao');
        $fabricacao->valor       = $dados['nev_fabricacao'] ?? '';
        $fabricacao->obrigatorio = true;
        $fabricacao->leitura     = $show;
        $fabricacao->dispForm    = 'col-4';
        $ret['nev_fabricacao'] = $fabricacao->crInput();

        // ---- Providências ----
        $providencias              = new MyCampo('oco_notif_evento', 'nev_providencias');
        $providencias->valor       = $dados['nev_providencias'] ?? '';
        $providencias->obrigatorio = true;
        $providencias->leitura     = $show;
        $providencias->minimo      = 5;
        $providencias->maximo      = 500;
        $providencias->linhas      = 4;
        $providencias->colunas     = 60;
        $providencias->dispForm    = 'col-12';
        $ret['nev_providencias'] = $providencias->crTexto();

        $notificado              = new MyCampo('oco_notif_evento', 'nev_notificado');
        $notificado->valor       = $dados['nev_notificado'] ?? '';
        $notificado->obrigatorio = true;
        $notificado->leitura     = $show;
        $notificado->minimo      = 5;
        $notificado->maximo      = 200;
        $notificado->dispForm    = 'col-6';
        $ret['nev_notificado'] = $notificado->crInput();

        // ---- Parecer Final ----
        $parecer              = new MyCampo('oco_notif_evento', 'nev_parecer');
        $parecer->valor       = $dados['nev_parecer'] ?? '';
        $parecer->obrigatorio = true;
        $parecer->leitura     = $show;
        $parecer->minimo      = 5;
        $parecer->maximo      = 500;
        $parecer->linhas      = 4;
        $parecer->colunas     = 60;
        $parecer->dispForm    = 'col-12';
        $ret['nev_parecer'] = $parecer->crTexto();

        // RN03.18 — toggle Notivisa (S/N), com campo condicional nev_notivisa_num
        // (RN03.19) exibido via mesmo padrão de campo condicional já usado em
        // T9/T12 (verificaTipoAcao()/JS equivalente — aqui um onchange simples,
        // não precisa reaproveitar verificaTipoAcao() em si, que é específico
        // de tpa_tipo; ver my_fornecedores.js:mostraNotivisaNum()).
        $notivisa              = new MyCampo('oco_notif_evento', 'nev_notivisa');
        // valor = valor SUBMETIDO quando marcado (fixo 'S'); selecionado =
        // valor ATUAL do registro, usado por crCheckbox() só pra decidir se
        // nasce marcado ou não. Antes os dois vinham iguais ao dado atual
        // (sempre 'N'==selecionado ou 'S'==selecionado), o que fazia o
        // checkbox nascer sempre marcado (bug corrigido nesta rodada).
        $notivisa->valor       = 'S';
        $notivisa->selecionado = $dados['nev_notivisa'] ?? 'N';
        $notivisa->leitura     = $show;
        $notivisa->dispForm    = 'col-3';
        $notivisa->funcChan    = 'mostraNotivisaNum(this)';
        $ret['nev_notivisa'] = $notivisa->crCheckbox();

        $notivisaNum              = new MyCampo('oco_notif_evento', 'nev_notivisa_num');
        $notivisaNum->valor       = $dados['nev_notivisa_num'] ?? '';
        $notivisaNum->leitura     = $show;
        $notivisaNum->minimo      = 5;
        $notivisaNum->maximo      = 50;
        $notivisaNum->dispForm    = 'col-4';
        // RN03.19 — obrigatório somente se nev_notivisa = 'S'; obrigatoriedade
        // condicional fica a cargo do JS (mostraNotivisaNum(), mesmo padrão de
        // mudaObrigatorioElemDiv() usado por verificaTipoAcao() em my_fields.js).
        $notivisaNum->obrigatorio = false;
        $ret['nev_notivisa_num'] = $notivisaNum->crInput();

        return $ret;
    }
}
