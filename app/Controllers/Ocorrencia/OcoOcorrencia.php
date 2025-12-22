<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Controllers\BuscasSapiens;
use App\Models\Produt\ProdutLoteModel;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Models\CommonModel;

class OcoOcorrencia extends BaseController
{
    public $data = [];
    public $novocorrencia;
    public $novaOcorrencia;
    public $modelTipo;
    public $modelMod;
    public $buscaSapiens;
    public $ocorrencia ;
    public $produtLoteModel;
    public $common;

    public function __construct()
    {
        $this->novocorrencia = new OcorreOcorrenciaModel();
        $this->data = session()->get('dados_tela') ?? [];

        $this->novaOcorrencia  = new EstoquRequisicaoModel();
        $this->modelTipo       = new OcorreTipoOcorrenciaModel();
        $this->modelMod        = new OcorreModOcorrenciaModel();
        $this->buscaSapiens    = new BuscasSapiens();
        $this->produtLoteModel = new ProdutLoteModel();
        $this->ocorrencia      = new OcorreOcorrenciaModel();
        $this->common          = new CommonModel();


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
        $oco_ids_assoc = array_map(fn($o) => $o->oco_id, $dados);
    
        $log = buscaLogTabela('oco_ocorrencia', $oco_ids_assoc);
    
        foreach ($dados as $nov) {
            $nov->usu_nome = $log[$nov->oco_id]['usua_alterou'] ?? '';
        }
    
        return $this->response->setJSON([
            'data' => montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1])
        ]);
    }
    

    public function add($modal = false)
    {
        $oco = new EntOcoOcorrencia();
    
        $this->data['secoes']  = ['Dados Gerais'];
        $this->data['campos']  = [[
            $oco->campos['tpo_id'],
            $oco->campos['tpa_id'],
            $oco->campos['oco_descricao'],
            $oco->campos['lot_lote'],
            $oco->campos['pro_despro'],
            $oco->campos['oco_qtd'],
            $oco->campos['oco_data'],
        ]];
        $this->data['destino'] = 'store';
    
        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }


    public function edit($id)
    {
        $dados = $this->novocorrencia->getById($id);
    
        if (!$dados) {
            throw new \Exception('Ocorrência não encontrada');
        }
        
        $oco    = new EntOcoOcorrencia((array) $dados, true);
        $fields = $oco->campos;
    
        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $fields['tpo_id'],
            $fields['tpa_id'],
            $fields['oco_descricao'],
            $fields['lot_lote'],
            $fields['pro_despro'],
            $fields['oco_qtd'],
            $fields['oco_data'],
        ]];
    
        $this->data['destino'] = "update/$id";
        $this->data['edicao']  = true;
    
        echo view('vw_edicao', $this->data);
    }


    public function update($id)
    {
        $entity = new EntOcoOcorrencia($this->request->getPost());
        $ret    = $this->ocorrencia->atualizarOcorrencia($id, $entity);
        return $this->response->setJSON($ret);
    }
    

    public function delete($id)
    {
        $ret = [];
        try {
            $this->novocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência Excluída com Sucesso');
            $ret['msg']  = 'Ocorrência Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $postado = $this->request->getPost();
        $entity  = new EntOcoOcorrencia($postado);
    
        $ret = $this->ocorrencia->salvarOcorrencia($entity);
    
        return $this->response->setJSON($ret);
    }


    public function getProdutoLote()
    {
        $codLote = $this->request->getPost('codLote');
        if (!$codLote) {
            return $this->response->setJSON([
                'erro' => 'Lote não informado'
            ]);
        }
    
        $dados = $this->produtLoteModel->getLoteSearch($codLote);
    
        if (!$dados || empty($dados)) {
            return $this->response->setJSON([
                'erro' => 'Lote não encontrado'
            ]);
        }
        $lote = $dados[0]; 
    
        return $this->response->setJSON([
            'descpro' => $lote->pro_despro ?? '',
        ]);
    }
}
