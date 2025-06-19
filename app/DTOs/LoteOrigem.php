<?php

declare(strict_types=1);

namespace App\DTOs;

class LoteOrigem
{
    public string $lote_origem = '';
    public string $validade_origem = '';
    public int $pro_estorigem = 0;

    public function toArray(): array
    {
        return [
            'lote_origem'     => $this->lote_origem,
            'validade_origem' => $this->validade_origem,
            'pro_estorigem'   => $this->pro_estorigem,
        ];
    }
}
