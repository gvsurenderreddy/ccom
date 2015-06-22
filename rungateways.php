<?php

/**
 * Gestion de pasarelas
 *
 * Alta/Baja/modificación de pasarelas
 * @package ecomm-core
 */


define( "GATEWAY_WRAPPER", true);
define("NO_SESSION",1);
define ("CR", "\n" );

include_once("tool.php");
include_once("inc/pasarelas.inc.php");


$corriendoGateway = "rungateway-". intval(rand()*9000);
$corriendoGeneral = "rungateway-general";

marcarProcesoCorriendo($corriendoGateway);

header("Content-type: text/plain");
//header("Content-type: text/html");

$cr = "\n";


if (estaCorriendoProceso($corriendoGeneral)){
	echo " *** ya esta corriendo *** $cr";
	abortarRunGateway();
}
marcarProcesoCorriendo($corriendoGeneral);


$sql = "SELECT * FROM gateway WHERE lastrun=1 LIMIT 1";
$row = queryrow($sql);

$viejo = $row["module"];

$selected = false;
$cogeSiguiente = false;

$modulos = genListaModulosPasarelas();

foreach($modulos as $modulo){

	if  ( !isAuthorizedModule( $modulo ) ) {
		echo "*** saltando modulo no autorizado '".$modulo."' ***$cr";
		continue;
	}

	if ( estaCorriendoProceso($modulo)  ){
		echo "*** saltando modulo que esta en funcionamiento '".$modulo."' ***$cr";
		continue;
	}

	//Si este modulo fue el ultimo en usarse, se salta
	if ($viejo == $modulo) {

		//si es el primero en la lista, se coge para correr por defecto.
		if (!$selected)
			$selected = $modulo;

		//el proximo que veamos, sera el escogido
		$cogeSiguiente = true;

		continue;
	}

	//al menos cogeremos un valido
	if (!$selected)
		$selected = $modulo;

	//AJA!.. este es el siguiente al ultimo en correr, asi que correremos este, y saldremos para que realmente sea el que se usara
	if($cogeSiguiente) {
		$selected = $modulo;
		break;
	}
	
}


if (!$selected){
	abortarRunGateway();
}


$selected_s = sql($selected);

query("UPDATE gateway SET lastrun='' ");
query("UPDATE gateway SET lastrun=1 WHERE module='$selected_s' ");
//NOTA: se marca antes de correr, porque una pasarela puede tener un error, y fallar, de modo que no hacemos
// nada importante despues.



//Aun con todo, algo puede salir mal, de modo que solo tratamos de correr una que exista.
if ($selected){

	marcarProcesoCorriendo($selected);
	
	echo "*** invocando pasarela ". $selected . " ***$cr";
	include( "gateway/" . $selected );
	echo "*** pasarela ". $selected . " se completo ***$cr";

	desmarcarCorriendoModulo($selected);
} else {
	echo "ERROR: ninguna pasarela fue encontrada cualificada para correr\n";
}

desmarcarCorriendoModulo($corriendoGeneral);

abortarRunGateway();

if (0){
?>

<script>

 setTimeout("document.location.reload()",2000);


</script>

<?php } ?>


