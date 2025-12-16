<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigCorModel;
use App\Entities\Config\EntCfgCor;

class CfgCor extends BaseController
{
    public $permissao = '';
    public $cores;

    /** @var array<string, mixed> */
    public array $data = [];


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
        // if (!$cores = cache('cores')) {
            $campos = montaColunasCampos($this->data, 'cor_id');
            $dados_cores = $this->cores->getListaCores();
            foreach ($dados_cores as $cor) {
                $cor->div_rgb = fmtEtiquetaCor($cor->cor_valorrgb);
            }
            $this->data['exclusao'] = false;
            $cores = [
                'data' => montaListaColunasEnt($this->data, 'cor_id', $dados_cores, $campos[1]),
            ];
            cache()->save('cores', $cores, 60000);
        // }

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
            $ret['msg']  = 'Cor Alterada com Sucesso';
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
        $cor = new EntCfgCor();
        // debug($fields);

        $this->data['secoes']     = ['Dados Gerais'];
        $this->data['campos']     = [[
            $cor->campos['cor_id'],
            $cor->campos['cor_nome'],
            $cor->campos['cor_valorrgb']
        ]];
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
        $cor = $this->cores->getCores($id); // ✅ Entity

        if (!$cor) {
            $this->data['erromsg'] = '<h2>Cor não encontrada</h2>';
            echo view('vw_semacesso', $this->data);        
        } else {
            $cor->campos = $cor->defCampos($show);

            $this->data['secoes']  = ['Dados Gerais'];
            $this->data['campos']  = [[
                $cor->campos['cor_id'],
                $cor->campos['cor_nome'],
                $cor->campos['cor_valorrgb']
            ]];

            $this->data['destino']    = 'store';

            $this->data['log'] = buscaLog('cfg_cor', $id);
            echo view('vw_edicao', $this->data);
        }
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

        $cor = new EntCfgCor($postado); // cria a Entity

        $this->cores->transBegin();

        try {
            if (!$this->cores->save($cor)) {
                throw new \Exception(implode(' ', $this->cores->errors()));
            }
            $this->cores->transCommit();
            cache()->clean();
            session()->setFlashdata('msg', 'Cor gravada com Sucesso!!!');

            $ret = [
                'erro' => false,
                'msg'  => 'Cor gravada com Sucesso!!!',
                'url'  => site_url($this->data['controler'])
            ];
        } catch (\Throwable $e) {
            $this->cores->transRollback();
            $ret = [
                'erro' => true,
                'msg'  => $e->getMessage() ?: 'Erro ao salvar cor.'
            ];
        }

        echo json_encode($ret);


        // $erros = [];
        // $this->cores->transBegin();

        // try {
        //     // Gravação da etiqueta
        //     if (!$this->cores->save($postado)) {
        //         throw new \Exception(implode(' ', $this->cores->errors()));
        //     }
        // } catch (\Exception $e) {
        //     // Em caso de erro, reverte a transação
        //     $this->cores->transRollback();
        //     $ret['erro'] = true;
        //     $ret['msg'] = $e->getMessage();
        // }
        // if ($ret['erro']) {
        //     if (!is_numeric($ret['msg'])) {
        //         if (count($erros) > 0 && is_numeric($erros[0])) {
        //             $ret['msg'] = $erros[0];
        //         } else {
        //             $ret['msg']  = 'Não foi possível gravar Cor, Verifique!<br><br>';
        //             foreach ($erros as $erro) {
        //                 $ret['msg'] .= $erro . '<br>';
        //             }
        //         }
        //     }
        // } else {
        //     cache()->clean();
        //     $this->cores->transCommit();
        //     $ret['msg']  = 'Cor gravada com Sucesso!!!';
        //     session()->setFlashdata('msg', $ret['msg']);
        //     $ret['url']  = site_url($this->data['controler']);
        // }

        // echo json_encode($ret);
    }
}
