<?php


chdir("..");
include("tool.php");
include_once("class/comunicacion.class.php");

$salida["html"] = "Sin datos de riesgo";


/*
$id_comm = $_REQUEST["id_comm"];

$com = new Comunicacion();

if ( !$com->Load($id_comm) ){


	return;
}*/



$salida["html"] = "Pendiente";


?>