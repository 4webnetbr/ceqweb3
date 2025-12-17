<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

class OcorreTrataOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $primaryKey = 'oco_id';
    protected $allowedFields = [
        'oco_id',
        'tpo_id',
        'tpa_id',
        'lot_lote',
        'oco_descricao',
        'pro_despro',
        'oco_qtd',
        'oco_data',
        'stt_id',
        'tmo_id',
        'oco_justi',
    ];

    protected $validationRules = [
        'oco_descricao' => 'required|max_length[100]|min_length[3]',
    ];

    protected $validationMessages = [
        'oco_descricao'   => [
            'required'    => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_length'  => 'O Campo deve Conter no Máximo 100 Caracteres',
            'min_length'  => 'O Campo Devente Conter no Minimo 3 Caracteres',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }
    
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }
    
    public function getStatusIdByNome(string $nome, ?int $telId = null): ?int
    {
        $builder = $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id')
            ->where('stt_nome', $nome);
    
        if ($telId !== null) {
            $builder->where('tel_id', $telId);
        }
        $row = $builder->orderBy('stt_id', 'DESC')->get()->getRow();
    
        return $row->stt_id ?? null;
    }

    public function getById($id)
    {
        return $this->db->table('oco_ocorrencia') 
            ->where('oco_id', $id)
            ->get()
            ->getRowArray();
    }
    

    public function getListaCompleta()
    {
        // Buscar VIEW
        $dados = $this->db->table($this->view)
                          ->get()
                          ->getResultArray(); 
        // Buscar LOG
        $ids = array_column($dados, 'oco_id');
        $log = buscaLogTabela('vw_oco_ocorrencia_relac', $ids);

        return $dados;
    }
    


    public function getView($id)
    {
        return $this->db->table($this->view)
            ->where('oco_id', $id)
            ->get()
            ->getResultArray();
    }
    
    public function getAcoesForTratativa($tpo_id)
    {
        return $this->db->table('oco_tpo_acao a')
            ->select('a.*, ta.tpa_nome, tm.tmo_nome, te.tel_nome, s.stt_nome')
            ->join('oco_tipo_acao ta', 'ta.tpa_id = a.tpa_id', 'left')
            ->join('estoqu_tipo_movimentacao tm', 'tm.tmo_id = a.tmo_id', 'left')
            ->join('config_tela te', 'te.tel_id = a.tel_id', 'left')
            ->join('cfg_status s', 's.stt_id = a.stt_id', 'left')
            ->where('a.tpo_id', $tpo_id)
            ->get()
            ->getResultArray();
    }
    

    public function defCampos($dados = false)
    
    // DADOS GERAIS
    {
        $ret = [];
        $mid             = new MyCampo('oco_id_ocorrencia', 'tpo_id');
        $mid->nome       = 'tpo_id';
        $mid->valor      = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $ret['tpo_id']   = $mid->crOculto();
        

        // OCORRÊNCIA
        $tipo              = new MyCampo('oco_ocorrencia', 'tpo_id');
        $tipo->valor       = (isset($dados['tpo_nome'])) ? $dados['tpo_nome'] : '';
        $tipo->label       = 'Ocorrência';
        $tipo->leitura     = true;
        $tipo->dispForm    = '2col';
        $tipo->size        = 54;
        
        $ret['tpo_id'] = $tipo->crInput();


         // USUÁRIO 
        $usu           = new MyCampo('oco_ocorrencia', 'usu_nome');
        $usu->valor    = (isset($dados['usu_nome'])) ? $dados['usu_nome'] : '';
        $usu->objeto   = '';
        $usu->label    = 'Usuário';
        $usu->dispForm = '2col';
        $usu->size     = 40;
        $usu->leitura  = true;

        $ret['usu_nome'] = $usu->crInput();


         // AÇÃO
        $modelMod  = new OcorreModOcorrenciaModel();
        $modelos   = $modelMod->getAcoesByTipoOcorrencia($dados['tpo_id']);
        
        $opcoesMod = [];
        foreach ($modelos as $mod) {
            $opcoesMod[$mod['tpa_id']] = $mod['tpa_nome'];
        }
        $acaoNome = $opcoesMod[$dados['tpa_id']] ?? '';
        
        $acao = new MyCampo('', 'tpa_nome');
        $acao->valor    = $acaoNome;
        $acao->label    = 'Ação';
        $acao->leitura  = true;
        $acao->dispForm = '2col';
        $acao->largura  = 50;
        $acao->size     = 50; 
        
        $ret['tpa_id'] = $acao->crInput();

            // JUSTIFICAR 
            if (($dados['tpa_id']) == 6) {
               $justi               = new MyCampo('', 'oco_justi');
               $justi->valor        = isset($dados['oco_justi']) ? $dados['oco_justi'] : '';
               $justi->label        = 'Justificar';
               $justi->obrigatorio  = true;
               $justi->dispForm     = '2col';
               $justi->linhas       = 3;
               $justi->colunas      = 56;
           
               $ret['oco_justi'] = $justi->crTexto();
            }
   
            // MOVIMENTAÇÂO
            if (($dados['tpa_id']) == 3) {

                $tmoModel = new EstoquTipoMovimentacaoModel();
                $lst_tmo  = $tmoModel->getTipoMovimentacao();
                $opc_tmo  = array_column($lst_tmo, 'tmo_nome', 'tmo_id');
            
                $idMov   = $dados['tmo_id'] ?? '';
                $tmoNome = $opc_tmo[$idMov] ?? '';
            
                $movNome = new MyCampo('oco_tpo_acao', 'tmo_nome');
                $movNome->valor    = $tmoNome;
                $movNome->label    = 'Movimentação';
                $movNome->leitura  = true;
                $movNome->dispForm = '2col';
                $movNome->size     = 50;
            
                $ret['tmo_id'] = $movNome->crInput();
            }

            // STATUS
            if (($dados['tpa_id']) == 7) {

                $statModel = new OcorreModOcorrenciaModel();
                $stt_id_real = $statModel->getStatusByTpoTpa($dados['tpo_id'], $dados['tpa_id']);
            
                $lst_stat = $statModel->getStatus();
                $opc_stat = array_column($lst_stat, 'stt_nome', 'stt_id');
                $nomeStatus = $opc_stat[$stt_id_real] ?? '';
            
                $statu = new MyCampo('', 'stt_id');
                $statu->valor    = $nomeStatus;
                $statu->label    = 'Status';
                $statu->leitura  = true;
                $statu->largura  = 35;
                $statu->size     = 50;
                $statu->dispForm = '2col';
            
                $ret['stt_id'] = $statu->crInput();
            }

            // TELA
            if (($dados['tpa_id']) == 4) {
                $mod = new OcorreModOcorrenciaModel();

                $lst_tel = $mod->getTelas();
                $opc_tel = array_column($lst_tel, 'tel_nome', 'tel_id');
            
                $tel_id_real = $mod->getTelaByTpoTpa($dados['tpo_id'], $dados['tpa_id']);
                $nomeTela = $opc_tel[$tel_id_real] ?? '';
            
                $tela = new MyCampo('', 'tel_id');
                $tela->valor    = $nomeTela;
                $tela->label    = 'Tela';
                $tela->leitura  = true;
                $tela->dispForm = '2col';
                $tela->largura  = 35;
                $tela->size     = 60;
            
                $ret['tel_id'] = $tela->crInput();
            }  


        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');  
        $desc->nome        = 'oco_descricao';
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->leitura     = true;
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';
        $ret['oco_descricao'] = $desc->crTexto();


        // LOTE
        $lote              = new MyCampo('pro_sap_lote', 'lot_lote'); 
        $lote->valor       = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->label       = 'Lote';
        $lote->leitura     = true;
        $lote->size        = 54;

        $ret['lot_lote'] = $lote->crInput();


        // PRODUTO 
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $produto->objeto   = '';
        $produto->label    = 'Produto';
        $produto->dispForm = '2col';
        $produto->size     = 54;
        $produto->leitura  = true;

        $ret['pro_despro'] = $produto->crInput();


        // QUANTIDADE
        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd'); 
        $qtd->valor        = (isset($dados['oco_qtd'])) ? $dados['oco_qtd'] : '';
        $qtd->label        = 'Quantidade';
        $qtd->dispForm     = '2col';
        $qtd->largura      = 5;
        $qtd->leitura      = true;

        $ret['oco_qtd'] = $qtd->crInput();
        

        // DATA 
        $data              = new MyCampo('oco_ocorrencia', 'oco_data');
        $data->valor       = $dados['oco_data'] ?? date('Y-m-d\TH:i'); 
        $data->label       = 'Data da Ocorrência';
        $data->dispForm    = '2col';
        $data->leitura     = true;
        $data->largura     = 30;

        $ret['oco_data'] = $data->crInput();

        return $ret;
    }
}
