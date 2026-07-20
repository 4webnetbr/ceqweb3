<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntOcoSubtOcorrencia extends Entity
{
    protected $attributes = [
        'sut_id'       => null,
        'sut_nome'     => null,
        'sut_ativo'    => 'A',
        'sut_excluido' => null,
        'tpo_id'       => null,
        'sut_fina'     => null,
    ];

    protected $casts = [
        'sut_id' => 'integer',
        'tpo_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos($show = true, $pos = 0)
    {
        $dados = $this->toArray();
        $ret   = [];

		// debug($dados, true);
        $mid            = new MyCampo('oco_subt_ocorrencia', 'sut_id');
        $mid->valor     = $dados['sut_id'] ?? '';
        $ret['sut_id']  = $mid->crOculto();

        // debug($dados);
        $nome            =  new MyCampo('oco_subt_ocorrencia', 'sut_nome');
        $nome->valor     = $dados['sut_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura   = false;
        $nome->largura   = 80;
        $ret['sut_nome'] = $nome->crInput();


        // TIPO DE OCORRÊNCIA
        $config = [];
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Leitura']  = $show;
        // $config['Ordem']    = $pos;
        $config['FunChan']  =  'carregaTelaAcaoTipo(this); carregaAcaoTipo(this);';
        // debug($config, true);
        // debug('Tipo de Ocorrencia');
        $ret['tpo_id'] = criaSelectRelativo(
            'vw_oco_tpo_ocorrencia_relac',
            'tpo_id',
            'tpo_nome',
            $dados['tpo_id'] ?? '',
            1,
            'oco_subt_ocorrencia',
            ['tpo_ativo' => 'A'],
            $config
        );

        // CLASSE
        $config = [];
        $config['Pai'] = 'tpo_id';
        $config['Label'] = "Classe de Produto";
        $config['Urlbusca'] = base_url('Buscas/buscaClassePorTipo');
        $config['DispForm'] = 'col-8';
        $config['Leitura']  = $show;
        $config['Todos']  = true;
        $filtro = [];
        if (!empty($dados['cla_id'])) {
            unset($config['Todos']);
        }
        // debug($dados['cla_id'], true);

        $ret['cla_id'] = criaSelectRelativo(
            '',
            '',
            '',
            $dados['cla_id'] ?? '',
            4,
            'oco_subt_ocorrencia_classe',
            [],
            $config,
            'cla_id'
        );
        // debug($ret, true);
        // ['cla_id' => $dados['cla_id'] ?? ''],

        // RN03.6 — sut_fina deixou de ser editável em Dados Gerais.
        // Passa a ser um campo DERIVADO, calculado em OcoSubtOcorrencia::store()
        // a partir do sta_fina de cada linha da aba Ações (ver defCamposAcao()).

        return $ret;
    }

    public function defCamposTelasAplicaveis($dados = false, $pos = 0, $show = false)
    {
        $dados = (array) $dados;

        // modulo
        $config = [];
        $config['Leitura']     = $show;
        $config['Largura']     = 40;
        $config['DispForm']    = 'col-4';
        $config['Ordem']       = $pos;

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
        $config['Pai']         = "mod_id[$pos]";
        $config['Urlbusca']    = base_url('buscas/busca_tela_modulo');
        // debug($config, true);

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

        // CAMPOS 
        $config['Pai']         = "tel_id[$pos]";
        $config['Urlbusca']    = base_url('buscas/busca_campo_tela');
        $config['DispForm']    = 'col-4';

        // debug($dados['tof_campo'], true);
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
        $add->funcChan  = "addCampo('" . base_url("OcoSubtOcorrencia/addCampoTa/") . "','telas_aplicaveis',this)";
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
        $config['Label']    = 'Tipo de Ação';
        $config['Leitura']  = true;
        $config['DispForm'] = 'col-5';
        $config['Largura']  = 50;
        $config['Ordem']    = $pos;
        $config['FunChan']  = 'verificaTipoAcao(this)';

        $ret['tpa_id'] = criaSelectRelativo(
            'oco_tipo_acao',
            'tpa_id',
            'tpa_nome',
            $dados['tpa_id'] ?? '',
            1,
            'oco_tipo_acao',
            [],
            $config
        );

        // tipo de movimentação
        $config['Label']    = 'Tipo de Movimentação';
        $config['FunChan']  = '';
        $config['DispForm'] = 'col-12';
        $ret['tmo_id'] = criaSelectRelativo(
            'est_tipo_movimentacao',
            'tmo_id',
            'tmo_nome',
            $dados['tmo_id'] ?? '',
            1,
            'oco_tipo_acao',
            [],
            $config,
            'tmo_id_tpa'
        );

        // modulo
        $config['Label']    = 'Módulo';
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 30;
        $ret['mod_id'] = criaSelectRelativo(
            'cfg_modulo',
            'mod_id',
            'mod_nome',
            $dados['mod_id'] ?? '',
            1,
            'oco_tipo_acao',
            [],
            $config,
            'mod_id_tpa'
        );

        // tela
        $config['Label']    = 'Tela';
        $config['Pai']      = 'mod_id';
        $config['Urlbusca'] = base_url('buscas/busca_tela_modulo');

        $ret['tel_id'] = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $dados['tel_id'] ?? '',
            2,
            'oco_tipo_acao',
            [],
            $config,
            'tel_id_tpa'
        );

        // status
        $config['Label']    = 'Status';
        $config['DispForm'] = 'col-12';
        $config['Largura']  = 50;
        $ret['stt_id'] = criaSelectRelativo(
            'cfg_status',
            'stt_id',
            'stt_nome',
            $dados['stt_id'] ?? '',
            1,
            'oco_tipo_acao',
            [],
            $config,
            'stt_id_tpa'
        );

        // RN03.6 — Finalização automática (por ação, aba Ações)
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $staFina                = new MyCampo();
        $staFina->nome          = "sta_fina_tpa[$pos]";
        $staFina->id            = "sta_fina_tpa[$pos]";
        $staFina->label         = 'Finalização Automática';
        $staFina->opcoes        = $simnao;
        $staFina->valor         = $staFina->selecionado = $dados['sta_fina'] ?? 'N';
        $staFina->dispForm      = 'col-2';
        $staFina->classep       = 'sta_fina_tpa';
        $ret['sta_fina'] = $staFina->cr2opcoes();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_addta[$pos]";
        $add->id        = "bt_addta[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("OcoSubtOcorrencia/addCampoTp/") . "','acoes',this)";
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
        $config['Leitura']  = $show;
        $config['Pai']      = 'tpo_id';
        $config['Urlbusca'] = base_url('Buscas/buscaPerfilPorTipo');
        $config['Todos']    = true;
        if (!empty($dados->prf_id)) {
            unset($config['Todos']);
        }

        // debug($dados->prf_id, true);

        $ret['prf_id'] = criaSelectRelativo(
            'cfg_perfil',
            'prf_id',
            'prf_nome',
            $dados->prf_id ?? '',
            4,
            'oco_subt_ocorrencia_permissao',
            [],
            $config
        );
        return $ret;
    }
}
