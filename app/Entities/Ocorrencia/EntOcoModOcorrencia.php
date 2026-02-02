<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;


class EntOcoModOcorrencia extends Entity
{
    protected $attributes = [
        'moc_id'       => null,
        'moc_nome'     => null,
        'moc_ativo'    => 'A',
        'moc_excluido' => null,
        'tpo_id'       => null,
    ];

    protected $casts = [
        'moc_id' => 'integer',
        'tpo_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(object|array|null $data = null)
    {
        if (is_array($data)) {
            $data = (object) $data;
        }
        parent::__construct((array) ($data ?? []));
        $this->campos = $this->defCampos($data ?? new \stdClass());
    }

    public function defCampos($dados = false, $pos = 0, $show = true)
    {
        if (!$dados || !is_object($dados)) {
            $dados = (object) [];
        }
        $ret = [];

        $mid            = new MyCampo('oco_mod_ocorrencia', 'moc_id');
        $mid->valor     = (isset($dados->moc_id)) ? $dados->moc_id : '';
        $ret['moc_id']   = $mid->crOculto();

        $nome            =  new MyCampo('oco_mod_ocorrencia', 'moc_nome');
        $nome->valor     = (isset($dados->moc_nome)) ? $dados->moc_nome : '';
        $nome->obrigatorio = true;
        $nome->leitura   = false;
        $nome->largura   = 80;
        $ret['moc_nome'] = $nome->crInput();


        // TIPO DE OCORRÊNCIA
        $config = [];
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Leitura']  = isset($dados->moc_id);
        $config['FunChan']  =  'carregaTelaAcaoTipo(this); carregaAcaoTipo(this);';

        $ret['tpo_id'] = criaSelectRelativo(
            'oco_tipo_ocorrencia',
            'tpo_id',
            'tpo_nome',
            $dados->tpo_id ?? '',
            1,
            'oco_mod_ocorrencia',
            [],
            $config
        );

        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';

        $teste          = new MyCampo('oco_mod_ocorrencia', 'moc_ativo');
        $teste->valor   = (isset($dados->moc_ativo)) ? $dados->moc_ativo : 'A';
        $teste->leitura = $show;
        $teste->opcoes  = $simnao;
        $ret['moc_ativo'] = $teste->cr2opcoes();

        return $ret;
    }

    public function defCamposTelasAplicaveis($dados = false, $pos = 0,  $total = 1)
    {
        $dados = (array) $dados;

        // modulo
        $config = [];
        $config['Leitura']     = true;
        $config['Largura']     = 40;
        $config['DispForm']    = 'col-4';
        $config['Ordem']       = $pos;

        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'oco_tpo_tela',
            [],
            $config
        );


        // telas
        $config['Pai']         = "mod_id[$pos]";
        $config['Urlbusca']    = base_url('buscas/busca_tela_modulo');

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            2,
            'oco_tpo_tela',
            [],
            $config
        );

        $config['Pai']         = "tel_id[$pos]";
        $config['Urlbusca']    = base_url('buscas/busca_campo_tela');

        // debug($dados['tof_campo'], true);
        $ret['tof_campo'] = criaSelectRelativo(
            '',
            '',
            '',
            $dados['tof_campo'] ?? '',
            4,
            'oco_tpo_campos',
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
        $add->funcChan  = "addCampo('" . base_url("OcoModOcorrencia/addCampoTa/") . "','telas_aplicaveis',this)";
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
        // if ($total == 1) {
        //     $del->classep .= " d-none";
        // }
        $ret['bt_delta']   = $del->crBotao();

        return $ret;
    }

    public function defCamposAcao($dados = false, $pos = 0, $total = 1, $modo = 'edit')
    {
        $dados = (array) $dados;
        // $tpa = $dados['tpa_id'] ?? null;
        $ret = [];


        // tipo de ação
        $config = [];
        $config['Leitura']  = true;
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Ordem']    = $pos;
        $config['FunChan']     = 'verificaTipoAcao(this)';

        $ret['tpa_id'] = criaSelectRelativo(
            'oco_tipo_acao',
            'tpa_id',
            'tpa_nome',
            $dados['tpa_id'] ?? '',
            1,
            'oco_tpo_acao',
            [],
            $config
        );

        $config['FunChan']     = '';
        $config['DispForm'] = 'col-12';
        $ret['tmo_id'] = criaSelectRelativo(
            'est_tipo_movimentacao',
            'tmo_id',
            'tmo_nome',
            $dados['tmo_id'] ?? '',
            1,
            'oco_tpo_acao',
            [],
            $config
        );

        $config['DispForm'] = 'col-6';
        $config['Largura']  = 30;
        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'oco_tpo_acao',
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
            'oco_tpo_acao',
            [],
            $config
        );

        $config['DispForm'] = 'col-12';
        $config['Largura']  = 50;
        $ret['stt_id'] = criaSelectRelativo(
            'cfg_status',
            'stt_id',
            'stt_nome',
            $dados['stt_id'] ?? '',
            1,
            'oco_tpo_acao',
            [],
            $config
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
        $add->funcChan  = "addCampo('" . base_url("OcoModOcorrencia/addCampoTp/") . "','acoes',this)";
        $ret['bt_addtp']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_delta[$pos]";
        $del->id        = "bt_delta[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('acoes',this)";
        $del->place     = "Excluir Campo";
        // if ($total == 1) {
        //     $del->classep .= " d-none";
        // }
        $ret['bt_deltp']   = $del->crBotao();

        return $ret;
    }


    public function defCamposParaMostrar($dados = false, $show = false)
    {
        if (!$dados || !is_object($dados)) {
            $dados = (object) [];
        }
        $ret = [];

        // TELA
        $config = [];
        $config['DispForm']     = '2col';
        $config['Largura']      = 50;
        $config['Obrigatorio']  = true;

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados->tel_id ?? '',
            1,
            'oco_moc_campos',
            [],
            $config
        );

        // CAMPO
        $config['Leitura']  = true;
        $config['Pai']      = 'tel_id';
        $config['UrlBusca'] = base_url('buscas/busca_tela_modulo');

        $ret['mof_campo'] = criaSelectRelativo(
            'oco_moc_campos',
            'mof_campo',
            'mof_nome',
            $dados->mof_campo ?? '',
            2,
            'oco_moc_campos',
            [],
            $config
        );


        $toc_rotulo                 =  new MyCampo('oco_moc_campos', 'toc_rotulo');
        $toc_rotulo->valor          = (isset($dados->toc_rotulo)) ? $dados->toc_rotulo : '';
        $toc_rotulo->obrigatorio    =  true;
        $toc_rotulo->leitura        = $show;
        $toc_rotulo->largura        = 50;
        $toc_rotulo->dispForm       = '2col';
        $ret['toc_rotulo']          = $toc_rotulo->crInput();
        return $ret;
    }

    public function defPermissoes($dados = false, $pos = 0, $show = false)
    {
        if (!$dados || !is_object($dados)) {
            $dados = (object) [];
        }
        $ret = [];

        // PERMISSÕES
        $config = [];
        $config['Largura']  = 50;
        $config['Leitura']  = true;

        $ret['prf_id'] = criaSelectRelativo(
            'oco_moc_permissao',
            'prf_id',
            'prf_nome',
            $dados->prf_id ?? '',
            3,
            'oco_moc_permissao',
            [],
            $config
        );
        return $ret;
    }
}
