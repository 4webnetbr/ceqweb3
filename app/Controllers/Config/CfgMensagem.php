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

        // Caso exista erro de permissão, bloqueia acesso
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

        // $this->mensagem->getMensagensCache();    
        // echo '<pre>';
        // echo 'CACHE cfg_mensagens_obj: ';
        // var_dump(cache()->get('cfg_mensagens_obj') ? 'SIM' : 'NAO');
        // echo '</pre>';
        // exit;
    }
    

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // Monta campos da listagem
            $campos = montaColunasCampos($this->data, 'msg_id');
            $dados_tela = $this->mensagem->getMensagem();
            $this->data['exclusao'] = false;

            // Estrutura padrão para DataTable
            $mensagem = [
                'data' => montaListaColunasEnt($this->data, 'msg_id', $dados_tela, $campos[1]),
            ];

            // Cache da listagem
            cache()->save('mensagem', $mensagem, 60000);
        echo json_encode($mensagem);
    }

    public function add($modal = false)
    {
        // Cria entity vazia
        $men = new EntCfgMensagem();
    
        // Dados Gerais
        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $men->campos['msg_id'],
            $men->campos['msg_titulo'],
            $men->campos['msg_tipo'],
            $men->campos['msg_cor'],
            $men->campos['msg_mensagem']
            ]];
        // Define método de gravação
        $this->data['destino']    = 'store';
    
        // Exibe view normal ou modal
        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }


    public function show($id){
        $this->edit($id, true);
    }


    public function edit($id, $show = false)
    {
        // Busca mensagem pelo ID
        $men = $this->mensagem->find($id);
    
        // Caso não encontre, lança exceção
        if (!$men) {
            return redirectWithError($this->data['controler'],41);
            // throw new \Exception('Impressora não encontrada');
        }
    
        // Define campos conforme modo edição/visualização
        $men->campos = $men->defCampos($show);

        // Dados Gerais
        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $men->campos['msg_id'],
            $men->campos['msg_titulo'],
            $men->campos['msg_tipo'],
            $men->campos['msg_cor'],
            $men->campos['msg_mensagem']
            ]];
        $this->data['destino']    = 'store';
        // Histórico de alterações
        $this->data['log']     = buscaLog('cfg_impressora', $id);
    
        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            // Exclui mensagem
            $this->mensagem->delete($id);

            $ret['erro'] = false;
            cache()->delete('cfg_mensagens_obj');
            session()->setFlashdata('msg', 'Mensagem Excluída com Sucesso');

        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            // Erro por relacionamento com outros cadastros
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Mensagem, Verifique!<br><br>';
            $ret['msg'] .= 'Está Mensagem possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

   public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = ['msg_ativo' => 'A'];
        } else {
            $dad_atin = ['msg_ativo' => 'I'];
        }
        $ret = [];
    
        try {
            $this->mensagem->update($id, $dad_atin);
    
            cache()->delete('cfg_mensagens_obj');
    
            $ret['erro'] = false;
            $ret['msg']  = 'Mensagem Alterada com Sucesso';
    
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
    
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar a Mensagem, Verifique!';
        }
        return $this->response->setJSON($ret);
    }


    public function getMensagemAjax(int $id)
    {
        $mensagens = $this->mensagem->getMensagensCache();
    
        if (!isset($mensagens[$id])) {
            return redirectWithError($this->data['controler'],41);
            // return $this->response->setJSON([
            //     'erro' => true,
            //     'msg'  => 'Mensagem não encontrada'
            // ]);
        }
        $men = $mensagens[$id];
    
        return $this->response->setJSON([
            'id'     => $men->msg_id,
            'titulo' => $men->msg_titulo,
            'texto'  => $men->msg_mensagem,
            'tipo'   => $men->msg_tipo,
            'cor'    => $men->msg_cor,
        ]);
    }


    public function store()
    {
        $ret     = [];
        $postado = $this->request->getPost();
        $men    = new EntCfgMensagem($postado); // Cria entity com dados enviados

        // Valida unicidade do título da mensagem
        $exists = $this->common->verificaUnico($this->mensagem, 'msg_titulo', $postado['msg_titulo'], 'msg_id', $postado['msg_id']);

        // Caso já exista, retorna erro
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 8;
        } else {
            $this->mensagem->transBegin(); // Inicia transação

            try {
                // Salva mensagem
                if (!$this->mensagem->save($men)) {
                    throw new \Exception(implode(' ', $this->mensagem->errors()));
                }
                // Commit da transação
                $this->mensagem->transCommit();
                cache()->delete('cfg_mensagens_obj');
                session()->setFlashdata('msg', 'Mensagem gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Mensagem gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler'])
                ];
            } catch (\Throwable $e) {
                // Rollback em caso de erro
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
