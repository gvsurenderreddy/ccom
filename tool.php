<?php

/**
 * Includo central
 *
 * Este include llama a los includes basicos que el resto de modulos esperan esten siempre presente
 * y ajusta algunos valores por defecto que requieren todas las paginas y la gestion de sesiones
 * @package ecomm-core
 */

$lang = "es";

if( 0 ){
	ini_set("session.gc_maxlifetime",    "86400");
	ini_alter("session.cookie_lifetime", "86400" );


	$expire = 60*60*23;
	ini_set("session.gc_maxlifetime", $expire);

	if (empty($_COOKIE['PHPSESSID'])) {
		session_set_cookie_params($expire);
		session_start();
	} else {
		session_start();
		setcookie("PHPSESSID", session_id(), time() + $expire);
	}
} else {
		//Si no hay sesion, la creamos.

		if (!defined("NO_SESSION")) {
			if (session_id() == "") session_start();
		}
}

$modo = (isset($_REQUEST["modo"])?$_REQUEST["modo"]:false);


if(function_exists("get_magic_quotes_gpc")){
	if (get_magic_quotes_gpc()) {
		function stripslashes_profundo($valor)    {
			$valor = is_array($valor) ?
						array_map('stripslashes_profundo', $valor) :
						stripslashes($valor);
			return $valor;
		}

		$_POST = array_map('stripslashes_profundo', $_POST);
		$_GET = array_map('stripslashes_profundo', $_GET);
		$_COOKIE = array_map('stripslashes_profundo', $_COOKIE);
		$_REQUEST = array_map('stripslashes_profundo', $_REQUEST);
	}
}


if(!function_exists("_")){
	function _($text){
		return $text;
	}
}

$SEPARADOR = DIRECTORY_SEPARATOR;


include_once("config/config.php");
include_once("inc/debug.inc.php");
include_once("inc/clean.inc.php");

if(1){
	include_once("inc/db.inc.php");

	function mysqlescape($str){
		forceconnect();
		return mysql_real_escape_string($str);
	}
} else {
	include_once("inc/dbi.inc.php");

	//NOTE: you can't freely use  mysql_real escape, because it needs a link of his type
	// so we encapsulate  escape into a function, using the mysqli version
	function mysqlescape($str){
		global $link;
		forceconnect();
		return mysqli_real_escape_string($link, $str);
	}
	
}

//include_once("inc/xul.inc.php");
include_once("inc/html.inc.php");
include_once("inc/supersesion.inc.php");
include_once("inc/combos.inc.php");
include_once("inc/auth.inc.php");

include_once("inc/plugandplaybility.inc.php");


include_once("class/json.class.php");//comunicacion


include_once("class/cursor.class.php");
include_once("class/config.class.php");

include_once("class/pagina.class.php");



$template = array();

$script = basename($_SERVER['SCRIPT_NAME']);
$script = substr($script, 0, -4);


$template["modname"] = $script;



$lang = "es";

$page = new Pagina();

$page->Inicia($template["modname"] );

$page->addVar('menu', 'labelbasica', getParametro('labelbasica_es') );


$trans = new Pagina();
$trans->IniciaTranslate();



?>