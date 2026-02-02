<?php

namespace App\Entities\Estoque;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;

class EntTransacao extends Entity
{
    protected $attributes = [
        'tns_codtns' => null,
        'tns_germvp' => null,
        'tns_dettns' => null,
    ];

    protected $casts = [
        'tns_codtns' => 'string',
    ];

    public object $campos;

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    // TRANSAÇÃO 
    public function defCampos($dados = false, $show = false)
    {
        $tns_cod           = new MyCampo('est_sap_transacao', 'tns_codtns', true);
        $tns_cod->valor    = (isset($dados['tns_codtns'])) ? $dados['tns_codtns'] : '';
        $tns_cod->leitura  = $show;
        $ret['tns_codtns'] = $tns_cod->crInput();

        $tns_ger           = new MyCampo('est_sap_transacao', 'tns_germvp');
        $tns_ger->valor    = (isset($dados['tns_germvp'])) ? $dados['tns_germvp'] : '';
        $tns_ger->leitura  = $show;
        $tns_ger->largura  = 15;
        $ret['tns_germvp'] = $tns_ger->crInput();

        $tns_det           = new MyCampo('est_sap_transacao', 'tns_dettns');
        $tns_det->valor    = (isset($dados['tns_dettns'])) ? $dados['tns_dettns'] : '';
        $tns_det->leitura  = $show;
        $ret['tns_dettns'] = $tns_det->crInput();

        return (object) $ret;
    }
}