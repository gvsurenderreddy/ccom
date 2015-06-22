<?php
/**
 * Integrador de webconnector
 *
 * @package ecomm-webconnector
 */


include_once("tool.php");


function genCanalList(){

	$canales = array();

	$sql = "SELECT * FROM gw_webforms ";

	$res = query($sql);

	while( $row = Row($res) ){

		$dir = $row["name"];

		$canales[] = $dir;
	}

	return $canales;
}





?>