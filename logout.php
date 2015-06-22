<?php




//session_start();
include("tool.php");



$id = $_SESSION["id_user"];

$sql = "UPDATE users SET eslogueado=0 WHERE id_user = '$id' ";
query($sql);


session_unset();
session_destroy();


header("Location: login.php");



?>