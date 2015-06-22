<?php


die("obsoleto");

ob_start("ob_gzhandler");
header('Content-type: text/css');


if (!preg_match("/^([a-zA-Z0-9_-]+.css$)/", $_GET['file'])) {
		die('not a css file.');
    }

if(file_exists($_GET['file'])) {
    $sText = file_get_contents($_GET['file']) or die('Could not open file.');
    $sText = preg_replace('!/*[^*]**+([^/][^*]**+)*/!', '', $sText);
    $sHash = md5($sText);
	header("Last-Modified: ".date("D, d M Y H:i:s T", filemtime($_GET['file'])));
	header("ETag: '{$sHash}'");

	echo str_replace(array("rn", "r", "n", "t", '  ', '   ', '    ', '     '), '', $sText);

}   else {
	   echo 'ERROR: "'.$_GET['file'].'"';
}

ob_flush();

?>