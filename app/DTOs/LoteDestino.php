<?php

declare(strict_types=1);

namespace App\DTOs;

class LoteDestino
{
    public string $lote_destino = '';
    public string $validade_destino = '';
    public int $pro_estdestino = 0;

    public function toArray(): array
    {
        return [
            'lote_destino'     => $this->lote_destino,
            'validade_destino' => $this->validade_destino,
            'pro_estdestino'   => $this->pro_estdestino,
        ];
    }
}
