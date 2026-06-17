<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgPerfil;
use App\Models\Config\ConfigTelaModel;
use App\Traits\ForeignKeyUsageChecker;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigPerfilItemModel;

class CfgPerfil extends BaseController
{
    use ForeignKeyUsageChecker;

    public $data;
    public $permissao = '';
    public $perfil;
    public $perfilitem;
    public $tela;

    public $prf_id;
    public $prf_nome;
    public $prf_dashboard;
    public $prf_descricao;
    public $pit_modu;
    public $pit_tela;
    public $pit_all;
    public $pit_consulta;
    public $pit_adicao;
    public $pit_edicao;
    public $pit_exclusao;
    public $pit_notifica;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'] ?? '';
        $this->perfil       = new ConfigPerfilModel();
        $this->perfilitem   = new ConfigPerfilItemModel();
        $this->tela         = new ConfigTelaModel();

        if (!empty($this->data['erromsg'])) {
            $this->__erro();
        }
    }

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
        $this->data['colunas']   = montaColunasLista($this->data, 'prf_id,');
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
        $dados_perfil = $this->perfil->getPerfil();

        // garante que os registros também sejam OBJ 
        if (!empty($dados_perfil) && is_array($dados_perfil) && isset($dados_perfil[0]) && is_array($dados_perfil[0])) {
            $dados_perfil = array_map(fn($r) => (object) $r, $dados_perfil);
        }

        $dataArr = (array) $this->data;   // helper antigo precisa array
        // $dataArr['exclusao'] = false;

        echo json_encode([
            'data' => montaListaColunasEnt($dataArr, 'prf_id', $dados_perfil, 'prf_nome')
        ]);
    }



    public function add()
    {
        $ent = new EntCfgPerfil();

        $secao[0] = 'Dados Gerais';
        $campos[0][0][] = $ent->campos['prf_id'];
        $campos[0][0][] = $ent->campos['prf_nome'];
        $campos[0][0][] = $ent->campos['prf_dashboard'];
        $campos[0][0][] = $ent->campos['prf_descricao'];

        $secao[1] = 'Permissões';
        $itens = montaListaTelas();
        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        for ($mn = 0; $mn < sizeof($itens); $mn++) {
            if ($modu != $itens[$mn]->mod_id) {
                $cti = 0;
                $ctm++;
                $modu = $itens[$mn]->mod_id;
                $ent->defCamposPermissoes(0, $modu, $itens[$mn]);
                $campos2[1][$modu][$cti][0] = $ent->camposPermissoes['pit_modu'];
                $campos2[1][$modu][$cti][1] = $ent->camposPermissoes['pit_tela'];
                $campos2[1][$modu][$cti][2] = $ent->camposPermissoes['pit_all'];
                $campos2[1][$modu][$cti][3] = $ent->camposPermissoes['pit_consulta'];
                $campos2[1][$modu][$cti][4] = $ent->camposPermissoes['pit_adicao'];
                $campos2[1][$modu][$cti][5] = $ent->camposPermissoes['pit_edicao'];
                $campos2[1][$modu][$cti][6] = $ent->camposPermissoes['pit_exclusao'];
                $campos2[1][$modu][$cti][7] = $ent->camposPermissoes['pit_notifica'];
            }
            $cti++;
            $item = $itens[$mn]->tel_id;
            $ent->defCamposPermissoes($item, $modu, $itens[$mn]);
            $campos2[1][$modu][$cti][0] = $ent->camposPermissoes['pit_modu'];
            $campos2[1][$modu][$cti][1] = $ent->camposPermissoes['pit_tela'];
            $campos2[1][$modu][$cti][2] = $ent->camposPermissoes['pit_all'];
            $campos2[1][$modu][$cti][3] = $ent->camposPermissoes['pit_consulta'];
            $campos2[1][$modu][$cti][4] = $ent->camposPermissoes['pit_adicao'];
            $campos2[1][$modu][$cti][5] = $ent->camposPermissoes['pit_edicao'];
            $campos2[1][$modu][$cti][6] = $ent->camposPermissoes['pit_exclusao'];
            $campos2[1][$modu][$cti][7] = $ent->camposPermissoes['pit_notifica'];
        }

        $this->data['secoes']  = $secao;
        $this->data['campos']  = $campos;
        $this->data['campos2'] = $campos2;
        $this->data['destino'] = 'store';

        echo view('vw_edicao_perfil', (array) $this->data);
    }

    public function edit($id)
    {
        $dados = $this->perfil->getPerfil($id);

        if (!$dados) {
            return redirectWithError($this->data['controler'], 41);
            // return view('errors/vw_semregistro', [
            //     'mensagem' => 'Perfil não encontrado'
            // ]);
        }

        $ePerfil = new EntCfgPerfil((array) $dados);

        // DADOS GERAIS
        $this->data['secoes'] = ['Dados Gerais', 'Permissões'];
        $campos[0][0][] = $ePerfil->campos['prf_id'];
        $campos[0][0][] = $ePerfil->campos['prf_nome'];
        $campos[0][0][] = $ePerfil->campos['prf_dashboard'];
        $campos[0][0][] = $ePerfil->campos['prf_descricao'];

        // $telas = montaListaTelas();
        // $id_telas = array_column($telas, 'tel_nome', 'tel_id');
        // $itens = montaListaItensPerfil($id, $id_telas);
        // foreach ($itens as $key => $value) {
        //     for ($t = 0; $t < count($telas); $t++) {
        //         if ($telas[$t]['tel_id'] == $key) {
        //             $telas[$t]['pit_permissao'] = $itens[$key];
        //             break;
        //         }
        //     }
        // }

        $telas = montaListaTelas();

        // Criar um mapa [tel_id => tel_nome] a partir dos objetos
        $id_telas = [];
        foreach ($telas as $tela) {
            $id_telas[$tela->tel_id] = $tela->tel_nome;
        }

        $itens = montaListaItensPerfil($id, $id_telas);

        foreach ($itens as $key => $value) {
            foreach ($telas as $tela) {
                if ($tela->tel_id == $key) {
                    $tela->pit_permissao = $value;
                    break;
                }
            }
        }

        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        $campos2 = [];
        for ($mn = 0; $mn < count($telas); $mn++) {
            $tela = $telas[$mn];

            if ($modu != $tela->mod_id) {
                $cti = 0;
                $ctm++;
                $modu = $tela->mod_id;
                $ePerfil->defCamposPermissoes(0, $modu, $tela);
                $this->preencherCampos($campos2, $ePerfil, $modu, $cti);
            }
            $cti++;
            $item = $tela->tel_id;
            $ePerfil->defCamposPermissoes($item, $modu, $telas[$mn]);
            $this->preencherCampos($campos2, $ePerfil, $modu, $cti);
        }

        $this->data['campos']   = $campos;
        $this->data['campos2']  = $campos2;
        $this->data['destino']  = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_perfil', $id);

        echo view('vw_edicao_perfil', $this->data);
    }

    function preencherCampos(&$campos2, $ePerfil, $modu, $cti)
    {
        $campos2[1][$modu][$cti] = [
            $ePerfil->camposPermissoes['pit_modu'],
            $ePerfil->camposPermissoes['pit_tela'],
            $ePerfil->camposPermissoes['pit_all'],
            $ePerfil->camposPermissoes['pit_consulta'],
            $ePerfil->camposPermissoes['pit_adicao'],
            $ePerfil->camposPermissoes['pit_edicao'],
            $ePerfil->camposPermissoes['pit_exclusao'],
            $ePerfil->camposPermissoes['pit_notifica'],
        ];
    }

    public function delete($id)
    {
        // if ($this->perfil->delete($id)) {
        //     return $this->response->setJSON([
        //         'erro' => false,
        //         'msg'  => 'Registro excluído com sucesso!'
        //     ]);
        // }
        // return $this->response->setJSON([
        //     'erro' => true,
        //     'msg'  => 'Erro ao excluir o registro.'
        // ]);


        $ret = [];
        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_perfil', 'prf_id', (int) $id);
            // Soft delete
            $this->perfil->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
        echo json_encode($ret);
    }

    public function store()
    {
        $dados = $this->request->getPost();
        // debug($dados,true);
        $retorno = [];
        $tempermis = false;
        if (isset($dados['pit_consulta']) || isset($dados['pit_adicao']) || isset($dados['pit_edicao']) || isset($dados['pit_exclusao']) || isset($dados['pit_notifica'])) {
            $tempermis = true;
        }
        if (!$tempermis) {
            $retorno['erro'] = true;
            $retorno['msg'] = 'É necessário informar pelo menos uma Permissão';
        } else {
            $dados_per = [
                'prf_id'          => $dados['prf_id'],
                'prf_nome'        => $dados['prf_nome'],
                'prf_dashboard'   => $dados['prf_dashboard'],
                'prf_descricao'   => $dados['prf_descricao']

            ];
            $this->perfil->transBegin();
            $prf_save = $this->perfil->save($dados_per);
            // debug($this->perfil->errors());
            if ($prf_save) {
                if ($dados['prf_id'] == '') {
                    $prf_id = $this->perfil->getInsertID();
                } else {
                    $prf_id = $dados['prf_id'];
                }
                $dados_pit = [];
                $d_ite = [];
                $cont = 0;
                // debug($dados['pit_consulta']);
                if (isset($dados['pit_consulta'])) {
                    $pit_consulta = $dados['pit_consulta'];
                    foreach ($pit_consulta as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao']) ? $d_ite[$chave][$tela]['permissao'] . $opcao : $opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_adicao'])) {
                    $pit_adicao = $dados['pit_adicao'];
                    foreach ($pit_adicao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao']) ? $d_ite[$chave][$tela]['permissao'] . $opcao : $opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_edicao'])) {
                    $pit_edicao = $dados['pit_edicao'];
                    foreach ($pit_edicao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao']) ? $d_ite[$chave][$tela]['permissao'] . $opcao : $opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_exclusao'])) {
                    $pit_exclusao = $dados['pit_exclusao'];
                    foreach ($pit_exclusao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao']) ? $d_ite[$chave][$tela]['permissao'] . $opcao : $opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_notifica'])) {
                    $pit_notifica = $dados['pit_notifica'];
                    foreach ($pit_notifica as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao']) ? $d_ite[$chave][$tela]['permissao'] . $opcao : $opcao;
                            }
                        }
                    }
                }
                foreach ($d_ite as $chave => $valor) {
                    foreach ($valor as $tela => $opcao) {
                        $dados_pit[$cont]['pit_perfil_id'] = $prf_id;
                        $dados_pit[$cont]['pit_modulo_id'] = $chave;
                        $dados_pit[$cont]['pit_tela_id'] = $tela;
                        $dados_pit[$cont]['pit_permissao'] = $opcao['permissao'];
                        $cont++;
                    }
                }
                // debug($dados_pit, false);
                // exclui as permissões
                $this->perfilitem->excluiItemPerfil('prf_id', $prf_id);

                $this->perfilitem->transBegin();
                for ($ct = 0; $ct < sizeof($dados_pit); $ct++) {
                    $pit_dados['prf_id']     =  $dados_pit[$ct]['pit_perfil_id'];
                    $pit_dados['mod_id']     =  $dados_pit[$ct]['pit_modulo_id'];
                    $pit_dados['tel_id']     =  $dados_pit[$ct]['pit_tela_id'];
                    $pit_dados['pit_permissao']     =  $dados_pit[$ct]['pit_permissao'];
                    $pit_save = $this->perfilitem->insert($pit_dados);
                }
                if ($pit_save) {
                    $retorno['erro'] = false;
                    $retorno['msg'] = 'Perfil Gravado com Sucesso';
                    $retorno['url'] = base_url($this->data['controler']);
                } else {
                    $retorno['erro'] = true;
                    $retorno['msg'] = 'Não foi possível gravar as Permissões';
                    // debug($this->perfilitem->errors());

                    $this->perfilitem->transRollback();
                }
            } else {
                $retorno['erro'] = true;
                $retorno['msg'] = 'Não foi possível gravar o Perfil<br>';
                $this->perfil->transRollback();
            }

            if (!$retorno['erro']) {
                session()->setFlashdata('msg', $retorno['msg']);
                $this->perfil->transCommit();
                $this->perfilitem->transCommit();
            }
            echo json_encode($retorno);
        }
    }
}
