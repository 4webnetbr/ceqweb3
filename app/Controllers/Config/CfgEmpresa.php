<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Models\Config\ConfigEmpresaModel;

class CfgEmpresa extends BaseController
{
    public $data      = [];
    public $permissao = '';
    public $common;
    public $empresa;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        // Recupera dados da tela salvos em flashdata
        $this->data      = session()->getFlashdata('dados_tela');
        // Define a permissão do usuário
        $this->permissao = $this->data['permissao'];
        // Instancia o model de empresa
        $this->empresa   = new ConfigEmpresaModel();

        // Caso exista mensagem de erro, exibe tela de acesso negado
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'emp_codfil,');
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

        // if (!$empresas = cache('empresas')) {
        //     $this->integra();
        
            $campos     = montaColunasCampos($this->data, 'emp_codfil');
            $dados_tela = $this->empresa->getEmpresa();
            $this->data['edicao'] = false;
            $this->data['exclusao'] = false;
            $empresas   = [
                'data'  => montaListaColunasEnt($this->data, 'emp_codfil', $dados_tela, $campos[1]),
            ];
            cache()->save('empresas', $empresas, 60000);
        // }
        echo json_encode($empresas);

    }

    public function integra()
    {
        $busca   = new BuscasSapiens();
        $r_emps  = $busca->buscaEmpresas();
        $empss   = [];
        // Percorre todas as empresas retornadas
        for ($e  = 0; $e < count($r_emps); $e++) {
            $emp = $r_emps[$e];
            $empss['emp_codemp'] = $emp->codEmp;
            $empss['emp_codfil'] = $emp->codFil;
            $empss['emp_nomfil'] = $emp->nomFil;
            $empss['emp_sigfil'] = $emp->sigFil;
            $empss['emp_numcgc'] = $emp->numCgc;
            $empss['emp_insest'] = $emp->insEst;
            $this->empresa->save($empss);
        }
    }
}
