<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class Infofax extends Fax {

	function AltaInfofax($original, $visible, $id_comm,$fechahora){
		
		$this->set("fax_path_system", $original);
		$this->set("fax_path_provider", $visible);
		$this->set("fax_id_comm",$id_comm,FORCE);

		$this->set("fax_time_provider", $fechahora);//TODO: convertir a unixtime
		$this->set("fax_time_system",date("Y-m-d H:i:s"));
		$this->AltaFax();
	}

}




?>