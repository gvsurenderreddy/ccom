<?php

/**
 * Grupo
 *
 * @package ecomm-clases
 */


/*
 * Extra 'estado' desde su id
 * 
 */
function getNombreGrupoFromId( $id_grupo ){
	$sql = "SELECT * FROM groups WHERE id_group ='$id_grupo' LIMIT 1";
	$row = queryrow($sql);

	return $row["group"];
}




/*
 *
 */
class Grupo extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("groups", "id_group", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("group");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo grupo"));
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

		$sql = "INSERT INTO groups ( $listaKeys ) VALUES ( $listaValues )";


	//	echo "<xmp>" . $sql . "</xmp>";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_group",$UltimaInsercion,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"groups","id_group",$this->get("id_group"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo grupo");
			return false;
		}
		return true;
	}


}










?>