<?php

/**
 * Ayudas para escribir gateways
 *
 * @package ecomm-aux
 */

function marca($texto=false){
	return time() . ": $texto";
}


/*

$path_parts = pathinfo('/www/htdocs/index.html');

echo $path_parts['dirname'], "\n";
echo $path_parts['basename'], "\n";
echo $path_parts['extension'], "\n";
echo $path_parts['filename'], "\n"; // since PHP 5.2.0

 */


function archivadorOnline( $sourceFile, $viewFile , $tag ){
	global $cr;

	$out = array();


	$sources = getParametro("gw_sourcefiles_path");
	$viewfiles = getParametro("gw_viewfiles_path");

	$parts = pathinfo( $sourceFile );
	$newSourceName = md5($sourceFile) .".". $parts["extension"];


	$ficheroDestino = NormalizarPath($sources . "/") . $newSourceName;


	if ( file_exists($sourceFile) ){
		if ( copy( $sourceFile, $ficheroDestino ) ){
			echo time() . "Se ha copiado SRC a [$ficheroDestino]" . $cr;
			$out["source"] = $newSourceName;
		} else {
			echo time() . "No se ha podido crear [$ficheroDestino]" . $cr;
		}

	} else {
		echo time() . ": se esperaba '$sourceFile' pero no se encontro" . $cr;
	}

	$parts = pathinfo( $viewFile );
	$newViewName = md5($viewFile) .".". $parts["extension"];

	$ficheroDestino = NormalizarPAth($viewfiles . "/") . $newViewName;

	if ( file_exists($viewFile) ){
		if ( copy( $viewFile, $ficheroDestino ) ){
			//$pedido->NormalizacionNombrePDF($nuevoNombrefichero);//se guarda
			echo time() . "Se ha copiado VIEW a [$ficheroDestino]" . $cr;
			$out["viewfile"] = $newViewName;
		} else {
			echo time() . "No se ha podido crear [$ficheroDestino]" . $cr;
		}

	} else {
		echo time() . ": se esperaba '$viewFile' pero no se encontro" . $cr;
	}


	return $out;
}





?>