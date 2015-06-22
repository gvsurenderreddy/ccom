<?php

/**
 * Gestion de parametros de configuracion
 *
 * modificación de parametros
 * @package ecomm-core
 */


include("tool.php");


$auth = canRegisteredUserAccess("modpanel");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$mostrarProfundo = false;
$mostrarNormal = false;


$page->addVar('headers', 'titulopagina', $trans->_('Panel del usuario')  );
$page->addVar('page', 'labelalta',  "" );
$page->addVar('page', 'labellistar',  $trans->_("Estado") );

if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "profundo":
		$mostrarProfundo = true;

		break;
	default:
		$mostrarNormal = true;
		break;
}





if ($mostrarNormal){
	$page->configMenu("sololistar");

	$page->setAttribute( 'listado', 'src', 'paneladmin.txt' );

	/* ------------------------------------------- */

	$list = array();

	$sql = "SELECT * FROM gateway WHERE id_gateway >0 AND enabled=1 ORDER BY `module` ASC";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_gateway"], "name"=>$row["module"] );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );


	//<tr><td><patTemplate:Translate>Comunicaciones en bd</patTemplate:Translate></td><td>{TOTALCOM}</td></tr>
	//<tr><td><patTemplate:Translate>Ultima comunicación fecha</patTemplate:Translate></td><td>{LASTCOM}</td></tr>

	$sql = "SELECT count(id_comm) as cuantos FROM communications";
	$row = queryrow($sql);
	$page->addVar("list","totalcom", $row["cuantos"]);

	$sql = " SELECT date_cap as fecha FROM `communications` ORDER BY id_comm DESC LIMIT 1 ";
	$row = queryrow($sql);
	$page->addVar("list","lastcom", CleanDatetimeToFechaES($row["fecha"]) );


	/* ------------------------------------------- */

	$list = array();

	$sql = "SELECT * FROM users WHERE eslogueado=1 ORDER BY `s_name1` ASC";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "name"=>( $row["s_name1"] ." " . $row["s_name2"]  ) );

		$list[] = $fila;
	}

	$page->addRows('active_users', $list );


	/* ------------------------------------------- */


	include_once("inc/plugandplaybility.inc.php");

	$list = array();
	$modulos = genListaModulosPasarelas();

	$numFilas =0;
	foreach($modulos as $modulo ){
		$numFilas++;

		$color = isAuthorizedModule( $modulo )?"green":"red";
		$stuck = estaCorriendoProceso($modulo)?"[running]":"[ ]";

		$fila = array("proceso"=>("<font style='color: $color'>$stuck ".$modulo."</font>") );

		$list[] = $fila;
	}

	$page->addRows('procesos', $list );



	//procesos


}



$page->Volcar();



?>