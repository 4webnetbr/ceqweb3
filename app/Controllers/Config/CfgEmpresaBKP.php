<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;

class CfgEmpresa extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $empresa;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->empresa     = new ConfigEmpresaModel();

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
        
            $campos = montaColunasCampos($this->data, 'emp_codfil');
            $dados_tela = $this->empresa->getEmpresa();
            $empresas = [
                'data' => montaListaColunas($this->data, 'emp_codfil', $dados_tela, $campos[1]),
            ];
            cache()->save('empresas', $empresas, 60000);
        // }
        echo json_encode($empresas);

    }

    public function show($id){
		$dados_empresa = $this->empresa->find($id);
        $fields = $this->empresa->defCampos($dados_empresa, true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['emp_nomfil'];
        $campos[0][1] = $fields['emp_sigfil'];
        $campos[0][2] = $fields['emp_numcgc'];
        $campos[0][3] = $fields['emp_insest'];
        $campos[0][4] = $fields['emp_codemp'];
        $campos[0][5] = $fields['emp_codfil']; 
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_empresa', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
     * integra
     */
    public function integra()
    {
        $busca = new BuscasSapiens();
        $r_emps = $busca->buscaEmpresas();
        $empss = [];
        for ($e = 0; $e < count($r_emps); $e++) {
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
