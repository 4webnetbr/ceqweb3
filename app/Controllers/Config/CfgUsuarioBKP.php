<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;

class CfgUsuario extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $usuario;
    public $perfil;
    public $tela;

    public function __construct()
    {
        $this->data = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->usuario      = new ConfigUsuarioModel();
        $this->perfil       = new ConfigPerfilModel();
        $this->tela       = new ConfigTelaModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

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
        $this->data['colunas'] = montaColunasLista($this->data, 'usu_id,');
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
        if (!$usuarios = cache('usuarios')) {
            $dados_usuario = $this->usuario->getUsuarioId();
            $usuarios = [
                'data' => montaListaColunas($this->data, 'usu_id', $dados_usuario, 'usu_nome'),
            ];
            cache()->save('usuarios', $usuarios, 30);
        }

        echo json_encode($usuarios);
    }

    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;
        $campos[0][6] = $this->usu_dashboard;

        $secao[1] = 'Avatar';
        $campos[1][0] = $this->usu_avatar;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        if (empty($usuario['usu_id'])) {
        $this->data['script'] = '<script>
            document.addEventListener("DOMContentLoaded", function () {
            const login = document.getElementById("usu_login");
            if (login) {
                login.value = "";
                setTimeout(() => login.value = "", 700);
            }
            });
        </script>';
        }

        echo view('vw_edicao', $this->data);
    }

    public function show($id){
        $this->edit($id, true);
    }

    public function edit($id, $show = false)
    {
        // busca a usuario
        $dados_usuario = $this->usuario->getUsuarioId($id, $show)[0];
        // debug($dados_usuario);
        $this->def_campos($dados_usuario);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_perfil;
        $campos[0][6] = $this->usu_dashboard;

        $secao[1] = 'Avatar';
        $campos[1][0] = $this->usu_avatar;

        $this->data['desc_edicao'] = $dados_usuario['usu_nome'];
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_usuario', $id);
        echo view('vw_edicao', $this->data);
    }

    public function edit_senha($id)
    {
        // busca a usuario
        $anterior['anterior'] = $_SERVER["HTTP_REFERER"];
        session()->set($anterior);

        // $id = session()->get('usu_id');
        $dados_usuario = $this->usuario->getUsuarioId($id)[0];
        $this->def_campos($dados_usuario, true);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->usu_id;
        $campos[0][1] = $this->usu_nome;
        $campos[0][2] = $this->usu_email;
        $campos[0][3] = $this->usu_login;
        $campos[0][4] = $this->usu_nova_senha;
        $campos[0][5] = $this->usu_contra_senha;
        $campos[0][6] = "<span id='msg_senha' class='text-danger bg-warning'></span>";
        $campos[0][7] = $this->usu_perfil;


        $secao[1] = 'Avatar';
        $campos[1][0] = $this->usu_avatar;

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
        $this->data['desc_metodo'] = 'Alteração de Senha de';

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $this->usuario->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso!');
        return redirect()->to(site_url($this->data['controler'])); 
    }

    public function def_campos($dados = false, $leitura = false, $show= false){
		// $id				= new Campos();
		$id				= new MyCampo('cfg_usuario', 'usu_id');
		// $id->objeto		= 'oculto';
		$id->nome		= 'usu_id';
		$id->valor		= (isset($dados['usu_id']))?$dados['usu_id']:'';
		$this->usu_id	= $id->crOculto();

		$nome =  new MyCampo('cfg_usuario', 'usu_nome');
		$nome->objeto  	= 'input';
        $nome->tipo    	= 'text';
        // $nome->nome    	= 'usu_nome';
        // $nome->id      	= 'usu_nome';
        // $nome->label   	= 'Nome';
        // $nome->place   	= 'Nome';
        $nome->obrigatorio = true;
        // $nome->hint    	= 'Informe o Nome do Usuário';
        $nome->leitura  = false;
		$nome->valor	= (isset($dados['usu_nome']))?$dados['usu_nome']:'';
        // $this->usu_nome = $nome->create();
        $this->usu_nome = $nome->crInput();


		$email =  new MyCampo('cfg_usuario', 'usu_email');
		// $email->objeto  	= 'input';
        // $email->tipo    	= 'email';
        // $email->nome    	= 'usu_email';
        // $email->id      	= 'usu_email';
        // $email->label   	= 'E-mail';
        // $email->place   	= 'E-mail';
        $email->obrigatorio = false;
        $email->leitura     = false;
        // $email->hint    	= 'Informe o E-mail';
        // $email->size   		= 100;
		// $email->tamanho   	= 75;
		$email->valor	    = (isset($dados['usu_email']))?$dados['usu_email']:'';
        $this->usu_email = $email->crInput();

		$login =  new MyCampo('cfg_usuario', 'usu_login');
		// $login->objeto  	    = 'input';
        // $login->tipo    	    = 'text';
        // $login->nome    	    = 'usu_login';
        // $login->id      	    = 'usu_login';
        // $login->label   	    = 'Login';
        // $login->place   	    = 'Login';
        $login->leitura         = false;
        $login->obrigatorio     = true;
        // $login->hint    	    = 'Informe o Login';
        $login->classs          = 'text-lowercase';
		$login->valor	        = (isset($dados['usu_login']))?$dados['usu_login']:'';
        $this->usu_login        = $login->crInput();

        $perfis             = array_column($this->perfil->getPerfil(),'prf_nome','prf_id');
		$perfil             =    new MyCampo('cfg_usuario', 'prf_id');
		// $perfil->objeto  	= 'select';
        // $perfil->nome    	= 'prf_id';
        // $perfil->id      	= 'prf_id';
        // $perfil->label   	= 'Perfil de Acesso';
        $perfil->leitura    = $leitura;
        $perfil->obrigatorio = true;
        // $perfil->hint    	= 'Escolha o Perfil de Acesso';
        $perfil->opcoes     = $perfis;
        $perfil->selecionado = (isset($dados['prf_id']))?$dados['prf_id']:''; 
		$perfil->valor	    = (isset($dados['prf_id']))?$dados['prf_id']:'';
        if($leitura){
            $perfil->infobot   = 'Para alterar o Perfil, solicite ao Gestor do Sistema';
        }
        $this->usu_perfil   = $perfil->crSelect();

        $ttelas = $this->tela->getTelaId();
        foreach ($ttelas as $tel) {
            $telas[$tel['tel_id']] = $tel['tel_nome'];
        }

        // $telas = array_column($ttelas, 'tel_nome', 'tel_id');
        // array_unshift($telas, '');
        // debug($telas, true);
        $dash                  =    new MyCampo('cfg_usuario', 'usu_dashboard');
		// $dash->objeto  	       = 'select';
        // $dash->nome    	       = 'usu_dashboard';
        // $dash->id      	       = 'usu_dashboard';
        // $dash->label   	       = 'Dashboard';
        $dash->obrigatorio     = false;
        // $dash->hint    	       = 'Escolha o Dashboard';
        $dash->opcoes          = $telas;
		$dash->valor	       = (isset($dados['usu_dashboard'])) ? $dados['usu_dashboard'] : '';
        $dash->selecionado     = $dash->valor;
        $this->usu_dashboard   = $dash->crSelect();

        $nova_senha =   new MyCampo('cfg_usuario', 'usu_senha');
		// $nova_senha->objeto  	= 'input';
        $nova_senha->tipo    	= 'password';
        // $nova_senha->nome    	= 'usu_senha';
        // $nova_senha->id      	= 'usu_senha';
        // $nova_senha->label   	= 'Senha';
        // $nova_senha->place   	= 'Senha';
        $nova_senha->obrigatorio = false;
        // $nova_senha->hint    	= 'Informe a Senha';
		$nova_senha->valor	    = '';
        if($leitura){
            $nova_senha->infotop   = 'Para manter a mesma senha, deixe-a em branco';
        }
        $this->usu_nova_senha = $nova_senha->crInput();

        $contra_senha =   new MyCampo('cfg_usuario', 'usu_senha');
		// $contra_senha->objeto  	= 'input';
        $contra_senha->tipo    	= 'password';
        $contra_senha->nome    	= 'contra_senha';
        $contra_senha->id      	= 'contra_senha';
        $contra_senha->label   	= 'Confirme a Senha';
        $contra_senha->place   	= 'Confirme a Senha';
        $contra_senha->obrigatorio = false;
        // $contra_senha->size   		= 20;
        // $contra_senha->max_size   	= 12;
		// $contra_senha->tamanho   	= 25;
		$contra_senha->valor	    = '';
        // $contra_senha->funcao_chan	    = "compara_senha('contra_senha','usu_senha')";
        $contra_senha->funcao_blur	    = "compara_senha('contra_senha','usu_senha')";
        $this->usu_contra_senha = $contra_senha->crInput();

        // $tipo_us[1] = 'Config';
        // $tipo_us[2] = 'Sistema';
        // $tipo_us[3] = 'Ambos';

        // $tipo_u =   new MyCampo('cfg_usuario', 'usu_tipo');
        // // $tipo_u->objeto         = 'radio';
        // // $tipo_u->nome           = "usu_tipo";
        // // $tipo_u->id             = "usu_tipo";
        // // $tipo_u->label          = 'Tipo de Acesso';
        // $tipo_u->obrigatorio    = false;
        // $tipo_u->valor          = (isset($dados['usu_tipo']))?$dados['usu_tipo']:'0'; 
        // $tipo_u->selecionado    = (isset($dados['usu_tipo']))?$dados['usu_tipo']:'0'; 
        // $tipo_u->size           = 20;
        // $tipo_u->tamanho        = 1;
        // $this->usu_tipo_u       = $tipo_u->crRadio();

        $avatar				    = new Campos();
		$avatar->objeto		    = 'imagem';
		$avatar->nome		    = 'usu_avatar';
		$avatar->id		        = 'usu_avatar';
        $avatar->label   	    = 'Avatar';
        $avatar->place   	    = 'Avatar';
        $avatar->obrigatorio  = false;
        $avatar->hint    	    = 'Informe o Avatar';
        $avatar->leitura   	    = false;
        $avatar->size   	    = 200;
		$avatar->tamanho        = 200;
        $avatar->accept         = '.png, .jpg, .jpeg';
        $avatar->pasta          = 'usuario';
        $avatar->img_name       = '';
        $avat                   = '';
        if (isset($dados['usu_id'])) {
            $img_name       = 'usu_'.$dados['usu_id'].'.jpg';
            $sem_avat       = base_url('assets/images/sem_avatar.png');
            $path_ser       = FCPATH.'assets/uploads/usuario/';
            $img_path       = site_url('assets/uploads/usuario/');
            if(file_exists($path_ser.$img_name)){
                $avatar->img_name = $img_path.$img_name.'?nocache='.time();
            } else {
                $avatar->img_name = $sem_avat;
            }
        } else {
            $avatar->img_name     = base_url('assets/images/sem_avatar.png');
        }
        $avat                   = $avatar->img_name.'?noc='.time();
        $avatar->funcao_chan    = "readURL(this, '#img_$avatar->id', $avatar->size, $avatar->tamanho)";
        $avatar->valor		    = $avat;
		$this->usu_avatar	    = $avatar->create();
    }

	public function store() {
        $dados = $this->request->getPost();
        if (isset($dados['usu_senha'])) {
            if ($dados['usu_senha'] == '') {
                unset($dados['usu_senha']);
            } else {
                $dados['usu_senha'] = md5($dados['usu_senha']);
            }
        }
        if ($this->usuario->save($dados)) {
            if ($dados['usu_id'] != '') {
                $usu_id = $dados['usu_id'];
            } else {
                $usu_id = $this->usuario->getInsertID();
            }
            $avatar = $this->request->getFile('usu_avatar');
            if ($avatar->getFilename() != '') {
                $path_avat = 'assets/uploads/usuario/usu_' . $usu_id . '.jpg';
                @unlink($path_avat);

                $avatar->move('assets/uploads/usuario', 'usu_' . $usu_id . '.jpg');
            }
            $ret['erro'] = false;
            $ret['msg']  = 'Usuario gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = session()->get('anterior');
            if ($ret['url'] == '') {
                $ret['url']  = site_url($this->data['controler']);
            }
        } else {
            $error = $this->usuario->getErrors();
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Usuario, Verifique!' . $error;
            session()->setFlashdata('msg', $ret['msg']);
        }
        echo json_encode($ret);
	}

}