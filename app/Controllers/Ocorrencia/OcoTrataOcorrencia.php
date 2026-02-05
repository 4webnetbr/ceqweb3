<?php

namespace App\Controllers\Ocorrencia;

use App\Libraries\MyCampo;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreOcorrenciaModel;

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
        $dados    = $this->ocorrencia->getListaCompleta();

        // Caso não existam registros
        if (!$dados) {
            return $this->response->setJSON(['data' => []]);
        }

        $oco_ids    = array_map(fn($o) => $o->oco_id, $dados);
        $logGeracao = buscaLogTabela('oco_ocorrencia', $oco_ids);

        $this->data['permissao'] = 'C';
        $this->data['exclusao'] = false;   // não tem exclusão
        $this->data['edicao']   = false;   // não tem edição

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
        $dados = $this->ocorrencia->find($id);
    
        if (!$dados) {
            session()->setFlashdata('erromsg', 'Ocorrência não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }
    
        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        if (isset($log[$id]['usua_alterou'])) {
            $dados->usu_nome = $log[$id]['usua_alterou'];
        }
    
        $acoes = $this->ocorrencia->getAcoesFinalizar($id);
    
        if ($acoes) {
            // usa a primeira linha (tem o.* + ação)
            $dados = $acoes[0];
    
            // mantém o usuário do log
            if (isset($log[$id]['usua_alterou'])) {
                $dados->usu_nome = $log[$id]['usua_alterou'];
            }
        }
    
        $entity = new EntOcoTratativa($dados, true);
        $fields = $entity->campos;
    
        $secao[0] = 'Dados da Ocorrência';
        $campos[0] = [];
    
        // Campos fixos
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['usu_nome'];
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['oco_data'];
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['pro_despro'];
        $campos[0][] = $fields['oco_qtd'];
    
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
    
        if (!empty($acoes)) {
            foreach ($acoes as $acao) {
                $acao->somente_leitura = true;
    
                $camposAcao = $entity->defCamposAcao($acao);
                foreach ($camposAcao as $campo) {
                    $campos[0][] = $campo;
                }
            }
        }
    
        $this->data['show']        = true;
        $this->data['title']       = 'Ocorrência';
        $this->data['desc_metodo'] = ' Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT);
    
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['metodo']  = 'show';
        $this->data['destino'] = '';
    
        echo view('vw_edicao', $this->data);
    }


    public function finalizar($id)
    {
        $acoes = $this->ocorrencia->getAcoesFinalizar($id);
    
        if (!$acoes) {
            throw new \Exception("Ocorrência não encontrada");
        }
        $base = $acoes[0];
    
        $log = buscaLogTabela('oco_ocorrencia', [$id]);
    
        $secao[0] = 'Finalizar a Tratativa';
        $campos[0] = [];
    
        // Campos fixos (usa a primeira linha)
        $base = $acoes[0];
    
        if (isset($log[$id]['usua_alterou'])) {
            $base->usu_nome = $log[$id]['usua_alterou'];
        }
    
        $entity = new EntOcoTratativa($base, true);
        $fields = $entity->campos;
    
        // Campos fixos
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['usu_nome'];
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['oco_data'];
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['pro_despro'];
        $campos[0][] = $fields['oco_qtd'];

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

        $hidOcoId = new MyCampo('oco_ocorrencia', 'oco_id');
        $hidOcoId->valor = $base->oco_id;
        $hidOcoId->tipo  = 'hidden';
        $hidOcoId->size  = 1;
        
        
        $hidFinalizar = new MyCampo('oco_ocorrencia', 'finalizar');
        $hidFinalizar->valor = 1;
        $hidFinalizar->tipo  = 'hidden';
        $hidFinalizar->size  = 1;
        
        $campos[0][] = $hidOcoId->crInput();
        $campos[0][] = $hidFinalizar->crInput();
        
            
        foreach ($acoes as $acao) {
            $camposAcao = $entity->defCamposAcao($acao);
            foreach ($camposAcao as $campo) {
                $campos[0][] = $campo;
            }
        }
    
        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = 'store';
        $this->data['desc_metodo'] = 'Finalização da';
        $this->data['forca_alteracao'] = true;
    
        echo view('vw_edicao', $this->data);
    }



    public function store()
    {
        $postado = $this->request->getPost();

        $oco = $this->ocorrencia->find($postado['oco_id']);
        if (!$oco) {
            throw new \Exception('Ocorrência não encontrada');
        }
        
        try {
            if (empty($postado['oco_id'])) {
                throw new \Exception('ID da ocorrência não informado para finalização');
            }
        
            $acao = $this->ocorrencia->getAcaoConfigurada(
                (int) $oco->tpo_id,
                $oco->sut_id
            );
        
            // confirmação obrigatória
            if (
                $acao &&
                (int)$acao->tpa_id === 3 &&
                empty($postado['confirmado'])
            ) {
                return $this->response->setJSON([
                    'erro' => true,
                    'msg'  => 6,
                ]);
            }
        
            // FINALIZA
            $postado['stt_id']       = 30;
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
