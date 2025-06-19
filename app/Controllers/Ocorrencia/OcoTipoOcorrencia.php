<?php
namespace App\Controllers\Ocorrencia;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoTipoOcorrencia extends BaseController {
    public $data = [];
    public $permissao = '';
    public $tipoocorrencia;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->tipoocorrencia = new OcorreTipoOcorrenciaModel();
        
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
        $this->data['colunas'] = montaColunasLista($this->data, 'tpo_id');
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
        // if (!$tpocor = cache('tpocor')) {
            $campos = montaColunasCampos($this->data, 'tpo_id');
            $dados_tpocor = $this->tipoocorrencia->getTipoOcorrencia();
            $tpocor = [
                'data' => montaListaColunas($this->data, 'tpo_id', $dados_tpocor, $campos[1]),
            ];
            cache()->save('tpocor', $tpocor, 60000);
        // }

        echo json_encode($tpocor);
    }
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $fields = $this->tipoocorrencia->defCampos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['tpo_id'];  
        $campos[0][count($campos[0])] = $fields['tpo_nome'];
        $campos[0][count($campos[0])] = $fields['cla_id'];

        $secao[1] = 'Telas Aplicaveis'; 
        $fields = $this->tipoocorrencia->defCamposTelasAplicaveis();
        $campos[1][0] = $fields['mod_id'];  
        $campos[1][count($campos[1])] = $fields['tel_id'];
        
        $secao[2] = 'Ações'; 
        $fields = $this->tipoocorrencia->defCamposAcao();
        $campos[2][0] = $fields['tpa_id'];  
        $campos[2][1] = $fields['tmo_id'];  
        $campos[2][2] = $fields['mod_id'];  
        $campos[2][3] = $fields['tel_id'];  

        $secao[3] = 'Campos para Mostrar'; 
        $fields = $this->tipoocorrencia->defCamposParaMostrar();
        $campos[3][0] = $fields['tel_id'];  
        $campos[3][1] = $fields['toc_id'];  
        $campos[3][2] = $fields['toc_rotulo'];  

        $secao[4] = 'Permissões';
        $fields = $this->tipoocorrencia->defPermissoes();
        $campos[4][0] = $fields['prf_id'];

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
        $dados_TipoOcorrencia = $this->tipoocorrencia->defCampos($id);
        $fields = $this->tipoocorrencia->defCampos($dados_TipoOcorrencia);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['tpo_id'];  
        $campos[0][count($campos[0])] = $fields['tpo_nome'];
        $campos[0][count($campos[0])] = $fields['cla_id'];

        $secao[1] = 'Telas Aplicaveis'; 
        $dados_TelasAplicaveis = $this->tipoocorrencia->defCamposTelasAplicaveis($id);
        $fields = $this->tipoocorrencia->defCamposTelasAplicaveis($dados_TelasAplicaveis);
        $campos[1][0] = $fields['mod_id'];  
        $campos[1][count($campos[1])] = $fields['tel_id'];
        
        $secao[2] = 'Ações';
        $dados_Açoes = $this->tipoocorrencia->defCamposAcao($id);
        $fields = $this->tipoocorrencia->defCamposAcao($dados_Açoes);
        $campos[2][0] = $fields['tpa_id'];  
        $campos[2][1] = $fields['tmo_id'];  
        $campos[2][2] = $fields['mod_id'];  
        $campos[2][3] = $fields['tel_id'];  

        $secao[3] = 'Campos para Mostrar'; 
        $fields = $this->tipoocorrencia->defCamposParaMostrar();
        $campos[3][0] = $fields['tel_id'];  
        $campos[3][1] = $fields['toc_id'];  
        $campos[3][2] = $fields['toc_rotulo'];  

        $secao[4] = 'Permissões';
        $fields = $this->tipoocorrencia->defPermissoes();
        $campos[4][0] = $fields['prf_id'];

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

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
            $this->mensagem->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Ocorrência Selecionada, Verifique!<br><br>';
        }
        echo json_encode($ret);
        
    }
    /**
    * Gravação
    * store
    
    * @return void
    */
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['tpo_id'] == '') {
            if ($this->tipoocorrencia->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->tipoocorrencia->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->tipoocorrencia->update($postado['tpo_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->tipoocorrencia->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar o Tipo da Ocorrência, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Tipo de Ocorrência gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }
        
}
