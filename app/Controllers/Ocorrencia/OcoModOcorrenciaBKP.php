<?php
namespace App\Controllers\Ocorrencia;
use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoModOcorrencia extends BaseController {
    public $data = [];
    public $permissao = '';
    public $tipoocorrencia;
    public $modocorrencia;
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
        $this->modocorrencia = new OcorreModOcorrenciaModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'moc_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
    * ListagemF
    * lista
    *
    * @return void
    */
    public function lista()
    {
        // if (!$tpocor = cache('tpocor')) {
            $campos = montaColunasCampos($this->data, 'moc_id');
            $dados_mdocor = $this->modocorrencia->getModOcorrencia();
            $mdocor = [
                'data' => montaListaColunas($this->data, 'moc_id', $dados_mdocor, $campos[1]),
            ];
            cache()->save('mdocor', $mdocor, 60000);
        // }

        echo json_encode($mdocor);
    }
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $fields = $this->modocorrencia->defCampos();        
        $secao[0] = 'Dados Gerais'; 
        $campos[0][] = $fields['moc_id'];  
        $campos[0][] = $fields['moc_nome'];
        $campos[0][] = $fields['tpo_id'];
        
        $secao[1] = 'Telas Aplicaveis'; 
        $displ[1] = 'tabela';
        $fields1 = $this->modocorrencia->defCamposTelasAplicaveis();
        $campos[1][0][] = $fields1['mod_id'];  
        $campos[1][0][] = $fields1['tel_id'];
        $campos[1][0][] = $fields1['mof_campo'];
        $campos[1][0][] = $fields1['bt_addta'];
        $campos[1][0][] = $fields1['bt_delta'];
        
        $secao[2] = 'Ações'; 
        $displ[2] = 'tabela';
        $fields2 = $this->modocorrencia->defCamposAcao(false, 0, 2, 'add');
        $campos[2][0][] = $fields2['tpa_id'];  
        $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>".$fields2['tmo_id']."</div>";  
        $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>".$fields2['mod_id'].$fields2['tel_id']."</div>";  
        $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>".$fields2['stt_id']."</div>";  
        $campos[2][0][] = $fields2['bt_addtp'];
        $campos[2][0][] = $fields2['bt_deltp'];  

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']      = $displ;
		$this->data['destino']    = 'store';
        $this->data['script'] = "<script>
                acerta_botoes_rep('telas_aplicaveis');
                acerta_botoes_rep('acoes');
        </script>";

        echo view('vw_edicao', $this->data);
    }

    /**
     * Summary of addCampoTa - Telas Aplicáveis
     * @param mixed $ind
     * @return never
     */
    public function addCampoTa($tipo, $ind)
    {
        $ttipo            = new OcorreTipoOcorrenciaModel();
        $ttelas           = $ttipo->getTOTelasAplicaveis($tipo);
        // debug($ttelas);
        for ($t=0; $t < sizeof($ttelas) ; $t++) { 
            // debug($ttelas, true);
            $total = sizeof($ttelas); // novo total
            $fields = $this->modocorrencia->defCamposTelasAplicaveis($ttelas[$t], $ind, $total);
            // debug($fields);
            $campo[$t][0] = $fields['mod_id'];  
            $campo[$t][1] = $fields['tel_id'];
            $campo[$t][2] = $fields['mof_campo'];
            $campo[$t][3] = $fields['bt_addta'];
            $campo[$t][4] = $fields['bt_delta'];
            $ind++; 

        }
        echo json_encode($campo);
        exit;
    }

    /**
     * Summary of addCampoTp - Ações
     * @param mixed $ind
     * @return never
     */
    public function addCampoTp($tpo_id, $ind)
    {
      $tipoAcaoModel  = new OcorreTipoOcorrenciaModel();
      $tacao          = $tipoAcaoModel->getTOAcao($tpo_id); 
  
        for ($a = 0; $a < sizeof($tacao); $a++) {
            $fields = $this->modocorrencia->defCamposAcao($tacao[$a], $ind);
            $campo[$a][0] = $fields['tpa_id'];
            $campo[$a][1] = "<div id='divmovi[$ind]' class='d-none row col-6'>".$fields['tmo_id']."</div>";
            $campo[$a][2] = "<div id='divtela[$ind]' class='d-none row col-6'>".$fields['mod_id'].$fields['tel_id']."</div>";
            $campo[$a][3] = "<div id='divstat[$ind]' class='d-none row col-6'>".$fields['stt_id']."</div>";
            $campo[$a][4] = $fields['bt_addtp'];
            $campo[$a][5] = $fields['bt_deltp'];
            $ind++; 
            //debug($campo);
            
        }
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
        $dados_ModOcorrencia = $this->modocorrencia->getModOcorrencia($id);
        // debug($dados_TipoOcorrencia);
        $fields = $this->modocorrencia->defCampos($dados_ModOcorrencia[0]);
                
        $secao [0]   = 'Dados Gerais'; 
        $campos[0][] = $fields['moc_id'];   
        $campos[0][] = $fields['moc_nome'];
        $campos[0][] = $fields['tpo_id']; 
        // $campos[0][] = $fields['cla_id'];
        
        $secao[1] = 'Telas Aplicaveis'; 
        $displ[1] = 'tabela';
        // $moc_id = $dados_ModOcorrencia[0]['moc_id'];
        $dados_TelasAplicaveis = $this->modocorrencia->getTOTelasAplicaveis($id);
        $total = count($dados_TelasAplicaveis);
        // debug($dados_TelasAplicaveis, true);
        
        if (count($dados_TelasAplicaveis) > 0) {
            
            for ($c = 0; $c < count($dados_TelasAplicaveis); $c++) {
                $fields = $this->modocorrencia->defCamposTelasAplicaveis($dados_TelasAplicaveis[$c], $c, $total);

                $campos[1][$c][] = $fields['mod_id'];  
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['mof_campo'];
                $campos[1][$c][] = $fields['bt_addta'];
                $campos[1][$c][] = $fields['bt_delta'];
            }
        } else {
            $fields = $this->modocorrencia->defCamposTelasAplicaveis(false, 0);
            $campos[1][0][0] = $fields['mod_id'];  
            $campos[1][0][]  = $fields['tel_id'];
            $campos[1][0][]  = $fields['mof_campo'];
            $campos[1][0][]  = $fields['bt_addta'];
            $campos[1][0][]  = $fields['bt_delta'];
        }
        
        $secao[2] = 'Ações'; 
        $displ[2]   = 'tabela';
        $dados_Acao = $this->modocorrencia->getTOAcao($id);
        // debug($dados_Acao, true);
        if (count($dados_Acao) > 0) {
            $total_acoes = count($dados_Acao);
        for ($c = 0; $c < $total_acoes; $c++) {
        $fields = $this->modocorrencia->defCamposAcao($dados_Acao[$c], $c, $total_acoes, 'edit');
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
            $fields = $this->modocorrencia->defCamposAcao(false, 0, 1);
            $campos[2][0][] = $fields['tpa_id'];
            $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>".$fields['tmo_id']."</div>";  
            $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>".$fields['mod_id'].$fields['tel_id']."</div>";  
            $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>".$fields['stt_id']."</div>";  
            $campos[2][0][] = $fields['bt_addtp'];
            $campos[2][0][] = $fields['bt_deltp'];
        }

        // $secao[3] = 'Permissões';
        // $fields3 = $this->modocorrencia->defPermissoes($dados_TipoOcorrencia[0]);
        // $campos[3][0] = $fields3['prf_id'];

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
            $this->modocorrencia->delete($id);
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
                'moc_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'moc_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->modocorrencia->update($id, $dad_atin);
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
                $this->modocorrencia,
                'moc_nome',
                $postado['moc_nome'],
                'moc_id',
                $postado['moc_id']
            );

            if ($exists > 0) {
                throw new \Exception('Nome do Tipo de Ocorrência já cadastrado.');
            }

            // Salvar Tipo de Ocorrência (principal)
            if (!$this->modocorrencia->save($postado)) {
                throw new \Exception(implode('<br>', $this->modocorrencia->errors()));
            }

            // Recupera ID do tipo (novo ou existente)
            $moc_id = $this->modocorrencia->getInsertID();
            if (!$moc_id && isset($postado['moc_id'])) {
                $moc_id = $postado['moc_id'];
            }

            // Se for update, apaga os registros relacionados
            if (!empty($moc_id)) {
                $this->common->deleteReg($grupo, 'oco_moc_acao',   "moc_id = {$moc_id}");
                // $this->common->deleteReg($grupo, 'oco_moc_classe', "moc_id = {$moc_id}");
                $this->common->deleteReg($grupo, 'oco_moc_tela',   "moc_id = {$moc_id}");
                $this->common->deleteReg($grupo, 'oco_moc_campos', "moc_id = {$moc_id}");
                // $this->common->deleteReg($grupo, 'oco_moc_permissao', "moc_id = {$moc_id}");
            }

            // Inserir ações com dados complementares
            if (!empty($postado['tpa_id'])) {
                $acoes = [];
                foreach ($postado['tpa_id'] as $i => $tpa_id) {
                    $acoes[] = [
                        'moc_id' => $moc_id,
                        'tpa_id' => $tpa_id,
                        'mod_id' => $postado['mod_id_tpa'][$i] ?? null,
                        'tel_id' => $postado['tel_id_tpa'][$i] ?? null,
                        'stt_id' => $postado['stt_id_tpa'][$i] ?? null,
                        'tmo_id' => $postado['tmo_id_tpa'][$i] ?? null,
                    ];
                }
                $this->common->insertRegBatch($grupo, 'oco_moc_acao', $acoes);
            }

            // Inserir classes
            // if (!empty($postado['cla_id'])) {
            //     $classes = [];
            //     foreach ($postado['cla_id'] as $cla_id) {
            //         $classes[] = ['moc_id' => $moc_id, 'cla_id' => $cla_id];
            //     }
            //     $this->common->insertRegBatch($grupo, 'oco_moc_classe', $classes);
            // }

            // Inserir telas
            if (!empty($postado['mod_id']) && !empty($postado['tel_id'])) {
                $telas = [];
                foreach ($postado['mod_id'] as $index => $mod_id) {
                    $tel_id = $postado['tel_id'][$index] ?? null;
                    if ($tel_id) {
                        $telas[] = [
                            'moc_id' => $moc_id,
                            'mod_id' => $mod_id,
                            'tel_id' => $tel_id
                        ];
                    }
                }
                $this->common->insertRegBatch($grupo, 'oco_moc_tela', $telas);
            }

            // Inserir campos (relacionados às telas)
            if (!empty($postado['mof_campo'])) {
                $campos = [];

                foreach ($postado['mof_campo'] as $index => $camposTela) {
                    $tel_id = $postado['tel_id'][$index] ?? null;

                    if (!$tel_id || !is_array($camposTela)) {
                        continue;
                    }

                    foreach ($camposTela as $mof_campo) {
                        if ($mof_campo) {
                            $campos[] = [
                                'moc_id'    => $moc_id,
                                'tel_id'    => $tel_id,
                                'mof_campo' => $mof_campo
                            ];
                        }
                    }
                }

                if (!empty($campos)) {
                    $this->common->insertRegBatch($grupo, 'oco_moc_campos', $campos);
                }
            }

            // Inserir permissões
            // if (!empty($postado['prf_id'])) {
            //     $permissoes = [];
            //     foreach ($postado['prf_id'] as $prf_id) {
            //         $permissoes[] = ['moc_id' => $moc_id, 'prf_id' => $prf_id];
            //     }
            //     $this->common->insertRegBatch($grupo, 'oco_moc_permissao', $permissoes);
            // }

            $db->transCommit();

            $ret['erro'] = false;
            $ret['msg']  = 'Modelo de Ocorrência gravado com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        } catch (\Exception $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao gravar o Modelo de Ocorrência :<br><br>' . $e->getMessage();
        }

        return $this->response->setJSON($ret);
    }
}
