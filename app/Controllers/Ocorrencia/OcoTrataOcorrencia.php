<?php

namespace App\Controllers\Ocorrencia;

use App\Libraries\MyCampo;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Controllers\BaseController;
use App\Models\Config\ConfigStatusModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Produt\ProdutProdutoModel;

class OcoTrataOcorrencia extends BaseController
{
    public $data = [];
    public $permissao;
    public $ocorrencia;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];

        // debug($this->data, true);
        // Inicialização dos models auxiliares
        $this->ocorrencia = new OcorreOcorrenciaModel();

        // debug($this->data['erromsg'], true);
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }


    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        // Renderiza view de listagem
        echo view('vw_lista', $this->data);
    }


    public function lista()
    {
        // Monta definição dos campos da listagem
        $campos   = montaColunasCampos($this->data, 'oco_id');
        $dados    = $this->ocorrencia->getListaCompleta(false, [28]);

        // Caso não existam registros
        if (!$dados) {
            return $this->response->setJSON(['data' => []]);
        }

        $oco_ids    = array_map(fn($o) => $o->oco_id, $dados);
        $logGeracao = buscaLogTabela('oco_ocorrencia', $oco_ids);

        $this->data['exclusao'] = false;   // não tem exclusão
        $this->data['edicao']   = false;   // não tem edição
        $this->data['allconsulta']   = true;   // não tem edição

        $base_url = base_url($this->data['controler']);

        // Processa cada ocorrência
        foreach ($dados as $nov) {

            // Usuário que realizou a última alteração
            $usuLog = $logGeracao[$nov->oco_id]['usua_alterou'] ?? '';
            $nov->usu_nome = $usuLog;

            // Define usuário de finalização se estiver finalizada
            if ((int) $nov->stt_id === 30) {
                $nov->usu_fina = $usuLog;
            } else {
                $nov->usu_fina = '';
            }

            $nov->acao_person = [];

            // Botão de finalizar se estiver pendente
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
        // Retorna JSON formatado para DataTable
        return $this->response->setJSON([
            'data' => montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1])
        ]);
    }

    public function show($id)
    {
        return redirect()->to('/OcoOcorrencia/show/' . $id);
    }


    public function finalizar($id)
    {
        $dados = $this->ocorrencia->getOcorrencia($id);

        $statModel = new ConfigStatusModel();
        $status   = $statModel->getStatus($dados->stt_id);
        $etiqueta = '';
        if ($status) {
            $etiqueta = fmtEtiquetaCor($status->cor_valorrgb ?? '', $status->stt_nome ?? '', 1);
        }
        
        if (!$dados) {
            throw new \Exception('Ocorrência não encontrada');
        }
        // $sttPrincipal = $dados->stt_id ?? null;
    
    
        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $usuLog = $log[$id]['usua_alterou'] ?? null;
    
        // ações 
        $acoes = $this->ocorrencia->getAcoesFinalizar($id);
    
        //  DADOS DO TOPO 
        if (!empty($acoes)) {
            $dados = clone $acoes[0]; // não altera $acoes[0]
        } else {
            $dados = $dados;
        }
    
        // mantém usuário do log
        if ($usuLog) {
            $dados->usu_nome = $usuLog;
        }
        // $dados->stt_id = $sttPrincipal;
    
        $entity = new EntOcoTratativa($dados, true);
        $fields = $entity->campos;

        $secao[0] = 'Dados da Ocorrência';
        $campos[0] = [];

        $campos[0][] = $fields['oco_id'];
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['sut_id'];
        $campos[0][] = $fields['usu_nome'];
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['oco_data'];
        // status do TOPO, no defCampos!
        if (isset($fields['stt_nome'])) {
            $campos[0][] = $fields['stt_nome'];
        }
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['pro_despro'];
        $campos[0][] = $fields['oco_qtd'];
        // $campos[0][] = $fields['tpa_id'];
    
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

        //  debug($fields, true);
        
        // BLOCO DAS AÇÕES 
        if (!empty($acoes)) {
    
            $acoesResultado = [];
    
            foreach ($acoes as $acao) {
                $acao->somente_leitura = false;
                $camposAcao = $entity->defCamposAcao($acao);
                $acoesResultado[] = $camposAcao;
            }
    
            $campos[0][] = view(
                'partials/pw_acoes_ocorrencia',
                [
                    'acoes'  => $acoesResultado,
                    'oco_id' => $id
                ]
            );
        }
        
    
        // CONFIG VIEW
        $this->data['show']        = true;
        $this->data['title']       = 'Ocorrência';
        $this->data['desc_edicao'] = ' Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' - ' . $etiqueta;
        
        $this->data['secoes']       = $secao;
        $this->data['campos']       = $campos;
        $this->data['destino']      = "store";
        $this->data['desc_metodo']  = '';
        $this->data['forca_submit'] = true;
    
        $this->data['hidden'][] = [
            'name'  => 'finalizar',
            'value' => 1
        ];
            
        echo view('vw_edicao', $this->data);
    }

    

    public function store()
    {
        // debug('ENTROU NO STORE');
        $postado = $this->request->getPost();
        // debug($postado);
        // debug($this->request->getPost(), true);
    
        try {
    
            $ocoId = $this->request->getPost('oco_id');

            if (empty($ocoId)) {
                throw new \Exception('ID da ocorrência não informado para finalização');
            }
            
            $oco = $this->ocorrencia->find($ocoId);
            
            if (!$oco) {
                throw new \Exception('Ocorrência não encontrada');
            }
    
            // confirmação obrigatória
            $acao = (new OcorreModOcorrenciaModel())
            ->getAcaoConfigurada(
                (int) $oco->tpo_id,
                $oco->sut_id
            );
                    
            if (!$acao) {
                throw new \Exception('Ação não configurada para este tipo/subtipo');
            }
            
            if ($acao && (int)$acao->tpa_id === 3) {
                return $this->response->setJSON([
                    'erro' => true,
                    'msg'  => 6
                ]);
            }
            
            // ADIÇÃO: DECISÃO STT e TMO
            $movs    = [];
            $novoStt = null;
            
            
            
            // MOVIMENTAÇÃO
            if ((int)$acao->tpa_id === 3) {
            
                if (!empty($postado['tmo_id']) && (int)($postado['oco_qtd'] ?? 0) > 0) {
                    $movs[] = [
                        'id'  => (int)$postado['tmo_id'],
                        'qt'  => (int)$postado['oco_qtd'],
                        'msg' => 'Ação da tratativa'
                    ];
                }
            }
            
            // ALTERAR STATUS
            if ((int)$acao->tpa_id === 7) {
            
                if (empty($acao->stt_id)) {
                    throw new \Exception('Ação "Alterar Status" sem status configurado');
                }
            
                $novoStt = (int)$acao->stt_id;
            
                // PRODUTO
                $produtoModel = new ProdutProdutoModel();
                $produtoModel->update($oco->pro_id, [
                    'stt_id' => $novoStt
                ]);
            
                // OCORRÊNCIA = SEMPRE FINALIZADA
                $this->ocorrencia->update($oco->oco_id, [
                    'stt_id'       => 30,
                    'usu_fina'     => session()->get('usu_nome'),
                    'oco_data_fim' => date('Y-m-d H:i:s')
                ]);
            
                $novoStt = null;
            }

            // Gera movimentos se existirem
            if (!empty($movs)) {
                cache()->clean();
                $movim = geraMovimentoSOAP($movs, $postado, $this->data);
            
                if ($movim['status'] == 'Erro') {
                    $ret['erro'] = true;
                    $ret['msg']  = $movim['mensagem'];
                }
            }
    
            // FINALIZA 
            $postado['stt_id']       = $novoStt ?? 30;
            $postado['usu_fina']     = session()->get('usu_nome');
            $postado['oco_data_fim'] = date('Y-m-d H:i:s');
    
            unset(
                $postado['sut_id'],
                $postado['tpa_id'],
                $postado['tmo_id'],
                $postado['tel_id']
            );
    
            $entity = new EntOcoOcorrencia($postado);
    
            if (!$this->ocorrencia->save($entity)) {
                throw new \Exception(implode('<br>', $this->ocorrencia->errors()));
            }

            session()->setFlashdata('msg', 'Ocorrência gravada com sucesso!');
    
            return $this->response->setJSON([
                'erro' => false,
                'url'  => site_url($this->data['controler'])
            ]);
    
        } catch (\Exception $e) {
    
            return $this->response->setJSON([
                'erro' => true,
                'msg'  => $e->getMessage()
            ]);
        }
    }
}
