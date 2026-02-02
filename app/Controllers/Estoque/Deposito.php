<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\Ws\WsCeqweb;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Entities\Estoque\EntDeposito;

class Deposito extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $depositos;

    /**
    * Construtor da Tela
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->depositos = new EstoquDepositoModel();

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
        $integ = new WsCeqweb();
        $integ->integraDeposito();

        $this->data['colunas'] = montaColunasLista($this->data, 'dep_codDep,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
    * Listagem
    * lista
    */
    public function lista()
    {  
            $campos = montaColunasCampos($this->data, 'dep_codDep');
            $dados_tela = $this->depositos->getDeposito();
            // debug($dados_tela, true);
            $this->data['exclusao'] = false;
            $depositos = [
                'data' => montaListaColunasEnt($this->data, 'dep_codDep', $dados_tela, $campos[1]),
            ];
            cache()->save('depositos', $depositos, 60000);
        echo json_encode($depositos);
    }

    public function show($id)
    {
        // Busca os dados do depósito pelo ID
        $dados_depositos = $this->depositos->getDeposito($id);
    
         // Cria a Entity EntDeposito a partir dos dados retornados
        /** @var EntDeposito $entity */
        $entity = new EntDeposito((array) $dados_depositos[0], true);
    
        $fields = $entity->defCampos((array) $dados_depositos[0], true);
    
        // Define a seção da tela
        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields->dep_codDep; 
        $campos[0][1] = $fields->dep_desDep;
        $campos[0][2] = $fields->dep_aceNeg;
        $campos[0][3] = $fields->dep_codDescricao;
        $campos[0][4] = $fields->dep_padrao;
        $campos[0][5] = $fields->dep_reserva;
            
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = '';
        $this->data['log']     = buscaLog('est_sap_deposito', $id);
    
        // Renderiza a view de edição
        echo view('vw_edicao', $this->data);
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
        // Busca os dados do depósito pelo ID
        $dados_depositos = $this->depositos->getDeposito($id);
    
        // Cria a Entity EntDeposito em modo edição
        /** @var EntDeposito $entity */
        $entity = new EntDeposito((array) $dados_depositos[0], true);
    
        // Gera os campos da tela via Entity
        $fields = $entity->defCampos((array) $dados_depositos[0], true);
    
        // Define a seção da tela
        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields->dep_codDep; 
        $campos[0][1] = $fields->dep_desDep;
        $campos[0][2] = $fields->dep_aceNeg;
        $campos[0][3] = $fields->dep_codDescricao;
        $campos[0][4] = $fields->dep_padrao;
        $campos[0][5] = $fields->dep_reserva;
            
        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('est_sap_deposito', $id);
    
        echo view('vw_edicao', $this->data);
    }

    /**
    * Gravação
    * store
    *
    * @return void
    */
    public function store()
    {
        // Inicializa retorno padrão
        $ret = [];
        $ret['erro'] = false;
    
        // Recupera dados enviados via POST e converte para objeto
        /** @var object $postado */
        $postado = (object) $this->request->getPost();
    
        // Inicia transação no banco
        $this->depositos->transBegin();
        $erros = [];
    
        // Se o depósito for definido como padrão,
        if (isset($postado->dep_padrao) && $postado->dep_padrao === 'S') {
            // Atualiza todos os registros para dep_padrao 'N'
            $this->depositos
                ->where('dep_padrao', 'S')
                ->set(['dep_padrao' => 'N'])
                ->update();
        }
    
        // Cria a Entity EntDeposito com os dados que serão gravados
        /** @var EntDeposito $entity */
        $entity = new EntDeposito([
            'dep_codDep'  => $postado->dep_codDep,
            'dep_padrao'  => $postado->dep_padrao,
            'dep_reserva' => $postado->dep_reserva,
        ]);
    
        // Salva os dados utilizando o Model 
        if ($this->depositos->save($entity)) {
            $this->depositos->transCommit();
            $ret['msg'] = 'Depósito atualizado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            // senão desfaz a transação em caso de erro
            $this->depositos->transRollback();
            $ret['msg'] = 'Não foi possível atualizar o Depósito, Verifique!<br><br>';
    
            // Concatena possíveis erros
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
                if (is_numeric($erro)) {
                    $ret['msg'] = $erro;
                }
            }
        }
        echo json_encode($ret);
    }
}
