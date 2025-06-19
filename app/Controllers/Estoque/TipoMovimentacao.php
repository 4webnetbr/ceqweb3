<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

class TipoMovimentacao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $tpmov;
    public $common;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->tpmov     = new EstoquTipoMovimentacaoModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'tmo_id,');
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
        $campos = montaColunasCampos($this->data, 'tmo_id');
        $dados_tpmovimentacao = $this->tpmov->getTipoMovimentacao();
        // debug($dados_tpmovimentacao, true);
        $tpmovim = [
            'data' => montaListaColunas($this->data, 'tmo_id', $dados_tpmovimentacao, $campos[1]),
        ];

        echo json_encode($tpmovim);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $fields = $this->tpmov->defCampos();
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = $fields['tmo_nome'];
        $campos[0][count($campos[0])] = $fields['tmo_acumulador'];
        $campos[0][count($campos[0])] = $fields['tmo_conferencia'];
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp'];
        $campos[0][count($campos[0])] = $fields['tmo_semestoque'];
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp_entrada'];
        $campos[0][count($campos[0])] = $fields['tmo_atendeautomatico'];
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp_saida'];
        $campos[0][count($campos[0])] = $fields['tmo_entrefiliais'];
        $campos[0][count($campos[0])] = 'vazio2';
        $campos[0][count($campos[0])] = $fields['tmo_estoquepadrao'];

        $secao[1] = 'Movimentações';
        $displ[1] = 'tabela';
        $fields = $this->tpmov->defCamposMov();
        $campos[1][0] = [];
        $campos[1][0][count($campos[1][0])] = $fields['tmm_id'];
        $campos[1][0][count($campos[1][0])] = $fields['tmm_deposito_origem'];
        $campos[1][0][count($campos[1][0])] = $fields['tmm_deposito_destino'];
        $campos[1][0][count($campos[1][0])] = $fields['bt_add'];
        $campos[1][0][count($campos[1][0])] = $fields['bt_del'];

        $secao[2] = 'Permissões';
        $fields = $this->tpmov->defCamposPrf();
        $campos[2][0] = $fields['prf_id'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']      = $displ;
        $this->data['destino']    = 'store';

        $this->data['script'] = "<script>acerta_botoes_rep('movimentacoes')</script>";

        echo view('vw_edicao', $this->data);
    }

    /**
     * Summary of addCampo
     * @param mixed $ind
     * @return never
     */
    public function addCampo($ind)
    {
        $fields = $this->tpmov->defCamposMov(false, $ind);
        $campo[0] = $fields['tmm_id'];
        $campo[1] = $fields['tmm_deposito_origem'];
        $campo[2] = $fields['tmm_deposito_destino'];
        $campo[3] = $fields['bt_add'];
        $campo[4] = $fields['bt_del'];

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
    public function edit($id, $show = false)
    {
        $dados_tmov = $this->tpmov->find($id);
        $fields = $this->tpmov->defCampos($dados_tmov);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = $fields['tmo_nome'];
        $campos[0][count($campos[0])] = $fields['tmo_acumulador'];
        $campos[0][count($campos[0])] = $fields['tmo_conferencia'];
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp'];
        $campos[0][count($campos[0])] = $fields['tmo_semestoque'];
        // $campos[0][count($campos[0])] = 'vazio2';
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp_entrada'];
        $campos[0][count($campos[0])] = $fields['tmo_atendeautomatico'];
        $campos[0][count($campos[0])] = $fields['tmo_transacao_erp_saida'];
        // $campos[0][count($campos[0])] = 'vazio2';
        $campos[0][count($campos[0])] = $fields['tmo_entrefiliais'];
        $campos[0][count($campos[0])] = 'vazio2';
        $campos[0][count($campos[0])] = $fields['tmo_estoquepadrao'];

        $secao[1] = 'Movimentações';
        $displ[1] = 'tabela';
        $dados_tmmm = $this->tpmov->getTipoMovimentacaoMovimentos($id);
        if (count($dados_tmmm) > 0) {
            for ($c = 0; $c < count($dados_tmmm); $c++) {
                $fields = $this->tpmov->defCamposMov($dados_tmmm[$c], $c, $show);
                $campos[1][$c][0] = $fields['tmm_id'];
                $campos[1][$c][count($campos[1][$c])] = $fields['tmm_deposito_origem'];
                $campos[1][$c][count($campos[1][$c])] = $fields['tmm_deposito_destino'];
                if (!$show) {
                    $campos[1][$c][count($campos[1][$c])] = $fields['bt_add'];
                    $campos[1][$c][count($campos[1][$c])] = $fields['bt_del'];
                } else {
                    $campos[1][$c][count($campos[1][$c])] = '';
                    $campos[1][$c][count($campos[1][$c])] = '';
                }
            }
        } else {
            $fields = $this->tpmov->defCamposMov(false, 0, $show);
            $campos[1][0][0] = $fields['tmm_id'];
            $campos[1][0][count($campos[1][0])] = $fields['tmm_deposito_origem'];
            $campos[1][0][count($campos[1][0])] = $fields['tmm_deposito_destino'];
            if (!$show) {
                $campos[1][0][count($campos[1][0])] = $fields['bt_add'];
                $campos[1][0][count($campos[1][0])] = $fields['bt_del'];
            } else {
                $campos[1][0][count($campos[1][0])] = '';
                $campos[1][0][count($campos[1][0])] = '';
            }
        }

        $secao[2] = 'Permissões';
        $dados_per = $this->tpmov->getTipoMovimentacaoPermissao($id);
        $permiss['prf_id'] = [];
        if (count($dados_per) > 0) {
            for ($p = 0; $p < count($dados_per); $p++) {
                array_push($permiss['prf_id'], $dados_per[$p]['prf_id']);
            }
        }
        $fields = $this->tpmov->defCamposPrf($permiss, $show);
        $campos[2][0] = $fields['prf_id'];

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['displ']  = $displ;
        $this->data['destino'] = 'store';

        $this->data['script'] = "<script>acerta_botoes_rep('movimentacoes');acertaObrigatorio('tmo_acumulador')</script>";
        $this->data['desc_edicao'] = $dados_tmov['tmo_nome'];
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_tipo_movimentacao', $id);
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
            $this->tpmov->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Tipo de Movimentação Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Movimentação Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'tmo_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'tmo_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->tpmov->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Movimentação Alterada com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar a Movimentação, Verifique!<br><br>';
        }
        echo json_encode($ret);
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
        $ret['erro'] = false;
        $postado = $this->request->getPost();

        $exists = $this->common->verificaUnico($this->tpmov, 'tmo_nome', $postado['tmo_nome'], 'tmo_id', $postado['tmo_id']);
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
        } else {
            $this->tpmov->transBegin();
            $erros = [];
            $sql_tmo = [
                'tmo_id' => $postado['tmo_id'],
                'tmo_nome' => $postado['tmo_nome'],
                'tmo_acumulador' => $postado['tmo_acumulador'],
                'tmo_conferencia' => $postado['tmo_conferencia'],
                'tmo_semestoque' => $postado['tmo_semestoque'],
                'tmo_transacao_erp' => $postado['tmo_transacao_erp'],
                'tmo_atendeautomatico' => $postado['tmo_atendeautomatico'],
                'tmo_transacao_erp_entrada' => $postado['tmo_transacao_erp_entrada'],
                'tmo_transacao_erp_saida' => $postado['tmo_transacao_erp_saida'],
                'tmo_entrefiliais' => $postado['tmo_entrefiliais'],
                'tmo_estoquepadrao' => $postado['tmo_estoquepadrao'],
            ];
            if ($this->tpmov->save($sql_tmo)) {
                $tmo_id = $this->tpmov->getInsertID();
                if ($postado['tmo_id'] != '') {
                    $tmo_id = $postado['tmo_id'];
                }
                $data_atu = date('Y-m-d H:i');

                // GRAVAÇãO DOS Movimentos
                foreach ($postado['tmm_id'] as $key => $value) {
                    if ($postado['tmm_deposito_origem'][$key] != '') {
                        $sql_tmm = [
                            'tmm_id' => $postado['tmm_id'][$key],
                            'tmo_id' => $tmo_id,
                            'tmm_deposito_origem' => $postado['tmm_deposito_origem'][$key],
                            'tmm_deposito_destino' => isset($postado['tmm_deposito_destino'][$key]) ? $postado['tmm_deposito_destino'][$key] : null,
                            'tmm_atualizado' => $data_atu,
                        ];
                        if ($postado['tmm_id'][$key] == '') {
                            $tmm_id = $this->common->insertReg('dbEstoque', 'est_tipo_movimentacao_movimento', $sql_tmm);
                        } else {
                            $tmm_id = $postado['tmm_id'][$key];
                            // debug($postado);
                            $this->common->updateReg("dbEstoque", "est_tipo_movimentacao_movimento", "tmm_id = " . $tmm_id, $sql_tmm);
                        }
                        if (!$tmm_id) {
                            $ret['erro'] = true;
                            $erros = $this->common->errors();
                            $ret['msg'] = 'Não foi possível gravar os Movimentos do Tipo de Movimentação, Verifique!';
                        }
                    }
                }
                $this->common->deleteReg("dbEstoque", "est_tipo_movimentacao_movimento", "tmo_id = " . $tmo_id . " AND tmm_atualizado != '" . $data_atu . "'");

                // GRAVAÇÃO DOS PERFIS
                $this->common->deleteReg("dbEstoque", "est_tipo_movimentacao_permissao", "tmo_id = " . $tmo_id);
                foreach ($postado['prf_id'] as $key => $value) {
                    $sql_prf = [
                        'tmo_id' => $tmo_id,
                        'prf_id' => $postado['prf_id'][$key],
                        'tmp_atualizado' => $data_atu,
                    ];
                    $prf_id = $this->common->insertReg('dbEstoque', 'est_tipo_movimentacao_permissao', $sql_prf);
                    if (!$prf_id) {
                        $this->tpmov->transRollback();

                        $ret['erro'] = true;
                        $erros = $this->common->errors();
                        $ret['msg'] = 'Não foi possível gravar as Permissões do Tipo de Movimentação, Verifique!';
                    }
                }
            } else {
                $ret['erro'] = true;
                $erros = $this->tpmov->errors();
            }
            if ($ret['erro']) {
                $this->tpmov->transRollback();
                $ret['msg'] = 'Não foi possível gravar o Tipo de Movimentação, Verifique!<br><br>';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                    if (is_numeric($erro)) {
                        $ret['msg'] = $erro;
                    }
                }
            } else {
                $this->tpmov->transCommit();
                $ret['msg']  = 'Tipo de Movimentação gravado com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url']  = site_url($this->data['controler']);
            }
        }
        echo json_encode($ret);
    }
}
