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

include_once("class/users.class.php");

switch($modo){
	case "profundo":
		$mostrarProfundo = true;

		break;
	case "guardarcambios":


		$usuario = new Usuario();

		$id =  getSesionDato("id_user");

		if ( $usuario->Load($id) ){

			$usuario->set("name", $_POST["name"]  , FORCE);
		//	$usuario->set("user_login", $_POST["user_login"]  , FORCE);
			//$usuario->set("pass_login", $_POST["pass_login"]  , FORCE);
			$usuario->set("s_name1", $_POST["s_name1"]  , FORCE);
			$usuario->set("s_name2", $_POST["s_name2"]  , FORCE);
			$usuario->set("phone", $_POST["phone"]  , FORCE);
			$usuario->set("email", $_POST["email"]  , FORCE);

			$usuario->Modificacion();
		}
		$mostrarNormal = true;
		break;

	default:
		$mostrarNormal = true;
		break;
}



if ($mostrarNormal){


	$page->configMenu("sololistar");

	$page->setAttribute( 'listado', 'src', 'panel.txt' );

	$list = array();


	$id_user_s = sql($_SESSION["id_user"]);

	$row = queryrow("SELECT * FROM users WHERE id_user='$id_user_s'");

	//$resultrow = queryrow("SELECT * FROM status WHERE id_status=0 LIMIT 1");
	//$list[] = retorno( $trans->_("Tabla status inicializada"),$trans->_("Correcta"),$trans->_("No tiene elemento 0"),$resultrow);

	$list[] = array( "label"=>$trans->_("Nombre"), "dato"=>($row["s_name1"] ." " . $row["s_name2"] )  );
	$list[] = array( "label"=>$trans->_("Email"), "dato"=>($row["email"]) );
	$list[] = array( "label"=>$trans->_("Telefono"), "dato"=>($row["phone"])  );
	$list[] = array( "label"=>$trans->_("Identificador"), "dato"=>($row["user_login"])  );
//	Textos completos  	id_user 	name 	s_name1
//	s_name2 	email 	phone 	user_login 	pass_login 	date_in
//	eac_rights 	id_profile 	deleted

	$page->addRows('list_entry', $list );

	$id_user = getSesionDato("id_user");
	$user = new Usuario();
	$user->Load($id_user);

	//$datos = $user->export();
	$page->addArrayFromCursor("list",$user,array("name","s_name1","s_name2","email","phone") );

	$page->addVar("list","modname",$template["modname"]);

	/*
     *
2	name	name	text
3	s_name1	s_name1	text
4	s_name2	s_name2	text
5	email	email	text
6	phone	phone	text
     *
     */



}



$page->Volcar();



?>