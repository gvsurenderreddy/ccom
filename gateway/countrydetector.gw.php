<?php


define("NO_SESSION",1);


include_once("tool.php");

include_once("countrydetectorgw/detector.lib.php");
include_once("inc/runmeforever.inc.php");
include_once("inc/pasarelas.inc.php");


include_once("class/comunicacion.class.php");
include_once("class/labels.class.php");

//$cr = "\n<br>";
$cr = "\n";
//echo "<pre>";



$sql = "";

require_once("class/class.recordset.php");
include_once 'class/class.naivebayesian.php';
include_once 'class/class.naivebayesianstorage.php';
include_once 'class/class.mysql.php';


$nbs = new NaiveBayesianStorage();
$nb  = new NaiveBayesian($nbs);


// FASE 0: Reparar estructura

echo timestamp(). " Comprueba estructura $cr";


if (!$config->existe("id_label_type_countrys")){
	$config->altaclave("id_label_type_countrys",0);
}

//ya esta todo correcto, podemos proseguir...
$id_label_type_countrys = $config->get("id_label_type_countrys");

if ( !$id_label_type_countrys ){

	echo timestamp(). " Reparando necesario? $cr";

	//oops.. parece que el type no existe o similar.

	$sql = "SELECT * FROM label_types WHERE label_type='Paises' ";
	$row = queryrow($sql);

	if (!$row){

		echo timestamp(). " Recreando tipo etiqueta paises $cr";

		$sql = "INSERT INTO label_types ( label_type) VALUES ( 'Paises' ) ";
		query($sql);

		$id_label_type_countrys = $UltimaInsercion;
		$config->set("id_label_type_countrys",$id_label_type_countrys);
	} else {

		$id_label_type_countrys = $row["id_label_type"];

		$config->set("id_label_type_countrys",$id_label_type_countrys);

	}
}

if (!$config->existe("ultimo_countrydetector")){
	echo timestamp(). " Iniciando ultimo_countrydetector que no existia $cr";
	$config->altaclave("ultimo_countrydetector",1);
}



// FASE 1: Nuevos?

//Potenciales pendientes de insercion, se insertan


/*
$sql = "SELECT communications.id_comm FROM communications LEFT JOIN gw_indexcountry ON communications.id_comm = gw_indexcountry.id_comm WHERE gw_indexcountry.id is NULL";

$res = query($sql);

while($row = Row($res)){
	$id_comm = $row["id_comm"];
	$sql = "INSERT gw_indexcountry ( id_comm, done ) VALUES ( '$id_comm', 0)";
	query($sql);

	echo time(). ": creada entrada que no existia id_comm: [$id_comm]". $cr;
}
*/

/*
SELECT communications.id_comm
FROM communications INNER JOIN gw_indexcountry ON communications.id_comm = gw_indexcountry.id_comm WHERE gw_indexcountry.done=0
*/

$sql = "SELECT * FROM labels WHERE id_label_type='$id_label_type_countrys'  ";

$res = query($sql);

while($row = Row($res)){
	$id_category = $row["id_label"];
	$sql = "SELECT * FROM nb_categories WHERE category_id='$id_category' LIMIT 1 ";
	$row2 = queryrow($sql);
	if (!$row2){
		
		$label = $row["label"];
		echo timestamp(). " Preparando categoria [$id_category][$label] que no existia $cr";

		$sql = "INSERT INTO nb_categories (category_id,id_label) VALUES ('$id_category','$id_category')";
		query($sql);
	}

}

// FASE 2:

$com = new Comunicacion();
$label = new Etiqueta();


$pasadasize = 1000;


$ultimo_countrydetector = $config->get("ultimo_countrydetector");

echo timestamp(). " Lote  [$ultimo_countrydetector ],$pasadasize $cr";


$num = 0;

$sql = "SELECT id_comm FROM communications WHERE id_comm>'$ultimo_countrydetector' ORDER BY id_comm ASC LIMIT $pasadasize";

$res = query($sql);

while( $row = Row($res)){

	//  id  	bigint(20)  	 	  	No  	 	auto_increment  	  Navegar los valores distintivos   	  Cambiar   	  Eliminar   	  Primaria   	  Único   	  Índice   	 Texto completo
	//	id_label 	int(11) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	//	id_comm 	bigint(20) 			No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	//	done

	$id_comm = $row["id_comm"];

	
	if(!$com->Load($id_comm)){
		echo timestamp(). " No se pudo cargar $id_comm $cr";
	}

	//echo timestamp(). " Procesando $id_comm $cr";

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm'  ";

	$res2 = query($sql);
	$row2 = Row($res2);

	if (!$row2){
		//echo timestamp(). "Marcando [$id_comm] procesado $cr";
		$sql = "UPDATE gw_indexcountry SET done=1 WHERE id_comm ='$id_comm'";
		query($sql);
	} else {
		echo timestamp(). " [$id_comm] pendiente. $cr";
		$num++;

		$sql = "SELECT * FROM label_coms INNER JOIN labels ".
			" ON label_coms.id_label = labels.id_label WHERE id_comm='$id_comm' AND labels.id_label_type=5 LIMIT 1";
		$row3 = queryrow($sql);
		if(!$row3){
			echo timestamp(). " no sabemos el idioma-> deducir $cr";

			$sql = "SELECT email_preview_html FROM emails WHERE email_id_comm = '$id_comm' ";
			$row4 = queryrow($sql);

			$text = strip_tags($row4["email_preview_html"]);

			//echo timestamp() . " text: $text $cr";
			if (!strlen($text)){
				//no vamos a perder mas el tiempo con este mensaje sin previsualizacion
				echo timestamp() . "no vamos a perder mas el tiempo con este mensaje sin previsualizacion $cr";
				$sql = "UPDATE gw_indexcountry SET done=1 WHERE id_comm ='$id_comm'";
				query($sql);
			}

			$id_label = deducirCategoria($text);
			
			if ($id_label){
				if ($label->Load($id_label)) {
					echo timestamp(). " [$id_comm] etiquetado con [$id_label] $cr";
					$label->createLink($id_comm);
				}
			} else {
				echo timestamp(). " no sabemos que idioma es. id_label[$id_label] $cr";
			}


		} else {
			//sabemos el idioma -> marcar
		}



	}

	$config->set("ultimo_countrydetector",$id_comm);

	if ($num>5000){
		echo timestamp(). "Alcanzado maximo $cr";
		break;
	}
}

echo timestamp(). " Procesados num:[$num] $cr";





















?>