function fn_sale_del_programa()
{
    var ruta_salida="<?php echo '../logout.php';?>";
    
    window.open(ruta_salida,'_top','');
}

function cancelar() {
    location.href="index.php";
}

//2017-06-09 difema: la idea con esta funcion es enviarle un echo al servidor cada 5 minutos para que no 
//se pierda la conexion

//1 minuto = 60000 milisegundos, 5 minutos = 300.000
function inicio() {
    $.ajax({
        url: "funciones.php",
        dataType:'text',
        type: 'post',
        data:{ paso: 'evalua_conexion'}, 
        success: function(data) {
            console.log(data);
        }
    })
}
window.setInterval("inicio()",300000);
//FIN 2017-06-09 difema


$(document).ready(function(){

    $(document).on('input', '.numeric-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

	$('#cedula').mask('00000000000');

    $('#num_sor').mask('0000');

    var date = new Date();
    var currentMonth = date.getMonth();
    var currentDate = '01';//date.getDate();
    var currentYear = date.getFullYear();

	$(document).on("click", "#genera_mensajes", function (e) {
		e.preventDefault();	
		$.ajax({
			url: "lottired_evolution_api_support.php", // URL del backend
			type: "POST", // Método de la solicitud
			dataType: "json", // Se espera una respuesta en formato JSON
			success: function (response) {
				console.log("Respuesta del servidor:", response);
				if (response.success) {
					console.log("Operación realizada correctamente.");
					// Aquí puedes agregar código para actualizar la tabla o mostrar un mensaje de éxito
				} else {
					console.error("Error en la operación:", response.message);
					// Aquí puedes agregar código para mostrar un mensaje de error al usuario
				}
			},
			error: function (xhr, status, error) {
				console.error("Error en la llamada AJAX:", error);
				// Aquí puedes agregar código para manejar errores de la solicitud
			}
		});
	});

	/*
	$(document).on("click", "#genera_mensajes123", function(e) {	
		e.preventDefault();	

		console.log('aqui voy');
		///librerias/funciones/lottired_evolution_api_support.php 
			//url: "<?php echo $lottired_evolution_api_support; ?>", // Usar la nueva variable

		$.ajax({
			url: "lottired_evolution_api_support.php", // Usar la nueva variable
			type: "POST",
			dataType: "json", // Se espera una respuesta en formato JSON
			success: function(response) {
				console.log("Respuesta del servidor:", response);
				if (response.success) {
					console.log("Imagen enviada a lottired_evolution_api.php correctamente.");
					// Aquí puedes agregar código para mostrar un mensaje de éxito al usuario
				} else {
					console.error("Error al enviar la imagen a lottired_evolution_api.php:", response.message);
					// Aquí puedes agregar código para mostrar un mensaje de error al usuario
				}
			},
			error: function(xhr, status, error) {
				console.error("Error en la llamada AJAX:", error);
				// Aquí puedes agregar código para mostrar un mensaje de error al usuario
			}
		});	

	});
	*/

    $('#rango_fechas_con').datepick({
        minDate: new Date(currentYear, currentMonth-12, currentDate),
        maxDate: new Date(currentYear, currentMonth+1, currentDate),
        rangeSelect: true, 
        monthsToShow: 1
    });

    $('#rango_fechas_sorteo').datepick({
        minDate: new Date(currentYear, currentMonth-12, currentDate),
        maxDate: new Date(currentYear, currentMonth+1, currentDate),
        rangeSelect: true, 
        monthsToShow: 1
    });   

    $('#rango_fechas_val_pre').datepick({
        minDate: new Date(currentYear, currentMonth-12, currentDate),
        maxDate: new Date(currentYear, currentMonth+1, currentDate),
        rangeSelect: true, 
        monthsToShow: 1
    }); 	

    $('[data-toggle="tooltip"]').tooltip();     

    $('#cboPtoventa_con').click(function(){	
        
        var cboPto  = $(this).attr("name");
        var cod_pto = $(this).val();
        var lista  = $(this).parent().attr('id');

        fn_ptos_vta(cboPto,cod_pto,lista);
    });	    

    function fn_ptos_vta(cboPto,cod_pto,lista) {
					
        $.ajax({
            async:	false, 
            url:	"funciones.php",
            dataType:"json",
            type	: 'post',
            data:  { paso: 'cboPtoventa', cboPto:cboPto, cod_pto:cod_pto},
            success: function(data){
                $("#"+lista).html(data.salida);	
            },	
            error: function (request, status, error) 
            {
                alert(request.responseText);
            }
        });	
    }	

    $('#cboLoterias').click(function(){		
        $.ajax({
            async:	false, 
            url: "funciones.php",
            dataType:"json",
            data:  { paso: 'cboLoterias'},
            success: function(data){
                $("#list_loterias").html(data.salida);	
            },	
            error: function (request, status, error) 
            {
                alert(request.responseText);
            }
        });			
    });


    $("#consulta_con").click(function(e) {
        fn_consulta_ppal(e); 
        e.preventDefault();	
    });

	$('#rep_excell').click(function(e){ 
		swal('SE GENERO EL ARCHIVO EN LA CARPETA DE DESCARGAS !!', "", "success");
		e.preventDefault();
	});	

    function fn_consulta_ppal(e){
 		var cboPtoventa			 =	$('#cboPtoventa_con').val();
		var cboLoterias			 =	$('#cboLoterias').val();
		var rango_fechas 		 =	$('#rango_fechas_con').val();	
		var rango_fechas_sorteo  =	$('#rango_fechas_sorteo').val();	
		var rango_fechas_val_pre =	$('#rango_fechas_val_pre').val();	
		var cedula       		 =	$('#cedula').val();	
		var num_sor       		 =	$('#num_sor').val();	

        //if ((cboPtoventa != '0') && (rango_fechas.length > 0) || cedula.length > 0)
		if (((cboPtoventa != '0' && rango_fechas.length > 0) || cedula.length > 0) || (rango_fechas_sorteo.length > 0) || (num_sor.length > 0) || (rango_fechas_val_pre.length > 0))
        {
            var ajax_data = {
                "paso"  	  		  : 'consulta_ppal',
                "cboPtoventa" 		  : cboPtoventa,
                "cboLoterias" 		  : cboLoterias,
                "rango_fechas"		  : rango_fechas,
                "rango_fechas_sorteo" : rango_fechas_sorteo,
                "rango_fechas_val_pre": rango_fechas_val_pre,
				"num_sor" 	  		  : num_sor,
                "cedula"      		  : cedula,
            }            

			$.ajax({
				//async:	false, 				
				url 		: "funciones.php", // the url where we want to POST
				type		: 'post',				
				dataType 	: "json", 				
				data 		: ajax_data, // our data object
				beforeSend: function(){
					$("#spinner").show();
				},							
				success: function(jsonData) 
				{
					var error = jsonData.error;
					
					if (error.length > 0)
					{
						swal(error, "", "error");	
					}
					else
					{
						//$("#spinner").hide();
						
                        $("#div_productos").html(jsonData.productos);
						$("#div_detalle").html(jsonData.detalle);
						$("#div_totales").html(jsonData.totales);
                        //$("#hd_action").val(jsonData.action);

                        // Aplicar TableSorter después de agregar el contenido de la tabla
                        $("#tbl_productos").tablesorter({
                            theme: 'blue',
                            widgets: ["zebra", "filter"],
                        }).tablesorterPager({
                            container: $(".pager")
                        });

                        var $table = $("#tbl_productos").tablesorter({sortList: [[0,0]]})

                        var resort = true,
                        callback = function(){ console.log('table updated'); };
                        $table.trigger("update", [ resort, callback ]);                        
    
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					// Es buena idea manejar errores también
					console.error("Error en AJAX:", textStatus, errorThrown);
					swal("Error", "Ocurrió un problema al procesar la solicitud.", "error");
				},
				complete: function() {
					// Oculta el spinner aquí para asegurarte de que se oculte
					// tanto si la petición fue exitosa como si falló.
					$("#spinner").hide();
				}				
				
			});	
        }
    }

	$(document).on("click", ".consulta_vta", function(e) {				
		$('li a[href="#consulta_det"]').trigger('click'); // Cambiamos de tab

		// Obtenemos la fila asociada al botón que se ha clickeado
		var $row = $(this).closest("tr");

		// Asignamos los valores a los campos del formulario
		$("#cd_pto_venta").val($row.find(".cls_pto_venta").text());
		$("#cd_id_venta").val($row.find(".cls_id_venta").text());
		$("#cd_fec_venta").val($row.find(".cls_fec_venta").text());
		$("#cd_cliente").val($row.find(".cls_cliente").text());

		// Obtener valor de cédula y agregar prefijo "No. Cédula"
		var cedula = $row.find(".cls_cedula").text();
		// Formatear el número de cédula con comas
		var formattedCedula = "No. Cédula: " + parseInt(cedula).toLocaleString();

		// Asignar el tooltip con formato de cédula
		$("#cd_cliente").attr("data-toggle", "tooltip")
			   .attr("data-original-title", formattedCedula)
			   .tooltip("show"); // Muestra el tooltip inmediatamente        

		var id_venta = $row.find(".cls_id_venta").text();

		// Filtrar las tablas de detalles
		var $tblVentasDetalle = $("#tbl_ventas_detalle tbody");
		var $tblPremiosDetalle = $("#tbl_premios_detalle tbody");

		// Limpiamos las tablas de detalles
		$tblVentasDetalle.empty();
		$tblPremiosDetalle.empty();

		var totalVlrFracciones = 0;
		var totalVlrPremio = 0;

		// Filtramos la tabla principal tbl_productos
		$('#tbl_productos tbody tr').each(function() {
			var rowIdVenta = $(this).find('.cls_id_venta').text();
			var rowIdVenta_premio = $(this).find('.cls_id_venta_premio').text();

			// Evaluamos si rowIdVenta o rowIdVenta_premio coinciden con id_venta
			if (rowIdVenta == id_venta || rowIdVenta_premio == id_venta) {
				var tipo = $(this).find(".cls_tipo").text(); // Usamos $(this) en vez de $row

				// Si hay un id_venta_premio distinto de '0'
				if (rowIdVenta_premio !== '0') {
					// Nueva rutina para buscar los registros con ese id_venta_premio
					$('#tbl_productos tbody tr').each(function() {
						var premioId = $(this).find('.cls_id_venta_premio').text();

						if (premioId == rowIdVenta_premio) {
							// Procesamos los registros de premio asociados
							agregarRegistroPremio($(this), $tblPremiosDetalle);
						}
					});
				}

				// Si no es premio, procesamos la venta
				if (tipo == "VENTA123") {
					var vlr_fracciones = parseFloat($(this).find(".cls_vlr_fracciones").text()) || 0;
					totalVlrFracciones += vlr_fracciones;

					//let botonClase = ($(this).find(".cls_valida_premio").text() == "S") ? "btn btn-danger" : "btn btn-success";
					let botonClase = ($(this).find(".cls_valida_premio").text() == "S") ? "btn btn-danger" : "btn btn-success";
					let botonIcon = ($(this).find(".cls_valida_premio").text() == "S") 
						? '<i class="fa fa-times"></i>'  // X
						: '<i class="fa fa-check"></i>';   // Visto bueno					

					$tblVentasDetalle.append(
						"<tr>" +
							"<td>" + $(this).find(".cls_loteria").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_sor").text() + "</td>" +
							"<td>" + $(this).find(".cls_fec_sor").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_bil").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_ser").text() + "</td>" +
							"<td>" + $(this).find(".cls_fracciones").text() + "</td>" +
							"<td>" + $(this).find(".cls_barcode").text() + "</td>" +
							"<td>" + vlr_fracciones.toLocaleString() + "</td>" +
							"<td>" + 
								'<button type="button" class="' + botonClase + '">' + botonIcon + '</button>' +
							"</td>" +
						"</tr>"
					);
				}

				if (tipo == "VENTA") {
					var vlr_fracciones = parseFloat($(this).find(".cls_vlr_fracciones").text()) || 0;
					totalVlrFracciones += vlr_fracciones;

					let validaPremio = $(this).find(".cls_valida_premio").text() == "S";
					let botonClase = validaPremio ? "btn btn-danger" : "btn btn-success";
					let botonIcon = validaPremio 
						? '<i class="fa fa-times"></i>'  // X
						: '<i class="fa fa-check"></i>';   // Visto bueno
					let tooltipTexto = validaPremio 
						? "PREMIO YA VALIDADO, HAGA CLICK PARA REVALIDAR DE NUEVO" 
						: "PENDIENTE POR VALIDAR PREMIO";

					// Obtener el ID directamente del barcode para usarlo en el botón
					let barcodeValue = $(this).find(".cls_barcode").text();

					$tblVentasDetalle.append(
						"<tr>" +
							"<td>" + $(this).find(".cls_loteria").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_sor").text() + "</td>" +
							"<td>" + $(this).find(".cls_fec_sor").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_bil").text() + "</td>" +
							"<td>" + $(this).find(".cls_num_ser").text() + "</td>" +
							"<td>" + $(this).find(".cls_fracciones").text() + "</td>" +
							"<td class='cls_barcode'>" + barcodeValue + "</td>" +
							"<td>" + vlr_fracciones.toLocaleString() + "</td>" +
							"<td>" + 
								'<button type="button" class="' + botonClase + ' custom-tooltip cls_revalida_premio" ' +
									'data-barcode="' + barcodeValue + '" ' +  // Guardar el ID en el botón
									'data-toggle="tooltip" data-placement="top" title="' + tooltipTexto + '">' +
									botonIcon +
								'</button>' +
							"</td>" +
						"</tr>"
					);

					// Inicializar solo los tooltips con la clase personalizada
					$('.custom-tooltip').tooltip({
						template: 
							'<div class="tooltip custom-tooltip-inner" role="tooltip">' +
								'<div class="tooltip-arrow"></div>' +
								'<div class="tooltip-inner"></div>' +
							'</div>'
					});
					
					// Evento click para el botón rojo
					$(document).on('click', '.cls_revalida_premio', function() {
						
						// Validar si el botón tiene la clase btn-danger
						if ($(this).hasClass('btn-danger')) {						
						
							var registroId = $(this).closest('tr').find(".cls_barcode").text(); // Ejemplo: obtener un identificador
							
							// Obtener el ID desde el atributo data-barcode
							var registroId = $(this).data('barcode');						
							
							swal({
								title: "ACTIVA PARA VALIDAR NUEVAMENTE LA REVISION DE PREMIO?",
								text: "",
								icon: "error",
								buttons: [
									'NO',
									'SI',
								],							
								//buttons: true,
								type: "warning",
								showCancelButton: true,							
								dangerMode: true,
							})
							.then((isConfirm) => {
								if (isConfirm) {
									var ajax_data = {
										"paso"    : 'revalidar_premio',							
										"barcode" : registroId,
									}		

									$.ajax({
										async:	false, 				
										url 		: "funciones.php", // the url where we want to POST
										type		: 'post',				
										dataType 	: "json", 				
										data 		: ajax_data, // our data object
										success: function(data)
										{
											var error = data.error;
											var msg   = data.msg; 
											
											if (error.length > 0)
											{
												swal(error, "", "error");
											}
											else
											{
												swal(msg, "success");
											}
										},	
										error: function (request, status, error) 
										{
											alert(request.responseText);
										}													
										
									});		
								}
								else
								{
									swal('EL REGISTRO NO FUE ACTUALIZADO', "", "error");	
								}
							});	
						}	
						else
						{
							swal('LA VALIDACION DE PREMIOS UNICAMENTE SE PUEDE REALIZAR POR EL MODULO DE VENTAS', "", "error");
						}
					});					

				}
				
			}
		});

		// Al final, agregamos los totales
		$tblVentasDetalle.append(
			"<tr>" +
				"<td colspan='7'><strong>Total:</strong></td>" +
				"<td>" + totalVlrFracciones.toLocaleString() + "</td>" +
			"</tr>"
		);
		$tblPremiosDetalle.append(
			"<tr>" +
				"<td colspan='9'><strong>Total:</strong></td>" +
				"<td>" + totalVlrPremio.toLocaleString() + "</td>" +
			"</tr>"
		);

		// Función auxiliar para agregar registros de premios
		function agregarRegistroPremio($rowPremio, $tblPremiosDetalle) {
			var vlr_premio = parseFloat($rowPremio.find(".cls_vlr_premio").text()) || 0;
			totalVlrPremio += vlr_premio;

			$tblPremiosDetalle.append(
				"<tr title='" + $rowPremio.find(".cls_nom_pre").text() + "'>" +
					"<td>" + $rowPremio.find(".cls_id_venta").text() + "</td>" +              
					"<td>" + $rowPremio.find(".cls_fec_venta_corta").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_loteria").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_num_sor").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_fec_sor").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_num_bil").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_num_ser").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_barcode").text() + "</td>" +
					"<td>" + $rowPremio.find(".cls_fracciones").text() + "</td>" +
					"<td>" + vlr_premio.toLocaleString() + "</td>" +
				"</tr>"
			);
		}
	});

	$(document).on("click", ".cls_validar_premio", function(e) {
		
		// Prevenir el comportamiento por defecto si es necesario (ej. si fuera un enlace)
		e.preventDefault();
	
		var barcode = $(this).data('id'); // Usar .data('id') es una forma común en jQuery	
	
	    // Asegurarse de que barcode no esté vacío antes de enviar
		if (!barcode) {
			swal("Error", "No se pudo obtener el código de barras.", "error");
			return; // Detener la ejecución si no hay barcode
		}

		var ajax_data = {
			"paso": 'validar_premio_en_lottired',
			"barcode": barcode, // Usar la variable barcode obtenida
		};            

		$.ajax({
			async: false, // Considera cambiar a true si es posible
			// Asegúrate que la URL apunte al archivo PHP correcto (funciones_lottired.php según el contexto anterior)
			url: "funciones.php",
			type: 'post',
			dataType: "json",
			data: ajax_data,
			beforeSend: function() {
				// $("#spinner").show();
				console.log("Solicitando autorización para barcode:", barcode);
			},

			success: function(jsonData) {
				// $("#spinner").hide(); // Ocultar indicador de carga si lo usas

				// 1. Verificar si la respuesta (jsonData) existe
				if (!jsonData) {
					 swal("Error", "No se recibió respuesta del servidor.", "error");
					 console.error("Respuesta AJAX vacía o inválida.");
					 return;
				}

				// 2. Verificar si hubo un error reportado por PHP
				if (jsonData.error && jsonData.error.length > 0) {
					// Hubo un error durante la generación de autorización o la validación del premio
					swal("Error en Validación", jsonData.error, "error");
					// Opcional: Mostrar el objeto completo para depuración si hay error
					console.log("Respuesta con error:", jsonData);
				}
				// 3. Si no hubo error Y se recibieron los datos del premio (el arreglo)
				//    Verificamos que 'datos_premio' exista y no sea null o vacío si es necesario
				else if (jsonData.datos_premio !== undefined && jsonData.datos_premio !== null) {
					// Éxito: Se obtuvieron los datos del premio
					var datosPremioRecibidos = jsonData.datos_premio; // Este es tu arreglo '$arre'
					console.log("Datos del premio recibidos:", datosPremioRecibidos);

					// Mostrar un mensaje de éxito o procesar los datos del premio
					// Ejemplo: Mostrar un mensaje simple
					//////////////swal("Validación Exitosa", "Se recibieron los datos del premio.", "success");

					// Ejemplo: Acceder a un valor específico del arreglo (si conoces su estructura)
					// if (datosPremioRecibidos.algun_campo) {
					//     console.log("Valor específico:", datosPremioRecibidos.algun_campo);
					//     // Hacer algo con este valor...
					// }
					$("#div_validacion_premios").html(jsonData.salida);
					$("#modal_validacion_premio").modal('show'); 
					// Aquí puedes implementar la lógica para usar 'datosPremioRecibidos'
					// como actualizar la interfaz, mostrar detalles del premio, etc.

				} else {
					// Caso inesperado: No hubo error explícito, pero 'datos_premio' no vino como se esperaba
					swal("Respuesta Incompleta", "Se procesó la solicitud, pero no se recibieron los datos del premio esperados.", "warning");
					console.log("Respuesta recibida (inesperada):", jsonData);
				}
			},
			error: function(request, status, error) {
				// $("#spinner").hide(); // Ocultar indicador de carga
				console.error("Error en AJAX:", request.responseText, status, error);
				swal("Error de Comunicación", "No se pudo conectar con el servidor para validar el premio. Detalles: " + status, "error");
			}
		});

	});

    $('#cd_nva_cedula').mask('00000000000');

    $("#cd_nva_cedula").keypress(function(event){
        if ((event.keyCode == 13) || (event.keyCode == 9)) 
        {
            var cedulaValue = $("#cd_nva_cedula").val(); // Obtiene el valor del campo
            if (cedulaValue.length > 0) {

                $("#modal_datos_cliente").modal('show');

                $('#mto_cedula').val(cedulaValue);
                fn_consulta_cliente()
                $('#mto_nombres').focus();
                event.preventDefault();	
            }    
        }					
    });

    $("#formulario_mtto :input").attr('autocomplete', 'off');	
				
    $('#formulario_mtto :input[type=text]').css({'margin-left':'0px','font-size':'1.4em','height':'30pt'});				
    
    $('#mto_telefono').mask('000-000-0000', {placeholder: '000-000-0000'});				
    $('#mto_cedula').mask('00000000000');

    $('#mto_cedula').blur(function(){
        fn_consulta_cliente()
        event.preventDefault();												
    });		

    $("#mto_cedula").keypress(function(event){
        if ((event.keyCode == 13) || (event.keyCode == 9)) 
        {
            fn_consulta_cliente()
            event.preventDefault();	
            $('#mto_nombres').focus();
        }					
    });

    $('#mto_direccion').keypress(function(e){	
        if ((e.keyCode == 13) || (e.keyCode == 9)) { //si da enter o tab	
            fn_graba_cliente();						
        }
    });

    $('#mto_correo').keypress(function(e){	
        if ((e.keyCode == 13) || (e.keyCode == 9)) { //si da enter o tab	
            fn_graba_cliente();						
        }
    });				
    
    $("#mto_nombres").keypress(function(event){
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if(!(inputValue >= 65 && inputValue <= 123) && (inputValue != 32 && inputValue != 0)) { 
            event.preventDefault(); 
        }
        
        if ((event.keyCode == 13) || (event.keyCode == 9)) 
        {
            $('#mto_apellidos').focus();	
        }
    });				
    
    $("#mto_apellidos").keypress(function(event){
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if(!(inputValue >= 65 && inputValue <= 123) && (inputValue != 32 && inputValue != 0)) { 
            event.preventDefault(); 
        }
        
        if ((event.keyCode == 13) || (event.keyCode == 9)) 
        {
            $('#mto_telefono').focus();	
        }					
    });	

    $("#mto_telefono").keypress(function(event){
        if ((event.keyCode == 13) || (event.keyCode == 9)) 
        {
            $('#mto_correo').focus(); //solicita para el sorteo de extras en diciembre
        }					
    });					

    function fn_consulta_cliente(){	
				
        var cedula    = $('#mto_cedula').val();		
        
        if (!cedula) {
            //debe tener un valor, sino no hace nada
        }
        else
        {
            var ajax_data = {
                    "paso"      : 'consultar',							
                    "cedula"    : cedula,
                }

            $.ajax({
                async:	false, 				
                url 		: '../clientes/funciones.php', // the url where we want to POST
                type		: 'post',				
                dataType 	: "json", 				
                data:  ajax_data,					
                success: function(data) 
                {
                    error = data.error;
                    
                    if (error.length > 0)
                    {
                        swal(error, "", "error");									
                    }
                    else
                    {
                        var arreglo = data.arreglo;	

                        $.each(arreglo, function(j,data2){									

                            $('#mto_nombres').val(data2.nombres);	
                            $('#mto_apellidos').val(data2.apellidos);
                            $('#mto_telefono').val(data2.celular);
                            $('#mto_direccion').val(data2.direccion);
                            $('#mto_correo').val(data2.correo);

                        });									
                    }
                },
                error: function (request, status, error) 
                {
                    alert(request.responseText);
                }					
                
            });	
        }
    }	
    
    $('#grabar_cliente').click(function(e) {				
        e.preventDefault();		
        fn_graba_cliente();
    });	

    $('#btn_cambio_cliente').click(function(e) {

        var cd_id_venta   = $("#cd_id_venta").val();
        var cd_nva_cedula = $("#cd_nva_cedula").val();
        var cd_nvo_nombre = $("#cd_nvo_nombre").val();

        if (cd_nvo_nombre.length > 0){

            var ajax_data = {
                "paso"          : 'actualizar_venta',							
                "cd_id_venta"   : cd_id_venta,
                "cd_nva_cedula" : cd_nva_cedula,
            }

            $.ajax({
                async:	false, 				
                url 		: 'funciones.php', // the url where we want to POST
                type		: 'post',				
                dataType 	: "json", 				
                data:  ajax_data,					
                success: function(data) 
                {
                    error = data.error;
                    msg   = data.msg; 
                    
                    if (error.length > 0)
                    {
                        swal(error, "", "error");									
                    }
                    else
                    {
                        swal({
                            title: msg,
                            text: "",
                            icon: "success",
                            type: "warning",
                            timer: 2000
                        }).then(function () {
                            $('#formulario_mtto').each (function(){
                                this.reset();
                            });	

                            // Reemplazar el valor de cd_cliente por el valor de cd_nvo_nombre
                            $('#cd_cliente').val($('#cd_nvo_nombre').val());    
                            
                            // Cambiar el color del campo cd_cliente a verde
                            $('#cd_cliente').css('background-color', 'lightgreen');   
                            
                            // Vaciar los campos cd_nva_cedula y cd_nvo_nombre
                            $('#cd_nva_cedula').val(''); // Vaciar el campo Cedula
                            $('#cd_nvo_nombre').val(''); // Vaciar el campo Nombre    
                            
                            $('#consulta_con').trigger('click');

                        });									
                    }
                },
                error: function (request, status, error) 
                {
                    alert(request.responseText);
                }					
                
            });	

        }

    });

    function fn_graba_cliente(){				
				
        var sw = 0;
        //$('#formulario :input:visible[required="required"]').each(function()
        
        var cedula    = $('#mto_cedula').val();	

        $('#formulario_mtto input[required]:visible,textarea[required]:visible').each(function()
        {
            if(!this.validity.valid)
            {
                $(this).focus();
                sw = 1;
                // break
                return false;
            }
        });
        
        if (cedula.length < 5){
            $('#mto_cedula').focus();	
            sw = 1;	
        }
        
        if (sw ==0)
        {
            //var cedula    = $('#mto_cedula').val();	
            var nombres   = $('#mto_nombres').val();	
            var apellidos = $('#mto_apellidos').val();	
            var celular   = $('#mto_telefono').val();							
            var direccion = $('#mto_direccion').val();
            var correo    = $('#mto_correo').val();
            
            var ajax_data = {
                "paso"      : 'grabar',							
                "cedula"    : cedula,
                "nombres"   : nombres,
                "apellidos" : apellidos,
                "celular"   : celular,
                "direccion" : direccion,							
                "correo"    : correo,							
            }

            $.ajax({
                async:	false, 				
                url 		: '../clientes/funciones.php', // the url where we want to POST
                type		: 'post',				
                dataType 	: "json", 				
                data:  ajax_data,					
                success: function(data) 
                {
                    error = data.error;
                    msg   = data.msg; 
                    
                    if (error.length > 0)
                    {
                        swal(error, "", "error");									
                    }
                    else
                    {
                        swal({
                            title: msg,
                            text: "",
                            icon: "success",
                            type: "warning",
                            timer: 2000
                        }).then(function () {
                            $('#formulario_mtto').each (function(){
                                this.reset();
                            });	
                            $('#modal_datos_cliente').modal('hide');

                            $('#cd_nvo_nombre').val(nombres+' '+apellidos);	
                            
                        });									
                    }
                },
                error: function (request, status, error) 
                {
                    alert(request.responseText);
                }					
                
            });	
        }
    }    

    $('#cancelar_cliente').click(function(e) {
        swal({
            title: "!!CLIENTE NO FUE GRABADO!!",
            text: "",
            icon: "error",
            type: "warning",
            timer: 2000
        }).then(function () {
            //window.close()
            $('#formulario_mtto').each (function(){
                this.reset();
            });
        });				
        e.preventDefault();								
    });					    

    function print_r(printthis, returnoutput) {
        var output = '';

        if($.isArray(printthis) || typeof(printthis) == 'object') {
            for(var i in printthis) {
                output += i + ' : ' + print_r(printthis[i], true) + '\n';
            }
        }else {
            output += printthis;
        }
        if(returnoutput && returnoutput == true) {
            return output;
        }else {
            alert(output);
        }
    }

    // Auto-focus on the first input on page load
    $('#cfr1').focus();

    // Auto-tab to next number input
    $('.number-input-digit').on('keyup', function(e) {
        if (this.value.length === this.maxLength) {
            $(this).closest('.number-input-digit-wrapper').next().find('input').focus();
        }
    });
});
