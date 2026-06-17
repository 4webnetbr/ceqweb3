<?php 

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use DirectoryIterator;

class CfgFuncoes extends BaseController
{
    public $data = [];
    public $dicionario;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    function __erro() 
    {
        echo view('vw_semacesso', $this->data);
    }

    public function index()
    {
        $this->data['colunas'] = ['Arquivo', 'Arquivo', 'Função', 'Descrição',  'Ação'];
        $this->data['url_lista'] = base_url(
            $this->data['controler'] . '/lista'
        );

        echo view('vw_lista', $this->data);
    }

    public function lista()
    {
        $result = [];
        $pasta = FCPATH.'/assets/jscript';
        $diretorio = dir($pasta);
        while(($arquivo = $diretorio->read()) !== false) {
            $arquivos[] = $arquivo;
        }
         
        asort($arquivos);
        foreach ($arquivos as $arquivo) {
            if(substr($arquivo, 0,3) == 'my_'){
                $conteudo = file_get_contents($pasta.'/'.$arquivo);
                $coments = explode('/**',$conteudo);
                for($c=0;$c<count($coments);$c++){
                    $fim = stripos($coments[$c], '*/');
                    $funcao = substr($coments[$c],0,$fim);
                    $lin_func = explode('*',$funcao);
                    if(isset($lin_func[1])){
                        $nome = $lin_func[1];
                        if(isset($lin_func[2])){
                            $descricao = $lin_func[2];
                        } else {
                            $nome = '';
                            $descricao = $lin_func[1];
                        }
                        $detalhes = '';
                        if (strpbrk($this->permissao, 'C')) {
                            $detalhes = anchor(
                                $this->data['controler'] . "/show/" .$arquivo.'/'.$c,
                                '<i class="far fa-eye"></i>',
                                [
                                    'class' => 'btn btn-outline-info btn-sm mx-0 border-0 fs-0',
                                    'data-mdb-toggle' => 'tooltip',
                                    'data-mdb-placement' => 'top',
                                    'title' => 'Código da Função', 
                                ]
                            );
                        }
                        $result[] = [
                            $arquivo,
                            $arquivo,
                            $nome,
                            $descricao,
                            $detalhes,
                        ];
                    }
                }
            }
        }
        $pasta = FCPATH.'../app/Helpers';
        $diretorio = dir($pasta);
        while(($arquivo = $diretorio->read()) !== false) {
            // debug($arquivo);
            $arquiphp[] = $arquivo;
        }
         
        asort($arquiphp);
        foreach ($arquiphp as $arquivo) {
            if(substr($arquivo, 0,1) != '.'){
                $conteudo = file_get_contents($pasta.'/'.$arquivo);
                $coments = explode('/**',$conteudo);
                for($c=0;$c<count($coments);$c++){
                    $fim = stripos($coments[$c], '*/');
                    $funcao = substr($coments[$c],0,$fim);
                    $lin_func = explode('*',$funcao);
                    if(isset($lin_func[1])){
                        $nome = $lin_func[1];
                        if(isset($lin_func[2])){
                            $descricao = $lin_func[2];
                        } else {
                            $nome = '';
                            $descricao = $lin_func[1];
                        }
                        $detalhes = anchor(
                            $this->data['controler'] . "/show/" .$arquivo.'/'.$c,
                            '<i class="far fa-eye"></i>',
                            [
                                'class' => 'btn btn-outline-info btn-sm mx-1',
                                'data-mdb-toggle' => 'tooltip',
                                'data-mdb-placement' => 'top',
                                'title' => 'Código da Função', 
                            ]
                        );
                        $result[] = [
                            $arquivo,
                            $arquivo,
                            $nome,
                            $descricao,
                            $detalhes,
                        ];
                    }
                }
            }
        }
        $functions = [
            'data' => $result,
        ];

        echo json_encode($functions);
    }

    public function show($arquivo, $posicao)
    {
        $ext = substr($arquivo, -3);
        if (trim($ext) == '.js') {
            $pasta = FCPATH . '/assets/jscript';
        } else {
            $pasta = FCPATH . '../app/Helpers';
        }
        $conteudo = file_get_contents($pasta . '/' . $arquivo);
        $coments    = explode('/**', $conteudo);
        // debug($posicao, false);
        // debug($coments[$posicao],false);
        $fim        = stripos($coments[$posicao], ';;');
        if (!$fim) {
            $fim = strlen($coments[$posicao]);
        }
        // debug($fim, false);
        $funcao     = htmlspecialchars(substr($coments[$posicao], 0, $fim));
        $lin_func   = explode('*', $funcao);
        // debug('Função', false);
        // debug($funcao, false);
        // debug($lin_func, false);
        $nome = $lin_func[0];
        if (trim($nome) == '') {
            $nome = $lin_func[1];
        }

        // debug($nome, false);
        $fp = fopen($pasta . '/' . $arquivo, "r");

        $conta = 0;
        while (!feof($fp)) { // loop em todas as linhas
            $linha = trim(fgets($fp, 4096)); // le 4096bytes ou ate o final da linha
            // debug($linha,false);
            if (stripos($linha, trim($nome))) {
                break;
            }
            $conta++;
        }
        $funcao = 'Arquivo: ' . $arquivo . '<br>Linha Inicial: ' . $conta . '<br><br>/**' . $funcao;

        $arquivo = file_get_contents($pasta . '/' . $arquivo);
        $coments    = explode('/**', $conteudo);

        $pastaapp = FCPATH . '../';
        $arqs = buscaArquivos($pastaapp);
        $linhas = [];
        foreach ($arqs as $arquivo) {
            // debug($arquivo);
            $pt_arquivos = explode('/', $arquivo);
            $nome_arquivo = $pt_arquivos[count($pt_arquivos) - 1];
            $conteudo = file($arquivo);
            $linhas[$nome_arquivo] = [];
            $achou = false;
            for ($l = 0; $l < sizeof($conteudo); $l++) {
                $lin = $conteudo[$l];
                if (stripos($lin, trim($nome)) > -1) {
                    $achou = true;
                    $linhas[$nome_arquivo][$l + 1] = $lin;
                }
            }
            if (!$achou) {
                unset($linhas[$nome_arquivo]);
            }
        }

        // debug($linhas);
        $this->defCampos($funcao, $linhas);

        $secao[0] = 'Código Fonte';
        $campos[0][0] = $this->def_funcao;

        $secao[1] = 'Referências';
        $campos[1][0] = $this->def_refer;

        $this->data['funcao'] = $funcao;
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }


    public function defCampos($funcao = '', $refer = [])
    {
        $func = new Campos();
        $func->objeto = 'show';
        $func->nome = 'codigo';
        $func->id = 'codigo';
        $func->label = 'Código Fonte da Função';
        $func->size = '100%';
        $func->tamanho = 'auto';
        $func->valor = '<pre>' . $funcao . '</pre>';
        // $codi->valor = print_r($fonte);
        $this->def_funcao = $func->create();

        $ref = '';
        foreach ($refer as $chave => $valor) {
            $ref .= '<br>ARQUIVO: ' . $chave . '<br>';
            // echo $chave;
            foreach ($valor as $key => $value) {
                $ref .= 'Linha ' . $key . ' = ' . $value;
            }
        }

        $refe = new Campos();
        $refe->objeto = 'show';
        $refe->nome = 'referencia';
        $refe->id = 'referencia';
        $refe->label = 'Referências da Função';
        $refe->size = '100%';
        $refe->tamanho = 'auto';
        $refe->valor = '<pre>' . $ref . '</pre>';
        // $codi->valor = print_r($fonte);
        $this->def_refer = $refe->create();
    }



}
