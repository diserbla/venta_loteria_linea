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

$(document).ready(function(){

    $('#cboLoterias_ltr').click(function(){		

        $.ajax({
            async:	false, 
            url:	"../ventas/funciones.php",
            dataType:"json",
            type	: 'post',
            data:  { paso: 'cboLoterias_ltr'},									
            success: function(data){
                $("#list_loterias_ltr").html(data.salida);	
            },	
            error: function (request, status, error) 
            {
                alert(request.responseText);
            }							
        });			
    });

    $('#cboLoterias_ltr').change(function(){		
        fn_ltr_sorteo_activo();
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
    $('#limpiar-numero-btn').on('click', function() {
        $('.number-input-digit').val('');
        $('#cfr1').focus();
        $('#div_series_disponibles').hide();
        $('.datos-sorteo-row').hide();
    });

    // Auto-focus en el primer campo de entrada al cargar la página
    $('#cliente-cedula').focus();

    // Auto-tab al siguiente campo de entrada de número
    $('.number-input-digit').on('keyup', function(e) {
        if (this.value.length === this.maxLength) {
            $(this).closest('.number-input-digit-wrapper').next().find('input').focus();
        }
    });

    // Auto-tab en blur para #cfr1 si tiene valor
    $('#cfr1').blur(function() {
        if (this.value.length === 1) {
            fn_ltr_sorteo_activo();
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
        $('#cliente-cedula').prop('disabled', true);
        $('#cliente-nombres').prop('disabled', true);
        $('#cliente-apellidos').prop('disabled', true);
        $('#cliente-celular').prop('disabled', true);
        $('#cliente-direccion').prop('disabled', true);
        $('#cliente-email').prop('disabled', true);
        fn_graba_cliente();
    });

    // Evento click para el botón cancelar_cliente: habilita los campos de datos del cliente
    $('#btn-cancelar-cliente').click(function(e) {
        e.preventDefault();
        $('#cliente-cedula').prop('disabled', false);
        $('#cliente-nombres').prop('disabled', false);
        $('#cliente-apellidos').prop('disabled', false);
        $('#cliente-celular').prop('disabled', false);
        $('#cliente-direccion').prop('disabled', false);
        $('#cliente-email').prop('disabled', false);
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

    // Evento click para el botón grabar venta con confirmación
    $('#btn-grabar-venta').click(function(e) {
        e.preventDefault();

        // Validar que haya datos para grabar
        if (!validarDatosVenta()) {
            swal('Datos incompletos', 'Debe completar los datos del cliente y agregar al menos un ítem a la venta.', 'warning');
            return;
        }

        // Mostrar confirmación con detalles de la venta
        //mostrarConfirmacionVenta();
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

        console.log('Adicionar:', {
            cod_lot,
            numero,
            serie,
            fracciones_seleccionadas: fracc,
            valor_fraccion: valorFraccion,
            incentivo_x_fraccion: valorIncentivo,
            total: nuevoValorFraccion
        });

        //genera el proceso de reserva y luego actualiza el data-reservacion en la fila
        var reservacion = '';
        var nuevaFila = `
            <tr data-reservacion="">
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
        $('#cfr1').focus();
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

    // Evento click para el botón genera_numero
    $('#genera_numero').click(function() {
        fn_ltr_sorteo_activo();

        var cboLoterias_ltr = $('#cboLoterias_ltr').val();
        var numFracciones = parseInt($('#current-frac').text()) || 1;

        $.ajax({
            async: true,
            url: "../ventas/funciones.php",
            dataType: "json",
            type: "post",
            data: { paso: "ltr_genera_numero", cod_lot: cboLoterias_ltr, nro_fracciones: numFracciones },
            beforeSend: function () {
                $("#spinner").show();
            },
            success: function (data) {
                var error = data.error;
                var fracciones 			   = data.fracciones;

                if (error.length > 0) {
                    swal(error, "", "error");
                } else {
                    var arreglo = data.arreglo;

                    $('#cfr1').val(arreglo.lr_num_bil1);
                    $('#cfr2').val(arreglo.lr_num_bil2);
                    $('#cfr3').val(arreglo.lr_num_bil3);
                    $('#cfr4').val(arreglo.lr_num_bil4);

                    fn_series_disponibles(cboLoterias_ltr, arreglo.lr_num_bil1, arreglo.lr_num_bil2, arreglo.lr_num_bil3,arreglo.lr_num_bil4, fracciones);
                    
                }
            },
            error: function (request, status, error) {
                alert(request.responseText);
            }
        });

    });

    //premios de loteria
    $(document).on("keypress", "#barcode", function (e) {
        if ((e.keyCode == 13) || (e.keyCode == 9)) {
            var barcode = $('#barcode').val();
            var limpiarYEnfocar = function() {
                $('#barcode').val('');
                $('#barcode').focus();
            };

            // Validar longitud de 11 caracteres
            if (barcode.length !== 11) {
                limpiarYEnfocar();
                e.preventDefault();
                return;
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
                        console.log(arreglo);

                        // Formatear el valor del premio como moneda
                        var valorFormateado = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(totalPrizeNetValue || 0);

                        // Recorrer el arreglo (array de premios)
                        if (Array.isArray(arreglo)) {
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
                        }

                        swal('Premio agregado', 'El premio ha sido registrado exitosamente.', 'success')
                            .then(limpiarYEnfocar);
                    }
                },
                error: function (request, status, error) {
                    alert(request.responseText);
                    limpiarYEnfocar();
                }
            });
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
    $(document).on('click', '#btn-grabar-venta', function(e) {
        e.preventDefault();
        var tieneVenta = $('#tbl_ltr tbody tr').length > 0;
        var tienePremio = $('#tbl_premios_ltr tbody tr').length > 0;
        if (!tieneVenta && !tienePremio) {
            swal('Sin registros', 'Debe agregar al menos un registro en la tabla de venta o de premios para poder grabar.', 'warning');
            return;
        }
        // Si hay registros, mostrar confirmación
        swal({
            title: '¿Está seguro de grabar la venta?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: "Cancelar",
                    value: false,
                    visible: true,
                    className: "btn btn-danger",
                    closeModal: true,
                },
                confirm: {
                    text: "Sí, grabar",
                    value: true,
                    visible: true,
                    className: "btn btn-success",
                    closeModal: true,
                }
            }
        }).then(function(result) {
            if (result) {
                // Aquí va la lógica para grabar la venta (AJAX o lo que corresponda)
                swal('Venta grabada', 'La venta ha sido registrada exitosamente.', 'success');
                // Puedes llamar aquí a tu función real de grabado si la tienes
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

    // Evento para Enter en campo premio-cedula
    $(document).on("keypress", "#premio-cedula", function (e) {
        if ((e.keyCode == 13) || (e.keyCode == 9)) {
            e.preventDefault();
            fn_validar_busqueda_premio();
        }
    });

    // Función de validación para búsqueda de premios
    function fn_validar_busqueda_premio() {
        var cedula = $('#premio-cedula').val(); 
        var barcode = $('#barcode').val(); 

        if (cedula.length == 0) {
            swal('DEBE INGRESAR EL NUMERO DE CEDULA', "", "error")
                .then((value) => {
                    $('#premio-cedula').focus();
                });									
            return false;
        } else {
            if (barcode.length == 0) {
                //fn_ltr_busca_premio(cedula);
                // ✅ MOSTRAR MODAL SOLO CUANDO: cédula > 0 Y barcode == 0
                $('#modal-buscar-transacciones').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                });
            }

            console.log('Buscar premios para cédula:', cedula, 'y barcode:', barcode);

            return true;
        }
        
    }

});

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
    var cod_lot = $('#cboLoterias_ltr').val();
    //console.log('blur con valor'+cod_lot);
    $.ajax({
        url: "../ventas/funciones.php",
        dataType: "json",
        type: "post",
        data: { paso: "ltr_sorteo_activo", cod_lot: cod_lot },
        success: function (data) {
            var error = data.error;

            if (error.length > 0) {
                swal(error, "", "error");
            } else {
                var arreglo = data.arreglo;

                console.log(arreglo);

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
            }
        },
        error: function (request, status, error) {
            alert(request.responseText);
        },
        complete: function() {
        // Ocultar el spinner cuando la solicitud se complete
            $('#spinner_lr_num_bil1').hide();
        }
    });
}

// Función para validar datos mínimos requeridos
function validarDatosVenta() {
    const cedula = $('#cliente-cedula').val().trim();
    const nombres = $('#cliente-nombres').val().trim();
    const totalVenta = parseFloat($('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '.')) || 0;
    const itemsCount = $('#tbl_ltr tbody tr').length;

    return cedula && nombres && totalVenta > 0 && itemsCount > 0;
}
