<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutIngredienteModel;
use App\Models\Produt\ProdutFabricanteModel;
use App\Models\Produt\ProdutClasseModel;

class EntProdutos extends Entity
{
    protected $attributes = [
        'pro_id'                   => null,
        'pro_codpro'               => null,
        'ori_codOri'               => null,
        'fam_codFam'               => null,
        'pro_ctrlot'               => null,
        'pro_despro'               => null,
        'cla_id'                   => null,
        'fab_codFab'               => null,
        'ing_id'                   => null,
        'pro_codbar_fabricante'    => null,
        'pro_informacoes'          => null,
        'pro_ativo'                => 'A',
    ];

    protected $casts = [
        'pro_id' => 'integer',
        'cla_id' => 'integer',
        'ing_id' => 'integer',
    ];

    public array $campos = [];

    public function __construct(array|object|null $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    // public static function campoSelectProduto(mixed $valor = null, bool $leitura = false, string $entidade = ''): string
    // {
    //     $modModel = new ConfigModuloModel();
    //     $mods = array_column($modModel->getModulo(), 'pro_nome', 'pro_id');

    //     if ($valor && !array_key_exists($valor, $mods)) {
    // $prodModel = new ProdutProdutoModel();
    //         $prod = $prodModel->select('pro_id, pro_despro')
    //                           ->where('pro_id', $valor)
    //                           ->first();

    //         if ($prod) {
    //             $mods[$prod->pro_id] = $prod->pro_despro;
    //         }
    //     }

    //     $mod = (new MyCampo($entidade, 'pro_id'))
    //         ->setLabel('Produto')
    //         ->setValor($valor ?? '')
    //         ->setSelecionado($valor ?? '')
    //         ->setOpcoes($mods)
    //         ->setLargura(60)
    //         ->setObrigatorio()
    //         ->setDispForm('col-12')
    //         ->setLeitura($leitura);

    //     return $mod->crSelect();
    // }


    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();

        $ret = [];

        // ID
        $id = new MyCampo('pro_produto', 'pro_id');
        $id->valor   = $dados['pro_id'] ?? '';
        $id->leitura = $show;
        $ret['pro_id'] = $id->crOculto();

        // Código Produto
        $dcod = new MyCampo('pro_produto', 'pro_codpro');
        $dcod->valor = $dados['pro_codpro'] ?? '';
        $dcod->leitura = $show;
        $dcod->size        = 30;
        $dcod->label       = 'Código';
        $ret['pro_codpro'] = $dcod->crInput();

        // // Origem
        $ori = new MyCampo('pro_produto', 'ori_codOri');
        $ori->valor   = $dados['ori_codOri'] ?? '';
        $ori->leitura = $show;
        $ori->size    = 20;
        $ori->label   = 'Origem';
        $ret['ori_codOri'] = $ori->crInput();

        // // Família
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
        // $classes = new ProdutClasseModel();
        // $lst_classes = $classes->getClassificacaoClasse(
        //     $dados['ori_codOri'],
        //     $dados['fam_codFam']
        // );

        // $opc_clas = array_column($lst_classes, 'cla_nome', 'cla_id');

        // $cla = new MyCampo('pro_classe', 'cla_id');
        // $cla->valor   = $dados['cla_id'] ?? '';
        // $cla->leitura = $show;
        // $cla->largura = 40;
        // $cla->opcoes  = $opc_clas;   
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
        // $fabricantes = new ProdutFabricanteModel();
        // $lst_fab = $fabricantes->getFabricante(); 
        // $opc_fab = array_column($lst_fab, 'fab_nomFab', 'fab_codFab');

        // $fab = new MyCampo('pro_produto', 'fab_codFab');
        // $fab->valor   = $dados['fab_codFab'] ?? '';
        // $fab->leitura = $show;
        // $fab->largura = 40;
        // $fab->label   = 'Fabricante';
        // $fab->opcoes  = $opc_fab;   

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

        // Ingrediente
        // $ingredientes = new ProdutIngredienteModel();
        // $lst_ing = $ingredientes->getIngrediente(); 
        // $opc_ing = array_column($lst_ing, 'ing_nome', 'ing_id');

        // $ing = new MyCampo('pro_produto', 'ing_id');
        // $ing->valor   = $dados['ing_id'] ?? '';
        // $ing->leitura = $show;
        // $ing->largura = 40;
        // $ing->label   = 'Ingrediente';
        // $ing->opcoes  = $opc_ing;   

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
