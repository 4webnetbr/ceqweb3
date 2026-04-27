<?php
namespace App\Entities\Produto;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Produt\ProdutClasseModel;
use CodeIgniter\Entity\Entity;

class EntProdutProduto extends Entity
{
    protected $attributes = [
        'pro_id'                => null,
        'pro_codemp'            => null,
        'pro_codpro'            => null,
        'pro_despro'            => null,
        'fab_codFab'            => null,
        'ori_codOri'            => null,
        'fam_codFam'            => null,
        'cla_id'                => null,
        'pro_cplpro'            => null,
        'pro_ctrlot'            => 'S',
        'pro_qtdemb'            => 0,
        'pro_codbar_fabricante' => null,
        'pro_informacoes'       => null,
        'pro_ativo'             => 'A',
        'stt_id'                => null,
    ];

    protected $casts = [];
    protected $dates = [];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($this->attributes, $show);
    }

    public function defCampos(array $dados, bool $show = false): array
    {
        $opcoes = new CommonModel();

        // Código do Produto
        $id              = new MyCampo('pro_sap_produto', 'pro_id', true);
        $id->tipo        = 'text';
        $id->valor       = $dados['cod_ceqweb'] ?? '';
        $id->obrigatorio = true;
        $id->leitura     = $show;
        $id->largura     = 15;
        $id->dispForm    = 'col-6';
        $ret['pro_id']   = $id->crInput();

        // Código do Produto
        $cpro              = new MyCampo('pro_sap_produto', 'pro_codpro', true);
        $cpro->valor       = (isset($dados['pro_codpro'])) ? $dados['pro_codpro'] : '';
        $cpro->obrigatorio = true;
        $cpro->leitura     = $show;
        $cpro->largura     = 15;
        $cpro->dispForm    = 'col-6';
        $ret['pro_codpro'] = $cpro->crInput();

        // Descrição do Produto
        $dpro              = new MyCampo('pro_sap_produto', 'pro_despro');
        $dpro->valor       = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $dpro->obrigatorio = true;
        $dpro->leitura     = $show;
        $dpro->linhas      = 2;
        $dpro->colunas     = 60;
        $dpro->largura     = 50;
        $dpro->largura     = 50;
        $dpro->dispForm    = 'col-6';
        $ret['pro_despro'] = $dpro->crTexto();

        // Origem do Produto
        $config                = [];
        $config['DispForm']    = 'col-6';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        $config['Leitura']     = $show;

        $valor = $dados['ori_codOri'] ?? '';

        // filtro para trazer somente a opção atual (edit)
        $filtro = [];
        if ($valor) {
            $filtro['ori_codOri'] = $valor;
        }

        $ret['ori_codOri'] = criaSelectRelativo(
            'pro_sap_origem',
            'ori_codOri',
            'ori_codDescricao',
            $valor,
            1,
            'pro_sap_produto',
            $filtro,
            $config,
            'ori_codOri'
        );

        // Família do Produto
        $config                = [];
        $config['DispForm']    = 'col-6';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        $config['Leitura']     = $show;

        $valor = $dados['fam_codFam'] ?? null;

        $filtro = [];
        if ($valor) {
            $filtro['fam_codFam'] = $valor;
        }

        $ret['fam_codFam'] = criaSelectRelativo(
            'pro_sap_familia',
            'fam_codFam',
            'fam_codDescricao',
            $valor,
            1,
            'pro_sap_produto',
            $filtro,
            $config,
            'fam_codFam'
        );

        // Classe do Produto
        $classes     = new ProdutClasseModel();
        $lst_classes = $classes->getClassificacaoClasse($dados['ori_codOri'], $dados['fam_codFam']);

        // Classe selecionada
        $opc_clas = array_column($lst_classes, 'cla_nome', 'cla_id');
        $valor    = $dados['cla_id'] ?? null;

        // debug($lst_classes, true);
        // se existir apenas 1 classe, seleciona automaticamente
        if (count($lst_classes) === 1) {
            $valor = $lst_classes[0]['cla_id'];
        }
        $leitura = ! (count($opc_clas) > 1 || count($opc_clas) == 0);

        $config                = [];
        $config['DispForm']    = 'col-6';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = false;
        $config['Leitura']     = $leitura;
        // $config['Opcoes']      = $opc_clas;

        $ret['cla_id'] = criaSelectRelativo(
            'vw_pro_classe_relac',
            'cla_id',
            'cla_nome',
            $valor,
            1,
            'pro_sap_produto',
            ['ori_codOri' => $dados['ori_codOri'], 'fam_codFam' => $dados['fam_codFam']],
            $config,
            'cla_id'
        );

        // Fabricante
        $config                = [];
        $config['Label']       = 'Fabricante';
        $config['DispForm']    = 'col-6';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = true;
        $config['Leitura']     = $show;

        $valor = $dados['fab_codFab'] ?? null;

        $filtro = [];
        if ($valor) {
            $filtro['fab_codFab'] = $valor;
        }

        $ret['fab_codFab'] = criaSelectRelativo(
            'pro_sap_fabricante',
            'fab_codFab',
            'fab_apeFab',
            $valor,
            1,
            'pro_sap_fabricante',
            $filtro,
            $config,
            'fab_codFab'
        );

        // Complemento do Produto
        $pcpl              = new MyCampo('pro_sap_produto', 'pro_cplpro');
        $pcpl->valor       = (isset($dados['pro_cplpro'])) ? $dados['pro_cplpro'] : '';
        $pcpl->obrigatorio = true;
        $pcpl->leitura     = $show;
        $pcpl->largura     = 50;
        $pcpl->dispForm    = 'col-6';
        $ret['pro_cplpro'] = $pcpl->crInput();

        // Controle de Lote
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $ctrl              = new MyCampo('pro_sap_produto', 'pro_ctrlot');
        $ctrl->valor       = (isset($dados['pro_ctrlot'])) ? $dados['pro_ctrlot'] : 'S';
        $ctrl->selecionado = $ctrl->valor;
        $ctrl->obrigatorio = true;
        $ctrl->leitura     = $show;
        $ctrl->largura     = 10;
        $ctrl->dispForm    = 'col-6';
        $ctrl->opcoes      = $simnao;
        $ret['pro_ctrlot'] = $ctrl->cr2opcoes();

        // Quantidade por Embalagem
        $qtem              = new MyCampo('pro_sap_produto', 'pro_qtdemb');
        $qtem->tipo        = 'sonumero';
        $qtem->valor       = (isset($dados['pro_qtdemb'])) ? $dados['pro_qtdemb'] : 0;
        $qtem->obrigatorio = true;
        $qtem->leitura     = $show;
        $qtem->largura     = 50;
        $qtem->minimo      = 0;
        $qtem->maximo      = 9999;
        $qtem->dispForm    = 'col-6';
        $ret['pro_qtdemb'] = $qtem->crInput();

        // Ingrediente
        $config                = [];
        $config['Label']       = 'Ingrediente';
        $config['DispForm']    = 'col-6';
        $config['Largura']     = 50;
        $config['Obrigatorio'] = false;
        $config['Leitura']     = false;

        $valor = $dados['ing_id'] ?? null;

        $filtro = [];
        if ($valor) {
            $filtro['ing_id'] = $valor;
        }

        $config['Pai']      = 'cla_id';
        $config['UrlBusca'] = base_url('buscas/buscaIngredienteClasse');

        $ret['ing_id'] = criaSelectRelativo(
            'pro_ingrediente',
            'ing_id',
            'ing_nome',
            $valor,
            2,
            'pro_ing_produto',
            $filtro,
            $config,
            'ing_id'
        );

        // Código de Barras do Fabricante
        $codb                         = new MyCampo('pro_sap_produto', 'pro_codbar_fabricante');
        $codb->tipo                   = 'sonumero';
        $codb->valor                  = (isset($dados['pro_codbar_fabricante'])) ? $dados['pro_codbar_fabricante'] : '';
        $codb->leitura                = false;
        $codb->largura                = 50;
        $codb->dispForm               = 'col-6';
        $ret['pro_codbar_fabricante'] = $codb->crInput();

        // Informações Adicionais
        $info                   = new MyCampo('pro_sap_produto', 'pro_informacoes');
        $info->tipo             = 'sonumero';
        $info->valor            = (isset($dados['pro_informacoes'])) ? $dados['pro_informacoes'] : '';
        $info->leitura          = false;
        $info->dispForm         = 'col-6';
        $ret['pro_informacoes'] = $info->crInput();

        // Retorna os campos montados
        return $ret;
    }

    public function defCamposCeqweb($dados = false, $show = false)
    {
        // Opções Sim / Não
        $opcoes      = new CommonModel();
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // Lista de cores para etiquetas
        $opc_cor = $opcoes->getListaOpcoes('default', 'cfg_cor', ['cor_nome_rgb', 'cor_id']);

        // ID do Produto CEQ
        $id            = new MyCampo('pro_ceq_produto', 'prc_id', true);
        $id->valor     = (isset($dados['prc_id'])) ? $dados['prc_id'] : '';
        $ret['prc_id'] = $id->crOculto();

        // Complemento do Produto
        $pcpl              = new MyCampo('pro_ceq_produto', 'prc_cplpro');
        $pcpl->valor       = (isset($dados['prc_cplpro'])) ? $dados['prc_cplpro'] : '';
        $pcpl->obrigatorio = false;
        $pcpl->leitura     = $show;
        $pcpl->largura     = 50;
        $pcpl->dispForm    = 'col-6';
        $ret['pro_cplpro'] = $pcpl->crInput();

        // Quantidade por Embalagem
        $qtem                  = new MyCampo('pro_ceq_produto', 'prc_qtdemb_ceq');
        $qtem->valor           = (isset($dados['prc_qtdemb_ceq'])) ? $dados['prc_qtdemb_ceq'] : 0;
        $qtem->obrigatorio     = false;
        $qtem->leitura         = $show;
        $qtem->minimo          = 0;
        $qtem->maximo          = 9999;
        $qtem->size            = 4;
        $qtem->maxLength       = 4;
        $qtem->dispForm        = 'col-6';
        $ret['prc_qtdemb_ceq'] = $qtem->crInput();

        // Confirma Requisição
        $creq                = new MyCampo('pro_ceq_produto', 'prc_conf_req');
        $creq->valor         = (isset($dados['prc_conf_req'])) ? $dados['prc_conf_req'] : 'N';
        $creq->obrigatorio   = true;
        $creq->leitura       = $show;
        $creq->dispForm      = 'col-4';
        $creq->opcoes        = $simnao;
        $creq->selecionado   = $creq->valor;
        $ret['prc_conf_req'] = $creq->cr2opcoes();

        // Pedido por Caixa
        $pcai                    = new MyCampo('pro_ceq_produto', 'prc_pedido_caixa');
        $pcai->valor             = (isset($dados['prc_pedido_caixa'])) ? $dados['prc_pedido_caixa'] : 'N';
        $pcai->obrigatorio       = true;
        $pcai->leitura           = $show;
        $pcai->dispForm          = 'col-6';
        $pcai->opcoes            = $simnao;
        $pcai->selecionado       = $pcai->valor;
        $ret['prc_pedido_caixa'] = $pcai->cr2opcoes();

        // Etiqueta de Misturador
        $emis                       = new MyCampo('pro_ceq_produto', 'prc_etiq_misturador');
        $emis->valor                = (isset($dados['prc_etiq_misturador'])) ? $dados['prc_etiq_misturador'] : 'N';
        $emis->obrigatorio          = true;
        $emis->leitura              = $show;
        $emis->dispForm             = 'col-12';
        $emis->opcoes               = $simnao;
        $emis->selecionado          = $emis->valor;
        $emis->funcChan             = "mostraOcultaCampo(this,'S','prc_cor_etiqueta_mist,prc_codbar')";
        $ret['prc_etiq_misturador'] = $emis->cr2opcoes();

        // Cor da Etiqueta do Misturador
        $cemi                         = new MyCampo('pro_ceq_produto', 'prc_cor_etiqueta_mist');
        $cemi->valor                  = (isset($dados['prc_cor_etiqueta_mist'])) ? $dados['prc_cor_etiqueta_mist'] : '';
        $cemi->selecionado            = $cemi->valor;
        $cemi->obrigatorio            = true;
        $cemi->leitura                = $show;
        $cemi->largura                = 50;
        $cemi->dispForm               = 'col-12';
        $cemi->opcoes                 = $opc_cor;
        $ret['prc_cor_etiqueta_mist'] = $cemi->crSelectCor();

        // Código de Barras
        $codb              = new MyCampo('pro_ceq_produto', 'prc_codbar');
        $codb->tipo        = 'sonumero';
        $codb->valor       = (isset($dados['prc_codbar'])) ? $dados['prc_codbar'] : '';
        $codb->leitura     = $show;
        $codb->obrigatorio = true;
        $codb->maxLength   = 13;
        $codb->largura     = 50;
        $codb->dispForm    = 'col-12';
        $ret['prc_codbar'] = $codb->crInput();

        // Etiqueta do Produto
        $epro                    = new MyCampo('pro_ceq_produto', 'prc_etiq_produto');
        $epro->valor             = (isset($dados['prc_etiq_produto'])) ? $dados['prc_etiq_produto'] : 'N';
        $epro->obrigatorio       = true;
        $epro->leitura           = $show;
        $epro->dispForm          = 'col-12';
        $epro->opcoes            = $simnao;
        $epro->selecionado       = $epro->valor;
        $epro->funcChan          = "mostraOcultaCampo(this,'S','prc_cor_etiqueta_prod')";
        $ret['prc_etiq_produto'] = $epro->cr2opcoes();

        // Cor da Etiqueta do Produto
        $cemp                         = new MyCampo('pro_ceq_produto', 'prc_cor_etiqueta_prod');
        $cemp->valor                  = (isset($dados['prc_cor_etiqueta_prod']) && $dados['prc_cor_etiqueta_prod'] != '') ? $dados['prc_cor_etiqueta_prod'] : '1';
        $cemp->selecionado            = [$cemp->valor];
        $cemp->obrigatorio            = true;
        $cemp->default                = '1';
        $cemp->leitura                = $show;
        $cemp->largura                = 50;
        $cemp->dispForm               = 'col-12';
        $cemp->opcoes                 = $opc_cor;
        $ret['prc_cor_etiqueta_prod'] = $cemp->crSelectCor();

        // Lista de Depósitos
        // $depositos            = new EstoquDepositoModel();
        // $lst_depositos        = $depositos->getDeposito();
        // $opc_dep              = array_column($lst_depositos, 'dep_desDep', 'dep_codDep');

        // // Depósitos selecionados
        // $depositos = '';
        // if (!empty($dados['prc_deposito'])) {
        //     $depositos = $dados['prc_deposito'];
        // } elseif (
        //     isset($dados['produto']) &&
        //     is_array($dados['produto']) &&
        // !empty($dados['produto']['cla_deposito'])
        // ) {
        //     $depositos = $dados['produto']['cla_deposito'];
        // }

        // // Campo de Depósitos (múltiplo)
        // $valor = (string) $depositos;
        $config             = [];
        $config['DispForm'] = 'col-6';
        $config['Largura']  = 50;

        // $config['Opcoes'] = $opc_dep;

        $ret['prc_deposito'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_codDescricao',
            $dados['prc_deposito'] ?? '',
            3,
            'pro_ceq_produto',
            [],
            $config,
            'prc_deposito'
        );

        // Retorna os campos montados
        return $ret;
    }

    public function defCamposEstoque($dados = false, $pos = 0, $show = false)
    {
        // Opções Sim / Não
        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        // ID do Registro de Estoque
        $id            = new MyCampo('pro_est_produto', 'pre_id', true);
        $id->nome      = $id->id      = "pre_id[$pos]";
        $id->valor     = (isset($dados['pre_id'])) ? $dados['pre_id'] : '';
        $ret['pre_id'] = $id->crOculto();

        // Depósito
        $config                = [];
        $config['Label']       = 'Depósito';
        $config['DispForm']    = '3col';
        $config['Largura']     = 40;
        $config['Obrigatorio'] = true;
        $config['Leitura']     = $show;

        $ret['dep_codDep'] = criaSelectRelativo(
            'est_sap_deposito',
            'dep_codDep',
            'dep_codDescricao',
            $dados['dep_codDep'] ?? '',
            1,
            'pro_est_produto',
            [],
            $config,
            "dep_codDep[$pos]"
        );

        // Gestão de Estoque
        $valor = isset($dados['pre_gestaoestoque'])
            ? $dados['pre_gestaoestoque']
            : (isset($dados['cla_gestaoestoque']) && $dados['cla_gestaoestoque'] != ''
                ? substr($dados['cla_gestaoestoque'], 0, 1)
                : 'S');

        $ges              = new MyCampo('pro_est_produto', 'pre_gestaoestoque', false);
        $ges->nome        = $ges->id        = "pre_gestaoestoque[$pos]";
        $ges->valor       = $valor;
        $ges->leitura     = $show;
        $ges->opcoes      = $simnao;
        $ges->selecionado = $ges->valor;
        $ges->funcChan    = "mostraOcultaDiv(this,'S','div_est_minimo\\[$pos\\],div_est_maximo\\[$pos\\]');
                                mostraOcultaCampo(this,'S','pre_sugerida\\[$pos\\],pre_estdataatual\\[$pos\\]');
                                mostraOcultaCampo('pre_mindiaanterior','S','pre_minimo\\[$pos\\]');
                                mostraOcultaCampo('pre_maxdiaanterior','S','pre_maximo\\[$pos\\]');";
        $ges->dispForm            = '4col';
        $ret['pre_gestaoestoque'] = $ges->cr2opcoes();

        // Usa mínimo do dia anterior
        $mda                       = new MyCampo('pro_est_produto', 'pre_mindiaanterior');
        $mda->nome                 = $mda->id                 = "pre_mindiaanterior[$pos]";
        $mda->valor                = (isset($dados['pre_mindiaanterior'])) ? $dados['pre_mindiaanterior'] : 'N';
        $mda->obrigatorio          = true;
        $mda->leitura              = $show;
        $mda->dispForm             = 'col-6';
        $mda->opcoes               = $simnao;
        $mda->selecionado          = $mda->valor;
        $mda->funcChan             = "mostraOcultaCampo(this,'N','pre_minimo\\[$pos\\]')";
        $ret['pre_mindiaanterior'] = $mda->cr2opcoes();

        // Estoque Mínimo
        $mini              = new MyCampo('pro_est_produto', 'pre_minimo');
        $mini->nome        = $mini->id        = "pre_minimo[$pos]";
        $mini->valor       = (isset($dados['pre_minimo'])) ? $dados['pre_minimo'] : 0;
        $mini->obrigatorio = true;
        $mini->leitura     = $show;
        $mini->minimo      = 0;
        $mini->maximo      = 9999;
        $mini->largura     = 10;
        $mini->maxLength   = 4;
        $mini->dispForm    = 'col-6';
        $ret['pre_minimo'] = $mini->crInput();

        // Usa máximo do dia anterior
        $mda                       = new MyCampo('pro_est_produto', 'pre_maxdiaanterior');
        $mda->nome                 = $mda->id                 = "pre_maxdiaanterior[$pos]";
        $mda->valor                = (isset($dados['pre_maxdiaanterior'])) ? $dados['pre_maxdiaanterior'] : 'N';
        $mda->obrigatorio          = true;
        $mda->leitura              = $show;
        $mda->dispForm             = 'col-4';
        $mda->opcoes               = $simnao;
        $mda->selecionado          = $mda->valor;
        $mda->funcChan             = "mostraOcultaCampo(this,'S','pre_porcmaximo\\[$pos\\]');mostraOcultaCampo(this,'N','pre_maximo\\[$pos\\]')";
        $ret['pre_maxdiaanterior'] = $mda->cr2opcoes();

        // Percentual Máximo
        $pmax                  = new MyCampo('pro_est_produto', 'pre_porcmaximo');
        $pmax->nome            = $pmax->id            = "pre_porcmaximo[$pos]";
        $pmax->valor           = (isset($dados['pre_porcmaximo'])) ? $dados['pre_porcmaximo'] : 0;
        $pmax->obrigatorio     = false;
        $pmax->leitura         = $show;
        $pmax->minimo          = 0;
        $pmax->maximo          = 999;
        $pmax->maxLength       = 3;
        $pmax->largura         = 10;
        $pmax->dispForm        = 'col-4';
        $ret['pre_porcmaximo'] = $pmax->crInput();

        // Estoque Máximo
        $maxi              = new MyCampo('pro_est_produto', 'pre_maximo');
        $maxi->nome        = $maxi->id        = "pre_maximo[$pos]";
        $maxi->valor       = (isset($dados['pre_maximo'])) ? $dados['pre_maximo'] : 0;
        $maxi->obrigatorio = false;
        $maxi->leitura     = $show;
        $maxi->minimo      = 0;
        $maxi->maximo      = 9999;
        $maxi->maxLength   = 4;
        $maxi->largura     = 10;
        $maxi->dispForm    = 'col-4';
        $ret['pre_maximo'] = $maxi->crInput();

        // Quantidade Sugerida
        $suge                = new MyCampo('pro_est_produto', 'pre_sugerida');
        $suge->nome          = $suge->id          = "pre_sugerida[$pos]";
        $suge->valor         = (isset($dados['pre_sugerida'])) ? $dados['pre_sugerida'] : 0;
        $suge->obrigatorio   = false;
        $suge->leitura       = $show;
        $suge->minimo        = 0;
        $suge->maximo        = 9999;
        $suge->maxLength     = 4;
        $suge->dispForm      = 'col-3';
        $ret['pre_sugerida'] = $suge->crInput();

        // Código de Barras do Fabricante
        $cbf                     = new MyCampo('pro_est_produto', 'pre_cbfabricante');
        $cbf->nome               = $cbf->id               = "pre_cbfabricante[$pos]";
        $cbf->valor              = (isset($dados['pre_cbfabricante'])) ? $dados['pre_cbfabricante'] : 'S';
        $cbf->obrigatorio        = true;
        $cbf->leitura            = $show;
        $cbf->dispForm           = 'col-3';
        $cbf->opcoes             = $simnao;
        $cbf->selecionado        = $cbf->valor;
        $cbf->funcChan           = "mostraOcultaCampo(this,'S','pre_undfabricante\\[$pos\\]')";
        $ret['pre_cbfabricante'] = $cbf->cr2opcoes();

        // Unidade do Fabricante
        $unf                      = new MyCampo('pro_est_produto', 'pre_undfabricante');
        $unf->nome                = $unf->id                = "pre_undfabricante[$pos]";
        $unf->valor               = (isset($dados['pre_undfabricante'])) ? $dados['pre_undfabricante'] : 'N';
        $unf->obrigatorio         = true;
        $unf->leitura             = $show;
        $unf->dispForm            = 'col-3';
        $unf->opcoes              = $simnao;
        $unf->selecionado         = $unf->valor;
        $ret['pre_undfabricante'] = $unf->cr2opcoes();

        // Código de Barras por Lote
        $cbl               = new MyCampo('pro_est_produto', 'pre_cblote');
        $cbl->nome         = $cbl->id         = "pre_cblote[$pos]";
        $cbl->valor        = (isset($dados['pre_cblote'])) ? $dados['pre_cblote'] : 'S';
        $cbl->obrigatorio  = true;
        $cbl->leitura      = $show;
        $cbl->dispForm     = 'col-3';
        $cbl->opcoes       = $simnao;
        $cbl->selecionado  = $cbl->valor;
        $cbl->funcChan     = "mostraOcultaCampo(this,'S','pre_undlote\\[$pos\\]')";
        $ret['pre_cblote'] = $cbl->cr2opcoes();

        // Unidade do Lote
        $unl                = new MyCampo('pro_est_produto', 'pre_undlote');
        $unl->nome          = $unl->id          = "pre_undlote[$pos]";
        $unl->valor         = (isset($dados['pre_undlote'])) ? $dados['pre_undlote'] : 'N';
        $unl->obrigatorio   = true;
        $unl->leitura       = $show;
        $unl->dispForm      = 'col-3';
        $unl->opcoes        = $simnao;
        $unl->selecionado   = $unl->valor;
        $ret['pre_undlote'] = $unl->cr2opcoes();

        // Código de Barras do Misturador
        $cbm                     = new MyCampo('pro_est_produto', 'pre_cbmisturador');
        $cbm->nome               = $cbm->id               = "pre_cbmisturador[$pos]";
        $cbm->valor              = (isset($dados['pre_cbmisturador'])) ? $dados['pre_cbmisturador'] : 'S';
        $cbm->obrigatorio        = true;
        $cbm->leitura            = $show;
        $cbm->dispForm           = 'col-3';
        $cbm->opcoes             = $simnao;
        $cbm->selecionado        = $cbm->valor;
        $cbm->funcChan           = "mostraOcultaCampo(this,'S','pre_undmisturador\\[$pos\\]')";
        $ret['pre_cbmisturador'] = $cbm->cr2opcoes();

        // Unidade do Misturador
        $unm                      = new MyCampo('pro_est_produto', 'pre_undmisturador');
        $unm->nome                = $unm->id                = "pre_undmisturador[$pos]";
        $unm->valor               = (isset($dados['pre_undmisturador'])) ? $dados['pre_undmisturador'] : 'N';
        $unm->obrigatorio         = true;
        $unm->leitura             = $show;
        $unm->dispForm            = 'col-3';
        $unm->opcoes              = $simnao;
        $unm->selecionado         = $unm->valor;
        $ret['pre_undmisturador'] = $unm->cr2opcoes();

        // Atualiza data do estoque
        $valor = isset($dados['pre_estdataatual'])
            ? $dados['pre_estdataatual']
            : (isset($dados['cla_desestdataatual']) && $dados['cla_desestdataatual'] != ''
                ? substr($dados['cla_desestdataatual'], 0, 1)
                : 'N');

        $eda              = new MyCampo('pro_est_produto', 'pre_estdataatual', false);
        $eda->nome        = $eda->id        = "pre_estdataatual[$pos]";
        $eda->valor       = $valor;
        $eda->leitura     = $show;
        $eda->opcoes      = $simnao;
        $eda->selecionado = $eda->valor;
        // $eda->classep        = 'mb2';
        $eda->dispForm           = 'col-3';
        $ret['pre_estdataatual'] = $eda->cr2opcoes();

        // Botão Adicionar Linha
        $atrib['data-index'] = $pos;
        $add                 = new MyCampo();
        $add->attrdata       = $atrib;
        $add->dispForm       = 'col-6';
        $add->nome           = "bt_add[$pos]";
        $add->id             = "bt_add[$pos]";
        $add->i_cone         = "<i class='fas fa-plus'></i>";
        $add->place          = "Adicionar Campo";
        $add->classep        = "btn-outline-success btn-sm bt-repete";
        $add->funcChan       = "addCampo('" . base_url("Produto/addCampo/") . "','gestao_de_estoque',this)";
        $ret['bt_add']       = $add->crBotao();

        // Botão Excluir Linha
        $del           = new MyCampo();
        $del->attrdata = $atrib;
        $del->dispForm = 'col-6';
        $del->nome     = "bt_del[$pos]";
        $del->id       = "bt_del[$pos]";
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->classep  = "btn-outline-danger btn-sm bt-exclui";
        $del->funcChan = "exclui_campo('gestao_de_estoque',this)";
        $del->place    = "Excluir Campo";
        $ret['bt_del'] = $del->crBotao();

        // Retorna os campos montados
        return $ret;
    }
}
