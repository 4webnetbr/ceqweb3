<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Config\ConfigUsuarioModel;

class Login extends BaseController
{
    private $login = '' ;
    public $usuario_config;
    public $data;
    public $usu_login;
    public $usu_senha;
    public $bt_entrar;
    public $bt_limpar;

    public function __construct()
    {
        $this->usuario_config = new ConfigUsuarioModel();
        $this->data['styles'] = 'login';
        $this->data['scripts'] = 'my_fields,my_mask';
    }

    public function defCampos()
    {
        $login =  new Campos();
        $login->objeto  = 'input';
        $login->tipo    = 'login';
        $login->nome    = 'usu_login';
        $login->id      = 'usu_login';
        $login->label   = 'Usuário';
        $login->place   = 'Usuário';
        $login->obrigatorio = true;
        $login->hint    = 'Informe o Usuário';
        $login->size    = 30;
        $login->tamanho  = 40;
        $login->tipo_form = 'vertical';
        $this->usu_login = $login->create();

        $senha =  new Campos();
        $senha->objeto  = 'input';
        $senha->tipo    = 'senha';
        $senha->nome    = 'usu_senha';
        $senha->id      = 'usu_senha';
        $senha->label   = 'Senha';
        $senha->place   = 'Senha';
        $senha->obrigatorio = true;
        $senha->hint    = 'Informe a Senha';
        $senha->size    = 8;
        $senha->tamanho   = 40;
        $senha->tipo_form = 'vertical';
        $this->usu_senha = $senha->create();

        $entrar =  new Campos();
        $entrar->objeto  = 'botao';
        $entrar->tipo    = 'submit';
        $entrar->nome    = 'bt_entrar';
        $entrar->id      = 'bt_entrar';
        $entrar->label   = '<i class="bi bi-door-open"></i> Entrar';
        $entrar->hint    = 'Acessar o Sistema';
        $entrar->classs  = 'btn-primary mx-1 my-2 px-3';
        $this->bt_entrar = $entrar->create();

        $limpar =  new Campos();
        $limpar->objeto  = 'botao';
        $limpar->tipo    = 'reset';
        $limpar->nome    = 'bt_limpar';
        $limpar->id      = 'bt_limpar';
        $limpar->label   = '<i class="bi bi-eraser"></i> Limpar';
        $limpar->hint    = 'Limpar os Dados';
        $limpar->classs  = 'btn-secondary mx-1 my-2 px-3';
        $this->bt_limpar = $limpar->create();
    }

    public function index()
    {
        if (session()->logged_in === true) {
            session()->destroy();
            // $sessionCookieName = config('App')->sessionCookieName; // Nome padrão é 'ci_session'
            // $sessionValue = $this->request->getCookie($sessionCookieName);
            // $sessionPath = WRITEPATH . 'session'; // Diretório onde as sessões são armazenadas
            // $sessionFile = $sessionPath . DIRECTORY_SEPARATOR . 'ci_session' . $sessionValue;
            // if(file_exists($sessionFile)){
            //     unlink($sessionFile);
            // }
        }
        $logo                   = base_url('assets/images/logo_header.jpg');

        $this->defCampos();

        $campos[0] = $this->usu_login;
        $campos[1] = $this->usu_senha;
        $campos[2] = $this->bt_entrar;
        $campos[3] = $this->bt_limpar;

        $this->data['logo']       = $logo;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'login/logon';
        // $session = session();
        return view('vw_login', $this->data);
    }

    /**
     * Validação de Login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logon()
    {
        // $session = service('session', $config);
        $session = session();
        // debug($sessionName, true);
        $agent = $this->request->getUserAgent();
        $mobile = $agent->isMobile();
        $login = strtolower(trim($this->request->getVar('usu_login')));
        $senha = trim($this->request->getVar('usu_senha'));
        // $data = array('lower(trim(usu_login))'=>$login,'trim(usu_senha)'=>md5($senha));
        $data = array('lower(trim(usu_login))' => $login);

        $log_config =  $this->usuario_config->usuLogonConfig($data);
        if (!$log_config) {
            $session->setFlashdata('msg', 'Usuário não Encontrado');
            return redirect()->to('/login');
        } else {
            $conf_senha = (md5($senha) == trim($log_config[0]['usu_senha']));
            if (!$conf_senha) {
                $session->setFlashdata('msg', 'Senha não corresponde ao Usuário!');
                return redirect()->to('/login');
            } else {
                $img_name       = 'usu_' . $log_config[0]['usu_id'] . '.jpg';
                $sem_avat       = base_url('assets/images/sem_avatar.png');
                $logo_def       = base_url('assets/images/logo_header.png');
                $icone          = base_url('assets/images/favicon.ico');
                $path_ser       = FCPATH . 'assets/uploads/usuario/';
                $img_path       = site_url('assets/uploads/usuario/');
                if (file_exists($path_ser . $img_name)) {
                    $avatar = $img_path . $img_name;
                } else {
                    $avatar = $sem_avat;
                }
                if ($log_config[0]['dash_usuario'] != '') {
                    $dash = $log_config[0]['dash_usuario'];
                } else {
                    $dash = $log_config[0]['dash_perfil'];
                }

                // GRAVAR SESSÃO
                $newdata = [
                    'usu_id'        => $log_config[0]['usu_id'],
                    'usu_nome'      => $log_config[0]['usu_nome'],
                    'usu_login'     => $log_config[0]['usu_login'],
                    'usu_perfil_id' => $log_config[0]['prf_id'],
                    'usu_perfil'    => $log_config[0]['prf_nome'],
                    'usu_dashboard' => $dash,
                    'usu_whats'     => isset($log_config[0]['usu_whats']) ? $log_config[0]['usu_whats'] : 'N',
                    'usu_avatar'    => $avatar,
                    'logo'          => $logo_def,
                    'icone'         => $icone,
                    'logged_in'     => true,
                    'ismobile'      => $mobile
                ];
                $session->set($newdata);
                if (isset($_COOKIE['paginausuario'])) {
                    $usuariocook = $_COOKIE['paginausuario'];
                    $valores = explode(",", $usuariocook);
                    if ($valores[0] == $log_config[0]['usu_id'] && $valores[1] != '') {
                        $dash = $valores[1];
                    }
                }
                return redirect()->to('/' . $dash);
            }
        }
    }
}
