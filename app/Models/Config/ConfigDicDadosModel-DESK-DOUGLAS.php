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
        $pre = 'dev_';
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
    // public function getRelacionamentos($nome_tabela)
    // {
    //     $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
    //     $array = ['kc.table_name' => $nome_tabela];
    //     $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
    //     $db      = db_connect($dbGrSche['dbGroup']);
    //     $builder = $db->table('information_schema.KEY_COLUMN_USAGE kc');

    //     // $builder = $this->builder('information_schema.KEY_COLUMN_USAGE kc');
    //     $builder->select('CONSTRAINT_NAME, 
    //                         kc.TABLE_NAME, 
    //                         kc.COLUMN_NAME, 
    //                         kc.REFERENCED_TABLE_NAME, 
    //                         kc.REFERENCED_COLUMN_NAME,
    //                         tb.TABLE_COMMENT');
    //     $builder->join('information_schema.TABLES tb', 'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME', 'inner');
    //     $builder->where($array);
    //     $builder->where('kc.table_schema', $dbGrSche['schema']);
    //     $builder->where('REFERENCED_TABLE_SCHEMA IS NOT NULL');

    //     // debug($builder->getCompiledSelect(), true);
    //     $ret = $builder->get()->getResultArray();
    //     return $ret;
    // }

    public function getRelacionamentos($nome_tabela, $completo = 0)
    {
        $dbGrSche = $this->getDbGroupAndSchema($nome_tabela);
        $db = db_connect($dbGrSche['dbGroup']);

        // Obtém os relacionamentos diretos da tabela base (com constraint)
        $relacionamentos_base = $this->_obterRelacionamentosTabela(
            $db,
            $nome_tabela,
            $dbGrSche['schema']
        );

        // Obtém potenciais relacionamentos sem constraint (por padrão de nomenclatura)
        $relacionamentos_potenciais = $this->_obterRelacionamentosPotenciais(
            $db,
            $nome_tabela,
            $dbGrSche['schema'],
            $relacionamentos_base
        );

        // Mescla os dois tipos de relacionamentos
        $relacionamentos_completos = array_merge($relacionamentos_base, $relacionamentos_potenciais);

        // Se completo = 0, retorna apenas os relacionamentos da tabela base
        if ($completo == 0) {
            return $relacionamentos_completos;
        }

        // Se completo = 1, expande para incluir relacionamentos das tabelas relacionadas
        $resultado = [
            'tabela_base' => $nome_tabela,
            'relacionamentos_diretos' => $relacionamentos_completos,
            'relacionamentos_expandidos' => []
        ];

        // Para cada tabela referenciada, obter seus relacionamentos
        $tabelas_processadas = [$nome_tabela];

        foreach ($relacionamentos_completos as $rel) {
            $tabela_ref = $rel['REFERENCED_TABLE_NAME'];

            if (!in_array($tabela_ref, $tabelas_processadas)) {
                $tabelas_processadas[] = $tabela_ref;

                $rels_base = $this->_obterRelacionamentosTabela(
                    $db,
                    $tabela_ref,
                    $dbGrSche['schema']
                );

                $rels_potenciais = $this->_obterRelacionamentosPotenciais(
                    $db,
                    $tabela_ref,
                    $dbGrSche['schema'],
                    $rels_base
                );

                $rels_expandidos = array_merge($rels_base, $rels_potenciais);

                if (!empty($rels_expandidos)) {
                    $resultado['relacionamentos_expandidos'][] = [
                        'tabela' => $tabela_ref,
                        'relacionamentos' => $rels_expandidos
                    ];
                }
            }
        }

        return $resultado;
    }

    /**
     * Obtém relacionamentos com constraint definido no banco
     */
    private function _obterRelacionamentosTabela($db, $nome_tabela, $schema)
    {
        $builder = $db->table('information_schema.KEY_COLUMN_USAGE kc');

        $builder->select(
            'CONSTRAINT_NAME, 
        kc.TABLE_NAME, 
        kc.COLUMN_NAME, 
        kc.REFERENCED_TABLE_NAME, 
        kc.REFERENCED_COLUMN_NAME,
        tb.TABLE_COMMENT,
        "constraint" as tipo_relacao'
        );

        $builder->join(
            'information_schema.TABLES tb',
            'tb.TABLE_NAME = kc.REFERENCED_TABLE_NAME AND tb.TABLE_SCHEMA = kc.REFERENCED_TABLE_SCHEMA',
            'inner'
        );

        $builder->where('kc.TABLE_NAME', $nome_tabela);
        $builder->where('kc.TABLE_SCHEMA', $schema);
        $builder->where('kc.REFERENCED_TABLE_SCHEMA IS NOT NULL', null, false);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtém potenciais relacionamentos por padrão de nomenclatura
     * (campos que terminam com _id e tabelas correspondentes existem)
     */
    private function _obterRelacionamentosPotenciais($db, $nome_tabela, $schema, $relacionamentos_com_constraint)
    {
        // Campos já mapeados por constraint
        $campos_com_constraint = [];
        foreach ($relacionamentos_com_constraint as $rel) {
            $campos_com_constraint[] = $rel['COLUMN_NAME'];
        }

        // Obter informações das colunas da tabela
        $builder = $db->table('information_schema.COLUMNS');
        $builder->select('COLUMN_NAME, COLUMN_TYPE');
        $builder->where('TABLE_NAME', $nome_tabela);
        $builder->where('TABLE_SCHEMA', $schema);
        $colunas = $builder->get()->getResultArray();

        $relacionamentos_potenciais = [];

        foreach ($colunas as $coluna) {
            $nome_coluna = $coluna['COLUMN_NAME'];

            // Verifica se é um campo *_id e não tem constraint
            if (
                preg_match('/^(.+)_id$/i', $nome_coluna, $matches) &&
                !in_array($nome_coluna, $campos_com_constraint)
            ) {

                $prefixo = $matches[1];

                // Procura por tabelas que começam com o prefixo
                $tabelas_candidatas = $this->_procurarTabelasCorrespondentes(
                    $db,
                    $prefixo,
                    $schema
                );

                foreach ($tabelas_candidatas as $tabela_cand) {
                    $nome_tabela_ref = $tabela_cand['TABLE_NAME'];
                    $comentario_tabela = $tabela_cand['TABLE_COMMENT'];

                    // Verifica se a tabela tem coluna de ID
                    $builder = $db->table('information_schema.COLUMNS');
                    $builder->where('TABLE_NAME', $nome_tabela_ref);
                    $builder->where('TABLE_SCHEMA', $schema);
                    $builder->where('COLUMN_NAME', 'id');
                    $existe_id = $builder->get()->getNumRows() > 0;

                    if (!$existe_id) {
                        // Tenta com o mesmo padrão: prefixo_id
                        $builder = $db->table('information_schema.COLUMNS');
                        $builder->where('TABLE_NAME', $nome_tabela_ref);
                        $builder->where('TABLE_SCHEMA', $schema);
                        $builder->where('COLUMN_NAME', $prefixo . '_id');
                        $existe_id = $builder->get()->getNumRows() > 0;
                        $coluna_ref = $existe_id ? $prefixo . '_id' : 'id';
                    } else {
                        $coluna_ref = 'id';
                    }

                    if ($existe_id) {
                        $relacionamentos_potenciais[] = [
                            'CONSTRAINT_NAME' => null,
                            'TABLE_NAME' => $nome_tabela,
                            'COLUMN_NAME' => $nome_coluna,
                            'REFERENCED_TABLE_NAME' => $nome_tabela_ref,
                            'REFERENCED_COLUMN_NAME' => $coluna_ref,
                            'TABLE_COMMENT' => $comentario_tabela,
                            'tipo_relacao' => 'potencial' // Indica que é sem constraint
                        ];
                    }
                }
            }
        }

        return $relacionamentos_potenciais;
    }

    /**
     * Procura por tabelas que correspondem ao padrão de nomenclatura
     */
    private function _procurarTabelasCorrespondentes($db, $prefixo, $schema)
    {
        $builder = $db->table('information_schema.TABLES');
        $builder->select('TABLE_NAME, TABLE_COMMENT');
        $builder->where('TABLE_SCHEMA', $schema);
        $builder->groupStart()
            ->like('TABLE_NAME', $prefixo . '%', 'after')
            ->orLike('TABLE_NAME', '%_' . $prefixo, 'before')
            ->groupEnd();
        $builder->limit(5); // Limita candidatos

        return $builder->get()->getResultArray();
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


    /**
     * Summary of getSchemaEstruturado
     * Monta um array estruturado do schema (colunas + FKs) das tabelas
     * informadas, reaproveitando getCampos() e getRelacionamentos().
     * Resolve sozinho o dbGroup/schema de cada tabela pelo prefixo.
     *
     * Criada por: Claude / Dezembro 2025 (gerador de relatórios em linguagem natural)
     *
     * @param string[] $tabelas Lista de tabelas a incluir
     * @return array<string, array>
     */
    public function getSchemaEstruturado(array $tabelas): array
    {
        $schema = [];

        foreach ($tabelas as $tabela) {
            $dbGrSche = $this->getDbGroupAndSchema($tabela);

            // Pula tabela com prefixo desconhecido (não sabemos o banco)
            if ($dbGrSche['schema'] === null) {
                continue;
            }

            $campos = $this->getCampos($tabela);
            $fks    = $this->getRelacionamentos($tabela);

            $schema[$tabela] = [
                'dbGroup' => $dbGrSche['dbGroup'],
                'schema'  => $dbGrSche['schema'],
                'columns' => [],
                'foreign_keys' => [],
            ];

            foreach ($campos as $c) {
                $schema[$tabela]['columns'][] = [
                    'name'  => $c['COLUMN_NAME'],
                    'type'  => $c['DATA_TYPE'],
                    'label' => $c['COLUMN_COMMENT'] ?: null, // seu rótulo
                    'key'   => $c['COLUMN_KEY'],             // PRI, MUL, UNI
                ];
            }

            foreach ($fks as $fk) {
                $schema[$tabela]['foreign_keys'][] = [
                    'column'     => $fk['COLUMN_NAME'],
                    'ref_table'  => $fk['REFERENCED_TABLE_NAME'],
                    'ref_column' => $fk['REFERENCED_COLUMN_NAME'],
                ];
            }
        }

        return $schema;
    }

    /**
     * Summary of getSchemaContext
     * Monta o texto legível (com os rótulos/COMMENT) que será injetado
     * no prompt da LLM para a geração de SQL.
     *
     * @param string[] $tabelas Lista de tabelas a incluir
     * @return string
     */
    public function getSchemaContext(array $tabelas): string
    {
        $schema = $this->getSchemaEstruturado($tabelas);
        $out    = [];

        foreach ($schema as $tabela => $info) {
            // Qualifica com o schema, pois o sistema é multi-banco
            $out[] = "Tabela: {$info['schema']}.{$tabela}";

            foreach ($info['columns'] as $col) {
                $line = "  - {$col['name']} ({$col['type']})";
                if ($col['label']) {
                    $line .= ": {$col['label']}";
                }
                if ($col['key'] === 'PRI') {
                    $line .= " [chave primária]";
                }
                $out[] = $line;
            }

            foreach ($info['foreign_keys'] as $fk) {
                $out[] = "  - FK: {$fk['column']} referencia "
                    . "{$fk['ref_table']}.{$fk['ref_column']}";
            }

            $out[] = '';
        }

        return implode("\n", $out);
    }
}
