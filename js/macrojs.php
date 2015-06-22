<?php


ob_start("ob_gzhandler");

define('TIME_BROWSER_CACHE','3600');
$last_modified = filemtime(__FILE__);


if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND
	strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified ) {
  header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified',TRUE,304);
  header('Pragma: public');
  header('x-macrojs: yes');
  header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
  header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
  header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');
  die();
}

header('Content-type: text/javascript; charset=UTF-8');
header('Pragma: public');
header('x-macrojs: no');
header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');


//$offset = 60 * 60 * 49;
//$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
//header($ExpStr);
header("Content-Type: text/javascript");

//include("jquery-1.3.2.js");
include("jquery-1.4.2.min.js");

if (1) {
	include("plugins/validate/jquery.validate.js");
	include("pages/modcentral.js");
	include("../css/greybox/greybox.js");
} else {
	echo JSMin::minify(file_get_contents('plugins/validate/jquery.validate.js'));
	echo JSMin::minify(file_get_contents('pages/modcentral.js'));
}


ob_end_flush();

?>