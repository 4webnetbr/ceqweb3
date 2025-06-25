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
        $this->mode_notifica = new NotificaMonModel();
        $this->mode_perfil   = new ConfigPerfilItemModel();

        $usuarioOrigem = 0; // Representa o sistema (Sapiens)
        $userOrigemNome = 'Sapiens';

        // Extrai o nome do controller sem namespace
        $pos = strrpos($controler, "\\");
        $nomeControl = ($pos !== false) ? substr($controler, $pos + 1) : $controler;

        log_message('info', 'Controller: ' . $controler);
        log_message('info', 'NomeControl: ' . $nomeControl);

        $usuarios = $this->mode_perfil->getPermissaoTelaUsuario(false, false, false, $nomeControl);
        log_message('info', 'Usuários encontrados: ' . json_encode($usuarios));

        if (!empty($usuarios)) {
            $dataHora = data_br(date('Y-m-d H:i:s'));
            $mensagemCompleta = "{$msg} em {$dataHora} por: {$userOrigemNome}";

            foreach ($usuarios as $usu) {
                $usuDest = $usu['usu_id'];
                $permissao = $usu['pit_permissao'];

                log_message('info', "Usuário Destino: {$usuDest} | Permissão: {$permissao}");

                // Apenas se não for o mesmo e tiver permissão de notificação
                if ($usuDest != $usuarioOrigem && str_contains($permissao, 'N')) {
                    $result = $this->mode_notifica->insertNotifica(
                        $controler,
                        $mensagemCompleta,
                        $registro,
                        $usuarioOrigem,
                        $usuDest,
                        $tipo
                    );
                    log_message('info', 'Notificação inserida: ' . var_export($result, true));
                }

                // Sempre envia a mensagem via WebSocket
                envia_msg_ws($controler, $msg, 'Servidor', $usuDest, $registro);
            }
        }

        return json_encode([]);
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