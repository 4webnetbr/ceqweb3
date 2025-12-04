<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;

class OcorreTipoEventoModel extends Model
{
    protected $DBGroup     = 'dbOcorrencia';
    protected $table       = 'oco_tipo_evento';
    protected $primaryKey  = 'tpo_id';
    protected $returnType  = 'array';

    protected $allowedFields = [
        'tpo_id',
        'tpo_nome',
        'tpo_ativo'
    ];

    public function getTipoEvento()
    {
        return $this->orderBy('tpo_nome', 'ASC')->findAll();
    }
}
