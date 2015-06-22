<?php



// VISOR HYLAFAX


function genVistaFinal($com){
	global $config;


	$id_comm = $com->get("id_comm");

	$sql = "SELECT * FROM faxes WHERE fax_id_comm='$id_comm' ";
	$row = queryrow($sql);

	$urlview = $row["fax_path_system"];

	$gw_viewfiles_webpath = $config->get("gw_viewfiles_webpath");

	$html = "<iframe class='amplioview' src='".$gw_viewfiles_webpath. "/" . $urlview."' >".
		"</iframe>";

	return $html;
}





?>