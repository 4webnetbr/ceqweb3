<?php

namespace Config;

use App\Controllers\Analise\Analise;
use App\Controllers\CfgEmpresa;
use App\Controllers\Config\CfgCor;
use App\Controllers\Config\CfgDicionario;
use App\Controllers\Config\CfgFuncoes;
use App\Controllers\Config\CfgMensagem;
use App\Controllers\Config\CfgMenu;
use App\Controllers\Config\CfgModulo;
use App\Controllers\Config\CfgPerfil;
use App\Controllers\Config\CfgStatus;
use App\Controllers\Config\CfgTela;
use App\Controllers\Config\CfgUsuario;
use App\Controllers\Config\Home_config;
use App\Controllers\CriaEtiqueta;
use App\Controllers\Estoque\AteRequisicao;
use App\Controllers\Estoque\CfgEtiqueta;
use App\Controllers\Estoque\CfgLayoutEtiq;
use App\Controllers\Estoque\ConfRequisicao;
use App\Controllers\Estoque\Deposito;
use App\Controllers\Estoque\OcoTipoAcao;
use App\Controllers\Estoque\OcoTipoOcorrencia;
use App\Controllers\Estoque\Requisicao;
use App\Controllers\Estoque\SaldoEstoque;
use App\Controllers\Estoque\TipoMovimentacao;
use App\Controllers\Estoque\Transacao;
use App\Controllers\Micro\AnaRequisicao;
use App\Controllers\Produto\Lote;
use App\Controllers\Showfile;
use App\Controllers\Utils;
use App\Controllers\Ws\WsCeqweb;
use CodeIgniter\Config\Services;
use Estoque\Familia;
use Estoque\Origem;
use Produto\Fabricante;
use Produto\ProClasse;
use Produto\ProIngrediente;
use Produto\Produto;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Config
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(true);

// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'login::index');
$routes->get('/home_config', 'Config\Home_config::index');
$routes->get('WorkAnalise', 'WorkAnalise::index');

$routes->get('/Utils', 'Utils::index');
$routes->get('/Utils/(:any)', 'Utils::$1');
$routes->post('/Utils/(:any)', 'Utils::$1');

$routes->get('/Showfile', 'Showfile::show');
$routes->get('/Showfile/(:any)', 'Showfile::show/$1');
$routes->post('/Showfile/(:any)', 'Showfile::show/$1');

$routes->get('/CriaEtiqueta', 'CriaEtiqueta::emiteEtiqueta');
$routes->get('/CriaEtiqueta/(:any)', 'CriaEtiqueta::emiteEtiqueta/$1');
$routes->post('/CriaEtiqueta/(:any)', 'CriaEtiqueta::emiteEtiqueta/$1');

$routes->get('/CfgCor', 'Config\CfgCor::index');
$routes->get('/CfgCor/(:any)', 'Config\CfgCor::$1');
$routes->post('/CfgCor/(:any)', 'Config\CfgCor::$1');

$routes->get('/CfgModulo', 'Config\CfgModulo::index');
$routes->get('/CfgModulo/000', 'Config\CfgModulo::add');
$routes->get('/CfgModulo/100/(:any)', 'Config\CfgModulo::show/$1');
$routes->get('/CfgModulo/200/(:any)', 'Config\CfgModulo::edit/$1');
$routes->get('/CfgModulo/300/(:any)', 'Config\CfgModulo::delete/$1');
$routes->get('/CfgModulo/400/(:any)', 'Config\CfgModulo::ativinativ/$1');
$routes->get('/CfgModulo/(:any)', 'Config\CfgModulo::$1');
$routes->post('/CfgModulo/(:any)', 'Config\CfgModulo::$1');

$routes->get('/CfgTela', 'Config\CfgTela::index');
$routes->get('/CfgTela/(:any)', 'Config\CfgTela::$1');
$routes->post('/CfgTela/(:any)', 'Config\CfgTela::$1');

$routes->get('/CfgMenu', 'Config\CfgMenu::index');
$routes->get('/CfgMenu/(:any)', 'Config\CfgMenu::$1');
$routes->post('/CfgMenu/(:any)', 'Config\CfgMenu::$1');

$routes->get('/CfgDicionario', 'Config\CfgDicionario::index');
$routes->get('/CfgDicionario/000/(:any)', 'Config\CfgDicionario::show/$1');
$routes->get('/CfgDicionario/(:any)', 'Config\CfgDicionario::$1');
$routes->post('/CfgDicionario/(:any)', 'Config\CfgDicionario::$1');

$routes->get('/CfgFuncoes', 'Config\CfgFuncoes::index');
$routes->get('/CfgFuncoes/(:any)', 'Config\CfgFuncoes::$1');
$routes->post('/CfgFuncoes/(:any)', 'Config\CfgFuncoes::$1');

$routes->get('/CfgPerfil', 'Config\CfgPerfil::index');
$routes->get('/CfgPerfil/(:any)', 'Config\CfgPerfil::$1');
$routes->post('/CfgPerfil/(:any)', 'Config\CfgPerfil::$1');

$routes->get('/CfgUsuario', 'Config\CfgUsuario::index');
$routes->get('/CfgUsuario/(:any)', 'Config\CfgUsuario::$1');
$routes->post('/CfgUsuario/(:any)', 'Config\CfgUsuario::$1');

$routes->get('/CfgMensagem', 'Config\CfgMensagem::index');
$routes->get('/CfgMensagem/(:any)', 'Config\CfgMensagem::$1');
$routes->post('/CfgMensagem/(:any)', 'Config\CfgMensagem::$1');

$routes->get('/CfgStatus', 'Config\CfgStatus::index');
$routes->get('/CfgStatus/(:any)', 'Config\CfgStatus::$1');
$routes->post('/CfgStatus/(:any)', 'Config\CfgStatus::$1');

$routes->get('/CfgLayoutEtiq', 'Config\CfgLayoutEtiq::index');
$routes->get('/CfgLayoutEtiq/(:any)', 'Config\CfgLayoutEtiq::$1');
$routes->post('/CfgLayoutEtiq/(:any)', 'Config\CfgLayoutEtiq::$1');

$routes->get('/CfgEtiqueta', 'Config\CfgEtiqueta::index');
$routes->get('/CfgEtiqueta/(:any)', 'Config\CfgEtiqueta::$1');
$routes->post('/CfgEtiqueta/(:any)', 'Config\CfgEtiqueta::$1');

$routes->get('/CfgEmpresa', 'Config\CfgEmpresa::index');
$routes->get('/CfgEmpresa/(:any)', 'Config\CfgEmpresa::$1');
$routes->post('/CfgEmpresa/(:any)', 'Config\CfgEmpresa::$1');

// $routes->match(['get', 'post'], '/CfgModulo/(:any)/(:any)', 'Config\CfgModulo::$1::$2');

$routes->get('/SaldoEstoque', 'Estoque\SaldoEstoque::index');
$routes->get('/SaldoEstoque/(:any)', 'Estoque\SaldoEstoque::$1');
$routes->post('/SaldoEstoque/(:any)', 'Estoque\SaldoEstoque::$1');


$routes->get('/Deposito', 'Estoque\Deposito::index');
$routes->get('/Deposito/(:any)', 'Estoque\Deposito::$1');
$routes->post('/Deposito/(:any)', 'Estoque\Deposito::$1');

$routes->get('/Transacao', 'Estoque\Transacao::index');
$routes->get('/Transacao/(:any)', 'Estoque\Transacao::$1');
$routes->post('/Transacao/(:any)', 'Estoque\Transacao::$1');

$routes->get('/TipoMovimentacao', 'Estoque\TipoMovimentacao::index');
$routes->get('/TipoMovimentacao/(:any)', 'Estoque\TipoMovimentacao::$1');
$routes->post('/TipoMovimentacao/(:any)', 'Estoque\TipoMovimentacao::$1');

$routes->get('/Requisicao', 'Estoque\Requisicao::index');
$routes->get('/Requisicao/(:any)', 'Estoque\Requisicao::$1');
$routes->post('/Requisicao/(:any)', 'Estoque\Requisicao::$1');

$routes->get('/AteRequisicao', 'Estoque\AteRequisicao::index');
$routes->get('/AteRequisicao/(:any)', 'Estoque\AteRequisicao::$1');
$routes->post('/AteRequisicao/(:any)', 'Estoque\AteRequisicao::$1');

$routes->get('/ConfRequisicao', 'Estoque\ConfRequisicao::index');
$routes->get('/ConfRequisicao/(:any)', 'Estoque\ConfRequisicao::$1');
$routes->post('/ConfRequisicao/(:any)', 'Estoque\ConfRequisicao::$1');

$routes->get('/Origem', 'Produto\Origem::index');
$routes->get('/Origem/(:any)', 'Produto\Origem::$1');
$routes->post('/Origem/(:any)', 'Produto\Origem::$1');

$routes->get('/Familia', 'Produto\Familia::index');
$routes->get('/Familia/(:any)', 'Produto\Familia::$1');
$routes->post('/Familia/(:any)', 'Produto\Familia::$1');

$routes->get('/Lote', 'Produto\Lote::index');
$routes->get('/Lote/(:any)', 'Produto\Lote::$1');
$routes->post('/Lote/(:any)', 'Produto\Lote::$1');

$routes->get('/ProClasse', 'Produto\ProClasse::index');
$routes->get('/ProClasse/(:any)', 'Produto\ProClasse::$1');
$routes->post('/ProClasse/(:any)', 'Produto\ProClasse::$1');

$routes->get('/ProIngrediente', 'Produto\ProIngrediente::index');
$routes->get('/ProIngrediente/(:any)', 'Produto\ProIngrediente::$1');
$routes->post('/ProIngrediente/(:any)', 'Produto\ProIngrediente::$1');

$routes->get('/Produto', 'Produto\Produto::index');
$routes->get('/Produto/(:any)', 'Produto\Produto::$1');
$routes->post('/Produto/(:any)', 'Produto\Produto::$1');

$routes->get('/Fabricante', 'Produto\Fabricante::index');
$routes->get('/Fabricante/(:any)', 'Produto\Fabricante::$1');
$routes->post('/Fabricante/(:any)', 'Produto\Fabricante::$1');

$routes->get('/Analise', 'Micro\Analise::index');
$routes->get('/Analise/(:any)', 'Micro\Analise::$1');
$routes->post('/Analise/(:any)', 'Micro\Analise::$1');

$routes->get('/AnaRequisicao', 'Micro\AnaRequisicao::index');
$routes->get('/AnaRequisicao/(:any)', 'Micro\AnaRequisicao::$1');
$routes->post('/AnaRequisicao/(:any)', 'Micro\AnaRequisicao::$1');

$routes->get('/OcoTipoAcao', 'Ocorrencia\OcoTipoAcao::index');
$routes->get('/OcoTipoAcao/(:any)', 'Ocorrencia\OcoTipoAcao::$1');
$routes->post('/OcoTipoAcao/(:any)', 'Ocorrencia\OcoTipoAcao::$1');

$routes->get('/OcoTipoOcorrencia', 'Ocorrencia\OcoTipoOcorrencia::index');
$routes->get('/OcoTipoOcorrencia/(:any)', 'Ocorrencia\OcoTipoOcorrencia::$1');
$routes->post('/OcoTipoOcorrencia/(:any)', 'Ocorrencia\OcoTipoOcorrencia::$1');

$routes->match(['get', 'post'], '/WsCeqweb/(:any)', 'Ws\WsCeqweb::$1');
$routes->match(['get', 'post'], '/WsCeqweb/(:any)/(:any)', 'Ws\WsCeqweb::$1::2');
$routes->match(['get', 'post'], '/WsCeqweb/(:any)/(:any)/(:any)', 'Ws\WsCeqweb::$1::2::3');
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
