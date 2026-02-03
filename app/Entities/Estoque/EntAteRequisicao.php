<?php

namespace App\Entities\Estoque;

use App\Entities\Estoque\EntRequisicao;

class EntAteRequisicao extends EntRequisicao
{
    /**
     * Atributos da Requisição
     */
    protected $attributes = [
        'rpa_id'                => null,
        'req_id'                => null,
        'req_data'              => null,
        'req_dataentrega'       => null,
        'tmo_id'                => null,
        'req_deporigem'         => null,
        'req_depdestino'        => null,
        'req_consdiaanterior'   => 'S',
        'req_medconsumodias'    => 'N',
        'req_meddias'           => 2,
        'req_repetedias'        => 0,
        'req_percseguranca'     => 0,
        'req_observacao'        => null,
        'stt_id'                => null,
        'usu_nome'              => null,
        'stt_nome'              => null,
        'stt_cor'               => null,
    ];

    /**
     * Casts 
     */
    protected $casts = [
        'rpa_id'              => 'integer',
        'req_id'              => 'integer',
        'tmo_id'              => 'integer',
        'req_meddias'         => 'integer',
        'req_repetedias'      => 'integer',
        'req_percseguranca'   => 'integer',
        'stt_id'              => 'integer',
    ];

    /**
     * Retorna somente os campos da REQUISIÇÃO
     */
    public function defCampos($dados = false, $show = false, $tipo = false)
    {
        return parent::defCampos($dados ?: $this, $show, $tipo);
    }
}
