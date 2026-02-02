<?php

namespace App\Entities\Microb;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\CommonModel;

class EntMicrobAnaRequisicao extends Entity
{
    protected $attributes = [
        'req_id'          => null,
        'req_data'        => null,
        'req_lotemb'      => null,
        'usu_id'          => null,
        'ana_descmetodo'  => null,
        'ana_lotemb'      => null,
    ];

    protected $dates = [];
    protected $casts = [];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    
     public function defCampos($dados = false, $show = false)
    {
        $opcoes = new CommonModel();
        $ret = [];

        // ID da Requisição (campo oculto)
        $id           =  new MyCampo('pro_mic_requisicao', 'req_id', false);
        $id->valor    = isset($dados['req_id']) ? $dados['req_id'] : '';
        $ret['req_id']    = $id->crOculto();

        // Definição do lote de embarque
        if (isset($dados['req_lotemb'])) {
            $lote = $dados['req_lotemb'];
        } else if (isset($dados['ana_lotemb'])) {
            $lote = $dados['ana_lotemb'];
        } else {
            $lote = '';
        }
        // debug($dados['ana_descmetodo'], true);

        // Lote de Embarque
        $lmb            =  new MyCampo('pro_mic_requisicao', 'req_lotemb', false);
        $lmb->valor     = $lote;
        $lmb->tipo      = 'sonumero';
        $lmb->maxLength = 9;
        $lmb->largura   = 100;
        $lmb->size      = 9;
        $lmb->leitura   = true;
        if ($lote != "") {
            $ret['req_lotemb']    = $lmb->crInput();
        } else {
            $ret['req_lotemb']    = $lmb->crOculto();
        }

        // Descrição do Método de Análise
        $met            =  new MyCampo('pro_mic_analise', 'ana_descmetodo', false);
        $met->valor     = isset($dados['ana_descmetodo']) ? $dados['ana_descmetodo'] : '';
        $met->maxLength = 40;
        $met->largura   = 100;
        $met->size      = 40;
        $met->leitura   = true;
        $ret['ana_descmetodo']    = $met->crInput();

        return $ret;
    }
}
