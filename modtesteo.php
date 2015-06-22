<?php

/**
 * Gestion de parametros de configuracion
 *
 * modificación de parametros
 * @package ecomm-core
 */


include("tool.php");


$auth = canRegisteredUserAccess("modtesteo");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$mostrarProfundo = false;
$mostrarNormal = false;


$page->addVar('headers', 'titulopagina', $trans->_('Chequeo del sistema')  );
$page->addVar('page', 'labelalta',  $trans->_("Chequeo profundo") );
$page->addVar('page', 'labellistar',  $trans->_("Chequeo rapido") );

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

function retorno($sistema, $mensajeOk,$mensajeError,$resultado){
	global $page;


	if ($resultado)
		return array("name"=>($page->getIcon("niceinfo.png") ." ".$sistema ),
		"resultado"=>($page->getIconOk() ." ". $mensajeOk ));

	return array("name"=>($page->getIcon("niceinfo.png") ." ".$sistema ),
		"resultado"=>($page->getIconError() ." ". $mensajeError ));
}



if ($mostrarProfundo){

	$page->configMenu("check2");

	$page->setAttribute( 'listado', 'src', 'testeo_simple.txt' );

	$list = array();

	$fila = array("name"=>($page->getIcon("niceinfo.png") ." ". $trans->_("Sistema de templates") ),
		"resultado"=>($page->getIcon("ok1.gif") ." ". $trans->_("Funciona") ));

	$list[]  = $fila;


	$page->addRows('list_entry', $list );

}

if ($mostrarNormal){
	$page->configMenu("check1");

	$page->setAttribute( 'listado', 'src', 'testeo_simple.txt' );

	$list = array();

	$fila = array("name"=>($page->getIcon("niceinfo.png") ." ". $trans->_("Sistema de templates") ),
		"resultado"=>($page->getIconOk() ." ". $trans->_("Funciona") ));
	$list[]  = $fila;

	$resultrow = queryrow("SELECT * FROM status WHERE id_status=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla status inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);

	$resultrow = queryrow("SELECT * FROM contacts WHERE id_contact=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla contacts inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);

	$resultrow = queryrow("SELECT * FROM groups WHERE id_group=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla groups inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);

	$resultrow = queryrow("SELECT * FROM medias WHERE id_media=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla medias inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);

	$resultrow = queryrow("SELECT * FROM tasks WHERE id_task=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla canales inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);


	$resultrow = queryrow("SELECT * FROM channels WHERE id_channel=0 LIMIT 1");
	$list[] = retorno( $trans->_("Tabla buzones inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);


	$resultrow = queryrow("SELECT * FROM risk_management LIMIT 1");
	$list[] = retorno( $trans->_("Tabla de riesgo con datos"),$trans->_("Existen"),$trans->_("No tiene datos"),$resultrow);


	$resultrow = queryrow("SELECT * FROM label_types  LIMIT 1");
	$list[] = retorno( $trans->_("Tabla de tipos de etiquetas"),$trans->_("Existen"),$trans->_("No tiene tipos"),$resultrow);


	$resultrow = queryrow("SELECT * FROM label_types LIMIT 1");
	$list[] = retorno( $trans->_("Varios tipos de etiquetas"),$trans->_("Existen"),$trans->_("No hay ningun tipo definido"),$resultrow);

	$resultrow = queryrow("SELECT * FROM label_types WHERE id_label_type=1 LIMIT 1");
	$list[] = retorno( $trans->_("Etiquetas de usuario"),$trans->_("Existe"),$trans->_("No hay tipo definido"),$resultrow);

	$resultrow = queryrow("SELECT * FROM label_types WHERE id_label_type=0 LIMIT 1");
	$list[] = retorno( $trans->_("Etiquetas genericas"),$trans->_("Existen"),$trans->_("No hay tipo definido"),$resultrow);


	$resultrow = queryrow("SELECT * FROM communications LIMIT 1");
	$list[] = retorno( $trans->_("Comunicaciones recibidas"),$trans->_("Hay comunicaciones"),$trans->_("No hay comunicaciones en tabla communications"),$resultrow);

	$resultrow = queryrow("SELECT * FROM trace LIMIT 1");
	$list[] = retorno( $trans->_("Comunicaciones con traza"),$trans->_("Hay trazas"),$trans->_("No hay trazas de communications"),$resultrow);

	$param = "correo_admin";
	$list[] = retorno( $trans->_("Parametros esencial:"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "labelbasica_es";
	$list[] = retorno( $trans->_("Parametros esencial:"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "gw_sourcefiles_path";
	$list[] = retorno( $trans->_("Parametros esencial (pasarelas):"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "gw_viewfiles_path";
	$list[] = retorno( $trans->_("Parametros esencial (pasarelas) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "path_emailadjuntos";
	$list[] = retorno( $trans->_("Parametros esencial (pasarela Email) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));
	$param = "path_store_pdf";
	$list[] = retorno( $trans->_("Parametros esencial (pasarela Email) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "zfaxgw_path_logs_zfax";
	$list[] = retorno( $trans->_("Parametros esencial (pasarela ZFax) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$param = "hfaxgw_path_avantfax";
	$list[] = retorno( $trans->_("Parametros esencial (pasarela HFax) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));


	$param = "id_channel_notallamada";
	$list[] = retorno( $trans->_("Parametros esencial (anotacion de llamadas) :"). " " . $param ,$trans->_("Existe")
		,$trans->_("No se ha iniciado"),getParametro($param));

	$id_channel_notallamada  = getParametro("id_channel_notallamada");
	$sql = "SELECT * FROM channels INNER JOIN medias ON channels.id_media = medias.id_media WHERE id_channel='$id_channel_notallamada' LIMIT 1 ";
	$row = queryrow($sql);
	$list[] = retorno( $trans->_("Existe un medio para notas de llamada:"),$trans->_("Existe")
		,$trans->_("No se ha iniciado"),$row);


	$page->addRows('list_entry', $list );
}


/*
	correo_admin 	oscar.vives+2@gmail.com
	gw_sourcefiles_path 	/home/oscar/www/ecomm_data/ecomm_adjuntos
	gw_viewfiles_path 	/home/oscar/www/ecomm_data/ecomm_view
	hfaxgw_path_avantfax 	/home/oscar/www/ecomm/logs
	hfaxgw_ultima_captura 	259200
	labelbasica_es 	Areas
	path_emailadjuntos 	/home/oscar/www/ecomm_data/ecomm_adjuntos
	path_store_pdf 	/home/oscar/www/ecomm_data/ecomm_src
	zfaxgw_path_logs_zfax 	/home/oscar/www/ecomm/logs
	zfaxgw_ultima_captura 	1264073415
 */




$page->Volcar();



?>