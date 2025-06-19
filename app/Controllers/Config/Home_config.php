<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigPerfilItemModel;


class Home_config extends BaseController
{
	public $data = [];
    public $perfil_ite;

	public function __construct(){
		$this->data 			= session()->getFlashdata('dados_tela');
		// $this->perfil_ite 		= new ConfigPerfilItemModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

	public function index(){
		// $busca_permissoes       = $this->perfil_ite->getItemPerfilTela(session()->get('usu_perfil_id'), 'Pedidos');
		echo view('config/vw_home', $this->data);
	}
}
