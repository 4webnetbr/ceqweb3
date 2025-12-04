<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Controllers\BuscasSapiens;

class OcoNovOcorrencia extends BaseController
{
    public $data      = [];
    public $permissao = '';
    public $model_atual;
    public $tipoocorrencia;
    public $modocorrencia;
    public $common;

    public function __construct()
    {
        $this->data = session()->getFlashdata('dados_tela') ?? [];
        $this->permissao = $this->data['permissao'] ?? '';
    
        if (empty($this->data['tabela'])) {
            $this->data['tabela'] = 'oco_nov_ocorrencia';
        }
    
        $this->tipoocorrencia = new \App\Models\Ocorre\OcorreTipoOcorrenciaModel();
        $this->modocorrencia  = new \App\Models\Ocorre\OcorreModOcorrenciaModel();
        $this->common         = new \App\Models\CommonModel();
    
        // Trata erro de acesso
        if (!empty($this->data['erromsg'])) {
            $this->__erro();
        }
    }


    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    
    // Redireciona para o formulário diretamente
    public function index()
    {
        return redirect()->to(base_url('OcoNovOcorrencia/add'));
    }

    
    // Formulário de inclusão
    public function add()
    {
        helper('form');

        $this->data['tabela'] = 'oco_nov_ocorrencia';
        session()->setFlashdata('dados_tela', $this->data);

        $secao[0] = 'Dados Gerais';
        $fields   = [];


        // CAMPO: TIPO DE OCORRÊNCIA
        $tipo = new MyCampo('oco_nov_ocorrencia', 'oco_tipo');
        $tipo->nome    = 'oco_tipo';
        $tipo->id      = 'oco_tipo';
        $tipo->valor   = '';
        $tipo->objeto  = 'oco_tipo';
        $tipo->largura = 58;

        $tipo->label       = 'Tipo de Ocorrência';
        $tipo->obrigatorio = true;
        $tipo->tipo        = 'select';

        // Instancia o model da T10
        $modelTipo = new OcorreTipoOcorrenciaModel();
        // Busca os dados do banco
        $tipos = $modelTipo->findAll();
        // Prepara as opções do select
        $opcoes = ['' => 'Selecione o tipo de ocorrência'];
        foreach ($tipos as $tipo_oc) {
            $opcoes[$tipo_oc['tpo_id']] = $tipo_oc['tpo_nome'];
        }
        // Atribui ao campo
        $tipo->opcoes       = $opcoes;
        $tipo->dispForm     = '2col';
        $fields['oco_tipo'] = $tipo->crSelect();


        // CAMPO: OCORRÊNCIA 
        $quebra = new MyCampo('oco_nov_ocorrencia', 'oco_quebra_tipo');
        $quebra->nome    = 'oco_quebra_tipo';
        $quebra->id      = 'oco_quebra_tipo';
        $quebra->valor   = '';
        $quebra->objeto  = 'oco_quebra_tipo';
        $quebra->largura = 57;
        
        $quebra->label       = 'Ocorrência';
        $quebra->obrigatorio = true;
        $quebra->tipo        = 'select';
        
        // Busca dados da T9
        $modelMod = new OcorreModOcorrenciaModel();
        $modelos  = $modelMod->getModOcorrencia();
        
        // Prepara as opções
        $opcoesMod = ['' => 'Selecione a ocorrência'];
        foreach ($modelos as $mod) {
            $opcoesMod[$mod['moc_id']] = $mod['moc_nome'];
        }
        
        $quebra->opcoes   = $opcoesMod;
        $quebra->dispForm = '2col';
        
        $fields['oco_quebra'] = $quebra->crSelect();


        //  CAMPO: DESCRIÇÃO
        $desc = new MyCampo('oco_nov_ocorrencia', 'oco_descricao');
        $desc->nome    = 'oco_descricao';
        $desc->id      = 'oco_descricao';
        $desc->valor   = '';
        $desc->objeto  = 'oco_descricao';
        $desc->place   = 'Digite a descrição da ocorrência';

        $desc->label        = 'Descrição';
        $desc->obrigatorio  = true;
        $desc->linhas       = 3;
        $desc->colunas      = 56;
        $desc->dispForm     = '2col';
        $fields['oco_desc'] = $desc->crTexto();

        // CAMPO: LOTE 
        $lote = new MyCampo('oco_nov_ocorrencia', 'oco_lote');
        $lote->nome    = 'oco_lote';
        $lote->id      = 'oco_lote';
        $lote->valor   = '';
        $lote->objeto  = 'oco_lote';
        
        $lote->label       = 'Lote';
        $lote->obrigatorio = true;
        $lote->tipo        = 'select';
        
        // Chama controller de busca
        $busca = new BuscasSapiens();
        $lotes = $busca->buscaLotes();

        // Prepara os dados para o select
        $opcoesLote = ['' => 'Selecione o lote'];
        foreach ($lotes as $lot) {
            $opcoesLote[$lot->codlot] = $lot->codlot;
        }
        
        $lote->opcoes       = $opcoesLote;
        $lote->dispForm     = 'linha';
        $fields['oco_lote'] = $lote->crSelect();


        //  CAMPO: QUANTIDADE
        $qtd = new MyCampo('oco_nov_ocorrencia', 'oco_qtd');
        $qtd->nome    = 'oco_qtd';
        $qtd->id      = 'oco_qtd';
        $qtd->valor   = 0;
        $qtd->objeto  = 'oco_qtd';
        $qtd->obrigatorio = true;

        $qtd->label    = 'Quantidade';
        $qtd->tipo     = 'number';
        $qtd->dispForm = '2col';

        $qtd->size     = 10;
        $qtd->minimo   = 1;
        $qtd->step     = 1; 
        $qtd->largura  = 10;
        $fields['oco_qtd'] = $qtd->crInput();

        //  CAMPO: PRODUTO
        $produto = new MyCampo('oco_nov_ocorrencia', 'oco_produto');
        $produto->nome    = 'oco_produto';
        $produto->id      = 'oco_produto';
        $produto->valor   = '';
        $produto->objeto  = '';
        $produto->place   = 'Nome do produto selecionado';

        $produto->label    = 'Produto';
        $produto->dispForm = '2col';
        $produto->size     = 54;
        $produto->leitura  = true;

        $fields['oco_produto'] = $produto->crInput();


         //  MONTAGEM DA TELA
        $campos[0][0] = $fields['oco_tipo'];
        $campos[0][1] = $fields['oco_quebra'];
        $campos[0][2] = $fields['oco_desc'];
        $campos[0][3] = $fields['oco_lote'];
        $campos[0][4] = '';              
        $campos[0][5] = $fields['oco_produto'];
        $campos[0][6] = $fields['oco_qtd']; 

        

        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['destino'] = 'store';
        $this->data['tabela']  = 'oco_nov_ocorrencia';

        echo view('vw_edicao', $this->data);
    }

    public function getProdutoLote()
    {
        $codLote = $this->request->getPost('codLote');
    
        if (!$codLote) {
            return $this->response->setJSON(['erro' => 'Lote não informado']);
        }
    
        $model = new \App\Models\Produt\ProdutLoteModel();
    
        // Busca por 'lot_lote' diretamente 
        $dados = $model->getLoteSearch($codLote); 
    
        if (!$dados || empty($dados)) {
            return $this->response->setJSON(['erro' => 'Lote não encontrado']);
        }
    
        $lote = $dados[0]; 
    
        return $this->response->setJSON([
            // 'codpro'   => $lote['lot_codpro'] ?? '',
            'descpro'  => $lote['pro_despro'] ?? '', 
        ]);
    }
    
    
    public function store()
    {

        $this->data['tabela'] = 'oco_nov_ocorrencia';
        session()->setFlashdata('dados_tela', $this->data);

        $ret = [];
        $dados = $this->request->getPost();
        $db = \Config\Database::connect('dbOcorrencia');
    
        try {
                $db->transBegin();
                $model = new \App\Models\Ocorre\OcorreNovOcorrenciaModel();
        
                // 1: Descobre status com base em ações do modelo (T9)
                $status = 'pendente'; // padrão
                $moc_id = $dados['oco_quebra_tipo'];
        
                // Consulta ações cadastradas para esse modelo
                $acoes = $db->table('oco_moc_acao')
                            ->where('moc_id', $moc_id)
                            ->get()
                            ->getResultArray();
        
            if (empty($acoes)) {
                // Nenhuma ação cadastrada
                $status = 'Finalização Automática';
            } else {
                // Verifica se TODAS são "Nenhuma" (tpa_id == 1)
                $todasNenhuma = true;
    
                foreach ($acoes as $acao) {
                    if ($acao['tpa_id'] != 1) {
                        $todasNenhuma = false;
                        break;
                    }
                }
                if ($todasNenhuma) {
                    $status = 'Finalização Automática';
                } else {
                    $status = 'pendente';
                }
            }
    
            // 2: Monta os dados da ocorrência
            $insert = [
                'oco_tipo'        => $dados['oco_tipo'],
                'oco_quebra_tipo' => $dados['oco_quebra_tipo'],
                'oco_descricao'   => $dados['oco_descricao'],
                'oco_lote'        => $dados['oco_lote'],
                'oco_qtd'         => $dados['oco_qtd'],
                'oco_produto'     => $dados['oco_produto'],
                'oco_status'      => $status
            ];
    
            if (!$model->insert($insert)) {
                throw new \Exception('Erro ao inserir ocorrência.');
            }
    
            $db->transCommit();
    
            $ret['erro'] = false;
            $ret['msg']  = 'Ocorrência registrada com sucesso!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url('OcoNovOcorrencia/add'); 
        } catch (\Exception $e) {
            $db->transRollback();
            $ret['erro'] = true;
            $ret['msg']  = 'Erro ao gravar a Ocorrência:<br><br>' . $e->getMessage();
        }
    
        return $this->response->setJSON($ret);
    }
}
