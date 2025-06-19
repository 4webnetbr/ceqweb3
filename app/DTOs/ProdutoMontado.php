<?php

declare(strict_types=1);

namespace App\DTOs;

use App\DTOs\LoteDestino;
use App\DTOs\LoteOrigem;
use App\DTOs\LotePadrao;

class ProdutoMontado
{
    public string $pro_codpro;
    public string $pro_despro;
    public string $lot_lote;
    public string $lot_validade;
    public string $pro_inform;
    public string $fab_apeFab;
    public string $pro_mindiaanterior;
    public int $pro_qtdemb;
    public int $pro_consumo;
    public int $pro_consumo_diaant;
    public int $pro_consumo_medio;
    public int $pro_multiplica;
    public int $pro_pctseguranca;
    public int $pro_seguranca;
    public int $pro_sugestao;
    public int $pro_minimo;
    public int $pro_maximo;
    public int $pro_requisicao;

    public LoteOrigem $loteori;
    public LoteDestino $lotedes;
    public LotePadrao $lotepad;

    public function __construct(array $data)
    {
        $this->pro_codpro   = (string) ($data['pro_codpro'] ?? '');
        $this->pro_despro   = (string) ($data['pro_despro'] ?? '');
        $this->lot_lote     = (string) ($data['lot_lote'] ?? '');
        $this->lot_validade = (string) (data_br($data['lot_validade']) ?? '');
        $this->pro_inform   = (string) ($data['pro_informacoes'] ?? '');
        $this->fab_apeFab   = (string) ($data['fab_apeFab'] ?? '');
        $this->pro_qtdemb   = (int) ($data['pro_qtdemb'] ?? 0);
        $this->pro_consumo   = (int) ($data['pro_consumo'] ?? 0);
        $this->pro_consumo_diaant   = (int) ($data['pro_consumo_diaant'] ?? 0);
        $this->pro_consumo_medio   = (int) ($data['pro_consumo_medio'] ?? 0);
        $this->pro_multiplica   = (int) ($data['pro_multiplica'] ?? 0);
        $this->pro_pctseguranca   = (int) ($data['pct_seguranca'] ?? 0);
        $this->pro_seguranca   = (int) ($data['pro_seguranca'] ?? 0);
        $this->pro_sugestao   = (int) ($data['pro_sugestao'] ?? 0);
        $this->pro_mindiaanterior   = (string) ($data['pre_mindiaanterior'] ?? 'S');
        $this->pro_minimo   = 0;
        $this->pro_maximo   = 0;
        $this->pro_requisicao   = 0;

        // Inicializamos com objetos "vazios" inicialmente
        $this->loteori = new LoteOrigem();
        $this->lotedes = new LoteDestino();
        $this->lotepad = new LotePadrao();
    }

    public function toArray(): array
    {
        return [
            'pro_codpro'   => $this->pro_codpro,
            'pro_despro'   => $this->pro_despro,
            'lot_lote'     => $this->lot_lote,
            'lot_validade' => $this->lot_validade,
            'pro_inform'   => $this->pro_inform,
            'fab_apeFab'   => $this->fab_apeFab,
            'pro_qtdemb'   => $this->pro_qtdemb,
            'pro_consumo'   => $this->pro_consumo,
            'pro_consumo_diaant'   => $this->pro_consumo_diaant,
            'pro_consumo_medio'   => $this->pro_consumo_medio,
            'pro_multiplica'   => $this->pro_multiplica,
            'pro_pctseguranca'   => $this->pro_pctseguranca,
            'pro_seguranca'   => $this->pro_seguranca,
            'pro_sugestao'   => $this->pro_sugestao,
            'pro_minimo'   => $this->pro_minimo,
            'pro_maximo'   => $this->pro_maximo,
            'pro_requisicao'   => $this->pro_requisicao,
            'pro_mindiaanterior'   => $this->pro_mindiaanterior,
            'loteori'      => $this->loteori->toArray(),
            'lotedes'      => $this->lotedes->toArray(),
            'lotepad'      => $this->lotepad->toArray(),
        ];
    }
}
