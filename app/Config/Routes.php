<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function () {
    log_message('critical', 'Rota 404 chamada: {uri}', ['uri' => current_url()]);
    return view('vw_semacesso', [
        'title'     => current_url(),
        'permissao' => false,
        'erromsg'   => "<h2>Atenção</h2>O Caminho <b>" . current_url() . "</b><br>
        <span style='color:red; font-size:16px'>Não foi Encontrado!</span><br>
        Informe o Problema ao Administrador do Sistema!",
    ]);
});
$routes->setAutoRoute(true);

$routes->get('mensagem/(:num)', 'Mensagem::show/$1');

// Rotas Padrão
$routes->get('/', 'Login::index', ['as' => 'login']);
$routes->get('/Login', 'Login::index', ['as' => 'loginindex']);
$routes->match(['GET', 'POST'], '/Login/(:any)', 'Login::$1', ['as' => 'loginlog']);
$routes->get('home_config', 'Config\\Home_config::index', ['as' => 'home_config_index']);
$routes->get('WorkAnalise', 'WorkAnalise::index', ['as' => 'workanalise_index']);

// Controladores de Ocorrências
$ocoControllers = [
    'OcoTipoOcorrencia',
    'OcoSubtOcorrencia',
    'OcoOcorrencia',
    'OcoTrataOcorrencia',
];

foreach ($ocoControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Ocorrencia\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Ocorrencia\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}

$routes->match(['GET', 'POST'], 'buscas/(:any)/(:any)', 'Buscas::$1/$2', ['as' => 'buscas_two_params']);
$routes->match(['GET', 'POST'], 'buscas/(:any)', 'Buscas::$1', ['as' => 'buscas_one_params']);

$routes->match(['GET', 'POST'], 'Notifica/(:any)', 'Notifica::$1', ['as' => 'notifica_verNotifica_match']);
// Grupo: Utils
$routes->group('Utils', static function ($routes) {
    $routes->get('/', 'Utils::index', ['as' => 'utils_index']);
    $routes->match(['GET', 'POST'], '(:any)', 'Utils::$1', ['as' => 'utils_match']);
});

// Grupo: Showfile
$routes->group('Showfile', static function ($routes) {
    $routes->get('/', 'Showfile::show', ['as' => 'showfile_show']);
    $routes->match(['GET', 'POST'], '(:any)', 'Showfile::show/$1', ['as' => 'showfile_show_match']);
});
$routes->group('Logger', static function ($routes) {
    $routes->match(
        ['GET', 'POST'],
        '(:segment)/(:num)',
        'Logger::show/$1/$2',
        ['as' => 'logger_show_match']
    );
});

// Grupo: CriaPdf2025
$routes->group('CriaPdf2025', static function ($routes) {
    $routes->match(['GET', 'POST'], 'PrintAnaRequisicao/(:any)', 'CriaPdf2025::PrintAnaRequisicao/$1', ['as' => 'criapdf2025_match']);
    $routes->match(['GET', 'POST'], 'PrintRequisicaoEstoq/(:any)', 'CriaPdf2025::PrintRequisicaoEstoq/$1', ['as' => 'criapdf2025_match_two']);
    $routes->match(['GET', 'POST'], 'PrintOcorrencia/(:any)', 'CriaPdf2025::PrintOcorrencia/$1', ['as' => 'criapdf2025_print_ocorrencia']);
});
$routes->group('CriamPdf2026', static function ($routes) {
    $routes->match(['GET', 'POST'], 'PrintAnaRequisicao/(:any)', 'CriamPdf2026::PrintAnaRequisicao/$1', ['as' => 'CriamPdf2026_match']);
    $routes->match(['GET', 'POST'], 'PrintRequisicaoEstoq/(:any)', 'CriamPdf2026::PrintRequisicaoEstoq/$1', ['as' => 'CriamPdf2026_match_two']);
    $routes->match(['GET', 'POST'], 'PrintOcorrencia/(:any)', 'CriamPdf2026::PrintOcorrencia/$1', ['as' => 'CriamPdf2026_print_ocorrencia']);
});

// Grupo: CriaEtiqueta
$routes->group('CriaEtiqueta', static function ($routes) {
    $routes->get('/', 'CriaEtiqueta::emiteEtiqueta', ['as' => 'criaetiqueta_emiteEtiqueta']);
    $routes->match(['GET', 'POST'], 'emiteEtiqueta/(:any)', 'CriaEtiqueta::emiteEtiqueta/$1', ['as' => 'criaetiqueta_emiteEtiqueta_match']);
    $routes->match(['GET', 'POST'], 'previewEtiquetaViaAjax/(:any)', 'CriaEtiqueta::previewEtiquetaViaAjax/$1', ['as' => 'criaetiqueta_previewEtiquetaViaAjax_match']);
});

// Grupo: CriaEtiquetaZPL
$routes->group('CriaEtiquetaZPL', static function ($routes) {
    $routes->get('/', 'CriaEtiquetaZPL::emiteEtiqueta', ['as' => 'criaetiquetazpl_emiteEtiqueta']);
    $routes->match(['GET', 'POST'], 'emiteEtiqueta/(:any)', 'CriaEtiquetaZPL::emiteEtiqueta/$1', ['as' => 'criaetiquetazpl_emiteEtiqueta_match1']);
    $routes->match(['GET', 'POST'], 'emiteEtiqueta/(:any)/(:any)', 'CriaEtiquetaZPL::emiteEtiqueta/$1/$2', ['as' => 'criaetiquetazpl_emiteEtiqueta_match2']);
    $routes->match(['GET', 'POST'], 'imprimeEtiqueta/(:any)/(:any)/(:any)', 'CriaEtiquetaZPL::imprimeEtiqueta/$1/$2/$3', ['as' => 'criaetiquetazpl_imprimeEtiqueta_match3']);
    $routes->match(['GET', 'POST'], 'previewEtiquetaViaAjax/(:any)', 'CriaEtiquetaZPL::previewZPL/$1', ['as' => 'criaetiquetazpl_previewZPL_match']);
});

// Controladores de Configuração
$cfgControllers = [
    'CfgCor',
    'CfgModulo',
    'CfgTela',
    'CfgMenu',
    'CfgDicionario',
    'CfgFuncoes',
    'CfgPerfil',
    'CfgUsuario',
    'CfgLoguser',
    'CfgMensagem',
    'CfgStatus',
    'CfgLayoutEtiq',
    'CfgEtiqueta',
    'CfgEmpresa',
    'CfgImpressora',
    'CfgRelatorio',
    'OcoTipoAcao',
];

foreach ($cfgControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Config\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Config\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}

// Controladores de Estoque
$estoqueControllers = [
    'SaldoEstoque',
    'Movimento',
    'Deposito',
    'Transacao',
    'TipoMovimentacao',
    'Requisicao',
    'CfgEtiqueta',
    'AteRequisicao',
    'ConfRequisicao',
    'EtqProduto',
    'EtqMisturador',
    'EtqProdutoReq',
];

foreach ($estoqueControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Estoque\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Estoque\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}
$routes->get('AteRequisicao/GeraEtiqueta/(:num)/(:num)', 'Estoque\\AteRequisicao::GeraEtiqueta/$1/$2', ['as' => 'aterequisicao_GeraEtiqueta_match']);

// Controladores de PréProcessamento
$preProcesControllers = [
    'InspecaoProd',
];

foreach ($preProcesControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Preproces\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Preproces\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}

// Controladores de Produto
$produtoControllers = [
    'Origem',
    'Familia',
    'Lote',
    'ProClasse',
    'ProIngrediente',
    'Produto',
    'Fabricante',
];

foreach ($produtoControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Produto\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Produto\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}

// Controladores de Micro
$microControllers = [
    'Analise',
    'AnaRequisicao',
];
// Grupo: Micro
foreach ($microControllers as $ctrl) {
    $routes->group($ctrl, static function ($routes) use ($ctrl) {
        $name = strtolower($ctrl);
        $routes->get('/', "Micro\\$ctrl::index", ['as' => "{$name}_index"]);
        $routes->match(['GET', 'POST'], '(:any)', "Micro\\$ctrl::$1", ['as' => "{$name}_match"]);
    });
}

// Grupo: WebService
$routes->group('WsCeqweb', static function ($routes) {
    $routes->match(['GET', 'POST'], '(:any)', 'Ws\\WsCeqweb::$1', ['as' => 'wsceqweb_single_match']);
    $routes->match(['GET', 'POST'], '(:any)/(:any)', 'Ws\\WsCeqweb::$1::$2', ['as' => 'wsceqweb_double_match']);
    $routes->match(['GET', 'POST'], '(:any)/(:any)/(:any)', 'Ws\\WsCeqweb::$1::$2::$3', ['as' => 'wsceqweb_triple_match']);
});

$routes->get(
    'config/cfgmensagem/getMensagemAjax/(:num)',
    'Config\CfgMensagem::getMensagemAjax/$1'
);

// Rotas por ambiente
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
