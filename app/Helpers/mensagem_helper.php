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

/**
 * Redireciona para um controller definindo uma mensagem de erro via Flashdata.
 *
 * @param string     $controller Rota/controller de destino
 * @param int|string $msgCode    Código da mensagem (default: 41)
 *
 * @return \CodeIgniter\HTTP\RedirectResponse
 */
function redirectWithError(string $controller, int|string $msgCode = 41): \CodeIgniter\HTTP\RedirectResponse
{
    session()->setFlashdata('msg', $msgCode);

    return redirect()->back();
}