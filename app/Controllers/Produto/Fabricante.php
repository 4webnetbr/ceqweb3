<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\Produt\ProdutFabricanteModel;

class Fabricante extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $fabricantes;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->fabricantes = new ProdutFabricanteModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'fab_codFab,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        // if (!$fabricantes = cache('fabricantes')) {
            $integ = new WsCeqweb();
            $integ->integraFabricante();
    
            $campos = montaColunasCampos($this->data, 'fab_codFab');
            $dados_tela = $this->fabricantes->getFabricante();
            $fabricantes = [
                'data' => montaListaColunas($this->data, 'fab_codFab', $dados_tela, $campos[1]),
            ];
            cache()->save('fabricantes', $fabricantes, 60000);
        // }
        echo json_encode($fabricantes);
    }

    public function show($id){
        // $integ = new WsCeqweb();
        // $integ->integraFabricante();

		$dados_fabricantes = $this->fabricantes->getFabricante($id);
        $fields = $this->fabricantes->defCampos($dados_fabricantes[0], true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['fab_codFab']; 
        $campos[0][1] = $fields['fab_nomFab'];
        $campos[0][2] = $fields['fab_apeFab'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('pro_sap_fabricante', $id);

        echo view('vw_edicao', $this->data);
    }

}
