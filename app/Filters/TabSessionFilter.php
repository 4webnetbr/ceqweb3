<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TabSessionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $request = service('requet');
        // Tente obter o tabId da URL, do cookie ou defina um valor padrão
        $tabId = $request->getGet('tabId') ?? $request->getCookie('tabId') ?? 'default_tab';

        // Define o nome da sessão dinamicamente com o tabId
        $sessionName = 'ci_session_' . $tabId;

        // Atualiza o nome da sessão antes de iniciar a sessão
        ini_set('session.name', $sessionName);

        // Inicia a sessão se ela ainda não foi iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não é necessário fazer nada aqui
    }
}
