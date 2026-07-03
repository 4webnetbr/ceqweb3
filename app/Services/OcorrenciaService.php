<?php

namespace App\Services;

use App\Controllers\Ocorrencia\OcoTrataOcorrencia;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Models\Produt\ProdutLoteModel;

class OcorrenciaService
{
    protected $db;
    protected $ocorrenciaModel;
    protected $loteModel;
    protected $subtipoModel;

    public function __construct()
    {
        $this->db              = \Config\Database::connect('dbEstoque');
        $this->ocorrenciaModel = new OcorreOcorrenciaModel(); // ajuste o nome
        $this->loteModel       = new ProdutLoteModel();        // ajuste o nome
        $this->subtipoModel    = new OcorreSubtOcorrenciaModel();
    }

    public function processAfterSave(array $data): void
    {
        $subtipo = $this->subtipoModel->getSubtOcorrencia($data['sut_id']);

        if (!empty($subtipo) && $subtipo[0]->sut_fina === 'S') {
            $controller = new OcoTrataOcorrencia();
            $controller->store($data);
        }
    }

    /**
     * Gera as ocorrências pendentes (oco_id IS NULL) dos produtos da requisição.
     * Idempotente: chamadas concorrentes não duplicam.
     *
     * @return array{erro:bool, msg:mixed, criadas:int[], ignoradas:int}
     */
    public function gerarOcorrencias(array $produtosreq, int $reqId, int $sttId = 28, ?int $usuId = null): array
    {
        $usuId = $usuId ?? session()->get('usu_id');
        $ret   = ['erro' => false, 'msg' => null, 'criadas' => [], 'ignoradas' => 0];

        if (empty($produtosreq)) {
            return $ret;
        }

        // pares pro_id|lot_lote que interessam nesta chamada
        $pares = [];
        foreach ($produtosreq as $val) {
            $pares[$val->pro_id . '|' . $val->lot_lote] = true;
        }

        // 1 query: todas as pendentes da requisição (em vez de N queries por produto)
        $pendentes = $this->db->table('vw_est_requisicao_produto_ocorrencia_relac')
            ->where('req_id', $reqId)
            ->where('oco_id', null)
            ->get()->getResultArray();

        $cacheLotes = [];

        foreach ($pendentes as $oco) {
            $chave = $oco['pro_id'] . '|' . $oco['lot_lote'];
            if (! isset($pares[$chave])) {
                continue; // produto fora do escopo desta chamada
            }

            // lote memoizado: busca cada lot_lote no máximo uma vez
            if (! array_key_exists($oco['lot_lote'], $cacheLotes)) {
                $cacheLotes[$oco['lot_lote']] = $this->loteModel->getLoteId($oco['lot_lote'])[0] ?? null;
            }
            $lote = $cacheLotes[$oco['lot_lote']];

            $result               = $oco;            // já é array; dispensa o foreach de cópia
            $result['stt_id']     = $sttId;
            $result['usu_criou']  = $usuId;
            unset($result['oco_id']);                // garante insert limpo

            $this->db->transBegin();

            $entity = new EntOcoOcorrencia($result);
            if (! $this->ocorrenciaModel->save($entity)) {
                $this->db->transRollback();
                return [
                    'erro' => true,
                    'msg' => $this->ocorrenciaModel->errors(),
                    'criadas' => $ret['criadas'],
                    'ignoradas' => $ret['ignoradas']
                ];
            }

            $idoco = $this->ocorrenciaModel->getInsertID();

            // CLAIM ATÔMICO: só grava se ainda estiver pendente
            $this->db->table('est_requisicao_produto_ocorrencia')
                ->where('rpo_id', $oco['rpo_id'])
                ->where('oco_id', null)
                ->update(['oco_id' => $idoco]);

            if ($this->db->affectedRows() === 0) {
                // outra requisição já processou este rpo → desfaz o insert e segue
                $this->db->transRollback();
                $ret['ignoradas']++;
                continue;
            }

            // só quem venceu a corrida dispara os efeitos
            $data                 = $entity->toArray();
            $data['oco_id']       = $idoco;
            $data['lot_lote']     = $lote->lot_lote   ?? null;
            $data['lot_validade'] = $lote->lot_validade ?? null;
            $data['usu_fina']     = $usuId;
            $this->processAfterSave($data);

            $this->db->transCommit();
            $ret['criadas'][] = $idoco;
        }

        return $ret;
    }
}
