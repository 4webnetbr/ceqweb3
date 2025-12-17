<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Entities\Config\EntCfgTela;
use App\Entities\Config\EntCfgLayoutEtiq;

class EntCfgEtiqueta extends Entity
{
    protected $attributes = [
        'etq_id'    => null,
        'etq_nome'  => null,
        'let_id'    => null,
        'mod_id'    => null,
        'tel_id'    => null,
        'etq_ativo' => 'A',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }


    public function defCampos($dados = false, $show = false, $view = '')
    {
        $opcoes         = new CommonModel();
        $simnao['S']    = 'Sim';
        $simnao['N']    = 'Não';

        // ID
        $ret = [];
        $etq_id                 = new MyCampo('cfg_etiqueta', 'etq_id');
        $etq_id->valor          = (isset($dados['etq_id'])) ? $dados['etq_id'] : '';
        $ret['etq_id']          = $etq_id->crOculto();

        // Nome
        $nome                   =  new MyCampo('cfg_etiqueta', 'etq_nome');
        $nome->valor            = (isset($dados['etq_nome'])) ? $dados['etq_nome'] : '';
        $nome->obrigatorio      = true;
        $nome->leitura          = $show;
        $nome->dispForm         = "col-12";
        $nome->largura          = 50;
        $ret['etq_nome']        = $nome->crInput();

        // Status
        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';
        $ativ                   = new MyCampo('cfg_etiqueta', 'etq_ativo');
        $ativ->valor            = (isset($dados['etq_ativo'])) ? $dados['etq_ativo'] : 'A';
        $ativ->selecionado      = $ativ->valor;
        $ativ->opcoes           = $opcat;
        $ativ->leitura          = $show;
        $ativ->dispForm         = "col-12";
        $ativ->largura          = 50;
        $ret['etq_ativo']       = $ativ->cr2opcoes();

        // Layout
        $chave = false;
        if (isset($dados['let_id'])) {
            $chave = 'let_id = ' . $dados['let_id'];
        }
        $opc_let      = $opcoes->getListaOpcoes('default', 'cfg_layout_etiqueta', ['let_nome', 'let_id'], $chave);

        $let_id                 = new MyCampo('cfg_etiqueta', 'let_id', false);
        $let_id->valor          = (isset($dados['let_id'])) ? $dados['let_id'] : '';
        $let_id->selecionado    = $let_id->valor;
        $let_id->opcoes         = $opc_let;
        $let_id->leitura        = $show;
        $let_id->largura        = 50;
        $let_id->dispForm       = "col-12";
        $ret['let_id'] = $let_id->crSelect();

        // Módulo
        $chave = false;
        if (isset($dados['mod_id'])) {
            $chave = 'mod_id = ' . $dados['mod_id'];
        }
        // $opc_mod      = $opcoes->getListaOpcoes('default', 'cfg_modulo', ['mod_nome', 'mod_id'], $chave);
        $opc_mod      = $opcoes->getListaOpcoes('default', 'cfg_modulo', ['mod_nome', 'mod_id']);

        $mod_id                 = new MyCampo('cfg_etiqueta', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->opcoes         = $opc_mod;
        $mod_id->leitura        = $show;
        $mod_id->dispForm       = "col-5";
        $mod_id->largura        = 50;
        $ret['mod_id']          = $mod_id->crSelect();

        // Tela
        $chave = false;
        if (isset($dados['tel_id'])) {
            $chave = 'tel_id = ' . $dados['tel_id'];
        }
        $opc_tel      = $opcoes->getListaOpcoes('default', 'cfg_tela', ['tel_nome', 'tel_id'], $chave);

        $tel_id                 = new MyCampo('cfg_etiqueta', 'tel_id');
        $tel_id->valor          = (isset($dados['tel_id'])) ? $dados['tel_id'] : "";
        $tel_id->selecionado    = $tel_id->valor;
        $tel_id->urlbusca       = base_url('buscas/busca_tela_modulo');
        $tel_id->pai            = 'mod_id';
        $tel_id->opcoes         = $opc_tel;
        $tel_id->dispForm       = "col-5";
        $tel_id->largura        = 50;
        $ret['tel_id']          = $tel_id->crDepende();
        return $ret;
    }
}
