<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LimpezaAutomatica implements FilterInterface
{
    protected $registroExecucao = WRITEPATH . 'limpeza_auto.dat';

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->executouHoje()) {
            return; // Já rodou hoje, não faz de novo
        }

        // Limpa logs com +7 dias
        $this->limparArquivosVelhos(WRITEPATH . 'logs', 7);

        // Limpa sessions com +1 dia
        $this->limparArquivosVelhos(WRITEPATH . 'session', 4);

        $this->limparArquivosVelhos(WRITEPATH . 'logs/cron', 2);

        // Marca que já rodou hoje
        $this->registrarExecucao();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada a fazer depois
    }

    private function limparArquivosVelhos(string $pasta, int $dias)
    {
        $arquivos = glob($pasta . '/*');
        $limite = time() - ($dias * 86400);

        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo) && filemtime($arquivo) < $limite) {
                @unlink($arquivo);
            }
        }
    }

    private function executouHoje(): bool
    {
        if (!file_exists($this->registroExecucao)) {
            return false;
        }

        $data = trim(file_get_contents($this->registroExecucao));
        return $data === date('Y-m-d');
    }

    private function registrarExecucao(): void
    {
        file_put_contents($this->registroExecucao, date('Y-m-d'));
    }
}
