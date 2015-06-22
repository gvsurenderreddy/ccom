<?php





function genVistaFinal($com){
	global $config;

	$id_comm = $com->get("id_comm");

	$sql = "SELECT * FROM phone_calls WHERE call_id_comm ='$id_comm' ";
	$row = queryrow($sql);

	$data = $row["call_information"];
	
	if(!$data){
		return "<center>". _("Documento no disponible"). "</center>";
	}

	$info = unserialize($data);

	$html = "<table>".
		"<tr>"."<td>Quien llama</td>"."<td>".html($info["quien"])."</td>"."</tr>".
		"<tr>"."<td>A quien llama</td>"."<td>".html($info["aquien"])."</td>"."</tr>".		
		"<tr>"."<td>Telefono</td>"."<td>".html($info["telefono"])."</td>"."</tr>".		
		"<tr>"."<td>Cod.Cliente</td>"."<td>".html($info["codcliente"])."</td>"."</tr>".		
		"<tr>"."<td>Motivo</td>"."<td>".html($info["motivo"])."</td>"."</tr>".				
		"<tr>"."<td>Notas</td>"."<td>".html($info["notas"])."</td>"."</tr>".
		"</table>";

	return $html;
}


function genVistaFinal_raw($com){
	die("no implementado");
}








?>