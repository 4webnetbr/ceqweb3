<?php
namespace App\Controllers\Ocorrencia;
use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoTipoOcorrencia extends BaseController 
{
    public $data = [];
    public $permissao = '';
    public $tipoocorrencia;
    public $common;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->tipoocorrencia = new OcorreTipoOcorrenciaModel();
        $this->common    = new CommonModel();

        
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
        $this->data['colunas'] = montaColunasLista($this->data, 'tpo_id');
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
        // if (!$tpocor = cache('tpocor')) {
            $campos = montaColunasCampos($this->data, 'tpo_id');
            $dados_tpocor = $this->tipoocorrencia->getTipoOcorrencia();
            $tpocor = [
                'data' => montaListaColunas($this->data, 'tpo_id', $dados_tpocor, $campos[1]),
            ];
            cache()->save('tpocor', $tpocor, 60000);
        // }

        echo json_encode($tpocor);
    }
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $fields = $this->tipoocorrencia->defCampos();        
        $secao[0] = 'Dados Gerais'; 
        $campos[0][] = $fields['tpo_id'];  
        $campos[0][] = $fields['tpo_nome'];
        $campos[0][] = $fields['cla_id'];
        
        $secao[1] = 'Telas Aplicaveis'; 
        $displ[1] = 'tabela';
        $fields1 = $this->tipoocorrencia->defCamposTelasAplicaveis();
        $campos[1][0][] = $fields1['mod_id'];  
        $campos[1][0][] = $fields1['tel_id'];
        $campos[1][0][] = $fields1['tof_campo'];
        $campos[1][0][] = $fields1['bt_addta'];
        $campos[1][0][] = $fields1['bt_delta'];
        
        $secao[2] = 'Ações'; 
        $displ[2] = 'tabela';
        $fields2 = $this->tipoocorrencia->defCamposAcao();
        $campos[2][0][] = $fields2['tpa_id'];  
        $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>".$fields2['tmo_id']."</div>";  
        $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>".$fields2['mod_id'].$fields2['tel_id']."</div>";  
        $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>".$fields2['stt_id']."</div>";  
        $campos[2][0][] = $fields2['bt_addtp'];  
        $campos[2][0][] = $fields2['bt_deltp'];  

        $secao[3] = 'Permissões';
        $fields3 = $this->tipoocorrencia->defPermissoes();
        $campos[3][0] = $fields3['prf_id'];

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']      = $displ;
		$this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Summary of addCampoTa - Telas Aplicáveis
     * @param mixed $ind
     * @return never
     */
    public function addCampoTa($ind)
    {
        $fields = $this->tipoocorrencia->defCamposTelasAplicaveis(false, $ind);
        // debug($fields);
        $campo[0] = $fields['mod_id'];  
        $campo[1] = $fields['tel_id'];
        $campo[2] = $fields['tof_campo'];
        $campo[3] = $fields['bt_addta'];
        $campo[4] = $fields['bt_delta'];

        echo json_encode($campo);
        exit;
    }

    /**
     * Summary of addCampoTp - Ações
     * @param mixed $ind
     * @return never
     */
    public function addCampoTp($ind)
    {
        $fields = $this->tipoocorrencia->defCamposAcao(false, $ind);
        // debug($fields);
        $campo[0] = $fields['tpa_id'];  
        $campo[1] = "<div id='divmovi[$ind]' class='d-none row col-6'>".$fields['tmo_id']."</div>";  
        $campo[2] = "<div id='divtela[$ind]' class='d-none row col-6'>".$fields['mod_id'].$fields['tel_id']."</div>";  
        $campo[3] = "<div id='divstat[$ind]' class='d-none row col-6'>".$fields['stt_id']."</div>";  
        $campo[4] = $fields['bt_addtp'];  
        $campo[5] = $fields['bt_deltp'];  

        echo json_encode($campo);
        exit;
    }
    
    /**
    * Edição
    * edit
    *
    * @param mixed $id 
    * @return void
    */
    public function edit($id)
    {   
        $dados_TipoOcorrencia = $this->tipoocorrencia->getTipoOcorrencia($id);
        // debug($dados_TipoOcorrencia);
        $fields = $this->tipoocorrencia->defCampos($dados_TipoOcorrencia[0]);
                
        $secao[0] = 'Dados Gerais'; 
        $campos[0][] = $fields['tpo_id'];  
        $campos[0][] = $fields['tpo_nome'];
        $campos[0][] = $fields['cla_id'];
        
        $secao[1] = 'Telas Aplicaveis'; 
        $displ[1] = 'tabela';
        $dados_TelasAplicaveis = $this->tipoocorrencia->getTOTelasAplicaveis($id);
        // debug($dados_TelasAplicaveis, true);
        if (count($dados_TelasAplicaveis) > 0) {
            for ($c = 0; $c < count($dados_TelasAplicaveis); $c++) {
                $fields = $this->tipoocorrencia->defCamposTelasAplicaveis($dados_TelasAplicaveis[$c], $c);
                $campos[1][$c][] = $fields['mod_id'];  
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['tof_campo'];
                $campos[1][$c][] = $fields['bt_addta'];
                $campos[1][$c][] = $fields['bt_delta'];
            }
        } else {
            $fields = $this->tipoocorrencia->defCamposTelasAplicaveis(false, 0);
            $campos[1][0][0] = $fields['mod_id'];  
            $campos[1][0][] = $fields['tel_id'];
            $campos[1][0][] = $fields['tof_campo'];
            $campos[1][0][] = $fields['bt_addta'];
            $campos[1][0][] = $fields['bt_delta'];
        }
        
        $secao[2] = 'Ações'; 
        $displ[2] = 'tabela';
        $dados_Acao = $this->tipoocorrencia->getTOAcao($id);
        // debug($dados_Acao, true);
        if (count($dados_Acao) > 0) {
            for ($c = 0; $c < count($dados_Acao); $c++) {
                $fields = $this->tipoocorrencia->defCamposAcao($dados_Acao[$c], $c);
                $campos[2][$c][] = $fields['tpa_id'];
                $dnone = 'd-none';
                if($dados_Acao[$c]['tmo_id'] != 0){
                    $dnone = '';
                }
                $campos[2][$c][] = "<div id='divmovi[$c]' class='$dnone row col-6'>".$fields['tmo_id']."</div>";  
                $dnone = 'd-none';
                if($dados_Acao[$c]['tel_id'] != 0){
                    $dnone = '';
                }
                $campos[2][$c][] = "<div id='divtela[$c]' class='$dnone row col-6'>".$fields['mod_id'].$fields['tel_id']."</div>";  
                $dnone = 'd-none';
                if($dados_Acao[$c]['stt_id'] != 0){
                    $dnone = '';
                }
                $campos[2][$c][] = "<div id='divstat[$c]' class='$dnone row col-6'>".$fields['stt_id']."</div>";  
                $campos[2][$c][] = $fields['bt_addtp'];
                $campos[2][$c][] = $fields['bt_deltp'];
            }
        } else {
            $fields = $this->tipoocorrencia->defCamposAcao(false, 0);
            $campos[2][0][] = $fields['tpa_id'];
            $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>".$fields['tmo_id']."</div>";  
            $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>".$fields['mod_id'].$fields['tel_id']."</div>";  
            $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>".$fields['stt_id']."</div>";  
            $campos[2][0][] = $fields['bt_addtp'];
            $campos[2][0][] = $fields['bt_deltp'];
        }

        $secao[3] = 'Permissões';
        $fields3 = $this->tipoocorrencia->defPermissoes($dados_TipoOcorrencia[0]);
        $campos[3][0] = $fields3['prf_id'];

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']      = $displ;
		$this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
        
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
        $ret = [];
        try {
            $this->tipoocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tipo de Ocorrência Excluída com Sucesso');
            $ret['msg'] = 'Tipo de Ocorrência Excluída com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
        
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'tpo_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'tpo_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->tipoocorrencia->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tipo de Ocorrência Alterada com Sucesso');
            $ret['msg']  = 'Tipo de Ocorrência Alterada com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Tipo de Ocorrência, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    /**
    * Gravação
    * store
    
    * @return void
    */
    public function store()
    {
        $postado = $this->request->getPost();
        $db = \Config\Database::connect();
        $ret = [];
        $grupo = 'dbOcorrencia';

        try {
            $db->transBegin();

            // Verifica unicidade
            $exists = $this->common->verificaUnico(
                $this->tipoocorrencia,
                'tpo_nome',
                $postado['tpo_nome'],
                'tpo_id',
                $postado['tpo_id']
            );

            if ($exists > 0) {
                $ret['erro'] = true;
                $ret['msg'] = 8;
                $erros = [8];
            }

            // Salvar Tipo de Ocorrência (principal)
            if (!$this->tipoocorrencia->save($postado)) {
                throw new \Exception(implode('<br>', $this->tipoocorrencia->errors()));
            }

            // Recupera ID do tipo (novo ou existente)
            $tpo_id = $this->tipoocorrencia->getInsertID();
            if (!$tpo_id && isset($postado['tpo_id'])) {
                $tpo_id = $postado['tpo_id'];
            }

            // Se for update, apaga os registros relacionados
            if (!empty($tpo_id)) {
                $this->common->deleteReg($grupo, 'oco_tpo_acao',      "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tpo_classe',    "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tpo_tela',      "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tpo_campos',    "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tpo_permissao', "tpo_id = {$tpo_id}");
            }

            // Inserir ações com dados complementares
            if (!empty($postado['tpa_id'])) {
                $acoes = [];
                foreach ($postado['tpa_id'] as $i => $tpa_id) {
                    $acoes[] = [
                        'tpo_id' => $tpo_id,
                        'tpa_id' => $tpa_id,
                        'mod_id' => $postado['mod_id_tpa'][$i] ?? null,
                        'tel_id' => $postado['tel_id_tpa'][$i] ?? null,
                        'stt_id' => $postado['stt_id_tpa'][$i] ?? null,
                        'tmo_id' => $postado['tmo_id_tpa'][$i] ?? null,
                    ];
                }
                $this->common->insertRegBatch($grupo, 'oco_tpo_acao', $acoes);
            }

            // Inserir classes
            if (!empty($postado['cla_id'])) {
                $classes = [];
                foreach ($postado['cla_id'] as $cla_id) {
                    $classes[] = ['tpo_id' => $tpo_id, 'cla_id' => $cla_id];
                }
                $this->common->insertRegBatch($grupo, 'oco_tpo_classe', $classes);
            }

            // Inserir telas
            if (!empty($postado['mod_id']) && !empty($postado['tel_id'])) {
                $telas = [];
                foreach ($postado['mod_id'] as $index => $mod_id) {
                    $tel_id = $postado['tel_id'][$index] ?? null;
                    if ($tel_id) {
                        $telas[] = [
                            'tpo_id' => $tpo_id,
                            'mod_id' => $mod_id,
                            'tel_id' => $tel_id
                        ];
                    }
                }
                $this->common->insertRegBatch($grupo, 'oco_tpo_tela', $telas);
            }

            // Inserir campos (relacionados às telas)
            if (!empty($postado['tof_campo'])) {
                $campos = [];

                foreach ($postado['tof_campo'] as $index => $camposTela) {
                    $tel_id = $postado['tel_id'][$index] ?? null;

                    if (!$tel_id || !is_array($camposTela)) {
                        continue;
                    }

                    foreach ($camposTela as $tof_campo) {
                        if ($tof_campo) {
                            $campos[] = [
                                'tpo_id'    => $tpo_id,
                                'tel_id'    => $tel_id,
                                'tof_campo' => $tof_campo
                            ];
                        }
                    }
                }

                if (!empty($campos)) {
                    $this->common->insertRegBatch($grupo, 'oco_tpo_campos', $campos);
                }
            }

            // Inserir permissões
            if (!empty($postado['prf_id'])) {
                $permissoes = [];
                foreach ($postado['prf_id'] as $prf_id) {
                    $permissoes[] = ['tpo_id' => $tpo_id, 'prf_id' => $prf_id];
                }
                $this->common->insertRegBatch($grupo, 'oco_tpo_permissao', $permissoes);
            }

            $db->transCommit();

            $ret['erro'] = false;
            $ret['msg']  = 'Tipo de Ocorrência gravado com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        } catch (\Exception $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao gravar o Tipo de Ocorrência:<br><br>' . $e->getMessage();
        }

        return $this->response->setJSON($ret);
    }
}
