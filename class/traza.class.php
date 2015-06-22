<?php



include_once("class/group.class.php");
include_once("class/users.class.php");


function getNombreEstado($id_estado){
	//static $estados = array();

	$sql = "SELECT * FROM status WHERE id_status = '$id_estado' LIMIT 1";
	$row = queryrow($sql);

	return $row["status"];
}




/*
 *  Devuelve un array con todos los datos de traza de un pedido
 *  @param id_comm
 */
function genTrazaPorPedido($id_comm){

	$id_comm = CleanID($id_comm);

	$sql = "SELECT * FROM trace WHERE (id_comm = '$id_comm') ORDER BY date_change ASC ";
	$res = query($sql);

	$filas = array();

	/*
       	id_comm  	int(10)  	 	UNSIGNED  	No  	 	 	  Navegar los valores distintivos   	  Cambiar   	  Eliminar   	  Primaria   	  Único   	  Índice   	 Texto completo
	id_user 	smallint(5) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_group 	smallint(5) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_status
     */

	$numFilas = 0;
	$estaba_antes_id = 0;
	$estaba_antes_tiempo = "";
	$estaba_antes_estado = "";

	while($row = Row($res) ){

		$id_usuario	= $row["id_user"];
		$id_group	= $row["id_group"];
		$id_status	= $row["id_status"];
		$hora		= $row["date_change"];


		if ( $estaba_antes_id){  //si estaba antes en una delegacion
			//ha estado N minutos en $estaba_antes_id
			$t1 = $hora;
			$t2 = $estaba_antes_tiempo;

			$dx = ($t1 - $t2)/60;

			if ( $estaba_antes_estado != "eliminado" and $estaba_antes_estado!="tramitado"){
				$minutos[ $estaba_antes_id ] += $dx;
				$enestados[ $estaba_antes_estado ] += $dx;
				if ($dx >0)
					$totalMinutos += $dx;
			}
		}


		//$s_hora		= CleanDatetimeFromDB( $hora );
		$usuario	= html( getNombreUsuarioFromId( $id_usuario ) );
		$grupo = html( getNombreGrupoFromId( $id_delegacion ) );
		$estado	= html( getNombreEstado($id_estado) );

		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";

		$datosfila = array();

		$datosfila["usuario"] = $usuario;
		$datosfila["grupo"] = $grupo;
		$datosfila["estado"] = $estado;

		$filas[] = $datosfila;

		$estaba_antes_id = $id_delegacion;
		$estaba_antes_tiempo = $hora;
		$estaba_antes_estado = $estado;
		$numFilas++;
	}

	/* Desde el ultimo registro hasta ahora mismo*/
	$hora = time();//date("Y-m-d H:i:s");//hora actual
	if ( $estaba_antes_id ){
			//ha estado N minutos en $estaba_antes_id
			$t1 = $hora;
			$t2 = $estaba_antes_tiempo;

			$dx = ($t1 - $t2)/60;

			if ( $estaba_antes_estado != "eliminado" and $estaba_antes_estado!="tramitado"){
				$minutos[ $estaba_antes_id ] += $dx;
				$totalMinutos += $dx;
				$enestados[ $estaba_antes_estado ] += $dx;
			}
	}



	return $filas;
}




?>