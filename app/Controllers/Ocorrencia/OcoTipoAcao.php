<?php
namespace App\Controllers\Ocorrencia;
use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Entities\Ocorrencia\EntOcoTipoAcao;

class OcoTipoAcao extends BaseController {
    public $data = [];
    public $permissao = '';
    public $tipoacao;
    public $common;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->tipoacao  = new OcorreTipoAcaoModel();
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
        $this->data['colunas']   = montaColunasLista($this->data, 'tpa_id');
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
            $campos = montaColunasCampos($this->data, 'tpa_id');
            $dados_tipoac = $this->tipoacao->getTipoAcao();
            $tipoac = [
                'data' => montaListaColunasEnt($this->data, 'tpa_id', $dados_tipoac, $campos[1]),
            ];
            cache()->save('tipoac', $tipoac, 60000);
    
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
        $acao = new EntOcoTipoAcao();
    
        $this->data['secoes'] = ['Dados Gerais'];
    
        $this->data['campos'] = [[
            $acao->campos['tpa_id'],
            $acao->campos['tpa_nome'],
            $acao->campos['tpa_tipo'],
        ]];
        
        $this->data['destino'] = 'store';
        
        echo view('vw_edicao', $this->data);
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
        $acao = $this->tipoacao->find($id);
    
        if (!$acao) {
            throw new \Exception('Ação não encontrada');
        }
    
        $acao->campos = $acao->defCampos($acao->toArray(), $show);

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $acao->campos['tpa_id'],
            $acao->campos['tpa_nome'],
            $acao->campos['tpa_tipo'],
        ]];

		$this->data['destino']    = 'store';
        $this->data['log']     = buscaLog('oco_tipo_acao', $id);

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
            session()->setFlashdata('msg', 'Tipo de Ação Alterada com Sucesso');
            $ret['msg']  = 'Tipo de Ação Alterada com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Tipo de Ação, Verifique!<br><br>';
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
            session()->setFlashdata('msg', 'Tipo de Ação Excluído com Sucesso');
            $ret['msg']  = 'Tipo de Ação Excluído com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Tipo de Ação, Verifique!<br><br>';
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

        $this->tipoacao->transBegin();
            $exists = $this->common->verificaUnico($this->tipoacao, 'tpa_nome', $postado['tpa_nome'], 'tpa_id', $postado['tpa_id']);
            if ($exists > 0) {
                $ret['erro'] = true;
                $ret['msg'] = 8;
                $erros = [8];
            }
        if(count($erros) == 0){
            try {
                if (!$this->tipoacao->save($postado)) {
                    throw new \Exception('Erro ao salvar os dados.');
                }

                // Commit se tudo der certo
                $this->tipoacao->transCommit();
                cache()->clean();

                $ret['erro'] = false;
                $ret['msg']  = 'Tipo de Ação gravada com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url']  = site_url($this->data['controler']);
            } catch (\Exception $e) {
                // Se houver erro, faz rollback
                $this->tipoacao->transRollback();

                $ret['erro'] = true;
                $ret['msg']  = 'Não foi possível gravar Tipo de Ação, Verifique!<br><br>';

                $erros = $this->tipoacao->errors();
                if (!empty($erros)) {
                    foreach ($erros as $erro) {
                        $ret['msg'] .= $erro . '<br>';
                        if (is_numeric($erro)) {
                            $ret['msg'] = $erro;
                        }
                    }
                } else {
                    $ret['msg'] .= $e->getMessage();
                }
            }
        }
        echo json_encode($ret);
    }
}
