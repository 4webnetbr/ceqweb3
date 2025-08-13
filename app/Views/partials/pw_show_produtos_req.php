<style>
  .tabela-pequena {
    font-size: 10px;
  }
</style>
<?
if(!isset($show)){
    $show = false;
}
?>
<div class="accordion" id="accProdutos">
    <div class="accordion-item">
        <h2 class="accordion-header border border-bottom-1" id="heading<?= $produtos[0] ?>">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapse<?= $produtos[0] ?>" aria-expanded="false"
              aria-controls="collapse<?= $produtos[0] ?>" data-proid="<?= $produtos[0] ?>">
                <div class='col-12 text-center'>Produtos</div>
            </button>
        </h2>
        <div id="collapse<?= $produtos[0] ?>" class="accordion-collapse collapse show" aria-labelledby="heading<?= $produtos[0] ?>" data-bs-parent="#accProdutos">
            <div class="accordion-body p-1" style="max-height:49vh; height:49vh; overflow-y: auto">
                <table class="table table-bordered table-sm text-center align-middle tabela-pequena">
                    <thead class="table-light">
                        <tr>
                            <?
                            for ($c=0; $c < count($colunas) ; $c++) { ?>
                                <th><?=$colunas[$c];?></th>
                            <?
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody style="max-height: 45vh; overflow-y: auto;">
                        <?
                        if(count($produtos) > 1){
                            foreach (array_slice($produtos, 1) as $produto): ?>
                                <tr id='<?= $produto[0]?>'>
                                <?
                                for ($c=1; $c < count($produto) ; $c++) { ?>
                                    <td><?=$produto[$c];?></td>
                                <?
                                }?>
                                </tr>
                                <?
                            endforeach;
                        } else {?>
                            <tr>
                            <?
                            for ($c=0; $c < count($colunas) ; $c++) { ?>
                                <td></td>
                            <?
                            }?>
                            </tr>
                        <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>