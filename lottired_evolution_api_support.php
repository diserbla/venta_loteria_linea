<?php
$telefono = "3232841619"; // Número de teléfono proporcionado
$telefono = preg_replace('/[^0-9]/', '', $telefono);

$filename = "extra_bogota_mayo_2025.jpg"; // Nombre del archivo en la carpeta local
if (!file_exists($filename)) {
    die("El archivo $filename no existe.");
}

$encoded_file = base64_encode(file_get_contents($filename)); // Codificar el archivo en base64

$apiKey = "3DB1F02AB891-402D-B7B7-8CD160C4CEDB";

// Payload para enviar el texto
$payload_text = json_encode(array(
    "number" => "57" . $telefono,
    "text" => "🎉 ¡Felicidades! 🎉\nHas realizado en Mercaloterías S.A una compra de Loteria en Linea  que podría cambiar tu suerte. 🍀✨\nGuarda este mensaje y mantente atento/a, porque pronto podrías estar cobrando tu premio. 💰📲\n\n🚨 No lo borres 🚨\nEste podría ser tu camino hacia la fortuna. ¡Suerte! 🍀💫",
    //"text" => "¡?? Apreciado Cliente! ??\nEste sabado 10 de Mayo, juega nuestro Gran Sorteo Extra Dorado de Loteria de Bogota! ??\n¡No te pierdas los $18.000 Mil Millones de Premio Mayor! ??????\nPide tu billete en nuestra linea ?? 3503031742, o visitanos en nuestros Puntos de Venta Mercaloterias! ???\n¡Estamos listos para atenderte! ¡Te esperamos! ??",
));

// Payload para enviar la imagen
$payload_image = json_encode(array(
    "number" => "57" . $telefono,
    "mediatype" => "image",
    "fileName" => $filename,
    "media" => $encoded_file
));

$respuestaTexto = enviarMensaje('texto', $apiKey, $payload_text);
$respuestaImagen = enviarMensaje('imagen', $apiKey, $payload_image);

function enviarMensaje($tipo, $apiKey, $contenido) {
    $urlBase = "http://46.202.159.143:8080/message/";
    $url = $urlBase . ($tipo === 'texto' ? 'sendText/Diego' : 'sendMedia/Diego');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $contenido);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "apikey: $apiKey"
    ));

    $respuesta = curl_exec($curl);
    curl_close($curl);

    return $respuesta;
}


// Ejemplo de respuesta exitosa
$response = array(
    "success" => true,
    "message" => "Mensaje enviado correctamente."
);

// Respuesta de prueba para verificar salida JSON
header('Content-Type: application/json');
echo json_encode(array("success" => true, "message" => "Prueba de respuesta JSON"));
//exit;
