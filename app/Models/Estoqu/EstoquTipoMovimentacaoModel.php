<?php

namespace App\Models\Estoqu;

use App\Libraries\MyCampo;
use App\Models\Config\ConfigPerfilModel;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class EstoquTipoMovimentacaoModel extends Model
{
    protected $DBGroup          = 'dbEstoque';
    protected $table            = 'est_tipo_movimentacao';
    protected $view             = 'vw_est_tipo_movimentacao_relac_lista';
    protected $primaryKey       = 'tmo_id';
    protected $useAutoIncremodt = true;


    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'tmo_id',
        'tmo_nome',
        'tmo_acumulador',
        'tmo_conferencia',
        'tmo_semestoque',
        'tmo_transacao_erp',
        'tmo_atendeautomatico',
        'tmo_transacao_erp_entrada',
        'tmo_transacao_erp_saida',
        'tmo_entrefiliais',
        'tmo_ativo',
        'tmo_excluido',
        'tmo_estoquepadrao',

    ];

    protected $deletedField  = 'tmo_excluido';

    protected $validationRules = [
        'tmo_nome' => 'required|min_length[5]|max_length[50]',
        'tmo_acumulador' => 'required'
    ];

    protected $validationMessages = [
        'tmo_nome' => [
            'required' => 'O campo nome é Obrigatório.',
            'min_length' => 'Digite pelo menos 5 Caracteres.',
            'max_length' => 'Número de Caracteres excedido.',
            'isUniqueValue' =>  '8'
        ],

        'tmo_acumulador' => [
            'required' => 'O campo acumulador é Obrigatório'
        ]

    ];

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;


    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getTipoMovimentacao($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_relac_lista');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        $builder->orderBy('tmo_ativo, tmo_nome');
        return $builder->get()->getResultArray();
    }

    public function getTipoMovimentacaoSearch($termo)
    {
        // TODO implementar

        $array = ['tmo_nome' => $termo . '%'];
        $db = db_connect('dbEstoque');
        $builder = $db->table('vw_est_tipo_movimentacao_relac_lista');
        $builder->select('*');
        $builder->like($array);

        return $builder->get()->getResultArray();
    }

    public function getTipoMovimentacaoMovimentos($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_tipo_movimentacao_movimento');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        return $builder->get()->getResultArray();
    }

    public function getTipoMovimentacaoPermissao($tmo_id = false)
    {
        $db = db_connect('dbEstoque');
        $builder = $db->table('est_tipo_movimentacao_permissao');
        $builder->select('*');
        if ($tmo_id) {
            $builder->where('tmo_id', $tmo_id);
        }
        return $builder->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $id           =  new MyCampo('est_tipo_movimentacao', 'tmo_id', false);
        $id->valor    = (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $id->leitura  = $show;
        $ret['tmo_id']    = $id->crOculto();

        $nome                 = new MyCampo('est_tipo_movimentacao', 'tmo_nome', false);
        $nome->valor          = (isset($dados['tmo_nome'])) ? $dados['tmo_nome'] : '';
        $nome->leitura        = $show;
        $nome->obrigatorio    = true;
        $nome->dispForm       = 'col-12';
        $ret['tmo_nome']          = $nome->crInput();

        $op_ac['E'] = 'Entrada';
        $op_ac['S'] = 'Saída';
        $op_ac['T'] = 'Transferência';
        $op_ac['N'] = 'Sem Movimentacão';

        $acmu                 = new MyCampo('est_tipo_movimentacao', 'tmo_acumulador', false);
        $acmu->valor          = (isset($dados['tmo_acumulador'])) ? $dados['tmo_acumulador'] : '';
        $acmu->selecionado    = $acmu->valor;
        $acmu->opcoes         = $op_ac;
        $acmu->leitura        = $show;
        $acmu->funcChan       = "acertaObrigatorio('tmo_acumulador')";
        $acmu->largura        = 25;
        $acmu->dispForm       = 'col-6';
        $acmu->obrigatorio    = true;
        $ret['tmo_acumulador'] = $acmu->crSelect();

        $conf                 = new MyCampo('est_tipo_movimentacao', 'tmo_conferencia', false);
        $conf->valor          = (isset($dados['tmo_conferencia'])) ? $dados['tmo_conferencia'] : 'N';
        $conf->leitura        = $show;
        $conf->opcoes         = $simnao;
        $conf->selecionado    = $conf->valor;
        $conf->dispForm       = 'col-6';
        $ret['tmo_conferencia']          = $conf->cr2opcoes();

        $transacoes = new EstoquTransacaoModel();
        $lst_transacoes = $transacoes->getTransacao();
        $ini = array(
            'tns_codtns' => '-1',
            'tns_germvp' => '',
            'tns_dettns' => '',
            'tns_codDescricao' => '',
        );
        array_unshift($lst_transacoes, $ini);
        $opc_tns = array_column($lst_transacoes, 'tns_codDescricao', 'tns_codtns');

        $trer                 = new MyCampo('est_tipo_movimentacao', 'tmo_transacao_erp', false);
        $trer->valor          = (isset($dados['tmo_transacao_erp'])) ? $dados['tmo_transacao_erp'] : '';
        $trer->selecionado    = $trer->valor;
        $trer->opcoes         = $opc_tns;
        $trer->leitura        = $show;
        $trer->largura        = 50;
        $trer->dispForm       = 'col-6';
        $ret['tmo_transacao_erp']          = $trer->crSelect();

        $tree                 = new MyCampo('est_tipo_movimentacao', 'tmo_transacao_erp_entrada', false);
        $tree->valor          = (isset($dados['tmo_transacao_erp_entrada'])) ? $dados['tmo_transacao_erp_entrada'] : '';
        $tree->selecionado    = $tree->valor;
        $tree->opcoes         = $opc_tns;
        $tree->leitura        = $show;
        $tree->largura        = 50;
        $tree->dispForm       = 'col-6';
        $ret['tmo_transacao_erp_entrada']          = $tree->crSelect();

        $tres                   = new MyCampo('est_tipo_movimentacao', 'tmo_transacao_erp_saida', false);
        $tres->valor            = (isset($dados['tmo_transacao_erp_saida'])) ? $dados['tmo_transacao_erp_saida'] : '';
        $tres->selecionado      = $tres->valor;
        $tres->opcoes           = $opc_tns;
        $tres->leitura          = $show;
        $tres->largura          = 50;
        $tres->dispForm         = 'col-6';
        $ret['tmo_transacao_erp_saida']            = $tres->crSelect();

        $atea                 = new MyCampo('est_tipo_movimentacao', 'tmo_atendeautomatico', false);
        $atea->valor          = (isset($dados['tmo_atendeautomatico'])) ? $dados['tmo_atendeautomatico'] : 'N';
        $atea->leitura        = $show;
        $atea->selecionado    = $atea->valor;
        $atea->opcoes         = $simnao;
        $atea->dispForm       = 'col-6';
        $ret['tmo_atendeautomatico']          = $atea->cr2opcoes();

        $seme                 = new MyCampo('est_tipo_movimentacao', 'tmo_semestoque', false);
        $seme->valor          = (isset($dados['tmo_semestoque'])) ? $dados['tmo_semestoque'] : 'N';
        $seme->leitura        = $show;
        $seme->selecionado    = $seme->valor;
        $seme->opcoes         = $simnao;
        $seme->dispForm       = 'col-6';
        $ret['tmo_semestoque']          = $seme->cr2opcoes();

        $entf               = new MyCampo('est_tipo_movimentacao', 'tmo_entrefiliais', false);
        $entf->valor        = (isset($dados['tmo_entrefiliais'])) ? $dados['tmo_entrefiliais'] : 'N';
        $entf->leitura      = $show;
        $entf->selecionado  = $entf->valor;
        $entf->opcoes       = $simnao;
        $entf->funcChan     = "acertaObrigatorio('tmo_acumulador')";
        $entf->dispForm     = "col-6";
        $ret['tmo_entrefiliais'] = $entf->cr2opcoes();

        $padf               = new MyCampo('est_tipo_movimentacao', 'tmo_estoquepadrao', false);
        $padf->valor        = (isset($dados['tmo_estoquepadrao'])) ? $dados['tmo_estoquepadrao'] : 'N';
        $padf->leitura      = $show;
        $padf->selecionado  = $padf->valor;
        $padf->opcoes       = $simnao;
        $padf->dispForm     = "col-6";
        $ret['tmo_estoquepadrao'] = $padf->cr2opcoes();

        return $ret;
    }


    public function defCamposMov($dados = false, $pos = 0, $show = false)
    {
        $ret = [];
        $id           =  new MyCampo('est_tipo_movimentacao_movimento', 'tmm_id', false);
        $id->valor    = (isset($dados['tmm_id'])) ? $dados['tmm_id'] : '';
        // $id->nome     = $id->nome . "[" . $pos . "]";
        // $id->id       = $id->id . "[" . $pos . "]";
        $id->leitura  = $show;
        $id->ordem    = $pos;
        $ret['tmm_id'] = $id->crOculto();

        $deposito         = new EstoquDepositoModel();
        $lst_depositos    = $deposito->getDeposito();
        $opc_dep          = array_column($lst_depositos, 'dep_desDep', 'dep_codDep');

        $depo                 = new MyCampo('est_tipo_movimentacao_movimento', 'tmm_deposito_origem', false);
        // $depo->nome           = $depo->id = "tmm_deposito_origem[$pos]";
        $depo->valor          = (isset($dados['tmm_deposito_origem'])) ? $dados['tmm_deposito_origem'] : '';
        $depo->selecionado    = [$depo->valor];
        $depo->opcoes         = $opc_dep;
        $depo->leitura        = $show;
        $depo->ordem          = $pos;
        $depo->dispForm       = 'col-6';
        $depo->largura        = 50;
        // debug($depo);
        $ret['tmm_deposito_origem'] = $depo->crSelect();

        $depd         = new MyCampo('est_tipo_movimentacao_movimento', 'tmm_deposito_destino', false);
        // $depd->nome          = $depd->id = "tmm_deposito_destino[$pos]";
        $depd->valor         = (isset($dados['tmm_deposito_destino'])) ? $dados['tmm_deposito_destino'] : '';
        $depd->selecionado   = [$depd->valor];
        $depd->opcoes        = $opc_dep;
        $depd->leitura       = $show;
        $depd->ordem         = $pos;
        $depd->dispForm      = 'col-6';
        $depd->largura       = 50;
        $depd->pai           = "tmm_deposito_origem[$pos]";
        $depd->urlbusca      = base_url('buscas/busca_dep_destino');
        // debug($depd);
        $ret['tmm_deposito_destino'] = $depd->crDepende();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("TipoMovimentacao/addCampo/") . "','movimentacoes',this)";
        $ret['bt_add']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('movimentacoes',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_del']   = $del->crBotao();

        return $ret;
    }

    public function defCamposPrf($dados = false, $show = false)
    {
        $ret = [];

        $perfis           = new ConfigPerfilModel();
        $lst_perfis       = $perfis->getPerfil();
        $opc_prf          = array_column($lst_perfis, 'prf_nome', 'prf_id');

        // debug($dados);
        $prf_id         = new MyCampo('est_tipo_movimentacao_permissao', 'prf_id', false);
        $prf_id->valor         = (isset($dados['prf_id'])) ? $dados['prf_id'][0] : '';
        $prf_id->selecionado   = (isset($dados['prf_id'])) ? $dados['prf_id'] : [];
        $prf_id->opcoes        = $opc_prf;
        $prf_id->leitura       = $show;
        $prf_id->largura       = 50;
        $prf_id->obrigatorio   = true;
        $ret['prf_id'] = $prf_id->crMultiple();

        return $ret;
    }
}
