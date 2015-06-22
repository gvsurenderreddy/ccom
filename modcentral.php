<?php

/**
 * Lista de comunicaciones
 *
 * Produce una lista de comunicaciones, y se pueden filtrar, organizar, visualizar
 * @package ecomm-core
 */

include("tool.php");
include("inc/comunications_fast.php");

ob_start("ob_gzhandler");

$auth = canRegisteredUserAccess("modcentral");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


if(0){
	mantenimientoTablaRapida();
	$necesitaActualizacionDeTablarapida = false;
	$usarTablaRapida = false;
} else {
	$necesitaActualizacionDeTablarapida = false;
	$usarTablaRapida = false;
}



$page    =    &new Pagina();

$page->Inicia( $template["modname"], "central.txt");

$page->addVar('menu', 'labelbasica', getParametro('labelbasica_es') );
$page->addVar('page', 'modname', $template["modname"] );
$page->addVar('headers', 'titulopagina', $trans->_('Ver comunicaciones') );

$salida = array();

switch($modo){


	case "cambio_buzon":
		$tipo_buzon = $_POST["tipo_buzon"];

		$_SESSION["tipo_buzon"] = $tipo_buzon;	
		break;

	case "cambio_status":

		$tipo_status = $_POST["tipo_status"];

		if ( $tipo_status == -1){
			$_SESSION["tipo_status"] = -1;//no hay filtro de estado
		} else
			$_SESSION["tipo_status"] = $tipo_status;
		break;
		
	case "cambio_grupo":
		$tipo_id_grupo = $_POST["tipo_id_grupo"];

		if ( $tipo_id_grupo == -1){
			$_SESSION["tipo_id_grupo"] = -1;//no hay filtro de grupo
		} else
			$_SESSION["tipo_id_grupo"] = $tipo_id_grupo;
		break;

	case "cambio_filtrotask":
		$id_task = $_REQUEST["id_task"];;

		if (!puedeActualVerCanal($id_task)){ //Tiene acceso a este canal
			break;
		}

		if ( $id_task == -1){
			$_SESSION["tipo_id_task"] = -1;//no hay filtro de grupo
		} else
			$_SESSION["tipo_id_task"] = $id_task;
		break;

	case "resultados_pagina":
		$resultados_pagina = $_REQUEST["tipo_resultadospagina"];

		$_SESSION["resultados_pagina"] = $resultados_pagina;
		break;

	case "cargarExtra":
		
		$salida["ok"] = true;
		$salida["html"] = "";//"hola mundo";
		$salida["tooltip_id"] = $_POST["tooltip_id"];

		$plugin = "plugin/tooltipextra.plug.php";
		if (file_exists($plugin)){
			include($plugin);
		}

		echo json_encode($salida);

		exit();
		return;

	case "updown_change":
		if(1) {
			$submodo = $_REQUEST["updown"];
			switch($submodo){
				case "inbox":
				case "date":
				case "priority":
				case "contact":
				case "group":
				case "status":
				case "address":
					$diractual = $_SESSION["modcentro_filtro_$submodo"];
					$newdir = ($diractual=="DESC")?"ASC":"DESC";

					$_SESSION["modcentro_filtro"] = "$submodo";
					$_SESSION["modcentro_filtro_dir"] = $newdir;
					$_SESSION["modcentro_filtro_$submodo"] = $neswdir;
					break;

				default:
					break;
			}
			$usarTablaRapida = false;
		}
		break;

	case "filtro_estados":

		$esTramitados = ($_REQUEST["tramitados"] =="on")?1:0;
		$esTraspasados = ($_REQUEST["traspasados"] =="on")?1:0;
		$esEliminados = ($_REQUEST["eliminados"] =="on")?1:0;

		$_SESSION["filtra_tramitados"] = $esTramitados;
		$_SESSION["filtra_traspasados"] = $esTraspasados;
		$_SESSION["filtra_eliminados"] = $esEliminados;
		break;

	case "apply_status":
		$list = $_REQUEST["list_id_comm"];
		$id_status = CleanID($_REQUEST["status"]);

		if(1){

			$links = split(",",$list);

			foreach($links as $id_comm){

				if($id_comm) {
					$sql = "UPDATE  communications SET id_status=$id_status WHERE id_comm='$id_comm' LIMIT 1";

					query($sql);

					$necesitaActualizacionDeTablarapida = true;
				}
			}

		}

		break;
	case "apply_label":
		$list = $_REQUEST["list_id_comm"];
		$id_label = CleanID($_REQUEST["etiqueta"]);

		if(1){
			include_once("class/labels.class.php");

			$label = new Etiqueta();
			if (!$label->Load($id_label)) {
				break;//no exista la label que se intenta asociar
			}

			$links = split(",",$list);

			foreach($links as $id_comm){

				if($id_comm)
					$label->createLink($id_comm);
			}

		}
		break;

	case "apply_task":
		$list = $_REQUEST["list_id_task"];
		$id_task = CleanID($_REQUEST["task"]);


		if(1){

			$links = split(",",$list);

			foreach($links as $id_comm){

				if($id_comm) {
					$sql = "UPDATE communications SET id_task=$id_task WHERE id_comm='$id_comm' LIMIT 1";
						
					query($sql);

					$necesitaActualizacionDeTablarapida = true;
				}
			}

		}
		
		break;

	case "buscar_contacto":
		$filtraIdContacto = CleanID($_REQUEST["buscacod_contacto"]);
		$filtraIdContacto = $filtraIdContacto?$filtraIdContacto:false;//seguramente innecesario.
		break;

	case "agnadirllamada":		{

		include_once("class/comunicacion.class.php");
		include_once("class/phonecall.class.php");
		
		$quien = $_POST["quien"];
		$telefono = $_POST["telefono"];
		$codcliente = $_POST["codcliente"];
		$aquien = $_POST["aquien"];
		$motivo = $_POST["motivo"];
		$notas	= $_POST["notas"];
		$esllamadarecibida = $_POST["esllamadarecibida"];

		$call = new nota_llamada();

		$call->setEsRecibida($esllamadarecibida?true:false);
		$call->setQuien($quien);
		$call->setTelefono($telefono);
		$call->setCodCliente($codcliente);
		$call->setAQuien($aquien);
		$call->setMotivoLlamada($motivo);
		$call->setNotas($notas);
		$call->AltaLlamada();

		}
		break;

	case "enviaremail":		{

		include_once("class/correo.class.php");
		include_once("class/channel.class.php");

		$email = $_POST["email"];
		$quien = $_POST["quien"];
		$telefono = $_POST["telefono"];
		$codcliente = $_POST["codcliente"];
		$aquien = $_POST["aquien"];
		$motivo = $_POST["motivo"];
		$notas	= $_POST["notas"];
		$esllamadarecibida = $_POST["esllamadarecibida"];

		$correo = new Correo();

		$correo->set("email_in_out","out");
		$correo->set("email_time_provider",date("Y-m-d H:i:s"));
		$correo->set("email_time_system",date("Y-m-d H:i:s"));
		$correo->set("email_receiver",$aquien);
		$correo->set("email_sender",$quien);
		$correo->set("email_subject",$motivo);
		$correo->set("email_body",$notas);

		$data = array();
		$data["id_channel"] = getChannelFromEmail($quien);

		$enviado = $correo->Enviar();
		
		if($enviado){
			$correo->AltaComunicacion($data);
		}

		
		}
		break;

	case "enviarfax": {
		include ("inc/enviarficheroavantfax.inc.php");


		$email = $_POST["email"];
		$quien = $_POST["quien"];
		$telefono = $_POST["telefono"];
		$codcliente = $_POST["codcliente"];
		$aquien = $_POST["aquien"];
		$motivo = $_POST["motivo"];
		$notas	= $_POST["notas"];


		$faxnumber = $telefono;
		$filename = $_FILES["file"]["tmp_name"];
			$resultSent = sendFileAvantFax( $filename, $faxnumber);


		}
		break;

}



$page->addVar("page","lista_etiquetas_status",getComboStatus(ETIQUETAS_USUARIO));
$page->addVar("page","lista_etiquetas_basicas",getComboStatus(ETIQUETAS_BASICAS));

$page->addVar("page","tramitados",	$_SESSION["filtra_tramitados"]	);
$page->addVar("page","traspasados", $_SESSION["filtra_traspasados"] );
$page->addVar("page","eliminados",	$_SESSION["filtra_eliminados"]	);


$page->addVar("page","user_nombreapellido",	$_SESSION["user_nombreapellido"]	);

$maxfilas = $_SESSION["resultados_pagina"];

if (!$maxfilas) {
	$maxfilas = 15;
	$_SESSION["resultados_pagina"] = $maxfilas;
}


$data = array();
$i = 0;

$sql = "SELECT * FROM labels WHERE id_label_type=1 ORDER BY label ASC";

$res = query($sql);

while($row = Row($res)){

	$item = array("label"=>$row["label"],
			"id_label"=>$row["id_label"]);
	$data[] = $item;
}

$page->addVar("page","jsonlabels",json_encode($data) );



$sql = "SELECT * FROM tasks ORDER BY task ASC";
$res = query($sql);


$esAlguno = false;
for($t=0;$t<100;$t++){
	//<li ><a  class="{CURRENT1}" href="{MENU_1_URL}">{MENU_1_TXT}</a></li>
	$row = Row($res);
	if (!$row)
		break;

	$id_task = $row["id_task"];

	//TODO: cachear estas respuestas
	if (!puedeActualVerCanal($id_task)){ //Tiene acceso a este canal
			$page->addVar('page','current'. $t, 'oculta' );
			continue;
	}

	$url = "modcentral.php?modo=cambio_filtrotask&id_task=" . $id_task;

	$page->addVar('page','menu_'. $t . '_txt', Corta($row['task'] ,15,"") );
	$page->addVar('page','menu_full_'. $t, $row['task']  );
	$page->addVar('page','menu_'. $t . '_url', $url );

	$id_actual = $_SESSION["tipo_id_task"];

	if ( $id_task == $id_actual) {
		$page->addVar('page','current'. $t, 'current' );
		$esAlguno = true;
	}
}

if (!$esAlguno){
	$page->addVar('page','current00', 'current' );
}


$page->addVar("page","combostask", genComboTarea() );
$page->addVar("page","combosstatus", genCombosStatus($_SESSION["tipo_status"]) );
$page->addVar("page","combosstatus2", genCombosStatus() );//sin autoseleccionado
$page->addVar("page","combosgrupos", genComboGrupos($_SESSION["tipo_id_grupo"]) );
$page->addVar("page","combolocations", getComboStatus($config->get("label_location_id") ) );



//$page->applyOutputFilter();



$min = intval($_REQUEST["min"]);
$numFilas =0;


$filas = array();
$lines = array();


$extraand = "";

if(!$filtraIdComm)
	$extra = false;
else {
	$extra = " communications.id_comm = '$filtraIdComm' ";
	$extraand = "AND";
}


if( $filtraIdContacto ){
	$extra .= " $extraand communications.id_contact = '$filtraIdContacto' ";
	$extraand = "AND";
}


if ( $_SESSION["tipo_buzon"] ){
	$in_out_s = ($_SESSION["tipo_buzon"]=="in")?"in":"out";
	$extra .= " $extraand in_out = '$in_out_s' ";
	$extraand = "AND";
}

if ( $_SESSION["tipo_status"] != -1 and $_SESSION["tipo_status"] ){
	$tipo_s = sql($_SESSION["tipo_status"]);
	$extra .= " $extraand communications.id_status = '$tipo_s' ";
	$extraand = "AND";
}


if ( $_SESSION["tipo_id_grupo"] != -1  and $_SESSION["tipo_id_grupo"] ){
	$tipo_s = sql($_SESSION["tipo_id_grupo"]);
	$extra .= " $extraand communications.id_group = '$tipo_s' ";
	$extraand = "AND";
}

if ( $_SESSION["tipo_id_task"] != -1  && $_SESSION["tipo_id_task"]  ){
	$tipo_s = sql($_SESSION["tipo_id_task"]);
	$extra .= " $extraand channels.id_task = '$tipo_s' ";
	$extraand = "AND";
}


if ( $modo == "cargarMasLineas"  ){
	$tipo_s = sql($_REQUEST["last_id_comm"]);
	$extra .= " $extraand communications.id_comm < '$tipo_s' ";
	$extraand = "AND";
	$usarTablaRapida = false;
}


if ($extra)
	$extra = " WHERE $extra ";


$neworder = " communications.id_comm DESC ";//ordenamiento por defecto
//$neworder = false;

$filtroorden = $_SESSION["modcentro_filtro"];
$updown = $_SESSION["modcentro_filtro_dir"];

switch($filtroorden){
	case "inbox":
		$neworder = " channel " . $updown;
		break;
	case "date":
		$neworder = " date_cap " . $updown;
		break;
	case "priority":
		$neworder = " priority " . $updown;
		break;
	case "contact":
		$neworder = " contact_name " . $updown;
		break;
	case "group":
		$neworder = " group " . $updown;
		break;
	case "status":
		$neworder = " status " . $updown;
		break;
	case "address":
		$neworder = " from_to " . $updown;
		break;
	default:
		break;
}

if ( $filtroorden){
	$usarTablaRapida = false;
}

if ($neworder){
	$neworder = "ORDER BY " .$neworder;
}


$filtrostipos = array("eliminado"=>19,
			"pendiente"=>20,"retenido"=>21,"tramitado"=>22,"traspasable"=>23);

function genFiltroLabels($tipos,$excepciones=false){

	$out = "";
	$OR = "";

	if(!$excepciones)
		$excepciones = array();

	foreach($tipos as $name=>$value){
		if (!array_search($value,$excepciones)){
			$out .= "$OR label_coms.id_label = '$value' ";
			$OR = "OR";
			//echo "<h1>$name:$value</h1>";
		}
	}

	//echo "<h1>". var_export($excepciones,true). "</h1>";/

	return $out;
}


$excepciones = array(0);

if ($_SESSION["filtra_tramitados"])
	$excepciones[] = $filtrostipos["tramitado"];

if ($_SESSION["filtra_traspasados"])
	$excepciones[] = $filtrostipos["traspasable"];//??

if ($_SESSION["filtra_eliminados"])
	$excepciones[] = $filtrostipos["eliminado"];

if (count($excepciones)>1){
	$filtrosPorLabel = " AND ( ". genFiltroLabels($filtrostipos,$excepciones) . ")";
}



if ( $necesitaActualizacionDeTablarapida ) {
//	actualizarTablaRapida();
}




if(0){
	$sql = "SELECT *, communications.id_comm as id_comm
	FROM communications
	INNER JOIN groups ON communications.id_group = groups.id_group
	INNER JOIN contacts ON communications.id_contact = contacts.id_contact
	INNER JOIN channels ON communications.id_channel = channels.id_channel
	INNER JOIN tasks ON channels.id_task = tasks.id_task
	INNER JOIN `status` ON communications.id_status = status.id_status
	LEFT JOIN `label_coms` ON communications.id_comm = label_coms.id_comm
	LEFT JOIN `read_comm` ON communications.id_comm = read_comm.id_comm
	$extra
	$filtrosPorLabel
	GROUP BY communications.id_comm
	ORDER BY $neworder
	LIMIT $min,$maxfilas";

} else {

	$sql = "SELECT
	communications.from_to as from_to,
	communications.title as title,
	communications.date_cap as date_cap,
	communications.priority as priority,
	status.status as status,
	contacts.contact_name as contact_name,
	communications.id_comm as id_comm,
	groups.group as `group`,
	read_comm.date_read  as date_read
	FROM communications
	INNER JOIN groups ON communications.id_group = groups.id_group
	INNER JOIN contacts ON communications.id_contact = contacts.id_contact
	INNER JOIN channels ON communications.id_channel = channels.id_channel
	INNER JOIN tasks ON channels.id_task = tasks.id_task
	INNER JOIN `status` ON communications.id_status = status.id_status
	LEFT JOIN `label_coms` ON communications.id_comm = label_coms.id_comm
	LEFT JOIN `read_comm` ON communications.id_comm = read_comm.id_comm
	$extra
	$filtrosPorLabel
	GROUP BY communications.id_comm
	$neworder
	LIMIT $min,$maxfilas";



	$sql = "SELECT
	communications.from_to as from_to,
	communications.title as title,
	communications.date_cap as date_cap,
	communications.priority as priority,
	status.status as status,
	contacts.contact_name as contact_name,
	communications.id_comm as id_comm,
	groups.group as `group`,
	read_comm.date_read  as date_read
	FROM communications
	INNER JOIN groups ON communications.id_group = groups.id_group
	INNER JOIN contacts ON communications.id_contact = contacts.id_contact
	INNER JOIN channels ON communications.id_channel = channels.id_channel
	INNER JOIN tasks ON channels.id_task = tasks.id_task
	INNER JOIN `status` ON communications.id_status = status.id_status
	LEFT JOIN `label_coms` ON communications.id_comm = label_coms.id_comm
	LEFT JOIN `read_comm` ON communications.id_comm = read_comm.id_comm
	$extra
	$filtrosPorLabel
	GROUP BY communications.id_comm
	$neworder
	LIMIT $min,$maxfilas";

	
	//$sql = str_replace("communications","communications_fast",$sql);
}

$res = query($sql);
$lista_filas_muestra = $FilasAfectadas;

if(0){
	$row2 = queryrow("SHOW SESSION STATUS LIKE 'Handler_read_next%'");
	$page->addVar("page","consulta", $sql . "\nAfecta: $lista_filas_muestra\nFilas leidas: " .  $row2["Value"]. "\n auto: $Auto_increment\n");
}

if (1){
	
	$page->addVar("page","consulta", $resultSent);
}




function genIcon($idcom,$icon,$label){
	if (!$icon)
		$icon = "nota.png";

	return "<img src='icons/$icon' class='ik' title='".html($label)."' />";
}




$small_id_comm = 9999999999;

while($row = Row($res)){
	$numFilas ++;

	$id_comm = 	$row["id_comm"];

	$newfila = array();
	$newfila["from_to"]			=  Corta($row["from_to"],30);
	$newfila["title"]			=  $row["title"];// . "--" . $row["date_read"];;
	//$newfila["title"]			= "(". $row["id_comm"] . ") " . $row["title"];
	$newfila["date_cap"]		= $row["date_cap"];
	$newfila["priority"]		= $row["priority"];
	$newfila["channel"]			= $row["channel"];
	$newfila["status"]			= $row["status"];
	$newfila["statuscss"]		= genCssName($row["status"]);
	$newfila["contact_name"]	= $row["contact_name"];
	$newfila["id_comm"]			= $row["id_comm"];
	$newfila["group"]			= $row["group"];

	$newfila["filacss"] = ($numFilas%2)?"":"titulofilapar";
	$newfila["filacss2"] = ($numFilas%2)?"":"filaDatospar";
	$newfila["pi"] = ($numFilas%2)?"is_par":"is_impar";


	if ( $row["id_comm"]< $small_id_comm){
		$small_id_comm = $row["id_comm"];
	}

	if (  $row["date_read"] ) {
		$newfila["claseleido"] = "leido";
	} else {
		$newfila["claseleido"] = "sinleer";
	}


	$out = "";

	$sql2 = "SELECT label_coms.id_comm,labels.icon,labels.label".
		 " FROM communications INNER JOIN `label_coms` ON communications.id_comm = label_coms.id_comm ".
		 " INNER JOIN `labels` ON label_coms.id_label = labels.id_label WHERE communications.id_comm='$id_comm' ";

	$sql2 = "SELECT label_coms.id_comm,labels.icon,labels.label".
		 " FROM label_coms  ".
		 " INNER JOIN `labels` ON label_coms.id_label = labels.id_label WHERE label_coms.id_comm='$id_comm' ";

	$res2 = query($sql2);
	while($row2=Row($res2)){
		$out .= genIcon($row2["id_comm"],$row2["icon"],$row2["label"]);
	}

	$newfila["icons"] = $out . " &nbsp;";

	$lines[] = $newfila;
}

if (!$lista_filas_muestra){
	//$page->addVar("page","ocultarlineavacia","oculto");
	$lines[] = array("ocultarlineavacia"=>"oculto");
} else {
	$page->addVar("page","ocultarmensajelineavacia","oculto");
}

$page->addRows('list_entry', $lines );

switch($modo){
	case "cargarMasLineas":

		$salida = array();
		$salida["ok"] = true;

		//$html = $page->displayParsedTemplate("list_entry");
		$html = $page->getParsedTemplate("list_entry");

		$salida["last_id_comm"] = $small_id_comm;
		$salida["sql"] = $sql;
		$salida["html"] = $html;

		$salida["paginasize"] = $maxfilas;

		if (!$_SESSION["modcentro_filtro"]){
			$salida["ordenlogico"] = 1;
		}else{
			$salida["ordenlogico"] = 0;
		}

		echo json_encode($salida);

		exit();
		break;

	default:
		break;
}

$page->addVar('page',"num_lineas", $numFilas );
$page->addVar('page',"last_id_comm", $small_id_comm );
$page->addVar('page',"paginasize", $maxfilas );
$page->addVar('page',"desordenado", (($_SESSION["modcentro_filtro"])?"true":"false") );


$page->configNavegador( $min, $maxfilas,$numFilas);




/*
echo "<xmp>";

echo print_r($page);

echo var_export($page,true);



echo "</xmp>";

*/


$page->Volcar();

//echo $page->dump(null,"XUL");



/*


echo "<xmp>";

echo var_export($_SESSION);

echo "---------------------------------------------------------------\n";

echo var_export($page,true);

echo "</xmp>";
*/

?>
