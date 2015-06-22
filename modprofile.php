<?php

/**
 * Gestion de profiles
 *
 * Alta/Baja/modificación de profiles
 * @package ecomm-core
 */



/*
 *
  *  Gestión de grupos de usuarios. Un grupo de usuario tendrá uno o varios
 administradores, uno o varios gestores y uno o varios usuarios.

  * Cada grupo podrá tener acceso a las comunicaciones de una o varios canales,
   uno o varios destinatarios(o incluso restricciones, al estilo gmail) y etiquetas.
    Hay que tener en cuenta que el canal restringe los resultados de destinatarios y éste los de etiquetas.

 * 
 */

include("tool.php");

include("class/profile.class.php");

$auth = canRegisteredUserAccess("modgrupos");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$mostrarListado = false;
$mostrarEdicion = false;

$page->addVar('headers', 'titulopagina', $trans->_('Gestion de profiles') );
$page->addVar('page', 'labelalta', $trans->_("Alta de profiles") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );



if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;
	

$profile = new Profile();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND name LIKE '%$filtranombre_s%' ";

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
		$id =  CleanID($_POST["id_profile"]);

		if ( $profile->Load($id) ){
			$profile->set("name", $_POST["name"]  , FORCE);
			$profile->set("isgroupprofile", (($_POST["isgroupprofile"]=="on")?1:0)  , FORCE);
			$profile->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":
		$profile->set("name", $_POST["name"]  , FORCE);
		$profile->set("isgroupprofile", (($_POST["isgroupprofile"]=="on")?1:0)  , FORCE);

		$profile->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$profile->Load($id);

		$nombreUsuarioMostrar = html($profile->getNombre());
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

        $sql = "DELETE FROM profiles WHERE id_profile='$id'";
        query($sql);
		$mostrarListado = true;
        break;

	case "deleterule":

		//id_profile=1&modo=deleterule&id_allowdisallow=0
		$mostrarEdicion = true;
		$id = CleanID($_POST["id_profile"]);
		$profile->Load($id);

		$nombreUsuarioMostrar = html($profile->getNombre());
		$metodo = "Modificar";
		$newmodo = "guardarcambios";

		$id = CleanID($_POST["id_allowdisallow"]);
		$sql = "UPDATE allowdisallows SET deleted=1 WHERE (id_allowdisallow = $id) ";
		query($sql);

		//echo "<xmp>".$sql."</xmp>";
		break;

	case "newrule":
		$mostrarEdicion = true;
		$id = CleanID($_POST["id_profile"]);
		$profile->Load($id);

		$nombreUsuarioMostrar = html($profile->getNombre());
		$metodo = "Modificar";
		$newmodo = "guardarcambios";

		$path = sql($_POST["path"]);
		$allow = ($_POST["allow"]=="allow")?"a":"d";


		$sql = "INSERT allowdisallows (path, way,id_profile) VALUES ('$path','$allow','$id')";

		query($sql);

		break;
	case "editrule":
		$mostrarEdicion = true;
		$id = CleanID($_POST["id_profile"]);
		$profile->Load($id);

		$nombreUsuarioMostrar = html($profile->getNombre());
		$metodo = "Modificar";
		$newmodo = "guardarcambios";

		$path_s = sql($_POST["path"]);
		$way_s = ($_POST["allow2"]=="allow")?"a":"d";

		$id = CleanID($_POST["id_allowdisallow"]);
		$sql = "UPDATE allowdisallows SET path='$path_s',way='$way_s' WHERE (id_allowdisallow = $id) ";
		query($sql);

		break;
	default:
		$mostrarListado = true;
		break;

}



if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_profiles.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );


	$id = $profile->get("id_profile");

	$list = array();
	$sql = "SELECT * FROM allowdisallows WHERE id_profile ='$id' and deleted=0  ORDER BY `path`";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$way_bonito = "permite";
		if ( $row["way"] =="d") {
				$way_bonito= "prohibe";
		}


		$fila = array("modname"=>$template["modname"], "id_allowdisallow"=>$row["id_allowdisallow"], "path"=>$row["path"], 
				"way"=>$way_bonito
		);

		$list[] = $fila;
	}

	if (!$numFilas){
		$list[] = array("hayfilas"=>"oculto");
	}

	$page->addRows('list_entry', $list );

	$page->addArrayFromCursor( 'edicion',$profile, array("id_profile","name","isgroupprofile")  );
}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_profiles.txt' );

	$maxfilas = 10;
	$min = intval($_REQUEST["min"]);


	$list = array();

	$sql = "SELECT * FROM profiles WHERE id_profile>0 $extracondicion ORDER BY isgroupprofile DESC, `name` ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_profile"], "name"=>$row["name"] );

		$fila["group"] = ($row["isgroupprofile"])?"de grupo":"";
		
		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );

	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();








?>
