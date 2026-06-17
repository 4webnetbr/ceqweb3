<?php namespace App\Controllers;

use App\Libraries\Campos;
use App\Models\OperacaoModel;
use App\Models\PedidoModel;
use App\Models\RotaModel;

class Home extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $novopedido;

	public function __construct(){
		// $this->data  = session()->getFlashdata('dados_tela');
        // $this->permissao = $this->data['permissao'];
        // if($this->data['erromsg'] != ''){
        //     $this->__erro();
        // }
	}

    function __erro(){
        // echo view('vw_semacesso', $this->data);
    }

	public function index()
	{
        $dash = base_url(session()->get('usu_dashboard'));
        echo $dash;
        return redirect()->to($dash);
        // return view('vw_home',$this->data);
    }

}
