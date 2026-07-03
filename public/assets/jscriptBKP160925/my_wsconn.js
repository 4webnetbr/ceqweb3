var conn;
var contador = 0;

jQuery(document).ready(function () {
    conectaWs();

    function conectaWs() {
        conn = new WebSocket('wss://ceqweb3.ceqnep.com.br/ws');
        conn.onopen = function (e) {
            console.log("Conexão estabelecida com o Servidor");
            jQuery("#stat_server").addClass("text-success");
            jQuery("#stat_server").removeClass("text-danger");
        };
        // keepAlive();
    }

    function keepAlive() {
        timerId = setInterval(function () {
            var data = JSON.stringify({ 'msg': 'Ativo', 'tipo': 'Ativo' });
            if (conn.readyState != WebSocket.OPEN) {
                // Fecha e reconecta
                conn.close();
            } else {
                contador++;
                console.log("Ativo " + contador);
                console.log("Timer " + timerId);
                conn.send(data);
            }
        }, 30000);
    }

    conn.onmessage = function (e) {
        // console.log(e.data);
        // clearTimeout(timerId);
        var data = JSON.parse(e.data)
        console.log('Recebi ' + data.tipo + ' - ' + data.msg);
        if (data.tipo == 'Entrou' || data.tipo == 'Saiu') {
            mostranoToast(data.msg);
        } else if (data.tipo == 'Ativo') {
        } else if (data.tipo == 'Servidor Ativo') {
            var data = JSON.stringify({ 'msg': 'ok', 'tipo': 'Ativo' });
            conn.send(data);
        } else if (data.tipo == 'Servidor') {
            contador = 0;
            usuario = jQuery('#usu_id').val();
            if (data.usuario == usuario) {
                mostranoToast(data.msg); // isso vai mudar
                verificaNotificacao();
            }
        } else if (data.tipo == 'MsgServer') {
            contador = 0;
            usuario = jQuery('#usu_id').val();
            if (data.usuario == usuario) {
                mostranorodape(data.msg); // isso vai mudar
            }
        } else if (data.tipo == 'Login') {
            mostranoToast(data.msg);
        }
        // keepAlive();
    };

    conn.onclose = function (e) {
        jQuery("#stat_server").removeClass("text-success");
        jQuery("#stat_server").addClass("text-danger");
        jQuery("#stat_server").prop('title', 'Servidor Desconectado');
        console.log('Fechou Conexão');
        executa_php();
        conectaWs();
    }

    conn.onerror = function (err) {
        console.error('Socket encountered error: ', err.message, 'Closing socket');
    };

});;

