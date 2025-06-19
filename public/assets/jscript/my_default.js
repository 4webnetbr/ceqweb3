jQuery.noConflict();
var retornoAjax;
var executandoAjax = false;
jQuery(document).ready(function () {
    // jQuery(".div-img-etapa").mousedown(function (ev) {
    //     if (ev.which == 2) {
    //         urlimage = this.children[0].src;
    //         openImgDiv(urlimage, 'show_image');
    //         ev.stopPropagation();
    //         ev.preventDefault();
    //         // these two are older ways I like to add to maximize browser compat
    //         ev.returnValue = false;
    //         ev.cancelBubble = true;
    //         return false;                 //   alert("Right mouse button clicked on element with id myId");
    //     }
    // });

    // Gera ou recupera um identificador único para a guia
    if (!sessionStorage.getItem('tabId')) {
        sessionStorage.setItem('tabId', Date.now() + '-' + Math.random().toString(36).substr(2, 9));
    }

    // Adiciona o identificador em um campo oculto
    const tabId = sessionStorage.getItem('tabId');
    document.cookie = `tabId=${tabId}; path=/;`;

    jQuery.ajaxSetup({
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Tab-ID', sessionStorage.getItem('tabId'));
        }
    });


    /**
     * se a div Toast tiver conteúdo mostra por 3 segundos
     * Document Ready my_default
     */
    texto = jQuery('.toast-body').html();
    if (jQuery.trim(texto) != '') {
        confirma = erro = false;
        if (!isNaN(jQuery.trim(texto))) {// é número
            msg = jQuery.trim(texto);
            msg_id = msg_cfg[msg - 1];
            mensagem = msg_id.msg_mensagem;
            tipo = msg_id.msg_tipo;
            if (tipo == 'P') {
                titulo = '<i class="fa-solid fa-circle-question fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span>';
                confirma = true;
            } else if (tipo == 'A') {
                titulo = '<i class="fa-solid fa-circle-exclamation fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span>';
                tipo = 1;
            } else if (tipo == 'E') {
                titulo = '<i class="fa-solid fa-circle-xmark fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span>';
                erro = true;
            } else if (tipo == 'I') {
                titulo = '<i class="fa-solid fa-circle-info fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span>';
            }
            if (confirma) {
                boxAlert(msg, false, '', true, 1, true, titulo);
            } else if (erro) {
                boxAlert(msg, true, '', true, 1, false, titulo);
            } else {
                cor = msg_id.msg_cor;
                jQuery('#toast-title').html(titulo);
                jQuery('#myToast').removeClass('bg-success');
                jQuery('#myToast').addClass(cor);
                jQuery('#toast-body').html(mensagem);
                mostranoToast(mensagem);
            }
        }
        // if (!confirma && !erro) {
        //     showToast();
        // }
    };;

    jQuery(".toast").on("hide.bs.toast", function () {
        jQuery(".toast-body").html('');
    });

    jQuery("#bt_salvar").on('click', function (event) {
        const controller = jQuery('#controler').val();

        if (controller === 'requisicao') {
            // event.preventDefault(); // impede submit automático
            enviarRequisicoes();
        }
        var elemAlterado = false;
        var form = jQuery("#form1");
        // elemAlterado = Boolean(form[0].getAttribute('data-alter'));
        if (form[0].getAttribute('data-alter') === true || form[0].getAttribute('data-alter') === "true") {
            elemAlterado = true;
        };
        // }
        if (!elemAlterado) {
            event.preventDefault();
            event.stopPropagation();
            url = '/buscas/gravasessao';
            var mens = 7;
            let dados = { msg: mens };
            executaAjax(url, 'json', dados);
            if (retornoAjax) {
                retorna_listagem();
            }
        } else {
            jQuery("[id*='-valid']").addClass('d-none');
            jQuery("[id*='-fival']").removeClass('d-block');
            var form = jQuery(this)[0].form;

            form.classList.add('was-validated');
            isvalido = validador(form);
            if (!isvalido) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // verificar se o campo select foi alterado
                jQuery("input[data-valid], select[data-valid]").each(async function () {
                    let validar = this.getAttribute('data-valid');

                    if (!validar || validar === "0") return;
                    let valororig = this.getAttribute('data-valor');
                    let value = jQuery(this).val();

                    // Se for um array (como em selects múltiplos), converte para string
                    if (Array.isArray(value)) {
                        value = value.join(",");
                    }

                    if (valororig !== value) {
                        event.preventDefault();
                        event.stopPropagation();
                        boxAlert(20, false, 'submit', true, 1, true, '');
                    }
                });
            }
        }
    });

    /**
     * Submete o Formulário com ajax
     * Document Ready my_default
     */
    jQuery('#form1, #form_modal').on('submit', function (event) {
        // se não foi clicado no botão salvar, cancela o submit
        bt_clic = (event.originalEvent) ? event.originalEvent.submitter.id : this[0].id;
        if (bt_clic != 'bt_salvar' && bt_clic != 'bt_aprovar' && bt_clic != 'bt_rejeitar' && bt_clic != 'bt_enviar') {
            return false;
        } else {
            var form = jQuery(this);
            form.addClass('was-validated');

            var tipo = form.attr('type');
            if (tipo != 'modal') {
                bloqueiaTela();
            }
            if (tipo != 'normal') {
                event.preventDefault(); // avoid to execute the actual submit of the form.
                jQuery(".moeda").each(function () {
                    var valor = converteMoedaFloat(jQuery(this).val());
                    jQuery(this).val(valor);
                });
                var disabled = form.find(':input:disabled').removeAttr('disabled');
                if (jQuery('.selectpicker').length > 0) {
                    jQuery('.selectpicker').selectpicker('refresh');
                }

                var dadosForm = new FormData(this);

                dadosForm.forEach(function (value, key) {
                    console.log(key + ': ' + value);
                });
                disabled.attr('disabled', 'disabled');
                // console.log(dadosForm.values);
                var url = form.attr('action');
                executaAjax(url, 'json', dadosForm);
                if (retornoAjax) {
                    if (tipo != 'modal') {
                        desBloqueiaTela();
                        if (!retornoAjax.erro) {
                            if (retornoAjax.url) {
                                if (tipo == 'novaguia') {
                                    redirec_blank(retornoAjax.url);
                                } else {
                                    redireciona(retornoAjax.url)
                                }
                            }
                        } else {
                            boxAlert(retornoAjax.msg, retornoAjax.erro, retornoAjax.url, false, 1, false);
                        }
                    } else {
                        var myModalEl = document.getElementById('myModal');
                        var modal = bootstrap.Modal.getInstance(myModalEl)
                        modal.hide();
                        jQuery('.toast-body').html(retornoAjax.msg);
                        texto = jQuery('.toast-body').html();
                        if (jQuery.trim(texto) != '') {
                            mostranoToast(texto, true);
                        };;
                    }
                }
            }
        }
    });;

    /**
     * aprovaProduto
     * Aprova ou Rejeita o Produto
     */
    jQuery("#bt_aprovar").on('click', function (event) {
        var form = jQuery("#form1");
        jQuery("[id*='-valid']").addClass('d-none');
        jQuery("[id*='-fival']").removeClass('d-block');
        var form = jQuery('#form1')[0];

        form.classList.add('was-validated');
        isvalido = validador(form);
        if (isvalido) {
            jQuery('<input>').attr({
                type: 'hidden',
                id: 'aprova',
                name: 'aprova',
                value: 3 // id do status aprovado
            }).appendTo('#form1');
            jQuery('#form1').trigger('submit');
        } else {
            event.preventDefault();
            event.stopPropagation();
        }
    });;

    /**
     * rejeitaProduto
     * Rejeita o Produto
     */
    jQuery("#bt_rejeitar").on('click', function (event) {
        var form = jQuery("#form1");
        jQuery("[id*='-valid']").addClass('d-none');
        jQuery("[id*='-fival']").removeClass('d-block');
        var form = jQuery('#form1')[0];

        form.classList.add('was-validated');
        isvalido = validador(form);
        if (isvalido) {
            jQuery('<input>').attr({
                type: 'hidden',
                id: 'aprova',
                name: 'aprova',
                value: 2 // id do status reprovado
            }).appendTo('#form1');
            jQuery('#form1').trigger('submit');
        } else {
            event.preventDefault();
            event.stopPropagation();
        }
    });;

    // setInterval(() => {
    //     retornoAjax = false;
    //     url = 'buscas/verSessao';
    //     executaAjax(url, 'json');
    //     if (!retornoAjax.sessao) {
    //         redireciona('/login');
    //     }
    // }, 120000);

})


function getContrastYIQ(hexcolor) {
    hexcolor = hexcolor.replace('#', '');
    const r = parseInt(hexcolor.substr(0, 2), 16);
    const g = parseInt(hexcolor.substr(2, 2), 16);
    const b = parseInt(hexcolor.substr(4, 2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return yiq >= 128 ? 'black' : 'white';
}

function rgbToHex(rgb) {
    const result = rgb.match(/\d+/g);
    return result
        ? '#' + result.map(x => (+x).toString(16).padStart(2, '0')).join('')
        : '#000000';
}

function showToast(message, isAlert = false) {
    let msg_id = null;
    let titulo = 'Informação';
    let corpo = message;
    let fundo = isAlert ? '#dc3545' : '#198754';
    let classeFundo = isAlert ? 'bg-danger' : 'bg-success';
    let icone = `<i class="bi ${isAlert ? 'bi-exclamation-triangle' : 'bi-info-circle'}"></i>`;
    let tituloHTML = `${icone} ${titulo}`;

    if (!isNaN(message)) {
        msg_id = msg_cfg[parseInt(message) - 1];
        if (msg_id) {
            titulo = msg_id.msg_titulo || titulo;
            corpo = msg_id.msg_mensagem || corpo;
            tituloHTML = msg_id.msg_desc_tipo || `${icone} ${titulo}`;
            if (msg_id.msg_cor) {
                if (msg_id.msg_cor.startsWith('#')) {
                    fundo = msg_id.msg_cor;
                    classeFundo = '';
                } else {
                    classeFundo = msg_id.msg_cor;
                    fundo = null; // será determinado via CSS real
                }
            }
        }
    }

    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center ${classeFundo}`.trim();

    // Aplicar contraste adequado ao texto
    let textoCor = 'white';
    if (fundo && fundo.startsWith('#')) {
        toast.style.backgroundColor = fundo;
        textoCor = getContrastYIQ(fundo);
        toast.style.color = textoCor;
    } else if (classeFundo) {
        // cria elemento temporário para obter a cor real
        const temp = document.createElement('div');
        temp.className = classeFundo;
        temp.style.display = 'none';
        document.body.appendChild(temp);

        const computed = getComputedStyle(temp).backgroundColor;
        const hex = rgbToHex(computed);
        textoCor = getContrastYIQ(hex);
        toast.classList.add(`text-${textoCor}`);
        document.body.removeChild(temp);
    }

    // Cabeçalho
    const header = document.createElement('div');
    header.className = 'toast-header';
    header.style.color = fundo; // título sempre preto

    const title = document.createElement('strong');
    title.className = 'me-auto';
    title.innerHTML = tituloHTML;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-close';
    closeBtn.style.color = 'black';
    closeBtn.setAttribute('aria-label', 'Fechar');

    header.appendChild(title);
    header.appendChild(closeBtn);

    // Corpo
    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = corpo;

    toast.appendChild(header);
    toast.appendChild(body);
    container.appendChild(toast);

    // Força reflow e ativa a animação
    void toast.offsetHeight;
    toast.classList.add('toast-animar');

    const timeout = setTimeout(() => {
        toast.classList.remove('toast-animar');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);

    closeBtn.addEventListener('click', () => {
        clearTimeout(timeout);
        toast.classList.remove('toast-animar');
        toast.addEventListener('transitionend', () => toast.remove());
    });
}

/**
 * bloqueiaTela
 * Bloqueia a tela e apresenta a msg Processando...
 */
function bloqueiaTela() {
    var displayValor = jQuery('#bloqueiaTela').css('display');

    if (displayValor == 'none') {
        jQuery('#bloqueiaTela').css('display', 'block');
        jQuery('#bloqueiaTela')[0].offsetHeight;
    }
    // setTimeout(console.log('Aguardando'), 1000);
};;

/**
 * desBloqueiaTela
 * Desbloqueia a Tela
 */
function desBloqueiaTela() {
    if (!executandoAjax) {
        jQuery('#bloqueiaTela').css('display', 'none');
    }
};;

/** Função nova
 *  Não faz nada é só pra exemplificar
 */
function naofaznada() {

};;

/**
 * submeter
 * submete um form, para um destino
 */
function submeter(destino) {
    jQuery("[id*='-valid']").addClass('d-none');
    jQuery("[id*='-fival']").removeClass('d-block');
    var form = jQuery('#form1')[0];

    form.classList.add('was-validated');
    isvalido = validador(form);
    if (isvalido) {
        jQuery('#form1').attr("action", destino);
        jQuery('#form1').trigger('submit');
    } else {
        event.preventDefault();
        event.stopPropagation();
    }
};;

/**
 * redireciona
 * Redireciona a execução para a URL fornecida
 * @param {string} url - URL de destino
 */
function redireciona(url) {
    window.location.assign(url);
};;

/**
 * redirec_blank
 * Abre a URL fornecida numa nova Janela
 * @param {string} url - URL de destino
 */
function redirec_blank(url) {
    window.open(url, '_blank');
};;

/**
 * retorna_listagem
 * Retorna para a a Listagem do Controler
 *
 * 
 */
function retorna_listagem() {
    var arr = location.href.split('/');
    var controler = arr[0] + '//' + arr[2] + '/' + arr[3];
    location.href = controler;
};;

/**
 * salva página atual
 * Salva a página atual no cookie, para retornar para a mesma página, quando entrar
 * 
 */
function salvaPagina() {
    var arr = location.href.split('/');
    // var controler = arr[0] + '//' + arr[2] + '/' + arr[3];
    var controler = arr[3];
    var usuario = jQuery('#usu_id').val();
    setCookie('paginausuario', usuario + ',' + controler);
};;

/**
 * retorna_url
 * Retorna para a URL anterior
 *
 * @param {int} tipo - Se for igual a 1, volta para a URL anterior gravada
 * 
 */
function retorna_url(tipo = 1) {
    window.history.back();

    // if (tipo == 1) {
    //     location.href = document.referrer;
    // } else if (tipo == 2) {
    //     location.href = history.back();
    // }
};;

/**
 * openUrlDiv
 * Abre a URL fornecida, dentro da Div informada
 * @param {string} url - URL de destino
 * @param {string} div - nome da Div de destino
 */
function openUrlDiv(url, div) {
    retornoAjax = false;
    executaAjax(url, 'html', '');
    if (retornoAjax) {
        jQuery("#" + div).html(retorno);
    }
    // jQuery.ajax({
    //     type: 'POST',
    //     async: true,
    //     dataType: 'html',
    //     url: url,
    //     success: function (retorno) {
    //         jQuery("#" + div).html(retorno);
    //     },
    //     error: function (xhr, ajaxOptions, thrownError) {
    //         retorno = xhr.statusText;
    //         boxAlert(retorno, true, '', false, 2, false);
    //     }
    // });

};;

/**
 * openOpcMenu
 * Abre a URL fornecida, dentro da Div informada
 * @param {string} url - URL de destino
 * @param {string} div - nome da Div de destino
 */
function openOpcMenu(url) {
    retornoAjax = false;
    executaAjax(url, 'html', '');
    if (retornoAjax) {
        jQuery("#" + div).html(retorno);
    }
    // jQuery.ajax({
    //     type: 'POST',
    //     async: true,
    //     dataType: 'html',
    //     url: url,
    //     success: function (retorno) {

    //         jQuery("#" + div).html(retorno);
    //     },
    //     error: function (xhr, ajaxOptions, thrownError) {
    //         retorno = xhr.statusText;
    //         boxAlert(retorno, true, '', false, 2, false);
    //     }
    // });

};;

/**
 * openPDFModal
 * Abre o PDF informado na URL, numa Janela Modal
 * @param {string} url - URL do PDF
 * @param {string} titulo - titulo da Janela Modal
 */
async function openPDFModal(url, titulo = false) {
    const retornoAjax = await executaAjaxWait(url, 'json');
    if (retornoAjax) {
        if (titulo) {
            jQuery("#myModalLabel").html(titulo);
        }
        let pdfBase64 = retornoAjax.pdf;
        let pdfretornado = "<embed src='data:application/pdf;base64," + pdfBase64 + "' type='application/pdf' width='100%' height='500px'>";


        jQuery("#modal-body").html(pdfretornado);

        // Selecionando o container do modal
        const container = document.querySelector('#myModal'); // Supondo que 'myModal' seja o ID do seu modal

        // Criando o modal do Bootstrap
        const modal = new bootstrap.Modal(container);

        // Adicionando a classe 'resize' ao corpo do modal
        container.querySelector('.modal-content').classList.add('resize');

        modal.show();
    }
};;

/**
 * openModal
 * Abre a URL informada, numa Janela Modal
 * @param {string} url - URL 
 * @param {string} titulo - titulo da Janela Modal
 */
async function openModal(url, titulo = false) {
    const retornoAjax = await executaAjaxWait(url, 'html');
    if (retornoAjax) {
        if (titulo) {
            jQuery('.modal-title').html(titulo);
        }
        jQuery('.modal-body').html(retornoAjax);
        var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
        // document.onreadystatechange = function () {
        myModal.show();
    }
    // jQuery.ajax({
    //     url: url,
    //     dataType: 'html',
    //     success: function (data) {
    //         jQuery('.modal-body').html(data);
    //         var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
    //         // document.onreadystatechange = function () {
    //         myModal.show();
    //         //   myModal.addEventListener('shown.bs.modal', () => {
    //         //     myInput.focus()
    //         //   })              
    //         // };
    //     }
    // });
};;

/**
 * openImgDiv
 * Abre a Imagem fornecida, dentro da Div informada
 * @param {string} Imagem - URL da imagem de destino
 * @param {string} div - nome da Div de destino
 */
function openImgDiv(url, div) {
    text = '';
    text += "<div id='" + div + "' onclick='removeDiv(\"" + div + "\")'>";
    text += "<div class='d-block m-auto h-100 w-100 opacity-50 bg-gray-padrao position-absolute ' style='z-index: 200; top:0' >";
    text += "</div>";
    text += "<div class='d-block m-auto h-75 w-75 border border-2 border-info rounded-3 shadow bg-white position-relative m-auto align-itens-center' style='z-index: 300;'>";
    text += "<img src='" + url + "' class='d-block position-relative m-auto' style='max-height: 40rem;' />";
    text += "</div>";
    text += "</div>";
    jQuery('body').append(text);
};;

/**
 * removeDiv
 * remove a Div Aberta
 * @param {string} div - nome da Div de destino
 */
function removeDiv(div) {
    jQuery('#' + div).remove();
};;


/**
 * toogle_div
 * Mostra ou esconde uma Div
 *
 * @param {string} iddiv - id da Div
 */
function toogle_div(iddiv) {
    jQuery('#' + iddiv).toggleClass('d-none');
};;

/**
 * excluir
 * Apresenta o box de alerta de exclusão, e caso confirmado, exclui o registro
 * 
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */

function excluir(url, registro) {
    msg = "Confirma a Exclusão do Registro?<br><h5 class='text-center'>" + registro + "</h5>";
    boxAlert(1, false, url, false, 4, true);
};;

/**
 * ativInativ
 * Apresenta o box de alerta de Ativação/Inativação, e caso confirmado, ativa ou inativa o registro
 * 
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */

function ativInativ(url, registro, ativo) {
    if (ativo) {
        msg = "Confirma a Inativação do Registro?<br><h5 class='text-center'>" + registro + "</h5>";
        tit = "Inativação de Registro";
        msg = 4;
    } else {
        msg = "Confirma a Ativação do Registro?<br><h5 class='text-center'>" + registro + "</h5>";
        tit = "Ativação de Registro";
        msg = 16;
    }
    boxAlert(msg, false, url, false, 4, true, tit);
};;

/**
 * boxAlert
 * Cria um box de Alerta para o Usuário
 * Se for confirmação de exclusão, a url deve ser vazia e o box retornará a opção escolhida (Sim/Não)
 *
 * @param {string} msg    - Mensagem que será exibida
 * @param {string} titulo    - Título do Alerta
 * @param {boolean} erro  - verdadeiro, caso seja uma mensagem de ERRO
 * @param {url}     url   - url que será executada no callback
 * @param {boolean} close - verdadeiro, Mostra o botão para fechar o Alerta
 * @param {integer} tipo  - Determina as características do Box 1 = Atenção, 2 = Informação
 * @param {boolean} confirma  - verdadeiro, Mostra os botões Sim ou Não para Confirmação
 */
function boxAlert(msg, erro = false, url, close = false, tipo = 1, confirma = false, titulo = '') {
    if (!isNaN(msg)) {//mensagem é um número
        // busca os dados da  mensagem no array de mensagens
        msg_id = msg_cfg[msg - 1];
        msg = msg_id.msg_mensagem;
        tipo = msg_id.msg_tipo;
        if (tipo == 'P') {
            titulo = '<h3><i class="fa-solid fa-circle-question fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span></h3>';
            confirma = true;
        } else if (tipo == 'A') {
            titulo = '<h3><i class="fa-solid fa-circle-exclamation fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span></h3>';
            tipo = 1;
        } else if (tipo == 'E') {
            titulo = '<h3><i class="fa-solid fa-circle-xmark fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span></h3>';
            erro = true;
        } else if (tipo == 'I') {
            titulo = '<h3><i class="fa-solid fa-circle-info fa-lg"></i><span class="mx-2">' + msg_id.msg_titulo + '</span></h3>';
        }
        cor = msg_id.msg_cor;
    } else {
        if (tipo == 1) { // ATENÇÃO
            titulo = '<h3><i class="fas fa-exclamation-triangle"></i><span class="mx-2">Atenção</span></h3>';
            tipo = 'A';
            cor = "bg-warning";
        } else if (tipo == 2) { // INFORMAÇÃO
            titulo = '<h3><i class="fas fa-info-circle"></i><span class="mx-2">Informação</span></h3>';
            tipo = 'I';
            cor = "bg-success";
        } else if (tipo == 3) { // ERRO
            titulo = '<h3><i class="fas fa-circle-xmark"></i><span class="mx-2">ERRO!</span></h3>';
            tipo = 'E';
            cor = "bg-danger";
        }
    }
    if (!confirma) {
        bootbox.alert({
            title: titulo,
            message: "<div class='text-center'><h5>" + msg + "</h5></div>",
            centerVertical: true,
            closeButton: close,
            size: 'large',
            className: 'rubberBand animated',
            callback: function () {
                if (!erro) {
                    redireciona(url);
                }
            }
        });
    } else {
        if (titulo == '') {
            titulo = '<h3><i class="fas fa-question-circle"></i><span class="mx-2">Exclusão de Registro</span></h3>';
        }
        bootbox.confirm({
            title: titulo,
            message: "<div class='text-center'><h5>" + msg + "</h5></div>",
            centerVertical: true,
            size: 'large',
            closeButton: false,
            swapButtonOrder: true,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Não',
                    className: 'btn-secondary',
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Sim',
                    className: 'btn-danger ',
                },
            },
            callback: function (result) {
                if (result) {
                    if (url == 'submit') {
                        jQuery('#form1').trigger('submit');
                    } else {
                        if (url.indexOf('location') > -1) {
                            eval(url);
                        } else if (url.indexOf('back') > -1) {
                            window.history.back();
                        } else {
                            if (tipo == 'P') {
                                retornoAjax = false;
                                executaAjax(url, 'json', '');
                                if (retornoAjax) {
                                    if (retornoAjax.erro) {
                                        boxAlert(retornoAjax.msg, true, '', false, 1, false);
                                    } else {
                                        var arr = location.href.split('/');
                                        var controler = arr[0] + '//' + arr[2] + '/' + arr[3];
                                        location.href = controler;
                                    }
                                }
                            } else {
                                redireciona(url);
                            }
                        }
                    }
                }
            }
        });
    }
    jQuery('.modal-header').addClass(cor);
    if (tipo == 'A') { // ATENÇÃO
        jQuery('.modal-header').css("color", "black");
    } else if (tipo == 'I') { // INFORMAÇÃO
        // jQuery('.modal-header').css("background-color", "green");
        jQuery('.modal-header').css("color", "white");
    } else if (tipo == 'E') { // ERRO
        // jQuery('.modal-header').css("background-color", "orange");
        jQuery('.modal-header').css("color", "yellow");
    } else if (tipo == 'P') { // PERGUNTA
        // jQuery('.modal-header').css("background-color", "orange");
        jQuery('.modal-header').css("color", "black");
    }
};;

/**
 * abre_tab
 * abre a Url fornecida como parametro, via Ajax, na Tab fornecida
 * 
 * @param {url} url    - 
 * @param {string} tab    - 
 * @param {string} tipo    - para o caso de etapas
 */

function abre_tab(url, tab, tipo) {
    retornoAjax = false;
    executaAjax(url, 'json', '');
    if (retornoAjax) {
        ulprin = jQuery('#tabPrincipal');
        liprin = ulprin.find('li');
        if (tipo == 'etapa') {
            tabpos = liprin[0].id.substring(3);
            jQuery("#li-" + tabpos).id = 'li-' + tab;
            jQuery("#but-" + tabpos).id = 'but-' + tab;
            jQuery("#tab-" + tabpos).id = 'tab-' + tab;
            jQuery("#tab-" + tab).html(retorno);
        } else {
            if (jQuery("#li-" + tab).length) {
                jQuery("#tab-" + tab).html(retorno);
            } else {
                textli = "<li id='li=" + retorno.dados.url_amiga + "' class='nav-item' role='presentation'>"
                textli += "    <button class='nav-link active' id='but-" + retorno.dados.url_amiga + "' data-bs-toggle='tab' data-bs-target='#tab-" + retorno.dados.url_amiga + "' type='button' role='tab' aria-controls='tab-" + retorno.dados.url_amiga + "' aria-selected='true'>";
                textli += "    <i class='far fa-hand-point-right'></i>";
                textli += "        &nbsp;-&nbsp;" + retorno.dados.title;
                textli += "    </button>";
                textli += "</li>";
                jQuery("#tabPrincipal").append(textli);
                textpn = "<div id='tab-" + retorno.dados.url_amiga + "' class='tab-pane-prin fade p-lg-3 p-2 active show' role='tabpanel' aria-labelledby='tab-" + retorno.dados.url_amiga + "' tabindex='0'>";
                textpn += retorno.html;
                textpn += "</div>";
                jQuery("#principal").append(textpn);
            }
        }
    }

    // ulprin = jQuery('#tabPrincipal');
    // liprin = ulprin.find('li');
    // jQuery.ajax({
    //     type: 'POST',
    //     async: true,
    //     dataType: 'json',
    //     url: url,
    //     success: function (retorno) {
    //         if (tipo == 'etapa') {
    //             tabpos = liprin[0].id.substring(3);
    //             jQuery("#li-" + tabpos).id = 'li-' + tab;
    //             jQuery("#but-" + tabpos).id = 'but-' + tab;
    //             jQuery("#tab-" + tabpos).id = 'tab-' + tab;
    //             jQuery("#tab-" + tab).html(retorno);
    //         } else {
    //             if (jQuery("#li-" + tab).length) {
    //                 jQuery("#tab-" + tab).html(retorno);
    //             } else {
    //                 textli = "<li id='li=" + retorno.dados.url_amiga + "' class='nav-item' role='presentation'>"
    //                 textli += "    <button class='nav-link active' id='but-" + retorno.dados.url_amiga + "' data-bs-toggle='tab' data-bs-target='#tab-" + retorno.dados.url_amiga + "' type='button' role='tab' aria-controls='tab-" + retorno.dados.url_amiga + "' aria-selected='true'>";
    //                 textli += "    <i class='far fa-hand-point-right'></i>";
    //                 textli += "        &nbsp;-&nbsp;" + retorno.dados.title;
    //                 textli += "    </button>";
    //                 textli += "</li>";
    //                 jQuery("#tabPrincipal").append(textli);
    //                 textpn = "<div id='tab-" + retorno.dados.url_amiga + "' class='tab-pane-prin fade p-lg-3 p-2 active show' role='tabpanel' aria-labelledby='tab-" + retorno.dados.url_amiga + "' tabindex='0'>";
    //                 textpn += retorno.html;
    //                 textpn += "</div>";
    //                 jQuery("#principal").append(textpn);
    //             }
    //         }
    //     },
    //     error: function (xhr, ajaxOptions, thrownError) {
    //         retorno = xhr.statusText;
    //         boxAlert(retorno, true, '', false, 2, false);
    //     }
    // });
}


/**
 * removerTags
 * Remove os Tags Html do texto fornecido como parâmetro
 * 
 * @param {string} html    - Texto original
 * @return {string} texto sem tags
 */
function removerTags(html) {
    const data = new DOMParser().parseFromString(html, 'text/html');
    return data.body.textContent || "";
}


/**
 * tiraAcentos
 * Remove os Acentos do texto fornecido como parâmetro
 * 
 * @param {string} txt    - Texto original
 * @return {string} texto sem acentos
 */
function tiraAcentos(txt) {
    if (txt != null && txt != '' && txt != undefined) {
        var i = txt.toLowerCase().trim();

        var acentos = "ãáàâäéèêëíìîïõóòôöúùûüç";
        var sem_acentos = "aaaaaeeeeiiiiooooouuuuc";

        for (var x = 0; x < i.length; x++) {
            var str_pos = acentos.indexOf(i.substr(x, 1));
            if (str_pos != -1) {
                i = i.replace(acentos.charAt(str_pos), sem_acentos.charAt(str_pos));
            }
        }

        return i;
    } else {
        return txt;
    }
};;


/**
 * Cancelar
 * Cancela a Edição de Dados atual e retorna para a tela de listagem
 * Caso existam informações não Salvas mostra o Box de Confirmação do cancelamento
 * 
 */
function cancelar() {
    var arr = location.href.split('/');
    var controler = arr[0] + '//' + arr[2] + '/' + arr[3];
    var elemAlterado = false;
    var form = jQuery("#form1");

    // elemAlterado = Boolean(form[0].getAttribute('data-alter'));
    if (form[0].getAttribute('data-alter') === true || form[0].getAttribute('data-alter') === "true") {
        elemAlterado = true;
    };

    if (elemAlterado) {
        // msg = "Algumas informações desta página, ainda não foram salvas!!! < br > Confirmando a saída, essas informações serão perdidas definitivamente! < br > <br>Confirma Saída?";
        msg = 2;
        boxAlert(msg, false, "back", false, 3, true, 'Registro Alterado');
    } else {
        window.history.back();
    }
};;


function setCookie(name, value, path = '/') {
    var d = new Date();
    var year = d.getFullYear();
    var month = d.getMonth();
    var day = d.getDate();
    var duration = new Date(year + 1, month, day);

    var cookie = name + "=" + escape(value) +
        ((duration) ? "; expires=" + duration.toGMTString() : "") + ";path=/";

    document.cookie = cookie;
}

function getCookie(name) {
    let cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        // Verifica se o cookie começa com o nome desejado
        if (cookie.indexOf(name + '=') === 0) {
            return cookie.substring((name + '=').length, cookie.length);
        }
    }
    return null; // Retorna null se não encontrar o cookie

    // var cookies = document.cookie;
    // var prefix = name + "=";
    // var begin = cookies.indexOf("; " + prefix);

    // if (begin == -1) {

    //     begin = cookies.indexOf(prefix);

    //     if (begin != 0) {
    //         return null;
    //     }

    // } else {
    //     begin += 2;
    // }

    // var end = cookies.indexOf(";", begin);

    // if (end == -1) {
    //     end = cookies.length;
    // }

    // return unescape(cookies.substring(begin + prefix.length, end));
}

function deleteCookie(name) {
    if (getCookie(name)) {
        document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
}

function mostranoToast(texto, alerta = false) {
    if (texto != '') {
        showToast(texto, alerta);
    };
    showNotification(texto);
}

let processandoTimeout = null;

function mostranorodape(texto) {
    jQuery('#msgprocessando').html(' Processando...<br>' + texto);
    jQuery('#msgrodape').html(texto);
    // Cancela o timeout anterior, se existir
    if (typeof processandoTimeout === "number") {
        clearTimeout(processandoTimeout);
    }

    // Cria um novo timeout
    processandoTimeout = setTimeout(function () {
        jQuery('#msgrodape').html('');
        jQuery('#msgprocessando').html(' Processando..');
    }, 1500);
}

async function permiteNotificacao() {
    if (!('Notification' in window)) {
        console.warn('Este navegador não suporta notificações.');
        return false;
    }

    if (Notification.permission === 'granted') {
        return true;
    }

    if (Notification.permission === 'denied') {
        console.warn('As notificações estão bloqueadas pelo usuário.');
        return false;
    }

    try {
        const permissao = await Notification.requestPermission();
        if (permissao === 'granted') {
            return true;
        } else {
            console.log('O usuário negou a permissão de notificações.');
            return false;
        }
    } catch (erro) {
        console.error('Erro ao solicitar permissão de notificações:', erro);
        return false;
    }
}

async function showNotification(texto) {
    const permitido = await permiteNotificacao();

    if (permitido) {
        const options = {
            body: texto,
            dir: 'ltr',
            icon: '/assets/images/alert.png'
        };

        new Notification('Nova Notificação', options);
    }
}


function verificaNotificacao() {
    var usuarioatual = jQuery('#usu_id').val();
    if (usuarioatual != '') {
        var url = '/Notifica/verNotifica';
        let dados = { usuario: usuarioatual };
        retornoAjax = false;
        executaAjax(url, 'json', dados);
        if (retornoAjax) {
            if (retornoAjax.novo > 0) {
                jQuery('#bt_notifica').removeClass('btn-outline-light');
                jQuery('#bt_notifica').removeClass('disabled');
                jQuery('#bt_notifica').addClass('btn-outline-warning');

                jQuery('#badgenotif').removeClass('d-none');

                jQuery('#show_notifica').html(retornoAjax.html);
                jQuery('#badgenotif').html(retornoAjax.novo);
            } else {
                jQuery('#bt_notifica').removeClass('btn-outline-warning');

                jQuery('#bt_notifica').addClass('btn-outline-light');
                jQuery('#bt_notifica').addClass('disabled');

                jQuery('#badgenotif').addClass('d-none');

                jQuery('#show_notifica').html('');
                jQuery('#badgenotif').html('');
                // jQuery('#show_notifica').removeClass('show');
            }
        }
    }
}

function viuNotifica($id) {
    var url = '/Notifica/viuNotifica';
    var dados = { id: $id };
    retornoAjax = false;
    executaAjax(url, 'json', dados);
    if (retornoAjax) {
        verificaNotificacao();
    }
}

// function validador(form) {
//     if (!form.checkValidity()) {
//         jQuery('.was-validated :invalid').each(function (index) {
//             nid = jQuery(this)[0].id;
//             jQuery("[id='" + nid + "-fival']").removeClass('d-none');
//             jQuery("[id='" + nid + "-fival']").addClass('d-block');
//             tab = jQuery(this)[0].closest('.tab-pane').id;
//             jQuery("[id='" + tab + "-valid']").removeClass('d-none');
//             if (this.validity.badInput) {
//                 jQuery("[id='" + nid + "-fival']").html("Valor inserido é inválido");
//             }
//             if (this.validity.patternMismatch) {
//                 jQuery("[id='" + nid + "-fival']").html("Valor não corresponde ao formato esperado");
//             }
//             if (this.validity.rangeOverflow) {
//                 nome = this.title;
//                 lmax = this.max;
//                 jQuery("[id='" + nid + "-fival']").html("Valor máximo permitido é " + lmax);
//             }
//             if (this.validity.rangeUnderflow) {
//                 nome = this.title;
//                 lmin = this.min;
//                 jQuery("[id='" + nid + "-fival']").html("Valor mínimo permitido é " + lmin);
//             }
//             if (this.validity.stepMismatch) {
//                 jQuery("[id='" + nid + "-fival']").html("Valor não é permitido para este intervalo.");
//             }
//             if (this.validity.tooLong) {
//                 nome = this.title;
//                 maxl = this.maxLength;
//                 jQuery("[id='" + nid + "-fival']").html("O campo " + nome + ", aceita no máximo " + maxl + " caracteres");
//             }
//             if (this.validity.tooShort) {
//                 nome = this.title;
//                 minl = this.minLength;
//                 jQuery("[id='" + nid + "-fival']").html('Digite pelo menos ' + minl + ' caracteres para ' + nome);
//             }
//             if (this.validity.typeMismatch) {
//                 jQuery("[id='" + nid + "-fival']").html("Valor não corresponde ao tipo esperado");
//             }
//             if (this.validity.valueMissing) {
//                 nome = this.title;
//                 jQuery("[id='" + nid + "-fival']").html(nome + ' é Obrigatório!');
//             }
//         });
//         valido = false;
//     } else {
//         valido = true;
//         jQuery('.was-validated :valid').each(function (index) {
//             if (jQuery(this).is("select")) {
//                 if (jQuery(this).prop('required')) {
//                     if (jQuery(this).val() <= 0 ||
//                         jQuery(this).val() == '' ||
//                         jQuery(this).val() == null) {
//                         this.selectedIndex = -1;
//                         valido = false;
//                         validador(form);
//                         return false;
//                     }
//                 }
//             }
//             if (jQuery(this).is("number")) {
//                 if (jQuery(this).prop('required')) {
//                     if (jQuery(this).val() < 0 ||
//                         jQuery(this).val() == '' ||
//                         jQuery(this).val() == null) {
//                         valido = false;
//                         validador(form);
//                         return false;
//                     }
//                 }
//             }
//         });
//     }
//     return valido;
// }
function validador(form) {
    let valido = true;

    if (!form.checkValidity()) {
        jQuery('.was-validated :invalid').each(function () {
            const el = this;
            const $el = jQuery(el);
            const nid = escIdColchetes(el.id);
            const tab = escIdColchetes(el.closest('.tab-pane')?.id);
            const $erro = jQuery("#" + nid + "-fival");
            const $tabVal = jQuery("#" + tab + "-valid");

            $erro.removeClass('d-none').addClass('d-block');
            if ($tabVal.length) $tabVal.removeClass('d-none');

            const nome = el.title || '';
            let mensagens = [];

            if (el.validity.badInput) {
                mensagens.push("Valor inserido é inválido");
            }
            if (el.validity.patternMismatch) {
                mensagens.push("Valor não corresponde ao formato esperado");
            }
            if (el.validity.rangeOverflow) {
                mensagens.push("Valor máximo permitido é " + el.max);
            }
            if (el.validity.rangeUnderflow) {
                mensagens.push("Valor mínimo permitido é " + el.min);
            }
            if (el.validity.stepMismatch) {
                mensagens.push("Valor não é permitido para este intervalo.");
            }
            if (el.validity.tooLong) {
                mensagens.push("O campo " + nome + ", aceita no máximo " + el.maxLength + " caracteres");
            }
            if (el.validity.tooShort) {
                mensagens.push("Digite pelo menos " + el.minLength + " caracteres para " + nome);
            }
            if (el.validity.typeMismatch) {
                mensagens.push("Valor não corresponde ao tipo esperado");
            }
            if (el.validity.valueMissing) {
                mensagens.push(nome + " é Obrigatório!");
            }

            // Mostra todas as mensagens
            const mensagemFinal = mensagens.join('<br>');
            $erro.html(mensagemFinal);
            mensagens.forEach(msg => mostranoToast(msg, true));

            valido = false;
        });
    } else {
        jQuery('.was-validated :valid').each(function () {
            const $el = jQuery(this);
            const val = $el.val();

            if ($el.is("select") && $el.prop('required')) {
                if (val <= 0 || val == '' || val == null) {
                    this.selectedIndex = -1;
                    valido = false;
                    return false;
                }
            }

            if ($el.is("number") && $el.prop('required')) {
                if (val < 0 || val == '' || val == null) {
                    valido = false;
                    return false;
                }
            }
        });

        if (!valido) validador(form);
    }

    return valido;
}

/**
 * executaAjax
 * Executa uma requisição ajax, e retorna o resultado pra quem chamou, ou executa a função informada
 * @param {string} urldest - URL de destino
 * @param {string} tipo - Tipo de Retorno (html, json)
 * @param {array} dados - dados a serem informados
 * @return {Object} retorno
 */
function executaAjax(urldest, tipo = 'json', dados = {}, funcao = '') {
    bloqueiaTela();
    var options = {};
    if (dados.entries != undefined) {
        options = {
            method: 'POST',
            url: urldest,
            data: dados,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            processData: false,
            contentType: false,
            async: false,
            dataType: tipo,
            beforeSend: function () {
                bloqueiaTela();
            },
            complete: function () {
                desBloqueiaTela();
            }
        }
    } else {
        options = {
            method: 'POST',
            cache: false,
            url: urldest,
            data: dados,
            async: false,
            dataType: tipo,
            beforeSend: function () {
                bloqueiaTela();
            },
            complete: function () {
                desBloqueiaTela();
            }
        };
    }
    var request = jQuery.ajax(options);

    request.done(function (retorno) {
        retornoAjax = retorno;
        if (typeof window[funcao] === "function") {
            window[funcao];
        }
    });
    request.fail(function (jqXHR, textStatus) {
        retornoAjax = false;
        if (jqXHR.responseJSON != undefined) {
            msgerro = 'Erro <br> Código ' + jqXHR.responseJSON.code +
                '<br> Arquivo: ' + jqXHR.responseJSON.file +
                '<br> Linha: ' + jqXHR.responseJSON.line +
                '<br> Mensagem: ' + jqXHR.responseJSON.message;
        } else {
            msgerro = jqXHR.responseText;
        }
        boxAlert(msgerro, true, '', false, 3, false);
    });
    return retornoAjax;
};;

/**
 * executaAjax
 * Executa uma requisição ajax, e retorna o resultado pra quem chamou, ou executa a função informada
 * @param {string} urldest - URL de destino
 * @param {string} tipo - Tipo de Retorno (html, json)
 * @param {array} dados - dados a serem informados
 * @return {Object} retorno
 */
function executaAjaxWait(urldest, tipo = 'json', dados = {}, funcao = '') {
    executandoAjax = true;
    // Bloqueia a tela
    bloqueiaTela();

    return new Promise((resolve, reject) => {
        jQuery.ajax({
            method: 'POST',
            cache: false,
            url: urldest,
            data: dados,
            dataType: tipo,
            success: function (retorno) {
                resolve(retorno);
            },
            error: function (jqXHR, textStatus) {
                let msgerro;
                if (jqXHR.responseJSON !== undefined) {
                    msgerro = 'Erro <br> Código ' + jqXHR.responseJSON.code +
                        '<br> Arquivo: ' + jqXHR.responseJSON.file +
                        '<br> Linha: ' + jqXHR.responseJSON.line +
                        '<br> Mensagem: ' + jqXHR.responseJSON.message;
                } else {
                    msgerro = jqXHR.responseText;
                }
                boxAlert(msgerro, true, '', false, 3, false);
                reject(false);
            },
            complete: function () {
                // Desbloqueia a tela após a conclusão
                executandoAjax = false;
                desBloqueiaTela();
            }
        });
    });
};;