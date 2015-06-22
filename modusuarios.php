<?php

/**
 * Gestion de usuarios
 *
 * Alta/Baja/modificación de usuarios
 * @package ecomm-core
 */


include("tool.php");

include("class/users.class.php");

$auth = canRegisteredUserAccess("modusuarios");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$page->addVar('headers', 'titulopagina', $trans->_('Gestión de usuarios') );
$page->addVar('page', 'labelalta',  $trans->_("Alta de usuario") );
$page->addVar('page', 'labellistar', $trans->_("Listar") );


if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;

$mostrarListado = false;
$mostrarEdicion = false;


$usuario = new Usuario();

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

		$id =  CleanID($_POST["id_user"]);

		if ( $usuario->Load($id) ){

			$usuario->set("name", $_POST["name"]  , FORCE);
			$usuario->set("user_login", $_POST["user_login"]  , FORCE);
			$usuario->set("pass_login", $_POST["pass_login"]  , FORCE);
			$usuario->set("s_name1", $_POST["s_name1"]  , FORCE);
			$usuario->set("s_name2", $_POST["s_name2"]  , FORCE);
			$usuario->set("phone", $_POST["phone"]  , FORCE);
			$usuario->set("email", $_POST["email"]  , FORCE);
			$usuario->set("id_profile", $_POST["id_profile"]  , FORCE);

			$usuario->Modificacion();
		}

		$groups = $_REQUEST["groupsismember"];




		$grupos = split(",",$groups);

		if (count($grupos)>0){

			query("BEGIN");
			query("DELETE FROM user_groups WHERE id_user='$id'");

			foreach($grupos as $idgrupo){

				if ($idgrupo){
					$idgrupo_s = sql($idgrupo);

					/*
					$sql = "SELECT id_user_groups as id FROM user_groups WHERE id_user= '$id' AND id_group='$idgrupo_s'";
					$row = queryrow($sql);
					if ($row["id"])
						continue;
                     */

					$sql = "INSERT user_groups ( id_user, id_group) VALUES ( '$id','$idgrupo_s')";
					query($sql);
					//echo $sql.  "<br>";
				}
			}
			query("COMMIT");


		}


		$mostrarListado = true;

		break;

	case "guardaralta":

		$usuario->set("name", $_POST["name"] , FORCE);
		$usuario->set("user_login", $_POST["user_login"] , FORCE );
		$usuario->set("pass_login", $_POST["pass_login"]  , FORCE);
		$usuario->set("s_name1", $_POST["s_name1"]  , FORCE);
		$usuario->set("s_name2", $_POST["s_name2"]  , FORCE);
		$usuario->set("phone", $_POST["phone"]  , FORCE);
		$usuario->set("email", $_POST["email"]  , FORCE);
		$usuario->set("id_profile", $_POST["id_profile"]  , FORCE);

		$usuario->Alta();             
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$usuario->Load($id);

		$nombreUsuarioMostrar = html($usuario->getNombre());
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
                
        $sql = "UPDATE users SET deleted='1' WHERE id_user='$id'";
        query($sql);
		$mostrarListado = true;

        break;
	default:
		$mostrarListado = true;
		break;

}







if ($mostrarEdicion){
	$page->configMenu($newmodo);
	$page->setAttribute( 'edicion', 'src', 'edicion_usuarios.txt' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );

	$page->addVar( 'edicion', 'optionsselectprofile', genComboProfiles($usuario->get("id_profile"), array("id"=>0) ) );

	$page->addVar( 'edicion', 'listaDeGrupos', genComboGrupos() );

	$grupos = genGroupUser($usuario->get("id_user")    );

	//
	$page->addVar( 'edicion', 'groupsismember', implode(",",$grupos ) );

	$out = "";

	foreach( $grupos as $idgrupo){
		if ($idgrupo){
			include_once("class/group.class.php");
			$nombreGrupo = getNombreGrupoFromId($idgrupo);
			//echo "<h1>info: '$idgrupo' ($nombreGrupo)</h1>";

			$out .= "<option value='".$idgrupo."' class='removeOnClick'>".html($nombreGrupo)."</option>";
		} else{
			
		}
	}
	$page->addVar( 'edicion', 'lista_groupsismember',$out );




	$page->addArrayFromCursor( 'edicion',$usuario, array("id_user","name","user_login","pass_login","s_name1","s_name2","phone","email")  );
}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_usuarios.txt' );

	$maxfilas = $_SESSION[ $template["modname"] . "_list_size"];
	$min = intval($_REQUEST["min"]);
	
	$list = array();

	$sql = "SELECT * FROM users WHERE deleted='0' $extracondicion ORDER BY name ASC LIMIT $min,$maxfilas";

	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_user"], "name"=>$row["name"] ,"s_name1"=>$row["s_name1"] , "s_name2"=>$row["s_name2"] );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );
	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();


?>
