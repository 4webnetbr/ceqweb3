<?php 

namespace App\Controllers\Config;

use App\Models\CommonModel;
use App\Controllers\BaseController;
use App\Models\Config\ConfigImpressoraModel;
use App\Entities\Config\EntCfgImpressora;
use App\Traits\ForeignKeyUsageChecker;


Class CfgImpressora extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $impressora;
    public $common;

    public function __construct()
    {
		$this->data        = session()->getFlashdata('dados_tela');
        $this->permissao   = $this->data['permissao'];

		$this->impressora  = new ConfigImpressoraModel();
        $this->common      = new CommonModel();

        // Caso exista erro de permissão, bloqueia acesso
        if ($this->data['erromsg'] != '') 
        {
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
        // Monta campos da listagem
        $campos     = montaColunasCampos($this->data, 'imp_id');
        $dados_tela = $this->impressora->getImpressora();

        // Estrutura padrão para DataTable
        $Impressora = ['data' => montaListaColunasEnt($this->data, 'imp_id', $dados_tela, $campos[1]),];

        // Cache da listagem
        cache()->save('Impressora', $Impressora, 60000);
        echo json_encode($Impressora);
    }


    public function add($modal = false)
    {
        // Cria entity vazia
        $impr = new EntCfgImpressora();
    
        // Seção: Dados Gerais
        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $impr->campos['imp_id'],
            $impr->campos['imp_nome'],
            $impr->campos['imp_ip'],
            $impr->campos['imp_porta']
        ]];
        // Define método de gravação
        $this->data['destino']    = 'store';
    
        // Exibe view normal ou modal
        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }


    public function show($id)
    {
        $this->edit($id, true);
    }


    public function edit($id, $show = false)
    {
        // Busca impressora pelo ID
        $impr = $this->impressora->find($id);
    
        // Caso não encontre, lança exceção
        if (!$impr) {
            throw new \Exception('Impressora não encontrada');
        }
    
        // Define campos conforme modo edição/visualização
        $impr->campos = $impr->defCampos($show);

        // Seção: Dados Gerais
        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $impr->campos['imp_id'],
            $impr->campos['imp_nome'],
            $impr->campos['imp_ip'],
            $impr->campos['imp_porta']
        ]];
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('cfg_impressora', $id); // Busca histórico de alterações
    
        echo view('vw_edicao', $this->data);
    }


    public function delete($id)
    {
        $ret = [];

        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_impressora', 'imp_id', (int) $id);

            // Soft delete
            $this->impressora->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Impressora Excluída com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }


    public function ativinativ($id, $ip)
    {
        // Define status conforme parâmetro 
        if ($ip == 1) {
            $dad_atin = [
                'imp_ativo' => 'A' // ATIVO
            ];
        } else {
            $dad_atin = [
                'imp_ativo' => 'I' // INATIVO
            ];
        }
        $ret = [];
        try {
            // Atualiza status
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

        // Cria entity com dados enviados
        $impr = new EntCfgImpressora($postado); 
        
        // Verifica unicidade do nome da impressora
        $exists = $this->common->verificaUnico($this->impressora, 'imp_nome', $postado['imp_nome'], 'imp_id', $postado['imp_id']);

        // Caso já exista, retorna erro
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 8;
        } else {
            $this->impressora->transBegin(); // Inicia transação

            try {
                // Salva impressora
                if (!$this->impressora->save($impr)) {
                    throw new \Exception(implode(' ', $this->impressora->errors()));
                }
                // Commit da transação
                $this->impressora->transCommit();
                cache()->clean();
                session()->setFlashdata('msg', 'Impressora gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Impressora gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler'])
                ];
            } catch (\Throwable $e) {
                // Rollback em caso de erro
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
