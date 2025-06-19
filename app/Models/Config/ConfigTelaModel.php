<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\LogMonModel;
use CodeIgniter\Model;
use ReflectionClass;
use ReflectionMethod;


class ConfigTelaModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'cfg_tela';
    protected $view             = 'vw_cfg_tela_relac';
    protected $primaryKey       = 'tel_id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'tel_id',
        'mod_id',
        'tel_nome',
        'tel_icone',
        'tel_controler',
        'tel_metodo',
        'tel_texto_botao',
        'tel_model',
        'tel_descricao',
        'tel_regras_gerais',
        'tel_regras_cadastro',
        'tel_lista',
        'tel_ident'
    ];


    protected $skipValidation   = true;

    // protected $createdField  = 'tel_criado';
    // protected $updatedField  = 'tel_alterado';
    protected $deletedField  = 'tel_excluido';

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
        $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
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
        $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
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
        $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    /**
     * getTelaId
     *
     * Retorna os dados da Tela, pelo ID informado
     *
     * @param bool $id
     * @return array
     */
    public function getTelaId($tel_id = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($tel_id) {
            $builder->where("tel_id", $tel_id);
        }
        $ret = $builder->get()->getResultArray();

        // debug($this->db->getLastQuery(), false);

        return $ret;
    }

    /**
     * getTelaSearch
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaSearch($termo)
    {
        $s_nome = ['tel_nome' => $termo];
        $s_contro = ['tel_controler' => $termo];
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->where($s_contro);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getTelaModulo
     *
     * Retorna os dados da Tela, pelo termo (nome) informado
     * Utilizado nas Seleções de Tela
     *
     * @param mixed $termo
     * @return array
     */
    public function getTelaModulo($modu)
    {
        $s_modu = ['mod_id' => $modu];
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        $builder->where($s_modu);
        // $builder->like($s_nome);
        // $builder->orLike($s_nome);
        $ret = $builder->get()->getResultArray();
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * getTelaPerfil
     *
     * Retorna os dados da Tela, pelo perfil (id) informado
     * Utilizado nas Seleções de Tela
     *  
     * @param mixed $perfil
     * @return array
     */
    public function getTelaPerfil($perfil = false)
    {
        $db = db_connect('default');
        $builder = $db->table('vw_cfg_tela_relac');
        $builder->select('*');
        if ($perfil) {
            // $builder->join('cfg_perfil_item pit', 'pit.tel_id = vw_cfg_tela_relac.tel_id 
            //                 AND pit.prf_id = ' . $perfil, 'left');
            $builder->where('prf_id', $perfil);
            $builder->orWhere('prf_id', null);
        }
        $builder->orderBy('mod_nome');
        $ret = $builder->get()->getResultArray();
        // debug($ret);
        // debug($this->db->getLastQuery(), false);
        return $ret;
    }

    /**
     * defCampos
     *
     * Retorna os Campos da Tela
     * Utilizado nas Seleções de Tela
     *  
     * @param array $dados
     * @param bool $show
     * @param string $tabela
     * @param string $view
     * @return array
     */
    public function defCampos($dados, $show = false, $tabela = '', $view = '')
    {
        $this->dicionario = new ConfigDicDadosModel();
        $this->usuario   = new ConfigUsuarioModel();
        $this->modulo    = new ConfigModuloModel();
        $this->common    = new CommonModel();

        $ret = [];
        $id         = new MyCampo('cfg_tela', 'tel_id');
        $id->valor  = array_key_exists('tel_id', $dados) ? $dados['tel_id'] : '';
        $ret['tel_id'] = $id->crOculto();

        $opc_mod = [];
        // if (isset($dados['mod_id'])){
        $opc_mods = $this->modulo->getModulo();
        $opc_mod  = array_column($opc_mods, 'mod_nome', 'mod_id');
        // }

        $modu               =  new MyCampo('cfg_tela', 'mod_id');
        $modu->label        = 'Módulo';
        $modu->largura      = 50;
        $modu->obrigatorio  = true;
        $modu->urlbusca     = base_url('buscas/busca_modulo');
        $modu->cadModal     = base_url('CfgModulo/add/modal=true');
        $modu->valor        = array_key_exists('mod_id', $dados) ? $dados['mod_id'] : '';
        $modu->selecionado  = $modu->valor;
        $modu->opcoes       = $opc_mod;
        $modu->leitura      = $show;
        $modu->dispForm     = '2col';
        $ret['tel_modulo']   = $modu->crSelBusca();

        $nome               = new MyCampo('cfg_tela', 'tel_nome');
        $nome->obrigatorio  = true;
        $nome->valor        = array_key_exists('tel_nome', $dados) ? $dados['tel_nome'] : '';
        $nome->dispForm     = '2col';
        $nome->leitura      = $show;
        $ret['tel_nome']    = $nome->crInput();

        // debug($dados,true);
        $iden               = new MyCampo('cfg_tela', 'tel_ident');
        $iden->obrigatorio  = true;
        $iden->valor        = array_key_exists('tel_ident', $dados) ? $dados['tel_ident'] : '';
        $iden->dispForm     = '2col';
        $iden->leitura      = $show;
        $iden->largura      = 15;
        $iden->minLength    = 2;
        $ret['tel_ident']    = $iden->crInput();

        $icon                = new MyCampo('cfg_tela', 'tel_icone');
        $icon->tipo         = 'icone';
        $icon->valor        = (isset($dados['tel_icone'])) ? $dados['tel_icone'] : '';
        $icon->dispForm     = '2col';
        $icon->leitura      = $show;
        $ret['tel_icon']     = $icon->crInput();

        $pasta    = APPPATH . '/Controllers';
        $arquivos = buscaArquivos($pasta, false, ['BaseController.php', 'Buscas.php', 'Logger.php']);
        sort($arquivos);

        $arqs               = array_combine(array_values($arquivos), array_values($arquivos));
        $cont               = new MyCampo('cfg_tela', 'tel_controler');
        $cont->obrigatorio  = true;
        $cont->valor        = array_key_exists('tel_controler', $dados) ? $dados['tel_controler'] : '';
        $cont->dispForm     = '2col';
        $cont->opcoes       = $arqs;
        $cont->selecionado  = $cont->valor;
        $cont->leitura      = $show;
        $ret['tel_cont']   = $cont->crSelect();

        $pasta = APPPATH . '/Models';
        $models = buscaArquivos($pasta, false, []);
        sort($models);
        $mods = array_combine(array_values($models), array_values($models));
        $mode               = new MyCampo('cfg_tela', 'tel_model');
        $mode->obrigatorio  = false;
        $mode->valor        = array_key_exists('tel_model', $dados) ? $dados['tel_model'] : '';
        $mode->dispForm     = '2col';
        $mode->opcoes       = $mods;
        $mode->selecionado  = $mode->valor;
        $mode->leitura      = $show;
        $ret['tel_mode']   =  $mode->crSelect();

        $txtb               = new MyCampo('cfg_tela', 'tel_texto_botao');
        $txtb->obrigatorio  = false;
        $txtb->dispForm     = '2col';
        $txtb->valor        = array_key_exists('tel_texto_botao', $dados) ? $dados['tel_texto_botao'] : '';
        $txtb->leitura      = $show;
        $ret['tel_txtb']   = $txtb->crInput();

        $desc           = new MyCampo('cfg_tela', 'tel_descricao');
        $desc->colunas  = 80;
        $desc->linhas   = 3;
        $desc->maximo   = 255;
        $desc->valor    = array_key_exists('tel_descricao', $dados)
            ? $dados['tel_descricao']
            : '';
        $desc->leitura      = $show;
        $ret['tel_desc'] = $desc->crTexto();

        $regg           = new MyCampo('cfg_tela', 'tel_regras_gerais');
        $regg->valor    = array_key_exists('tel_regras_gerais', $dados)
            ? $dados['tel_regras_gerais']
            : '';
        $regg->leitura      = $show;
        $ret['tel_regg'] = $regg->crEditor();

        $regc           = new MyCampo('cfg_tela', 'tel_regras_cadastro');
        $regc->valor    = array_key_exists('tel_regras_cadastro', $dados)
            ? $dados['tel_regras_cadastro']
            : '';
        $regc->leitura      = $show;
        $ret['tel_regc'] = $regc->crEditor();

        $tabe               =  new MyCampo();
        $tabe->largura      = 40;
        $tabe->dispForm     = '2col';
        $tabe->label        = 'Tabela Principal';
        $tabe->valor        = isset($tabela) ? $tabela : '';
        $tabe->leitura      = $show;
        $ret['tel_tabela']   = $tabe->crShow();

        $cvie               =  new MyCampo();
        $cvie->largura      = 40;
        $cvie->dispForm     = '2col';
        $cvie->label        = 'Visão Principal';
        $cvie->valor        = isset($view) ? $view : '';
        $cvie->leitura      = $show;
        $ret['tel_view']   = $cvie->crShow();

        // if ($tabela != '') {
        $camp           = new MyCampo();
        $camp->label    = 'Campos da Tabela';
        if ($tabela != '') {
            $campos_tab = $this->dicionario->getCampos($tabela);
            $camp->valor    = campos_tabela($campos_tab);
        }
        $ret['tel_camp'] = $camp->crShow();

        $camp           = new MyCampo();
        $camp->label    = 'Campos da Visão Principal';
        if ($view != '') {
            $campos_view = $this->dicionario->getCampos($view);
            $camp->valor    = campos_tabela($campos_view);
        }
        $ret['tel_camp_view'] = $camp->crShow();

        $trel           = new MyCampo();
        $trel->label    = 'Tabelas Relacionadas';
        if ($tabela != '') {
            $relac = $this->dicionario->getRelacionamentos($tabela);
            $trel->valor    = relacion_tabela($relac);
        }
        $ret['tel_trel'] = $trel->crShow();

        $meto           = new MyCampo();
        $meto->label    = 'Métodos';
        $meto->valor    = '';
        if (isset($dados['tel_controler'])) {
            $path = 'App\\Controllers\\Estoque\\';
            if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
                $path = 'App\\Controllers\\Config\\';
            } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
                $path = 'App\\Controllers\\Produto\\';
            } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
                $path = 'App\\Controllers\\Ocorrencia\\';
            }
            if (class_exists($path . $dados['tel_controler'])) {
                $class      = new ReflectionClass($path . $dados['tel_controler']);
                $methods    = $class->getMethods(ReflectionMethod::IS_PUBLIC);
                $meto->valor = metodosTela($methods, $class->name);
            } else {
                $meto->valor = 'Tela ainda não foi codificada!';
            }
        }
        $ret['tel_meto'] = $meto->crShow();

        if ($dados) {
            if (substr($dados['tel_controler'], 0, 3) == 'Cfg') {
                $fonte = verCodigo('Controllers/Config/' . $dados['tel_controler']);
            } else if (substr($dados['tel_controler'], 0, 3) == 'Pro') {
                $fonte = verCodigo('Controllers/Produto/' . $dados['tel_controler']);
            } else if (substr($dados['tel_controler'], 0, 3) == 'Oco') {
                $fonte = verCodigo('Controllers/Ocorrencia/' . $dados['tel_controler']);
            } else {
                $fonte = verCodigo('Controllers/Estoque/' . $dados['tel_controler']);
            }

            $codi           = new MyCampo();
            $codi->label    = 'Código Fonte';
            $codi->valor    = '<pre>' . $fonte . '</pre>';
            $ret['tel_codi'] = $codi->crShow();
        }

        return $ret;
    }
}
