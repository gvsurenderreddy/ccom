<?php


define("NO_SESSION",1);


include_once("tool.php");

include_once("inc/runmeforever.inc.php");
include_once("inc/pasarelas.inc.php");

include_once("class/comunicacion.class.php");
include_once("class/labels.class.php");
include_once("class/fax.class.php");
include_once("infofaxgw/fax.class.php");




$com = new Comunicacion();
$label = new Etiqueta();

if (!$config->existe("last_infofaxindex")){
	$config->altaclave("last_infofaxindex",0);
}

if (!$config->existe("id_label_infofax")){
	$config->altaclave("id_label_infofax",0);
}

if (!$config->existe("id_channel_infofax")){
	$config->altaclave("id_channel_infofax",0);
}


$id_channel_infofax = $config->get("id_channel_infofax");

//ya esta todo correcto, podemos proseguir...
$last_infofaxindex = $config->get("last_infofaxindex");
$id_label_infofax = $config->get("id_label_infofax");


if (!$id_label_infofax){
	echo timestamp(). " Sin configurar  [id_label_infofax:$id_label_infofax] $cr";
	return;
}


if (!$label->Load($id_label_infofax)){
	echo timestamp(). " Error: [last_infofaxindex:$last_infofaxindex] posiblemente erroneo $cr";
	return;
}


$sql = "SELECT * FROM communications WHERE ";

$pasadasize = 100;

echo timestamp(). " Lote  [$last_infofaxindex ],$pasadasize $cr";


$podemosProgresar = true;


$num = 0;

$sql = "SELECT id_comm FROM communications WHERE id_comm>='$last_infofaxindex' ORDER BY id_comm ASC LIMIT $pasadasize";

$res = query($sql);


while( $row = Row($res)){
	$id_comm = $row["id_comm"];


	$sql = "SELECT fax_id_comm FROM faxes WHERE fax_id_comm='$id_comm' LIMIT 1";
	$row = queryrow($sql);
	if ($row["fax_id_comm"]){
		//Ya tenemos un fax para esta comunicacion, podemos seguir
		echo timestamp(). " [$id_comm] ya fue considerado como fax $cr";
		continue;
	}


	if(!$com->Load($id_comm)){
		echo timestamp(). " ERROR: No se pudo cargar $id_comm $cr";
		return;
	}
	
	$titulo = $com->get("title");
	echo timestamp(). " Examinando [$id_comm][$titulo] $cr";

	if ( strpos("#". $titulo,"Infofax recibido desde:" )>0 ){
			//Es mensaje de infofax, nos interesa!
			echo timestamp(). " Aplicando label infofax [$id_label_infofax]  $cr";

			$label->createLink($id_comm);
			
//			$podemosProgresar = false;

			echo timestamp(). " Aplicando buzon infofax [$id_channel_infofax](id:$id_comm)  $cr";
			$com->set("id_channel",$id_channel_infofax);
			$com->Modificacion();


			$sql = "SELECT * FROM gw_email_subfiles WHERE email_id_comm='$id_comm' AND description LIKE '%.PDF' LIMIT 1";

			$row = queryrow($sql);

			$path_subfile = $row["path_subfile"];
			$subfile = basename($path_subfile);
			$path = str_replace($subfile,"",$path_subfile);
			$gw_viewfiles_path	= $config->get("gw_viewfiles_path");


			$newName = $subfile. ".pdf";
			$oldDir = $path_subfile;
			$newDir = $gw_viewfiles_path . "/".  $newName;
			
			if ( copy( $oldDir, $newDir ) ){
				echo timestamp(). " Copiando fichero en $newDir  $cr";

				$fax = new Infofax();
				$visible = $newName;
				$fechahora = $com->get("date_cap");

   			    $fax->AltaInfofax($original, $visible, $id_comm,$fechahora);
			}

	} else {
		//No es mensaje de infofax, saltamos

	}

	$num++;

	if ($podemosProgresar){
		$config->set("last_infofaxindex",$id_comm);
	}

}


echo timestamp(). " Examinandos [$num] $cr";



?>