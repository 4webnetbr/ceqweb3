<?php

namespace App\Models\Fornec;

use App\Entities\Fornecedores\EntOcoNotifDesvio;
use App\Models\LogMonModel;
use CodeIgniter\Model;

/**
 * T42 — Desvio de Qualidade (oco_notif_desvio).
 * Segue o mesmo padrão de App\Models\Ocorre\OcorreOcorrenciaModel.
 */
class FornecNotifDesvioModel extends Model
{
    protected $DBGroup    = 'dbOcorrencia';
    protected $table      = 'oco_notif_desvio';
    protected $view       = 'vw_oco_notif_desvio_relac';
    protected $primaryKey = 'ndv_id';

    protected $returnType = EntOcoNotifDesvio::class;

    protected $allowedFields = [
        'ndv_id',
        'oco_id',
        'ndv_local',
        'ndv_descreva',
        'stt_id',
        'usu_criou',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'ndv_criado';
    protected $updatedField  = 'ndv_alterado';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'ndv_excluido';

    protected $validationRules = [
        'ndv_local'    => 'required|min_length[5]|max_length[50]',
        'ndv_descreva' => 'required|min_length[5]|max_length[200]',
    ];

    protected $validationMessages = [
        'ndv_local' => [
            'required'   => 'O campo Local é obrigatório',
            'min_length' => 'O campo Local deve conter no mínimo 5 caracteres',
            'max_length' => 'O campo Local deve conter no máximo 50 caracteres',
        ],
        'ndv_descreva' => [
            'required'   => 'O campo Descreva é obrigatório',
            'min_length' => 'O campo Descreva deve conter no mínimo 5 caracteres',
            'max_length' => 'O campo Descreva deve conter no máximo 200 caracteres',
        ],
    ];

    protected $allowCallbacks = true;
    protected $afterInsert    = ['depoisInsert'];
    protected $afterUpdate    = ['depoisUpdate'];
    protected $afterDelete    = ['depoisDelete'];

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

    public function getNotifDesvio($id)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->where('ndv_id', $id);

        return $builder->get()->getRow();
    }

    public function getListaCompleta()
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view);
        $builder->select('*');
        $builder->orderBy('stt_ordem, oco_data DESC, ndv_id ASC');

        return $builder->get()->getResult();
    }

    /**
     * RN02.3 — resolve o stt_id de "Pendente" da T42, usado pela integração
     * automática disparada em OcoTrataOcorrencia::gerarNotificacaoDesvio()
     * quando a ação "Notificação do Fornecedor" (tpa_tipo = 5) é executada.
     */
    public function getStatusPendenteId(): ?int
    {
        return $this->getStatusPorNome('Pendente');
    }

    public function getStatusConcluidaId(): ?int
    {
        return $this->getStatusPorNome('Concluída');
    }

    private function getStatusPorNome(string $nome): ?int
    {
        $schemaDefault = db_connect('default')->getDatabase();
        $db            = db_connect('dbOcorrencia');

        $row = $db->table("{$schemaDefault}.cfg_status st")
            ->select('st.stt_id')
            ->join("{$schemaDefault}.cfg_tela t", 't.tel_id = st.tel_id')
            ->where('t.tel_controler', 'NotifDesvio')
            ->where('st.stt_nome', $nome)
            ->get()
            ->getRow();

        return $row ? (int) $row->stt_id : null;
    }

    /**
     * Usado pela tela intermediária de seleção de produtos de T43 (RN03.1):
     * registros de T42 já Concluídos e ainda não vinculados a nenhuma linha
     * de oco_notif_evento_produto ("disponível para notificação").
     */
    public function getDisponiveisParaNotificacao(?string $fabricante = null)
    {
        $db = db_connect('dbOcorrencia');

        $builder = $db->table($this->view . ' v');
        $builder->select('v.*');
        $builder->where('v.stt_nome', 'Concluída');
        $builder->where('NOT EXISTS (
            SELECT 1 FROM oco_notif_evento_produto p WHERE p.ndv_id = v.ndv_id
        )', null, false);

        if ($fabricante) {
            $builder->where('v.fab_apeFab', $fabricante);
        }

        $builder->orderBy('v.oco_data DESC');

        return $builder->get()->getResult();
    }
}
