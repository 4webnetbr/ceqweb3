<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntFabricante extends Entity
{
    protected $attributes = [
        'fab_codFab' => null,
        'fab_nomFab' => null,
        'fab_apeFab' => null,
    ];

    protected $casts = [
        'fab_codFab' => 'string',
    ];

    public array $campos = [];

    public function __construct(array|object|null $data = null, bool $show = false)
    {
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        parent::__construct($data ?? []);
        $this->campos = $this->defCampos($data ?? [], $show);
    }

    /**
     * Define os campos do Fabricante (padrão antigo do Model)
     */
    public function defCampos($dados = false, $show = false)
    {
        $cori           =  new MyCampo('pro_sap_fabricante', 'fab_codFab', true);
        $cori->valor    = (isset($dados['fab_codFab'])) ? $dados['fab_codFab'] : '';
        $cori->obrigatorio = true;
        $cori->leitura  = $show;
        $ret['fab_codFab'] = $cori->crInput();

        $dori           =  new MyCampo('pro_sap_fabricante', 'fab_nomFab');
        $dori->valor    = (isset($dados['fab_nomFab'])) ? $dados['fab_nomFab'] : '';
        $dori->obrigatorio = true;
        $dori->leitura  = $show;
        $ret['fab_nomFab'] = $dori->crInput();

        $cdes           =  new MyCampo('pro_sap_fabricante', 'fab_apeFab');
        $cdes->valor    = (isset($dados['fab_apeFab'])) ? $dados['fab_apeFab'] : '';
        $cdes->obrigatorio = true;
        $cdes->leitura  = $show;
        $ret['fab_apeFab'] = $cdes->crInput();
        
        return $ret;
    }   
}