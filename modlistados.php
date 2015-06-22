<?php

include("tool.php");


$auth = canRegisteredUserAccess("modlistados");
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$outItems = "";

$sql = "SELECT IdListado,NombrePantalla,CodigoSQL,Peso FROM ges_listados WHERE (Eliminado=0) $sqlarea ORDER BY Peso DESC, NombrePantalla ASC";
$res = query($sql);

if ($res) {
	while ($row = Row($res)) {
		$NombrePantalla = $row["NombrePantalla"];
		$id = $row["IdListado"];

		$activos = DetectaActivos( $row["CodigoSQL"]);

		$code .= $row["CodigoSQL"] . "\n----------------------------------\n";

		$NombrePantalla = cv_input($NombrePantalla);
		$NombrePantalla = strictify($NombrePantalla);

		$peso = $row["Peso"];

		if ($peso){
			$style="font-weight: bold";
		} else {
			$style="";
		}

		$outItems = $outItems . "<option style='$style'  value='$id' oncommand='SetActive(\"$activos\")'> $NombrePantalla</option>\n";

		$jsactivos[$id] = $activos;
	}
}
		

$hoy = date("d-m-Y");


$filtros = "";


foreach ( $jsactivos as $key=>$value){
	$filtros .=  " activos[" . $key . "]= '". $value . "';\n";

}



$page->addVar('headers', 'titulopagina', $trans->_('Listados') );
$page->addVar('page', 'labelalta', $trans->_("Listado") );
$page->addVar('page', 'labellistar', $trans->_("Listados") );


$page->configMenu("sololistar");

$page->setAttribute( 'listado', 'src', 'informe_listado.txt' );


$page->addVar( 'list', 'hoy', $hoy );
$page->addVar( 'list', 'filtros', $filtros );
$page->addVar( 'list', 'outitems', $outItems );


$page->addVar("list","comboestadopedidos", genCombosStatus($_SESSION["tipo_status"]) );

$page->addVar("list","combodelegaciones",getComboStatus($config->get("label_location_id") )  );



$page->Volcar();



/* ******************* FUNCIONES *********************** */


// function to change german umlauts into ue, oe, etc.
function cv_input($str){
     $out = "";
     for ($i = 0; $i<strlen($str);$i++){
           $ch= ord($str{$i});
           switch($ch){
               case 241: $out .= "&241;"; break;
               case 195: $out .= "";break;
               case 164: $out .= "ae"; break;
               case 188: $out .= "ue"; break;
               case 182: $out .= "oe"; break;
               case 132: $out .= "Ae"; break;
               case 156: $out .= "Ue"; break;
               case 150: $out .= "Oe"; break;

               default : $out .= chr($ch) ;
           }
     }
     return $out;
}

function strictify ( $string ) {
       $fixed = htmlspecialchars( $string, ENT_QUOTES );

       $trans_array = array();
       for ($i=127; $i<255; $i++) {
           $trans_array[chr($i)] = "&#" . $i . ";";
       }

       $really_fixed = strtr($fixed, $trans_array);

       return $really_fixed;
}





function DetectaActivos($cod){
	global $esTPV;
	$a = "";

	if( strpos($cod,'%IDIDIOMA%') >0 ){
		$a .= "IdIdioma,";
	}
	if( strpos($cod,'%DESDE%')  >0){
		$a .= "Desde,";
	}
	if( strpos($cod,'%HASTA%') >0){
		$a .= "Hasta,";
	}
	if( strpos($cod,'%ESTADO%') >0){
		$a .= "Estado,";
	}
	if( strpos($cod,'%CODCLIENTE%') >0){
		$a .= "CodCliente,";
	}

	if( strpos($cod,'%IDDELEGACION%')  >0 ){
		$a .= "IdDelegacion,";
	}
	if( strpos($cod,'%IDTIENDA%')  >0 and !$esTPV){
		$a .= "IdTienda,";
	}
	if( strpos($cod,'%IDFAMILIA%')  >0){
		$a .= "IdFamilia,";
	}
	if( strpos($cod,'%IDSUBFAMILIA%')  >0){
		$a .= "IdSubFamilia,";
	}
	if( strpos($cod,'%IDARTICULO%')  >0){
		$a .= "IdArticulo,";
	}
	if( strpos($cod,'%FAMILIA%')  >0){
		$a .= "IdFamilia,";
	}
	if( strpos($cod,'%IDMODISTO%')  >0){
		$a .= "IdModisto,";
	}
	if( strpos($cod,'%STATUSTBJOMODISTO%')  >0){
		$a .= "StatusTrabajo,";
	}
	if( strpos($cod,'%IDPROVEEDOR%')  >0){
		$a .= "IdProveedor,";
	}
	if( strpos($cod,'%IDCENTRO%')  >0){
		$a .= "IdCentro,";
	}
	if( strpos($cod,'%IDUSUARIO%')  >0){
		$a .= "IdUsuario,";
	}
	if( strpos($cod,'%REFERENCIA%')  >0){
		$a .= "Referencia,";
	}
	if( (strpos($cod,'%IDPRODBASEDESDECB%')>0) or (strpos($cod,'%CODIGOBARRAS%')>0) ){
		$a .= "CB,";
	}

	return $a;
}




?>
