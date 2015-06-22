<?php

/**
 *
 *
 * @package ecomm-gateway
 */

//chdir("..");

define("NO_SESSION",1);

include_once("tool.php");
include_once("inc/runmeforever.inc.php");
include_once("inc/pasarelas.inc.php");
include_once("gateway/faxingw/lib.php");
//include_once("class/comunicacion.class.php");
include_once("gateway/faxingw/fax.class.php");


//$cr = "\n<br>";
$cr = "\n";
echo "<pre>";


//TODO: quitar esto, esta para obligar a recargar la config, no deberia ser necesario
$_SESSION["parametros_globales"] = false;

$path_logs = getParametro("zfaxgw_path_logs_zfax");

$ultimarevision = getParametro("zfaxgw_ultima_captura");


$fecha_actual = date("Ymd");
$fecha_antigua = date("Ymd",$ultimarevision);


//Corremos para la fecha actual
$esCorrerHoy = ( $fecha_actual ==  $fecha_antigua );


if ($esCorrerHoy ) {
	$codigo_fecha = $fecha_antigua - 20000000;
	$ultimarevision_guardar = time();
} else {

	//tenemos que revisar una fecha en el pasado, y hacer para que 
	// se revise el siguiente dia a esa fecha en el futuro
	$ultimarevision_guardar = $ultimarevision + 24*60*60; // +1 dia
	
	$codigo_fecha = date("ymd",$ultimarevision);
}


$path_logzfax  = NormalizarPath($path_logs) . "Z-" . $codigo_fecha . ".LOG";



if (!file_exists($path_logzfax)){
  // query("UPDATE parametros SET enuso_faxin = 0");
  // log_evento("faxin sale","($proceso) i: no hay log para hoy");
   die ( marca() .  " No hay fichero de log para hoy ($path_logzfax)");
}


$oldId = 0;
$ficherosReconocidos = array();

$handle = fopen($path_logzfax, "rb");
$contents = fread($handle, filesize($path_logzfax));
fclose($handle);


echo marca() . "Analizando bloques" . $cr;

$numLineas = 0;

foreach( split("\n",$contents)  as $texto){
	$numLineas++;

	//echo print_r($out,true) . "\n";
	$datos = CapturaLineas($texto);

	$msgid = $datos["idmensaje"];

	if ( intval($msgid)>0){ //ignora informacion para msgid 0000000000
          if ($oldId != $msgid){
            //nuevo mensaje
            $oldId = $msgid;
          }

          if (!$ficherosReconocidos[$msgid]){
            $ficherosReconocidos[$msgid] = array();
          }

          $texto = $datos["texto"];
          $clave = Accion($texto);

          switch($clave){

                case "Incoming message":
                  //Incoming message ADMINIST:~RECDGY0
                  break;
                case "Routing fax":
                  //Routing fax to folder: 'C:\ARCHIV~1\ZETAFA~1\SERVER\Z-RECD\PDF'
                  break;

                case "Created file"://PDF
                  $ficheroPDF = extraPDFLineaRouting($datos["texto"]);
                  $ficherosReconocidos[$msgid]["PDF"] = $ficheroPDF;

                  echo marca()."[$msgid]: Encontrando fichero [$ficheroPDF]\n";
                  break;
                case "Created archive":

			      list( $stuff, $original) = split("archive file ",$texto);
			
				  //"Created archive file 92090005.G3N"
                  echo marca()."[$msgid]: Encontrando original [$original]\n";

				  $ficherosReconocidos[$msgid]["ORIGINAL"] = $msgid;
                  break;

                case "RecdOK":
                  //"RecdOK", "", "FAX", "34 976 345979", "1", "00:00:00", ""

                  list($rec,$dato1,$dato2,$numfax,$dato3,$dato4) = split('". "',$texto);

				  $ficherosReconocidos[$msgid]["FAX"] = $numfax;

				   // 'fecha' => '09-02-09',  'hora' => '09:15:13',
				  $fecha =  $datos["fecha"];
				  $hora =  $datos["hora"];
				  echo  marca(). "[$msgid]: Encontrando num fax [$numfax]\n";

				  $ficherosReconocidos[$msgid]["FECHAHORA"] =  "$fecha $hora";
				  break;

                default:
                  //echo marca() . "[$msgid] Desconocido: ($clave)". $cr;
                  break;
                }

          //echo $msgid . ": " . $datos["texto"] . "\n";
	}



}



echo marca() . "Analisis de bloques terminado ($numLineas lineas de datos)" . $cr;

echo marca() . " ---------------------------------------------------------------\n";
echo marca() . " FAX vistos:". count($ficherosReconocidos) . "\n";




foreach($ficherosReconocidos as $msgid=>$fichero){

  cron_flush();

  $pdf =  $fichero["PDF"];
  $fax =  $fichero["FAX"];
  $fechahora = $fichero["FECHAHORA"];

  echo marca(), "Procesando fax","($proceso) i: procesando ($pdf) ($fax)" . $cr;
  
  if ( strlen($pdf)<5){
	  echo marca(). "[$msgid]: [$pdf] es incompleto (se esperaba al menos 5 caracteres)" . $cr;
	  continue;
  }

/*
  $sql = "SELECT count(id_pedido) as existe FROM pedidos WHERE zfax_msgid ='$msgid' and eliminado=0";
  $row = queryrow($sql);

  if (intval($row["existe"])>0){
	//TODO: verificacion de msgid duplicados y resolucion de conflicto?
	echo "$msgid: [$pdf] ya esta en la base de datos\n";
	continue;
  }*/

   if ( zfax_pdf_conocido( $msgid) ){
	echo marca() . "[$msgid]: [$pdf] ya esta en la base de datos  " . $cr;
	continue;
  }

  echo marca(). "[$msgid]: Incorporando z:$msgid; [$pdf] en la base de datos" . $cr;

  $pedido = new ZFax();

  $pdf_system = $fichero["FAX"];
  $file_zfax  = $fichero["ORIGINAL"];
  $fax_sender	= "?????";
  $fax_receiver = "?????";

  $pedido->Alta($file_zfax, $pdf_system,$fax_sender,$fax_receiver,$msgid,$fechahora,$id_channel);
	//$file_zfax, $pdf_system,$fax_sender,$fax_receiver,$msgid,$fechahora,$id_channel



  echo  marca()."$msgid:  zfax_msg: $msgid\n";
  echo  marca()."$msgid:       pdf: $pdf\n";
  echo  marca()."$msgid:       fax: $fax\n";

	/*

  $path = getParametro("path_faxes");
  $pathDestino   = getParametro("path_faxes_final");

  CrearSiNoExiste($path);
  CrearSiNoExiste($pathDestino);

  $ficheroOrigen = NormalizarPath($path). $pdf;
  $pathDestino = NormalizarPath($pathDestino) ;

  $nuevoNombrefichero = $pedido->NormalizacionNombrePDF();//se toma
  $ficheroDestino = $pathDestino . $nuevoNombrefichero;

  $pedido->genNumPedido();//No es necesario en produccion actual, pero no debe estar en la siguiente version!.
  $pedido->Modificacion();


  if (!file_exists($ficheroOrigen) or !$pdf or $pdf==""){
    //TODO: error, no encontrado el PDF
	if( file_exists($ficheroDestino) ) {
		echo "$msgid: [$ficheroDestino] ya existe\n";
	} else {
		echo "$msgid: E: no existe PDF en [$ficheroOrigen] ni en destino [$ficheroDestino]\n";
		log_evento("faxin","($proceso) E: no encuentra [$ficheroOrigen]");
	}
  } else {
	if ( copy( $ficheroOrigen, $ficheroDestino) ){
		$pedido->NormalizacionNombrePDF($nuevoNombrefichero);//se guarda
		echo "$msgid: Se ha copiado PDF a [$ficheroDestino]\n";
		log_evento("faxin","($proceso) I: copiado fichero Ok");
	} else {
		//TODO: informar error ¿permisos de escritura del usuario php?
		if( file_exists($ficheroDestino)) {
			echo "$msgid: [$ficheroDestino] ya existe\n";
			log_evento("faxin","($proceso) E: error al copiar [$ficheroDestino] ya existe");
		} else {
			echo "$msgid: E: no se puede mover PDF a [$ficheroDestino]\n";
			log_evento("faxin","($proceso) E: error al copiar [$ficheroDestino]");
		}
	}
  }
  */

}
    



echo marca()."cod fecha: " . $codigo_fecha . $cr;
echo marca()."el log:    " . $path_logzfax . $cr;
echo marca()."path logs: " . $path_logs . $cr;
echo marca()."esHoy:     " . $esCorrerHoy . $cr;
echo marca()."ultimarev: " . $ultimarevision . $cr;
echo marca()."ultimarevG:" . $ultimarevision_guardar . $cr;
echo marca()."time:      " . time() . $cr;


echo "</pre>";

setParametro("zfaxgw_ultima_captura",$ultimarevision_guardar);


?>