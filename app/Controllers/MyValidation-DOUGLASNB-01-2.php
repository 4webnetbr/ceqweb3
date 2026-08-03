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

    /**
     * RN03.19 (T43 — Fornecedores > Notificação de Evento): nev_notivisa_num
     * só é obrigatório (5 a 50 caracteres) quando nev_notivisa = 'S'; se
     * nev_notivisa = 'N', o campo pode vir vazio. Validação de negócio real
     * (não só checagem solta no Controller) — usada em
     * App\Models\Fornec\FornecNotifEventoModel::$validationRules.
     * Uso: obrigatorioSeNotivisaSim
     */
    public function obrigatorioSeNotivisaSim($value, ?string $params, array $data): bool
    {
        if (($data['nev_notivisa'] ?? 'N') !== 'S') {
            return true; // não obrigatório quando Notivisa = Não
        }

        $valor = trim((string) $value);

        return mb_strlen($valor) >= 5 && mb_strlen($valor) <= 50;
    }

    /**
     * Notificações SMS (Logística): campo nsc_saldo_minimo (grupo "Saldo
     * Baixo") só é obrigatório quando nsc_tipo_regra = 'saldo_baixo'.
     * NÃO ALTERADO neste ciclo (ver
     * docs/desenvolvimento/notificacoes-sms-tipos-alerta-dev.md, seção 4.10).
     * Uso: obrigatorioSeTipoRegraSaldo[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraSaldo($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'saldo_baixo') {
            return true;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Notificações SMS (Logística): campo nsc_metodo_api (grupo "API") só
     * é obrigatório quando nsc_tipo_regra = 'api'. Mesmo molde de
     * obrigatorioSeTipoRegraSaldo, só troca o valor de comparação.
     * Uso: obrigatorioSeTipoRegraApi[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraApi($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'api') {
            return true;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Notificações SMS (Logística): campo nsc_view_consulta (grupo
     * "Consulta") só é obrigatório quando nsc_tipo_regra = 'consulta'.
     * Mesmo molde de obrigatorioSeTipoRegraSaldo/Api.
     * Uso: obrigatorioSeTipoRegraConsulta[nsc_tipo_regra]
     */
    public function obrigatorioSeTipoRegraConsulta($value, ?string $params, array $data): bool
    {
        if (($data['nsc_tipo_regra'] ?? '') !== 'consulta') {
            return true;
        }

        return trim((string) $value) !== '';
    }
}
