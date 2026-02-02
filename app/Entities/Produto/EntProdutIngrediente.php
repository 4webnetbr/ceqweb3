<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Produt\ProdutIngredienteModel;

class EntProdutIngrediente extends Entity
{
    protected $attributes = [
        'ing_id'    => null,
        'ing_nome'  => null,
        'cla_id'    => null,
        'ing_ativo' => 'A',
    ];

    protected $casts = [
        'ing_id' => 'integer',
        'cla_id' => 'integer',
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


    public static function campoSelectIngrediente(mixed $valor = null, int $pos = 0, bool $leitura = false, string $entidade = 'pro_ing_produto'): string
    {
        $opc_ing = [];
    
        if ($valor) {
            $ingModel = new ProdutIngredienteModel();
            $lst_ing  = $ingModel->getIngrediente();
            $opc_ing  = array_column($lst_ing, 'ing_nome', 'ing_id');
        }
    
        $ingp = (new MyCampo($entidade, 'ing_id', false))
            ->setValor($valor ?? '')
            ->setSelecionado($valor ? [$valor] : [])
            ->setOpcoes($opc_ing)
            ->setLeitura($leitura)
            ->setLargura(50)
            ->setObrigatorio(false)
            ->setDispForm('2col')
            ->setPai('cla_id')
            ->setUrlBusca(base_url('buscas/buscaIngredienteClasse'));
    
        return $ingp->crDepende();
    }


    public function defCampos($dados = false, $show = false)
    {
        $ret = [];

        // Opções Sim/Não
        $simnao['S']   = 'Sim';
        $simnao['N']   = 'Não';

        // ID do Ingrediente
        $id            =  new MyCampo('pro_ingrediente', 'ing_id', false);
        $id->valor     = (isset($dados['ing_id'])) ? $dados['ing_id'] : '';
        $id->objeto    = 'oculto';
        $id->leitura   = $show;
        $ret['ing_id'] = $id->crOculto();

        // Nome do Ingrediente
        $nome              = new MyCampo('pro_ingrediente', 'ing_nome', false);
        $nome->valor       = (isset($dados['ing_nome'])) ? $dados['ing_nome'] : '';
        $nome->objeto      = 'input';
        $nome->label       = '';
        $nome->leitura     = $show;
        $nome->obrigatorio = true;
        $ret['ing_nome']   = $nome->crInput();

        // Lista de Classes de Produto
        // $classes = new ProdutClasseModel();
        // $lst_classes = $classes->getClasse();
        // $opc_classes = array_column($lst_classes, 'cla_nome', 'cla_id');

        // Classe do Ingrediente
        $config = [];
        $config['Label']       = '';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        
        $ret['cla_id'] = criaSelectRelativo(
            'pro_classe',          
            'cla_id',              
            'cla_nome',            
            $dados['cla_id'] ?? '',
            1,                     
            'oco_tpo_pro_classe',  
            [],                    
            $config                
        );
        // Classe do Ingrediente
        // $cla_id = new MyCampo('oco_tpo_pro_classe', 'cla_id', false);
        // $cla_id->valor       = (isset($dados['cla_id'])) ? $dados['cla_id'] : '';
        // $cla_id->obrigatorio = true;
        // $cla_id->objeto      = 'select';
        // $cla_id->label       = '';
        // $cla_id->selecionado = $cla_id->valor;
        // $cla_id->opcoes      = $opc_classes;
        // $cla_id->largura     = 50;
        // $ret['cla_id']       = $cla_id->crSelect();

        // Retorna os campos montados
        return $ret;
    }

    public function defCamposProduto($dados = false, $selec = false, $show = false)
    {
        // Instancia o model de Produtos
        $produtos       = new ProdutProdutoModel();

        // Verifica se existe classe definida
        if (isset($dados['cla_id'])) {
            $lst_produts    = $produtos->getProdutoClasse($dados['cla_id'], $dados['ing_id']);
            $tipo = $dados['ing_id'];
        } else {
            // Busca produtos sem ingrediente associado
            $lst_produts    = $produtos->getProdutoSemIngrediente();
            $tipo = 0;
        }
        $opc_prods      = array_column($lst_produts, 'pro_despro', 'pro_id');
        if (isset($selec['pro_id'])) {
            $prodsele = array_values($selec['pro_id']);
        } else {
            $prodsele = [];
        }

         // Campo de seleção múltipla de Produtos
        $prod                   = new MyCampo('pro_ing_produto', 'pro_id', false);
        $prod->valor            = (isset($selec['pro_id'])) ? implode(",", $selec['pro_id']) : '';
        $prod->selecionado      = $prodsele;
        $prod->opcoes           = $opc_prods;
        $prod->valid            = ($tipo > 0) ? true : false;
        $prod->largura          = 50;
        $prod->pai              = "cla_id";
        $prod->urlbusca         = base_url('buscas/buscaProdutoClasseSemIngrediente/' . $tipo);
        $ret['pro_id']          = $prod->crDependeMultiplo();

        // Retorna os campos montados
        return $ret;
    }

}    