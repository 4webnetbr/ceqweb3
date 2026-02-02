<?php

namespace App\Controllers\Config;

use App\Models\CommonModel;
use App\Entities\Config\EntCfgLayout;
use App\Controllers\BaseController;
use App\Models\Config\ConfigEtiquetaModel;
use App\Models\Config\ConfigLayoutEtiqModel;
use App\Traits\ForeignKeyUsageChecker;

class CfgLayoutEtiq extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $layetiqueta;
    public $common;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->layetiqueta = new ConfigLayoutEtiqModel();
        $this->common      = new CommonModel();

        // Caso exista erro de permissão, bloqueia acesso
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
        $this->data['colunas'] = montaColunasLista($this->data, 'let_id');
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
        $campos = montaColunasCampos($this->data, 'let_id');
        $dados_layetiq = $this->layetiqueta->getListaLayouts();

        // Não permite a exclusão direta pela listagem
        $this->data['exclusao'] = false;

        // Estrutura padrão para DataTable
        $layetiq = [
            'data' => montaListaColunasEnt($this->data, 'let_id', $dados_layetiq, $campos[1]),
        ];

        // Cache da listagem
        cache()->save('layetiq', $layetiq, 60000);

        echo json_encode($layetiq);
    }


    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        // Cria entity vazia
        $lay = new EntCfgLayout();
    
        // Dados Gerais
        $this->data['secoes'] = ['Dados Gerais'];
    
        // Campos do layout
        $this->data['campos'] = [[
            $lay->campos['let_id'],
            $lay->campos['let_nome'],
            $lay->campos['let_altura'],
            $lay->campos['let_largura'],
            $lay->campos['let_colunas'],
            $lay->campos['let_linhas'],
            $lay->campos['let_marg_esquerda'],
            $lay->campos['let_marg_direita'],
            $lay->campos['let_distancia_h'],
            $lay->campos['let_marg_superior'],
            $lay->campos['let_marg_inferior'],
            $lay->campos['let_distancia_v'],
        ]];
        
        // Define método de gravação
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
        // Busca layout pelo ID
        $lay = $this->layetiqueta->find($id);
    
        // Caso não encontre, lança exceção
        if (!$lay) {
            throw new \Exception('Layout não encontrado');
        }
    
        // Define campos conforme modo edição/visualização
        $lay->campos = $lay->defCampos($lay->toArray(), $show);
    
        // Dados Gerais
        $this->data['secoes'] = ['Dados Gerais'];
    
        // Campos
        $this->data['campos'] = [[
            $lay->campos['let_id'],
            $lay->campos['let_nome'],
            $lay->campos['let_altura'],
            $lay->campos['let_largura'],
            $lay->campos['let_colunas'],
            $lay->campos['let_linhas'],
            $lay->campos['let_marg_esquerda'],
            $lay->campos['let_marg_direita'],
            $lay->campos['let_distancia_h'],
            $lay->campos['let_marg_superior'],
            $lay->campos['let_marg_inferior'],
            $lay->campos['let_distancia_v'],
        ]];
    
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('cfg_layout_etiqueta', $id); // Histórico de alterações
    
        echo view('vw_edicao', $this->data);
    }


    public function ativinativ($id, $tipo)
    {
        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'let_ativo' => 'A'
                ];
            } else {
                $dad_atin = [
                    'let_ativo' => 'I'
                ];
                $this->verificarUsoEmRelacionamentos('cfg_layout_etiqueta', 'let_id', (int) $id);
            }

            $this->layetiqueta->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Layout de Etiqueta Alterado com Sucesso');
            $ret['msg']  = 'Layout de Etiqueta Alterado com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            // $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14; // ou código personalizado, se preferir
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
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_layout_etiqueta', 'let_id', (int) $id);

            // Soft delete
            $this->layetiqueta->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Status excluído com sucesso!');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
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
    
        // Inicia transação
        $this->layetiqueta->transBegin();
    
        $exists = $this->common->verificaUnico(
            $this->layetiqueta, 'let_nome', $postado['let_nome'], 'let_id', $postado['let_id'] ?? null
        );
    
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 8;
            $erros = [8];
        }
    
        if (count($erros) == 0) {
            try {
                // Grava layout
                if (!$this->layetiqueta->save($postado)) {
                    throw new \Exception('Erro ao salvar os dados.');
                }
    
                // Commit da transação
                $this->layetiqueta->transCommit();
                cache()->clean();
    
                $ret['erro'] = false;
                $ret['msg']  = 'Layout gravado com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url']  = site_url($this->data['controler']);
    
            } catch (\Exception $e) {
                // Rollback em caso de erro
                $this->layetiqueta->transRollback();
    
                $ret['erro'] = true;
                $ret['msg']  = 'Não foi possível gravar o Layout, Verifique!<br><br>';
    
                // Concatena erros de validação, se existirem
                $erros = $this->layetiqueta->errors();
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
