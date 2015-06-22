


function ConmutarSolapa(id_comm){
	//if ( AmpliarSolapasDeComm.ultimaAmpliada != id_comm)
	//		CerrarSolapa(AmpliarSolapasDeComm.ultimaAmpliada);

	if ( AmpliarSolapasDeComm.ultimaAmpliada == id_comm) {
		//la proxima vez que se pulse, se reabrira esta
		AmpliarSolapasDeComm.ultimaAmpliada = false;

		//La solapa abierta es la actual, la cerramos.
		CerrarSolapa(id_comm);
		return;
	}

	AmpliarSolapasDeComm(id_comm);
}


function checkUnlogin(obj){
	if (obj["ok"])
		return false;

	if (obj["logout"]){		
		GB_show("Login","login.php?modo=popup",470,600);
		return true;
	}
	return false;
}



var htmlCargando = "<center style='height: 185px;font-weight:bold'><img style='margin-top:80px' zalign='center' src='img/ajaxian.gif'> Cargando...</center>" ;

function AmpliarSolapasDeComm(id_comm){

	var enlace = "#a_" +id_comm;

	if ($(enlace).length){
		$(enlace).removeClass("sinleer");
		$(enlace).addClass("leido");
	}

	var name = "#contenedor_" + id_comm;

	if ( $(name).length  ){
		$(name).html( htmlCargando );
		//$(name).html( "<center style='height: 185px;font-weight:bold'><img style='margin-top:80px' zalign='center' src='http://opengraphicdesign.com/wp-content/uploads/2009/01/bar180.gif'> Cargando...</center>" );
		//

		var nombre2 = "#titulo_" + id_comm ;
		var nombre3 = "#datos_" + id_comm ;

		$(nombre2).addClass("seleccionado");
		$(nombre3).addClass("seleccionado_datos");


		$.ajax({
				type: "POST",
				url: "ajax.php",
				data: "modo=cargaSolapa&id_comm="+id_comm,
				success: function(datos){
						try {
							//alert(datos);
							var obj = eval("(" + datos + ")");
							if(checkUnlogin(obj)) return;

							if (obj.ok) {
								var nameent = "#contenedor_" + obj.id_comm;
								$(nameent).html("<div class='solapascontenedor'>"+ obj.html+"</div>" );

								if ( AmpliarSolapasDeComm.ultimaAmpliada != obj.id_comm)
									CerrarSolapa(AmpliarSolapasDeComm.ultimaAmpliada);
								AmpliarSolapasDeComm.ultimaAmpliada = obj.id_comm;
							}
						}catch(e){
							alert("ERROR: " + e);
						}
				  }
		});

	}
}

AmpliarSolapasDeComm.ultimaAmpliada = 0;


function CerrarSolapa(id_comm){
	if (!id_comm) return;

	var nombre = "#contenedor_" + id_comm;
	var nombre2 = "#titulo_" + id_comm;
	var nombre3 = "#datos_" + id_comm;

	if ( $(nombre).length  ){
		$(nombre).html( "" );
		$(nombre2).removeClass("seleccionado");
		$(nombre3).removeClass("seleccionado_datos");
	}
}


function marcaSolapaAbierta( modo){
	var nameClaseSolapasel ="solapasel";
	$("#es_"+modo).addClass( nameClaseSolapasel );
}

function RecargaSolapaModo(id_comm,modo){
	var name = "#contenedor2_" + id_comm;


//<a href="#solapa_<patTemplate:var name="id_comm" />"
//  onclick="return RecargaSolapaModo(<patTemplate:var name="id_comm" />,'datos'  )">Datos</a> |


	var nameClaseSolapasel ="solapasel";
	$("#es_datos").removeClass(nameClaseSolapasel);
	$("#es_documento").removeClass(nameClaseSolapasel);
	$("#es_traza").removeClass(nameClaseSolapasel);
	$("#es_etiquetas").removeClass(nameClaseSolapasel);
	$("#es_riesgo").removeClass(nameClaseSolapasel);
	$("#es_reenviar").removeClass(nameClaseSolapasel);


	$("#es_"+modo).addClass( nameClaseSolapasel );

	

	if ( $(name).length  ){

		var nombre2 = "#titulo_" + id_comm ;
		var nombre3 = "#datos_" + id_comm ;

		$(nombre2).addClass("seleccionado");
		$(nombre3).addClass("seleccionado_datos");

		$(name).html( htmlCargando );

		$.ajax({
				type: "POST",
				url: "ajax.php",
				data: "modo=cargaSubSolapa&id_comm="+id_comm+"&submodo="+modo,
				success: function(datos){
						try {
//							alert(datos);

							var obj = eval("(" + datos + ")");
							if(checkUnlogin(obj)) return;
							
							if (obj.ok) {
								var nombre = "#contenedor2_" + obj.id_comm ;
								$(nombre).html( obj.html );


								//alert(obj.html);
							}
						}catch(e){
							alert("ERROR: " + e+ ", code:"+datos);
						}
				  }
		});

	}
	
	return false;
}


function Revelador(){

	var t = Revelador.altoindex;

	var namepossible = "#titulo_" + t;
	var namepossible2 = "#datos_" + t;

	//console.log("Revelador:,t:"+t+",namepossible:"+namepossible+",altoindex:"+Revelador.altoindex+",bajoindex:"+Revelador.bajoindex);

	if ( $(namepossible).length ){
		$(namepossible).show();//debe ser simplemente show, porque si utilizamos algo mas espectacular, se estropea el css
		$(namepossible2).show();
	}

	t--;

	if (t<Revelador.bajoindex){
		//hemos superado el indice alto
		Revelador.activo = false;//no necesita correr mas
	}

	Revelador.altoindex = t;//por tanto, bajoindex apuntara al siguiente elemento a mostrar

	if (Revelador.activo){
		setTimeout("Revelador()",10);
	}
}

Revelador.empezar = function (desde, hasta){

		//console.log("empieza:,desde:"+desde+",hasta:"+hasta);

		//que esta activo y debe correr
		Revelador.activo = true;

		//ajusta margenes		
		Revelador.bajoindex = desde;

		if (!Revelador.altoindex)
			Revelador.altoindex = hasta;

		setTimeout("Revelador()",200);//empieza
}


function MasLineas(){



		$.ajax({
				type: "POST",
				url: "modcentral.php",
				data: "modo=cargarMasLineas&last_id_comm=" + MasLineas.last_id_comm+"&offset="+(MasLineas.offset),
				success: function(datos){
						try {

							var obj = eval("(" + datos + ")");
							if(checkUnlogin(obj)) return;

							if (obj.ok) {

								oldidcom = MasLineas.last_id_comm;
								newidcom = obj.last_id_comm
								MasLineas.last_id_comm = newidcom;
								
								$("#lineas_de_comm").append( obj.html );

								if (obj.ordenlogico){
									for(var t=newidcom;t<oldidcom;t++){
										var namepossible = "#titulo_" + t;
										var namepossible2 = "#datos_" + t;

										if ( $(namepossible).length ){
											$(namepossible).hide();
											$(namepossible2).hide();
										}
									}

									Revelador.empezar(newidcom,oldidcom);
								} else {
									MasLineas.offset += parseInt(obj.paginasize,10);
								}

							}
						}catch(e){
							alert("ERROR: " + e+ ", code:"+datos);
						}
				  }
		});
	return false;
}

//testio
