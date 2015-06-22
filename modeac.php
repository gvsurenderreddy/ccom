<?php

/**
 *
 * Gestion de filtros
 *
 * Alta/Baja/modificación de filtros
 * @package ecomm-core
 */

include("tool.php");

include("class/eac.class.php");


$auth = canRegisteredUserAccess("modeac");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$mostrarListado = false;
$mostrarEdicion = false;

$page->addVar('headers',	'titulopagina', $trans->_("Gestion de filtros") );
$page->addVar('page',		'labelalta',    $trans->_("Alta de filtro") );
$page->addVar('page',		'labellistar',  $trans->_("Listar") );

if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;

$eac = new Regla();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND label LIKE '%$filtranombre_s%' ";

		$mostrarListado = true;
		break;

	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;
		break;

	case "navto":
		$min = intval($_REQUEST["offset"]);
		$_SESSION["offset_eac"] = $min;
		$mostrarListado = true;
		break;

	case "guardarcambios":
		$id =  CleanID($_POST["id_eac"]);

		if ( $eac->Load($id) ){
			$eac->set("id_label",$_POST["id_label"]);
			$eac->set("id_contact",$_POST["id_contact"]);
			$eac->set("eac_from", $_POST["eac_from"] , FORCE);
			$eac->set("eac_to", $_POST["eac_to"] , FORCE);
			$eac->set("eac_title", $_POST["eac_title"] , FORCE);
			$eac->set("eac_content", $_POST["eac_content"] , FORCE);
			$eac->set("eac_com_dir", $_POST["eac_com_dir"] , FORCE);
			$eac->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":


		//$eac->set("eac", $_POST["eac"] , FORCE);
		$eac->set("id_label",$_POST["id_label"]);
		$eac->set("id_contact",$_POST["id_contact"]);
		$eac->set("id_user",getSesionDato("id_usuario_logueado") );

		$eac->set("eac_date_in", date("Y-m-d") , FORCE);
		$eac->set("eac_from", $_POST["eac_from"] , FORCE);
		$eac->set("eac_to", $_POST["eac_to"] , FORCE);
		$eac->set("eac_title", $_POST["eac_title"] , FORCE);
		$eac->set("eac_content", $_POST["eac_content"] , FORCE);
		$eac->set("eac_com_dir", $_POST["eac_com_dir"] , FORCE);

		$eac->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$eac->Load($id);

		$nombreUsuarioMostrar = html($eac->getNombre());
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

        $sql = "DELETE FROM eac WHERE id_eac='$id'";
        query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}




if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_eac.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );

	$page->addArrayFromCursor( 'edicion',$eac, array("id_eac","id_label","id_contact","eac_from","eac_to","eac_title","eac_content","eac_com_dir")  );


	$row = queryrow("SELECT * from contacts WHERE id_contact='".$eac->get("id_contact")."'");
	$page->addVar( 'edicion', 'contacto',	$row["contact_name"] );


	$page->addVar("edicion","nubeetiquetashtml", genSelectorNubeEtiquetas( $eac->get("id_label"), getSesionDato("id_usuario_logueado") , "id_label") );
	$page->addVar("edicion","comdirhtml", genComboCOMDIR($eac->get("eac_com_dir")) );

}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_eac.txt' );

	$maxfilas = $_SESSION[ $template["modname"] . "_list_size"];
	$min = intval($_SESSION["offset_eac"]);

	$list = array();

	$sql = "SELECT * FROM eac LEFT JOIN labels ON eac.id_label = labels.id_label
			WHERE id_eac >0 $extracondicion
			ORDER BY `eac_title`  ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array(
				"modname"=>$template["modname"], "id"=>$row["id_eac"],
				"name"=>$row["label"],
				"eac_from"=>$row["eac_from"],
				"eac_to"=>$row["eac_to"],
				"eac_content"=>$row["eac_content"],
				"eac_title"=>$row["eac_title"]
				);

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );
	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();


die();

?>