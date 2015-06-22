<?php



define("NO_SESSION",1);


/*
 * Requerido, pues el OCR realmente requiere mucha memoria.
 */
ini_set('memory_limit', '1024M');


/*
 * Si corre aisladamente, debe correr en "raiz" de la aplicación, y no en el directorio gateway.
 */
if (!defined( "GATEWAY_WRAPPER")) chdir("..");

header("Content-type: text/html; charset=UTF-8");


include_once("tool.php");
include_once("gateway/ocrgw/lib.php");


$path_origin_pdf = $config->get("path_store_pdf");


echo time() . ": path archivo pdf's: '$path_origin_pdf' ". $cr;



$cr = "\n";


//if(1)query("TRUNCATE gw_indexocr"); //DEBUG


//Potenciales pendientes de insercion, se insertan
$sql = "SELECT fax_id_comm FROM faxes LEFT JOIN gw_indexocr ON faxes.fax_id_comm = gw_indexocr.id_comm WHERE id_indexocr is NULL";

$res = query($sql);

while($row = Row($res)){
	$id_comm = $row["fax_id_comm"];
	$sql = "INSERT gw_indexocr ( id_comm, done ) VALUES ( '$id_comm', 0)";
	query($sql);

	echo time(). ": creada entrada que no existia id_comm: [$id_comm]". $cr;
}



$sql = "SELECT fax_path_system,fax_id_comm,id_indexocr  FROM faxes LEFT JOIN gw_indexocr ON faxes.fax_id_comm = gw_indexocr.id_comm WHERE done=0";

$res = query($sql);
while($row =Row($res)){

	$data = array();
	$data["pdf"] = $row["fax_path_system"];//nombre del fichero pdf

	$data["path_origin_pdf"] = $path_origin_pdf; //donde residen los PDF

	$finalname = NormalizarPath($data["path_origin_pdf"] . "/") . 	$data["pdf"];

	$data["finalname"] =  $finalname;

	if ( !file_exists($finalname) ){
		echo time(). ": ERROR: '$finalname' no existe". $cr;
		continue;
	}

	echo time(). ": Informacion:" . print_r($data,true). $cr;

	$texto = ExtraeTextoPDF($data);

	echo time(). ": Texto extraido: \n>>>>>>>>>>>>>>>>>>\n", $texto, "\n<<<<<<<<<<<<<<<<\n";
	
	//INFO: archivamos
	if (1){
		$texto_s = sql($texto);
		$index = $row["id_indexocr"];

		$sql = "UPDATE gw_indexocr SET done=1, content='$texto_s' WHERE id_indexocr = '$index' ";
		query($sql);
	}

}



echo time() . ": proceso completado". $cr;

exit();



/*

 * sudo apt-get install libpng12-dev
sudo apt-get install libjpeg62-dev
sudo apt-get install libtiff4-dev
sudo apt-get install zlibg-dev
 *
 */

?>