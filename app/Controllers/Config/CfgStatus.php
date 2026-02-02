<?php

namespace App\Controllers\Config;

use App\Libraries\MyCampo;
use App\Controllers\BaseController;
use App\Entities\Config\EntCfgStatus;
use App\Models\CommonModel;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Config\ConfigStatusModel;

class CfgStatus extends BaseController
{
    use ForeignKeyUsageChecker;

    protected ConfigStatusModel $status;
    protected CommonModel $common;
    protected array $data;

    public function __construct()
    {
        $this->data   = session()->getFlashdata('dados_tela') ?? [];
        $this->status = new ConfigStatusModel();
        $this->permissao = $this->data['permissao'];
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
        $order          = new MyCampo();
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-arrow-down-short-wide" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Ordenar</div>';
        $order->place    = 'Ordenar os Status';
        $order->funcChan = 'redireciona(\'CfgStatus/ordenar/\')';
        $order->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_order = $order->crBotao();
        $this->data['botao'] = $this->bt_order;

        $this->data['colunas'] = montaColunasLista($this->data, 'cor_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'stt_id');
        $dados  = $this->status->getStatus();

        foreach ($dados as $ent) {
            $ent->tabela  = 'cfg_status';
            $ent->stt_cor = fmtEtiquetaCor($ent->cor_valorrgb, $ent->stt_nome);
            // debug($ent);
        }

        echo json_encode([
            'data' => montaListaColunasEnt($this->data, 'stt_id', $dados, 'stt_nome')
        ]);
    }


    public function ordenar()
    {
        $lst_status     =  $this->status->getStatusOrdem();
        // debug($lst_status, true);
        $this->data['desc_metodo'] = 'Ordenação de ';
        $this->data['lst_status']    = $lst_status;
        $this->data['destino']    = 'storeOrd';

        echo view('vw_status_ordenar', $this->data);
    }


    public function add()
    {
        $estatus = new EntCfgStatus();

        $this->data['secoes']  = ['Dados Gerais', 'Permissões'];
        $this->data['campos']  = [
            [
                $estatus->campos['stt_id'],
                $estatus->campos['stt_nome'],
                // 'vazio2',
                $estatus->campos['mod_id'],
                $estatus->campos['tel_id'],
                // $estatus->campos['stt_cor'],
                $estatus->campos['cor_id'],
                // 'vazio2',
                $estatus->campos['stt_exclusao'],
                $estatus->campos['stt_edicao'],
                $estatus->campos['stt_disponivel'],
                $estatus->campos['stt_impressao'],
            ],
            [
                $estatus->campos['prf_id'],
            ]
        ];
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function edit($id, $show = false)
    {
        $dados = $this->status->getStatus($id);

        // debug($dados, true);

        if (!$dados) {
            return view('errors/vw_semregistro', [
                'mensagem' => 'Status não encontrado'
            ]);
        }

        $estatus = new EntCfgStatus((array) $dados);

        $this->data['secoes'] = ['Dados Gerais', 'Permissões'];
        $this->data['campos'] = [
            [
                $estatus->campos['stt_id'],
                $estatus->campos['stt_nome'],
                $estatus->campos['mod_id'],
                $estatus->campos['tel_id'],
                // $estatus->campos['stt_cor'],
                $estatus->campos['cor_id'],
                // 'vazio2',
                $estatus->campos['stt_exclusao'],
                $estatus->campos['stt_edicao'],
                $estatus->campos['stt_disponivel'],
                $estatus->campos['stt_impressao'],
            ],
            [
                $estatus->campos['prf_id'],
            ]
        ];
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('cfg_status', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];

        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_status', 'stt_id', (int) $id);

            // Soft delete
            $this->status->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Status excluído com sucesso!');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'stt_ativo' => 'A'
                ];
            } else {
                $dad_atin = [
                    'stt_ativo' => 'I'
                ];
                $this->verificarUsoEmRelacionamentos('cfg_status', 'stt_id', (int) $id);
            }

            $this->status->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Status Alterado com Sucesso');
            $ret['msg'] = 'Status Alterado com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            // $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14; // ou código personalizado, se preferir
        }

        echo json_encode($ret);
    }

    public function storeOrd()
    {
        $req = $this->request->getPost();
        $ord = 0;
        foreach ($req as $key => $value) {
            if (substr($key, 0, 3) == 'tel') {
                $ord = 1;
            } else {
                $updt = [
                    'stt_ordem' => $ord
                ];
                $this->status->update($value, $updt);
                $ord++;
            }
        }
        // debug($ord_, true);
        session()->setFlashdata('msg', 'Status Reordenado com Sucesso!!!');
        echo json_encode([
            'erro' => false,
            'url'  => site_url($this->data['controler'])
        ]);
    }


    public function store()
    {
        $ret = [];
        $ret['erro'] = false;

        $postado = $this->request->getPost();
        $postado['prf_id'] = array_merge(...$postado['prf_id']);

        $ent = new EntCfgStatus($postado);
        $ent->fill($postado);

        if ($postado['stt_id'] == '') { // define ordem só se for inclusão
            $ent->stt_ordem = $this->status->getProximaOrdem($ent->tel_id);
        }

        $exists = $this->common->verificaUnico($this->status, 'stt_nome', $postado['stt_nome'], 'stt_id', $postado['stt_id'], 'tel_id', $postado['tel_id']);

        // debug(var_dump($exists), true);
        if (intval($exists) > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
        } else {
            $this->status->transBegin();

            try {
                // debug($ent, true);
                $salva = $this->status->save($ent);
                // debug($this->status->getLastQuery(), true);

                if (!$salva) {
                    $ret['erro'] = true;
                    $ret['msg']  = implode('<br>', $this->status->errors());
                    // return;
                } else {
                    if ($postado['stt_id'] == '') {
                        $postado['stt_id'] = $this->status->getInsertId();
                    }
                    // GRAVAÇÃO DOS PERFIS
                    $this->common->deleteReg("default", "cfg_status_permissao", "stt_id = " . $postado['stt_id']);
                    $data_atu = date('Y-m-d H:i');
                    foreach ($postado['prf_id'] as $key => $value) {
                        $sql_prf = [
                            'stt_id' => $postado['stt_id'],
                            'prf_id' => $postado['prf_id'][$key],
                            'stp_atualizado' => $data_atu,
                        ];
                        $prf_id = $this->common->insertReg('default', 'cfg_status_permissao', $sql_prf);
                        if (!$prf_id) {
                            $this->status->transRollback();

                            $ret['erro'] = true;
                            $erros = $this->common->errors();
                            $ret['msg'] = 'Não foi possível gravar as Permissões do Status, Verifique!';
                        }
                    }
                }

                if (!$ret['erro']) {
                    $this->status->transCommit();
                    session()->setFlashdata('msg', 'Status gravado com sucesso!');
                    $ret['erro'] = false;
                    $ret['url'] = site_url($this->data['controler']);
                }
            } catch (\Throwable $e) {
                $this->status->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Erro ao salvar Status.'
                ];
            }
        }
        echo json_encode($ret);
    }
}
