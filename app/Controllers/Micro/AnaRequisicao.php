<?php

namespace App\Controllers\Micro;

use DateTime;
use Config\Database;
use App\Entities\Microb\EntMicrobAnaRequisicao;
use App\Controllers\BaseController;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\CommonModel;
use App\Models\Microb\MicrobAnaRequisicaoModel;
use App\Models\Microb\MicrobAnaliseModel;

class AnaRequisicao extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $analise;
    public $anarequisicao;
    public $produto;
    public $lote;
    public $tipomovimento;
    public $common;

    /**
     * Construtor da Analise
     * construct
     */
    public function __construct()
    {
        $this->data          = session()->getFlashdata('dados_tela');
        $this->permissao     = $this->data['permissao'];
        $this->analise       = new MicrobAnaliseModel();
        $this->anarequisicao = new MicrobAnaRequisicaoModel();
        $this->common        = new CommonModel();

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
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'ana_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'req_id');
        $dados_tela = $this->anarequisicao->getListaRequisicao();
    
        foreach ($dados_tela as $req) {
    
            $url_ati = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $req->req_id);
    
            $req->acao_person = [
                "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0 float-end'
                    data-mdb-toggle='tooltip'
                    data-mdb-placement='top'
                    title='Imprimir Requisição'
                    onclick='openPDFModal(\"{$url_ati}\",\"Imprimir Requisição\")'>
                    <i class='fa-solid fa-print'></i>
                </button>"
            ];
        }
    
        $this->data['edicao'] = false;
    
        $anarequis = ['data' => montaListaColunasEnt($this->data,'req_id',$dados_tela,$campos[1]),];
        cache()->save('anarequis', $anarequis, 60000);
    
        return $this->response->setJSON($anarequis);
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        // Busca a requisição pelo ID
        $requis = $this->anarequisicao->getListaRequisicao($id);
    
        if (!$requis) {
            return $this->index();
        }
    
        // Primeira posição contém os dados principais da requisição
        /** @var object $req */
        $req = $requis[0];
    
        $entity = new EntMicrobAnaRequisicao((array) $req,true);
    
        $fields = $entity->campos;
    
        // DADOS GERAIS
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
    
        // Caso exista lote microbiológico
        if (!empty($req->req_lotemb)) {
            $campos[0][1] = $fields['req_lotemb'];
            $prox = 2;
        } else {
            // Caso não exista, exibe também o método de análise
            $campos[0][1] = $fields['req_lotemb'];
            $campos[0][2] = $fields['ana_descmetodo'];
            $prox = 3;
        }
    
        // LISTA DE PRODUTOS
        $texto = "<div class='col-12 float-start d-block mt-5'>";
        $texto .= "<div class='col-4 float-start fw-bold'>Produto</div>";
        $texto .= "<div class='col-4 float-start fw-bold'>Fabricante</div>";
        $texto .= "<div class='col-2 float-start fw-bold'>Lote</div>";
        $texto .= "<div class='col-2 float-start fw-bold'>Validade</div>";
    
        // Percorre produtos vinculados à requisição
        foreach ($requis as $prod) {
            $texto .= "<div class='col-4 float-start'>{$prod->pro_despro}</div>";
            $texto .= "<div class='col-4 float-start'>{$prod->fab_apeFab}</div>";
            $texto .= "<div class='col-2 float-start'>{$prod->lot_lote}</div>";
            $texto .= "<div class='col-2 float-start'>" . data_br($prod->lot_validade) . "</div>";
        }
    
        $texto .= "</div>";
        // Adiciona bloco HTML à tela
        $campos[0][$prox] = $texto;
    
        // Define dados da view
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';
    
        echo view('vw_edicao', $this->data);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        // Busca análises pendentes sem requisição
        $dados_analise = $this->analise->getListaAnaliseSemReq($id);

        // Caso não encontre análises, retorna à listagem
        if (!$dados_analise || count($dados_analise) === 0) {
            return $this->index();
        }
    
        // Primeira análise
        $analise = (array) $dados_analise[0];
        $lotemb = $analise['ana_lotemb'];

        // Busca produtos vinculados ao lote
        $dados_requisi = $this->analise->getAnaliseLotemb($lotemb);
        
        // Cria entity para edição
        $entity = new EntMicrobAnaRequisicao($analise, false);
        $fields = $entity->campos;
    
        // DADOS GERAIS
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
    
        if (!empty($analise['ana_lotemb'])) {
            $campos[0][1] = $fields['req_lotemb'];
            $prox = 2;
        } else {
            $campos[0][1] = $fields['req_lotemb'];
            $campos[0][2] = $fields['ana_descmetodo'];
            $prox = 3;
        }
    
            // LISTA DE PRODUTOS
            $texto = "<div class='col-12 float-start d-block mt-5'>";
            $texto .= "<div class='row border border-2'>";
            $texto .= "<div class='col-4 fw-bold'>Produto</div>";
            $texto .= "<div class='col-4 fw-bold'>Fabricante</div>";
            $texto .= "<div class='col-2 fw-bold'>Lote</div>";
            $texto .= "<div class='col-2 fw-bold'>Validade</div>";
            $texto .= "</div>";
    
        foreach ($dados_requisi as $prod) {
            $prod = (array) $prod;
            $texto .= "<div class='row border border-1'>";
            $texto .= "<div class='col-4'>" . $prod['pro_despro'] . "</div>";
            $texto .= "<div class='col-4'>" . $prod['fab_apeFab'] . "</div>";
            $texto .= "<div class='col-2'>" . $prod['lot_lote'] . "</div>";
            $texto .= "<div class='col-2'>" . data_br($prod['lot_validade']) . "</div>";
            $texto .= "</div>";
        }
    
        $texto .= "</div>";
        $campos[0][$prox] = $texto;
    
        // Define dados da view
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>jQuery('#form1').attr('data-alter', true);</script>";
    
        echo view('vw_edicao', $this->data);
    }

    /**
     * Gerar Requisicao
     * gerar
     *
     * @param mixed $id 
     * @return void
     */
    public function add()
    {
        // Definição das colunas da listagem
        $fields[0] = 'Id';
        $fields[1] = 'Produto';
        $fields[2] = 'Fabricante';
        $fields[3] = 'Lote';
        $fields[4] = 'Lote MB';
        $fields[5] = 'Data';
        $fields[6] = 'Status';
        $fields[7] = 'Usuário';
        $fields[8] = 'Ação';

        $this->data['colunas'] = $fields;
        $this->data['desc_metodo'] = '';
        $this->data['mostrar']  = true; // não mostrar botão salvar e cancelar
        $this->data['url_lista'] = base_url($this->data['controler'] . '/listarequisicao');

        echo view('vw_lista', $this->data);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        // $requis = $this->anarequisicao->getListaRequisicao($id);
        // // debug($requis);
        // $ret = [];
        // if ($requis) {
        //     $ret['erro'] = false;
        //     $req = $requis[0];
        //     $reqid = $req['req_id'];

        //     // Busca análises associadas à requisição
        //     $analises = $this->analise->getListaAnaliseComReq($reqid);

        //     if($analises){
        //         // Atualiza status das análises
        //         $resultado = $this->atualizaStatusAnalise($analises);

        //         switch ($resultado) {
        //             case 0:
        //                 $ret['erro'] = true;
        //                 $ret['msg']  = 'Não foi possível Atualizar as Análise, Verifique!<br><br>';
        //                 break;
        //             case 2:
        //                 $ret['erro'] = true;
        //                 $ret['msg']  = 3;
        //                 break;
        //         }
        //     } else {
        //         $ret['erro'] = true;
        //         $ret['msg']  = 3;
        //     }
        //     // Caso não haja erro, exclui a requisição
        //     if(!$ret['erro']){
        //         try {
        //             $this->analise->delete($id);
        //             $ret['erro'] = false;
        //             cache()->clean();
        //             session()->setFlashdata('msg', 'Requisição Excluída com Sucesso');
        //             $ret['msg']  = 'Requisição Excluída com Sucesso';
        //         } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
        //             $ret['erro'] = true;
        //             $ret['msg']  = 'Não foi possível Excluir essa Requisição, Verifique!<br><br>';
        //         }
        //     }
        // }

        // echo json_encode($ret);

        $requis = $this->anarequisicao->getListaRequisicao($id);
        // debug($requis);
        $ret = [];
        
        if ($requis) {
            $ret['erro'] = false;
            $req   = $requis[0];
            $reqid = $req->req_id;
        
            // Busca análises associadas à requisição
            $analises = $this->analise->getListaAnaliseComReq($reqid);
        
            if ($analises) {
                // Atualiza status das análises
                $resultado = $this->atualizaStatusAnalise($analises);
        
                switch ($resultado) {
                    case 0:
                        $ret['erro'] = true;
                        $ret['msg']  = 'Não foi possível Atualizar as Análise, Verifique!<br><br>';
                        break;
                    case 2:
                        $ret['erro'] = true;
                        $ret['msg']  = 3;
                        break;
                }
            } else {
                $ret['erro'] = true;
                $ret['msg']  = 3;
            }
        
            // Caso não haja erro, exclui a requisição
            if (!$ret['erro']) {
                try {
        
                    // ADIÇÃO 
                    $this->verificarUsoEmRelacionamentos('ana_requisicao', 'req_id', (int) $id);
                    $this->analise->delete($id);

                    $ret['erro'] = false;
                    cache()->clean();
                    session()->setFlashdata('msg', 'Requisição Excluída com Sucesso');
                    $ret['msg']  = 'Requisição Excluída com Sucesso';
        
                } catch (\Exception $e) {
                    $ret['erro'] = true;
                    $ret['msg']  = 3;
                }
            }
        }
        
        echo json_encode($ret);
    }

    private function atualizaStatusAnalise(array $analises): int
    {
        // Verifica antes se todos têm stt_id == 14
        foreach ($analises as $item) {
            if (!isset($item->stt_id) || $item->stt_id != 14) { //14 é status realizada
                return 2; // Tem pelo menos um com status diferente de 14
            }
        }

        $this->analise->transStart();
        try {
            foreach ($analises as $item) {
                if (!empty($item->ana_id)) {
                    $this->analise->save([
                        'ana_id' => $item->ana_id,
                        'req_id' => NULL,
                        'stt_id' => 11 // PENDENTE
                    ]);
                }
            }
            $this->analise->transCommit();
            return 1; // Sucesso
        } catch (\Exception $e) {
            $this->analise->transRollback();
            log_message('error', 'Erro ao atualizar análise: ' . $e->getMessage());
            return 0; // Falha com exceção
        }
    }

   /**
     * Lista das Requisições
     * listarequisicao
     *
     * @param mixed $id 
     * @return void
     */
    public function listarequisicao()
    {
        // Colunas retornadas
        $fields[0] = 'pro_despro';
        $fields[1] = 'fab_apeFab';
        $fields[2] = 'lot_lote';
        $fields[3] = 'ana_lotemb';
        $fields[4] = 'ana_data';
        $fields[5] = 'stt_nome';
        $fields[6] = 'usu_nome';
    
        // RETORNA OBJ (stdClass)
        $analis = $this->analise->getListaAnaliseSemReq(false, 14);
    
        if (!$analis) {
            return $this->response->setJSON(['data' => []]);
        }
    
        // Ajusta dados de exibição
        for ($da = 0; $da < count($analis); $da++) {
    
            $ent = $analis[$da]; // stdClass
    
            $log = buscaLog('pro_mic_analise', $ent->ana_id);
    
            $ent->usu_nome = $log['usua_alterou'] ?? '';
            $ent->stt_cor  = 'bg-warning';
            $ent->stt_nome = 'Pendente';
        }
    
        return $this->response->setJSON([
            'data' => montaListaEditColunas(
                $fields,
                $this->data,
                'ana_id',
                $analis,
                'pro_despro'
            )
        ]);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $ret['erro'] = false;
        $erros = [];
    
        $db = Database::connect();
        $db->transBegin();
    
        try {
            // Data atual
            $data_atual = new DateTime();
            $datatu_fmt = $data_atual->format('Y-m-d H:i:s');
    
            // Dados da requisição
            $dados_req = [
                'req_data'   => $datatu_fmt,
                'req_lotemb' => $postado['req_lotemb'],
                'usu_id'     => session()->get('usu_id'),
            ];
    
            // Salva requisição
            if (!$this->anarequisicao->save($dados_req)) {
                $erros = $this->anarequisicao->errors();
                throw new \Exception(
                    'Não foi possível gravar a Requisição. ' . implode(' ', $erros)
                );
            }
    
            // ID da requisição criada
            $req_id = $this->anarequisicao->getInsertID();
    
            // Busca análises do lote
            $dados_analise = $this->analise->getAnaliseLotemb($postado['req_lotemb']);
    
            // Atualiza análises com ID da requisição
            for ($da = 0; $da < count($dados_analise); $da++) {
                $analise = (array) $dados_analise[$da];
    
                $dados_ana = [
                    'ana_id' => $analise['ana_id'],
                    'req_id' => $req_id,
                ];
    
                if (!$this->analise->save($dados_ana)) {
                    $erros = $this->analise->errors();
                    throw new \Exception(
                        'Não foi possível atualizar as Análises. ' . implode(' ', $erros)
                    );
                }
            }
    
            // COMMIT
            $db->transCommit();
    
            // Mensagens e impressão
            $ret['msg'] = 'Dados da Requisição gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
    
            $link = base_url('/CriaPdf2025/PrintAnaRequisicao/' . $req_id);
            $script = "openPDFModal(\"{$link}\",\"Imprimir Requisição\")";
    
            session()->setFlashdata('script', $script);
            session()->setFlashdata('modal', $link);
            session()->setFlashdata('modal-title', 'Imprimir Requisição');
    
            $ret['url'] = site_url($this->data['controler']);
    
        } catch (\Exception $e) {
    
            // ROLLBACK
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = $e->getMessage();
        }
    
        return $this->response->setJSON($ret);
    }
}
