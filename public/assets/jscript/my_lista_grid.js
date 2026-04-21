var table;

function montaListaDadosGrid(tabela, url) {
  const container = document.getElementById(tabela);
  container.innerHTML = "";

  table = new gridjs.Grid({
    columns: colunas.map((c) => ({
      name: c,
    })),

    server: {
      url: url,
      method: "POST",
      then: (data) => data.data,
    },

    pagination: {
      limit: 50,
    },

    search: true,
    sort: true,

    style: {
      table: {
        "white-space": "nowrap",
      },
    },
  }).render(container);
}
