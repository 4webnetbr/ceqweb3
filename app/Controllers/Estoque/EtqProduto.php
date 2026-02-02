<?php

namespace App\Controllers\Estoque;

use App\Controllers\BuscasSapiens;
use App\Controllers\BaseController;
use App\Models\Produt\ProdutLoteModel;
use App\Entities\Estoque\EntRequisicao;
use App\Models\Produt\ProdutClasseModel;
use App\Models\Produt\ProdutProdutoModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquRequisicaoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoModel;
use App\Models\Estoqu\EstoquRequisicaoProdutoAtendimentoModel;

class EtqProduto extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $requisicao;
    public $reqproduto;
    public $reqprodutoate;
    public $classes;
    public $produtos;
    public $lote;
    public $busca;
    public $deposito;
    public $bt_envia;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->requisicao   = new EstoquRequisicaoModel();
        $this->reqproduto   = new EstoquRequisicaoProdutoModel();
        $this->reqprodutoate   = new EstoquRequisicaoProdutoAtendimentoModel();
        $this->classes      = new ProdutClasseModel();
        $this->produtos     = new ProdutProdutoModel();
        $this->busca        = new BuscasSapiens();
        $this->deposito     = new EstoquDepositoModel();
        $this->lote         = new ProdutLoteModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'req_id');
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
        // if (!$requis = cache('requis')) {
        $campos = montaColunasCampos($this->data, 'req_id');
        $dados_requis = $this->requisicao->getRequisicaoLista(false, [4, 21]);
        $dados_requis = filtrarRequisicoesPorPerfil($dados_requis);
        $req_ids_assoc = array_column($dados_requis, 'req_id');
        $log = buscaLogTabela('est_requisicao', $req_ids_assoc);

        $base_url = base_url($this->data['controler']);
        foreach ($dados_requis as &$req) {
            // Verificar se o log já está disponível para esse ana_id
            if ($req->req_id) {
                $req->usu_nome = $log[$req->req_id]['usua_alterou'] ?? '';
                // Concatenar o URL de forma mais eficiente
                // $url_eti = $base_url .'/EtqProduto/' . $req['req_id'];
                $url_eti = $base_url . '/Etiqueta/' . $req->req_id;
                // Gerar a ação do botão
                $req->acao_person = [
                    "<button class='btn btn-outline-warning btn-sm border-0 mx-0 fs-0' 
                    data-mdb-toggle='tooltip' data-mdb-placement='top' 
                    title='Etiquetas de Produtos' onclick='redireciona(\"$url_eti\")'>
                    <i class='fas fa-tag'></i></button>"
                ];
            }
        }
        // debug($dados_requis, true);
        $this->data['edicao'] = false;
        $requis = [
            'data' => montaListaColunasEnt($this->data, 'req_id', $dados_requis, $campos[1]),
        ];
        cache()->save('requis', $requis, 60000);
        // }

        echo json_encode($requis);
    }
    /**
     * Impressão de Etiquetas de Produtos
     * EtqProduto
     *
     * @param mixed $id 
     * @return void
     */
    public function Etiqueta($id)
    {
        $requisicao = $this->requisicao->getRequisicao($id)[0];

        if (!$requisicao) {
            session()->setFlashdata('erromsg', 'Requisição não encontrada.');
            return redirect()->to(site_url($this->data['controler']));
        }
        $ent    = new EntRequisicao((array) $requisicao, true);

        $fields = $ent->campos;
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['req_id'];
        $campos[0][count($campos[0])] = $fields['req_data'];
        $campos[0][count($campos[0])] = $fields['req_dataentrega'];
        $campos[0][count($campos[0])] = $fields['tmo_id'];
        $campos[0][count($campos[0])] = "<div class='col-6'>.</div>";
        // $campos[0][count($campos[0])] = $fields['lot_codbar'];

        $produtosreq = $this->requisicao->getRequisicaoProdutos($id);

        $colunas = ['Cód ERP', 'Descrição', 'Fabricante', 'Lote', 'Qtde.Requerida', 'Qtde.Imprimir', 'Coloração Etiqueta', 'Imprimir'];
        $produtos = [];
        $produtos[0] = $id;
        if (count($produtosreq) > 0) {
            for ($p = 0; $p < count($produtosreq); $p++) {
                $prod = $produtosreq[$p];

                $rep_id = $prod->rep_id;
                $qtia = $prod->rep_quantia;
                $url_ati = base_url($this->data['controler'] . '/GeraEtiqueta/' . $rep_id . '/' . $qtia);
                $imprimir =
                    "<button class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0' data-mdb-toggle='tooltip' 
                    data-mdb-placement='top' title='Imprimir Etiqueta' onclick='geraEiquetaProd(\"" . $url_ati . "\")'><i class='fas fa-print'></i></button>";
                $item = [];
                $item[0] = $rep_id;
                $item[count($item)] = $prod->pro_codpro;
                $item[count($item)] = $prod->pro_despro;
                $item[count($item)] = $prod->fab_apeFab;
                $item[count($item)] = $prod->lot_lote;
                $item[count($item)] = $prod->rep_quantia;
                $item[count($item)] = $prod->rep_quantia;
                $item[count($item)] = $prod->etiq_cor;
                $item[count($item)] = $imprimir;
                $produtos[count($produtos)] = $item;
            }
        }
        // debug($produtos, true);
        $data = [
            'show' => true,
            'colunas' => $colunas,
            'produtos' => $produtos
        ];

        $campos[0][count($campos[0])] = view('partials/pw_show_produtos_req', $data); // mesma estrutura do add()

        $this->data['icone']   = "<i class='fas fa-tag'></i>"; // ou 'update' se você for criar
        $this->data['desc_metodo']   = ''; // ou 'update' se você for criar
        $this->data['title']   = 'Impressão de Etiquetas de Produtos'; // ou 'update' se você for criar
        $this->data['desc_edicao']  = ' Requisição No. ' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $this->data['secoes']    = $secao;
        $this->data['campos']    = $campos;
        $this->data['destino']   = ''; // ou 'update' se você for criar
        $this->data['scripts']   = 'my_requisicao';

        echo view('vw_edicao', $this->data);
    }

    public function GeraEtiqueta($id, $qtia)
    {
        $produtos = $this->requisicao->getRequisicaoRep($id);
        // debug($produtos);
        $produtosreq = array_fill(0, $qtia, $produtos[0]);
        // debug($produtosreq);
        $chave = uniqid('etq_');
        cache()->save($chave, $produtosreq, 300); // 1 minuto

        $link = base_url('/CriaEtiquetaZPL/emiteEtiqueta');

        $ret['link'] = $link;
        $ret['chave'] = $chave;

        return json_encode($ret);
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store() {}
}
