<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;

class CfgMenu extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $menu;
    public $dicionario;
    public $tela;
    public $modulo;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->menu      = new ConfigMenuModel();
        $this->tela      = new ConfigTelaModel();
        $this->modulo      = new ConfigModuloModel();
        $this->dicionario     = new ConfigDicDadosModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'men_id,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');

        $order          = new MyCampo();
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-arrow-down-short-wide" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Ordenar</div>';
        $order->place    = 'Ordenar o Menu';
        $order->funcChan = 'redireciona(\'CfgMenu/ordenar/\')';
        $order->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_order = $order->crBotao();

        $this->data['botao'] = $this->bt_order;

        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $dados_menu = $this->menu->getMenu();
        $menus = [
            'data' => montaListaColunas($this->data, 'men_id', $dados_menu, 'men_etiqueta'),
        ];

        echo json_encode($menus);
    }


    public function ordenar()
    {
        // debug('Entrei aqui', true);
        $it_menu     =  montaMenu(1, false, true);
        $this->data['desc_metodo'] = 'Ordenação de ';
        $this->data['it_menu'] = $it_menu;
        $this->data['destino']    = 'storeOrd';

        echo view('vw_menu_ordenar', $this->data);
    }

    public function add()
    {
        $this->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->men_id;
        $campos[0][1] = $this->men_hier;
        $campos[0][2] = $this->men_mpai;
        $campos[0][3] = $this->men_subm;
        $campos[0][4] = $this->men_modu;
        $campos[0][5] = $this->men_tela;
        $campos[0][6] = $this->men_etiq;
        $campos[0][7] = $this->men_icon;

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }
    public function show($id){
        $this->edit($id, true);
    }

    public function edit($id,$show = false)
    {
        $dados_menu = $this->menu->getMenu($id)[0];
        $this->defCampos($dados_menu, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->men_id;
        $campos[0][1] = $this->men_hier;
        $campos[0][2] = $this->men_mpai;
        $campos[0][3] = $this->men_subm;
        $campos[0][4] = $this->men_modu;
        $campos[0][5] = $this->men_tela;
        $campos[0][6] = $this->men_etiq;
        $campos[0][7] = $this->men_icon;

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        $this->data['script']    = "<script>acertaCamposCadMenu('men_hierarquia');</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_menu', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->menu->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Menu Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Menu, Verifique!<br><br>';
            $ret['msg'] .= 'Este Menu possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    public function defCampos($dados = false)
    {
        // debug($dados, false);
        $id             = new MyCampo('cfg_menu', 'men_id');
        $id->valor      = (isset($dados['men_id'])) ? $dados['men_id'] : '';
        $this->men_id   = $id->crOculto();

        $hierarquia[1] = 'Raíz do Menu';
        $hierarquia[2] = 'Menu';
        $hierarquia[3] = 'Sub-menu';
        $hierarquia[4] = 'Opção do Menu';

        $hier           = new MyCampo('cfg_menu', 'men_hierarquia');
        $hier->objeto   = 'select';
        $hier->obrigatorio = true;
        $hier->valor        = (isset($dados['men_hierarquia'])) ? $dados['men_hierarquia'] : "";
        $hier->selecionado  = $hier->valor;
        $hier->urlbusca     = base_url('buscas/busca_hierarquia');
        $hier->opcoes       = $hierarquia;
        $hier->funcChan     = "acertaCamposCadMenu('men_hierarquia')";
        $hier->largura      = 50;
        $this->men_hier     = $hier->crSelect();

        $menus = $this->menu->getMenuPai();
        $menu_pai = array_column($menus, 'men_etiqueta', 'men_id');
        $sub_menu = [];
        if (isset($dados['men_menupai_id'])) {
            $submenus = $this->menu->getSubMenu($dados['men_menupai_id']);
            $sub_menu = array_column($submenus, 'men_etiqueta', 'men_id');
        }

        $mpai           = new MyCampo('cfg_menu', 'men_menupai_id');
        $mpai->valor        = (isset($dados['men_menupai_id'])) ? $dados['men_menupai_id'] : "";
        $mpai->selecionado  = $mpai->valor;
        $mpai->urlbusca     = base_url('buscas/busca_menu_pai');
        $mpai->pai          = 'men_hierarquia';
        $mpai->opcoes       = $menu_pai;
        $mpai->largura      = 50;
        $this->men_mpai = $mpai->crSelect();

        $subm           = new MyCampo('cfg_menu', 'men_submenu_id');
        $subm->valor        = (isset($dados['men_submenu_id'])) ? $dados['men_submenu_id'] : "";
        $subm->selecionado  = $subm->valor;
        $subm->urlbusca     = base_url('buscas/busca_submenu');
        $subm->pai          = 'men_menupai_id';
        $subm->opcoes       = $sub_menu;
        $subm->largura      = 50;
        $this->men_subm = $subm->crDepende();

        $etiq           = new MyCampo('cfg_menu', 'men_etiqueta');
        $etiq->obrigatorio = true;
        $etiq->valor    = (isset($dados['men_etiqueta'])) ? $dados['men_etiqueta'] : '';
        $this->men_etiq = $etiq->crInput();

        $icon           = new MyCampo('cfg_menu', 'men_icone');
        $icon->obrigatorio = true;
        $icon->tipo     = 'icone';
        $icon->valor    = (isset($dados['men_icone'])) ? $dados['men_icone'] : '';
        $this->men_icon = $icon->crInput();

        // $moduloss = $this->modulo->getModulo((isset($dados['mod_id'])) ? $dados['mod_id'] : false);
        $moduloss = $this->modulo->getModulo();
        $modulos = array_column($moduloss, 'mod_nome', 'mod_id');

        $modu           = new MyCampo('cfg_menu', 'mod_id');
        $modu->objeto   = 'selbusca';
        $modu->valor    = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $modu->selecionado  = $modu->valor;
        $modu->urlbusca     = base_url('buscas/busca_modulo');
        $modu->funcChan     = "busca_atributos('modulo','mod_id', 'men_etiqueta','men_icone')";
        $modu->opcoes       = $modulos;
        $modu->largura      = 50;
        $this->men_modu = $modu->crSelbusca();

        // $telass = $this->tela->getTelaId((isset($dados['tel_id'])) ? $dados['tel_id'] : false);
        $telass = $this->tela->getTelaId();
        $telas = array_column($telass, 'tel_nome', 'tel_id');

        $tela           = new MyCampo('cfg_menu', 'tel_id');
        $tela->obrigatorio  = true;
        $tela->valor        = (isset($dados['tel_id'])) ? $dados['tel_id'] : '';
        $tela->selecionado  = $tela->valor;
        $tela->urlbusca     = base_url('buscas/busca_tela');
        $tela->funcChan     = "busca_atributos('tela', 'tel_id', 'men_etiqueta','men_icone')";
        $tela->opcoes       = $telas;
        $tela->largura      = 50;
        $this->men_tela     = $tela->crSelect();
    }

    public function storeOrd()
    {
        $req = $this->request->getPost();
        $order  = 1;
        $ord[1] = 100;
        $ord[2] = 200;
        $ord_ = [];
        $hierant = '';
        foreach ($req as $key => $value) {
            $menu = $this->menu->getMenu($value)[0];
            // debug($menu,true);
            $hier = $menu['men_hierarquia'];
            if($hier == 1){
                $order = $ord[$hier];
                $ord[$hier] =$ord[$hier]+10;
            } else if($hier == 2){
                $order = $ord[$hier];
                $ord[$hier] = $ord[$hier] + 100;
            } else if($hier == 3){
                $pai = $menu['men_menupai_id'];
                $menupai = $this->menu->getMenu($pai)[0];                
                // debug($menupai['men_order']);
                if(!isset($ord_[$menupai['men_order']])){
                    $ord_[$menupai['men_order']] = 10;
                }
                if($hier != $hierant){
                    $ord_[$menupai['men_order']] += 10;
                    $hierant = $hier;
                }
                // debug($ord_[$menupai['men_order']]);
                $order = $menupai['men_order'] + $ord_[$menupai['men_order']];
                // debug($order,true);
                $ord_[$menupai['men_order']] = $ord_[$menupai['men_order']] + 10;
            } else if($hier == 4){
                $sub = $menu['men_submenu_id'];
                if($sub > 0){
                    $menupai = $this->menu->getMenu($sub)[0];
                } else {
                    $pai = $menu['men_menupai_id'];
                    $menupai = $this->menu->getMenu($pai)[0];
                }
                if(!isset($ord_[$menupai['men_order']])){
                    $ord_[$menupai['men_order']] = 1;
                }
                $order = $menupai['men_order'] + $ord_[$menupai['men_order']];
                $ord_[$menupai['men_order']] = $ord_[$menupai['men_order']] + 1;
            }
            $updt = [
                'men_order' => $order
            ];
            $this->menu->update($value, $updt);
        }
        // debug($ord_, true);
        $ret['erro'] = false;
        $ret['msg']  = 'Menu Reordenado com Sucesso!!!';
        session()->setFlashdata('msg', $ret['msg']);
        $ret['url']  = site_url($this->data['controler']);
        echo json_encode($ret);
    }

    public function store()
    {
        $dados_men = $this->request->getPost();
        // debug($dados_men, true);
        $save_men = [
            'men_id'            => $dados_men['men_id'],
            'men_hierarquia'    => $dados_men['men_hierarquia'],
            'men_menupai_id'    => ($dados_men['men_menupai_id'] != '') ? $dados_men['men_menupai_id'] : null,
            'men_submenu_id'    => ($dados_men['men_submenu_id'] != '') ? $dados_men['men_submenu_id'] : null,
            'mod_id'            => array_key_exists('mod_id', $dados_men) ? $dados_men['mod_id'] : null,
            'tel_id'            => (isset($dados_men['tel_id']) && $dados_men['tel_id'] != '') ? $dados_men['tel_id'] : null,
            'men_etiqueta'      => $dados_men['men_etiqueta'],
            'men_icone'         => $dados_men['men_icone'],
        ];
        if($dados_men['men_hierarquia'] == 1){
            $somador = 1;
            $ordem = $this->menu->getProximo($dados_men['men_hierarquia'], false, false)[0];
        } else if($dados_men['men_hierarquia'] == 4){
            $somador = 1;
            if($dados_men['men_submenu_id'] != null && $dados_men['men_submenu_id'] > 0){
                $modulo = $this->menu->getMenu($dados_men['men_submenu_id'])[0];
                // debug($modulo);
                $save_men['mod_id'] = $modulo['mod_id'];
                $ordem = $this->menu->getProximo(false, $dados_men['men_submenu_id'], false);
                if(count($ordem) == 0){
                    $ordem['ordem'] = $modulo['men_order'];
                } else {
                    $ordem = $ordem[0];
                }    
            } else { // se não tem submenu
                $modulo = $this->menu->getMenu($dados_men['men_menupai_id'])[0];
                $save_men['mod_id'] = $modulo['mod_id'];
                $ordem = $this->menu->getProximo(false, false, $dados_men['men_menupai_id']);                
                if(count($ordem) == 0){
                    $ordem['ordem'] = $modulo['men_order'];
                } else {
                    $ordem = $ordem[0];
                }    
            }
        } else if($dados_men['men_hierarquia'] == 3){
            $modulo = $this->menu->getMenu($dados_men['men_menupai_id'])[0];
            $save_men['mod_id'] = $modulo['mod_id'];

            $ordem = $this->menu->getProximo(false, false, $dados_men['men_menupai_id']);
            if(count($ordem) == 0){
                $ordem['ordem'] = $modulo['men_order'];
            } else {
                $ordem = $ordem[0];
            }
            $somador = 10;
        } if($dados_men['men_hierarquia'] == 2){
            $ordem = $this->menu->getProximo($dados_men['men_hierarquia'], false, false)[0];
            $somador = 100;
        }
        $save_men['men_order'] = $ordem['ordem'] + $somador;
        // debug($save_men, true);

        $ret = [];
        if ($this->menu->save($save_men)) {
            $ret['erro'] = false;
            $ret['msg']  = 'Menu gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Menu, Verifique!';
        }
        echo json_encode($ret);
    }
}
