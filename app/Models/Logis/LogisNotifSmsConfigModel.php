<?php

namespace App\Models\Logis;

use CodeIgniter\Model;
use App\Models\LogMonModel;
use App\Entities\Logistica\EntLogNotifSmsConfig;

/**
 * Regras de Notificação SMS (log_notif_sms_config, prefixo nsc_).
 * Segue o mesmo padrão de App\Models\Config\ConfigCorModel /
 * App\Models\Fornec\FornecNotifDesvioModel (callbacks de auditoria via
 * LogMonModel).
 */
class LogisNotifSmsConfigModel extends Model
{
    protected $DBGroup    = 'dbLogistica';
    protected $table      = 'log_notif_sms_config';
    protected $primaryKey = 'nsc_id';

    protected $returnType     = EntLogNotifSmsConfig::class;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'nsc_excluido';

    protected $allowedFields = [
        'nsc_nome',
        'nsc_tipo_regra',
        'nsc_saldo_minimo',
        'nsc_metodo_api',
        'nsc_view_consulta',
        'nsc_view_dbgroup',
        'nsc_telefones',
        'nsc_mensagem_template',
        'nsc_ativo',
    ];

    protected $validationRules = [
        'nsc_nome'              => 'required|min_length[3]|max_length[100]',
        'nsc_tipo_regra'        => 'required|in_list[saldo_baixo,api,consulta]',
        'nsc_telefones'         => 'required|max_length[255]',
        'nsc_mensagem_template' => 'required',
        // nsc_saldo_minimo / obrigatorioSeTipoRegraSaldo — REGRA NÃO
        // ALTERADA neste ciclo, permanece exatamente igual a antes.
        'nsc_saldo_minimo'      => 'permit_empty|obrigatorioSeTipoRegraSaldo[nsc_tipo_regra]',
        // Entradas NOVAS deste ciclo (tipos 'api'/'consulta').
        'nsc_metodo_api'        => 'permit_empty|obrigatorioSeTipoRegraApi[nsc_tipo_regra]',
        'nsc_view_consulta'     => 'permit_empty|obrigatorioSeTipoRegraConsulta[nsc_tipo_regra]',
    ];

    protected $validationMessages = [
        'nsc_nome' => [
            'required'   => 'O campo Nome da Regra é obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 3 caracteres',
        ],
        'nsc_telefones' => [
            'required' => 'O campo Telefones é obrigatório',
        ],
        'nsc_mensagem_template' => [
            'required' => 'O campo Mensagem é obrigatório',
        ],
        // Mensagem de nsc_saldo_minimo — NÃO ALTERADA neste ciclo.
        'nsc_saldo_minimo' => [
            'obrigatorioSeTipoRegraSaldo' => 'O campo Saldo Mínimo é obrigatório para regras do tipo Saldo Baixo',
        ],
        'nsc_metodo_api' => [
            'obrigatorioSeTipoRegraApi' => 'O campo Método da API é obrigatório para regras do tipo API',
        ],
        'nsc_view_consulta' => [
            'obrigatorioSeTipoRegraConsulta' => 'O campo View de Consulta é obrigatório para regras do tipo Consulta',
        ],
    ];

    // Callbacks de auditoria (LogMonModel)
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

    public function getListaRegras()
    {
        // findAll() respeita $useSoftDeletes/$deletedField automaticamente
        // (o uso anterior via builder()->get() ignorava o soft delete).
        return $this->orderBy('nsc_ativo, nsc_nome')->findAll();
    }

    public function getRegra($id)
    {
        return $this
            ->when($id !== null, fn($q) => $q->where('nsc_id', $id))
            ->first();
    }

    public function getRegrasAtivas()
    {
        return $this->where('nsc_ativo', 'A')->findAll();
    }
}
