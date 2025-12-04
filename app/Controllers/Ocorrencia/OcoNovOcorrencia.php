<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreNovOcorrenciaModel;
use App\Models\Estoqu\EstoquRequisicaoModel;

class OcoNovOcorrencia extends BaseController
{
    public $data = [];
    public $novocorrencia;
    public $novaOcorrencia;

    public function __construct()
    {
        $this->novocorrencia = new OcorreNovOcorrenciaModel();
        $this->data = session()->get('dados_tela') ?? [];

        $this->novaOcorrencia  = new EstoquRequisicaoModel();
        $this->data['tabela']  = $this->novocorrencia->table;
        $this->data['view']    = $this->novocorrencia->view ?? '';
        session()->set('dados_tela', $this->data); 
    }

    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    

// public function lista() 
// { 
//     $db = \Config\Database::connect('dbOcorrencia'); 
//     $dados = $db->table($this->data['view'])->get()->getResultArray(); 

//     $campos = montaColunasCampos($this->data, 'oco_id'); 
    
//     $retorno = [ 'data' => montaListaColunas($this->data, 'oco_id', $dados, $campos[1]) ]; 
//     return $this->response->setJSON($retorno); 
// }
    
    
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'oco_id');
    
        // Buscando corretamente da VIEW
        $db = \Config\Database::connect('dbOcorrencia');
        $dados = $db->table($this->data['view'])->get()->getResultArray();
    
        // Buscar log
        $oco_ids_assoc = array_column($dados, 'oco_id');
        $log = buscaLogTabela('oco_nov_ocorrencia', $oco_ids_assoc);
    
        $base_url = base_url($this->data['controler']);
    
        foreach ($dados as &$nov) {
    
            if ($nov['oco_id']) {
    
                // Status badge
                $nov['stt_nome'] = $nov['stt_nome']; 
                $nov['stt_cor']  = $nov['stt_cor'];
    
                $nov['usu_nome'] = $log[$nov['oco_id']]['usua_alterou'] ?? '';
    
                // URLs corretas
                $url_edit = $base_url . '/edit/' . $nov['oco_id'];
    
                $nov['acao_person'] = [
                    "<button class='btn btn-outline-warning btn-sm border-0 mx-0 fs-0'
                        data-mdb-toggle='tooltip' data-mdb-placement='top'
                        title='Editar Ocorrência' onclick='redireciona(\"$url_edit\")'>
                        <i class='fas fa-pen-to-square'></i></button>",
                ];
            }
        }
    
        $this->data['edicao'] = false;
    
        $retorno = [
            'data' => montaListaColunas($this->data, 'oco_id', $dados, $campos[1]),
        ];
    
        return $this->response->setJSON($retorno);
    }



    public function add()
    {
        $fields = $this->novocorrencia->defCampos([], false, $this->data['tabela'], $this->data['view']);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['oco_tipo'];
        $campos[0][1] = $fields['tpo_nome'];
        $campos[0][2] = $fields['oco_descricao'];
        $campos[0][3] = $fields['oco_lote'];
        $campos[0][4] = '';
        $campos[0][5] = $fields['oco_produto'];
        $campos[0][6] = $fields['oco_qtd'];

        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }


    public function getProdutoLote()
    {
        $codLote = $this->request->getPost('codLote');

        if (!$codLote) {
            return $this->response->setJSON(['erro' => 'Lote não informado']);
        }

        $model = new \App\Models\Produt\ProdutLoteModel();
        $dados = $model->getLoteSearch($codLote);

        if (!$dados || empty($dados)) {
            return $this->response->setJSON(['erro' => 'Lote não encontrado']);
        }

        $lote = $dados[0];

        return $this->response->setJSON([
            'descpro' => $lote['pro_despro'] ?? '',
        ]);
    }



    public function edit($id)
    {
        $db = \Config\Database::connect('dbOcorrencia');
    
        // Buscar da VIEW 
        $dados = $db->table($this->data['view'])
                    ->where('oco_id', $id)
                    ->get()
                    ->getRowArray();
    
        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }
    
        // Montar campos do formulário
        $fields = $this->novocorrencia->defCampos($dados, true, $this->data['tabela'], $this->data['view']);
    
        // ORGANIZAÇÃO DOS CAMPOS 
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['oco_tipo'];
        $campos[0][1] = $fields['tpo_nome'];
        $campos[0][2] = $fields['oco_descricao'];
        $campos[0][3] = $fields['oco_lote'];
        $campos[0][4] = $fields['oco_produto'];
        $campos[0][5] = $fields['oco_qtd'];
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = "update/$id";
        $this->data['edicao']  = true;
    
        echo view('vw_edicao', $this->data);
    }


        public function update($id)
    {
        $db = \Config\Database::connect('dbOcorrencia');
        $ret = [];
        $dados = $this->request->getPost();
    
        try {
            $db->transBegin();
    
            $model = new \App\Models\Ocorre\OcorreNovOcorrenciaModel();
    
            // Verifica se ocorrência existe
            $ocorrencia = $model->find($id);
            if (!$ocorrencia) {
                throw new \Exception('Ocorrência não encontrada.');
            }

            $statusPendente = $model->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $model->getStatusIdByNome('Finalização automática', 56);
    
            $status = $statusPendente;
    
            $moc_id = $dados['moc_id'] ?? null;
    
            if ($moc_id) {
                $acoes = $db->table('oco_moc_acao')
                            ->where('moc_id', $moc_id)
                            ->get()
                            ->getResultArray();
    
                if (empty($acoes)) {
                    $status = $statusFinaliza;
                } else {
                    $todasNenhuma = true;
                    foreach ($acoes as $acao) {
                        if ($acao['tpa_id'] != 1) {
                            $todasNenhuma = false;
                            break;
                        }
                    }
    
                    if ($todasNenhuma) {
                        $status = $statusFinaliza;
                    } else {
                        $status = $statusPendente;
                    }
                }
            }
            // Atualiza os dados
            $atualiza = [
                'oco_tipo'      => $dados['oco_tipo'],
                // 'tpo_nome'      => $dados['tpo_nome'],
                'oco_descricao' => $dados['oco_descricao'],
                'oco_lote'      => $dados['oco_lote'],
                'oco_qtd'       => $dados['oco_qtd'],
                'oco_produto'   => $dados['oco_produto'],
                'oco_data'      => $dados['oco_data'] ?? date('Y-m-d H:i:s'),
                'stt_id'        => $status,
            ];
    
            if (!$model->update($id, $atualiza)) {
                throw new \Exception('Erro ao atualizar ocorrência.');
            }
    
            $db->transCommit();
    
            $ret['erro'] = false;
            $ret['msg']  = 'Ocorrência atualizada com sucesso!';
            $ret['url']  = site_url('OcoNovOcorrencia');
            session()->setFlashdata('msg', $ret['msg']);
        } catch (\Exception $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao atualizar a ocorrência: <br><br>' . $e->getMessage();
        }
    
        return $this->response->setJSON($ret);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->novocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tipo de Ocorrência Excluída com Sucesso');
            $ret['msg'] = 'Tipo de Ocorrência Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $this->data['tabela'] = 'oco_nov_ocorrencia';
        session()->set('dados_tela', $this->data);
    
        $ret = [];
        $dados = $this->request->getPost();
        $db = \Config\Database::connect('dbOcorrencia');
    
        try {
            $db->transBegin();
            $model = new \App\Models\Ocorre\OcorreNovOcorrenciaModel();
    
            // Busca os status dinamicamente
            $statusPendente = $model->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $model->getStatusIdByNome('Finalização automática', 56);
    
            $status = $statusPendente;
    
            $moc_id = $dados['tpo_nome'];
            $acoes = $db->table('oco_moc_acao')
                        ->where('moc_id', $moc_id)
                        ->get()
                        ->getResultArray();
    
            // Verifica se deve ser finalização automática
            if (empty($acoes)) {
                $status = $statusFinaliza;
            } else {
                $todasNenhuma = true;
                foreach ($acoes as $acao) {
                    if ($acao['tpa_id'] != 1) {
                        $todasNenhuma = false;
                        break;
                    }
                }
    
                if ($todasNenhuma) {
                    $status = $statusFinaliza;
                } else {
                    $status = $statusPendente;
                }
            }
    
            // Monta os dados da ocorrência
            $insert = [
                'oco_tipo'        => $dados['oco_tipo'],
                'tpo_nome'        => $dados['tpo_nome'],
                'oco_descricao'   => $dados['oco_descricao'],
                'oco_lote'        => $dados['oco_lote'],
                'oco_qtd'         => $dados['oco_qtd'],
                'oco_produto'     => $dados['oco_produto'],
                'stt_id'          => $status,
                'oco_data'        => date('Y-m-d H:i:s')
            ];
    
            if (!$model->insert($insert)) {
                throw new \Exception('Erro ao inserir ocorrência.');
            }
    
            $db->transCommit();
    
            $ret['erro'] = false;
            $ret['msg']  = 'Ocorrência registrada com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url('OcoNovOcorrencia');
        } catch (\Exception $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao gravar a Ocorrência:<br><br>' . $e->getMessage();
        }
    
        return $this->response->setJSON($ret);
    }


    public function getOcorrenciasPorTipo()
    {
        $tipoId = $this->request->getPost('tipo');
    
        if (!$tipoId) {
            return $this->response->setJSON(['erro' => 'Tipo não informado']);
        }
    
        $db = \Config\Database::connect('dbOcorrencia');
        $dados = $db->table('oco_mod_ocorrencia')
                    ->select('moc_id, moc_nome')
                    ->where('tpo_id', $tipoId)
                    ->get()
                    ->getResultArray();
    
        return $this->response->setJSON($dados);
    }

}
