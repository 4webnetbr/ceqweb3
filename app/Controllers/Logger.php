<?php
namespace App\Controllers;

use App\Libraries\Campos;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\LogMonModel;

class Logger extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $novopedido;

    public function __construct()
    {
        $this->data  = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    public function show($tabela, $registro)
    {
        $logs      = new LogMonModel();
        $dicionario = new ConfigDicDadosModel();

        $logId = $logs->get_logs_all($tabela, $registro);
        $dados = [];
        if ($logId) {
            $campos = $logId[0]->log_dados;
            $arr_lista  = [];
            foreach ($campos as $field => $value) {
                array_push($arr_lista, $field);
            }
            $nomes_campos = $dicionario->getDetalhesCampo($tabela, $arr_lista);
            $campo_nome = array_column($nomes_campos, 'COLUMN_COMMENT', 'COLUMN_NAME');
            // debug($campo_nome);
            foreach ($logId as $document) {
                $dad = [];
                $dad['operacao']      = $document->log_operacao;
                $dad['usua_alterou']  = $document->log_id_usuario;
                $dad['data_alterou']  = data_br($document->log_data);
                $dad['dados'] = [];
                $field = [];
                if ($document->log_dados != null) {
                    foreach ($document->log_dados as $key => $value) {
                        if (isset($campo_nome[$key])) {
                            $field[$campo_nome[$key]] = $value;
                        }
                        // debug($field);
                    }
                }
                array_push($dad['dados'], $field);
                $dad['tabela']        = $tabela;
                $dad['registro']      = $registro;
                array_push($dados, $dad);
            }
        }
        asort($dados);
        $this->data['dados'] = $dados;
        // debug($dados);

        return view('vw_logger', $this->data);
    }
}
