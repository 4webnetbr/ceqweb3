function carregarCamposDoTipoOcorrencia(dados) {

    // LIMPA AS DIVS
    jQuery('#rep_telas_aplicaveis').empty();
    jQuery('#rep_acoes').empty();


    //  TELAS APLICÁVEIS

    dados.telas.forEach((tela, index) => {

        let fakeBtn = {
            getAttribute: (attr) => attr === "data-index" ? index : null
        };
    
        addCampo(
            base_url + "OcoModOcorrencia/addCampoTa",
            "telas_aplicaveis",
            fakeBtn
        );
    
        setTimeout(() => {
            preencheLinha(
                `#rep_telas_aplicaveis .table-telas_aplicaveis[data-index="${index}"]`,
                tela
            );
        }, 50);
    });




    //  AÇÕES 

    dados.acoes.forEach((acao, index) => {

        let fakeBtn = {
            getAttribute: (attr) => attr === "data-index" ? index : null
        };
    
        addCampo(
            base_url + "OcoModOcorrencia/addCampoTp",
            "acoes",
            fakeBtn
        );
    
        let acaoCorrigida = {
            tpa_id: acao.tpa_id,
            tmo_id: acao.tmo_id,
            mod_id: acao.mod_id,
            tel_id: acao.tel_id,
            stt_id: acao.stt_id
        };
    
        setTimeout(() => {
            preencheLinha(
                `#rep_acoes .table-acoes[data-index="${index}"]`,
                acaoCorrigida
            );
        }, 50);
   });

}



