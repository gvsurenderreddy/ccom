<?php

/**
 * Reglas 
 *
 * @package ecomm-clases
 */

		include_once("labels.class.php");


/*
 * Entiende de reglas aplicadas a una comunicacion.
 */
class RuleMaker extends Cursor {

	var $comm;


	/*
     * Selecciona una regla
     */
	function setCom( $communication){
		$this->comm = $communication;
	}


	/*
     * Dispara los efectos que corresponde a la regla activada
     */
	function AplicarRegla($datosRegla){

		//1) Se añade esta etiqueta a la comunicacion

		$label = new Etiqueta();

		if( $label->Load($datosRegla["id_label"]) ){
			$label->createLink($this->comm->get("id_comm"));
		}

		if ( $datosRegla["id_contact"]){
			$id = $datosRegla["id_contact"];
			$this->comm->set("id_contact",$id);

			//actualizamos este puntualmente
			$id_comm = $this->comm->get("id_comm");
			$sql = "UPDATE communications SET id_contact='$id' WHERE id_comm='$id_comm'";
			query($sql);
		}
	}


/*
  	id_eac  	smallint(6)  	 	UNSIGNED  	No  	 	auto_increment  	  Navegar los valores distintivos   	  Cambiar   	  Eliminar   	  Primaria   	  Único   	  Índice   	 Texto completo
	id_user 	smallint(6) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_contact 	int(6) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_label 	smallint(6) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_date_in 	timestamp 			No 	CURRENT_TIMESTAMP 		Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_from 	tinytext 	latin1_swedish_ci 		No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_to 	tinytext 	latin1_swedish_ci 		No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_title 	tinytext 	latin1_swedish_ci 		No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_content 	tinytext 	latin1_swedish_ci 		No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	eac_com_dir
 */


	/*
     * Corre todas las reglas para la comunicacion seleccionada
     */
	function RunRules(){
		/*
		$sql = "SELECT * FROM eac WHERE ".
			"eac_from LIKE '%$from_s%' OR ".
			" eac_to LIKE '%$to_s%' OR ".
			" eac_title LIKE '%$title_s%'   ".
			" eac_content LIKE '%$content_s%'" ;*/

		$sql = "SELECT * FROM eac ORDER BY id_eac DESC";

		$res = query($sql);

		while($row = Row($res)){
			$cumpleReglas			= 0;
			$fallosCoincidencia		= 0;

			if ( strstr($this->comm->get("from_to"),$row["eac_from"] ) ) {
				 $cumpleReglas++;
			} else if ( $row["eac_from"])  {
				$fallosCoincidencia++;
				continue; //esta regla ya no va a coincidir porque falla una de las condiciones
			}

			if ( strstr($this->comm->get("from_to"),$row["eac_to"] ) ) {
				 $cumpleReglas++;
			} else if ( $row["eac_to"] ){
				$fallosCoincidencia++;
				continue;
			}

			if ( strstr($this->comm->get("title"),$row["eac_title"] ) ) {
				 $cumpleReglas++;
			} else if ($row["eac_title"])  {
				$fallosCoincidencia++;
				continue;				
			}

			if ($cumpleReglas and !$fallosCoincidencia ){
				$this->AplicarRegla($row);
			}
		}
	}

}






class Regla extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("eac", "id_eac", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("eac");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva regla"));
	}


	function getContactoName(){

		$id_contact = $this->get("id_contact");

		$sql = "SELECT contact_name as dato FROM contacts WHERE (id_contact='$id_contact')";

		$row = queryrow($sql);
		return $row["dato"];
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

		$sql = "INSERT INTO eac ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
            $this->set("id_eac",$UltimaInsercion,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"eac","id_eac",$this->get("id_eac"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;
	}


}




?>