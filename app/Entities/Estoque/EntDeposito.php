<?php

namespace App\Entities\Estoque;

use App\Libraries\MyCampo;
use CodeIgniter\Entity\Entity;
use App\Models\Estoqu\EstoquDepositoModel;

class EntDeposito extends Entity
{
    protected $attributes = [
        'dep_codDep'       => null,
        'dep_desDep'       => null,
        'dep_aceNeg'       => null,
        'dep_codDescricao' => null,
        'dep_padrao'       => null,
        'dep_reserva'      => null,
    ];

    protected $casts = [
        'dep_codDep' => 'string',
    ];

    public object $campos;

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    // DEPÓSITO
    public function defCampos($dados = false, $show = false)
    {
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $cdep           = new MyCampo('est_sap_deposito', 'dep_codDep', true);
        $cdep->valor    = (isset($dados['dep_codDep'])) ? $dados['dep_codDep'] : '';
        $cdep->leitura  = $show;
        $ret['dep_codDep'] = $cdep->crInput();

        $ddep           = new MyCampo('est_sap_deposito', 'dep_desDep');
        $ddep->valor    = (isset($dados['dep_desDep'])) ? $dados['dep_desDep'] : '';
        $ddep->leitura  = $show;
        $ret['dep_desDep'] = $ddep->crInput();

        $acng           = new MyCampo('est_sap_deposito', 'dep_aceNeg');
        $acng->valor    = $acng->selecionado = (isset($dados['dep_aceNeg'])) ? $dados['dep_aceNeg'] : 'N';
        $acng->leitura  = $show;
        $acng->opcoes   = $simnao;
        $ret['dep_aceNeg'] = $acng->cr2opcoes();

        $padr           = new MyCampo('est_sap_deposito', 'dep_padrao');
        $padr->valor    = $padr->selecionado = (isset($dados['dep_padrao'])) ? $dados['dep_padrao'] : 'N';
        $padr->leitura  = false;
        $padr->opcoes   = $simnao;
        $ret['dep_padrao'] = $padr->cr2opcoes();

        $cdes           = new MyCampo('est_sap_deposito', 'dep_codDescricao');
        $cdes->valor    = (isset($dados['dep_codDescricao'])) ? $dados['dep_codDescricao'] : '';
        $cdes->leitura  = $show;
        $ret['dep_codDescricao'] = $cdes->crInput();

        $depositoModel = new EstoquDepositoModel();
        $depositos     = $depositoModel->getDeposito();
        $reservas      = array_column($depositos, 'dep_codDescricao', 'dep_codDep');

        // $rese           = new MyCampo('est_sap_deposito', 'dep_reserva');
        // $rese->valor    = $rese->selecionado = (isset($dados['dep_reserva'])) ? $dados['dep_reserva'] : '';
        // $rese->leitura  = false;
        // $rese->opcoes   = $reservas;
        // $rese->largura  = 50;
        // $ret['dep_reserva'] = $rese->crSelect();

        $config = [];
        $config['Largura']     = 50;
        $config['Leitura']     = false;
        $config['Obrigatorio'] = false;
        
        $ret['dep_reserva'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_reserva',
            'dep_reserva',
            $dados['dep_reserva'] ?? '',
            1,
            'est_sap_deposito',
            $reservas,          
            $config,
            'dep_reserva'
        );

        return (object) $ret;
    }
}