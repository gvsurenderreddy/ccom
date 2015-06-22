<?php

/**
 * Ayudas para acceso a datos
 *
 * @package ecomm-aux
 */


define ("FORCE",1);

$link = falsE;
$UltimaInsercion = false;
$FilasAfectadas = false;



/*
 * Llama un procedimiento y devuelve la primera fila de datos.
 */
function ProcedureRow($sql){
	global $link;
	$row = false;

	$posible = mysqli_multi_query($link, $sql);

	if (!$posible){
		LogSQLErroneo($sql);
		return $false;
	}

    if ($result = mysqli_store_result($link)) {
       $row = mysqli_fetch_row($result);
       mysqli_free_result($result);
    }


	/*
     * Necesario para asegurarnos que se recicle la memoria. Deberia ser suficiente con free_result, pero no.
     */
	mysqli_close($link);
	$link = false;
	forceconnect();

	return $row;
}



function Row($res) {

	if(!$res){
		error(__FILE__ . __LINE__ ,"ERROR requiriendo datos");
		return false;
	}

	$data = mysqli_fetch_assoc($res);
	if (!is_array($data)) {
		$data = mysqli_fetch_row($res);
	}

	return $data;
}


function LogSQLErroneo ($sql) {
	global $logSQL;

	error_log("E: sql($sql)");
}



function forceconnect(){
	//Solamente abre una conexion
	global $link;
	global $ges_database;
	global $global_host_db;
	global $global_user_db ;
	global $global_pass_db;

	$database = $ges_database;

	if (!$link) {
		//Si no se conecto antes, conecta ahora.
		$link = mysqli_connect($global_host_db, $global_user_db, $global_pass_db);
		if (!$link)
			error(__FILE__. __LINE__, "Fatal: No puedo conectar a la base de datos");
		else
			mysqli_select_db($link,$database);
	}
}

//$numQuery = 0;
//$querysRealizadas = array();

function query($sql=false,$nick="") {
	global $link;
	global $UltimaInsercion,$FilasAfectadas, $debug_sesion;
	global $ges_database;
	global $sqlTimeSuma;
	global $global_host_db;
	global $global_user_db ;
	global $global_pass_db;
	global $querysRealizadas, $numQuery;
	global $logSQL;

	$lastime = microtime(true);

	if (!isset($sql)) {
		error(__FILE__ . __LINE__ , "Fatal: se paso un sql vacio!");
		return false;
	}

	$database = $ges_database;
	$result = false;

	if (!$link) { forceconnect(); }

	if ($link) 	$result = mysqli_query($link,$sql) or LogSQLErroneo($sql);

	if (!$result) {
		$error = mysqli_error($link);
		die("Fallo de conexión en $sql o $link\n<br>\nTipo: $error");
	}

	$ahora = microtime(true);

	$sqlTimeSuma = $sqlTimeSuma + ($ahora - $lastime);

	$UltimaInsercion  =	mysqli_insert_id($link);
	$FilasAfectadas  = mysqli_affected_rows($link);


	//$querysRealizadas[ $numQuery++ ] = $sql;
	//$querysRealizadas[ $sql ] = 1;
	//$_SESSION["querysRealizadas"] = $querysRealizadas;

	//echo "<h1>$sql</h1>";


	return $result;
}



function CreaUpdate ($soloEstos, $data,$nombreTabla, $nombreID,$idvalue ) {
		global $link;

		$coma = false;
		$str = "";

		foreach ($data as $key => $value) {
			if ( in_array($key,$soloEstos) and $key != "0" ) {
				if ($coma)
					$str .= ",";

				$value = mysqli_escape_string($link,$value);

				$str .= " $key = '$value'";
				$coma = true;
			}
		}

		return "UPDATE $nombreTabla SET $str WHERE $nombreID = '$idvalue'";
}



function CreaUpdateSimple ($data,$nombreTabla, $nombreID,$idvalue ) {
		global $link;
		
		$coma = false;

		foreach ($data as $key => $value) {
			if (  $key != "0" and intval($key)==0 ) {
				if ($coma)
					$str .= ",";

				$value = mysqli_escape_string($link,$value);

				$str .= " `$key` = '$value'";
				$coma = true;
			}
		}

		return "UPDATE $nombreTabla SET $str WHERE $nombreID = '$idvalue'";
}


function queryrow($sql,$nick=false) {
	$res = query($sql,$nick);
	if (!$res){
		return false;
	}
	$row = Row($res);
	if (!is_array($row)){
		//echo "no es array...($sql)(" .var_export($row,true). ")\n";
		return false;
	}
	return $row;
}




?>
