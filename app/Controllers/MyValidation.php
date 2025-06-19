<?php

namespace App\Controllers;

use App\Models\Config\ConfigStatusModel;
use App\Models\Produt\ProdutClasseModel;
use Config\Database;

class MyValidation
{
    /**
     * Validação personalizada, utilizada na Classe Status
     * Usada para validar se já existe um Status com o nome informado, na Tela informada
     * @param mixed $value  //o nome do status
     * @param string $params // parametro obrigatório definido como [] vazio
     * @param array $data // os dados que estão sendo submetidos
     * @return bool
     */
    public function nome_status_existe($value, string $params, array $data): bool
    {
        $params = explode(',', $params);

        $nome = $value;
        $tel_id   = $data['tel_id'];
        $stt_id   = $data['stt_id'];
        $stat = new ConfigStatusModel();
        $tem = $stat->getStatusNomeTela($tel_id, $nome, $stt_id);
        if (count($tem) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validação genérica de campo único.
     * Uso: isUniqueValue[tabela.campo,campo_id]
     */
    public function isUniqueValue(string $str, string $fields, array $data, ?string $field = null): bool
    {
        // Exemplo de $fields: "dbProduto.pro_classe.cla_nome,cla_id"
        $parts = explode(',', $fields);
        $connTableField = explode('.', $parts[0]);

        if (count($connTableField) !== 3) {
            return false; // formato inválido
        }

        [$conn, $table, $fieldName] = $connTableField;
        $idField = $parts[1] ?? null;

        $db = Database::connect($conn);
        $builder = $db->table($table);
        $builder->where($fieldName, $str);

        if ($idField && !empty($data[$idField])) {
            $builder->where("$idField !=", $data[$idField]);
        }

        return $builder->countAllResults() === 0;
    }
}
