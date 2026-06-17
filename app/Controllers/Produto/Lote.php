<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\Produt\ProdutLoteModel;
use App\Entities\Produto\EntLote;

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
        $integ = new WsCeqweb();
        // $integ->integraLotesBusca();
        $integ->integraLotesFaltantes();

        $campos = montaColunasCampos($this->data, 'lot_id');
        $dados_lote = $this->lotes->getLote();
        $dados_lote = filtrarPorPerfil($dados_lote);
        $lotes = [
            'data' => montaListaColunasEnt($this->data, 'lot_id', $dados_lote, $campos[1]),
        ];
        cache()->save('lotes', $lotes, 60000);
        echo json_encode($lotes);
    }

    public function show($id)
    {
        // Busca o lote pelo ID informado
        $dados_lotes = $this->lotes->getLote($id);

        // Caso não encontre pelo ID, tenta buscar pelo termo (ex: código/lote)
        if (count($dados_lotes) === 0) {
            $dados_lotes = $this->lotes->getLoteSearch($id);
        }

        // se encontrou algum lote
        if (count($dados_lotes) > 0) {
            /** @var \App\Entities\Produto\EntLote $lote */

            $lote   = new EntLote($dados_lotes[0], true);
            $fields = $lote->campos;    // Campos já montados pelo Entity

            $secao = [];
            $secao[0] = 'Dados Gerais';

            // Monta os campos da seção "Dados Gerais"
            $campos = [];
            $campos[0][0] = $fields['lot_id'];
            $campos[0][1] = $fields['lot_codbar'];
            $campos[0][2] = $fields['lot_codpro'];
            $campos[0][3] = $fields['pro_despro'];
            $campos[0][4] = $fields['lot_lote'];
            $campos[0][5] = $fields['lot_validade'];
            $campos[0][6] = $fields['lot_status'];

            $this->data['secoes']  = $secao;
            $this->data['campos']  = $campos;
            $this->data['destino'] = 'store';
            $this->data['log']     = buscaLog('pro_sap_lote', $id);

            echo view('vw_edicao', $this->data);
        } else {

            $msg = 'LOTE não Encontrado, ou não disponível!!!';
            session()->setFlashdata('msg', $msg);
            $this->index();
        }
    }
}
