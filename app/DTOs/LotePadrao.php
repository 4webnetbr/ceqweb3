<?php

declare(strict_types=1);

namespace App\DTOs;

class LotePadrao
{
    public string $lote_padrao = '';
    public string $validade_padrao = '';
    public int $pro_estpadrao = 0;

    public function toArray(): array
    {
        return [
            'lote_padrao'     => $this->lote_padrao,
            'validade_padrao' => $this->validade_padrao,
            'pro_estpadrao'   => $this->pro_estpadrao,
        ];
    }
}
