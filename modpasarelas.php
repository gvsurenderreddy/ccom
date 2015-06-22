<?php

/**
 * Gestion de pasarelas
 *
 * Mantenimiento/Activacion de pasarelas
 * @package ecomm-core
 */

include("tool.php");

include_once("class/gateway.class.php");
include_once("inc/pasarelas.inc.php");


$auth = canRegisteredUserAccess("modpasarelas");
if ( !$auth["ok"] ){	include("moddisable.php");	 }



if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;
	

$page->addVar('headers', 'titulopagina',  $trans->_('Gestion de pasarelas') );
$page->addVar('page', 'labelalta',  $trans->_("Alta de pasarelas") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );



$modulos = genListaModulosPasarelas();



//Garantiza de que existan todas las versiones que son visibles en el directorio como ficheros
foreach($modulos as $modulo){

	$modulo_s = sql($modulo);

	$sql = "SELECT * FROM gateway WHERE module='$modulo_s' LIMIT 1";
	$row = queryrow($sql);

	if (!$row){
		$sql = "INSERT gateway (module,enabled) VALUES ('$modulo_s',0) ";
		query($sql);
	}
}





$mostrarListado = false;
$mostrarEdicion = false;


$gateway = new Pasarela();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND module LIKE '%$filtranombre_s%' ";

		/*<input type="hidden" name="modo" value="filtrar-elemento" />
	<input type="hidden" name="filtrar-elemento" value="" id="filtra-list-value" />*/
		$mostrarListado = true;
		break;
	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;

		break;
	case "guardarcambios":
		$id =  CleanID($_POST["id_gateway"]);

		if ( $gateway->Load($id) ){
		//	$gateway->set("module", $_POST["module"]  , FORCE);
			$gateway->set("enabled", ($_POST["enabled"]=='on'?"1":"0")  , FORCE);
			$gateway->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":
		//$gateway->set("module", $_POST["module"] , FORCE);
		$gateway->set("enabled", ($_POST["enabled"]=='on'?"1":"0")  , FORCE);
		
		$gateway->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		if ( $gateway->Load($id) ){
		//	echo "<xmp>Pasarela cargada</xmp>";
		}

		$nombreUsuarioMostrar = html($gateway->getNombre());
		$metodo = "Modificar";
		$newmodo = "guardarcambios";
		break;

	case "alta":
		$mostrarEdicion = true;

		$metodo = "Alta";
		$newmodo = "guardaralta";
		break;
    case "eliminar":
        $id =  CleanID($_REQUEST["id"]);

        $sql = "DELETE FROM gateway WHERE id_gateway='$id'";
		query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}




if ($mostrarEdicion){
	$page->configMenu($newmodo);
	
	$page->setAttribute( 'edicion', 'src', 'edicion_pasarela.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );

	$page->addVar( 'edicion', 'activahtml',	$gateway->get("enabled")?"checked='checked'":"");

	$page->addArrayFromCursor( 'edicion',$gateway, array("id_gateway","module")  );
}

if ($mostrarListado){
	$page->configMenu("sololistar");


	$page->setAttribute( 'listado', 'src', 'listado_pasarelas.txt' );

	$maxfilas = 10;
	$min = intval($_REQUEST["min"]);


	$list = array();

	$sql = "SELECT * FROM gateway WHERE id_gateway >0 $extracondicion ORDER BY `module` ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_gateway"], "name"=>$row["module"],
				"activa"=>($row["enabled"]?"si":"no") );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );
	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();


?>