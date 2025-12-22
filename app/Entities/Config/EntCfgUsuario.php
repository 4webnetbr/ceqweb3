<?php

// namespace App\Entities\Config;

// use CodeIgniter\Entity\Entity;
// use App\Libraries\MyCampo;
// use App\Libraries\Campos;
// use App\Models\Config\ConfigPerfilModel;
// use App\Models\Config\ConfigTelaModel;

// class EntCfgUsuario extends Entity
// {
//     public $campos = [];

//     protected $perfilModel;
//     protected $telaModel;

//      public function __construct(?array $data = null, bool $show = false)
//     {
//         parent::__construct($data);
//         $this->perfilModel = new ConfigPerfilModel();
//         $this->telaModel   = new ConfigTelaModel();
//         $this->defCampos($data, false, $show);
//     }


//     public function defCampos($dados = false, $leitura = false, $show= false){
// 		// $id				= new Campos();
// 		$id				= new MyCampo('cfg_usuario', 'usu_id');
// 		// $id->objeto		= 'oculto';
// 		$id->nome		= 'usu_id';
// 		$id->valor		= (isset($dados['usu_id']))?$dados['usu_id']:'';
// 		$this->usu_id	= $id->crOculto();

// 		$nome =  new MyCampo('cfg_usuario', 'usu_nome');
// 		$nome->objeto  	= 'input';
//     $nome->tipo    	= 'text';
//     // $nome->nome    	= 'usu_nome';
//     // $nome->id      	= 'usu_nome';
//     // $nome->label   	= 'Nome';
//     // $nome->place   	= 'Nome';
//     $nome->obrigatorio = true;
//     // $nome->hint    	= 'Informe o Nome do Usuário';
//     $nome->leitura  = false;
// 	  $nome->valor	= (isset($dados['usu_nome']))?$dados['usu_nome']:'';
//     // $this->usu_nome = $nome->create();
//     $this->usu_nome = $nome->crInput();
// 	  $email =  new MyCampo('cfg_usuario', 'usu_email');
// 	  // $email->objeto  	= 'input';
//     // $email->tipo    	= 'email';
//     // $email->nome    	= 'usu_email';
//     // $email->id      	= 'usu_email';
//     // $email->label   	= 'E-mail';
//     // $email->place   	= 'E-mail';
//     $email->obrigatorio = false;
//     $email->leitura     = false;
//     // $email->hint    	= 'Informe o E-mail';
//     // $email->size   		= 100;
// 		// $email->tamanho   	= 75;
// 		$email->valor	    = (isset($dados['usu_email']))?$dados['usu_email']:'';
//     $this->usu_email = $email->crInput();
// 	  $login =  new MyCampo('cfg_usuario', 'usu_login');
// 	  // $login->objeto  	    = 'input';
//     // $login->tipo    	    = 'text';
//     // $login->nome    	    = 'usu_login';
//     // $login->id      	    = 'usu_login';
//     // $login->label   	    = 'Login';
//     // $login->place   	    = 'Login';
//     $login->leitura         = false;
//     $login->obrigatorio     = true;
//     // $login->hint    	    = 'Informe o Login';
//     $login->classs          = 'text-lowercase';
// 	  $login->valor	        = (isset($dados['usu_login']))?$dados['usu_login']:'';
//     $this->usu_login        = $login->crInput();
//     $perfis             = array_column($this->perfilModel->getPerfil(),'prf_nome','prf_id');
// 	  $perfil             =    new MyCampo('cfg_usuario', 'prf_id');
// 	  // $perfil->objeto  	= 'select';
//     // $perfil->nome    	= 'prf_id';
//     // $perfil->id      	= 'prf_id';
//     // $perfil->label   	= 'Perfil de Acesso';
//     $perfil->leitura    = $leitura;
//     $perfil->obrigatorio = true;
//     // $perfil->hint    	= 'Escolha o Perfil de Acesso';
//     $perfil->opcoes     = $perfis;
//     $perfil->selecionado = (isset($dados['prf_id']))?$dados['prf_id']:''; 
// 	  $perfil->valor	    = (isset($dados['prf_id']))?$dados['prf_id']:'';
//     if($leitura){
//         $perfil->infobot   = 'Para alterar o Perfil, solicite ao Gestor do Sistema';
//     }
//     $this->usu_perfil   = $perfil->crSelect();
//     $ttelas = $this->telaModel->getTelaId();
//     foreach ($ttelas as $tel) {
//         $telas[$tel['tel_id']] = $tel['tel_nome'];
    
//     // $telas = array_column($ttelas, 'tel_nome', 'tel_id');
//     // array_unshift($telas, '');
//     // debug($telas, true);
//     $dash                  =    new MyCampo('cfg_usuario', 'usu_dashboard');
// 	  // $dash->objeto  	       = 'select';
//     // $dash->nome    	       = 'usu_dashboard';
//     // $dash->id      	       = 'usu_dashboard';
//     // $dash->label   	       = 'Dashboard';
//     $dash->obrigatorio     = false;
//     // $dash->hint    	       = 'Escolha o Dashboard';
//     $dash->opcoes          = $telas;
// 	  $dash->valor	       = (isset($dados['usu_dashboard'])) ? $dados['usu_dashboard'] : '';
//     $dash->selecionado     = $dash->valor;
//     $this->usu_dashboard   = $dash->crSelect();
//     $nova_senha = new MyCampo('cfg_usuario', 'usu_senha');
//     $nova_senha->objeto = 'input';
//     $nova_senha->tipo   = 'password';
//     // $nova_senha->nome    	= 'usu_senha';
//     // $nova_senha->id      	= 'usu_senha';
//     // $nova_senha->label   	= 'Senha';
//     // $nova_senha->place   	= 'Senha';
//     $nova_senha->obrigatorio = false;
//     // $nova_senha->hint    	= 'Informe a Senha';
// 	  $nova_senha->valor	    = '';
//     if($leitura){
//         $nova_senha->infotop   = 'Para manter a mesma senha, deixe-a em branco';
//     }
//     $this->usu_nova_senha = $nova_senha->crInput();
//     $contra_senha =   new MyCampo('cfg_usuario', 'usu_senha');
// 		// $contra_senha->objeto  	= 'input';
//     $contra_senha->tipo    	= 'password';
//     $contra_senha->nome    	= 'contra_senha';
//     $contra_senha->id      	= 'contra_senha';
//     $contra_senha->label   	= 'Confirme a Senha';
//     $contra_senha->place   	= 'Confirme a Senha';
//     $contra_senha->obrigatorio = false;
//     // $contra_senha->size   		= 20;
//     // $contra_senha->max_size   	= 12;
// 		// $contra_senha->tamanho   	= 25;
// 		$contra_senha->valor	    = '';
//     // $contra_senha->funcao_chan	    = "compara_senha('contra_senha','usu_senha')";
//     $contra_senha->funcao_blur	    = "compara_senha('contra_senha','usu_senha')";
//     $this->usu_contra_senha = $contra_senha->crInput();
//     // $tipo_us[1] = 'Config';
//     // $tipo_us[2] = 'Sistema';
//     // $tipo_us[3] = 'Ambos'
//     // $tipo_u =   new MyCampo('cfg_usuario', 'usu_tipo');
//     // // $tipo_u->objeto         = 'radio';
//     // // $tipo_u->nome           = "usu_tipo";
//     // // $tipo_u->id             = "usu_tipo";
//     // // $tipo_u->label          = 'Tipo de Acesso';
//     // $tipo_u->obrigatorio    = false;
//     // $tipo_u->valor          = (isset($dados['usu_tipo']))?$dados['usu_tipo']:'0'; 
//     // $tipo_u->selecionado    = (isset($dados['usu_tipo']))?$dados['usu_tipo']:'0'; 
//     // $tipo_u->size           = 20;
//     // $tipo_u->tamanho        = 1;
//     // $this->usu_tipo_u       = $tipo_u->crRadio()
//     $avatar				        = new Campos();
// 	  $avatar->objeto		    = 'imagem';
// 	  $avatar->nome		      = 'usu_avatar';
// 	  $avatar->id		        = 'usu_avatar';
//     $avatar->label   	    = 'Avatar';
//     $avatar->place   	    = 'Avatar';
//     $avatar->obrigatorio  = false;
//     $avatar->hint    	    = 'Informe o Avatar';
//     $avatar->leitura   	  = false;
//     $avatar->size   	    = 200;
// 		$avatar->tamanho      = 200;
//     $avatar->accept       = '.png, .jpg, .jpeg';
//     $avatar->pasta        = 'usuario';
//     $avatar->img_name     = '';
//     $avat                 = '';
//     if (isset($dados['usu_id'])) {
//         $img_name       = 'usu_'.$dados['usu_id'].'.jpg';
//         $sem_avat       = base_url('assets/images/sem_avatar.png');
//         $path_ser       = FCPATH.'assets/uploads/usuario/';
//         $img_path       = site_url('assets/uploads/usuario/');
//         if(file_exists($path_ser.$img_name)){
//             $avatar->img_name = $img_path.$img_name.'?nocache='.time();
//         } else {
//             $avatar->img_name = $sem_avat;
//         }
//     } else {
//         $avatar->img_name     = base_url('assets/images/sem_avatar.png');
//     }
//     $avat                   = $avatar->img_name.'?noc='.time();
//     $avatar->funcao_chan    = "readURL(this, '#img_$avatar->id', $avatar->size, $avatar->tamanho)";
//     $avatar->valor		    = $avat;
// 		$this->usu_avatar	    = $avatar->create();
//     }
//   }
}    