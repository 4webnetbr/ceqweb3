<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\OperacaoModel;

class Operacao extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $operacao;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
        $this->operacao     = new OperacaoModel();
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
        $this->data['colunas'] = ['Id','Nome', 'Endereço','Reinicia Pedido','Ação'];
        $this->data['url_lista'] = base_url($this->data['controler'].'/lista');

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
        $result = [];
        $dados_opera = $this->operacao->getOperacao();
        for ($p = 0; $p < sizeof($dados_opera); $p++) {
            $opera = $dados_opera[$p];
            $edit = '';
            $exclui = '';
            if (strpbrk($this->permissao, 'E')) {
                $edit = anchor(
                    $this->data['controler'] . '/edit/' . $opera['ope_id'],
                    '<i class="far fa-edit"></i>',
                    [
                        'class' => 'btn btn-outline-warning btn-sm mx-1',
                        'data-mdb-toggle' => 'tooltip',
                        'data-mdb-placement' => 'top',
                        'title' => 'Alterar este Registro', 
                    ]
                );
            }
            if (strpbrk($this->permissao, 'X')) {
                $url_del =
                    $this->data['controler'] . '/delete/' . $opera['ope_id'];
                $exclui =
                    "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"" .
                    $url_del .
                    "\",\"" .
                    $opera['ope_nome'] .
                    "\")'><i class='far fa-trash-alt'></i></button>";
            }

            $dados_opera[$p]['acao'] = $edit . ' ' . $exclui;
            $opera = $dados_opera[$p];
            $result[] = [
                $opera['ope_id'],
                $opera['ope_nome'],
                $opera['ope_endereco'],
                $opera['ope_descri_reinicia'],
                $opera['acao'],
            ];
        }
        $operas = [
            'data' => $result,
        ];

        echo json_encode($operas);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->ope_id;
        $campos[0][1] = $this->ope_nome;
        $campos[0][2] = $this->cep;
        $campos[0][3] = $this->ope_endereco;
        $campos[0][4] = $this->hos_reinicia_pedido;
        $campos[0][5] = $this->ope_hora_inicio;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
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
    public function edit($id)
    {
        $dados_linha = $this->linha->getLinha($id)[0];
        $this->def_campos($dados_linha);

        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->ope_id;
        $campos[0][1] = $this->ope_nome;
        $campos[0][2] = $this->cep;
        $campos[0][3] = $this->ope_endereco;
        $campos[0][4] = $this->hos_reinicia_pedido;
        $campos[0][5] = $this->ope_hora_inicio;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

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
        $this->linha->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler']));
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $pos = 0)
    {
        $id = new Campos();
        $id->objeto = 'oculto';
        $id->nome = 'ope_id';
        $id->id = 'ope_id';
        $id->valor = isset($dados['ope_id']) ? $dados['ope_id'] : '';
        $this->ope_id = $id->create();

		$nome = new Campos();
        $nome->objeto = 'input';
        $nome->tipo = 'text';
        $nome->nome = 'ope_nome';
        $nome->id = 'ope_nome';
        $nome->label = 'Nome';
        $nome->place = 'Nome';
        $nome->obrigatorio = true;
        $nome->hint = 'Informe o Nome';
        $nome->size    = 50;
        $nome->tamanho = 55;
        $nome->valor = isset($dados['ope_nome'])? $dados['ope_nome']: '';
        $this->ope_nome = $nome->create();

		$cep = new Campos();
        $cep->objeto = 'input';
        $cep->tipo = 'cep';
        $cep->nome = 'cep';
        $cep->id = 'cep';
        $cep->label = 'CEP';
        $cep->place = 'CEP';
        $cep->obrigatorio = true;
        $cep->hint = 'Informe o CEP';
        $cep->size    = 10;
        $cep->tamanho = 12;
        $cep->funcao_blur = 'pesquisacep(this, this.value)';
        $cep->valor = '';
        $this->cep = $cep->create();

		$ende = new Campos();
        $ende->objeto = 'input';
        $ende->tipo = 'text';
        $ende->nome = 'ope_endereco';
        $ende->id = 'ope_endereco';
        $ende->label = 'Endereço';
        $ende->place = 'Endereço';
        $ende->obrigatorio = false;
        $ende->hint = 'Informe o Endereço';
        $ende->size    = 100;
        $ende->tamanho = 50;
        $nome->valor = isset($dados['ope_endereco'])? $dados['ope_endereco']: '';
        $this->ope_endereco = $ende->create();


        $simnao[1] = 'Sim';
        $simnao[2] = 'Não';

        $reini =  new Campos();
        $reini->objeto         = 'radio';
        $reini->nome           = "hos_reinicia_pedido";
        $reini->id             = "hos_reinicia_pedido";
        $reini->label          = 'Reinicia Pedidos?';
        $reini->obrigatorio    = true;
        $reini->opcoes         = $simnao;
        $reini->valor          = (isset($dados['hos_reinicia_pedido']))?$dados['hos_reinicia_pedido']:'1'; 
        $reini->selecionado    = (isset($dados['hos_reinicia_pedido']))?$dados['hos_reinicia_pedido']:'1'; 
        $reini->size           = 20;
        $reini->tamanho        = 1;
        $this->hos_reinicia_pedido         = $reini->create();

        $hrce =  new Campos();
		$hrce->objeto  	= 'input';
        $hrce->tipo    	= 'time';
        $hrce->nome    	= 'ope_hora_inicio';
        $hrce->id      	= 'ope_hora_inicio';
        $hrce->label   	= 'Hora de Início';
        $hrce->place   	= 'Hora de Início';
        $hrce->obrigatorio = false;
        $hrce->hint    	= 'Informe a Hora de Início';
        $hrce->size   	= 8;
		$hrce->tamanho    = 14;
		$hrce->valor	    = (isset($dados['ope_hora_inicio']))?$dados['ope_hora_inicio']:'';
        $this->ope_hora_inicio   = $hrce->create();

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
        $dados = $this->request->getPost();
        // debug($dados);
        $dados_lin = [
            'ope_id'           => $dados['ope_id'],
            'ope_nome'         => $dados['ope_nome'],
            'ope_endereco'     => $dados['ope_endereco'],
            'ope_reinicia_pedido'=> $dados['ope_reinicia_pedido'],
            'ope_hora_inicio'    => $dados['ope_hora_inicio'],
        ];
        if ($this->operacao->save($dados_lin)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Linha gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Linha, Verifique!';
        }
        echo json_encode($ret);
    }
}
