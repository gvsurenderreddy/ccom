<?php

/**
 * Gestion de lugares
 *
 * Alta/Baja/modificación de lugares
 * @package ecomm-core
 */

include("tool.php");

include("class/location.class.php");

$auth = canRegisteredUserAccess("modlugares");
if ( !$auth["ok"] ){	include("moddisable.php");	 }



$mostrarListado = false;
$mostrarEdicion = false;

$page->addVar('headers', 'titulopagina', $trans->_('Gestion de lugares') );
$page->addVar('page', 'labelalta', $trans->_("Alta de lugares") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );



if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;
	

$location = new Lugar();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND name LIKE '%$filtranombre_s%' ";

		$mostrarListado = true;
		break;
	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;

		break;
	case "guardarcambios":
		$id =  CleanID($_POST["id_location"]);

		if ( $location->Load($id) ){
			$location->set("name", $_POST["name"]  , FORCE);
			$location->set("id_profile", $_POST["id_profile"]  , FORCE);
			$location->set("id_group", $_POST["id_group"]  , FORCE);
			$location->set("id_label", $_POST["id_label"]  , FORCE);
			$location->set("css_x", $_POST["css_x"]  , FORCE);
			$location->set("css_y", $_POST["css_y"]  , FORCE);
/*
  	id_location  	bigint(20)  	 	  	No  	 	 	  Navegar los valores distintivos   	  Cambiar   	  Eliminar   	  Primaria   	  Único   	  Índice   	 Texto completo
	name 	tinytext 	latin1_swedish_ci 		No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_label 	bigint(20) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_group 	int(11) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	css_x 	int(11) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	css_y 	int(11) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_status_location
 */
			$location->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":
		$location->set("name", $_POST["name"]  , FORCE);
		$location->set("id_profile", $_POST["id_profile"]  , FORCE);
		$location->set("id_group", $_POST["id_group"]  , FORCE);
		$location->set("id_label", $_POST["id_label"]  , FORCE);
		$location->set("css_x", $_POST["css_x"]  , FORCE);
		$location->set("css_y", $_POST["css_y"]  , FORCE);

		$location->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$location->Load($id);

		$nombreUsuarioMostrar = html($location->getNombre());
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

        $sql = "DELETE FROM locations WHERE id_location='$id'";
        query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;
}



if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_lugares.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );
	//$page->addVar( 'edicion', 'optionsselectprofile', genComboProfiles($location->get("id_profile"), array("id"=>1) ) );
	//$page->addVar("edicion","lista_etiquetas_lugares",getComboStatus(7));//TODO: leer el id de lugares

	$page->addArrayFromCursor( 'edicion',$location, array("id_location","name","id_group","id_label","css_x","css_y","id_status_location")  );
}



if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_lugares.txt' );

	$maxfilas = 10;
	$min = intval($_REQUEST["min"]);

	$list = array();

	$sql = "SELECT * FROM locations WHERE id_location>0 $extracondicion ORDER BY `name` ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_location"], "name"=>$row["name"] );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );

	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();


?>
