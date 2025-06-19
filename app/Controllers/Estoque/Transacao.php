<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Models\Estoqu\EstoquTransacaoModel;

class Transacao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $transacao;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->transacao = new EstoquTransacaoModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'tns_codtns,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        if (!$transacao = cache('transacao')) {
            $this->integra();
            $campos = montaColunasCampos($this->data, 'tns_codtns');
            $dados_tela = $this->transacao->getTransacao();
            $transacao = [
                'data' => montaListaColunas($this->data, 'tns_codtns', $dados_tela, $campos[1]),
            ];
            cache()->save('transacao', $transacao, 60000);
        }
        echo json_encode($transacao);
    }

    public function show($id){
		$dados_depositos = $this->transacao->find($id);
        $fields = $this->transacao->defCampos($dados_depositos, true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['tns_codtns']; 
        $campos[0][1] = $fields['tns_germvp'];
        $campos[0][2] = $fields['tns_dettns'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_sap_transacao', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
     * integra
     */
    public function integra()
    {
        $busca = new BuscasSapiens();
        $r_tnss = $busca->buscaTransacoes();
        $tnss = [];
        for ($t = 0; $t < count($r_tnss); $t++) {
            $tns = $r_tnss[$t];
            $tnss['tns_codtns'] = $tns->codTns;
            $tnss['tns_germvp'] = $tns->gerMvp;
            $tnss['tns_dettns'] = $tns->detTns;
            $tem = $this->transacao->getTransacao($tns->codTns);
            if($tem){
                $this->transacao->save($tnss);
            } else {
                $this->transacao->insert($tnss);                
            }
            // $sql = $this->depositos->getLastQuery();
            // debug($sql);
        }
        // debug('fim',true);
    }
}
