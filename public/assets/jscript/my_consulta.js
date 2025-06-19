
function pesquisacep(obj, valor) {
    // regex = '\[(\w+)\]';
    // pos = obj['id'].indexOf("__");
    posi = obj.id.indexOf('[') + 1;
    posf = obj.id.indexOf(']');
    pos = obj.id.substr(posi, (posf - posi));
    if (pos >= 0) {
        pos_seq = obj['id'].substring(pos + 2, obj['id'].length);
    }
    //Nova variável "cep" somente com dígitos.
    var cep = valor.replace(/\D/g, '');
    //Verifica se campo cep possui valor informado.
    if (cep != "") {
        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;
        //Valida o formato do CEP.
        if (validacep.test(cep)) {
            // limpa_formulário_cep();
            var url = "https://viacep.com.br/ws/" + cep + "/json/?callback=?";
            var uf = [];
            uf['AC'] = '1';
            uf['AL'] = '2';
            uf['AM'] = '3';
            uf['AP'] = '4';
            uf['BA'] = '5';
            uf['CE'] = '6';
            uf['DF'] = '7';
            uf['ES'] = '8';
            uf['GO'] = '9';
            uf['MA'] = '10';
            uf['MT'] = '11';
            uf['MS'] = '12';
            uf['MG'] = '13';
            uf['PA'] = '14';
            uf['PB'] = '15';
            uf['PR'] = '16';
            uf['PE'] = '17';
            uf['PI'] = '18';
            uf['RJ'] = '19';
            uf['RN'] = '20';
            uf['RS'] = '21';
            uf['RO'] = '22';
            uf['RR'] = '23';
            uf['SC'] = '24';
            uf['SP'] = '25';
            uf['SE'] = '26';
            uf['TO'] = '27';

            // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
            // caso ocorra algum erro (o cep pode não existir, por exemplo) a
            // usabilidade não seja afetada, assim o usuário pode continuar//
            // preenchendo os campos normalmente
            jQuery.getJSON(url, function (dadosRetorno) {
                try {
                    // Preenche os campos de acordo com o retorno da pesquisa
                    // jQuery("#end_rua__"+pos_seq).val(dadosRetorno.logradouro);
                    // jQuery("#end_bairro__"+pos_seq).val(dadosRetorno.bairro);
                    // jQuery("#end_id_estado__"+pos_seq).val(uf[dadosRetorno.uf]);
                    // jQuery("#busca_end_id_cidade__"+pos_seq).val(dadosRetorno.localidade);
                    // jQuery('#busca_end_id_cidade__'+pos_seq).trigger('onkeyup');
                    jQuery("input[id='end_rua[" + pos + "]']").val(dadosRetorno.logradouro);
                    jQuery("input[id='end_bairro[" + pos + "]']").val(dadosRetorno.bairro);
                    jQuery("select[id='end_id_estado[" + pos + "]'] option[value='" + uf[dadosRetorno.uf] + "']").attr("selected", "selected");
                    jQuery("[name='end_id_estado[" + pos + "]']").selectpicker('refresh');
                    jQuery("[name='end_id_estado[" + pos + "]']").selectpicker('render');
                    jQuery("select[id='end_id_estado[" + pos + "]']").trigger('onchange');
                    jQuery("select[id='end_id_cidade[" + pos + "]'] option:contains(" + dadosRetorno.localidade + ")").attr("selected", "selected");
                    jQuery("[name='end_id_cidade[" + pos + "]']").selectpicker('refresh');
                    jQuery("[name='end_id_estado[" + pos + "]']").selectpicker('render');
                    // jQuery("[id='end_id_cidade["+pos+"]']").trigger('onkeyup');
                } catch (ex) { }
            });
        } //end if.
        else {
            //cep é inválido.
            // limpa_formulário_cep();
            alert("Formato de CEP inválido.");
        }
    } //end if.
    else {
        //cep sem valor, limpa formulário.
        // limpa_formulário_cep();
    }
};

function pesquisaCNPJ(valor) {
    //Nova variável "CNPJ" somente com dígitos.
    var CNPJ = valor.replace(/\D/g, '');
    //Verifica se campo CNPJ possui valor informado.
    if (CNPJ != "") {
        //Expressão regular para validar o CEP.
        var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
        //Valida o formato do CEP.
        if (validaCNPJ.test(CNPJ)) {
            // testaCliente = CNPJCPFcadastrado(valor); 
            url = window.location.origin + '/buscas/cnpjcpfcadastrado';
            dados = { 'cpfcnpf': valor };
            retornoAjax = false;
            executaAjax(url, 'json', dados);
            if (retornoAjax) {
                if (retornoAjax.tem == '1') {
                    boxAlert('CNPJ Já Cadastrado', true, '', false, 1, false);
                    jQuery('#cli_cnpj').val('');
                    jQuery('#cli_cnpj').focus();
                } else {
                    var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

                    // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
                    // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
                    // usabilidade não seja afetada, assim o usuário pode continuar//
                    // preenchendo os campos normalmente

                    jQuery.getJSON(url, function (dadosRetorno) {
                        jQuery('#cli_nome').val(dadosRetorno['RAZAO SOCIAL']);
                        jQuery('#cli_apelido').val(dadosRetorno['NOME FANTASIA']);
                        localStorage.setItem('dadoscnpj', dadosRetorno);
                        // pesquisacepcnpj();
                    });
                }
            }
            // jQuery.ajax({
            //     type: 'POST',
            //     async: false,
            //     dataType: 'json',
            //     url: url,
            //     data: { 'cpfcnpf': valor },
            //     success: function (retorno) {
            //         if (retorno.tem == '1') {
            //             boxAlert('CNPJ Já Cadastrado', true, '', false, 1, false);
            //             jQuery('#cli_cnpj').val('');
            //             jQuery('#cli_cnpj').focus();
            //         } else {
            //             var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

            //             // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
            //             // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
            //             // usabilidade não seja afetada, assim o usuário pode continuar//
            //             // preenchendo os campos normalmente

            //             jQuery.getJSON(url, function (dadosRetorno) {
            //                 jQuery('#cli_nome').val(dadosRetorno['RAZAO SOCIAL']);
            //                 jQuery('#cli_apelido').val(dadosRetorno['NOME FANTASIA']);
            //                 localStorage.setItem('dadoscnpj', dadosRetorno);
            //                 // pesquisacepcnpj();
            //             });
            //         }
            //     }
            // });
        } //end if.
        else {
            //CNPJ é inválido.
            // limpa_formulário_CNPJ();
            boxAlert('Formato de CNPJ inválido', true, '', false, 1, false);
        }
    } //end if.
    else {
        //CNPJ sem valor, limpa formulário.
        // limpa_formulário_CNPJ();
    }
};

function pesquisaCPF(valor) {
    if (valor != '') {
        // testaCliente = ; 
        url = window.location.origin + '/buscas/cnpjcpfcadastrado';
        dados = { 'cpfcnpf': valor };
        retornoAjax = false;
        executaAjax(url, 'json', dados);
        if (retornoAjax) {
            if (retornoAjax.tem == '1') {
                boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
                jQuery('#cli_cpf').val('');
                jQuery('#cli_cpf').focus();
            }
        }
        // jQuery.ajax({
        //     type: 'POST',
        //     async: false,
        //     dataType: 'json',
        //     url: url,
        //     data: { 'cpfcnpf': valor },
        //     success: function (retorno) {
        //         if (retorno.tem == '1') {
        //             boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
        //             jQuery('#cli_cpf').val('');
        //             jQuery('#cli_cpf').focus();
        //         }
        //     }
        // });
    }
}

function pesquisacepcnpj() {
    dadoscnpj = localStorage.getItem('dadoscnpj');
    if (dadoscnpj != undefined) {
        jQuery("#end_logradouro").val(dadoscnpj['LOGRADOURO']);
        jQuery("#end_cep").val(dadoscnpj['CEP']);
        jQuery("#end_bairro").val(dadoscnpj['BAIRRO']);
        jQuery("#end_cidade").val(dadoscnpj['MUNICIPIO']);
        jQuery("#end_uf").val(dadoscnpj['UF']);
    }
}

function busca_dados_material(orig, obj, url, base) {
    var datarr = new Array();
    pref = obj.substring(0, 3);
    posi = orig.id.indexOf('[') + 1;
    posf = orig.id.indexOf(']');
    pos = orig.id.substr(posi, (posf - posi));
    // sufi = '';
    // if(orig.id.indexOf('__') > 0){
    //     sufi = orig.id.substr(-3);
    // }   
    datarr[0] = {};
    datarr[0].valor = orig.value;
    dados = { campo: datarr };
    retornoAjax = false;
    executaAjax(url, 'json', dados);
    if (retornoAjax) {
        jQuery('#' + pref + '_unidade\\[' + pos + '\\]').selectpicker('val', retornoAjax.mat_unidade);
        jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retornoAjax.mat_compra / retornoAjax.mat_quantia));
        if (retornoAjax.mpc_unitario != null) {
            jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retornoAjax.mpc_unitario / 1));
        }
    }
    // jQuery.ajax({
    //     type: 'POST',
    //     async: false,
    //     dataType: 'json',
    //     url: url,
    //     data: { campo: datarr },
    //     success: function (retorno) {
    //         // jQuery('#'+pref+'_unidade\\['+pos+'\\]').val(retorno.mat_unidade).change();
    //         // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('destroy');
    //         // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('deselectAll');
    //         // jQuery('#'+pref+'_unidade\\['+pos+'\\] option[value='+retorno.mat_unidade+']').attr('selected', 'selected');
    //         jQuery('#' + pref + '_unidade\\[' + pos + '\\]').selectpicker('val', retorno.mat_unidade);
    //         // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('refresh');
    //         jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retorno.mat_compra / retorno.mat_quantia));
    //         if (retorno.mpc_unitario != null) {
    //             jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retorno.mpc_unitario / 1));
    //         }
    //         console.log(retorno);
    //     }
    // });
}

