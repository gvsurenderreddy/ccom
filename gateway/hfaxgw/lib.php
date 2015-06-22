<?php



/*
 * Devuelve el id de buzon que usa ese fax
 */
function getChannelFromFax( $fax ){
	$fax_s = sql($fax);

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '$fax_s' ";
	$row = queryrow($sql);

	if ($row["id"]) return $row["id"];

	$fax_s = trim($fax_s);

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '$fax_s' ";
	$row = queryrow($sql);

	if ($row["id"]) return $row["id"];

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '$fax_s%' LIMIT 1";
	$row = queryrow($sql);
	if ($row["id"]) return $row["id"];

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '%$fax_s' LIMIT 1";
	$row = queryrow($sql);
	if ($row["id"]) return $row["id"];

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '%$fax_s%' LIMIT 1";
	$row = queryrow($sql);
	if ($row["id"]) return $row["id"];

	$fax_s = str_replace(" ","",$fax_s);
	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '%$fax_s%' LIMIT 1";
	$row = queryrow($sql);
	if ($row["id"]) return $row["id"];

	return $row["id"];
}





function hfax_pdf_conocido($msgid){
	$msgid_s = sql($msgid);


	if (0){
		$sql = "CALL isHFax('$msgid_s')";

		$row = ProcedureRow($sql);
		return $row["c"]  ;
	} else {
		$sql = "SELECT id as c FROM gw_hfax WHERE hfax_msgid LIKE '%$msgid_s%' ";
		$row = queryrow($sql);
		return $row["c"];
	}
}

function hfax_pdf_marcarconocido($msgid,$id_comm){
	$msgid_s = sql($msgid);
	$sql = "INSERT gw_hfax (hfax_msgid,fax_id_comm) VALUES ('$msgid_s','$id_comm')";

	query($sql);

	echo time() . ": fax '$msgid' pasa a ser conocido ($sql)\n";
}




function esDocumento($file){
	global $cr;
    $ext = end(explode(".", $file));//TODO: basename?

	switch($ext){
		case "gif":
		case "pdf":
		case "tif":
			return true;
		default:
			//echo "$ext no conocido$cr";
			return false;
	}

}

function enProfundidad($dir){
	global $cr, $modulos;

	$dir = NormalizarPath($dir);

	echo marca() . "enProfunidad($dir)" . $cr;

	if (is_dir( NormalizarPath($dir) )) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( $file =="." or $file =="..") continue;

				if ( esDocumento($file) ){
					//tenemos una carpeta de datos, lo trataremos de insertar
					echo marca(). "DOC: $file es un documento" . $cr;
					
					addDir( $dir, $file);

				} else {
					echo marca(). "DIR: entra en $dir/$file en profundidad " . $cr;
					enProfundidad($dir ."/" . $file );
				}
			}
			closedir($dh);
		}
	} else {
		echo "scan: c:$dir  no es carpeta ni fichero (?)",$cr;
	}
}


function getExt($file) {
	return  end(explode(".", $file));//TODO: basename?
}


function addDir($dir,$file){
	global $modulos;

	$actual = $modulos[$dir];

	if (!$actual)
		$actual = array();

	$ext = getExt($file);

	$actual[$ext] = $file;

	$path = getParametro("hfaxgw_path_avantfax");

	$relative = str_replace($path, "",$dir);

	$actual["relative_path"] = $relative;
	list($crap,$rec, $year,$month,$day,$cod1,$cod2) = split("/",$relative);

	$actual["date"] = "$year/$month/$day";
	$actual["acode"] = $year .$month. $day . $cod1 .  $cod2;
	$actual["fullpath"] = $dir;

	$modulos[$dir] = $actual;
}




?>