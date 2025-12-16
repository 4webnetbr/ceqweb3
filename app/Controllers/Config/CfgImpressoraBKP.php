<?php 

namespace App\Controllers\Config;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigImpressoraModel;

Class CfgImpressora extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $impressora;
    public $common;

    public function __construct()
    {
		$this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
		$this->impressora 		= new ConfigImpressoraModel();
        $this->common       = new CommonModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'imp_id,');
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
        // if (!$Impressora = cache('Impressora')) {
            $campos = montaColunasCampos($this->data, 'imp_id');
            $dados_tela = $this->impressora->getImpressora();
            // $this->data['exclusao'] = false; // quando não quer mostrar o botão de exclusão
            $Impressora = [
                'data' => montaListaColunas($this->data, 'imp_id', $dados_tela, $campos[1]),
            ];
            cache()->save('Impressora', $Impressora, 60000);
        // }
        echo json_encode($Impressora);
    }

    public function add($modal = false)
    {
        $fields = $this->impressora->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['imp_id'];
        $campos[0][1] = $fields['imp_nome'];
        $campos[0][2] = $fields['imp_ip'];
        $campos[0][3] = $fields['imp_porta'];
       
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
		$dados_Impressora = $this->impressora->find($id);
        $fields = $this->impressora->defCampos($dados_Impressora, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['imp_id'];
        $campos[0][1] = $fields['imp_nome'];
        $campos[0][2] = $fields['imp_ip'];
        $campos[0][3] = $fields['imp_porta'];
        // $campos[0][5] = $fields['imp_ativo'];

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_Impressora', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->impressora->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Impressora Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Impressora, Verifique!<br><br>';
            $ret['msg'] .= 'Está Impressora possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $ip)
    {
        if ($ip == 1) {
            $dad_atin = [
                'imp_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'imp_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->impressora->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Impressora Alterada com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Impressora, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        $exists = $this->common->verificaUnico($this->impressora, 'imp_nome', $postado['imp_nome'], 'imp_id', $postado['imp_id']);
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
            $erros = [8];
        } else {
            if ($this->impressora->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->impressora->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) { 
            $ret['msg']  = 'Não foi possível gravar a Impressora, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
                if (is_numeric($erro)) {
                    $ret['msg'] = $erro;
                }
            }
        } else {
            $ret['msg']  = 'Impressora gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }

}
