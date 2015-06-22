<?php

/**
 * Gestion de canales
 *
 * Alta/Baja/modificación de canales
 * @package ecomm-core
 */


include("tool.php");

include("class/channel.class.php");


$auth = canRegisteredUserAccess("modchannels");
if ( !$auth["ok"] ){	include("moddisable.php");	 }



$page->addVar('headers', 'titulopagina', $trans->_('Gestion de canales') );
$page->addVar('page', 'labelalta', $trans->_("Alta de canal") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );


if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;
	

$mostrarListado = false;
$mostrarEdicion = false;


$canal = new Canal();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND channel LIKE '%$filtranombre_s%' ";

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

		$id =  CleanID($_POST["id_channel"]);

		if ( $canal->Load($id) ){
			$canal->set("id_media", $_POST["id_media"]  );
			$canal->set("id_task", $_POST["id_task"]  );
			$canal->set("channel", $_POST["channel"]  );
			$canal->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":
	
		$canal->set("id_media", $_POST["id_media"]  );
		$canal->set("id_task", $_POST["id_task"]  );
		$canal->set("channel", $_POST["channel"] , FORCE);

		$canal->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$canal->Load($id);

		$nombreUsuarioMostrar = html($canal->getNombre());
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

        $sql = "DELETE FROM channels WHERE id_channel='$id'";
        query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}





if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_canales.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );

	$page->addVar( 'edicion', 'combomedioshtml', genComboMedios($canal->get("id_media")) );
	$page->addVar( 'edicion', 'combotareahtml',	genComboTarea($canal->get("id_task")) );

	$page->addArrayFromCursor( 'edicion',$canal, array("id_channel","channel")  );
}

if ($mostrarListado){
	$page->configMenu("listar");
	
	$page->setAttribute( 'listado', 'src', 'listado_canales.txt' );
	$page->addVar( 'list', 'modname',		$template["modname"] );


	$maxfilas = $_SESSION[ $template["modname"] . "_list_size"];
	$min = intval($_REQUEST["min"]);


	$list = array();

	$sql = "SELECT * FROM channels WHERE id_channel>0 $extracondicion ORDER BY channel ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_channel"], "name"=>$row["channel"] );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );


	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();


?>