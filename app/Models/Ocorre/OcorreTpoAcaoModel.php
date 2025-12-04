<?php
namespace App\Models\Ocorre;

use CodeIgniter\Model;

class OcorreTpoAcaoModel extends Model
{
    protected $DBGroup = 'dbOcorrencia';
    protected $table   = 'oco_tpo_acao';
    protected $primaryKey = 'toa_id';
    protected $returnType = 'array';

   public function getAcoesByTpo($tpo_id)
{
    return $this->select('oco_tpo_acao.*, oco_tipo_acao.tpa_nome')
                ->join('oco_tipo_acao', 'oco_tipo_acao.tpa_id = oco_tpo_acao.tpa_id')
                ->where('oco_tpo_acao.tpo_id', $tpo_id)
                ->findAll();
}
}