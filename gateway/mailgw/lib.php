<?php

/**
 * Librerias de gateway de correo
 *
 * @package ecomm-mailgw
 */






function getIconFromFilename($filename){
	$tipo = "doc.gif";

	$path_parts = pathinfo($filename);
	$extension = $path_parts['extension'];

	switch($extension){
		case "pdf":
			$tipo = "pdf.gif";
			break;
		case "ppt":
			$tipo = "ppt.gif";
			break;
		case ".odt":
		case ".doc":
			$tipo = "word.gif";
			break;
		case "xls":
			$tipo = "xls.gif";
			break;
		case "zip":
			$tipo = "zip.gif";
			break;
		case "jpg":
			$tipo = "jpg.gif";
			break;
		case "rar":
		case "tar":
		case "tar.gz":
		case "gz":
			$tipo = "paquete.gif";
			break;
		case "exe":
		case "msi":
		case "com":
			$tipo = "exe.gif";
			break;
		default:
			break;
	}

	return "<img src='img/iconos/$tipo' align='absmiddle' border='0'/>";
}




/* ------------------------------------------------------ */


$htmlmsg = "";
$plainmsg = "";
$charset = "";
$attachments = false;
$partesadjuntos = array();

function getmsg($mbox,$mid) {
    // input $mbox = IMAP stream, $mid = message id
    // output all the following:
    global $charset,$htmlmsg,$plainmsg,$attachments,$partesadjuntos;

    $htmlmsg = $plainmsg = $charset = '';
    $attachments = array();
	$partesadjuntos = array();

    // HEADER
    //$h = imap_header($mbox,$mid);
    // add code here to get date, from, to, cc, subject...

    // BODY
    $s = imap_fetchstructure($mbox,$mid);
    if (!$s->parts)  // simple
        getpart($mbox,$mid,$s,0);  // pass 0 as part-number
    else {  // multipart: cycle through each part
        foreach ($s->parts as $partno0=>$p)
            getpart($mbox,$mid,$p,$partno0+1);
    }
}

function getpart($mbox,$mid,$p,$partno) {
    // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
    global $htmlmsg,$plainmsg,$charset,$attachments,$partesadjuntos;

    // DECODE DATA
    $data = ($partno)?
        imap_fetchbody($mbox,$mid,$partno):  // multipart
        imap_body($mbox,$mid);  // simple
    // Any part may be encoded, even plain text messages, so check everything.
    if ($p->encoding==4)
        $data = quoted_printable_decode($data);
    elseif ($p->encoding==3)
        $data = base64_decode($data);

    // PARAMETERS
    // get all parameters, like charset, filenames of attachments, etc.
    $params = array();
    if ($p->parameters)
        foreach ($p->parameters as $x)
            $params[strtolower($x->attribute)] = $x->value;
    if ($p->dparameters)
        foreach ($p->dparameters as $x)
            $params[strtolower($x->attribute)] = $x->value;

    // ATTACHMENT
    // Any part with a filename is an attachment,
    // so an attached text file (type 0) is not mistaken as the message.
    if ($params['filename'] || $params['name']) {
        // filename may be given as 'Filename' or 'Name' or both
        $filename = ($params['filename'])? $params['filename'] : $params['name'];
        // filename may be encoded, so see imap_mime_header_decode()
        $attachments[$filename] = $data;  // this is a problem if two files have same name
		$partesadjuntos[$filename] = $partno;
    }

    // TEXT
    if ($p->type==0 && $data) {
        // Messages may be split in different parts because of inline attachments,
        // so append parts together with blank row.
        if (strtolower($p->subtype)=='plain') {
            $plainmsg .= trim($data) ."\n\n";
        } else {
            $htmlmsg .= $data ."<br><br>";
		}

        $charset = $params['charset'];  // assume all parts are same charset
    } else if ($p->type==2 && $data) {
		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
        $plainmsg .= $data."\n\n";
    }

    // SUBPART RECURSION
    if ($p->parts) {
        foreach ($p->parts as $partno0=>$p2)
            getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
    }
}



function generarCuerpoVisible_conImap($mbox,$num,$id_pedido){
	global $htmlmsg,$plainmsg,$charset,$attachments,$partesadjuntos;

	$salida = array();


	$cuerpo = "";
	//Extra los datos
	getmsg($mbox,$num);


	if ($htmlmsg)
			//$cuerpo = nl2br(imap_qprint($cuerpo));
			$cuerpo = $htmlmsg;
	else {
			$cuerpo = nl2br($plainmsg);
	}

	if (!strstr($charset,"UTF")) {

		$cuerpo_old = $cuerpo;

		if (strstr($charset,"8859"))
			$cuerpo_tmp = iconv("ISO-8859-1","UTF-8",$cuerpo);
		else
			$cuerpo_tmp = iconv($charset,"UTF-8",$cuerpo);

		if ($cuerpo_tmp)
			$cuerpo = $cuerpo_tmp;
		else
			$cuerpo = $cuerpo_old;
	}

	$adjuntos = array();


	$num = count ($attachments);

	$out = "";
	$numvisibles = 0;
	foreach($attachments as $filanem=>$data){
		$numvisibles ++;
		$parte = $partesadjuntos[$filanem];

		$path = getParametro("path_emailadjuntos");

		$name = $id_pedido . "_" . md5( $id_pedido . "_" . $parte . "_SALT_name_");

		$enname = base64_encode($filanem . "_SALT_enname_");

		$filename = NormalizarPath($path) . $name;

		$s_filanem = urlencode( $filanem );

		$fp = fopen($filename, "w");
		if ($fp) {
			fwrite($fp, $data);
			fclose($fp);
			$filanem_s = iconv_mime_decode($filanem,2,"UTF-8");

			//NOTA: no quieren esta feature.
			//$icon = getIconFromFilename($filanem_s);

			//$out .= "<a target='_new' href='lectoradjuntos.php?modo=extrae&enname=$enname&name=$name&filename=$s_filanem'>$icon Adjunto ".html($filanem_s)."</a><br>";

			$newadjunto = array("filename"=>$filename,"description"=>$filanem);

			$adjuntos[] = $newadjunto;

		} else {
			//$out .= "no pudo guardar $filenem<br/>";

			echo "<h1>";
		}
	}


	$salida["cuerpoHTML"] = $cuerpo;
	$salida["adjuntos"] = $adjuntos;


	//unset($cuerpo);
	//unset($adjuntos);

	/*
	echo "<xmp>";
	echo print_r($salida);
	echo "</xmp>";*/

	return $salida;
}



/* ------------------------------------------------------ */



function removeUnsafeAttributesAndGivenTags($input, $validTags = ''){
    $regex = '#\s*<(/?\w+)\s+(?:on\w+\s*=\s*(["\'\s])?.+?
\(\1?.+?\1?\);?\1?|style=["\'].+?["\'])\s*>#is';
    return preg_replace($regex, '<${1}>',strip_tags($input, $validTags));
}


function urldescarga($name){
	$name = urlencode($name);
	$name = str_replace("\n","_",$name);
	$name = str_replace("+","_",$name);
	$name = str_replace("%27","_",$name);
	$name = str_replace("%28","_",$name);
	$name = str_replace("%","_",$name);
	return $name;
}




function extraLineasEmail($headers){
	$data = split("\n",$headers);

	$out = array();
	foreach ($data as $linea){

		list($key) = split(":",$linea);
		$informacion = strstr($linea, ":");
		$out[$key] = $informacion;

	}

	return $out;
}

function LimpiarHTMLMaligno($html){
	$html=strip_tag_script($html,'script');
	$html=strip_tag_script($html,'style');

	return $html;
}



function strip_tag_script($html,$tag) {
    $pos1 = false;
    $pos2 = false;
    do {
        if ($pos1 !== false && $pos2 !== false) {
            $first = NULL;
            $second = NULL;
            if ($pos1 > 0)
                 $first = substr($html, 0, $pos1);
            if ($pos2 < strlen($html) - 1)
                $second = substr($html, $pos2);
            $html = $first . $second;
        }
        preg_match("/<".$tag."[^>]*>/i", $html, $matches);
        $str1 =& $matches[0];
        preg_match("/<\/".$tag.">/i", $html, $matches);
        $str2 =& $matches[0];
       $pos1 = strpos($html, $str1);
        $pos2 = strpos($html, $str2);
       if ($pos2 !== false)
            $pos2 += strlen($str2);
    } while ($pos1 !== false && $pos2 !== false);
    return $html;
}




function extraTexto($carta) {
	if (!is_array($carta))
		return $carta;

	if (isset($carta["type"])){
		$tipo = $carta["type"];
		$cuerpo = $carta["body"];

		if ($tipo =="text/plain")
			$cuerpo = nl2br(imap_qprint($cuerpo));
		else {
			$cuerpo = imap_qprint($cuerpo);
		}

		return $cuerpo;
	}

	$resultado =  extraTexto(array_shift($carta));


	if (!$resultado and isset($carta["0"]))
		$resultado = extraTexto(&$carta["0"] );

	if (!$resultado and isset($carta["1"]))
		$resultado = extraTexto(&$carta["1"] );

	if (!$resultado and isset($carta["2"]))
		$resultado = extraTexto(&$carta["2"] );

	if (!$resultado and isset($carta["3"]))
		$resultado = extraTexto(&$carta["3"] );

	if (!$resultado and isset($carta["4"]))
		$resultado = extraTexto(&$carta["4"] );

	if (!$resultado and isset($carta["5"]))
		$resultado = extraTexto(&$carta["5"] );

	if (!$resultado and isset($carta["6"]) )
		$resultado = extraTexto(&$carta["6"] );

	return $resultado;
}


function generarCuerpoVisible($textoCorreo,$id_pedido){
	$salida = "";

	$dec = new DecodeMessage();

	$dec->InitMessage($textoCorreo);
	$body = "";

	$buzones = $dec->Result();

	$body = extraTexto(&$buzones);


	$data = extraLineasEmail(&$textoCorreo);


	$msg_array  = split("\n",$textoCorreo);
	$md = new mime_email();
	$md->set_emaildata(&$msg_array);
	$md->go_decode();

	$salida .= $body;


	$num = count($md->mime_block);

	$numvisibles = 0;

	$out = "";

	if ($num>0) {
		for($t=0;$t<$num;$t++){

			$tipo = htmlentities($md->mime_block[$t]->getMimeContentType());

			$len = 7;
			if ($tipo != "multipart/alternative" and $tipo!="text/plain" and $tipo!="text/html" ){

				$numvisibles++;
				$out .= "<a title='($tipo)' target='_new' href='emailvisor.php?modo=extrae&parte=$t&id=".$id_pedido."'>Adjunto nº ".$numvisibles."</a><br>";
			}
		}

	}


	if ($numvisibles>0){
		if ($numvisibles>1)
			$out = "<hr><b>Este correo tiene ".$numvisibles." adjuntos </b><br>" . $out;
		else
			$out = "<hr><b>Este correo tiene un adjunto </b><br>" . $out;
		//echo $out;
	}

	$salida = $salida . $out;

	return $salida;

}





function registrar_Email($datos){
	global $UltimaInsercion;

	$sql = "INSERT emails (email_in_out,email_time_provider,email_time_system,email_sender,email_receiver,email_preview_html,email_subject, email_body,
	email_information) ";

	$campos = "";
	$campos .= " 'in',NOW(),NOW(), ";
	$campos .=  " '". sql($datos["from"]) . "', ";
	$campos .=  " '". sql($datos["to"]) . "', ";
	$campos .=  " '". sql($datos["preview"]) . "', ";
	$campos .=  " '". sql($datos["subject"]) . "', ";
	$campos .=  " '". sql($datos["body"]) . "', ";
	$campos .=  " '". sql($datos["extrainformation"]) . "' ";

	$sql .= " VALUES ( $campos ) ";

	if ( query($sql) ){
		return $UltimaInsercion;
	}

	return 0;
}




/*Campo  	Tipo   	Nulo  	Predeterminado   	Comentarios
email_id_comm 	int(10) 	No
email_in_out 	enum('in', 'out') 	No
email_time_provider 	timestamp 	No  	0000-00-00 00:00:00
email_time_system 	timestamp 	No  	CURRENT_TIMESTAMP
email_sender 	tinytext 	No
email_receiver 	tinytext 	No
email_preview_html 	mediumtext 	No
email_subject 	text 	No
email_body 	text 	No
email_information 	text 	No
*/



?>