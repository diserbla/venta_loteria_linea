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

    if (valorAPagar < 0) {
        $('#valor-pagar-valor').text('$0');
        // Muestra alerta informativa con swal v1
        swal(
            'Saldo a Favor',
            'El valor de los premios es mayor que la venta. El cliente no debe pagar.',
            'info'
        );
    } else {
        // Formatea el número a estilo de moneda local (ej. 1.234.567)
        $('#valor-pagar-valor').text('');
    }   
}

$(document).ready(function(){

    // Restringe la entrada solo a números para los campos con la clase .numeric-input
    $(document).on('input', '.numeric-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
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
});
