<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Config\ConfigEtiquetaCampoModel;
use App\Models\Config\ConfigEtiquetaModel;

class CfgEtiqueta extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $etiqueta;
    public $etiquetaCampo;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->etiqueta = new ConfigEtiquetaModel();
        $this->etiquetaCampo = new ConfigEtiquetaCampoModel();
        $this->common = new CommonModel();

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
        $this->data['colunas'] = montaColunasLista($this->data, 'etq_id');
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
        // if (!$etiqt = cache('etiqt')) {
        $campos = montaColunasCampos($this->data, 'etq_id');
        $dados_etiqt = $this->etiqueta->getEtiqueta();
        $base_url = base_url('/CriaEtiqueta/');

        foreach ($dados_etiqt as &$etq) {
            $url_ati = $base_url . $etq['etq_id'];
            // Gerar a ação do botão
            $etq['acao_person'] = [
                "<button class='btn btn-outline-black btn-sm border-0 mx-0 fs-0 float-end' 
            data-mdb-toggle='tooltip' data-mdb-placement='top' 
            title='Imprimir Etiquetas' onclick='redirec_blank(\"$url_ati\",\"Imprimir Etiqueta\")'>
            <i class='fa-solid fa-print'></i></button>"
            ];
        }

        $etiqt = [
            'data' => montaListaColunas($this->data, 'etq_id', $dados_etiqt, $campos[1]),
        ];
        cache()->save('etiqt', $etiqt, 60000);
        // }

        echo json_encode($etiqt);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $fields = $this->etiqueta->defCampos();
        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['etq_id'];
        $campos[0][count($campos[0])] = $fields['etq_nome'];
        $campos[0][count($campos[0])] = $fields['let_id'];
        $secao[1] = 'Tela Aplicável';
        $campos[1] = [];
        $campos[1][count($campos[1])] = $fields['mod_id'];
        $campos[1][count($campos[1])] = $fields['tel_id'];

        $fields = $this->etiquetaCampo->defCamposCfg();
        $secao[2] = 'Campos para Etiqueta';
        $displ[2] = 'tabela';
        $campos[2][0] = [];
        $campos[2][0][count($campos[2][0])] = $fields['etc_campo'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_codbar'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_rotulo'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_caracteres'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_linhas'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_colunas'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_fonte'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_tamanho'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_alinhamento'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_negrito'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_italico'];
        $campos[2][0][count($campos[2][0])] = $fields['etc_sublinhado'];
        $campos[2][0][count($campos[2][0])] = $fields['bt_add'];
        $campos[2][0][count($campos[2][0])] = $fields['bt_del'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']       = $displ;
        $this->data['destino']    = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('campos_para_etiqueta')</script>";

        echo view('vw_edicao', $this->data);
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
        $dados_etiqueta = $this->etiqueta->find($id);
        $fields = $this->etiqueta->defCampos($dados_etiqueta);
        $secao[0] = 'Dados Gerais';
        $campos[0] = [];
        $campos[0][count($campos[0])] = $fields['etq_id'];
        $campos[0][count($campos[0])] = $fields['etq_nome'];
        $campos[0][count($campos[0])] = $fields['let_id'];
        $secao[1] = 'Tela Aplicável';
        $campos[1] = [];
        $campos[1][count($campos[1])] = $fields['mod_id'];
        $campos[1][count($campos[1])] = $fields['tel_id'];

        $dados_campos = $this->etiquetaCampo->getEtiquetaCampo($id);
        // debug(count($dados_campos));
        $secao[2] = 'Campos para Etiqueta';
        $displ[2] = 'tabela';
        if (count($dados_campos) > 0) {
            for ($ec = 0; $ec < count($dados_campos); $ec++) {
                $fields = $this->etiquetaCampo->defCamposCfg($dados_campos[$ec], false, $ec);
                $campos[2][$ec][0] = $fields['etc_campo'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_codbar'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_rotulo'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_caracteres'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_linhas'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_colunas'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_fonte'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_tamanho'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_alinhamento'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_negrito'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_italico'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['etc_sublinhado'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['bt_add'];
                $campos[2][$ec][count($campos[2][$ec])] = $fields['bt_del'];
            }
        } else {
            $fields = $this->etiquetaCampo->defCamposCfg();
            $campos[2][0] = [];
            $campos[2][0][count($campos[2][0])] = $fields['etc_campo'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_codbar'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_rotulo'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_caracteres'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_linhas'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_colunas'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_fonte'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_tamanho'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_alinhamento'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_negrito'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_italico'];
            $campos[2][0][count($campos[2][0])] = $fields['etc_sublinhado'];
            $campos[2][0][count($campos[2][0])] = $fields['bt_add'];
            $campos[2][0][count($campos[2][0])] = $fields['bt_del'];
        }
        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['displ']     = $displ;
        $this->data['destino']    = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('campos_para_etiqueta')</script>";

        echo view('vw_edicao', $this->data);
    }

    public function addCampo($ind)
    {
        $fields = $this->etiquetaCampo->defCamposCfg(false, false, $ind);
        $campos[0] = $fields['etc_campo'];
        $campos[count($campos)] = $fields['etc_codbar'];
        $campos[count($campos)] = $fields['etc_rotulo'];
        $campos[count($campos)] = $fields['etc_caracteres'];
        $campos[count($campos)] = $fields['etc_linhas'];
        $campos[count($campos)] = $fields['etc_colunas'];
        $campos[count($campos)] = $fields['etc_fonte'];
        $campos[count($campos)] = $fields['etc_tamanho'];
        $campos[count($campos)] = $fields['etc_alinhamento'];
        $campos[count($campos)] = $fields['etc_negrito'];
        $campos[count($campos)] = $fields['etc_italico'];
        $campos[count($campos)] = $fields['etc_sublinhado'];
        $campos[count($campos)] = $fields['bt_add'];
        $campos[count($campos)] = $fields['bt_del'];

        echo json_encode($campos);
        exit;
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
            $this->etiqueta->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Etiqueta Excluída com Sucesso');
            cache()->clean();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Etiqueta, Verifique!<br><br>';
        }
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
        $ret = [];
        $ret['erro'] = false;
        $postado = $this->request->getPost();
        // $db = \Config\Database::connect();
        $this->etiqueta->transBegin();

        try {
            // Gravação da etiqueta
            if (!$this->etiqueta->save($postado)) {
                throw new \Exception(implode(' ', $this->etiqueta->errors()));
            }

            // Pega o ID da etiqueta recém-gravada
            $etq_id = isset($postado['etq_id']) && !empty($postado['etq_id']) ? $postado['etq_id'] : $this->etiqueta->getInsertID();

            // Verifica se o ID é válido antes de tentar deletar
            if (empty($etq_id)) {
                throw new \Exception('Erro: Não foi possível obter o ID da etiqueta.');
            }

            // Gravação dos campos da etiqueta
            if (!empty($postado['etc_campo']) && is_array($postado['etc_campo'])) {
                $dadosCampos = [];
                $data_atua = date('Y-m-d H:i:s');

                // debug($postado);
                foreach ($postado['etc_campo'] as $indice => $campo) {
                    $dadosCampos[] = [
                        'etq_id'            => $etq_id,
                        'etc_campo'         => $campo,
                        'etc_codbar'        => $postado['etc_codbar'][$indice] ?? null,
                        'etc_rotulo'        => $postado['etc_rotulo'][$indice] ?? null,
                        'etc_caracteres'    => $postado['etc_caracteres'][$indice] ?? null,
                        'etc_linhas'        => $postado['etc_linhas'][$indice] ?? null,
                        'etc_colunas'       => $postado['etc_colunas'][$indice] ?? null,
                        'etc_fonte'         => $postado['etc_fonte'][$indice] ?? null,
                        'etc_tamanho'       => $postado['etc_tamanho'][$indice] ?? null,
                        'etc_alinhamento'   => $postado['etc_alinhamento'][$indice] ?? null,
                        'etc_negrito'       => $postado['etc_negrito'][$indice] ?? null,
                        'etc_italico'       => $postado['etc_italico'][$indice] ?? null,
                        'etc_sublinhado'    => $postado['etc_sublinhado'][$indice] ?? null,
                        'etc_atualizado'    => $data_atua
                    ];
                }

                if (!empty($dadosCampos)) {
                    // debug($dadosCampos, true);
                    $this->etiquetaCampo->transBegin();
                    try {
                        $this->etiquetaCampo->insertBatch($dadosCampos);
                    } catch (\Exception $e) {
                        // Em caso de erro, reverte a transação
                        $this->etiquetaCampo->transRollback();
                        $ret['erro'] = true;
                        $ret['msg'] = $e->getMessage();
                    }
                }
            }
            if (!$ret['erro']) {
                // Se tudo deu certo, finaliza a transação
                $this->etiqueta->transCommit();
                $this->etiquetaCampo->transCommit();

                $this->common->deleteReg("default", "cfg_etiqueta_campo", "etq_id = " . $etq_id . " AND etc_atualizado < '" . $data_atua . "'");

                $ret['erro'] = false;
                $ret['msg'] = 'Etiqueta salva com sucesso!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url']  = site_url($this->data['controler']);
            } else {
                $this->etiqueta->transRollback();
                $ret['erro'] = true;
                $ret['msg'] = $e->getMessage();
            }
        } catch (\Exception $e) {
            // Em caso de erro, reverte a transação
            $this->etiqueta->transRollback();
            $ret['erro'] = true;
            $ret['msg'] = $e->getMessage();
        }

        echo json_encode($ret);
    }
}
