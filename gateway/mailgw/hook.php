<?php



$hook_onview["text/html"] = "getPreviewForComHTML";


function getPreviewForComHTML( $id_comm ){
	$id_comm = CleanID($id_comm);

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm' ";
	$row = queryrow($sql);

	return $row["email_preview_html"];
}







?>