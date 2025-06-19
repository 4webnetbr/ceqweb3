/**
 * Calcula o total dos Itens de Compra, rateia fretes e descontos, e calcula o total da Compra
 * 
 * @param {*} orig 
 * @param {*} quantia 
 * @param {*} unitario 
 * @param {*} frete 
 * @param {*} ipi 
 * @param {*} desconto 
 * @param {*} total 
 */
function calcula_tot_it_compra(orig, quantia,unitario,frete, ipi,desconto, total){
    calcula_tot_desconto(orig,desconto,'pdc_desconto');
    indice = orig.getAttribute('data-index');    
    if(jQuery('#'+quantia+'\\['+indice+'\\]').val() == ''){
        quant = 0;
    } else {
        quant = parseFloat(jQuery('#'+quantia+'\\['+indice+'\\]').val());
    }
    vunit = converteMoedaFloat(jQuery('#'+unitario+'\\['+indice+'\\]').val());
    pctipi = jQuery('#'+ipi+'\\['+indice+'\\]').val();
    vaipi = vunit * (pctipi / 100);
    totun = quant * vunit;
    tot = totun + vaipi;
    jQuery('#'+total+'\\['+indice+'\\]').val(converteFloatMoeda(tot));
    calcula_tot_compra_frete();
}

/**
 * Calcula o Total de Impostos dos itens da Compra
 * 
 * @param {*} orig 
 * @param {*} imposto 
 * @param {*} total 
 */
function calcula_tot_impostos(orig, imposto,total){
    // sufi = '__'+jQuery(orig).attr('data-index');
    // sufi = jQuery(orig).attr('data-index');
    indice = orig.getAttribute('data-index');    

    totimposto = 0;
    jQuery("[id^='"+imposto+"']").each( function(){
        if(imposto == 'mpc_ipi'){
            vunit = converteMoedaFloat(jQuery('#mpc_unitario\\['+indice+'\\]').val());
            pctipi = this.value;
            vaipi = vunit * (pctipi / 100);    
            valor = vaipi;
        } else {
            valor = converteMoedaFloat(this.value);
        }
        totimposto += valor;
    });
    jQuery('#'+total).val(converteFloatMoeda(totimposto));
}

/**
 * Calcula o total do frete dos itens da Compra
 * 
 * @param {*} orig 
 * @param {*} frete 
 * @param {*} total 
 */
function calcula_tot_frete(orig, frete,total){
    // sufi = '__'+jQuery(orig).attr('data-index');
    // sufi = jQuery(orig).attr('data-index');
    indice = orig.getAttribute('data-index');    
    totfrete = 0;
    jQuery("[id^='"+frete+"']").each( function(){
        valor = converteMoedaFloat(this.value);
        totfrete += valor;
    });
    jQuery('#'+total).val(converteFloatMoeda(totfrete));
}

/**
 * Calcula o Total de Desconto dos ítens da Compra
 * 
 * @param {*} orig 
 * @param {*} desconto 
 * @param {*} total 
 */
function calcula_tot_desconto(orig, desconto, total){
    indice = orig.getAttribute('data-index');    
    totdesc = 0;
    jQuery("[id^='"+desconto+"']").each( function(){
        valor = converteMoedaFloat(this.value);
        totdesc += valor;
    });
    jQuery('#'+total).val(converteFloatMoeda(totdesc));
}

// function calcula_tot_compra(orig, totuni,total){
//     calcula_tot_compra_frete();
// }
/**
 * Calcula o Total da Compra, considerando Frete e Descontos
 */
function calcula_tot_compra_frete(){
    vfret = converteMoedaFloat(jQuery('#pdc_total_frete').val());
    vdesc = converteMoedaFloat(jQuery('#pdc_desconto').val());
    somar_frete = jQuery('input[name="pdc_somar_frete"]:checked').val();
    vtotal = 0;
    jQuery("[id^='mpc_total']").each( function(){
        vtotal += converteMoedaFloat(jQuery(this).val());
    });
    if(somar_frete == 'S' ){
        tot = vtotal + vfret;
    } else {
        tot = vtotal;
    }
    tot = tot - vdesc;
    jQuery('#pdc_total').val(converteFloatMoeda(tot));
    rateia_frete_mat();
    rateia_desconto_mat();
}

/**
 * Rateia o valor do frete nos itens da Compra
 * 
 */
function rateia_frete_mat(){
    total_desconto = converteMoedaFloat(jQuery('#pdc_desconto').val());
    total_frete = converteMoedaFloat(jQuery('#pdc_total_frete').val());
    if(total_frete > 0){
        total_compra   = converteMoedaFloat(jQuery('#pdc_total').val());
        somar_frete = jQuery('input[name="pdc_somar_frete"]:checked').val();
        // se o frete não é incluso no Total da Compra, e é pago separado
        // add o valor do frete no total da compra pra efeito de cálculo do custo do material
        if(somar_frete == 'S'){ 
            total_compra = total_compra - total_frete;
        }
        total_compra = total_compra +  total_desconto; 
        // for por todos os materiais
        jQuery("[id^='mpc_frete']").each( function(){
            // $ind     = jQuery(this).attr('data-index');
            indice = this.getAttribute('data-index');    
            tot_mat = converteMoedaFloat(jQuery('#mpc_total\\['+indice+'\\]').val());
            pct_mat   = (tot_mat * 100) / total_compra;
            fre_mat = (total_frete * (pct_mat /100));
            jQuery(this).val(converteFloatMoeda(fre_mat));
            des_mat = converteMoedaFloat(jQuery('#mpc_desconto\\['+indice+'\\]').val());
            cus_mat =  tot_mat + fre_mat - des_mat;
            jQuery('#mpc_custo\\['+indice+'\\]').val(converteFloatMoeda(cus_mat));
        });
    } else {
        jQuery("[id^='mpc_frete']").each( function(){
            jQuery(this).val(converteFloatMoeda('0.00'));
        });
    }
}

/**
 * Rateia o Total de Desconto nos itens da compra
 */
function rateia_desconto_mat(){
    total_desconto = converteMoedaFloat(jQuery('#pdc_desconto').val());
    desc_total = total_desconto;
    total_compra   = converteMoedaFloat(jQuery('#pdc_total').val());
    total_frete   = converteMoedaFloat(jQuery('#pdc_total_frete').val());
    somar_frete = jQuery('input[name="pdc_somar_frete"]:checked').val();
    // se o frete não é incluso no Total da Compra, e é pago separado
    // add o valor do frete no total da compra pra efeito de cálculo do custo do material
    if(somar_frete == 'S'){ 
        total_compra = total_compra - total_frete;
    }
// verifica se algum produto já tem desconto destacado
    jQuery("[id^='mpc_desconto']").each( function(){
        val_des_mat = converteMoedaFloat(jQuery(this).val());
        if(val_des_mat > 0 && jQuery(this).attr('data-alter')){
            total_desconto = total_desconto - val_des_mat;
            // $ind     = jQuery(this).attr('data-index');
            indice = this.getAttribute('data-index');    
            fre_mat = converteMoedaFloat(jQuery('#mpc_frete\\['+indice+'\\]').val());
            tot_mat = converteMoedaFloat(jQuery('#mpc_total\\['+indice+'\\]').val());
            cus_mat =  tot_mat + fre_mat - val_des_mat;
            total_compra = total_compra - cus_mat;
            
            pct = (val_des_mat * 100) / cus_mat;
            jQuery('#mpc_pct_desconto\\['+indice+'\\]').val(parseFloat(pct).toFixed(3)); // pct_desconto é decimal não é moeda
    
            jQuery('#mpc_custo\\['+indice+'\\]').val(converteFloatMoeda(cus_mat));
        }
    })
    // for por todos os materiais
    jQuery("[id^='mpc_desconto']").each( function(){
        indice = this.getAttribute('data-index');    
        val_des_mat = converteMoedaFloat(jQuery(this).val());
        fre_mat = converteMoedaFloat(jQuery('#mpc_frete\\['+indice+'\\]').val());
        tot_mat = converteMoedaFloat(jQuery('#mpc_total\\['+indice+'\\]').val());
        if(!jQuery(this).attr('data-alter') && !jQuery('#mpc_pct_desconto\\['+indice+'\\]').attr('data-alter')){
            // $ind     = jQuery(this).attr('data-index');
            pct_mat   = (tot_mat * 100) / (total_compra + total_desconto);
            val_des = (total_desconto * (pct_mat /100));
            jQuery(this).val(converteFloatMoeda(val_des));
            cus_mat =  tot_mat + fre_mat - val_des;
            jQuery('#mpc_custo\\['+indice+'\\]').val(converteFloatMoeda(cus_mat));

            pct = (val_des * 100) / cus_mat;
            jQuery('#mpc_pct_desconto\\['+indice+'\\]').val(parseFloat(pct).toFixed(3)); // pct_desconto é decimal não é moeda
        } else {
            val_des = converteMoedaFloat(jQuery('#mpc_desconto\\['+indice+'\\]').val());
            cus_mat = tot_mat + fre_mat - val_des;
            jQuery('#mpc_custo\\['+indice+'\\]').val(converteFloatMoeda(cus_mat));
        }
    });
}

/**
 * Rateia o Total de Desconto nos itens da compra
 */
function calcula_desconto(orig, pct_desconto, val_desconto){
    indice = orig.getAttribute('data-index');    
    campo = orig.getAttribute('data-nome');
    uni_mat = converteMoedaFloat(jQuery('#mpc_unitario\\['+indice+'\\]').val());
    pct = jQuery('#'+pct_desconto+'\\['+indice+'\\]').val();
    val = converteMoedaFloat(jQuery('#'+val_desconto+'\\['+indice+'\\]').val());
    if(campo == 'mpc_pct_desconto' && jQuery('#mpc_pct_desconto\\['+indice+'\\]').attr('data-alter')){
        val = uni_mat * (pct / 100);
        jQuery('#mpc_desconto\\['+indice+'\\]').attr('data-alter',"");
    } else if(campo == 'mpc_desconto' && jQuery('#mpc_desconto\\['+indice+'\\]').attr('data-alter')){
        pct = (val * 100) / uni_mat;
        jQuery('#mpc_pct_desconto\\['+indice+'\\]').attr('data-alter',"");
    }
    jQuery('#mpc_desconto\\['+indice+'\\]').val(converteFloatMoeda(val));
    jQuery('#mpc_pct_desconto\\['+indice+'\\]').val(parseFloat(pct).toFixed(3)); // pct_desconto é decimal não é moeda
}