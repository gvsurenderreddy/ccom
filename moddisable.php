<?php

//global $auth;//auth fallido.


$page->addVar('headers', 'titulopagina', '' );

$page->setAttribute( 'informacion', 'src', 'info_sinderechos.txt' );
//$page->setAttribute( 'informacion', 'parse', 'on' );


$page->addVar('informacion', 'pathfallido', $auth["path"]);

$page->addVar('informacion', 'data1', "<pre>". var_export($auth,true). "</pre>");
//$page->addVar('informacion', 'data1', "<pre>". var_export($auth,true). "</pre>");

$page->addVar("page","nologin","<!--");
$page->addVar("page","nologin2","-->");
$page->addVar("cabeza","nologin","<!--");
$page->addVar("cabeza","nologin2","-->");



$page->Volcar();

//echo print_r($auth);

exit();

?>