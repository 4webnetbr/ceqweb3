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
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

	public function index()
	{
        return view('vw_home',$this->data);
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
    }

    public function dashboard(){
        $status = '1,2,3';
        $dados = $this->request->getPost();
        if(isset($dados['status'])){
            $status = $dados['status'];
        }
        $stat = explode(',',$status);
        // debug($stat);
        $r['retprod'] = '';
        $r['retdisp'] = '';
        $r['retcria'] = '';
        $r['retentr'] = '';
        $ret = '';
        if($stat[0] < 4 ){
            $emproducao = $this->pedido->getPedidoPcp(false, $stat);
            $peds = [];
            $ped_id = '';
            if(count($emproducao) > 0){
                for($ep=0;$ep<count($emproducao);$ep++){
                    if($ped_id != $emproducao[$ep]['ped_id']){
                        if($ped_id != ''){
                            // debug($peds,false);
                            $ret .= monta_card_pedido($peds); 
                            if(intval($peds['ped_status']) == 3){
                                $r['retdisp'] .= $ret;
                            } else {
                                $r['retprod'] .= $ret;
                            }
                            $ret = '';
                            $peds = [];
                        }
                        $peds['ped_id'] = $emproducao[$ep]['ped_id'];
                        $peds['ped_numero'] = $emproducao[$ep]['ped_numero'];
                        $peds['pcp_inicio'] = $emproducao[$ep]['pcp_inicio'];
                        $peds['pcp_final'] = $emproducao[$ep]['pcp_final'];
                        $peds['ped_data'] = $emproducao[$ep]['ped_data'];
                        $peds['ped_hora'] = $emproducao[$ep]['ped_hora'];
                        $peds['ped_valor_taxa'] = $emproducao[$ep]['ped_valor_taxa'];
                        $peds['ped_status'] = $emproducao[$ep]['ped_status'];
                        $peds['ped_endereco'] = $emproducao[$ep]['ped_endereco'];
                        $peds['ped_desc_status'] = $emproducao[$ep]['ped_desc_status'];
                        $peds['ope_nome'] = $emproducao[$ep]['ope_nome'];
                        $peds['tax_nome'] = $emproducao[$ep]['tax_nome'];
                        $ped_id = $emproducao[$ep]['ped_id'];
                        $ct_pp = 0;
                    }
                    $peds['produtos'][$ct_pp]['pcp_id'] = $emproducao[$ep]['pcp_id'];
                    $peds['produtos'][$ct_pp]['pcp_final'] = $emproducao[$ep]['pcp_final'];
                    $peds['produtos'][$ct_pp]['tpp_id'] = $emproducao[$ep]['tpp_id'];
                    $peds['produtos'][$ct_pp]['tpp_nome'] = $emproducao[$ep]['tpp_nome'];
                    $ct_pp++;
                }
                $ret .= monta_card_pedido($peds);
                if(intval($peds['ped_status']) == 3){
                    $r['retdisp'] .= $ret;
                } else {
                    $r['retprod'] .= $ret;
                }
                $ret = '';
            }
        } else {
            $stat_rota[0] = 1;
            $stat_rota[1] = 2;
            // if($stat[0] == 5){
            //     $stat_rota[0] = 2;
            // }
            $emrota = $this->rota->getRotaPed(false, $stat_rota);
            $rots = [];
            $rot_id = '';
            $ped_id = '';
            $ret = '' ;
            if(count($emrota) > 0){
                for($rt=0;$rt<count($emrota);$rt++){                    
                    if($rot_id != $emrota[$rt]['rot_id']){
                        if($rot_id != ''){
                            $ret .= monta_card_rota($rots); 
                            if(intval($rots['rot_status']) == 1){
                                $r['retcria'] .= $ret;
                            } else {
                                $r['retentr'] .= $ret;
                            }
                            $ret = '';
                            $rots = [];
                        }
                        $rots['rot_id']             = $emrota[$rt]['rot_id'];
                        $rots['rot_status']         = $emrota[$rt]['rot_status'];
                        $rots['rot_data_criacao']   = data_br($emrota[$rt]['rot_data_criacao']);
                        $rots['rot_data_saida']     = data_br($emrota[$rt]['rot_data_saida']);
                        $rots['rot_data_final']     = data_br($emrota[$rt]['rot_data_final']);
                        $rots['rot_desc_status']     = $emrota[$rt]['rot_desc_status'];
                        $rots['mtb_cpf']            = $emrota[$rt]['mtb_cpf'];
                        $rots['mtb_nome']           = $emrota[$rt]['mtb_nome'];
                        $rots['ope_nome']           = $emrota[$rt]['ope_nome'];
                        $rot_id = $emrota[$rt]['rot_id'];
                        $ct_pp = 0;
                        $rots['pedidos'][$ct_pp]['ped_id'] = $emrota[$rt]['ped_id'];
                        $rots['pedidos'][$ct_pp]['ped_numero'] = $emrota[$rt]['ped_numero'];
                        $rots['pedidos'][$ct_pp]['pcp_inicio'] = data_br($emrota[$rt]['pcp_inicio']);
                        $rots['pedidos'][$ct_pp]['pcp_final'] = data_br($emrota[$rt]['pcp_final']);
                        $rots['pedidos'][$ct_pp]['ped_data'] = data_br($emrota[$rt]['ped_data']);
                        $rots['pedidos'][$ct_pp]['ped_hora'] = $emrota[$rt]['ped_hora'];
                        $rots['pedidos'][$ct_pp]['ped_valor_taxa'] = $emrota[$rt]['ped_valor_taxa'];
                        $rots['pedidos'][$ct_pp]['ped_status'] = $emrota[$rt]['ped_status'];
                        $rots['pedidos'][$ct_pp]['ped_endereco'] = $emrota[$rt]['ped_endereco'];
                        $rots['pedidos'][$ct_pp]['ped_desc_status'] = $emrota[$rt]['ped_desc_status'];
                        $rots['pedidos'][$ct_pp]['ope_nome'] = $emrota[$rt]['ope_nome'];
                        $rots['pedidos'][$ct_pp]['tax_nome'] = $emrota[$rt]['tax_nome'];
                        $rots['pedidos'][$ct_pp]['ren_status'] = $emrota[$rt]['ren_status'];
                        $ped_id = $emrota[$rt]['ped_id'];
                        // $ct_pp++;
                        $ct_pr = 0;
                    } else if($ped_id != $emrota[$rt]['ped_id']){                                                          
                        $ct_pp++;
                        $rots['pedidos'][$ct_pp]['ped_id'] = $emrota[$rt]['ped_id'];
                        $rots['pedidos'][$ct_pp]['ped_numero'] = $emrota[$rt]['ped_numero'];
                        $rots['pedidos'][$ct_pp]['pcp_inicio'] = data_br($emrota[$rt]['pcp_inicio']);
                        $rots['pedidos'][$ct_pp]['pcp_final'] = data_br($emrota[$rt]['pcp_final']);
                        $rots['pedidos'][$ct_pp]['ped_data'] = data_br($emrota[$rt]['ped_data']);
                        $rots['pedidos'][$ct_pp]['ped_hora'] = $emrota[$rt]['ped_hora'];
                        $rots['pedidos'][$ct_pp]['ped_valor_taxa'] = $emrota[$rt]['ped_valor_taxa'];
                        $rots['pedidos'][$ct_pp]['ped_status'] = $emrota[$rt]['ped_status'];
                        $rots['pedidos'][$ct_pp]['ped_endereco'] = $emrota[$rt]['ped_endereco'];
                        $rots['pedidos'][$ct_pp]['ped_desc_status'] = $emrota[$rt]['ped_desc_status'];
                        $rots['pedidos'][$ct_pp]['ope_nome'] = $emrota[$rt]['ope_nome'];
                        $rots['pedidos'][$ct_pp]['tax_nome'] = $emrota[$rt]['tax_nome'];
                        $rots['pedidos'][$ct_pp]['ren_status'] = $emrota[$rt]['ren_status'];
                        $ped_id = $emrota[$rt]['ped_id'];
                        $ct_pr = 0;
                    }
                    $rots['pedidos'][$ct_pp]['produtos'][$ct_pr]['pcp_id'] = $emrota[$rt]['pcp_id'];
                    $rots['pedidos'][$ct_pp]['produtos'][$ct_pr]['pcp_final'] = $emrota[$rt]['pcp_final'];
                    $rots['pedidos'][$ct_pp]['produtos'][$ct_pr]['tpp_id'] = $emrota[$rt]['tpp_id'];
                    $rots['pedidos'][$ct_pp]['produtos'][$ct_pr]['tpp_nome'] = $emrota[$rt]['tpp_nome'];
                    $ct_pr++;
                }
                $ret .= monta_card_rota($rots);
                if(intval($rots['rot_status']) == 1){
                    $r['retcria'] .= $ret;
                } else {
                    $r['retentr'] .= $ret;
                }
            }
        }
        echo json_encode($r);
        // return $r;
    }

}
