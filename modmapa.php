<?php

/**
 * Mapa de delegaciones/centros/puestos
 *
 * Muestra la actividad en areas especificas
 * @package ecomm-core
 */

include("tool.php");
include_once("class/labels.class.php");


$auth = canRegisteredUserAccess("modmapa");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$page    =    &new Pagina();

$page->Inicia( $template["modname"], "mapa3.txt");


$page->addVar('menu', 'labelbasica', getParametro('labelbasica_es') );
$page->addVar('page', 'modname', $template["modname"] );
$page->addVar('headers', 'titulopagina', $trans->_('Ver comunicaciones') );


$out = ""; $js = "";

$sql = "SELECT * FROM locations ORDER BY name ASC";

$res = query($sql);

$zonas = array();

$num= 1;
while($row = Row($res)){

	$css_x = $row["css_x"];
	$css_y = $row["css_y"];
	$name = $row["name"];
	$id_label_s = $row["id_label"];

	$zonas[] = $row;


	$js .=  " var m = new Object();\n";
	$js .=  " m.name = ".json_encode($name).";\n ";
	$js .=  " m.num =  $num;\n ";
	$js .=  " m.id_location =  ". $row["id_location"] .";\n ";
	$js .=  " m.id_label =  ". $row["id_label"] .";\n ";
	$js .=  " m.css_x =  ". intval($row["css_x"]) .";\n ";
	$js .=  " m.css_y =  ". intval($row["css_y"]) .";\n ";
	$js .=  " m.id_label =  ". $row["id_label"] .";\n ";

	$js .=  " datos[$num] = m;\n";
	$js .=  " id2num[". $row["id_label"] . "] = $num;\n";



	$out .= $bloque;
	$num++;
}


$label = new Etiqueta();

$js .= " var labelinteres2name = new Array();\n";

$labels_interes = $config->get("map_labelsshow");
$interesantes = split(",",$labels_interes);
foreach($interesantes as $interesante){
	
	if ($label->Load($interesante)) {
		$js .= " labelinteres2name[".$interesante."] = '".html($label->getNombre())."';\n ";
	}
}


$page->addRows('list_entry', $zonas );



$out .= "\n<script>\n var id2num = new Object();\nvar maxdatos=$num;\n  var datos = new Array(); ". $js . "\n</script>\n";

$page->addVar("page","zonas", $out);




$page->Volcar();


?>