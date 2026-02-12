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

function actualizarTotales() {
    // Extrae y limpia los valores para convertirlos a números
    var totalVentaText = $('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '.');
    var totalVenta = parseFloat(totalVentaText) || 0;

    // Sumar todos los valores de premios en la tabla #tbl_premios_ltr
    var totalPremios = 0;
    $('#tbl_premios_ltr tbody tr').each(function () {
        var valorPremioText = $(this).find('td').eq(4).text().replace(/[$.]/g, '').replace(',', '.');
        var valorPremio = parseFloat(valorPremioText) || 0;
        totalPremios += valorPremio;
    });

    // Actualizar el campo de total premios
    var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
    $('#total-premios-valor').text(formatoMoneda.format(totalPremios));

    var valorAPagar = totalVenta - totalPremios;
    if (valorAPagar < 0) valorAPagar = 0;

    // Obtener el campo de efectivo
    var efectivoInput = $('#ingrese-efectivo');

    $('#valor-pagar-valor').text(formatoMoneda.format(valorAPagar));
    efectivoInput.prop('disabled', valorAPagar === 0).val(valorAPagar === 0 ? '' : efectivoInput.val());

    // Si el valor a pagar es cero, muestra alerta informativa
    if (totalPremios > totalVenta) {
        swal(
            'Saldo a Favor',
            'El valor de los premios es mayor que la venta. El cliente no debe pagar.',
            'info'
        );
    }
}

function gestionarScrollTablas() {
    // Selecciona todos los contenedores de tablas que deben tener scroll condicional
    $('.table-container-scroll').each(function() {
        var container = $(this);
        var table = container.find('table');
        var rowCount = table.find('tbody tr').length;

        if (rowCount > 5) {
            container.addClass('scroll-active');
        } else {
            container.removeClass('scroll-active');
        }
    });
}

function setClienteCamposDisabled(disabled) {
    $('#cliente-cedula').prop('disabled', disabled);
    $('#cliente-nombres').prop('disabled', disabled);
    $('#cliente-apellidos').prop('disabled', disabled);
    $('#cliente-celular').prop('disabled', disabled);
    $('#cliente-direccion').prop('disabled', disabled);
    $('#cliente-email').prop('disabled', disabled);
}

function cancelar(e) {
    if (e) e.preventDefault();
    location.href = "index.php";
}

function generarArchivosExcel() {
	var fechaActual = new Date();
	var anio = fechaActual.getFullYear();
	var mes = String(fechaActual.getMonth() + 1).padStart(2, '0');
	var dia = String(fechaActual.getDate()).padStart(2, '0');
	var horas = String(fechaActual.getHours()).padStart(2, '0');
	var minutos = String(fechaActual.getMinutes()).padStart(2, '0');

	//var fechaHora = "${anio}-${mes}-${dia}_${horas}-${minutos}";
	var fechaHora = anio + '-' + mes + '-' + dia + '_' + horas + '-' + minutos;
	var archivo = "reporte_ltr_" + fechaHora + ".xls";

	// Tablas y nombres de hojas
	var tablas = ['tbl_detalle', 'tbl_totales_venta'];
	var nombresHojas = ['Detalle de Ventas', 'Ventas_Premios X Sorteo'];

	tablesToExcel(tablas, nombresHojas, archivo, 'Excel');
}

function manejarErrorAjaxLottired(request) {
    var responseText = request && request.responseText ? request.responseText : '';
    var textoPlano = responseText.replace(/<[^>]*>/g, ' ');
    var textoNormalizado = textoPlano.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    var esErrorAutorizacion = textoNormalizado.indexOf('no se pudo obtener autorizacion') !== -1;
    var esErrorConexionServicio = textoNormalizado.indexOf('no se pudo conectar con el servicio') !== -1;

    if (esErrorConexionServicio) {
        swal('No se pudo conectar con el servicio, por favor informe sobre esta inconsistencia.', '', 'warning');
    } else if (esErrorAutorizacion) {
        swal('No se pudo obtener la autorizacion de la plataforma LOTTIRED, por favor informe sobre esta inconsistencia', '', 'warning');
    } else {
        alert(responseText);
    }
}

$(document).ready(function(){

    $('#num_sor').mask('0000');

    $('#cedula').mask('00000000000');

    var date = new Date();
    var currentMonth = date.getMonth();
    var currentDate = '01';//date.getDate();
    var currentYear = date.getFullYear();

    $('[id^="rango_fechas"]').datepick({
        minDate: new Date(currentYear, currentMonth-12, currentDate),
        maxDate: new Date(currentYear, currentMonth+1, currentDate),
        rangeSelect: true, 
        monthsToShow: 1
    }); 	

    $('#cboLoterias_ltr').click(function(e){
        e.preventDefault();

        var triggerLoterias = $(this);
        if (triggerLoterias.prop('disabled')) {
            return;
        }

        $.ajax({
            async: true,
            url: "../ventas/funciones.php",
            dataType:"json",
            type: 'post',
            data:  { paso: 'cboLoterias_ltr'},
            beforeSend: function() {
                triggerLoterias.prop('disabled', true);
                $('#spinner').show();
            },
            success: function(data){
                $("#list_loterias_ltr").html(data.salida);
            },
            error: function (request, status, error)
            {
                manejarErrorAjaxLottired(request);
            },
            complete: function() {
                triggerLoterias.prop('disabled', false);
                $('#spinner').hide();
            }
        });
    });

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

    $('#cboPtoventa_con').click(function(){		
        fn_ptos_vta(this.id,'list_pto_venta_con');
    });

    $("#consulta_con").click(function(e) {
        fn_consulta_ppal(e); 
        e.preventDefault();	
    });

	$('#rep_excell').click(function(e){ 
		swal('SE GENERO EL ARCHIVO EN LA CARPETA DE DESCARGAS !!', "", "success");
		e.preventDefault();
	});	

    $('#rep_print').click(function(e) {
        e.preventDefault();

        var tabla = $('#tbl_detalle');
        var filasDatos = tabla.find('tr').filter(function() {
            return $(this).find('td').length > 0;
        });

        if (filasDatos.length === 0) {
            swal('Sin registros', 'La tabla de detalle no tiene registros para procesar.', 'warning');
            return;
        }

        var indiceValor = -1;
        var indiceValidoPremio = -1;

        tabla.find('tr').first().find('th').each(function(index) {
            var encabezado = $(this).text().trim().toLowerCase();
            if (encabezado === 'valor') {
                indiceValor = index;
            }
            if (encabezado === 'valido premio') {
                indiceValidoPremio = index;
            }
        });

        if (indiceValor === -1 || indiceValidoPremio === -1) {
            swal('Columnas no encontradas', 'No fue posible ubicar las columnas Valor y/o Valido Premio en tbl_detalle.', 'error');
            return;
        }

        var parseNumero = function(texto) {
            var limpio = (texto || '').toString().trim()
                .replace(/\./g, '')
                .replace(',', '.')
                .replace(/[^0-9.-]/g, '');
            var numero = parseFloat(limpio);
            return isNaN(numero) ? 0 : numero;
        };

        var sumaValor = 0;
        var sumaValidoPremio = 0;

        filasDatos.each(function() {
            var celdas = $(this).find('td');
            sumaValor += parseNumero(celdas.eq(indiceValor).text());
            sumaValidoPremio += parseNumero(celdas.eq(indiceValidoPremio).text());
        });

        console.log('Totales tbl_detalle:', {
            sumaValor: sumaValor,
            sumaValidoPremio: sumaValidoPremio,
            filasProcesadas: filasDatos.length
        });
    });

    //$('#cboLoterias_ltr').change(async function(){ // <--- ¡Añadir 'async' aquí!
    $(document).on('change', '#cboLoterias_ltr', async function () {
        try {
            await fn_ltr_sorteo_activo(); // <--- Usar 'await' aquí

            // 2) **Ejecutar la misma limpieza que el botón**
            limpiarNumero();          // <-- disparo automático

            // Si fn_ltr_sorteo_activo() es exitosa, el código continuaría aquí.
        } catch (err) {
            console.warn('Abortado en #cboLoterias_ltr change porque hubo error en sorteo activo:', err);
            // Aquí puedes añadir lógica adicional si necesitas manejar el error
            // de forma específica para este evento, por ejemplo, resetear el select.
        }
    });

    $('#modal-loteria-select').click(function(){		

        $.ajax({
            async:	false, 
            url:	"../ventas/funciones.php",
            dataType:"json",
            type	: 'post',
            data:  { paso: 'cboLoterias_ltr'},									
            success: function(data){

                // Corregir el ID del select devuelto por el backend
                var selectCorregido = data.salida.replace(
                    'id="cboLoterias_ltr"',
                    'id="modal-loteria-select"'
                );

                $("#list_modal-loteria-select").html(selectCorregido);	
            },	
            error: function (request, status, error) 
            {
                manejarErrorAjaxLottired(request);
            }							
        });			
    });

    // Restringe la entrada solo a números para los campos con la clase .numeric-input
    $(document).on('input', '.numeric-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Restringe la entrada solo a letras mayúsculas y espacios para los campos con la clase .alpha-input
    $(document).on('input', '.alpha-input', function() {
        this.value = this.value.replace(/[^A-Za-z\s]/g, '').toUpperCase();
    });

    // Formatea el campo celular a XXX-XXX-XXXX, permitiendo solo números
    $(document).on('input', '.phone-input', function() {
        // Remover todo menos dígitos
        let value = this.value.replace(/\D/g, '');
        // Limitar a 10 dígitos
        value = value.substring(0, 10);
        // Formatear: XXX-XXX-XXXX
        if (value.length >= 7) {
            value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
        } else if (value.length >= 4) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        this.value = value;
    });

    // Restringe y formatea el campo dirección: letras mayúsculas, números, espacios, / y -
    $(document).on('input', '.address-input', function() {
        let value = this.value.replace(/[^A-Za-z0-9\s/-]/g, '').toUpperCase();
        if (value.length > 50) {
            value = value.substring(0, 50);
        }
        this.value = value;
    });

    // Valida el formato de email en tiempo real para el campo con clase .email-input
    $(document).on('input', '.email-input', function() {
        let value = this.value.substring(0, 30); // Limitar a 30 caracteres
        this.value = value;
    });

    // Consulta cliente al salir del campo cédula (blur)
    $('#cliente-cedula').blur(function(){
        fn_consulta_cliente();
    });

    // Consulta cliente al presionar Enter o Tab en cédula
    $("#cliente-cedula").keypress(function(event){
        if ((event.keyCode == 13) || (event.keyCode == 9))
        {
            fn_consulta_cliente();
            event.preventDefault();
            $('#cliente-nombres').focus();
        }
    });

    // Limpia los campos de entrada del número
    $('#limpiar-numero-btn').on('click', limpiarNumero);

    // Auto-focus en el primer campo de entrada al cargar la página
    $('#cliente-cedula').focus();

    // Auto-tab al siguiente campo de entrada de número
    $('.number-input-digit').on('keyup', function(e) {
        if (this.value.length === this.maxLength) {
            $(this).closest('.number-input-digit-wrapper').next().find('input').focus();
        }
    });

    // Auto-tab en blur para #cfr1 si tiene valor

    $('#cfr1').blur(async function() { // <--- ¡Añadir 'async' aquí!
        if (this.value.length === 1) {
            try {
                await fn_ltr_sorteo_activo(); // <--- Usar 'await' aquí
                // Si fn_ltr_sorteo_activo() es exitosa, el código continuaría aquí.
            } catch (err) {
                console.warn('Abortado en #cfr1 blur porque hubo error en sorteo activo:', err);
                // Aquí puedes añadir lógica adicional si necesitas manejar el error
                // de forma específica para este evento.
            }
        }
    });

	$(document).on("keypress", "#ltr_serie_ingresada", function (e) {
        if ((e.keyCode == 13) || (e.keyCode == 9)) {
            
            var cod_lot = $('#cboLoterias_ltr').val(); 
            var num_bil =  $('#cfr1').val().trim()+$('#cfr2').val().trim()+$('#cfr3').val().trim()+$('#cfr4').val().trim();
            var num_ser = $('#ltr_serie_ingresada').val(); 
            
            if (num_ser.length == 3)
            {
                //console.log('Enter en serie con valor'+cod_lot+num_bil+num_ser);
							//como es una serie que se ingresa de forma manual, entonces para evitar que tenga que darle click en el boton de
							//adicionar y demorarse mas, entonces desde aqui validamos si existe:
                $.ajax({
                    url: "../ventas/funciones.php",
                    dataType: 'json',
                    type: 'post',
                    data: { 
                        paso: 'ltr_fractions_count', 
                        cod_lot: cod_lot, 
                        num_ser: num_ser, 
                        num_bil: num_bil,
                        num_fra: 1    //le enviamos una sola fraccion 
                    },
                    success: function (data) {
                        var error = data.error;
                        if (error.length > 0) {
                            swal(error, "", "error").then(function () {
                                $('#ltr_serie_ingresada').val('');
                                $('#ltr_serie_ingresada').focus();
                            });									
                        }
                        else {
                            fn_ltr_fracciones_serie_seleccionada(cod_lot,num_bil,num_ser);

                            var ltr_nro_fracciones = parseInt($('#current-frac').text()) || 1;  // Captura el valor PREVIO antes de la llamada

                            swal('SE ENCONTRARON '+ltr_nro_fracciones+' FRACCIONES DE LA SERIE '+num_ser+' DEL BILLETE '+num_bil, "", "success")
                            .then((value) => {
                                    //$('#ltr_nro_fracciones').focus();
                            });
                        }
                    },
                    error: function (request, status, error) {
                        alert(request.responseText);
                    }
                });

            }
        }
    });

    $(document).on("change", "#ltr_serie", function (e) {
        if ($(this).is(':checked')) {					
            var cod_lot = $('#cboLoterias_ltr').val(); 
            var num_bil = $('#cfr1').val()+$('#cfr2').val()+$('#cfr3').val()+$('#cfr4').val();
            var num_ser = $(this).val();

            //console.log('Cambio en serie con valor'+cod_lot+num_bil+num_ser);   
            fn_ltr_fracciones_serie_seleccionada(cod_lot,num_bil,num_ser);
            
        }
        e.preventDefault();	
    });

    // Llama a la función para gestionar el scroll al cargar la página
    gestionarScrollTablas();

    // Evento click para el botón grabar_cliente
    $('#btn-grabar-cliente').click(function(e) {
        e.preventDefault();
        // Deshabilitar los campos de datos del cliente
        setClienteCamposDisabled(true);
        fn_graba_cliente();
    });

    // Evento click para el botón cancelar_cliente: habilita los campos de datos del cliente
    $('#btn-cancelar-cliente').click(function(e) {
        e.preventDefault();
        setClienteCamposDisabled(false);
        $('#cliente-cedula').focus();
    });
    
    // Event listeners para botones de fracciones
    $(document).on('click', '#frac-minus', function() {
        let $display = $('#current-frac');
        let current = parseInt($display.text()) || 1;
        if (current > 1) {
            $display.text(current - 1);
            var cboLoterias_ltr = $('#cboLoterias_ltr').val();
            var cfr1 = $('#cfr1').val().trim();
            var cfr2 = $('#cfr2').val().trim();
            var cfr3 = $('#cfr3').val().trim();
            var cfr4 = $('#cfr4').val().trim();
            var numFracciones = parseInt($display.text()) || 1;
            
            if (cfr1 !== '' && cfr2 !== '' && cfr3 !== '' && cfr4 !== '') {

                console.log(cboLoterias_ltr,cfr1, cfr2, cfr3, cfr4, numFracciones);

                fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones);
            }
        }
    });
    
    $(document).on('click', '#frac-plus', function() {
        let $display = $('#current-frac');
        let current = parseInt($display.text()) || 1;
        let max = parseInt($display.data('max')) || 1;
        if (current < max) {
            $display.text(current + 1);
            var cboLoterias_ltr = $('#cboLoterias_ltr').val();
            var cfr1 = $('#cfr1').val().trim();
            var cfr2 = $('#cfr2').val().trim();
            var cfr3 = $('#cfr3').val().trim();
            var cfr4 = $('#cfr4').val().trim();
            var numFracciones = parseInt($display.text()) || 1;
            
            if (cfr1 !== '' && cfr2 !== '' && cfr3 !== '' && cfr4 !== '') {
                fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones);
            }
        }
    });

    // Evento input para #cfr4: verifica si cfr1, cfr2 y cfr3 tienen valores no vacíos después de ingresar un número
    $('#cfr4').on('input', function() {
        var cboLoterias_ltr = $('#cboLoterias_ltr').val();
        var cfr1 = $('#cfr1').val().trim();
        var cfr2 = $('#cfr2').val().trim();
        var cfr3 = $('#cfr3').val().trim();
        var cfr4 = $(this).val().trim();
        var numFracciones = parseInt($('#current-frac').text()) || 1;

        fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones);
    });

    $(document).on('keypress', '#ingrese-efectivo', function (e) {
        // 13 = tecla Enter
        if (e.which === 13) {
            e.preventDefault();               // evitar envío de formulario implícito
            $('#btn-grabar-venta').trigger('click'); // reutiliza el mismo código
        }
    });    

    // Evento click para btn-adicionar: agregar registro de prueba a tbl_ltr
    $(document).on('click', '#btn-adicionar', function() {
        var cod_lot = $('#cboLoterias_ltr').val();
        var loteria = $('#cboLoterias_ltr option:selected').text();
        var sorteo = $('.sorteo-line strong').eq(0).text() || '0000'; // Sorteo del DOM
        var numero = $('#cfr1').val() + $('#cfr2').val() + $('#cfr3').val() + $('#cfr4').val();
        var serie_input = $('#ltr_serie_ingresada').val().trim();
        var serie = serie_input || $('input[type="radio"]:checked', '#div_series_disponibles').val() || '000';
        var fracc = parseInt($('#current-frac').text()) || 1;

        if (!numero || numero.length !== 4) {
            swal('Número incompleto', 'Ingrese un número completo de 4 dígitos.', 'warning');
            return;
        }

        if (!serie || serie === '') {
            swal('Serie no seleccionada', 'Seleccione una serie disponible.', 'warning');
            return;
        }


        // Extraer valor de la fracción y del incentivo del DOM
        var preciosLine = $('.precios-line').text();
        var matchFraccion = preciosLine.match(/Fracción:\s*\$?([\d.,]+)/);
        var valorFraccionStr = matchFraccion ? matchFraccion[1].replace(/[.,]/g, '') : '0';
        var valorFraccion = parseFloat(valorFraccionStr) || 0;
        var matchIncentivo = preciosLine.match(/Incentivo x Fracción:\s*\$?([\d.,]+)/);
        var valorIncentivoStr = matchIncentivo ? matchIncentivo[1].replace(/[.,]/g, '') : '0';
        var valorIncentivo = parseFloat(valorIncentivoStr) || 0;
        var nuevoValorFraccion = (valorFraccion + valorIncentivo) * fracc;
        var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        var valorFormateado = formatoMoneda.format(nuevoValorFraccion);

        //genera el proceso de reserva y luego actualiza el data-reservacion en la fila
        var reservacion = '';
        var nuevaFila = `
            <tr data-reservacion="" data-valor-incentivo="${valorIncentivo}">
                <td style="text-align: left;">${loteria}</td>
                <td class="text-center">${sorteo}</td>
                <td class="text-center">${numero}</td>
                <td class="text-center">${serie}</td>
                <td class="text-center">${fracc}</td>
                <td class="text-right">${valorFormateado}</td>
                <td class="text-center eliminar-fila" style="cursor: pointer;"><i class="fa fa-trash text-danger"></i></td>
            </tr>
        `;
        $('#tbl_ltr tbody').prepend(nuevaFila);

        $.ajax({
            url: "../ventas/funciones.php",
            dataType: 'json',
            type: 'post',
            data: { 
                paso: 'ltr_reservation', 
                cod_lot: cod_lot, 
                num_bil: numero, 
                num_ser: serie, 
                num_fra: fracc 
            },
            success: function (data) {
                var error = data.error;
                if (error.length > 0) {
                    swal(error, "", "error");
                } else {
                    reservacion = data.reservacion;
                    // Asigna el data-reservacion al <tr> recién insertado
                    var $ultimaFila = $('#tbl_ltr tbody tr').first();
                    $ultimaFila.attr('data-reservacion', reservacion);
                }
            },
            error: function (request, status, error) {
                alert(request.responseText);
            }
        });

        // Actualizar total de venta (sumar nuevoValorFraccion calculado) ...
        var currentTotalText = $('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '');
        var currentTotal = parseFloat(currentTotalText) || 0;
        currentTotal += nuevoValorFraccion;
        $('#total-venta-valor').text(formatoMoneda.format(currentTotal));

        actualizarTotales();
        gestionarScrollTablas();

        // Limpiar inputs para nueva entrada
        $('.number-input-digit').val('');
        $('#ltr_serie_ingresada').val(''); // Limpiar input si existe
        $('input[type="radio"]', '#div_series_disponibles').prop('checked', false); // Desmarcar radios
        //$('#cfr1').focus();
        $('#ingrese-efectivo').focus();
        swal('Registro agregado', 'Se ha adicionado un registro de prueba a la tabla.', 'success');

        // Ocultar el bloque de series después de adicionar exitoso
        $('#div_series_disponibles').hide();
        $('.datos-sorteo-row').hide();
    });

    // Función reutilizable para liberar una reserva
    function liberarReserva(reservacion) {
        if (reservacion && reservacion.length > 0) {
            $.ajax({
                async: false,
                url: "../ventas/funciones.php",
                dataType: "json",
                type: 'post',
                data: { paso: 'ltr_release_reservation', reservacion: reservacion },
                success: function(data) {
                    var error = data.error;
                    if (error && error.length > 0) {
                        swal(error, "", "error");
                    }
                },
                error: function (request, status, error) {
                    alert(request.responseText);
                }
            });
        }
    }

    // Evento para eliminar filas de la tabla (clic en ícono trash)
    $(document).on('click', '.eliminar-fila i', function() {
        var fila = $(this).closest('tr');
        var valorFilaText = fila.find('td:nth-child(6)').text().replace(/[$.]/g, '').replace(',', '');
        var valorFila = parseFloat(valorFilaText) || 0;

        // Liberar la reserva de esta fila
        var reservacion = fila.attr('data-reservacion');
        liberarReserva(reservacion);

        // Restar del total
        var currentTotalText = $('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '');
        var currentTotal = parseFloat(currentTotalText) || 0;
        currentTotal = Math.max(0, currentTotal - valorFila);
        var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        $('#total-venta-valor').text(formatoMoneda.format(currentTotal));

        fila.remove();
        actualizarTotales();
        gestionarScrollTablas();
        swal('Fila eliminada', 'El registro ha sido removido de la tabla.', 'info');
    });

    // Evento para cancelar toda la tabla y liberar todas las reservas
    $(document).on('click', '#cancelar-tbl-ltr', function() {
        // Liberar todas las reservas de las filas actuales
        $('#tbl_ltr > tbody > tr').each(function() {
            var reservacion = $(this).attr('data-reservacion');
            liberarReserva(reservacion);
        });
        // Limpiar la tabla
        $('#tbl_ltr tbody').empty();
        // Reiniciar el total de venta
        var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        $('#total-venta-valor').text(formatoMoneda.format(0));
        actualizarTotales();
        gestionarScrollTablas();
        swal('Reservas liberadas', 'Todos los registros han sido cancelados.', 'info');
    });

    // Evento click para el bot�n genera_numero
    $('#genera_numero').click(async function(e) {
        e.preventDefault();

        var btnGeneraNumero = $(this);
        if (btnGeneraNumero.prop('disabled')) {
            return;
        }

        var htmlOriginalBoton = '<i class="fa fa-random"></i>';
        btnGeneraNumero.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $('#spinner').show();

        try {
            await fn_ltr_sorteo_activo();

            const cboLoterias_ltr = $('#cboLoterias_ltr').val();
            const numFracciones = parseInt($('#current-frac').text()) || 1;

            $.ajax({
                async: true,
                url: "../ventas/funciones.php",
                dataType: "json",
                type: "post",
                data: { paso: "ltr_genera_numero", cod_lot: cboLoterias_ltr, nro_fracciones: numFracciones },
                success: function(data) {
                    var error = data.error;
                    var fracciones = data.fracciones;

                    if (error.length > 0) {
                        swal(error, "", "error");
                    } else {
                        var arreglo = data.arreglo;

                        $('#cfr1').val(arreglo.lr_num_bil1);
                        $('#cfr2').val(arreglo.lr_num_bil2);
                        $('#cfr3').val(arreglo.lr_num_bil3);
                        $('#cfr4').val(arreglo.lr_num_bil4);

                        fn_series_disponibles(cboLoterias_ltr, arreglo.lr_num_bil1, arreglo.lr_num_bil2, arreglo.lr_num_bil3, arreglo.lr_num_bil4, fracciones);
                    }
                },
                error: function(request, status, error) {
                    manejarErrorAjaxLottired(request);
                },
                complete: function() {
                    $('#spinner').hide();
                    btnGeneraNumero.prop('disabled', false).html(htmlOriginalBoton);
                }
            });
        } catch (err) {
            console.warn('Abortado porque hubo error en sorteo activo:', err);
            $('#spinner').hide();
            btnGeneraNumero.prop('disabled', false).html(htmlOriginalBoton);
        }
    });

    //premios de loteria

    //premios de loteria - Modificado para usar la función reutilizable
    $(document).on("keypress", "#barcode", function (e) {
        if ((e.keyCode == 13) || (e.keyCode == 9)) {
            var barcode = $('#barcode').val();
            fn_valida_premio(barcode);
            e.preventDefault(); 
        } 
    });

    ///Reimpresion de Factura:
    $(document).on("keypress", "#id_venta", function (e) {
        if ((e.keyCode == 13) || (e.keyCode == 9)) {
            var id_venta = $('#id_venta').val();
            //console.log('Reimprimir venta ID: '+id_venta);
            fn_reimprimir_venta(id_venta);
            e.preventDefault(); 
        } 
    });

    // Evento para eliminar filas de la tabla de premios (clic en ícono trash)
    $(document).on('click', '#tbl_premios_ltr .fa-trash', function() {
        var fila = $(this).closest('tr');
        fila.remove();
        actualizarTotales();
        swal('Premio eliminado', 'El registro ha sido removido de la tabla de premios.', 'info');
    });

    // Confirmación de venta con validación de registros en tablas
    $(document).on('click', '#btn-grabar-venta', async function(e) {
        e.preventDefault();
        setClienteCamposDisabled(false);

        // -------------------------------------------------------------
        // 1️⃣  VALIDACIÓN BÁSICA DE DATOS DEL CLIENTE
        // -------------------------------------------------------------
        const cedula = $('#cliente-cedula').val().trim();
        const nombres = $('#cliente-nombres').val().trim();

        // Si los dos campos están vacíos, mostrar error inmediatamente
        if (!cedula || !nombres) {
            swal('Datos del cliente incompletos', 'Debe ingresar los datos del cliente y grabarlos', 'warning');
            return;
        }

        // Validar existencia del cliente en base de datos antes de continuar
        try {
            const respCliente = await $.ajax({
                url: "funciones.php",
                type: "post",
                dataType: "json",
                data: {
                    paso: "validar_cliente_existe",
                    cedula: cedula
                }
            });

            if (respCliente.error && respCliente.error.length > 0) {
                swal('Error de validacion', respCliente.error, 'error');
                return;
            }

            if (!respCliente.existe) {
                swal('Cliente no existe', 'Debe registrar/grabar el cliente antes de realizar la venta.', 'warning');
                $('#cliente-cedula').focus();
                return;
            }
        } catch (xhr) {
            swal('Error de conexion', xhr.responseText || 'No fue posible validar el cliente.', 'error');
            return;
        }

        // -------------------------------------------------------------
        // 2  VALIDAR CAMPOS OBLIGATORIOS
        // -------------------------------------------------------------
        var idUsu   = $('#id_usu').val().trim();   // <-- campo oculto o visible
        var ptoVta  = $('#pto_vta').val().trim();  // <-- campo oculto o visible

        if (!idUsu) {
            swal('Falta información', 'El campo "id_usu" es obligatorio para grabar la venta.', 'warning');
            $('#id_usu').focus();
            return;
        }
        if (!ptoVta) {
            swal('Falta información', 'El campo "pto_vta" es obligatorio para grabar la venta.', 'warning');
            $('#pto_vta').focus();
            return;
        }

        // -------------------------------------------------------------
        // 3  VALIDAR QUE HAYA REGISTROS EN LAS TABLAS
        // -------------------------------------------------------------
        var tieneVenta  = $('#tbl_ltr tbody tr').length > 0;
        var tienePremio = $('#tbl_premios_ltr tbody tr').length > 0;
        if (!tieneVenta && !tienePremio) {
            swal('Sin registros', 'Debe agregar al menos un ítem en venta o premio.', 'warning');
            return;
        }

        // ---------- 3️⃣  VALIDAR EFECTIVO ----------
        // Si existen ítems de venta, el campo #ingrese-efectivo no debe estar vacío
        // ni ser menor al total de la venta.
        if (tieneVenta) {
            // Obtener el total de la venta (texto → número)
            var totalVentaTxt = $('#total-venta-valor')
                                    .text()
                                    .replace(/[$.]/g, '')
                                    .replace(',', '.')
                                    .trim();
            var totalVenta = parseFloat(totalVentaTxt) || 0;

            // **Nuevo: obtener el valor a pagar (valor‑pagar‑valor)**
            var valorPagarTxt = $('#valor-pagar-valor')
                                    .text()
                                    .replace(/[$.]/g, '')
                                    .replace(',', '.')
                                    .trim();
            var valorPagar = parseFloat(valorPagarTxt) || 0;

            // Valor ingresado por el cliente
            var efectivoTxt = $('#ingrese-efectivo')
                                .val()
                                .replace(/[$.]/g, '')
                                .replace(',', '.')
                                .trim();
            var efectivo = parseFloat(efectivoTxt) || 0;   // 0 si está vacío o no numérico

            /*
            console.log('Total venta:', totalVenta,
                            'Valor a pagar:', valorPagar,
                            'Efectivo ingresado:', efectivo);
            */

            if (efectivoTxt === '' || efectivo < valorPagar) {
                swal('Efectivo insuficiente',
                    'El valor ingresado en "Efectivo" es nulo o menor al total de la venta.',
                    'warning')
                    .then(function () {
                        $('#ingrese-efectivo')
                            .focus()
                            .select();   // resalta el contenido para que el usuario lo sobrescriba
                    });
                return;   // <‑‑ NO AVANZAR
            }
        }

        // -------------------------------------------------------------
        // 3️⃣  MOSTRAR CONFIRMACIÓN Y LLAMAR A grabarVenta()
        // -------------------------------------------------------------
        swal({
            title: '¿Está seguro de grabar la venta?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: { text: 'Cancelar', value: false, visible: true, className: 'btn btn-danger' },
                confirm: { text: 'Sí, grabar', value: true, visible: true, className: 'btn btn-success' }
            }
        }).then(function (result) {
            if (result) {
                // Desactivar el botón mientras se procesa
                $('#btn-grabar-venta').prop('disabled', true);
                // Pasar los valores al objeto que enviará grabarVenta()
                grabarVenta(idUsu, ptoVta);
            }
        });

    });

   // -----------------------------------------------------------------
    //  INICIALIZAR tablesorter para la tabla de consulta de ventas
    // -----------------------------------------------------------------
    if ($.fn.tablesorter) {
        $("#tbl_consulta_ventas").tablesorter({
            theme: "blue",          // usa el tema de Bootstrap 3
            headerTemplate: '{content} {icon}', // agrega íconos de ordenación
            widgets: ["zebra"],         // alterna colores de filas
            widthFixed: true,
            // **Ordenar por la columna “Venta” (índice 0) en orden descendente**
            sortList: [[0, 1]]                      // 0 = columna Venta, 1 = DESC        
        }).tablesorterPager({
			container: $(".pager")
		});
    } else {
        console.warn("tablesorter no está cargado. Asegúrate de incluir jquery.tablesorter.min.js y su CSS.");
    }

   // -----------------------------------------------------------------
    //  RESTRICCIÓN DE INPUT: Sólo números en el campo Sorteo
    // -----------------------------------------------------------------
    $(document).on('input', '#modal-sorteo-input', function () {
        // Elimina cualquier carácter que no sea dígito
        this.value = this.value.replace(/\D/g, '');
    });

    /* --------------------------------------------------------------
    3️⃣ Listener para abrir el modal al hacer click en el nuevo botón.
    -------------------------------------------------------------- */
    $(document).on('click', '#buscar-transacciones-btn', function () {
        fn_validar_busqueda_premio();
    });

    $(document).on("click", ".cls_busca_premio",function(e) {

        // Deshabilitar el botón inmediatamente para evitar múltiples clicks
        var $boton = $(this);
        $boton.prop('disabled', true);

        // Agregar estilos visuales para mostrar que está deshabilitado
        $boton.css({
            'opacity': '0.5',
            'cursor': 'not-allowed',
            'pointer-events': 'none'
        });

        var fila = $(this).closest('tr');
        var barcode = fila.data('barcode');

        fn_valida_premio(barcode);
        e.preventDefault();

    });

    // -----------------------------------------------------------------
    //  CANCELAR VENTA – liberar reservas, vaciar tablas y reiniciar totales
    // -----------------------------------------------------------------
    $(document).on('click', '#btn-cancelar-venta', function (e) {
        e.preventDefault();

        // 1️⃣  Liberar reservas de la tabla de venta
        $('#tbl_ltr tbody tr').each(function () {
            var reservacion = $(this).attr('data-reservacion');
            liberarReserva(reservacion);
        });

        // 2️⃣  Vaciar ambas tablas (la de venta y la de premios)
        $('#tbl_ltr tbody').empty();
        $('#tbl_premios_ltr tbody').empty();

        // 3️⃣  Reiniciar los totales a $0
        var formatoMoneda = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        });
        $('#total-venta-valor').text(formatoMoneda.format(0));
        $('#total-premios-valor').text(formatoMoneda.format(0));
        $('#valor-pagar-valor').text(formatoMoneda.format(0));

        // 4️⃣  Desactivar y limpiar el campo de efectivo
        $('#ingrese-efectivo')
            .val('')
            .prop('disabled', true);

        // 5️⃣  Limpiar los datos del cliente y habilitarlos nuevamente
        setClienteCamposDisabled(false);
        $('#cliente-cedula').val('').focus();
        $('#cliente-nombres').val('');
        $('#cliente-apellidos').val('');
        $('#cliente-celular').val('');
        $('#cliente-direccion').val('');
        $('#cliente-email').val('');

        // 6️⃣  Mensaje informativo
        swal('Venta cancelada', 'Todas las reservas fueron liberadas y los totales reiniciados.', 'info');
    });

    // -----------------------------------------------------------------
    // 2️⃣  Listener que formatea en tiempo real (evento input)
    // -----------------------------------------------------------------
    $('#ingrese-efectivo').on('input', function (e) {
        const $input = $(this);
        const rawValue = $input.val();

        // Guardar posición del cursor antes de formatear
        const selectionStart = this.selectionStart;
        const selectionEnd   = this.selectionEnd;

        // Formatear y actualizar el valor del input
        const formatted = formatCurrencyInt(rawValue);
        $input.val(formatted);

        // Restaurar posición del cursor (aproximada)
        const diff = formatted.length - rawValue.length;
        const newPos = Math.max(0, selectionStart + diff);
        this.setSelectionRange(newPos, newPos);
    });

});

function fn_consulta_ppal(e){
    var cboPtoventa	 		 =	$('#cboPtoventa_con').val();
    var cboLoterias	 		 =	$('#cboLoterias').val();
    var rango_fechas 		 =	$('#rango_fechas_con').val();	
    var rango_fechas_sorteo  =	$('#rango_fechas_sorteo').val();	
    var rango_fechas_val_pre =	$('#rango_fechas_val_pre').val();	
    var cedula       		 =	$('#cedula').val();	
    var num_sor       		 =	$('#num_sor').val();


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

function fn_ptos_vta(cbo_id,list_pto_venta) {
    
    $.ajax({
        async:	false, 
        url:	"funciones.php",
        dataType:"json",
        type	: 'post',
        data:  { paso: 'cboPtoventa', cbo_id: cbo_id,tipo: list_pto_venta},									
        success: function(data){
            $("#"+list_pto_venta).html(data.salida);	
        },	
        error: function (request, status, error) 
        {
            alert(request.responseText);
        }							
    });	
}

function fn_reimprimir_venta(id_venta) {
    $.ajax({
        async: true, 
        url: "funciones.php",
        dataType: "json",
        type: "post",   
        data: { paso: "reimprimir_venta", id_venta: id_venta },

        /** ---------------------------------------------------------
         *  SUCCESS
         *  ---------------------------------------------------------
         *  La respuesta del servidor tiene la forma:
         *
         *  {
         *      error      : "",               // cadena vacía si todo OK
         *      mensaje    : "...",            // opcional
         *      cliente    : { … },            // datos del cliente
         *      recibidos  : { … },            // id_venta, id_usu, pto_vta
         *      arre_totales : { … }           // totalVenta, totalPremios, …
         *  }
         *
         *  Los tres objetos se extraen y se envían a imprimirTicketVenta().
         *  ------------------------------------------------------- */


        success: function (response) {
            var error = response.error;

            if (error && error.length > 0){
                swal(error, "", "error");
                return;
            }

            var cliente   = response.data.cliente;
            var recibidos = response.data.recibidos;
            var totales   = response.data.totales;

            /* ---------------------------------------------------------
               2️⃣  Mensaje de confirmación al usuario
               --------------------------------------------------------- */
            swal('Venta reimpresa', 'La venta ha sido reimpresa correctamente.', 'success')
                .then(function () {
                    // Limpiar el campo de búsqueda y devolver el foco
                    $('#id_venta').val('').focus();
                });

            /* ---------------------------------------------------------
               3️⃣  Llamar a la rutina de impresión con los datos
            --------------------------------------------------------- */

            imprimirTicketVenta(cliente, recibidos, totales);
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    }); 
}

/* -----------------------------------------------------------------
   FUNCIÓN: grabarVenta()
   - Valida que exista al menos un registro en alguna de las tablas.
   - Reúne los datos del cliente, los items de venta y los premios.
   - Envía todo al backend (paso = "grabar_venta") mediante AJAX.
   - En caso de éxito: libera reservas, vacía tablas, reinicia totales,
     muestra mensaje de éxito y recarga la página (o deja la UI lista
     para una nueva operación).
   - En caso de error: muestra alerta con el mensaje recibido.
   ----------------------------------------------------------------- */
function grabarVenta(idUsu, ptoVta) {
 
    // -------------------------------------------------------------
    // 2️⃣  RECOLECTAR DATOS DEL CLIENTE
    // -------------------------------------------------------------
    var cliente = {
        // <-- 1️⃣  Cédula (ya existía)
        cedula    : $('#cliente-cedula').val().trim(),

        // <-- 2️⃣  Nombres y apellidos
        nombres   : $('#cliente-nombres').val().trim(),
        apellidos : $('#cliente-apellidos').val().trim(),

        // <-- 3️⃣  Teléfono / celular
        celular   : $('#cliente-celular').val().trim(),

        // <-- 4️⃣  Dirección y correo electrónico
        direccion : $('#cliente-direccion').val().trim(),
        email     : $('#cliente-email').val().trim()
    };
    // -------------------------------------------------------------
    // 3️⃣  DETALLE DE VENTA (tabla #tbl_ltr)
    // -------------------------------------------------------------
    var detalleVenta = [];
    $('#tbl_ltr tbody tr').each(function () {
        var $fila = $(this);
        detalleVenta.push({
            // -----------------------------------------------------------------
            // 1️⃣  Campos existentes (ejemplo: reservación)
            // -----------------------------------------------------------------
            reservacion : $fila.attr('data-reservacion') || '',

            // -----------------------------------------------------------------
            // 2️⃣  NUEVO: obtener el valor monetario de la columna 6 (índice 5)
            // -----------------------------------------------------------------
            valor : $fila.find('td').eq(5).text()
                        .replace(/[$.]/g, '')   // eliminar símbolos de moneda y separadores de miles
                        .replace(',', '.')      // usar punto decimal
                        .trim(),

            // -----------------------------------------------------------------
            // 3️⃣  NUEVO: obtener el **valor del incentivo** que está en el
            //           atributo `data-valor-incentivo` de la fila
            // -----------------------------------------------------------------
            valorIncentivo : $fila.attr('data-valor-incentivo') || '0'
        });    
    });

    // <-- **Console log fuera del ciclo** para inspeccionar el array completo
    //console.log('Detalle de venta (array completo):', detalleVenta);

    // -------------------------------------------------------------
    // 4️⃣  DETALLE DE PREMIOS (tabla #tbl_premios_ltr)
    // -------------------------------------------------------------
    var detallePremios = [];
    $('#tbl_premios_ltr tbody tr').each(function () {
        var $fila = $(this);
        detallePremios.push({
            // datos que ya tenías
            barcode : $fila.attr('data-barcode') || '',
            // <-- NUEVO: obtener el valor del premio (columna 5, índice 4)
            valor   : $fila.find('td').eq(4).text()
                            .replace(/[$.]/g, '')
                            .replace(',', '.')
                            .trim()
        });
    });

    // -------------------------------------------------------------
    // 5️⃣  TOTALES Y EFECTIVO
    // -------------------------------------------------------------
    var totales = {
        totalVenta   : $('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '.').trim(),
        totalPremios : $('#total-premios-valor').text().replace(/[$.]/g, '').replace(',', '.').trim(),
        valorPagar   : $('#valor-pagar-valor').text().replace(/[$.]/g, '').replace(',', '.').trim(),
        efectivo     : $('#ingrese-efectivo').val().replace(/[$.]/g, '').replace(',', '.').trim()
    };

    // -------------------------------------------------------------
    // 6️⃣  ENVIAR LA INFORMACIÓN AL BACKEND
    // -------------------------------------------------------------
    $.ajax({
        url: "funciones.php",
        type: "post",
        dataType: "json",
        data: {
            paso      : "grabar_venta_ltr",
            id_usu    : idUsu,          // <-- ahora enviado explícitamente
            pto_vta   : ptoVta,         // <-- ahora enviado explícitamente
            cliente   : cliente,
            venta     : detalleVenta,
            premios   : detallePremios,
            totales   : totales        
        },
        beforeSend: function () {
            // Opcional: bloquear UI o mostrar spinner
            $('#btn-grabar-venta')
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Grabando...');
        },
        success: function (resp) {
            // El backend debe devolver { error: "", mensaje: "" }

            //console.log('Respuesta grabar_venta:', resp);

            const recibidos = resp.recibidos || {};

            console.log('ID venta:', recibidos.id_venta);

            if (resp.error && resp.error.length > 0) {
                swal('Error al grabar la venta', resp.error, 'error');
                return;
            }

            // ---------------------------------------------------------
            // 7️⃣  OPERACIONES POST‑GRABADO
            // ---------------------------------------------------------
            // Vaciar ambas tablas y reiniciar totales
            $('#tbl_ltr tbody').empty();
            $('#tbl_premios_ltr tbody').empty();

            var fmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
            $('#total-venta-valor').text(fmt.format(0));
            $('#total-premios-valor').text(fmt.format(0));
            $('#valor-pagar-valor').text(fmt.format(0));
            $('#ingrese-efectivo').val('').prop('disabled', true);

			limpiarCliente()

            // Mensaje final de éxito
            swal('Venta grabada', resp.mensaje || 'La venta se registró correctamente.', 'success')
                .then(function () {
                    // Recargar la página o simplemente dejar la UI lista para una nueva venta
                    //location.reload();   // <-- puedes comentar esta línea si no deseas recargar
                    imprimirTicketVenta(cliente,recibidos,totales);
                });
        },
        error: function (xhr) {
            swal('Error de conexión', xhr.responseText, 'error');
        },
        complete: function () {
            // Restaurar el botón
            $('#btn-grabar-venta')
                .prop('disabled', false)
                .html('<i class="fa fa-check"></i> Grabar');
        }
    });
}

// -----------------------------------------------------------------
// 1️⃣  Función que formatea un número como moneda **sin decimales**
// -----------------------------------------------------------------
function formatCurrencyInt(value, symbol = '$') {
    // Eliminar todo lo que no sea dígito o signo negativo
    let clean = value.replace(/[^\d-]/g, '');

    // Convertir a número entero (si falla, devolver 0)
    let number = parseInt(clean, 10);
    if (isNaN(number)) number = 0;

    // Formatear con separador de miles y **sin decimales**
    // Intl.NumberFormat respeta la configuración regional del navegador.
    // En Colombia (es-CO) el separador de miles es '.' y no se muestra
    // parte decimal.
    const formatter = new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 0,
        minimumFractionDigits: 0
    });

    return symbol + formatter.format(number);
}

// Función de validación para búsqueda de premios
function fn_validar_busqueda_premio() {
    var cedula = $('#cliente-cedula').val(); 
    var barcode = $('#barcode').val(); 

    if (cedula.length == 0) {
        swal('DEBE INGRESAR EL NUMERO DE CEDULA', "", "error")
            .then((value) => {
                $('#cliente-cedula').focus();
            });									
        return false;
    } 

    // ✅ VALIDAR NOMBRES Y APELLIDOS
    var clienteNombres = $('#cliente-nombres').val();
    var clienteApellidos = $('#cliente-apellidos').val();
    
    if (clienteNombres.length == 0 || clienteApellidos.length == 0) {
        // ✅ SWAL PARA INFORMACIÓN FALTANTE DEL CLIENTE
        swal('FALTA INFORMACION DEL CLIENTE', 'Debe completar los nombres y apellidos del cliente', "warning")
            .then((value) => {
                if (clienteNombres.length == 0) {
                    $('#cliente-nombres').focus();
                } else {
                    $('#cliente-apellidos').focus();
                }
            });
        return false;
    }
    
    // ✅ SI TODO ESTÁ CORRECTO, PROCEDER
    if (barcode.length == 0) {
        fn_ltr_consulta_ventas(cedula);
    }
    
    // ✅ CONCATENAR NOMBRES Y APELLIDOS EN EL MODAL
    var nombreCompleto = clienteNombres.trim();
    if (clienteApellidos.trim().length > 0) {
        nombreCompleto += ' ' + clienteApellidos.trim();
    }
    
    // ✅ COLOCAR NOMBRE CONCATENADO EN EL MODAL
    $('#con-nombre-cliente').val(nombreCompleto);
    
    // ✅ MOSTRAR MODAL
    $('#modal-buscar-transacciones').modal({
        show: true,
        backdrop: 'static',
        keyboard: false
    });
    return true;
    
}

function fn_ltr_consulta_ventas(cedula) {
    $.ajax({
        async: false,
        url: "../ventas/funciones.php",
        dataType: "json",
        type: 'post',
        data: { paso: 'busca_tnx_ltr', cedula: cedula },    
        beforeSend: function () {
            $("#spinner").show();
        },
        success: function (data) {
            // ✅ VALIDAR ESTRUCTURA DE LA RESPUESTA
            if (!data) {
                swal('ERROR', 'No se recibió respuesta del servidor', "error");
                return;
            }

            var error = data.error; 
            if (error && error.length > 0) {
                swal(error, "", "error");
            } else {
                var arreglo = data.arreglo;

                console.log('Arreglo recibido:', arreglo);
                console.log('Tipo de arreglo:', typeof arreglo);
                console.log('¿Es un array?', Array.isArray(arreglo));
                console.log('Longitud del arreglo:', arreglo ? arreglo.length : 'undefined');

                var tablaBody = $('#tbl_consulta_ventas tbody');
                tablaBody.empty(); // Limpiar contenido previo
                if (arreglo && arreglo.length > 0) {
                    $.each(arreglo, function (j, data2) {

                        var validationCell;
                        
                        if (data2.valida_premio == 'S') {
                            //validationCell = "<td align='center' title='REGISTRO YA VALIDADO' class='text-danger'><span class='fa fa-times'></span></td>";
                            validationCell = "<td align='center'><a type='button' title='VALIDAR PREMIO' class='cls_busca_premio btn btn-danger btn-xs'><span class='fa fa-check'></span></a></td>";
                        } else {
                            validationCell = "<td align='center'><a type='button' title='VALIDAR PREMIO' class='cls_busca_premio btn btn-success btn-xs'><span class='fa fa-check'></a></td>";
                        }


                        var nuevaFila = `
                            <tr data-barcode="${data2.barcode || ''}">
                                <td class="text-center">${data2.id_venta || ''}</td>       
                                <td class="text-center">${(data2.fec_venta || '').substring(0, 10)}</td>
                                <td class="text-center">${data2.loteria || ''}</td>
                                <td class="text-center">${data2.num_sor || ''}</td>
                                <td class="text-center">${data2.fec_sor_str || ''}</td>
                                <td class="text-center">${data2.num_bil || ''}</td>
                                <td class="text-center">${data2.num_ser || ''}</td>
                                <td class="text-center">${data2.fracciones || ''}</td>
                                ${validationCell}
                            </tr>
                        `;
                        tablaBody.append(nuevaFila);      
                    });
                } else {
                    tablaBody.append('<tr><td colspan="7" class="text-center">No se encontraron transacciones para este cliente.</td></tr>');
                }   
                // Actualizar tablesorter después de modificar la tabla
                if ($.fn.tablesorter) {
                    $("#tbl_consulta_ventas").trigger("update");
                }
            }
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    }); 
}

function fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones) {
    // Validación previa: asegurar que todos los cfrx no estén vacíos y numFracciones sea válido
    if (!cboLoterias_ltr || cfr1 === '' || cfr2 === '' || cfr3 === '' || cfr4 === '' || !numFracciones || numFracciones <= 0) {
        console.log('Parámetros inválidos para fn_series_disponibles');
        return;
    }
    
    // Concatenación optimizada de num_bil solo si todos los cfrx son válidos
    var num_bil = cfr1 + cfr2 + cfr3 + cfr4;
    
	$.ajax({
        async: true,
        url: "funciones.php",
        dataType: "json",
        type: 'post',
        data: { paso: 'ltr_series_disponibles', cod_lot: cboLoterias_ltr, num_bil: num_bil, fracciones: numFracciones },
        beforeSend: function () {
            $("#spinner").show();
        },
        success: function (data) {

            $("#div_series_disponibles").html(data.salida);

            // Mostrar #div_series_disponibles después de generar
            $('#div_series_disponibles').show();

            // Mejora la presentación del input y título solo si hay salida
            if (data.salida && data.salida.trim() !== '') {
                // Estilo para el input de serie
                $('#ltr_serie_ingresada').css({
                    'border': '2px solid #007bff',
                    'border-radius': '5px',
                    'padding': '10px',
                    'font-size': '1.2em',
                    'text-align': 'center',
                    'background-color': '#f8f9fa',
                    'width': '60px',
                    'margin-left': '10px'
                }).parent().css({
                    'display': 'flex',
                    'justify-content': 'flex-end',
                    'margin-left': 'auto'
                });

                // Estilo para el título
                $('#div_series_disponibles p').css({
                    'background-color': '#e7f3ff',
                    'color': '#0c5460',
                    'font-weight': 'bold',
                    'padding': '8px',
                    'border-radius': '4px',
                    'text-align': 'center',
                    'font-size': '1.1em'
                });

                // Restringir input a solo 3 números
                $('#ltr_serie_ingresada').on('input', function() {
                    let value = $(this).val().replace(/[^0-9]/g, ''); // Solo números
                    if (value.length > 3) value = value.substring(0, 3); // Máximo 3 dígitos
                    $(this).val(value);
                });
            }

            //var fracciones = data.fracciones;
            var error = data.error;

            if (error.length > 0) {
                swal({
                    title: error,
                    text: "",
                    icon: "error",
                    type: "warning",
                    timer: 5000
                }).then(function () {
                    //fn_limpiar_ltr();
                });
            } else {
            }
        },
        error: function (request, status, error) {
            alert(request.responseText);
        }
    });
}

function fn_consulta_cliente(){

    var cedula = $('#cliente-cedula').val();
    
    if (!cedula) {
        //debe tener un valor, sino no hace nada
        return;
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

                    if (arreglo.length > 0) {
                        $.each(arreglo, function(j,data2){

                            $('#cliente-nombres').val(data2.nombres);
                            $('#cliente-apellidos').val(data2.apellidos);
                            $('#cliente-celular').val(data2.celular);
                            $('#cliente-direccion').val(data2.direccion);
                            $('#cliente-email').val(data2.correo);

                        });
                    } else {
                        swal('Cliente no encontrado', 'No se encontró un cliente con esta cédula.', 'warning')
                        .then(function() {
                            $('#cliente-nombres').focus();
                        });
                    }
                }
            },
            error: function (request, status, error)
            {
                alert(request.responseText);
            }
            
        });
    }
}

function validarCliente() {
    const camposRequeridos = [
        { id: 'cliente-cedula', label: 'Cédula' },
        { id: 'cliente-nombres', label: 'Nombres' },
        { id: 'cliente-apellidos', label: 'Apellidos' },
        { id: 'cliente-celular', label: 'Celular' }
    ];

    let esValido = true;
    let primerCampoInvalido = null;

    camposRequeridos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        const valor = elemento.value.trim();

        if (!valor) {
            esValido = false;
            if (!primerCampoInvalido) {
                primerCampoInvalido = elemento;
            }
            return;
        }

        // Validación específica para cédula: mínimo 5 dígitos
        if (campo.id === 'cliente-cedula') {
            const soloDigitos = valor.replace(/\D/g, '');
            if (soloDigitos.length < 5) {
                esValido = false;
                if (!primerCampoInvalido) {
                    primerCampoInvalido = elemento;
                }
                swal({
                    title: 'Cédula Inválida',
                    text: 'La cédula debe tener al menos 5 dígitos.',
                    type: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Para celular, verificar que tenga al menos 10 dígitos raw (ignorando guiones)
        if (campo.id === 'cliente-celular') {
            const soloDigitos = valor.replace(/\D/g, '');
            if (soloDigitos.length < 10) {
                esValido = false;
                if (!primerCampoInvalido) {
                    primerCampoInvalido = elemento;
                }
                swal({
                    title: 'Celular Inválido',
                    text: 'El celular debe tener 10 dígitos.',
                    type: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        }
    });

    if (!esValido && primerCampoInvalido) {
        primerCampoInvalido.focus();
    }
    return esValido;
}

function fn_graba_cliente(){
    
    var sw = 0;
    
    if (!validarCliente()) {
        sw = 1;
        return;
    }
    
    var cedula    = $('#cliente-cedula').val();
    var nombres   = $('#cliente-nombres').val();
    var apellidos = $('#cliente-apellidos').val();
    var celular   = $('#cliente-celular').val();
    var direccion = $('#cliente-direccion').val();
    var correo    = $('#cliente-email').val();
    
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
        url 		: '../clientes/funciones.php',
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
                    type: "success",
                    timer: 5000,
                    showConfirmButton: false
                }).then(function() {
                    $('#cfr1').focus();
                });
            }
        },
        error: function (request, status, error)
        {
            alert(request.responseText);
        }
        
    });
}

function limpiarCliente() {
    $('#cliente-cedula').val('');
    $('#cliente-nombres').val('');
    $('#cliente-apellidos').val('');
    $('#cliente-celular').val('');
    $('#cliente-direccion').val('');
    $('#cliente-email').val('');
    $('#cliente-cedula').focus();
}

// 1️⃣ Función reutilizable que limpia los campos y oculta los bloques
function limpiarNumero() {
    $('.number-input-digit').val('');          // Vaciar los 4 dígitos
    $('#cfr1').focus();                       // Enfocar el primer campo
    $('#div_series_disponibles').hide();      // Ocultar series
    $('.datos-sorteo-row').hide();            // Ocultar datos del sorteo

    //console.log('Campos de número limpiados y bloques ocultados.');
}

function fn_ltr_fracciones_serie_seleccionada(cod_lot, num_bil, num_ser) {
    // Validaciones básicas antes del AJAX
    if (!cod_lot || !num_bil || !num_ser) {
        console.error('Parámetros inválidos:', { cod_lot, num_bil, num_ser });
        swal('Error', 'Parámetros incompletos para consultar fracciones.', 'warning');
        return;
    }

    $.ajax({
        async: false,  // Cambiado a true para no bloquear (mejor práctica)
        url: "../ventas/funciones.php",
        dataType: "json",
        type: 'post',	
        data: { 
            paso: 'ltr_fracciones_serie_seleccionada', 
            cod_lot: cod_lot, 
            num_bil: num_bil, 
            num_ser: num_ser 
        },									
        success: function(data) {
            // Verificar si hay error en la respuesta
            if (data.error && data.error.length > 0) {
                swal('Error', data.error, 'error');
                return;
            }

            var fracciones = parseInt(data.fracciones) || 1;  // Asegurar que sea un número válido (default 1)
            
            console.log('Fracciones encontradas: ' + fracciones);
            //$("#current-frac").val(fracciones);

            var $currentFrac = $("#current-frac");
            if ($currentFrac.length > 0) {  // Verificar que el elemento exista
                $currentFrac.text(fracciones);  // Cambia el TEXTO visible (en lugar de .val())
                $currentFrac.data('max', fracciones);  // Actualiza data-max para lógica JS (ej. botones +/-)
                // Opcional: Actualiza el atributo HTML si lo necesitas para otros fines
                // $currentFrac.attr('max', fracciones);
                // Opcional: Actualizar totales o UI relacionada
                //actualizarTotales();  // Si existe esta función en tu código
            } else {
                console.error('Elemento #current-frac no encontrado en el DOM');
            }
        },	
        error: function (request, status, error) {
            console.error('Error en AJAX:', error);
            alert('Error al consultar fracciones: ' + request.responseText);
        }							
    });	
}

function fn_ltr_sorteo_activo() {
    const cod_lot = $('#cboLoterias_ltr').val();
    //console.log('blur con valor'+cod_lot);

    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../ventas/funciones.php",
            dataType: "json",
            type: "post",
            data: { paso: "ltr_sorteo_activo", cod_lot: cod_lot },
            success: function (data) {
                var error = data.error;

                if (error.length > 0) {
                    swal(error, "", "error");
                    reject(error);  
                    return;
                } else {
                    var arreglo = data.arreglo;

                    //console.log(arreglo);

                    // Actualizar el div con datos del sorteo
                    var datosSorteo = $('.datos-sorteo-row');
                    var premioMayorValor = Number(arreglo.vlr_premio_mayor / 1000000).toLocaleString('es-CO');
                    var premioMayorFormateado = premioMayorValor + ' Millones';
                    var fechaSorteo = arreglo.fec_sor ? `<span style=\"color:#007bff;font-size:16px;font-weight:bold;margin-left:10px;\">F.Sorteo: ${arreglo.fec_sor}</span>` : '';

                    var maxFracc = parseInt(arreglo.fracciones);
                    var currentFracc = maxFracc;

                    var nuevoBillete = arreglo.vlr_billete + arreglo.incentive_fractionPrice;
                    var nuevaFraccion = arreglo.vlr_fraccion + arreglo.incentive_fractionPrice;

                    datosSorteo.html(`
                        <div class=\"adicionar-line\" style=\"text-align: center; margin-bottom: 10px;\">
                            <button type=\"button\" id=\"btn-adicionar\" style=\"background-color: #28a745; color: white; border: 1px solid #28a745; border-radius: 5px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;\">Adicionar</button>
                        </div>
                        <div class=\"sorteo-line\">
                            Sorteo: <strong>${arreglo.num_sor}</strong> ${fechaSorteo} - Mayor: <strong class=\"premio-mayor-valor\">$${premioMayorFormateado}</strong>
                            <button type=\"button\" id=\"frac-minus\" style=\"width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;\">-</button>
                            <strong id=\"current-frac\" data-max=\"${maxFracc}\" style=\"margin: 0 5px; color: #ff0000; font-weight: bold;\">${currentFracc}</strong>
                            <button type=\"button\" id=\"frac-plus\" style=\"width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;\">+</button>
                        </div>
                        <div class=\"precios-line\">
                            <span class=\"precios-item\">Fracciones: <strong>${arreglo.fracciones}</strong></span>
                            <span class=\"precios-item\">Billete: <strong>$${nuevoBillete.toLocaleString()}</strong></span>
                            <span class=\"precios-item\">Fracción: <strong>$${nuevaFraccion.toLocaleString()}</strong></span>
                            <span class=\"precios-item incentivo-item\">Incentivo x Fracción: <strong>$${arreglo.incentive_fractionPrice.toLocaleString()}</strong></span>
                        </div>
                    `);

                    // Colocar el div datos-sorteo-row debajo de #div_series_disponibles y arriba de .table-container-scroll
                    $('.datos-sorteo-row').insertAfter('#div_series_disponibles');

                    // Mostrar .datos-sorteo-row después de generar
                    $('.datos-sorteo-row').show();

                    // Opcional: Actualizar totales en la interfaz si es necesario
                    actualizarTotales();

                    resolve(data); 
                }
            },
            error: (req) => {
                manejarErrorAjaxLottired(req);

                reject(req);
            },
            complete: () => $('#spinner_lr_num_bil1').hide()
        });
    });
}

// Función reutilizable para validar premios por barcode
function fn_valida_premio(barcode) {
    var clienteCedula = $('#cliente-cedula').val();
    var id_usu        = $('#id_usu').val();

    // Validar que id_usu no sea nulo o vacío
    if (!id_usu || id_usu.trim() === '') {
        swal('Error de usuario', 'No se pudo obtener la identificación del usuario. Por favor, recargue la página e intente nuevamente.', 'error')
            .then((value) => {
                // Opcional: recargar la página
                // location.reload();
            });
        return false;
    }

    var barcodeStr = String(barcode);

    var limpiarYEnfocar = function() {
        $('#barcode').val('');
        $('#barcode').focus();
    };

    // Validar longitud de 11 caracteres
    if (barcodeStr.length !== 11) {
        limpiarYEnfocar();
        return false;
    }

    // ✅ VALIDAR QUE LA CEDULA DEL CLIENTE TENGA VALOR
    if (clienteCedula.length == 0) {
        swal('DEBE INGRESAR LA INFORMACION  DEL CLIENTE', "", "error")
            .then((value) => {
                $('#cliente-cedula').focus();
            });
        return false;
    }

    $.ajax({
        url: "../ventas/funciones.php",
        dataType: "json",
        type: "post",
        data: { paso: "busca_premio_ltr", barcode: barcode },
        success: function (data) {
            var error = data.error;
            var totalPrizeNetValue = parseFloat(data.totalPrizeNetValue) || 0;

            if (error.length > 0) {
                swal(error, "", "error").then(limpiarYEnfocar);
            } else if (totalPrizeNetValue > 300000) {
                swal("!!VALOR DEL PREMIO ES MAYOR QUE EL MONTO AUTORIZADO POR FAVOR COMUNIQUESE CON LA OFICINA PRINCIPAL PARA COBRARLO!!", "", "error")
                    .then(limpiarYEnfocar);
            } else {
                var arreglo = data.arreglo;
                //console.log(arreglo);

                // Formatear el valor del premio como moneda
                var valorFormateado = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(totalPrizeNetValue || 0);

                // Recorrer el arreglo (array de premios)
                if (Array.isArray(arreglo)) {

                    // Validar que el barcode no exista ya en la tabla
                    if ($('#tbl_premios_ltr tbody tr[data-barcode="' + barcode + '"]').length > 0) {
                        // Si existe duplicado, solo limpiar y cerrar modal si está abierto
                        limpiarYEnfocar();
                        if ($('#modal-buscar-transacciones').is(':visible')) {
                            $('#modal-buscar-transacciones').modal('hide');
                        } else if ($('#modal-buscar-transacciones').hasClass('show')) {
                            $('#modal-buscar-transacciones').modal('hide');
                        }
                        return false;
                    }

                    arreglo.forEach(function(premio) {
                        var nuevaFila = `
                            <tr data-barcode="${barcode}">
                                <td class="text-center">${premio.lotteryDraw || ''}</td>
                                <td class="text-center">${premio.numero || ''}</td>
                                <td class="text-center">${premio.serie || ''}</td>
                                <td style="text-align: left;">${premio.nombre_premio || ''}</td>
                                <td class="text-right">${valorFormateado}</td>
                                <td class="text-center" style="cursor: pointer;"><i class="fa fa-trash text-danger"></i></td>
                            </tr>
                        `;
                        $('#tbl_premios_ltr tbody').append(nuevaFila);
                    });
                    // Llamar a actualizarTotales después de agregar premios
                    actualizarTotales();

                    // Cerrar modal si fue llamado desde modal-buscar-transacciones
                    // Probar diferentes formas de detectar el modal
                    if ($('#modal-buscar-transacciones').is(':visible')) {
                        $('#modal-buscar-transacciones').modal('hide');
                    } else if ($('#modal-buscar-transacciones').hasClass('show')) {
                        $('#modal-buscar-transacciones').modal('hide');
                    }

                    swal('Premio agregado', 'El premio ha sido registrado exitosamente.', 'success')
                    .then(limpiarYEnfocar);

                }

                // swal('Premio agregado', 'El premio ha sido registrado exitosamente.', 'success')
                //     .then(limpiarYEnfocar);
            }

            // ✅ NUEVO AJAX: Consumir funciones.php local con paso valida_premio
            $.ajax({
                url: "funciones.php",
                dataType: "json",
                type: "post",
                data: {
                    paso: "valida_premio",
                    barcode: barcode,
                    id_usu: id_usu
                },
                success: function(data) {
                    if (data.error && data.error.length > 0) {
                        console.error('Error al validar tiquete:', data.error);
                        swal('Error al validar', 'No se pudo marcar el tiquete como validado: ' + data.error, 'error');
                    } else {
                        console.log('Tiquete validado correctamente en la base de datos');
                    }
                },
                error: function (request, status, error) {
                    console.error('Error en AJAX de validación:', request.responseText);
                    swal('Error de conexión', 'No se pudo conectar con el servidor para validar el tiquete', 'error');
                }
            });

        },
        error: function (request, status, error) {
            alert(request.responseText);
            limpiarYEnfocar();
        }
    });
    
    return true;
}

// -------------------------------------------------------------
//  Función de impresión (debe ser async y sin redeclarar parámetros)
// -------------------------------------------------------------
async function imprimirTicketVenta(cliente, recibidos, totales) {
    // Convertir a JSON (no volver a declarar con const)
    const clienteStr   = JSON.stringify(cliente);
    const recibidosStr = JSON.stringify(recibidos);
    const totalesStr   = JSON.stringify(totales);

    const params = new URLSearchParams({
        cliente:   clienteStr,
        recibidos: recibidosStr,
        totales:   totalesStr
    }).toString();

    try {
        // 1. Enviar solicitud de impresión
        await jsWebClientPrint.print(params);
        // console.log('Solicitud de impresión enviada.');
    } catch (error) {
        // console.error('Error en el proceso de impresión o recuperación:', error);
        // swal("Error", "Ocurrió un error durante la impresión.", "error");
    }
}




