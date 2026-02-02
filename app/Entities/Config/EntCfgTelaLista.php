<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigDicDadosModel;
use ReflectionClass;
use ReflectionMethod;
use App\Traits\HasTela;

class EntCfgTelaLista extends Entity
{
    use HasTela;

    protected $attributes = [
        'lis_id'         => null,
        'tel_id'         => null,
        'lis_campo'      => null,
        'lis_rotulo'     => null,
        'lis_atualizado' => null,
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param array $dados
     * @return array
     */
}
