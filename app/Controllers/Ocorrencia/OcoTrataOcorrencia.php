<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTrataOcorrenciaModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Controllers\BuscasSapiens;
use App\Models\Produt\ProdutLoteModel;

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
        $this->ocorreNovOcorrenciaModel = new OcorreOcorrenciaModel();


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
        $campos   = montaColunasCampos($this->data, 'oco_id');
        $dados    = $this->trataocorrencia->getListaCompleta();
        $oco_ids  = array_column($dados, 'oco_id');
    
        $logGeracao = buscaLogTabela('oco_ocorrencia', $oco_ids);
    
        foreach ($dados as &$nov) {
    
            $nov['usu_nome'] = $logGeracao[$nov['oco_id']]['usua_alterou'] ?? '';
    
            if (trim($nov['stt_nome']) === 'Finalização automática') {
                $nov['usu_fina'] = $nov['usu_nome'];
            } else {
                $nov['usu_fina'] = '';
            }
            
        }
    
        return $this->response->setJSON([
            'data' => montaListaColunas($this->data, 'oco_id', $dados, $campos[1])
        ]);
    }


    public function edit($id)
    {
        
        $dados = $this->trataocorrencia->getById($id);

        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }

        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados['usu_nome'] = $log[$id]['usua_alterou'] ?? $dados['usu_nome'];

        $tipo = $this->modelTipo->find($dados['tpo_id']);
        $dados['tpo_nome'] = $tipo['tpo_nome'] ?? '';

        $fields = $this->trataocorrencia->defCampos($dados, true);

        $secao[0] = 'Dados Gerais';
    
        $campos[0][] = $fields['tpo_id']; 
        $campos[0][] = $fields['usu_nome'];          
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['oco_data'];
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['pro_despro'];   
        $campos[0][] = $fields['oco_qtd'];
        $campos[0][] = $fields['tpa_id'];
        if (isset($fields['oco_justi'])) {
            $campos[0][] = $fields['oco_justi'];
        }
        if (isset($fields['tmo_id'])) {
            $campos[0][] = $fields['tmo_id'];
        }
        if (isset($fields['stt_id'])) {
            $campos[0][] = $fields['stt_id'];
        }
        if (isset($fields['tel_id'])) {
            $campos[0][] = $fields['tel_id'];
        }
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = "update/$id";
        $this->data['edicao']  = true;
    
        echo view('vw_edicao', $this->data);
    }


    public function update($id)
    {
        $dados = $this->request->getPost();
    
        try {
    
            $this->trataocorrencia->update($id, [
                'oco_justi' => $dados['oco_justi'] ?? ''
            ]);
            
            $msg = 'Tratativa atualizada com sucesso!';
            session()->setFlashdata('msg', $msg);
    
            return $this->response->setJSON([
                'erro' => false,
                'msg'  => $msg,
                'url'  => site_url($this->data['controler']) 
            ]);
    
        } catch (\Exception $e) {
    
            return $this->response->setJSON([
                'erro' => true,
                'msg'  => 'Erro ao atualizar ocorrência:<br><br>' . $e->getMessage()
            ]);
        }
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



    public function view($id)
    {
         $dados = $this->trataocorrencia->getView($id);
     
         if (!$dados) {
             throw new \Exception("Ocorrência não encontrada");
         }
     
         $secao[0] = 'Dados Gerais';
         $campos[0] = [];
     
         foreach ($dados as $dado) {
             $fields = $this->trataocorrencia->defCampos($dado, true);
     
             $campos[0][] = $fields['tpo_id'];
             $campos[0][] = $fields['usu_nome'];
             $campos[0][] = $fields['oco_descricao'];
             $campos[0][] = $fields['oco_data'];
             $campos[0][] = $fields['lot_lote'];
             $campos[0][] = $fields['pro_despro'];
             $campos[0][] = $fields['oco_qtd'];
             $campos[0][] = $fields['tpa_id'];
     
             if (isset($fields['oco_justi'])) {
                 $campos[0][] = $fields['oco_justi'];
             }
             if (isset($fields['tmo_id'])) {
                 $campos[0][] = $fields['tmo_id'];
             }
             if (isset($fields['stt_id'])) {
                 $campos[0][] = $fields['stt_id'];
             }
             if (isset($fields['tel_id'])) {
                 $campos[0][] = $fields['tel_id'];
             }
         }
     
         $this->data['secoes'] = $secao;
         $this->data['campos'] = $campos;
         $this->data['destino'] = '';
         $this->data['visualizacao'] = true;
     
         echo view('vw_visualizacao', $this->data);
     }
}
