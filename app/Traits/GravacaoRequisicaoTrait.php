<?php

namespace App\Traits;

use App\Libraries\Notifica;

/**
 * Trait para compartilhar métodos auxiliares entre Atendimento e Conferência
 */
trait RequisicaoHelpersTrait
{
    /**
     * Agrupa os dados do POST baseados no padrão repid_{id}
     */
    private function agruparDadosBase(array $post): array
    {
        $itens = [];
        foreach ($post as $key => $value) {
            if (preg_match('/^repid_(\d+)$/', $key, $matches)) {
                $id          = $matches[1];
                $temp        = new \stdClass();
                $temp->repid = $value;

                foreach ($post as $campo => $val) {
                    if (str_ends_with($campo, "_$id") && $campo !== "repid_$id") {
                        $nomeCampo        = substr($campo, 0, -strlen("_$id"));
                        $temp->$nomeCampo = $val;
                    }
                }

                // Campos globais comuns
                $temp->req_id = $post['req_id'] ?? null;
                $temp->tmo_id = $post['tmo_id'] ?? null;

                $itens[$id] = $temp;
            }
        }
        return $itens;
    }

    /**
     * Padroniza a criação do array de movimento de estoque
     */
    private function criarMovimento(int $tmoId, $qtia, $proId, $repId, string $reserva, string $msg = ''): array
    {
        return [
            'id'      => $tmoId,
            'qt'      => $qtia,
            'msg'     => $msg ?: ($reserva === 'D' ? 'Atendimento de Requisição' : 'Conclusão Automática de Requisição'),
            'pro_id'  => $proId,
            'rep_id'  => $repId,
            'reserva' => $reserva,
        ];
    }

    /**
     * Centraliza o disparo de notificações
     */
    private function dispararNotificacao(int $status, int $reqId): void
    {
        $nreq    = str_pad($reqId, 6, '0', STR_PAD_LEFT);
        $notif   = new Notifica();
        $usuario = session()->get('usu_id');

        $config = match ($status) {
            7       => ['dest' => 'Estoque\Requisicao', 'msg' => "A Requisição Nº $nreq foi Cancelada!"],
            25      => ['dest' => 'Preproces\InspecaoProd', 'msg' => "A Requisição Nº $nreq foi Conferida!"],
            24      => ['dest' => 'Preproces\InspecaoProd', 'msg' => "A Requisição Nº $nreq foi Conferida Parcialmente!"],
            5       => ['dest' => 'Estoque\Requisicao', 'msg' => "A Requisição Nº $nreq foi Concluída!"],
            default => ['dest' => 'Estoque\ConfRequisicao', 'msg' => "A Requisição Nº $nreq foi Atendida!"],
        };

        $notif->gravaNotifica($config['dest'], $usuario, $reqId, $config['msg'], 'C');
    }

    /**
     * Centraliza a lógica de decisão do Status da Requisição (Atendimento e Conferência)
     */
    private function calcularStatusRequisicao(int $reqId, int $tmoId, array $params): int
    {
        // 1. Regra de Cancelamento (Status 7)
        // Atendimento: qtiaSessao == 0 && !temPendente && totalAtendidoGeral == 0
        // Conferência: qtiaSessao == 0 && !parcial && totalAtendidoGeral == 0
        if (($params['qtiaSessao'] ?? 0) === 0 && ! ($params['temPendente'] ?? false) && ($params['totalAtendidoGeral'] ?? 0) === 0) {
            return 7;
        }

        // 2. Regra de Saldo Parcial (Status 21)
        $saldos     = $this->reqprodutoate->getSaldoRequisicao($reqId);
        $totalSaldo = array_sum(array_column($saldos, 'saldo'));
        if ($totalSaldo > 0) {
            return 21;
        }

        // 3. Regra de Conferência Parcial (Status 24 - Exclusivo Conferência)
        if ($params['parcial'] ?? false) {
            return 24;
        }

        // 4. Regra de Conferência/Conclusão (Status 25 ou 5)
        $tipomov = $this->tipomovimentacao->getTipoMovimentacao($tmoId)[0];
        if ($tipomov->tmo_conferencia === 'N') {
            $insvis = retornaInsVis($reqId);
            $isConcluida = ($params['contexto'] === 'conferencia')
                ? (! $insvis->temS || $insvis->temN || $tipomov->tmo_exigeinspecao == 'N')
                : ($insvis->temN || $tipomov->tmo_exigeinspecao == 'N');

            return $isConcluida ? 5 : 25;
        }

        return 18; // PADRÃO
    }
}
