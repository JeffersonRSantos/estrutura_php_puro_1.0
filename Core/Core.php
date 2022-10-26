<?php

// Super globals functions

use App\Helpers\Tools;
use Core\Session;

ini_set("error_log", path() . "/error_log");

date_default_timezone_set("America/Sao_Paulo");

function dd($var, $exit=true) {
	echo "<pre>";
	if(is_array($var) OR is_object($var)){
		print_r($var);
	}
	else {
		var_dump($var);
	}
	echo "</pre>";
	if($exit) { exit; }
}

/**
 * Models
 * 
 * @return instance Model
 */

function Model($name){
	return ( new App\Providers )->globalModels($name);
}

/**
 * Config
 *  
 * @return App\Config;
 */

function Config(){
	return ( new App\Config );
}

function Table($table) {
	$class = new class($table) {
		use \Core\Model;
		public static $table;
		public function __construct($table) {
			self::$table = $table;
		}
	};
	return $class;
}

function path($path=null) {
	$dir = substr(__DIR__,0,-5);
	if($path == null) return $dir;
	return $dir . "/" . $path;
}

function url($path=null) {
	$url = Config()::BASE_URL;
	if($path == null) return $url;
	return $url . $path;
}

function thisurl() {
	return "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function isRoute($routeName = null){
	$url = $_SERVER['REQUEST_URI'];
	$exists = strpos($url, $routeName);
	if(!$exists)
		return true;
	else
		return false;
}

function goToPage($page) {
	$getUrl = explode("?",thisurl())[0];
	$nextParams = $_GET;
    array_shift($nextParams);
    $nextParams["page"] = $page;
	return $getUrl."?".http_build_query($nextParams);
}

/**
 * Lang
 *  
 * @return array;
 */

function lang($key=null){
	$lang = include(dirname(__DIR__) . '/App/Views/Translation/common.php');
	list($index, $key) = explode('.', $key, 2);
    if (!isset($lang[$index])) throw new Exception("Parametro chave não existe: " . $index);

	return $lang[$index][$key];
}

function typeUser(){
	return Session::get('type_user');
}

function CSRFToken(){
	return Session::get('_TOKEN_CSRF');
}

function CSRFForm(){
	$csrf = Session::get('_TOKEN_CSRF');
	return '<input type="hidden" name="_TOKEN_CSRF" value="'.$csrf.'" />';
}

function mounted($s3name){
	return Tools::mounted($s3name);
}

function formatDateTime($date)
{
	return date('d/m/Y H:i:s', strtotime($date));
}

function RequestInput($data) {
	if(!isset($GLOBALS["_request"])) $GLOBALS["_request"] = (new \Core\Helpers\Request);
	return $GLOBALS["_request"]->input($data);
}