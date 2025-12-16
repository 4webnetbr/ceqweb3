<?php

namespace App\Controllers\Config;

use App\Libraries\MyCampo;
use App\Controllers\BaseController;
use App\Entities\Config\EntCfgStatus;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Config\ConfigStatusModel;

class CfgStatus extends BaseController
{
    use ForeignKeyUsageChecker;

    protected ConfigStatusModel $status;
    protected array $data;

    public function __construct()
    {
        $this->data   = session()->getFlashdata('dados_tela') ?? [];
        $this->status = new ConfigStatusModel();
        $this->permissao = $this->data['permissao'];

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
            $ent->stt_cor = fmtEtiquetaCorBst($ent->stt_cor, $ent->stt_nome);
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
        $status = new EntCfgStatus();

        $this->data['secoes']  = ['Dados Gerais'];
        $this->data['campos']  = [[
            $status->campos['stt_id'],
            $status->campos['stt_nome'],
            // 'vazio2',
            $status->campos['mod_id'],
            $status->campos['tel_id'],
            $status->campos['stt_cor'],
            // 'vazio2',
            $status->campos['stt_exclusao'],
            $status->campos['stt_edicao'],
            $status->campos['stt_disponivel'],
        ]];
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function edit($id, $show = false)
    {
        $status = $this->status->find($id);

        if (!$status) {
            return view('errors/registro_nao_encontrado', [
                'mensagem' => 'Status não encontrado'
            ]);
        }

        $status->campos = $status->defCampos($show);

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $status->campos['stt_id'],
            $status->campos['stt_nome'],
            // 'vazio2',
            $status->campos['mod_id'],
            $status->campos['tel_id'],
            $status->campos['stt_cor'],
            $status->campos['stt_exclusao'],
            $status->campos['stt_edicao'],
            $status->campos['stt_disponivel'],
        ]];
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('cfg_status', $id);

        echo view('vw_edicao', $this->data);
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
        $ent = new EntCfgStatus($this->request->getPost());
        $ent->stt_ordem = $this->status->getProximaOrdem($ent->tel_id);

        if (!$this->status->save($ent)) {
            echo json_encode([
                'erro' => true,
                'msg'  => implode('<br>', $this->status->errors())
            ]);
            return;
        }

        session()->setFlashdata('msg', 'Status gravado com sucesso!');
        echo json_encode([
            'erro' => false,
            'url'  => site_url($this->data['controler'])
        ]);
    }
}
