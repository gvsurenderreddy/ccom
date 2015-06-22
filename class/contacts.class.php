<?php
/**
 * Clase de gestion de contactos
 *
 *
 * @package ecomm-clases
 */


/**
 * Representa un buzon
 *
 * @package ecomm-core
 * @subpackage ecomm-mainclass
 */

class Contacto extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("contacts", "id_contact", $id);
		return $this->getResult();
	}


  	function getNombre() {
		return $this->get("contact_name");
  	}

  	function settNombre($name) {
		return $this->set("contact_name",$name);
  	}


  	function Crea(){
		$this->setNombre(_("Nuevo contacto"));
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

		$sql = "INSERT INTO contacts ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_contact",$UltimaInsercion,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"contacts","id_contact",$this->get("id_contact"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;
	}

}



?>