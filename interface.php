<?php

/**
 * Pantalla inicial
 *
 *
 * @package ecomm-core
 */

include("tool.php");

$page    =    &new Pagina();
$page->setRoot( 'templates' );
$page->readTemplatesFromInput('interface.txt');
//$page->addVar('page', 'modname', $template["modname"] );

$page->addVar('headers', 'titulopagina', 'Ecom :: Gestión' );





$page->Volcar();


?>
