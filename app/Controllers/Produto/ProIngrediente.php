<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Entities\Produto\EntProdutIngrediente;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\CommonModel;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutProdutoModel;

class ProIngrediente extends BaseController
{
    use ForeignKeyUsageChecker;

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
        $tmp = session()->getFlashdata('dados_tela') ?? [];
        $this->data = (object) $tmp;
    
        $this->permissao   = $this->data->permissao ?? '';
        $this->ingrediente = new ProdutIngredienteModel();
        $this->common      = new CommonModel();
    
        if (!empty($this->data->erromsg)) {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', (array) $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data->colunas = montaColunasLista((array) $this->data, 'ing_id');
        $this->data->url_lista = base_url($this->data->controler . '/lista');
        echo view('vw_lista', (array) $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $campos = montaColunasCampos((array) $this->data, 'ing_id');
        $dados_ingred = $this->ingrediente->getIngredienteLista();
    
        $ret = new \stdClass();
        $ret->data = montaListaColunasEnt((array) $this->data, 'ing_id', $dados_ingred, $campos[1]);
        $ret->exclusao = false;
        cache()->save('ingred', $ret, 60000);
    
        return $this->response->setJSON($ret);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        // ENTITY
        $entity = new EntProdutIngrediente();
    
        $fields    = (object) $entity->campos;
        $fieldprod = (object) $entity->defCamposProduto();
    
        // SEÇÕES
        $this->data->secoes    = [];
        $this->data->secoes[0] = 'Dados Gerais';
    
        // CAMPOS
        $this->data->campos    = [];
        $this->data->campos[0] = [];
    
        $this->data->campos[0][] = $fields->ing_id;
        $this->data->campos[0][] = $fields->ing_nome;
        $this->data->campos[0][] = $fields->cla_id;
        $this->data->campos[0][] = $fieldprod->pro_id;
    
        $this->data->destino = 'store';
    
        echo view('vw_edicao', (array) $this->data);
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

        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'ing_ativo' => 'A'
                ];
            } else {
                $dad_atin = [
                    'ing_ativo' => 'I'
                ];
                $this->verificarUsoEmRelacionamentos('pro_ingrediente', 'ing_id', (int) $id);
            }
            $this->ingrediente->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ingrediente Alterado com Sucesso');
            $ret['msg']  = 'Ingrediente Alterado com Sucesso';
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            // $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14; // ou código personalizado, se preferir
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
        // Busca os dados do ingrediente
        $dadosIng  = $this->ingrediente->getIngrediente($id)[0] ?? null;
        $dadosProd = $this->ingrediente->getIngredienteProdutos($id);
        $entity = new EntProdutIngrediente($dadosIng, $show); // Instancia a entity 
    
        // Campos principais do ingrediente
        $fields = (object) $entity->campos;
        
        // Objeto auxiliar para armazenar os produtos vinculados
        $prods = new \stdClass();
        $prods->pro_id = [];
    
        if (!empty($dadosProd)) {
            foreach ($dadosProd as $prod) {
                $prods->pro_id[] = $prod->pro_id;
            }
        }
    
        // Campos relacionados aos produtos
        $fieldprod = (object) $entity->defCamposProduto(
            (array) $dadosIng,
            (array) $prods,
            $show
        );
    
        // SEÇÕES
        $this->data->secoes    = [];
        $this->data->secoes[0] = 'Dados Gerais';
    
        // CAMPOS
        $this->data->campos    = [];
        $this->data->campos[0] = [];
    
        $this->data->campos[0][] = $fields->ing_id;
        $this->data->campos[0][] = $fields->ing_nome;
        $this->data->campos[0][] = $fields->cla_id;
        $this->data->campos[0][] = $fieldprod->pro_id;
    
        $this->data->destino = 'store';
    
        echo view('vw_edicao', (array) $this->data);
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
        // $ret = [];
        // try {
        //     $this->ingrediente->delete($id);
    
        //     $ret['erro'] = false;
        //     $ret['msg']  = 'Ingrediente Excluído com Sucesso';
        //     session()->setFlashdata('msg', $ret['msg']);
    
        // } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
        //     $ret['erro'] = true;
        //     $ret['msg']  = 'Não foi possível Excluir esse Ingrediente. Verifique!';
        // }
    
        // return $this->response->setJSON($ret);

        $ret = [];

        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('pro_ingrediente', 'ing_id', (int) $id);

            // Soft delete
            $this->ingrediente->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ingrediente Excluído com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
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

        // Dados principais do ingrediente
        $sql_ing = [
            'ing_id'   => $postado['ing_id'],
            'ing_nome' => $postado['ing_nome'],
            'cla_id'   => $postado['cla_id'],
        ];

        // Atualiza ou insere o ingrediente
        if ($postado['ing_id'] != '') {
            $salva = $this->ingrediente->update($postado['ing_id'], $sql_ing);
        } else {
            $salva = $this->ingrediente->insert($sql_ing);
        }

        if ($salva) {
            // Recupera o ID do ingrediente
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

                    // Insere vínculo do ingrediente com o produto
                    $pro_id = $this->common->insertReg('dbProduto', 'pro_ing_produto', $sql_pro);

                    if (!$pro_id) {
                        $ret['erro'] = true;
                        $erros = $this->common->errors();
                        $ret['msg'] = 'Não foi possível gravar os Produtos do Ingrediente, Verifique!';
                    }
                }
                // Remove vínculos antigos não atualizados
                $this->common->deleteReg("dbProduto", "pro_ing_produto", "ing_id = " . $ing_id . " AND inp_atualizado != '" . $data_atu . "'");
            }
        } else {
            // Captura erros do model de ingrediente
            $ret['erro'] = true;
            $erros = $this->ingrediente->errors();
        }
        // Monta mensagem de erro
        if ($ret['erro']) {
            $ret['msg']  = '';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        } else {
            // Limpa cache e retorna sucesso
            cache()->clean();
            $ret['msg']  = 'Ingrediente Gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data->controler);
        }
        echo json_encode($ret);
        cache()->clean();
    }
}
