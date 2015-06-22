<?php

return "no se usa";


chdir("..");


function open($save_path, $session_name)
{
  global $sess_save_path;

  $sess_save_path = $save_path;
  return(true);
}

function close()
{
  return(true);
}

function read($id)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  return (string) @file_get_contents($sess_file);
}

function write($id, $sess_data)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  if ($fp = @fopen($sess_file, "w")) {
    $return = fwrite($fp, $sess_data);
    fclose($fp);
    return $return;
  } else {
    return(false);
  }

}

function destroy($id)
{
  global $sess_save_path;

  $sess_file = "$sess_save_path/sess_$id";
  return(@unlink($sess_file));
}

function gc($maxlifetime)
{
  global $sess_save_path;

  foreach (glob("$sess_save_path/sess_*") as $filename) {
    if (filemtime($filename) + $maxlifetime < time()) {
      @unlink($filename);
    }
  }
  return true;
}

session_set_save_handler("open", "close", "read", "write", "destroy", "gc");


//define("NO_SESSION",1);


$cr = "<br />\n";


/**
 * Includo central
 *
 * Este include llama a los includes basicos que el resto de modulos esperan esten siempre presente
 * y ajusta algunos valores por defecto que requieren todas las paginas y la gestion de sesiones
 * @package ecomm-core
 */

$lang = "es";

$str = "";

if (1){



		if (!defined("NO_SESSION")) {
			$str .= "cargamos sesion? $cr";

			if (session_id() == "") {
				$str .= "sesion start $cr";
				$start = microtime(TRUE);

				session_start();
				$time = microtime(TRUE) - $start;

				$str .= "time: $time $cr";
			}
		}

}

echo $str;


if (0){

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
}


if (0){

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

}

if(0){
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
}

if(0){

$template = array();

$script = basename($_SERVER['SCRIPT_NAME']);
$script = substr($script, 0, -4);


$template["modname"] = $script;



$lang = "es";

$page = &new Pagina();
$page->Inicia($template["modname"] );
$page->addVar('menu', 'labelbasica', getParametro('labelbasica_es') );

}



for($t=0;$t<20;$t++){
	echo " t[$t]<br>\n";
	sleep(1);
}






?>