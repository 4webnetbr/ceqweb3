<?php

namespace App\Libraries;

use App\Models\NotificaMonModel;
use App\Models\Config\ConfigPerfilItemModel;

class Notificacao {
    public $mode_notifica;
    public $mode_perfil;

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
}