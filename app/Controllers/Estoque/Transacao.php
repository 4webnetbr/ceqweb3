<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Models\Estoqu\EstoquTransacaoModel;
use App\Entities\Estoque\EntTransacao;

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
        if (!$transacao = cache('transacao_lista')) {
            $this->data['exclusao'] = false;
            $this->data['edicao']   = false;
            $this->integra();

            $campos = montaColunasCampos($this->data, 'tns_codtns');
            $dados_tela = $this->transacao->getTransacao();
            $transacao = [
                'data' => montaListaColunasEnt($this->data, 'tns_codtns', $dados_tela, $campos[1]),
            ];
            cache()->save('transacao_lista', $transacao, 600);
        }
        return $this->response->setJSON($transacao);
    }

    public function show($id)
    {
        /** @var EntTransacao $entity */
        $entity = $this->transacao->find($id);
    
        // Campos vindos do Entity 
        $fields = $entity->campos;
    
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields->tns_codtns;
        $campos[0][1] = $fields->tns_germvp;
        $campos[0][2] = $fields->tns_dettns;
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';
    
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_sap_transacao', $id);
    
        echo view('vw_edicao', $this->data);
    }

    /**
    * integra
    */
    public function integra()
    {
        $busca  = new BuscasSapiens();
        $r_tnss = $busca->buscaTransacoes();
    
        for ($t = 0; $t < count($r_tnss); $t++) {
            $tns = $r_tnss[$t];
    
            // Objeto Entity
            $entity = new EntTransacao([
                'tns_codtns' => $tns->codTns,
                'tns_germvp' => $tns->gerMvp,
                'tns_dettns' => $tns->detTns,
            ]);
    
            // Verifica existência pelo código
            $tem = $this->transacao->getTransacao($tns->codTns);
    
            if ($tem) {
                $this->transacao->save($entity);
            } else {
                $this->transacao->insert($entity);
            }
        }
    }
}
