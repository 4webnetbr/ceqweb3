<?php

namespace App\Controllers\Ocorrencia;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoModOcorrencia extends BaseController
{
    use ForeignKeyUsageChecker;

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
        $this->data           = session()->getFlashdata('dados_tela');
        $this->permissao      = $this->data['permissao'];
        $this->modocorrencia  = new OcorreModOcorrenciaModel();
        $this->common         = new CommonModel();


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
        $this->data['colunas']   = montaColunasLista($this->data, 'sut_id');
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
        ini_set('display_errors', 1);
    error_reporting(E_ALL);

    header('Content-Type: application/json');
        // Monta as colunas da listagem com base na configuração da tela
        $campos = montaColunasCampos($this->data, 'sut_id');
        $dados_mdocor = $this->modocorrencia->getModOcorrencia();
        // Monta o array de retorno no formato esperado pelo DataTable
        $mdocor = [
            'data' => montaListaColunasEnt($this->data, 'sut_id', $dados_mdocor, $campos[1]),
        ];
        cache()->save('mdocor', $mdocor, 60000);

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
        // Instancia a entity do Modelo de Ocorrência
        $entity = new EntOcoModOcorrencia();
        $fields = $entity->campos;

        // Dados Gerais
        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['sut_id'];
        $campos[0][] = $fields['sut_nome'];
        $campos[0][] = $fields['tpo_id'];

        // Telas Aplicaveis
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';

        // campos
        $fields1 = $entity->defCamposTelasAplicaveis();
        $campos[1][0]   = [];
        $campos[1][0][] = $fields1['mod_id'];
        $campos[1][0][] = $fields1['tel_id'];
        $campos[1][0][] = $fields1['tof_campo'];      
        $campos[1][0][] = '';        
        $campos[1][0][] = $fields1['bt_delta'];

        // ações
        $secao[2] = 'Ações';
        $displ[2] = 'tabela';

        $fields2 = $entity->defCamposAcao();
        $campos[2][0][] = $fields2['tpa_id'];
        $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>" . $fields2['tmo_id'] . "</div>";
        $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>" . $fields2['mod_id'] . $fields2['tel_id'] . "</div>";
        $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>" . $fields2['stt_id'] . "</div>";
        $campos[2][0][] = '';
        $campos[2][0][] = $fields2['bt_deltp'];

        // Define dados da tela
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Summary of addCampoTa - Telas Aplicáveis
     * @param mixed $ind
     * @return never
     */
    public function addCampoTa($tipo, $ind)
    {
        $campo = [];
        $ttipo  = new OcorreTipoOcorrenciaModel();
        $ttelas = $ttipo->getTOTelasAplicaveis($tipo);
        $entity = new EntOcoModOcorrencia();

        for ($t = 0; $t < sizeof($ttelas); $t++) {
            $total = sizeof($ttelas);
            // debug($ttelas[$t]);
            $fields = $entity->defCamposTelasAplicaveis(
                $ttelas[$t],
                $ind,
                $total
            );
            $campo[$t][0] = $fields['mod_id'];
            $campo[$t][1] = $fields['tel_id'];
            $campo[$t][2] = $fields['tof_campo'];
            $campo[$t][3] = '';
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
        $entity = new EntOcoModOcorrencia();

        // Gera campos da ação

        for ($a = 0; $a < sizeof($tacao); $a++) {
            $total = sizeof($tacao);
            $fields = $entity->defCamposAcao($tacao[$a], $ind, $total);
            $campo[$a][0] = $fields['tpa_id'];
            $campo[$a][1] = "<div id='divmovi[$ind]' class='d-none row col-6'>" . $fields['tmo_id'] . "</div>";
            $campo[$a][2] = "<div id='divtela[$ind]' class='d-none row col-6'>" . $fields['mod_id'] . $fields['tel_id'] . "</div>";
            $campo[$a][3] = "<div id='divstat[$ind]' class='d-none row col-6'>" . $fields['stt_id'] . "</div>";
            $campo[$a][4] = '';
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
        // Busca os dados do Modelo de Ocorrência pelo ID
        $dados_ModOcorrencia = $this->modocorrencia->getModOcorrencia($id);

        // Cria a entity com os dados retornados
        $entity = new EntOcoModOcorrencia((array) $dados_ModOcorrencia[0]);
        $fields = $entity->campos;

        // Dados Gerais
        $secao[0]   = 'Dados Gerais';
        $campos[0][] = $fields['sut_id'];
        $campos[0][] = $fields['sut_nome'];
        $campos[0][] = $fields['tpo_id'];

        // Telas Aplicáveis
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';

        $registro = $dados_ModOcorrencia[0];
        $sut_id   = (int) $registro->sut_id;

        $campos[1] = [];
        
        $dados_TelasAplicaveis = $this->modocorrencia->getTOTelasAplicaveis($sut_id);
        
        // debug($dados_TelasAplicaveis, true);
        if (count($dados_TelasAplicaveis) > 0) {
            $total = count($dados_TelasAplicaveis);
        
            for ($c = 0; $c < $total; $c++) {
                $fields = $entity->defCamposTelasAplicaveis(
                    $dados_TelasAplicaveis[$c],
                    $c,
                    $total
                );
        
                $campos[1][$c][] = $fields['mod_id'];
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['tof_campo'];
                $campos[1][$c][] = '';
                $campos[1][$c][] = $fields['bt_delta'];
            }
        }

        // Ações
        $secao[2]   = 'Ações';
        $displ[2]   = 'tabela';
        $campos[2]  = [];
        $dados_Acao = $this->modocorrencia->getTOAcao($id);
        
        if (!empty($dados_Acao)) {
            $total = count($dados_Acao);
        
            foreach ($dados_Acao as $c => $acao) {
        
                $fields = $entity->defCamposAcao(
                    $acao,
                    $c,
                    $total,
                    'edit'
                );
        
                $clsMovi = $clsTela = $clsStat = 'd-none';

                switch ((int) $acao->tpa_id) {
                    case 3: // Gerar Movimentação
                        $clsMovi = '';
                    break;
                
                    case 4: // Abrir Tela
                        $clsTela = '';
                    break;
                
                    case 7: // Alterar Status
                        $clsStat = '';
                    break;
                }
        
                $campos[2][$c][] = $fields['tpa_id'];
                $campos[2][$c][] ="<div id='divmovi[$c]' class='{$clsMovi} row col-6'>{$fields['tmo_id']}</div>";     
                $campos[2][$c][] ="<div id='divtela[$c]' class='{$clsTela} row col-6'>{$fields['mod_id']}{$fields['tel_id']}</div>";
                $campos[2][$c][] ="<div id='divstat[$c]' class='{$clsStat} row col-6'>{$fields['stt_id']}</div>";
                $campos[2][$c][] = '';
                $campos[2][$c][] = $fields['bt_deltp'];
            }
        }

        // Define dados finais da tela
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';
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
            // bloqueia se existir ocorrência vinculada
            if ($this->modocorrencia->getUsoGestao((int)$id)) {
                throw new \Exception('MSG_3');
            }
    
            // Soft delete do subtipo
            $this->modocorrencia->delete($id);
    
            $ret['erro'] = false;
            $ret['msg']  = 'Subtipo de Ocorrência excluída com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
    
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
    
        echo json_encode($ret);
    }
    
    public function ativinativ($id, $tipo)
    {
        try {
    
           if ($tipo != 1 && $this->modocorrencia->getUsoGestao($id)) {
                return $this->response->setJSON([
                    'erro' => true,
                    'msg'  => 14
                ]);
            }
    
            $this->modocorrencia->update($id, [
                'sut_ativo' => ($tipo == 1 ? 'A' : 'I')
            ]);
    
            return $this->response->setJSON([
                'erro' => false,
                'msg'  => 'Subtipo de ocorrência alterada com sucesso'
            ]);
    
        } catch (\Exception $e) {
    
            return $this->response->setJSON([
                'erro' => true,
                'msg'  => 14
            ]);
        }
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
                'sut_nome',
                $postado['sut_nome'],
                'sut_id',
                $postado['sut_id']
            );

            if ($exists > 0) {
                throw new \Exception('MSG_8');
            }

            // Salvar Tipo de Ocorrência (principal)
            if (!$this->modocorrencia->save($postado)) {
                throw new \Exception(implode('<br>', $this->modocorrencia->errors()));
            }

            // Recupera ID do tipo (novo ou existente)
            $sut_id = $this->modocorrencia->getInsertID();
            if (!$sut_id && isset($postado['sut_id'])) {
                $sut_id = $postado['sut_id'];
            }

            // Se for update, apaga os registros relacionados
            if (!empty($sut_id)) {
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_acao',   "sut_id = {$sut_id}");
                // $this->common->deleteReg($grupo, 'oco_moc_classe', "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_tela',   "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_campos', "sut_id = {$sut_id}");
                // $this->common->deleteReg($grupo, 'oco_moc_permissao', "sut_id = {$sut_id}");
            }

            // Ações
            if (!empty($postado['tpa_id'])) {
                $acoes = [];
            
                foreach ($postado['tpa_id'] as $i => $tpa_id) {
            
                    if (!$tpa_id) continue;
            
                    $mod_id = (!isset($postado['mod_id_tpa'][$i]) || $postado['mod_id_tpa'][$i] <= 0)
                        ? null
                        : (int) $postado['mod_id_tpa'][$i];
                    
                    $tel_id = (!isset($postado['tel_id_tpa'][$i]) || $postado['tel_id_tpa'][$i] <= 0)
                        ? null
                        : (int) $postado['tel_id_tpa'][$i];
                    
                    $stt_id = (!isset($postado['stt_id_tpa'][$i]) || $postado['stt_id_tpa'][$i] <= 0)
                        ? null
                        : (int) $postado['stt_id_tpa'][$i];
                    
                    $tmo_id = (!isset($postado['tmo_id_tpa'][$i]) || $postado['tmo_id_tpa'][$i] <= 0)
                        ? null
                        : (int) $postado['tmo_id_tpa'][$i];
            
                    $acoes[] = [
                        'sut_id' => $sut_id,
                        'tpa_id' => $tpa_id,
                        'mod_id' => $mod_id,
                        'tel_id' => $tel_id,
                        'stt_id' => $stt_id,
                        'tmo_id' => $tmo_id,
                    ];
                }
                $this->common->insertRegBatch($grupo, 'oco_subt_ocorrencia_acao', $acoes);
            }

            // Inserir telas
            if (!empty($postado['mod_id']) && !empty($postado['tel_id'])) {
                $telas = [];
                foreach ($postado['mod_id'] as $i => $mod_id) {
                    if (!empty($postado['tel_id'][$i])) {
                        $telas[] = [
                            'sut_id' => $sut_id,
                            'mod_id' => $mod_id,
                            'tel_id' => $postado['tel_id'][$i]
                        ];
                    }
                }
                if (!empty($telas)) {
                    $this->common->insertRegBatch($grupo,'oco_subt_ocorrencia_tela', $telas);
                }
            }

            // Inserir campos (relacionados às telas)
            if (!empty($postado['tof_campo'])) {
                $campos = [];
            
                foreach ($postado['tof_campo'] as $pos => $camposTela) {
            
                    if (empty($postado['tel_id'][$pos])) {
                        continue;
                    }
            
                    $tel_id = $postado['tel_id'][$pos];
            
                    foreach ($camposTela as $tof_campo) {
                        if (!empty($tof_campo)) {
                            $campos[] = [
                                'sut_id'    => $sut_id,
                                'tel_id'    => $tel_id,
                                'mof_campo' => $tof_campo
                            ];
                        }
                    }
                }
            
                if (!empty($campos)) {
                    $this->common->insertRegBatch($grupo, 'oco_subt_ocorrencia_campos', $campos);
                }
            }

            $db->transCommit();

            $ret['erro'] = false;
            $ret['msg']  = 'Subtipo de Ocorrência gravado com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        } catch (\Exception $e) {
            $db->transRollback();
        
            if ($e->getMessage() === 'MSG_8') {
                $ret['erro'] = true;
                $ret['msg']  = 8; 
            } else {
                $ret['erro'] = true;
                $ret['msg']  = 'Erro ao gravar o Subtipo de Ocorrência :<br><br>' . $e->getMessage();
            }
        }

        return $this->response->setJSON($ret);
    }
}
