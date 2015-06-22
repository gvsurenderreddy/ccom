<?php



?>


<?php

die();

//ob_start("ob_gzhandler");

if(0){
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
} else {
	header("Content-Type: text/plain");
}

 $iconos = array('1downarrow.gif',  '1rightarrow.gif',  '1uparrow.gif',  'abierto.gif',  'activo.gif',
 'addcliente.gif',  'ark.gif',  'attach.gif',  'borrarcliente.gif',
 'busca1.gif',  'button_cancel.gif',  'button_ok.gif',  'candadoabierto16.gif',  'candadocerrado16.gif',
 'cdrom_mount.gif',  'cerrado.gif',  'channel1.gif',   'cliente16.gif',
 'clock.gif',  'conexion.gif',  'config16.gif',  'contacto.gif',  'contents.gif',
 'del.gif',  'desactivado.gif',  'document.gif',  'ed.gif',  'editcopy.gif',
 'editcut.gif',  'editdelete.gif',  'edit.gif',  'ed_up.gif',  'enventa16.gif',
 'enventa16gray.gif',  'error.gif',  'estadisticas.gif',  'exit16.gif',  'facturas.gif',
 'filefind.gif',  'find16.gif',  'find16.gif',  'forward.gif',  'group.gif',
 'health.gif',  'help.gif',  'helpred.gif',  'hi1.gif',  'important.gif',
 'inbox.gif',  'info.gif',  'kbackgammon_engine.gif',  'keditbookmarks.gif',  'kpackage.gif',
 'listados.gif',  'location.gif',  'logout.gif',  'looknfeel.gif',  'mail_delete.gif',
 'mail_find.gif',  'mail_generic.gif',  'message.gif',  'modcliente.gif',  'mundo2.gif',
 'network.gif',  'niceinfo.gif',  'ok1.gif',  'ok1gray.gif',  'package_favourite.gif',
 'pcgreen1.gif',  'pdf16.gif',  'personal.gif',  'personal.gif',  'player_pause.gif',
 'presupuestos.gif',  'producto16.gif',  'profilesm.gif',  'proveedor16.gif',  'proveedores.gif',
 'remove.gif',  'run.gif',  'spreadsheet.gif',  'stock16.gif',  'stockfull.gif',
 'stock.gif',  'stop.gif',  'tex.gif',  'usuarios.gif',  'wrule.gif',
 'yast_partitioner.gif',  'zoom2.gif', 'ingles.jpg','frances.gif','spanish.gif','parametros.gif','home.gif','listado.gif');





header("Content-type: text/css");

foreach($iconos as $icon){

	$data = file_get_contents("../icons/". $icon);

	$icon_name = str_replace(".gif","",$icon);

	?>
   .ik_<?php echo $icon_name ?> {
     background-image: url(http://192.168.2.102/ecomm/icons/<?php echo $icon; ?>);
     zbackground-image: url("data:image/gif;base64,<?php echo base64_encode($data); ?>");
	 background-image: url(data:image/gif,<?php echo $data; ?>);
	 width: 16px!important;height: 16px!important;
	 display:inline-block;
	 border:1px solid red;
	 background-color: #fcc;
	 background-repeat:no-repeat;
	 position:relative;
	 top: 3px;
	 }


		<?php
}




ob_end_flush();



?>