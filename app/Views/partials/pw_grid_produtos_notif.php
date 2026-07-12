<?php
/**
 * Grid "Dados do Produto" (aba Dados Gerais, RN03.2-RN03.9). Uma linha por
 * produto/lote selecionado na tela intermediária (imutável nesta tela — a
 * seleção só é feita em NotifEvento::add()/selecionaProdutos()).
 * $produtos array de arrays de campos já renderizados (EntOcoNotifEventoProduto::campos)
 */
?>
<div class="col-12">
    <label class="form-label fw-bold">Dados do Produto</label>
    <table class="table table-bordered table-sm align-middle tabela-pequena">
        <tbody>
            <?php foreach ($produtos as $linha): ?>
                <tr>
                    <td>
                        <div class="row">
                            <?php foreach ($linha as $chave => $campo): ?>
                                <?= $campo ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
