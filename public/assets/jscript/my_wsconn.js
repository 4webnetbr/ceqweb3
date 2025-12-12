var conn;
var contador = 0;
var timerId = null;

jQuery(document).ready(function () {
  conectaWs();

  function conectaWs() {
    conn = new WebSocket("wss://ceqweb3.ceqnep.com.br:8443/ws");

    conn.onopen = function (e) {
      console.log("Conexão estabelecida com o servidor WS");
      jQuery("#stat_server")
        .addClass("text-success")
        .removeClass("text-danger");
      jQuery("#stat_server").prop("title", "Servidor Conectado");

      startKeepAlive();
    };

    conn.onmessage = function (e) {
      var data = JSON.parse(e.data);
      console.log("Recebi: " + data.tipo + " - " + data.msg);

      switch (data.tipo) {
        case "Entrou":
        case "Saiu":
        case "Login":
          mostranoToast(data.msg);
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
            mostranoToast(data.msg); // ou outra ação
            verificaNotificacao();
          }
          break;

        case "MsgServer":
          contador = 0;
          var usuario = jQuery("#usu_id").val();
          if (data.usuario == usuario) {
            mostranorodape(data.msg);
          }
          break;

        case "AtualizarControler":
          let path = window.location.pathname;
          let segments = path.split("/").filter(Boolean);
          let lastSegment = segments[segments.length - 1];
          if (segments.length === 1 && lastSegment === data.msg) {
            jQuery("#table").DataTable().ajax.reload(null, false);
          }
          break;
      }
    };

    conn.onclose = function (e) {
      jQuery("#stat_server")
        .removeClass("text-success")
        .addClass("text-danger");
      jQuery("#stat_server").prop("title", "Servidor Desconectado");
      console.log("Conexão fechada. Tentando reconectar...");

      stopKeepAlive();
      //executa_php();

      // Reconnect após um pequeno delay
      setTimeout(() => conectaWs(), 3000);
    };

    conn.onerror = function (err) {
      console.error("Erro WebSocket:", err.message);
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
