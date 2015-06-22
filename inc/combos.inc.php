<?php

/**
 * Ayuda para creacion de listas desplegables sobre elementos del sistema
 *
 * @package ecomm-aux
 */


//para su uso con combos de etiquetas
define("ETIQUETAS_BASICAS",1);
define("ETIQUETAS_USUARIO",2);
define("ETIQUETAS_ESTADOS",3);

function genComboTipoEtiqueta($idquien=-1){

	$sql = "SELECT * FROM `label_types` ORDER BY `label_type` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["label_type"];

		$key = $row["id_label_type"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}




function genComboProfiles($idquien=-1, $especifica=false){

	if ($especifica) {
		$extra = " isgroupprofile='".$especifica["id"]."' AND ";
	}

	$sql = "SELECT * FROM `profiles` WHERE $extra deleted=0 ORDER BY `name` ASC ";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["name"];

		$key = $row["id_profile"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}


function genCombosStatus($idquien=-1){

	$sql = "SELECT * FROM `status` ORDER BY `status` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["status"];

		$key = $row["id_status"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}



function genComboTarea($idquien=-1){

	$sql = "SELECT * FROM `tasks` ORDER BY `task` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["task"];

		$key = $row["id_task"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}



function genComboMedios($idquien){

	$sql = "SELECT * FROM `medias` ORDER BY `media` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["media"];

		$key = $row["id_media"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}



function genComboGrupos($idquien){

	$sql = "SELECT * FROM `groups` ORDER BY `group` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["group"];

		$key = $row["id_group"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}

function getComboStatus($id_label_type=3,$idquien=-1){

	$sql = "SELECT * FROM `labels` WHERE id_label_type='$id_label_type' ORDER BY `label` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["label"];

		$key = $row["id_label"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$icon = $row["icon"];
		$css = $icon?"background-image: url(icons/$icon);background-repeat: no-repeat":"";

		$out .= "<option style='$css;padding-left: 18px' value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}





function genComboCanales($idquien){

	$sql = "SELECT * FROM `channels` ORDER BY `channel` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["channel"];

		$key = $row["id_channel"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}


function genComboCOMDIR($com_dir ){

	$dirs = array("0"=>"Enviados y recibidos","1"=>"Recibidos","2"=>"Enviados");

	$out .= "";

	foreach($dirs as $key=>$value){
		$extra = ($com_dir==$key)?"selected='selected'":"";

		$out .= "<option $extra value='" . $key . "'>" . html($value) . "</option>";
	}

	return $out;
}


function genSelectorNubeEtiquetas($id_label_actual,$id_usuario_actual, $namelabel ){

	$sql = "SELECT label as dato FROM labels WHERE id_label='$id_label_actual'";
	$row = queryrow($sql);
	$labelactual = $row["dato"];

	$sql = "SELECT * FROM labels WHERE id_user='$id_usuario_actual' ORDER BY id_label_type ASC, id_channel ASC, label ASC ";
	$res = query($sql);
	$row = Row($res);

	$forceStart = true;
	$propiosAgotados = false;

	$out .= "<div style='color: #ddd'>";

	$out .= "<input type='text' id='label_seleccionada_texto' value='".html($labelactual)."'>";
	$out .= "<input type='hidden' name='$namelabel' value='".$id_label_actual."' id='id_label_seleccionada'><br/>";

	$out .= "<a href='#'  onclick='select(0,\"\")'>" . html("Borrar etiqueta") . "</a> | ";

	while($row or $forceStart){
		$forceStart = false;

		$decora = "";

		if ($row){

			$decora .= ($row["id_user"]==$id_usuario_actual)?"font-weight: bold;":"";

			$out .= "<a href='#' style='".$decora."' onclick='select(".$row['id_label'].",\"".addslashes($row["label"]) ."\")'>" . html($row["label"]) . "</a> | ";
		}
		
		//siguiente label
		$row = Row($res);
		if ( !$row and !$propiosAgotados){
			//se han agotado los labels propios, intentar los externos
			$sql =  "SELECT * FROM labels WHERE id_user!='$id_usuario_actual' ORDER BY id_label_type ASC, id_channel ASC, label ASC ";
			$res = query($sql);
			$row = Row($res);
			$propiosAgotados = true;
		}


	}

	$out .= "</div>";


	$out .= "<script>";

	$out .=  "  function select(id_seleccion,labelname){
	document.getElementById('id_label_seleccionada').setAttribute('value',id_seleccion);
	document.getElementById('id_label_seleccionada').value = id_seleccion;
	document.getElementById('label_seleccionada_texto').setAttribute('value',labelname);
 }; ";

	$out .= "</script>";


	return $out;

}




function genListEtiquetasCommArray($id_comm){

	$out = "";

	$sql = "SELECT * FROM labels
		INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type
        INNER JOIN label_coms ON labels.id_label = label_coms.id_label
		WHERE labels.id_label>0 AND label_coms.id_comm = $id_comm
		ORDER BY labels.id_label_type ASC, `label`  ASC";



	$res = query($sql);

	$forceStart = true;
	$propiosAgotados = false;

	$etiquetas = array();

	$coma = "";

	while($row  =Row($res) ){
		$etiquetas[]  = array("etiqueta"=>$row["label"], "idlabelcom"=>$row["id_label_com"]);
	}

	return $etiquetas;
}


function genListEtiquetasComm($id_comm){

	$out = "";

	$sql = "SELECT * FROM labels
		INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type
        INNER JOIN label_coms ON labels.id_label = label_coms.id_label
		WHERE labels.id_label>0 AND label_coms.id_comm = $id_comm
		ORDER BY labels.id_label_type ASC, `label`  ASC";



	$res = query($sql);

	$forceStart = true;
	$propiosAgotados = false;

	$coma = "";

	while($row  =Row($res) ){
		//id_label 	id_label_type 	id_channel 	id_user 	label 	id_label_type 	label_type

		$out .= "$coma " . $row["label"];
		$coma = ",";
	}

	return $out;
}




/*
function genComboCentros($idquien){

	$id = $_SESSION["id_proyecto"];

	$sql = "SELECT * FROM centros WHERE eliminado=0 AND id_proyecto='$id' ORDER BY nombre ASC";
		
	$res = query($sql);
	
	$out = "";
	while ($row= Row($res)){
		$name = $row["nombre"];
		
		$name = iconv("ISO-8859-1","UTF8",$name);
		
		
		$key = $row["id"];
		
		if ($key == $idquien){
			$selected = "selected='selected'";	
		} else {
			$selected = "";
		}			
		
		$out .= "<option value='$key' $selected>" . CleanRealMysql($name) . "</option>\n";				
	}
	
	return $out;	
}

function getCentroFromId($idquien){
	$idquien = intval($idquien);	
	$sql = "SELECT * FROM centros WHERE id='$idquien'";
	$row = queryrow($sql);
	
	if (!$row){
		return "Otro";	
	}
	
	$nombre = $row["nombre"];
	
	return iconv("iso-8859-1","UTF-8",$nombre);
		
}


function genXulComboCentros($selected=false,$xul="menuitem",$callback=false) {
	$sql = "SELECT * FROM centros ORDER BY nombre ASC, id ASC ";
	$res = query($sql);
		
	$out = "";	
	$call = "";
	while($row = Row($res)){
		
		$key = $row["id"];
		$value = iconv("iso-8859-1","UTF8",$row["nombre"]);
		
		if ($callback) 
			$call = "oncommand=\"$callback('$key')\"";
			
		if ($key!=$selected)
			$out .= "<$xul value='$key' label='$value' $call/>";
		else	
			$out .= "<$xul selected value='$key' label='$value' $call/>";

			
	}
	return $out;
}

*/

?>