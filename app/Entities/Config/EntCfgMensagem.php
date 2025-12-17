<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgMensagem extends Entity
{
    protected $attributes = [
        'msg_id'        => null,
        'msg_titulo'    => null,
        'msg_tipo'      => null,
        'msg_cor'       => null,
        'msg_mensagem'  => null,
        'msg_desc_tipo' => null,
        'msg_ativo'     => 'A',
        'msg_excluido'  => null,
    ];

    protected $dates = ['msg_excluido'];
    protected $casts = [];

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

        // ID DA MENSAGEM
        $mid = new MyCampo('cfg_mensagem', 'msg_id');
        $mid->valor = $dados['msg_id'] ?? '';
        $ret['msg_id'] = $mid->crOculto();

        // TÍTULO DA MENSAGEM
        $titu = new MyCampo('cfg_mensagem', 'msg_titulo');
        $titu->valor       = $dados['msg_titulo'] ?? '';
        $titu->obrigatorio = true;
        $titu->leitura     = $show;
        $ret['msg_titulo'] = $titu->crInput();

        // OPÇÕES DE TIPO, ÍCONE & TEXTO
        $opctipo['icone']['P'] = '<i class="fa-solid fa-circle-question fa-lg"></i> Pergunta';
        $opctipo['texto']['P'] = 'Pergunta';

        $opctipo['icone']['A'] = '<i class="fa-solid fa-circle-exclamation fa-lg"></i> Atenção';
        $opctipo['texto']['A'] = 'Alerta';

        $opctipo['icone']['E'] = '<i class="fa-solid fa-circle-xmark fa-lg"></i> Erro';
        $opctipo['texto']['E'] = 'Erro';

        $opctipo['icone']['I'] = '<i class="fa-solid fa-circle-info fa-lg"></i> Informação';
        $opctipo['texto']['I'] = 'Informação';

        // TIPO DA MENSAGEM
        $tipo = new MyCampo('cfg_mensagem', 'msg_tipo');
        $tipo->tipo        = 'tipo';
        $tipo->valor       = $dados['msg_tipo'] ?? '';
        $tipo->largura     = 30;
        $tipo->selecionado = $tipo->valor;
        $tipo->opcoes      = $opctipo;
        $tipo->obrigatorio = true;
        $tipo->leitura     = $show;
        $ret['msg_tipo']   = $tipo->crSelectIcone();

        // COR DA MENSAGEM
        $cor = new MyCampo('cfg_mensagem', 'msg_cor');
        $cor->valor        = $dados['msg_cor'] ?? '';
        $cor->selecionado  = $cor->valor;
        $cor->largura      = 30;
        $cor->obrigatorio  = true;
        $cor->leitura      = $show;
        $ret['msg_cor']    = $cor->crCorbst();

        // TEXTO DA MENSAGEM
        $mens = new MyCampo('cfg_mensagem', 'msg_mensagem');
        $mens->valor       = $dados['msg_mensagem'] ?? '';
        $mens->obrigatorio = true;
        $mens->leitura     = $show;
        $ret['msg_mensagem'] = $mens->crTexto();

        // STATUS ATIVO & INATIVO
        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        // STATUS DA MENSAGEM
        $ativ = new MyCampo('cfg_mensagem', 'msg_ativo');
        $ativ->valor       = $dados['msg_ativo'] ?? 'A';
        $ativ->selecionado = $ativ->valor;
        $ativ->opcoes      = $opcat;
        $ativ->leitura     = $show;
        $ret['msg_ativo']  = $ativ->cr2opcoes();

        return $ret;
    }
}
