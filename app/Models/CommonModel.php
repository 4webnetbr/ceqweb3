<?php

namespace App\Models;

use App\Models\LogMonModel;
use CodeIgniter\Model;

class CommonModel extends Model
{
    protected $table            = 'cfg_log';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    protected $allowedFields    = [
        'log_id',
        'log_tabela',
        'log_operacao',
        'log_id_registro',
        'log_id_usuario',
        'log_data'
    ];

    /**
     * insertReg
     *
     * Insere o Registro na Tabela informada
     *  
     * @param string $table 
     * @param mixed $data 
     * @return int
     */
    public function insertReg($grupo, $table, $data)
    {
        $db = db_connect($grupo);
        $builder = $db->table($table);
        $insert_id = $builder->insert($data);
        $key = null;

        foreach ($data as $k => $v) {
            if (str_ends_with($k, '_id')) {
                $key = $k;
                break;
            }
        }

        (new LogMonModel())->insertLog($table, 'Incluído', (int) $data[$key], $data);

        return $insert_id;
    }

    public function insertRegBatch($grupo, $table, $data)
    {
        $db = db_connect($grupo);
        $builder = $db->table($table);
        $insert_id = $builder->insertBatch($data);

        return $insert_id;
    }
    /**
     * updateReg
     *
     * Insere o Registro na Tabela informada
     *  
     * @param string $table 
     * @param mixed $data 
     * @return int
     */
    public function updateReg($grupo, $table, $chave, $data)
    {
        $db = db_connect($grupo);
        $builder = $db->table($table);
        $builder->where($chave);
        $update_id = $builder->update($data);
        // $key = array_find_key($data, fn($k) => str_ends_with($k, '_id'));
        // debug($data);
        $key = null;

        foreach ($data as $k => $v) {
            if (str_ends_with($k, '_id')) {
                $key = $k;
                break;
            }
        }

        // debug($key, true);
        (new LogMonModel())->insertLog($table, 'Alteração', (int) $data[$key], $data);

        return $update_id;
    }

    /**
     * deleteReg
     *
     * deleta o Registro na Tabela informada
     *  
     * @param string $table 
     * @param mixed $data 
     * @return bool
     */
    public function deleteReg($grupo, $tabela, $chave)
    {
        $db = db_connect($grupo);

        $query = $db->query("DELETE FROM " . $tabela . " WHERE " . $chave);

        return true;
    }

    /**
     * getFieldsTable
     *
     * Retorna os Campos da Tabela informada
     *  
     * @param mixed $table 
     * @return array
     */
    public function getFieldsTable($table)
    {
        $fields = $this->db->getFieldData($table);

        return $fields;
    }

    /**
     * insertLog
     *
     * Insere o Registro na Tabela de Log
     *  
     * @param string $tabela
     * @param string $operacao
     * @param int    $registro
     * @return int
     */
    public function insertLog($tabela, $operacao, $registro)
    {
        $sql_data = [
            'log_tabela'        => $tabela,
            'log_operacao'      => $operacao,
            'log_id_registro'   => $registro,
            'log_id_usuario'    => session()->get('usu_id'),
            'log_data'          => date('Y-m-d H:i:s')
        ];
        $ins_id = $this->builder()->insert($sql_data);

        return $ins_id;
    }

    public function getRegFiltro($banco, $table, $fields, $filtros, $limit = false, $start = 0, $col = '', $dir = 'ASC')
    {
        $db = db_connect($banco);
        $builder = $db->table($table);
        $builder->select($fields);
        if (!empty($filtros)) {
            foreach ($filtros as $campo => $valcampo) {
                // aplica filtro somente se o campo existir na tabela
                if ($db->fieldExists($campo, "$table")) {
                    if (is_array($valcampo)) {
                        $builder->like($campo, $valcampo[0]);
                    } else {
                        $builder->where($campo, $valcampo);
                    }
                }
            }
        }
        if ($limit) {
            $builder->limit($limit, $start);
        }
        if ($col == '') {
            $col = $fields[0];
        }
        if ($col != '*') {
            $builder->orderBy($col, $dir);
        }

        // debug($builder->getCompiledSelect(), true);
        $ret = $builder->get()->getResultArray();
        // $sql = $db->getLastQuery();
        // debug($sql, false);
        return $ret;
    }

    public function getPostsTotal($banco, $table, $fields)
    {
        $db = db_connect($banco);
        $builder = $db->table($table);
        $ret = $builder->countAll();

        // $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // debug($sql, true);

        return $ret;
    }

    public function getListaOpcoes($banco, $table, $fields, $chave = false)
    {
        $campos = implode(', ', $fields);
        $db = db_connect($banco);
        $builder = $db->table($table);
        $builder->select($campos);
        if ($chave) {
            if (substr(trim($chave), -1) != "=") { // PREVINE CHAVE COM VALOR NULO
                $builder->where($chave);
            }
        }
        // $sql = $builder->getCompiledSelect();
        // debug($sql, false);
        $ret = $builder->get()->getResultArray();
        // debug($ret);
        $opcoes       = array_column($ret, $fields[0], $fields[1]);
        // debug($opcoes, true);
        // $sql = $this->db->getLastQuery();
        return $opcoes;
    }

    public function getListaTabela($banco, $table, $fields, $chave = false, $limite = 50)
    {
        $campos = implode(', ', $fields);
        $db = db_connect($banco);
        $ret = [];
        $builder = $db->table($table);
        $builder->distinct()->select($campos);
        if ($chave) {
            if (substr(trim($chave), -1) != "=") { // PREVINE CHAVE COM VALOR NULO
                $builder->where($chave);
            }
        }
        $builder->limit($limite);
        // $sql = $builder->getCompiledSelect();
        // debug($sql, true);
        $ret = $builder->get()->getResultArray();
        return $ret;
    }

    public function verificaUnico($model, $campo, $valor, $chave, $id, $chave2 = false, $id2 = false)
    {
        $builder = $model->where($campo, $valor)
            ->where("$chave !=", $id);
        if ($chave2) {
            $builder->where("$chave2", $id2);
        }

        $tem = $builder->countAllResults();

        // Pegando o SQL gerado
        // debug($builder->getLastQuery(), true);
        return $tem;
    }
}
