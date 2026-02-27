<?php

namespace App\Entities\Ocorrencia;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntOcoTipoOcorre extends Entity
{
    protected $attributes = [
        'tpo_id'    => null,
        'tpo_nome'  => null,
        'tpo_ativo' => 'A',
        'cla_id'    => null,
        'tmo_id'    => null,
    ];

    protected $casts = [
        'tpo_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    public function defCampos($dados = false, $show = false)
    {
        $dados = (array) $dados;
        $ret = [];

        // ID do Tipo de Ocorrência (oculto)
        $mid            = new MyCampo('oco_tipo_ocorrencia', 'tpo_id');
        $mid->valor     = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $ret['tpo_id']   = $mid->crOculto();

        // Nome do Tipo de Ocorrência
        $nome            =  new MyCampo('oco_tipo_ocorrencia', 'tpo_nome');
        $nome->valor     = (isset($dados['tpo_nome'])) ? $dados['tpo_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura   = $show;
        $nome->largura   = 80;
        $ret['tpo_nome'] = $nome->crInput();

        // Status Ativo / Inativo
        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';

        // Classes vinculadas ao Tipo de Ocorrência
        $teste          = new MyCampo('oco_tipo_ocorrencia', 'tpo_ativo');
        $teste->valor   = (isset($dados['tpo_ativo'])) ? $dados['tpo_ativo'] : 'A';
        $teste->leitura = $show;
        $teste->opcoes  = $simnao;
        $ret['tpo_ativo'] = $teste->cr2opcoes();

        $config = [];
        $config['Obrigatorio'] = false;
        $config['Leitura'] = $show;

        $ret['cla_id'] = criaSelectRelativo(
            'pro_classe',
            'cla_id',
            'cla_nome',
            $dados['cla_id'] ?? '',
            3,
            'oco_tipo_ocorrencia_classe',
            [],
            $config
        );

        // Retorna os campos principais
        return $ret;
    }

    public function defCamposTelasAplicaveis($dados = false, $pos = 0, $show = false)
    {
        $dados = (array) $dados;

        // modulo
        $config = [];
        $config['Label']       = 'Modulo';
        $config['Leitura']     = $show;
        $config['Largura']     = 40;
        $config['DispForm']    = 'col-4';
        $config['Ordem']       = $pos;
        $config['Obrigatorio'] = false;
        // $config['FunChan']    = "mudaObrigatorio(this,'>-1','tel_id[$pos]')";

        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'oco_tipo_ocorrencia_tela',
            [],
            $config
        );

        // telas
        $config['Label']       = 'Tela';
        $config['Pai']         = "mod_id[$pos]";
        $config['Urlbusca']    = base_url('buscas/busca_tela_modulo');
        // $config['FunChan']    = "mudaObrigatorio(this,'>-1','tof_campo[$pos][]')";

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            2,
            'oco_tipo_ocorrencia_tela',
            [],
            $config
        );

        // campo
        $config['Pai']         = "tel_id[$pos]";
        $config['Label']       = 'Campo';
        $config['Urlbusca']    = base_url('buscas/busca_campo_tela');

        $ret['tof_campo'] = criaSelectRelativo(
            '',
            '',
            '',
            $dados['tof_campo'] ?? '',
            4,
            'oco_tipo_ocorrencia_campos',
            ['tel_id' => $dados['tel_id'] ?? ''],
            $config,
            'tof_campo'
        );

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addta[$pos]";
        $add->id        = "bt_addta[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("OcoTipoOcorrencia/addCampoTa/") . "','telas_aplicaveis',this);setTimeout(function(){acerta_botoes_rep_telas('telas_aplicaveis');},50)";
        $ret['bt_addta']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('telas_aplicaveis',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_delta']   = $del->crBotao();

        return $ret;
    }

    public function defCamposAcao($dados = false, $pos = 0, $show = false)
    {
        $dados = (array) $dados;
        $ret = [];

        // tipo de ação
        $config = [];
        $config['Label']    = 'Tipo de Ação';
        $config['Leitura']  = $show;
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Ordem']    = $pos;
        $config['FunChan']  = 'verificaTipoAcao(this)';

        $ret['tpa_id'] = criaSelectRelativo(
            'oco_tipo_acao',
            'tpa_id',
            'tpa_nome',
            $dados['tpa_id'] ?? '',
            1,
            'oco_tipo_ocorrencia',
            [],
            $config
        );

        // tipo de movimentação
        $config['FunChan']     = '';
        $config['Label']       = 'Tipo de Movimentação';
        $config['DispForm']    = 'col-12';
        $config['Obrigatorio'] = false;

        $ret['tmo_id'] = criaSelectRelativo(
            'est_tipo_movimentacao',
            'tmo_id',
            'tmo_nome',
            $dados['tmo_id'] ?? '',
            1,
            'oco_tipo_ocorrencia',
            [],
            $config,
            "tmo_id_acao[$pos]"
        );

        // modulo
        $config['Label']    = 'Módulo';
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 30;
        $config['Obrigatorio'] = false;

        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'oco_tipo_ocorrencia',
            [],
            $config,
            "mod_id_acao[$pos]"
        );

        // tela
        $config['Label']       = 'Tela';
        $config['Pai']         = "mod_id_acao[$pos]";
        // $config['Obrigatorio'] = false;
        $config['Urlbusca']    = base_url('buscas/busca_tela_modulo');

        $ret['tel_id'] = criaSelectRelativo(
            '',
            '',
            '',
            $dados['tel_id'] ?? '',
            2,
            'oco_tipo_ocorrencia',
            ['mod_id' => $dados['mod_id'] ?? ''],
            $config,
            "tel_id_acao[$pos]"
        );

        // status
        $config['DispForm'] = 'col-12';
        $config['Label']    = 'Status';
        $config['Largura']  = 50;
        // $config['Obrigatorio'] = false;

        $ret['stt_id'] = criaSelectRelativo(
            'vw_cfg_status_relac',
            'stt_id',
            'stt_tela_status',
            $dados['stt_id'] ?? '',
            1,
            'oco_tipo_ocorrencia',
            [],
            $config,
            "stt_id_acao[$pos]"
        );

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addtp[$pos]";
        $add->id        = "bt_addtp[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("OcoTipoOcorrencia/addCampoTp/") . "','acoes',this); setTimeout(function(){acerta_botoes_rep_acoes('acoes'); corrigeObrigatorio();}, 50)";


        $ret['bt_addtp']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_deltp[$pos]";
        $del->id        = "bt_deltp[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('acoes',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_deltp']   = $del->crBotao();

        return $ret;
    }

    public function defCamposParaMostrar($dados = false)
    {
        $dados = (array) $dados;
        $ret = [];


        // tela
        $config = [];
        $config['Label']       = 'Tela';
        $config['DispForm']    = '2col';
        $config['Largura']     = 30;
        $config['Obrigatorio'] = false;

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            1,
            'oco_tipo_ocorrencia_campos',
            [],
            $config
        );


        // Campo da Tela
        $config['DispForm']    = '2col';
        $config['Largura']     = 50;
        $config['Pai']         = 'tel_id';
        $config['Urlbusca']    = base_url('buscas/busca_tela_modulo');

        $ret['tof_id'] = criaSelectRelativo(
            'cfg_tela',
            'tof_id',
            'tof_campo',
            $dados['tof_id'] ?? '',
            2,
            'oco_tipo_ocorrencia_campos',
            [],
            $config
        );


        // Rótulo do campo
        $toc_rotulo                 =  new MyCampo('oco_tipo_ocorrencia_campos', 'toc_rotulo');
        $toc_rotulo->valor          = (isset($dados['toc_rotulo'])) ? $dados['toc_rotulo'] : '';
        $toc_rotulo->obrigatorio    =  true;
        $toc_rotulo->leitura        = false;
        $toc_rotulo->largura        = 50;
        $toc_rotulo->dispForm       = '2col';
        $ret['toc_rotulo']          = $toc_rotulo->crInput();
        return $ret;
    }

    public function defPermissoes($dados = false, $show = false)
    {
        $dados = (array) $dados;
        $ret = [];

        // Perfis com permissão
        $config = [];
        $config['Label']    = 'Perfis com Permissão';
        $config['Obrigatorio'] = true;
        $config['Largura']     = 50;
        $config['Leitura']     = $show;

        $ret['prf_id'] = criaSelectRelativo(
            'cfg_perfil',
            'prf_id',
            'prf_nome',
            $dados['prf_id'] ?? '',
            3,
            'oco_tipo_ocorrencia_permissao',
            [],
            $config
        );

        return $ret;
    }
}
