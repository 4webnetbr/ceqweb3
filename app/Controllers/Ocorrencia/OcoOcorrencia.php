<?php

namespace App\Controllers\Ocorrencia;

use App\Controllers\BaseController;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Traits\ForeignKeyUsageChecker;

class OcoOcorrencia extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $modelTipo;
    public $modelMod;
    public $ocorrencia;
    public $produtLoteModel;
    public $common;
    public $permissao = '';

    public function __construct()
    {
        $this->data = session()->get('dados_tela') ?? [];
        $this->permissao = $this->data['permissao'];

        $this->modelTipo  = new OcorreTipoOcorrenciaModel();
        $this->modelMod   = new OcorreModOcorrenciaModel();
        $this->ocorrencia = new OcorreOcorrenciaModel();
        $this->common     = new CommonModel();

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
     * Tela de abertura
     * index
     *
     * @param mixed $id 
     * @return void
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Tela de listagem
     * lista
     *
     * @param mixed $id 
     * @return void
     */
    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'oco_id');

        $dados = $this->ocorrencia->getListaCompleta();
        // debug($dados, true);
        $oco_ids_assoc = array_map(fn($o) => $o->oco_id, $dados);

        $log = buscaLogTabela('oco_ocorrencia', $oco_ids_assoc);
        $base_url = base_url('OcoTrataOcorrencia');


        foreach ($dados as $nov) {

            $usuLog = $log[$nov->oco_id]['usua_alterou'] ?? '';

            // Gerado por (sempre)
            $nov->usu_nome = $usuLog;

            // Finalizado por (somente se FINALIZADA)
            if ((int)$nov->stt_id === 30) {
                $nov->usu_fina = $usuLog;
            } else {
                $nov->usu_fina = '';
            }

            $nov->acao_person = [];


            // Botão imprimir
            if (trim($nov->stt_nome ?? '') !== 'Pendente') {
                $url_imp = base_url('/CriaPdf2025/PrintOcorrencia/' . $nov->oco_id);
                $nov->acao_person[] = "
                     <button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0'
                         title='Imprimir'
                         onclick='openPDFModal(\"{$url_imp}\",\"Imprimir Ocorrência\")'>
                         <i class='fa-solid fa-print'></i>
                     </button>
                 ";
            }

            // Botão finalizar se pendente
            if (trim($nov->stt_nome ?? '') === 'Pendente') {
                $url_finalizar = $base_url . '/finalizar/' . $nov->oco_id;
                $nov->acao_person[] = "
                    <button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0'
                        title='Finalizar'
                        onclick='redireciona(\"$url_finalizar\")'>
                        <i class='fas fa-check'></i>
                    </button>
                ";
            }
        }
        // debug($dados, true);
        $ret = new \stdClass();
        $this->data['allconsulta'] = true;
        $listaFinal = [];

        foreach ($dados as $index => $nov) {
            // SE TEM REQUISIÇÃO: NÃO PERMITE EDITAR e EXCLUIR
            if (!empty($nov->req_id)) {
                $this->data['exclusao'] = false;
                $this->data['edicao']   = false;
            } else {
                $this->data['exclusao'] = true;
                $this->data['edicao']   = true;
            }
            $linha = montaListaColunasEnt($this->data, 'oco_id', [$nov], $campos[1]);
            $listaFinal[] = $linha[0];
        }

        $ret->data = $listaFinal;

        return $this->response->setJSON($ret);
    }

    /**
     * inclusão
     * add
     *
     * @param mixed $id 
     * @return void
     */
    public function add()
    {
        // Instancia a entity
        $oco = new EntOcoOcorrencia();
        $fields = $oco->campos;
        // Dados Gerais
        $this->data['title']       = 'Ocorrência';
        // $this->data['desc_metodo'] = 'Nova ';
        $this->data['secoes']      = ['Dados Gerais'];

        // define os campos
        $this->data['campos']  = [[
            $fields['oco_id'],
            $fields['tpo_id'],
            $fields['sut_id'],
            $fields['oco_descricao'],
            $fields['pro_id'],
            $fields['lot_id'],
            $fields['lot_lote'],
            $fields['pro_despro'],
            $fields['oco_qtd'],
            $fields['oco_data'],
        ]];
        $this->data['destino'] = 'store';

        // Renderiza a view
        echo view('vw_edicao', $this->data);
    }

    /**
     * Visualização
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $dados = $this->ocorrencia->getOcorrencia($id);
        // debug($dados, true);

        // Valida se a ocorrência existe
        if (!$dados) {
            return redirectWithError($this->data['controler'],41);
        }

        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? null;
        // Instancia a entity
        // debug($dados, true);

        // dados gerais
        $secao = ['Dados Gerais'];
        $campos = $this->showCabecalho($dados);
        // debug($campos, true);

        // BLOCO TELAS APLICÁVEIS
        $entity   = new EntOcoModOcorrencia((array) $dados);
        $modModel = new OcorreModOcorrenciaModel();
        $oco   = $this->ocorrencia->getOcorrencia($id);
        $telas = array_map(
            fn($item) => (array) $item,
            $modModel->getTOTelasAplicaveis($oco->sut_id)
        );
        // debug($telas, true);

        if (!empty($telas)) {

            $telasResultado = [];
            $total = count($telas);

            for ($c = 0; $c < $total; $c++) {
                $fields = $entity->defCamposTelasAplicaveis($telas[$c], $c, $total, true);
                $telasResultado[] = $fields;
            }

            $campos[0][] = view(
                'partials/pw_telas_aplicaveis_ocorrencia',
                [
                    'telas'  => $telasResultado,
                    'oco_id' => $id
                ]
            );
        }

        // BLOCO DAS AÇÕES 
        $entity = new EntOcoTratativa($dados, true);
        $acoes = $this->ocorrencia->getAcoesFinalizar($id);
        // debug($acoes, true);
        if (!empty($acoes)) {

            $acoesResultado = [];

            foreach ($acoes as $acao) {
                $acao->somente_leitura = true;
                $camposAcao = $entity->defCamposAcao($acao);
                $acoesResultado[] = $camposAcao;
            }

            $campos[0][] = view(
                'partials/pw_acoes_ocorrencia',
                [
                    'acoes'  => $acoesResultado,
                    'oco_id' => $id
                ]
            );
        }

        $this->data['title']       = 'Ocorrência';
        $this->data['desc_edicao'] = ' Req. Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' - ' .  fmtEtiquetaCor($dados->stt_cor, $dados->stt_nome, 1);
        $this->data['secoes']      = $secao;
        $this->data['campos']      = $campos;
        $this->data['destino']     = '';

        echo view('vw_edicao', $this->data);
    }

    /**
     * visualização cabeçalho
     * showCabecalho
     *
     * @param mixed $id 
     * @return void
     */
    public function showCabecalho($dados)
    {
        $oco    = new EntOcoOcorrencia((array) $dados, true);
        $fields = $oco->campos;
        // define os campos
        $campos[] = [
            $fields['oco_id'],
            $fields['tpo_id'],
            $fields['sut_id'],
            $fields['oco_data'],
            $fields['usu_nome'],
            $fields['pro_id'],
            $fields['cod_erp'],
            $fields['cod_erp_show'],
            $fields['lot_id'],
            $fields['lot_lote'],
            $fields['fab_nomFab'],
            $fields['lot_validade_show'],
            $fields['lot_validade'],
            $fields['pro_despro'],
            $fields['oco_qtd'],
            $fields['oco_descricao'],
        ];

        return $campos;
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id)
    {
        // Busca a ocorrência pelo ID
        $dados = $this->ocorrencia->getOcorrencia($id);

        // Valida se a ocorrência existe
        if (!$dados) {
            return redirectWithError($this->data['controler'],41);
        }

        // Instancia a entity
        // debug($dados, true);
        $oco    = new EntOcoOcorrencia((array) $dados, false);
        $fields = $oco->campos;

        // dados gerais
        $this->data['secoes'] = ['Dados Gerais'];

        // define os campos
        $this->data['campos'] = [[
            $fields['oco_id'],
            $fields['tpo_id'],
            $fields['sut_id'],
            $fields['oco_descricao'],
            $fields['pro_id'],
            $fields['lot_id'],
            $fields['lot_lote'],
            $fields['pro_despro'],
            $fields['oco_qtd'],
            $fields['oco_data'],
        ]];

        $this->data['destino'] = "store";
        // $this->data['edicao']  = true;
        $this->data['title']       = 'Ocorrência';
        $this->data['desc_edicao'] = ' Nº ' . str_pad($id, 6, '0', STR_PAD_LEFT);

        echo view('vw_edicao', $this->data);
    }

    /**
     * Model
     * addOutraTela
     *
     * @param mixed $id 
     * @return void
     */
    public function addOutraTela()
    {
        $request = service('request');

        // Lê e decodifica o JSON recebido
        $json = $request->getJSON(true); // true = como array associativo
        // Agora você pode acessar normalmente:
        $proId    = $json['pro_id'] ?? null;
        $lotlote  = $json['lot_lote'] ?? null;
        $reqId    = $json['req_id'] ?? null;
        $telId    = $json['tel_id'] ?? null;

        $config['Leitura']  = true;
        $config['Label']    = "Tela";
        $config['DispForm'] = "col-6";

        $tel_id = criaSelectRelativo(
            'cfg_tela',
            'tel_id',
            'tel_nome',
            $telId,
            1,
            'oco_ocorrencia',
            [],
            $config
        );
        $modProd = new ProdutProdutoModel();
        $dadosProd = $modProd->getProduto($proId)[0];

        $config['Label'] = "Tipo de Ocorrência";
        $config['Leitura'] = false;
        $filtros = [
            'tel_id' => [$telId],
            'cla_id' => [$dadosProd->cla_id],
        ];

        $toc_oc = criaSelectRelativo(
            'vw_oco_tpo_ocorrencia_relac',
            'tpo_id',
            'tpo_nome',
            null,
            1,
            'oco_ocorrencia',
            $filtros,
            $config
        );

        $config['Label'] = "Subtipo de Ocorrência";
        $config['Pai'] = "tpo_id";
        $config['Urlbusca'] = base_url('Buscas/buscaSubtipoPorTipo');

        $mod_oc = criaSelectRelativo(
            'vw_oco_subt_ocorrencia_relac',
            'sut_id',
            'sut_nome',
            null,
            2,
            'oco_ocorrencia',
            ['tel_id' => ''],
            $config
        );

        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->dispForm    = 'col-6';
        $descreva = $desc->crInput();

        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->dispForm     = 'col-6';
        $qtd->minimo       = 1;
        $qtd->largura      = 10;
        $qtd->size         = 3;
        $qtd->maximo       = 999;
        $qtd->obrigatorio  = true;
        $quantia = $qtd->crInput();

        // Instancia a entity
        $dados['lot_lote'] = $lotlote;
        $dados['req_id']   = $reqId;
        $oco = new EntOcoOcorrencia($dados, true);

        // Dados Gerais
        $this->data['secoes']  = ['Dados Gerais'];

        // define os campos
        $this->data['campos']  = [[
            $tel_id,
            $toc_oc,
            $oco->campos['lot_id'],
            $oco->campos['lot_lote'],
            $mod_oc,
            $oco->campos['pro_id'],
            $oco->campos['pro_despro'],
            $quantia,
            $oco->campos['oco_data'],
            $descreva,
        ]];
        $this->data['destino'] = 'store';
        $this->data['desc_metodo'] = 'Nova Ocorrência';
        $this->data['script'] = "<script>
                                    var elemento = document.getElementById('lot_lote');
                                    buscaLoteProduto(elemento,'" . base_url('/buscas/buscaProdutoporLote') . "')
                                </script>";
        $this->data['script'] .= "  <script>
                                       jQuery(document).ready(function() {
                                           var \$sut = jQuery('#sut_id');
                                           if (\$sut.hasClass('selectpicker')) {
                                               \$sut.selectpicker('destroy'); 
                                               \$sut.removeAttr('title');     
                                               \$sut.selectpicker();         
                                           }
                                       });
                                    </script>";
        $this->data['hidden'][] = [
            'name'  => 'req_id',
            'value' => $reqId
        ];

        // Renderiza a view
        echo view('vw_edicao_modal', $this->data);
    }


    // public function getProdutoLote()
    // {
    //     // Recupera o código do lote enviado via POST
    //     $codLote = $this->request->getPost('codLote');
    //     // Valida se o lote foi informado
    //     if (!$codLote) {
    //         return $this->response->setJSON([
    //             'erro' => 'Lote não informado'
    //         ]);
    //     }

    //     $dados = $this->produtLoteModel->getLoteSearch($codLote);

    //     // Valida se o lote foi encontrado
    //     if (!$dados || empty($dados)) {
    //         return $this->response->setJSON([
    //             'erro' => 'Lote não encontrado'
    //         ]);
    //     }
    //     $lote = $dados[0];

    //     // Retorna a descrição do produto vinculada ao lote
    //     return $this->response->setJSON([
    //         'descpro' => $lote->pro_despro ?? '',
    //     ]);
    // }


    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        try {

            $oco = $this->ocorrencia->getOcorrencia($id);

            if (!$oco) {
                throw new \Exception('Ocorrência não encontrada');
            }

            // Se tem req_id, foi gerada por outra tela
            if (!empty($oco->req_id)) {
                return $this->response->setJSON([
                    'erro' => true,
                    'msg'  => 3
                ]);
            }

            $this->ocorrencia->delete($id);

            return $this->response->setJSON([
                'erro' => false,
                'msg'  => 'Ocorrência Excluída com Sucesso'
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'erro' => true,
                'msg'  => $e->getMessage()
            ]);
        }
    }

    /**
     * Finalização da tratativa
     * finalizar
     *
     * @param mixed $id 
     * @return void
     */
    public function finalizar($id)
    {
        return redirect()->to('/OcoTrataOcorrencia/finalizar/' . $id);
    }

    /**
     * Gravação
     * store
     *
     * @param mixed $id 
     * @return void
     */
    public function store()
    {
        $postado = $this->request->getPost();
        // debug($postado, true);

        try {
            if (empty($postado['oco_id'])) {
                unset($postado['oco_id']);
            }

            // SUBTIPO / STATUS
            $modModel = new OcorreModOcorrenciaModel();
            $subtipo = $modModel->getModOcorrencia((int)$postado['sut_id'])[0] ?? null;

            if ($subtipo && $subtipo->sut_fina === 'S') {
                // FINALIZA AUTOMÁTICO
                $postado['stt_id'] = 29;
            } else {
                $acao = $modModel->getAcaoConfigurada((int)$postado['sut_id']);

                if (!$acao) {
                    $postado['stt_id'] = 29;
                } elseif ((int)$acao->tpa_id === 12) {
                    $postado['stt_id'] = 29;
                } else {
                    $postado['stt_id'] = 28;
                }
                if ($acao) {
                    $postado['tpa_id'] = $acao->tpa_id ?? null;
                    $postado['tmo_id'] = $acao->tmo_id ?? null;
                    $postado['tel_id'] = $acao->tel_id ?? null;
                }
            }
            // cria data se não veio do form
            if (empty($postado['oco_data'])) {
                $postado['oco_data'] = date('Y-m-d H:i:s');
            }

            // cria a entity
            $entity = new EntOcoOcorrencia($postado);
            // debug($entity->stt_id);

            if (!$this->ocorrencia->save($entity)) {
                throw new \Exception(implode('<br>', $this->ocorrencia->errors()));
            }

            session()->setFlashdata('msg', 'Ocorrência gravada com sucesso!');

            return $this->response->setJSON([
                'erro' => false,
                'url'  => site_url($this->data['controler'])
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'erro' => true,
                'msg'  => $e->getMessage()
            ]);
        }
    }
}
