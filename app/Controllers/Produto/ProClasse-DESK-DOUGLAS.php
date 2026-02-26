<?php

namespace App\Controllers\Produto;

use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Controllers\BaseController;
use function PHPUnit\Framework\isNan;
use App\Entities\Produto\EntProClasse;
use App\Traits\ForeignKeyUsageChecker;

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
        $this->classes    = new ProdutClasseModel();
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
        $this->bt_order = $order->crBotao();
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
        // debug($dados_classe, true);
        $this->data['exclusao'] = false;
        $classe = [
            'data' => montaListaColunasEnt($this->data, 'cla_id', $dados_classe, $campos[1]),
        ];
        cache()->save('classe', $classe, 60000);

        echo json_encode($classe);
    }

    /**
     * Ordenação
     * ordenar
     *
     * @return void
     */
    public function ordenar()
    {
        $lst_classes     =  $this->classes->getClasseOrdem();
        // debug($lst_status, true);
        $this->data['desc_metodo'] = 'Ordenação de ';
        $this->data['lst_classe']    = $lst_classes;
        $this->data['destino']    = 'storeOrd';

        echo view('vw_classe_ordenar', $this->data);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $entProClasse = new EntProClasse();

        $this->data['secoes']  = ['Dados Gerais', 'Classificação', 'Microbiológico'];
        $this->data['campos']  = [
            [
                $entProClasse->campos['cla_id'],
                $entProClasse->campos['cla_nome'],
                $entProClasse->campos['cla_requisicao'],
                $entProClasse->campos['cla_insvis'],
                $entProClasse->campos['cla_insvisconf'],
                $entProClasse->campos['cla_formula'],
                $entProClasse->campos['cla_estdataatual'],
                $entProClasse->campos['cla_gestaoestoque'],
                $entProClasse->campos['cla_dash_consumo'],
                $entProClasse->campos['cla_deposito'],
            ],
        ];
        $fields = $entProClasse->defCamposClassif();
        $campos1[0][] = $fields['pcl_id'];
        $campos1[0][] = $fields['ori_codOri'];
        $campos1[0][] = $fields['fam_codFam'];
        $campos1[0][] = $fields['bt_add'];
        $campos1[0][] = $fields['bt_del'];

        $this->data['campos'][1]  = $campos1;
        $this->data['displ'][1]  = 'tabela';


        $fields2 = $entProClasse->defCamposMicro();
        $campos2[] = $fields2['cla_micro'];
        $campos2[] = $fields2['cla_metodanalise'];
        $campos2[] = $fields2['cla_cabecalho'];
        $campos2[] = $fields2['cla_rodape'];

        $this->data['campos'][2]  = $campos2;

        $this->data['destino'] = 'store';


        $this->data['script'] = "<script>
                                    acerta_botoes_rep('classificacao');
                                    mostraOcultaCampo('cla_insvis','S','cla_insvisconf');
                                    mostraOcultaCampo('cla_micro','S','cla_metodanalise,cla_cabecalho,cla_rodape');
                                </script>";
        $this->data['destino']    = 'store';
        echo view('vw_edicao', $this->data);
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
        $dados_classes = $this->classes->getClasse($id);
        // debug($dados_classes, true);
        // $fields = $this->classes->defCampos($dados_classes, $show);
        $entProClasse = new EntProClasse((array) $dados_classes);
        $fields = $entProClasse->defCampos((array) $dados_classes, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['cla_id'];
        $campos[0][count($campos[0])] = $fields['cla_nome'];
        $campos[0][count($campos[0])] = $fields['cla_requisicao'];
        $campos[0][count($campos[0])] = $fields['cla_insvis'];
        $campos[0][count($campos[0])] = $fields['cla_gestaoestoque'];
        $campos[0][count($campos[0])] = $fields['cla_formula'];
        $campos[0][count($campos[0])] = $fields['cla_insvisconf'];
        $campos[0][count($campos[0])] = $fields['cla_estdataatual'];
        $campos[0][count($campos[0])] = $fields['cla_dash_consumo'];
        $campos[0][count($campos[0])] = $fields['cla_deposito'];
        // $campos[0][count($campos[0])] = 'vazio2';
        // $campos[0][count($campos[0])] = 'vazio2';

        $secao[1] = 'Classificação';
        $displ[1] = 'tabela';
        $dados_classes_classif = $this->classes->getClasseClassificacao($id);
        // debug($dados_classes_classif,true);
        if (count($dados_classes_classif) > 0) {
            for ($c = 0; $c < count($dados_classes_classif); $c++) {
                $fields = $entProClasse->defCamposClassif($dados_classes_classif[$c], $c, $show);
                $campos[1][$c][0] = $fields['pcl_id'];
                $campos[1][$c][count($campos[1][$c])] = $fields['ori_codOri'];
                $campos[1][$c][count($campos[1][$c])] = $fields['fam_codFam'];
                if (!$show) {
                    $campos[1][$c][count($campos[1][$c])] = $fields['bt_add'];
                    $campos[1][$c][count($campos[1][$c])] = $fields['bt_del'];
                } else {
                    $campos[1][$c][count($campos[1][$c])] = '';
                    $campos[1][$c][count($campos[1][$c])] = '';
                }
            }
        } else {
            $fields = $entProClasse->defCamposClassif();
            $campos[1][0] = [];
            $campos[1][0][count($campos[1][0])] = $fields['pcl_id'];
            $campos[1][0][count($campos[1][0])] = $fields['ori_codOri'];
            $campos[1][0][count($campos[1][0])] = $fields['fam_codFam'];
            if (!$show) {
                $campos[1][0][count($campos[1][0])] = $fields['bt_add'];
                $campos[1][0][count($campos[1][0])] = $fields['bt_del'];
            } else {
                $campos[1][0][count($campos[1][0])] = '';
                $campos[1][0][count($campos[1][0])] = '';
            }
        }
        // debug($campos[1]);
        $fields = $entProClasse->defCamposMicro((array) $dados_classes, $show);
        $secao[2] = 'Microbiológico';
        $campos[2] = [];
        $campos[2][count($campos[2])] = $fields['cla_micro'];
        $campos[2][count($campos[2])] = $fields['cla_metodanalise'];
        $campos[2][count($campos[2])] = $fields['cla_cabecalho'];
        $campos[2][count($campos[2])] = $fields['cla_rodape'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']      = $displ;
        $this->data['destino']    = 'store';

        $this->data['script'] = "<script>
                                    acerta_botoes_rep('classificacao');
                                    mostraOcultaCampo('cla_insvis','S','cla_insvisconf');
                                    mostraOcultaCampo('cla_micro','S','cla_metodanalise,cla_cabecalho,cla_rodape');
                                </script>";
        $this->data['desc_edicao'] = $dados_classes->cla_nome;

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('pro_classe', $id);

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

            $this->status->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Classe de Produto Alterada com Sucesso');
            $ret['msg'] = 'Classe de Produto Alterada com Sucesso';
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
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        $ret = [];

        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('pro_classe', 'cla_id', (int) $id);

            // Soft delete
            $this->classes->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Classe de Produto excluída com sucesso!');
            $ret['msg']  = 'Classe de Produto excluída com Sucesso!';
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
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
        $entProClasse = new EntProClasse();
        
        $fields = $entProClasse->defCamposClassif(false, $ind);
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
        $req = $this->request->getPost();
        $ord = 1;
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
        // debug($postado, true);
        $ret['erro'] = false;
        $erros = [];

        $update = false;
        // Verifica se é uma inclusão ou atualização
        if (empty($postado['cla_id'])) {
            // Gera próxima ordem
            $ultima = $this->classes->getUltimaOrdemClasse();
            $postado['cla_ordem'] = (int) ($ultima[0]['ultima'] ?? 0) + 1;
        } else {
            $update = true;
        }
        // Trata o campo 'cla_deposito' como string separada por vírgula
        if (!empty($postado['cla_deposito']) && is_array($postado['cla_deposito'])) {
            $deposito = $this->request->getPost('cla_deposito');

            $postado['cla_deposito'] = implode(', ',
                                                array_map(
                                                    fn(array $item): string => $item[0] ?? '',
                                                    $deposito
                                                )
                                            );
            // $postado['cla_deposito'] = implode(', ', array_filter($postado['cla_deposito']));
        }
        $famCodFam = $this->request->getPost('fam_codFam');

        $famCodFam = array_merge(...$famCodFam);

        $postado['fam_codFam'] = $famCodFam;

        // debug($postado, true);

        $ent = new EntProClasse();
        $ent->fill($postado);


        $exists = $this->common->verificaUnico($this->classes, 'cla_nome', $postado['cla_nome'], 'cla_id', $postado['cla_id']);
        if (intval($exists) > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
            $erros = [8];
        } else {
            $this->classes->transBegin();

            try {
                // debug($ent, true);
                $salva = $this->classes->save($ent);
                // debug($this->status->getLastQuery(), true);

                if (!$salva) {
                    $ret['erro'] = true;
                    $ret['msg']  = implode('<br>', $this->classes->errors());
                    // return;
                } else {
                    if ($postado['cla_id'] == '') {
                        $postado['cla_id'] = $this->classes->getInsertId();
                    }
                    $cla_id = $postado['cla_id'];
                    $data_atu = date('Y-m-d H:i');

                    // GRAVAÇãO DOS Movimentos
                    $ordem = 0;
                    foreach ($postado['pcl_id'] as $key => $value) {
                        if (
                            $postado['ori_codOri'][$key] != '' &&
                            $postado['fam_codFam'][$key] != ''
                        ) {
                            $sql_pcl = [
                                'pcl_id' => $postado['pcl_id'][$key],
                                'cla_id' => $cla_id,
                                'ori_codOri' => $postado['ori_codOri'][$key],
                                'fam_codFam' => $postado['fam_codFam'][$key],
                                'pcl_atualizado' => $data_atu,
                                'pcl_ordem'     => $ordem
                            ];
                            $ordem++;
                            if ($postado['pcl_id'][$key] == '') {
                                $pcl_id = $this->common->insertReg('dbProduto', 'pro_classe_classificacao', $sql_pcl);
                            } else {
                                $pcl_id = $this->common->updateReg('dbProduto', 'pro_classe_classificacao', "pcl_id = " . $postado['pcl_id'][$key], $sql_pcl);
                            }
                            if (!$pcl_id) {
                                $ret['erro'] = true;
                                $erros = ['Não foi possível gravar as Classificações da Classe, Verifique!'];
                            }
                        }
                    }
                    if (!$ret['erro']) {
                        $this->common->deleteReg("dbProduto", "pro_classe_classificacao", "cla_id = " . $cla_id . " AND pcl_atualizado != '" . $data_atu . "'");
                    }
                }
                if (!$ret['erro']) {
                    $this->classes->transCommit();
                    session()->setFlashdata('msg', 'Classe de Produtos gravada com sucesso!');
                    $ret['erro'] = false;
                    $ret['url'] = site_url($this->data['controler']);
                }

            } catch (\Throwable $e) {
                $this->classes->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Erro ao salvar Classe.'
                ];
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                    if (is_numeric($erro)) {
                        $ret['msg'] = $erro;
                    }
                }
            }
        }
        echo json_encode($ret);
        cache()->clean();
    }
}
