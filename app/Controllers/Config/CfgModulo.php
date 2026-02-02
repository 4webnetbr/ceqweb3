<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Models\Config\ConfigModuloModel;
use App\Entities\Config\EntCfgModulo;
use App\Traits\ForeignKeyUsageChecker;

class CfgModulo extends BaseController
{
    use ForeignKeyUsageChecker;

    protected array $data;
    protected ConfigModuloModel $modulo;

    public function __construct()
    {
        $this->data   = session()->getFlashdata('dados_tela') ?? [];
        $this->modulo = new ConfigModuloModel();
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
            $mod->campos['mod_icon'],
            // $mod->campos['mod_ativo'],
        ]];
        $this->data['destino'] = 'store';

        echo view($modal ? 'vw_edicao_modal' : 'vw_edicao', $this->data);
    }

    public function edit($id, $show = false)
    {
        $mod = $this->modulo->find($id);

        if (!$mod) {
            return view('errors/registro_nao_encontrado', [
                'mensagem' => 'Módulo não encontrado'
            ]);
        }

        $mod->campos = $mod->defCampos($show);

        $this->data['secoes'] = ['Dados Gerais'];
        $this->data['campos'] = [[
            $mod->campos['mod_id'],
            $mod->campos['mod_nome'],
            $mod->campos['mod_icone'],
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
        $ent = new EntCfgModulo($this->request->getPost());

        if (!$this->modulo->save($ent)) {
            echo json_encode([
                'erro' => true,
                'msg'  => implode('<br>', $this->modulo->errors())
            ]);
            return;
        }

        session()->setFlashdata('msg', 'Módulo gravado com sucesso!');
        echo json_encode([
            'erro' => false,
            'url'  => site_url($this->data['controler'])
        ]);
    }
}
