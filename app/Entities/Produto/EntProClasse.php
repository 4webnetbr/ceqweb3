<?php
namespace App\Entities\Produto;

use App\Libraries\MyCampo;
use App\Models\Produt\ProdutProdutoModel;
use CodeIgniter\Entity\Entity;

class EntProClasse extends Entity
{
    protected $attributes = [
        'cla_id'            => null,
        'cla_nome'          => null,
        'cla_requisicao'    => null,
        'cla_insvis'        => null,
        'cla_insvisconf'    => null,
        'cla_formula'       => null,
        'cla_micro'         => null,
        'cla_metodanalise'  => null,
        'cla_ativo'         => 'A',
        'cla_ordem'         => null,
        'cla_estdataatual'  => null,
        'cla_dash_consumo'  => null,
        'cla_gestaoestoque' => null,
        'cla_cabecalho'     => null,
        'cla_rodape'        => null,
        'cla_deposito'      => null,
    ];

    protected $dates = ['cla_excluido'];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        // debug($dados, true);

        $ret = [];

        $simnao['S']   = 'Sim';
        $simnao['N']   = 'Não';
        $id            = new MyCampo('pro_classe', 'cla_id', false);
        $id->valor     = $dados['cla_id'] ?? '';
        $id->leitura   = $show;
        $ret['cla_id'] = $id->crOculto();

        $nome              = new MyCampo('pro_classe', 'cla_nome', false);
        $nome->valor       = $dados['cla_nome'] ?? '';
        $nome->leitura     = $show;
        $nome->obrigatorio = true;
        $ret['cla_nome']   = $nome->crInput();

        $requ                  = new MyCampo('pro_classe', 'cla_requisicao', false);
        $requ->valor           = $dados['cla_requisicao'] ?? 'S';
        $requ->leitura         = $show;
        $requ->opcoes          = $simnao;
        $requ->selecionado     = $requ->valor;
        $requ->classep         = 'mb2';
        $requ->dispForm        = '3col';
        $ret['cla_requisicao'] = $requ->cr2opcoes();

        $ivis              = new MyCampo('pro_classe', 'cla_insvis', false);
        $ivis->valor       = $dados['cla_insvis'] ?? 'S';
        $ivis->leitura     = $show;
        $ivis->opcoes      = $simnao;
        $ivis->selecionado = $ivis->valor;
        $ivis->classep     = 'mb2';
        $ivis->funcChan    = "mostraOcultaCampo(this,'S','cla_insvisconf');";
        $ivis->dispForm    = '3col';
        $ret['cla_insvis'] = $ivis->cr2opcoes();

        $ivcf                  = new MyCampo('pro_classe', 'cla_insvisconf', false);
        $ivcf->valor           = $dados['cla_insvisconf'] ?? 'S';
        $ivcf->leitura         = $show;
        $ivcf->obrigatorio     = true;
        $ivcf->opcoes          = $simnao;
        $ivcf->selecionado     = $ivcf->valor;
        $ivcf->classep         = 'mb2';
        $ivcf->dispForm        = '3col';
        $ret['cla_insvisconf'] = $ivcf->cr2opcoes();

        $micro              = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor       = $dados['cla_micro'] ?? 'S';
        $micro->leitura     = $show;
        $micro->opcoes      = $simnao;
        $micro->selecionado = $micro->valor;
        $micro->classep     = 'mb2';
        $micro->funcChan    = "mostraOcultaCampo(this,'S','cla_metodanalise');";
        $micro->dispForm    = '3col';
        $ret['cla_micro']   = $micro->cr2opcoes();

        $mean                    = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor             = $dados['cla_metodanalise'] ?? 'S';
        $mean->leitura           = $show;
        $mean->obrigatorio       = true;
        $mean->opcoes            = $simnao;
        $mean->selecionado       = $mean->valor;
        $mean->classep           = 'mb2';
        $mean->dispForm          = '3col';
        $ret['cla_metodanalise'] = $mean->cr2opcoes();

        $frml               = new MyCampo('pro_classe', 'cla_formula', false);
        $frml->valor        = $dados['cla_formula'] ?? 'S';
        $frml->leitura      = $show;
        $frml->opcoes       = $simnao;
        $frml->selecionado  = $frml->valor;
        $frml->classep      = 'mb2';
        $frml->dispForm     = '3col';
        $ret['cla_formula'] = $frml->cr2opcoes();

        $eda                     = new MyCampo('pro_classe', 'cla_estdataatual', false);
        $eda->valor              = $dados['cla_estdataatual'] ?? 'N';
        $eda->leitura            = $show;
        $eda->opcoes             = $simnao;
        $eda->selecionado        = $eda->valor;
        $eda->classep            = 'mb2';
        $eda->dispForm           = '3col';
        $ret['cla_estdataatual'] = $eda->cr2opcoes();

        $opc_daco['Bolsas']      = 'Bolsas';
        $opc_daco['Equipos']     = 'Equipos';
        $opc_daco['Insumos']     = 'Insumos';
        $daco                    = new MyCampo('pro_classe', 'cla_dash_consumo', false);
        $daco->valor             = $dados['cla_dash_consumo'] ?? '';
        $daco->selecionado       = $daco->valor;
        $daco->opcoes            = $opc_daco;
        $daco->largura           = 50;
        $daco->dispForm          = 'col-4';
        $ret['cla_dash_consumo'] = $daco->crSelect();

        $ges                      = new MyCampo('pro_classe', 'cla_gestaoestoque', false);
        $ges->valor               = $dados['cla_gestaoestoque'] ?? 'S';
        $ges->leitura             = $show;
        $ges->opcoes              = $simnao;
        $ges->selecionado         = $ges->valor;
        $ges->classep             = 'mb2';
        $ges->dispForm            = '3col';
        $ret['cla_gestaoestoque'] = $ges->cr2opcoes();

        $config[] = '';
        // $config['Label']    = 'Depósito';
        $config['Leitura']     = $show;
        $config['Largura']     = 50;
        $config['Dispform']    = 'col-4';
        $config['Obrigatorio'] = false;

        $depositos = ! empty($dados['cla_deposito'])
            ? array_map(
            static fn(string $item) : string => trim($item),
            explode(',', $dados['cla_deposito'])
        )
            : [];

        // debug($depositos, true);

        $ret['cla_deposito'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_desDep',
            $depositos,
            3,
            'pro_classe',
            [],
            $config,
            'cla_deposito'
        );

        return $ret;
    }

    public function defCamposClassif($dados = false, $pos = 0, $show = false)
    {
        $ret           = [];
        $id            = new MyCampo('pro_classe_classificacao', 'pcl_id', false);
        $id->valor     = $dados['pcl_id'] ?? '';
        $id->leitura   = $show;
        $id->ordem     = $pos;
        $ret['pcl_id'] = $id->crOculto();

        $produtoexiste = $show;
        if (isset($dados['ori_codOri']) && isset($dados['fam_codFam'])) {
            $produtos = new ProdutProdutoModel();
            $buscapro = $produtos->getProdutoOrigemFamiliaClasse($dados['ori_codOri'], $dados['fam_codFam'], $dados['cla_id']);
            if (count($buscapro) > 0) {
                $produtoexiste = true;
            }
        }

        $config[]           = '';
        $config['Label']    = 'Origem';
        $config['Leitura']  = $produtoexiste;
        $config['Largura']  = 50;
        $config['Ordem']    = $pos;
        $config['Dispform'] = 'col-4';

        $ret['ori_codOri'] = criaSelectRelativo(
            'pro_sap_origem',
            'ori_codOri',
            'ori_codDescricao',
            $dados['ori_codOri'] ?? '',
            1,
            'pro_classe',
            [],
            $config
        );

        $config['Label']    = 'Família';
        $config['Pai']      = "ori_codOri[$pos]";
        $config['Urlbusca'] = base_url('buscas/busca_familia');
        if ($produtoexiste) {
            $config['Infotexto'] = "<span class='text-danger'>Existem vínculos ativos</span>";
        }

        $ret['fam_codFam'] = criaSelectRelativo(
            'pro_sap_familia',
            'fam_codFam',
            'fam_codDescricao',
            $dados['fam_codFam'] ?? '',
            4,
            'pro_classe',
            [],
            $config
        );

        $atrib['data-index'] = $pos;
        $add                 = new MyCampo();
        $add->attrdata       = $atrib;
        $add->dispForm       = '2col';
        $add->nome           = "bt_add[$pos]";
        $add->id             = "bt_add[$pos]";
        $add->i_cone         = "<i class='fas fa-plus'></i>";
        $add->place          = "Adicionar Campo";
        $add->classep        = "btn-outline-success btn-sm bt-repete";
        $add->funcChan       = "addCampo('" . base_url("ProClasse/addCampo/") . "','classificacao',this)";
        $ret['bt_add']       = $add->crBotao();

        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->dispForm = '2col';
        $del->nome     = "bt_del[$pos]";
        $del->id       = "bt_del[$pos]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan = "exclui_campo('classificacao',this)";
        $del->place    = "Excluir Campo";
        $ret['bt_del'] = $del->crBotao();
        if ($produtoexiste) {
            $ret['bt_del'] = '';
        }
        return $ret;
    }

    public function defCamposMicro($dados = false, $show = false)
    {
        // debug($dados, true);
        $ret         = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $micro              = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor       = $dados['cla_micro'] ?? 'N';
        $micro->leitura     = $show;
        $micro->opcoes      = $simnao;
        $micro->selecionado = $micro->valor;
        $micro->classep     = 'mb2';
        $micro->funcChan    = "mostraOcultaCampo(this,'S','cla_metodanalise,cla_cabecalho,cla_rodape');mudaObrigatorio(this,'S','cla_cabecalho,cla_rodape');";
        $micro->dispForm    = 'linha';
        $ret['cla_micro']   = $micro->cr2opcoes();

        $mean                    = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor             = $dados['cla_metodanalise'] ?? 'S';
        $mean->leitura           = $show;
        $mean->obrigatorio       = false;
        $mean->opcoes            = $simnao;
        $mean->selecionado       = $mean->valor;
        $mean->classep           = 'mb2';
        $mean->dispForm          = 'linha';
        $ret['cla_metodanalise'] = $mean->cr2opcoes();

        $cabe                 = new MyCampo('pro_classe', 'cla_cabecalho', false);
        $cabe->valor          = $dados['cla_cabecalho'] ?? '';
        $cabe->leitura        = $show;
        $cabe->obrigatorio    = false;
        $cabe->selecionado    = $cabe->valor;
        $cabe->classep        = 'mb2';
        $cabe->dispForm       = 'linha';
        $ret['cla_cabecalho'] = $cabe->crTexto();

        $roda              = new MyCampo('pro_classe', 'cla_rodape', false);
        $roda->valor       = $dados['cla_rodape'] ?? '';
        $roda->leitura     = $show;
        $roda->obrigatorio = false;
        $roda->selecionado = $roda->valor;
        $roda->classep     = 'mb2';
        $roda->dispForm    = 'linha';
        $ret['cla_rodape'] = $roda->crTexto();

        return $ret;
    }
}
