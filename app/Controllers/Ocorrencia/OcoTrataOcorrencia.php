<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Entities\Ocorrencia\EntOcoTratativa;
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
        if (!$dados) {
        return $this->response->setJSON(['data' => []]);
        }

        $oco_ids    = array_map(fn($o) => $o->oco_id, $dados);
        $logGeracao = buscaLogTabela('oco_ocorrencia', $oco_ids);

        $base_url = base_url($this->data['controler']);
        foreach ($dados as $nov) {
        
            $usuLog = $logGeracao[$nov->oco_id]['usua_alterou'] ?? '';
            $nov->usu_nome = $usuLog;
        
            if ((int) $nov->stt_id === 30) {
                $nov->usu_fina = $usuLog;
            } else {
                $nov->usu_fina = '';
            }
    
            $nov->acao_person = [];
            if (trim($nov->stt_nome ?? '') === 'Pendente') {
                $url_finalizar = $base_url . '/finalizar/' . $nov->oco_id;
                $nov->acao_person[] = "
                    <button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0'
                        data-mdb-toggle='tooltip'
                        data-mdb-placement='top'
                        title='Finalizar Tratativa'
                        onclick='redireciona(\"$url_finalizar\")'>
                        <i class='fas fa-check'></i>
                    </button>
                ";
            }
        }
        return $this->response->setJSON([
            'data' => montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1])
        ]);
    }


    public function edit($id)
    {
        $result = $this->trataocorrencia->getById($id);
        $dados  = $result[0] ?? null;
    
        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
    
        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? $dados->usu_nome;
    
        $tipo = $this->modelTipo->find($dados->tpo_id);
        $dados->tpo_nome = $tipo->tpo_nome ?? '';
    
        $entity = new EntOcoTratativa($dados, true);
        $fields = $entity->campos;
    
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

    public function finalizar($id)
    {
        $result = $this->trataocorrencia->getById($id);
        $dados  = $result[0] ?? null;
    
        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
    
        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? $dados->usu_nome;
    
        $tipo = $this->modelTipo->find($dados->tpo_id);
        $dados->tpo_nome = $tipo->tpo_nome ?? '';
    
        $entity = new EntOcoTratativa($dados, true);
        $fields = $entity->campos;
    
        $secao[0] = 'Finalizar a Tratativa';
    
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
    
        $this->data['secoes']       = $secao;
        $this->data['campos']       = $campos;
        $this->data['destino']      = "finalizarAction/$id"; 
        $this->data['desc_metodo']  = 'Finalização da';
        $this->data['forca_submit'] = true;

        echo view('vw_edicao', $this->data);
    }
    
    public function finalizarAction($id)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON([
            'erro' => true,
            'msg'  => 'Requisição inválida'
        ]);
    }

    try {

        $builder = $this->trataocorrencia->builder();

        $builder
            ->where('oco_id', $id)
            ->set('stt_id', 30);

        if ($this->request->getPost('oco_justi') !== null) {
            $builder->set('oco_justi', $this->request->getPost('oco_justi'));
        }

        $builder->update();
        $usuarioFinalizou = session()->get('usu_nome') ?? 'Usuário';

        session()->setFlashdata(
            'msg',
            "Tratativa finalizada com sucesso!"
        );

        return $this->response->setJSON([
            'erro' => false,
            'url'  => site_url($this->data['controler'])
        ]);

    } catch (\Exception $e) {

        return $this->response->setJSON([
            'erro' => true,
            'msg'  => 'Erro ao finalizar tratativa:<br><br>' . $e->getMessage()
        ]);
    }
}
    

    public function update($id)
    {
        $dados = $this->request->getPost();
    
        try {
                $this->trataocorrencia->update($id, [
                    'oco_justi' => $dados['oco_justi'] ?? '',
                    'oco_data'  => date('Y-m-d H:i:s'),
                ]);
    
                session()->setFlashdata(
                    'msg',
                    'Tratativa atualizada com sucesso!'
                );
    
            return $this->response->setJSON([
                'erro' => false,
                'msg'  => session()->getFlashdata('msg'),
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
            session()->setFlashdata('msg', 'Tratativa Excluída com Sucesso');
            $ret['msg']  = 'Tratativa Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Tratativa, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }

    public function view($id)
    {
        $dados = $this->trataocorrencia->getView($id);
    
        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
    
        $secao[0]  = 'Dados Gerais';
        $campos[0] = [];
    
        foreach ($dados as $dado) {
    
            $entity = new EntOcoTratativa($dado, true);
            $fields = $entity->campos;
    
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
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = '';
        $this->data['visualizacao'] = true;
    
        echo view('vw_visualizacao', $this->data);
    }
}
