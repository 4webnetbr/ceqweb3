<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigStatusModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigEtiquetaModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Models\Config\ConfigImpressoraModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;

class Buscas extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo;
    public $tela;
    public $usuario;
    public $admDados;
    public $status;


    public function __construct()
    {
        $this->menu     = new ConfigMenuModel();
        $this->modulo   = new ConfigModuloModel();
        $this->tela     = new ConfigTelaModel();
        $this->usuario  = new ConfigUsuarioModel();
        $this->admDados = new ConfigDicDadosModel();
        $this->status   = new ConfigStatusModel();
    }

    public function busca_hierarquia()
    {
        $hierarquia = new \stdClass();

        if ($_REQUEST['campo']) {
            $termo = $_REQUEST['campo'][0]['id_dep'];

            if ($termo == 1) {
                $hierarquia->{2} = 'Pai';
                $hierarquia->{3} = 'Filho';
            } else {
                $hierarquia->{1} = 'Órfão';
                $hierarquia->{3} = 'Filho';
                $hierarquia->{4} = 'Neto';
            }
        }

        echo json_encode($hierarquia);
    }

    public function busca_menu_pai()
    {
        $menus = $this->menu->getMenuPai();
        $menu_pai = new \stdClass();

        foreach ($menus as $m) {
            $menu_pai->{$m->men_id} = $m->men_etiqueta;
        }

        echo json_encode($menu_pai);
    }

    public function busca_submenu()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $submenus = $this->menu->getSubMenu($_REQUEST['busca']);

            if (empty($submenus)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'SubMenu não encontrada...';
                $ret[] = $o;
            } else {
                // debug($submenus, true);
                foreach ($submenus as $s) {
                    $o = new \stdClass();
                    $o->id = $s['men_id'];
                    $o->text = $s['men_etiqueta'];
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_modulo()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $modulos = $this->modulo->getModulosSearch($_REQUEST['busca']);

            if (empty($modulos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Módulo não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($modulos as $m) {
                    $o = new \stdClass();
                    $o->id = $m->mod_id;
                    $o->text = $m->mod_nome;
                    $o->icone = $m->mod_icone;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_modulo_id()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $modulos = $this->modulo->getModulo($_REQUEST['busca']);

            if (empty($modulos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Módulo não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($modulos as $m) {
                    $o = new \stdClass();
                    $o->id = $m->mod_id;
                    $o->text = $m->mod_nome;
                    $o->icone = $m->mod_icone;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_menu()
    {
        $tipo = $_REQUEST['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);

        $menu = new \stdClass();

        foreach ($menus as $m) {
            $menu->{$m->men_id} = $m->men_nome;
        }

        echo json_encode($menu);
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
                    $ret[$c]['id']      = $telas[$c]->tel_id;
                    $ret[$c]['text']    = $telas[$c]->tel_nome;
                    $ret[$c]['icone']   = $telas[$c]->tel_icone;
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_tela()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $telas = $this->tela->getTelaSearch($_REQUEST['busca']);

            if (empty($telas)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Tela não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($telas as $t) {
                    $o = new \stdClass();
                    $o->id = $t->tel_id;
                    $o->text = $t->tel_nome;
                    $o->icone = $t->tel_icone;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_tela_id()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $class = $this->tela->getTelaId($_REQUEST['busca']);

            if (empty($class)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Tela não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($class as $t) {
                    $o = new \stdClass();
                    $o->id    = $t->tel_id;
                    $o->text  = $t->tel_nome;
                    $o->icone = $t->tel_icone;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_status_tela()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $status = $this->status->getStatusTela($_REQUEST['busca']);

            if (empty($status)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Tela não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($status as $s) {
                    $o = new \stdClass();
                    $o->id   = $s->stt_id;
                    $o->text = $s->stt_nome;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_menupai()
    {
        $tipo  = $_REQUEST['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);

        $menu = new \stdClass();

        foreach ($menus as $m) {
            $menu->{$m->men_id} = $m->men_nome;
        }

        echo json_encode($menu);
        exit;
    }

    public function busca_tabela()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $tabelas = $this->admDados->getTabelaSearch($_REQUEST['busca']);

            if (empty($tabelas)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Tabela não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($tabelas as $t) {
                    $o = new \stdClass();
                    $o->id   = $t->table_name;
                    $o->text = $t->table_name;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function busca_familia()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $familias = (new ProdutFamiliaModel())->getFamiliaOrigem($_REQUEST['busca']);

            if (empty($familias)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Família não encontrada...';
                $ret[] = $o;
            } else {
                foreach ($familias as $f) {
                    $o = new \stdClass();
                    $o->id   = $f->fam_codFam;
                    $o->text = $f->fam_codDescricao;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function buscaProdutoClasse()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $produtos = (new ProdutProdutoModel())->getProdutoClasse($_REQUEST['busca']);

            if (empty($produtos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Produto não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($produtos as $p) {
                    $o = new \stdClass();
                    $o->id   = $p->pro_id;
                    $o->text = $p->pro_desinf;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function buscaDescricaoProdutoClasse()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $produtos = (new ProdutProdutoModel())->getProdutoClasse($_REQUEST['busca']);

            if (empty($produtos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Produto não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($produtos as $p) {
                    $o = new \stdClass();
                    $o->id   = $p->pro_id;
                    $o->text = $p->pro_despro;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function buscaProdutoClasseSemIngrediente($ing = 0)
    {
        $ret = [];

        if (!empty($_REQUEST['busca'])) {
            $prods = new ProdutProdutoModel();

            if ($ing != 0) {
                $produtos = $prods->getProdutoClasse($_REQUEST['busca'], $ing);
            } else {
                $produtos = $prods->getProdutoSemIngrediente(false, $_REQUEST['busca']);
            }

            if (empty($produtos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Produto não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($produtos as $p) {
                    $o = new \stdClass();
                    $o->id   = $p->pro_id;
                    $o->text = $p->pro_despro;
                    $ret[] = $o;
                }
            }
        }

        return $this->response->setJSON($ret);
    }

    public function buscaProduto()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $prods    = new ProdutProdutoModel();
            $produtos = $prods->getProdutoSearch($_REQUEST['busca']);

            if (empty($produtos)) {
                $o = new \stdClass();
                $o->id   = '-1';
                $o->text = 'Produto não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($produtos as $p) {
                    $o = new \stdClass();
                    $o->id   = $p->pro_id;
                    $o->text = $p->pro_despro;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function buscaProdutoporLote()
    {
        $ret = new \stdClass();
        $busca = $this->request->getVar('busca');
        // debug($busca);
        if (!empty($busca)) {
            $lotesm = new ProdutLoteModel();
            $lote   = $lotesm->getLoteSearch($busca);

            if (empty($lote)) {
                $ret->lotid = '-1';
            } else {
                $ret->proid     = $lote[0]->pro_id;
                $ret->lotid     = $lote[0]->lot_id;
                $ret->despro    = $lote[0]->pro_despro;
                $ret->validade  = $lote[0]->lot_validade ?? '';
                $ret->Fabrica   = $lote[0]->fab_nomFab ?? '';
                $ret->codErp    = $lote[0]->lot_codpro ?? '';
            }
        }

        return $this->response->setJSON($ret);
    }

    public function buscaProdutoDeposito()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $produtos = (new ProdutProdutoModel())->getProdutoEstoque(false, $_REQUEST['busca']);
            // debug($produtos, true);
            if (empty($produtos)) {
                $o = new \stdClass();
                $o->id = '-1';
                $o->text = 'Produto não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($produtos as $p) {
                    $o = new \stdClass();
                    $o->id   = $p->pro_id;
                    $o->text = $p->pro_coddes;
                    $ret[] = $o;
                }
            }
            // debug($ret);
        }

        echo json_encode($ret);
    }

    public function buscaIngredienteClasse()
    {
        $ret = [];

        if (!empty($_REQUEST['busca'])) {
            $ingreds      = new ProdutIngredienteModel();
            $ingredientes = $ingreds->getIngredienteClasse($_REQUEST['busca']);

            if (empty($ingredientes)) {
                $o = new \stdClass();
                $o->id   = '-1';
                $o->text = 'Ingrediente não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($ingredientes as $ing) {
                    $o = new \stdClass();
                    $o->id   = $ing->ing_id;
                    $o->text = $ing->ing_nome;
                    $ret[] = $o;
                }
            }
        }

        return $this->response->setJSON($ret);
    }

    public function buscaTipoMovimentacao()
    {
        $ret = new \stdClass();

        if (!empty($_REQUEST['busca'])) {
            $tmovs    = new EstoquTipoMovimentacaoModel();
            $tmovimen = $tmovs->getTipoMovimentacao($_REQUEST['busca']);

            if (empty($tmovimen)) {
                $ret->id   = '-1';
                $ret->text = 'Tipo de Movimentação não encontrado...';
            } else {
                $ret->id     = $tmovimen[0]->tmo_id;
                $ret->depori = $tmovimen[0]->dep_codorigem;
                $ret->depdes = $tmovimen[0]->dep_coddestino;
                $ret->deppad = $tmovimen[0]->dep_codpadrao;
            }
        }

        return $this->response->setJSON($ret);
    }

    public function buscaTipoAcao()
    {
        $ret = new \stdClass();

        if (!empty($_REQUEST['busca'])) {
            $tacao  = new OcorreTipoAcaoModel();
            $tacaos = $tacao->getTipoAcao($_REQUEST['busca']);

            if (empty($tacaos)) {
                $ret->id   = '-1';
                $ret->text = 'Tipo de Movimentação não encontrado...';
            } else {
                $ret->id   = $tacaos[0]->tpa_id;
                $ret->acao = $tacaos[0]->tpa_tipo;
            }
        }

        return $this->response->setJSON($ret);
    }

    public function buscatelasaplicaveis()
    {
        $ret = [];

        if ($_REQUEST['tipoocor']) {
            $ttipo  = new OcorreTipoOcorrenciaModel();
            $ttelas = $ttipo->getTOTelasAplicaveis($_REQUEST['tipoocor']);

            if (empty($ttelas)) {
                $o = new \stdClass();
                $o->id   = '-1';
                $o->text = 'Tipo de Ocorrência não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($ttelas as $t) {
                    $o = new \stdClass();
                    $o->id   = $t->tot_id;
                    $o->text = '';
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function buscaetiqcontroler()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $mtela = new ConfigTelaModel();
            $tela  = $mtela->getTelaSearch($_REQUEST['busca'])[0];
            // debug($tela, true);
            if ($tela) {
                $tetqs     = new ConfigEtiquetaModel();
                $tetquetas = $tetqs->getEtiquetaTela($tela->tel_id);

                if (empty($tetquetas)) {
                    $o = new \stdClass();
                    $o->id   = '-1';
                    $o->text = 'Etiqueta não encontrada...';
                    $ret[] = $o;
                } else {
                    foreach ($tetquetas as $etq) {
                        $o = new \stdClass();
                        $o->id   = $etq->etq_id;
                        $o->text = $etq->etq_nome;
                        $ret[] = $o;
                    }
                }
            } else {
                $o = new \stdClass();
                $o->id   = '-1';
                $o->text = 'Tela não encontrada...';
                $ret[] = $o;
            }
        }

        echo json_encode($ret);
    }

    public function buscaimpressoras()
    {
        $termo    = $this->request->getGetPost('busca');
        $model    = new ConfigImpressoraModel();
        $printers = $model->getImpressoraSearch($termo);

        $ret = [];

        if (empty($printers)) {
            $o = new \stdClass();
            $o->id   = -1;
            $o->text = 'Impressora não encontrada...';
            $ret[] = $o;
        } else {
            foreach ($printers as $p) {
                $o = new \stdClass();
                $o->id   = $p->imp_id;
                $o->text = $p->imp_nome;
                $ret[] = $o;
            }
        }

        return $this->response->setJSON($ret);
    }

    public function busca_tipo_movimentacao()
    {
        $ret = [];

        $tmoModel = new EstoquTipoMovimentacaoModel();
        $lista = $tmoModel->getTipoMovimentacao();

        foreach ($lista as $tmo) {
            $ret[] = [
                'id'   => $tmo->tmo_id,
                'text' => $tmo->tmo_nome
            ];
        }

        return $this->response->setJSON($ret);
    }

    public function busca_dep_destino()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $destinos = new EstoquDepositoModel();
            $lst = $destinos->getDestino($_REQUEST['busca']);

            if (empty($lst)) {
                $o = new \stdClass();
                $o->id   = '-1';
                $o->text = 'Depósito de Destino não encontrado...';
                $ret[] = $o;
            } else {
                foreach ($lst as $d) {
                    $o = new \stdClass();
                    $o->id   = $d->dep_codDep;
                    $o->text = $d->dep_desDep;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
    }

    public function gravasessao()
    {
        $ret = new \stdClass();

        if ($_REQUEST['msg']) {
            session()->setFlashdata('msg', $_REQUEST['msg']);
            $ret->erro = false;
        } else {
            $ret->erro = true;
        }

        echo json_encode($ret);
    }

    public function verSessao()
    {
        $ret = new \stdClass();
        $ret->sessao = session()->logged_in ?? false;
        echo json_encode($ret);
    }

    public function buscaAcoesPorTipo()
    {
        $ret = [];

        $busca = $_REQUEST['busca'] ?? null;

        if ($busca) {

            // Model correto para SUBTIPO
            $subtipoModel = new OcorreModOcorrenciaModel();

            // Método coerente com o que está buscando
            $lst = $subtipoModel->getSubtipoPorTipos($busca);

            if (empty($lst)) {

                $o = new \stdClass();
                $o->id   = -1;
                $o->text = 'Nenhum Subtipo encontrado';
                $ret[] = $o;
            } else {

                foreach ($lst as $l) {
                    $o = new \stdClass();
                    $o->id   = $l->sut_id;     // ID do Subtipo
                    $o->text = $l->sut_nome;   // Nome do Subtipo
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function buscaSubtipoPorTipo()
    {
        $ret = [];

        if ($_REQUEST['busca']) {
            $subtipos = new OcorreModOcorrenciaModel();
            $lst = $subtipos->getModOcorrenciaPorTipo($_REQUEST['busca']);
            if (empty($lst)) {
                $o = new \stdClass();
                $o->id   = -1;
                $o->text = 'Nenhum Subtipo encontrado';
                $ret[] = $o;
            } else {
                foreach ($lst as $l) {
                    $o = new \stdClass();
                    $o->id   = $l->sut_id;
                    $o->text = $l->sut_nome;
                    $ret[] = $o;
                }
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function buscaClassePorTipo()
    {
        $ret = [];
        if ($_REQUEST['busca']) {
            $model = new OcorreTipoOcorrenciaModel();
            $lst = $model->getClassePorTipo($_REQUEST['busca']);
            if (empty($lst)) {
                $o = new \stdClass();
                $o->id   = -1;
                $o->text = 'Nenhuma Classe encontrada';
                $ret[] = $o;
            } else {
                foreach ($lst as $l) {
                    $o = new \stdClass();
                    $o->id   = $l->cla_id;
                    $o->text = $l->cla_nome;
                    $ret[] = $o;
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function verificaSessao()
    {
        $ret = new \stdClass();
        $ret->status = (session_status() === PHP_SESSION_ACTIVE)
            ? 'sessao_ativa'
            : 'sessao_expirada';

        echo json_encode($ret);
    }

    public function buscaPerfilPorTipo()
    {
        $ret = [];
    
        if ($_REQUEST['busca']) {
    
            $db = db_connect('dbOcorrencia');
            $builder = $db->table('oco_tipo_ocorrencia_permissao p');
            $builder->select('p.prf_id, c.prf_nome');
            $builder->join(
                'config_ceqweb_db.cfg_perfil c',
                'c.prf_id = p.prf_id'
            );
            $builder->where('p.tpo_id', $_REQUEST['busca']);
            $lst = $builder->get()->getResult();
    
            foreach ($lst as $l) {
                $o = new \stdClass();
                $o->id   = $l->prf_id;
                $o->text = $l->prf_nome;
                $ret[] = $o;
            }
        }

        echo json_encode($ret);
        exit;
    }

    public function busca_campo_tela(?string $busca = null)
    {
        $ret  = [];
        $etiq = true;

        $termo = $busca ?? (($_REQUEST['busca']) ?? null);
        // debug($termo, true);
        if ($termo) {
            $referer = ($_SERVER['HTTP_REFERER'] ?? null);

            if ($referer) {
                $path = parse_url($referer, PHP_URL_PATH); // extrai apenas "/produtos/editar/5"

                $segments = explode('/', trim($path, '/')); // quebra em ['produtos', 'editar', '5']

                $controller = $segments[0] ?? null; // pega 'produtos'

                if (strtolower($controller) != 'cfgetiqueta') {
                    $etiq = false;
                }
            }

            $telas = $this->tela->getTelaId($termo)[0];

            if (isset($telas->tel_model) && $telas->tel_model != null) {
                $model = $telas->tel_model;
                $compl_model = substr($model, 0, 6);
                $pasta = "App\\Models\\" . $compl_model . "\\";
                $model_atual = model($pasta . $model);
                $view   = $model_atual->view;
                if (isset($model_atual->viewoutra)) {
                    $view   = $model_atual->viewoutra;
                }
                $campos_tab = $this->admDados->getCampos($view);
                // debug($campos_tab);
                $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');
                // $campos_tab = $this->admDados->getCampos($view);
                // $campos_lis = array_column($campos_tab, 'NOME_COMPLETO', 'COLUMN_NAME');

                if (sizeof($campos_lis) <= 0) {
                    $ret[0]['id'] = '-1';
                    $ret[0]['text'] = 'Campos não encontrados...';
                } else {
                    $c = 0;
                    if ($etiq) {
                        $ret[$c]['id']      = '99';
                        $ret[$c]['text']    = 'Texto Livre';
                        $c++;
                        $ret[$c]['id']      = '1';
                        $ret[$c]['text']    = 'Linha Horizontal';
                        $c++;
                        $ret[$c]['id']      = '2';
                        $ret[$c]['text']    = 'Data Atual';
                        $c++;
                        $ret[$c]['id']      = '3';
                        $ret[$c]['text']    = 'Data e Hora Atual';
                        $c++;
                        $ret[$c]['id']      = -10;
                        $ret[$c]['text']    = '';
                        $c++;
                    }
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
        // debug($ret, true);
        if ($busca) {
            return $ret;
        } else {
            echo json_encode($ret);
        }
        exit;
    }
}
