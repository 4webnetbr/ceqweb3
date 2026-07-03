<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Dashboard\EstoqueDashboardModel;

class EstoqueDashboard extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $dashboard;

    public function __construct()
    {
        $this->data = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->dashboard = new EstoqueDashboardModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    public function index()
    {
        $periodo = new MyCampo();
        $periodo->nome    = 'filtro_periodo';
        $periodo->id      = 'filtro_periodo';
        $periodo->label   = 'Período';
        $periodo->largura = 30;

        $btnFiltrar = new MyCampo();
        $btnFiltrar->nome    = 'btn_filtrar';
        $btnFiltrar->id      = 'btn_filtrar';
        $btnFiltrar->label   = '';
        $btnFiltrar->place   = 'Atualizar';
        $btnFiltrar->i_cone  = "<i class='fas fa-search me-1'></i> Atualizar";
        $btnFiltrar->classep = 'btn-primary btn-sm';

        $this->data['campos_filtro'] = [
            $periodo->crDaterange(),
            $btnFiltrar->crBotao(),
        ];

        echo view('Dashboard/vw_estoque_dashboard', $this->data);
    }

    public function dados()
    {
        $post = $this->request->getPost();
        // debug($post);

        $dataIni = $post['data_ini'] ?? date('Y-m-01');
        $dataFim = $post['data_fim'] ?? date('Y-m-d');

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataIni)) {
            $dataIni = \DateTime::createFromFormat('d/m/Y', $dataIni)->format('Y-m-d');
        }
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataFim)) {
            $dataFim = \DateTime::createFromFormat('d/m/Y', $dataFim)->format('Y-m-d');
        }

        $perfilId = (int) session()->get('usu_perfil_id');
        $tmoIds   = $this->dashboard->getTmoIdsPorPerfil($perfilId);

        return $this->response->setJSON([
            'curva_abc'            => $this->dashboard->getCurvaAbc($dataIni, $dataFim, $tmoIds),
            'solicitado_atendido'  => $this->dashboard->getSolicitadoVsAtendido($dataIni, $dataFim, $tmoIds),
            'deposito_origem'      => $this->dashboard->getPorDepositoOrigem($dataIni, $dataFim, $tmoIds),
            'deposito_destino'     => $this->dashboard->getPorDepositoDestino($dataIni, $dataFim, $tmoIds),
            'evolucao_temporal'    => $this->dashboard->getEvolucaoTemporal($dataIni, $dataFim, $tmoIds),
            'status_atual'         => $this->dashboard->getStatusAtual($dataIni, $dataFim, $tmoIds),
            'sla_atendimento'      => $this->dashboard->getSlaAtendimento($dataIni, $dataFim, $tmoIds),
        ]);
    }
}
