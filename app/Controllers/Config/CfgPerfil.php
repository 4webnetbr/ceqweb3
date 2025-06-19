<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;

class CfgPerfil extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $perfil;
    public $perfilitem;
    public $tela;

    public function __construct()
    {
        $this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
        $this->perfil       = new ConfigPerfilModel();
        $this->perfilitem   = new ConfigPerfilItemModel();
        $this->tela         = new ConfigTelaModel();

        if ($this->data['erromsg'] != '') {
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
        $this->data['colunas'] = montaColunasLista($this->data, 'prf_id,');
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
        $this->data['exclusao'] = false;
        $perfis = [
            'data' => montaListaColunas($this->data, 'prf_id', $dados_perfil, 'prf_nome'),
        ];

        echo json_encode($perfis);
    }



    public function add()
    {
        $this->defCampos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0][0] = $this->prf_id;
        $campos[0][0][1] = $this->prf_nome;
        $campos[0][0][2] = $this->prf_dashboard;
        $campos[0][0][3] = $this->prf_descricao;

        $secao[1] = 'Permissões';
        $itens = montaListaTelas();
        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        for ($mn = 0; $mn < sizeof($itens); $mn++) {
            if ($modu != $itens[$mn]['mod_id']) {
                $cti = 0;
                $ctm++;
                $modu = $itens[$mn]['mod_id'];
                $this->defCamposItens(0, $modu, $itens[$mn]);
                $campos2[1][$modu][$cti][0] = $this->pit_modu;
                $campos2[1][$modu][$cti][1] = $this->pit_tela;
                $campos2[1][$modu][$cti][2] = $this->pit_all;
                $campos2[1][$modu][$cti][3] = $this->pit_consulta;
                $campos2[1][$modu][$cti][4] = $this->pit_adicao;
                $campos2[1][$modu][$cti][5] = $this->pit_edicao;
                $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
                $campos2[1][$modu][$cti][7] = $this->pit_notifica;
            }
            $cti++;
            $item = $itens[$mn]['tel_id'];
            $this->defCamposItens($item, $modu, $itens[$mn]);
            $campos2[1][$modu][$cti][0] = $this->pit_modu;
            $campos2[1][$modu][$cti][1] = $this->pit_tela;
            $campos2[1][$modu][$cti][2] = $this->pit_all;
            $campos2[1][$modu][$cti][3] = $this->pit_consulta;
            $campos2[1][$modu][$cti][4] = $this->pit_adicao;
            $campos2[1][$modu][$cti][5] = $this->pit_edicao;
            $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
            $campos2[1][$modu][$cti][7] = $this->pit_notifica;
        }

        $this->data['secoes']   = $secao;
        $this->data['campos']   = $campos;
        $this->data['campos2']  = $campos2;
        $this->data['destino']  = 'store';

        echo view('vw_edicao_perfil', $this->data);
    }

    public function edit($id)
    {
        $dados_perfil = $this->perfil->find($id);
        $this->defCampos($dados_perfil);

        $secao[0] = 'Dados Gerais';
        $campos[0][0][0] = $this->prf_id;
        $campos[0][0][1] = $this->prf_nome;
        $campos[0][0][2] = $this->prf_dashboard;
        $campos[0][0][3] = $this->prf_descricao;

        $secao[1] = 'Permissões';
        $telas = montaListaTelas();
        $id_telas = array_column($telas, 'tel_nome', 'tel_id');
        $itens = montaListaItensPerfil($id, $id_telas);
        foreach ($itens as $key => $value) {
            for ($t = 0; $t < count($telas); $t++) {
                if ($telas[$t]['tel_id'] == $key) {
                    $telas[$t]['pit_permissao'] = $itens[$key];
                    break;
                }
            }
        }
        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        for ($mn = 0; $mn < sizeof($telas); $mn++) {
            if ($modu != $telas[$mn]['mod_id']) {
                $cti = 0;
                $ctm++;
                $modu = $telas[$mn]['mod_id'];
                $clas = $telas[$mn]['tel_id'];
                // $perfil = $this->perfilitem->getItemPerfil($dados_perfil['prf_id'], $tela);
                // debug($perfil, false);
                $this->defCamposItens(0, $modu, $telas[$mn]);

                $campos2[1][$modu][$cti][0] = $this->pit_modu;
                $campos2[1][$modu][$cti][1] = $this->pit_tela;
                $campos2[1][$modu][$cti][2] = $this->pit_all;
                $campos2[1][$modu][$cti][3] = $this->pit_consulta;
                $campos2[1][$modu][$cti][4] = $this->pit_adicao;
                $campos2[1][$modu][$cti][5] = $this->pit_edicao;
                $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
                $campos2[1][$modu][$cti][7] = $this->pit_notifica;
            }
            // debug('Módulo', false);
            $cti++;
            $item = $telas[$mn]['tel_id'];
            // debug($itens[$mn], false);
            $this->defCamposItens($item, $modu, $telas[$mn]);
            $campos2[1][$modu][$cti][0] = $this->pit_modu;
            $campos2[1][$modu][$cti][1] = $this->pit_tela;
            $campos2[1][$modu][$cti][2] = $this->pit_all;
            $campos2[1][$modu][$cti][3] = $this->pit_consulta;
            $campos2[1][$modu][$cti][4] = $this->pit_adicao;
            $campos2[1][$modu][$cti][5] = $this->pit_edicao;
            $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
            $campos2[1][$modu][$cti][7] = $this->pit_notifica;
        }

        $this->data['secoes']   = $secao;
        $this->data['campos']   = $campos;
        $this->data['campos2']  = $campos2;
        $this->data['destino']  = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_perfil', $id);

        echo view('vw_edicao_perfil', $this->data);
    }

    public function delete($id)
    {
        $this->perfil->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler']));
    }

    public function defCampos($dados = false)
    {
        $id             = new Campos();
        $id->objeto     = 'oculto';
        $id->nome       = 'prf_id';
        $id->valor      = (isset($dados['prf_id'])) ? $dados['prf_id'] : '';
        $this->prf_id   = $id->create();

        $nome           =  new Campos();
        $nome->objeto   = 'input';
        $nome->tipo     = 'text';
        $nome->nome     = 'prf_nome';
        $nome->id       = 'prf_nome';
        $nome->label    = 'Nome';
        $nome->place    = 'Nome';
        $nome->obrigatorio = true;
        $nome->hint     = 'Informe o Nome do Perfil';
        $nome->size     = 30;
        $nome->tamanho  = 30;
        $nome->valor    = (isset($dados['prf_nome'])) ? $dados['prf_nome'] : '';
        $this->prf_nome = $nome->create();

        $telas = array_column($this->tela->getTelaId(), 'tel_nome', 'tel_id');
        $dash               =  new Campos();
        $dash->objeto       = 'select';
        $dash->nome         = 'prf_dashboard';
        $dash->id           = 'prf_dashboard';
        $dash->label        = 'Dashboard';
        $dash->obrigatorio  = true;
        $dash->hint         = 'Escolha o Dashboard';
        $dash->size         = 50;
        $dash->tamanho      = 55;
        $dash->opcoes       = $telas;
        $dash->valor        = (isset($dados['prf_dashboard'])) ? $dados['prf_dashboard'] : '';
        $dash->selecionado  = $dash->valor;
        $this->prf_dashboard   = $dash->create();

        $desc =  new Campos();
        $desc->objeto   = 'texto';
        $desc->nome     = 'prf_descricao';
        $desc->id       = 'prf_descricao';
        $desc->label    = 'Descrição';
        $desc->place    = 'Descrição';
        $desc->obrigatorio = false;
        $desc->hint     = 'Informe a Descrição';
        $desc->size     = 70;
        $desc->max_size = 3;
        $desc->tamanho  = 80;
        $desc->valor    = (isset($dados['prf_descricao'])) ? $dados['prf_descricao'] : '';
        $this->prf_descricao = $desc->create();
    }

    public function defCamposItens($pos, $mod, $items = false)
    {
		$modu				= new Campos();
		$modu->objeto		= 'text_show';
		$modu->valor		= (isset($items['mod_nome']))?$items['mod_nome']:'';
		$this->pit_modu	    = $modu->create();

        $tela				= new Campos();
		$tela->objeto		= 'text_show';
		$tela->valor		= (isset($items['tel_nome']))?$items['tel_nome']:'';
		$this->pit_tela	    = $tela->create();

        $all =  new Campos();
        $all->objeto       = 'checkbox';
        $all->nome         = "pit_all[$mod][$pos]";
        $all->id           = "pit_all[$mod][$pos]";
        $all->label        = '';
        $all->obrigatorio  = false;
        $all->valor        = '';
        $all->selecionado  = 'X';
        $all->size         = 20;
        $all->tamanho      = 1;
        $all->classs       = "pit_all[$mod]";
        $this->pit_all     = $all->create();

        $consulta =  new Campos();
        $consulta->objeto       = 'checkbox';
        $consulta->nome         = "pit_consulta[$mod][$pos]";
        $consulta->id           = "pit_consulta[$mod][$pos]";
        $consulta->label        = '';
        $consulta->obrigatorio  = false;
        $consulta->valor        = 'C';
        $consulta->selecionado  = '';
        if (isset($items['pit_permissao'])) {
            if (strpos($items['pit_permissao'], 'C') !== false) {
                $consulta->selecionado  = 'C';
            }
        }
        $consulta->size         = 20;
        $consulta->tamanho      = 1;
        $consulta->classs       = "pit_consulta[$mod] pit_all[$mod]";
        $this->pit_consulta     = $consulta->create();

        $adicao =  new Campos();
        $adicao->objeto      = 'checkbox';
        $adicao->nome        = "pit_adicao[$mod][$pos]";
        $adicao->id          = "pit_adicao[$mod][$pos]";
        $adicao->label       = '';
        $adicao->obrigatorio = false;
        $adicao->valor          = 'A';
        $adicao->selecionado    = '';
        if (isset($items['pit_permissao'])) {
            if (strpos($items['pit_permissao'], 'A') !== false) {
                $adicao->selecionado  = 'A';
            }
        }
        $adicao->size       = 20;
        $adicao->tamanho   = 1;
        $adicao->classs    = "pit_adicao[$mod]  pit_all[$mod]";
        $this->pit_adicao  = $adicao->create();

        $edicao =  new Campos();
        $edicao->objeto         = 'checkbox';
        $edicao->nome           = "pit_edicao[$mod][$pos]";
        $edicao->id             = "pit_edicao[$mod][$pos]";
        $edicao->label          = '';
        $edicao->obrigatorio    = false;
        $edicao->valor          = 'E';
        $edicao->selecionado    = '';
        if (isset($items['pit_permissao'])) {
            if (strpos($items['pit_permissao'], 'E') !== false) {
                $edicao->selecionado  = 'E';
            }
        }
        $edicao->size           = 20;
        $edicao->tamanho        = 1;
        $edicao->classs         = "pit_edicao[$mod] pit_all[$mod]";
        $this->pit_edicao       = $edicao->create();

        $exclusao =  new Campos();
        $exclusao->objeto      = 'checkbox';
        $exclusao->nome        = "pit_exclusao[$mod][$pos]";
        $exclusao->id          = "pit_exclusao[$mod][$pos]";
        $exclusao->label       = '';
        $exclusao->obrigatorio = false;
        $exclusao->valor       = 'X';
        $exclusao->selecionado = '';
        if (isset($items['pit_permissao'])) {
            if (strpos($items['pit_permissao'], 'X') !== false) {
                $exclusao->selecionado  = 'X';
            }
        }
        $exclusao->size        = 20;
        $exclusao->tamanho     = 1;
        $exclusao->classs      = "pit_exclusao[$mod] pit_all[$mod]";
        $this->pit_exclusao    = $exclusao->create();

        $notifica =  new Campos();
        $notifica->objeto      = 'checkbox';
        $notifica->nome        = "pit_notifica[$mod][$pos]";
        $notifica->id          = "pit_notifica[$mod][$pos]";
        $notifica->label       = '';
        $notifica->obrigatorio = false;
        $notifica->valor       = 'N';
        $notifica->selecionado = '';
        if (isset($items['pit_permissao'])) {
            if (strpos($items['pit_permissao'], 'N') !== false) {
                $notifica->selecionado  = 'N';
            }
        }
        $notifica->size        = 20;
        $notifica->tamanho     = 1;
        $notifica->classs      = "pit_notifica[$mod] pit_all[$mod]";
        $this->pit_notifica    = $notifica->create();
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
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao'])?$d_ite[$chave][$tela]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_adicao'])) {
                    $pit_adicao = $dados['pit_adicao'];
                    foreach ($pit_adicao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao'])?$d_ite[$chave][$tela]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_edicao'])) {
                    $pit_edicao = $dados['pit_edicao'];
                    foreach ($pit_edicao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao'])?$d_ite[$chave][$tela]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_exclusao'])) {
                    $pit_exclusao = $dados['pit_exclusao'];
                    foreach ($pit_exclusao as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao'])?$d_ite[$chave][$tela]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_notifica'])) {
                    $pit_notifica = $dados['pit_notifica'];
                    foreach ($pit_notifica as $chave => $valor) {
                        foreach ($valor as $tela => $opcao) {
                            if ($tela > 0) {
                                $d_ite[$chave][$tela]['permissao'] = isset($d_ite[$chave][$tela]['permissao'])?$d_ite[$chave][$tela]['permissao'].$opcao:$opcao;
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
