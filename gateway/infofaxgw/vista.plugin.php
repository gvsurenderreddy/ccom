<?php





function genVistaFinal($com){
	global $config;

	$id_comm = $com->get("id_comm");

	$sql = "SELECT * FROM faxes WHERE fax_id_comm='$id_comm' ";
	$row = queryrow($sql);

	$urlview = $row["fax_path_system"];

	if(!$urlview){
		return "<center>". _("Documento no disponible"). "</center>";
	}


	$gw_viewfiles_webpath = $config->get("gw_viewfiles_webpath");

	$html = "<iframe class='amplioview' src='".$gw_viewfiles_webpath. "/" . $urlview."' >".
		"</iframe>";

	return $html;
}


function genVistaFinal_raw($com){

	$id_comm = $com->get("id_comm");

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm' ";
	$row = queryrow($sql);

	$html = "<iframe class='amplioview' src='gateway/infofaxgw/visor.php?id=".$id_comm."' >".
		"</iframe>";

	return $html;
}








?>