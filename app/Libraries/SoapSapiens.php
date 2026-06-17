<?php

namespace App\Libraries;

use SoapClient;
use App\Models\MovimMonModel;

class SoapSapiens
{
    public $soapc;

    public function __construct($servico = false)
    {
        if ($servico) {
            $options = [
                'connection_timeout' => 5, // segundos
                'exceptions' => true,      // permite capturar falhas com try/catch
                'trace' => true            // útil para depuração (opcional)
            ];
            $serv = 'http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_' . $servico . '?wsdl';
            // debug($serv, true);
            $this->soapc = new SoapClient($serv, $options);
            // debug($this->soapc);
        }
    }

    public function transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida, $msg = '')
    {
        $options = [
            'connection_timeout' => 5, // segundos
            'exceptions' => true,      // permite capturar falhas com try/catch
            'trace' => true            // útil para depuração (opcional)
        ];
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_est_estoques?wsdl', $options);
        #Operação a ser executada
        $function = 'TransferenciaProdutos';
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'transferenciaEntreProdutosSaida' => array(
                    'codEmp'   => 1,
                    'codFil'   => 1,
                    'codPro'   => $codpro,
                    'codDer'   => '',
                    'codTns'   => $codtns,
                    'codDep'   => $depori,
                    'datMov'   => $datmov,
                    'qtdMov'   => $qtdmov,
                    'vlrMov'   => 1,
                    'codLot'   => array(
                        'codLot'   => $codlot,
                        'qtdEst'   => $qtdmov,
                    ),
                ),
                'transferenciasEntreProdutosEntrada' => array(
                    'codPro'   => $codpro,
                    'codDer'   => '',
                    'codTns'   => $codtns,
                    'codDep'   => $depdes,
                    'qtdMov'   => $qtdmov,
                    'vlrMov'   => 1,
                    'codLot'   => array(
                        'codLot'   => $codlot,
                        'qtdEst'   => $qtdmov,
                        'datVlt'   => $valida,
                    ),
                ),
            )
        );
        #Sobrescrevendo endpoint do serviço
        $arguments = array('TransferenciaProdutos' => array($parameters));
        // echo "<pre>";
        // print_r($parameters);
        // echo "</pre>";

        $options = array('location' => 'http://services.senior.com.br');

        $msgretorno = '';
        $status = '';
        #Chamada do serviço
        try {

            $result = $client->__soapCall($function, $parameters);

            log_message('info', 'TransferenciaProdutos parametros ' . json_encode($parameters));
            log_message('info', 'TransferenciaProdutos resposta ' . json_encode($result));
            // debug($result, true);
            $status = 'OK';
            if (is_array($result) && isset($result['mensagemRetorno'])) {
                $msgretorno = $result['mensagemRetorno'];
            } elseif (is_object($result) && isset($result->mensagemRetorno)) {
                $msgretorno = $result->mensagemRetorno;
            }
            // $msgretorno = $result->mensagemRetorno;
            if ($result->tipoRetorno > 1) {
                $status = 'Erro';
            }
            // sucesso
            log_message('info', 'SOAP retorno OK');

            $ret = [
                'status' => $status,
                'mensagem' => $msgretorno,
            ];
        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            $msgretorno = 'Erro SOAP: ' . $e->getMessage();
            $status = 'Erro';
            log_message('error', 'Erro SOAP: ' . $e->getMessage());
            $ret = [
                'status' => $status,
                'mensagem' => $msgretorno,
            ];
        }

        $movdb = new MovimMonModel();
        if ($depdes == null || $depdes == '') {
            $depdes = 'Baixa';
        }
        $movim = $movdb->insertMovimento($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida, $status, $msg . '<br>' . $msgretorno);
        return $ret;
    }

    public function movimProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida, $msg = '')
    {
        $options = [
            'connection_timeout' => 5, // segundos
            'exceptions' => true,      // permite capturar falhas com try/catch
            'trace' => true            // útil para depuração (opcional)
        ];
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_est_estoques?wsdl', $options);
        #Operação a ser executada
        $function = 'MovimentarEstoque';
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'dadosGerais' => array(
                    'codEmp'   => 1,
                    'codFil'   => 1,
                    'codPro'   => $codpro,
                    'codDer'   => '',
                    'codTns'   => $codtns,
                    'codDep'   => $depori,
                    'codTns'   => $codtns,
                    'codLot'   => $codlot,
                    'qtdMov'   => $qtdmov,
                    'datMov'   => $datmov,
                ),
            ),
        );
        #Sobrescrevendo endpoint do serviço
        // $arguments = array('MovimentarEstoque' => array($parameters));

        // $options = array('location' => 'http://services.senior.com.br');

        $msgretorno = '';
        $status = '';
        #Chamada do serviço
        try {

            $result = $client->__soapCall($function, $parameters);

            log_message('info', 'MovimentarEstoque parametros ' . json_encode($parameters));
            log_message('info', 'MovimentarEstoque resposta ' . json_encode($result));
            // debug($result, true);
            $status = 'OK';
            if (is_array($result) && isset($result['mensagemRetorno'])) {
                $msgretorno = $result['mensagemRetorno'];
            } elseif (is_object($result) && isset($result->mensagemRetorno)) {
                $msgretorno = $result->mensagemRetorno;
            }
            // $msgretorno = $result->mensagemRetorno;
            if ($result->tipoRetorno > 1) {
                $status = 'Erro';
            }
            // sucesso
            log_message('info', 'SOAP retorno OK');

            $ret = [
                'status' => $status,
                'mensagem' => $msgretorno,
            ];
        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            $msgretorno = 'Erro SOAP: ' . $e->getMessage();
            $status = 'Erro';
            log_message('error', 'Erro SOAP: ' . $e->getMessage());
            $ret = [
                'status' => $status,
                'mensagem' => $msgretorno,
            ];
        }

        $movdb = new MovimMonModel();
        $depdes = 'Baixa';
        $movim = $movdb->insertMovimento($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes, $valida, $status, $msg . '<br>' . $msgretorno);
        return $ret;
    }

    public function clientesSapiens()
    {
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_cad_clientes?wsdl');
        #Operação a ser executada
        $function = 'obterCliente';
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'Smart2',
            'password'        => 'omyjano1',
            'encryption'      => 0,
            'parameters'      => array(
                'codigoEmpresa'   => 1,
                'codigoFilial'    => 1,
                'codigoCliente'   => 8
            )
        );
        #Sobrescrevendo endpoint do serviço
        $arguments = array('obterCliente' => array($parameters));

        $options = array('location' => 'http://services.senior.com.br');

        #Chamada do serviço
        $result = $client->__soapCall($function, $parameters);

        echo 'Response: ';
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    public function empresasSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
            )
        );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function depositosSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
                'codFil'    => 1,
            )
        );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function origemSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
            )
        );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function familiaSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
            )
        );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function fabricanteSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array()
        );
        #Sobrescrevendo endpoint do serviço

        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }

    public function produtosSapiens($funcao, $codEmp = 1, $codPro = '')
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => $codEmp,
            )
        );
        if ($codPro != '') {
            $parameters['parameters']['codPro'] = $codPro;
        }
        #Sobrescrevendo endpoint do serviço

        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }

    public function lotesSapiens($funcao, $codLot = '')
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
        );
        if ($codLot != '') {
            $parameters['parameters']['codLot'] = $codLot;
        }

        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // debug($e->getMessage());
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }

    public function estoquePorDeposito($deposito, $codPro = false)
    {
        #Operação a ser executada
        $function = 'EstoqueporDeposito';
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'    => 1,
                'codFil'    => 1,
                'codDep'    => $deposito,
            )
        );
        if ($codPro != '') {
            $parameters['parameters']['codPro'] = $codPro;
        }

        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }

    public function transacoesEstoque($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'IntCeqweb',
            'password'        => 'soPR#JOV@omVs',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
            )
        );
        #Sobrescrevendo endpoint do serviço

        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }

    public function saldoEstoqueSapiensLista()
    {
        #Operação a ser executada
        $function = 'Exportar_3';
        #Montando o payload de requisição
        $parameters = array(
            'user'            => 'Smart2',
            'password'        => 'omyjano1',
            'encryption'      => 0,
            'parameters'      => array(
                'codEmp'   => 1,
                'codFil'    => 1,
                'identificadorSistema'    => 'CEQWEB3',
                'quantidadeRegistros' => 1000,
                'tipoIntegracao'    => 'T'
            )
        );
        #Chamada do serviço
        try {
            $result = $this->soapc->__soapCall($function, $parameters);
            log_message('info', 'SOAP retorno OK');
            $this->soapc = null;
            return $result;

            // sucesso

        } catch (\SoapFault $e) {
            // falha de conexão ou erro na resposta
            log_message('error', 'Erro SOAP: ' . $e->getMessage());

            // se quiser, pode lançar exceção ou retornar erro customizado
            $ret = [
                'erro' => 'Falha na comunicação com o serviço SOAP',
                'mensagem' => $e->getMessage()
            ];
            return (object) $ret;
        }
    }
}
