<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutLoteModel;

class EntLote extends Entity
{
    protected $attributes = [
        'lot_id'        => null,
        'lot_codbar'    => null,
        'lot_codpro'    => null,
        'pro_despro'    => null,
        'lot_lote'      => null,
        'lot_entrada'   => null,
        'lot_validade'  => null,
        'stt_id'        => null,
        'lot_status'    => null,
    ];

    protected $casts = [
        'lot_id' => 'integer',
        'stt_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(array|object|null $data = null, bool $show = false)
    {
            if ($data instanceof \stdClass) {
                $data = (array) $data;
            }
    
            parent::__construct($data ?? []);
            $this->campos = $this->defCampos($data ?? [], $show);
    }
    
    public static function campoSelectLote(mixed $valor = null, bool $leitura = false): string 
    {
        $LoteModel = new ProdutLoteModel();
    
        array_column($LoteModel->getLoteId($valor),'lot_lote', 'lot_lote');
    
        $lote = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->objeto   = 'input';
        $lote->tipo     = 'text';
        $lote->size     = 45;
        $lote->obrigatorio = true;        
        $lote->funcBlur = "buscaLoteProduto(this,'" . base_url('/buscas/buscaProdutoporLote') . "')";     
    
        $lote->setLabel('Lote')
             ->setValor($valor ?? '')
             ->setDispForm('col-12')
             ->setLeitura($leitura);
    
        return $lote->crInput(); 
    }

    public function defCampos(object|array $dados = [], bool $show = false): array
    {
        $ret = [];
        if ($dados instanceof \stdClass) {
           $dados = (array) $dados;
       }

        // ID do Lote
        $lot_id = new MyCampo('pro_sap_lote', 'lot_id');
        $lot_id->valor   = $dados['lot_id'] ?? '';
        $lot_id->leitura = true;
        $ret['lot_id'] = $lot_id->crOculto();
        
        // Código de Barras do Lote
        $codbar = new MyCampo('pro_sap_lote', 'lot_codbar');
        $codbar->valor   = $dados['lot_codbar'] ?? '';
        $codbar->leitura = $show;
        $codbar->size    = 30;
        $codbar->label   = 'Código de Barras';
        $ret['lot_codbar'] = $codbar->crInput();
        
        // Código do Produto (Lote)
        $codpro = new MyCampo('pro_sap_lote', 'lot_codpro');
        $codpro->valor   = $dados['lot_codpro'] ?? '';
        $codpro->leitura = $show;
        $codpro->label   = 'Código Produto';
        $codpro->size    = 30;
        $ret['lot_codpro'] = $codpro->crInput();
        
        // Lote
        $lote = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor   = $dados['lot_lote'] ?? '';
        $lote->leitura = $show;
        $lote->label   = 'Lote';
        $lote->size    = 30;
        $ret['lot_lote'] = $lote->crInput();
        
        // Validade
        $val = new MyCampo('pro_sap_lote', 'lot_validade');
        $val->valor   = $dados['lot_validade'] ?? '';
        $val->leitura = $show;
        $val->label   = 'Validade';
        $val->size    = 20;
        $ret['lot_validade'] = $val->crInput();
        
        // Status
        $stt = new MyCampo('pro_sap_lote', 'lot_status');
        $stt->valor   = $dados['stt_nome'] ?? '';
        $stt->leitura = true;
        $stt->label   = 'Status';
        $stt->size    = 30;
        $ret['lot_status'] = $stt->crInput();

        // ID
        $id = new MyCampo('pro_produto', 'pro_id');
        $id->valor   = $dados['pro_id'] ?? '';
        $id->leitura = $show;
        $ret['pro_id'] = $id->crOculto();

        // Código Produto
        $dcod = new MyCampo('pro_produto', 'pro_codpro');
        $dcod->valor = $dados['pro_codpro'] ?? '';
        $dcod->leitura = $show;
        $dcod->size         = 30; 
        $dcod->label       = 'Código';
        $ret['pro_codpro'] = $dcod->crInput();

        // Origem
        $ori = new MyCampo('pro_produto', 'ori_codOri');
        $ori->valor   = $dados['ori_codOri'] ?? '';
        $ori->leitura = $show;
        $ori->size    = 20;
        $ori->label   = 'Origem';
        $ret['ori_codOri'] = $ori->crInput();

        // Família
        $fam = new MyCampo('pro_produto', 'fam_codFam');
        $fam->valor   = $dados['fam_codFam'] ?? '';
        $fam->leitura = $show;
        $fam->size    = 20;
        $fam->opcoes  = [];
        $fam->label   = 'Família';
        $ret['fam_codFam'] = $fam->crInput();

        // Controla lote
        $ctrl = new MyCampo('pro_produto', 'pro_ctrlot');
        $ctrl->valor       = $dados['pro_ctrlot'] ?? 'N';
        $ctrl->leitura     = $show;
        $ctrl->label       = 'Controla Lote';
        $ctrl->opcoes      = [
            'S' => 'Sim',
            'N' => 'Não',
        ];
        $ctrl->selecionado = $ctrl->valor;
        
        $ret['pro_ctrlot'] = $ctrl->crRadio();

        // Descrição
        $desc = new MyCampo('pro_produto', 'pro_despro');
        $desc->valor = $dados['pro_despro'] ?? '';
        $desc->leitura = $show;
        $desc->size        = 50; 
        $desc->label       = 'Descrição'; 
        $ret['pro_despro'] = $desc->crInput();

        // Classe
        // $cla = new MyCampo('pro_produto', 'cla_id');
        // $cla->valor = $dados['cla_id'] ?? '';
        // $cla->leitura = $show;
        // $cla->opcoes  = [];
        // $cla->label   = 'Classe';
        // $ret['cla_id'] = $cla->crSelect();

        // Classe
        $config = [];
        $config['Label']    = 'Classe';
        $config['DispForm'] = 'col-4';
        $config['Largura']  = 40;
        $config['Leitura']  = $show;
        
        $ret['cla_id'] = criaSelectRelativo(
            'vw_pro_classe_lista_relac',
            'cla_id',
            'cla_nome',
            $dados['cla_id'] ?? '',
            1,
            'pro_classe',
            [], 
            $config,
            'cla_id'
        );

        // Fabricante
        // $fab = new MyCampo('pro_produto', 'fab_codFab');
        // $fab->valor   = $dados['fab_codFab'] ?? '';
        // $fab->leitura = $show;
        // $fab->label   = 'Fabricante';
        // $fab->opcoes  = [];
        // $ret['fab_codFab'] = $fab->crSelect();

        // Fabricante
        $config = [];
        $config['Label']    = 'Fabricante';
        $config['DispForm'] = 'col-12';
        $config['Largura']  = 40;
        $config['Leitura']  = $show;
        
        $ret['fab_codFab'] = criaSelectRelativo(
            'pro_sap_fabricante',
            'fab_codFab',
            'fab_nomFab',
            $dados['fab_codFab'] ?? '',
            1,
            'pro_produto',
            [], 
            $config,
            'fab_codFab'
        );

        // // Ingrediente
        // $ing = new MyCampo('pro_produto', 'ing_id');
        // $ing->valor   = $dados['ing_id'] ?? '';
        // $ing->leitura = $show;
        // $ing->label   = 'Ingrediente';
        // $ing->opcoes  = [];
        // $ret['ing_id'] = $ing->crSelect();

        // Ingrediente
        $config = [];
        $config['Label']    = 'Ingrediente';
        $config['DispForm'] = 'col-4';
        $config['Largura']  = 40;
        $config['Leitura']  = $show;
        
        $filtros = [
            'cla_id'    => $dados['cla_id'] ?? null,
            'ing_ativo' => 'A',
        ];
        
        $ret['ing_id'] = criaSelectRelativo(
            'pro_ingrediente',
            'ing_id',
            'ing_nome',
            $dados['ing_id'] ?? '',   
            1,
            'pro_produto',
            $filtros,
            $config,
            'ing_id'
        );

        // Código de barras fabricante
        $cb = new MyCampo('pro_produto', 'pro_codbar_fabricante');
        $cb->valor   = $dados['pro_codbar_fabricante'] ?? '';
        $cb->leitura = $show;
        $cb->size    = 30;
        $cb->label   = 'Código de Barras Fabricante';
        $ret['pro_codbar_fabricante'] = $cb->crInput();
        
        // Informações
        $info = new MyCampo('pro_produto', 'pro_informacoes');
        $info->valor   = $dados['pro_informacoes'] ?? '';
        $info->leitura = $show;
        $info->objeto  = 'textarea';   
        $info->size    = 100; 
        $info->label   = 'Informações';
        $ret['pro_informacoes'] = $info->crInput();

        return $ret;
    }
}
