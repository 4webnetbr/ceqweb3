<?php
/**
 * Lista de anexos com upload múltiplo (RN03.16 / RN03.20), padrão validado
 * pelo bydsgn: reaproveita bt-repete/bt-exclui/addCampo() (my_fields.js) —
 * mesmo mecanismo de T9 — e o upload sobe junto com o Salvar geral do form
 * (submeteForm()/executaAjax() já montam FormData automaticamente, sem
 * precisar de upload assíncrono item-a-item).
 *
 * $origem  'PROVID' | 'PARECER'
 * $nev_id  int|null (null = notificação ainda não existe, tela de criação)
 * $anexos  array de stdClass já persistidos (oco_notif_evento_anexo), vazio em criação
 */

use App\Libraries\MyCampo;

$sufixo = strtolower($origem) === 'provid' ? 'provid' : 'parecer';
$anexos = $anexos ?? [];
?>
<div class="col-12 mt-2">
    <label class="form-label fw-bold">Anexos</label>
    <div id="rep_anexo_<?= $sufixo ?>" data-index="<?= count($anexos) ?>">
        <?php foreach ($anexos as $i => $anexo): ?>
            <div class="row tableDiv table2 mb-2 table-anexo_<?= $sufixo ?> align-items-center" data-index="<?= $i ?>">
                <div class="col-9">
                    <img src="<?= buscaTipoArquivo(['arq_tipo' => $anexo->nva_arquivo]) ?>" style="height:20px" class="me-2">
                    <a href="<?= base_url('Showfile/' . rawurlencode($anexo->nva_arquivo)) ?>" target="_blank"><?= esc($anexo->nva_nome_original) ?></a>
                    <input type="hidden" name="nva_id_<?= $sufixo ?>[<?= $i ?>]" value="<?= $anexo->nva_id ?>">
                </div>
                <div class="col-3 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        title="Excluir Anexo"
                        onclick="excluiAnexoExistente(this, <?= $anexo->nva_id ?>, 'anexo_<?= $sufixo ?>')">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php
        // Uma linha em branco inicial, para permitir anexar já na criação.
        $campo = new MyCampo();
        $campo->objeto  = 'file';
        $campo->nome    = $campo->id = "nva_arquivo_{$sufixo}[" . count($anexos) . ']';
        $campo->label   = 'Anexo';
        $campo->setTipoArq('.pdf,.png,.jpeg,.jpg');
        ?>
        <div class="row tableDiv table2 mb-2 table-anexo_<?= $sufixo ?> align-items-center" data-index="<?= count($anexos) ?>">
            <div class="col-9"><?= $campo->crArquivo() ?></div>
            <div class="col-3 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm bt-exclui"
                    data-index="<?= count($anexos) ?>"
                    title="Excluir Anexo"
                    onclick="exclui_campo('anexo_<?= $sufixo ?>', this)">
                    <i class="far fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row px-2 pb-2">
        <div class="col-12 text-end">
            <button type="button"
                class="btn btn-outline-success btn-sm bt-repete"
                data-index="<?= count($anexos) ?>"
                title="Adicionar Anexo"
                onclick="addCampo('<?= base_url('NotifEvento/addAnexo' . ucfirst($sufixo) . '/') ?>','anexo_<?= $sufixo ?>', this)">
                <i class="fas fa-plus"></i> Adicionar Anexo
            </button>
        </div>
    </div>
</div>
