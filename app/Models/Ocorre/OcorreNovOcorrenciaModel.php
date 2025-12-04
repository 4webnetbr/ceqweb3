<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Libraries\MyCampo;

class OcorreNovOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_nov_ocorrencia';
    protected $view       = 'vw_oco_nov_ocorrencia_relac';
    protected $primaryKey = 'oco_id';
    protected $allowedFields = [
        'oco_tipo',
        'tpo_nome',
        'oco_descricao',
        'oco_lote',
        'oco_qtd',
        'oco_produto',
        'oco_status',
        'oco_data',
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
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;


public function getStatusIdByNome($nome)
{
    $db = \Config\Database::connect(); 

    $row = $db->table('config_ceqweb_db.cfg_status')
        ->select('stt_id')
        ->where('stt_nome', $nome)
        ->where('tel_id', 56)
        ->get()
        ->getRow();

    return $row->stt_id ?? null;
}


    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }


    public function defCampos($dados = [], $show = false, $tabela = '', $view = '')
    {
        helper('form');
        $fields = [];

        // TIPO DE OCORRÊNCIA
        $tipo              = new MyCampo('oco_nov_ocorrencia', 'oco_tipo');
        $tipo->nome        = 'oco_tipo';
        $tipo->id          = 'oco_tipo';
        $tipo->valor       = $dados['oco_tipo'] ?? '';
        $tipo->label       = 'Tipo de Ocorrência';
        $tipo->obrigatorio = true;
        $tipo->tipo        = 'select';
        $tipo->dispForm    = '2col';
        $tipo->largura     = 58;

        $modelTipo = new \App\Models\Ocorre\OcorreTipoOcorrenciaModel();
        $tipos     = $modelTipo->findAll();
        $opcoes    = ['' => 'Selecione o tipo de ocorrência'];
        foreach ($tipos as $tipo_oc) {
            $opcoes[$tipo_oc['tpo_id']] = $tipo_oc['tpo_nome'];
        }
        $tipo->opcoes = $opcoes;
        $fields['oco_tipo'] = $tipo->crSelect();

        // MOD OCORRÊNCIA
        $quebra              = new MyCampo('oco_nov_ocorrencia', 'tpo_nome'); 
        $quebra->nome        = 'tpo_nome';
        $quebra->id          = 'tpo_nome';
        $quebra->valor       = $dados['tpo_nome'] ?? '';
        $quebra->label       = 'Ocorrência';
        $quebra->obrigatorio = true;
        $quebra->tipo        = 'select';
        $quebra->dispForm    = '2col';
        $quebra->largura     = 58;

        $modelMod  = new \App\Models\Ocorre\OcorreModOcorrenciaModel();
        $modelos   = $modelMod->getModOcorrencia();
        $opcoesMod = ['' => 'Selecione a ocorrência'];
        foreach ($modelos as $mod) {
            $opcoesMod[$mod['moc_id']] = $mod['moc_nome'];
        }
        $quebra->opcoes = $opcoesMod;
        $fields['tpo_nome'] = $quebra->crSelect();

        // DESCRIÇÃO
        $desc              = new MyCampo('oco_nov_ocorrencia', 'oco_descricao');  
        $desc->nome        = 'oco_descricao';
        $desc->id          = 'oco_descricao';
        $desc->valor       = $dados['oco_descricao'] ?? '';
        $desc->label       = 'Descrição';
        $desc->obrigatorio = true;
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';
        $desc->leitura     = $show;
        $desc->place       = 'Digite a descrição da ocorrência';
        $fields['oco_descricao'] = $desc->crTexto();

        // LOTE
        $lote              = new MyCampo('oco_nov_ocorrencia', 'oco_lote');  
        $lote->nome        = 'oco_lote';
        $lote->id          = 'oco_lote';
        $lote->valor       = $dados['oco_lote'] ?? '';
        $lote->label       = 'Lote';
        $lote->obrigatorio = true;
        $lote->tipo        = 'select';
        $lote->dispForm    = 'linha';
        $lote->leitura     = $show;

        $busca      = new \App\Controllers\BuscasSapiens();
        $lotes      = $busca->buscaLotes();
        $opcoesLote = ['' => 'Selecione o lote'];
        foreach ($lotes as $lot) {
            $opcoesLote[$lot->codlot] = $lot->codlot;
        }
        $lote->opcoes = $opcoesLote;
        $fields['oco_lote'] = $lote->crSelect();

        // QUANTIDADE
        $qtd               = new MyCampo('oco_nov_ocorrencia', 'oco_qtd'); 
        $qtd->nome         = 'oco_qtd';
        $qtd->id           = 'oco_qtd';
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->label        = 'Quantidade';
        $qtd->tipo         = 'number';
        $qtd->dispForm     = '2col';
        $qtd->leitura      = $show;
        $qtd->minimo       = 1;
        $qtd->step         = 1;
        $qtd->largura      = 10;
        $qtd->obrigatorio  = true;
        $fields['oco_qtd'] = $qtd->crInput();

        // PRODUTO 
        $produto          = new MyCampo('oco_nov_ocorrencia', 'oco_produto');  
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

        return $fields;
        
        // DATA 
        $data              = new MyCampo('oco_nov_ocorrencia', 'oco_data');
        $data->nome        = 'oco_data';
        $data->id          = 'oco_data';
        $data->valor       = $dados['oco_data'] ?? date('Y-m-d\TH:i'); 
        $data->label       = 'Data da Ocorrência';
        $data->tipo        = 'datetime-local';
        $data->dispForm    = '2col';
        $data->largura     = 30;
        $data->leitura     = $show;
        $data->obrigatorio = true;

        $fields['oco_data'] = $data->crInput();

        return $fields;
    }
}
