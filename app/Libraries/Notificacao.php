<?php

namespace App\Libraries;

use App\Models\NotificaMonModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Config\ConfigPerfilItemModel;

class Notificacao {
    public $mode_notifica;
    public $mode_perfil;
    public $modnotif;
    public $modusuario;
    public $modpermis;
    public $modprodutos;

    function gravaNotifica($controler, $registro, $msg, $tipo)
    {
        $this->mode_notifica    = new NotificaMonModel();
        $this->mode_perfil      = new ConfigPerfilItemModel();
        // echo "Grava Notifica \n";
        $usuario  = 0;
        $userorig = 'Sapiens';
        $pos = strrpos($controler, "\\");
        // echo "posicao ".$pos."\n";
        if($pos != ''){
            $nomecontrol = substr($controler, $pos + 1);
        } else {
            $nomecontrol = $controler;
        }
        // echo "nomecontrol ".$nomecontrol."\n";
        log_message('info', 'Controler ' . $controler);
        log_message('info', 'Posição ' . $pos);
        log_message('info', 'NomeControl ' . $nomecontrol);
        // debug($controler);

        $usuariospermissoes = $this->mode_perfil->getPermissaoTelaUsuario(false, false, false, $nomecontrol);
        log_message('info', 'Usuários ' . json_encode($usuariospermissoes));
        if (count($usuariospermissoes) > 0) {
            $texto = $msg . ' em ' . data_br(date('Y-m-d H:i:s')) . ' por: ' . $userorig;
            for ($up = 0; $up < count($usuariospermissoes); $up++) {
                $usu_dest = $usuariospermissoes[$up]['usu_id'];
                $permissoes = $usuariospermissoes[$up]['pit_permissao'];
                log_message('info', 'Usuario ' . $usuario);
                log_message('info', 'Permissoes ' . $permissoes);
                // debug($usuario);
                // debug($usu_dest);
                if ($usu_dest != $usuario  && str_contains($permissoes, 'N')) { // se não for o mesmo usuário que alterou e o usuário tem permissão de notificação
                    // insere a nova notificação
                    $insNot =  $this->mode_notifica->insertNotifica($controler, $texto, $registro, $usuario, $usu_dest, $tipo);
                    log_message('info', 'insNot ' . var_dump($insNot));
                    // var_dump($insNot);
                    envia_msg_ws($controler, $msg, 'Servidor', $usu_dest, $registro);
                }
            }
        }
        $usuariospermissoes = $this->mode_perfil->getPermissaoTelaUsuario(false, false, false, $nomecontrol);
        if (count($usuariospermissoes) > 0) {
            for ($up = 0; $up < count($usuariospermissoes); $up++) {
                $usuario = $usuariospermissoes[$up];
                log_message('info', 'Usuario ' . $usuario);
                envia_msg_ws($controler, $msg, 'Servidor', $usuario, $registro);
            }
        }

        return (json_encode([]));
    }

    public function verNotifica(){
        $this->modnotif = new NotificaMonModel();
        $this->modusuario = new ConfigUsuarioModel();
        $this->modpermis = new ConfigPerfilItemModel();
        $this->modprodutos = new ProdutProdutoModel();

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
                        $pos = strrpos($notif->not_controler, "\\");
                        if($pos != ''){
                            $controler = substr($notif->not_controler, $pos+1);
                        } else {
                            $controler = $notif->not_controler;
                            // $class = "App\\Controllers\\".$notif->not_controler."\\";
                        }
                        // debug($notif, true);
                        // debug($class, true);
                        $methods = get_class_methods("App\\Controllers\\".$notif->not_controler);
                        // debug($methods, true);
                        if($notif->not_id_registro != ''){
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
                        } else {
                            $link = base_url($controler);
                        }
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

    public function viuNotifica(){
        $this->modnotif = new NotificaMonModel();

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