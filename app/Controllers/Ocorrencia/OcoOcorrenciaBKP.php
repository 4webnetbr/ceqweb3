<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Controllers\BuscasSapiens;
use App\Models\Produt\ProdutLoteModel;

class OcoOcorrencia extends BaseController
{
    public $data = [];
    public $novocorrencia;
    public $novaOcorrencia;
    public $modelTipo;
    public $modelMod;
    public $buscaSapiens;
    public $ocorreOcorrenciaModel;
    public $produtLoteModel;

    public function __construct()
    {
        $this->novocorrencia = new OcorreOcorrenciaModel();
        $this->data = session()->get('dados_tela') ?? [];

        $this->novaOcorrencia           = new EstoquRequisicaoModel();
        $this->modelTipo                = new OcorreTipoOcorrenciaModel();
        $this->modelMod                 = new OcorreModOcorrenciaModel();
        $this->buscaSapiens             = new BuscasSapiens();
        $this->produtLoteModel          = new ProdutLoteModel();
        $this->ocorreOcorrenciaModel    = new OcorreOcorrenciaModel();


        $this->data = session()->get('dados_tela') ?? [];
        $this->data['tabela']  = $this->novocorrencia->table;
        $this->data['view']    = $this->novocorrencia->view ?? '';
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
        $dados = $this->novocorrencia->getListaCompleta();
        $oco_ids_assoc = array_column($dados, 'oco_id');
        $log = buscaLogTabela('oco_ocorrencia', $oco_ids_assoc);  

        foreach ($dados as &$nov) {
            $nov['usu_nome'] = $log[$nov['oco_id']]['usua_alterou'] ?? '';
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
        $fields = $this->novocorrencia->defCampos();

        $secao[0]     = 'Dados Gerais';
        $campos[0][] = $fields['tpo_id'];      
        $campos[0][] = $fields['tpa_id'];    
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['lot_lote'];   
        $campos[0][] = $fields['pro_despro'];   
        $campos[0][] = $fields['oco_qtd'];
        $campos[0][] = $fields['oco_data'];

        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }


    public function edit($id)
    {
        $dados = $this->novocorrencia->getOcorrencia($id);

        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
        $fields = $this->novocorrencia->defCampos($dados, true);

       $secao[0]     = 'Dados Gerais';
        $campos[0][] = $fields['tpo_id'];      
        $campos[0][] = $fields['tpa_id'];    
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['lot_lote'];   
        $campos[0][] = $fields['pro_despro'];   
        $campos[0][] = $fields['oco_qtd'];
        $campos[0][] = $fields['oco_data'];
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = "update/$id";
        $this->data['edicao']  = true;
    
        echo view('vw_edicao', $this->data);
    }


    public function update($id)
    {
        $dados = $this->request->getPost();
    
        $ret   = $this->ocorreOcorrenciaModel->atualizarOcorrencia($id, $dados);
    
        return $this->response->setJSON($ret);
    }


    public function delete($id)
    {
        $ret = [];
        try {
            $this->novocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência Excluída com Sucesso');
            $ret['msg'] = 'Ocorrência Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $this->data['tabela'] = 'oco_ocorrencia';
        session()->set('dados_tela', $this->data);
    
        $dados = $this->request->getPost();
        $ret = $this->ocorreOcorrenciaModel->salvarOcorrencia($dados);

         if (!($ret['erro'] ?? true)) {
        session()->setFlashdata('msg', $ret['msg'] ?? 'Ocorrência gravada com sucesso!');
    }
    
        return $this->response->setJSON($ret);
    }


    public function getProdutoLote()
    {
        $codLote = $this->request->getPost('codLote');

        if (!$codLote) {
            return $this->response->setJSON(['erro' => 'Lote não informado']);
        }

        $dados = $this->produtLoteModel->getLoteSearch($codLote);

        if (!$dados || empty($dados)) {
            return $this->response->setJSON(['erro' => 'Lote não encontrado']);
        }

        $lote = $dados[0];
        return $this->response->setJSON([
            'descpro' => $lote['pro_despro'] ?? '',
        ]);
    }
}
