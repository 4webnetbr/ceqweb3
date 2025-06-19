<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
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
        // if (!$origems = cache('origems')) {
            // $integ = new WsCeqweb();
            // $integ->integraOrigem();
    
            $campos = montaColunasCampos($this->data, 'ori_codOri');
            $dados_tela = $this->origems->getOrigem();
            $origems = [
                'data' => montaListaColunas($this->data, 'ori_codOri', $dados_tela, $campos[1]),
            ];
            cache()->save('origems', $origems, 60000);
        // }
        echo json_encode($origems);
    }

    public function show($id){
        // $integ = new WsCeqweb();
        // $integ->integraOrigem();

		$dados_origems = $this->origems->getOrigem($id);
        $fields = $this->origems->defCampos($dados_origems[0], true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['ori_codOri']; 
        $campos[0][1] = $fields['ori_desOri'];
        $campos[0][2] = $fields['ori_codDescricao'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('pro_sap_origem', $id);

        echo view('vw_edicao', $this->data);
    }

}
