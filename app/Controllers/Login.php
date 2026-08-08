<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigUsuarioModel;

class Login extends BaseController
{
    private $login = '';
    public $usuario_config;
    public $data;
    public $usu_login;
    public $usu_senha;
    public $bt_entrar;
    public $bt_limpar;

    public function __construct()
    {
        $this->usuario_config  = new ConfigUsuarioModel();
        $this->data['styles']  = 'login';
        $this->data['scripts'] = 'my_mask';
    }

    public function defCampos()
    {
        $login              = new MyCampo('cfg_usuario', 'usu_login');
        $login->tipo        = 'login';
        $login->valor       = '';
        $login->obrigatorio = true;
        $login->size        = 21;
        // $login->tamanho     = 21;
        $this->usu_login    = $login->crInput();
        // debug($this->usu_login, true);

        $senha              = new MyCampo('cfg_usuario', 'usu_senha');
        $senha->tipo        = 'password';
        $senha->valor       = '';
        $senha->obrigatorio = true;
        $senha->size        = 15;
        $senha->maxLength   = 15;
        $this->usu_senha    = $senha->crInput();

        $bt_ent          = new MyCampo();
        $bt_ent->tipo    = 'submit';
        $bt_ent->id      = $bt_ent->nome      = 'bt_entrar';
        $bt_ent->classep = 'btn btn-primary btn-sm border-0 mx-3 my-2 px-4 py-2';
        $bt_ent->i_cone  = "<i class='fa-solid fa-door-open'></i> Entrar";
        $bt_ent->place   = "Acessar o Sistema";
        $this->bt_entrar = $bt_ent->crBotao();

        $bt_res          = new MyCampo();
        $bt_res->tipo    = 'reset';
        $bt_res->id      = $bt_res->nome      = 'bt_reset';
        $bt_res->classep = 'btn btn-secondary btn-sm border-0 my-2 px-4 py-2';
        $bt_res->i_cone  = "<i class='fa-solid fa-eraser'></i> Limpar";
        $bt_res->place   = "Limpar as Credenciais";
        $this->bt_limpar = $bt_res->crBotao();
    }

    public function index()
    {
        if (session()->logged_in === true) {
            session()->destroy();
        }

        $logo = base_url('assets/images/logo_header.jpg');

        $this->defCampos();

        $campos[0] = $this->usu_login;
        $campos[1] = $this->usu_senha;
        $campos[2] = $this->bt_entrar;
        $campos[3] = $this->bt_limpar;

        $this->data['logo']    = $logo;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'login/logon';
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
        $agent  = $this->request->getUserAgent();
        $mobile = $agent->isMobile();
        $login  = strtolower(trim($this->request->getVar('usu_login')));
        $senha  = trim($this->request->getVar('usu_senha'));
        // $data = array('lower(trim(usu_login))'=>$login,'trim(usu_senha)'=>md5($senha));
        $data = ['lower(trim(usu_login))' => $login];

        $log_config = $this->usuario_config->usuLogonConfig($data);
        if (! $log_config) {
            $session->setFlashdata('msg', 'Usuário não Encontrado');
            return redirect()->to('/login');
        } else {
            $conf_senha = (md5($senha) == trim($log_config[0]->usu_senha));
            if (! $conf_senha) {
                $session->setFlashdata('msg', 'Senha não corresponde ao Usuário!');
                return redirect()->to('/login');
            } else {
                $img_name = 'usu_' . $log_config[0]->usu_id . '.jpg';
                $sem_avat = base_url('assets/images/sem_avatar.png');
                $logo_def = base_url('assets/images/logo_header.png');
                $icone    = base_url('assets/images/icone.png');
                $path_ser = FCPATH . 'assets/uploads/usuario/';
                $img_path = site_url('assets/uploads/usuario/');
                if (file_exists($path_ser . $img_name)) {
                    $avatar = $img_path . $img_name;
                } else {
                    $avatar = $sem_avat;
                }
                if ($log_config[0]->dash_usuario != '') {
                    $dash = $log_config[0]->dash_usuario;
                } else {
                    $dash = $log_config[0]->dash_perfil;
                }

                // GRAVAR SESSÃO
                $newdata = [
                    'usu_id'        => $log_config[0]->usu_id,
                    'usu_nome'      => $log_config[0]->usu_nome,
                    'usu_login'     => $log_config[0]->usu_login,
                    'usu_perfil_id' => $log_config[0]->prf_id,
                    'usu_perfil'    => $log_config[0]->prf_nome,
                    'usu_dashboard' => $dash,
                    'usu_whats'     => isset($log_config[0]->usu_whats) ? $log_config[0]->usu_whats : 'N',
                    'usu_avatar'    => $avatar,
                    'logo'          => $logo_def,
                    'icone'         => $icone,
                    'logged_in'     => true,
                    'ismobile'      => $mobile,
                ];
                $session->set($newdata);

                $usuarioId = $log_config[0]->usu_id ?? null;

                if ($usuarioId) {
                    $cookieNome = 'pguser_' . $usuarioId;

                    $paginasalva = $this->request->getCookie($cookieNome) ?? '';
                    // debug($paginasalva, true);
                    if ($paginasalva != '') {
                        // $usuariocook = $this->request->getCookie($cookieNome);
                        $dash = $paginasalva;
                    }
                }

                return redirect()->to('/' . $dash);
            }
        }
    }
}
