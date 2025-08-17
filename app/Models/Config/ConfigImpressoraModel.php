<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigImpressoraModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfg_impressora';
    protected $view             = 'cfg_impressora';
    protected $primaryKey       = 'imp_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'imp_id',
        'imp_nome',
        'imp_ip',
        'imp_porta',
        'imp_excluido'
    ];

    protected $deletedField  = 'imp_excluido';

    protected $validationRules = [
        'imp_nome' => 'required|min_length[5]',
        'imp_ip'  => 'required',
        'imp_porta'   => 'required',
    ];

    protected $validationMessages = [
        'imp_nome' => [
            'required' => 'O campo Nome é Obrigatório',
            'min_length' => 'O campo Nome exige pelo menos 5 Caracteres.',
            'isUniqueValue' => '8',
        ],
        'imp_ip' => [
            'required' => 'O campo IP é Obrigatório',
        ],
        'imp_porta' => [
            'required' => 'O campo Porta é Obrigatório',
        ],
    ];


    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete  = ['depoisDelete'];

    protected $logdb;

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getImpressora($imp_id = false)
    {
        $this->builder()->select('*');
        if ($imp_id) {
            $this->builder()->where('imp_id', $imp_id);
        }
        $this->builder()->where('imp_excluido', null);
        $this->builder()->orderBy('imp_nome');
        return $this->builder()->get()->getResultArray();
    }

    public function getImpressoraId($imp_id = false)
    {
        $this->builder()->select('*');
        if ($imp_id) {
            $this->builder()->where('imp_id', $imp_id);
        }
        $this->builder()->where('imp_excluido', null);
        $this->builder()->orderBy('imp_id');
        return $this->builder()->get()->getResultArray();
    }

    public function getImpressoraSearch($termo)
    {
        $array = ['imp_nome' => $termo . '%'];
        $this->builder()->select(['imp_id', 'imp_nome']);
        $this->builder()->where('imp_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_impressora', 'imp_id');
        $mid->valor     = (isset($dados['imp_id'])) ? $dados['imp_id'] : '';
        $ret['imp_id']   = $mid->crOculto();

        $titu           =  new MyCampo('cfg_impressora', 'imp_nome');
        $titu->valor    = (isset($dados['imp_nome'])) ? $dados['imp_nome'] : '';
        $titu->obrigatorio = true;
        $ret['imp_nome'] = $titu->crInput();


        $ip           =  new MyCampo('cfg_impressora', 'imp_ip');
        $ip->tipo     =  'ip';
        $ip->valor    = (isset($dados['imp_ip'])) ? $dados['imp_ip'] : '';
        $ip->largura  =  25;
        $ip->maxLength  =  15;
        $ip->selecionado = $ip->valor;
        $ip->obrigatorio = true;
        $ret['imp_ip'] = $ip->crInput();

        $porta           =  new MyCampo('cfg_impressora', 'imp_porta');
        $porta->valor    = (isset($dados['imp_porta'])) ? $dados['imp_porta'] : '';
        $porta->selecionado    = $porta->valor;
        $porta->largura  =  20;
        $porta->obrigatorio = true;
        $porta->maximo = 10000;
        $ret['imp_porta'] = $porta->crInput();

        return $ret;
    }
}
