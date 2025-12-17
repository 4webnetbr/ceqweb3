<?php 

namespace App\Controllers\Config;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigImpressoraModel;
use App\Entities\Config\EntCfgImpressora;

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
                'data' => montaListaColunasEnt($this->data, 'imp_id', $dados_tela, $campos[1]),
            ];
            cache()->save('Impressora', $Impressora, 60000);
        // }
        echo json_encode($Impressora);
    }




    public function add($modal = false)
    {
        $impr = new EntCfgImpressora();
    
        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $impr->campos['imp_id'],
            $impr->campos['imp_nome'],
            $impr->campos['imp_ip'],
            $impr->campos['imp_porta']
        ]];
        $this->data['destino']    = 'store';
    
        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }


    public function show($id){
        $this->edit($id, true);
    }


    public function edit($id, $show = false)
    {
        $impr = $this->impressora->find($id);
    
        if (!$impr) {
            throw new \Exception('Impressora não encontrada');
        }
    
        $impr->campos = $impr->defCampos($show);

        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $impr->campos['imp_id'],
            $impr->campos['imp_nome'],
            $impr->campos['imp_ip'],
            $impr->campos['imp_porta']
        ]];
        $this->data['destino']    = 'store';

        $this->data['log']     = buscaLog('cfg_impressora', $id);
    
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

        $impr = new EntCfgImpressora($postado); // cria a Entity
        
        $exists = $this->common->verificaUnico($this->impressora, 'imp_nome', $postado['imp_nome'], 'imp_id', $postado['imp_id']);

        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
        } else {
            $this->impressora->transBegin();

            try {
                if (!$this->impressora->save($impr)) {
                    throw new \Exception(implode(' ', $this->impressora->errors()));
                }
                $this->impressora->transCommit();
                cache()->clean();
                session()->setFlashdata('msg', 'Impressora gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Impressora gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler'])
                ];
            } catch (\Throwable $e) {
                $this->impressora->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Não foi possível gravar a Impressora, Verifique!<br><br>'
                ];
            }
        }
        echo json_encode($ret);
    }

}
