<?php 
namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigModuloModel;

Class CfgModulo extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $modulo;

    public function __construct()
    {
		$this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
		$this->modulo 		= new ConfigModuloModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'mod_id,');
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
        $dados_modulo = $this->modulo->getModulo();
        $modulos = [
            'data' => montaListaColunas($this->data, 'mod_id', $dados_modulo, 'mod_nome'),
        ];

        echo json_encode($modulos);
    }

    public function add($modal = false)
    {
        $fields = $this->modulo->defCampos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['mod_id'];  
        $campos[0][1] = $fields['mod_nome'];
        $campos[0][2] = $fields['mod_icon'];
        $campos[0][3] = $fields['mod_ativo'];

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

    public function edit($id, $show = false){
		$dados_modulo = $this->modulo->find($id);
        $fields = $this->modulo->defCampos($dados_modulo, $show);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['mod_id']; 
        $campos[0][1] = $fields['mod_nome'];
        $campos[0][2] = $fields['mod_icon'];
        $campos[0][3] = $fields['mod_ativo'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_modulo', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->modulo->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Módulo, Verifique!<br><br>';
            $ret['msg'] .= 'Este Módulo possui relacionamentos em outros cadastros!'.$e->getMessage();
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'mod_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'mod_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->modulo->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo Alterado com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Módulo, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }


    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['mod_id'] == '') {
            if ($this->modulo->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->modulo->update($postado['mod_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar o Módulo, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Módulo gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }

}
