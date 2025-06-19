<?php

namespace App\Libraries;

use SoapClient;

class SoapSapiens
{
    public $soapc;

    public function __construct($servico = false)
    {
        if ($servico) {
            $serv = 'http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_' . $servico . '?wsdl';
            // debug($serv, true);
            $this->soapc = new SoapClient($serv);
            // debug($this->soapc);
        }
    }

    public function transfProdutosSapiens($codpro, $codtns, $depori, $datmov, $qtdmov, $codlot, $depdes)
    {
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_est_estoques?wsdl');
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

        #Chamada do serviço
        $result = $client->__soapCall($function, $parameters);

        log_message('info', 'TransferenciaProdutos parametros ' . json_encode($parameters));
        log_message('info', 'TransferenciaProdutos resposta ' . json_encode($result));

        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
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

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
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

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
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
        #Sobrescrevendo endpoint do serviço
        // debug($parameters, true);
        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
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

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;

        return $result;
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

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
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
        $result = $this->soapc->__soapCall($function, $parameters);

        return $result;
    }
}
