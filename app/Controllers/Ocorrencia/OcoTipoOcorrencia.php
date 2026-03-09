<?php

namespace App\Controllers\Ocorrencia;

use App\Entities\Ocorrencia\EntOcoTipoOcorre;
use App\Controllers\BaseController;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\CommonModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;

class OcoTipoOcorrencia extends BaseController
{
    use ForeignKeyUsageChecker;

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
        $this->data = session()->getFlashdata('dados_tela') ?? [];
        $this->permissao = $this->data['permissao'] ?? '';
        $this->tipoocorrencia = new OcorreTipoOcorrenciaModel();
        $this->common         = new CommonModel();
        $this->data           = session()->getFlashdata('dados_tela');


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
        $this->data['colunas']   = montaColunasLista($this->data, 'tpo_id');
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
        $campos = montaColunasCampos($this->data, 'tpo_id');
        $dados_tpocor = $this->tipoocorrencia->getTipoOcorrencia();

        $tpocor = [
            'data' => montaListaColunasEnt($this->data, 'tpo_id', $dados_tpocor, $campos[1]),
        ];

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
        // ENTITY
        $entity = new EntOcoTipoOcorre();
        $fields = $entity->campos;

        // Dados Gerais
        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['tpo_nome'];
        $campos[0][] = $fields['cla_id'];

        // Telas Aplicaveis
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';

        // campos
        $fields1 = $entity->defCamposTelasAplicaveis();
        $campos[1][0][] = $fields1['mod_id'];
        $campos[1][0][] = $fields1['tel_id'];
        $campos[1][0][] = $fields1['tof_campo'];
        $campos[1][0][] = $fields1['bt_addta'];
        $campos[1][0][] = $fields1['bt_delta'];

        // ações
        $secao[2] = 'Ações';
        $displ[2] = 'tabela';

        $fields2 = $entity->defCamposAcao();
        $campos[2][0][] = $fields2['tpa_id'];
        $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>" . $fields2['tmo_id'] . "</div>";
        $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>" . $fields2['mod_id'] . $fields2['tel_id'] . "</div>";
        $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>" . $fields2['stt_id'] . "</div>";
        $campos[2][0][] = $fields2['bt_addtp'];
        $campos[2][0][] = $fields2['bt_deltp'];

        // Permissões
        $secao[3] = 'Permissões';
        $fields3  = $entity->defPermissoes();
        $campos[3][0] = $fields3['prf_id'];

        // Define dados da tela
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';
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
    public function addCampoTa($ind)
    {
        // Instancia a entity
        $entity = new EntOcoTipoOcorre();

        // Gera os campos da tela aplicável
        $fields = $entity->defCamposTelasAplicaveis(false, $ind);

        $campo[0] = $fields['mod_id'];
        $campo[1] = $fields['tel_id'];
        $campo[2] = $fields['tof_campo'];
        $campo[3] = $fields['bt_addta'];
        $campo[4] = $fields['bt_delta'];

        // Retorna JSON
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
        // Instancia a entity
        $entity = new EntOcoTipoOcorre();

        // Gera campos da ação
        $fields = $entity->defCamposAcao(false, $ind);

        $campo[0] = $fields['tpa_id'];
        $campo[1] = "<div id='divmovi[$ind]' class='d-none row col-6'>" . $fields['tmo_id'] . "</div>";
        $campo[2] = "<div id='divtela[$ind]' class='d-none row col-6'>" . $fields['mod_id'] . $fields['tel_id'] . "</div>";
        $campo[3] = "<div id='divstat[$ind]' class='d-none row col-6'>" . $fields['stt_id'] . "</div>";
        $campo[4] = $fields['bt_addtp'];
        $campo[5] = $fields['bt_deltp'];

        // Retorna JSON
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
        $show = false;
        try {
            // Checa uso do tipo em outros bancos
            $this->verificarUsoEmRelacionamentos('oco_tipo_ocorrencia', 'tpo_id', (int) $id);
        } catch (\Exception $e) {
            $show = true;
        }

        // BUSCA DADOS 
        $dados_TipoOcorrencia = $this->tipoocorrencia->getTipoOcorrencia($id);
        // debug($dados_TipoOcorrencia);

        // ENTITY
        $entity = new EntOcoTipoOcorre((array) $dados_TipoOcorrencia[0], $show);
        // debug($entity, true);

        // CAMPOS GERAIS
        $fields = $entity->campos;

        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['tpo_nome'];
        $campos[0][] = $fields['cla_id'];

        // TELAS APLICÁVEIS
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';

        // Busca telas aplicáveis vinculadas
        $dados_TelasAplicaveis = $this->tipoocorrencia->getTOTelasAplicaveis($id, $show);

        if (count($dados_TelasAplicaveis) > 0) {
            $total = count($dados_TelasAplicaveis);
            for ($c = 0; $c < count($dados_TelasAplicaveis); $c++) {
                // debug($dados_TelasAplicaveis[$c]);
                $fields = $entity->defCamposTelasAplicaveis(
                    $dados_TelasAplicaveis[$c],
                    $c,
                    $total,
                    $show
                );
                // debug($fields);
                $campos[1][$c][] = $fields['mod_id'];
                $campos[1][$c][] = $fields['tel_id'];
                $campos[1][$c][] = $fields['tof_campo'];
                if ($show) {
                    $campos[1][$c][] = $fields['bt_addta'];
                    $campos[1][$c][] = '';
                } else {
                    $campos[1][$c][] = $fields['bt_addta'];
                    $campos[1][$c][] = $fields['bt_delta'];
                }
            }
        } else {
            $fields = $entity->defCamposTelasAplicaveis(false, 0, $show);

            $campos[1][0][] = $fields['mod_id'];
            $campos[1][0][] = $fields['tel_id'];
            $campos[1][0][] = $fields['tof_campo'];
            $campos[1][0][] = $fields['bt_addta'];
            $campos[1][0][] = $fields['bt_delta'];
        }

        // AÇÕES
        $secao[2] = 'Ações';
        $displ[2] = 'tabela';

        $dados_Acao = $this->tipoocorrencia->getTOAcao($id);
        $dados_Acao = array_map(fn($r) => (array) $r, $dados_Acao);

        if (count($dados_Acao) > 0) {
            for ($c = 0; $c < count($dados_Acao); $c++) {

                $fields = $entity->defCamposAcao($dados_Acao[$c], $c, $show);

                $campos[2][$c][] = $fields['tpa_id'];
                // debug(array_keys($dados_Acao[$c]), true);

                $dnone = ($dados_Acao[$c]['tmo_id'] != 0) ? '' : 'd-none';
                $campos[2][$c][] = "<div id='divmovi[$c]' class='$dnone row col-6'>" . $fields['tmo_id'] . "</div>";

                $dnone = ($dados_Acao[$c]['tel_id'] != 0) ? '' : 'd-none';
                $campos[2][$c][] = "<div id='divtela[$c]' class='$dnone row col-6'>" . $fields['mod_id'] . $fields['tel_id'] . "</div>";

                $dnone = ($dados_Acao[$c]['stt_id'] != 0) ? '' : 'd-none';
                $campos[2][$c][] = "<div id='divstat[$c]' class='$dnone row col-6'>" . $fields['stt_id'] . "</div>";

                if ($show) {
                    $campos[2][$c][] = '';
                    $campos[2][$c][] = '';
                } else {
                    $campos[2][$c][] = $fields['bt_addtp'];
                    $campos[2][$c][] = $fields['bt_deltp'];
                }
            }
        } else {
            $fields = $entity->defCamposAcao(false, 0, true);

            $campos[2][0][] = $fields['tpa_id'];
            $campos[2][0][] = "<div id='divmovi[0]' class='d-none row col-6'>" . $fields['tmo_id'] . "</div>";
            $campos[2][0][] = "<div id='divtela[0]' class='d-none row col-6'>" . $fields['mod_id'] . $fields['tel_id'] . "</div>";
            $campos[2][0][] = "<div id='divstat[0]' class='d-none row col-6'>" . $fields['stt_id'] . "</div>";
            $campos[2][0][] = $fields['bt_addtp'];
            $campos[2][0][] = $fields['bt_deltp'];
        }

        // PERMISSÕES
        $secao[3] = 'Permissões';

        $fields3 = $entity->defPermissoes($dados_TipoOcorrencia[0]);
        $campos[3][0] = $fields3['prf_id'];

        // VIEW
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>
                                acerta_botoes_rep('telas_aplicaveis');
                                acerta_botoes_rep('acoes');
                                </script>";

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
            // verifica somente subtipo
            $existeSubtipo = $this->tipoocorrencia->db
                ->table('oco_subt_ocorrencia')
                ->where('tpo_id', (int) $id)
                ->countAllResults();

            if ($existeSubtipo > 0) {
                throw new \Exception('MSG_3'); // possui subtipo vinculado
            }

            // Soft delete
            $this->tipoocorrencia->delete($id);

            $ret['erro'] = false;
            $ret['msg']  = 'Tipo de Ocorrência excluída com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }


    public function ativinativ($id, $tipo)
    {
        // debug([$id, $tipo], true);
        $ret = [];

        try {
            if ($tipo == 1) {
                // ATIVAR
                $dad_atin = [
                    'tpo_ativo' => 'A'
                ];
            } else {
                // INATIVAR
                $subtipoModel = new OcorreModOcorrenciaModel();

                if ($subtipoModel->getSubtipoAtivo((int) $id)) {
                    throw new \Exception('MSG_14');
                }

                $dad_atin = [
                    'tpo_ativo' => 'I'
                ];
            }

            $this->tipoocorrencia->update($id, $dad_atin);

            $ret['erro'] = false;
            $ret['msg']  = 'Tipo de Ocorrência alterado com sucesso';
            session()->setFlashdata('msg', $ret['msg']);
            cache()->clean();
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        }

        return $this->response->setJSON($ret);
    }


    /**
     * Gravação
     * store
    
     * @return void
     */
    public function store()
    {
        $postado = $this->request->getPost();

        // Classes (array de arrays)
        if (isset($postado['cla_id']) && is_array($postado['cla_id'])) {
            $cla = [];
            foreach ($postado['cla_id'] as $linha) {
                if (isset($linha[0])) {
                    $cla[] = $linha[0];
                }
            }
            $postado['cla_id'] = $cla;
        }

        // Campos por tela
        if (isset($postado['tof_campo']) && is_array($postado['tof_campo'])) {
            foreach ($postado['tof_campo'] as $i => $linha) {
                if (is_array($linha) && isset($linha[0])) {
                    $postado['tof_campo'][$i] = $linha;
                }
            }
        }

        // Perfis (array aninhado)
        if (isset($postado['prf_id']) && is_array($postado['prf_id'])) {
            $prf = [];
            foreach ($postado['prf_id'] as $linha) {
                if (is_array($linha)) {
                    foreach ($linha as $id) {
                        $prf[] = $id;
                    }
                } else {
                    $prf[] = $linha;
                }
            }
            $postado['prf_id'] = $prf;
        }

        $db    = \Config\Database::connect();
        $ret   = [];
        $grupo = 'dbOcorrencia';

        try {
            $db->transBegin();

            // não permitir edição se já houver subtipo vinculado
            // Verifica unicidade
            $exists = $this->common->verificaUnico($this->tipoocorrencia, 'tpo_nome', $postado['tpo_nome'], 'tpo_id', $postado['tpo_id'] ?? null);

            if ($exists > 0) {
                throw new \Exception('MSG_8');
            }

            // Salva tipo de ocorrência
            if (!$this->tipoocorrencia->save($postado)) {
                throw new \Exception(implode('<br>', $this->tipoocorrencia->errors()));
            }

            $tpo_id = $this->tipoocorrencia->getInsertID();
            if (!$tpo_id && isset($postado['tpo_id'])) {
                $tpo_id = $postado['tpo_id'];
            }

            // Limpa relacionamentos
            if ($tpo_id) {
                $this->common->deleteReg($grupo, 'oco_tipo_ocorrencia_acao',     "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tipo_ocorrencia_classe',   "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tipo_ocorrencia_tela',     "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tipo_ocorrencia_campos',   "tpo_id = {$tpo_id}");
                $this->common->deleteReg($grupo, 'oco_tipo_ocorrencia_permissao', "tpo_id = {$tpo_id}");
            }

            // Ações
            if (!empty($postado['tpa_id'])) {
                $acoes = [];

                foreach ($postado['tpa_id'] as $i => $tpa_id) {


                    if (!$tpa_id) continue;

                    $mod_id = (!isset($postado['mod_id_acao'][$i]) || $postado['mod_id_acao'][$i] <= 0)
                        ? null
                        : (int) $postado['mod_id_acao'][$i];

                    $tel_id = (!isset($postado['tel_id_acao'][$i]) || $postado['tel_id_acao'][$i] <= 0)
                        ? null
                        : (int) $postado['tel_id_acao'][$i];

                    $stt_id = (!isset($postado['stt_id_acao'][$i]) || $postado['stt_id_acao'][$i] <= 0)
                        ? null
                        : (int) $postado['stt_id_acao'][$i];

                    $tmo_id = (!isset($postado['tmo_id_acao'][$i]) || $postado['tmo_id_acao'][$i] <= 0)
                        ? null
                        : (int) $postado['tmo_id_acao'][$i];

                    $acoes[] = [
                        'tpo_id' => $tpo_id,
                        'tpa_id' => $tpa_id,
                        'mod_id' => $mod_id,
                        'tel_id' => $tel_id,
                        'stt_id' => $stt_id,
                        'tmo_id' => $tmo_id,
                    ];
                }

                $this->common->insertRegBatch('dbOcorrencia', 'oco_tipo_ocorrencia_acao', $acoes);
            }

            // Classes
            if (!empty($postado['cla_id'])) {
                $classes = [];
                foreach ($postado['cla_id'] as $cla_id) {
                    $classes[] = ['tpo_id' => $tpo_id, 'cla_id' => $cla_id];
                }
                $this->common->insertRegBatch($grupo, 'oco_tipo_ocorrencia_classe', $classes);
            }

            // Telas
            if (!empty($postado['mod_id']) && !empty($postado['tel_id'])) {
                $telas = [];
                foreach ($postado['mod_id'] as $i => $mod_id) {
                    if (!empty($postado['tel_id'][$i])) {
                        $telas[] = [
                            'tpo_id' => $tpo_id,
                            'mod_id' => $mod_id,
                            'tel_id' => $postado['tel_id'][$i]
                        ];
                    }
                }
                if (!empty($telas)) {
                    $this->common->insertRegBatch($grupo, 'oco_tipo_ocorrencia_tela', $telas);
                }
            }

            // Campos
            if (!empty($postado['tof_campo'])) {
                $campos = [];
                foreach ($postado['tof_campo'] as $i => $lista) {
                    $tel_id = $postado['tel_id'][$i] ?? null;
                    if (!$tel_id) continue;

                    foreach ($lista as $campo) {
                        if ($campo !== '') {
                            $campos[] = [
                                'tpo_id'    => $tpo_id,
                                'tel_id'    => $tel_id,
                                'tof_campo' => $campo
                            ];
                        }
                    }
                }
                if (!empty($campos)) {
                    $this->common->insertRegBatch($grupo, 'oco_tipo_ocorrencia_campos', $campos);
                }
            }

            // Permissões
            if (!empty($postado['prf_id'])) {
                $permissoes = [];
                foreach ($postado['prf_id'] as $prf_id) {
                    $permissoes[] = [
                        'tpo_id' => $tpo_id,
                        'prf_id' => $prf_id
                    ];
                }
                $this->common->insertRegBatch($grupo, 'oco_tipo_ocorrencia_permissao', $permissoes);
            }

            $db->transCommit();

            session()->setFlashdata('msg', 'Tipo de Ocorrência gravado com sucesso!');

            return $this->response->setJSON([
                'erro' => false,
                'url'  => site_url($this->data['controler'])
            ]);
        } catch (\Exception $e) {
            $db->transRollback();

            if ($e->getMessage() === 'MSG_8') {
                return $this->response->setJSON([
                    'erro' => true,
                    'msg'  => 8
                ]);
            }

            return $this->response->setJSON([
                'erro' => true,
                'msg'  => 'Erro ao gravar o Tipo de Ocorrência:<br><br>' . $e->getMessage()
            ]);
        }
    }
}
