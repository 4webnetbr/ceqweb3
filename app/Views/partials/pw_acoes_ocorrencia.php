<div class="accordion" id="accAcoes">
    <div class="accordion-item">

        <h2 class="accordion-header border border-bottom-1" id="headacao<?= $oco_id ?>">
            <button class="accordion-button text-center"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseacao<?= $oco_id ?>"
                aria-expanded="true"
                aria-controls="collapseacao<?= $oco_id ?>">
                <div class='col-12 text-center'>Ações</div>
            </button>
        </h2>

        <div id="collapseacao<?= $oco_id ?>"
             class="accordion-collapse collapse show"
             aria-labelledby="headacao<?= $oco_id ?>"
             data-bs-parent="#accAcoes">

            <div class="accordion-body p-0">

                <!-- Mesmo layout "tabela" (div-based) usado pela aba Ações do
                     OcoSubtOcorrencia (ver vw_edicao.php) — necessário para o
                     bt_addtp/bt_deltp (já criados na entity, addCampo()/
                     exclui_campo() em my_fields.js) inserir/remover linhas no
                     formato correto. O botão de adicionar só aparece na linha
                     da última ação (ver OcoTrataOcorrencia::montaLinhaAcao()). -->
                <div id="rep_acoes" class="rep_campos d-inline-table table2-responsive rep_acoes" data-acoes-index="<?= max(count($acoes) - 1, 0) ?>">
                    <?php $count = 0; ?>
                    <?php foreach ($acoes as $c => $linha): $count++; ?>
                        <div class="row tableDiv table2 mb-4 table-acoes <?= ($count % 2 ? 'odd' : 'even') ?>" width="100%" data-index="<?= $c ?>">
                            <div class="col-11">
                                <?= $linha[0] ?><?= $linha[1] ?><?= $linha[2] ?><?= $linha[3] ?>
                            </div>
                            <div class="col-1 d-initial h-auto p-0">
                                <div class="col-9 d-block float-start text-center p-0">
                                    <?= $linha[4] ?><?= $linha[5] ?>
                                </div>
                                <?php if (stripos((string) $linha[5], 'exclui') !== false): ?>
                                    <div class="col-3 d-block float-end text-end">
                                        <button name="bt_up[<?= $c ?>]" type="button" id="bt_up[<?= $c ?>]" class="btn btn-outline-info btn-sm bt-up acoes mt-0 float-end" onclick='sobe_desce_item(this,"sobe","acoes")' title="Acima" data-index="<?= $c ?>"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>
                                        <button name="bt_down[<?= $c ?>]" type="button" id="bt_down[<?= $c ?>]" class="btn btn-outline-info btn-sm bt-down acoes mt-0 float-end" onclick='sobe_desce_item(this,"desce","acoes")' title="Abaixo" data-index="<?= $c ?>"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    </div>
</div>
