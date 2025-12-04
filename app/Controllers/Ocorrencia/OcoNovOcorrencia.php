<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreNovOcorrenciaModel;

class OcoNovOcorrencia extends BaseController
{
    public $data = [];
    public $novocorrencia;

    public function __construct()
    {
        $this->novocorrencia = new OcorreNovOcorrenciaModel();
        $this->data = session()->get('dados_tela') ?? [];

        $this->data['tabela'] = $this->novocorrencia->table;
        $this->data['view']   = $this->novocorrencia->view ?? '';
        session()->set('dados_tela', $this->data); 
    }

    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    

    
    public function lista()
{
    $db = \Config\Database::connect('dbOcorrencia');
    $dados = $db->table($this->data['view'])->get()->getResultArray(); 

    $campos = montaColunasCampos($this->data, 'oco_id');

    $retorno = [
        'data' => montaListaColunas($this->data, 'oco_id', $dados, $campos[1])
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
            $statusPendente = $model->getStatusIdByNome('Pendente');
            $statusFinaliza = $model->getStatusIdByNome('Finalização automática');
    
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
                'tpo_nome' => $dados['tpo_nome'],
                'oco_descricao'   => $dados['oco_descricao'],
                'oco_lote'        => $dados['oco_lote'],
                'oco_qtd'         => $dados['oco_qtd'],
                'oco_produto'     => $dados['oco_produto'],
                'oco_status'      => $status,
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
