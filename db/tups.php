<?php


die("unused");

chdir("..");
include("tool.php");


function recreaProcedure($nombre, $sql){

	echo "<hr>";
	echo "<h3> re-creando procedure: $nombre </h3>";
	echo "<xmp>" . $sql . "</xmp>";
	query("DROP PROCEDURE IF EXISTS `$nombre`");
	query($sql);
}


$sql= "CREATE PROCEDURE `isHFax`(p VARCHAR(100))
SELECT count(id) as c  FROM gw_hfax WHERE hfax_msgid LIKE P";

recreaProcedure("isHFax", $sql);




?>
