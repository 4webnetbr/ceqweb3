<?php
declare(strict_types=1);

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MongoDb;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigUsuarioModel;
use MongoDB\Driver\Query;

class CfgLoguser extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $periodo;
    public $usuario;

	private $database = 'MongoDB';
	private $collection = 'logs_acesso';
	private $conn;

    /**
     * Construtor da Tela
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->usuario   = new ConfigUsuarioModel();
		$mongodb = new MongoDb();
		$this->conn = $mongodb->getConn();

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
        $secao[0] = 'Buscar';
        $campos[0][] = $fields->log_periodo;
        $campos[0][] = $fields->log_usuario;
        $campos[0][] = $fields->log_btbu;
    
        
        $colunas = ['Data', 'Usuário', 'Tela', 'Ação','Registro', 'Descrição', 'Acesso','IP'];
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
		$filtro  		= $this->request->getPost();
		$usuario        = $filtro['usuario'];
		$periodo        = $filtro['periodo'];
		$inicio         = $filtro['inicio'];
		$fim            = $filtro['fim'];
        $dataIni        = data_db($inicio);
        $dataFim        = data_db($fim);

        $tz = new \DateTimeZone('America/Sao_Paulo');

        // data inicial
        $dataIniObj = new \DateTime($dataIni, $tz);
        $dataIniObj->setTime(0, 0, 0);

        // data final (dia seguinte, exclusivo)
        $dataFimObj = new \DateTime($dataFim, $tz);
        $dataFimObj->setTime(23, 59, 59);

        $filter = [];
        
        $filter['log_id_usuario'] = (string) $usuario;
        $filter['log_data'] = [
            '$gte' => new \MongoDB\BSON\UTCDateTime($dataIniObj->getTimestamp() * 1000),
            '$lt'  => new \MongoDB\BSON\UTCDateTime($dataFimObj->getTimestamp() * 1000),
        ];
            
        $options = [
            'sort' => ['log_data' => 1]
        ];
        $query = new \MongoDB\Driver\Query($filter, $options);

        $rows = $this->conn->executeQuery($this->database . '.' . $this->collection, $query);
        
        $dados = [];

        $map = [
            'index'  => 'Carregou',
            'lista'  => 'Listou',
            'add'    => 'Criou Novo',
            'show'   => 'Visualizou',
            'store'  => 'Gravou',
            'edit'   => 'Alteração',
            'ativinativ' => 'Ativou/Inativou',
            'delete' => 'Excluiu',
            'logon' => 'Entrou no Sistema',
        ];


        foreach ($rows as $row) {
            $dados[] = [
                'data'     => mongoDateToBr($row->log_data),
                'usuario'  => $row->log_usuario ?? '',
                'tela'     => $row->log_tela ?? '',
                'metodo'   => $map[$row->log_metodo] ?? ucfirst($row->log_metodo) ?? '',
                'titulo'   => $row->log_titulo ?? '',
                'detalhe'  => $row->log_detalhe ?? '',
                'registro'  => $row->log_registro ?? '',
                'ip'       => $row->log_ip ?? null,
                'acesso'       => $row->log_user_agent ?? null,
                ];
        }
        
        echo json_encode($dados);
                
        // if ($pro == '') {
        //     $pro = false;
        // }

        // $estoques = [];
        // // $produt = new SoapSapiens();
        // if (trim($vars['codDep']) != '') {
        //     $busca = new BuscasSapiens();
        //     $saldoest = $busca->buscaEstoqueDeposito($dep, $pro);
        //     if (is_object($saldoest)) {
        //         // Converte objeto em array
        //         $dep = $saldoest;
        //         // debug($dep);
        //         if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
        //             ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
        //         ) {
        //             if ($this->produto->getProdutoCod($dep->codigoProduto)) {
        //                 $lote = [
        //                     'codDep'      => $dep->codigoDeposito,
        //                     'Coderp'      => $dep->codigoProduto,
        //                     'DescProduto' => $dep->descricaoProduto,
        //                     'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
        //                     'lote'        => $dep->codigoLote,
        //                     'validade'    => $dep->validade,
        //                     'validadeord' => data_db($dep->validade),
        //                     'entrada'     => $dep->entrada,
        //                     'entradaord'  => data_db($dep->entrada),
        //                     'und'         => $dep->unidmedida,
        //                 ];
        //                 if ($dep->codigoLote == 'N/A') {
        //                     $lote['saldo'] = $dep->estoqueDeposito;
        //                     $lote['validade']    = '';
        //                     $lote['validadeord'] = '';
        //                     $lote['entrada']     = '';
        //                     $lote['entradaord']  = '';
        //                 } else {
        //                     $lote['saldo'] = $dep->quantidadeEstoque;
        //                 }
        //                 array_push($estoques, $lote);
        //             }
        //         }
        //     } else {
        //         // debug($saldoest, true);

        //         $total = 0;
        //         for ($d = 0; $d < sizeof($saldoest); $d++) {
        //             $dep = $saldoest[$d];
        //             // debug($dep);
        //             if ($this->produto->getProdutoCod($dep->codigoProduto)) {
        //                 if (($dep->codigoLote == 'N/A' && $dep->estoqueDeposito > 0) ||
        //                     ($dep->codigoLote != 'N/A' && $dep->quantidadeEstoque > 0)
        //                 ) {
        //                     // debug($dep, true);
        //                     $lote = [
        //                         'codDep'      => $dep->codigoDeposito,
        //                         'Coderp'      => $dep->codigoProduto,
        //                         'DescProduto' => $dep->descricaoProduto,
        //                         'Produto'     => $dep->codigoProduto . ' - ' . $dep->descricaoProduto,
        //                         'lote'        => $dep->codigoLote,
        //                         'validade'    => $dep->validade,
        //                         'validadeord' => data_db($dep->validade),
        //                         'entrada'     => $dep->entrada,
        //                         'entradaord'  => data_db($dep->entrada),
        //                         'und'         => $dep->unidmedida,
        //                     ];
        //                     if ($dep->codigoLote == 'N/A') {
        //                         $lote['saldo'] = $dep->estoqueDeposito;
        //                         $lote['validade']    = '';
        //                         $lote['validadeord'] = '';
        //                         $lote['entrada']     = '';
        //                         $lote['entradaord']  = '';
        //                     } else {
        //                         $lote['saldo'] = $dep->quantidadeEstoque;
        //                     }
        //                     array_push($estoques, $lote);
        //                 }
        //             }
        //         }
        //     }
        // }
    }

    public function defCampos()
    {
        $ret = new \stdClass();
        // USUÁRIOS
        $config = [];
        $config['Label']       = 'Usuário';
        $config['DispForm']    = 'col-5';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        
        $ret->log_usuario = criaSelectRelativo(
            'cfg_usuario',
            'usu_id',
            'usu_nome',
            null,
            1,
            'cfg_usuario',
            [],
            $config,
            'log_usuario'
        );

        // PERÍODO
        $peri               = new MyCampo();
        $peri->objeto       = 'input';
        $peri->id           = 'log_periodo';
        $peri->nome         = 'log_periodo';
        $peri->label        = 'Período';
        $peri->obrigatorio  = true;
        $peri->size         = 30;
        $peri->valor        = '';
        $peri->dispForm     = 'col-3';

        $ret->log_periodo = $peri->crDaterange();


        // BOTÃO BUSCAR
        $btbu               = new MyCampo();
        $btbu->id           = 'btBuscar';
        $btbu->nome         = 'btBuscar';
        $btbu->tipo         = 'button';
        $btbu->label        = 'Buscar';
        $btbu->dispForm     = '2col';
        $btbu->funcChan     = 'buscaLogUser()';
        $btbu->i_cone       = '<i class="fa-solid fa-magnifying-glass"></i> Buscar Log';
        $btbu->place        = 'Buscar Log';
        $btbu->classep      = 'btn-primary mt-2';

        $ret->log_btbu = $btbu->crBotao();

        return $ret;
    }

}
