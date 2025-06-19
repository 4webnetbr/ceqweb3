<?php
namespace App\Controllers\Ocorrencia;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTipoAcaoModel;

class OcoTipoAcao extends BaseController {
    public $data = [];
    public $permissao = '';
    public $tipoacao;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->tipoacao = new OcorreTipoAcaoModel();
        
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
        $this->data['colunas'] = montaColunasLista($this->data, 'tpa_id');
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
        //if (!$tipoac = cache('tipoac')) {
            $campos = montaColunasCampos($this->data, 'tpa_id');
            $dados_tipoac = $this->tipoacao->getTipoAcao();
            $tipoac = [
                'data' => montaListaColunas($this->data, 'tpa_id', $dados_tipoac, $campos[1]),
            ];
            cache()->save('tipoac', $tipoac, 60000);
        //}
    
        echo json_encode($tipoac);
    }
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $fields = $this->tipoacao->defCampos();
        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['tpa_id'];  
        $campos[0][1] = $fields['tpa_nome'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
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
        $dados_tipoacao = $this->tipoacao->find($id);
        $fields = $this->tipoacao->defCampos($dados_tipoacao);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['tpa_id'];  
        $campos[0][1] = $fields['tpa_nome'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'tpa_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'tpa_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->tipoacao->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência Alterada com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar a Ocorrência, Verifique!<br><br>';
        }
        echo json_encode($ret);
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
            $this->tipoacao->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir Ocorrência, Verifique!<br><br>';
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
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['tpa_id'] == '') {
            if ($this->tipoacao->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros 
                = $this->tipoacao->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->tipoacao->update($postado['tpa_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->tipoacao->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar Ocorrência, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            cache()->clean();
            $ret['msg']  = 'Ocorrência gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }
}
