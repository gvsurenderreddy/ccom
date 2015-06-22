<?php


function Accion($texto){
     //Extra las dos primeras palabras del texto descriptivo de la accion, usaremos estas palabras para identificar la accion
    $texto = str_replace('"','',$texto);
    $texto = str_replace(',','',$texto);
    $texto = str_replace(":","",$texto);
    list($primera,$segunda) = split(" ",$texto);

    return trim($primera . " ". $segunda);
}




function CapturaLineas($lineatexto){
	//5801: 09-02-09 09:15:13,CAPI-1    ,        ,00000000,0  ,0000,LIB_I_INFO          ,"Ring-in detected - incoming call"
	$out = array();

	//Extraemos datos menores,  fecha esta aun con otras cosas
	list( $lineaycrap, $lineamodem,$vacio1,$idmensaje,$num5,$num5,$tipo) = split(",",$lineatexto);

	//Extraemos todas las particulas del texto.. como puede contener comas, nos fiamos solo del tipo.
	list($crap,$realdatostexto) = split($tipo. ",",$lineatexto);

	//Extraemos las partes mezcladas en lineaycrap
	list( $num1, $fecha, $hora) = split(" ",$lineaycrap);

	//le quitamos ":" al num1
	list($num1) = split(":",$num1);



	$out["idmensaje"] = trim($idmensaje);
	$out["fecha"] = trim($fecha);
	$out["hora"] = trim($hora);
	$out["linea"] = trim($lineamodem);
	$out["tipo"] = trim($tipo);
	$out["texto"] = limpiar($realdatostexto,$pad=3);

	return $out;
}

function zfax_pdf_conocido($msgid){
	$msgid_s = sql($msgid);
	$sql = "SELECT id FROM gw_zfax WHERE zfax_msgid='$msgid_s' LIMIT 1";

	$row = queryrow($sql);


	return $row["id"] >0 ;
}

function zfax_pdf_marcarconocido($msgid,$id_comm){
	$msgid_s = sql($msgid);
	$sql = "INSERT gw_zfax (zfax_msgid,fax_id_comm) VALUES ('$msgid_s','$id_comm')";
	
	query($sql);
}



function limpiar($textoConQuotes,$pad=3){

    $textoConQuotes = str_replace("\n","",$textoConQuotes);

	$len = strlen( $textoConQuotes);
	$corte = $len-$pad;

	if ($corte<0)
		$corte = 0;

	$texto = substr( $textoConQuotes,1,$corte);

	return $texto;
}

function extraPDFLineaRouting($linea){
    //Extramos el fichero PDF de esto:
    //Created file: 'Fax from '' to 'ADMINIST'.PDF'

    list($crap,$texto) = split("Created file:",$linea);

    return limpiar(trim($texto),2);
}



?>