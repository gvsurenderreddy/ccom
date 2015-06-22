<?php

/**
 * Clase auxiliar de gateway de correo
 *
 * @package ecomm-mailgw
 */

include_once("comunicacion.class.php");

class Correo extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("emails", "email_id_comm", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("email_subject");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo correo"));
	}


	function Enviar(){

		$to = $this->get("from_to");
		$subject = $this->get("email_subject");
		$message = $this->get("email_body");

		return mail($to,$subject,$message);

		//function mail ($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {}
	}





	function AltaComunicacion($data,$inout="in"){

		$comunicacion = new Comunicacion();

		$comunicacion->set("date_cap",$this->get("email_time_system"));
		$comunicacion->set("title",$this->get("email_subject"));
		$comunicacion->set("in_out",$inout);
		$comunicacion->set("id_channel",$data["id_channel"]);

		
		$id_contactodesconocido = getIdContactoDesconocido();
		$comunicacion->set("id_contact",$id_contactodesconocido);

		//TODO: ¿que pasa con el campo "status"?

		if ($this->get("email_in_out") == "in")
			$comunicacion->set("from_to",$this->get("email_receiver"));
		else
			$comunicacion->set("from_to",$this->get("email_sender"));

		if (!$comunicacion->Alta()){
			return false;
		}

		$id = $comunicacion->get("id_comm");

		$this->set("email_id_comm",$id);
		$this->Alta($id);
	}


	function ProcesaAdjuntos($adjuntos=false){
		if (!$adjuntos)
			return;

		$id = $this->get("email_id_comm");

		foreach($adjuntos as $adjunto){

			$filename_s = sql($adjunto["filename"]);
			$descripcion_s = sql($adjunto["description"]);

			$sql = "INSERT gw_email_subfiles ( path_subfile,description, email_id_comm) VALUES ( '$filename_s','$descripcion_s','$id') ";
			query($sql);
		}
	}

	function Alta($id){
        global $UltimaInsercion;

		$this->set("email_id_comm",$id,FORCE);

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

		$sql = "INSERT INTO emails ( $listaKeys ) VALUES ( $listaValues )";


		//echo "<xmp>" . $sql . "</xmp>";

		$resultado = query($sql);

        if ($resultado){
            $this->set("email_id_comm",$id,FORCE);
        }

        return $resultado;
	}


	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"emails","email_id_comm",$this->get("email_id_comm"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;
	}

}





?>