<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;

class TipoTransacao extends BaseController {	
    public $data = [];
    public $permissao = '';
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
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
        // TODO implementar
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // TODO implementar
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        // TODO implementar
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
        // TODO implementar
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
        // TODO implementar
    }


    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        // TODO implementar
    }
}
