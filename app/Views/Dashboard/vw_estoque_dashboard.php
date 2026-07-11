<?= $this->extend('templates/default_template') ?>
<?= $this->section('header'); ?>
<?= view('strut/vw_titulo'); ?>
<?= $this->endSection(); ?>

<?= $this->section('menu'); ?>
<?= view('strut/vw_menu'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div id="content" class="container-fluid page-content bg-light m-0">
  <div class='tab-content bg-white mt-3' id='myTabContent'>
    <div class='tab-pane fade p-lg-3 p-2 show active' id='rellista' role='tabpanel' aria-labelledby='rellista-tab' tabindex='0'>

      <!-- Filtro de período -->
      <div class="card mt-3 mb-3 shadow-sm">
        <div class="card-body py-2">
          <div class="row align-items-center g-2">
            <div class="col-auto">
              <i class="fas fa-filter me-1"></i>
            </div>
            <?php foreach ($campos_filtro as $campo): ?>
              <div class="col-auto"><?= $campo ?></div>
            <?php endforeach; ?>
            <div class="col-auto">
              <span id="dash_loading" class="text-muted small d-none">
                <span class="spinner-border spinner-border-sm me-1"></span>Carregando...
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Linha 1: Curva ABC + Solicitado vs Atendido -->
      <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-chart-bar me-1"></i>Curva ABC de Produtos</span>
              <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_abc','Curva ABC de Produtos')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
            </div>
            <div class="card-body" style="position:relative;height:320px">
              <canvas id="chart_abc"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-balance-scale me-1"></i>Solicitado x Cancelado x Atendido</span>
              <span class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" id="btn_toggle_chart_status_bar" onclick="alternarTipoGrafico('chart_status_bar')" title="Alternar tipo de gráfico"><i class="fas fa-chart-pie"></i></button>
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_status_bar','Solicitado x Cancelado x Atendido')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
              </span>
            </div>
            <div class="card-body" style="position:relative;height:320px">
              <canvas id="chart_status_bar"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Linha 2: Depósito Origem + Depósito Destino -->
      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-warehouse me-1"></i>Por Depósito de Origem</span>
              <span class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" id="btn_toggle_chart_dep_origem" onclick="alternarTipoGrafico('chart_dep_origem')" title="Alternar tipo de gráfico"><i class="fas fa-chart-pie"></i></button>
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_dep_origem','Por Depósito de Origem')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
              </span>
            </div>
            <div class="card-body" style="position:relative;height:280px">
              <canvas id="chart_dep_origem"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-dolly me-1"></i>Por Depósito de Destino</span>
              <span class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" id="btn_toggle_chart_dep_destino" onclick="alternarTipoGrafico('chart_dep_destino')" title="Alternar tipo de gráfico"><i class="fas fa-chart-pie"></i></button>
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_dep_destino','Por Depósito de Destino')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
              </span>
            </div>
            <div class="card-body" style="position:relative;height:280px">
              <canvas id="chart_dep_destino"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Linha 3: Evolução temporal + Status atual -->
      <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-chart-line me-1"></i>Evolução Temporal das Requisições</span>
              <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_evolucao','Evolução Temporal das Requisições')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
            </div>
            <div class="card-body" style="position:relative;height:280px">
              <canvas id="chart_evolucao"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-circle-notch me-1"></i>Requisições por Status</span>
              <span class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" id="btn_toggle_chart_status_donut" onclick="alternarTipoGrafico('chart_status_donut')" title="Alternar tipo de gráfico"><i class="fas fa-chart-bar"></i></button>
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_status_donut','Requisições por Status')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
              </span>
            </div>
            <div class="card-body" style="position:relative;height:280px">
              <canvas id="chart_status_donut"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Linha 4: SLA -->
      <div class="row g-3 mb-3">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between align-items-center">
              <span><i class="fas fa-stopwatch me-1"></i>Tempo Médio de Atendimento por Tipo de Movimentação (horas)</span>
              <span class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" id="btn_toggle_chart_sla" onclick="alternarTipoGrafico('chart_sla')" title="Alternar tipo de gráfico"><i class="fas fa-chart-pie"></i></button>
                <button class="btn btn-sm btn-outline-secondary border-0 py-0 px-1" onclick="abrirFullscreen('chart_sla','Tempo Médio de Atendimento por Tipo de Movimentação')" title="Expandir"><i class="fas fa-expand-alt"></i></button>
              </span>
            </div>
            <div class="card-body" style="position:relative;height:240px">
              <canvas id="chart_sla"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Chart.js -->
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
  (function() {
    'use strict';

    // ── Instâncias dos gráficos ───────────────────────────────────────────────
    var charts = {};
    var chartConfigs = {};
    var chartVariants = {}; // configs alternativas (bar/pie) dos gráficos que suportam troca de tipo
    var chartKind = {}; // tipo atualmente selecionado para cada gráfico alternável

    // ── Paleta de cores fixa para barras/pizza ───────────────────────────────
    var palette = [
      '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
      '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf',
      '#f4b619', '#e02d1b', '#6610f2', '#fd7e14', '#20c997',
      '#0dcaf0', '#d63384', '#198754', '#0d6efd', '#ffc107',
    ];

    function cor(i) {
      return palette[i % palette.length];
    }

    jQuery('#btn_filtrar').on('click', carregarDados);

    // ── Destruir e recriar gráfico ───────────────────────────────────────────
    function criarOuAtualizar(id, config) {
      chartConfigs[id] = config;
      if (charts[id]) {
        charts[id].destroy();
      }
      var ctx = document.getElementById(id);
      if (!ctx) {
        return;
      }
      charts[id] = new Chart(ctx, config);
    }

    // ── Registrar gráfico com suporte a troca de tipo (barra/pizza) ──────────
    function registrarAlternavel(id, variants, kindInicial) {
      chartVariants[id] = variants;
      chartKind[id] = kindInicial;
      criarOuAtualizar(id, variants[kindInicial]);
      atualizarIconeToggle(id);
    }

    function atualizarIconeToggle(id) {
      var btn = document.getElementById('btn_toggle_' + id);
      if (!btn) {
        return;
      }
      var proximo = chartKind[id] === 'bar' ? 'pie' : 'bar';
      btn.innerHTML = '<i class="fas ' + (proximo === 'pie' ? 'fa-chart-pie' : 'fa-chart-bar') + '"></i>';
    }

    // ── Alternar tipo do gráfico (barra <-> pizza) ───────────────────────────
    window.alternarTipoGrafico = function(id) {
      var variants = chartVariants[id];
      if (!variants) {
        return;
      }
      chartKind[id] = chartKind[id] === 'bar' ? 'pie' : 'bar';
      criarOuAtualizar(id, variants[chartKind[id]]);
      atualizarIconeToggle(id);
    };

    // ── Abrir gráfico em janela fullscreen ───────────────────────────────────
    window.abrirFullscreen = function(id, titulo) {
      var variants = chartVariants[id];
      var config = chartConfigs[id];
      if (!config && !variants) {
        alert('Aguarde os dados carregarem antes de expandir.');
        return;
      }

      var kindInicial = chartKind[id] || 'bar';
      var variantsJson = variants ? JSON.stringify(variants) : 'null';
      var configJson = JSON.stringify(variants ? variants[kindInicial] : config);

      var newWin = window.open('', '_blank', 'width=1200,height=750,menubar=no,toolbar=no,status=no');
      newWin.document.write(
        '<!DOCTYPE html><html lang="pt-BR"><head>' +
        '<meta charset="UTF-8"><title>' + titulo + '<\/title>' +
        '<style>' +
        '* { margin:0; padding:0; box-sizing:border-box; }' +
        'body { font-family:sans-serif; background:#fff; display:flex; flex-direction:column; height:100vh; }' +
        'header { padding:10px 16px; font-size:14px; font-weight:600; color:#495057; border-bottom:1px solid #dee2e6; background:#f8f9fa; display:flex; justify-content:space-between; align-items:center; }' +
        'header button { border:1px solid #ced4da; background:#fff; color:#495057; border-radius:4px; padding:4px 10px; cursor:pointer; font-size:13px; }' +
        'header button:hover { background:#e9ecef; }' +
        '#wrap { flex:1; position:relative; padding:20px; }' +
        '<\/style>' +
        '<\/head><body>' +
        '<header><span>' + titulo + '<\/span>' +
        (variants ? '<button id="btnToggle" onclick="alternar()">&#8644; Alternar tipo<\/button>' : '') +
        '<\/header>' +
        '<div id="wrap"><canvas id="c"><\/canvas><\/div>' +
        '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"><\/script>' +
        '<script>' +
        'var variants = ' + variantsJson + ';' +
        'var kind = "' + kindInicial + '";' +
        'var chart;' +
        'function ajustar(cfg) { cfg.options = cfg.options || {}; cfg.options.responsive = true; cfg.options.maintainAspectRatio = false; return cfg; }' +
        'function criar(cfg) { if (chart) { chart.destroy(); } chart = new Chart(document.getElementById("c"), ajustar(cfg)); }' +
        'criar(' + configJson + ');' +
        'function alternar() {' +
        '  kind = kind === "bar" ? "pie" : "bar";' +
        '  criar(variants[kind]);' +
        '}' +
        '<\/script>' +
        '<\/body><\/html>'
      );
      newWin.document.close();
    }

    // ── Mensagem de sem dados ────────────────────────────────────────────────
    function semDados(id, msg) {
      delete chartVariants[id];
      criarOuAtualizar(id, {
        type: 'bar',
        data: {
          labels: [msg || 'Sem dados para o período'],
          datasets: [{
            data: [0]
          }]
        },
        options: {
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              display: false
            }
          }
        },
      });
    }

    // ── Requisição AJAX ──────────────────────────────────────────────────────
    function carregarDados() {
      var periodo = jQuery('#filtro_periodo').val().split(' - ');
      var dataIni = moment(periodo[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
      var dataFim = moment(periodo[1], 'DD/MM/YYYY').format('YYYY-MM-DD');

      jQuery('#dash_loading').removeClass('d-none');
      jQuery('#btn_filtrar').prop('disabled', true);

      jQuery.ajax({
        url: '<?= base_url($controler . '/dados') ?>',
        type: 'POST',
        data: {
          data_ini: dataIni,
          data_fim: dataFim
        },
        dataType: 'json',
        success: function(res) {
          renderAbc(res.curva_abc || []);
          renderStatusBar(res.solicitado_atendido || {});
          renderDeposito('chart_dep_origem', res.deposito_origem || [], 'Origem');
          renderDeposito('chart_dep_destino', res.deposito_destino || [], 'Destino');
          renderEvolucao(res.evolucao_temporal || []);
          renderStatusDonut(res.status_atual || []);
          renderSla(res.sla_atendimento || []);
        },
        error: function() {
          ['chart_abc', 'chart_status_bar', 'chart_dep_origem', 'chart_dep_destino',
            'chart_evolucao', 'chart_status_donut', 'chart_sla'
          ].forEach(function(id) {
            semDados(id, 'Erro ao carregar dados');
          });
        },
        complete: function() {
          jQuery('#dash_loading').addClass('d-none');
          jQuery('#btn_filtrar').prop('disabled', false);
        },
      });
    }

    // ── 1. Curva ABC ─────────────────────────────────────────────────────────
    function renderAbc(dados) {
      if (!dados.length) {
        semDados('chart_abc');
        return;
      }

      var labels = dados.map(function(d) {
        return d.pro_codpro + ' ' + d.pro_despro.substring(0, 25);
      });
      var valores = dados.map(function(d) {
        return d.total_solicitado;
      });
      var pctAcum = dados.map(function(d) {
        return d.pct_acumulado;
      });
      var bgColors = dados.map(function(d) {
        return d.classe_abc === 'A' ? '#4e73df' : d.classe_abc === 'B' ? '#1cc88a' : '#f6c23e';
      });

      criarOuAtualizar('chart_abc', {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'Qtd Solicitada',
              data: valores,
              backgroundColor: bgColors,
              yAxisID: 'y',
              order: 2,
            },
            {
              label: '% Acumulado',
              data: pctAcum,
              type: 'line',
              borderColor: '#e74a3b',
              backgroundColor: 'transparent',
              pointRadius: 3,
              yAxisID: 'y2',
              order: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top'
            }
          },
          scales: {
            y: {
              position: 'left',
              title: {
                display: true,
                text: 'Qtd'
              }
            },
            y2: {
              position: 'right',
              min: 0,
              max: 100,
              title: {
                display: true,
                text: '%'
              },
              grid: {
                drawOnChartArea: false
              }
            },
          },
        },
      });
    }

    // ── 2. Solicitado x Cancelado x Atendido x Conferido x Aprovado ──────────
    function renderStatusBar(d) {
      var labels = ['Solicitado', 'Cancelado', 'Atendido', 'Conferido', 'Aprovado'];
      var cores = ['#4e73df', '#e74a3b', '#1cc88a', '#36b9cc', '#f6c23e'];
      var vals = [
        parseFloat(d.total_solicitado) || 0,
        parseFloat(d.total_cancelado) || 0,
        parseFloat(d.total_atendido) || 0,
        parseFloat(d.total_conferido) || 0,
        parseFloat(d.total_aprovado) || 0,
      ];

      if (!vals.some(function(v) {
          return v > 0;
        })) {
        semDados('chart_status_bar');
        return;
      }

      registrarAlternavel('chart_status_bar', {
        bar: {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Quantidade',
              data: vals,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true
              }
            },
          },
        },
        pie: {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: vals,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              }
            },
          },
        },
      }, chartKind['chart_status_bar'] || 'bar');
    }

    // ── 3 & 4. Por Depósito ──────────────────────────────────────────────────
    function renderDeposito(id, dados, titulo) {
      if (!dados.length) {
        semDados(id);
        return;
      }

      var labels = dados.map(function(d) {
        return d.dep_nome || d.dep_cod;
      });
      var valores = dados.map(function(d) {
        return d.total_solicitado;
      });
      var cores = dados.map(function(_, i) {
        return cor(i);
      });

      registrarAlternavel(id, {
        bar: {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Qtd Solicitada',
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                beginAtZero: true
              }
            },
          },
        },
        pie: {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              }
            },
          },
        },
      }, chartKind[id] || 'bar');
    }

    // ── 5. Evolução Temporal ─────────────────────────────────────────────────
    function renderEvolucao(dados) {
      if (!dados.length) {
        semDados('chart_evolucao');
        return;
      }

      criarOuAtualizar('chart_evolucao', {
        type: 'line',
        data: {
          labels: dados.map(function(d) {
            return moment(d.data_dia, 'YYYY-MM-DD').format('DD/MM/YYYY');
          }),
          datasets: [{
              label: 'Requisições',
              data: dados.map(function(d) {
                return d.total_requisicoes;
              }),
              borderColor: '#4e73df',
              backgroundColor: 'rgba(78,115,223,0.1)',
              fill: true,
              tension: 0.3,
              pointRadius: 3,
            },
            {
              label: 'Média',
              data: dados.map(function(d) {
                return d.media_requisicoes;
              }),
              borderColor: '#e74a3b',
              backgroundColor: 'transparent',
              borderDash: [6, 4],
              fill: false,
              tension: 0,
              pointRadius: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          },
        },
      });
    }

    // ── 6. Status (Rosca) ────────────────────────────────────────────────────
    function renderStatusDonut(dados) {
      if (!dados.length) {
        semDados('chart_status_donut');
        return;
      }

      var labels = dados.map(function(d) {
        return d.stt_nome;
      });
      var valores = dados.map(function(d) {
        return d.total;
      });
      var cores = dados.map(function(d) {
        return d.cor_valorrgb || '#6c757d';
      });

      registrarAlternavel('chart_status_donut', {
        pie: {
          type: 'doughnut',
          data: {
            labels: labels,
            datasets: [{
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              }
            },
          },
        },
        bar: {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Total',
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true
              }
            },
          },
        },
      }, chartKind['chart_status_donut'] || 'pie');
    }

    // ── 7. SLA ───────────────────────────────────────────────────────────────
    function renderSla(dados) {
      if (!dados.length) {
        semDados('chart_sla');
        return;
      }

      var labels = dados.map(function(d) {
        return d.tmo_nome;
      });
      var valores = dados.map(function(d) {
        return d.sla_horas;
      });
      var cores = dados.map(function(_, i) {
        return cor(i);
      });

      registrarAlternavel('chart_sla', {
        bar: {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Horas médias',
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Horas'
                }
              }
            },
          },
        },
        pie: {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: valores,
              backgroundColor: cores,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              }
            },
          },
        },
      }, chartKind['chart_sla'] || 'bar');
    }

    // ── Carrega ao abrir ─────────────────────────────────────────────────────
    jQuery(document).ready(function() {
      carregarDados();
    });

  }());
</script>
<?= $this->endSection(); ?>