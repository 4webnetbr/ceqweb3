<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutOrigemModel;
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

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';
        $id                   =  new MyCampo('pro_classe', 'cla_id', false);
        $id->valor            = (isset($dados['cla_id'])) ? $dados['cla_id'] : '';
        $id->leitura          = $show;
        $ret['cla_id']        = $id->crOculto();

        $nome                 = new MyCampo('pro_classe', 'cla_nome', false);
        $nome->valor          = (isset($dados['cla_nome'])) ? $dados['cla_nome'] : '';
        $nome->leitura        = $show;
        $nome->obrigatorio    = true;
        $ret['cla_nome']          = $nome->crInput();

        $requ                 = new MyCampo('pro_classe', 'cla_requisicao', false);
        $requ->valor          = (isset($dados['cla_requisicao'])) ? $dados['cla_requisicao'] : 'S';
        $requ->leitura        = $show;
        $requ->opcoes         = $simnao;
        $requ->selecionado    = $requ->valor;
        $requ->classep        = 'mb2';
        $requ->dispForm       = '3col';
        $ret['cla_requisicao']          = $requ->cr2opcoes();

        $ivis                 = new MyCampo('pro_classe', 'cla_insvis', false);
        $ivis->valor          = (isset($dados['cla_insvis'])) ? $dados['cla_insvis'] : 'S';
        $ivis->leitura        = $show;
        $ivis->opcoes         = $simnao;
        $ivis->selecionado    = $ivis->valor;
        $ivis->classep        = 'mb2';
        $ivis->funcChan       = "mostraOcultaCampo(this,'S','cla_insvisconf');";
        $ivis->dispForm       = '3col';
        $ret['cla_insvis']          = $ivis->cr2opcoes();

        $ivcf                 = new MyCampo('pro_classe', 'cla_insvisconf', false);
        $ivcf->valor          = (isset($dados['cla_insvisconf'])) ? $dados['cla_insvisconf'] : 'S';
        $ivcf->leitura        = $show;
        $ivcf->obrigatorio    = true;
        $ivcf->opcoes         = $simnao;
        $ivcf->selecionado    = $ivcf->valor;
        $ivcf->classep        = 'mb2';
        $ivcf->dispForm       = '3col';
        $ret['cla_insvisconf']          = $ivcf->cr2opcoes();

        $micro                 = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor          = (isset($dados['cla_micro'])) ? $dados['cla_micro'] : 'S';
        $micro->leitura        = $show;
        $micro->opcoes         = $simnao;
        $micro->selecionado    = $micro->valor;
        $micro->classep        = 'mb2';
        $micro->funcChan       = "mostraOcultaCampo(this,'S','cla_metodanalise');";
        $micro->dispForm       = '3col';
        $ret['cla_micro']          = $micro->cr2opcoes();

        $mean                 = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor          = (isset($dados['cla_metodanalise'])) ? $dados['cla_metodanalise'] : 'S';
        $mean->leitura        = $show;
        $mean->obrigatorio    = true;
        $mean->opcoes         = $simnao;
        $mean->selecionado    = $mean->valor;
        $mean->classep        = 'mb2';
        $mean->dispForm       = '3col';
        $ret['cla_metodanalise']          = $mean->cr2opcoes();

        $frml                 = new MyCampo('pro_classe', 'cla_formula', false);
        $frml->valor          = (isset($dados['cla_formula'])) ? $dados['cla_formula'] : 'S';
        $frml->leitura        = $show;
        $frml->opcoes         = $simnao;
        $frml->selecionado    = $frml->valor;
        $frml->classep        = 'mb2';
        $frml->dispForm       = '3col';
        $ret['cla_formula']          = $frml->cr2opcoes();

        $eda                 = new MyCampo('pro_classe', 'cla_estdataatual', false);
        $eda->valor          = (isset($dados['cla_estdataatual'])) ? $dados['cla_estdataatual'] : 'N';
        $eda->leitura        = $show;
        $eda->opcoes         = $simnao;
        $eda->selecionado    = $eda->valor;
        $eda->classep        = 'mb2';
        $eda->dispForm       = '3col';
        $ret['cla_estdataatual']          = $eda->cr2opcoes();

        $opc_daco['Bolsas'] = 'Bolsas';
        $opc_daco['Equipos'] = 'Equipos';
        $opc_daco['Insumos'] = 'Insumos';
        $opc_daco['']        = 'Nenhuma';
        $daco                 = new MyCampo('pro_classe', 'cla_dash_consumo', false);
        $daco->valor          = (isset($dados['cla_dash_consumo'])) ? $dados['cla_dash_consumo'] : '';
        $daco->selecionado    = $daco->valor;
        $daco->opcoes         = $opc_daco;
        $daco->largura        = 50;
        $daco->dispForm       = 'col-4';
        $ret['cla_dash_consumo'] = $daco->crSelect();

        $ges                 = new MyCampo('pro_classe', 'cla_gestaoestoque', false);
        $ges->valor          = (isset($dados['cla_gestaoestoque'])) ? $dados['cla_gestaoestoque'] : 'S';
        $ges->leitura        = $show;
        $ges->opcoes         = $simnao;
        $ges->selecionado    = $ges->valor;
        $ges->classep        = 'mb2';
        $ges->dispForm       = '3col';
        $ret['cla_gestaoestoque']          = $ges->cr2opcoes();

        $depositos            = new EstoquDepositoModel();
        $lst_depositos        = $depositos->getDeposito();
        $opc_dep              = array_column($lst_depositos, 'dep_desDep', 'dep_codDep');

        $depo                 = new MyCampo('pro_classe', 'cla_deposito', false);
        $depo->nome           = $depo->id = "cla_deposito";
        $depo->valor          = (isset($dados['cla_deposito'])) ? $dados['cla_deposito'] : '';
        $depo->selecionado    = array_filter(array_map('trim', explode(',', $depo->valor)));
        $depo->leitura        = $show;
        $depo->obrigatorio    = true;
        $depo->opcoes         = $opc_dep;
        $depo->largura        = 50;
        $depo->dispForm       = 'col-4';
        $ret['cla_deposito']  = $depo->crMultiple();

        return $ret;
    }


    public function defCamposClassif($dados = false, $pos = 0, $show = false)
    {
        $ret = [];
        $id           =  new MyCampo('pro_classe_classificacao', 'pcl_id', false);
        $id->valor    = (isset($dados['pcl_id'])) ? $dados['pcl_id'] : '';
        $id->nome     = $id->nome . "[" . $pos . "]";
        $id->id       = $id->id . "[" . $pos . "]";
        $id->leitura  = $show;
        $id->ordem    = $pos;
        $ret['pcl_id'] = $id->crOculto();

        $produtoexiste = $show;
        if (isset($dados['ori_codOri']) && isset($dados['fam_codFam'])) {
            $produtos = new ProdutProdutoModel();
            $buscapro = $produtos->getProdutoOrigemFamiliaClasse($dados['ori_codOri'], $dados['fam_codFam'], $dados['cla_id']);
            if (count($buscapro) > 0) {
                $produtoexiste = true;
            }
        }
        $origem         = new ProdutOrigemModel();
        $lst_origem     = $origem->getOrigem();
        $opc_ori          = array_column($lst_origem, 'ori_codDescricao', 'ori_codOri');

        $orig                 = new MyCampo('pro_classe_classificacao', 'ori_codOri', false);
        $orig->nome             = $orig->id = "ori_codOri[$pos]";
        $orig->valor          = (isset($dados['ori_codOri'])) ? $dados['ori_codOri'] : '';
        $orig->selecionado    = $orig->valor;
        $orig->opcoes         = $opc_ori;
        $orig->leitura        = $produtoexiste;
        $orig->ordem          = $pos;
        $orig->largura        = 50;
        $orig->obrigatorio    = true;
        $orig->dispForm       = '2col';
        $ret['ori_codOri'] = $orig->crSelect();

        $opc_fam = [];
        if (isset($dados['fam_codFam'])) {
            $familia         = new ProdutFamiliaModel();
            $lst_familia     = $familia->getFamilia();
            $opc_fam          = array_column($lst_familia, 'fam_codDescricao', 'fam_codFam');
        }

        $fami                = new MyCampo('pro_classe_classificacao', 'fam_codFam', false);
        $fami->nome          = $fami->id = "fam_codFam[$pos]";
        $fami->valor         = (isset($dados['fam_codFam'])) ? $dados['fam_codFam'] : '';
        $fami->selecionado   = $fami->valor;
        $fami->opcoes        = $opc_fam;
        $fami->leitura       = $produtoexiste;
        $fami->ordem         = $pos;
        $fami->obrigatorio   = true;
        $fami->largura       = 50;
        $fami->dispForm      = '2col';
        $fami->pai           = "ori_codOri[$pos]";
        $fami->urlbusca      = base_url('buscas/busca_familia');
        if ($produtoexiste) {
            $fami->infotexto     = "<span class='text-danger'>Existem vínculos ativos</span>";
        }
        $ret['fam_codFam']   = $fami->crDepende();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->dispForm  = '2col';
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Campo";
        $add->classep   = "btn-outline-success btn-sm bt-repete";
        $add->funcChan  = "addCampo('" . base_url("ProClasse/addCampo/") . "','classificacao',this)";
        $ret['bt_add']   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->dispForm  = '2col';
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone    = "<i class='fas fa-trash'></i>";
        $del->classep   = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan  = "exclui_campo('classificacao',this)";
        $del->place     = "Excluir Campo";
        $ret['bt_del']   = $del->crBotao();
        return $ret;
    }

    public function defCamposMicro($dados = false, $show = false)
    {
        $ret = [];
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $micro                 = new MyCampo('pro_classe', 'cla_micro', false);
        $micro->valor          = (isset($dados['cla_micro'])) ? $dados['cla_micro'] : 'N';
        $micro->leitura        = $show;
        $micro->opcoes         = $simnao;
        $micro->selecionado    = $micro->valor;
        $micro->classep        = 'mb2';
        $micro->funcChan       = "mostraOcultaCampo(this,'S','cla_metodanalise,cla_cabecalho,cla_rodape');mudaObrigatorio(this,'S','cla_cabecalho,cla_rodape');";
        $micro->dispForm       = 'linha';
        $ret['cla_micro']          = $micro->cr2opcoes();

        $mean                 = new MyCampo('pro_classe', 'cla_metodanalise', false);
        $mean->valor          = (isset($dados['cla_metodanalise'])) ? $dados['cla_metodanalise'] : 'S';
        $mean->leitura        = $show;
        $mean->obrigatorio    = false;
        $mean->opcoes         = $simnao;
        $mean->selecionado    = $mean->valor;
        $mean->classep        = 'mb2';
        $mean->dispForm       = 'linha';
        $ret['cla_metodanalise']          = $mean->cr2opcoes();

        $cabe                 = new MyCampo('pro_classe', 'cla_cabecalho', false);
        $cabe->valor          = (isset($dados['cla_cabecalho'])) ? $dados['cla_cabecalho'] : '';
        $cabe->leitura        = $show;
        $cabe->obrigatorio    = false;
        $cabe->selecionado    = $cabe->valor;
        $cabe->classep        = 'mb2';
        $cabe->dispForm       = 'linha';
        $ret['cla_cabecalho']          = $cabe->crTexto();

        $roda                 = new MyCampo('pro_classe', 'cla_rodape', false);
        $roda->valor          = (isset($dados['cla_rodape'])) ? $dados['cla_rodape'] : '';
        $roda->leitura        = $show;
        $roda->obrigatorio    = false;
        $roda->selecionado    = $roda->valor;
        $roda->classep        = 'mb2';
        $roda->dispForm       = 'linha';
        $ret['cla_rodape']          = $roda->crTexto();

        return $ret;
    }
}    