<?php 

namespace App\Entities\Estoque;

use CodeIgniter\Entity\Entity;

class EntMovimento extends Entity
{
    public object $campos;

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($data, $show);
    }

    // MÉTODO PRINCIPAL 
    public function defCampos($dados = false, $show = false)
    {
        $ret = [];

        $ret += (array) (new EntDeposito())->defCampos($dados, $show);
        $ret += (array) (new EntTransacao())->defCampos($dados, $show);

        return (object) $ret; 
    }
}