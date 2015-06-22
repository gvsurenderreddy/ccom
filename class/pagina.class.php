<?php

/**
 * Template de pagina
 *
 * @package ecomm-clases
 */

//include_once("class/patError.php");
include_once("class/patErrorManager.php");
include_once("class/patTemplate.php");


/*
 * Pagina
 *
 * Template de una pagina de ecomm
 * 
 */
class Pagina extends patTemplate {

	function IniciaTranslate(){
		global $lang;
		$pagina = 'cadenasdesistema.txt';

		$this->setOption( 'translationFolder', "translations" );
		$this->setOption( 'translationAutoCreate', true );
		$this->setOption( 'lang',  $lang );
		$this->addGlobalVar( 'page_encoding', "utf-8" );

		$this->setRoot( 'templates' );

		//NOTA: activa el cache
		if (0){
		$this->useTemplateCache( 'File', array(
                                            'cacheFolder' => './templates/cache',
                                            'lifetime'    => 60*60,
                                            'filemode'    => 0644
                                        )
                        );
		}

		$this->readTemplatesFromInput($pagina);
	}


	function Inicia($modname,$pagina=false){
		global $page,$lang;//?? porque esto en lugar de $this

		if (!$pagina)
			$pagina = 'basica.txt';

		$page->setOption( 'translationFolder', "translations" );
		$page->setOption( 'translationAutoCreate', true );
		$page->setOption( 'lang',  $lang );
		$page->addGlobalVar( 'page_encoding', "utf-8" );

		$page->setRoot( 'templates' );

		//NOTA: activa el cache
		if (0){
		$page->useTemplateCache( 'File', array(
                                            'cacheFolder' => './templates/cache',
                                            'lifetime'    => 60*60,
                                            'filemode'    => 0644
                                        )
                        );
		}


		$page->readTemplatesFromInput($pagina);
		$page->addVar('page', 'modname', $modname );

		$page->addVar("headers","versioncss",rand());
		$page->addVar("headers","modname",$modname);


		if ($pagina=="basica.txt" || $pagina=="central.txt"){
			//TODO: ugly excepcion made here!
			$page->addVar("cabeza","nombreusuario",getSesionDato("user_nombreapellido") );
			$page->addVar("cabeza","id_user",getSesionDato("id_user") );
		}
	}


	function _($text){	
		global $lang;
			
		//if ($lang=="es") $lang = "";		
		
		$folder = "translations";
		//$input = $this->_reader->getCurrentInput();		
		//$input = $this->_reader->_currentInput;						
		
		//$name = $folder . "/" . $input . "-".$lang.".ini";
		$name = "traducciones_" . $lang;
		
		$code = md5($text);
		
		$dato = $_SESSION[$name][$code];
		
		if ($dato)	{ //Nota, que no haya traduccion no es suficiente, puede que este ya en el fichero

			if (1){
				//TODO: desactivar esto en produccion
				$pagina = "templates" . "/" .'cadenasdesistema.txt';

				//Si no estaba, lo añadimos
				$template = file_get_contents($pagina);

				$existe = strstr($template,">".$text."<");

				if($template and !$existe){
					$template = str_replace("</patTemplate:tmpl>","Auto: <patTemplate:Translate>".$text."</patTemplate:Transl</patTemplate:tmpl>",$template);
					file_put_contents($pagina,$template);
				} else {
					if (!$template){
						$dir = getcwd();
						die("no puedo abrir ($pagina|$dir)");
					}
				}
			}
			
			return $dato;
		}
		
		return $text;
	}


	function configMenu($option){
		global $template;

		//TODO: esta funcion y su utilidad es compleja innecesariamente
		// ..es candidadata para una reescritura o re-enfocamiento


		if ($option!="check1" and $option!="check2"){
			$this->addVar('page', 'menu_0_txt', $this->_("Listar") );
			$this->addVar('page', 'menu_0_url', $template["modname"] . ".php" );
			$this->addVar('page', 'menu_1_url', $template["modname"] . ".php?modo=alta" );
			$this->addVar('page', 'menu_2_url', "#" );
			$this->addVar('page', 'menu_1_name', $this->_("Alta") );
			$this->addVar('page', 'menu_0_name', $this->_("Listar") );

		} else {
			$this->addVar('page', 'menu_0_txt', $this->_("Chequeo rapido") );
			$this->addVar('page', 'menu_0_url', $template["modname"] . ".php" );
			$this->addVar('page', 'menu_1_url', $template["modname"] . ".php?modo=profundo" );
			$this->addVar('page', 'menu_2_url', "#" );
			$this->addVar('page', 'menu_0_name', $this->_("Chequeo rapido") );
			$this->addVar('page', 'menu_1_name', $this->_("Chequeo profundo") );

		}

		
		$this->addVar("edicion", "cssbtnremove", "oculto");//lo quitamos de todos sitios

		switch($option){
				case "check2":
					$this->addVar('page', 'current1', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "check1":
					$this->addVar('page', 'current0', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );				
					break;

				case "sololistar":
					$this->addVar('page', 'current0',"current" );
					$this->addVar('page', 'menu_1_css', "oculto" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "listar":
					$this->addVar('page', 'current0', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "guardaralta":
					$this->addVar('page', 'current1', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );

					break;
				case "guardarcambios":
					$this->addVar('page', 'current2',"current" );
					break;
				default:
					$this->addVar('page', 'menu_2_css', "oculto" );
					

					break;
			}


	}




	function configNavegador( $min, $maxfilas,$numFilas ){
		global $template;

	//	if (!$numFilas) return;

		$siguienteDisabled = "";
		$anteriorDisabled = "";
		
		$numActivos = 0;
		$pagSiguiente = 0;
		$pagAnterior = 0;

		if ( $min >= $maxfilas ) {
			$pagAnterior = $min - $maxfilas;
			$numActivos++;
		}  else {
			$anteriorDisabled = "disabled='disabled'";
		}

		if  ($numFilas < $maxfilas) {
			$pagSiguiente = $min;
			$siguienteDisabled = "disabled='disabled'";
		} else {
			$numActivos++;
			$pagSiguiente = $min + $maxfilas;
		}


		if ( 0){
			if (!$numActivos) {
				//echo "SAle, porque no hay botones que activar";
				return;//no hay botones activos, asi que ocultamos el navegador, que no es necesario.
			}

			$this->setAttribute( 'navegador', 'src', 'navegador.txt' );


			$this->addVar( 'navegador', 'modname', $template["modname"] );

			$this->addVar( 'navegador', 'paganterior', $pagAnterior );
			$this->addVar( 'navegador', 'pagsiguiente', $pagSiguiente );

			$this->addVar( 'navegador', 'antdisabledhtml', $anteriorDisabled );
			$this->addVar( 'navegador', 'sigdisabledhtml', $siguienteDisabled );
		}else{
			$this->addVar( 'mininavegador', 'paganterior', $pagAnterior );
			$this->addVar( 'mininavegador', 'pagsiguiente', $pagSiguiente );

			if ($min<=0){
				$this->addVar( 'mininavegador', 'firstdisabledhtml', 'imagebotondesactivado');
			}

			if ($siguienteDisabled){
				$this->addVar( 'mininavegador', 'lastdisabledhtml', 'imagebotondesactivado');
			}

			if ($anteriorDisabled){
				$this->addVar( 'mininavegador', 'antdisabledhtml', 'imagebotondesactivado');
			}
			if ($siguienteDisabled){
				$this->addVar( 'mininavegador', 'sigdisabledhtml', 'imagebotondesactivado');
			}
		}
	}

	function Volcar(){
		$this->displayParsedTemplate();
	}



	function addArrayFromCursor( $subtemplate,&$cursor, $multiple ){

		if (!$multiple) return;

		if (!$cursor) return;//TODO: emitir un error

		foreach($multiple as $key){
			$this->addVar( $subtemplate, $key, $cursor->get($key)  );
		}

	}

	//TODO: mover esto a su propia clase, pues no se utiliza ampliamente, y es demasiado especifico

	function getIcon($gifname){
		return "<img src='icons/".$gifname."' class='icon'  align='absmiddle'  />";
	}

	function getIconOk(){
		return $this->getIcon("ok1.gif");
	}

	function getIconError(){
		return $this->getIcon("error.png");
	}

	function getIconResult($result){

		if ($result)
			return $this->getIconOk();

		return $this->getIconError();
	}

}


?>
