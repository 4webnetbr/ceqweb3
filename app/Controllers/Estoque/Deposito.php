<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\Estoqu\EstoquDepositoModel;

class Deposito extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $depositos;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->depositos = new EstoquDepositoModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'dep_codDep,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        // if (!$depositos = cache('depositos')) {
            $integ = new WsCeqweb();
            $integ->integraDeposito();
            
            $campos = montaColunasCampos($this->data, 'dep_codDep');
            $dados_tela = $this->depositos->getDeposito();
            $depositos = [
                'data' => montaListaColunas($this->data, 'dep_codDep', $dados_tela, $campos[1]),
            ];
            cache()->save('depositos', $depositos, 60000);
        // }
        echo json_encode($depositos);
    }

    public function show($id){
        $integ = new WsCeqweb();
        $integ->integraDeposito();

		$dados_depositos = $this->depositos->getDeposito($id);
        $fields = $this->depositos->defCampos($dados_depositos[0], true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['dep_codDep']; 
        $campos[0][1] = $fields['dep_desDep'];
        $campos[0][2] = $fields['dep_aceNeg'];
        $campos[0][3] = $fields['dep_codDescricao'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_sap_deposito', $id);

        echo view('vw_edicao', $this->data);
    }

}
