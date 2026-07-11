<?php

namespace App\Models\Dashboard;

use CodeIgniter\Model;

class EstoqueDashboardModel extends Model
{
    protected $DBGroup = 'dbEstoque';

    public function getTmoIdsPorPerfil(int $perfilId): array
    {
        $db = db_connect('dbEstoque');
        return array_column(
            $db->table('est_tipo_movimentacao_permissao')
                ->select('tmo_id')
                ->where('prf_id', $perfilId)
                ->get()->getResultArray(),
            'tmo_id'
        );
    }

    public function getCurvaAbc(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $rows = $db->table('est_requisicao_produto rep')
            ->select('rep.pro_id, SUM(rep.rep_quantia) as total_solicitado')
            ->join('est_requisicao r', 'r.req_id = rep.req_id')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->groupBy('rep.pro_id')
            ->orderBy('total_solicitado', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $proIds = array_column($rows, 'pro_id');
        $dbProd = db_connect('dbProduto');
        $produtos = $dbProd->table('pro_sap_produto')
            ->select('pro_id, pro_despro, pro_codpro')
            ->whereIn('pro_id', $proIds)
            ->get()->getResultArray();

        $prodMap = array_column($produtos, null, 'pro_id');

        $totalGeral = array_sum(array_column($rows, 'total_solicitado'));
        $acumulado  = 0;
        $result     = [];

        foreach ($rows as $row) {
            $acumulado += $row['total_solicitado'];
            $pctAcum    = $totalGeral > 0 ? round($acumulado / $totalGeral * 100, 2) : 0;
            $classe     = $pctAcum <= 80 ? 'A' : ($pctAcum <= 95 ? 'B' : 'C');

            $result[] = [
                'pro_id'           => $row['pro_id'],
                'pro_codpro'       => $prodMap[$row['pro_id']]['pro_codpro'] ?? '',
                'pro_despro'       => $prodMap[$row['pro_id']]['pro_despro'] ?? 'Produto ' . $row['pro_id'],
                'total_solicitado' => (float) $row['total_solicitado'],
                'pct_acumulado'    => $pctAcum,
                'classe_abc'       => $classe,
            ];
        }

        return $result;
    }

    public function getSolicitadoVsAtendido(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $row = $db->table('est_requisicao_produto rep')
            ->select('
                SUM(rep.rep_quantia)    as total_solicitado,
                SUM(rpa.rpa_cancelada)  as total_cancelado,
                SUM(rpa.rpa_atendida)   as total_atendido,
                SUM(CASE WHEN rpa.rpa_conferida = -1 THEN rpa.rpa_atendida ELSE rpa.rpa_conferida END) as total_conferido,
                SUM(CASE WHEN rpa.rpa_aprovada = -1 THEN
                        (CASE WHEN rpa.rpa_conferida = -1 THEN rpa.rpa_atendida ELSE rpa.rpa_conferida END)
                    ELSE rpa.rpa_aprovada END) as total_aprovado
            ')
            ->join('est_requisicao r', 'r.req_id = rep.req_id')
            ->join('est_requisicao_produto_atendimento rpa', 'rpa.rep_id = rep.rep_id', 'left')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->get()->getRowArray();

        return $row ?? [];
    }

    public function getPorDepositoOrigem(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        return $this->_getPorDeposito($dataIni, $dataFim, $tmoIds, 'req_deporigem');
    }

    public function getPorDepositoDestino(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        return $this->_getPorDeposito($dataIni, $dataFim, $tmoIds, 'req_depdestino');
    }

    private function _getPorDeposito(string $dataIni, string $dataFim, array $tmoIds, string $campo): array
    {
        $db = db_connect('dbEstoque');
        return $db->table('est_requisicao_produto rep')
            ->select("r.{$campo} as dep_cod, d.dep_desDep as dep_nome, SUM(rep.rep_quantia) as total_solicitado")
            ->join('est_requisicao r', 'r.req_id = rep.req_id')
            ->join('est_sap_deposito d', "d.dep_codDep = r.{$campo}", 'left')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->groupBy("r.{$campo}, d.dep_desDep")
            ->orderBy('total_solicitado', 'DESC')
            ->get()->getResultArray();
    }

    public function getEvolucaoTemporal(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $rows = $db->table('est_requisicao r')
            ->select('DATE(r.req_data) as data_dia, COUNT(DISTINCT r.req_id) as total_requisicoes')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->groupBy('DATE(r.req_data)')
            ->orderBy('data_dia', 'ASC')
            ->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $media = round(array_sum(array_column($rows, 'total_requisicoes')) / count($rows), 2);

        foreach ($rows as &$row) {
            $row['media_requisicoes'] = $media;
        }

        return $rows;
    }

    public function getStatusAtual(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $rows = $db->table('est_requisicao r')
            ->select('r.stt_id, COUNT(r.req_id) as total')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->groupBy('r.stt_id')
            ->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $sttIds  = array_column($rows, 'stt_id');
        $dbConf  = db_connect('default');
        $status  = $dbConf->table('vw_cfg_status_relac')
            ->select('stt_id, stt_nome, cor_valorrgb, stt_ordem')
            ->whereIn('stt_id', $sttIds)
            ->get()->getResultArray();

        $sttMap = array_column($status, null, 'stt_id');

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'stt_id'       => $row['stt_id'],
                'stt_nome'     => $sttMap[$row['stt_id']]['stt_nome'] ?? 'Status ' . $row['stt_id'],
                'cor_valorrgb' => $sttMap[$row['stt_id']]['cor_valorrgb'] ?? '#6c757d',
                'stt_ordem'    => $sttMap[$row['stt_id']]['stt_ordem'] ?? 0,
                'total'        => (int) $row['total'],
            ];
        }

        usort($result, fn($a, $b) => $a['stt_ordem'] <=> $b['stt_ordem']);

        return $result;
    }

    public function getSlaAtendimento(string $dataIni, string $dataFim, array $tmoIds): array
    {
        if (empty($tmoIds)) {
            return [];
        }

        $db = db_connect('dbEstoque');
        $rows = $db->table('est_requisicao r')
            ->select('r.tmo_id, AVG(TIMESTAMPDIFF(HOUR, r.req_data, rpa.rpa_data)) as sla_horas')
            ->join('est_requisicao_produto rep', 'rep.req_id = r.req_id')
            ->join('est_requisicao_produto_atendimento rpa', 'rpa.rep_id = rep.rep_id')
            ->where('r.req_data >=', $dataIni . ' 00:00:00')
            ->where('r.req_data <=', $dataFim . ' 23:59:59')
            ->whereIn('r.tmo_id', $tmoIds)
            ->where('rpa.rpa_data IS NOT NULL', null, false)
            ->groupBy('r.tmo_id')
            ->orderBy('sla_horas', 'DESC')
            ->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $tmoIds2 = array_column($rows, 'tmo_id');
        $dbEst   = db_connect('dbEstoque');
        $tipos   = $dbEst->table('vw_est_tipo_movimentacao_relac_lista')
            ->select('tmo_id, tmo_nome')
            ->whereIn('tmo_id', $tmoIds2)
            ->get()->getResultArray();

        $tmoMap = array_column($tipos, null, 'tmo_id');

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'tmo_id'    => $row['tmo_id'],
                'tmo_nome'  => $tmoMap[$row['tmo_id']]['tmo_nome'] ?? 'Tipo ' . $row['tmo_id'],
                'sla_horas' => round((float) $row['sla_horas'], 1),
            ];
        }

        return $result;
    }
}
