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

    $(document).on('blur', '.email-input', function() {
        const email = this.value.trim();
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                swal({
                    title: 'Email Inválido',
                    text: 'Por favor, ingrese un correo electrónico válido (ej. usuario@dominio.com)',
                    type: 'warning',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                this.focus();
            }
        }
    });

    // Función para validar campos requeridos del cliente al grabar
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

        // Si es válido, aquí puedes agregar lógica para grabar (ej. AJAX a funciones.php)
        // swal de éxito se puede mover al handler del botón si se necesita
        // TODO: Implementar lógica de grabado real, ej. enviar form via AJAX
    }

    // Valida el formato de email en tiempo real para el campo con clase .email-input
    $(document).on('input blur', '.email-input', function() {
        const email = this.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            // Si es inválido en blur, mostrar alerta suave o limpiar (aquí usamos alert simple; puedes cambiar a tooltip)
            if (this === document.activeElement) { // Solo en input si está activo
                return; // No interrumpir mientras escribe
            } else {
                swal({
                    title: 'Formato Inválido',
                    text: 'Por favor ingrese un email válido (ej. usuario@dominio.com)',
                    type: 'warning',
                    confirmButtonText: 'OK'
                });
                this.value = ''; // Limpiar si inválido al salir
                this.focus(); // Re-enfocar para corregir
            }
        }
    });

    // Limpia los campos de entrada del número
    $('#limpiar-numero-btn').on('click', function() {
        $('.number-input-digit').val('');
        $('#cfr1').focus();
    });

    // Auto-focus en el primer campo de entrada al cargar la página
    $('#cfr1').focus();

    // Auto-tab al siguiente campo de entrada de número
    $('.number-input-digit').on('keyup', function(e) {
        if (this.value.length === this.maxLength) {
            $(this).closest('.number-input-digit-wrapper').next().find('input').focus();
        }
    });

    // Llama a la función para gestionar el scroll al cargar la página
    gestionarScrollTablas();

    // Evento click para el botón Grabar: valida y procesa si OK
    $(document).on('click', '#btn-grabar-cliente', function(e) {
        e.preventDefault();
        if (validarCliente()) {
            // Aquí implementar lógica real de grabado, ej. AJAX a funciones.php
            console.log('Datos del cliente válidos y grabados.');
            // Ejemplo: swal de éxito ya está en validarCliente()
        }
    });
});

function limpiarCliente() {
    $('#cliente-cedula').val('');
    $('#cliente-nombres').val('');
    $('#cliente-apellidos').val('');
    $('#cliente-celular').val('');
    $('#cliente-direccion').val('');
    $('#cliente-email').val('');
    $('#cliente-cedula').focus();
}
