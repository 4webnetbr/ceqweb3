<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Models\Produt\ProdutOrigemModel;

class Origem extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $origems;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->origems = new ProdutOrigemModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
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
        // $integ = new WsCeqweb();
        // $integ->integraOrigem();

        $this->data['temacao']   = false; // não tem ação
        $this->data['colunas'] = montaColunasLista($this->data, 'ori_codOri,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'ori_codOri');
        $dados_tela = $this->origems->getOrigem();

        $this->data['edicao']   = false;
        $this->data['exclusao'] = false;
        $this->data['temacao'] = false; // não tem ação
        $origems = ['data' => montaListaColunasEnt($this->data, 'ori_codOri', $dados_tela, $campos[1])];
        cache()->save('origems', $origems, 60000);
        echo json_encode($origems);
    }

    public function show($id)
    {
        $dados_origems = $this->origems->getOrigem($id);

        if (!$dados_origems || !isset($dados_origems[0])) {
            return redirect()->back();
        }

        /** @var object $origem */
        $origem = $dados_origems[0];
        // defCampos trabalhando com OBJ
        $fields = $this->origems->defCampos((array) $origem, true);

        // SEÇÕES
        $secao = [];
        $secao[0] = 'Dados Gerais';

        // CAMPOS
        $campos = [];
        $campos[0][0] = $fields->ori_codOri       ?? $fields['ori_codOri'];
        $campos[0][1] = $fields->ori_desOri       ?? $fields['ori_desOri'];
        $campos[0][2] = $fields->ori_codDescricao ?? $fields['ori_codDescricao'];

        // DADOS DA TELA
        $this->data->secoes  = $secao;
        $this->data->campos  = $campos;
        $this->data->destino = 'store';
        $this->data->log = buscaLog('pro_sap_origem', $id);

        echo view('vw_edicao', (array) $this->data);
    }
}
