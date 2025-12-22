<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Estoqu\EstoquTipoMovimentacaoModel;

class EntOcoTratativa extends Entity
{
    protected $attributes = [
        'oco_id'        => null,
        'tpo_id'        => null,
        'tpa_id'        => null,
        'lot_lote'      => null,
        'oco_descricao' => null,
        'pro_despro'    => null,
        'oco_qtd'       => null,
        'oco_data'      => null,
        'stt_id'        => null,
        'tmo_id'        => null,
        'oco_justi'     => null,
        'usu_nome'      => null,
        'tel_id'        => null,
    ];

    protected $casts = [
        'oco_id' => 'integer',
        'tpo_id' => 'integer',
        'tpa_id' => 'integer',
        'stt_id' => 'integer',
        'tmo_id' => 'integer',
        'tel_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(object|array|null $data = null)
    {
        if (is_array($data)) {
            $data = (object) $data;
        }
        parent::__construct((array) ($data ?? []));
        $this->campos = $this->defCampos($data ?? new \stdClass());
    }

    public function defCampos(object $dados)
          
    // DADOS GERAIS
    {
      $ret = [];
      $mid             = new MyCampo('oco_id_ocorrencia', 'tpo_id');
      $mid->nome       = 'tpo_id';
      $mid->valor      = isset($dados->tpo_id) ? $dados->tpo_id : '';
      $ret['tpo_id']   = $mid->crOculto();
  
  
      // OCORRÊNCIA
      $tipo              = new MyCampo('oco_ocorrencia', 'tpo_id');
      $tipo->valor       = isset($dados->tpo_nome) ? $dados->tpo_nome : '';
      $tipo->label       = 'Ocorrência';
      $tipo->leitura     = true;
      $tipo->dispForm    = '2col';
      $tipo->size        = 54;
  
      $ret['tpo_id'] = $tipo->crInput();
  
  
      // USUÁRIO
      $usu           = new MyCampo('oco_ocorrencia', 'usu_nome');
      $usu->valor    = isset($dados->usu_nome) ? $dados->usu_nome : '';
      $usu->objeto   = '';
      $usu->label    = 'Usuário';
      $usu->dispForm = '2col';
      $usu->size     = 40;
      $usu->leitura  = true;
  
      $ret['usu_nome'] = $usu->crInput();
  
  
      // AÇÃO
      $modelMod  = new OcorreModOcorrenciaModel();
      $modelos   = $modelMod->getAcoesByTipoOcorrencia($dados->tpo_id);
  
      $opcoesMod = [];
     foreach ($modelos as $mod) {
         $opcoesMod[$mod->tpa_id] = $mod->tpa_nome;
     }
      $acaoNome = $opcoesMod[$dados->tpa_id] ?? '';
  
      $acao = new MyCampo('', 'tpa_nome');
      $acao->valor    = $acaoNome;
      $acao->label    = 'Ação';
      $acao->leitura  = true;
      $acao->dispForm = '2col';
      $acao->largura  = 50;
      $acao->size     = 50;
  
      $ret['tpa_id'] = $acao->crInput();
  
          // JUSTIFICAR
          if (($dados->tpa_id) == 6) {
              $justi               = new MyCampo('', 'oco_justi');
              $justi->valor        = isset($dados->oco_justi) ? $dados->oco_justi : '';
              $justi->label        = 'Justificar';
              $justi->obrigatorio  = true;
              $justi->dispForm     = '2col';
              $justi->linhas       = 3;
              $justi->colunas      = 56;
      
              $ret['oco_justi'] = $justi->crTexto();
          }
      
          // MOVIMENTAÇÃO
          if (($dados->tpa_id) == 3) {
      
              $modelMod = new OcorreModOcorrenciaModel();
      
              $tmo_id = $modelMod->getMovimentacaoByTpoTpa(
                  $dados->tpo_id,
                  $dados->tpa_id
              );
      
              $tmoModel = new EstoquTipoMovimentacaoModel();

              $lst_tmo = $tmoModel->asObject()->findAll();

              $opc_tmo = [];
              foreach ($lst_tmo as $tmo) {
                  $opc_tmo[$tmo->tmo_id] = $tmo->tmo_nome;
              }
              
              $tmoNome = $opc_tmo[$tmo_id] ?? '';
      
              $tmoNome = $opc_tmo[$tmo_id] ?? '';
      
              $movNome = new MyCampo('oco_tpo_acao', 'tmo_nome');
              $movNome->valor    = $tmoNome;
              $movNome->label    = 'Movimentação';
              $movNome->leitura  = true;
              $movNome->dispForm = '2col';
              $movNome->size     = 50;
      
              $ret['tmo_id'] = $movNome->crInput();
          }
      
          // STATUS
          if (($dados->tpa_id) == 7) {
      
              $statModel   = new OcorreModOcorrenciaModel();
              $stt_id_real = $statModel->getStatusByTpoTpa($dados->tpo_id, $dados->tpa_id);
      
              $opc_stat = [];
              foreach ($statModel->getStatus() as $stt) {
                  $opc_stat[$stt->stt_id] = $stt->stt_nome;
              }
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
          if (($dados->tpa_id) == 4) {
      
              $mod = new OcorreModOcorrenciaModel();
      
              $opc_tel = [];
              foreach ($mod->getTelas() as $tel) {
                  $opc_tel[$tel->tel_id] = $tel->tel_nome;
              }
      
              $tel_id_real = $mod->getTelaByTpoTpa($dados->tpo_id, $dados->tpa_id);
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
      $desc->valor       = isset($dados->oco_descricao) ? $dados->oco_descricao : '';
      $desc->leitura     = true;
      $desc->linhas      = 3;
      $desc->colunas     = 56;
      $desc->dispForm    = '2col';
      $ret['oco_descricao'] = $desc->crTexto();
  
  
      // LOTE
      $lote              = new MyCampo('pro_sap_lote', 'lot_lote');
      $lote->valor       = isset($dados->lot_lote) ? $dados->lot_lote : '';
      $lote->label       = 'Lote';
      $lote->leitura     = true;
      $lote->size        = 54;
  
      $ret['lot_lote'] = $lote->crInput();
  
  
      // PRODUTO
      $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
      $produto->valor    = isset($dados->pro_despro) ? $dados->pro_despro : '';
      $produto->objeto   = '';
      $produto->label    = 'Produto';
      $produto->dispForm = '2col';
      $produto->size     = 54;
      $produto->leitura  = true;
  
      $ret['pro_despro'] = $produto->crInput();
  
  
      // QUANTIDADE
      $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
      $qtd->valor        = isset($dados->oco_qtd) ? $dados->oco_qtd : '';
      $qtd->label        = 'Quantidade';
      $qtd->dispForm     = '2col';
      $qtd->largura      = 5;
      $qtd->leitura      = true;
  
      $ret['oco_qtd'] = $qtd->crInput();
  
  
      // DATA
      $data              = new MyCampo('oco_ocorrencia', 'oco_data');
      $data->valor       = $dados->oco_data ?? date('Y-m-d\TH:i');
      $data->label       = 'Data da Ocorrência';
      $data->dispForm    = '2col';
      $data->leitura     = true;
      $data->largura     = 30;
  
      $ret['oco_data'] = $data->crInput();
  
      return $ret;
  }
}
