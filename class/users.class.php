<?php

/**
 * Usuarios
 *
 * @package ecomm-clases
 */

function getNombreUsuarioFromId($id){
	$id = CleanID($id);
	$sql = "SELECT name as dato FROM users WHERE (id_user='$id')";

	$row = queryrow($sql);

	$dato = $row["dato"];

	if (!$dato)
		return "";

	return $dato;
}

function getIdUsuarioSistema(){
//	$sql = "SELECT id_user as data FROM users WHERE es_sistema=1";
//	$row = queryrow($sql);
//	return $row["data"];
}

class Usuario extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("users", "id_user", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("name");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo Usuario"));
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

		$sql = "INSERT INTO users ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_user",$UltimaInsercion,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"users","id_user",$this->get("id_user"));

		//echo "<xmp>" . $sql . "</xmp>";


		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;
	}


}











?>