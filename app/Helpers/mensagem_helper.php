<?php

use App\Models\Config\ConfigMensagemModel;

function getMensagem(string $codigo): ?string
{
    static $cache = [];

    if (isset($cache[$codigo])) {
        return $cache[$codigo];
    }

    $model = new ConfigMensagemModel();
    $mensagem = $model->where('msg_codigo', $codigo)->first();

    if ($mensagem) {
        $cache[$codigo] = $mensagem['msg_texto'];
        return $mensagem['msg_texto'];
    }

    return null;
}
