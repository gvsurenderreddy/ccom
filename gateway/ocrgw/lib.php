<?php




function eliminarFichero($file){
	global $cr;
	echo time(). ": eliminado $file". $cr;
	unlink( $file );
}


function cmd_wrapper($cmd, $input='')
         {$proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
          fwrite($pipes[0], $input);fclose($pipes[0]);
          $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
          $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
          $rtn=proc_close($proc);
          return array('stdout'=>$stdout,
                       'stderr'=>$stderr,
                       'return'=>$rtn
                      );
         }


function correYMuestra($cmd){
	global $cr;

	echo time(). ": Corriendo comando '$cmd' $cr";
	$salida = cmd_wrapper($cmd);

	echo ">>>>>>>>>>>>>>>>>>>>$cr";
	echo $salida["stdout"]. $cr;
	echo "----------$cr";
	echo $salida["stderr"]. $cr;
	echo "----------$cr";
	echo $salida["return"]. $cr;
	echo "<<<<<<<<<<<<<<<<<<<<$cr";
}


function randomname(){
	return abs(intval(abs(rand()*90000)));
}


function ExtraeTextoPDF( $data){
	global $cr;
	$debug = true;

	if(0)
		$resolution = 50;//30 = para pruebas, insuficiente para extraer textos
    else
		$resolution = 300; //300 = modo de calidad, para produccion

	$pdf = $data["pdf"];
	$path_origin_pdf = $data["path_origin_pdf"];

	$file = randomname() . ".tif";

	$cmd = " cd $path_origin_pdf ; convert -type Grayscale -density $resolution $pdf -depth 8  -density $resolution -quality 100  ./tmp2/$file ";

	correYMuestra(  $cmd  );

	if($debug) echo time(). ": Conversion a TIFF: ". $cmd . $cr;

	$cmd =  "cd $path_origin_pdf ; tiffsplit ./tmp2/$file ./tmp/ocr_";
	correYMuestra(  $cmd  );
	if($debug) echo time(). ": Paginacion TIFF: ". $cmd . $cr;

	eliminarFichero( NormalizarPath($path_origin_pdf . "/tmp2/") . $file);//ya no es necesario el original.tif multipagina

	$basedir = NormalizarPath($path_origin_pdf .  "/tmp/");

	mkdir($basedir);

	if ( !is_dir($basedir) and $debug ){
		echo time(). ": basedir $basedir no es dir " . $cr;
	}

	if ( !is_dir($path_origin_pdf) and $debug  ){
		echo time(). ": path_origin_pdf $path_origin_pdf no es dir " . $cr;
	}

	// list the tiff files
	$faxfiles = scandir($basedir);

	if($debug) echo time(). ": escaneando $basedir  (". print_r($faxfiles,true) .")" . $cr;

	$faxcontent = "";


	foreach ($faxfiles as $file) {

		//nota: ignora los que no son resultados de un split.
		if (!strstr($file, "ocr_"))
			continue;

		if($debug) echo time(). ": fichero: ". $file .$cr;

		$cmd = "cd $path_origin_pdf/tmp; convert -type Grayscale -density $resolution $file -depth 8  -density $resolution -quality 100  $file ";
		system($cmd);

		if($debug) echo  time(). ": comando conversion: ". $cmd . $cr;

		$ran        = intval(rand()*900000);

		$cmd = " cd $path_origin_pdf/tmp ; tesseract $file out-$ran -l spa ";
		system($cmd);

		if($debug) echo  time(). ": comando extraccion: ". $cmd . $cr;

		$outfile = NormalizarPath( $path_origin_pdf  . "/tmp/" ). "out-$ran.txt";

		// get fax content
		if ($content = file_get_contents($outfile)){
			$faxcontent .= $content;
		}

		eliminarFichero($outfile);//ya no es necesario el TXT
		eliminarFichero( NormalizarPath($path_origin_pdf . "/tmp/") . $file);//no es necesario el ocr_???.tif 
	}

	return $faxcontent;

}




?>