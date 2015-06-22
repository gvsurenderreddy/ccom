<?php

/**
 * Gestion de etiquetas
 *
 * Alta/Baja/modificación de etiquetas
 * @package ecomm-core
 */



include("tool.php");

include("class/labels.class.php");
include("icons/tools/iconlist.php");


$auth = canRegisteredUserAccess("modlabels");
if ( !$auth["ok"] ){	include("moddisable.php");	 }

$maxfilas = 100;

$page->addVar('headers', 'titulopagina', $trans->_('Gestion etiquetas'));
$page->addVar('page', 'labelalta', $trans->_("Alta etiqueta") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );

if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;

$mostrarListado = false;
$mostrarEdicion = false;


$label = new Etiqueta();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

$min = 0;

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND labels.label LIKE '%$filtranombre_s%' ";

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
	case "navto":
		$min = intval($_REQUEST["offset"]);
		$_SESSION["offset_labels"] = $min;
		$mostrarListado = true;
		break;

	case "navlast":

		$sql = "SELECT count(id_label) as max FROM labels
		INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type
		ORDER BY labels.id_label_type ASC, `label` ASC";
		$row = queryrow($sql);
		
		$max = $row["max"];

		$_SESSION["offset_labels"] = $max -$maxfilas ;
		$mostrarListado = true;
		break;
	case "guardarcambios":
		$id =  CleanID($_POST["id_label"]);

		if ( $label->Load($id) ){
			$label->set("label",$_POST["label"]);
			$label->set("icon",$_POST["icon"]);
			$label->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":

		$label->set("label",$_POST["label"]);
		$label->set("id_label_type",$_POST["id_label_type"]);
		$label->set("icon",$_POST["icon"]);
		
		$label->Alta();
		$mostrarListado = true;
		
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$label->Load($id);


		$nombreUsuarioMostrar = html($label->getNombre());
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

        $sql = "DELETE FROM labels WHERE id_label='$id'";
        query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}



if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_etiqueta.txt' );


	//

	$page->addVar('edicion', 'combotipoetiqueta', genComboTipoEtiqueta( $label->get("id_label_type")) . "XXXXXXXXXXXXX1" );

	$page->addVar( 'edicion', 'modname', $template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',$metodo );
	$page->addVar( 'edicion', 'modoedicion',$newmodo );
	
	$page->addVar( 'edicion', 'label', $label->get("label")  );
	$page->addVar( 'edicion', 'icon', $label->get("icon")  );
	$page->addVar( 'edicion', 'idlabel', $label->get("id_label")  );


	$list = array();

	foreach($iconos as $icon){
		$newfila = array();

		$newfila["icon"] = $icon;

		$list[] = $newfila;
	}



	$page->addRows('list_entry', $list );
}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_etiquetas.txt' );

	$min = intval($_SESSION["offset_labels"]);

	$list = array();

	$sql = "SELECT * FROM labels
		INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type
		WHERE id_label>0 $extracondicion 
		ORDER BY labels.id_label_type ASC, `label`  ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_label"],
			"name"=>$row["label"],
			"icon"=>($row["icon"]?$row["icon"]:"trans.gif"),
			"tipo"=>$row["label_type"]
			);

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );

	$page->configNavegador( $min, $maxfilas,$numFilas);
}

$page->Volcar();


?>