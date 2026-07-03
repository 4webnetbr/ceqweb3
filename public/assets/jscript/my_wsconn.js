var conn;
var contador = 0;
var timerId = null;

jQuery(document).ready(function () {
  conectaWs();

  function conectaWs() {
    conn = new WebSocket("wss://ceqweb3.ceqnep.com.br/ws");

    conn.onopen = function (e) {
      console.log("Conexão estabelecida com o servidor WS");
      jQuery("#stat_server").removeClass("parado");
      jQuery("#stat_server").prop("title", "Servidor Conectado");

      // registra o usuário no servidor
      var usuId = jQuery("#usu_id").val();
      conn.send(
        JSON.stringify({
          msg: "register",
          tipo: "REGISTER_TAB",
          usuario: parseInt(usuId) || 0,
        }),
      );

      startKeepAlive();
    };

    conn.onmessage = function (e) {
      var data = JSON.parse(e.data);
      var msg = "";
      console.log("Recebi: " + data.tipo + " - " + data.msg);
      if (Array.isArray(data.msg)) {
        msg = data.msg[0];
      } else if (typeof data.msg === "string") {
        msg = data.msg;
      }

      switch (data.tipo) {
        case "Entrou":
        case "Saiu":
        case "Login":
          mostranoToast(msg);
          break;

        case "Ativo":
          // Cliente recebeu o keepalive e não precisa fazer nada
          break;

        case "Servidor Ativo":
          // Servidor mandou ping, cliente responde com "ok"
          conn.send(JSON.stringify({ msg: "ok", tipo: "Ativo" }));
          break;

        case "Servidor":
          contador = 0;
          var usuario = jQuery("#usu_id").val();
          if (data.usuario == usuario) {
            mostranoToast(msg); // ou outra ação
            verificaNotificacao();
          }
          break;

        case "MsgServer":
          contador = 0;
          var usuario = jQuery("#usu_id").val();
          if (data.usuario == usuario) {
            mostranorodape(msg);
          }
          break;

        case "AtualizarControler":
          let path = window.location.pathname;
          let segments = path.split("/").filter(Boolean);
          let lastSegment = segments[segments.length - 1];
          if (segments.length === 1 && lastSegment === msg) {
            jQuery("#table").DataTable().ajax.reload(null, false);
          }
          break;
        case "NovaInspecao":
          verificaInspecao(data.msg[0], data.msg[1], data.msg[2], data.msg[3]);
          desBloqueiaTela();
          break;
        case "NovaOcorrencia":
          verificaOcorrencia(data.msg[0], data.msg[1]);
          desBloqueiaTela();
          break;
      }
    };
    conn.onclose = function (event) {
      console.log("Código:", event.code);
      console.log("Motivo:", event.reason);
      console.log("Fechou limpo?", event.wasClean);
      jQuery("#stat_server").addClass("parado");
      jQuery("#rodape").addClass("parado");
      jQuery("#stat_server").prop("title", "Servidor Desconectado");
      console.log("Conexão fechada. Tentando reconectar...");

      stopKeepAlive();
      //executa_php();

      // Reconnect após um pequeno delay
      setTimeout(() => conectaWs(), 3000);
    };
    conn.onerror = function (event) {
      console.error("Erro WebSocket - type:", event.type);
      console.error("Objeto completo:", event);
    };
  }

  function startKeepAlive() {
    stopKeepAlive(); // Garante que só tenha um timer ativo
    timerId = setInterval(function () {
      if (conn && conn.readyState === WebSocket.OPEN) {
        contador++;
        console.log("Enviando keepalive #" + contador);
        conn.send(JSON.stringify({ msg: "Ativo", tipo: "Ativo" }));
      }
    }, 30000);
  }

  function stopKeepAlive() {
    if (timerId !== null) {
      clearInterval(timerId);
      timerId = null;
    }
  }
});
