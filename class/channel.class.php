<?php
/**
 * Clase de gestion de canales
 *
 * 
 * @package ecomm-clases
 */



/*
 * Devuelve el id de buzon que usa ese email
 */
function getChannelFromEmail( $email ){
	$email_s = sql($email);

	$sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '$email_s' ";
	$row = queryrow($sql);

	return $row["id"];
}


/**
 * Representa un buzon
 *
 * @package ecomm-core
 * @subpackage ecomm-mainclass
 */

class Canal extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("channels", "id_channel", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("channel");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo canal"));
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

		$sql = "INSERT INTO channels ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_channel",$UltimaInsercion,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"channels","id_channel",$this->get("id_channel"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;
	}

}



?>