<?php

namespace App\Models\Ocorre;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Models\Ocorre\OcorreModOcorrenciaModel;
use App\Entities\Ocorrencia\EntOcoOcorrencia;

class OcorreOcorrenciaModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_ocorrencia';
    protected $view       = 'vw_oco_ocorrencia_relac';
    protected $primaryKey = 'oco_id';

    protected $returnType = EntOcoOcorrencia::class;

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
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
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
    
        $result = $builder
            ->orderBy('stt_id', 'DESC')
            ->get()
            ->getResult();
    
        return $result[0]->stt_id ?? null;
    }
        

    public function getListaCompleta()
    {
        $builder = $this->db->table($this->view);
    
        return $builder
            ->get()
            ->getResult();
    }
    

    public function getById($id)
    {
        $builder = $this->db->table($this->view)
            ->where('oco_id', $id);
    
        $result = $builder
            ->get()
            ->getResult();
    
        return $result[0] ?? null;
    }


    public function getAcoesByModelo($moc_id)
    {
        $builder = $this->db->table('oco_moc_acao')
            ->where('moc_id', $moc_id);
    
        return $builder
            ->get()
            ->getResult();
    }
    

    public function salvarOcorrencia(EntOcoOcorrencia $ocorrencia)
    {
        try {
            $this->db->transBegin();

            $statusPendente = $this->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $this->getStatusIdByNome('Finalização automática', 56);

            // padrão
            $status = $statusPendente;

            // AÇÃO
            $tpa_id = $ocorrencia->tpa_id ?? null;

            // MOVIMENTAÇÃO
            $tmo_id = $ocorrencia->tmo_id ?? null;

            // REGRA DE NEGÓCIO
            if ((int) $tpa_id === 8) {
                $status = $statusFinaliza;
            }

            // MODELO DA OCORRÊNCIA
            $modelMod = new OcorreModOcorrenciaModel();
            $modelo   = $modelMod->buscarPorTipo($ocorrencia->tpo_id);
            if (!empty($modelo) && isset($modelo[0])) {
                $moc_id = is_object($modelo[0])
                    ? ($modelo[0]->moc_id   ?? null)
                    : ($modelo[0]['moc_id'] ?? null);
            }

            $insert = [
                'tpo_id'        => $ocorrencia->tpo_id,
                'oco_descricao' => $ocorrencia->oco_descricao,
                'lot_lote'      => $ocorrencia->lot_lote,
                'pro_despro'    => $ocorrencia->pro_despro,
                'oco_qtd'       => $ocorrencia->oco_qtd,
                'oco_data'      => $ocorrencia->oco_data,
                'moc_id'        => $moc_id,
                'stt_id'        => $status,
                'tpa_id'        => $tpa_id,
                'tmo_id'        => $tmo_id,
            ];

            if (!empty($ocorrencia->oco_justi)) {
                $insert['oco_justi'] = $ocorrencia->oco_justi;
            }

            if (!$this->insert($insert)) {
                throw new \Exception('Erro ao inserir ocorrência.');
            }

            $this->db->transCommit();

            return [
                'erro' => false,
                'msg'  => 'Ocorrência registrada com sucesso!',
                'url'  => site_url('OcoOcorrencia'),
            ];

        } catch (\Exception $e) {

            $this->db->transRollback();

            return [
                'erro' => true,
                'msg'  => 'Erro ao gravar a Ocorrência:<br><br>' . $e->getMessage(),
            ];
        }
    }


    public function atualizarOcorrencia(int $id, EntOcoOcorrencia $ocorrencia)
    {
        try {
            $this->db->transBegin();
    
            $registro = $this->find($id);
            if (!$registro) {
                throw new \Exception('Ocorrência não encontrada.');
            }
    
            // Status
            $statusPendente = $this->getStatusIdByNome('Pendente', 56);
            $statusFinaliza = $this->getStatusIdByNome('Finalização automática', 56);
    
            // padrão
            $status = $statusPendente;
    
            // AÇÃO
            $tpa_id = $ocorrencia->tpa_id ?? null;
    
            // MOVIMENTAÇÃO
            $tmo_id = $ocorrencia->tmo_id ?? null;
    
            // REGRA DE NEGÓCIO
            if ((int) $tpa_id === 8) {
                $status = $statusFinaliza;
            }
    
            // MODELO DA OCORRÊNCIA
            $modelMod = new OcorreModOcorrenciaModel();
            $modelo   = $modelMod->buscarPorTipo($ocorrencia->tpo_id);
            if (!empty($modelo) && isset($modelo[0])) {
                $moc_id = is_object($modelo[0])
                    ? ($modelo[0]->moc_id   ?? null)
                    : ($modelo[0]['moc_id'] ?? null);
            }
    
            $atualiza = [
                'tpo_id'        => $ocorrencia->tpo_id,
                'oco_descricao' => $ocorrencia->oco_descricao,
                'lot_lote'      => $ocorrencia->lot_lote,
                'pro_despro'    => $ocorrencia->pro_despro,
                'oco_qtd'       => $ocorrencia->oco_qtd,
                'oco_data'      => $ocorrencia->oco_data,
                'moc_id'        => $moc_id,
                'stt_id'        => $status,
                'tpa_id'        => $tpa_id,
                'tmo_id'        => $tmo_id,
            ];
    
            if (!empty($ocorrencia->oco_justi)) {
                $atualiza['oco_justi'] = $ocorrencia->oco_justi;
            }
    
            if (!$this->update($id, $atualiza)) {
                throw new \Exception('Erro ao atualizar ocorrência.');
            }
    
            $this->db->transCommit();
    
            return [
                'erro' => false,
                'msg'  => 'Ocorrência atualizada com sucesso!',
                'url'  => site_url('OcoOcorrencia'),
            ];
    
        } catch (\Exception $e) {
    
            $this->db->transRollback();
    
            return [
                'erro' => true,
                'msg'  => 'Erro ao atualizar a ocorrência:<br><br>' . $e->getMessage(),
            ];
        }
    }
    
        
    public function getSelectPorTipo(int $tpo_id): array
    {
        $modelMod    = new OcorreModOcorrenciaModel();
        $ocorrencias = $modelMod->buscarPorTipo($tpo_id);
    
        $retorno = [];
    
        foreach ($ocorrencias as $ocorrencia) {
            $retorno[$ocorrencia->moc_id] = $ocorrencia->moc_nome;
        }
    
        return $retorno;
    }
}
