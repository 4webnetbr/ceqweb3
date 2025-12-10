<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Controllers\BuscasSapiens;
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
        'tpa_nome',
        'lot_lote',
        'oco_descricao',
        'lot_id',
        'oco_qtd',
        'oco_data',
        'stt_id',
        'tmo_id',
        'oco_justi',
        'usu_nome',
        'stt_cor'
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
        $dados = $this->db->table($this->view)
                    ->where('oco_id', $id)
                    ->get()
                    ->getRowArray();
        if (!$dados) {
            return null;
        }
        return $dados;
    }
    

    public function getListaCompleta()
    {
        // Buscar VIEW
        $dados = $this->db->table($this->view)
                    ->get()
                    ->getResultArray(); 
        // Buscar LOG
        $ids = array_column($dados, 'oco_id');
        $log = buscaLogTabela('oco_ocorrencia', $ids);
    
        // Inject usu_nome
        foreach ($dados as &$d) {
            $d['usu_nome'] = $log[$d['oco_id']]['usua_alterou'] ?? '';
        }
        return $dados;
    }


    public function getView()
    {

    }    



    public function defCampos($dados = false)
    {
        $ret = [];
        $mid             = new MyCampo('oco_id_ocorrencia', 'tpo_id');
        $mid->nome       = 'tpo_id';
        $mid->valor      = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $ret['tpo_id']   = $mid->crOculto();
        

        // OCORRÊNCIA
        $modelTipo = new OcorreTipoOcorrenciaModel();
        $tipos = $modelTipo->findAll();
        
        foreach ($tipos as $t) {
            $opcoes[$t['tpo_id']] = $t['tpo_nome'];
        }
        
        $tipo              = new MyCampo('oco_ocorrencia', 'tpo_id');
        $tipo->opcoes      = $opcoes;
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
        $tpaId = $dados['tpa_id'] ?? null;
        // Apenas mostra ação simples
        $acaoNome = $dados['tpa_nome'] ?? '';
    
        $tpa_id = new MyCampo('oco_tpo_acao', 'tpa_id');
        $tpa_id->valor = $acaoNome;
        $tpa_id->label = 'Ação';
        $tpa_id->leitura = true;
        $tpa_id->dispForm = '2col';
        $ret['tpa_id'] = $tpa_id->crInput();
    
    
        // se existir tpa_id
        if ($tpaId) {
    
            switch ($tpaId) {
    
                case 1: // Abrir Tela
                    $mod = new MyCampo('oco_tpo_acao', 'mod_id');
                    $mod->valor = $dados['mod_nome'] ?? '';
                    $mod->label = 'Módulo';
                    $mod->leitura = true;
                    $mod->dispForm = '2col';
                    $ret['mod_id'] = $mod->crInput();
    
                    $tel = new MyCampo('oco_tpo_acao', 'tel_id');
                    $tel->valor = $dados['tel_nome'] ?? '';
                    $tel->label = 'Tela';
                    $tel->leitura = true;
                    $tel->dispForm = '2col';
                    $ret['tel_id'] = $tel->crInput();
                    break;
    
                case 2: // Alterar Status
                    $st = new MyCampo('oco_tpo_acao', 'stt_id');
                    $st->valor = $dados['stt_tela_status'] ?? '';
                    $st->label = 'Status';
                    $st->leitura = true;
                    $st->dispForm = '2col';
                    $ret['stt_id'] = $st->crInput();
                    break;
    
                case 3: // Movimentação
                    $tm = new MyCampo('oco_tpo_acao', 'tmo_id');
                    $tm->valor = $dados['tmo_nome'] ?? '';
                    $tm->label = 'Movimentação';
                    $tm->leitura = true;
                    $tm->dispForm = '2col';
                    $ret['tmo_id'] = $tm->crInput();
                    break;
            }
        }
        // Se for JUSTIFICAR 
        if ($tpaId == 6) {
            $just = new \App\Libraries\MyCampo('oco_ocorrencia', 'oco_justi');
            $just->valor = $dados['oco_justi'] ?? '';
            $just->label = 'Justificar';
            $just->leitura = false; 
            $just->linhas = 4;
            $just->colunas = 56;
            $just->dispForm = '2col';
            
            $ret['oco_justi'] = $just->crTexto();
        }


        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');  
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->leitura     = true;
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';

        $ret['oco_descricao'] = $desc->crTexto();

        // LOTE
        $buscaSapiens      = new BuscasSapiens();
        $lotes             = $buscaSapiens->buscaLotes();
        foreach ($lotes as $lot) {
            $opcoesLote[$lot->codlot] = $lot->codlot;
        }

        $lote              = new MyCampo('oco_ocorrencia', 'lot_id'); 
        $lote->valor       = (isset($dados['lot_id'])) ? $dados['lot_id'] : '';
        $lote->opcoes      = $opcoesLote;
        $lote->label       = 'Lote';
        $lote->leitura     = true;
        $lote->size        = 54;

        $ret['lot_id'] = $lote->crInput();

        // PRODUTO 
        $produto           = new MyCampo('oco_ocorrencia', 'lot_lote');
        $produto->valor    = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $produto->objeto   = '';
        $produto->label    = 'Produto';
        $produto->dispForm = '2col';
        $produto->size     = 54;
        $produto->leitura  = true;

        $ret['lot_lote'] = $produto->crInput();

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
