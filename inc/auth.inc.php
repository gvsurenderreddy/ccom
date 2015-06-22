<?php

/**
 * Sistemas auxiliares de autentificacion
 *
 * @package ecomm-aux
 */


function debug($line,$text){

	echo "<div style='display:block;background-color: white;color:gray'>[$line] ". html($text) . "</div>";
}

function zdebug() {};


function genGroupUser($id_user){
	$id_user_s = sql($id_user);
	
	$sql = "SELECT id_group FROM user_groups WHERE id_user='$id_user_s'";
	$res = query($sql);

	$grupos = array();

	while( $row = Row($res) ){
		$grupos[] = $row["id_group"];
	}

	return $grupos;
}


function puedeActualVerCanal($id_task){
	$id_profile = getSesionDato("id_profile_active");


	$name = "puedeActualVerCanal_" . $id_task . "_" . $id_profile;

	if (isset($_SESSION[$name])){
			return $_SESSION[$name];
	}


	//zdebug(__LINE__, "Para usuario logueado: task:$id_task,way:$way");

	$way = puedeVerCanalPerfil($id_task,$id_profile);

	//permisos especificos de usuario
	if ($way=="d") {
		$_SESSION[$name] = false;
		return false;
	}
	if ($way=="a") {
		$_SESSION[$name] = true;
		return true;
	}


	//zdebug(__LINE__, "Para grupos:");


	//chequeo permisos especificos de grupo
	$grupos = getSesionDato("user_groups");


	//zdebug(__LINE__, var_export( $grupos,true) ) ;

	$way_final = "";

	foreach($grupos as $key=>$id_grupo){

		$id_profile = getIdProfileFromGroup($id_grupo);


		$way = puedeVerCanalPerfil($id_task,$id_profile);


		//zdebug(__LINE__, "key: $key, grupo:$id_grupo,tiene id_prof:$id_profile,way:$way");

		//permisos especificos de este grupo
		if ($way=="d") {
			//zdebug(__LINE__, "task:$id_task,way:$way => no se podra acceder");
			$way_final = "d"; // Si por un grupo estamos prohibidos, estamos definitivamente prohibidos
		} else 		if ($way=="a") {
			//zdebug(__LINE__, "task:$id_task,way:$way");
			if ($way_final== "")
				$way_final = "a"; // Si por algun grupo estamos permitidos, entonces estamos permitidos
		} else {
			//zdebug(__LINE__, "task:$id_task,way:$way");
		}

	}

	//permisos especificos de usuario
	if ($way_final=="d") {
			//zdebug(__LINE__, "grupo:($id_task) NO tiene permitido el acceso");
			$_SESSION[$name] = false;
			return false;// al menos prohibido en un grupo
	}
	if ($way_final=="a") {
			//zdebug(__LINE__, "grupo:($id_task) tiene permitido el acceso");
			$_SESSION[$name] = true;
			return true;
	}

	$_SESSION[$name] = true;
	//Si no hay nada especifico, no hay restricciones
	return true;
}


function puedeVerCanalPerfil( $id_task,$id_profile) {

	$name = "puedeVerCanalPerfil_" . $id_task . "_" . $id_profile;

	if (isset($_SESSION[$name])){
			return $_SESSION[$name];
	}

	$sql = "SELECT way FROM allowdisallows WHERE id_profile=%d AND path LIKE 'canales/idcanal:%d' LIMIT 1 ";

	$sql = sprintf($sql,$id_profile,$id_task);

	zdebug(__LINE__,$sql);
	
	$row = queryrow($sql);

	//Permisos especificos de este usuario (seran excepciones respecto al grupo)
	if ($row["way"]=='d' || $row["way"]=='a'){
		$_SESSION[$name] = $row["way"];
		return $row["way"];
	}
	
	$_SESSION[$name] = "";
	return "";
}



function canUsePage($page, $id_profile ) {

	$name = "canUsePage_" . $page . "_" . $id_profile;

	if (isset($_SESSION[$name])){
			return $_SESSION[$name];
	}


	if (!$id_profile)
		return true;//TODO: ???

	$sql = "SELECT id_allowdisallow as id FROM allowdisallows WHERE id_profile='$id_profile' AND path LIKE '$page/%' AND way='d' LIMIT 1 ";
	$row = queryrow($sql);

	if ($row["id"]>0){
		$_SESSION[$name] = false;
		return false;
	}

	$_SESSION[$name] = true;
	return true;
}


function getIdProfileFromGroup($id_group){

	if ( isset($_SESSION["getIdProfileFromGroup_" . $id_group ]) ){
		return $_SESSION["getIdProfileFromGroup_" . $id_group ];
	}

	$sql = "SELECT id_profile as id FROM groups WHERE id_group='$id_group'";
	$row = queryrow($sql);

	$_SESSION["getIdProfileFromGroup_" . $id_group ] = $row["id"];

	return $row["id"];
}

function canRegisteredUserAccess($path){

	if (isset($_SESSION["canRegisteredUserAccess_" . $path])){
			return $_SESSION["canRegisteredUserAccess_" . $path];
	}



	$res = canUse($path);//comprueba datos de sesion, y profile activo.

	if (!$res["ok"]) {

		$_SESSION["canRegisteredUserAccess_" . $path] = $res;
		return $res;//devuelve el permiso fallido
    }
	

	$grupos = getSesionDato("user_groups");

	foreach($grupos as $id_grupo){

		$id_profile = getIdProfileFromGroup($id_group);

		$ok = canUsePage($page,$id_profile);
		if (!$ok){
			$res["ok"] = false;
			$_SESSION["canRegisteredUserAccess_" . $path] = $res;
			return $res;//devuelve el permiso fallido
		}
	}


	$_SESSION["canRegisteredUserAccess_" . $path] = $res;
	return $res;//devuelve los permisos de usuario, que seran un ok.
}


function canUse( $path ) {

	if (isset($_SESSION["canUse_" . $path])){
			return $_SESSION["canUse_" . $path];
	}

	$result = array();
	$result["ok"] = true;
	$result["path"] = $path;

	$id_user = getSesionDato("id_user");
	$id_profile = getSesionDato("id_profile_active");

	if (!$id_user)  {
		$result["ok"] = false;
		$result["error"] = "NOUSER:Usuario desconocido ";

		$_SESSION["canUse_" . $path] = $result;

		return $result;
	}

	list($page,$action,$extra) = split("/",$path);

	if (!canUsePage($page,$id_profile) ){
		$result["ok"] = false;
		$result["error"] = "NOPAGEACCESS:El usuario no tiene derecho para la pagina ";

		$_SESSION["canUse_" . $path] = $result;
		return $result;
	}


	return $result;
}



/* NOTA: las siguientes funciones estan obsoletas */

/*
 *
 */
function SimpleAuth($module,$password){
	if (!$password){
		echo _("Este modulo esta desactivado");	
		exit();	
	}	
	
	if (!$_SESSION["Permitir_". $module]){
		$ok = false;
		if (isset($_POST["pass"])){
			if ($_POST["pass"]==$password){
				$ok = 1;
				$_SESSION["Permitir_". $module] = true;
			}
		}

	if (!$ok){	
			echo "<form method='post' name='form'>";
			echo "<input id='inputbox' type=password name='pass' onkeypress=\"if (event.which == 13) form.submit();\">" ;		
			echo "</form>" ;
			echo "<script> document.getElementById('inputbox').focus() </script>";
			exit();		
		} 	
	}	
}

// - - - - - - - 

function Admite($noseque,$modulo=false){
	global $modulos;

	//Si exige modulo, pero este no esta disponible	
	if ($modulo and !$modulos[$modulo] )
		return false;	
	
	$val = getSesionDato("PerfilActivo");
	return 	$val[$noseque];
}

function xulAdmite($noseque,$modulo=false){
	if (Admite($noseque,$modulo)){
		return "";
	}else{
		return " disabled='true' ";		
	}

}

function gulAdmite($noseque,$modulo=false){		
	echo xulAdmite($noseque,$modulo);
}

function RegistrarTiendaLogueada($id){
	$id = CleanID($id);
	setSesionDato("IdTienda",$id);	
}

function RegistrarUsuarioLogueado($id){
			
	$sql = "SELECT Nombre,IdPerfil,AdministradorWeb FROM ges_usuarios WHERE IdUsuario='$id'";
	$row = queryrow($sql,"¿como se llama usuario?");
	if($row)
		$nombre = $row["Nombre"];
	
	
	setSesionDato("NombreUsuario",$nombre);
	setSesionDato("IdUsuario",$id);
	
	if ($row["AdministradorWeb"])
		setSesionDato("UsuarioAdministradorWeb",1);
	else
	  	setSesionDato("UsuarioAdministradorWeb",false);
	
	$user = getUsuario($id);
	$_SESSION["EsAdministradorFacturas"] = $user->get("AdministradorFacturas");
	
	//Autentificacion para modulos novisuales																							
	$_SESSION["AutentificacionAutomatica"] = true;

	$idPerfil = $row["IdPerfil"];
	
	$sql = "SELECT * FROM ges_perfiles_usuario WHERE IdPerfil = '$idPerfil'";
	
	$row = queryrow($sql);
	if (!$row)
		return;
	
	setSesionDato("PerfilActivo",$row);	
}


function identificacionLocalValidaMd5($identificador,$passmd5){
	global $_motivoFallo;
	
	//$randString = $_SESSION["CadenaAleatoria"];
	
	$identificador = CleanLogin($identificador);	
	$datosValidos = strlen($identificador)>1 and strlen($passmd5)>1;
	
	if (!$datosValidos) {
		//$_motivoFallo = "datos'$identificador o $passmd5 nulos'";
		return false;	
	}		
	
	$sql = "SELECT IdLocal,Password FROM ges_locales WHERE Identificacion = '$identificador' AND Eliminado=0";
	$row = queryrow($sql);
	if (!$row) {
		//$_motivoFallo = _("No encuentra local");			
		return false;
	}
	
	$valido = md5($row["Password"]);// . $randString);
	
	if ( $valido != $passmd5) {
		//$_motivoFallo = "DEBUG: datos'$valido != $passmd5', para " . $row["Password"];		
		return false;		
	}
		
	return $row["IdLocal"];	
}


function identificacionUsuarioValidoMd5($identificador,$passmd5){
	global $_motivoFallo;
	
	//$randString = $_SESSION["CadenaAleatoria"];
		
		
	$datosValidos = strlen($identificador)>1 and strlen($passmd5)>1;
	
	if (!$datosValidos)
		return false;		
	
	$sql = "SELECT IdUsuario, Password FROM ges_usuarios WHERE Identificacion = '$identificador' AND Eliminado=0";
	$row = queryrow($sql);
	if (!$row)
		return false;

	$valido = md5($row["Password"]);// . $randString);
	if ( $valido != $passmd5) {
		$_motivoFallo = "datos'$valido != $passmd5'";		
		return false;		
	}
		
		
	return $row["IdUsuario"];	
}


function identificacionLocalValida($identificador,$pass){
	
	$datosValidos = strlen($identificador)>1 and strlen($pass)>1;
	
	if (!$datosValidos)
		return false;	
		
	
	$sql = "SELECT IdLocal FROM ges_locales WHERE Identificacion = '$identificador' AND Password = '$pass'";
	$res = query($sql);
	if (!$res)
		return false;
		
	$row = Row($res);
	if (!is_array($row))
		return false;
		
	return $row["IdLocal"];	
}

function identificacionUsuarioValido($identificador,$pass){
	
	$datosValidos = strlen($identificador)>1 and strlen($pass)>1;
	
	if (!$datosValidos)
		return false;		
	
	$sql = "SELECT IdUsuario FROM ges_usuarios WHERE Identificacion = '$identificador' AND Password = '$pass'";
	$res = query($sql);
	if (!$res)
		return false;
		
	$row = Row($res);
	if (!is_array($row))
		return false;
		
	return $row["IdUsuario"];	
}

function SimpleAutentificacionAutomatica($subtipo=false,$redireccion=false){
	if(!isset($_SESSION["AutentificacionAutomatica"]) || !$_SESSION["AutentificacionAutomatica"]){
		//Si no esta autentificado, la pagina termina aqui mismo.
		// esto es valido para modulos sin parte visual,
		// y deberia solo ocurrir cuando se trata de acceder directamente		
		// por un cracker.
		
		if ($redireccion) {
			session_write_close();
			header("Location: $redireccion");
		}		
		exit;	
	}	
}
 
?>
