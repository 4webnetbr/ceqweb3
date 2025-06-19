<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UsuariosEnt extends Entity
{
    protected $datamap = [];
    protected $dates   = ['usu_excluido'];
    protected $casts   = [];
}
