<?php

namespace App\Filters;

use App\Models\Config\ConfigMensagemModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigTelaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class loginFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['funcoes_helper']);

        $session = session();

        if (!$session->logged_in) {
            return redirect()->to(site_url('login'));
        }

        $session->set('last_activity', time());

        $uri = $request->getUri();
        $segments = $uri->getSegments();
        $path = $uri->getPath();

        if ($path === '/') {
            return redirect()->to($session->usu_dashboard);
        }

        $modal = end($segments) === 'modal=true';
        $controller = $segments[0] ?? '';
        $metodo = $segments[1] ?? 'index';

        // ✅ Carrega mensagens apenas uma vez por sessão
        if (!$session->has('msg_cfg')) {
            $msgModel = new ConfigMensagemModel();
            $mensagens = $msgModel->getMensagemId();
            $session->set(['msg_cfg' => $mensagens]);
        }

        $perfilId = $session->get('usu_perfil_id');
        $tipoUsuario = $session->get('usu_tipo');

        $telaModel = new ConfigTelaModel();
        $perfilItemModel = new ConfigPerfilItemModel();

        $telaInfo = $telaModel->getTelaSearch($controller);
        $dadosTela = [];

        if (!$telaInfo) {
            $dadosTela = [
                'title' => $controller,
                'permissao' => false,
                'erromsg' => "<h2>Atenção</h2>A Tela <b>{$controller}</b> 
                    <span style='color:red'>Não foi Encontrada!</span><br>
                    Informe o Problema ao Administrador do Sistema!",
            ];
        } else {
            $tela = $telaInfo[0];

            $dadosTela = [
                'modal' => $modal,
                'tel_id' => $tela['tel_id'],
                'icone' => $tela['tel_tela_icone'],
                'title' => $tela['tel_nome'],
                'controler' => $tela['tel_controler'],
                'model' => $tela['tel_model'],
                'identificador' => $tela['tel_ident'],
                'metodo' => $metodo,
                'regras_gerais' => $tela['tel_regras_gerais'],
                'regras_cadastro' => $tela['tel_regras_cadastro'],
                'bt_add' => $tela['tel_texto_botao'],
                'perfil_usu' => $perfilId,
            ];

            // ✅ Carrega menu apenas se necessário
            if (!$session->has('menu')) {
                $menu = montaMenu($perfilId, $tipoUsuario);
                $session->set(['menu' => $menu]);
                $dadosTela['it_menu'] = $menu;
            } else {
                $dadosTela['it_menu'] = $session->get('menu');
            }

            $dadosTela['permissao'] = $this->buscaPermissaoTela($perfilItemModel, $perfilId, $tela['tel_id']) ?? 'CAEX';
            $dadosTela['erromsg'] = $this->validaPermissao($dadosTela['permissao'], $metodo, $dadosTela['title']);
        }

        $session->setFlashdata(['dados_tela' => $dadosTela]);

        if (!empty(trim($dadosTela['erromsg']))) {
            $view = $modal ? 'vw_semacesso_modal' : 'vw_semacesso';
            echo view($view, $dadosTela);
            exit;
        }
    }

    private function buscaPermissaoTela(ConfigPerfilItemModel $model, int $perfilId, int $telaId): ?string
    {
        $result = $model->getItemPerfilClasse($perfilId, $telaId);
        return $result[0]['pit_permissao'] ?? null;
    }

    private function validaPermissao(string $permissao, string $metodo, string $titulo): string
    {
        if (in_array($metodo, ['', 'index']) && $permissao === '') {
            return "<h2>Sem autorização para acessar a lista de <br>{$titulo}</h2><br>Solicite acesso ao Administrador do Sistema";
        }

        if ($metodo === 'add' && !strpbrk($permissao, 'A')) {
            return "<h2>Sem autorização para Adicionar <br>{$titulo}</h2><br>Solicite acesso ao Administrador do Sistema";
        }

        if ($metodo === 'edit' && !strpbrk($permissao, 'E')) {
            return "<h2>Sem autorização para Editar <br>{$titulo}</h2><br>Solicite acesso ao Administrador do Sistema";
        }

        return '';
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não utilizado
    }
}
