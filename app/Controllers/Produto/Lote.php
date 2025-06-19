<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\Produt\ProdutLoteModel;

class Lote extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $lotes;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->lotes = new ProdutLoteModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'lot_id,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        // if (!$lotes = cache('lotes')) {
        $integ = new WsCeqweb();
        $integ->integraLotesBusca();

        $campos = montaColunasCampos($this->data, 'lot_id');
        $dados_lote = $this->lotes->getLote();
        // foreach ($dados_lote as $key => $value) {
        //     if ($value['stt_nome'] != $value['stt_nome_produto']) {
        //         $dados_lote[$key]['stt_nome'] = $value['stt_nome_produto'];
        //     }
        // }
        $lotes = [
            'data' => montaListaColunas($this->data, 'lot_id', $dados_lote, $campos[1]),
        ];
        cache()->save('lotes', $lotes, 60000);
        // }
        echo json_encode($lotes);
    }

    public function show($id)
    {
        // $integ = new WsCeqweb();
        // $integ->integraLote();

        $dados_lotes = $this->lotes->getLote($id);
        // debug($dados_lotes, true);
        if (count($dados_lotes) > 0) {
            $fields = $this->lotes->defCampos($dados_lotes[0], true);

            $secao[0] = 'Dados Gerais';
            $campos[0][0] = $fields['lot_id'];
            $campos[0][1] = $fields['lot_codbar'];
            $campos[0][2] = $fields['lot_codpro'];
            $campos[0][3] = $fields['pro_despro'];
            $campos[0][4] = $fields['lot_lote'];
            $campos[0][5] = $fields['lot_validade'];
            $campos[0][6] = $fields['lot_status'];

            $this->data['secoes']     = $secao;
            $this->data['campos']     = $campos;
            $this->data['destino']    = 'store';
            // BUSCAR DADOS DO LOG
            $this->data['log'] = buscaLog('pro_sap_lote', $id);

            echo view('vw_edicao', $this->data);
        } else {
            $msg  = 'LOTE não Encontrado, ou não disponível!!!';
            session()->setFlashdata('msg', 25);
            $this->index();
        }
    }
}
