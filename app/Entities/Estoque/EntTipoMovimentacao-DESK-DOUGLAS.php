<?php

namespace App\Entities\Estoque;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntTipoMovimentacao extends Entity
{
    protected $attributes = [
        'tmo_id'                    => null,
        'tmo_nome'                  => null,
        'tmo_acumulador'            => null,
        'tmo_conferencia'           => 'N',
        'tmo_semestoque'            => 'N',
        'tmo_transacao_erp'         => null,
        'tmo_atendeautomatico'      => 'N',
        'tmo_transacao_erp_entrada' => null,
        'tmo_transacao_erp_saida'   => null,
        'tmo_requisicao'            => 'S',
        'tmo_entrefiliais'          => 'N',
        'tmo_estoquepadrao'         => 'N',
        'tmo_ativo'                 => 'A',
        'tmo_excluido'              => null,
    ];

    protected $datamap = [];
    protected $dates   = ['tmo_excluido'];
    protected $casts   = [];

    public object $campos;

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false)
    {
        $dados = $this->toArray();
        // debug($dados, true);
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // ID do tipo de movimentação
        $id           =  new MyCampo('est_tipo_movimentacao', 'tmo_id', false);
        $id->valor    = (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $id->leitura  = $show;
        $ret['tmo_id'] = $id->crOculto();

        // Nome do tipo de movimentação
        $nome                 = new MyCampo('est_tipo_movimentacao', 'tmo_nome', false);
        $nome->valor          = (isset($dados['tmo_nome'])) ? $dados['tmo_nome'] : '';
        // $nome->leitura        = $show;
        $nome->obrigatorio    = true;
        $nome->dispForm       = 'col-12';
        $ret['tmo_nome']      = $nome->crInput();

        // Tipos de acumulador 
        $op_ac['E'] = 'Entrada';
        $op_ac['S'] = 'Saída';
        $op_ac['T'] = 'Transferência';
        $op_ac['N'] = 'Sem Movimentacão';

        $tacu                 = new MyCampo('est_tipo_movimentacao', 'tmo_acumulador', false);
        $tacu->valor          = (isset($dados['tmo_acumulador'])) ? substr($dados['tmo_acumulador'], 0, 1) : '';
        $tacu->funcChan       = "acertaObrigatorioTransacoesERP()";
        $tacu->leitura        = $show;
        $tacu->obrigatorio    = true;
        $tacu->opcoes         = $op_ac;
        $tacu->largura        = '50';
        $tacu->dispForm       = 'col-6';
        $ret['tmo_acumulador']      = $tacu->crSelect();


        // Transação ERP 
        $config = [];
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Leitura']  = $show;
        $config['Obrigatorio']  = false;

        $ret['tmo_transacao_erp'] = criaSelectRelativo(
            'est_sap_transacao',
            'tns_codtns',
            'tns_codDescricao',
            $dados['tmo_transacao_erp'] ?? '',
            1,
            'est_tipo_movimentacao',
            [],
            $config,
            'tmo_transacao_erp'
        );


        // TRANSAÇÃO ERP ENTRADA
        $ret['tmo_transacao_erp_entrada'] = criaSelectRelativo(
            'est_sap_transacao',
            'tns_codtns',
            'tns_codDescricao',
            $dados['tmo_transacao_erp_entrada'] ?? '',
            1,
            'est_tipo_movimentacao',
            [],
            $config,
            'tmo_transacao_erp_entrada'
        );

        // TRANSAÇÃO ERP SAÍDA
        $ret['tmo_transacao_erp_saida'] = criaSelectRelativo(
            'est_sap_transacao',
            'tns_codtns',
            'tns_codDescricao',
            $dados['tmo_transacao_erp_saida'] ?? '',
            1,
            'est_tipo_movimentacao',
            [],
            $config,
            'tmo_transacao_erp_saida'
        );

        // Indica se o tipo exige conferência
        $conf                 = new MyCampo('est_tipo_movimentacao', 'tmo_conferencia', false);
        $conf->valor          = (isset($dados['tmo_conferencia'])) ? $dados['tmo_conferencia'] : 'N';
        // $conf->leitura        = $show;
        $conf->opcoes         = $simnao;
        $conf->selecionado    = $conf->valor;
        $conf->dispForm       = 'col-6';
        $ret['tmo_conferencia'] = $conf->cr2opcoes();
        // Atendimento automático da movimentação
        $atea                 = new MyCampo('est_tipo_movimentacao', 'tmo_atendeautomatico', false);
        $atea->valor          = (isset($dados['tmo_atendeautomatico'])) ? $dados['tmo_atendeautomatico'] : 'N';
        // $atea->leitura        = $show;
        $atea->selecionado    = $atea->valor;
        $atea->opcoes         = $simnao;
        $atea->dispForm       = 'col-6';
        $ret['tmo_atendeautomatico'] = $atea->cr2opcoes();

        // Permite movimentação sem controle de estoque
        $seme                 = new MyCampo('est_tipo_movimentacao', 'tmo_semestoque', false);
        $seme->valor          = (isset($dados['tmo_semestoque'])) ? $dados['tmo_semestoque'] : 'N';
        // $seme->leitura        = $show;
        $seme->selecionado    = $seme->valor;
        $seme->opcoes         = $simnao;
        $seme->dispForm       = 'col-6';
        $ret['tmo_semestoque'] = $seme->cr2opcoes();

        // Indica movimentação entre filiais
        $entf               = new MyCampo('est_tipo_movimentacao', 'tmo_entrefiliais', false);
        $entf->valor        = (isset($dados['tmo_entrefiliais'])) ? $dados['tmo_entrefiliais'] : 'N';
        $entf->funcChan     = "acertaObrigatorioTransacoesERP()";
        // $entf->leitura      = $show;
        $entf->selecionado  = $entf->valor;
        $entf->opcoes       = $simnao;
        $entf->funcChan     = "acertaObrigatorioTransacoesERP()";
        $entf->dispForm     = "col-6";
        $ret['tmo_entrefiliais'] = $entf->cr2opcoes();

        // Define se o tipo usa estoque padrão
        $padf               = new MyCampo('est_tipo_movimentacao', 'tmo_estoquepadrao', false);
        $padf->valor        = (isset($dados['tmo_estoquepadrao'])) ? $dados['tmo_estoquepadrao'] : 'N';
        // $padf->leitura      = $show;
        $padf->selecionado  = $padf->valor;
        $padf->opcoes       = $simnao;
        $padf->dispForm     = "col-6";
        $ret['tmo_estoquepadrao'] = $padf->cr2opcoes();

        // Indica se o tipo estará disponível na Requisição
        $requ                 = new MyCampo('est_tipo_movimentacao', 'tmo_requisicao', false);
        $requ->valor          = (isset($dados['tmo_requisicao'])) ? $dados['tmo_requisicao'] : 'N';
        // $requ->leitura        = $show;
        $requ->opcoes         = $simnao;
        $requ->selecionado    = $requ->valor;
        $requ->dispForm       = 'col-6';
        $ret['tmo_requisicao'] = $requ->cr2opcoes();

        // Retorna os campos do tipo de movimentação
        return (object) $ret;
    }



    public function defCamposMov(?object $dados = null, int $pos = 0, bool $show = false)
    {
        $ret = [];

        // ID da linha de movimentação
        $id           =  new MyCampo('est_tipo_movimentacao_movimento', 'tmm_id', false);
        $id->valor    = (isset($dados->tmm_id)) ? $dados->tmm_id : '';
        // $id->nome     = $id->nome . "[" . $pos . "]";
        // $id->id       = $id->id . "[" . $pos . "]";
        $id->leitura  = $show;
        $id->ordem    = $pos;
        $ret['tmm_id'] = $id->crOculto();


        // Deposito de Origem
        $config = [];
        $config['Label'] = 'Deposito de Origem';
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;
        $config['Ordem']    = $pos;
        $config['Leitura']    = $show;

        $ret['tmm_deposito_origem'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_desDep',
            $dados->tmm_deposito_origem ?? '',
            1,
            'est_tipo_movimentacao_movimento',
            [],
            $config,
            "tmm_deposito_origem"
        );

        // Deposito e Destino
        $config['Label'] = 'Deposito de Destino';
        $config['Ordem']     = $pos;
        $config['Pai']       = "tmm_deposito_origem[$pos]";
        $config['Urlbusca']  = base_url('buscas/busca_dep_destino');
        $config['Obrigatorio']    = false;

        $ret['tmm_deposito_destino'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_desDep',
            $dados->tmm_deposito_destino ?? '',
            2,
            'est_tipo_movimentacao_movimento',
            [],
            $config,
            "tmm_deposito_destino"
        );

        // Botão para adicionar nova linha de movimentação
        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("TipoMovimentacao/addCampo") . "','movimentacoes',this)";
        $ret['bt_add']   = $add->crBotao();

        // Botão para remover linha de movimentação
        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('movimentacoes',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_del']   = $del->crBotao();

        return (object) $ret;
    }



    public function defCamposPrf(?object $dados = null, bool $show = false)
    {
        $ret = [];

        // Lista de perfis disponíveis no sistema
        $config = [];
        $config['Largura']      = 50;
        // $config['Leitura']      = $show;
        $config['Obrigatorio']  = true;
        $config['Selecionado']  = $dados->prf_id ?? [];

        $ret['prf_id'] = criaSelectRelativo(
            'cfg_perfil',
            'prf_id',
            'prf_nome',
            $dados->prf_id ?? [],
            3,
            'est_tipo_movimentacao_permissao',
            [],
            $config
        );

        return (object) $ret;
    }
}
