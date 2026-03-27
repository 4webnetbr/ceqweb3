<?php

namespace App\Services;

use App\Models\Config\ConfigMensagemModel;
use CodeIgniter\Config\Services;

class MensagemService
{
    public function getMensagem(int $msgid): ?object
    {
        $cache = Services::cache();
        $cacheKey = "mensagem_{$msgid}";

        if ($mensagem = $cache->get($cacheKey)) {
            return $mensagem;
        }

        $model = new ConfigMensagemModel();
        $mensagem = $model->getMensagem($msgid) ?? null;

        if ($mensagem) {
            $cache->save($cacheKey, $mensagem, 300);
        }

        return $mensagem;
    }
}