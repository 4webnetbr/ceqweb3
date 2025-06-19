<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\NotificaMonModel;
use App\Models\Produt\ProdutProdutoModel;

class Notifica extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $anota;
    public $modnotif;
    public $modusuario;
    public $modpermis;
    public $modprodutos;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct(){
        $this->modnotif = new NotificaMonModel();
        $this->modusuario = new ConfigUsuarioModel();
        $this->modpermis = new ConfigPerfilItemModel();
        $this->modprodutos = new ProdutProdutoModel();
    }

    public function verNotifica(){
        $ret = [];
        $dados = $_REQUEST;
        // debug($_REQUEST,true);
        $usuario   = $dados['usuario'];
        $notificacoes = $this->modnotif->getNotificaAberta(); 
        // debug($notificcoes, true);
        $lstnotif = '';
        $lstnotif .= "<button id='li_todas' name='li_todas' class='btn btn-primary fs-7 col-12 d-block' style='line-height: 1rem' onclick='viuNotifica(0)' ><i class='fas fa-check me-3' style='font-size: 1rem;' aria-hidden='true'></i>Todas Lidas</button>";
        $lstnotif .= "<div id='notificacoes' class='col-11 overflow-y-auto'>";
        $totnotif = 0;
        if(count($notificacoes) > 0){
            $count = 0;
            $ultimo = '';
            for($n = 0; $n < count($notificacoes); $n++){
                if($usuario == $notificacoes[$n]->not_id_usuario){
                    // if($ultimo != $notificacoes[$n]->not_id_registro){
                        $totnotif++;
                        $notif = $notificacoes[$n];
                        $tipo  = $notif->not_tipo;        
                        $metod = 'edit';
                        $class = "App\\Controllers\\".$notif->not_controler;
                        $pos = strrpos($notif->not_controler, "\\") ;
                        $controler = substr($notif->not_controler, $pos+1);
                        // debug($notif, true);
                        $methods = get_class_methods($class);
                        // debug($methods, true);
                        if(in_array('show', $methods)){
                            $metod = 'show';
                        }
                        if($tipo == 'E'){
                            $metod = 'delete';
                        }
                        // debug($controler, true);
                        if($controler == 'Produto'){
                            $prods = $this->modprodutos->getProdutoCod($notif->not_id_registro);
                            $notif->not_id_registro = $prods[0]['pro_id'];
                        }
                        $link = base_url($controler.'/'.$metod.'/'.$notif->not_id_registro);
                        // debug($link);
                        $lstnotif .= "<div class='".(++$count%2 ? "even" : "odd") ." p-1 border-2 border-bottom border-dark'>";
                        $lstnotif .= "<div class='fst-italic fs-7'>".data_br($notif->not_data)."</div>";
                        $lstnotif .= "<a href='$link' onclick='viuNotifica(\"".(string)$notif->_id."\")'>".$notif->not_texto."</a>";
                        $lstnotif .= "</div>";
                        $ultimo = $notif->not_id_registro;
                    // }
                }
            }
        }
        $lstnotif .= "</div>";
        $ret['novo'] = $totnotif;
        $ret['html'] = $lstnotif;
        echo json_encode($ret);
    }
    
    public function gravaNotifica($controler, $usuario, $registro, $msg, $tipo){
        $userorig = 'Sapiens';
        if($usuario != 0){
            $user   = $this->modusuario->getUsuarioId($usuario);
            $userorig = $user[0]['usu_nome']; 
        }
        $usuariospermissoes = $this->modpermis->getPermissaoTelaUsuario($controler);
        if(count($usuariospermissoes) > 0){
            $texto = $msg . ' em '.data_br(date('Y-m-d H:i:s')). ' por: '.$userorig;
            for($up = 0; $up < count($usuariospermissoes); $up++){
                $usu_dest = $usuariospermissoes[$up]['usu_id'];
                $permissoes =$usuariospermissoes[$up]['pit_permissao'];
                if($usu_dest != $usuario  && str_contains($permissoes, 'N')){
                    // insere a nova notificação
                    $this->modnotif->insertNotifica($controler, $texto, $registro, $usuario, $usu_dest, $tipo);
                    envia_msg_ws($controler,$msg,'Servidor',$usu_dest,$registro);
                }
            }
        }

        return(json_encode([]));
    }    

    public function viuNotifica(){
        $dados = $_REQUEST;
        $id   = $dados['id'];
        if($id == 0){ // marca todas
            $usuario = session()->get('usu_id');
            // debug($usuario);
            $grava = $this->modnotif->updateAllNotifica($usuario);
            // debug($grava);
        } else {
            $this->modnotif->updateNotifica($id);
        }
        return(json_encode([]));
    }
}