<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Entities\Config\EntCfgModulo;
use App\Models\CommonModel;
use App\Models\Config\ConfigModuloModel;
use App\Traits\ForeignKeyUsageChecker;

class CfgModulo extends BaseController
{
    use ForeignKeyUsageChecker;

    protected array $data;
    protected ConfigModuloModel $modulo;
    protected CommonModel $common;

    public function __construct()
    {
        $this->data   = session()->getFlashdata('dados_tela') ?? [];
        $this->modulo = new ConfigModuloModel();
        $this->common    = new CommonModel();
    }

    public function index()
    {
        $this->data['colunas']   = montaColunasLista($this->data, 'mod_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }

    public function lista()
    {
        $dados = $this->modulo->getModulo();

        echo json_encode([
            'data' => montaListaColunasEnt($this->data, 'mod_id', $dados, 'mod_nome')
        ]);
    }

    public function add($modal = false)
    {
        $mod = new EntCfgModulo();

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $mod->campos['mod_id'],
            $mod->campos['mod_nome'],
            $mod->campos['mod_icone'],
            $mod->campos['mod_dbgroup'],
            // $mod->campos['mod_ativo'],
        ]];
        $this->data['destino'] = 'store';

        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }

    public function edit($id, $show = false)
    {
        $mod = $this->modulo->find($id);

        if (!$mod) {
            return redirectWithError($this->data['controler'], 41);
            // return view('errors/registro_nao_encontrado', [
            //     'mensagem' => 'Módulo não encontrado'
            // ]);
        }

        $mod->campos = $mod->defCampos($show);

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $mod->campos['mod_id'],
            $mod->campos['mod_nome'],
            $mod->campos['mod_icone'],
            $mod->campos['mod_dbgroup'],
            // $mod->campos['mod_ativo'],
        ]];
        $this->data['destino'] = 'store';
        $this->data['log']     = buscaLog('cfg_modulo', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        // try {
        //     $this->modulo->delete($id);
        //     session()->setFlashdata('msg', 'Módulo excluído com sucesso!');
        //     echo json_encode(['erro' => false]);
        // } catch (\Throwable $e) {
        //     echo json_encode([
        //         'erro' => true,
        //         'msg'  => 'Não foi possível excluir o módulo.'
        //     ]);
        // }

        $ret = [];
        try {
            // Checa uso do status em outros bancos
            $this->verificarUsoEmRelacionamentos('cfg_modulo', 'mod_id', (int) $id);

            // Soft delete
            $this->modulo->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo excluído com sucesso!');
        } catch (\Exception $e) {
            $ret['erro'] = true;
            $ret['msg']  = 3;
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        $this->modulo->update($id, [
            'mod_ativo' => $tipo == 1 ? 'A' : 'I'
        ]);

        session()->setFlashdata('msg', 'Módulo alterado com sucesso!');
        echo json_encode(['erro' => false]);
    }

    public function store()
    {
        $ret = [];
        $ret['erro'] = false;
        $postado = $this->request->getPost();
        $ent = new EntCfgModulo($postado);

        $exists = $this->common->verificaUnico($this->modulo, 'mod_nome', $postado['mod_nome'], 'mod_id', $postado['mod_id']);

        // debug(var_dump($exists), true);
        if (intval($exists) > 0) {
            $ret['erro'] = true;
            $ret['msg'] = 8;
        } else {
            $this->modulo->transBegin();
            try {
                // debug($ent, true);
                $salva = $this->modulo->save($ent);
                // debug($this->status->getLastQuery(), true);

                if (!$salva) {
                    $ret['erro'] = true;
                    $ret['msg']  = implode('<br>', $this->modulo->errors());
                }
                if (!$ret['erro']) {
                    $this->modulo->transCommit();
                    session()->setFlashdata('msg', 'Módulo gravado com sucesso!');
                    $ret['erro'] = false;
                    $ret['url'] = site_url($this->data['controler']);
                }
            } catch (\Throwable $e) {
                $this->modulo->transRollback();
                $ret = [
                    'erro' => true,
                    'msg'  => $e->getMessage() ?: 'Erro ao salvar Módulo.'
                ];
            }
        }
        echo json_encode($ret);
    }
}
