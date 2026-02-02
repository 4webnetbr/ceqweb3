<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutProdutoModel;

class ProIngrediente extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $ingrediente;
    public $common;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->ingrediente  = new ProdutIngredienteModel();
        $this->common       = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'ing_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
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
        // if (!$ingred = cache('ingred')) {
        $campos = montaColunasCampos($this->data, 'ing_id');
        $dados_ingred = $this->ingrediente->getIngredienteLista();
        $this->data['exclusao'] = false;
        $ingred = [
            'data' => montaListaColunas($this->data, 'ing_id', $dados_ingred, $campos[1]),
        ];
        cache()->save('ingred', $ingred, 60000);
        // }

        echo json_encode($ingred);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $fields = $this->ingrediente->defCampos();
        $fieldprod = $this->ingrediente->defCamposProduto();

        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['ing_id'];
        $campos[0][count($campos[0])] = $fields['ing_nome'];
        $campos[0][count($campos[0])] = $fields['cla_id'];
        $campos[0][count($campos[0])] = $fieldprod['pro_id'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'ing_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'ing_ativo' => 'I'
            ];
        }
        $ret = [];
        $produtos = new ProdutProdutoModel();
        $existepro = $produtos->getProdutoComIngrediente($id);
        if (count($existepro)) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        } else {
            try {
                $this->ingrediente->update($id, $dad_atin);
                $ret['erro'] = false;
                session()->setFlashdata('msg', 'Ingrediente Alterado com Sucesso');
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $ret['erro'] = true;
                $ret['msg']  = 'Não foi possível Alterar o Ingrediente, Verifique!<br><br>';
            }
        }
        echo json_encode($ret);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        $dados_ings = $this->ingrediente->getIngrediente($id)[0];
        $dados_prod = $this->ingrediente->getIngredienteProdutos($id);
        $fields = $this->ingrediente->defCampos($dados_ings, $show);
        $prods['pro_id'] = [];
        if (count($dados_prod) > 0) {
            for ($p = 0; $p < count($dados_prod); $p++) {
                array_push($prods['pro_id'], $dados_prod[$p]['pro_id']);
            }
        }
        $fieldprod = $this->ingrediente->defCamposProduto($dados_ings, $prods, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['ing_id'];
        $campos[0][count($campos[0])] = $fields['ing_nome'];
        $campos[0][count($campos[0])] = $fields['cla_id'];
        $campos[0][count($campos[0])] = $fieldprod['pro_id'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {

        $ret = [];
        try {
            $this->ingrediente->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ingrediente Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir esse Ingrediente Verifique!<br><br>';
        }
        echo json_encode($ret);
    }
    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $ret['erro'] = false;
        $erros = [];
        $sql_ing = [
            'ing_id' => $postado['ing_id'],
            'ing_nome' => $postado['ing_nome'],
            'cla_id'   => $postado['cla_id'],
        ];
        // debug($sql_ing, true);
        if ($postado['ing_id'] != '') {
            $salva = $this->ingrediente->update($postado['ing_id'], $sql_ing);
        } else {
            $salva = $this->ingrediente->insert($sql_ing);
        }
        if ($salva) {
            $ing_id = $this->ingrediente->getInsertID();
            if ($postado['ing_id'] != '') {
                $ing_id = $postado['ing_id'];
            }
            $data_atu = date('Y-m-d H:i');

            // GRAVAÇãO DOS Produtos
            $this->common->deleteReg("dbProduto", "pro_ing_produto", "ing_id = " . $ing_id . "");
            if (isset($postado['pro_id'])) {
                foreach ($postado['pro_id'] as $key => $value) {
                    $sql_pro = [
                        'ing_id' => $ing_id,
                        'cla_id' => $postado['cla_id'],
                        'pro_id' => $postado['pro_id'][$key],
                        'inp_atualizado' => $data_atu,
                    ];
                    $pro_id = $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_pro);
                    if (!$pro_id) {
                        $ret['erro'] = true;
                        $erros = $this->common->errors();
                        $ret['msg'] = 'Não foi possível gravar os Produtos do Ingrediente, Verifique!';
                    }
                }
                $this->common->deleteReg("dbProduto", "pro_ing_produto", "ing_id = " . $ing_id . " AND inp_atualizado != '" . $data_atu . "'");
            }
        } else {
            $ret['erro'] = true;
            $erros = $this->ingrediente->errors();
        }
        if ($ret['erro']) {
            // $ret['msg']  = 'Não foi possível gravar o Ingrediente, Verifique!<br><br>';
            $ret['msg']  = '';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        } else {
            cache()->clean();
            $ret['msg']  = 'Ingrediente Gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
        cache()->clean();
    }
}
