<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigEtiquetaModel;
use App\Models\Config\ConfigLayoutEtiqModel;

use function PHPUnit\Framework\isNan;

class CfgLayoutEtiq extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $layetiqueta;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->layetiqueta = new ConfigLayoutEtiqModel();

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
        // if (!$layetiq = cache('layetiq')) {
        $campos = montaColunasCampos($this->data, 'let_id');
        $dados_layetiq = $this->layetiqueta->getListaLayouts();
        $this->data['exclusao'] = false;
        $layetiq = [
            'data' => montaListaColunas($this->data, 'let_id', $dados_layetiq, $campos[1]),
        ];
        cache()->save('layetiq', $layetiq, 60000);
        // }

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
        $fields = $this->layetiqueta->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['let_id'];
        $campos[0][count($campos[0])] = $fields['let_nome'];
        $campos[0][count($campos[0])] = $fields['let_altura'];
        $campos[0][count($campos[0])] = $fields['let_largura'];
        $campos[0][count($campos[0])] = $fields['let_colunas'];
        $campos[0][count($campos[0])] = $fields['let_linhas'];
        $campos[0][count($campos[0])] = $fields['let_marg_esquerda'];
        $campos[0][count($campos[0])] = $fields['let_marg_direita'];
        $campos[0][count($campos[0])] = $fields['let_distancia_h'];
        $campos[0][count($campos[0])] = $fields['let_marg_superior'];
        $campos[0][count($campos[0])] = $fields['let_marg_inferior'];
        $campos[0][count($campos[0])] = $fields['let_distancia_v'];

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
        $dados_etiqueta = $this->layetiqueta->find($id);
        $fields = $this->layetiqueta->defCampos($dados_etiqueta);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['let_id'];
        $campos[0][1] = $fields['let_nome'];
        $campos[0][2] = $fields['let_altura'];
        $campos[0][3] = $fields['let_largura'];
        $campos[0][4] = $fields['let_colunas'];
        $campos[0][5] = $fields['let_linhas'];
        $campos[0][6] = $fields['let_marg_esquerda'];
        $campos[0][7] = $fields['let_marg_direita'];
        $campos[0][8] = $fields['let_distancia_h'];
        $campos[0][9] = $fields['let_marg_superior'];
        $campos[0][10] = $fields['let_marg_inferior'];
        $campos[0][11] = $fields['let_distancia_v'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
    }
    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'let_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'let_ativo' => 'I'
            ];
        }
        $ret = [];
        $etiquetas = new ConfigEtiquetaModel();
        $existeeti = $etiquetas->getEtiquetaLayout($id);
        if (count($existeeti)) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        } else {
            try {
                $this->layetiqueta->update($id, $dad_atin);
                $ret['erro'] = false;
                session()->setFlashdata('msg', 'Layout de Etiqueta Gravado com Sucesso');
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                $ret['erro'] = true;
                $ret['msg']  = 'Não foi possível Alterar o Layout de Etiqueta, Verifique!<br><br>';
            }
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
            $this->layetiqueta->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Layout Excluído com Sucesso');
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir esse Layout Verifique!<br><br>';
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

        // Inicia a transação
        $this->layetiqueta->transBegin();

        try {
            if (!$this->layetiqueta->save($postado)) {
                throw new \Exception('Erro ao salvar os dados.');
            }

            // Commit se tudo der certo
            $this->layetiqueta->transCommit();
            cache()->clean();

            $ret['erro'] = false;
            $ret['msg']  = 'Layout gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        } catch (\Exception $e) {
            // Se houver erro, faz rollback
            $this->layetiqueta->transRollback();

            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Layout, Verifique!<br><br>';

            $erros = $this->layetiqueta->errors();
            if (!empty($erros)) {
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                    if (is_numeric($erro)) {
                        $ret['msg'] = $erro;
                    }
                }
                // foreach ($erros as $erro) {
                //     $ret['msg'] .= (ctype_digit($erro)) ? $erro : $erro . '<br>';
                // }
            } else {
                $ret['msg'] .= $e->getMessage(); // Mensagem genérica do erro
            }
        }

        echo json_encode($ret);
    }
}
