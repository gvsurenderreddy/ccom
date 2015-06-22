<?php

/**
 * Modulo de recepcion de peticiones AJAX
 *
 * Este fichero centraliza las peticiones ajax.
 * @package ecomm-core
 */




include("tool.php");
include("class/gateway.class.php");
include("class/comunicacion.class.php");

$id_user = getSesionDato("id_user");


if (!$id_user) /* No estamos logueados*/ {
	$respuesta = array("ok"=>false,"logout"=>true);

	echo json_encode($respuesta); //NOTA: avisamos al usuario
	die();
}


$modo = $_REQUEST["modo"];


$salida = array();
$salida["ok"] = false;

switch($modo){
	case "cargaposiblesclientes": {
			$substring_s = sql($_REQUEST["subcadena"]);

			$sql = "SELECT * FROM contacts WHERE contact_name LIKE '%". $substring_s. "%' ";

			$lines = array();

			$res = query($sql);
			$num= 0;
			while($row= Row($res)){
				$item = array('name'=>$row['contact_name'],'id_contact'=>$row['id_contact']);
				$lines[$num] = $item;
				$num++;
			}

			$data = array("sql"=>$sql,"ok"=>true, "lines"=>$lines, "numlines"=>$num);

			echo json_encode($data);
		}
		break;
	case "selectcontact":{
		$page    =    new Pagina();
		$page->setRoot( 'templates' );
		$page->readTemplatesFromInput('selectcontact.popup.txt');
		$page->addVar('page', 'modname', "modcentral.php" );
		$page->addVar('page', 'id_comm', $id_comm );
	
		$page->Volcar();

		}
		break;
	case "reenviar":
		$id_comm = $_REQUEST["id_comm"];
		$newto = $_REQUEST["newto"];
		$newasunto = $_REQUEST["newasunto"];
		$mensaje = $_REQUEST["mensaje"];

		mail($newto,$newasunto,$mensaje);

		$data = array("ok"=>true, "msg"=>$trans->_("Mensaje enviado"));
		echo json_encode($data);

		break;


	/*
	Permite crear nuevas etiquetas
	*/		
	case "newTag":
		$newTag =$_REQUEST["newtag"];
		$id_comm = $_REQUEST["id_comm"];

		///echo "<h1>es:$modo</h1>";
		if(1){
			include_once("class/labels.class.php");



			$label = new Etiqueta();

			//echo time() . ": intenta cargar ($newTag)";

			if (!$label->LoadByName($newTag)) {
				//echo time() . ": no existe, se crea";

				//INFO: no exista la label
				$label->set("label",$newTag);

				//TODO: $label->set("label_type", de usuario						
				$label->Alta();
				//echo time() . ": dando de alta";
			}

			echo time() . ": enlazando con ($id_comm)";
			$label->createLink($id_comm);
		}
		break;
	
		break;




	case "eliminarLabel":
		{

		include_once("class/label.class.php");
		//idlabelcom	3221
		//modo	eliminarLabel
		//	$idlabelcom
		$idlabelcom = $_POST["idlabelcom"];


		$label = new Etiqueta();
		$label->eliminarComlink($idlabelcom);



		}
		break;


	case "cargaSolapa":
		$id_comm = $_POST["id_comm"];

		markRead($id_comm);

		$page    =    new Pagina();
		$page->setRoot( 'templates' );
		$page->readTemplatesFromInput('subsolapa.txt');
		$page->addVar('page', 'modname', "modcentral.php" );
		$page->addVar('page', 'id_comm', $id_comm );
		$page2 = new Pagina();
		$page2->setRoot( 'templates' );
		{
			
			$page2->readTemplatesFromInput('subsolapa_datos.txt');

			$sql = "SELECT *
				FROM communications
				INNER JOIN groups ON communications.id_group = groups.id_group
				INNER JOIN contacts ON communications.id_contact = contacts.id_contact
				INNER JOIN channels ON communications.id_channel = channels.id_channel
				INNER JOIN `status` ON communications.id_status = status.id_status
				WHERE id_comm = '$id_comm' ";

			$row = queryrow($sql);

			$newfila = array();
			$newfila["from_to"]			= $row["from_to"];
			$newfila["title"]			= $row["title"];
			$newfila["date_cap"]		= $row["date_cap"];
			$newfila["priority"]		= $row["priority"];
			$newfila["channel"]			= $row["channel"];
			$newfila["status"]			= $row["status"];
			$newfila["contact_name"]	= $row["contact_name"];
			$newfila["id_comm"]			= $row["id_comm"];
			$newfila["group"]			= $row["group"];

			$page2->addVars('page', $newfila );
		}

		$pagetext_datos = $page2->getParsedTemplate();
		$page->addVar('page','solapadatos',$pagetext_datos);

		$pagetext = $page->getParsedTemplate();

		$data = array("html"=>$pagetext,"ok"=>true, "id_comm"=>$id_comm);


		echo json_encode($data);
		break;

	case "cargaSubSolapa":
		$id_comm = CleanID($_POST["id_comm"]);
		$exito = true;
		

		$page    =    new Pagina();
		$page->setRoot( 'templates' );
		{
			$submodo = $_POST["submodo"];
			switch($submodo){
				case "datos":
					$page->readTemplatesFromInput('subsolapa_datos.txt');

					$sql = "SELECT *
						FROM communications
						INNER JOIN groups ON communications.id_group = groups.id_group
						INNER JOIN contacts ON communications.id_contact = contacts.id_contact
						INNER JOIN channels ON communications.id_channel = channels.id_channel
						INNER JOIN `status` ON communications.id_status = status.id_status
						WHERE id_comm = '$id_comm' ";

					$row = queryrow($sql);

					$newfila = array();
					$newfila["from_to"]			= $row["from_to"];
					$newfila["title"]			= $row["title"];
					$newfila["date_cap"]		= $row["date_cap"];
					$newfila["priority"]		= $row["priority"];
					$newfila["channel"]			= $row["channel"];
					$newfila["status"]			= $row["status"];
					$newfila["contact_name"]	= $row["contact_name"];
					$newfila["id_comm"]			= $row["id_comm"];
					$newfila["group"]			= $row["group"];

					$page->addVars('page', $newfila );					
					break;
					
				case "documento":
					$page->readTemplatesFromInput('subsolapa_visorhtml.txt');

					$sql = "SELECT *
						FROM communications
						INNER JOIN groups ON communications.id_group = groups.id_group
						INNER JOIN contacts ON communications.id_contact = contacts.id_contact
						INNER JOIN channels ON communications.id_channel = channels.id_channel
						INNER JOIN `status` ON communications.id_status = status.id_status
						WHERE id_comm = '$id_comm' ";

					$row = queryrow($sql);
					$newfila = array();
					$newfila["from_to"]			= $row["from_to"];
					$newfila["title"]			= $row["title"];
					$newfila["date_cap"]		= $row["date_cap"];
					$page->addVars('page', $newfila );

					//$preview_html = getPreviewForCom( $id_comm );
					$com = new Comunicacion();
					$com->Load($id_comm);
					$preview_html = $com->getVista();


					$page->addVar('page', "preview_html" , $preview_html );

					break;
				case "reenviar":
					$page->readTemplatesFromInput('subsolapa_reenviar.txt');

					$sql = "SELECT *
						FROM communications
						INNER JOIN groups ON communications.id_group = groups.id_group
						INNER JOIN contacts ON communications.id_contact = contacts.id_contact
						INNER JOIN channels ON communications.id_channel = channels.id_channel
						INNER JOIN `status` ON communications.id_status = status.id_status
						WHERE id_comm = '$id_comm' ";

					$row = queryrow($sql);
					$newfila = array();
					$newfila["from_to"]			= $row["from_to"];
					$newfila["title"]			= $row["title"];
					$newfila["date_cap"]		= $row["date_cap"];
					$page->addVars('page', $newfila );
			

					break;
				case "traza":
					$traza = "";
					$page->readTemplatesFromInput('subsolapa_traza.txt');
					
					$sql = "SELECT *
						FROM communications
						INNER JOIN groups ON communications.id_group = groups.id_group
						INNER JOIN contacts ON communications.id_contact = contacts.id_contact
						INNER JOIN channels ON communications.id_channel = channels.id_channel
						INNER JOIN `status` ON communications.id_status = status.id_status
						WHERE id_comm = '$id_comm' ";

					$row = queryrow($sql);
					$newfila = array();
					$newfila["from_to"]			= $row["from_to"];
					$newfila["title"]			= $row["title"];
					$newfila["date_cap"]		= $row["date_cap"];
					$newfila["id_comm"]		= $id_comm;

					$page->addVars('page', $newfila );

					{
						include("class/traza.class.php");
						$traza = genTrazaPorPedido($id_comm);
						$page->addRows('list_entry', $traza );
					}					


					break;

				case "riesgo":
					$page->readTemplatesFromInput('subsolapa_riesgo.txt');

					$sql = "SELECT *
						FROM communications
						INNER JOIN contacts ON communications.id_contact = contacts.id_contact
						INNER JOIN risk_management ON risk_management.id_contact = communications.id_contact
						WHERE id_comm = '$id_comm' ";

					$row = queryrow($sql);
					$row["sql"] = $sql;

					$page->addVars('page', $row );

					break;
				case "etiquetas":

					$page->readTemplatesFromInput('subsolapa_etiquetas.txt');

					$page->addVar( "page", "id",$id_comm );
					$page->addVar( "page", "etiquetas",genListEtiquetasComm($id_comm) );
					$page->addRows('list_etiquetas', genListEtiquetasCommArray($id_comm) );

					break;
				default:
					$exito = false;
					break;
				break;
			}
		}

		$page->addVar('page', 'modname', "modcentral.php" );
		$page->addVar('page', 'id_comm', $id_comm );

		$pagetext = $page->getParsedTemplate();

		$data = array("html"=>$pagetext,"ok"=>$exito, "id_comm"=>$id_comm);


		echo json_encode($data);

		break;
	case "cargaSugerenciasMedio":
		//Tenemos el id de la media
		$id_media_s = sql(CleanID($_POST["id_media"]));

		//pero no sabemos que es...

		$sql = "SELECT * FROM medias WHERE id_media = '$id_media_s'";
		$row = queryrow($sql);


		/*
	    *  medias 
		* id_media
		* media -> nombre del medio de transferencia: fax, email, Tfno, form, etc...
        * id_gateway
		*/

		//sabemos que gateway, cogemos sus datos... para llamar a esta "puerta"
		$id_gateway_s = $row["id_gateway"];
			
		$sql = "SELECT * FROM gateway WHERE id_gateway ='$id_gateway_s' ";
		$row = queryrow($sql);

		/*
		* id_gateway
		* module  --> filename del modulo que se configura
		* enabled  --> 0/1, en 0 indica que esta pasarela no esta activa, y no se debe correr
         */

		$module = $row["module"];

		$bloquesugerencias = getSugerenciasFromModulo( $module );


		$out = "";

		if ($bloquesugerencias){

			foreach ( $bloquesugerencias as $sugerencia){
				$out .= "<option>". $sugerencia . "</option>\n";
			}

		}

		

		$salida["ok"] = true;
		$salida["combohtml"] = $out;
		$salida["combo"] = $bloquesugerencias;

		echo json_encode($salida);
		break;

	case "estadolabels2":
		$labels = $_REQUEST["labels"];

		$labels_interes = $config->get("map_labelsshow");

		$lista = split(",",$labels);
		$interesantes = split(",",$labels_interes);

		$orlabels = "";
		foreach($interesantes as $interesante){
			$interesante_s = CleanID($interesante);
			$orlabels .= " $OR label_coms.id_label = '$interesante_s' ";
			$OR = " OR ";
		}
		

		$resultado = array();

		foreach($lista as $item){

			if (!$item) continue;

			$num_s = CleanID($item);

			$sql =	"SELECT count( communications.id_comm ) sinleer
				FROM communications
				LEFT JOIN `label_coms` ON communications.id_comm = label_coms.id_comm
				LEFT JOIN `read_comm` ON communications.id_comm = read_comm.id_comm
				WHERE label_coms.id_label = '$num_s'
				AND read_comm.date_read IS NULL ";

			$row = queryrow($sql);
			$num_sin_leer = $row["sinleer"];
			

			$interes = array();
			$sql =	"SELECT count( com_{$num_s}.id_comm ) num, label_coms.id_label
					FROM com_{$num_s}
					INNER JOIN `label_coms` ON com_{$num_s}.id_comm = label_coms.id_comm
					WHERE ( $orlabels  ) GROUP BY label_coms.id_label ";



			$res = query($sql);
			while($row = Row($res)){
				$dato = array("id_label"=>$row["id_label"], "num"=>$row["num"]);
				$interes[] = $dato;
			}			

			$datos = array( "sinleer" => $num_sin_leer, "id_label"=>$num_s, "interes"=>$interes);
		
			$resultado[$num_s] = $datos;
		}

		echo json_encode($resultado);

		break;


	case "estadolabels":
		$labels = trim($_REQUEST["labels"],",");

		$labels_interes = trim($config->get("map_labelsshow"),",");

		$lista = split(",",$labels);
		$interesantes = split(",",$labels_interes);

		$orlabels = "";
		foreach($interesantes as $interesante){
			$interesante_s = CleanID($interesante);
			$orlabels .= " $OR label_coms.id_label = '$interesante_s' ";
			$OR = " OR ";
		}



		/* Extraemos contabilidad de centros y labels de interes */

		$contaCentros = array();
		$comaCentros = join(",",$lista);
		$comaLabelsinteres = join(",",$interesantes);

		$sql = "SELECT count( label_coms1.id_comm ) num, label_coms1.id_label,
				label_coms2.id_label as id_centro
				FROM communications INNER JOIN label_coms AS label_coms1 ON
				communications.id_comm = label_coms1.id_comm INNER JOIN label_coms AS
				label_coms2 ON label_coms1.id_comm=label_coms2.id_comm
				WHERE label_coms1.id_label IN($comaLabelsinteres) AND label_coms2.id_label IN($comaCentros)
				GROUP BY label_coms1.id_label, label_coms2.id_label";

		$res = query($sql);

		while($row =Row($res)){

			$id_centro = $row["id_centro"];

			if (!is_array($contaCentros[$id_centro]))
				$contaCentros[$id_centro] = array();

			$datosCentro = $contaCentros[$id_centro];
			
			//$datosCentro[$row["id_label"]] = $row["num"];
			//$datosCentro[$row["id_label"]] = $row["num"];
			$dato = array( "id_label"=> $row["id_label"], "num"=>$row["num"] );
			$datosCentro[] = $dato;

			$contaCentros[$id_centro] = $datosCentro;
		}

	
		/* Agregamos contabilidad de no-leidos */

		$resultado = array();

		foreach($lista as $item){

			if (!$item) continue;

			$num_s = CleanID($item);

			$sql =	"SELECT count( communications.id_comm ) sinleer
				FROM communications
				LEFT JOIN `label_coms` ON communications.id_comm = label_coms.id_comm
				LEFT JOIN `read_comm` ON communications.id_comm = read_comm.id_comm
				WHERE label_coms.id_label = '$num_s'
				AND read_comm.date_read IS NULL ";

			$row = queryrow($sql);
			$num_sin_leer = $row["sinleer"];

			/*
			$interes = array();
			$sql =	"SELECT count( com_{$num_s}.id_comm ) num, label_coms.id_label
					FROM com_{$num_s}
					INNER JOIN `label_coms` ON com_{$num_s}.id_comm = label_coms.id_comm
					WHERE ( $orlabels  ) GROUP BY label_coms.id_label ";



			$res = query($sql);
			while($row = Row($res)){
				$dato = array("id_label"=>$row["id_label"], "num"=>$row["num"]);
				$interes[] = $dato;
			}
            $datos = array( "sinleer" => $num_sin_leer, "id_label"=>$num_s, "interes"=>$interes);
             */

			$interes = array();
			$datosCentro = $contaCentros[$num_s];
			if(is_array($datosCentro)){
				foreach($datosCentro as $datosLabel){
					$dato = array("id_label"=>$datosLabel["id_label"], "num"=>$datosLabel["num"]);
					$interes[] = $dato;
				}
			}


			$datos = array( "sinleer" => $num_sin_leer, "id_label"=>$num_s, "interes"=>$interes);

			$resultado[$num_s] = $datos;
		}

		echo json_encode($resultado);

		break;

}



?>
