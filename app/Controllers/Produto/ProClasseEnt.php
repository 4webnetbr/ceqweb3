<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;
use App\Entities\Produto\EntProClasse;
use App\Libraries\MyCampo;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\CommonModel;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;

class ProClasse extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $permissao = '';
    public $classes;
    public $common;

    public $bt_order;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->classes   = new ProdutClasseModel();
        $this->common    = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $order          = new MyCampo();
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-arrow-down-short-wide" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Ordenar</div>';
        $order->place    = 'Ordenar as Classes';
        $order->funcChan = 'redireciona(\'ProClasse/ordenar/\')';
        $order->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_order  = $order->crBotao();
        $this->data['botao'] = $this->bt_order;

        $this->data['colunas'] = montaColunasLista($this->data, 'cla_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'cla_id');
        $dados_classe = $this->classes->getClasse();
        $this->data['exclusao'] = false;

        $ret = new \stdClass();
        $ret->data = montaListaColunasEnt($this->data, 'cla_id', $dados_classe, $campos[1]);

        $ret->exclusao = false;
        cache()->save('classe', $ret, 60000);

        return $this->response->setJSON($ret);
    }

    /**
     * Ordenação
     * ordenar
     *
     * @return void
     */
    public function ordenar()
    {
        // Busca a lista de classes já ordenadas
        $lst_classes = $this->classes->getClasseOrdem();
        $lst_classes = array_map(fn($row) => (array) $row, $lst_classes);
        $data        = new \stdClass();

        // Objeto para envio de dados à view
        $data->controler        = $this->data['controler'];
        $data->metodo           = 'ordenar';
        $data->it_menu          = $this->data['it_menu'] ?? null;
        $data->icone            = null;
        $data->regras_cadastro  = false;
        $data->identificador    = null;

        // Descrição exibida na tela
        $data->desc_metodo = 'Ordenação de ';
        $data->lst_classe  = $lst_classes;
        $data->destino     = 'storeOrd';

        echo view('vw_classe_ordenar', (array) $data);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {

        $entity = new EntProClasse();
        $fieldsGerais = $entity->campos;
        $fieldsClass  = $entity->defCamposClassif();
        $fieldsMicro  = $entity->defCamposMicro();

        $secoes = new \stdClass();
        $secoes->dados_gerais   = 'Dados Gerais';
        $secoes->classificacao  = 'Classificação';
        $secoes->microbiologico = 'Microbiológico';

        $displ = new \stdClass();
        $displ->classificacao = 'tabela';

        $campos = new \stdClass();

        // Dados Gerais 
        $campos->dados_gerais = [];
        $campos->dados_gerais[] = $fieldsGerais['cla_id'];
        $campos->dados_gerais[] = $fieldsGerais['cla_nome'];
        $campos->dados_gerais[] = $fieldsGerais['cla_requisicao'];
        $campos->dados_gerais[] = $fieldsGerais['cla_insvis'];
        $campos->dados_gerais[] = "<div class='col-1'></div>" . $fieldsGerais['cla_insvisconf'];
        $campos->dados_gerais[] = $fieldsGerais['cla_formula'];
        $campos->dados_gerais[] = $fieldsGerais['cla_estdataatual'];
        $campos->dados_gerais[] = $fieldsGerais['cla_gestaoestoque'];
        $campos->dados_gerais[] = $fieldsGerais['cla_dash_consumo'];
        $campos->dados_gerais[] = $fieldsGerais['cla_deposito'];

        // Classificação 
        $campos->classificacao = [];

        $linhaClass = [];
        $linhaClass[] = $fieldsClass['pcl_id'];
        $linhaClass[] = $fieldsClass['ori_codOri'];
        $linhaClass[] = $fieldsClass['fam_codFam'];
        // debug(['fam_codFam' => $fieldsClass['fam_codFam']], true);
        $linhaClass[] = $fieldsClass['bt_add'];
        $linhaClass[] = $fieldsClass['bt_del'];
        $campos->classificacao[] = $linhaClass;

        // Microbiológico 
        $campos->microbiologico = [];
        $campos->microbiologico[] = $fieldsMicro['cla_micro'];
        $campos->microbiologico[] = $fieldsMicro['cla_metodanalise'];
        $campos->microbiologico[] = $fieldsMicro['cla_cabecalho'];
        $campos->microbiologico[] = $fieldsMicro['cla_rodape'];

        $data = new \stdClass();
        $data->secoes = array_values((array) $secoes);
        $data->campos = array_values((array) $campos);
        $data->displ = ['', 'tabela', ''];

        $data->destino         = 'store';
        $data->controler       = $this->data['controler'];
        $data->metodo          = 'add';
        $data->regras_cadastro = false;
        $data->icone           = null;
        $data->identificador   = null;
        $data->it_menu         = $this->data['it_menu'] ?? null;

        $data->script          = "<script>
                                     acerta_botoes_rep('classificacao');
                                     mostraOcultaCampo('cla_insvis','S','cla_insvisconf');
                                     mostraOcultaCampo('cla_micro','S','cla_metodanalise,cla_cabecalho,cla_rodape');
                                 </script>";

        echo view('vw_edicao', (array) $data);
    }


    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        // Busca os dados da classe pelo ID
        $dados = $this->classes->getClasseEdicao($id);

        // debug($dados, true);

        if (!$dados) {
            return view('errors/vw_semregistro', [
                'mensagem' => 'Classe de Produto não encontrada'
            ]);
        }

        $entClasse = new EntProClasse($dados[0], $show);
        $fieldsGerais = $entClasse->defCampos($show);

        $secao[0] = 'Dados Gerais';
        $campos[0] = [
            $fieldsGerais['cla_id'],
            $fieldsGerais['cla_nome'],
            $fieldsGerais['cla_requisicao'],
            $fieldsGerais['cla_insvis'],
            "<div class='col-1'></div>" . $fieldsGerais['cla_insvisconf'],
            $fieldsGerais['cla_formula'],
            $fieldsGerais['cla_estdataatual'],
            $fieldsGerais['cla_gestaoestoque'],
            $fieldsGerais['cla_dash_consumo'],
            $fieldsGerais['cla_deposito'],
        ];

        $secao[1] = 'Classificação';
        $displ[1] = 'tabela';
        if (count($dados) > 0) {
            // Percorre classificações existentes
            // debug($dados);
            for ($cl = 0; $cl < count($dados); $cl++) {
                // Define campos configurados
                $fields = $entClasse->defCamposClassif($dados[$cl], $cl, $show);

                $campos[1][$cl][] = $fields['pcl_id'];
                $campos[1][$cl][] = $fields['ori_codOri'];
                $campos[1][$cl][] = $fields['fam_codFam'];
                $campos[1][$cl][] = $show ? '' : $fields['bt_add'];
                $campos[1][$cl][] = $show ? '' : $fields['bt_del'];
            }
        } else {
            // Caso não existam classificações cadastradas
            $fields = $entClasse->defCamposClassif(false, 0, $show);

            $campos[1][0][] = $fields['pcl_id'];
            $campos[1][0][] = $fields['ori_codOri'];
            $campos[1][0][] = $fields['fam_codFam'];
            $campos[1][0][] = $show ? '' : $fields['bt_add'];
            $campos[1][0][] = $show ? '' : $fields['bt_del'];
        }

        $fieldsMicro  = $entClasse->defCamposMicro($dados[0], $show);


        //Microbiológico 
        $secao[2] = 'Microbiológico';
        $campos[2][] = $fieldsMicro['cla_micro'];
        $campos[2][] = $fieldsMicro['cla_metodanalise'];
        $campos[2][] = $fieldsMicro['cla_cabecalho'];
        $campos[2][] = $fieldsMicro['cla_rodape'];


        // Configurações da tela
        $this->data['desc_edicao'] = $dados[0]['cla_nome'];
        // $this->data['log'] = buscaLog('pro_classe', $id);
        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ;
        $this->data['campos'] = $campos;
        // debug($campos, true);
        $this->data['destino'] = 'store';

        $this->data['script'] = "<script>
                             acerta_botoes_rep('classificacao');
                             mostraOcultaCampo('cla_insvis','S','cla_insvisconf');
                             mostraOcultaCampo('cla_micro','S','cla_metodanalise,cla_cabecalho,cla_rodape');
                             acertaDependente();
                         </script>";


        echo view('vw_edicao', $this->data);
    }

    public function ativinativ($id, $tipo)
    {


        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'cla_ativo' => 'A'
                ];
            } else {
                $dad_atin = [
                    'cla_ativo' => 'I'
                ];
                $this->verificarUsoEmRelacionamentos('pro_classe', 'cla_id', (int) $id);
            }
            $this->classes->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Classe de Produto Alterada com Sucesso');
            $ret['msg']  = 'Classe de Produto Alterada com Sucesso';
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            // $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
            $ret['msg']  = 14;
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 14; // ou código personalizado, se preferir
        }
        echo json_encode($ret);
    }


    /**
     * Summary of addCampo
     * @param mixed $ind
     * @return never
     */
    public function addCampo($ind)
    {
        $show = false;

        $entityClass = new EntProClasse();
        $fields = $entityClass->defCamposClassif(false, $ind, $show);

        $campo = [];
        $campo[0] = $fields['pcl_id'];
        $campo[1] = $fields['ori_codOri'];
        $campo[2] = $fields['fam_codFam'];
        $campo[3] = $fields['bt_add'];
        $campo[4] = $fields['bt_del'];

        echo json_encode($campo);
        exit;
    }

    public function storeOrd()
    {
        // Recupera dados enviados pelo formulário
        $req = $this->request->getPost();
        $ord = 1;

        // Percorre os IDs recebidos e atualiza a ordem
        foreach ($req as $key => $value) {
            $updt = [
                'cla_ordem' => $ord
            ];
            $this->classes->update($value, $updt);
            $ord++;
        }
        // debug($ord_, true);
        $ret['erro'] = false;
        $ret['msg']  = 'Classes Reordenadas com Sucesso!!!';
        session()->setFlashdata('msg', $ret['msg']);
        $ret['url']  = site_url($this->data['controler']);
        echo json_encode($ret);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $postado = $this->request->getPost();
        $ret     = ['erro' => false];
        $erros   = [];

        $pcl_id     = $postado['pcl_id']     ?? [];
        $ori_codOri = $postado['ori_codOri'] ?? [];
        $fam_codFam = $postado['fam_codFam'] ?? [];

        // Garante que são arrays
        if (!is_array($pcl_id))     $pcl_id = [];
        if (!is_array($ori_codOri)) $ori_codOri = [];
        if (!is_array($fam_codFam)) $fam_codFam = [];

        // Remove do payload da classe
        unset($postado['pcl_id'], $postado['ori_codOri'], $postado['fam_codFam']);

        // cla_id pode vir como array
        if (isset($postado['cla_id']) && is_array($postado['cla_id'])) {
            $postado['cla_id'] = $postado['cla_id'][0] ?? null;
        }

        // Nome pode vir como array em alguns casos
        if (isset($postado['cla_nome']) && is_array($postado['cla_nome'])) {
            $postado['cla_nome'] = $postado['cla_nome'][0] ?? '';
        }

        // Select simples (criaSelectRelativo costuma mandar array)
        if (isset($postado['cla_dash_consumo']) && is_array($postado['cla_dash_consumo'])) {
            $postado['cla_dash_consumo'] = $postado['cla_dash_consumo'][0] ?? '';
        }

        // Radios Sim/Não (cr2opcoes)
        $camposSimNao = [
            'cla_requisicao',
            'cla_insvis',
            'cla_insvisconf',
            'cla_micro',
            'cla_metodanalise',
            'cla_formula',
            'cla_estdataatual',
            'cla_gestaoestoque',
        ];
        foreach ($camposSimNao as $campo) {
            if (isset($postado[$campo]) && is_array($postado[$campo])) {
                $postado[$campo] = $postado[$campo][0] ?? 'N';
            }
        }

        // Multiple
        if (isset($postado['cla_deposito']) && is_array($postado['cla_deposito'])) {
            $postado['cla_deposito'] = implode(
                ', ',
                array_filter(is_array($postado['cla_deposito'][0] ?? null)
                    ? $postado['cla_deposito'][0]
                    : $postado['cla_deposito'])
            );
        }

        $update = !empty($postado['cla_id']);

        if (!$update) {
            $ultima = $this->classes->getUltimaOrdemClasse();

            // compatível com retorno array ou objeto
            $ultimaValor = 0;
            if (is_array($ultima) && isset($ultima[0])) {
                $ultimaValor = is_array($ultima[0]) ? ($ultima[0]['ultima'] ?? 0) : ($ultima[0]->ultima ?? 0);
            }

            $postado['cla_ordem'] = (int)$ultimaValor + 1;
        }

        if ($update) {
            $cla_id = (int)$postado['cla_id'];

            // Banco
            $classif_db = $this->classes->getClasseClassificacao($cla_id);
            $db_norm = [];

            if (is_array($classif_db)) {
                foreach ($classif_db as $c) {
                    // compatível com array/obj
                    $ori = is_array($c) ? ($c['ori_codOri'] ?? '') : ($c->ori_codOri ?? '');
                    $fam = is_array($c) ? ($c['fam_codFam'] ?? '') : ($c->fam_codFam ?? '');
                    if ($ori !== '' && $fam !== '') {
                        $db_norm[] = $ori . '|' . $fam;
                    }
                }
            }

            // Tela (mantém ordem)
            $post_norm = [];

            foreach ($ori_codOri as $k => $ori) {

                $fams = $fam_codFam[$k] ?? [];
                if (!is_array($fams)) {
                    $fams = [$fams];
                }

                foreach ($fams as $fam) {

                    while (is_array($ori)) {
                        $ori = reset($ori);
                    }
                    $ori = (string) $ori;

                    while (is_array($fam)) {
                        $fam = reset($fam);
                    }
                    $fam = (string) $fam;

                    if ($ori !== '' && $fam !== '') {
                        $post_norm[] = $ori . '|' . $fam;
                    }
                }
            }


            // Se houve qualquer alteração (inclui/exclui/edita/reordena)
            if ($db_norm !== $post_norm) {
                $produtos = new ProdutProdutoModel();
                $existe   = $produtos->getProdutoClasse($cla_id);

                if (is_array($existe) && count($existe) > 0) {
                    echo json_encode([
                        'erro' => true,
                        'msg'  => 15
                    ]);
                    return;
                }
            }
        }

        $exists = $this->common->verificaUnico(
            $this->classes,
            'cla_nome',
            $postado['cla_nome'] ?? '',
            'cla_id',
            $postado['cla_id'] ?? null
        );

        if ($exists > 0) {
            echo json_encode(['erro' => true, 'msg' => 8]);
            return;
        }

        unset($postado['fam_codFam']);
        $salva = $update
            ? $this->classes->update($postado['cla_id'], $postado)
            : $this->classes->insert($postado);

        if (!$salva) {
            echo json_encode(['erro' => true, 'msg' => $this->classes->errors()]);
            return;
        }

        $cla_id   = $update ? (int)$postado['cla_id'] : (int)$this->classes->getInsertID();
        $data_atu = date('Y-m-d H:i:s');

        $ordem = 0;

        foreach ($ori_codOri as $k => $ori) {

            // origem
            if (is_array($ori)) {
                $ori = reset($ori);
            }
            $ori = (string) $ori;

            // famílias (MULTIPLE)
            $fams = $fam_codFam[$k] ?? [];
            if (!is_array($fams)) {
                $fams = [$fams];
            }

            foreach ($ori_codOri as $ori) {

                if (is_array($ori)) {
                    $ori = reset($ori);
                }
                $ori = (string)$ori;

                foreach ($fam_codFam as $fam) {

                    if (is_array($fam)) {
                        $fam = reset($fam);
                    }
                    $fam = (string)$fam;

                    if ($ori !== '' && $fam !== '') {

                        $sql_pcl = [
                            'cla_id'         => $cla_id,
                            'ori_codOri'     => $ori,
                            'fam_codFam'     => $fam,
                            'pcl_atualizado' => $data_atu,
                            'pcl_ordem'      => $ordem++
                        ];

                        $ok = $this->common->insertReg(
                            'dbProduto',
                            'pro_classe_classificacao',
                            $sql_pcl
                        );

                        if (!$ok) {
                            echo json_encode(['erro' => true, 'msg' => 15]);
                            return;
                        }
                    }
                }
            }
        }

        // limpa as antigas
        $this->common->deleteReg(
            'dbProduto',
            'pro_classe_classificacao',
            "cla_id = {$cla_id} AND pcl_atualizado != '{$data_atu}'"
        );

        cache()->clean();

        $msg = 'Classe de Produto gravada com sucesso!';
        session()->setFlashdata('msg', $msg);

        echo json_encode([
            'erro' => false,
            'msg'  => $msg,
            'url'  => site_url($this->data['controler'])
        ]);
        return;
    }
}
