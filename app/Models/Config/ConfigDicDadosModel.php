<?php

/**
 * Summary of namespace App\Models\Config
 * Classe para tratamento das Tabelas e Campos do Sistema
 * Trabalha com várias bases de dados ao mesmo tempo
 * 
 * Criada por: Douglas Junior Ferreira
 * Dezembro/2023
 */

namespace App\Models\Config;

use CodeIgniter\Model;

class ConfigDicDadosModel extends Model
{
    protected $DBGroup          = 'default';

    protected $table            = 'information_schema.tables';
    protected $primaryKey       = 'table_name';
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'table_name',
        'table_rows',
        'table_comment',
    ];

    /**
     * Summary of getTabelas
     * Retorna as Tabelas do Sistema com seus Detalhes
     * @param mixed $nome_tabela
     * @param mixed $grupo
     * @return array
     */
    public function getTabelas($nome_tabela = false)
    {
        $this->DBGroup          = 'dbEstoque';
        $db      = db_connect($this->DBGroup);
        $pre = '';
        $url = base_url();
        if (!str_contains($url, 'dev.')) {
            $pre = 'prd_';
        }
        $builder = $db->table('information_schema.tables');
        $builder
            ->select(['table_schema', 'table_name', 'table_rows', 'table_comment']);

        if ($nome_tabela) {
            $builder->where('table_name', $nome_tabela);
        }
        $builder->where('table_schema', $pre . 'estoque_db');
        $builder->orderBy('table_name', 'ASC');

        $ret = $builder->get()->getResultArray();
        // debug($db->getLastQuery());

        $this->DBGroup          = 'dbProduto';
        $db      = db_connect($this->DBGroup);
        $builder = $db->table('information_schema.tables');
        $builder
            ->select(['table_schema', 'table_name', 'table_rows', 'table_comment']);

        if ($nome_tabela) {
            $builder->where('table_name', $nome_tabela);
        }
        $builder->where('table_schema', $pre . 'produto_db');
        $builder->orderBy('table_name', 'ASC');

        $ret2 = $builder->get()->getResultArray();
        foreach ($ret2 as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'dbOcorrencia';
        $db      = db_connect($this->DBGroup);
        $builder = $db->table('information_schema.tables');

        $builder
            ->select(['table_schema', 'table_name', 'table_rows', 'table_comment']);

        if ($nome_tabela) {
            $builder->where('table_name', $nome_tabela);
        }
        $builder->where('table_schema', $pre . 'ocorrencia_db');
        $builder->orderBy('table_name', 'ASC');
        $ret3 = $builder->get()->getResultArray();
        foreach ($ret3 as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'default';
        $db      = db_connect($this->DBGroup);
        $builder = $db->table('information_schema.tables');

        $builder
            ->select(['table_schema', 'table_name', 'table_rows', 'table_comment']);

        if ($nome_tabela) {
            $builder->where('table_name', $nome_tabela);
        }
        $builder->where('table_schema', $pre . 'config_ceqweb_db');
        $builder->orderBy('table_name', 'ASC');
        $ret4 = $builder->get()->getResultArray();
        foreach ($ret4 as $reg) {
            array_push($ret, $reg);
        }
        return $ret;
    }

    /**
     * Summary of getTabelaSearch
     * Retorna os detalhes da Tabela informada
     * @param mixed $nome_tabela
     * @return array
     */
    public function getTabelaSearch($nome_tabela)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $db      = db_connect($dbGrSche['dbGroup']);
        $builder = $db->table($this->table);
        $array = ['table_name' => $nome_tabela . '%'];
        $builder
            ->select(['table_name', 'table_rows', 'table_comment'])
            ->like($array);

        $builder->where('table_schema', $dbGrSche['schema']);
        $builder->orderBy('table_name', 'ASC');

        $ret = $builder->get()->getResultArray();
        return $ret;
    }

    /**
     * Summary of getRelacionamentos
     * Retorna os Relacionamentos da Tabela informada
     * @param mixed $nome_tabela
     * @return array
     */
    public function getRelacionamentos($nome_tabela)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $array = ['kc.table_name' => $nome_tabela];
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $db      = db_connect($dbGrSche['dbGroup']);
        $builder = $db->table('information_schema.KEY_COLUMN_USAGE kc');

        // $builder = $this->builder('information_schema.KEY_COLUMN_USAGE kc');
        $builder->select('CONSTRAINT_NAME, 
                            kc.TABLE_NAME, 
                            kc.COLUMN_NAME, 
                            kc.REFERENCED_TABLE_NAME, 
                            kc.REFERENCED_COLUMN_NAME,
                            tb.TABLE_COMMENT');
        $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME', 'inner');
        $builder->where($array);
        $builder->where('kc.table_schema', $dbGrSche['schema']);
        $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');

        // debug($builder->getCompiledSelect(), true);
        $ret = $builder->get()->getResultArray();
        return $ret;
    }

    /**
     * Summary of getCampos
     * Retorna os Campos  da Tabela informada
     * @param mixed $nome_tabela
     * @return array
     */
    public function getCampos($nome_tabela)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $db = db_connect($dbGrSche['dbGroup']);
        $query = $db->query("SELECT TABLE_NAME, 
                            COLUMN_NAME, 
                            IS_NULLABLE, 
                            DATA_TYPE, 
                            COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                            NUMERIC_SCALE, 
                            COLUMN_COMMENT, 
                            COLUMN_KEY,
                            CONCAT(COLUMN_COMMENT,' - ',COLUMN_NAME) AS NOME_COMPLETO
                            FROM information_schema.columns
                            WHERE TABLE_NAME = '" . $nome_tabela . "'
                            AND TABLE_SCHEMA = '" . $dbGrSche['schema'] . "'");
        $ret = $query->getResultArray();
        $lq = $query = $db->getLastQuery();
        // debug($lq);
        // debug($ret);
        return $ret;
    }

    /**
     * Summary of getDetalhesCampo
     * Retorna os detalhes do Campo Informado,  da Tabela informada
     * @param mixed $nome_tabela
     * @param mixed $nome_campo
     * @return array
     */
    public function getDetalhesCampo($nome_tabela, $nome_campo)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $db = db_connect($dbGrSche['dbGroup']);
        $consulta = "SELECT TABLE_NAME, 
                            COLUMN_NAME, 
                            IS_NULLABLE, 
                            DATA_TYPE, 
                            COALESCE(`CHARACTER_MAXIMUM_LENGTH`,NUMERIC_PRECISION) AS COLUMN_SIZE, 
                            NUMERIC_SCALE, 
                            COLUMN_COMMENT, 
                            COLUMN_KEY,
                            CONCAT(COLUMN_COMMENT,' - ',COLUMN_NAME) AS NOME_COMPLETO
                            FROM information_schema.columns
                            WHERE TABLE_NAME = '" . $nome_tabela . "'
                            AND TABLE_SCHEMA = '" . $dbGrSche['schema'] . "' ";
        if (gettype($nome_campo) == 'array') {
            $str_nome_campo = '';
            for ($n = 0; $n < count($nome_campo); $n++) {
                $str_nome_campo .= "'" . $nome_campo[$n] . "',";
            }
            $str_nome_campo = rtrim($str_nome_campo, ",");
            $consulta .= "AND column_name IN ($str_nome_campo) ";
        } else {
            $consulta .= "AND column_name = '" . $nome_campo . "' ";
        }
        // debug($consulta, true);

        $query = $db->query($consulta);
        $ret = $query->getResultArray();
        $lq = $query = $db->getLastQuery();
        // debug($lq);
        // debug($ret);
        return $ret;
    }

    /**
     * Summary of getCampoChave
     * Retorna os Campos Chaves da Tabela informada
     * @param mixed $nome_tabela
     * @return array
     */
    public function getCampoChave($nome_tabela)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $array = ['table_name' => $nome_tabela];
        // $db = db_connect();
        $db      = db_connect($this->DBGroup);
        $builder = $db;
        // $builder = $this->builder('information_schema.columns');
        $builder->select('TABLE_NAME, COLUMN_NAME, 
                                IS_NULLABLE, 
                                DATA_TYPE, 
                                COALESCE(`CHARACTER_MAXIMUM_LENGTH`, NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                COLUMN_COMMENT, 
                                COLUMN_KEY');
        $builder->where($array);
        $builder->where('COLUMN_KEY', 'PRI');
        $builder->where('table_schema', $dbGrSche['schema']);

        $ret = $builder->get()->getResultArray();

        return $ret;
    }

    function getDbGroupAndSchema(?string $nome_tabela): array
    {
        $url = base_url();
        if (str_contains($url, 'dev.')) {
            $prefixMap = [
                'vw_est' => ['dbGroup' => 'dbEstoque',    'schema' => 'dev_estoque_db'],
                'est'    => ['dbGroup' => 'dbEstoque',    'schema' => 'dev_estoque_db'],
                'vw_oco' => ['dbGroup' => 'dbOcorrencia', 'schema' => 'dev_ocorrencia_db'],
                'oco'    => ['dbGroup' => 'dbOcorrencia', 'schema' => 'dev_ocorrencia_db'],
                'vw_pro' => ['dbGroup' => 'dbProduto',    'schema' => 'dev_produto_db'],
                'pro'    => ['dbGroup' => 'dbProduto',    'schema' => 'dev_produto_db'],
                'vw_cfg' => ['dbGroup' => 'default',      'schema' => 'dev_config_ceqweb_db'],
                'cfg'    => ['dbGroup' => 'default',      'schema' => 'dev_config_ceqweb_db'],
            ];
        } else {
            $prefixMap = [
                'vw_est' => ['dbGroup' => 'dbEstoque',    'schema' => 'prd_estoque_db'],
                'est'    => ['dbGroup' => 'dbEstoque',    'schema' => 'prd_estoque_db'],
                'vw_oco' => ['dbGroup' => 'dbOcorrencia', 'schema' => 'prd_ocorrencia_db'],
                'oco'    => ['dbGroup' => 'dbOcorrencia', 'schema' => 'prd_ocorrencia_db'],
                'vw_pro' => ['dbGroup' => 'dbProduto',    'schema' => 'prd_produto_db'],
                'pro'    => ['dbGroup' => 'dbProduto',    'schema' => 'prd_produto_db'],
                'vw_cfg' => ['dbGroup' => 'default',      'schema' => 'prd_config_ceqweb_db'],
                'cfg'    => ['dbGroup' => 'default',      'schema' => 'prd_config_ceqweb_db'],
            ];
        }

        // Ordem de verificação: prefixos maiores primeiro para evitar conflito (ex: vw_est vs est)
        $prefixes = ['vw_est', 'vw_oco', 'vw_pro', 'vw_cfg', 'est', 'oco', 'pro', 'cfg'];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($nome_tabela, $prefix)) {
                return $prefixMap[$prefix];
            }
        }

        // Retorna nulo caso nenhum prefixo conhecido seja encontrado
        return ['dbGroup' => null, 'schema' => null];
    }
}
