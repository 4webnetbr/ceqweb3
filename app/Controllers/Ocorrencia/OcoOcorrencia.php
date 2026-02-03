<?php

namespace App\Controllers\Ocorrencia;

use App\Models\CommonModel;
use App\Controllers\BuscasSapiens;
use App\Controllers\BaseController;
use App\Entities\Ocorrencia\EntOcoTratativa;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Config\ConfigTelaModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Ocorre\OcorreOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoOcorrencia;
use App\Entities\Ocorrencia\EntOcoTipoOcorre;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoModOcorrencia;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class OcoOcorrencia extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data = [];
    public $modelTipo;
    public $modelMod;
    public $ocorrencia;
    public $produtLoteModel;
    public $common;

    public function __construct()
    {
        $this->data = session()->get('dados_tela') ?? [];

        $this->modelTipo       = new OcorreTipoOcorrenciaModel();
        $this->modelMod        = new OcorreModOcorrenciaModel();
        $this->ocorrencia      = new OcorreOcorrenciaModel();
        $this->common          = new CommonModel();

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


    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'oco_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }


    public function lista()
    {
        $campos = montaColunasCampos($this->data, 'oco_id');

        $dados = $this->ocorrencia->getListaCompleta();
        $oco_ids_assoc = array_map(fn($o) => $o->oco_id, $dados);

        $log = buscaLogTabela('oco_ocorrencia', $oco_ids_assoc);
        $base_url = base_url('OcoTrataOcorrencia');

        foreach ($dados as $nov) {
            $nov->usu_nome = $log[$nov->oco_id]['usua_alterou'] ?? '';

            // Botão de finalizar se estiver pendente
            if (trim($nov->stt_nome ?? '') === 'Pendente') {
                $url_finalizar = $base_url . '/finalizar/' . $nov->oco_id;
                $nov->acao_person[] = "
                    <button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0'
                        data-mdb-toggle='tooltip'
                        data-mdb-placement='top'
                        title='Finalizar Tratativa'
                        onclick='redireciona(\"$url_finalizar\")'>
                        <i class='fas fa-check'></i>
                    </button>
                ";
            }

            // Botão imprimir
            $url_imp = base_url('/CriaPdf2025/PrintOcorrencia/' . $nov->oco_id);
            $nov->acao_person[] = "
                <button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0'
                    data-mdb-toggle='tooltip'
                    data-mdb-placement='top'
                    title='Imprimir Ocorrência'
                    onclick='openPDFModal(\"{$url_imp}\",\"Imprimir Ocorrência\")'>
                    <i class='fa-solid fa-print'></i>
                </button>
            ";
        }
        $ret = new \stdClass();
        $ret->data = montaListaColunasEnt($this->data, 'oco_id', $dados, $campos[1]);

        $ret->exclusao = false;

        return $this->response->setJSON($ret);
    }


    public function add($modal = false)
    {
        // Instancia a entity
        $oco = new EntOcoOcorrencia();
        $fields = $oco->campos;
        // Dados Gerais
        $this->data['secoes']  = ['Dados Gerais'];

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

    public function addOutraTela()
    {
        $modal = true;

        $request = service('request');

        // Lê e decodifica o JSON recebido
        $json = $request->getJSON(true); // true = como array associativo
        // Agora você pode acessar normalmente:
        $proId    = $json['pro_id'] ?? null;
        $lotlote    = $json['lot_lote'] ?? null;
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

        $config['Label'] = "Tipo de Ocorrência";
        $config['Leitura'] = false;
        $toc_oc = criaSelectRelativo(
            'vw_oco_tpo_ocorrencia_relac',
            'tpo_id',
            'tpo_nome',
            null,
            1,
            'oco_ocorrencia',
            ['tel_id' => $telId],
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
            ['tel_id' => $telId],
            $config
        );

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
            $oco->campos['req_id'],
            $oco->campos['lot_id'],
            $oco->campos['lot_lote'],
            $mod_oc,
            $oco->campos['pro_id'],
            $oco->campos['pro_despro'],
            $oco->campos['oco_qtd'],
            $oco->campos['oco_data'],
            $oco->campos['oco_descricao'],
        ]];
        $this->data['destino'] = 'store';
        $this->data['desc_metodo'] = 'Nova Ocorrência';
        $this->data['script'] = "<script>
                                    var elemento = document.getElementById('lot_lote');
                                    buscaLoteProduto(elemento,'" . base_url('/buscas/buscaProdutoporLote') . "')
                                </script>";

        // Renderiza a view
        echo view('vw_edicao_modal', $this->data);
    }


    public function edit($id)
    {
        // Busca a ocorrência pelo ID
        $dados = $this->ocorrencia->getById($id);

        // Valida se a ocorrência existe
        if (!$dados) {
            throw new \Exception('Ocorrência não encontrada');
        }

        // Instancia a entity
        $oco    = new EntOcoOcorrencia((array) $dados, true);
        $fields = $oco->campos;

        // dados gerais
        $this->data['secoes'] = ['Dados Gerais'];

        // define os campos
        $this->data['campos'] = [[
            $fields['oco_id'],
            $fields['tpo_id'],
            $fields['tpa_id'],
            $fields['oco_descricao'],
            $fields['pro_id'],
            $fields['lot_id'],
            $fields['lot_lote'],
            $fields['pro_despro'],
            $fields['oco_qtd'],
            $fields['oco_data'],
        ]];

        $this->data['destino'] = "store";
        $this->data['edicao']  = true;

        echo view('vw_edicao', $this->data);
    }

    public function getProdutoLote()
    {
        // Recupera o código do lote enviado via POST
        $codLote = $this->request->getPost('codLote');
        // Valida se o lote foi informado
        if (!$codLote) {
            return $this->response->setJSON([
                'erro' => 'Lote não informado'
            ]);
        }

        $dados = $this->produtLoteModel->getLoteSearch($codLote);

        // Valida se o lote foi encontrado
        if (!$dados || empty($dados)) {
            return $this->response->setJSON([
                'erro' => 'Lote não encontrado'
            ]);
        }
        $lote = $dados[0];

        // Retorna a descrição do produto vinculada ao lote
        return $this->response->setJSON([
            'descpro' => $lote->pro_despro ?? '',
        ]);
    }

    public function finalizar($id)
    {
        // Fluxo semelhante ao edit, porém com destino de finalização
        $result = $this->ocorrencia->getById($id);
        $dados  = $result[0] ?? null;

        if (!$dados) {
            throw new \Exception("Ocorrência não encontrada");
        }

        $log = buscaLogTabela('oco_ocorrencia', [$id]);
        $dados->usu_nome = $log[$id]['usua_alterou'] ?? $dados->usu_nome;

        $tipo = $this->modelTipo->find($dados->tpo_id);
        $dados->tpo_nome = $tipo->tpo_nome ?? '';

        $entity = new EntOcoTratativa($dados, true);
        $fields = $entity->campos;

        $secao[0] = 'Finalizar a Tratativa';

        $campos[0][] = $fields['tpo_id'];
        $campos[0][] = $fields['usu_nome'];
        $campos[0][] = $fields['oco_descricao'];
        $campos[0][] = $fields['oco_data'];
        $campos[0][] = $fields['lot_lote'];
        $campos[0][] = $fields['pro_despro'];
        $campos[0][] = $fields['oco_qtd'];
        $campos[0][] = $fields['tpa_id'];

        if (isset($fields['oco_justi'])) {
            $campos[0][] = $fields['oco_justi'];
        }
        if (isset($fields['tmo_id'])) {
            $campos[0][] = $fields['tmo_id'];
        }
        if (isset($fields['stt_id'])) {
            $campos[0][] = $fields['stt_id'];
        }
        if (isset($fields['tel_id'])) {
            $campos[0][] = $fields['tel_id'];
        }

        $this->data['secoes']       = $secao;
        $this->data['campos']       = $campos;
        $this->data['destino']      = "store";
        $this->data['desc_metodo']  = 'Finalização da ';
        $this->data['forca_submit'] = true;

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('oco_ocorrencia', 'oco_id', (int) $id);

            // Soft delete
            $this->ocorrencia->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Ocorrência excluída com sucesso!');
            $ret['msg']  = 'Ocorrência excluída com sucesso!';
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }

        echo json_encode($ret);
    }


    public function store()
    {
        $postado = $this->request->getPost();

        try {
            if (empty($postado['oco_id'])) {
                unset($postado['oco_id']);
            }

            // SUBTIPO / STATUS
            $subtipoNenhuma = $this->ocorrencia
                ->getSubtipoPorTipo((int) $postado['tpo_id']);

            if ($subtipoNenhuma !== null) {
                $postado['sut_id'] = $subtipoNenhuma;
                $postado['stt_id'] = 29; // Finalização automática
            } else {
                if (empty($postado['sut_id']) || $postado['sut_id'] == -1) {
                    $postado['sut_id'] = null;
                }
                $postado['stt_id'] = 28; // Pendente
            }

            // BUSCA AÇÃO PELO MODEL
            $acao = $this->ocorrencia->getAcaoConfigurada(
                (int) $postado['tpo_id'],
                $postado['sut_id']
            );

            // INJETA NA OCORRENCIA
            if ($acao) {
                $postado['tpa_id'] = $acao->tpa_id ?? null;
                $postado['tmo_id'] = $acao->tmo_id ?? null;
                $postado['tel_id'] = $acao->tel_id ?? null;

                if (!empty($acao->stt_id)) {
                    $postado['stt_id'] = $acao->stt_id;
                }
            }

            // cria a entity
            $entity = new EntOcoOcorrencia($postado);

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
