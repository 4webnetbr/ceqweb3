<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgEtiqueta;
use App\Models\CommonModel;
use App\Models\Config\ConfigEtiquetaCampoModel;
use App\Models\Config\ConfigEtiquetaModel;
use App\Traits\ForeignKeyUsageChecker;

class CfgEtiqueta extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data      = [];
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
        $this->data          = session()->getFlashdata('dados_tela');
        $this->permissao     = $this->data['permissao'];
        $this->etiqueta      = new ConfigEtiquetaModel();
        $this->etiquetaCampo = new ConfigEtiquetaCampoModel();
        $this->common        = new CommonModel();

        // Caso exista erro de permissão, bloqueia acesso
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'etq_id');
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
        $campos = montaColunasCampos($this->data, 'etq_id');
        $dados  = $this->etiqueta->getEtiqueta();

        $urlPrint = base_url('/CriaEtiquetaZPL/emiteEtiqueta');
        $urlCopy  = base_url($this->data['controler'] . '/copy/');

        foreach ($dados as $etq) {
            if ($etq->etq_ativo === 'A') {
                $etq->acao_person[] =
                    "<button type='button' class='btn btn-outline-info btn-sm border-0 mx-0 fs-0'
                        title='Copiar Etiqueta'
                        onclick='redireciona(\"{$urlCopy}{$etq->etq_id}\",event)'>
                        <i class='fas fa-copy'></i>
                    </button>";

                $etq->acao_person[] =
                    "<button type='button' class='btn btn-outline-dark btn-sm border-0 mx-0 fs-0'
                        title='Imprimir Etiqueta'
                        onclick='gerarEtiquetaZPL(\"{$urlPrint}\", {$etq->etq_id})'>
                        <i class='fas fa-print'></i>
                    </button>";
            }
        }

        $this->data['exclusao'] = false;

        echo json_encode([
            'data' => montaListaColunasEnt(
                $this->data,
                'etq_id',
                $dados,
                $campos[1]
            ),
        ]);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $etq = new EntCfgEtiqueta();

        // Seção: Dados Gerais
        $this->data['secoes'][] = 'Dados Gerais';
        $this->data['campos'][] = [
            $etq->campos['etq_id'],
            $etq->campos['etq_nome'],
            $etq->campos['let_id'],
        ];

        // Seção: Tela Aplicável
        $this->data['secoes'][] = 'Tela Aplicável';
        $this->data['campos'][] = [
            $etq->campos['mod_id'],
            $etq->campos['tel_id'],
        ];

        // Campos da etiqueta
        $fieldsCampo = $etq->defCamposCfg();

        $this->data['secoes'][]    = 'Campos para Etiqueta';
        $this->data['displ'][2]    = 'tabela';
        $this->data['campos'][2][] = [
            $fieldsCampo['etc_campo'],
            $fieldsCampo['etc_codbar'],
            $fieldsCampo['etc_colunas'],
            $fieldsCampo['etc_rotulo'],
            $fieldsCampo['etc_caracteres'],
            $fieldsCampo['etc_linhas'],
            $fieldsCampo['etc_fonte'],
            $fieldsCampo['etc_tamanho'],
            $fieldsCampo['etc_alinhamento'],
            $fieldsCampo['etc_negrito'],
            $fieldsCampo['etc_italico'],
            $fieldsCampo['etc_sublinhado'],
            $fieldsCampo['bt_add'],
            $fieldsCampo['bt_del'],
        ];

        // Define método de gravação
        $this->data['destino'] = 'store';
        // Ajustes JS da tela
        $this->data['script'] = "<script>acerta_botoes_rep('campos_para_etiqueta')</script>";

        echo view('vw_edicao_etiqueta', $this->data);
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
        // Busca etiqueta pelo ID
        $dados = $this->etiqueta->getEtiqueta($id)[0];

        // Caso não encontre, lança exceção
        if (! $dados) {
            return redirectWithError($this->data['controler'], 41);
            // return view('errors/vw_semregistro', [
            //     'mensagem' => 'Etiqueta não encontrada'
            // ]);
        }
        $eetiq  = new EntCfgEtiqueta((array) $dados);
        $fields = $eetiq->campos;

        // DADOS GERAIS
        $this->data['secoes'] = ['Dados Gerais', 'Telas Aplicáveis', 'Campos para Etiqueta'];
        $campos[0]            =
            [
                $fields['etq_id'],
                $fields['etq_nome'],
                $fields['let_id'],
            ];
        $campos[1] =
            [
                $fields['mod_id'],
                $fields['tel_id'],
            ];

        // CAMPOS DA ETIQUETA
        $dados_campos = $this->etiquetaCampo->getEtiquetaCampo($id);
        // debug(count($dados_campos));
        $displ[2] = 'tabela';
        if (count($dados_campos) > 0) {
            for ($ec = 0; $ec < count($dados_campos); $ec++) {
                // Define campos configurados
                $fields           = $eetiq->defCamposCfg($dados_campos[$ec], false, $ec);
                $campos[2][$ec][] = $fields['etc_campo'];
                $campos[2][$ec][] = $fields['etc_codbar'];
                $campos[2][$ec][] = $fields['etc_colunas'];
                $campos[2][$ec][] = $fields['etc_rotulo'];
                $campos[2][$ec][] = $fields['etc_caracteres'];
                $campos[2][$ec][] = $fields['etc_linhas'];
                $campos[2][$ec][] = $fields['etc_fonte'];
                $campos[2][$ec][] = $fields['etc_tamanho'];
                $campos[2][$ec][] = $fields['etc_alinhamento'];
                $campos[2][$ec][] = $fields['etc_negrito'];
                $campos[2][$ec][] = $fields['etc_italico'];
                $campos[2][$ec][] = $fields['etc_sublinhado'];
                $campos[2][$ec][] = $fields['bt_add'];
                $campos[2][$ec][] = $fields['bt_del'];
            }
        } else {
            // Caso não existam campos, cria primeira linha vazia
            $fields                             = $eetiq->defCamposCfg();
            $campos[2][0]                       = [];
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
        // Define dados da tela
        $this->data['campos']  = $campos;
        $this->data['displ']   = $displ;
        $this->data['destino'] = 'store';
        // Script de ajuste e preview
        $base_url             = base_url('/CriaEtiquetaZPL/previewEtiquetaViaAjax');
        $this->data['script'] = "<script>acerta_botoes_rep('campos_para_etiqueta');prevEtiqueta('" . $base_url . "')</script>";

        echo view('vw_edicao_etiqueta', $this->data);
    }

    /**
     * Copiar Etiquetas
     * copy
     *
     * @param mixed $id
     * @return void
     */
    public function copy($id)
    {
        // Busca etiqueta pelo ID
        $dados = $this->etiqueta->getEtiqueta($id)[0];
        // Remove ID para gerar nova etiqueta
        unset($dados->etq_id);
        // Caso não encontre, lança exceção
        if (! $dados) {
            return view('errors/vw_semregistro', [
                'mensagem' => 'Etiqueta não encontrada',
            ]);
        }
        $eetiq  = new EntCfgEtiqueta((array) $dados);
        $fields = $eetiq->campos;

        // DADOS GERAIS
        $this->data['secoes'] = ['Dados Gerais', 'Telas Aplicáveis', 'Design Etiqueta'];
        $campos[0]            =
            [
                $fields['etq_id'],
                $fields['etq_nome'],
                $fields['let_id'],
            ];
        $campos[1] =
            [
                $fields['mod_id'],
                $fields['tel_id'],
            ];

        // CAMPOS DA ETIQUETA
        $dados_campos = $this->etiquetaCampo->getEtiquetaCampo($id);
        // debug(count($dados_campos));
        $displ[2] = 'tabela';
        if (count($dados_campos) > 0) {
            for ($ec = 0; $ec < count($dados_campos); $ec++) {
                // Define campos configurados
                $fields                                 = $eetiq->defCamposCfg($dados_campos[$ec], false, $ec);
                $campos[2][$ec][0]                      = $fields['etc_campo'];
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
            // Caso não existam campos, cria primeira linha vazia
            $fields                             = $eetiq->defCamposCfg();
            $campos[2][0]                       = [];
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
        // Define dados da tela
        $this->data['campos']      = $campos;
        $this->data['displ']       = $displ;
        $this->data['destino']     = 'store';
        $this->data['desc_metodo'] = 'Nova Versão de ';
        // Script de ajuste e preview
        $base_url             = base_url('/CriaEtiquetaZPL/previewEtiquetaViaAjax');
        $this->data['script'] = "<script>acerta_botoes_rep('campos_para_etiqueta');prevEtiqueta('" . $base_url . "')</script>";

        echo view('vw_edicao_etiqueta', $this->data);
    }

    public function addCampo($ind)
    {
        // Define campos da etiqueta para o índice informado
        $ent    = new EntCfgEtiqueta();
        $fields = $ent->defCamposCfg(false, false, $ind);

        // Monta retorno na ordem esperada pela tabela
        $campos[0]              = $fields['etc_campo'];
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

        // Retorna JSON para o JavaScript
        echo json_encode($campos);
        exit;
    }

    public function ativinativ($id, $tipo)
    {
        $ret = [];
        try {
            if ($tipo == 1) {
                $dad_atin = [
                    'etq_ativo' => 'A',
                ];
            } else {
                $dad_atin = [
                    'etq_ativo' => 'I',
                ];
                $this->verificarUsoEmRelacionamentos('cfg_etiqueta', 'etq_id', (int) $id);
            }

            $this->etiqueta->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Etiqueta Alterada com Sucesso');
            $ret['msg'] = 'Etiqueta Alterada com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            // $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
            $ret['msg'] = 14;
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
            $this->verificarUsoEmRelacionamentos('cfg_etiqueta', 'etq_id', (int) $id);

            // Soft delete
            $this->etiqueta->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Etiqueta Excluída com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
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
        $ret         = [];
        $ret['erro'] = false;
        $postado     = $this->request->getPost();

        // Cria entity com dados postados
        $etiqueta = new EntCfgEtiqueta($postado);

        // Verifica unicidade do nome da etiqueta
        $exists = $this->common->verificaUnico($this->etiqueta, 'etq_nome', $postado['etq_nome'], 'etq_id', $postado['etq_id']);
        if ($exists > 0) {
            $ret['erro'] = true;
            $ret['msg']  = 8;
        } else {
            $this->etiqueta->transBegin();
            try {
                // Gravação da etiqueta
                if (! $this->etiqueta->save($etiqueta)) {
                    throw new \Exception(implode(' ', $this->etiqueta->errors()));
                }

                // Pega o ID da etiqueta recém-gravada
                $etq_id = isset($postado['etq_id']) && ! empty($postado['etq_id']) ? $postado['etq_id'] : $this->etiqueta->getInsertID();

                // Verifica se o ID é válido antes de tentar deletar
                if (empty($etq_id)) {
                    throw new \Exception('Erro: Não foi possível obter o ID da etiqueta.');
                }

                // Gravação dos campos da etiqueta
                if (! empty($postado['etc_campo']) && is_array($postado['etc_campo'])) {
                    $dadosCampos = [];
                    $data_atua   = date('Y-m-d H:i:s');

                    // debug($postado);
                    foreach ($postado['etc_campo'] as $indice => $campo) {
                        $dadosCampos[] = [
                            'etq_id'          => $etq_id,
                            'etc_campo'       => $campo,
                            'etc_codbar'      => $postado['etc_codbar'][$indice] ?? null,
                            'etc_rotulo'      => $postado['etc_rotulo'][$indice] ?? null,
                            'etc_caracteres'  => $postado['etc_caracteres'][$indice] ?? null,
                            'etc_linhas'      => $postado['etc_linhas'][$indice] ?? null,
                            'etc_colunas'     => $postado['etc_colunas'][$indice] ?? null,
                            'etc_fonte'       => $postado['etc_fonte'][$indice] ?? null,
                            'etc_tamanho'     => $postado['etc_tamanho'][$indice] ?? null,
                            'etc_alinhamento' => $postado['etc_alinhamento'][$indice] ?? null,
                            'etc_negrito'     => $postado['etc_negrito'][$indice] ?? null,
                            'etc_italico'     => $postado['etc_italico'][$indice] ?? null,
                            'etc_sublinhado'  => $postado['etc_sublinhado'][$indice] ?? null,
                            'etc_atualizado'  => $data_atua,
                        ];
                    }

                    // Insere campos em lote
                    if (! empty($dadosCampos)) {
                        $this->etiquetaCampo->transBegin();
                        try {
                            $this->etiquetaCampo->insertBatch($dadosCampos);
                        } catch (\Exception $e) {
                            // Rollback dos campos
                            $this->etiquetaCampo->transRollback();
                            $ret['erro'] = true;
                            $ret['msg']  = $e->getMessage();
                        }
                    }
                }
                // Finaliza transações se não houve erro
                if (! $ret['erro']) {
                    // Se tudo deu certo, finaliza a transação
                    $this->etiqueta->transCommit();
                    $this->etiquetaCampo->transCommit();

                    // Remove campos antigos não atualizados
                    $this->common->deleteReg("default", "cfg_etiqueta_campo", "etq_id = " . $etq_id . " AND etc_atualizado < '" . $data_atua . "'");

                    $ret['erro'] = false;
                    $ret['msg']  = 'Etiqueta salva com sucesso!';
                    session()->setFlashdata('msg', $ret['msg']);
                    $ret['url'] = site_url($this->data['controler']);
                } else {
                    // Rollback geral
                    $this->etiqueta->transRollback();
                    $ret['erro'] = true;
                    $ret['msg']  = $e->getMessage();
                }
            } catch (\Exception $e) {
                // Em caso de erro, reverte a transação
                $this->etiqueta->transRollback();
                $ret['erro'] = true;
                $ret['msg']  = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}
