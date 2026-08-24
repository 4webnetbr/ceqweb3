<?php

namespace App\Controllers\Logistica;

use App\Models\CommonModel;
use App\Entities\Logistica\EntLogNotifSmsConfig;
use App\Controllers\BaseController;
use App\Models\Logis\LogisNotifSmsConfigModel;

/**
 * CRUD de Regras de Notificação SMS (log_notif_sms_config).
 * Espelha App\Controllers\Config\CfgCor.
 */
class NotifSmsConfig extends BaseController
{
    public $permissao = '';
    public $regras;
    public $common;

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->regras    = new LogisNotifSmsConfigModel();
        $this->common    = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'nsc_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $campos       = montaColunasCampos($this->data, 'nsc_id');
        $dados_regras = $this->regras->getListaRegras();
        foreach ($dados_regras as $regra) {
            if ($regra->nsc_ativo == 'I') {
                $regra->stt_consulta = 'N';
                $regra->stt_edicao   = 'N';
            }
        }
        $this->data['exclusao'] = true;
        $regras = [
            'data' => montaListaColunasEnt($this->data, 'nsc_id', $dados_regras, $campos[1]),
        ];

        echo json_encode($regras);
    }

    public function ativinativ($id, $tipo)
    {
        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'nsc_ativo' => 'A',
                ];
            } else {
                $dad_atin = [
                    'nsc_ativo' => 'I',
                ];
                // Permite inativar livremente, mesmo havendo histórico em
                // log_notif_sms_enviadas (só a exclusão é bloqueada — ver delete()).
            }
            $this->regras->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Regra Alterada com Sucesso');
            $ret['msg'] = 'Regra Alterada com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14;
        }
        echo json_encode($ret);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $regra = new EntLogNotifSmsConfig();

        $this->data['secoes']  = ['Dados Gerais'];
        $this->data['campos']  = [$this->montaCamposFormulario($regra)];
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Mostrar Registro
     * show
     *
     * @param mixed $id
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id
     * @return void
     */
    public function edit($id, $show = false)
    {
        $regra = $this->regras->getRegra($id);

        if (!$regra) {
            return redirectWithError($this->data['controler'], 41);
        }
        $regra->campos = $regra->defCampos($show);

        $this->data['secoes']  = ['Dados Gerais'];
        $this->data['campos']  = [$this->montaCamposFormulario($regra)];
        $this->data['destino'] = 'store';

        $this->data['log'] = buscaLog('log_notif_sms_config', $id);

        // Estado inicial do toggle de campos condicionais (RN — mesmo valor
        // salvo em nsc_tipo_regra), disparado no carregamento da tela.
        $this->data['script'] = "<script>jQuery(function(){ alternaCamposTipoRegra(document.getElementById('nsc_tipo_regra')); });</script>";

        echo view('vw_edicao', $this->data);
    }

    /**
     * Monta o array de campos da seção "Dados Gerais", envolvendo os
     * campos condicionais por nsc_tipo_regra nos wrappers
     * #divSaldo/#divApi/#divConsulta (mesmo mecanismo de div manual já
     * usado antes deste ciclo para #divEntrega/#divSaldo).
     *
     * #divSaldo já nasce SEM a classe "d-none" porque o novo default de
     * nsc_tipo_regra é 'saldo_baixo' (EntLogNotifSmsConfig::$attributes,
     * seção 4.5) — o estado HTML inicial já reflete isso tanto em add()
     * quanto em edit(), sem depender de JS de inicialização. #divApi e
     * #divConsulta nascem com "d-none". Em edit(), quando o registro salvo
     * é 'api'/'consulta', o script disparado no carregamento da tela (ver
     * edit()) corrige o toggle via alternaCamposTipoRegra() (seção 4.16),
     * que também usa a classe "d-none" (não jQuery.toggle()) para poder
     * alternar livremente a partir daqui.
     */
    private function montaCamposFormulario(EntLogNotifSmsConfig $regra): array
    {
        return [
            $regra->campos['nsc_id'],
            $regra->campos['nsc_nome'],
            $regra->campos['nsc_tipo_regra'],
            '<div id="divSaldo" class="row col-12 float-start">'
                . $regra->campos['nsc_saldo_minimo']
                . '</div>',
            '<div id="divApi" class="row col-12 float-start d-none">'
                . $regra->campos['nsc_metodo_api']
                . '</div>',
            '<div id="divConsulta" class="row col-12 float-start d-none">'
                . $regra->campos['nsc_view_consulta']
                . '</div>',
            $regra->campos['nsc_telefones'],
            $regra->campos['nsc_mensagem_template'],
            $regra->campos['nsc_ativo'],
        ];
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        $ret = [];

        try {
            // Bloqueia exclusão se houver histórico de envio associado.
            // Check direto e escopado ao dbLogistica (via o Model já
            // instanciado em $this->regras), em vez do
            // ForeignKeyUsageChecker compartilhado — ver
            // docs/revisao/notificacoes-sms-revisao-01 (Bloqueante 1 /
            // Sugestão 6): o trait varre TODOS os schemas cadastrados a
            // cada delete() do sistema inteiro, o que é uma regressão de
            // performance em código compartilhado por 20+ Controllers, e
            // além disso buscava a coluna errada ('nsc_id' em vez da FK
            // real 'nse_nsc_id'), nunca bloqueando a exclusão.
            $emUso = $this->regras->builder('log_notif_sms_enviadas')
                ->where('nse_nsc_id', (int) $id)
                ->countAllResults();

            if ($emUso > 0) {
                throw new \Exception('Regra em uso em log_notif_sms_enviadas.');
            }

            $this->regras->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Regra Excluída com Sucesso');
        } catch (\Exception $e) {
            // Mesmo padrão de CfgCor/ProClasse/CfgStatus::delete() —
            // mensagem centralizada via cfg_mensagem (código 3), não
            // string solta do $e->getMessage().
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret         = [];
        $ret['erro'] = false;
        $postado     = $this->request->getPost();

        // Revalidação da fonte de verdade corrente para os tipos 'api' e
        // 'consulta' (defesa em profundidade — nunca confia apenas na
        // validação de cadastro em LogisNotifSmsConfigModel, seção 4.14).
        if (($postado['nsc_tipo_regra'] ?? '') === 'api') {
            $classeApi          = SMS_API_CONSUMER_CLASS;
            $metodosDisponiveis = $classeApi::metodosDisponiveis();

            if (!array_key_exists($postado['nsc_metodo_api'] ?? '', $metodosDisponiveis)) {
                $ret['erro'] = true;
                $ret['msg']  = 'Método de API selecionado não é mais válido.';
                echo json_encode($ret);
                return;
            }
        }

        if (($postado['nsc_tipo_regra'] ?? '') === 'consulta') {
            // nsc_view_consulta chega do POST como valor composto
            // "dbGroup|view" (select montado em
            // EntLogNotifSmsConfig::defCampos(), seção 4.12). Faz o split
            // e SÓ ENTÃO revalida contra a fonte de verdade corrente
            // (getViewsPorPrefixo()) antes de gravar — nunca confia no
            // valor bruto do POST para nsc_view_dbgroup (poderia ser
            // manipulado no client).
            $valorComposto = (string) ($postado['nsc_view_consulta'] ?? '');
            $partes        = explode('|', $valorComposto, 2);
            $dbGroupPost   = $partes[0] ?? '';
            $viewPost      = $partes[1] ?? '';

            $admDados = new \App\Models\Config\ConfigDicDadosModel();
            $valido   = false;
            foreach ($admDados->getViewsPorPrefixo('vw_sms_') as $v) {
                if ($v['dbGroup'] === $dbGroupPost && $v['view'] === $viewPost) {
                    $valido = true;
                    break;
                }
            }

            if (!$valido) {
                $ret['erro'] = true;
                $ret['msg']  = 'View de consulta selecionada não é mais válida.';
                echo json_encode($ret);
                return;
            }

            // Só grava nsc_view_dbgroup/nsc_view_consulta com os valores já
            // revalidados acima, nunca com o valor bruto do POST.
            $postado['nsc_view_dbgroup']  = $dbGroupPost;
            $postado['nsc_view_consulta'] = $viewPost;
        }

        $regra = new EntLogNotifSmsConfig($postado);

        $exists = $this->common->verificaUnico($this->regras, 'nsc_nome', $postado['nsc_nome'], 'nsc_id', $postado['nsc_id']);

        if (intval($exists) > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 9;
        } else {
            $this->regras->transBegin();

            try {
                if (!$this->regras->save($regra)) {
                    throw new \Exception(implode(' ', $this->regras->errors()));
                }
                $this->regras->transCommit();
                session()->setFlashdata('msg', 'Regra gravada com Sucesso!!!');

                $ret = [
                    'erro' => false,
                    'msg'  => 'Regra gravada com Sucesso!!!',
                    'url'  => site_url($this->data['controler']),
                ];
            } catch (\Throwable $e) {
                $this->regras->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Erro ao salvar regra.',
                ];
            }
        }
        echo json_encode($ret);
    }
}
