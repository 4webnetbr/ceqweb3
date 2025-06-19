<?php 

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigMensagemModel;

Class CfgMensagem extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $mensagem;

    public function __construct()
    {
		$this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
		$this->mensagem 		= new ConfigMensagemModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'msg_id,');
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
        // if (!$mensagem = cache('mensagem')) {
            $campos = montaColunasCampos($this->data, 'msg_id');
            $dados_tela = $this->mensagem->getMensagem();
            $this->data['exclusao'] = false; // quando não quer mostrar o botão de exclusão
            $mensagem = [
                'data' => montaListaColunas($this->data, 'msg_id', $dados_tela, $campos[1]),
            ];
            cache()->save('mensagem', $mensagem, 60000);
        // }
        echo json_encode($mensagem);
    }

    public function add($modal = false)
    {
        $fields = $this->mensagem->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['msg_id'];
        $campos[0][1] = $fields['msg_titulo'];
        $campos[0][2] = $fields['msg_tipo'];
        $campos[0][3] = $fields['msg_cor'];
        $campos[0][4] = $fields['msg_mensagem'];
        // $campos[0][5] = $fields['msg_ativo'];
       
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            // $this->data['destino']    = 'store/modal';
            echo view('vw_edicao_modal', $this->data);
        }
    }
    public function show($id){
        $this->edit($id, true);
    }
    public function edit($id, $show= false){
		$dados_mensagem = $this->mensagem->find($id);
        $fields = $this->mensagem->defCampos($dados_mensagem, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['msg_id'];
        $campos[0][1] = $fields['msg_titulo'];
        $campos[0][2] = $fields['msg_tipo'];
        $campos[0][3] = $fields['msg_cor'];
        $campos[0][4] = $fields['msg_mensagem'];
        // $campos[0][5] = $fields['msg_ativo'];

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_mensagem', $id);

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
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($this->mensagem->save($postado)) {
            $ret['erro'] = false;
        } else {
            $erros = $this->mensagem->errors();
            $ret['erro'] = true;
        }

        if ($ret['erro']) { 
            $ret['msg']  = 'Não foi possível gravar a Mensagem, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Mensagem gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }

}
