<?php

namespace App\Controllers\Ocorrencia;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoSubtOcorrencia;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoSubtOcorrencia extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $tipoocorrencia;
    public $subtocorrencia;
    public $common;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data           = session()->getFlashdata('dados_tela');
        $this->permissao      = $this->data['permissao'];
        $this->subtocorrencia  = new OcorreSubtOcorrenciaModel();
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
        $campos = montaColunasCampos($this->data, 'sut_id');
        $dados_mdocor = $this->subtocorrencia->getSubtOcorrencia();
        $mdocor = [
            'data' => montaListaColunasEnt($this->data, 'sut_id', $dados_mdocor, $campos[1]),
        ];

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
        $entity = new EntOcoSubtOcorrencia();
        $fields = $entity->campos;

        // Dados Gerais
        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['sut_id'];
        $campos[0][] = $fields['sut_nome'];
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['cla_id'];
        $campos[0][] = $fields['sut_fina'];
        // debug($fields, true);


        // Telas Aplicáveis
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';
        $campos[1] = [];

        // ações
        $secao[2] = 'Ações';
        $displ[2] = 'tabela';

        $campos[2] = [];

        // PERMISSÕES
        $secao[3] = 'Permissões';
        $dadosPerm = $dados_SubtOcorrencia[0] ?? [];
        $fields3 = $entity->defPermissoes($dadosPerm);
        $campos[3][0] = $fields3['prf_id'];

        // Define dados da tela
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';
        // $this->data['script'] = '<script>acertaDependente();</script>';

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
        $entity = new EntOcoSubtOcorrencia();

        for ($t = 0; $t < sizeof($ttelas); $t++) {
            $total = sizeof($ttelas);
            // debug($ttelas[$t]);
            $fields = $entity->defCamposTelasAplicaveis($ttelas[$t], $ind, $total);
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

        $campo = [];
        $tipoAcaoModel  = new OcorreTipoOcorrenciaModel();
        $tacao          = $tipoAcaoModel->getTOAcao($tpo_id);
        $entity = new EntOcoSubtOcorrencia();

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
        $show = false;
        // try {
        //     // Checa uso do tipo em outros bancos
        //     $this->verificarUsoEmRelacionamentos('oco_subt_ocorrencia', 'sut_id', (int) $id);
        // } catch (\Exception $e) {
        //     $show = true;
        // }

        // Busca os dados do Modelo de Ocorrência pelo ID
        $dados_SubtOcorrencia = (array) $this->subtocorrencia->getSubtOcorrencia($id)[0];
        $classes = $this->subtocorrencia->getClassePorSubtipo($id);
        // debug($classes, true);
        $dados_SubtOcorrencia['cla_id'] = array_column($classes, 'cla_id');

        // Cria a entity com os dados retornados
        // debug($dados_SubtOcorrencia['cla_id'], true);
        $entity = new EntOcoSubtOcorrencia($dados_SubtOcorrencia);

        $fields = $entity->defCampos($show);

        // Dados Gerais
        $secao[0] = 'Dados Gerais';
        $campos[0][] = $fields['sut_id'];
        $campos[0][] = $fields['sut_nome'];
        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['cla_id'];
        $campos[0][] = $fields['sut_fina'];
        // debug($fields, true);

        // Telas Aplicáveis
        $secao[1] = 'Telas Aplicaveis';
        $displ[1] = 'tabela';
        $sut_id   = (int) $dados_SubtOcorrencia['sut_id'];
        $campos[1] = [];

        $dados_TelasAplicaveis = $this->subtocorrencia->getTOTelasAplicaveis($sut_id);

        // debug($dados_TelasAplicaveis, true);
        if (count($dados_TelasAplicaveis) > 0) {
            $total = count($dados_TelasAplicaveis);

            for ($c = 0; $c < $total; $c++) {
                $fields = $entity->defCamposTelasAplicaveis($dados_TelasAplicaveis[$c], $c, $total, $show);

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
        $dados_Acao = $this->subtocorrencia->getTOAcao($id);

        if (!empty($dados_Acao)) {
            $total = count($dados_Acao);
            // debug($dados_Acao, true);
            foreach ($dados_Acao as $c => $acao) {
                $fields  = $entity->defCamposAcao($acao, $c, $total, 'edit');
                $clsMovi = $clsTela = $clsStat = 'd-none';

                switch ((int) $acao->tpa_id) {
                    case 3: // Gerar Movimentação
                        $clsMovi = '';
                        break;

                    case 2: // Abrir Tela
                        $clsTela = '';
                        break;

                    case 4: // Alterar Status
                        $clsStat = '';
                        break;
                }

                $campos[2][$c][] = $fields['tpa_id'];
                $campos[2][$c][] = "<div id='divmovi[$c]' class='{$clsMovi} row col-6'>{$fields['tmo_id']}</div>";
                $campos[2][$c][] = "<div id='divtela[$c]' class='{$clsTela} row col-6'>{$fields['mod_id']}{$fields['tel_id']}</div>";
                $campos[2][$c][] = "<div id='divstat[$c]' class='{$clsStat} row col-6'>{$fields['stt_id']}</div>";
                $campos[2][$c][] = '';
                $campos[2][$c][] = $fields['bt_deltp'];
            }
        }

        // PERMISSÕES
        $secao[3] = 'Permissões';
        $permissoes = $this->subtocorrencia->getPermissoesSubtipo($id);
        $dados_SubtOcorrencia['prf_id'] = array_column($permissoes, 'prf_id');
        $dadosPerm = (object) $dados_SubtOcorrencia;
        $fields3 = $entity->defPermissoes($dadosPerm);
        $campos[3][0] = $fields3['prf_id'];

        // Define dados finais da tela
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['desc_edicao']   = $dados_SubtOcorrencia['sut_nome'];
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
            $this->verificarUsoEmRelacionamentos('oco_subt_ocorrencia', 'sut_id', (int) $id);
            $this->subtocorrencia->delete($id);
            $ret['erro'] = false;
            $ret['msg']  = 'Ocorrência Excluída com Sucesso';
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }

    /**
     * ATIVO
     * & INATIVO
     *
     * @param mixed $id 
     * @return void
     */
    public function ativinativ($id, $tipo)
    {
        // debug([$id, $tipo], true);
        $ret = [];

        try {
            if ($tipo == 1) {
                // ATIVAR
                $dad_atin = [
                    'sut_ativo' => 'A'
                ];
            } else {
                // INATIVAR
                if ($this->subtocorrencia->getUsoGestao((int) $id)) {
                    throw new \Exception('MSG_14'); // possui ocorrência vinculada
                }

                $dad_atin = [
                    'sut_ativo' => 'I'
                ];
            }

            $this->subtocorrencia->update($id, $dad_atin);

            $ret['erro'] = false;
            $ret['msg']  = 'Subtipo de ocorrência alterado com sucesso';
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
        // debug($postado, true);
        $ret   = [];
        $grupo = 'dbOcorrencia';
        $db    = db_connect($grupo);

        // Corrige campos que não podem ser array
        // tipo de ocorrência
        if (is_array($postado['tpo_id'] ?? null)) {
            $postado['tpo_id'] = $postado['tpo_id'][0];
        }
        //finalização automática
        if (is_array($postado['sut_fina'] ?? null)) {
            $postado['sut_fina'] = $postado['sut_fina'][0];  
        }
        // classes
        $classesSelecionadas = $postado['cla_id'] ?? [];
        unset($postado['cla_id']);
        // permissões
        if (isset($postado['prf_id']) && is_array($postado['prf_id'])) {
            $perfis = [];
            foreach ($postado['prf_id'] as $linha) {
                $perfis[] = is_array($linha) ? $linha[0] : $linha;
            }
            $postado['prf_id'] = $perfis;
        }

        try {
            $db->transBegin();

            // Verifica unicidade
            $exists = $this->common->verificaUnico($this->subtocorrencia, 'sut_nome', $postado['sut_nome'], 'sut_id', $postado['sut_id']);
            if ($exists > 0) {
                throw new \Exception('MSG_8');
            }

            // Salvar Tipo de Ocorrência (principal)
            if (!$this->subtocorrencia->save($postado)) {
                throw new \Exception(implode('<br>', $this->subtocorrencia->errors()));
            }

            if (empty($postado['sut_id'])) {
                unset($postado['sut_id']);

                $this->subtocorrencia->insert($postado);
                $sut_id = $this->subtocorrencia->getInsertID();
            } else {
                $sut_id = (int)$postado['sut_id'];
                $this->subtocorrencia->update($sut_id, $postado);
            }

            // Recupera ID do tipo (novo ou existente)
            if (!isset($postado['sut_id']) || empty($postado['sut_id'])) {
                $sut_id = $this->subtocorrencia->getInsertID();
            } else {
                $sut_id = $postado['sut_id'];
                // Se for update, apaga os registros relacionados
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_acao',   "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_tela',   "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_campos', "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_classe', "sut_id = {$sut_id}");
                $this->common->deleteReg($grupo, 'oco_subt_ocorrencia_permissao', "sut_id = {$sut_id}");
            }
            // debug($sut_id);

            // Ações
            if (!empty($postado['tpa_id'])) {
                // debug($postado, true);
                $acoes = [];

                foreach ($postado['tpa_id'] as $i => $tpa_id) {

                    if (!$tpa_id) continue;

                    $mod_id = $postado['mod_id_tpa'][$i];
                    $tel_id = $postado['tel_id_tpa'][$i];
                    $stt_id = $postado['stt_id_tpa'][$i];
                    $tmo_id = $postado['tmo_id_tpa'][$i];

                    $acoes[] = [
                        'sut_id' => $sut_id,
                        'tpa_id' => $tpa_id,
                        'mod_id' => $mod_id,
                        'tel_id' => $tel_id,
                        'stt_id' => $stt_id,
                        'tmo_id' => $tmo_id,
                    ];
                }
                // debug($acoes, true);
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
                    $this->common->insertRegBatch($grupo, 'oco_subt_ocorrencia_tela', $telas);
                }
            }


            // CLASSES
            if (!empty($classesSelecionadas)) {
                $classes = [];

                foreach ($classesSelecionadas as $cla_id) {
                    if (!$cla_id) continue;

                    $classes[] = [
                        'sut_id' => $sut_id,
                        'cla_id' => $cla_id
                    ];
                }
                if (!empty($classes)) {
                    $this->common->insertRegBatch(
                        $grupo,
                        'oco_subt_ocorrencia_classe',
                        $classes
                    );
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

                    // SE VIER STRING, CONVERTE PRA ARRAY
                    if (!is_array($camposTela)) {
                        $camposTela = [$camposTela];
                    }

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

            // Permissões
            if (!empty($postado['prf_id'])) {
                $permissoes = [];
                foreach ($postado['prf_id'] as $prf_id) {
                    $permissoes[] = [
                        'sut_id' => $sut_id,
                        'prf_id' => $prf_id
                    ];
                }
                $db->table('oco_subt_ocorrencia_permissao')->insertBatch($permissoes);
                // debug($permissoes, true);
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
