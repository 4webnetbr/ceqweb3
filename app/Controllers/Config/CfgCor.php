<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigCorModel;

class CfgCor extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $cores;


    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->cores = new ConfigCorModel();


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
        $this->data['colunas'] = montaColunasLista($this->data, 'cor_id');
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
        if (!$cores = cache('cores')) {
            $campos = montaColunasCampos($this->data, 'cor_id');
            $dados_cores = $this->cores->getListaCores();
            for ($cr = 0; $cr < count($dados_cores); $cr++) {
                $dados_cores[$cr]['div_rgb'] = fmtEtiquetaCor($dados_cores[$cr]['cor_valorrgb']);
            }
            $this->data['exclusao'] = false;
            $cores = [
                'data' => montaListaColunas($this->data, 'cor_id', $dados_cores, $campos[1]),
            ];
            cache()->save('cores', $cores, 60000);
        }

        echo json_encode($cores);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'cor_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'cor_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->cores->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Cor Alterada com Sucesso');
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar a Cor, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $fields = $this->cores->defCampos();
        // debug($fields);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['cor_id'];
        $campos[0][1] = $fields['cor_nome'];
        $campos[0][2] = $fields['cor_valorrgb'];
        $campos[0][3] = $fields['cor_ativo'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        echo view('vw_edicao', $this->data);
    }
    /**
     * Mostrar Registro
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
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
        $dados_cores = $this->cores->find($id);
        $fields = $this->cores->defCampos($dados_cores);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['cor_id'];
        $campos[0][1] = $fields['cor_nome'];
        $campos[0][2] = $fields['cor_valorrgb'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        $this->data['log'] = buscaLog('cfg_cor', $id);
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
            $this->cores->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Cor Excluída com Sucesso');
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Cor, Verifique!<br><br>';
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
        $ret['erro'] = false;
        $postado = $this->request->getPost();
        $erros = [];
        $this->cores->transBegin();

        try {
            // Gravação da etiqueta
            if (!$this->cores->save($postado)) {
                throw new \Exception(implode(' ', $this->cores->errors()));
            }
        } catch (\Exception $e) {
            // Em caso de erro, reverte a transação
            $this->cores->transRollback();
            $ret['erro'] = true;
            $ret['msg'] = $e->getMessage();
        }
        if ($ret['erro']) {
            if (!is_numeric($ret['msg'])) {
                if (count($erros) > 0 && is_numeric($erros[0])) {
                    $ret['msg'] = $erros[0];
                } else {
                    $ret['msg']  = 'Não foi possível gravar Cor, Verifique!<br><br>';
                    foreach ($erros as $erro) {
                        $ret['msg'] .= $erro . '<br>';
                    }
                }
            }
        } else {
            cache()->clean();
            $this->cores->transCommit();
            $ret['msg']  = 'Cor gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }

        echo json_encode($ret);
    }
}
