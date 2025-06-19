<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutProdutoModel;

class Buscas extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo;
    public $tela;
    public $usuario;
    public $admDados;


    public function __construct()
    {
        $this->menu                 = new ConfigMenuModel();
        $this->modulo                 = new ConfigModuloModel();
        $this->tela                 = new ConfigTelaModel();
        $this->usuario              = new ConfigUsuarioModel();
        $this->admDados             = new ConfigDicDadosModel();
    }

    public function busca_hierarquia()
    {

        $ret    = [];
        if ($_REQUEST['campo']) {
            $data = $_REQUEST;
            $termo              = $data['campo'][0]['id_dep'];
            if ($termo == 1) {
                $hierarquia[2] = 'Pai';
                $hierarquia[3] = 'Filho';
            } else {
                $hierarquia[1] = 'Órfão';
                $hierarquia[3] = 'Filho';
                $hierarquia[4] = 'Neto';
            }
        }
        echo json_encode($hierarquia);
    }
    public function busca_menu_pai()
    {
        $menus = $this->menu->getMenuPai();
        $menu_pai = array_column($menus, 'men_etiqueta', 'men_id');
        echo json_encode($menu_pai);
    }

    public function busca_submenu()
    {
        $ret = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $submenus = $this->menu->getSubMenu($termo);
            if (sizeof($submenus) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'SubMenu não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($submenus); $c++) {
                    $ret[$c]['id']      = $submenus[$c]['men_id'];
                    $ret[$c]['text']    = $submenus[$c]['men_etiqueta'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_modulo()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulosSearch($termo);
            if (sizeof($modulos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($modulos); $c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_modulo_id()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulo($termo);
            if (sizeof($modulos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($modulos); $c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menu()
    {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (count($menus) > 0) {
            $menu = array_column($menus, 'men_nome', 'men_id');
            $menu_ret = json_encode($menu);
            echo $menu_ret;
        }
        exit;
    }

    public function busca_tela_modulo()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $telas = $this->tela->getTelaModulo($termo);
            if (sizeof($telas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($telas); $c++) {
                    $ret[$c]['id']      = $telas[$c]['tel_id'];
                    $ret[$c]['text']    = $telas[$c]['tel_nome'];
                    $ret[$c]['icone']    = $telas[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_tela()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $telas = $this->tela->getTelaSearch($termo);
            if (sizeof($telas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($telas); $c++) {
                    $ret[$c]['id']      = $telas[$c]['tel_id'];
                    $ret[$c]['text']    = $telas[$c]['tel_nome'];
                    $ret[$c]['icone']    = $telas[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_tela_id()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $class = $this->tela->getTelaId($termo);
            if (sizeof($class) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($class); $c++) {
                    $ret[$c]['id']      = $class[$c]['tel_id'];
                    $ret[$c]['text']    = $class[$c]['tel_nome'];
                    $ret[$c]['icone']    = $class[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menupai()
    {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (count($menus) > 0) {
            $menu = array_column($menus, 'men_nome', 'men_id');
            $menu_ret = json_encode($menu);
            echo $menu_ret;
        }
        exit;
    }

    public function busca_tabela()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $tabelas         = $this->admDados->getTabelaSearch($termo);
            if (sizeof($tabelas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tabela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($tabelas); $c++) {
                    $ret[$c]['id'] = $tabelas[$c]['table_name'];
                    $ret[$c]['text'] = $tabelas[$c]['table_name'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function busca_familia()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $famil = new ProdutFamiliaModel();
            $familias         = $famil->getFamiliaOrigem($termo);
            if (sizeof($familias) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Família não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($familias); $c++) {
                    $ret[$c]['id'] = $familias[$c]['fam_codFam'];
                    $ret[$c]['text'] = $familias[$c]['fam_codDescricao'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function buscaProdutoClasse()
    {
        $ret    = [];
        // debug($_REQUEST,false);         
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $prods            = new ProdutProdutoModel();
            $produtos         = $prods->getProdutoClasse($termo);
            if (sizeof($produtos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Produto não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($produtos); $c++) {
                    $ret[$c]['id'] = $produtos[$c]['pro_id'];
                    $ret[$c]['text'] = $produtos[$c]['pro_desinf'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function buscaDescricaoProdutoClasse()
    {
        $ret    = [];
        // debug($_REQUEST,false);         
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $prods            = new ProdutProdutoModel();
            $produtos         = $prods->getProdutoClasse($termo);
            if (sizeof($produtos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Produto não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($produtos); $c++) {
                    $ret[$c]['id'] = $produtos[$c]['pro_id'];
                    $ret[$c]['text'] = $produtos[$c]['pro_despro'];
                }
            }
        }
        echo json_encode($ret);
    }
    public function buscaProdutoClasseSemIngrediente($ing = 0)
    {
        $ret    = [];
        // debug($_REQUEST,false);         
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $prods            = new ProdutProdutoModel();
            if ($ing != 0) {
                $produtos    = $prods->getProdutoClasse($termo, $ing);
            } else {
                $produtos    = $prods->getProdutoSemIngrediente(false, $termo);
            }
            if (sizeof($produtos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Produto não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($produtos); $c++) {
                    $ret[$c]['id'] = $produtos[$c]['pro_id'];
                    $ret[$c]['text'] = $produtos[$c]['pro_despro'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function buscaProduto()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $prods            = new ProdutProdutoModel();
            $produtos         = $prods->getProdutoSearch($termo);
            if (sizeof($produtos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Produto não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($produtos); $c++) {
                    $ret[$c]['id'] = $produtos[$c]['pro_id'];
                    $pro_despro = $produtos[$c]['pro_despro'];
                    $ret[$c]['text'] = $pro_despro;
                }
            }
        }
        echo json_encode($ret);
    }

    public function buscaIngredienteClasse()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $ingreds            = new ProdutIngredienteModel();
            $ingredientes       = $ingreds->getIngredienteClasse($termo);
            if (sizeof($ingredientes) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Ingrediente não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($ingredientes); $c++) {
                    $ret[$c]['id'] = $ingredientes[$c]['ing_id'];
                    $ret[$c]['text'] = $ingredientes[$c]['ing_nome'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function buscaTipoMovimentacao()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $tmovs            = new EstoquTipoMovimentacaoModel();
            $tmovimen         = $tmovs->getTipoMovimentacao($termo);
            if (sizeof($tmovimen) <= 0) {
                $ret['id'] = '-1';
                $ret['text'] = 'Tipo de Movimentação não encontrado...';
            } else {
                $ret['id']     = $tmovimen[0]['tmo_id'];
                $ret['depori'] = $tmovimen[0]['dep_codorigem'];
                $ret['depdes'] = $tmovimen[0]['dep_coddestino'];
            }
        }
        echo json_encode($ret);
    }



    public function busca_dep_destino()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $destinos         = new EstoquDepositoModel();
            $lst_destinos     = $destinos->getDestino($termo);
            if (sizeof($lst_destinos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Depósito de Destino não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($lst_destinos); $c++) {
                    $ret[$c]['id'] = $lst_destinos[$c]['dep_codDep'];
                    $ret[$c]['text'] = $lst_destinos[$c]['dep_desDep'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function gravasessao()
    {
        if ($_REQUEST['msg']) {
            $msg            = $_REQUEST['msg'];
            session()->setFlashdata('msg', $msg);
            $ret['erro'] = false;
        } else {
            $ret['erro'] = true;
        }
        echo json_encode($ret);
    }

    public function verSessao()
    {
        $sessao = session();
        $ret['sessao'] = $sessao->logged_in;
        echo json_encode($ret);
    }

    public function verificaSessao()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $ret = ['status' => 'sessao_ativa'];
            // echo "A sessão está ativa.";
        } else {
            $ret = ['status' => 'sessao_expirada'];
            // echo "A sessão não está ativa.";
        }
        // $sessionCookieName = config('App')->sessionCookieName; // Nome padrão é 'ci_session'
        // $sessionValue = $this->request->getCookie($sessionCookieName);
        // // debug($sessionValue, true);

        // $tempoLimite = 1795; // 30 minutos
        // $sessionPath = WRITEPATH . 'session'; // Diretório onde as sessões são armazenadas
        // $sessionFile = $sessionPath . DIRECTORY_SEPARATOR . 'ci_session' . $sessionValue;
        // $ret = ['status' => 'sessao_ativa'];
        // if(file_exists($sessionFile)){
        //     if (time() - filemtime($sessionFile) > $tempoLimite) {
        //         $ret = ['status' => 'sessao_expirada'];
        //     }
        // } else {
        //     $ret = ['status' => 'sessao_expirada'];
        // }
        echo json_encode($ret);
    }

    public function busca_campo_tela()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $telas = $this->tela->getTelaId($termo)[0];
            if (isset($telas['tel_model']) && $telas['tel_model'] != null) {
                $model = $telas['tel_model'];
                $compl_model = substr($model, 0, 6);
                $pasta = "App\\Models\\" . $compl_model . "\\";
                $model_atual = model($pasta . $model);
                $view   = $model_atual->view;
                $campos_tab = $this->admDados->getCampos($view);
                $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
                if (sizeof($campos_lis) <= 0) {
                    $ret[0]['id'] = '-1';
                    $ret[0]['text'] = 'Campos não encontrados...';
                } else {
                    $c = 0;
                    $ret[$c]['id']      = 0;
                    $ret[$c]['text']    = 'Texto Livre';
                    $c++;
                    $ret[$c]['id']      = 1;
                    $ret[$c]['text']    = 'Linha Horizontal';
                    $c++;
                    $ret[$c]['id']      = -10;
                    $ret[$c]['text']    = '';
                    $c++;
                    foreach ($campos_lis as $key => $value) {
                        $ret[$c]['id']      = $key;
                        $ret[$c]['text']    = $value;
                        $c++;
                    }
                }
            } else {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não Encontrada...';
            }
        }
        echo json_encode($ret);
        exit;
    }
}
