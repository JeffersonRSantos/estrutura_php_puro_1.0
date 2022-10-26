<?php

use Core\Session;

date_default_timezone_set("America/Sao_Paulo");

require dirname(__DIR__) . '/vendor/autoload.php';
include(dirname(__DIR__) . '/App/Providers.php');
require dirname(__DIR__) . '/Core/Core.php';

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * CSRF TOKEN
 * @var
 */
$GLOBALS['_TOKEN_CSRF'] = true;
if(!Session::get('_TOKEN_CSRF'))
    Session::set('_TOKEN_CSRF', md5(rand().time()));

/**
 * Routing
 * URL Params {id:\d+} -> Apenas dígitos {param:.+} -> Slugs
 */
$router = new Core\Router();

// Rotas web
require_once(path("/routes/web.php"));

/*************************** */
$GLOBALS['_TOKEN_CSRF'] = false;

// Rotas api
require_once(path("/routes/api.php"));

//Rotas Webhook
$router->post('webhook/zenvia', ['controller' => 'Webhook', 'action' => 'zenvia']);
$router->post('webhook_caf', ['controller' => 'Webhook', 'action' => 'caf']);

// Rotas api
$router->post('api/getToken', ['controller' => 'Api', 'action' => 'getToken']);
$router->post('api/createAuthorization', ['controller' => 'Api', 'action' => 'createAuthorization', 'middleware' => 'AuthApi']);

// Auto route
//$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
