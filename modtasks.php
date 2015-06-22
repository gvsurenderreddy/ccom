<?php


/**
 * Gestion de tareas
 *
 * Alta/Baja/modificación de tareas
 * @package ecomm-core
 */

include("tool.php");

include("class/task.class.php");


$auth = canRegisteredUserAccess("modtasks");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$page->addVar('headers', 'titulopagina', 'Gestion de tareas');
$page->addVar('page', 'labelalta', "Alta tarea" );
$page->addVar('page', 'labellistar', "Listar" );


$mostrarListado = false;
$mostrarEdicion = false;


if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 10;
	

$task = new Tarea();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

		$extracondicion  = " AND task LIKE '%$filtranombre_s%' ";

		/*<input type="hidden" name="modo" value="filtrar-elemento" />
	<input type="hidden" name="filtrar-elemento" value="" id="filtra-list-value" />*/
		$mostrarListado = true;
		break;
	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;

		break;
	case "guardarcambios":
		$id =  CleanID($_POST["id_task"]);

		if ( $task->Load($id) ){
			$task->set("task", $_POST["task"]  , FORCE);
			$task->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":
		$task->set("task", $_POST["task"] , FORCE);

		$task->Alta();
		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$task->Load($id);

		$nombreUsuarioMostrar = html($task->getNombre());
		$metodo = "Modificar";
		$newmodo = "guardarcambios";
		break;

	case "alta":
		$mostrarEdicion = true;

		$metodo = "Alta";
		$newmodo = "guardaralta";
		break;
    case "eliminar":
        $id =  CleanID($_REQUEST["id"]);

        $sql = "DELETE FROM tasks WHERE id_task='$id'";
        query($sql);
		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;
}

if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_tareas.txt' );

	$page->addVar( 'edicion', 'modname', $template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',$metodo );
	$page->addVar( 'edicion', 'modoedicion',$newmodo );

	$page->addVar( 'edicion', 'tarea', $task->get("task")  );
	$page->addVar( 'edicion', 'id', $task->get("id_task")  );
}

if ($mostrarListado){
	$page->configMenu("listar");


	$page->setAttribute( 'listado', 'src', 'listado_tareas.txt' );

	$maxfilas = 10;
	$min = intval($_REQUEST["min"]);


	$list = array();

	$sql = "SELECT * FROM tasks WHERE id_task>0 $extracondicion  ORDER BY task ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_task"], "tarea"=>$row["task"] );

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );
	$page->configNavegador( $min, $maxfilas,$numFilas);
}





$page->Volcar();



?>