<?php



function mantenimientoTablaRapida(){

	return;

	//NOTA: innecesario con soporte de particiones

		//Si la tabla existe (casi siempre) seria 0.0003 s
	query("CREATE TABLE IF NOT EXISTS `communications_fast` (
		`id_comm` int( 8 ) unsigned NOT NULL AUTO_INCREMENT ,
		`id_group` smallint( 6 ) NOT NULL ,
		`id_contact` int( 4 ) NOT NULL ,
		`id_channel` smallint( 6 ) NOT NULL ,
		`id_status` tinyint( 4 ) NOT NULL ,
		`in_out` enum( 'in', 'out' ) NOT NULL ,
		`from_to` varchar( 40 ) NOT NULL ,
		`title` varchar( 200 ) NOT NULL ,
		`date_cap` timestamp NOT NULL default CURRENT_TIMESTAMP ,
		`priority` varchar( 20 ) NOT NULL ,
		PRIMARY KEY ( `id_comm` ) ,
		KEY `id_contact` ( `id_contact` ) ,
		KEY `id_channel` ( `id_channel` ) ,
		KEY `id_status` ( `id_status` ) ,
		KEY `in_out` ( `in_out` ) ,
		KEY `id_group` ( `id_group` )
		) ENGINE = memory DEFAULT CHARSET = latin1");
}


function actualizarTablaRapida($idcoms = false){

	return;
	/*
	//NOTA: innecesario con soporte de particiones


	//cerca de 0.0003 s
	mantenimientoTablaRapida();

	//muy rapido tambien. si vacia 0.0002 s; si llena 0.1885 seg
	query("TRUNCATE communications_fast");

	//cerca de 0 
	$row = queryrow("SHOW TABLE STATUS LIKE 'communications'");
	$Auto_increment = $row["Auto_increment"];

	$limite = $Auto_increment-10000;
	$_SESSION["limite_tabla_rapida"] = $limite;

	//alrededor de  0.1555 s
	query("INSERT INTO communications_fast SELECT * FROM communications WHERE communications.id_comm>$limite");
     * 
     */
}









?>