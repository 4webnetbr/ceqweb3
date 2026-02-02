<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreModOcorrenciaModel;


class OcorreOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $primaryKey = 'oco_id';
    protected $allowedFields = [
        'oco_id',
        'tpo_id',
        'moc_id',
        'tpa_id',
        'oco_descricao',
        'lot_lote',
        'pro_id',
        'pro_despro',
        'oco_qtd',
        'oco_data',
        'stt_id',
        'tmo_id',
        'oco_justi',

    ];

    protected $validationRules = [
        'oco_descricao' => 'required|max_length[100]|min_length[3]',
    ];

    protected $validationMessages = [
        'oco_descricao'   => [
            'required'    => 'O campo Nome do Tipo da Ocorrência é Obrigatório',
            'max_length'  => 'O Campo deve Conter no Máximo 100 Caracteres',
            'min_length'  => 'O Campo Devente Conter no Minimo 3 Caracteres',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }


    public function getStatusIdByNome(string $nome, ?int $telId = null): ?int
    {
        $builder = $this->db->table('config_ceqweb_db.cfg_status')
            ->select('stt_id')
            ->where('stt_nome', $nome);

        if ($telId !== null) {
            $builder->where('tel_id', $telId);
        }
        $row = $builder->orderBy('stt_id', 'DESC')->get()->getRow();

        return $row->stt_id ?? null;
    }


    public function getListaCompleta()
    {
        // Buscar VIEW
        $dados = $this->db->table($this->view)
            ->get()
            ->getResultArray();
        // Buscar LOG
        $ids = array_column($dados, 'oco_id');
        $log = buscaLogTabela('oco_ocorrencia', $ids);
        return $dados;
    }


    public function getById($id)
    {
        $dados = $this->db->table($this->view)
            ->where('oco_id', $id)
            ->get()
            ->getRowArray();
        if (!$dados) {
            return null;
        }
        return $dados;
    }


    public function getAcoesByModelo($moc_id)
    {
        return $this->db->table('oco_moc_acao')
            ->where('moc_id', $moc_id)
            ->get()
            ->getResultArray();
    }



    public function salvarOcorrencia(array $dados)
    {
        try {
            $this->db->transBegin();

            $statusPendente = $this->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $this->getStatusIdByNome('Finalização automática', 56);

            // padrão
            $status = $statusPendente;

            // AÇÃO VEM DO FORMULÁRIO
            $tpa_id = $dados['tpa_id'] ?? null;

            // MOVIMENTAÇÃO 
            $tmo_id = $dados['tmo_id'] ?? null;

            // REGRA DE NEGÓCIO
            if ((int)$tpa_id === 8) {
                $status = $statusFinaliza;
            }

            $modelMod = new OcorreModOcorrenciaModel();
            $modelo = $modelMod->buscarPorTipo($dados['tpo_id']);
            $moc_id = $modelo[0]['moc_id'] ?? null;
            $insert = [
                'tpo_id'        => $dados['tpo_id'],
                'oco_descricao' => $dados['oco_descricao'],
                'lot_lote'      => $dados['lot_lote'],
                'pro_despro'    => $dados['pro_despro'],
                'oco_qtd'       => $dados['oco_qtd'],
                'oco_data'      => $dados['oco_data'],
                'moc_id'        => $moc_id,
                'stt_id'        => $status,
                'tpa_id'        => $tpa_id,
                'tmo_id'        => $tmo_id,
            ];

            if (isset($dados['oco_justi'])) {
                $atualiza['oco_justi'] = $dados['oco_justi'];
            }
            if (!$this->insert($insert)) {
                throw new \Exception('Erro ao inserir ocorrência.');
            }

            $this->db->transCommit();

            return [
                'erro' => false,
                'msg'  => 'Ocorrência registrada com sucesso!',
                'url'  => site_url('OcoOcorrencia')
            ];
        } catch (\Exception $e) {

            $this->db->transRollback();

            return [
                'erro' => true,
                'msg'  => 'Erro ao gravar a Ocorrência:<br><br>' . $e->getMessage()
            ];
        }
    }


    public function atualizarOcorrencia(int $id, array $dados)
    {
        try {
            $this->db->transBegin();

            $ocorrencia = $this->find($id);
            if (!$ocorrencia) {
                throw new \Exception('Ocorrência não encontrada.');
            }

            // Status
            $statusPendente = $this->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $this->getStatusIdByNome('Finalização automática', 56);

            // padrão
            $status = $statusPendente;

            // AÇÃO VEM DO FORMULÁRIO
            $tpa_id = $dados['tpa_id'] ?? null;

            // MOVIMENTAÇÃO 
            $tmo_id = $dados['tmo_id'] ?? null;

            if ((int)$tpa_id === 8) {
                $status = $statusFinaliza;
            }

            $modelMod = new OcorreModOcorrenciaModel();
            $modelo = $modelMod->buscarPorTipo($dados['tpo_id']);
            $moc_id = $modelo[0]['moc_id'] ?? null;
            $atualiza = [
                'tpo_id'        => $dados['tpo_id'],
                'oco_descricao' => $dados['oco_descricao'],
                'lot_lote'      => $dados['lot_lote'],
                'pro_despro'    => $dados['pro_despro'],
                'oco_qtd'       => $dados['oco_qtd'],
                'oco_data'      => $dados['oco_data'],
                'moc_id'        => $moc_id,
                'stt_id'        => $status,
                'tpa_id'        => $tpa_id,
                'tmo_id'        => $tmo_id,
            ];
            if (isset($dados['oco_justi'])) {
                $atualiza['oco_justi'] = $dados['oco_justi'];
            }

            if (!$this->update($id, $atualiza)) {
                throw new \Exception('Erro ao atualizar ocorrência.');
            }

            $this->db->transCommit();
            return [
                'erro' => false,
                'msg'  => 'Ocorrência atualizada com sucesso!',
                'url'  => site_url('OcoOcorrencia')
            ];
        } catch (\Exception $e) {

            $this->db->transRollback();
            return [
                'erro' => true,
                'msg'  => 'Erro ao atualizar a ocorrência:<br><br>' . $e->getMessage()
            ];
        }
    }


    public function getSelectPorTipo(int $tpo_id): array
    {
        $modelMod    = new OcorreModOcorrenciaModel();
        $ocorrencias = $modelMod->buscarPorTipo($tpo_id);

        $retorno = [];
        foreach ($ocorrencias as $ocorrencia) {
            $retorno[$ocorrencia['moc_id']] = $ocorrencia['moc_nome'];
        }

        return $retorno;
    }



    public function defCampos($dados = false)

    // DADOS GERAIS
    {
        $ret = [];

        // TIPO DE OCORRÊNCIA
        $modelTipo = new OcorreTipoOcorrenciaModel();
        $tipos = $modelTipo->where('tpo_ativo', 'A')->findAll();

        foreach ($tipos as $t) {
            $opcoes[$t['tpo_id']] = $t['tpo_nome'];
        }

        $tipo              = new MyCampo('oco_ocorrencia', 'tpo_id');
        $tipo->opcoes      = $opcoes;
        $tipo->valor       = (isset($dados['tpo_id'])) ? $dados['tpo_id'] : '';
        $tipo->label       = 'Tipo de Ocorrência';
        $tipo->obrigatorio = true;
        $tipo->dispForm    = '2col';
        $tipo->largura     = 58;

        $ret['tpo_id'] = $tipo->crSelect();


        // AÇÃO
        $acao = new MyCampo('oco_ocorrencia', 'tpa_id');
        $acao->valor       = $dados['tpa_id'] ?? '';
        $acao->opcoes      = [];
        $acao->label       = 'Ação';
        $acao->dispForm    = '2col';
        $acao->largura     = 58;

        $acao->pai      = 'tpo_id';
        $acao->urlbusca = base_url('Buscas/buscaAcoesPorTipo');

        $ret['tpa_id'] = $acao->crDepende();


        // DESCRIÇÃO
        $desc              = new MyCampo('oco_ocorrencia', 'oco_descricao');
        $desc->valor       = (isset($dados['oco_descricao'])) ? $dados['oco_descricao'] : '';
        $desc->obrigatorio = true;
        $desc->linhas      = 3;
        $desc->colunas     = 56;
        $desc->dispForm    = '2col';

        $ret['oco_descricao'] = $desc->crTexto();


        // LOTE
        $lote              = new MyCampo('pro_sap_lote', 'lot_lote');
        $lote->valor       = (isset($dados['lot_lote'])) ? $dados['lot_lote'] : '';
        $lote->obrigatorio = true;
        $lote->size        = 54;
        $lote->funcBlur    = "buscaLoteProduto(this,'" . base_url('/buscas/buscaProdutoporLote') . "')";
        $ret['lot_lote'] = $lote->crInput();


        // PRODUTO 
        $produto           = new MyCampo('pro_sap_produto', 'pro_despro');
        $produto->valor    = (isset($dados['pro_despro'])) ? $dados['pro_despro'] : '';
        $produto->dispForm = '2col';
        $produto->size     = 54;
        $produto->leitura  = true;
        $ret['pro_despro'] = $produto->crInput();

        // QUANTIDADE
        $qtd               = new MyCampo('oco_ocorrencia', 'oco_qtd');
        $qtd->valor        = $dados['oco_qtd'] ?? 0;
        $qtd->label        = 'Quantidade';
        $qtd->dispForm     = '2col';
        $qtd->minimo       = 1;
        $qtd->step         = 1;
        $qtd->largura      = 10;
        $qtd->obrigatorio  = true;

        $ret['oco_qtd'] = $qtd->crInput();

        // DATA 
        $data              = new MyCampo('oco_ocorrencia', 'oco_data');
        $data->valor       = $dados['oco_data'] ?? date('Y-m-d\TH:i');
        $data->label       = 'Data da Ocorrência';
        $data->dispForm    = '2col';
        $data->leitura     = true;
        $data->largura     = 30;

        $ret['oco_data'] = $data->crInput();

        return $ret;
    }
}
