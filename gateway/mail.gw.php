<?php


/**
 * Pasarela de correo
 *
 * @package ecomm-gateway
 */

define("NO_SESSION",1);

if(1)
	ini_set('memory_limit', '1024M');

include_once("tool.php");
//include_once("inc/comunications_fast.php");

include_once("inc/runmeforever.inc.php");

include_once("mailgw/lib.php");
include_once("class/correo.class.php");
include_once("class/channel.class.php");


$necesitaMantenimientoTablas = false;

function descodifica_mime($var){

	if (strstr($var,"iso-8859-1?")){
		$newvar = iconv_mime_decode($var,2,"iso-8859-1");
	} else if (strstr($var,"UTF-8?")){
		$newvar = iconv_mime_decode($var,2,"UTF-8");
	}

	if ($newvar)	$var = $newvar;

	return $var;
}


$config->Reload();//se asegura de tener los datos actualizados



echo "*** Corriendo pasarela de correo  ***" . $cr;

$rid  = rand();

cron_Cabecera();

log_evento("Gateway Email empieza","($rid)");
log_start();


$sql = "SELECT * FROM gw_emails WHERE disabled=0 ";
$res = query($sql);

while($row = Row($res)){
	$i=1;

	$usuario	= $row["pop3_user"];//usuario en usuario@dominio
	$host		= $row["pop3_domain"];//dominio
	$pop3server = $row["pop3_host"];//servidor de descarga
	$password	= $row["pop3_password"];//puerto imap
	$puerto		= $row["pop3_port"];//puerto imap
	$param		= $row["pop3_extraparams"];//  cosas como "/ssl/novalidate-cert"
	

	$cadenaConexion = "{".$pop3server.":".$puerto."/pop3".$param."}INBOX";

	echo timestamp().  "Empieza la pasarela " . $cr;


	$direccion_usa = $usuario . "@" . $host;

	$mbox = imap_open($cadenaConexion, $direccion_usa , $password);


	if (!$mbox){
		echo (timestamp(). "No se ha podido conectar con el servidor ($direccion_usa con $cadenaConexion) ". $cr);
		continue;
	}

	cron_flush();

	$headers = imap_headers($mbox);

	if ( $headers == false) {
		echo timestamp(). "No hay mensajes nuevos". $cr;
	} else {
		$numMensaje = 0;
		foreach ($headers as $val) {

			//continue;//DEBUG


			cron_flush();

			$numMensaje++;
			$MC = imap_check($mbox);
			$resultados = imap_fetch_overview($mbox,$numMensaje.":{$MC->Nmsgs}",0);
			echo timestamp() . "Recuperando datos de correo [$numMensaje] id(".$MC->Nmsgs.") ...". $cr;


			$maxMensajes = 200;
			if ($numMensaje>$maxMensajes){
				echo timestamp() . "Hemos recuperado $maxMensajes, hay mas pero de momento salimos.". $cr;
				return;
			}

			foreach ($resultados as $detalles) {

				$eml = "";
				$cuerpoHTML = "";
				$asunto = "";
				$origenemail = "";
				$fechahora = "";
				$data = array();

				$msgno = $detalles->msgno;
				$numMensaje++;

				if(0)echo timestamp(), " dump: ". var_export($detalles, true) . "\n";

				$asunto = descodifica_mime($detalles->subject);

				$origenemail = $detalles->from;
				$origenemail = descodifica_mime($origenemail);

				log_evento("emailin captura email","($proceso) $origenemail");


				echo timestamp(), "Procesando: correo[$msgno] de [$origenemail] asunto: [$asunto]" , $cr;


				if ($detalles->size){
						echo timestamp(), "Mensaje de [$origenemail] tamagno: ", $detalles->size , $cr;
				}


				$currentmen =  memory_get_usage();
				$maxmem = memory_get_peak_usage();

				if(1)
					echo timestamp(), "memoria en uso: $currentmen, memoria maxima usada: $maxmem" , $cr;


				$timestamp = strtotime($detalles->date);
				$fechahora = date('Y-m-d H:i:s', $timestamp);

				$eml .= imap_fetchheader($mbox, $msgno);
				$partebody = imap_body($mbox,$msgno);
				$eml .= $partebody;

				if(1){
					echo timestamp(), "borrando correo[$msgno] del buzon" , $cr;
					imap_delete($mbox, $msgno);
				}

				$file = "email_". date("ymd")."_".md5($detalles->date . $detalles->to) . ".eml";

				echo timestamp() , "info: $asunto,$origenemail,$fechahora gen eml: $file". $cr;

				$correo = new Correo();
				$correo->set("email_in_out","in");
				$correo->set("email_time_provider",$fechahora);
				$correo->set("email_time_system",date("Y-m-d H:i:s"));
				$correo->set("email_receiver",$detalles->to);
				$correo->set("email_sender",$origenemail);
				$correo->set("email_subject",$asunto);
				$correo->set("email_body",$eml);			

				$data = array();				
				
				$data["id_channel"] = getChannelFromEmail($usuario . "@". $host);
				
				$correo->AltaComunicacion($data);
				$necesitaMantenimientoTablas = true;

				$idcorreo = $correo->get("email_id_comm");

				echo timestamp(), "Generando cuerpo para idcorreo $idcorreo" , $cr;

				$data = generarCuerpoVisible_conImap($mbox,$msgno, $idcorreo );

				$cuerpoHTML = $data["cuerpoHTML"];
				$adjuntos	= $data["adjuntos"];

				$correo->set("email_preview_html",$cuerpoHTML);

				//echo timestamp(), "Generado(preview): " , str_replace("\n"," ",substr(strip_tags($cuerpoHTML),0,32))  , $cr;
				//echo timestamp(), "Generado(body): " , str_replace("\n"," ",substr(strip_tags($partebody),0,32))  , $cr;
				//echo timestamp(), "Generado(body): " , str_replace("\n"," ",$partebody)  , $cr;


				$correo->ProcesaAdjuntos($adjuntos);
				$correo->Modificacion();
		
				echo timestamp(), "Email de [$origenemail] dado de alta" , $cr;
				echo timestamp(), "----------------------------------------------" , $cr;

				//$numMensaje++;//test

				
				unset($data);
				unset($adjuntos);
				unset($cuerpoHTML);
				unset($eml);
				unset($detalles);
				unset($correo);
				unset($eml);
				unset($correo);
				unset($partebody);
				if ( function_exists( "gc_collect_cycles") ){
					gc_collect_cycles();
				} else {
					//usleep(100000); // Sleep for 100 miliseconds;
				}

			}

		}
	}

	echo timestamp().  "Operaciones completas. Cerrando conexión." . $cr;

	imap_close($mbox);



}

log_end();


if ( $necesitaMantenimientoTablas)
	actualizarTablaRapida();

cron_flush();
cron_final();


?>