<?php

namespace App\Controllers\Logistica;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Logis\LogisNotifSmsEnviadasModel;

/**
 * Consulta de SMS Enviados por período (log_notif_sms_enviadas).
 * Tela só de leitura, sem Entity própria — espelha
 * App\Controllers\Config\CfgLoguser.
 */
class NotifSmsEnviadas extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $enviadas;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->enviadas  = new LogisNotifSmsEnviadasModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $fields = $this->defCampos();

        $secao[0]    = 'Buscar';
        $campos[0][] = $fields->periodo;
        $campos[0][] = $fields->regra;
        $campos[0][] = $fields->btbu;

        $colunas = ['Data Envio', 'Chave', 'Regra'];

        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['colunas'] = $colunas;
        $this->data['destino'] = 'lista';

        echo view('vw_filtro', $this->data);
    }

    /**
     * Listagem
     * lista
     */
    public function lista()
    {
        $filtro  = $this->request->getPost();
        $periodo = $filtro['periodo'] ?? '';
        $inicio  = $filtro['inicio'] ?? '';
        $fim     = $filtro['fim'] ?? '';
        $nscId   = !empty($filtro['nse_nsc_id']) ? (int) $filtro['nse_nsc_id'] : null;

        $dataIni = data_db($inicio);
        $dataFim = data_db($fim);

        $registros = $this->enviadas->getEnviadasPeriodo($dataIni, $dataFim, $nscId);

        $dados = [];
        foreach ($registros as $reg) {
            $dados[] = [
                'data_envio' => data_br($reg['nse_data_envio']),
                'chave'      => $reg['nse_chave'],
                'regra'      => $reg['nsc_nome'],
            ];
        }

        echo json_encode($dados);
    }

    public function defCampos()
    {
        $ret = new \stdClass();

        // PERÍODO
        $peri              = new MyCampo();
        $peri->objeto      = 'input';
        $peri->id          = 'nse_periodo';
        $peri->nome        = 'nse_periodo';
        $peri->label       = 'Período';
        $peri->obrigatorio = true;
        $peri->size        = 30;
        $peri->valor       = '';
        $peri->dispForm    = 'col-3';

        $ret->periodo = $peri->crDaterange();

        // REGRA (opcional)
        $config                = [];
        $config['Label']       = 'Regra';
        $config['DispForm']    = 'col-5';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = false;

        $ret->regra = criaSelectRelativo(
            'log_notif_sms_config',
            'nsc_id',
            'nsc_nome',
            null,
            1,
            'log_notif_sms_enviadas',
            [],
            $config,
            'nse_nsc_id'
        );

        // BOTÃO BUSCAR
        $btbu           = new MyCampo();
        $btbu->id       = 'btBuscarSms';
        $btbu->nome     = 'btBuscarSms';
        $btbu->tipo     = 'button';
        $btbu->label    = 'Buscar';
        $btbu->dispForm = '2col';
        $btbu->funcChan = 'buscaSmsEnviadas()';
        $btbu->i_cone   = '<i class="fa-solid fa-magnifying-glass"></i> Buscar';
        $btbu->place    = 'Buscar SMS Enviados';
        $btbu->classep  = 'btn-primary mt-2';

        $ret->btbu = $btbu->crBotao();

        return $ret;
    }
}
