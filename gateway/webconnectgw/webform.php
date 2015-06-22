<?php

//echo __LINE__ . __FILE__ . "<br>\n";

chdir('../../');
chdir('.');

//echo __LINE__ . __FILE__ . "<br>\n";

include_once("tool.php");

//echo __LINE__ . __FILE__ . "<br>\n";

$path = $_REQUEST["path"];

//echo __LINE__ . __FILE__ . "<br>\n";

$numfields = $_REQUEST["numfields"];

//echo __LINE__ . __FILE__ . "<br>\n";

$data = array();
$campos = " id_webform ";
$values = " '%s' ";

//echo __LINE__ . __FILE__ . "<br>\n";

for($t=0;$t<$numfields;$t++){
	$text = $_REQUEST["data_" . $t ];
	$data[] = $text;

	$dato_s = sql($text);

	$campos .= " , data_" . $t . " ";
	$values .= " , '$dato_s'  ";
}

//echo __LINE__ . __FILE__ . "<br>\n";

$path_s = sql($path);

//echo __LINE__ . __FILE__ . "<br>\n";

$sql = "SELECT * FROM gw_webforms WHERE path = '$path_s' ";
$row = queryrow($sql);

//echo __LINE__ . __FILE__ . "<br>\n";

$path_return = $row["path_return"];

$id_webform = $row["id_webform"];

$values = sprintf($values,$id_webform);

$sql = "INSERT gw_webforms_data ( $campos ) VALUES ( $values )";
query($sql);

//echo __LINE__ . __FILE__ . "<br>\n";

$path_return = sprintf($path_return, "ok");

header("Location: " .$path_return );



?>