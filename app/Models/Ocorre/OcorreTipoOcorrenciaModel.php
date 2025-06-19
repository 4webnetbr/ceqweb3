<?php

namespace App\Models\Ocorre;

use App\Controllers\Estoque\TipoMovimentacao;
use App\Controllers\Ocorrencia\OcoTipoAcao;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\LogMonModel;
use App\Models\Produt\ProdutClasseModel;
use CodeIgniter\Model;

class OcorreTipoOcorrenciaModel extends Model
{
    protected $DBGroup          = 'dbOcorrencia';
    protected $table            = 'oco_tipo_ocorrencia';
    protected $view             = 'oco_tipo_ocorrencia';
    protected $primaryKey       = 'tpo_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'tpo_id',
        'tpo_nome',
        'tpo_ativo'
    ];

    protected $validationRules = [
        'tpo_nome' => 'required|max_length[50]|min_length[5]|isUniqueValue[dbOcorrencia.oco_tipo_ocorrencia.tpo_nome, tpo_id]',
        'toc_rotulo' => 'required|min_lenght[5]|max_lenght[30]'
    ];

    protected $validationMessages = [
        'tpo_nome' => [
            'required' => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'isUniqueValue' => '8',
            'max_lenght'  => 'O Campo deve Conter no Máximo 50 Caracteres',
            'min_lenght' => 'O Campo Devente Conter no Minimo 5 Caracteres',
        ],
        'toc_rotulo' => [
            'max_lenght'  => 'O Campo deve Conter no Máximo 30 Caracteres',
            'min_lenght' => 'O Campo Devente Conter no Minimo 5 Caracteres',
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

    public function getTipoOcorrencia($tpo_id = false)
    {
        $this->builder()->select('*');
        if ($tpo_id) {
            $this->builder()->where('tpo_id', $tpo_id);
        }
        $this->orderBy('tpo_ativo, tpo_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getTipoOcorrenciaSearch($termo)
    {
        $array = ['tpo_nome' => $termo . '%'];
        $this->builder()->select(['tpo_id', 'tpo_nome']);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $mid            = new MyCampo('oco_tipo_ocorrencia', 'tpo_id');
        $mid->valor     = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $ret['tpo_id']   = $mid->crOculto();

        $nome            =  new MyCampo('oco_tipo_ocorrencia', 'tpo_nome');
        $nome->valor     = (isset($dados['tpo_nome'])) ? $dados['tpo_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura   = $show;
        $ret['tpo_nome'] = $nome->crInput();

        $classes = new ProdutClasseModel();
        $lst_classes = $classes->getClasse();
        $opc_classes = array_column($lst_classes, 'cla_nome', 'cla_id');

        $cla_id = new MyCampo('oco_tpo_pro_classe', 'cla_id', false);
        $cla_id->valor          = (isset($dados['cla_id'])) ? $dados['cla_id'][0] : '';
        $cla_id->selecionado    = (isset($dados['cla_id'])) ? $dados['cla_id'] : [];
        $cla_id->opcoes         = $opc_classes;
        $cla_id->largura        = 50;
        $ret['cla_id'] = $cla_id->crMultiple();
        return $ret;
    }

    public function defCamposTelasAplicaveis($dados = false, $show = false)
    {
        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_tpo_cfg_tela', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->obrigatorio    = true;
        $mod_id->largura        = 50;
        $mod_id->dispForm       = '2col';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_tpo_cfg_tela', 'tel_id');
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->obrigatorio  = true;
        $tela->largura      = 50;
        $tela->dispForm     = '2col';
        $tela->pai          = 'mod_id';
        $ret['tel_id']     = $tela->crDepende();
        return $ret;
    }

    public function defCamposAcao($dados = false, $show = false)
    {
        $ret = [];

        $tipoacao = new OcorreTipoAcaoModel;
        $lst_tipoacao = $tipoacao->getTipoAcao();
        $opc_tipoacao = array_column($lst_tipoacao, 'tpa_nome', 'tpa_id');

        $tpa_id               =  new MyCampo('oco_tpo_acao', 'tpa_id');
        $tpa_id->obrigatorio  =  true;
        $tpa_id->valor        =  (isset($dados['tpa_id'])) ? $dados['tpa_id'] : '';
        $tpa_id->dispForm     =  '2col';
        $tpa_id->largura      =  50;
        $tpa_id->opcoes       =  $opc_tipoacao;
        $tpa_id->selecionado  =  $tpa_id->valor;
        $ret['tpa_id']        =  $tpa_id->crSelect();


        $tpoacao = new EstoquTipoMovimentacaoModel;
        $lst_acao = $tpoacao->getTipoMovimentacao();
        $opc_acao = array_column($lst_acao, 'tmo_nome', 'tmo_id');

        $tmo_id               =  new MyCampo('oco_tpo_acao', 'tmo_id');
        $tmo_id->obrigatorio  =  true;
        $tmo_id->valor        =  (isset($dados['tmo_id'])) ? $dados['tmo_id'] : '';
        $tmo_id->dispForm     =  '2col';
        $tmo_id->largura      =  50;
        $tmo_id->opcoes       =  $opc_acao;
        $tmo_id->selecionado  =  $tmo_id->valor;
        $ret['tmo_id']        =  $tmo_id->crSelect();

        $modulos = new ConfigModuloModel();
        $lst_modulos = $modulos->getModulo();
        $opc_modulos = array_column($lst_modulos, 'mod_nome', 'mod_id');

        $mod_id                 = new MyCampo('oco_tpo_cfg_tela', 'mod_id', false);
        $mod_id->valor          = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $mod_id->selecionado    = $mod_id->valor;
        $mod_id->opcoes         = $opc_modulos;
        $mod_id->obrigatorio    = true;
        $mod_id->largura        = 50;
        $mod_id->dispForm       = '2col';
        $ret['mod_id'] = $mod_id->crSelect();

        $telas  = new ConfigTelaModel();
        $lst_telas = $telas->getTelaId();
        $opc_telas = array_column($lst_telas, 'tel_nome', 'tel_id');

        $tela               = new MyCampo('oco_tpo_acao', 'tel_id');
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela_modulo');
        $tela->opcoes       = $opc_telas;
        $tela->obrigatorio  = true;
        $tela->largura      = 50;
        $tela->dispForm     = '2col';
        $tela->pai          = 'mod_id';
        $ret['tel_id']     = $tela->crDepende();

        return $ret;
    }

    public function defCamposParaMostrar($dados = false, $show = false)
    {
        $ret = [];

        $pegatela = new ConfigTelaModel();
        $lst_pegatela = $pegatela->getTelaId();
        $opc_tela = array_column($lst_pegatela, 'tel_nome', 'tel_id');

        $tel_id                 = new MyCampo('oco_tpo_campos', 'tel_id', false);
        $tel_id->valor          = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tel_id->selecionado    = $tel_id->valor;
        $tel_id->opcoes         = $opc_tela;
        $tel_id->obrigatorio    = true;
        $tel_id->largura        = 50;
        $tel_id->dispForm       = '2col';
        $ret['tel_id'] = $tel_id->crSelect();

        $tipoCampo  = new ConfigTelaModel();
        $lst_campo = $tipoCampo->getTelaId();
        $opc_campo = array_column($lst_campo, 'toc_nome', 'toc_id');

        $toc_campo               = new MyCampo('oco_tpo_campos', 'toc_campo');
        $toc_campo->valor        = (isset($dados['toc_campo'])) ? $dados['toc_campo'] : '';
        $toc_campo->selecionado  = $toc_campo->valor;
        $toc_campo->opcoes       = $opc_campo;
        $toc_campo->urlbusca     = base_url('buscas/busca_tela_modulo');
        $toc_campo->obrigatorio  = true;
        $toc_campo->largura      = 50;
        $toc_campo->dispForm     = '2col';
        $toc_campo->pai          = 'tel_id';
        $ret['toc_id']     = $toc_campo->crDepende();

        $toc_rotulo                 =  new MyCampo('oco_tpo_campos', 'toc_rotulo');
        $toc_rotulo->valor          = (isset($dados['toc_rotulo'])) ? $dados['toc_rotulo'] : '';
        $toc_rotulo->obrigatorio    =  true;
        $toc_rotulo->leitura        = $show;
        $toc_rotulo->largura        = 50;
        $toc_rotulo->dispForm       = '2col';
        $ret['toc_rotulo']          = $toc_rotulo->crInput();
        return $ret;
    }

    public function defPermissoes($dados = false, $show = false)
    {
        $ret = [];

        $pegPerfil               = new ConfigPerfilModel;
        $lst_permissao           = $pegPerfil->getPerfil();
        $opc_prf                 = array_column($lst_permissao, 'prf_nome', 'prf_id');

        $top_id                = new MyCampo('oco_tpo_permissao', 'prf_id', false);
        $top_id->valor         = (isset($dados['prf_id'])) ? $dados['prf_id'][0] : '';
        $top_id->selecionado   = (isset($dados['prf_id'])) ? $dados['prf_id'] : [];
        $top_id->opcoes        = $opc_prf;
        $top_id->leitura       = $show;
        $top_id->largura       = 50;
        $top_id->obrigatorio   = true;
        $ret['prf_id']         = $top_id->crMultiple();
        return $ret;
    }
}
