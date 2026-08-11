<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;

/**
 * `oco_ocorrencia_acao` — registro individual de cada ação aplicável a uma
 * ocorrência (pendente ou executada). Ver
 * docs/desenvolvimento/ocorrencia-acao-parcial-plano.md.
 *
 * Sem Entity dedicada (retorno em array) — uso interno pelo motor de
 * execução de OcoTrataOcorrencia::store(), sem tela própria de CRUD.
 */
class OcorreOcorrenciaAcaoModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia_acao';
    protected $primaryKey = 'oac_id';

    protected $allowedFields = [
        'oco_id',
        'tpa_id',
        'tpa_tipo',
        'oac_auto',
        'tmo_id',
        'stt_id',
        'tel_id',
        'oco_justi',
        'oac_executada',
        'oac_erro',
        'oac_msg',
        'usu_executou',
        'oac_automatica',
        'oac_criado',
        'oac_executado_em',
    ];

    /**
     * Todas as linhas de `oco_ocorrencia_acao` de uma ocorrência, já com o
     * nome da ação (`tpa_nome`, de `oco_tipo_acao`) — `findAll()`/`where()`
     * puro não traz essa coluna, o que deixava a coluna "Ação" em branco em
     * `finalizar()`/`show()` (`EntOcoTratativa::defCamposAcao()` usa
     * `$dados->tpa_nome ?? ''`).
     */
    public function getAcoesComNome(int $oco_id): array
    {
        return $this->select('oco_ocorrencia_acao.*, oco_tipo_acao.tpa_nome')
            ->join('oco_tipo_acao', 'oco_tipo_acao.tpa_id = oco_ocorrencia_acao.tpa_id')
            ->where('oco_ocorrencia_acao.oco_id', $oco_id)
            ->orderBy('oco_ocorrencia_acao.oac_id')
            ->findAll();
    }
}
