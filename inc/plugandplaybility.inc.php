<?php



function NormalizarPath($path){
	// Convierte cadenas del tipo c:/files// o c:/files  en c:/files/
	$path = $path . DIRECTORY_SEPARATOR;

	$path = str_replace("//","/",$path);
	$path = str_replace("//","/",$path);
	$path = str_replace("//","/",$path);
	$path = str_replace("\\\\","\\",$path);

	return $path;
}


function getPathBaseModule($module){

	$module = str_replace(".php","",$module);
	$module = str_replace(".","",$module);

	$dir = "gateway/". $module . "/";

	return $dir;
}




function getValidModule($moddir,$mod=""){
	$elemento = NormalizarPath( $moddir ). $mod;

	if ( file_exists($elemento))
		return $elemento;

	return false;
}









/**
 * comprueba si modulo esta autorizado para correr
 * @param string nombre del modulo
 * @return boolean
 */
function isAuthorizedModule($module){

	$module_s = sql($module);

	$sql = "SELECT enabled FROM gateway WHERE module='$module_s'  ";

	$row = queryrow($sql);

	return $row["enabled"];
}



/**
 * lista los modulos 
 * @return arra modulos
 */
function genListaModulosPasarelas(){
	$modulos = array();

	$dir = "./gateway/";

	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( $file =="." or $file =="..") continue;

				if (!strstr($file, '.gw.php')) continue;

				$modulos[] = $file;
			}
			closedir($dh);
		}
	}

	return $modulos;
}



function marcarProcesoCorriendo($proceso){
	//Linux
	system("touch procesos/". $proceso . ".pid");

	//Windows, other..

	//TODO
}

function desmarcarCorriendoModulo($proceso){
	unlink( "procesos/" . $proceso. ".pid");
	echo "*** Cerrando $proceso ***". CR;
}


function estaCorriendoProceso($proceso){
	$is = file_exists( "procesos/" .  $proceso . ".pid");
//	echo "*** Corriendo $proceso? ($is) ***". CR;

	return $is;
}


function abortarRunGateway(){
	global $corriendoGateway;
	echo "*** Completo: proceso general y saliendo  ***". CR;
	desmarcarCorriendoModulo($corriendoGateway);
}


function adquirirLlave($proceso){

  // Open PID file
  $tmpfilename = "procesos/$proceso.pid";
  if (!($handle_lockfile = @fopen($tmpfilename,"a+")))
  {
    // Script already running - abort
    return false;
  }

  // Obtain an exlcusive lock on file
  // (If script is running this will fail)
  if (!@flock( $handle_lockfile, LOCK_EX | LOCK_NB, &$wouldblock) || $wouldblock)
  {
    // Script already running - abort
    @fclose($handle_lockfile);
    return false;
  }

  // Write our PID
  @ftruncate($handle_lockfile,0);
  @fseek($handle_lockfile, 0, 0);
  @fwrite($handle_lockfile, getmypid());
  @fflush($handle_lockfile);
  return true;
}


?>