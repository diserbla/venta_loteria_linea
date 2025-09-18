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
    var totalPremiosText = $('#total-premios-valor').text().replace(/[$.]/g, '').replace(',', '.');

    var totalVenta = parseFloat(totalVentaText) || 0;
    var totalPremios = parseFloat(totalPremiosText) || 0;

    var valorAPagar = totalVenta - totalPremios;

    // Obtener el campo de efectivo
    var efectivoInput = $('#ingrese-efectivo');

    if (valorAPagar < 0) {
        $('#valor-pagar-valor').text('$0');
        efectivoInput.prop('disabled', true).val(''); // Deshabilitar y limpiar
        // Muestra alerta informativa con swal v1
        swal(
            'Saldo a Favor',
            'El valor de los premios es mayor que la venta. El cliente no debe pagar.',
            'info'
        );
    } else {
        // Formatea el número a estilo de moneda local (COP)
        var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        $('#valor-pagar-valor').text(formatoMoneda.format(valorAPagar));
        efectivoInput.prop('disabled', false); // Habilitar el campo
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
                        
                        var maxFracc = parseInt(arreglo.fracciones);
                        var currentFracc = maxFracc;
                        
                        var nuevoBillete = arreglo.vlr_billete + arreglo.incentive_fractionPrice;
                        var nuevaFraccion = arreglo.vlr_fraccion + arreglo.incentive_fractionPrice;
                        
                        datosSorteo.html(`
                            <div class="adicionar-line" style="text-align: center; margin-bottom: 10px;">
                                <button type="button" id="btn-adicionar" style="background-color: #28a745; color: white; border: 1px solid #28a745; border-radius: 5px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">Adicionar</button>
                            </div>
                            <div class="sorteo-line">
                                Sorteo: <strong>${arreglo.num_sor}</strong> - Premio Mayor: <strong class="premio-mayor-valor">$${premioMayorFormateado}</strong> Fracciones:
                                <button type="button" id="frac-minus" style="width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">-</button>
                                <strong id="current-frac" data-max="${maxFracc}" style="margin: 0 5px; color: #ff0000; font-weight: bold;">${currentFracc}</strong>
                                <button type="button" id="frac-plus" style="width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">+</button>
                            </div>
                            <div class="precios-line">
                                <span class="precios-item">Fracciones: <strong>${arreglo.fracciones}</strong></span>
                                <span class="precios-item">Billete: <strong>$${nuevoBillete.toLocaleString()}</strong></span>
                                <span class="precios-item">Fracción: <strong>$${nuevaFraccion.toLocaleString()}</strong></span>
                                <span class="precios-item incentivo-item">Incentivo x Fracción: <strong>$${arreglo.incentive_fractionPrice.toLocaleString()}</strong></span>
                            </div>
                        `);

                        // Colocar el div datos-sorteo-row debajo de #div_series_disponibles y arriba de .table-container-scroll
                        $('.datos-sorteo-row').insertAfter('#div_series_disponibles');
        
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
    });


    // Llama a la función para gestionar el scroll al cargar la página
    gestionarScrollTablas();

    // Evento click para el botón grabar_cliente
    $('#btn-grabar-cliente').click(function(e) {
        e.preventDefault();
        fn_graba_cliente();
    });
    
    // Event listeners para botones de fracciones
    $(document).on('click', '#frac-minus', function() {
        let $display = $('#current-frac');
        let current = parseInt($display.text()) || 1;
        if (current > 1) {
            $display.text(current - 1);
        }
    });
    
    $(document).on('click', '#frac-plus', function() {
        let $display = $('#current-frac');
        let current = parseInt($display.text()) || 1;
        let max = parseInt($display.data('max')) || 1;
        if (current < max) {
            $display.text(current + 1);
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
        
        if (cfr1 !== '' && cfr2 !== '' && cfr3 !== '') {
            console.log('Los inputs cfr1, cfr2 y cfr3 tienen valores no vacíos al ingresar en cfr4');
        }

        fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones);
    });

    // Evento click para btn-adicionar: agregar registro de prueba a tbl_ltr
    $(document).on('click', '#btn-adicionar', function() {
        var loteria = $('#cboLoterias_ltr option:selected').text();
        var sorteo = $('.sorteo-line strong').eq(0).text() || '0000'; // Sorteo del DOM
        var numero = $('#cfr1').val() + $('#cfr2').val() + $('#cfr3').val() + $('#cfr4').val();
        var serie = $('input[type="radio"]:checked', '#div_series_disponibles').val() || '000';
        var fracc = parseInt($('#current-frac').text()) || 1;

        if (!numero || numero.length !== 4) {
            swal('Número incompleto', 'Ingrese un número completo de 4 dígitos.', 'warning');
            return;
        }

        if (!serie || serie === '') {
            swal('Serie no seleccionada', 'Seleccione una serie disponible.', 'warning');
            return;
        }

        // Extraer valor de la fracción del DOM
        var preciosLine = $('.precios-line').text();
        var matchFraccion = preciosLine.match(/Fracción:\s*\$?([\d.,]+)/);
        var valorFraccionStr = matchFraccion ? matchFraccion[1].replace(/[.,]/g, '') : '0';
        var valorFraccion = parseFloat(valorFraccionStr) || 0;
        var valorTotal = fracc * valorFraccion;
        var formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        var valor = formatoMoneda.format(valorTotal);

        var nuevaFila = `
            <tr>
                <td style="text-align: left;">${loteria}</td>
                <td class="text-center">${sorteo}</td>
                <td class="text-center">${numero}</td>
                <td class="text-center">${serie}</td>
                <td class="text-center">${fracc}</td>
                <td class="text-right">${valor}</td>
                <td class="text-center eliminar-fila" style="cursor: pointer;"><i class="fa fa-trash text-danger"></i></td>
            </tr>
        `;

        $('#tbl_ltr tbody').append(nuevaFila);

        // Actualizar total de venta (sumar valorTotal calculado)
        var currentTotalText = $('#total-venta-valor').text().replace(/[$.]/g, '').replace(',', '');
        var currentTotal = parseFloat(currentTotalText) || 0;
        currentTotal += valorTotal;
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

    // Evento para eliminar filas de la tabla (clic en ícono trash)
    $(document).on('click', '.eliminar-fila i', function() {
        var fila = $(this).closest('tr');
        var valorFilaText = fila.find('td:nth-child(6)').text().replace(/[$.]/g, '').replace(',', '');
        var valorFila = parseFloat(valorFilaText) || 0;

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
});

function fn_series_disponibles(cboLoterias_ltr, cfr1, cfr2, cfr3, cfr4, numFracciones) {
    /*
    // Función para manejar series disponibles con los parámetros proporcionados
    console.log('fn_series_disponibles llamada con:');
    console.log('cboLoterias_ltr:', cboLoterias_ltr);
    console.log('cfr1:', cfr1);
    console.log('cfr2:', cfr2);
    console.log('cfr3:', cfr3);
    console.log('cfr4:', cfr4);
    console.log('numFracciones:', numFracciones);
    */
    
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

            //var reservationId = $('#hd_ltr_reservationId').val();

            //$("#spinner").hide();

            /*
            if (reservationId.length == 1) {
                $("#div_series_disponibles").html(data.salida);
            }
            */

            $("#div_series_disponibles").html(data.salida);

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
                //$('.ocultar_ltr1').show();

                //$("#ltr_nro_fracciones").val(fracciones);
                //$("#ltr_nro_fracciones").attr('max', fracciones);
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
                        swal('Cliente no encontrado', 'No se encontró un cliente con esta cédula.', 'warning');
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
