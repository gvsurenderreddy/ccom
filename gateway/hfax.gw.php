<?php

/**
 * Hylafax+Avantfax Gateway para Ecomm
 *
 * @package ecomm-gateway
 */
define("NO_SESSION",1);



/*
 * Si corre aisladamente, debe correr en "raiz" de la aplicación, y no en el directorio gateway.
 */
if (!defined( "GATEWAY_WRAPPER")) chdir("..");


/*
 * Requiere tener estas librerias incluidas, pero estas no deben re-definirse
 */
include_once("tool.php");
include_once("inc/runmeforever.inc.php");
include_once("inc/pasarelas.inc.php");
include_once("gateway/hfaxgw/lib.php");
include_once("class/fax.class.php");


$necesitaMantenimientoTablas = false;

//$cr = "\n<br>";
$cr = "\n";
echo "<pre>";
$modulos = array();


if ($debug_total){
	//INFO: para obligar a repetir todo el proceso, es necesario resetear estos valores
	query("UPDATE `ecomm`.`system_param` SET `system_param_value` = NOW( ) WHERE `system_param`.`id_system_param` =8 ");
	query("TRUNCATE gw_hfax");
}

$path_logs = getParametro("hfaxgw_path_avantfax");

//TODO: carga parametro volatil
$ultimarevision = getParametro("hfaxgw_ultima_captura",true);

$fecha_actual = date("Ymd");
$fecha_antigua = date("Ymd",$ultimarevision);


//Corremos para la fecha actual
$esCorrerHoy = ( $fecha_actual ==  $fecha_antigua );

if ($esCorrerHoy ) {
	$codigo_fecha = date("Y/m/d",time());
	$ultimarevision_guardar = time();
} else {

	//tenemos que revisar una fecha en el pasado, y hacer para que
	// se revise el siguiente dia a esa fecha en el futuro
	$ultimarevision_guardar = $ultimarevision + 24*60*60; // +1 dia

	$codigo_fecha = date("Y/m/d",$ultimarevision);
}


$path_logfiles  = NormalizarPath($path_logs) . "recvd/". $codigo_fecha . "/";




	$dir = NormalizarPath($path_logfiles);

echo marca(). "Iniciando escaneo de $dir" . $cr;


if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( $file =="." or $file =="..") continue;

				if ( esDocumento($file) ){
				  //tenemos una carpeta de datos, lo trataremos de insertar
				  echo "scan: $file es un documento". $cr;
				} else {
					echo marca(). "scan: entra en $dir $file en profundidad " . $cr;
					enProfundidad($dir . $file );
				}
			}
			closedir($dh);
		}
} else {
	echo marca(). " $dir no es carpeta ". $cr;
}


/*
echo "<pre>";

echo print_r($modulos,true);

echo "</pre>";
*/




foreach($modulos as $fichero){

  cron_flush();

  $pdf =  $fichero["pdf"];
  $fax =  $fichero["tif"];
  $fechahora = $fichero["FECHAHORA"];
  $msgid = $fichero["acode"];

  echo marca(), "Procesando fax","($proceso) i: procesando ($pdf) ($fax)" . $cr;

  if ( strlen($pdf)<5){
	  echo marca(). "[$msgid]: [$pdf] es incompleto (se esperaba al menos 5 caracteres)" . $cr;
	  continue;
  }

  if ( hfax_pdf_conocido( $msgid) ){
	echo marca() . "[$msgid]: [$pdf] ya esta en la base de datos  " . $cr;
	continue;
  }

//	echo "info: ".  print_r( hfax_pdf_conocido( $msgid),true ) . $cr;

  echo marca(). "[$msgid]: Incorporando z:$msgid; [$pdf] en la base de datos" . $cr;

  $pedido = new Fax();

  $fullpath_tiff = NormalizarPath($fichero["fullpath"] . "/"). $fichero["tif"];
  $fullpath_pdf  = NormalizarPath($fichero["fullpath"] . "/") . $fichero["pdf"];

  $archivos = archivadorOnline( $fullpath_tiff, $fullpath_pdf , false);

  //$pdf_system = $fichero["fax"];
  //$file_hfax  = $fichero["tif"];
  $pdf_system = $archivos["viewfile"];
  $file_hfax = $archivos["source"];

  $fax_sender	= "?????";//TODO: muy importante, saber de que fax se recibe
  $fax_receiver = "HFax";//TODO: muy importante, saber en que fax se recibe


  $id_channel = getChannelFromFax($fax_receiver);

  if (!$id_channel){
	$id_channel = $config->get("hfax_id_channel_default");
  }



  $pedido->Alta($file_hfax, $pdf_system,$fax_sender,$fax_receiver,$msgid,$fechahora,$id_channel);
  $necesitaMantenimientoTablas = true;

  echo  marca()."$msgid:  hfax_msg: $msgid\n";
  echo  marca()."$msgid:       pdf: $pdf\n";
  echo  marca()."$msgid:       fax: $fax\n";

}


echo marca().": cod fecha: " . $codigo_fecha . $cr;
echo marca().": el log:    " . $path_logfiles . $cr;
echo marca().": path logs: " . $path_logs . $cr;
echo marca().": esHoy:     " . $esCorrerHoy . $cr;
echo marca().": ultimarev: " . $ultimarevision . $cr;
echo marca().": ultimarevG:" . $ultimarevision_guardar . $cr;
echo marca().": time:      " . time() . $cr;


//if ( $necesitaMantenimientoTablas)
//	actualizarTablaRapida();

echo "</pre>";

setParametro("hfaxgw_ultima_captura",$ultimarevision_guardar);




?>