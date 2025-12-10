<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTrataOcorrenciaModel;
use App\Models\Ocorre\OcorreNovOcorrenciaModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Controllers\BuscasSapiens;
use App\Models\Produt\ProdutLoteModel;
use CodeIgniter\Controller;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $trataocorrencia;
    public $OcorreTrataOcorrenciaMode;
    public $novaOcorrencia;
    public $modelTipo;
    public $modelMod;
    public $buscaSapiens;
    public $ocorreNovOcorrenciaModel;
    public $produtLoteModel;

    public function __construct()
    {
        $this->trataocorrencia = new OcorreTrataOcorrenciaModel();
        $this->data = session()->get('dados_tela') ?? [];

        $this->novaOcorrencia           = new EstoquRequisicaoModel();
        $this->modelTipo                = new OcorreTipoOcorrenciaModel();
        $this->modelMod                 = new OcorreModOcorrenciaModel();
        $this->buscaSapiens             = new BuscasSapiens();
        $this->produtLoteModel          = new ProdutLoteModel();
        $this->ocorreNovOcorrenciaModel = new OcorreNovOcorrenciaModel();


        $this->data = session()->get('dados_tela') ?? [];
        $this->data['tabela']  = $this->trataocorrencia->table;
        $this->data['view']    = $this->trataocorrencia->view ?? '';
        session()->set('dados_tela', $this->data); 
    }

    
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    
    
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'oco_id');
        $dados = $this->trataocorrencia->getListaCompleta();
    
        foreach ($dados as &$nov) {

            // $id = $nov['oco_id'];
            // $numero = $nov['oco_numero'];
       
            // // LINK para visualização
            // $url = base_url("OcoTrataOcorrencia/view/$id");
            // $nov['oco_numero'] = "<a href='{$url}' title='Visualizar ocorrência #{$numero}'>{$numero}</a>";
    
            // $url_imp = base_url('/CriaPdf2025/PrintRequisicaoEstoq/' . $nov['oco_id']);
            // $nov['acao_person'] = [
            //     "<button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0 float-end' 
            //         data-mdb-toggle='tooltip' data-mdb-placement='top' 
            //         title='Imprimir' onclick='openPDFModal(\"$url_imp\",\"Imprimir\")'>
            //         <i class='fa-solid fa-print'></i></button>", 
            // ];
        }
        return $this->response->setJSON([
            'data' => montaListaColunas($this->data, 'oco_id', $dados, $campos[1])
        ]);
    }
    

    public function add()
    {
        
    }


    public function edit($id)
    {
        
        $dados = $this->trataocorrencia->getById($id);

        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }

        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados['usu_nome'] = $log[$id]['usua_alterou'] ?? $dados['usu_nome'];

        $fields = $this->trataocorrencia->defCampos($dados, true);

        $secao[0] = 'Dados Gerais';
    
        $campos[0][0] = $fields['tpo_id']; 
        $campos[0][1] = $fields['usu_nome'];          
        $campos[0][2] = $fields['oco_descricao'];
        $campos[0][3] = $fields['oco_data'];
        $campos[0][4] = $fields['lot_id'];
        $campos[0][5] = $fields['lot_lote'];   
        $campos[0][6] = $fields['oco_qtd'];
        $campos[0][7] = $fields['tpa_id'];
        if (isset($fields['oco_justi'])) {
            $campos[0][8] = $fields['oco_justi'];
        }
        // $campos[0][8] = $fields['tmo_id'];
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = "update/$id";
        $this->data['edicao']  = true;
    
        echo view('vw_edicao', $this->data);
    }


    public function update($id)
    {
        
    }


    public function delete($id)
    {
        $ret = [];
        try {
            $this->trataocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tipo de Ocorrência Excluída com Sucesso');
            $ret['msg'] = 'Tipo de Ocorrência Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        
    }

    public function view($id)
    {
        $dados = $this->trataocorrencia->getView($id);
    
        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
    
        $fields = $this->trataocorrencia->defCampos($dados, true);
    
        $secao[0] = 'Dados Gerais';
    
        $campos[0][0] = $fields['tpo_id']; 
        $campos[0][1] = $fields['usu_nome'];          
        $campos[0][2] = $fields['oco_descricao'];
        $campos[0][3] = $fields['oco_data'];
        $campos[0][4] = $fields['lot_id'];
        $campos[0][5] = $fields['lot_lote'];   
        $campos[0][6] = $fields['oco_qtd'];
        $campos[0][7] = $fields['moc_id']; 
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = ''; 
        $this->data['visualizacao'] = true; 
    
        echo view('vw_visualizacao', $this->data);
    }



    public function getProdutoLote()
    {
        
    }


    public function buscaOcorrenciasPorTipo()
    {
        
    }
}
