<?php
/**
 * Pagina de entrada a la aplicación
 *
 *
 * @package ecomm-core
 */

include("tool.php");
include("inc/comunications_fast.php");//se actualizan datos antes de loguear


$page    =    &new Pagina();
$page->setRoot( 'templates' );
$page->setOption( 'translationFolder', 'translations' );
$page->setOption( 'lang',  "es" );

switch($modo){
	case "login":
		$login = trim($_REQUEST["login"]);
		$pass = trim($_REQUEST["pass"]);

		$login_s = sql($login);
		$pass_s = sql($pass);


		$sql = "SELECT * FROM users WHERE (user_login='$login_s') AND (pass_login='$pass_s') AND deleted='0' ";
		$row = queryrow($sql);

		$esLogueado = $row["id_user"];

		if (!$esLogueado){
			$page->addVar('page', 'esErrorLogin', 'error');
			break;
		}

		$page->addVar('page', 'esErrorLogin', 'false');


		limpiarSesion();

		setSesionDato("id_user",$row["id_user"]);
		setSesionDato("id_profile_active",$row["id_profile"]);
		setSesionDato("user_groups",genGroupUser($row["id_user"]) );
		setSesionDato("user_nombreapellido",$row["s_name1"] . " " . $row["s_name2"]  );
	
		$id = CleanID( getSesionDato("id_user") );
		$sql = "UPDATE users SET eslogueado=1 WHERE id_user = '$id' ";
		query($sql);

		actualizarTablaRapida();//se va a cargar 

		header("Location: modcentral.php?modo=breakframes");
		exit();
		break;
	default:
		break;
}




$page->readTemplatesFromInput('login.txt');


$page->addVar('headers', 'titulopagina', $trans->_('Login') );
$page->addVar('page', 'info', print_r( getSesionDato("user_groups"), true ));


$page->addVar("cabeza","nologin","<!--");
$page->addVar("cabeza","nologin2","-->");


$page->Volcar();


if(0){
	echo "<!--";
	echo "<h1>datos:</h1>";
	echo var_dump(getSesionDato("user_groups"),true);
	echo "-->";
}

?>
