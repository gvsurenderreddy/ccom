<?php

/**
 * Clase de gestion de comunicaciones
 *
 *
 * @package ecomm-clases
 */


if ( !defined('COMMUNICATION_CLASS') ):

define('COMMUNICATION_CLASS',1);


include_once("eac.class.php");
include_once("label.class.php");

/*
 * Marca una comunicacion como leida por el usuario logueado
 */
function markRead($id_comm){
	$id_com_s = CleanID($id_comm);
	$id_user_s = CleanID(getSesionDato("id_user"));

	$sql = "SELECT 1 FROM read_comm  WHERE id_comm='$id_com_s' AND id_user='$id_user_s' ";
	$row = queryrow($sql);

	if (!$row){
		$sql = "INSERT INTO read_comm ( id_comm,id_user, date_read) values ('$id_com_s','$id_user_s',NOW())";
		query($sql);
	}
}

/*
 * Devuelve el id del contacto desconocido
 */
function getIdContactoDesconocido(){

	$sql = "SELECT * FROM contacts WHERE contact_unknown=1 LIMIT 1";
	$row = queryrow($sql);

	return $row["id_contact"];
}

/*
 * Extrae el preview_html o similar de una com.
 */
function getPreviewForCom( $id_comm ){
	$id_comm = CleanID($id_comm);

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm' ";
	$row = queryrow($sql);

	return $row["email_preview_html"];
}



/**
 * Representa una Comunicacion
 *
 * @package ecomm-core
 * @subpackage ecomm-mainclass
 */
class Comunicacion extends Cursor {


	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("communications", "id_comm", $id);
		return $this->getResult();
	}

	function getMiModulo(){
		$id_channel= $this->get("id_channel");

		$sql = "SELECT module
				FROM `channels`
				INNER JOIN medias ON channels.id_media = medias.id_media
				INNER JOIN gateway ON gateway.id_gateway = medias.id_gateway
				WHERE id_channel ='$id_channel'";
		$row = queryrow($sql);
		return $row["module"];			
	}


	function getVista(){
	
		$modulo = $this->getMiModulo();

		//if(!$modulo) return "No existe visualizador para este tipo de documento (ch:$id_channel|$modulo)";
		if(!$modulo) return false;

		$dir = getPathBaseModule($modulo);

		$mod = getValidModule($dir,"vista.plugin.php");

		if($mod){
			include($mod);
			$id_channel= $this->get("id_channel");
			return genVistaFinal($this);
		} else {
			//return "mod [$mod] no se encuentra";
			return false;
		}
	}


  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("title");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva com."));
	}

	function EtiquetasNecesarias(){

		$sql = "SELECT * FROM label_types WHERE isobligatory=1";

		$res = query($sql);

		$label = new Etiqueta();
		$id_comm = $this->get("id_comm");

		while($row = Row($res)){
			$id_label_default = $row["id_label_default"];
			$label->Load($id_label_default);
			$label->createLink($id_comm);
		}
	}



	function RunRules(){
		$maker = new RuleMaker();

		$maker->setCom( $this );
		$maker->RunRules();
	}

	function Alta(){
        global $UltimaInsercion;

		$data = $this->export();

		$coma = false;
		$listaKeys = "";
		$listaValues = "";

		foreach ($data as $key=>$value){
			if ($coma) {
				$listaKeys .= ", ";
				$listaValues .= ", ";
			}

            $value = sql($value);

			$listaKeys .= " `$key`";
			$listaValues .= " '$value'";
			$coma = true;
		}

		$sql = "INSERT INTO communications  ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_comm",$UltimaInsercion,FORCE);


			//Vamos a correr EAC para la comunicación.
			$this->RunRules();
			$this->Traza();

			//vamos a crear todas las etiquetas obligatorias
			$this->EtiquetasNecesarias();
        }

        return $resultado;
	}


	function Traza(){

		$id_comm = $this->get("id_comm");
		$id_group = $this->get("id_group");
		$id_status = $this->get("id_status");
		$id_user = getSesionDato("id_user");//TODO: reconocer usuario sistema?

		$sql = "INSERT INTO trace  ( id_comm,id_user, id_group,id_status,date_change ) VALUES ".
				"( '$id_comm','$id_user','$id_group', '$id_status', NOW() )";

		if (!query($sql,'Traza')){
			//die("error: $sql");
		}


	}



	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"communications","id_comm",$this->get("id_comm"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualiza comm");
			return false;
		}

		return true;
	}



	function Etiquetar($etiqueta,$tipo=false){
		$etiqueta = trim($etiqueta);

		$label = new Etiqueta();
		if ( $label->LoadByName($etiqueta)) {
			$label->createLink($this->get("id_comm"));
		} else {

			$label->set("label",$etiqueta);
			$label->Alta();

			$label->createLink($this->get("id_comm"));
		}
	}


}

endif;


?>