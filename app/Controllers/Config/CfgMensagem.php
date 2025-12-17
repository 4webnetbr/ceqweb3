<?php 

namespace App\Controllers\Config;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigMensagemModel;
use App\Entities\Config\EntCfgMensagem;

Class CfgMensagem extends BaseController
{
    public $data      = [];
    public $permissao = '';
    public $mensagem;
    public $common;

    public function __construct()
    {
		$this->data       = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->common     = new CommonModel();
		$this->mensagem   = new ConfigMensagemModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'msg_id,');
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
            $campos = montaColunasCampos($this->data, 'msg_id');
            $dados_tela = $this->mensagem->getMensagem();
            $mensagem = [
                'data' => montaListaColunasEnt($this->data, 'msg_id', $dados_tela, $campos[1]),
            ];
            cache()->save('mensagem', $mensagem, 60000);
        echo json_encode($mensagem);
    }

    public function add($modal = false)
    {
        $men = new EntCfgMensagem();
    
        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $men->campos['msg_id'],
            $men->campos['msg_titulo'],
            $men->campos['msg_tipo'],
            $men->campos['msg_cor'],
            $men->campos['msg_mensagem']
            ]];
        $this->data['destino']    = 'store';
    
        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }


    public function show($id){
        $this->edit($id, true);
    }


    public function edit($id, $show = false)
    {
        $men = $this->mensagem->find($id);
    
        if (!$men) {
            throw new \Exception('Impressora não encontrada');
        }
    
        $men->campos = $men->defCampos($show);

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $men->campos['msg_id'],
            $men->campos['msg_titulo'],
            $men->campos['msg_tipo'],
            $men->campos['msg_cor'],
            $men->campos['msg_mensagem']
            ]];
        $this->data['destino']    = 'store';
        $this->data['log']     = buscaLog('cfg_impressora', $id);
    
        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->mensagem->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Mensagem Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Mensagem, Verifique!<br><br>';
            $ret['msg'] .= 'Está Mensagem possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'msg_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'msg_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->mensagem->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Mensagem Alterada com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Mensagem, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $ret     = [];
        $postado = $this->request->getPost();
        $men    = new EntCfgMensagem($postado);

        $exists = $this->common->verificaUnico($this->mensagem, 'msg_titulo', $postado['msg_titulo'], 'msg_id', $postado['msg_id']);

        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 8;
        } else {
            $this->mensagem->transBegin();

            try {
                if (!$this->mensagem->save($men)) {
                    throw new \Exception(implode(' ', $this->mensagem->errors()));
                }
                $this->mensagem->transCommit();
                cache()->clean();
                session()->setFlashdata('msg', 'Mensagem gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Mensagem gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler'])
                ];
            } catch (\Throwable $e) {
                $this->mensagem->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Não foi possível gravar a Mensagem, Verifique!<br><br>'
                ];
            }
        }
        echo json_encode($ret);
    } 

}
