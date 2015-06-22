<?php

die("obsoleto");


$debug = true;


if(!$debug)
	ob_start("compress");


//ob_start("ob_gzhandler");

ob_start("compress");



if(!$debug)
	header('Content-type: text/css');
else
	header('Content-type: text/plain');

function compress($buffer) {
	/* remove comments */

	
	$buffer= preg_replace('/#.*/','',preg_replace('#//.*#','',preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',($buffer))));
	/* remove tabs, spaces, newlines, etc. */

	$buffer = preg_replace('/(\s+|\t| \t|\n+)/',' ',$buffer);
	return $buffer;
}


/* your css files */

@include("main.css");
@include("smoothness/jquery-ui-1.8.1.custom.css");
@include("pages/modcentral.php");
@include("tagit/custom.css");
//@include("greybox/greybox.css");


//if(!$debug)
	ob_end_flush();

						
?>