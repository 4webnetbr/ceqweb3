<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgUsuario;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;

class CfgUsuario extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $usuario;
    public $perfil;
    public $tela;

    public function __construct()
    {
        $this->data = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->usuario   = new ConfigUsuarioModel();
        $this->perfil    = new ConfigPerfilModel();
        $this->tela      = new ConfigTelaModel();

        // Caso exista erro de permissão, bloqueia acesso
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'usu_id,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // Busca usuários
        // if (!$usuarios = cache('usuarios')) {
        $dados_usuario = $this->usuario->getUsuarioId();

        // Estrutura padrão do DataTable
        $this->data['exclusao'] = false;
        $usuarios = [
            'data' => montaListaColunasEnt($this->data, 'usu_id', $dados_usuario, 'usu_nome'),
        ];
        // Cache curto devido a possíveis alterações frequentes
        cache()->save('usuarios', $usuarios, 30);
        // }

        echo json_encode($usuarios);
    }

    public function add()
    {
        $usuarioEnt = new EntCfgUsuario(null, false);

        // Dados Gerais
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $usuarioEnt->usu_id;
        $campos[0][1] = $usuarioEnt->usu_nome;
        $campos[0][2] = $usuarioEnt->usu_email;
        $campos[0][3] = $usuarioEnt->usu_login;
        $campos[0][4] = $usuarioEnt->usu_nova_senha;
        $campos[0][5] = $usuarioEnt->usu_perfil;
        $campos[0][6] = $usuarioEnt->usu_dashboard;

        // Avatar
        $secao[1] = 'Avatar';
        $campos[1][0] = $usuarioEnt->usu_avatar;

        $this->data['secoes']  = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // Limpa o login automaticamente ao carregar a tela
        $this->data['script'] = '<script>
            document.addEventListener("DOMContentLoaded", function () {
                const login = document.getElementById("usu_login");
                if (login) {
                    login.value = "";
                    setTimeout(() => login.value = "", 700);
                }
            });
        </script>';

        echo view('vw_edicao', $this->data);
    }

    public function show($id)
    {
        $this->edit($id, true);
    }

    public function edit($id, $show = false)
    {
        $dados_usuario = $this->usuario->getUsuarioId($id, $show)[0];

        $usuEnt = new EntCfgUsuario($dados_usuario, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][] = $usuEnt->usu_id;
        $campos[0][] = $usuEnt->usu_nome;
        $campos[0][] = $usuEnt->usu_email;
        $campos[0][] = $usuEnt->usu_login;
        $campos[0][] = $usuEnt->usu_nova_senha;
        $campos[0][] = $usuEnt->usu_perfil;
        $campos[0][] = $usuEnt->usu_dashboard;

        $secao[1] = 'Avatar';
        $campos[1][] = $usuEnt->usu_avatar;

        $this->data['desc_edicao'] = $dados_usuario->usu_nome;
        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = 'store';

        $this->data['log'] = buscaLog('cfg_usuario', $id);

        echo view('vw_edicao', $this->data);
    }

    public function edit_senha($id)
    {
        // guarda página anterior
        $anterior['anterior'] = $_SERVER['HTTP_REFERER'] ?? '';
        session()->set($anterior);

        // busca usuário
        $dados_usuario = $this->usuario->getUsuarioId($id)[0];
        $usuarioEnt = new EntCfgUsuario($dados_usuario, true);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $usuarioEnt->usu_id;
        $campos[0][1] = $usuarioEnt->usu_nome;
        $campos[0][2] = $usuarioEnt->usu_email;
        $campos[0][3] = $usuarioEnt->usu_login;
        $campos[0][4] = $usuarioEnt->usu_nova_senha;
        $campos[0][5] = $usuarioEnt->usu_contra_senha;
        $campos[0][6] = "<span id='msg_senha' class='text-danger bg-warning'></span>";
        $campos[0][7] = $usuarioEnt->usu_perfil;

        $secao[1] = 'Avatar';
        $campos[1][0] = $usuarioEnt->usu_avatar;

        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = 'store';
        $this->data['desc_metodo'] = 'Alteração de Senha de';

        echo view('vw_edicao', $this->data);
    }

    public function ativinativ($id, $tipo)
    {
        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'usu_ativo' => 'A'
                ];
            } else {
                $dad_atin = [
                    'usu_ativo' => 'I'
                ];
            }
            $this->usuario->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Usuário Alterada com Sucesso');
            $ret['msg']  = 'Usuário Alterado com Sucesso';
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14; // ou código personalizado, se preferir
        }
        echo json_encode($ret);
    }


    public function delete($id)
    {
        // if ($this->usuario->delete($id)) {
        //     cache()->delete('usuarios');
        //     return $this->response->setJSON([
        //         'erro' => false,
        //         'msg'  => 'Registro excluído com sucesso!'
        //     ]);
        // }
        // return $this->response->setJSON([
        //     'erro' => true,
        //     'msg'  => 'Erro ao excluir o registro.'
        // ]);


        $ret = [];
        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_usuario', 'usu_id', (int) $id);

            // Soft delete
            $this->usuario->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Registro excluído com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
        echo json_encode($ret);
    }

    public function store()
    {
        $dados = $this->request->getPost();
        if (isset($dados['usu_senha'])) {
            if ($dados['usu_senha'] == '') {
                unset($dados['usu_senha']);
            } else {
                $dados['usu_senha'] = md5($dados['usu_senha']);
            }
        }
        if ($this->usuario->save($dados)) {
            if ($dados['usu_id'] != '') {
                $usu_id = $dados['usu_id'];
            } else {
                $usu_id = $this->usuario->getInsertID();
            }
            $avatar = $this->request->getFile('usu_avatar');
            if ($avatar->getFilename() != '') {
                $path_avat = 'assets/uploads/usuario/usu_' . $usu_id . '.jpg';
                @unlink($path_avat);

                $avatar->move('assets/uploads/usuario', 'usu_' . $usu_id . '.jpg');
            }
            $ret['erro'] = false;
            $ret['msg']  = 'Usuario gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = session()->get('anterior');
            if ($ret['url'] == '') {
                $ret['url']  = site_url($this->data['controler']);
            }
        } else {
            $error = $this->usuario->getErrors();
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Usuario, Verifique!' . $error;
            session()->setFlashdata('msg', $ret['msg']);
        }
        echo json_encode($ret);
    }
}
