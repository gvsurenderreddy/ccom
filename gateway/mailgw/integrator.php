<?php

/**
 * Integrador de gateway de correo
 *
 * @package ecomm-mailgw
 */


include_once("tool.php");


function genCanalList(){

	$canales = array();

	$sql = "SELECT * FROM gw_emails ";

	$res = query($sql);

	while( $row = Row($res) ){

		$dir = $row["pop3_user"] . "@" . $row["pop3_domain"];

		$canales[] = $dir;
	}

	return $canales;
}



?>