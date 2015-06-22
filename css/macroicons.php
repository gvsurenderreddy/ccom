<?php

define('TIME_BROWSER_CACHE','3600');
$last_modified = filemtime(__FILE__);

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND
	strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified) {
  header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified',TRUE,304);
  header('Pragma: public');
  header('x-macrojs: yes');
  header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
  header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
  header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');
  die();
}

header('Content-type: text/css; charset=UTF-8');
header('Pragma: public');
header('x-macrojs: no');
header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');



include("../icons/tools/iconlist.php");


$maxicons = count($iconos);

$n = 0;
foreach( $iconos as $icon ){
	$icon_name = str_replace(".gif","",$icon);

	?>
	.ik_<?php echo $icon_name ?> {
		background-image: url(../icons/fila.gif);
		background-position: <?php echo (($maxicons-$n) * 16); ?>px 0px;
		width: 16px!important;
		height: 16px!important;
		display: inline-block;
	}
	<?
	$n++;
}



?>