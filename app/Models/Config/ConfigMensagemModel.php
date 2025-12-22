<?php

namespace App\Models\Config;

use App\Models\LogMonModel;
use CodeIgniter\Model;
use App\Entities\Config\EntCfgMensagem;

class ConfigMensagemModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_mensagem';
    protected $primaryKey       = 'msg_id';
    protected $useAutoIncrement = true;

    protected $returnType       = EntCfgMensagem::class;
    protected $deletedField     = 'msg_excluido';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'msg_titulo',
        'msg_tipo',
        'msg_cor',
        'msg_mensagem',
        'msg_desc_tipo',
        'msg_ativo',
    ];


    protected $validationRules = [
        'msg_titulo'   => 'required|min_length[5]',
        'msg_tipo'     => 'required',
        'msg_cor'      => 'required',
        'msg_mensagem' => 'required|min_length[5]'
    ];

    protected $validationMessages = [
        'msg_titulo' => [
            'required' => 'O campo Título é Obrigatório',
            'min_length' => 'O campo Título exige pelo menos 5 Caracteres.',
            'isUniqueValue' => '8',
        ],
        'msg_tipo' => [
            'required' => 'O campo Tipo é Obrigatório',
        ],
        'msg_cor' => [
            'required' => 'O campo Cor é Obrigatório',
        ],
        'msg_mensagem' => [
            'required' => 'O campo Mensagem é Obrigatório',
            'min_length' => 'O campo Mensagem exige pelo menos 5 Caracteres.',
        ],
    ];


    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Incluído', $data['id'], $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Alteração', $data['id'][0], $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        (new LogMonModel())->insertLog($this->table, 'Excluído', $data['id'][0], $data['data']);
        return $data;
    }


    public function getMensagem(int $msg_id = null)
    {
        $builder = $this->builder();
        $builder->where('msg_excluido', null);

        if ($msg_id) {
            $builder->where('msg_id', $msg_id);
            return $builder->get()->getFirstRow();
        }

        $builder->orderBy('msg_ativo, msg_titulo');
        return $builder->get()->getResult();
    }
    

    public function getMensagemId(int $msg_id = null)
    {
        $builder = $this->builder();
        $builder->where('msg_excluido', null);
    
        if ($msg_id) {
            $builder->where('msg_id', $msg_id);
            return $builder->get()->getFirstRow(); 
        }
    
        $builder->orderBy('msg_id');
        return $builder->get()->getResult(); 
    }

    public function getMensagemSearch(string $termo)
    {
        $builder = $this->builder();
        $builder->select(['msg_id', 'msg_titulo']);
        $builder->where('msg_excluido', null);
        $builder->like('msg_titulo', $termo);
    
        return $builder->get()->getResult(); 
    }
}
