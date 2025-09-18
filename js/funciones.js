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
            var cod_lot = $('#cboLoterias').val(); 
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
                        
                        datosSorteo.html(`
                            <div class="sorteo-line">
                                Sorteo: <strong>${arreglo.num_sor}</strong> - Premio Mayor: <strong class="premio-mayor-valor">$${premioMayorFormateado}</strong> Fracciones:
                                <button type="button" id="frac-minus" style="width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">-</button>
                                <strong id="current-frac" data-max="${maxFracc}" style="margin: 0 5px; color: #ff0000; font-weight: bold;">${currentFracc}</strong>
                                <button type="button" id="frac-plus" style="width: 28px; height: 28px; border-radius: 50%; font-size: 14px; margin: 0 5px; border: 1px solid #ccc; background: #f8f9fa; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">+</button>
                            </div>
                            <div class="precios-line">
                                <span class="precios-item">Fracciones: <strong>${arreglo.fracciones}</strong></span>
                                <span class="precios-item">Billete: <strong>$${arreglo.vlr_billete.toLocaleString()}</strong></span>
                                <span class="precios-item">Fracción: <strong>$${arreglo.vlr_fraccion.toLocaleString()}</strong></span>
                                <span class="precios-item incentivo-item">Incentivo x Fracción: <strong>$${arreglo.incentive_fractionPrice.toLocaleString()}</strong></span>
                            </div>
                        `);
        
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
});

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
