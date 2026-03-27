<?php
namespace App\Controllers;

use App\Services\MensagemService;
use CodeIgniter\RESTful\ResourceController;

class Mensagem extends ResourceController
{
    private MensagemService $service;

    public function __construct()
    {
        $this->service = new MensagemService();
    }

    public function show($id = null)
    {
        $id = (int) $id;

        $mensagem = (new \App\Services\MensagemService())
            ->getMensagem($id);

        return $mensagem
            ? $this->respond($mensagem)
            : $this->failNotFound('Mensagem não encontrada');
    }
}