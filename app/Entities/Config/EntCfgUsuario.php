<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Libraries\Campos;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;

class EntCfgUsuario extends Entity
{
    public $campos = [];

    protected $perfilModel;
    protected $telaModel;

    public function __construct($data = null, bool $show = false)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        parent::__construct($data);

        $this->perfilModel = new ConfigPerfilModel();
        $this->telaModel   = new ConfigTelaModel();

        $this->defCampos($data, false, $show);
    }

    public function defCampos($dados = null, bool $leitura = false)
    {
        // 👉 GARANTE OBJETO (como você pediu)
        if (is_array($dados)) {
            $dados = (object) $dados;
        }

        $id = new MyCampo('cfg_usuario', 'usu_id');
        $id->nome  = 'usu_id';
        $id->valor = $dados->usu_id ?? '';
        $this->usu_id = $id->crOculto();

        $nome = new MyCampo('cfg_usuario', 'usu_nome');
        $nome->objeto      = 'input';
        $nome->tipo        = 'text';
        $nome->obrigatorio = true;
        $nome->leitura     = false;
        $nome->valor       = $dados->usu_nome ?? '';
        $this->usu_nome    = $nome->crInput();

        $email = new MyCampo('cfg_usuario', 'usu_email');
        $email->obrigatorio = false;
        $email->leitura     = false;
        $email->valor       = $dados->usu_email ?? '';
        $this->usu_email    = $email->crInput();

        $login = new MyCampo('cfg_usuario', 'usu_login');
        $login->leitura     = false;
        $login->obrigatorio = true;
        $login->classep     = 'text-lowercase';
        $login->valor       = $dados->usu_login ?? '';
        $this->usu_login    = $login->crInput();

        $perfis = array_column(
            $this->perfilModel->getPerfil(),
            'prf_nome',
            'prf_id'
        );

        $perfil = new MyCampo('cfg_usuario', 'prf_id');
        $perfil->leitura     = $leitura;
        $perfil->obrigatorio = true;
        $perfil->opcoes      = $perfis;
        $perfil->selecionado = $dados->prf_id ?? '';
        $perfil->valor       = $dados->prf_id ?? '';
        if ($leitura) {
            $perfil->infotop = 'Para alterar o Perfil, solicite ao Gestor do Sistema';
        }
        $this->usu_perfil = $perfil->crSelect();

        $ttelas = $this->telaModel->getTelaId();
        $telas  = [];
        foreach ($ttelas as $tel) {

            if (is_array($tel)) {
                $tel = (object) $tel;
            }
            $telas[$tel->tel_id] = $tel->tel_nome;
        }

        $dash = new MyCampo('cfg_usuario', 'usu_dashboard');
        $dash->obrigatorio = false;
        $dash->opcoes      = $telas;
        $dash->valor       = $dados->usu_dashboard ?? '';
        $dash->selecionado = $dash->valor;
        $this->usu_dashboard = $dash->crSelect();

        $nova_senha = new MyCampo('cfg_usuario', 'usu_senha');
        $nova_senha->tipo        = 'password';
        $nova_senha->obrigatorio = false;
        $nova_senha->valor       = '';
        if ($leitura) {
            $nova_senha->infotop = 'Para manter a mesma senha, deixe-a em branco';
        }
        $this->usu_nova_senha = $nova_senha->crInput();

        $contra_senha = new MyCampo('cfg_usuario', 'usu_senha');
        $contra_senha->tipo        = 'password';
        $contra_senha->nome        = 'contra_senha';
        $contra_senha->id          = 'contra_senha';
        $contra_senha->label       = 'Confirme a Senha';
        $contra_senha->place       = 'Confirme a Senha';
        $contra_senha->obrigatorio = false;
        $contra_senha->valor       = '';
        $contra_senha->funcBlur = "compara_senha('contra_senha','usu_senha')";
        $this->usu_contra_senha    = $contra_senha->crInput();

        $avatar = new Campos();
        $avatar->objeto       = 'imagem';
        $avatar->nome         = 'usu_avatar';
        $avatar->id           = 'usu_avatar';
        $avatar->label        = 'Avatar';
        $avatar->place        = 'Avatar';
        $avatar->obrigatorio  = false;
        $avatar->hint         = 'Informe o Avatar';
        $avatar->leitura      = false;
        $avatar->size         = 200;
        $avatar->tamanho      = 200;
        $avatar->accept      = '.png, .jpg, .jpeg';
        $avatar->pasta        = 'usuario';
        $avatar->img_name     = '';

        if (!empty($dados->usu_id)) {
            $img_name = 'usu_' . $dados->usu_id . '.jpg';
            $sem_avat = base_url('assets/images/sem_avatar.png');
            $path_ser = FCPATH . 'assets/uploads/usuario/';
            $img_path = site_url('assets/uploads/usuario/');

            if (file_exists($path_ser . $img_name)) {
                $avatar->img_name = $img_path . $img_name . '?nocache=' . time();
            } else {
                $avatar->img_name = $sem_avat;
            }
        } else {
            $avatar->img_name = base_url('assets/images/sem_avatar.png');
        }

        $avatar->funcao_chan = "readURL(this, '#img_$avatar->id', $avatar->size, $avatar->tamanho)";
        $avatar->valor       = $avatar->img_name . '?noc=' . time();
        $this->usu_avatar    = $avatar->create();
    }
}