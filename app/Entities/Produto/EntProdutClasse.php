<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Produt\ProdutOrigemModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;

class EntProdutClasse extends Entity
{
    protected $attributes = [
        'cla_id'            => null,
        'cla_nome'          => null,
        'cla_requisicao'    => 'S',
        'cla_insvis'        => 'S',
        'cla_insvisconf'    => 'S',
        'cla_formula'       => 'S',
        'cla_micro'         => 'N',
        'cla_metodanalise'  => 'S',
        'cla_ativo'         => 'A',
        'cla_ordem'         => null,
        'cla_estdataatual'  => 'N',
        'cla_dash_consumo'  => '',
        'cla_gestaoestoque' => 'S',
        'cla_cabecalho'     => '',
        'cla_rodape'        => '',
        'cla_deposito'      => null,
        'cla_excluido'      => null,
    ];

    protected $dates = ['cla_excluido'];
    protected $casts = [];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }


    public static function campoSelectClasse(mixed $valor = null, bool $leitura = false, string $entidade = ''): string
    {
        $classeModel = new ProdutClasseModel();
        $lst_classe  = $classeModel->getClasse();
        $opc_cla     = array_column($lst_classe, 'ori_codDescricao', 'ori_codOri');
    
        $orig = (new MyCampo($entidade, 'ori_codOri', false))
            ->setLabel('Origem')
            ->setValor($valor ?? '')
            ->setSelecionado($valor ?? '')
            ->setOpcoes($opc_cla)
            ->setLeitura($leitura)
            ->setLargura(50)
            ->setObrigatorio()
            ->setDispForm('2col');
    
        return $orig->crSelect();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];

        // Opções Sim / Não
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // ID da Classe
        $id = new MyCampo('pro_classe', 'cla_id', false);
        $id->nome      = 'cla_id';
        $id->id        = 'cla_id';
        $id->valor     = $this->cla_id ?? '';
        $id->leitura   = true;
        $ret['cla_id'] = $id->crOculto();

        // Nome da Classe
        $nome = new MyCampo('pro_classe', 'cla_nome', false);
        $nome->valor       = $this->cla_nome ?? '';
        $nome->leitura     = $show;
        $nome->obrigatorio = true;
        $ret['cla_nome']   = $nome->crInput();

        // Requisição obrigatória
        $requ = new MyCampo('pro_classe', 'cla_requisicao', false);
        $requ->valor       = $this->cla_requisicao ?? 'S';
        $requ->leitura     = $show;
        $requ->opcoes      = $simnao;
        $requ->selecionado = $requ->valor;
        $requ->classep     = 'mb2';
        $requ->dispForm    = '3col';
        $ret['cla_requisicao'] = $requ->cr2opcoes();
        
        // Inserção Visível
        $ivis = new MyCampo('pro_classe', 'cla_insvis', false);
        $ivis->valor       = $this->cla_insvis ?? 'S';
        $ivis->leitura     = $show;
        $ivis->opcoes      = $simnao;
        $ivis->selecionado = $ivis->valor;
        $ivis->classep     = 'mb2';
        $ivis->funcChan    = "mostraOcultaCampo(this,'S','cla_insvisconf');";
        $ivis->dispForm    = '3col';
        $ret['cla_insvis'] = $ivis->cr2opcoes();
        
        // Confirmação da Inserção Visível
        $ivcf = new MyCampo('pro_classe', 'cla_insvisconf', false);
        $ivcf->valor       = $this->cla_insvisconf ?? 'S';
        $ivcf->leitura     = $show;
        $ivcf->obrigatorio = true;
        $ivcf->opcoes      = $simnao;
        $ivcf->selecionado = $ivcf->valor;
        $ivcf->classep     = 'mb2';
        $ivcf->dispForm    = '3col';
        $ret['cla_insvisconf'] = $ivcf->cr2opcoes();
        
        // Classe Microbiológica
        $micro = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor       = $this->cla_micro ?? 'S';
        $micro->leitura     = $show;
        $micro->opcoes      = $simnao;
        $micro->selecionado = $micro->valor;
        $micro->classep     = 'mb2';
        $micro->funcChan    = "mostraOcultaCampo(this,'S','cla_metodanalise');";
        $micro->dispForm    = '3col';
        $ret['cla_micro']   = $micro->cr2opcoes();
        
        // Método de Análise
        $mean = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor       = $this->cla_metodanalise ?? 'S';
        $mean->leitura     = $show;
        $mean->obrigatorio = true;
        $mean->opcoes      = $simnao;
        $mean->selecionado = $mean->valor;
        $mean->classep     = 'mb2';
        $mean->dispForm    = '3col';
        $ret['cla_metodanalise'] = $mean->cr2opcoes();
        
        // Utiliza Fórmula
        $frml = new MyCampo('pro_classe', 'cla_formula', false);
        $frml->valor       = $this->cla_formula ?? 'S';
        $frml->leitura     = $show;
        $frml->opcoes      = $simnao;
        $frml->selecionado = $frml->valor;
        $frml->classep     = 'mb2';
        $frml->dispForm    = '3col';
        $ret['cla_formula'] = $frml->cr2opcoes();
        
        // Atualiza Data no Estoque
        $eda = new MyCampo('pro_classe', 'cla_estdataatual', false);
        $eda->valor       = $this->cla_estdataatual ?? 'N';
        $eda->leitura     = $show;
        $eda->opcoes      = $simnao;
        $eda->selecionado = $eda->valor;
        $eda->classep     = 'mb2';
        $eda->dispForm    = '3col';
        $ret['cla_estdataatual'] = $eda->cr2opcoes();
        
        // Opções de Dashboard de Consumo
        $opc_daco = [
            'Bolsas'  => 'Bolsas',
            'Equipos' => 'Equipos',
            'Insumos' => 'Insumos',
            ''        => 'Nenhuma',
        ];
        
        // Dashboard de Consumo
        $config = [];
        $config['DispForm'] = 'col-6';
        
        $ret['cla_dash_consumo'] = criaSelectRelativo(
            'pro_classe',
            'cla_dash_consumo',
            'cla_dash_consumo',
            $this->cla_dash_consumo ?? '',
            1,
            'pro_classe',
            [],
            $config
        );
        
        // Gestão de Estoque
        $ges = new MyCampo('pro_classe', 'cla_gestaoestoque', false);
        $ges->valor       = $this->cla_gestaoestoque ?? 'S';
        $ges->leitura     = $show;
        $ges->opcoes      = $simnao;
        $ges->selecionado = $ges->valor;
        $ges->classep     = 'mb2';
        $ges->dispForm    = '3col';
        $ret['cla_gestaoestoque'] = $ges->cr2opcoes();
        
        // Depósitos Permitidos
        $depositos     = new EstoquDepositoModel();
        $lst_depositos = $depositos->getDeposito();
        $opc_dep       = array_column($lst_depositos, 'dep_desDep', 'dep_codDep');
        
        // Seleção múltipla de depósitos
        $depo = new MyCampo('pro_classe', 'cla_deposito', false);
        $depo->nome        = $depo->id = 'cla_deposito';
        $depo->valor       = $this->cla_deposito ?? '';
        $depo->selecionado = array_filter(array_map('trim', explode(',', $depo->valor)));
        $depo->leitura     = $show;
        $depo->obrigatorio = $show;
        $depo->opcoes      = $opc_dep;
        $depo->largura     = 50;
        $depo->dispForm    = 'col-4';
        $ret['cla_deposito'] = $depo->crMultiple();

        // Retorna os campos montados
        return $ret;
    }

    public function defCamposClassif(int $pos = 0, bool $show = false): array
    {
        $ret = [];
        $produtoexiste = $show;

        // VERIFICA PRODUTO EXISTENTE
        if ($this->ori_codOri && $this->fam_codFam) {
            $produtos = new ProdutProdutoModel();
            $buscapro = $produtos->getProdutoOrigemFamiliaClasse(
                $this->ori_codOri,
                $this->fam_codFam,
                $this->cla_id
            );
    
            if (count($buscapro) > 0) {
                $produtoexiste = true;
            }
        }

        // ORIGEM
        $valorOri = $this->ori_codOri ?? null;
        $jaExiste = !empty($this->pcl_id); 
        
        $config = [];
        $config['Label']       = 'Origem';
        $config['Largura']     = 50;
        $config['DispForm']    = '2col';
        $config['Ordem']       = $pos;
        $config['Leitura']     = $jaExiste;
        
        $ret['ori_codOri'] = criaSelectRelativo(
            'pro_sap_origem',
            'ori_codOri',
            'ori_codDescricao',
            $valorOri,
            1,
            'pro_classe_classificacao',
            [],
            $config,
            "ori_codOri[$pos]"
        );
    
        // ID OCULTO
        $id = new MyCampo('pro_classe_classificacao', 'pcl_id', false);
        $id->valor   = $this->pcl_id ?? '';
        $id->nome    = "pcl_id[$pos]";
        $id->id      = "pcl_id[$pos]";
        $id->leitura = true;
        $id->ordem   = $pos;
        $ret['pcl_id'] = $id->crOculto();
    
        // FAMÍLIA
        $config['Label']       = 'Família';
        $config['Leitura']     = $jaExiste;
        
        if ($produtoexiste) {
            $config['Infotexto'] = "<span class='text-danger'>Existem vínculos ativos</span>";
        }
        
        $valorFam = $this->fam_codFam ?? [];
        if (!is_array($valorFam)) {
            $valorFam = [$valorFam];
        }
        
        $ret['fam_codFam'] = criaSelectRelativo(
            'pro_sap_familia',
            'fam_codFam',
            'fam_codDescricao',
            $valorFam,  
            3,
            'pro_classe_classificacao',
            [],
            $config
        );


        // BOTÕES
        if (!$show) {
            $atrib['data-index'] = $pos;
    
            $add = new MyCampo();
            $add->attrdata = $atrib;
            $add->dispForm = '2col';
            $add->nome     = "bt_add[$pos]";
            $add->id       = "bt_add[$pos]";
            $add->i_cone   = "<i class='fas fa-plus'></i>";
            $add->place    = "Adicionar Campo"; 
            $add->classep  = "btn-outline-success btn-sm bt-repete";
            $add->funcChan = "addCampo('" . base_url("ProClasse/addCampo/") . "','classificacao',this)";
            $ret['bt_add'] = $add->crBotao();
    
            $del = new MyCampo();
            $del->attrdata = $atrib;
            $del->dispForm = '2col';
            $del->nome     = "bt_del[$pos]";
            $del->id       = "bt_del[$pos]";
            $del->i_cone   = "<i class='fas fa-trash'></i>";
            $del->place    = "Excluir Campo";
            $del->classep  = "btn-outline-danger btn-sm bt-exclui";
            $del->funcChan = "exclui_campo('classificacao',this)";
            $ret['bt_del'] = $del->crBotao();
        }
        return $ret;
    }
    
    
        public function defCamposMicro(bool $show = false): array
    {
        $ret    = [];
        $simnao = ['S' => 'Sim', 'N' => 'Não'];
    
        // MICROBIOLÓGICO
        $micro = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor       = $this->cla_micro ?? 'N';
        $micro->leitura     = $show;
        $micro->opcoes      = $simnao;
        $micro->selecionado = $micro->valor;
        $micro->classep     = 'mb2';
        $micro->funcChan    = "mostraOcultaCampo(this,'S','cla_metodanalise,cla_cabecalho,cla_rodape');
                               mudaObrigatorio(this,'S','cla_cabecalho,cla_rodape');";
        $micro->dispForm    = 'linha';
        $ret['cla_micro']   = $micro->cr2opcoes();
    

        // MÉTODO DE ANÁLISE
        $mean = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor       = $this->cla_metodanalise ?? 'S';
        $mean->leitura     = $show;
        $mean->obrigatorio = false;
        $mean->opcoes      = $simnao;
        $mean->selecionado = $mean->valor;
        $mean->classep     = 'mb2';
        $mean->dispForm    = 'linha';
        $ret['cla_metodanalise'] = $mean->cr2opcoes();
    
        // CABEÇALHO
        $cabe = new MyCampo('pro_classe', 'cla_cabecalho', false);
        $cabe->valor       = $this->cla_cabecalho ?? '';
        $cabe->leitura     = $show;
        $cabe->obrigatorio = false;
        $cabe->selecionado = $cabe->valor;
        $cabe->classep     = 'mb2';
        $cabe->dispForm    = 'linha';
        $ret['cla_cabecalho'] = $cabe->crTexto();
    
        // RODAPÉ
        $roda = new MyCampo('pro_classe', 'cla_rodape', false);
        $roda->valor       = $this->cla_rodape ?? '';
        $roda->leitura     = $show;
        $roda->obrigatorio = false;
        $roda->selecionado = $roda->valor;
        $roda->classep     = 'mb2';
        $roda->dispForm    = 'linha';
        $ret['cla_rodape'] = $roda->crTexto();
    
        return $ret;
    }

}    