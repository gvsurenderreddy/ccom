<?php


ob_start("ob_gzhandler");

define('TIME_BROWSER_CACHE','3600');
$last_modified = filemtime(__FILE__);



if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND
	strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified) {
  header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified',TRUE,304);
  header('Pragma: public');
  header('x-macrojs: yes');
  header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
  header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
  header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');
  die();
}

header('Content-type: text/css; charset=UTF-8');
header('Pragma: public');
header('x-macrojs: no');
header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');


$colortooltip  = "#ffe";
$colorenlaces	=	"#d05627";

$caliente		= "#ffa500";

$grisoscuro		= "#444";

$naranjaclaro2	= "#fff3dc";
$naranjaclaro2	= "#fff3dc";

$alturaSolapas = "300";
$alturaSolapaMin = "210";

/* ------------------------------------------------------------------------ */

$colorgeneraltxt = $grisoscuro;

/*--------------------------*/

$lines		= $colortooltip;

/*--------------------------*/

$fontsize	= "12px";

/*--------------------------*/

$bggeneral	= $colortooltip;

/*--------------------------*/
$resaltada	= $caliente;
//$resaltada = "#ccf";
//$resaltada = "black";


/*--------------------------*/
//$resaltadatxt  = "blue";
//$resaltadatxt  = "";
$resaltadatxt = "white";

/* --------------------------*/

$leidotxt	= "#999";
$leidotxt	= "#777";
$leidotxt	= "#666";
$leidotxt = "#7E519C";


/*--------------------------*/
$sinleertxt = "black";

$sinleertxt = "blue";


/*--------------------------*/

$colorecomtxt = "red";
/*--------------------------*/

$enlacessolapastxt = "blue";//$colorenlaces;
$enlacessolapastxt = $colorenlaces;

/*--------------------------*/

$colorseparador = "#ccc";


/*--------------------------*/
/*--------------------------*/


echo <<<EOT
/ *  para central 3 */


#lineas_de_comm {
 width: 100%;
}

.enlaceTituloCom {
 display: block;
 padding-left: 16px;
 padding-right: 16px;
 font-size: $fontsize;
 padding-top: 0px;
 padding-bottom: 0px;
 border-top: 0px solid white;
 cursor: hand;
 cursor: pointer;
}


.leido b {
	zfont-weight: normal;
	color: $leidotxt;
}

.sinleer b {
	color: $sinleertxt;

}



#cajafiltros2 {
 margin-right: 8px;
 margin-left: 7px;
}


/* para central */

.submenux {
 margin-left: 8px;
}


#cajalinkslabels {
 display: block;
}


th.cabecera ,th.cabecera * {
	white-space: nowrap
}

.cajaEnlacesSolapas {
   /* Zona de enlaces/solapas para un comunicado desplegado */
   margin: 0px;
   padding:4px;
   color: $colorseparador;
   padding-left: 16px;
   border: 0px;


    
}

.cajaEnlacesSolapas a {
   font-weight: bold;
   text-decoration: none;
   color: $enlacessolapastxt;
}

.cajaEnlacesSolapas a:hover{
  text-decoration: underline;
}


.seleccionado td, .seleccionado b, .seleccionado *, .seleccionado tr {
 color: $resaltadatxt;
 background-color: $resaltada;
}


.seleccionado_datos, .seleccionado_datos td, .seleccionado_datos b, .seleccionado_datos *, .seleccionado_datos tr
, .seleccionado_datos , .seleccionado_datos td * {
  display: none;
}


.filaTitulo td {
     background-image: none;
}


.titulofilapar {
  background-color: $resaltada;

}

.titulofilapar td {   }

.filaDatos td { }

#lineas_de_comm td {
	zborder-color: $lines;
}

.filaTitulo td, .filaDatos td  {
   background-color: $bggeneral;
}


.filaTitulo a:hover {
   zbackground-color: $resaltada;
}

.seleccionado td a:hover {
	/* el raton esta sobre una seleccionada */
   zbackground-color: $resaltada;
   zpadding-top: 4px;
   zpadding-bottom: 4px;
}


.filaDatospar td, .titulofilapar td {
}

/* todos */
.filaTitulo td, .filaDatos td,.filaDatospar td, .titulofilapar td, #lineas_de_comm td  {
  background-color: $bggeneral;
}


.col1 ,* {
  color: $colorgeneraltxt;
}


/* Estilo compacto para menu de canales */
ul#menux li a {
  padding-left: 2px;
  padding-right: 2px;
  border-right: 1px solid #eee;
  border-top: 1px solid #eee;
  width: 60px;
  min-width: 60px;

}

.updown {
 margin-right: 2px;
}


.label-filtro {
	display: inline-block;
	width: 100px;
}


input.cod_cliente {
	width: 8em;
}

input.idcomm {
	width: 6em;
}

form.etiquetador, form.etiquetador_canal {
	display: inline-block;
}

.mensajeListaVacia  {
	text-align: center;
	font-weight: bold;
	font-size: 12px;
	padding: 20px;
}

.idcom {
	color: #colorecomtxt;
	margin-right: 8px
}

.selcomm {
	float:left;padding-right: 16px;margin-right: 7px;
}

.solapascontenedor {
	max-height: {$alturaSolapas}px!important;

	min-height: {$alturaSolapaMin}px;
	zheight: {$alturaSolapas}px;
	overflow:auto;
	background-color: white;
	border-bottom:2px solid orange;
	border-left:	0px solid orange;
	border-right:	0px solid orange;
	width:100%;
}

.tagform {
	padding:20px 0px;
}

.tagform div.line {
	clear:both;
	min-height:50px;
	margin-bottom:15px;
}

.tagform label {
	display:block;
	font-weight:bold;
	margin-bottom:5px;
}


.principiofiltro {
 margin-left: 32px;
}

.colz1 {
	text-align: right;
	margin-right: 8px;
	padding-right: 8px;
	min-width:64px;
	width: 128px;
	white-space:nowrap;
	text-align:right;
  	color: #d05627;
	color:black;
}


.ik {
  position: relative;
  top: 4px;
  border:0px;
}


.tagit_procesado b {
  background-color: #cfc!important;
}

.tagit_enespera  b {
  background-color: #cff!important;
}



tr.is_par td  {
  background-color: #f6f6f6!important;
  border: 0px;
}


tr.is_impar td {
 background-color: #eee!important;
 border: 0px;
zborder-left: 0px;
 zborder-right: 0px;
}

tr.seleccionado td * {
  color:black!important;
  font-weight: normal;
 background-color: #ccc;
 background-color: $caliente;
}


.solapasel {
 color: black;
 background-color: $bggeneral;
 padding-top: 0px;
 padding-right: 5px;
 padding-left: 5px;
 padding-bottom: 5px;
}

.cajaEnlacesSolapas {
 zbackground-color: #eee;
 background-color: #fee;
 zbackground-color: $caliente;
 zbackground-color: #eee;
 background-color: #eee;
 border-bottom: 1px solid gray;
 zbackground-color: #ffa500;
 zbackground-image: url(img/top.gif);
 background-repeat: no-repeat;

}

.amplioview {
  width: 99%;
  border: 0px;
  overflow: auto;
}


.filaDatos td {
  padding-top:2px;
  padding-bottom:2px;
}

.cajatitulo {
 overflow: hidden;
}


table.desolapa {
 height: 185px;
 vertical-align: center;
}


.ik {
  margin-right: 2px;
  padding-bottom: 1px;
}



/* tags */

span.tagit {
	zpadding:1px 5px;
	zoverflow:auto;
	zdisplay:inline;
}

span.tagit b {
	-moz-border-radius:5px 5px 5px 5px;
	zdisplay: block;
	zfloat: left;
	zmargin:2px 5px 2px 0;
}
span.tagit b.tagit-choice {
	background-color:#DEE7F8;
	border:1px solid #CAD8F3;
	padding:2px 4px 3px;
	padding:1px 2px 1px;

    position:relative;
    top: -2px;
	background-color:#DEE7F8;
	border:1px solid #CAD8F3;
	padding:2px 4px 3px;
	padding:1px 2px 1px;
	padding:0px 2px 0px;
}


.filaListado * {

 font-size: 9px;
 font-family: sans,verdana,sans-serif;
}


.dedato {
  font-family: sans,verdana,sans-serif;
  font-size: 11px;
}

EOT;




ob_end_flush();


