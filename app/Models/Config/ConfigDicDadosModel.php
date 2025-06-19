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

    protected $allowedFields    = ['table_name',
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
    public function getTabelas($nome_tabela = false, $grupo = 'default')
    {
        $this->DBGroup          = 'dbEstoque';

        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'estoque_db');
        $this->builder()->orderBy('table_name', 'ASC');

        $ret = $this->builder()->get()->getResultArray();
        $this->DBGroup          = 'dbProduto';

        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'produto_db');
        $this->builder()->orderBy('table_name', 'ASC');

        $ret2 = $this->builder()->get()->getResultArray();
        foreach ($ret2 as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'dbOcorrencia';

        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'ocorrencia_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret3 = $this->builder()->get()->getResultArray();
        foreach ($ret3 as $reg) {
            array_push($ret, $reg);
        }

        $this->DBGroup          = 'default';
        $this->builder()
                ->select(['table_schema','table_name','table_rows','table_comment']);

        if ($nome_tabela) {
            $this->builder()->where('table_name', $nome_tabela);
        }
        $this->builder()->where('table_schema', 'config_ceqweb_db');
        $this->builder()->orderBy('table_name', 'ASC');
        $ret4 = $this->builder()->get()->getResultArray();
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
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'estoque_db';
        } else if(substr($nome_tabela,0,3) == 'oco' || 
            substr($nome_tabela,0,6) == 'vw_oco') {
            $this->DBGroup          = 'dbOcorrencia';
            $schema                 = 'ocorrencia_db';
        } else if(substr($nome_tabela,0,3) == 'pro' || 
            substr($nome_tabela,0,6) == 'vw_pro') {
            $this->DBGroup          = 'dbProduto';
            $schema                 = 'produto_db';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'default';
            $schema                 = 'config_ceqweb_db';
        }
        $array = ['table_name' => $nome_tabela . '%'];
        $this->builder()
                ->select(['table_name','table_rows','table_comment'])
                ->like($array);

        $this->builder()->where('table_schema', $schema);
        $this->builder()->orderBy('table_name', 'ASC');

        $ret = $this->builder()->get()->getResultArray();
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
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'estoque_db';
        } else if(substr($nome_tabela,0,3) == 'oco' || 
            substr($nome_tabela,0,6) == 'vw_oco') {
            $this->DBGroup          = 'dbOcorrencia';
            $schema                 = 'ocorrencia_db';
        } else if(substr($nome_tabela,0,3) == 'pro' || 
            substr($nome_tabela,0,6) == 'vw_pro') {
            $this->DBGroup          = 'dbProduto';
            $schema                 = 'produto_db';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'default';
            $schema                 = 'config_ceqweb_db';
        }
        $array = ['kc.table_name' => $nome_tabela];
        // $db = db_connect();
        $builder = $this->builder('information_schema.KEY_COLUMN_USAGE kc');
        $builder->select('CONSTRAINT_NAME, 
                            kc.TABLE_NAME, 
                            kc.COLUMN_NAME, 
                            kc.REFERENCED_TABLE_NAME, 
                            kc.REFERENCED_COLUMN_NAME,
                            tb.TABLE_COMMENT');
        $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME', 'inner');
        $builder->where($array);
        $builder->where('kc.table_schema', $schema);
        $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');

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
        // debug('Tabela Chegou '.$nome_tabela);
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'estoque_db';
        } else if(substr($nome_tabela,0,3) == 'oco' || 
            substr($nome_tabela,0,6) == 'vw_oco') {
            $this->DBGroup          = 'dbOcorrencia';
            $schema                 = 'ocorrencia_db';
        } else if(substr($nome_tabela,0,3) == 'pro' || 
            substr($nome_tabela,0,6) == 'vw_pro') {
            $this->DBGroup          = 'dbProduto';
            $schema                 = 'produto_db';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'default';
            $schema                 = 'config_ceqweb_db';
        }
        // debug($schema);
        $db = db_connect($this->DBGroup);
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
                            WHERE TABLE_NAME = '".$nome_tabela."'
                            AND TABLE_SCHEMA = '".$schema."'");
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
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'estoque_db';
        } else if(substr($nome_tabela,0,3) == 'oco' || 
            substr($nome_tabela,0,6) == 'vw_oco') {
            $this->DBGroup          = 'dbOcorrencia';
            $schema                 = 'ocorrencia_db';
        } else if(substr($nome_tabela,0,3) == 'pro' || 
            substr($nome_tabela,0,6) == 'vw_pro') {
            $this->DBGroup          = 'dbProduto';
            $schema                 = 'produto_db';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'default';
            $schema                 = 'config_ceqweb_db';
        }
        $db = db_connect($this->DBGroup);
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
                            WHERE TABLE_NAME = '".$nome_tabela."'
                            AND TABLE_SCHEMA = '".$schema."' ";
        if (gettype($nome_campo) == 'array') {
            $str_nome_campo = '';
            for($n=0;$n<count($nome_campo);$n++){
                $str_nome_campo .= "'".$nome_campo[$n]."',";
            }
            $str_nome_campo = rtrim($str_nome_campo, ",");
            $consulta .= "AND column_name IN ($str_nome_campo) ";
        } else {
            $consulta .= "AND column_name = '".$nome_campo."' ";
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
        if(substr($nome_tabela,0,3) == 'est' || 
            substr($nome_tabela,0,6) == 'vw_est') {
            $this->DBGroup          = 'dbEstoque';
            $schema                 = 'estoque_db';
        } else if(substr($nome_tabela,0,3) == 'oco' || 
            substr($nome_tabela,0,6) == 'vw_oco') {
            $this->DBGroup          = 'dbOcorrencia';
            $schema                 = 'ocorrencia_db';
        } else if(substr($nome_tabela,0,3) == 'pro' || 
            substr($nome_tabela,0,6) == 'vw_pro') {
            $this->DBGroup          = 'dbProduto';
            $schema                 = 'produto_db';
        } else if(substr($nome_tabela,0,3) == 'cfg' || 
            substr($nome_tabela,0,6) == 'vw_cfg') {
            $this->DBGroup          = 'default';
            $schema                 = 'config_ceqweb_db';
        }
        $array = ['table_name' => $nome_tabela];
        // $db = db_connect();
        $builder = $this->builder('information_schema.columns');
        $builder->select('TABLE_NAME, COLUMN_NAME, 
                                IS_NULLABLE, 
                                DATA_TYPE, 
                                COALESCE(`CHARACTER_MAXIMUM_LENGTH`, NUMERIC_PRECISION) AS COLUMN_SIZE, 
                                COLUMN_COMMENT, 
                                COLUMN_KEY');
        $builder->where($array);
        $builder->where('COLUMN_KEY', 'PRI');
        $builder->where('table_schema', $schema);

        $ret = $builder->get()->getResultArray();

        return $ret;
    }
}
