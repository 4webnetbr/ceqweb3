-- Migration pendente — Ocorrências T9/T11/T12 (RN03.6 / RN03.1.6 / RN03.2.5)
-- Referência: docs/desenvolvimento/ocorrencias-t9-t11-t12-dev.md — Seção 4
--
-- NÃO EXECUTAR AUTOMATICAMENTE.
-- Aplicar manualmente em dev (dev_ocorrencia_db) primeiro; produção exige
-- confirmação explícita e separada do usuário antes do deploy.
--
-- Após aplicar esta migration, os seguintes pontos do código passam a
-- funcionar corretamente (dependem da existência da coluna `sta_fina`):
--   - App\Controllers\Ocorrencia\OcoSubtOcorrencia::store() / defCamposAcao()
--   - App\Models\Ocorre\OcorreSubtOcorrenciaModel::getStatusInicial()
--   - App\Controllers\Ocorrencia\OcoOcorrencia::store() / storetmp()

ALTER TABLE `oco_subt_ocorrencia_acao`
  ADD COLUMN `sta_fina` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Finalização Automática' AFTER `stt_id`;
