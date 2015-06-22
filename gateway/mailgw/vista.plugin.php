<?php




function genVistaFinal($com){

	$id_comm = $com->get("id_comm");

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm' ";
	$row = queryrow($sql);

	return $row["email_preview_html"];
}








?>