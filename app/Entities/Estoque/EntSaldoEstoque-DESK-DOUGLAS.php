<?php

namespace App\Entities\Estoque;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquDepositoModel;

class EntSaldoEstoque extends Entity
{
    public object $campos;

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos();
    }

    public function defCampos()
    {
        $ret = new \stdClass();

        // DEPÓSITOS
        // $depositoModel = new EstoquDepositoModel();
        // $r_deps        = $depositoModel->getDeposito();

        // $depos = array_column($r_deps, 'dep_codDescricao', 'dep_codDep');

        // $depo               = new MyCampo();
        // $depo->objeto       = 'select';
        // $depo->id           = 'codDep';
        // $depo->nome         = 'codDep';
        // $depo->label        = 'Depósito';
        // $depo->obrigatorio  = true;
        // $depo->size         = 50;
        // $depo->largura      = 50;
        // $depo->valor        = '';
        // $depo->dispForm     = 'col-5';
        // $depo->opcoes       = $depos;
        // $depo->selecionado  = 'GER';

        // $ret->sal_depo = $depo->crSelect();

        // DEPÓSITOS
        $config = [];
        $config['Label']       = 'Depósito';
        $config['DispForm']    = 'col-5';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        
        $ret->sal_depo = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_codDescricao',
            'GER',          
            1,
            '',
            [],
            $config,
            'codDep'        
        );

        // CÓDIGO ERP
        $code               = new MyCampo();
        $code->objeto       = 'input';
        $code->id           = 'codPro';
        $code->nome         = 'codPro';
        $code->label        = 'Código ERP';
        $code->obrigatorio  = false;
        $code->size         = 15;
        $code->valor        = '';
        $code->dispForm     = 'col-2';

        $ret->sal_code = $code->crInput();

        // LOTE
        $lote               = new MyCampo();
        $lote->objeto       = 'input';
        $lote->id           = 'codLot';
        $lote->nome         = 'codLot';
        $lote->label        = 'Lote';
        $lote->size         = 15;
        $lote->valor        = '';
        $lote->dispForm     = 'col-2';

        $ret->sal_lote = $lote->crInput();

        // BOTÃO BUSCAR
        $btbu               = new MyCampo();
        $btbu->id           = 'btBuscar';
        $btbu->nome         = 'btBuscar';
        $btbu->tipo         = 'button';
        $btbu->label        = 'Buscar';
        $btbu->dispForm     = '2col';
        $btbu->funcChan     = 'buscaSaldo()';
        $btbu->i_cone       = '<i class="fa-solid fa-magnifying-glass"></i> Buscar Estoque';
        $btbu->place        = 'Buscar Saldo';
        $btbu->classep      = 'btn-primary mt-2';

        $ret->sal_btbu = $btbu->crBotao();

        return $ret;
    }
}
